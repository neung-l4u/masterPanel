<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : "";
$params["typeID"] = !empty($_POST['typeID']) ? $_POST['typeID'] : "";
$params["nickName"] = !empty($_POST['nickName']) ? $_POST['nickName'] : "";
$params["name"] = !empty($_POST['name']) ? $_POST['name'] : "";
$params["team"] = !empty($_POST['team']) ? $_POST['team'] : "";
$params["status"] = !empty($_POST['status']) ? $_POST['status'] : "";
$params["datepicker"] = !empty($_POST['datepicker']) ? reDateFormat($_POST['datepicker']) : "";
$params["typeExpense"] = !empty($_POST['typeExpense']) ? $_POST['typeExpense'] : "";
$params["value"] = !empty($_POST['value']) ? $_POST['value'] : "";
$params["month"] = !empty($_POST['month']) ? $_POST['month'] : "";
$params["year"] = !empty($_POST['year']) ? $_POST['year'] : "";
$params["delID"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["employeeStatus"] = !empty($_POST['employeeStatus']) ? 1 : 0;


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