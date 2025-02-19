<?php
session_start();
global $db;

//เรียกใช้งาน object
include '../assets/db/db.php';
include "../assets/db/initDB.php";


$return['result'] = '';
$return['msg'] = '';

$stat = $db->query('SELECT COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo WHERE mo.staffID = ? GROUP BY mo.staffID;',$_SESSION['id'])->fetchArray();

$data = $stat['count'];
$return['result'] = 'add report success';
$return['msg'] = 'report sent';

echo json_encode($data);