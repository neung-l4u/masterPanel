<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result["result"] = "";
$result["msg"] = "";

$customerDetailsLogs = !empty($_POST["payload"]) ? $_POST["payload"] : "no data";
$stripeID = !empty($_POST["stripeID"]) ? $_POST["stripeID"] : "";
$logsToDB =  $db->query('INSERT INTO `ai_araya`(`customerDetailsLogs`, `stripeID`) VALUES (?, ?)', [$customerDetailsLogs, $stripeID]);

$result["result"] = "success";
$result["msg"] = "Save to DB successfully!";

echo json_encode($result);
?>