<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$result["result"] = "";
$result["msg"] = "";

$dataLogs = !empty($_POST["payload"]) ? $_POST["payload"] : null;
$dataStripe = !empty($_POST["stripePayload"]) ? $_POST["stripePayload"] : null;
$country = !empty($_POST["country"]) ? $_POST["country"] : null;
$contractURL = !empty($_POST["contractURL"]) ? $_POST["contractURL"] : null;
$testMode = !empty($_POST["testMail"]) ? $_POST["testMail"] : 0;

$dataLogs = json_encode($dataLogs);
$dataStripe = json_encode($dataStripe);
$status = 1;
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

$logsToDB =  $db->query('INSERT INTO `logssignup`(`dataLogs`, `dataStripe`, `dataContract`, `countryCode`, `status`, `test`, `createAt`, `createBy`) VALUES (?,?,?,?,?,?,?,?)'
    , $dataLogs, $dataStripe, $contractURL, $country, $status, $testMode, $timestamp, $signupBy );


$result["result"] = "success";

$result["msg"] = "Save to DB successfully!";

echo json_encode($result);