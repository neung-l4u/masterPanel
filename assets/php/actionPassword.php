<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_POST['editID']) ? $_POST['editID'] : "";
$params["status"] = !empty($_POST['status']) ? 1 : 0;
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

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
    $params["inputSharePW"] = $row["pwShare"];
}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if (!empty($_POST['inputPassword'])){
        $passwordHash = md5($salt . $_POST['inputPassword']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["inputType"] = !empty($_POST['inputType']) ? $_POST['inputType'] : "invalid Type";
    $params["inputTeam"] = !empty($_POST['inputTeam']) ? $_POST['inputTeam'] : "invalid Team";
    $params["inputLevel"] = !empty($_POST['inputLevel']) ? $_POST['inputLevel'] : "invalid Level";
    $params["inputpwName"] = !empty($_POST['inputpwName']) ? $_POST['inputpwName'] : "invalid Name";
    $params["inputAccessLink"] = !empty($_POST['inputAccessLink']) ? $_POST['inputAccessLink'] : "invalid Link";
    $params["inputUserName"] = !empty($_POST['inputUserName']) ? $_POST['inputUserName'] : "invalid Username";
    $params["inputPassword"] = !empty($_POST['inputPassword']) ? $_POST['inputPassword'] : "invalid Password";
    $params["inputSharePW"] = !empty($_POST['inputSharePW']) ? $_POST['inputSharePW'] : "0";
    $params["inputNote"] = !empty($_POST['inputNote']) ? $_POST['inputNote'] : "";
    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `passwordmanager`
                                (`pwName`, `pwLink`, `pwUser`, `pwPass`, `pwType`, `pwTeam`, `pwLevel`, `pwNote`, `pwShare`, `pwCreateBy`) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);'
            ,$params["inputpwName"],
            $params["inputAccessLink"],
            $params["inputUserName"],
            $params["inputPassword"],
            $params["inputType"],
            $params["inputTeam"],
            $params["inputLevel"],
            $params["inputNote"],
            $params["inputSharePW"],
            $params["by"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();

    }elseif($params ["formAction"]=='edit'){
        $update = $db->query('UPDATE `passwordmanager` SET 
                                `pwName`= ?, `pwLink` = ?, `pwUser` =?, `pwPass` = ?, `pwType` = ?, `pwTeam`= ?, `pwLevel`= ?, `pwNote`= ?, `pwShare` = ?, `pwUpdateBy`= ?, pwUpdateAt = NOW() 
                                WHERE id = ? ;'
            ,$params["inputpwName"],
            $params["inputAccessLink"],
            $params["inputUserName"],
            $params["inputPassword"],
            $params["inputType"],
            $params["inputTeam"],
            $params["inputLevel"],
            $params["inputNote"],
            $params["inputSharePW"],
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