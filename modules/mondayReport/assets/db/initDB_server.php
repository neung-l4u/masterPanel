<?php

use templates\assets\db\db;

date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = '85.187.128.54';
$dbUser = 'localfor_reports';
$dbPass = 'Localforyou2023!';
$dbName = 'localfor_reports';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);
