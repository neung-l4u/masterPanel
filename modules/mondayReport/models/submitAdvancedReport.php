<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'sessionExp', 'message' => 'Session expired']);
    exit;
}

global $db;
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$staffID = $_SESSION['id'];
$board   = !empty($_POST['board'])   ? trim($_POST['board'])   : '';
$subject = !empty($_POST['subject']) ? trim($_POST['subject']) : '';
$detail  = !empty($_POST['detail'])  ? trim($_POST['detail'])  : '';

// Validate required fields
if (empty($board) || empty($subject)) {
    echo json_encode(['status' => 'error', 'message' => 'Board and Subject are required']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
$maxSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

function uploadFile($fileKey, $uploadDir, $maxSize, $allowedTypes) {
    if (empty($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // optional file
    }

    $file = $_FILES[$fileKey];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload error for ' . $fileKey . ': code ' . $file['error']];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => $fileKey . ' exceeds 5MB limit'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['error' => $fileKey . ': only image files are allowed'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $fileKey . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['error' => 'Failed to save ' . $fileKey];
    }

    return ['path' => 'modules/mondayReport/uploads/' . $filename];
}

// Process uploads
$attachmentResult   = uploadFile('attachment', $uploadDir, $maxSize, $allowedTypes);
$internetResult     = uploadFile('screenshot_internet', $uploadDir, $maxSize, $allowedTypes);
$computerResult     = uploadFile('screenshot_computer', $uploadDir, $maxSize, $allowedTypes);

// Check for upload errors
foreach (['attachment' => $attachmentResult, 'screenshot_internet' => $internetResult, 'screenshot_computer' => $computerResult] as $key => $result) {
    if (is_array($result) && isset($result['error'])) {
        echo json_encode(['status' => 'error', 'message' => $result['error']]);
        exit;
    }
}

// Validate required screenshots
if (empty($internetResult) || !isset($internetResult['path'])) {
    echo json_encode(['status' => 'error', 'message' => 'Internet speed test screenshot is required']);
    exit;
}
if (empty($computerResult) || !isset($computerResult['path'])) {
    echo json_encode(['status' => 'error', 'message' => 'Computer info screenshot is required']);
    exit;
}

$attachmentPath  = is_array($attachmentResult) && isset($attachmentResult['path']) ? $attachmentResult['path'] : null;
$internetPath    = $internetResult['path'];
$computerPath    = $computerResult['path'];

try {
    $db->query(
        'INSERT INTO `monday_advanced_reports` (`staffID`, `board`, `subject`, `detail`, `attachment`, `screenshot_internet`, `screenshot_computer`) VALUES (?, ?, ?, ?, ?, ?, ?)',
        $staffID, $board, $subject, $detail, $attachmentPath, $internetPath, $computerPath
    );

    echo json_encode(['status' => 'success', 'message' => 'Report submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
