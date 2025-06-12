<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

//http://localhost/masterPanel/api/signup/getReport.php?date=1996/01/20

$getDateString = $_GET["date"];

$getDate = new DateTime($getDateString);
$Date = $getDate->format('Y-m-d');
echo $Date;

$dateIndex = $getDateString->

$startDate = new DateTime();
$endDate = new DateTime();


?>