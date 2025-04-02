<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'db_localforyou';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);
