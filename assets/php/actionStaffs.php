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
    $update = $db->query('UPDATE `staffs` SET `sStatus` = ? WHERE `staffs`.`sID` = ?;', $params ["status"], $params ["id"]);
    //echo $update->affectedRows();
    $params["affected"] = $update->affectedRows();
}elseif ($params ["action"] == "loadUpdate"){
    $row = $db->query('SELECT * FROM `staffs` WHERE sID = ?;',$params ["id"])->fetchArray();
    $params["name"] = $row["sName"];
    $params["email"] = $row["sEmail"];
    $params["phone"] = $row["sMobile"];
    $params["password"] = $row["sPassword"];
    $params["level"] = $row["sLevel"];
    $params["status"] = $row["sStatus"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if (!empty($_REQUEST['inputPassword'])){
        $passwordHash = md5($salt . $_REQUEST['inputPassword']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["inputName"] = !empty($_REQUEST['inputName']) ? $_REQUEST['inputName'] : "invalid Name";
    $params["inputEmail"] = !empty($_REQUEST['inputEmail']) ? $_REQUEST['inputEmail'] : "invalid Email";
    $params["inputPhone"] = !empty($_REQUEST['inputPhone']) ? $_REQUEST['inputPhone'] : "invalid Phone";
    $params["inputPassword"] = $passwordHash;
    $params["inputLevel"] = !empty($_REQUEST['inputLevel']) ? $_REQUEST['inputLevel'] : "3";
    $params["inputStatus"] = !empty($_REQUEST['inputStatus']) ? $_REQUEST['inputStatus'] : "0";
    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `staffs`
                                (`sName`, `sEmail`, `sMobile`, `sPassword`, `sStatus`, `sLevel`, `sUpdateBy`) 
                                VALUES (?, ?, ?, ?, ?, ?, ?);'
            ,$params["inputName"],$params["inputEmail"],$params["inputPhone"],$params["inputPassword"],$params["inputStatus"],$params["inputLevel"],$params["by"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){
        $update = $db->query('UPDATE `staffs` SET 
                                `sName`= ?, `sEmail` = ?, `sMobile` =?, `sStatus` = ?, `sLevel` = ?, `sUpdateBy`= ?, sUpdateAt = NOW() 
                                WHERE sID = ? ;'
            ,$params["inputName"],$params["inputEmail"],$params["inputPhone"],$params["inputStatus"],$params["inputLevel"],$params["by"],$params ["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "changePassword"){
    if (!empty($_REQUEST['password'])){
        $passwordHash = md5($salt . $_REQUEST['password']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["newPassword"] = $passwordHash;

    $update = $db->query('UPDATE `staffs` SET `sPassword` = ? WHERE sID = ?;', $params["newPassword"], $myID);
    $params["affected"] = $update->affectedRows();

    $_SESSION['password'] = $_REQUEST['password'];

}elseif ($params ["action"] == "setDelete"){
    $delete = $db->query('UPDATE `staffs` SET `sDeleteAt` = NOW(), `sDeleteBy` = ? WHERE sID = ?;', $_SESSION['id'], $params ["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);