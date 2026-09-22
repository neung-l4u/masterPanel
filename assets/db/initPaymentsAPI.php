<?php
// ค่าสำหรับเรียก API ของระบบ payments (Laravel) จากหน้า Payment Password

$paymentsApiUrl = 'https://payments.localforyou.com';

// $paymentsApiUrl = 'http://localhost:8000';   // ตอนเทสต์กับ payments ในเครื่อง

// secret อ่านจาก environment ไม่ hardcode ไว้ในโค้ด
// ในเครื่อง: ตั้ง MASTERPANEL_RESET_SECRET ใน docker-compose.yml
// บน production (cPanel ไม่มี docker-compose): วางค่าไว้ในไฟล์ payments_secret.txt (gitignored)
// ค่าต้องตรงกับ MASTERPANEL_RESET_SECRET ใน payments/.env
$__secretFile = __DIR__ . '/payments_secret.txt';
$paymentsApiSecret = getenv('MASTERPANEL_RESET_SECRET')
    ?: (is_readable($__secretFile) ? trim(file_get_contents($__secretFile)) : '');
