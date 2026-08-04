<?php
/**
 * sample_create_invoice_th.php
 * สร้าง invoice + receipt ตัวอย่างจากข้อมูล monday.com สำหรับลูกค้าไทย
 * ตัวอย่าง: ภูกาษาวาเล่ย์ - Restaurant - TH (monday item_id: 2074899749)
 */

ob_start();
$root = dirname(__DIR__, 2);
require_once $root . '/assets/db/db.php';
require_once $root . '/api/invoice/convertToBahtText.php';

// ต่อ DB localhost ก่อน ถ้าไม่ได้ fallback ไป db (Docker)
$creds = [
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root', 'name' => 'localfor_reports'],
    ['host' => 'db',       'user' => 'root', 'pass' => 'root', 'name' => 'localfor_reports'],
];
$lastError = '';
$db = null;
foreach ($creds as $c) {
    try {
        @$db = new db($c['host'], $c['user'], $c['pass'], $c['name'], 'utf8mb4');
        if ($db) break;
    } catch (Throwable $e) {
        $lastError = $e->getMessage();
    }
}
if (!$db) {
    ob_clean();
    exit('DB connection failed: ' . $lastError);
}
ob_clean();
header('Content-Type: text/plain; charset=utf-8');

// --- ข้อมูลจาก monday.com (CSV export) ---
$mondayItem = [
    'item_id'              => '2074899749',
    'name'                 => 'ภูกาษาวาเล่ย์ - Restaurant - TH',
    'group'                => 'Completed Projects',
    'active_subscriptions' => 'THB - ฿2000 WHT03M00 Website Hosting + Email included',
    'billing_date'         => '2026-03-25',
    'amount'               => 2000.00,
    'email'                => 'sawi2515@gmail.com',
    'stripe_customer_id'   => 'cus_T3xXXrWrJgmuaI',
    'initial_product'      => 'Add-on Only - ฿0.00',
];

$shopName = 'ภูกาษาวาเล่ย์';

// --- หา customer ใน DB ---
$custRows = $db->query(
    'SELECT `id`, `customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type` FROM `thCustomer` WHERE `name` LIKE ? OR `customerCode` = ? LIMIT 1',
    '%Phu Kasa%', 'KZ8HZ9AV'
)->fetchAll();

if (empty($custRows[0])) {
    exit('ไม่พบ customer ใน DB');
}

$cust = $custRows[0];
$customerId   = (int)$cust['id'];
$customerCode = $cust['customerCode'];

// --- หา billingSeq ถัดไป ---
$seqRows = $db->query(
    'SELECT MAX(`billingSeq`) AS maxSeq FROM `thInvoice` WHERE `customer_id` = ?',
    $customerId
)->fetchAll();
$lastSeq = (int)($seqRows[0]['maxSeq'] ?? 0);
$nextSeq = $lastSeq + 1;
$invoiceID = $customerCode . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

// --- ป้องกัน duplicate ตาม billingSeq ---
$dupRows = $db->query(
    'SELECT `id` FROM `thInvoice` WHERE `customer_id` = ? AND `billingSeq` = ? LIMIT 1',
    $customerId, $nextSeq
)->fetchAll();

if (!empty($dupRows[0])) {
    exit("Invoice ซ้ำ: {$invoiceID} มีอยู่แล้ว");
}

// --- คำนวณแบบรูป: amount จาก monday.com = ราคารวม VAT (gross) ---
$gross = (float)$mondayItem['amount'];
$net   = round($gross / 1.07, 2);
$vat   = round($gross - $net, 2);
$withhold = round($net * 0.03, 2);
$netPay   = round($gross - $withhold, 2);

$billingDate = $mondayItem['billing_date'];
$billingDateThai = date('d/m/Y', strtotime($billingDate));

$thBathIn = convertToBahtText($netPay);

$taxType = match ($cust['type']) {
    'นิติบุคคล' => 'นิติบุคคล',
    'บุคคลธรรมดา' => 'บุคคลธรรมดา',
    default => 'นิติบุคคล',
};

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
    'table' => [[
        'product' => $mondayItem['active_subscriptions'],
        'qyt'     => '1',
        'amount'  => number_format($gross, 2, '.', ''),
    ]],
    'summary' => [
        'subtotal'           => number_format($net, 2, '.', ''),
        'vat'                => number_format($vat, 2, '.', ''),
        'grandtotal_inc_vat' => number_format($gross, 2, '.', ''),
        'withholdingTax'     => number_format($withhold, 2, '.', ''),
        'net_payment'        => number_format($netPay, 2, '.', ''),
    ],
], JSON_UNESCAPED_UNICODE);

// --- Payload ตัวอย่างที่จะส่งไป createInvoiceTH.php (หรือ Make.com) ---
$payload = [
    'secret'        => 'L4U_BILLING_TH_2026',
    'customer_id'   => $customerId,
    'shop_name'     => $shopName,
    'amount'        => $gross,
    'billing_date'  => $billingDate,
    'items'         => json_decode($productJson, true)['table'],
    'monday_item_id'=> $mondayItem['item_id'],
];

echo "=== Monday.com Item ===\n";
print_r($mondayItem);

echo "\n=== DB Customer ===\n";
print_r($cust);

echo "\n=== Next Invoice Number ===\n";
echo "billingSeq: {$nextSeq}\n";
echo "invoiceID:  {$invoiceID}\n";

echo "\n=== Payload to createInvoiceTH.php / Make.com ===\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// --- INSERT thInvoice (status pending) ---
$db->query(
    'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `createdAt`)
     VALUES (?,?,?,?,?,?,?,?,?,?,NOW())',
    $customerId,
    $invoiceID,
    'subscription',
    $productJson,
    $netPay,
    $thBathIn,
    'pending',
    'monday',
    $nextSeq,
    $billingDate
);

$newInvoiceId = $db->lastInsertID();

// --- INSERT thReceipt รอไว้ ---
$db->query(
    'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`) VALUES (?,?,?,?,?)',
    $newInvoiceId,
    $invoiceID,
    $netPay,
    $thBathIn,
    'pending'
);

echo "\n=== Product JSON ===\n";
echo json_encode(json_decode($productJson, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== Result ===\n";
echo "Created invoice_id: {$newInvoiceId}\n";
echo "Created invoiceID:  {$invoiceID}\n";
echo "Status: pending (waiting for slip/payment)\n";
?>
