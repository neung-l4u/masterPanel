<?php
/**
 * saveEventId.php
 * รับ callback จาก Make.com หลัง Google Calendar สร้าง event สำเร็จ
 * อัปเดต gcalEventId + hangoutLink + htmlLink ลงแถว booking ที่ตรงกับ bookingId
 *
 * Expected body (JSON or form): { bookingId, eventId, hangoutLink, htmlLink }
 * Optional security: ส่ง header X-Webhook-Token ให้ตรงกับ WEBHOOK_SECRET
 */

global $db;
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB2.php';

// ====== CONFIG ======
// ตั้งเป็นค่าเดียวกันใน Make HTTP module header (X-Webhook-Token)
// ถ้าไม่ต้องการ secret check ให้ตั้งเป็น "" (empty string)
const WEBHOOK_SECRET = '';

// ====== Parse input (รองรับทั้ง JSON body และ form POST) ======
$raw = file_get_contents('php://input');
$input = [];
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $input = $decoded;
}
if (empty($input) && !empty($_POST)) $input = $_POST;

$bookingId   = trim($input['bookingId']   ?? '');
$eventId     = trim($input['eventId']     ?? '');
$hangoutLink = trim($input['hangoutLink'] ?? '');
$htmlLink    = trim($input['htmlLink']    ?? '');

// ====== Security check ======
if (WEBHOOK_SECRET !== '') {
    $token = $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? '';
    if (!hash_equals(WEBHOOK_SECRET, $token)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

// ====== Validate ======
if (!preg_match('/^[a-f0-9]{32}$/i', $bookingId)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid bookingId']);
    exit;
}
if ($eventId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing eventId']);
    exit;
}

// ====== Update DB ======
try {
    $db->query(
        'UPDATE `formASAP`
            SET `gcalEventId` = ?,
                `hangoutLink` = ?,
                `htmlLink`    = ?
          WHERE `bookingId`   = ?
          LIMIT 1',
        $eventId,
        $hangoutLink ?: null,
        $htmlLink    ?: null,
        $bookingId
    );

    $affected = $db->affected_rows ?? null;

    echo json_encode([
        'ok'        => true,
        'bookingId' => $bookingId,
        'eventId'   => $eventId,
        'affected'  => $affected,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB Error: ' . $e->getMessage()]);
}
