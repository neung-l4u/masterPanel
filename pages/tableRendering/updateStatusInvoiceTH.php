<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
}

$isGet      = $_SERVER['REQUEST_METHOD'] === 'GET';
$invoice_id = $isGet ? (int)($_GET['invoice_id'] ?? 0) : (int)($_POST['invoice_id'] ?? 0);
$status     = $isGet ? 'confirmed' : ($_POST['status'] ?? '');
$allowed    = ['pending', 'confirmed', 'rejected'];

if (!$invoice_id || !in_array($status, $allowed)) {
    if ($isGet) {
        http_response_code(400);
        echo '<p style="font-family:sans-serif;text-align:center;margin-top:60px;color:red;">ลิงก์ไม่ถูกต้อง กรุณาติดต่อทีมงาน</p>';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid params']);
    }
    exit;
}

// Get previous status before update
$prevRows = $db->query(
    'SELECT `status` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoice_id
)->fetchAll();
$prevStatus = $prevRows[0]['status'] ?? '';

$db->query(
    'UPDATE `thReceipt` SET `status` = ? WHERE `invoice_id` = ?',
    $status, $invoice_id
);

$invoiceStatus = $status === 'confirmed' ? 'sent' : 'pending';
$db->query(
    'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
    $invoiceStatus, $invoice_id
);

if ($status === 'rejected') {
    $needfix = trim($_POST['needfix'] ?? $_GET['needfix'] ?? '');
    if ($needfix !== '') {
        $db->query('UPDATE `thReceipt` SET `needfix` = ? WHERE `invoice_id` = ?', $needfix, $invoice_id);
    }
}

// Fire webhook when rejected → pending (customer resubmits for review)
if ($status === 'pending' && $prevStatus === 'rejected') {
    require_once dirname(__DIR__, 2) . '/api/invoice/convertToBahtText.php';

    $invRows = $db->query(
        'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`thBathIn`,
                c.`name`, c.`address`, c.`taxNumber`, c.`type`, c.`sale`,
                c.`email` AS customerEmail, c.`phone` AS customerPhone,
                c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE i.`id` = ? LIMIT 1',
        $invoice_id
    )->fetchAll();

    if (!empty($invRows[0])) {
        $row        = $invRows[0];
        $productArr = json_decode($row['product'] ?? '', true) ?? [];
        $summary    = $productArr['summary'] ?? [];

        $receiptRows = $db->query(
            'SELECT `slip`, `thBathRe`, `receiptID` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
            $invoice_id
        )->fetchAll();
        $receipt   = $receiptRows[0] ?? [];
        $slipPath  = $receipt['slip'] ?? '';
        $slipUrl   = $slipPath ? 'https://report.localforyou.com/modules/signup2/assets/uploads/' . $slipPath : '';
        $receiptID = $receipt['receiptID'] ?? $row['invoiceID'];
        $thBathRe  = $receipt['thBathRe']  ?? convertToBahtText((float)$row['amount']);

        $webhookPayload = [
            'invoice_id'     => (int)$row['id'],
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
            'subtotal'       => $summary['subtotal']           ?? '',
            'vat'            => $summary['vat']                ?? '',
            'grandtotal'     => $summary['grandtotal_inc_vat'] ?? '',
            'withholdingTax' => $summary['withholdingTax']     ?? '',
            'net_payment'    => $summary['net_payment']        ?? '',
            'receiptID'      => $receiptID,
            'receipt_url'    => 'https://report.localforyou.com/pages/receiptTH.php?invoice_id=' . $invoice_id,
            'slip_url'       => $slipUrl,
            'thBathIn'       => $row['thBathIn'] ?? '',
            'thBathRe'       => $thBathRe,
        ];

        $ch = curl_init('https://hook.us1.make.com/rxexlgi6kk68fsygk64fvx6mz2uhbhdp');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
}

if ($isGet) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>ยืนยันสำเร็จ</title></head><body style="font-family:sans-serif;text-align:center;margin-top:80px;">';
    echo '<div style="color:#16a34a;font-size:48px;">&#10003;</div>';
    echo '<h2 style="color:#16a34a;">ยืนยันข้อมูลเรียบร้อยแล้ว</h2>';
    echo '<p style="color:#555;">สถานะใบแจ้งหนี้ #' . htmlspecialchars((string)$invoice_id) . ' ได้รับการอัปเดตเป็น <b>confirmed</b> แล้ว</p>';
    echo '</body></html>';
} else {
    echo json_encode(['success' => true]);
}
?>
