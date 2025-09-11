<?php

// ฟังก์ชันช่วยเช็คค่า incomplete
function isIncomplete($value) {
    return $value === "-" || $value === "null" || $value === null || $value === "";
}

// รับค่า raw JSON body
$input = file_get_contents('php://input');
$postData = json_decode($input, true);

// ถ้า json_decode ล้มเหลว ให้ใช้ $_POST แบบเดิม
if (!is_array($postData)) {
    $postData = $_POST;
}

// รับค่า POST หรือใช้ default
$data['token'] = !empty($postData['token']) ? $postData['token'] : "-";
$data['action']  = !empty($postData['action']) ? $postData['action'] : "add";
$data['shop_type'] = !empty($postData['shop_type']) ? $postData['shop_type'] : "-";
$data['country'] = !empty($postData['country']) ? $postData['country'] : "-";
$data['timezone'] = !empty($postData['timezone']) ? $postData['timezone'] : "Asia/Bangkok";
$data['city'] = !empty($postData['city']) ? $postData['city'] : "-";
$data['startDate'] = !empty($postData['startDate']) ? $postData['startDate'] : "-";
$data['startTime'] = !empty($postData['startTime']) ? $postData['startTime'] : "-";
$data['customer_name'] = !empty($postData['customer_name']) ? $postData['customer_name'] : "-";
$data['shop_name'] = !empty($postData['shop_name']) ? $postData['shop_name'] : "-";
$data['contact_email'] = !empty($postData['contact_email']) ? $postData['contact_email'] : "-";
$data['contact_phone'] = !empty($postData['contact_phone']) ? $postData['contact_phone'] : "-";
$data['contact_mobile'] = !empty($postData['contact_mobile']) ? $postData['contact_mobile'] : "-";
$data['presentation'] = !empty($postData['presentation']) ? $postData['presentation'] : "Thai";
$data['line_id'] = isset($postData['line_id']) ? $postData['line_id'] : "-";
$data['whatsapp'] = isset($postData['whatsapp']) ? $postData['whatsapp'] : "-";
$data['address'] = isset($postData['address']) ? $postData['address'] : "-";
$data['comment'] = isset($postData['comment']) ? $postData['comment'] : "-";

// สร้าง request_id
$request_id = "req_" . time();


// ตรวจสอบ timezone ว่าถูกต้องหรือไม่
try {
    $timezone = str_replace("\\", "", $data['timezone']); // ลบ backslash เผื่อมี
    $startDatetime = new DateTime($data['startDate'] . " " . $data['startTime'], new DateTimeZone($timezone));
} catch (Exception $e) {
    echo json_encode([
        "status" => [
            [
                "code" => "200",
                "message" => "Invalid timezone or date/time format.",
                "request_id" => $request_id
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// สร้าง DateTime ของ end (+30 นาที)
$endDatetime = clone $startDatetime;
$endDatetime->modify('+30 minutes');

// เก็บค่า endDate และ endTime ตาม timezone เดิม
$data['endDate'] = $endDatetime->format("Y-m-d");
$data['endTime'] = $endDatetime->format("H:i:s");

// แปลงเวลา start เป็นไทย
$startDatetime->setTimezone(new DateTimeZone("Asia/Bangkok"));
$data['daythaionly']  = $startDatetime->format("Y-m-d");
$data['timethaionly'] = $startDatetime->format("H:i:s");

// แปลงเวลา end เป็นไทย
$endDatetime->setTimezone(new DateTimeZone("Asia/Bangkok"));
$data['end_timethaionly'] = $endDatetime->format("H:i:s");

// สร้าง dtStamp และ dtStart/dtEnd
$startDateClean = str_replace('-', '', $data['startDate']);
$startTimeClean = str_replace(':', '', $data['startTime']);
$data['dtStamp'] = $startDateClean . 'T' . $startTimeClean;
$data['dtStart'] = $timezone.':'.$data['dtStamp'];

$endDateClean = str_replace('-', '', $data['endDate']);
$endTimeClean = str_replace(':', '', $data['endTime']);
$endDtStamp = $endDateClean . 'T' . $endTimeClean;
$data['dtEnd'] = $timezone.':'.$endDtStamp;

// ส่ง webhook ปกติ
$webhookUrl = "https://hook.us1.make.com/nudqpykkqubl337bijmb1w7qy3wr9rde";
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$result = curl_exec($ch);
curl_close($ch);

// Return success
echo json_encode([
    "status" => [
        [
            "code" => "200",
            "message" => "OK",
            "request_id" => $request_id
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
