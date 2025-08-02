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
$data['workDate'] = !empty($_POST['workDate']) ? $_POST['workDate'] : "-";
$data['checkinTime'] = !empty($_POST['checkinTime']) ? $_POST['checkinTime'] : "-";
$data['checkoutTime'] = !empty($_POST['checkoutTime']) ? $_POST['checkoutTime'] : "-";
$data['noteCheckin'] = !empty($_POST['noteCheckin']) ? $_POST['noteCheckin'] : "-";
$data['noteCheckout'] = !empty($_POST['noteCheckout']) ? $_POST['noteCheckout'] : "-";
$data['Department'] = !empty($_POST['Department']) ? $_POST['Department'] : "-";
$data['manager'] = !empty($_POST['manager']) ? $_POST['manager'] : "-";
$data['manager2'] = !empty($_POST['manager2']) ? $_POST['manager2'] : "-";
$data['activeSQL'] = !empty($_POST['activeSQL']) ? $_POST['activeSQL'] : "-";
$id = $_POST['staff'];




$status = "Noted";
$issueDate = date("j M", strtotime($data['workDate']));
/*$updateBy = "14";*/
$result["result"] = "";
$result["msg"] = "";


if ($data['actionType'] == 'Clock Out'){
    $logsToDB = $db->query(
        'SELECT * FROM `checkin` WHERE `createBy` = ? AND `checkOut` IS NULL ORDER BY `id` DESC LIMIT 1',
        $id
    )->fetchArray();

    $itemID = $logsToDB['id'];

    if ($logsToDB) {
        $employeeID = $id;

        // ทำการอัปเดต checkOut
        $updateCheckout = $db->query(
            'UPDATE `checkin` 
             SET `issueDate` = ?, 
                 `checkOut` = ?, 
                 `workShiftTimeLogging` = ?,
                 `noteCheckOut` = ?, 
                 `updateAt` = NOW(), 
                 `total` = SEC_TO_TIME(TIMESTAMPDIFF(SECOND, `createAt`, `updateAt`)), 
                 `updateBy` = ? 
             WHERE `id` = ?',
            $issueDate, $data['checkoutTime'], "Clock Out", $data['noteCheckout'], $employeeID, $itemID
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

        $return['total'] = $getData['total'];
        $return['item'] = $getData;
        $return['msg'] = 'Checked out successfully';
    }
}



$return['result'] = 'success';
$return['msg'] = 'Insert success';


echo json_encode($return);
?>