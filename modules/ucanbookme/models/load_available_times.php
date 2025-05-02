<?php
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

header('Content-Type: application/json');

$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$date = $_GET['date'] ?? '';

if (!$staff_id || !$date) {
    echo json_encode(['status' => 'error', 'message' => 'Missing staff_id or date']);
    exit;
}

// หาวันในสัปดาห์
$dayOfWeek = date('l', strtotime($date));

// 1. ดึงเวลาทำงานของเซลคนนั้นในวันนั้น
/*$sql = "SELECT start_time, end_time FROM sale_staff_availability WHERE staff_id = ? AND day_of_week = ?";
$slots = $db->query($sql, [$staff_id, $dayOfWeek]);*/

$slots = $db->query('SELECT start_time, end_time FROM sale_staff_availability WHERE staff_id = ? AND day_of_week = ?',$staff_id,$dayOfWeek)->fetchAll();

if (count($slots) === 0) {
    echo json_encode(['status' => 'ok', 'data' => []]); // ไม่ได้เข้าเวรวันนี้
    exit;
}

// 2. ดึงเวลาที่จองไปแล้ว
/*$sql = "SELECT meeting_time FROM sale_appointment WHERE staff_id = ? AND meeting_date = ? AND deleted_at IS NULL";
$used = $db->query($sql, [$staff_id, $date]);*/


//$used_times = array_column($used, 'meeting_time');

$used_times = $db->query('SELECT meeting_time FROM sale_appointment WHERE staff_id = ? AND meeting_date = ?',$staff_id,$date)->fetchAll();

// 3. สร้างช่วงเวลา 15 นาที แล้วกรองเฉพาะที่ยังว่าง
$available = [];

foreach ($slots as $slot) {
    $start = strtotime($slot['start_time']);
    $end = strtotime($slot['end_time']);

    while ($start + 900 <= $end) {
        $time = date('H:i:s', $start);
        if (!in_array($time, $used_times)) {
            $available[] = [
                'id' => $time,
                'text' => date('H:i', $start)
            ];
        }
        $start += 900; // +15 นาที
    }
}

echo json_encode(['status' => 'ok', 'data' => $available]);