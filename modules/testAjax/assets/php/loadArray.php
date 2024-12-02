<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

$mode = $_POST['mode'];
$token = $_POST['token'];
$projectID = $_POST['id'];

if ($mode == "loadArray"){
    // $select = $db->query('SELECT * FROM `testAjaxData`;')->fetchAll();
    $select = $db->query('SELECT * FROM `tb_project` WHERE `projectID` = ?;', $projectID)->fetchAll();

    $data = array();
    $i = 0;
    foreach ($select as $row){
        $data[$i] = $row;
        $i++;
    }

    $params['result'] = "found ".count($select)." data";
    $params['data'] = $data;
}

echo json_encode($params);