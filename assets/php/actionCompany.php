<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "";
$params["nickName"] = !empty($_REQUEST['nickName']) ? $_REQUEST['nickName'] : "";
$params["name"] = !empty($_REQUEST['name']) ? $_REQUEST['name'] : "";
$params["team"] = !empty($_REQUEST['team']) ? $_REQUEST['team'] : "";
$params["status"] = !empty($_REQUEST['status']) ? $_REQUEST['status'] : "";
$params["datepicker"] = !empty($_REQUEST['datepicker']) ? reDateFormat($_REQUEST['datepicker']) : "";
$params["typeExpense"] = !empty($_REQUEST['typeExpense']) ? $_REQUEST['typeExpense'] : "";
$params["value"] = !empty($_REQUEST['value']) ? $_REQUEST['value'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$params["delID"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["employeeStatus"] = !empty($_REQUEST['employeeStatus']) ? 1 : 0;


if ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if($params ["typeID"]=='formEmployee'){
        $insert = $db->query('INSERT INTO `Employees` (`nickName`, `fullName`, `teamID`, `status`, `activeDate`) VALUES (?, ?, ?, ?, ?);'
            ,$params["nickName"], $params["name"], $params["team"], $params["status"], $params["datepicker"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["typeID"]=='formBkkExpense'){
        $insert = $db->query('INSERT INTO `ExpenseDetail` (`typeID`, `value`, `month`, `year`) VALUES (?, ?, ?, ?);'
            ,$params["typeExpense"], $params["value"], $params["month"], $params["year"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["typeID"]=='formThOperation'){
        $insert = $db->query('INSERT INTO `ExpenseDetail` (`typeID`, `value`, `month`, `year`) VALUES (?, ?, ?, ?);'
            ,$params["typeExpense"], $params["value"], $params["month"], $params["year"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["typeID"]=='formCEOLiving'){
        $insert = $db->query('INSERT INTO `ExpenseDetail` (`typeID`, `value`, `month`, `year`) VALUES (?, ?, ?, ?);'
            ,$params["typeExpense"], $params["value"], $params["month"], $params["year"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }

}elseif ($params ["action"] == "setDelete"){

    $params["txt"] = "Delete it";

    if($params["typeID"]=='formEmployee') {
        $delete = $db->query('DELETE FROM Employees WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }elseif($params["typeID"]=='formBkkExpense'){
        $delete = $db->query('DELETE FROM ExpenseDetail WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }elseif($params["typeID"]=='formThOperation'){
        $delete = $db->query('DELETE FROM ExpenseDetail WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }elseif($params["typeID"]=='formCEOLiving'){
        $delete = $db->query('DELETE FROM ExpenseDetail WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }
}elseif ($params ["action"] == "setStatus"){
    if($params["typeID"]=='formEmployee') {
        $status = $db->query('UPDATE `Employees` SET `status` = ? WHERE `Employees`.`id` = ?;', $params["employeeStatus"], $params["id"]);
        $params["affected"] = $status->affectedRows();
    }
}

echo json_encode($params);

function reDateFormat($param){
    $tmp1 = explode("-", $param);
    return $tmp1[2]."-".$tmp1[1]."-".$tmp1[0];
}