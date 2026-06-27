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

$act = isset($_POST['act']) ? trim($_POST['act']) : 'list';

// --- getOne: ดึงข้อมูลเดียวสำหรับ modal ---
if ($act === 'getOne') {
    $submissionId = (int)($_POST['submission_id'] ?? 0);
    if (!$submissionId) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }
    $rows = $db->query(
        'SELECT s.`id` AS submission_id, s.`invoice_id`, s.`submittedBy`, s.`slip`,
                s.`note`, s.`submittedAt`, s.`status` AS slip_status,
                i.`invoiceID`, i.`amount`, i.`createdAt`,
                c.`name` AS shopName
         FROM `thSlipSubmission` s
         JOIN `thInvoice` i ON i.`id` = s.`invoice_id`
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE s.`id` = ? LIMIT 1',
        $submissionId
    )->fetchAll();

    if (empty($rows[0])) {
        echo json_encode(['success' => false, 'message' => 'Not found']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => $rows[0]]);
    exit;
}

// --- list: DataTable source ---
$search     = isset($_POST['search_text']) ? trim($_POST['search_text']) : '';
$slipStatus = isset($_POST['slip_status']) ? trim($_POST['slip_status']) : '';

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]  = '(c.`name` LIKE ? OR i.`invoiceID` LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($slipStatus !== '') {
    $where[]  = 's.`status` = ?';
    $params[] = $slipStatus;
}

$whereSql = implode(' AND ', $where);

$rows = $db->query(
    'SELECT s.`id` AS submission_id, s.`invoice_id`, s.`submittedBy`, s.`slip`,
            s.`note`, s.`submittedAt`, s.`status` AS slip_status,
            i.`invoiceID`, i.`amount`, i.`createdAt`,
            c.`name` AS shopName,
            r.`status` AS receipt_status
     FROM `thSlipSubmission` s
     JOIN `thInvoice` i ON i.`id` = s.`invoice_id`
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     LEFT JOIN `thReceipt` r ON r.`invoice_id` = i.`id`
     WHERE ' . $whereSql . '
     ORDER BY s.`submittedAt` DESC',
    ...$params
)->fetchAll();

echo json_encode(['data' => $rows ?: []]);
?>
