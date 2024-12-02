<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$data = [];
$sum = 0;
$params["year"] = 2023;

$resultType = $db->query('SELECT id,name FROM `StatsMeasureType` WHERE status = 1;')->fetchAll();


foreach ($resultType as $type) {
    $result = $db->query('SELECT ty.name, ROUND(SUM(de.total),2) AS `val`, de.year FROM `StatsMeasureDetail` de, `StatsMeasureType` ty WHERE de.typeID = ty.id AND de.year = ? AND de.typeID = ? GROUP BY de.year, de.typeID;', $params["year"], $type["id"])->fetchAll();
    $found = count($result);

    if($found>0){
        foreach ($result as $detail) {
            $data["data"][] = [ $type["id"], $type["name"], "$".number_format($detail["val"], 2) ];
            $sum += round($detail["val"], 2);
        }//foreach
    }else{
        $data["data"][] = [ $type["id"], $type["name"], "$".number_format(0, 2) ];
    }
}//foreach
$data["data"][] = [ "=", "<b>Total</b>", "<b>$".number_format($sum, 2)."</b>" ];

echo json_encode($data);













