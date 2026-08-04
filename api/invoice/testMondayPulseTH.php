<?php
header('Content-Type: application/json; charset=utf-8');

$pulseId = trim($_POST['pulseId'] ?? $_GET['pulseId'] ?? '');
if (!preg_match('/^\d+$/', $pulseId)) {
    http_response_code(422);
    echo json_encode(['error' => 'pulseId must be a numeric Monday item ID']);
    exit;
}

$mondayToken = trim($_SERVER['HTTP_X_MONDAY_TOKEN'] ?? '') ?: (getenv('MONDAY_API_TOKEN') ?: '');
if ($mondayToken === '') {
    http_response_code(500);
    echo json_encode(['error' => 'MONDAY_API_TOKEN is not configured']);
    exit;
}

$query = 'query { items(ids: [' . $pulseId . ']) { id name board { id name } column_values { id text value column { title } } } }';

$ch = curl_init('https://api.monday.com/v2');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_POSTFIELDS => json_encode(['query' => $query]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: ' . $mondayToken,
        'API-Version: 2024-01',
    ],
]);
$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Monday API transport error', 'details' => $curlError]);
    exit;
}

http_response_code($httpCode ?: 200);
echo $response;
