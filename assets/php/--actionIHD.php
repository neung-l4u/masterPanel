<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : "";
$params["typeID"] = !empty($_POST['typeID']) ? $_POST['typeID'] : "0";
$params["year"] = !empty($_POST['year']) ? $_POST['year'] : "";
$params["month"] = !empty($_POST['month']) ? $_POST['month'] : "";
$params["value"] = !empty($_POST['value']) ? $_POST['value'] : "";
$params["delID"] = !empty($_GET['id']) ? $_GET['id'] : "";

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