<?php
/**
 * createInvoiceTH.php
 *
 * สร้าง invoice ใหม่จาก monday.com item data ที่ Make.com ส่งมา
 * แล้วส่ง email invoice ไปลูกค้าผ่าน Make.com webhook
 *
 * POST params (จาก Make.com):
 *   secret        string  auth key
 *   customer_id   int     thCustomer.id  (หรือใช้ shop_name แทน)
 *   shop_name     string  ชื่อร้าน (fallback หาก customer_id ไม่มี)
 *   amount        float   ยอดชำระ
 *   billing_date  string  YYYY-MM-DD
 *   items         json    array of {product, qyt, amount}  (optional)
 *   monday_item_id string
 */

ob_start();
session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';
require_once $docRoot . '/api/invoice/convertToBahtText.php';
require_once $docRoot . '/api/invoice/thApoMondayHelper.php';
ob_clean();
header('Content-Type: application/json');

// --- Ensure monday_item_id column exists (idempotent) ---
try {
    $db->query("ALTER TABLE `thInvoice` ADD COLUMN `monday_item_id` VARCHAR(50) DEFAULT NULL AFTER `billingDate`");
} catch (Throwable $e) { /* column already exists */ }

// --- Auth ---
$secret        = $_POST['secret'] ?? $_GET['secret'] ?? '';
$allowedSecret = 'L4U_BILLING_TH_2026';
$isLoggedIn    = !empty($_SESSION['id']);
if (!$isLoggedIn && $secret !== $allowedSecret) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// --- Params ---
$customerId    = (int)($_POST['customer_id']    ?? 0);
$shopName      = trim($_POST['shop_name']       ?? '');
$amount        = (float)($_POST['amount']       ?? 0);
$billingDate   = trim($_POST['billing_date']    ?? date('Y-m-d'));
$mondayItemId  = trim($_POST['monday_item_id']  ?? '');
$itemsRaw      = $_POST['items'] ?? '[]';
$items         = is_array($itemsRaw) ? $itemsRaw : (json_decode($itemsRaw, true) ?? []);

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $billingDate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid billing_date format']);
    exit;
}

// --- หา customer จาก customer_id หรือ shop_name ---
if ($customerId > 0) {
    $custRows = $db->query(
        'SELECT `id`, `customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type`, `clientType` FROM `thCustomer` WHERE `id` = ? LIMIT 1',
        $customerId
    )->fetchAll();
} elseif ($shopName !== '') {
    $custRows = $db->query(
        'SELECT `id`, `customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type`, `clientType` FROM `thCustomer` WHERE `name` LIKE ? LIMIT 1',
        $shopName . '%'
    )->fetchAll();
} else {
    echo json_encode(['success' => false, 'message' => 'ต้องระบุ customer_id หรือ shop_name']);
    exit;
}

if (empty($custRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ customer: ' . ($shopName ?: $customerId)]);
    exit;
}

$cust         = $custRows[0];
$customerId   = (int)$cust['id'];
$customerCode = $cust['customerCode'];
$clientType   = $cust['clientType'] ?? 'first_time';

// --- หา billingSeq ถัดไป ---
$seqRows = $db->query(
    'SELECT MAX(`billingSeq`) AS maxSeq FROM `thInvoice` WHERE `customer_id` = ?',
    $customerId
)->fetchAll();
$lastSeq  = (int)($seqRows[0]['maxSeq'] ?? 0);
$nextSeq  = $lastSeq + 1;
$invoiceID = $customerCode . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

// --- ป้องกัน duplicate ---
$dupCheck = $db->query(
    'SELECT `id` FROM `thInvoice` WHERE `customer_id` = ? AND `billingDate` = ? LIMIT 1',
    $customerId, $billingDate
)->fetchAll();
if (!empty($dupCheck[0])) {
    echo json_encode(['success' => false, 'message' => 'Invoice วันนี้มีอยู่แล้ว: ' . $invoiceID, 'existing_id' => $dupCheck[0]['id']]);
    exit;
}

// --- Build product JSON: amount จาก Make.com คือราคารวม VAT (gross) ---
$gross = (float)$amount;
$net   = round($gross / 1.07, 2);
$vat   = round($gross - $net, 2);
$withhold = round($net * 0.03, 2);
$netPay   = round($gross - $withhold, 2);

$thBathIn = convertToBahtText($netPay);

$taxType = match ($cust['type'] ?? '') {
    'นิติบุคคล' => 'นิติบุคคล',
    'บุคคลธรรมดา' => 'บุคคลธรรมดา',
    default => 'นิติบุคคล',
};

$billingDateThai = date('d/m/Y', strtotime($billingDate));

$productItems = [];
foreach ($items as $it) {
    $productItems[] = [
        'product' => $it['product'] ?? '',
        'qyt'     => (string)($it['qyt'] ?? 1),
        'amount'  => number_format((float)($it['amount'] ?? 0), 2, '.', ''),
    ];
}

$productJson = json_encode([
    'quotation' => [[
        'date'   => $billingDateThai,
        'detail' => [[
            'company'  => $cust['name'],
            'address'  => $cust['address'],
            'tax_id'   => $cust['taxNumber'],
            'email'    => $cust['email'],
            'phone'    => $cust['phone'],
            'tax_type' => $taxType,
        ]],
    ]],
    'table' => $productItems,
    'summary' => [
        'subtotal'           => number_format($net, 2, '.', ''),
        'vat'                => number_format($vat, 2, '.', ''),
        'grandtotal_inc_vat' => number_format($gross, 2, '.', ''),
        'withholdingTax'     => number_format($withhold, 2, '.', ''),
        'net_payment'        => number_format($netPay, 2, '.', ''),
    ],
], JSON_UNESCAPED_UNICODE);

// --- INSERT thInvoice ---
$db->query(
    'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `monday_item_id`, `createdAt`)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())',
    $customerId, $invoiceID, 'subscription', $productJson, $netPay, $thBathIn, 'pending', 'monday', $nextSeq, $billingDate, $mondayItemId
);
$newInvoiceId = $db->lastInsertId();

// --- Queue payload for confirmed Monday webhook ---
queueThApoMondayPayload($db, (int)$newInvoiceId);

// --- อัป clientType: first_time → subscription หลัง invoice แรกถูกสร้าง ---
if ($clientType === 'first_time' && $lastSeq >= 1) {
    $db->query(
        'UPDATE `thCustomer` SET `clientType` = ? WHERE `id` = ?',
        'subscription', $customerId
    );
}

// --- ส่ง email invoice ผ่าน Make.com webhook ---
$protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl    = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'report.localforyou.com');
$slipFormUrl = $baseUrl . '/modules/customerMailUpSlip/index.php?invoice_id=' . (int)$newInvoiceId;

$payload = [
    'invoice_id'    => (int)$newInvoiceId,
    'invoiceID'     => $invoiceID,
    'shopName'      => $cust['name'],
    'customerEmail' => $cust['email'],
    'amount'        => $netPay,
    'thBathIn'      => $thBathIn,
    'billingDate'   => $billingDate,
    'billingSeq'    => $nextSeq,
    'clientType'    => $clientType,
    'slip_form_url' => $slipFormUrl,
    'items'         => $items,
    'monday_item_id'=> $mondayItemId,
];

$webhookUrl = 'https://hook.us1.make.com/gnxfafua86k6mutxg8tr65cuxmrk3v2k';
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

if ($curlError) {
    echo json_encode(['success' => true, 'invoice_created' => true, 'invoiceID' => $invoiceID, 'warning' => 'Invoice สร้างแล้ว แต่ส่ง webhook ไม่ได้: ' . $curlError]);
    exit;
}

echo json_encode([
    'success'          => true,
    'invoice_id'       => (int)$newInvoiceId,
    'invoiceID'        => $invoiceID,
    'billingSeq'       => $nextSeq,
    'clientType'       => $clientType,
    'webhook_http'     => $httpCode,
    'webhook_response' => $response,
]);
?>
