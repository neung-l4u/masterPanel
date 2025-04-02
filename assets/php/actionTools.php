<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$salt = "L4U";
$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["editID"] = !empty($_REQUEST['editID']) ? $_REQUEST['editID'] : "";
$params["status"] = !empty($_REQUEST['status']) ? 1 : 0;
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : 'add';

if ($params ["action"] == "setStatus"){
    $update = $db->query('UPDATE `tools` SET `status` = ? WHERE `tools`.`id` = ?;', $params ["status"], $params ["id"]);
    $params["affected"] = $update->affectedRows();
}elseif ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT * FROM `tools` WHERE id = ?;',$params ["id"])->fetchArray();
    $params["type"] = $row["type"];
    $params["name"] = $row["name"];
    $params["description"] = $row["detail"];
    $params["link"] = $row["link"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";


    $params["inputType"] = !empty($_REQUEST['inputType']) ? $_REQUEST['inputType'] : "";
    $params["inputServices"] = !empty($_REQUEST['inputServices']) ? $_REQUEST['inputServices'] : "";
    $params["inputDescription"] = !empty($_REQUEST['inputDescription']) ? $_REQUEST['inputDescription'] : "";
    $params["inputLink"] = !empty($_REQUEST['inputLink']) ? $_REQUEST['inputLink'] : "";
    $params["inputStatus"] = !empty($_REQUEST['inputStatus']) ? $_REQUEST['inputStatus'] : "0";
    $params["by"] = $_SESSION['id'];
}

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `tools`
                                (`name`,`link`, `detail`,`status`, `type`, `createBy`)
                                VALUES (?,?,?,?,?,?);'
            ,$params["inputServices"],$params["inputLink"],$params["inputDescription"],$params["inputStatus"],$params["inputType"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `tools` SET 
                                `name`= ?,`link`= ?, `detail`=?, `status`= ?, `type`= ?, `updateBy`= ?, updateAt = NOW() 
                                WHERE sID = ? ;'
            ,$params["inputServices"],$params["inputLink"],$params["inputDescription"],$params["inputStatus"],$params["inputType"],$params["by"]
        );

        $params["affected"] = $update->affectedRows();
    }

if ($params ["action"] == "setDelete"){

    $delete = $db->query('UPDATE `tools` SET `deleteAt` = NOW(),`status` = 0, `deleteBy` = ? WHERE id = ?;', $_SESSION['id'], $params ["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);

