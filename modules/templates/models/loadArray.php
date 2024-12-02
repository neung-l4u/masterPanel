<?php
global $db;
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$mode = $_POST['mode'];
$token = $_POST['token'];
$projectID = $_POST['projectID'];

if ($mode == "loadArray"){
    $select = $db->query(
        'SELECT p.projectID, p.projectName, p.shopTypeID, IF(p.shopTypeID=1, "Restaurant", "Massage") as "typeName", p.countryID, c.countryName, s.sNickName 
        FROM tb_project p, staffs s, tb_country c 
        WHERE p.projectOwner = s.sID AND p.projectID = ? AND p.countryID = c.countryID;', $projectID)->fetchAll();
//    $data = array();
    /*$i = 0;
    foreach ($select as $row){
        $data[$i] = $row;
        $i++;
    }*/
    $data = $select[0];

    $params['result'] = "found ".count($select)." data";
    $params['data'] = $data;
}

echo json_encode($params);