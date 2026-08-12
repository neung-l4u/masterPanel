<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$result = $db->query(
    "SELECT id, name, email, phone, type, address, taxNumber FROM thCustomer WHERE id = ? LIMIT 1",
    $id
);

$row = $result->fetch();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $row['id'],
        'name' => $row['name'] ?? '',
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
        'type' => $row['type'] ?? '',
        'address' => $row['address'] ?? '',
        'taxNumber' => $row['taxNumber'] ?? ''
    ]
]);
?>
