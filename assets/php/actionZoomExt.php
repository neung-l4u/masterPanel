<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_POST['editID']) ? $_POST['editID'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

if ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT ext FROM `staffs` WHERE sID = ?;',$params ["id"])->fetchArray();
    $params["zoomExt"] = $row["ext"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";
    $params["zoomExt"] = !empty($_POST['zoomExt']) ? $_POST['zoomExt'] : "";
    $zoomExtJson = json_encode($params["zoomExt"]);
    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `staffs` SET 
                                `ext` = ?, `sUpdateBy`= ?, sUpdateAt = NOW() 
                                WHERE sID = ? ;'
            ,$zoomExtJson,$params["by"],$params["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}

echo json_encode($params);