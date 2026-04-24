<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB2.php";

// สร้าง bookingId (token 32 hex ใช้ใน reschedule link)
$data['bookingId'] = bin2hex(random_bytes(16));

// Collect form fields that match `formASAP` table schema
$data['fullName']       = !empty($_POST['fullName'])       ? trim($_POST['fullName'])       : null;
$data['restaurantName'] = !empty($_POST['restaurantName']) ? trim($_POST['restaurantName']) : null;
$data['country']        = !empty($_POST['country'])        ? trim($_POST['country'])        : null;
$data['email']          = !empty($_POST['email'])          ? trim($_POST['email'])          : null;
$data['note']           = !empty($_POST['note'])           ? trim($_POST['note'])           : null;
$data['bookingDate']    = !empty($_POST['bookingDate'])    ? trim($_POST['bookingDate'])    : null;
$data['bookingTime']    = !empty($_POST['bookingTime'])    ? trim($_POST['bookingTime'])    : null;

// Server-side range check — กันคนทะลุ client-side
$HOUR_MIN = '06:00';
$HOUR_MAX = '18:00';
if (!empty($data['bookingTime']) && preg_match('/^\d{2}:\d{2}$/', $data['bookingTime'])) {
    if ($data['bookingTime'] < $HOUR_MIN || $data['bookingTime'] > $HOUR_MAX) {
        http_response_code(400);
        echo json_encode(['result' => "Booking time must be between $HOUR_MIN and $HOUR_MAX (BKK)."]);
        exit;
    }
}

// คำนวณ endBookingTime = bookingTime + 1:30 ชม.
$data['endBookingTime'] = null;
if (!empty($data['bookingTime']) && preg_match('/^\d{2}:\d{2}$/', $data['bookingTime'])) {
    $endTs = strtotime($data['bookingTime']) + (90 * 60); // +1 ชม. 30 นาที
    $data['endBookingTime'] = date('H:i', $endTs);
}

// สร้าง dateThai รูปแบบ dd/mm/yyyy HH:MM:SS (ปี พ.ศ.)
date_default_timezone_set('Asia/Bangkok');
$data['dateThai'] = date("d/m/") . (intval(date("Y")) + 543) . date(" H:i:s");

// Combine product checkboxes (string "true" from frontend) into comma-separated list
$productMap = [
    'onlineOrderingSystem' => 'Online Ordering System',
    'pos'                  => 'POS',
    'deliveryIntegration'  => 'Delivery Integration',
    'onlinePayment'        => 'Online Payment',
];
$productsSelected = [];
foreach ($productMap as $key => $label) {
    if (!empty($_POST[$key]) && $_POST[$key] !== 'false') {
        $productsSelected[] = $label;
    }
}
$data['products'] = !empty($productsSelected) ? implode(', ', $productsSelected) : null;

$params['result']    = "Default Text";
$params['timestamp'] = date("Y-m-d H:i:s");

try {
    $db->query(
        'INSERT INTO `formASAP` (`bookingId`, `fullName`, `restaurantName`, `Country`, `Email`, `productNees`, `noteForm`, `bookingDate`, `bookingTime`, `endBookingTime`, `dateThai`, `status`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        $data['bookingId'],
        $data['fullName'],
        $data['restaurantName'],
        $data['country'],
        $data['email'],
        $data['products'],
        $data['note'],
        $data['bookingDate'],
        $data['bookingTime'],
        $data['endBookingTime'],
        $data['dateThai'],
        'active'
    );

    $params['result']    = "Save to Database Success";
    $params['bookingId'] = $data['bookingId'];
} catch (Exception $e) {
    http_response_code(500);
    $params['result'] = "DB Error: " . $e->getMessage();
}

echo json_encode($params);