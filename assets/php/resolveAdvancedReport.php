<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

global $db;
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$id = !empty($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid report ID']);
    exit;
}

try {
    $db->query('UPDATE `monday_advanced_reports` SET `status` = 0 WHERE `id` = ?', $id);
    echo json_encode(['status' => 'success', 'message' => 'Report resolved']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
