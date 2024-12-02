<?php
$servername = "85.187.128.54";
$username = "localfor_reports";
$password = "Localforyou2023!";
$dbname = "localfor_reports";

// $dbHost = '85.187.128.54';
// $dbUser = 'localfor_reports';
// $dbPass = 'Localforyou2023!';
// $dbName = 'localfor_reports';

$conn = new mysqli($servername, $username, $password , $dbname);


if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
?>