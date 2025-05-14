<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["typeID"] = !empty($_POST['typeID']) ? $_POST['typeID'] : "";
$params["year"] = !empty($_POST['year']) ? $_POST['year'] : "";
$params["month"] = !empty($_POST['month']) ? $_POST['month'] : "";
$params["total"] = !empty($_POST['total']) ? $_POST['total'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

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