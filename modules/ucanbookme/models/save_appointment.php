<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// รับค่าจากฟอร์ม
$staff_id = $_POST['sales'] ?? null;
$created_by = $_SESSION['id'] ?? null; // พนักงานที่ล็อกอินอยู่
$shop_type_id = $_POST['shop_type'] ?? null;
$country = $_POST['country'] ?? '';
$city = $_POST['city'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$customer_name = trim($_POST['customer_name'] ?? '');
$shop_name = trim($_POST['shop_name'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');
$contact_phone = trim($_POST['contact_phone'] ?? '');
$line_id = trim($_POST['line_id'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');

if (!$staff_id || !$date || !$time || !$customer_name || !$shop_name) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// คำนวณเวลาสิ้นสุด (15 นาทีถัดไป)
$end_time = date('H:i:s', strtotime($time) + 900); // +15 นาที

// เตรียมบันทึกลง DB
/*$sql = "INSERT INTO sale_appointment (staff_id, created_by_staff_id, customer_name, shop_name, contact_email, contact_phone, line_id, whatsapp, shop_type_id, country_code, city, timezone, meeting_date, meeting_time, meeting_end_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

$params = [
    $staff_id, $created_by, $customer_name, $shop_name, $contact_email, $contact_phone, $line_id, $whatsapp, $shop_type_id, $country, $city, null, $date, $time, $end_time
];

$result = $db->query($sql, $params);*/

$result = $db->query('INSERT INTO sale_appointment (staff_id, created_by_staff_id, customer_name, shop_name, contact_email, contact_phone, line_id, whatsapp, shop_type_id, country_code, city, timezone, meeting_date, meeting_time, meeting_end_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
    ,$staff_id, $created_by, $customer_name, $shop_name, $contact_email, $contact_phone, $line_id, $whatsapp, $shop_type_id, $country, $city, null, $date, $time, $end_time );

if ($result) {
    echo json_encode(['status' => 'ok', 'message' => 'จองคิวสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
}