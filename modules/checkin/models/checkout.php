<?php
global $db;
session_start();
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";


$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");


$data['staffName'] = !empty($_POST['staffName']) ? $_POST['staffName'] : "-";
$data['actionType'] = "Clock Out";
$data['workDateOut'] = !empty($_POST['workDateOut']) ? $_POST['workDateOut'] : "-";

$data['checkoutTime'] = !empty($_POST['checkoutTime']) ? $_POST['checkoutTime'] : "-";

$data['noteCheckout'] = !empty($_POST['noteCheckout']) ? $_POST['noteCheckout'] : "-";

$id = $_POST['staff'];

$status = "Noted";
$issueDate = date("j M", strtotime($data['workDateOut']));

/*$updateBy = "14";*/
$result["result"] = "";
$result["msg"] = "";


if ($data['actionType'] == 'Clock Out'){
    $logsToDB = $db->query(
        'SELECT * FROM `checkin` WHERE `createBy` = ? AND `checkOut` IS NULL ORDER BY `id` DESC LIMIT 1',
        $id
    )->fetchArray();


   /* if ($logsToDB) {
        $employeeID = $id;

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
            $issueDate, $data['checkoutTime'], "Clock Out",$data['workDateOut'],$totalFormatted,$data['noteCheckout'], $employeeID, $itemID
        );

        $getData = $db->query(
            'SELECT * 
                FROM `checkin` 
                WHERE `id` = ? 
                ORDER BY `id` DESC 
                LIMIT 1
                ',
            $itemID
        )->fetchArray();

        $sendUpdate = $db->query(
            'UPDATE `checkin` 
             SET `send` = ?
             WHERE `id` = ?'
            ,"1",$itemID
        );

}*/
        $return['item'] = $logsToDB;
        $return['msg'] = 'Checked out successfully';

}



$return['result'] = 'success';
$return['msg'] = 'Insert success';


echo json_encode($return);