<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["editID"] = !empty($_REQUEST['editID']) ? $_REQUEST['editID'] : "";
$params["status"] = !empty($_REQUEST['status']) ? 1 : 0;
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : 'add';

if ($params ["action"] == "loadUpdate"){
    $row = $db->query('SELECT * FROM `passwordmanager` WHERE id = ?;',$params ["id"])->fetchArray();
    $params["inputType"] = $row["pwType"];
    $params["inputTeam"] = $row["pwTeam"];
    $params["inputTeam"] = $row["pwTeam"];
    $params["inputLevel"] = $row["pwLevel"];
    $params["inputpwName"] = $row["pwName"];
    $params["inputAccessLink"] = $row["pwLink"];
    $params["inputUserName"] = $row["pwUser"];
    $params["inputPassword"] = $row["pwPass"];
    $params["inputNote"] = $row["pwNote"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if (!empty($_REQUEST['inputPassword'])){
        $passwordHash = md5($salt . $_REQUEST['inputPassword']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["inputType"] = !empty($_REQUEST['inputType']) ? $_REQUEST['inputType'] : "invalid Type";
    $params["inputTeam"] = !empty($_REQUEST['inputTeam']) ? $_REQUEST['inputTeam'] : "invalid Team";
    $params["inputLevel"] = !empty($_REQUEST['inputLevel']) ? $_REQUEST['inputLevel'] : "invalid Level";
    $params["inputpwName"] = !empty($_REQUEST['inputpwName']) ? $_REQUEST['inputpwName'] : "invalid Name";
    $params["inputAccessLink"] = !empty($_REQUEST['inputAccessLink']) ? $_REQUEST['inputAccessLink'] : "invalid Link";
    $params["inputUserName"] = !empty($_REQUEST['inputUserName']) ? $_REQUEST['inputUserName'] : "invalid Username";
    $params["inputPassword"] = !empty($_REQUEST['inputPassword']) ? $_REQUEST['inputPassword'] : "invalid Password";
    $params["inputNote"] = !empty($_REQUEST['inputNote']) ? $_REQUEST['inputNote'] : "";
    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `passwordmanager`
                                (`pwName`, `pwLink`, `pwUser`, `pwPass`, `pwType`, `pwTeam`, `pwLevel`, `pwNote`, `pwCreateBy`) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);'
            ,$params["inputpwName"],
            $params["inputAccessLink"],
            $params["inputUserName"],
            $params["inputPassword"],
            $params["inputType"],
            $params["inputTeam"],
            $params["inputLevel"],
            $params["inputNote"],
            $params["by"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();

    }elseif($params ["formAction"]=='edit'){
        $update = $db->query('UPDATE `passwordmanager` SET 
                                `pwName`= ?, `pwLink` = ?, `pwUser` =?, `pwPass` = ?, `pwType` = ?, `pwTeam`= ?, `pwLevel`= ?, `pwNote`= ?, `pwUpdateBy`= ?, pwUpdateAt = NOW() 
                                WHERE id = ? ;'
            ,$params["inputpwName"],
            $params["inputAccessLink"],
            $params["inputUserName"],
            $params["inputPassword"],
            $params["inputType"],
            $params["inputTeam"],
            $params["inputLevel"],
            $params["inputNote"],
            $params["by"],
            $params ["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "setDelete"){
    $delete = $db->query('UPDATE `passwordmanager` SET 
                            `pwType` = 0, `pwDeleteAt` = NOW(), `pwDeleteBy` = ? WHERE id = ? ;'
        ,$params["by"],
        $params["id"]
    );
    $params["success"] = $delete->affectedRows() > 0;
}

echo json_encode($params);