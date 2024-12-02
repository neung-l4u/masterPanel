<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["year"] = 2023;
$params["month"] = '10';
$params["type"] = '10';

$resultType = $db->query('SELECT `id`, `name` FROM `SucscriptionsType` WHERE `period` = ? AND `status` = ? ORDER BY name;', "Monthly", 1)->fetchAll();

$data = array("data"=> array());
$i = 1;

foreach ($resultType as $type) {
    $resultSum = $db->query('SELECT ROUND(SUM(`value`),2) AS "total" FROM `SucscriptionsMonthly` WHERE `month` = ? AND `year` = ? AND `typeID` = ? GROUP BY `year`;', $params["month"], $params["year"], $type["id"])->fetchAll();

    if(count($resultSum)>0){
        foreach ($resultSum as $sum) {
            $data["data"][] = array(
                $i,
                $type["name"],
                "$".number_format($sum["total"],2)
            );
        }
    }else{
        $data["data"][] = array(
            $i,
            $type["name"],
            "$0.00"
        );
    }

    $i++;
} //foreach


echo json_encode($data);
