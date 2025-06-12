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
$staff_id = !empty($_POST['sales']) ? $_POST['sales'] : '';
$created_by = $_SESSION['id']; // พนักงานที่ล็อกอินอยู่
$shop_type_id = !empty($_POST['shop_type']) ? $_POST['shop_type'] : '';
$country = !empty($_POST['country']) ? $_POST['country'] : '';
$city = !empty($_POST['city']) ? $_POST['city'] : '';
$date = !empty($_POST['date']) ? $_POST['date'] : '';
$time = !empty($_POST['time']) ? $_POST['time'] : '';
$customer_name = !empty($_POST['customer_name']) ? $_POST['customer_name'] : '';
$shop_name = !empty($_POST['shop_name']) ? $_POST['shop_name'] : '';
$contact_email =  !empty($_POST['contact_email']) ? $_POST['contact_email'] : '';
$contact_phone = !empty($_POST['contact_phone']) ? $_POST['contact_phone'] : '';
$line_id = !empty($_POST['line_id']) ? $_POST['line_id'] : '';
$whatsapp = !empty($_POST['whatsapp']) ? $_POST['whatsapp'] : '';

if (!$staff_id || !$date || !$time || !$customer_name || !$shop_name) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$staff_email = '';
$staff_email_map = [
    17 => 'boom@localforyou.com',
    18 => 'dear@localforyou.com',
    24 => 'honey@localforyou.com',
    35 => 'pluem@localforyou.com',
    38 => 'pruek@localforyou.com',
    47 => 'toffee@localforyou.com',
    62 => 'ball@localforyou.com',
    72 => 'lani@localforyou.com',
    76 => 'naya@localforyou.com',
    79 => 'gun@localforyou.com',
    84 => 'aon@localforyou.com',
];

$staff_email = $staff_email_map[$staff_id] ?? null; // ถ้าไม่มีใน map จะได้ null

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