<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";


$data["total"] = "$0.00";
$params["action"] = !empty($_REQUEST["act"]) ? $_REQUEST["act"] : "";
$params["year"] = !empty($_REQUEST["year"]) ? $_REQUEST["year"] : "";
$params["month"] = !empty($_REQUEST["month"]) ? $_REQUEST["month"] : "";

if ($params["action"] == "getYearly"){
    $result = $db->query('SELECT ROUND(SUM(`value`),2) AS "total" FROM `SucscriptionsYearly` WHERE `year` = ? GROUP BY `year`;', $params["year"])->fetchAll();

    foreach ($result as $row) {
        $data["total"] = "$".number_format($row["total"],2);
    } //foreach
}

echo json_encode($data);