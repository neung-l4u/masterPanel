<?php
session_start();
include 'assets/db/db.php';
include "assets/db/initDB.php";
$salt = "L4U";

$act = $_REQUEST["act"];

if($act=="login"){
    header("Location: ../../pages/main.php");
    die();
}else if($act=="logout"){
    session_destroy();
    setcookie("user", "", time() - 3600, '/');
    setcookie("pass", "", time() - 3600, '/');
    setcookie("remember", "", time() - 3600, '/');
    setcookie("token", "", time() - 3600, '/');
    header("Location: ../../../../index.php");
    die();
}else{
    session_destroy();
    header("Location: ../../../../index.php");
    die();
}