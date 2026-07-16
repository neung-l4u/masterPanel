<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
require_once dirname(__DIR__, 2) . '/api/invoice/convertToBahtText.php';
ob_clean();
header('Content-Type: application/json');

$id         = isset($_POST['id'])     ? (int)$_POST['id']     : 0;
$rawAction  = isset($_POST['action']) ? $_POST['action']       : 'send';
// map selectbox values → internal actions
$action = match($rawAction) {
    'confirmed' => 'send',
    'pending'   => 'pending',
    'rejected'  => 'pending',
    default     => $rawAction,
};

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// --- Query thInvoice JOIN thCustomer ---
$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`thBathIn`, i.`status`, i.`createdAt`,
            c.`id` AS customer_id, c.`customerCode`,
            c.`name`, c.`address`, c.`taxNumber`,
            c.`email` AS customerEmail, c.`phone` AS customerPhone,
            c.`type`, c.`sale`,
            c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE i.`id` = ? LIMIT 1',
    $id
)->fetchAll();

$row = $rows[0] ?? null;

if (empty($row)) {
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    exit;
}

// --- Parse product JSON ---
$productJson = json_decode($row['product'] ?? '', true);
$summary     = $productJson['summary']   ?? [];
$rawItems    = $productJson['table']     ?? [];
$quotation   = $productJson['quotation'] ?? [];

// Normalize: unify product/setupfee/addon → always 'product' key
$tableItems = array_map(function($item) {
    $label = $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '-';
    return [
        'product' => trim($label),
        'qyt'     => $item['qyt']        ?? 1,
        'amount'  => $item['amount']     ?? 0,
    ];
}, $rawItems);

// --- receiptID = invoiceID (same document) ---
$generatedReceiptID = $row['invoiceID'];

// --- Handle action=pending (รอยืนยันหลักฐาน) ---
if ($action === 'pending') {
    $thBathRe = convertToBahtText((float)$row['amount']);
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`) VALUES (?,?,?,?,?)',
        $id, $generatedReceiptID, $row['amount'], $thBathRe, 'pending'
    );
    $db->query('UPDATE `thInvoice` SET `status`=? WHERE `id`=?', 'pending', $id);
    echo json_encode(['success' => true, 'message' => 'บันทึกรอยืนยันหลักฐานเรียบร้อย', 'receiptID' => $generatedReceiptID]);
    exit;
}

// --- Base URL ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl  = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$receiptUrl = $baseUrl . '/pages/receiptTH.php?invoice_id=' . $id;

// --- Get slip from thReceipt ---
$receiptRows = $db->query(
    'SELECT `slip`, `thBathRe` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $id
)->fetchAll();
$slipPath = $receiptRows[0]['slip'] ?? '';
$thBathRe = $receiptRows[0]['thBathRe'] ?? convertToBahtText((float)$row['amount']);
$slipUrl  = $slipPath ? $baseUrl . '/modules/signup/assets/uploads/' . $slipPath : '';

// --- action=send: ส่ง invoice webhook ---
$payload = [
    'invoice_id'     => $row['id'],
    'invoiceID'      => $row['invoiceID'],
    'name'           => $row['name'],
    'address'        => $row['address'],
    'taxNumber'      => $row['taxNumber'],
    'type'           => $row['type'],
    'sale'           => $row['sale'],
    'customerEmail'  => $row['customerEmail'],
    'customerPhone'  => $row['customerPhone'],
    'bankName'       => $row['bankName'],
    'bankThaiNumber' => $row['bankThaiNumber'],
    'bankThaiName'   => $row['bankThaiName'],
    'createdAt'      => $row['createdAt'],
    'subtotal'       => $summary['subtotal']           ?? '',
    'vat'            => $summary['vat']                ?? '',
    'grandtotal'     => $summary['grandtotal_inc_vat'] ?? '',
    'withholdingTax' => $summary['withholdingTax']     ?? '',
    'net_payment'    => $summary['net_payment']        ?? '',
    'items'          => $tableItems,
    'quotation'      => $quotation,
    'receiptID'      => $generatedReceiptID,
    'receipt_url'    => $receiptUrl,
    'slip_url'       => $slipUrl,
    'base_url'       => $baseUrl,
    'thBathIn'       => $row['thBathIn'] ?? '',
    'thBathRe'       => $thBathRe,
];

$ch = curl_init('https://hook.us1.make.com/gnxfafua86k6mutxg8tr65cuxmrk3v2k');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'message' => 'cURL error: ' . $curlError]);
    exit;
}

if ($httpCode >= 200 && $httpCode < 300) {
    // Insert receipt + update invoice status
    $thBathRe = convertToBahtText((float)$row['amount']);
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`, `sentAt`) VALUES (?,?,?,?,?,NOW())',
        $id, $generatedReceiptID, $row['amount'], $thBathRe, 'confirmed'
    );
    $db->query('UPDATE `thInvoice` SET `status`=? WHERE `id`=?', 'sent', $id);
    echo json_encode(['success' => true, 'message' => 'ส่งเรียบร้อย', 'receiptID' => $generatedReceiptID, 'webhook_response' => $response]);
} else {
    echo json_encode(['success' => false, 'message' => 'Webhook error HTTP ' . $httpCode, 'webhook_response' => $response]);
}
?>
