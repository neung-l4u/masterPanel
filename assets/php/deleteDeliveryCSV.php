<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    $response['message'] = 'No filename provided';
    echo json_encode($response);
    exit;
}

$filename = basename($_POST['filename']);
$dataDir = __DIR__ . '/../data/';
$filePath = $dataDir . $filename;

if (!file_exists($filePath)) {
    $response['message'] = 'File not found';
    echo json_encode($response);
    exit;
}

$allFiles = array_filter(scandir($dataDir), function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'csv';
});

if (count($allFiles) <= 1) {
    $response['message'] = 'Cannot delete the last remaining file';
    echo json_encode($response);
    exit;
}

if (unlink($filePath)) {
    $response['success'] = true;
    $response['message'] = 'File deleted successfully';
} else {
    $response['message'] = 'Failed to delete file';
}

echo json_encode($response);
?>
