<?php

use cancellation\assets\db\db;

date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'db_localforyou';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);