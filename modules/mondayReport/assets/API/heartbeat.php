<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
$result['status'] = 'expired';

if (isset($_SESSION['login']) && is_numeric($_SESSION['login']) && (time() - $_SESSION['login'] < 1800)) {
    $_SESSION['login'] = time(); // อัปเดตเวลาล่าสุดที่ session นี้มีการใช้งาน
    $result['status'] = 'active';
} else {
    $result['status'] = 'expired';
}

echo json_encode($result);