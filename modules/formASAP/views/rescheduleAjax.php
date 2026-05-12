<?php
/**
 * rescheduleAjax.php
 * อัปเดต booking ด้วยเวลาใหม่ แล้วยิง webhook ไป Make scenario #2 (update event)
 */
global $db;
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB2.php';

// ====== CONFIG ======
// URL ของ Make webhook สำหรับ scenario "Reschedule/Update Event"
// ** ใส่ URL จริงของคุณเมื่อสร้าง scenario #2 เสร็จ **
const RESCHEDULE_WEBHOOK_URL = 'https://hook.us1.make.com/mh8q1fqk0nmhncsrkx9ag71g8nc1rpww';

// ====== Input ======
$bookingId   = trim($_POST['bookingId']   ?? '');
$bookingDate = trim($_POST['bookingDate'] ?? '');
$bookingTime = trim($_POST['bookingTime'] ?? '');

// ====== Validate ======
if (!preg_match('/^[a-f0-9]{32}$/i', $bookingId)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid bookingId']); exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid bookingDate']); exit;
}
if (!preg_match('/^\d{2}:\d{2}$/', $bookingTime)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid bookingTime']); exit;
}

$today = date('Y-m-d');
if ($bookingDate < $today) {
    echo json_encode(['ok' => false, 'error' => 'Cannot reschedule to past date']); exit;
}

// endBookingTime = bookingTime + 1:30
$endBookingTime = date('H:i', strtotime($bookingTime) + 90 * 60);

// ====== Load booking (ต้อง active และยังไม่เลย today) ======
$db->query(
    'SELECT id, bookingId, fullName, restaurantName, Country, Email, productNees, noteForm,
            bookingDate, bookingTime, endBookingTime, gcalEventId, hangoutLink, htmlLink, status
       FROM `formASAP` WHERE `bookingId` = ? LIMIT 1',
    $bookingId
);
$booking = $db->fetchArray();

if (!$booking) {
    echo json_encode(['ok' => false, 'error' => 'Booking not found']); exit;
}
if ($booking['status'] === 'cancelled') {
    echo json_encode(['ok' => false, 'error' => 'Booking already cancelled']); exit;
}
if ($booking['bookingDate'] < $today) {
    echo json_encode(['ok' => false, 'error' => 'Original booking already passed']); exit;
}
if (empty($booking['gcalEventId'])) {
    echo json_encode(['ok' => false, 'error' => 'Google Calendar event not found']); exit;
}

// ====== Update DB ======
try {
    $db->query(
        'UPDATE `formASAP`
            SET `bookingDate`    = ?,
                `bookingTime`    = ?,
                `endBookingTime` = ?,
                `status`         = ?
          WHERE `bookingId`      = ?
          LIMIT 1',
        $bookingDate,
        $bookingTime,
        $endBookingTime,
        'active', // ยังคง active อยู่ เพียงแค่เปลี่ยนเวลา
        $bookingId
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB error: ' . $e->getMessage()]); exit;
}

// ====== Fire webhook ไป Make (scenario update) ======
$payload = [
    'bookingId'          => $booking['bookingId'],
    'gcalEventId'        => $booking['gcalEventId'],
    'hangoutLink'        => $booking['hangoutLink'],
    'htmlLink'           => $booking['htmlLink'],
    'fullName'           => $booking['fullName'],
    'restaurantName'     => $booking['restaurantName'],
    'email'              => $booking['Email'],
    'country'            => $booking['Country'],
    'productsNeeded'     => $booking['productNees'],
    'note'               => $booking['noteForm'],
    // เวลาเดิม (สำหรับอ้างอิงใน email)
    'oldBookingDate'     => $booking['bookingDate'],
    'oldBookingTime'     => substr($booking['bookingTime'], 0, 5),
    'oldEndBookingTime'  => substr($booking['endBookingTime'], 0, 5),
    // เวลาใหม่
    'bookingDate'        => $bookingDate,
    'bookingTime'        => $bookingTime,
    'endBookingTime'     => $endBookingTime,
    'timeStamps'         => date('H:i D ,d M Y') . ' (BKK)',
];

$webhookOk    = false;
$webhookError = null;

if (RESCHEDULE_WEBHOOK_URL && strpos(RESCHEDULE_WEBHOOK_URL, 'YOUR_') === false) {
    $ch = curl_init(RESCHEDULE_WEBHOOK_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    $webhookOk    = ($http >= 200 && $http < 300);
    $webhookError = $err ?: null;
} else {
    $webhookError = 'RESCHEDULE_WEBHOOK_URL not configured';
}

echo json_encode([
    'ok'            => true,
    'bookingId'     => $bookingId,
    'newDate'       => $bookingDate,
    'newTime'       => $bookingTime,
    'newEnd'        => $endBookingTime,
    'webhookOk'     => $webhookOk,
    'webhookError'  => $webhookError,
]);
