<?php
function firstOnly($string): string
{
    $temp = explode(" ", $string);
    return $temp[0];
}//firstOnly

function formatDate($date): string
{
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    $temp = explode("-", $date);
    return $dateTime->format('D').' '.$temp[2]."/".$temp[1]."/".$temp[0];
}//formatDate

function showName($nick, $fullName): string{
    $temp = explode(" ", $fullName);
    return $nick.'.'.$temp[0];
}//showName

function timeAgoInDays($startDate): string
{
    $start = new DateTime($startDate);
    $now = new DateTime(); // วันปัจจุบัน
    $diff = $start->diff($now)->days; // คำนวณจำนวนวัน

    return $diff . " days ago.";
}//timeAgoInDays