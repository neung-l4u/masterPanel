<?php
/**
 * migrate_stripe_invoices.php
 * รัน 1 ครั้งเพื่อ:
 * 1. เพิ่ม billingSeq / billingDate ใน thInvoice
 * 2. Migrate invoice จากภาพ Stripe ของลูกค้าที่มีมากกว่า 1 ใบ
 * 3. อัปเดท thCustomer.clientType ตามจำนวน invoice รวม
 */

ob_start();
global $db;
$root = dirname(__DIR__, 2);
require_once $root . '/assets/db/db.php';
require_once __DIR__ . '/convertToBahtText.php';

// --- ต่อ DB localhost ก่อน ถ้าไม่ได้ fallback ไป db (Docker) ---
$creds = [
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root', 'name' => 'localfor_reports'],
    ['host' => 'db',       'user' => 'root', 'pass' => 'root', 'name' => 'localfor_reports'],
];
$lastError = '';
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

// --- 1. เพิ่ม column ถ้ายังไม่มี ---
try {
    $db->query("ALTER TABLE `thInvoice` ADD COLUMN `billingSeq` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `source`");
} catch (Throwable $e) { /* column already exists */ }
try {
    $db->query("ALTER TABLE `thInvoice` ADD COLUMN `billingDate` DATE DEFAULT NULL AFTER `billingSeq`");
} catch (Throwable $e) { /* column already exists */ }

// --- 2. อัปเดท invoice เก่าให้ billingSeq=1, billingDate=createdAt ---
$db->query("UPDATE `thInvoice` SET `billingSeq`=1, `billingDate`=DATE(`createdAt`) WHERE `billingDate` IS NULL");

// --- 3. ข้อมูล invoice จากภาพ Stripe ที่ยังไม่มีใน DB ---
// หมายเหตุ: ไม่ insert 0001 เพราะ DB มีอยู่แล้ว (แม้จะเป็น invoice คนละใบกับ Stripe ก็ตาม)
$invoices = [
    // Duangnet Thai massage (id=25)
    ['customer_id' => 25, 'seq' => 2, 'invoiceID' => 'QSF7O6ZQ-0002', 'amount' => 6899.00, 'createdAt' => '2026-05-08 09:58:00', 'dueDate' => null,      'status' => 'sent'],
    ['customer_id' => 25, 'seq' => 3, 'invoiceID' => 'QSF7O6ZQ-0003', 'amount' => 6899.00, 'createdAt' => '2026-06-25 00:01:00', 'dueDate' => null,      'status' => 'pending'],

    // Great Cafe Brunch Aonang Krabi (id=21)
    ['customer_id' => 21, 'seq' => 2, 'invoiceID' => 'E2LSKGLT-0002', 'amount' => 0.00,    'createdAt' => '2026-06-24 07:10:00', 'dueDate' => null,      'status' => 'sent'],

    // ไอคอนมี จำกัด (id=20)
    ['customer_id' => 20, 'seq' => 2, 'invoiceID' => 'WAZ2CDMQ-0002', 'amount' => 0.00,    'createdAt' => '2026-06-09 15:13:00', 'dueDate' => null,      'status' => 'sent'],

    // Zira Spa (id=19)
    ['customer_id' => 19, 'seq' => 2, 'invoiceID' => 'GKPVVT57-0002', 'amount' => 0.00,    'createdAt' => '2026-05-22 11:50:00', 'dueDate' => null,      'status' => 'sent'],

    // Family Hair & Spa (id=17)
    ['customer_id' => 17, 'seq' => 2, 'invoiceID' => 'TPZIGTMP-0002', 'amount' => 1999.00, 'createdAt' => '2026-05-13 12:24:00', 'dueDate' => '2026-05-16', 'status' => 'sent'],
    ['customer_id' => 17, 'seq' => 3, 'invoiceID' => 'TPZIGTMP-0003', 'amount' => 0.00,    'createdAt' => '2026-05-22 11:50:00', 'dueDate' => null,      'status' => 'sent'],

    // Cattleya Health Massage (id=16)
    ['customer_id' => 16, 'seq' => 2, 'invoiceID' => 'RIFG03LF-0002', 'amount' => 1999.00, 'createdAt' => '2026-05-13 12:30:00', 'dueDate' => '2026-05-16', 'status' => 'sent'],
    ['customer_id' => 16, 'seq' => 3, 'invoiceID' => 'RIFG03LF-0003', 'amount' => 0.00,    'createdAt' => '2026-05-22 11:50:00', 'dueDate' => null,      'status' => 'sent'],

    // ร้านครัวนายเหมือง ราชบุรี (id=15)
    ['customer_id' => 15, 'seq' => 2, 'invoiceID' => 'NZVB4EQ2-0002', 'amount' => 4900.00, 'createdAt' => '2026-05-08 09:58:00', 'dueDate' => null,      'status' => 'sent'],
    ['customer_id' => 15, 'seq' => 3, 'invoiceID' => 'NZVB4EQ2-0003', 'amount' => 4900.00, 'createdAt' => '2026-06-25 00:01:00', 'dueDate' => null,      'status' => 'pending'],

    // ครัวในสวน สวนรถไฟ (id=14)
    ['customer_id' => 14, 'seq' => 2, 'invoiceID' => 'YCX8IIIR-0002', 'amount' => 4900.00, 'createdAt' => '2026-05-08 09:58:00', 'dueDate' => null,      'status' => 'sent'],
    ['customer_id' => 14, 'seq' => 3, 'invoiceID' => 'YCX8IIIR-0003', 'amount' => 4900.00, 'createdAt' => '2026-06-25 00:00:00', 'dueDate' => null,      'status' => 'pending'],

    // sukkaya massage (id=13)
    ['customer_id' => 13, 'seq' => 2, 'invoiceID' => 'I1KDFWW4-0002', 'amount' => 0.00,    'createdAt' => '2026-05-08 09:58:00', 'dueDate' => null,      'status' => 'sent'],

    // Thai London Health Massage Pattaya (id=12)
    ['customer_id' => 12, 'seq' => 2, 'invoiceID' => 'HF9FJFKW-0002', 'amount' => 0.00,    'createdAt' => '2026-05-08 10:01:00', 'dueDate' => null,      'status' => 'sent'],

    // Guaytiew Rue Khun Pa (id=8)
    ['customer_id' => 8,  'seq' => 2, 'invoiceID' => '1TNBHJK7-0002', 'amount' => 4900.00, 'createdAt' => '2026-02-19 14:40:00', 'dueDate' => '2026-02-26', 'status' => 'pending'],

    // qqq / haeicexx (id=7)
    ['customer_id' => 7,  'seq' => 2, 'invoiceID' => '3I4KCHYK-0002', 'amount' => 0.00,    'createdAt' => '2026-05-08 09:58:00', 'dueDate' => null,      'status' => 'sent'],

    // B'Leo Beauty & Wellness Phuket (id=5)
    ['customer_id' => 5,  'seq' => 2, 'invoiceID' => 'JTIDPCGY-0002', 'amount' => 4900.00, 'createdAt' => '2025-12-25 13:14:00', 'dueDate' => '2025-12-25', 'status' => 'sent'],
    ['customer_id' => 5,  'seq' => 3, 'invoiceID' => 'JTIDPCGY-0003', 'amount' => 4900.00, 'createdAt' => '2026-01-25 13:14:00', 'dueDate' => '2026-01-25', 'status' => 'cancelled'],

    // Phu Kasa Co., Ltd. (id=4) — ข้อมูลวันที่จากภาพอาจต้อง verify เพิ่ม
    ['customer_id' => 4,  'seq' => 2, 'invoiceID' => 'KZ8HZ9AV-0002', 'amount' => 2499.00, 'createdAt' => '2025-02-25 00:00:00', 'dueDate' => '2025-02-25', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 3, 'invoiceID' => 'KZ8HZ9AV-0003', 'amount' => 2499.00, 'createdAt' => '2025-02-25 00:00:00', 'dueDate' => '2025-02-25', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 4, 'invoiceID' => 'KZ8HZ9AV-0004', 'amount' => 2500.00, 'createdAt' => '2025-09-27 15:51:00', 'dueDate' => '2025-09-29', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 5, 'invoiceID' => 'KZ8HZ9AV-0005', 'amount' => 0.00,    'createdAt' => '2025-12-25 13:14:00', 'dueDate' => '2025-12-25', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 6, 'invoiceID' => 'KZ8HZ9AV-0006', 'amount' => 2499.00, 'createdAt' => '2026-01-25 00:00:00', 'dueDate' => '2026-01-25', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 7, 'invoiceID' => 'KZ8HZ9AV-0007', 'amount' => 2499.00, 'createdAt' => '2026-02-25 00:00:00', 'dueDate' => '2026-02-25', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 8, 'invoiceID' => 'KZ8HZ9AV-0008', 'amount' => 0.00,    'createdAt' => '2026-03-24 15:46:00', 'dueDate' => '2026-03-24', 'status' => 'sent'],
    ['customer_id' => 4,  'seq' => 9, 'invoiceID' => 'KZ8HZ9AV-0009', 'amount' => 2000.00, 'createdAt' => '2026-03-25 15:29:00', 'dueDate' => '2027-03-25', 'status' => 'sent'],
];

$inserted = 0;
$skipped  = 0;
$receiptCreated = 0;
$receiptSkipped = 0;

foreach ($invoices as $inv) {
    // ดึง invoice แรกของลูกค้าเพื่อ clone product JSON
    $firstRows = $db->query(
        'SELECT `product` FROM `thInvoice` WHERE `customer_id` = ? ORDER BY `id` ASC LIMIT 1',
        $inv['customer_id']
    )->fetchAll();

    $productJson = '{}';
    if (!empty($firstRows[0]['product'])) {
        $product = json_decode($firstRows[0]['product'], true);
        if (is_array($product)) {
            $product['summary'] = [
                'subtotal'           => $inv['amount'],
                'vat'                => 0,
                'grandtotal_inc_vat' => $inv['amount'],
                'withholdingTax'     => 0,
                'net_payment'        => $inv['amount'],
            ];
            if (!empty($product['table'][0]) && is_array($product['table'][0])) {
                $product['table'][0]['amount'] = $inv['amount'];
                $product['table'][0]['qyt']    = 1;
            }
            $productJson = json_encode($product, JSON_UNESCAPED_UNICODE);
        }
    }

    $billingDate = $inv['dueDate'] ?? date('Y-m-d', strtotime($inv['createdAt']));
    $thBathIn    = convertToBahtText((float)$inv['amount']);
    $invoiceType = $inv['amount'] > 0 ? 'subscription' : 'one_time';

    // ข้ามการ insert invoice ถ้ามี invoiceID นี้อยู่แล้ว
    $existing = $db->query(
        'SELECT `id` FROM `thInvoice` WHERE `invoiceID` = ? LIMIT 1',
        $inv['invoiceID']
    )->fetchAll();

    if (!empty($existing[0])) {
        $invoiceId = (int)$existing[0]['id'];
        echo "SKIP (exists): {$inv['invoiceID']}\n";
        $skipped++;
    } else {
        $db->query(
            'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `createdAt`)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            $inv['customer_id'],
            $inv['invoiceID'],
            $invoiceType,
            $productJson,
            $inv['amount'],
            $thBathIn,
            $inv['status'],
            'manual',
            $inv['seq'],
            $billingDate,
            $inv['createdAt']
        );
        $invoiceId = $db->lastInsertID();
        echo "INSERTED: {$inv['invoiceID']} (customer_id={$inv['customer_id']}, seq={$inv['seq']}, amount={$inv['amount']})\n";
        $inserted++;
    }

    // --- สร้าง thReceipt ถ้ายังไม่มี ---
    $receiptExists = $db->query(
        'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? LIMIT 1',
        $invoiceId
    )->fetchAll();

    if (!empty($receiptExists[0])) {
        echo "  SKIP receipt (exists): {$inv['invoiceID']}\n";
        $receiptSkipped++;
        continue;
    }

    $receiptStatus = match ($inv['status']) {
        'sent'      => 'confirmed',
        'cancelled' => 'rejected',
        default     => 'pending',
    };
    $sentAt  = $receiptStatus === 'confirmed' ? $inv['createdAt'] : null;
    $needfix = $receiptStatus === 'rejected' ? 'Void invoice from Stripe migration' : null;
    $thBathRe = convertToBahtText((float)$inv['amount']);

    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`, `sentAt`, `needfix`) VALUES (?,?,?,?,?,?,?)',
        $invoiceId,
        $inv['invoiceID'],
        $inv['amount'],
        $thBathRe,
        $receiptStatus,
        $sentAt,
        $needfix
    );
    echo "  CREATED receipt: {$inv['invoiceID']} (status={$receiptStatus})\n";
    $receiptCreated++;
}

// --- 4. อัปเดท clientType ตามจำนวน invoice รวม ---
$db->query(
    "UPDATE `thCustomer` c
     SET `clientType` = 'subscription'
     WHERE (SELECT COUNT(*) FROM `thInvoice` i WHERE i.`customer_id` = c.`id`) > 1"
);

$db->query(
    "UPDATE `thCustomer` c
     SET `clientType` = 'first_time'
     WHERE (SELECT COUNT(*) FROM `thInvoice` i WHERE i.`customer_id` = c.`id`) <= 1"
);

echo "\nDone: inserted={$inserted}, skipped={$skipped}, receiptCreated={$receiptCreated}, receiptSkipped={$receiptSkipped}\n";
?>
