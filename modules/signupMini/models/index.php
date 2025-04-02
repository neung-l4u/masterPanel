<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

////* IMPORTANT!!!: This file called by AJAX from signupMini and signupTiny(https://localforyou.com/instant-online-ordering-demothai/) *////

$result["result"] = "";
$result["msg"] = "";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$dataLogs = !empty($_REQUEST["payload"]) ? $_REQUEST["payload"] : null;
$countryCode = !empty($dataLogs["countryCode"]) ? $dataLogs["countryCode"] : "-";
$formType = !empty($dataLogs["formType"]) ? $dataLogs["formType"] : "-";

$dataLogs = json_encode($dataLogs);
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

$logsToDB =  $db->query('INSERT INTO `signupMiniLogs`(`dataLogs`, `countryCode`, `formType`, `createAt`, `createBy`) VALUES (?,?,?,?,?)'
    , $dataLogs, $countryCode, $formType, $timestamp, $signupBy );

$result["result"] = "success";

$result["msg"] = "Save to DB successfully!";

echo json_encode($result);
?>