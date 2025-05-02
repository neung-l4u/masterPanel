<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
$result['status'] = 'expired';
if (isset($_COOKIE['id'])){
    $_SESSION['id'] = $_COOKIE['id'];
    $myID = $_COOKIE['id'];
}


if (isset($_COOKIE['id'])) {
    $_SESSION['login'] = time(); // อัปเดตเวลาล่าสุดที่ session นี้มีการใช้งาน
    $result['status'] = 'active';
} else {
    $result['status'] = 'expired';
}

echo json_encode($result);