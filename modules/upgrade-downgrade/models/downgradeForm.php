<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result["result"] = "";
$result["msg"]    = "";

$dataLogs = !empty($_POST["payload"]) ? $_POST["payload"] : "no data";

$db->query('INSERT INTO `feedback`(`dataLogs`) VALUES (?)', [$dataLogs]);

$result["result"] = "success";
$result["msg"]    = "Save to DB successfully!";

echo json_encode($result);
?>
