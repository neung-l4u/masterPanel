<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["year"] = 2023;
$params["month"] = '10';
$params["type"] = '10';

$resultType = $db->query('SELECT `id`, `name` FROM `SucscriptionsType` WHERE `period` = ? AND `status` = ? ORDER BY name;', "Yearly", 1)->fetchAll();

$data = array("data"=> array());
$i = 1;

foreach ($resultType as $type) {
    $resultSum = $db->query('SELECT `value` AS "total", paidOn FROM `SucscriptionsYearly` WHERE `year` = ? AND `typeID` = ?;', $params["year"], $type["id"])->fetchAll();

    if(count($resultSum)>0){
        foreach ($resultSum as $sum) {
            $data["data"][] = array(
                $i,
                $type["name"],
                !empty($sum["paidOn"]) ? showOnlyDate($sum["paidOn"]) : " -- ",
                "$".number_format($sum["total"],2)
            );
        }
    }else{
        $data["data"][] = array(
            $i,
            $type["name"],
            " -- ",
            "$0.00"
        );
    }

    $i++;
} //foreach

echo json_encode($data);

function showOnlyDate($param){
    $tmp = explode(" ", $param);
    $tmp2 = explode("-",$tmp[0]);
    return $tmp2[2]."/".$tmp2[1]."/".$tmp2[0];
}
