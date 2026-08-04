<?php
/**
 * getInvoiceDataTH.php
 *
 * ดึงข้อมูล invoice จาก invoice_id และ return ในรูปแบบ JSON
 * สำหรับใช้กับ Make.com blueprint ในการส่ง invoice ให้ลูกค้า
 *
 * POST params:
 *   invoice_id  int  invoice ID จาก thInvoice table
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

$invoiceId = (int)($_POST['invoice_id'] ?? $_GET['invoice_id'] ?? 0);

if ($invoiceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ต้องระบุ invoice_id']);
    exit;
}

// --- ดึงข้อมูล invoice ---
$invRows = $db->query(
    'SELECT i.`id`, i.`customer_id`, i.`invoiceID`, i.`type`, i.`product`, i.`amount`, i.`thBathIn`, i.`status`, i.`billingSeq`, i.`billingDate`, i.`createdAt`, i.`monday_item_id`
     FROM `thInvoice` i
     WHERE i.`id` = ?
     LIMIT 1',
    $invoiceId
)->fetchAll();

if (empty($invRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ invoice_id: ' . $invoiceId]);
    exit;
}

$invoice = $invRows[0];
$customerId = (int)$invoice['customer_id'];

// --- ดึงข้อมูล customer ---
$custRows = $db->query(
    'SELECT `id`, `name`, `address`, `taxNumber`, `type`, `email`, `phone`, `bankName`, `bankNumber`, `bankAccName`
     FROM `thCustomer`
     WHERE `id` = ?
     LIMIT 1',
    $customerId
)->fetchAll();

if (empty($custRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ customer_id: ' . $customerId]);
    exit;
}

$customer = $custRows[0];

// --- ดึงข้อมูล receipt ---
$recRows = $db->query(
    'SELECT `id`, `receiptID`, `amount_paid`, `thBathRe`, `status`, `slip`
     FROM `thReceipt`
     WHERE `invoice_id` = ?
     ORDER BY `id` DESC
     LIMIT 1',
    $invoiceId
)->fetchAll();

$receipt = $recRows[0] ?? null;

// --- Parse product JSON ---
$productJson = json_decode($invoice['product'] ?? '{}', true) ?? [];
$items = $productJson['table'] ?? [];
$quotation = $productJson['quotation'] ?? [];
$summary = $productJson['summary'] ?? [];

// --- Format dates ---
$createdAtObj = new DateTime($invoice['createdAt']);
$createdAtThai = $createdAtObj->format('d/m/Y');

$billingDateObj = new DateTime($invoice['billingDate']);
$billingDateThai = $billingDateObj->format('d/m/Y');

// --- Calculate next charge date (1 month after billing date) ---
$nextChargeObj = clone $billingDateObj;
$nextChargeObj->add(new DateInterval('P1M'));
$nextChargeDateThai = $nextChargeObj->format('d/m/Y');

// --- Build response ---
$baseUrl = 'https://report.localforyou.com';
$slipPath = $receipt['slip'] ?? '';
$slipUrl = !empty($slipPath) ? $baseUrl . '/modules/signup/assets/uploads/' . $slipPath : '';

$response = [
    'success' => true,
    'invoice_id' => (int)$invoice['id'],
    'invoiceID' => $invoice['invoiceID'],
    'receiptID' => $receipt['receiptID'] ?? $invoice['invoiceID'],
    'name' => $customer['name'],
    'address' => $customer['address'],
    'taxNumber' => $customer['taxNumber'],
    'type' => $customer['type'],
    'sale' => '',
    'customerEmail' => $customer['email'],
    'customerPhone' => $customer['phone'],
    'bankName' => $customer['bankName'],
    'bankThaiNumber' => $customer['bankNumber'],
    'bankThaiName' => $customer['bankAccName'],
    'createdAt' => $createdAtThai,
    'subtotal' => $summary['subtotal'] ?? '0.00',
    'vat' => $summary['vat'] ?? '0.00',
    'grandtotal' => $summary['grandtotal_inc_vat'] ?? '0.00',
    'withholdingTax' => $summary['withholdingTax'] ?? '0.00',
    'net_payment' => $summary['net_payment'] ?? '0.00',
    'thBathIn' => $invoice['thBathIn'],
    'thBathRe' => $receipt['thBathRe'] ?? $invoice['thBathIn'],
    'items' => array_map(function($item) {
        return [
            'product' => $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '-',
            'qyt' => $item['qyt'] ?? '1',
            'amount' => $item['amount'] ?? '0.00',
        ];
    }, $items),
    'quotation' => array_map(function($q) {
        $detail = $q['detail'] ?? [];
        return [
            'date' => $q['date'] ?? '',
            'detail' => array_map(function($d) {
                return [
                    'company' => $d['company'] ?? '',
                    'address' => $d['address'] ?? '',
                    'tax_id' => $d['tax_id'] ?? '',
                    'email' => $d['email'] ?? '',
                    'phone' => $d['phone'] ?? '',
                    'tax_type' => $d['tax_type'] ?? '',
                ];
            }, $detail),
        ];
    }, $quotation),
    'slip_form_url' => $baseUrl . '/modules/customerMailUpSlip/index.php?invoice_id=' . $invoiceId,
    'receipt_url' => $baseUrl . '/pages/receiptTH.php?invoice_id=' . $invoiceId,
    'slip_url' => $slipUrl,
    'base_url' => $baseUrl,
    'billingDate' => $invoice['billingDate'],
    'billingSeq' => (int)$invoice['billingSeq'],
    'next_charge_date' => $nextChargeDateThai,
    'monday_item_id' => $invoice['monday_item_id'],
];

echo json_encode([$response], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
