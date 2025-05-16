<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$salt = "L4U";
$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_POST['editID']) ? $_POST['editID'] : "";
$params["status"] = !empty($_POST['status']) ? 1 : 0;
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

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


    $params["inputType"] = !empty($_POST['inputType']) ? $_POST['inputType'] : "";
    $params["inputServices"] = !empty($_POST['inputServices']) ? $_POST['inputServices'] : "";
    $params["inputDescription"] = !empty($_POST['inputDescription']) ? $_POST['inputDescription'] : "";
    $params["inputLink"] = !empty($_POST['inputLink']) ? $_POST['inputLink'] : "";
    $params["inputStatus"] = !empty($_POST['inputStatus']) ? $_POST['inputStatus'] : "0";
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

