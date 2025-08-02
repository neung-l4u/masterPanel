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
$data['actionType'] = "Clock In";
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


if ($data['actionType'] == 'Clock In'){
    $logsToDB = $db->query('INSERT INTO `checkin` (`employee`, `status`, `department`, `workShiftTimeLogging`, `issueDate`, `checkIn`, `noteCheckIn`, `createBy`) VALUES (?, ?, ? ,? ,? ,? ,? ,?)' ,$data['staffName'],$status,$data['Department'], $data['actionType'], $issueDate,$data['checkinTime'],$data['noteCheckin'],$id );
}



$return['result'] = 'success';
$return['msg'] = 'Insert success';


echo json_encode($return);
?>