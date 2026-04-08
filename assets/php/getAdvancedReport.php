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

$id = !empty($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid report ID']);
    exit;
}

try {
    $row = $db->query(
        'SELECT r.*, s.sNickName AS nick, s.sName AS name, s.sPic AS pic, t.name AS team
         FROM monday_advanced_reports r
         LEFT JOIN staffs s ON r.staffID = s.sID
         LEFT JOIN Team t ON s.teamID = t.id
         WHERE r.id = ?', $id
    )->fetchArray();

    if (empty($row)) {
        echo json_encode(['status' => 'error', 'message' => 'Report not found']);
        exit;
    }

    $displayName = !empty($row['nick']) ? $row['nick'] : explode(' ', $row['name'])[0];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id'                  => $row['id'],
            'reporter'            => $displayName,
            'team'                => $row['team'] ?? '',
            'pic'                 => $row['pic'] ?? '',
            'board'               => $row['board'],
            'subject'             => $row['subject'],
            'detail'              => $row['detail'] ?? '',
            'attachment'          => $row['attachment'] ?? '',
            'screenshot_internet' => $row['screenshot_internet'] ?? '',
            'screenshot_computer' => $row['screenshot_computer'] ?? '',
            'reportStatus'        => $row['status'],
            'createdAt'           => date('d/m/Y H:i', strtotime($row['createdAt']))
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
