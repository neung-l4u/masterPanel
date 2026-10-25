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
$pulseName       = trim($_POST['pulse_name']        ?? '');
$email           = trim($_POST['email']             ?? '');
$amountRaw       = $_POST['amount'] ?? '0';
$amount          = (float)str_replace([',', ' ฿', '฿', ' '], '', $amountRaw);
$productName     = trim($_POST['product_name']      ?? '');
$billingDate     = trim($_POST['billing_date']      ?? date('Y-m-d'));
$mondayItemId    = trim($_POST['monday_item_id']    ?? '');
$nextChargeDateRaw = trim($_POST['next_charge_date'] ?? '');
// รอบฟรีจากโปรโมชั่น (เช่น Buy 1 Get 1 Free) → ออกใบกำกับภาษียอด 0 ตามที่บัญชีต้องการ
$freeCycle       = ($_POST['free_cycle'] ?? '') === '1';
if ($freeCycle) {
    $amount = 0.0;
}

if ($email === '' || !preg_match('/^\d+$/', $mondayItemId)) {
    echo json_encode(['success' => false, 'message' => 'ต้องระบุ email และ monday_item_id']);
    exit;
}

// ยอด 0 ได้เฉพาะเมื่อส่ง free_cycle=1 มาชัดเจน กัน mapping ใน Make พังแล้วออกบิล 0 โดยไม่ตั้งใจ
if (!$freeCycle && $amount <= 0) {
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

// ชื่อร้านตามที่ PO เห็นบน monday (ชื่อ item บน Past billing) ให้หน้า Receipt Thailand แสดงคู่กับ Trading Name
if ($pulseName !== '') {
    $db->query('UPDATE `thCustomer` SET `shopName` = ? WHERE `id` = ?', $pulseName, $customerId);
}

// --- กันออกบิลซ้ำ: รอบเดียวกัน (project item + billingDate) ออกได้ใบเดียว เผื่อ Make retry ---
$dupRows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`billingSeq`, i.`amount`, r.`id` AS receipt_id
     FROM `thInvoice` i LEFT JOIN `thReceipt` r ON r.`invoice_id` = i.`id`
     WHERE i.`customer_id` = ? AND i.`monday_item_id` = ? AND i.`billingDate` = ? AND i.`status` != ?
     LIMIT 1',
    $customerId, $mondayItemId, $billingDate, 'cancelled'
)->fetchAll();
if (!empty($dupRows[0])) {
    $dup = $dupRows[0];
    $baseUrl = 'https://report.localforyou.com';
    echo json_encode([
        'success' => true,
        'duplicate' => true,
        'message' => 'Invoice for this billing date already exists',
        'pulse_name' => $pulseName,
        'monday_item_id' => $mondayItemId,
        'customer_id' => $customerId,
        'customer_name' => $cust['name'],
        'customer_email' => $cust['email'],
        'invoice_id' => (int)$dup['id'],
        'invoiceID' => $dup['invoiceID'],
        'receipt_id' => (int)$dup['receipt_id'],
        'receiptID' => $dup['invoiceID'],
        'billingSeq' => (int)$dup['billingSeq'],
        'billing_date' => $billingDate,
        'next_charge_date' => $nextChargeDateRaw,
        'amount' => (float)$dup['amount'],
        'currency' => 'THB',
        'slip_form_url' => $baseUrl . '/modules/customerMailUpSlip/index.php?invoice_id=' . (int)$dup['id'],
        'receipt_url' => $baseUrl . '/pages/receiptTH.php?invoice_id=' . (int)$dup['id'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

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
// หัก ณ ที่จ่าย 3% เฉพาะนิติบุคคล ตามกฎเดียวกับฟอร์ม signup (modules/signup/assets/js/ajaxFunction.js)
$withhold = ($cust['type'] ?? '') === 'นิติบุคคล' ? round($net * 0.03, 2) : 0.0;
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
    // amount = ราคาก่อน VAT, fullamount = ราคารวม VAT (รูปแบบเดียวกับใบจาก signup)
    'amount'     => number_format($net, 2, '.', ''),
    'fullamount' => number_format($gross, 2, '.', ''),
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
    'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `monday_item_id`, `createdAt`)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())',
    $customerId, $invoiceID, 'subscription', $productJson, $netPay, $thBathIn, 'pending', 'monday', $nextSeq, $billingDate, $mondayItemId
);
$newInvoiceId = (int)$db->lastInsertId();

// --- INSERT thReceipt (pending รอลูกค้าส่งสลิป / รอบฟรีไม่ต้องจ่าย → confirmed ทันที) ---
$thBathRe = convertToBahtText($netPay);
$db->query(
    'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `confirmed_by`, `status`) VALUES (?,?,?,?,?,?)',
    $newInvoiceId, $invoiceID, $netPay, $thBathRe, $freeCycle ? 'promotion' : null, $freeCycle ? 'confirmed' : 'pending'
);
$receiptId = (int)$db->lastInsertId();

// --- UPDATE clientType → subscription เสมอ ---
$db->query(
    'UPDATE `thCustomer` SET `clientType` = ? WHERE `id` = ?',
    'subscription', $customerId
);

$baseUrl = 'https://report.localforyou.com';

echo json_encode([
    'success' => true,
    'message' => 'Subscription invoice and receipt created',
    'pulse_name' => $pulseName,
    'monday_item_id' => $mondayItemId,
    'customer_id' => $customerId,
    'customer_name' => $cust['name'],
    'customer_email' => $cust['email'],
    'invoice_id' => $newInvoiceId,
    'invoiceID' => $invoiceID,
    'receipt_id' => $receiptId,
    'receiptID' => $invoiceID,
    'billingSeq' => $nextSeq,
    'billing_date' => $billingDate,
    'next_charge_date' => $nextChargeDateRaw,
    'amount' => $netPay,
    'currency' => 'THB',
    'free_cycle' => $freeCycle,
    'status' => $freeCycle ? 'confirmed' : 'pending',
    'slip_form_url' => $baseUrl . '/modules/customerMailUpSlip/index.php?invoice_id=' . $newInvoiceId,
    'receipt_url' => $baseUrl . '/pages/receiptTH.php?invoice_id=' . $newInvoiceId,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
