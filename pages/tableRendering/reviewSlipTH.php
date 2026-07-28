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

$submissionId = (int)($_POST['submission_id'] ?? 0);
$invoiceId    = (int)($_POST['invoice_id']    ?? 0);
$action       = isset($_POST['action']) ? trim($_POST['action']) : '';
$note         = isset($_POST['note'])   ? trim($_POST['note'])   : '';

if (!$submissionId || !$invoiceId || !in_array($action, ['confirm', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// ตรวจสอบ submission มีอยู่จริง
$subRows = $db->query(
    'SELECT `id`, `status` FROM `thSlipSubmission` WHERE `id` = ? LIMIT 1',
    $submissionId
)->fetchAll();

if (empty($subRows[0])) {
    echo json_encode(['success' => false, 'message' => 'Submission not found']);
    exit;
}

if ($action === 'confirm') {
    // อัป thSlipSubmission → reviewed
    $db->query(
        'UPDATE `thSlipSubmission` SET `status` = ? WHERE `id` = ?',
        'reviewed', $submissionId
    );

    // อัป thReceipt → confirmed
    $existing = $db->query(
        'SELECT `id`, `status` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
        $invoiceId
    )->fetchAll();
    $prevReceiptStatus = $existing[0]['status'] ?? '';

    if (!empty($existing[0])) {
        $db->query(
            'UPDATE `thReceipt` SET `status` = ?, `sentAt` = NOW() WHERE `id` = ?',
            'confirmed', $existing[0]['id']
        );
    }

    // อัป thInvoice → confirmed
    $db->query(
        'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
        'confirmed', $invoiceId
    );

    // Fire thApoMonday webhook when receipt becomes confirmed for the first invoice (signup)
    if ($prevReceiptStatus !== 'confirmed') {
        require_once dirname(__DIR__, 2) . '/api/invoice/thApoMondayHelper.php';
        sendThApoMondayPayload($db, $invoiceId);
    }

    echo json_encode(['success' => true, 'message' => 'Confirmed เรียบร้อย']);

} else {
    // reject: อัป thSlipSubmission → reviewed, thReceipt → rejected, thInvoice → rejected
    $db->query(
        'UPDATE `thSlipSubmission` SET `status` = ? WHERE `id` = ?',
        'reviewed', $submissionId
    );

    $existing = $db->query(
        'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
        $invoiceId
    )->fetchAll();

    if (!empty($existing[0])) {
        $db->query(
            'UPDATE `thReceipt` SET `status` = ?, `needfix` = ? WHERE `id` = ?',
            'rejected', $note, $existing[0]['id']
        );
    }

    $db->query(
        'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
        'rejected', $invoiceId
    );

    echo json_encode(['success' => true, 'message' => 'Rejected เรียบร้อย']);
}
?>
