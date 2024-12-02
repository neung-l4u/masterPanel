<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["total"] = !empty($_REQUEST['total']) ? $_REQUEST['total'] : "";
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : 'add';

if ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `StatsMeasureDetail` (`typeID`, `month`, `year`, `total`) VALUES (?,?,?,?);'
            ,$params["typeID"], $params["month"], $params["year"], $params["total"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }

}elseif ($params ["action"] == "setDelete"){
    $delete = $db->query('DELETE FROM StatsMeasureDetail WHERE `id` = ?;', $params ["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);