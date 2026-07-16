<?php
/**
 * createSubTH.php
 *
 * รับ webhook จาก Make.com เมื่อ Monday board trigger "Charge Customer"
 * → หา customer ด้วย email
 * → INSERT thInvoice (type=subscription) + thReceipt (status=pending)
 * → UPDATE thCustomer.clientType = subscription
 * → ส่ง webhook กลับ Make.com เพื่อส่งอีเมลให้ลูกค้า
 *
 * POST params (จาก Make.com):
 *   secret         string  auth key
 *   email          string  thCustomer.email (required)
 *   amount         float   ยอดชำระ (gross รวม VAT)
 *   product_name   string  ชื่อ product จาก Monday column
 *   billing_date   string  YYYY-MM-DD (optional, default today)
 *   monday_item_id string  Monday item id สำหรับ update Charge Status กลับ
 */

ob_start();
session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';
require_once $docRoot . '/api/invoice/convertToBahtText.php';
ob_clean();
header('Content-Type: application/json');

// --- Auth ---
$secret        = $_POST['secret'] ?? $_GET['secret'] ?? '';
$allowedSecret = 'L4U_BILLING_TH_2026';
$isLoggedIn    = !empty($_SESSION['id']);
if (!$isLoggedIn && $secret !== $allowedSecret) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// --- Params ---
$email         = trim($_POST['email']          ?? '');
$amountRaw     = $_POST['amount'] ?? '0';
$amount        = (float)str_replace([',', ' ฿', '฿', ' '], '', $amountRaw);
$productName   = trim($_POST['product_name']   ?? '');
$billingDate      = trim($_POST['billing_date']      ?? date('Y-m-d'));
$mondayItemId     = trim($_POST['monday_item_id']     ?? '');
$nextChargeDateRaw = trim($_POST['next_charge_date'] ?? '');

if ($email === '') {
    echo json_encode(['success' => false, 'message' => 'ต้องระบุ email']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'ต้องระบุ amount > 0']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $billingDate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid billing_date format (YYYY-MM-DD)']);
    exit;
}

// --- หา customer จาก email ---
$custRows = $db->query(
    'SELECT `id`, `customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type`, `clientType`, `bankName`, `bankNumber`, `bankAccName` FROM `thCustomer` WHERE `email` = ? LIMIT 1',
    $email
)->fetchAll();

if (empty($custRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ customer email: ' . $email]);
    exit;
}

$cust       = $custRows[0];
$customerId = (int)$cust['id'];

// --- หา billingSeq ถัดไป ---
$seqRows = $db->query(
    'SELECT MAX(`billingSeq`) AS maxSeq FROM `thInvoice` WHERE `customer_id` = ?',
    $customerId
)->fetchAll();
$lastSeq   = (int)($seqRows[0]['maxSeq'] ?? 0);
$nextSeq   = $lastSeq + 1;
$invoiceID = $cust['customerCode'] . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

// --- คำนวณ VAT / withholding ---
$gross    = $amount;
$net      = round($gross / 1.07, 2);
$vat      = round($gross - $net, 2);
$withhold = round($net * 0.03, 2);
$netPay   = round($gross - $withhold, 2);
$thBathIn = convertToBahtText($netPay);

$taxType = match ($cust['type'] ?? '') {
    'นิติบุคคล'    => 'นิติบุคคล',
    'บุคคลธรรมดา' => 'บุคคลธรรมดา',
    default        => 'นิติบุคคล',
};

$billingDateThai = date('d/m/Y', strtotime($billingDate));

$productItems = $productName !== '' ? [[
    'product' => $productName,
    'qyt'     => '1',
    'amount'  => number_format($gross, 2, '.', ''),
]] : [];

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
    'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `createdAt`)
     VALUES (?,?,?,?,?,?,?,?,?,?,NOW())',
    $customerId, $invoiceID, 'subscription', $productJson, $netPay, $thBathIn, 'pending', 'monday', $nextSeq, $billingDate
);
$newInvoiceId = (int)$db->lastInsertId();

// --- INSERT thReceipt (status = pending รอลูกค้าส่งสลิป) ---
$thBathRe = convertToBahtText($netPay);
$db->query(
    'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`) VALUES (?,?,?,?,?)',
    $newInvoiceId, $invoiceID, $netPay, $thBathRe, 'pending'
);

// --- UPDATE clientType → subscription เสมอ ---
$db->query(
    'UPDATE `thCustomer` SET `clientType` = ? WHERE `id` = ?',
    'subscription', $customerId
);

// --- อัป Monday board: text_mm58ay9j = invoiceID ล่าสุด (เช็ค email ตรงกัน) ---
try {
    $mondayToken   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
    $mondayBoardId = '5029904278';

    $searchQuery = 'query($boardId: ID!, $email: String!) { boards(ids: [$boardId]) { items_page(limit: 1, query_params: { rules: [{ column_id: "text_mm58ns1b", compare_value: [$email] }] }) { items { id name } } } }';
    $searchCh = curl_init('https://api.monday.com/v2');
    curl_setopt_array($searchCh, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $searchQuery, 'variables' => ['boardId' => $mondayBoardId, 'email' => $email]]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
    ]);
    $searchResp  = curl_exec($searchCh);
    curl_close($searchCh);

    $searchData  = json_decode($searchResp, true);
    $foundItemId = $searchData['data']['boards'][0]['items_page']['items'][0]['id'] ?? null;
    $targetItemId = $foundItemId ?? ($mondayItemId ?: null);

    if ($targetItemId) {
        $updateQuery = 'mutation($boardId: ID!, $itemId: ID!, $colVals: JSON!) { change_multiple_column_values(board_id: $boardId, item_id: $itemId, column_values: $colVals, create_labels_if_missing: true) { id } }';
        $colVals = json_encode(['text_mm58ay9j' => $invoiceID, 'link_mm5867rj' => '']);
        $updCh = curl_init('https://api.monday.com/v2');
        curl_setopt_array($updCh, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode(['query' => $updateQuery, 'variables' => ['boardId' => $mondayBoardId, 'itemId' => $targetItemId, 'colVals' => $colVals]]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
        ]);
        $updResp = curl_exec($updCh);
        curl_close($updCh);
        error_log('[createSubTH] Monday update text_mm58ay9j item=' . $targetItemId . ' resp=' . $updResp);
    } else {
        error_log('[createSubTH] Monday: ไม่พบ item สำหรับ email=' . $email);
    }
} catch (\Throwable $e) {
    error_log('[createSubTH Monday] ' . $e->getMessage());
}

// --- ส่ง webhook ไป Make.com เพื่อส่งอีเมลให้ลูกค้า ---
$baseUrl     = 'https://report.localforyou.com';
$slipFormUrl = $baseUrl . '/modules/customeruploadslip/?invoiceID=' . urlencode($invoiceID);

$receiptUrl  = $baseUrl . '/pages/receiptTH.php?invoice_id=' . $newInvoiceId;

$payload = [
    'invoice_id'     => $newInvoiceId,
    'invoiceID'      => $invoiceID,
    'receiptID'      => $invoiceID,
    'name'           => $cust['name'],
    'address'        => $cust['address'],
    'taxNumber'      => $cust['taxNumber'],
    'type'           => $taxType,
    'sale'           => '',
    'customerEmail'  => $cust['email'],
    'customerPhone'  => $cust['phone'],
    'bankName'       => $cust['bankName']    ?? '',
    'bankThaiNumber' => $cust['bankNumber']  ?? '',
    'bankThaiName'   => $cust['bankAccName'] ?? '',
    'createdAt'      => date('d/m/Y'),
    'subtotal'       => number_format($net,      2, '.', ''),
    'vat'            => number_format($vat,      2, '.', ''),
    'grandtotal'     => number_format($gross,    2, '.', ''),
    'withholdingTax' => number_format($withhold, 2, '.', ''),
    'net_payment'    => number_format($netPay,   2, '.', ''),
    'thBathIn'       => $thBathIn,
    'thBathRe'       => $thBathRe,
    'items'          => $productItems,
    'quotation'      => [[
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
    'slip_form_url'  => $slipFormUrl,
    'receipt_url'    => $receiptUrl,
    'slip_url'       => '',
    'base_url'       => $baseUrl,
    'billingDate'      => $billingDate,
    'billingSeq'       => $nextSeq,
    'next_charge_date' => $nextChargeDateRaw,
    'monday_item_id'   => $mondayItemId,
];

$webhookUrl = 'https://hook.us1.make.com/5l2ejqxpj926rmb1j6fvey0ks57yyps8';
$ch = curl_init($webhookUrl);
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
    echo json_encode([
        'success'         => true,
        'invoice_created' => true,
        'invoiceID'       => $invoiceID,
        'warning'         => 'Invoice สร้างแล้ว แต่ส่ง webhook ไม่ได้: ' . $curlError,
    ]);
    exit;
}

echo json_encode([
    'success'          => true,
    'invoice_id'       => $newInvoiceId,
    'invoiceID'        => $invoiceID,
    'billingSeq'       => $nextSeq,
    'webhook_http'     => $httpCode,
    'webhook_response' => $response,
]);
?>
