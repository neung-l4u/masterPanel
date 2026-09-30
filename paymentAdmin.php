<?php
// พาไปหน้า Payment Admin (payments) พร้อมลิงก์ที่เซ็นด้วย secret ร่วม ให้ payments เชื่อว่ามาจาก masterPanel จริง
// เซ็นตอนกด (ไม่เซ็นไว้ใน sidebar) เพื่อให้ลิงก์หมดอายุเร็วได้
session_start();
include 'assets/db/initPaymentsAPI.php';

if (empty($_SESSION['id']) || $_SESSION['level'] > 3 || $paymentsApiSecret === '') {
    http_response_code(403);
    die('Forbidden');
}

$userID = (int) $_SESSION['id'];
$exp = time() + 60;
$sig = hash_hmac('sha256', "$userID|$exp", $paymentsApiSecret);

header('Location: ' . $paymentsApiUrl . '/admin?' . http_build_query(['userID' => $userID, 'exp' => $exp, 'sig' => $sig]));
