<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB_example.php";
$myID = $_SESSION['id'];

$salt = "L4U";
$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["editID"] = !empty($_REQUEST['editID']) ? $_REQUEST['editID'] : "";
$params["status"] = !empty($_REQUEST['status']) ? 1 : 0;
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : 'add';

if ($params ["action"] == "setStatus"){
    $update = $db->query('UPDATE `staffs` SET `sStatus` = ? WHERE `staffs`.`sID` = ?;', $params ["status"], $params ["id"]);
    $params["affected"] = $update->affectedRows();
}elseif ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT * FROM `staffs` WHERE sID = ?;',$params ["id"])->fetchArray();
    $params["name"] = $row["sName"];
    $params["tname"] = $row["sTName"];
    $params["email"] = $row["sEmail"];
    $params["phone"] = $row["sMobile"];
    $params["password"] = $row["sPassword"];
    $params["level"] = $row["sLevel"];
    $params["religion"] = $row["rID"];
    $params["team"] = $row["teamID"];
    $params["status"] = $row["sStatus"];
    $params["address"] = $row["sAddress"];
    $params["nickname"] = $row["sNickName"];
    $params["birthday"] = $row["sDOB"];//yyyy-mm-dd
    $params["startdate"] = $row["sActiveDate"];//yyyy-mm-dd
    $params["employeenumber"] = $row["sEmpID"];
    $params["address"] = $row["sAddress"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if (!empty($_REQUEST['inputPassword'])){
        $passwordHash = md5($salt . $_REQUEST['inputPassword']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["inputName"] = !empty($_REQUEST['inputName']) ? $_REQUEST['inputName'] : "invalid Name";
    $params["inputTname"] = !empty($_REQUEST['inputTname']) ? $_REQUEST['inputTname'] : "invalid Thai Name";
    $params["inputEmail"] = !empty($_REQUEST['inputEmail']) ? $_REQUEST['inputEmail'] : "invalid Email";
    $params["inputPhone"] = !empty($_REQUEST['inputPhone']) ? $_REQUEST['inputPhone'] : "invalid Phone";
    $params["inputPassword"] = $passwordHash;
    $params["inputLevel"] = !empty($_REQUEST['inputLevel']) ? $_REQUEST['inputLevel'] : "3";
    $params["inputReligion"] = !empty($_REQUEST['inputReligion']) ? $_REQUEST['inputReligion'] : "1";
    $params["inputStatus"] = !empty($_REQUEST['inputStatus']) ? $_REQUEST['inputStatus'] : "0";
    $params["inputNickName"] = !empty($_REQUEST['inputNickName']) ? $_REQUEST['inputNickName'] : "";
    $params["inputBirthday"] = !empty($_REQUEST['inputBirthday']) ? $_REQUEST['inputBirthday'] : NULL;
    $params["inputStartDate"] = !empty($_REQUEST['inputStartDate']) ? $_REQUEST['inputStartDate'] : NULL;
    $params["inputEmployeeNumber"] = !empty($_REQUEST['inputEmployeeNumber']) ? $_REQUEST['inputEmployeeNumber'] : NULL;
    $params["inputAddress"] = !empty($_REQUEST['inputAddress']) ? $_REQUEST['inputAddress'] : NULL;
    $params["inputTeam"] = !empty($_REQUEST['inputTeam']) ? $_REQUEST['inputTeam'] : "7";


    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `staffs`
                                (`sName`,`STName`, `sNickName`,`sDOB`,`sActiveDate`,`sEmpID`,`sAddress`,`sEmail`, `sMobile`, `sPassword`,`rID`,`teamID`, `sStatus`, `sLevel`, `sCreateBy`) 
                                VALUES (?,?,?,?,?,?,?,?,?, ?,?, ?,?,?,?);'
            ,$params["inputName"],$params["inputTname"],$params["inputNickName"],$params["inputBirthday"],$params["inputStartDate"],$params["inputEmployeeNumber"],$params["inputAddress"],$params["inputEmail"],$params["inputPhone"],$params["inputPassword"],$params["inputReligion"],$params["inputTeam"],$params["inputStatus"],$params["inputLevel"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `staffs` SET 
                                `sName`= ?,`sTName`= ?, `sNickName`=?, `sDOB`= ?, `rID`= ?, `sActiveDate`= ?, `sEmpID`= ?, `sAddress`= ?,`sEmail` = ?, `sMobile` =?,`rID` = ?,`teamID` = ?, `sStatus` = ?, `sLevel` = ?, `sUpdateBy`= ?, sUpdateAt = NOW() 
                                WHERE sID = ? ;'
            ,$params["inputName"],$params["inputTname"],$params["inputNickName"],$params["inputBirthday"],$params["inputReligion"],$params["inputStartDate"],$params["inputEmployeeNumber"],$params["inputAddress"],$params["inputEmail"],$params["inputPhone"],$params["inputReligion"],$params["inputTeam"],$params["inputStatus"],$params["inputLevel"],$params["by"],$params ["editID"]
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

    $delete = $db->query('UPDATE `staffs` SET `sDeleteAt` = NOW(),`sStatus` = 0, `sDeleteBy` = ? WHERE sID = ?;', $_SESSION['id'], $params ["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);

function dateSqltoHuman($databd){//input yyyy-mm-dd
    $arr = explode("-",$databd);
    $Brithday = $arr[2]."-".$arr[1]."-".$arr[0];

    return ($Brithday);//output dd/mm/yyyy
};

function dateHumantoSql($databd){//dd/mm/yyyy
    $arr = explode("-",$databd);
    $Human = $arr[2]."-".$arr[1]."-".$arr[0];

    return ($Human);//output yyyy-mm-dd
};
