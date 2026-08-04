<?php
ob_start();
session_start();
if (empty($_SESSION['id'])) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
global $db;
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
header('Content-Type: application/json');

$search = trim($_POST['search'] ?? '');
$like   = '%' . $search . '%';

$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`amount`, i.`billingDate`, i.`createdAt`,
            c.`name` AS shopName,
            r.`id` AS receipt_id, r.`slip`
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     JOIN `thReceipt` r ON r.`id` = (
         SELECT MAX(r2.`id`) FROM `thReceipt` r2 WHERE r2.`invoice_id` = i.`id`
     )
     WHERE i.`status` NOT IN (\'sent\')
       AND (r.`slip` IS NULL OR r.`slip` = \'\')
       AND (c.`name` LIKE ? OR i.`invoiceID` LIKE ?)
     ORDER BY i.`id` DESC
     LIMIT 200',
    $like, $like
)->fetchAll();

echo json_encode(['success' => true, 'data' => $rows]);
?>
