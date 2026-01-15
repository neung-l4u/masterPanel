<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? null;
if (!$email) {
    echo json_encode([
        "status" => [
            "code" => 400, 
            "message" => "Bad Request - Email is required"
        ], 
        "data" => null
    ]);
    exit;
}

$rows = $db->query('SELECT * FROM invoice WHERE customerEmail = ?', $email)->fetchAll();
if (!$rows || count($rows) == 0) {
    echo json_encode([
        "status" => [
            "code" => 404, 
            "message" => "No invoices found for this email"
        ], 
        "data" => null
    ]);
    exit;
}

echo json_encode([
    "status" => [
        "code" => 200, 
        "message" => "Success"
    ], 
    "data" => $rows
]);
