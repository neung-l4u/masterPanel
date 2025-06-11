<?php
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

header('Content-Type: application/json');

// ดึงเฉพาะพนักงาน teamID = 3 และยังไม่ถูกลบ
$sql = "
    SELECT sID, sNickName, sName
    FROM staffs
    WHERE teamID = 3 AND staffs.sStatus = 1
    ORDER BY sNickName ASC
";

$rows = $db->query($sql)->fetchAll();
$data = [];

foreach ($rows as $row) {
    $data[] = [
        'id' => $row['sID'],
        'text' => $row['sNickName'] . ' - ' . $row['sName']
    ];
}

echo json_encode(['status' => 'ok', 'data' => $data]);