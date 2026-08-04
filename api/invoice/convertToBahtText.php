<?php
function convertToBahtText($number) {
    if ($number == 0) {
        return "ศูนย์บาทถ้วน";
    }

    $txtNum = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $txtUnit = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

    $number = number_format($number, 2, '.', '');
    $parts = explode('.', $number);
    $baht = intval($parts[0]);
    $satang = intval($parts[1]);

    $result = '';

    if ($baht > 0) {
        $result .= readNumberTH($baht, $txtNum, $txtUnit) . 'บาท';
    }

    if ($satang > 0) {
        $result .= readNumberTH($satang, $txtNum, $txtUnit) . 'สตางค์';
    } else {
        $result .= 'ถ้วน';
    }

    return $result;
}

function readNumberTH($number, $txtNum, $txtUnit) {
    $result = '';
    $number = intval($number);

    if ($number == 0) {
        return 'ศูนย์';
    }

    if ($number >= 1000000) {
        $millions = floor($number / 1000000);
        $result .= readNumberTH($millions, $txtNum, $txtUnit) . 'ล้าน';
        $number = $number % 1000000;
    }

    $numStr = strval($number);
    $len = strlen($numStr);

    for ($i = 0; $i < $len; $i++) {
        $digit = intval($numStr[$i]);
        $position = $len - $i - 1;

        if ($digit == 0) continue;

        if ($position == 0 && $digit == 1 && $len > 1) {
            $result .= 'เอ็ด';
        } elseif ($position == 1 && $digit == 2) {
            $result .= 'ยี่สิบ';
        } elseif ($position == 1 && $digit == 1) {
            $result .= 'สิบ';
        } else {
            $result .= $txtNum[$digit] . $txtUnit[$position];
        }
    }

    return $result;
}
