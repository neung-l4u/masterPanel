<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'DB_Localforyou';


$db = new db($dbHost, $dbUser, $dbPass, $dbName);