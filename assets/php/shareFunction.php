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


function addBusinessDays($startDate, $businessDays, $holidays = []): string
{
    $currentDate = new DateTime($startDate);

    // Start counting from the next day
    $currentDate->modify('+1 day');

    $daysAdded = 0;

    while ($daysAdded < $businessDays) {
        $weekday = $currentDate->format('N'); // 1 (Monday) to 7 (Sunday)

        // Check if the day is not a weekend and not a holiday
        if ($weekday < 6 && !in_array($currentDate->format('Y-m-d'), $holidays)) {
            $daysAdded++;
        }

        $currentDate->modify('+1 day'); // Move to the next day
    }
    return $currentDate->format('Y-m-d');
}//addBusinessDays