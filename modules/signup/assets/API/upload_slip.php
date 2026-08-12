<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
set_exception_handler(function($e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit;
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['success' => false, 'message' => $errstr, 'file' => $errfile, 'line' => $errline]);
    exit;
});
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$shopName   = $_POST['shopName']    ?? '';
$country    = $_POST['country']     ?? '';
$quotationID = $_POST['quotationID'] ?? '';

if (empty($shopName) || empty($country) || empty($quotationID)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!isset($_FILES['slip']) || $_FILES['slip']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'No slip file uploaded']);
    exit;
}

$file = $_FILES['slip'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit;
}

// Validate mime type
$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedMime, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type: ' . $mimeType]);
    exit;
}

// Max 10 MB
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 10MB)']);
    exit;
}

// Build folder: shopName-country
// Allow alphanumeric (including Thai), underscore, and hyphen
$safeName   = preg_replace('/[^\p{L}\p{N}_\-]/u', '_', trim($shopName));
$safeName   = preg_replace('/_+/', '_', $safeName); // Replace multiple underscores with single
$safeName   = trim($safeName, '_'); // Remove leading/trailing underscores
$safeCountry = strtoupper(trim($country));
$folderName = $safeName . '-' . $safeCountry;

$uploadBase = __DIR__ . '/../uploads/slip/';
$uploadDir  = $uploadBase . $folderName . '/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$originalName = basename($file['name']);
$newFileName  = 'slip_' . time() . '_' . $originalName;
$destination  = $uploadDir . $newFileName;
$slipPath     = 'slip/' . $folderName . '/' . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Save slip to thReceipt
include __DIR__ . '/../db/db.php';
include __DIR__ . '/../db/initDB.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/invoice/convertToBahtText.php';

$invoiceId = (int)$quotationID;

// Get invoiceID to use as receiptID
$invRows   = $db->query('SELECT `invoiceID`, `amount` FROM `thInvoice` WHERE `id` = ? LIMIT 1', $invoiceId)->fetchAll();
$invoiceID = $invRows[0]['invoiceID'] ?? null;
$amount    = $invRows[0]['amount']    ?? 0;

// Check if receipt row exists for this invoice
$existing = $db->query(
    'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoiceId
)->fetchAll();

$thBathRe  = convertToBahtText((float)$amount);
$receiptID = $invoiceID ?: ('INV-' . $invoiceId);

if (!empty($existing[0])) {
    $db->query('UPDATE `thReceipt` SET `slip` = ?, `thBathRe` = ? WHERE `id` = ?', $slipPath, $thBathRe, $existing[0]['id']);
} elseif ($invoiceId > 0) {
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `slip`, `status`) VALUES (?,?,?,?,?,?)',
        $invoiceId, $receiptID, $amount, $thBathRe, $slipPath, 'pending'
    );
}

echo json_encode([
    'success'  => true,
    'slipPath' => $slipPath,
    'message'  => 'Slip uploaded and saved successfully'
]);
