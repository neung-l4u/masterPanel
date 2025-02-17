<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$result["result"] = "";
$result["msg"] = "";

$dataLogs = !empty($_REQUEST["payload"]) ? $_REQUEST["payload"] : null;
$dataStripe = !empty($_REQUEST["stripePayload"]) ? $_REQUEST["stripePayload"] : null;
$country = !empty($_REQUEST["country"]) ? $_REQUEST["country"] : null;

$status = $result["result"] == "success"? 2 : 1;
$dataLogs = json_encode($dataLogs);
$dataStripe = json_encode($dataStripe);
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

$logsToDB =  $db->query('INSERT INTO `logssignup`(`data`, `stripeData`, `countryCode`, `status`, `createAt`, `createBy`) VALUES (?,?,?,?,?,?)'
    , $dataLogs, $dataStripe, $country, $status, $timestamp, $_SESSION['id'] );

$result["result"] = "success";
$result["msg"] = "Save to DB successfully!";

echo json_encode($result);