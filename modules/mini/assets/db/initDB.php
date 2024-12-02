<?php

use mini\assets\db\db;

date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = 'Atomix#5345@@';
$dbName = 'DB_Localforyou';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);
