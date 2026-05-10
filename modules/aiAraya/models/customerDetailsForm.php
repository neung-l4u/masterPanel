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
$orderRow = $db->query('SELECT startedAt FROM order_tracking WHERE stripeID = ? LIMIT 1', [$stripeID])->fetchArray();
$qualify = "No (over 24 hrs)";
if ($orderRow && isset($orderRow['startedAt'])) {
    $startedAt = new DateTime($orderRow['startedAt']);
    $now = new DateTime();
    $diffHours = ($now->getTimestamp() - $startedAt->getTimestamp()) / 3600;
    $qualify = $diffHours < 24 ? "Yes (done 24 hr)" : "No (over 24 hrs)";
}

$logsData = json_decode($customerDetailsLogs, true) ?? [];
$logsData['qualify'] = $qualify;
$customerDetailsLogs = json_encode($logsData);

$logsToDB = $db->query('INSERT INTO `ai_araya`(`customerDetailsLogs`, `stripeID`) VALUES (?, ?)', [$customerDetailsLogs, $stripeID]);

$updateStatus = $db->query('UPDATE order_tracking SET statusUser = "completed" , reminderStep = 1  WHERE stripeID = ?', [$stripeID]);

$result["result"] = "success";
$result["msg"] = "Save to DB successfully!";
$result["qualify"] = $qualify;

echo json_encode($result);
?>