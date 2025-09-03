<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$result["result"] = "";
$result["msg"] = "";

$act = !empty($_POST["act"]) ? $_POST["act"] : null;
$dataLogs = !empty($_POST["payload"]) ? $_POST["payload"] : null;
$dataStripe = !empty($_POST["stripePayload"]) ? $_POST["stripePayload"] : null;
$country = !empty($_POST["country"]) ? $_POST["country"] : null;
$contractURL = !empty($_POST["contractURL"]) ? $_POST["contractURL"] : null;
$stripeResult = !empty($_POST["stripeRes"]) ? $_POST["stripeRes"] : null;
$testMode = !empty($_POST["testMail"]) ? $_POST["testMail"] : 0;
$logID = !empty($_POST["logID"]) ? $_POST["logID"] : null;

$dataLogs = json_encode($dataLogs);
$dataStripe = json_encode($dataStripe);
$status = 1;
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

if (!is_null($stripeResult)) {
    $trimmed = trim($stripeResult);
    json_decode($trimmed);

    if (json_last_error() !== JSON_ERROR_NONE || $trimmed === "null") {
        $stripeResult = json_encode($stripeResult);
    }
}

if ($act === "add") {
    $logsToDB =  $db->query('INSERT INTO `logssignup`(`dataLogs`, `dataStripe`, `dataContract`, `countryCode`, `status`, `test`, `createAt`, `createBy`) VALUES (?,?,?,?,?,?,?,?)'
    , $dataLogs, $dataStripe, $contractURL, $country, $status, $testMode, $timestamp, $signupBy );
} elseif ($act === "update") {
    $resToDB = $db->query('UPDATE `logssignup` SET `stripeResult`=? WHERE id=?', $stripeResult, $logID);
}

$lastInsertId = $db->lastInsertId();

$result["result"] = "success";
$result["logID"] = $lastInsertId;
$result["msg"] = "Save to DB successfully!";

echo json_encode($result);