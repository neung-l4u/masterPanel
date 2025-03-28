<?php
/*
----- function getRandomPic ------
parameters
    $shopType = restaurant, massage
    $picType = folder name in assets/img/sample_image/shopType/ e.g., bg, logo, other, review, service, food
    $fullPath = true, false (default false)

  example 1:
    $getPic = getRandomPic("restaurant", "logo");
    will return >> bg5.webp

  example 2:
    $getPic = getRandomPic("massage", "bg", true);
    will return >> ../assets/img/sample_image/massages/bg/bg5.webp
*/

function getRandomPic($shopType, $picType, $fullPath=false)
{
    $mainDir = "";
    if (strtolower($shopType)=="restaurant"){
        $mainDir = "restaurant";
    }else if(strtolower($shopType)=="massage"){
        $mainDir = "massages";
    }
    $path = '../assets/img/sample_image/'.$mainDir."/".$picType;


    $allFile = array();
    foreach (new DirectoryIterator($path) as $file) {
        if ($file->isFile()) {
            $allFile[] = $file->getFilename();
        }//if
    }//foreach

    $rand_keys = array_rand($allFile,1);

    if ($fullPath){
        return $path."/".$allFile[$rand_keys];
    }else{
        return($allFile[$rand_keys]);
    }
}//getRandomPic

/*
----- function getRandomPic ------
parameters
    $name = any shop name

  example 1:
    $shopName = "Hello My Pizza Boy's & Co.";
    $folderName = sanitizeFolderName($shopName);
    echo $folderName; // Outputs: Hello_My_Pizza_Boys_Co
*/
function sanitizeFolderName($name): string
{
    $name = trim($name);
    // Remove spaces and disallowed characters
    $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $name));
    return trim($sanitized);
}

/*
----- function getRandomPic ------
parameters
    $startDate = start date in format yyyy-mm-dd
    $businessDays = how many business day you want to add
    $holidays = [] //array of holiday string

  example 1:
    $startDate = '2025-01-05';
    $businessDaysToAdd = 7;
    $holidays = ['2025-01-13'];

    $result = addBusinessDays($startDate, $businessDaysToAdd, $holidays);
    echo "Due date = next 7 business day is ".$result;
    // Outputs: Due date = next 7 business day is 2025-01-21
*/
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

?>