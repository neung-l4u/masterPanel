<?php
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include '../../assets/db/initDBstripe.php';

// รหัสผ่านเปิดดูได้เฉพาะทีมที่กำหนดไว้ใน paymentPasswordAccess.php คนอื่นเห็นเป็นขีด
include_once '../../assets/php/paymentPasswordAccess.php';
$canViewPw = canViewCustomerPassword();

$data = array("data" => array());

// ลูกค้าที่เคยรีเซ็ตรหัสสำเร็จ = users.key เป็นของเก่าที่ใช้ไม่ได้แล้ว
// (ResetPassword.php แก้แค่คอลัมน์ password ไม่ได้แตะ key)
$resetEmails = array();
$hasLogTable = $dbStripe->query("SHOW TABLES LIKE 'password_reset_logs'")->fetchAll();
if (!empty($hasLogTable)) {
    $rows = $dbStripe->query(
        'SELECT DISTINCT `email` FROM `password_reset_logs` WHERE `token_used` = 1'
    )->fetchAll();
    foreach ($rows as $r) {
        $resetEmails[strtolower($r['email'])] = true;
    }
}

$result = $dbStripe->query(
    'SELECT `id`, `name`, `email`, `country`, `customerID`, `created_at`
     FROM `users`
     ORDER BY `created_at` DESC'
)->fetchAll();

foreach ($result as $row) {
    $mail    = htmlspecialchars($row['email'], ENT_QUOTES);
    $country = $row['country'] ? htmlspecialchars($row['country']) : '-';
    $changed = isset($resetEmails[strtolower($row['email'])]);

    // ค่ารหัสไม่ถูกส่งมากับ JSON ต้องกดแล้วยิงขอทีละคน (revealPassword.php)
    if (!$canViewPw) {
        $pwCell = '<span class="text-muted">—</span>';
    } elseif ($changed) {
        $pwCell = '<span class="text-muted" title="ลูกค้าตั้งรหัสใหม่เองแล้ว รหัสที่ระบบเก็บไว้ใช้ไม่ได้">'
                . 'เปลี่ยนแล้ว</span>';
    } else {
        $pwCell = '<span class="pw-slot" data-email="' . $mail . '">'
                . '<button class="btn btn-sm btn-outline-secondary btn-reveal">'
                . '<i class="bi bi-eye"></i> ดูรหัส</button></span>';
    }

    $resendBtn = '<button class="btn btn-sm btn-outline-primary btn-resend" data-email="' . $mail . '">'
               . '<i class="bi bi-envelope-arrow-up"></i> Resend</button>';

    $data["data"][] = array(
        htmlspecialchars($row['name'] ?: '-'),
        htmlspecialchars($row['email']),
        $country,
        htmlspecialchars($row['customerID'] ?: '-'),
        $row['created_at'] ?: '-',
        $pwCell,
        $resendBtn,
    );
}

echo json_encode($data);
