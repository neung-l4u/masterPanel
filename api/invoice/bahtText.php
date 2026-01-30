<?php
header('Content-Type: application/json; charset=utf-8');

// รับค่าจาก GET parameter
$amount = isset($_GET['amount']) ? $_GET['amount'] : 0;

// แปลงค่าเป็นตัวเลข (ลบ comma ออก)
$amount = str_replace(',', '', $amount);
$amount = floatval($amount);

// Validate
if ($amount < 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Amount must be positive number"
    ]);
    exit;
}

// แปลงเป็นบาทไทย
$text_result = convertToBahtText($amount);

// ส่งค่ากลับเป็น JSON
echo json_encode([
    "status" => "success",
    "amount" => $amount,
    "thai_text" => $text_result
]);

/**
 * แปลงตัวเลขเป็นคำอ่านภาษาไทย (บาทถ้วน)
 */
function convertToBahtText($number) {
    if ($number == 0) {
        return "ศูนย์บาทถ้วน";
    }

    $txtNum = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $txtUnit = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

    // แยกบาทกับสตางค์
    $number = number_format($number, 2, '.', '');
    $parts = explode('.', $number);
    $baht = intval($parts[0]);
    $satang = intval($parts[1]);

    $result = '';

    // แปลงส่วนบาท
    if ($baht > 0) {
        $result .= readNumber($baht, $txtNum, $txtUnit) . 'บาท';
    }

    // แปลงส่วนสตางค์
    if ($satang > 0) {
        $result .= readNumber($satang, $txtNum, $txtUnit) . 'สตางค์';
    } else {
        $result .= 'ถ้วน';
    }

    return $result;
}

/**
 * อ่านตัวเลขเป็นคำไทย (รองรับหลักล้าน)
 */
function readNumber($number, $txtNum, $txtUnit) {
    $result = '';
    $number = intval($number);
    
    if ($number == 0) {
        return 'ศูนย์';
    }

    // จัดการหลักล้านขึ้นไป
    if ($number >= 1000000) {
        $millions = floor($number / 1000000);
        $result .= readNumber($millions, $txtNum, $txtUnit) . 'ล้าน';
        $number = $number % 1000000;
    }

    // แปลงส่วนที่เหลือ (ไม่เกิน 999,999)
    $numStr = strval($number);
    $len = strlen($numStr);

    for ($i = 0; $i < $len; $i++) {
        $digit = intval($numStr[$i]);
        $position = $len - $i - 1; // ตำแหน่งหลัก (0=หน่วย, 1=สิบ, ...)

        if ($digit == 0) {
            continue;
        }

        // กรณีพิเศษ: หลักหน่วยเป็น 1 และไม่ใช่เลขหลักเดียว
        if ($position == 0 && $digit == 1 && $len > 1) {
            $result .= 'เอ็ด';
        }
        // กรณีพิเศษ: หลักสิบเป็น 2
        elseif ($position == 1 && $digit == 2) {
            $result .= 'ยี่สิบ';
        }
        // กรณีพิเศษ: หลักสิบเป็น 1
        elseif ($position == 1 && $digit == 1) {
            $result .= 'สิบ';
        }
        // กรณีปกติ
        else {
            $result .= $txtNum[$digit] . $txtUnit[$position];
        }
    }

    return $result;
}
