<?php
function showName($nick, $full){
    $tmp = explode(" ", $full);
    $getName = ($nick == $tmp[0]) ? $tmp[1] : $tmp[0];
    return $nick.' '.$getName;
}

function showDate($data){
    return date( "d/m/Y (H:i)", strtotime($data));
}

function sanitizeFolderName($name): string
{
    $name = trim($name);
    // Remove spaces and disallowed characters
    $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $name));
    return trim($sanitized);
}

function checkTime()
{
// ตรวจสอบว่าเวลาปัจจุบันเป็นก่อนเที่ยง (12:00) โดยไม่รวม 12:00 หรือหลังจากนั้น
    date_default_timezone_set("Asia/Bangkok");
    $hour = date("H");
    $minute = date("i");
    $second = date("s");

    if ($hour < 12) {
        echo true;  // เวลาก่อนเที่ยง
    } else {
        if ($hour == 12 && ($minute > 0 || $second > 0)) {
            echo false;  // เวลาหลัง 12:00 นาทีหรือวินาที
        } else {
            echo false;  // รวมถึงเวลา 12:00:00 น.
        }
    }
}//checkTime


function calculateDueDate($startDate): string
{
    $businessDays = 7; //fix ไว้ทีื 7 วันทำการ
    $currentDate = new DateTime($startDate);
    $includeToday = checkTime();
    $daysCounted = 0;

    if ($includeToday) {
        $currentDate->modify('+0 day'); // ถ้าสั่งงานก่อนเที่ยงจะ เริ่มนับวันจากวันนี้เลย
    } else {
        $currentDate->modify('+1 day'); // ถ้าสั่งงานหลังเที่ยง เริ่มนับจากวันถัดไป
    }

    while ($daysCounted < $businessDays) {
        $weekday = $currentDate->format('N');

        if ($weekday < 6) { //6 = เสาร์
            $daysCounted++;
        }

        if ($daysCounted < $businessDays) {
            $currentDate->modify('+1 day');
        }
    }

    $currentDate->modify('+1 day');

    // ตรวจสอบวันส่งงาน หากตรงกับเสาร์-อาทิตย์ ให้เลื่อนออกไป
    if ($currentDate->format('N') == 6) {
        $currentDate->modify('+2 day');
    }
    if ($currentDate->format('N') == 7) {
        $currentDate->modify('+1 day');
    }

    return $currentDate->format('Y-m-d');
}//calculateDueDate