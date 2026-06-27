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
require_once dirname(__DIR__, 2) . '/api/invoice/convertToBahtText.php';
ob_clean();
header('Content-Type: application/json');

$invoiceId   = isset($_POST['invoice_id'])  ? (int)$_POST['invoice_id']       : 0;
$submittedBy = isset($_POST['submittedBy']) ? trim($_POST['submittedBy'])       : ($_SESSION['name'] ?? 'Unknown');
$note        = isset($_POST['note'])        ? trim($_POST['note'])              : '';

if (!$invoiceId) {
    echo json_encode(['success' => false, 'message' => 'Invoice ID ไม่ถูกต้อง']);
    exit;
}

// Validate file
if (!isset($_FILES['slip']) || $_FILES['slip']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'กรุณาแนบไฟล์สลิป']);
    exit;
}

$file = $_FILES['slip'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit;
}

$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, $allowedMime, true)) {
    echo json_encode(['success' => false, 'message' => 'ไฟล์ต้องเป็น JPG, PNG หรือ PDF เท่านั้น']);
    exit;
}
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'ไฟล์ใหญ่เกิน 10MB']);
    exit;
}

// Get invoice + customer
$invRows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`amount`, i.`status`,
            c.`name` AS shopName
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE i.`id` = ? LIMIT 1',
    $invoiceId
)->fetchAll();

if (empty($invRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ Invoice']);
    exit;
}

$inv = $invRows[0];

if ($inv['status'] === 'sent') {
    echo json_encode(['success' => false, 'message' => 'Invoice นี้ส่ง Receipt แล้ว']);
    exit;
}

// Save file
$safeName  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($inv['shopName']));
$folderName = $safeName . '-TH';
$uploadBase = dirname(__DIR__, 2) . '/modules/signup/assets/uploads/slip/';
$uploadDir  = $uploadBase . $folderName . '/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = 'slip_' . time() . '_' . basename($file['name']);
$destination = $uploadDir . $newFileName;
$slipPath    = 'slip/' . $folderName . '/' . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'บันทึกไฟล์ไม่สำเร็จ']);
    exit;
}

$thBathRe  = convertToBahtText((float)$inv['amount']);
$receiptID = $inv['invoiceID'];

// Insert/update thReceipt
$existing = $db->query(
    'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoiceId
)->fetchAll();

if (!empty($existing[0])) {
    $db->query(
        'UPDATE `thReceipt` SET `slip` = ?, `thBathRe` = ?, `status` = ? WHERE `id` = ?',
        $slipPath, $thBathRe, 'pending', $existing[0]['id']
    );
} else {
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `slip`, `status`) VALUES (?,?,?,?,?,?)',
        $invoiceId, $receiptID, $inv['amount'], $thBathRe, $slipPath, 'pending'
    );
}

// Insert thSlipSubmission (tracking)
$db->query(
    'INSERT INTO `thSlipSubmission`(`invoice_id`, `submittedBy`, `slip`, `note`, `status`) VALUES (?,?,?,?,?)',
    $invoiceId, $submittedBy, $slipPath, $note, 'pending'
);

// --- Notify Billing team ผ่าน Make.com webhook ---
try {
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl   = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'report.localforyou.com');
    $slipUrl   = $baseUrl . '/modules/signup/assets/uploads/' . $slipPath;
    $billingUrl = $baseUrl . '/main.php?p=billingTH';

    $notifyPayload = [
        'event'        => 'slip_submitted',
        'invoice_id'   => $invoiceId,
        'invoiceID'    => $receiptID,
        'shopName'     => $inv['shopName'],
        'amount'       => $inv['amount'],
        'submittedBy'  => $submittedBy,
        'note'         => $note,
        'slip_url'     => $slipUrl,
        'billing_url'  => $billingUrl,
        'submittedAt'  => date('Y-m-d H:i:s'),
    ];

    $notifyWebhook = 'https://hook.us1.make.com/gnxfafua86k6mutxg8tr65cuxmrk3v2k';
    $ch = curl_init($notifyWebhook);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifyPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
} catch (\Throwable $e) {
    error_log('[submitSlipTH notify] ' . $e->getMessage());
}

echo json_encode([
    'success'   => true,
    'message'   => 'ส่งหลักฐานเรียบร้อย',
    'invoiceID' => $receiptID,
    'slipPath'  => $slipPath,
]);
?>
