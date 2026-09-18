<?php
session_start();
header('Content-Type: application/json');

// ต้องล็อกอิน masterPanel ก่อนถึงสั่งส่งเมลได้
if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบใหม่']);
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

include '../../assets/db/initPaymentsAPI.php';

if ($paymentsApiSecret === '') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'ยังไม่ได้ตั้งค่า MASTERPANEL_RESET_SECRET บนเซิร์ฟเวอร์ ติดต่อทีม IT',
    ]);
    exit;
}

$staff = ($_SESSION['nickName'] ?? $_SESSION['name'] ?? 'staff') . ' #' . $_SESSION['id'];

$ch = curl_init($paymentsApiUrl . '/api/staff/resend-reset');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query(['email' => $email, 'staff' => $staff]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => [
        'X-MP-Secret: ' . $paymentsApiSecret,
        'Accept: application/json',
    ],
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($body === false) {
    echo json_encode(['status' => 'error', 'message' => 'ต่อระบบ payments ไม่ได้: ' . $err]);
    exit;
}

$res = json_decode($body, true);

// payments ตอบไม่ใช่ JSON (เช่นหน้า error ของ Apache)
if (!is_array($res)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'ระบบ payments ตอบกลับผิดรูปแบบ (HTTP ' . $code . ')',
    ]);
    exit;
}

// เป็น JSON แต่ไม่ใช่ 2xx เช่น Laravel ตอบ 404 มาเป็น {"message":""}
// ถ้าปล่อยผ่านจะกลายเป็นข้อความว่างเปล่า ผู้ใช้ไม่รู้ว่าเกิดอะไรขึ้น
if ($code < 200 || $code >= 300) {
    // ใช้ข้อความจากปลายทางเป็นหลัก เพราะมันรู้สาเหตุจริงดีกว่าเราเดา
    // เช่น 404 เป็นได้ทั้ง "ไม่พบอีเมลลูกค้า" และ "ยังไม่ได้ deploy route"
    // ซึ่งแยกกันที่ว่าปลายทางส่งข้อความมาด้วยหรือไม่ (Laravel 404 ส่ง message ว่าง)
    $detail = trim((string) ($res['message'] ?? ''));
    if ($detail === '') {
        if ($code === 404) {
            $detail = 'ไม่พบปลายทาง /api/staff/resend-reset บน payments (ยังไม่ได้ deploy?)';
        } elseif ($code === 401) {
            $detail = 'MASTERPANEL_RESET_SECRET สองฝั่งไม่ตรงกัน';
        } else {
            $detail = 'ไม่มีรายละเอียดจากปลายทาง';
        }
    }
    echo json_encode([
        'status'  => 'error',
        'message' => 'ส่งไม่สำเร็จ (HTTP ' . $code . '): ' . $detail,
    ]);
    exit;
}

echo json_encode($res);
