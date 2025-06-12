<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$getDateString = $_GET["date"];

$getDate = new DateTime($getDateString);
$Date = $getDate->format('Y-m-d');

$dateIndex = $getDate->format('w');



echo $Date;
echo "<br>";
echo $dateIndex;

?>