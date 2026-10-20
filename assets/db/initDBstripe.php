<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

// ฐานข้อมูลของระบบ payments (Laravel) คนละตัวกับ localfor_reports
// ใช้อ่าน password_reset_logs สำหรับหน้า Payment Password

// สลับบล็อกล่างขึ้นมาใช้ตอนขึ้น production (เครื่อง dev ต่อ 85.187.128.54 ไม่ได้)
// $dbHost = 'db';
// $dbUser = 'root';
// $dbPass = 'root';
// $dbName = 'localfor_stripe';

$dbHost = '85.187.128.54';
$dbUser = 'localfor_stripe';
$dbPass = 'ZU#iquS*k@FR';
$dbName = 'localfor_stripe';

$dbStripe = new db($dbHost, $dbUser, $dbPass, $dbName);
