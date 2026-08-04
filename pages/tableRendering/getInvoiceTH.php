<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`status`, i.`createdAt`,
            c.`name`, c.`address`, c.`taxNumber`,
            c.`email` AS customerEmail, c.`phone` AS customerPhone,
            c.`type`, c.`sale`,
            c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName,
            r.`receiptID`, r.`slip`, r.`status` AS receiptStatus, r.`needfix`
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     LEFT JOIN `thReceipt` r ON r.`invoice_id` = i.`id`
     WHERE i.`id` = ? LIMIT 1',
    $id
)->fetchAll();

$row = $rows[0] ?? null;

if (empty($row)) {
    echo json_encode(['success' => false, 'message' => 'Not found']);
    exit;
}

$row['product'] = json_decode($row['product'] ?? '', true) ?: [];

echo json_encode(['success' => true, 'data' => $row]);
?>
