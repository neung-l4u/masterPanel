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


if ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT * FROM `Cancellation`')->fetchArray();
    $params["county"] = $row["county"];
    $params["city"] = $row["city"];
    $params["shopname"] = $row["shopname"];
    $params["trading"] = $row["trading"];
    $params["address"] = $row["address"];
    $params["state"] = $row["state"];
    $params["zip"] = $row["zip"];
    $params["firstname"] = $row["firstname"];
    $params["lastname"] = $row["lastname"];
    $params["mobile"] = $row["mobile"];
    $params["email"] = $row["email"];
    $params["other"] = $row["other"];
    $params["reason"] = $row["reason"];
    $params["lastdate"] = $row["latedate"];
    $params["feedback"] = $row["feedback"];
    $params["timestamp"] = $row["timestamp"];


}

function dateHumantoSql($databd){//dd/mm/yyyy
    $arr = explode("-",$databd);
    $Human = $arr[2]."-".$arr[1]."-".$arr[0];

    return ($Human);//output yyyy-mm-dd
};
