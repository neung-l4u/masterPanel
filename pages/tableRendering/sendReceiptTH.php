<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
require_once dirname(__DIR__, 2) . '/api/invoice/convertToBahtText.php';
ob_clean();
header('Content-Type: application/json');

$invoice_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$invoice_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// --- Query thInvoice + thCustomer ---
$invRows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`thBathIn`, i.`createdAt`,
            c.`id` AS customer_id, c.`customerCode`,
            c.`name`, c.`address`, c.`taxNumber`,
            c.`email` AS customerEmail, c.`phone` AS customerPhone,
            c.`type`, c.`sale`,
            c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE i.`id` = ? LIMIT 1',
    $invoice_id
)->fetchAll();

if (empty($invRows[0])) {
    echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    exit;
}

$row = $invRows[0];

// --- Parse product JSON ---
$productJson = json_decode($row['product'] ?? '', true);
$summary     = $productJson['summary']   ?? [];
$rawItems    = $productJson['table']     ?? [];
$quotation   = $productJson['quotation'] ?? [];

$tableItems = array_map(function($item) {
    $label = $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '-';
    return [
        'product' => trim($label),
        'qyt'     => $item['qyt']    ?? 1,
        'amount'  => $item['amount'] ?? 0,
    ];
}, $rawItems);

// --- Get receipt data ---
$receiptRows = $db->query(
    'SELECT `slip`, `thBathRe`, `receiptID`, `amount_paid`, `sentAt`, `status`
     FROM `thReceipt`
     WHERE `invoice_id` = ?
     ORDER BY `id` DESC LIMIT 1',
    $invoice_id
)->fetchAll();

$receipt = $receiptRows[0] ?? [];

if (empty($receipt) || ($receipt['status'] ?? '') !== 'confirmed') {
    echo json_encode(['success' => false, 'message' => 'Receipt not confirmed']);
    exit;
}

// --- Base URL ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl  = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$receiptUrl = $baseUrl . '/pages/receiptTH.php?invoice_id=' . $invoice_id;

$slipPath = $receipt['slip'] ?? '';
$slipUrl  = $slipPath ? $baseUrl . '/modules/signup/assets/uploads/' . $slipPath : '';
$thBathRe = $receipt['thBathRe'] ?? convertToBahtText((float)$row['amount']);
$receiptID = $receipt['receiptID'] ?? $row['invoiceID'];

// --- Build payload ---
$payload = [
    'invoice_id'     => $row['id'],
    'invoiceID'      => $row['invoiceID'],
    'receiptID'      => $receiptID,
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
    'receipt_url'    => $receiptUrl,
    'slip_url'       => $slipUrl,
    'base_url'       => $baseUrl,
    'thBathIn'       => $row['thBathIn'] ?? '',
    'thBathRe'       => $thBathRe,
    'amount_paid'    => $receipt['amount_paid'] ?? $row['amount'],
    'receiptStatus'  => $receipt['status'] ?? '',
    'sentAt'         => $receipt['sentAt'] ?? '',
];

// --- Send webhook ---
$ch = curl_init('https://hook.us1.make.com/dv92a58kun2j6pwjrx6sgcasb0lmd8la');
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

if (!($httpCode >= 200 && $httpCode < 300)) {
    echo json_encode(['success' => false, 'message' => 'Webhook error HTTP ' . $httpCode, 'webhook_response' => $response]);
    exit;
}

// --- อัป Monday board: reset Charge Customer + Next Charge Date ---
try {
    $mondayToken   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
    $mondayBoardId = '5029904278';
    $customerEmail = $row['customerEmail'] ?? '';

    if ($customerEmail) {
        $searchQuery = 'query($boardId: ID!, $email: String!) { boards(ids: [$boardId]) { items_page(limit: 1, query_params: { rules: [{ column_id: "text_mm58ns1b", compare_value: [$email] }] }) { items { id } } } }';
        $sCh = curl_init('https://api.monday.com/v2');
        curl_setopt_array($sCh, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode(['query' => $searchQuery, 'variables' => ['boardId' => $mondayBoardId, 'email' => $customerEmail]]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
        ]);
        $sResp    = curl_exec($sCh);
        curl_close($sCh);
        $sData    = json_decode($sResp, true);
        $targetId = $sData['data']['boards'][0]['items_page']['items'][0]['id'] ?? null;

        if ($targetId) {
            $updateQuery = 'mutation($boardId: ID!, $itemId: ID!, $colVals: JSON!) { change_multiple_column_values(board_id: $boardId, item_id: $itemId, column_values: $colVals, create_labels_if_missing: true) { id } }';
            $colVals = json_encode([
                'color_mm58wjwk' => '',
                'date_mm586gty'  => '',
            ]);
            $uCh = curl_init('https://api.monday.com/v2');
            curl_setopt_array($uCh, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_POSTFIELDS     => json_encode(['query' => $updateQuery, 'variables' => ['boardId' => $mondayBoardId, 'itemId' => $targetId, 'colVals' => $colVals]]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
            ]);
            $uResp = curl_exec($uCh);
            curl_close($uCh);
            error_log('[sendReceiptTH] Monday reset item=' . $targetId . ' resp=' . $uResp);
        } else {
            error_log('[sendReceiptTH] Monday: ไม่พบ item สำหรับ email=' . $customerEmail);
        }
    }
} catch (\Throwable $e) {
    error_log('[sendReceiptTH Monday] ' . $e->getMessage());
}

echo json_encode(['success' => true, 'message' => 'ส่ง Receipt สำเร็จ', 'receiptID' => $receiptID, 'webhook_response' => $response]);
?>
