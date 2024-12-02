<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$date["monthName"] = array("01"=>'January', "02"=>'February', "03"=>'March', "04"=>'April', "05"=>'May', "06"=>'June', "07"=>'July', "08"=>'August', "09"=>'September', "10"=>'October', "11"=>'November', "12"=>'December');

$result = $db->query('SELECT r.`rID` AS "id", p.pName AS "product", c.name AS "country", r.`rMonth` AS "month", r.`rYear` AS "year", r.`rValue` AS "value" FROM `RevenueDetail` r, `Products` p, `Countries` c WHERE r.`pID` = p.`pID` AND r.rCountryID = c.id;')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    $data["data"][] = array(
        $row["country"],
        $row["product"],
        $row["year"],
        $date["monthName"][$row["month"]],
        $row["value"]
    );
}

echo json_encode($data);