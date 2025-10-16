<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

//$dbHost = '127.0.0.1';
$dbHost = 'localhost';
$dbUser = 'localfor_stripe';
$dbPass = 'ZU#iquS*k@FR';
$dbName = 'localfor_stripe';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);
