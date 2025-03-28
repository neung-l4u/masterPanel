<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";
//
$param['id'] = (!empty($_REQUEST['id'])) ? trim($_REQUEST['id']) : ''; //id


$projects = $db->query('SELECT* FROM `staffs` WHERE sID = ? ;' ,$param['id'])->fetchAll();

echo json_encode($projects);


//echo "hello world 77";
