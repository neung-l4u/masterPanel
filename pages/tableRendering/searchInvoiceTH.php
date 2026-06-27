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

$q    = isset($_POST['q'])    ? trim($_POST['q'])    : '';
$mode = isset($_POST['mode']) ? trim($_POST['mode']) : 'select';

if (strlen($q) < 1) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อร้าน']);
    exit;
}

$like = $q . '%';

// --- mode=suggest: คืน list ร้านสำหรับ autocomplete dropdown ---
if ($mode === 'suggest') {
    $rows = $db->query(
        'SELECT DISTINCT c.`name` AS shopName
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE c.`name` LIKE ?
           AND i.`status` NOT IN (\'sent\')
         ORDER BY c.`name` ASC
         LIMIT 8',
        $like
    )->fetchAll();
    echo json_encode(['success' => true, 'suggestions' => array_column($rows, 'shopName')]);
    exit;
}

// --- mode=select (default): คืน invoice ล่าสุดของร้านนั้น ---
$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`amount`, i.`status`, i.`createdAt`, i.`billingSeq`,
            c.`name` AS shopName
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE c.`name` LIKE ?
       AND i.`status` NOT IN (\'sent\')
     ORDER BY i.`billingSeq` DESC, i.`id` DESC
     LIMIT 1',
    $like
)->fetchAll();

if (empty($rows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลร้าน หรือ Invoice รอบนี้ถูกส่งแล้ว']);
    exit;
}

echo json_encode(['success' => true, 'data' => $rows[0]]);
?>
