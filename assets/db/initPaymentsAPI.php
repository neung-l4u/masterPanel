<?php
// ค่าสำหรับเรียก API ของระบบ payments (Laravel) จากหน้า Payment Password

$paymentsApiUrl = 'https://payments.localforyou.com';

// $paymentsApiUrl = 'http://localhost:8000';   // ตอนเทสต์กับ payments ในเครื่อง

// secret อ่านจาก environment ไม่ hardcode ไว้ในโค้ด
// ตั้งค่าใน Apache/php-fpm ของ masterPanel ให้ตรงกับ MASTERPANEL_RESET_SECRET ใน payments/.env
$paymentsApiSecret = getenv('MASTERPANEL_RESET_SECRET') ?: '';
