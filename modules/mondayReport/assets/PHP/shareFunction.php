<?php
function firstOnly($string): string
{
    $temp = explode(" ", $string);
    return $temp[0];
}

function formatDate($date): string
{
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    $temp = explode("-", $date);
    return $dateTime->format('D').' '.$temp[2]."/".$temp[1]."/".$temp[0];
}

function showName($nick, $fullName): string{
    $temp = explode(" ", $fullName);
    return $nick.'.'.$temp[0];
}//showName