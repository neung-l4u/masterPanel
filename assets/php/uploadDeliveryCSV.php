<?php
// Clean start - no whitespace before this
ob_start();
session_start();

// Check authentication first
if (!isset($_SESSION['id'])) {
    ob_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

$response = ['success' => false, 'message' => ''];

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'No file uploaded or upload error occurred';
    die(json_encode($response));
}

$file = $_FILES['csvFile'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExt !== 'csv') {
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'Only CSV files are allowed';
    die(json_encode($response));
}

if ($fileSize > 10 * 1024 * 1024) {
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'File size exceeds 10MB limit';
    die(json_encode($response));
}

$handle = fopen($fileTmpName, 'r');
if (!$handle) {
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'Failed to read CSV file';
    die(json_encode($response));
}

$headers = fgetcsv($handle);
if (!$headers || count($headers) < 3) {
    fclose($handle);
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'Invalid CSV format. Expected at least 3 columns';
    die(json_encode($response));
}

$expectedHeaders = ['restaurant_name', 'date_of_last_order', 'total_orders_last_30days'];
$headersMatch = true;
for ($i = 0; $i < 3; $i++) {
    if (strtolower(trim($headers[$i])) !== $expectedHeaders[$i]) {
        $headersMatch = false;
        break;
    }
}

if (!$headersMatch) {
    fclose($handle);
    ob_clean();
    header('Content-Type: application/json');
    $response['message'] = 'Invalid CSV headers. Expected: restaurant_name, date_of_last_order, total_orders_last_30days';
    die(json_encode($response));
}

fclose($handle);

$uploadDir = __DIR__ . '/../data/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . date('YmdHis') . '.csv';
$destination = $uploadDir . $newFileName;

if (move_uploaded_file($fileTmpName, $destination)) {
    $response['success'] = true;
    $response['message'] = 'File uploaded successfully';
    $response['filename'] = $newFileName;
} else {
    $response['message'] = 'Failed to save file';
}

// Clear buffer and send only JSON
ob_clean();
header('Content-Type: application/json');
die(json_encode($response));
