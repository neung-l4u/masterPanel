<?php
global $db;
session_start();
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";


$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$id = $_POST['staff'];

//////////////////

$dateToMake['dayCheckIn'] = !empty($_POST['dayCheckIn']) ? $_POST['dayCheckIn'] : "-";
$dateToMake['dayCheckOut'] = !empty($_POST['dayCheckOut']) ? $_POST['dayCheckOut'] : "-";
$dateToMake['checkIn'] = !empty($_POST['checkIn']) ? $_POST['checkIn'] : "-";
$dateToMake['checkOut'] = !empty($_POST['checkOut']) ? $_POST['checkOut'] : "-";
$dateToMake['id'] = !empty($_POST['id']) ? $_POST['id'] : "-";
$dateToMake['noteCheckOut'] = !empty($_POST['noteCheckOut']) ? $_POST['noteCheckOut'] : "-";
$dateToMake['createBy'] = !empty($_POST['createBy']) ? $_POST['createBy'] : "-";



//////////////


$issueDate = date("j M", strtotime($dateToMake['dayCheckOut']));

/*$updateBy = "14";*/
$result["result"] = "";
$result["msg"] = "";


    $checkinDate  = $dateToMake['dayCheckIn'] . " " . $dateToMake['checkIn'] . ":00";
    $checkoutDate = $dateToMake['dayCheckOut'] . " " . $dateToMake['checkOut'] . ":00";

    $checkinTimestamp  = strtotime($checkinDate);
    $checkoutTimestamp = strtotime($checkoutDate);
    $totalSeconds =  $checkoutTimestamp - $checkinTimestamp;
    $totalFormatted = gmdate("H:i:s", $totalSeconds);



    $itemID = $dateToMake['id'];


     $employeeID = $dateToMake['createBy'];

     // ทำการอัปเดต checkOut
     $updateCheckout = $db->query(
         'UPDATE `checkin` 
         SET `issueDate` = ?, 
          `checkOut` = ?, 
          `workShiftTimeLogging` = ?,
          `dayCheckOut` = ?,
         `total` = ?,
         `noteCheckOut` = ?, 
         `updateAt` = NOW(), 
         `updateBy` = ? 
         WHERE `id` = ?',
         $issueDate, $dateToMake['checkOut'], "Clock Out",$dateToMake['dayCheckOut'],$totalFormatted,$dateToMake['noteCheckOut'], $employeeID, $itemID
     );

     $getData = $db->query(
         'SELECT * 
             FROM `checkin` 
             WHERE `id` = ? 
             ',
         $itemID
     )->fetchArray();


     $sendUpdate = $db->query(
         'UPDATE `checkin` 
          SET `send` = ?
          WHERE `id` = ?'
         ,"1",$itemID
     );



$return['item'] = $getData;

$return['result'] = 'success';
$return['msg'] = 'Insert success';


echo json_encode($return);