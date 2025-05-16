<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["year"] = !empty($_POST['year']) ? $_POST['year'] : "";
$params["month"] = !empty($_POST['month']) ? $_POST['month'] : "";
$params["paidOn"] = !empty($_POST['paidOn']) ? $_POST['paidOn'] : "";
$params["total"] = !empty($_POST['total']) ? $_POST['total'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : "";
$params["serviceID"] = !empty($_POST['serviceID']) ? $_POST['serviceID'] : "";
$params["typeID"] = !empty($_POST['typeID']) ? $_POST['typeID'] : "";
$params["delID"] = !empty($_GET['id']) ? $_GET['id'] : "";

if ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if($params ["formAction"]=='Monthly'){
        $insert = $db->query('INSERT INTO `SucscriptionsMonthly`(`typeID`, `month`, `year`, `value`) VALUES (?,?,?,?);'
            ,$params["serviceID"], $params["month"], $params["year"], $params["total"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='Yearly'){
        $insert = $db->query('INSERT INTO `SucscriptionsYearly`(`typeID`, `year`, `value`, `paidOn`) VALUES (?, ?, ?, ?);'
            ,$params["serviceID"], $params["year"], $params["total"], $params["paidOn"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }

}elseif ($params ["action"] == "setDelete"){
    $params["txt"] = "Delete it";

    if($params["typeID"]=='Monthly') {
        $delete = $db->query('DELETE FROM SucscriptionsMonthly WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }elseif($params["typeID"]=='Yearly'){
        $delete = $db->query('DELETE FROM SucscriptionsYearly WHERE `id` = ?;', $params["delID"]);
        $params["affected"] = $delete->affectedRows();
    }

}

echo json_encode($params);