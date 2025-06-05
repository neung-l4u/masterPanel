<?php
global $db;
session_start();
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDBlog.php";


$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$data['staffName'] = !empty($_POST['staffName']) ? $_POST['staffName'] : "-";
$data['actionType'] = !empty($_POST['actionType']) ? $_POST['actionType'] : "-";
$data['workDate'] = !empty($_POST['workDate']) ? $_POST['workDate'] : "-";
$data['checkinTime'] = !empty($_POST['checkinTime']) ? $_POST['checkinTime'] : "-";
$data['checkoutTime'] = !empty($_POST['checkoutTime']) ? $_POST['checkoutTime'] : "-";
$data['noteCheckin'] = !empty($_POST['noteCheckin']) ? $_POST['noteCheckin'] : "-";
$data['noteCheckout'] = !empty($_POST['noteCheckout']) ? $_POST['noteCheckout'] : "-";
$data['activeSQL'] = !empty($_POST['activeSQL']) ? $_POST['activeSQL'] : "-";

$json = json_encode($data);

$result["result"] = "";
$result["msg"] = "";


$logsToDB = $db->query('INSERT INTO `loginPastTime` (`data`, `staffName`, `type`) VALUES (?, ?, ?)',$json ,$data['staffName'], $data['actionType'] );

$return['result'] = 'success';
$return['msg'] = 'Insert success';
// $return['shopName'] = $param['shopName'];

echo json_encode($return);















