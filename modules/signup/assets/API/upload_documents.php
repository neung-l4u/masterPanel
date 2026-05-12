<?php
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required POST fields
$country  = $_POST['country'] ?? '';
$shopName = $_POST['shopName'] ?? '';

if (empty($country) || empty($shopName)) {
    echo json_encode(['success' => false, 'message' => 'Missing country or shopName']);
    exit;
}

// Build folder name: YYMMDD-country-shopName
$datePart   = date('ymd');
$safeName   = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($shopName));
$folderName = $datePart . '-' . strtoupper(trim($country)) . '-' . $safeName;

$uploadBase = __DIR__ . '/../uploads/';
$uploadDir  = $uploadBase . $folderName . '/';

// Create directories if they don't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// File field => prefix mapping
$fileMap = [
    'businessRegistrationDoc' => 'BRD_',
    'bankStatementDoc'        => 'BS_',
    'directorIdDoc'           => 'DirectorID_'
];

$uploaded = [];
$errors   = [];

foreach ($fileMap as $fieldName => $prefix) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "No file uploaded for {$fieldName}";
        continue;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error for {$fieldName}: code {$file['error']}";
        continue;
    }

    // Validate file type (images + PDF only)
    $allowedMime = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
        'application/pdf'
    ];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMime, true)) {
        $errors[] = "Invalid file type for {$fieldName}: {$mimeType}";
        continue;
    }

    // Max 10 MB per file
    if ($file['size'] > 10 * 1024 * 1024) {
        $errors[] = "File too large for {$fieldName} (max 10MB)";
        continue;
    }

    $originalName = basename($file['name']);
    $newFileName  = $prefix . $originalName;
    $destination  = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $uploaded[$fieldName] = $folderName . '/' . $newFileName;
    } else {
        $errors[] = "Failed to move uploaded file for {$fieldName}";
    }
}

echo json_encode([
    'success'  => empty($errors),
    'folder'   => $folderName,
    'uploaded' => $uploaded,
    'errors'   => $errors
]);
