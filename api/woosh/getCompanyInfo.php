<?php
header('Content-Type: application/json');

$base = 'https://' . ($_GET['domain'] ?? 'staging.core.wooshfood.com');
$apiUrl = $base . '/api/get_company_info';

$requestBody = [
    "" => ""
];

$ch = curl_init($apiUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($requestBody)
]);

$response = curl_exec($ch);

if ($response === false) {
    die("Curl Error: " . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['result'])) {
    die('No company infomation found');
}

echo $response;