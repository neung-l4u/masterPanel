<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "0";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["value"] = !empty($_REQUEST['value']) ? $_REQUEST['value'] : "";
$params["delID"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";

if ($params ["action"] == "save"){
    $params["txt"] = "Got it";

    $insert = $db->query('INSERT INTO `IHDDetail` (`typeID`, `month`, `year`, `value`) VALUES (?, ?, ?, ?);'
        ,$params["typeID"], $params["month"], $params["year"], $params["value"]
    );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();

}elseif ($params ["action"] == "setDelete"){
    $params["txt"] = "Delete it";

    $delete = $db->query('DELETE FROM IHDDetail WHERE `id` = ?;', $params["delID"]);
    $params["affected"] = $delete->affectedRows();

}

echo json_encode($params);