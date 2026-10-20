<?php
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include '../../assets/db/initDBstripe.php';

// แท็บนี้เป็น event log ล้วน ๆ ไม่มีรหัสผ่าน (ย้ายไปแท็บ Account ซึ่งเป็นรายคน)
$data = array("data" => array());

// ตารางนี้อยู่ในฐานของระบบ payments ซึ่ง production อาจยังไม่ได้ migrate
// เช็คก่อนว่ามีจริงแล้วค่อย query กันไม่ให้หน้าตาย
$exists = $dbStripe->query("SHOW TABLES LIKE 'password_reset_logs'")->fetchAll();
if (empty($exists)) {
    echo json_encode($data);
    exit;
}

$result = $dbStripe->query(
    'SELECT l.`id`, l.`email`, l.`ip_address`, l.`user_agent`, l.`token_used`,
            l.`reset_completed_at`, l.`created_at`, u.`name`, u.`country`
     FROM `password_reset_logs` l
     LEFT JOIN `users` u ON u.`id` = l.`user_id`
     ORDER BY l.`created_at` DESC'
)->fetchAll();

foreach ($result as $row) {
    $status = $row['token_used']
        ? '<span class="badge badge-success">Reset Done</span>'
        : '<span class="badge badge-warning">Requested Only</span>';

    $completed = $row['reset_completed_at'] ?: '-';
    $country   = $row['country'] ? htmlspecialchars($row['country']) : '-';

    // user agent ยาวมาก ตัดให้พอเห็นแล้วกางเต็มด้วย title
    $ua = $row['user_agent'] ?? '';
    $uaShort = $ua === ''
        ? '-'
        : '<span title="' . htmlspecialchars($ua, ENT_QUOTES) . '">'
          . htmlspecialchars(mb_strimwidth($ua, 0, 45, '…')) . '</span>';

    $mail = htmlspecialchars($row['email'], ENT_QUOTES);
    $resendBtn = '<button class="btn btn-sm btn-outline-primary btn-resend" data-email="' . $mail . '">'
               . '<i class="bi bi-envelope-arrow-up"></i> Resend</button>';

    $data["data"][] = array(
        (int) $row['id'],
        $row['created_at'],
        htmlspecialchars($row['name'] ?? '-'),
        htmlspecialchars($row['email']),
        $country,
        htmlspecialchars($row['ip_address'] ?? '-'),
        $uaShort,
        $status,
        $completed,
        $resendBtn,
    );
}

echo json_encode($data);
