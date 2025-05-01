<?php
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

header('Content-Type: application/json');

if (!isset($_GET['country_code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing country_code']);
    exit;
}

$param['country'] = strtoupper(trim($_GET['country_code']));

$sales = $db->query('SELECT s.sID, s.sNickName, s.sName FROM staffs s INNER JOIN sale_staff_country sc ON sc.staff_id = s.sID WHERE sc.country_code = ? AND s.teamID = 3 AND sc.active = 1 ORDER BY s.sNickName',$param['country'])->fetchAll();

$data = [];
foreach ($sales as $row) {
    $data[] = [
        'id' => $row['sID'],
        'text' => $row['sNickName'] . ' ' . strtok($row['sName'], ' ')
    ];
}

echo json_encode(['status' => 'ok', 'data' => $data]);