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