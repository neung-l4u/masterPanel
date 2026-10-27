<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'db';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'localfor_reports';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);