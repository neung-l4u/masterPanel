<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();

$invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if (!$invoice_id) {
    http_response_code(400);
    echo '<h2>Invalid request: missing invoice_id</h2>';
    exit;
}

// ดึง customer_id จาก thInvoice
$rows = $db->query('SELECT `id`, `customer_id` FROM `thInvoice` WHERE `id` = ? LIMIT 1', $invoice_id)->fetchAll();

if (empty($rows)) {
    http_response_code(404);
    echo '<h2>Invoice not found</h2>';
    exit;
}

$customer_id = $rows[0]['customer_id'];

// อัปเดท thReceipt
$db->query(
    'UPDATE `thReceipt` SET `confirmed_by` = ?, `status` = ? WHERE `invoice_id` = ?',
    34, 'confirmed', $invoice_id
);

// อัปเดท thInvoice
$db->query(
    'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
    'sent', $invoice_id
);

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>ยืนยันสำเร็จ</title>
<style>
body{font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f0fdf4;}
.box{background:#fff;border-radius:10px;padding:40px 50px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
h2{color:#16a34a;margin-bottom:10px;}
p{color:#555;font-size:15px;}
</style></head>
<body><div class="box">
<h2>✅ ยืนยันข้อมูลสำเร็จ</h2>
<p>Invoice ID: <strong>' . htmlspecialchars($invoice_id) . '</strong></p>
<p>Customer ID: <strong>' . htmlspecialchars($customer_id) . '</strong></p>
<p>อัปเดทสถานะเรียบร้อยแล้ว</p>
</div></body></html>';
?>
