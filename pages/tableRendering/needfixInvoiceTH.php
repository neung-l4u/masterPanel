<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();

$receipt_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if (!$receipt_id) {
    http_response_code(400);
    echo '<h2>Invalid request: missing receipt_id</h2>';
    exit;
}

// Lookup invoice_id from thReceipt
$receiptLookup = $db->query(
    'SELECT `invoice_id` FROM `thReceipt` WHERE `id` = ? LIMIT 1',
    $receipt_id
)->fetchAll();
if (empty($receiptLookup[0])) {
    http_response_code(404);
    echo '<h2>ไม่พบข้อมูล Receipt</h2>';
    exit;
}
$invoice_id = (int)$receiptLookup[0]['invoice_id'];

// Handle POST submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['needfix'] ?? '');
    if ($comment !== '') {
        $db->query(
            'UPDATE `thReceipt` SET `needfix` = ?, `status` = ? WHERE `id` = ?',
            $comment, 'rejected', $receipt_id
        );
        $db->query(
            'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
            'pending', $invoice_id
        );
    }
    $submitted = true;
}

// ดึงข้อมูล
$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`amount`,
            c.`name`, c.`email` AS customerEmail,
            r.`receiptID`, r.`needfix`, r.`status` AS receiptStatus
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     LEFT JOIN `thReceipt` r ON r.`id` = ?
     WHERE i.`id` = ? LIMIT 1',
    $receipt_id, $invoice_id
)->fetchAll();

if (empty($rows)) {
    http_response_code(404);
    echo '<h2>Invoice not found</h2>';
    exit;
}

$row = $rows[0];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รายงานแก้ไขข้อมูล</title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
    background: #f4f6fb;
    margin: 0;
    padding: 30px 15px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
  }
  .card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 36px 40px;
    max-width: 520px;
    width: 100%;
  }
  .header-icon { font-size: 36px; margin-bottom: 10px; }
  h2 { margin: 0 0 6px 0; font-size: 20px; color: #dc2626; }
  .subtitle { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
  .info-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 22px;
    font-size: 14px;
    line-height: 1.8;
  }
  .info-box .label { color: #6b7280; }
  .info-box .value { color: #111; font-weight: 600; }
  label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 120px;
    color: #111;
    outline: none;
    transition: border-color 0.2s;
  }
  textarea:focus { border-color: #dc2626; }
  .btn-submit {
    display: block;
    width: 100%;
    margin-top: 18px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px 0;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn-submit:hover { background: #b91c1c; }
  .success-box {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 8px;
    padding: 18px 20px;
    text-align: center;
    color: #991b1b;
    font-size: 15px;
    margin-top: 20px;
  }
  .success-box .big { font-size: 32px; margin-bottom: 8px; }
</style>
</head>
<body>
<div class="card">

  <?php if (!empty($submitted)): ?>
    <div class="success-box">
      <div class="big">⚠️</div>
      <strong>รับทราบการรายงานปัญหาแล้ว</strong><br>
      ทีมงานจะตรวจสอบและติดต่อกลับโดยเร็ว
    </div>
  <?php else: ?>

    <div class="header-icon">⚠️</div>
    <h2>รายงานแก้ไขข้อมูล</h2>
    <p class="subtitle">กรุณาระบุปัญหาที่พบเพื่อให้ทีมงานดำเนินการแก้ไข</p>

    <div class="info-box">
      <div><span class="label">ชื่อร้าน / บริษัท: </span><span class="value"><?= htmlspecialchars($row['name'] ?? '-') ?></span></div>
      <div><span class="label">อีเมล: </span><span class="value"><?= htmlspecialchars($row['customerEmail'] ?? '-') ?></span></div>
      <div><span class="label">หมายเลข Receipt: </span><span class="value"><?= htmlspecialchars($row['receiptID'] ?? $row['invoiceID'] ?? '-') ?></span></div>
      <div><span class="label">ยอดเงิน: </span><span class="value">฿<?= number_format((float)($row['amount'] ?? 0), 2) ?></span></div>
    </div>

    <form method="POST">
      <label for="needfix">รายละเอียดปัญหา / ความคิดเห็น</label>
      <textarea id="needfix" name="needfix" placeholder="เช่น ยอดเงินไม่ตรง, สลิปไม่ชัด, ข้อมูลลูกค้าผิด..." required></textarea>
      <button type="submit" class="btn-submit">📩 ส่งรายงาน</button>
    </form>

  <?php endif; ?>

</div>
</body>
</html>
