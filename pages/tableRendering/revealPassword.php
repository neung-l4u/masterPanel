<?php
session_start();
header('Content-Type: application/json');

// เปิดดูรหัสผ่านลูกค้าทีละคน เฉพาะทีม IT (teamID 5) เท่านั้น
// รหัสมาจาก users.key ซึ่งเก็บ plaintext ตอนสร้างบัญชี (Webhooks.php)
// ถ้าลูกค้าเคยรีเซ็ตรหัสแล้ว ค่าใน key จะเป็นของเก่าที่ใช้ไม่ได้ จึงไม่โชว์

if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบใหม่']);
    exit;
}

include_once '../../assets/php/paymentPasswordAccess.php';
if (!canViewCustomerPassword()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'ทีมของคุณไม่มีสิทธิ์ดูรหัสผ่านลูกค้า']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'อีเมลไม่ถูกต้อง']);
    exit;
}

include '../../assets/db/db.php';
// ต้องต่อ localfor_reports ก่อน เพราะ initDBstripe.php เขียนทับตัวแปร $dbHost/$dbUser/... ชุดเดียวกัน
include '../../assets/db/initDB.php';      // $db      -> localfor_reports (ตาราง log)
include '../../assets/db/initDBstripe.php'; // $dbStripe -> localfor_stripe  (ข้อมูลลูกค้า)

$rows = $dbStripe->query('SELECT `key` FROM `users` WHERE `email` = ? LIMIT 1', $email)->fetchAll();
if (empty($rows)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบลูกค้าอีเมลนี้']);
    exit;
}

$key = $rows[0]['key'] ?? '';
if ($key === '') {
    echo json_encode(['status' => 'error', 'message' => 'บัญชีนี้ไม่มีรหัสเก็บไว้ ใช้ปุ่ม Resend แทน']);
    exit;
}

// เคยรีเซ็ตสำเร็จ = users.key ไม่ใช่รหัสปัจจุบันอีกแล้ว (ResetPassword.php แก้แค่ password)
$reset = $dbStripe->query(
    'SELECT COUNT(*) AS n FROM `password_reset_logs` WHERE `email` = ? AND `token_used` = 1',
    $email
)->fetchAll();

if (!empty($reset) && (int) $reset[0]['n'] > 0) {
    echo json_encode([
        'status'  => 'stale',
        'message' => 'ลูกค้าเคยตั้งรหัสใหม่เองแล้ว รหัสที่ระบบเก็บไว้จึงใช้ไม่ได้ กรุณาใช้ปุ่ม Resend',
    ]);
    exit;
}

// บันทึกก่อนส่งค่ากลับ ให้ตรวจย้อนหลังได้ว่าใครดูรหัสของใคร
$staffName = $_SESSION['nickName'] ?? ($_SESSION['name'] ?? '');
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

$hasLog = $db->query("SHOW TABLES LIKE 'password_view_logs'")->fetchAll();
if (!empty($hasLog)) {
    $db->query(
        'INSERT INTO `password_view_logs` (`staffID`, `staffName`, `customerEmail`, `ipAddress`)
         VALUES (?, ?, ?, ?)',
        (int) $_SESSION['id'],
        $staffName,
        $email,
        $ip
    );
}

echo json_encode(['status' => 'success', 'password' => $key]);
