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

$db->query("DELETE FROM thCustomer WHERE id = ?", $id);

echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
?>
