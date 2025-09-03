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
    $update = $db->query('UPDATE `staffs` SET `sStatus` = ? WHERE `staffs`.`sID` = ?;', $params ["status"], $params ["id"]);
    $params["affected"] = $update->affectedRows();
}elseif ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT * FROM `staffs` WHERE sID = ?;',$params ["id"])->fetchArray();
    $params["stafftype"] = $row["sStaffType"];
    $params["name"] = $row["sName"];
    $params["tname"] = $row["sTName"];
    $params["email"] = $row["sEmail"];
    $params["phone"] = $row["sMobile"];
    $params["password"] = $row["sPassword"];
    $params["level"] = $row["sLevel"];
    $params["religion"] = $row["rID"];
    $params["nationality"] = $row["sNationality"];
    $params["team"] = $row["teamID"];
    $params["status"] = $row["sStatus"];
    $params["address"] = $row["sAddress"];
    $params["nickname"] = $row["sNickName"];
    $params["birthday"] = $row["sDOB"];//yyyy-mm-dd
    $params["startdate"] = $row["sActiveDate"];//yyyy-mm-dd
    $params["employeenumber"] = $row["sEmpID"];
    $params["address"] = $row["sAddress"];
    $params["zoomExt"] = $row["ext"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if (!empty($_POST['inputPassword'])){
        $passwordHash = md5($salt . $_POST['inputPassword']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["inputStaffType"] = !empty($_POST['inputStaffType']) ? $_POST['inputStaffType'] : "fullTime";
    $params["inputName"] = !empty($_POST['inputName']) ? $_POST['inputName'] : "invalid Name";
    $params["inputTname"] = !empty($_POST['inputTname']) ? $_POST['inputTname'] : "invalid Thai Name";
    $params["inputEmail"] = !empty($_POST['inputEmail']) ? $_POST['inputEmail'] : "invalid Email";
    $params["inputPhone"] = !empty($_POST['inputPhone']) ? $_POST['inputPhone'] : "invalid Phone";
    $params["inputPassword"] = $passwordHash;
    $params["inputLevel"] = !empty($_POST['inputLevel']) ? $_POST['inputLevel'] : "3";
    $params["inputReligion"] = !empty($_POST['inputReligion']) ? $_POST['inputReligion'] : "1";
    $params["inputNationality"] = !empty($_POST['inputNationality']) ? $_POST['inputNationality'] : "Thai";
    $params["inputStatus"] = !empty($_POST['inputStatus']) ? $_POST['inputStatus'] : "0";
    $params["inputNickName"] = !empty($_POST['inputNickName']) ? $_POST['inputNickName'] : "";
    $params["inputBirthday"] = !empty($_POST['inputBirthday']) ? $_POST['inputBirthday'] : NULL;
    $params["inputStartDate"] = !empty($_POST['inputStartDate']) ? $_POST['inputStartDate'] : NULL;
    $params["inputEmployeeNumber"] = !empty($_POST['inputEmployeeNumber']) ? $_POST['inputEmployeeNumber'] : NULL;
    $params["inputAddress"] = !empty($_POST['inputAddress']) ? $_POST['inputAddress'] : NULL;
    $params["inputTeam"] = !empty($_POST['inputTeam']) ? $_POST['inputTeam'] : "7";
    $params["zoomExt"] = !empty($_POST['zoomExt']) ? $_POST['zoomExt'] : "";
    $zoomExtJson = json_encode($params["zoomExt"]);

    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `staffs`
                                (`sName`,`STName`, `sNickName`,`sDOB`,`sActiveDate`,`sEmpID`,`sAddress`,`sEmail`, `sMobile`, `sPassword`,`rID`,`teamID`, `sStatus`, `sLevel`, `sStaffType`, `ext`, `sNationality`, `sCreateBy`) 
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);'
            ,$params["inputName"],$params["inputTname"],$params["inputNickName"],$params["inputBirthday"],$params["inputStartDate"],$params["inputEmployeeNumber"],$params["inputAddress"],$params["inputEmail"],$params["inputPhone"],$params["inputPassword"],$params["inputReligion"],$params["inputTeam"],$params["inputStatus"],$params["inputLevel"],$params["inputStaffType"], $zoomExtJson,$params["inputNationality"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `staffs` SET 
                                `sName`= ?,`sTName`= ?, `sNickName`=?, `sDOB`= ?, `rID`= ?, `sActiveDate`= ?, `sEmpID`= ?, `sAddress`= ?,`sEmail` = ?, `sMobile` =?,`rID` = ?,`teamID` = ?, `sStatus` = ?, `sLevel` = ?, `sStaffType` = ?,`ext` = ?, `sNationality` = ?, `sUpdateBy`= ?, sUpdateAt = NOW() 
                                WHERE sID = ? ;'
            ,$params["inputName"],$params["inputTname"],$params["inputNickName"],$params["inputBirthday"],$params["inputReligion"],$params["inputStartDate"],$params["inputEmployeeNumber"],$params["inputAddress"],$params["inputEmail"],$params["inputPhone"],$params["inputReligion"],$params["inputTeam"],$params["inputStatus"],$params["inputLevel"],$params["inputStaffType"],$zoomExtJson,$params["inputNationality"],$params["by"],$params ["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "changePassword"){
    if (!empty($_POST['password'])){
        $passwordHash = md5($salt . $_POST['password']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["newPassword"] = $passwordHash;

    $update = $db->query('UPDATE `staffs` SET `sPassword` = ? WHERE sID = ?;', $params["newPassword"], $myID);
    $params["affected"] = $update->affectedRows();

    $_SESSION['password'] = $_POST['password'];

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
