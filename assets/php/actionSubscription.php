<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["paidOn"] = !empty($_REQUEST['paidOn']) ? $_REQUEST['paidOn'] : "";
$params["total"] = !empty($_REQUEST['total']) ? $_REQUEST['total'] : "";
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : "";
$params["serviceID"] = !empty($_REQUEST['serviceID']) ? $_REQUEST['serviceID'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "";
$params["delID"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";

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