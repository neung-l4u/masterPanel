<?php
header('Content-Type: application/json');

$base = 'https://' . ($_GET['domain'] ?? 'staging.core.wooshfood.com');
$apiUrl = $base . '/api/get_website_products';

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$allProducts = [];
$page = 1;
$totalPages = 1;

do {
    $requestBody = ["page" => $page, "limit" => 500];
    if ($categoryId) {
        $requestBody["category_id"] = $categoryId;
    }

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($requestBody)
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        die(json_encode(["error" => "Curl Error: " . curl_error($ch)]));
    }

    curl_close($ch);

    $data = json_decode($response, true);
    $totalPages = $data['result']['total_pages'] ?? 1;
    $products = $data['result']['products'] ?? [];
    $allProducts = array_merge($allProducts, $products);

    $page++;
} while ($page <= $totalPages);

$data['result']['products'] = $allProducts;
$data['result']['page'] = 1;
$data['result']['total_pages'] = 1;
$data['result']['limit'] = count($allProducts);

echo json_encode($data);