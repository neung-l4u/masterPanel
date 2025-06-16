<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$getDateString = $_GET["date"];

$obj_getDate = new DateTime($getDateString);
$dateIndex = $obj_getDate->format('w');

$endDate = 6-$dateIndex;

$obj_startDate = new DateTime($getDateString);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d 00:00:00');

$obj_endDate = new DateTime($getDateString);
$endDate = $obj_endDate->modify("+$endDate days")->format('Y-m-d 23:59:59');

// echo "Date : ".$getDateString."<br>";
// echo "Index : ".$dateIndex."<br>";
// echo "Start date : ".$startDate."<br>";
// echo "End date : ".$endDate;

?>