<?php
/**
 * Server copy of the database connection, kept as a template.
 *
 * The real credentials are not committed. On the server, copy this to
 * initDB.php (which is what every page and model actually includes) and
 * fill in the values, or point it at the shared reports database.
 */
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

$dbHost = 'CHANGE_ME';
$dbUser = 'CHANGE_ME';
$dbPass = 'CHANGE_ME';
$dbName = 'CHANGE_ME';

$db = new db($dbHost, $dbUser, $dbPass, $dbName);
