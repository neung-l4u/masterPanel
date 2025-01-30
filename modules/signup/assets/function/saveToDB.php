<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$fileName = 'Logs#'.$date.'.txt';
$filePath = '../../logs/'.$fileName;

$result["result"] = "";
$result["msg"] = "";

$json = $_REQUEST["payload"];
$country = $_REQUEST["country"];

$status = $result["result"] == "success"? 1 : 2;
$dbJson = json_encode($json);
$_SESSION['id'] = "60";

$logsToDB =  $db->query('INSERT INTO `logssignup`(`data`, `countryCode`, `status`, `createAt`, `createBy`) VALUES (?,?,?,?,?)'
    , $dbJson, $country, $status, $timestamp, $_SESSION['id'] );

$result["result"] = "success";
$result["msg"] = "Save to DB success!";

echo json_encode($result);