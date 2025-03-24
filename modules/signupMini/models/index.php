<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result["result"] = "";
$result["msg"] = "";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$dataLogs = !empty($_REQUEST["payload"]) ? $_REQUEST["payload"] : null;
$countryCode = $dataLogs["countryCode"];

$dataLogs = json_encode($dataLogs);
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

$logsToDB =  $db->query('INSERT INTO `signupMiniLogs`(`dataLogs`, `countryCode`, `createAt`, `createBy`) VALUES (?,?,?,?)'
    , $dataLogs, $countryCode, $timestamp, $signupBy );

$result["result"] = "success";

$result["msg"] = "Save to DB successfully!";

echo json_encode($result);
?>