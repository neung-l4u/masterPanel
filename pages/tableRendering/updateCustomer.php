<?php
session_start();
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
global $db;

header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$type = $_POST['type'] ?? '';
$address = $_POST['address'] ?? '';
$taxNumber = $_POST['taxNumber'] ?? '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name and Email are required']);
    exit;
}

try {
    $db->query(
        "UPDATE thCustomer SET name = ?, email = ?, phone = ?, type = ?, address = ?, taxNumber = ? WHERE id = ?",
        $name, $email, $phone, $type, $address, $taxNumber, $id
    );

    echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
