<?php
global $db;
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$params = array();

$mode = $_POST['mode'];
$token = $_POST['token'];
$projectID = $_POST['projectID'];


if ($mode == "loadArray"){
    $row = $db->query(
        'SELECT  p.projectName, p.shopTypeID, IF(p.shopTypeID=1, "Restaurant", "Massage") as "typeName", p.countryID, c.name AS "countryName", s.sNickName 
        FROM tb_project p, staffs s, Countries c 
        WHERE p.projectOwner = s.sID AND p.countryID = c.id AND p.projectID = ?;', $projectID)->fetchArray();


    $params["projectID"] = $projectID;
    $params["projectName"] = $row["projectName"];
    $params["shopTypeID"] = $row["shopTypeID"];
    $params["typeName"] = $row["typeName"];
    $params["countryID"] = $row["countryID"];
    $params["countryName"] = $row["countryName"];
    $params["sNickName"] = $row["sNickName"];

    $params['result'] = "found ".count($row)." data";
}

echo json_encode($params);