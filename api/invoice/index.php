<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

$invoiceID = $_GET['invoiceID'] ?? null;
if (!$invoiceID) {
    echo json_encode([
        "status" => [
            "code" => 400, 
            "message" => "Bad Request"
        ], 
        "data" => null]
    );
    exit;
}

$row = $db->query('SELECT * FROM invoice WHERE invoiceID = ?', $invoiceID)->fetchArray();
if (!$row) {
    echo json_encode([
        "status" => [
            "code" => 404, 
            "message" => "Not found"
        ], 
        "data" => null]
    );
    exit;
}

$row['createdAt'] = date('Y-m-d H:i:s');
echo json_encode(["data" => $row], JSON_UNESCAPED_UNICODE);