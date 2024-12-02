<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$total = 0;

$data["IHD20AU"] = 0;
$data["IHDMarketAU"] = 0;
$data["IHD25USA"] = 0;
$data["IHDMarketUSA"] = 0;
$data["total"] = 0;

$type[1] = 0;
$type[2] = 0;
$type[3] = 0;
$type[4] = 0;

if ($params ["action"] == "getIHD"){
    $result = $db->query('SELECT ID.`id`, ID.`typeID`, IT.`name`, ID.`month`, ID.`year`, ID.`value` FROM `IHDDetail` ID, `IHDType` IT WHERE ID.`year`=? AND ID.`month`=? AND ID.typeID = IT.id;', $params ["year"], $params ["month"] )->fetchAll();
    $data["found"] = count($result);

    if ($data["found"]>0){
        foreach ($result as $row) {
            if ($row["typeID"] == 1){ $type[1] = $row["value"]; }
            if ($row["typeID"] == 2){ $type[2] = $row["value"]; }
            if ($row["typeID"] == 3){ $type[3] = $row["value"]; }
            if ($row["typeID"] == 4){ $type[4] = $row["value"]; }
        } //foreach
    }
}

$data["IHD20AU"] = "$".number_format(($type[1]*0.2),2);
$data["IHDMarketAU"] = "$".number_format(($type[2]*1.0),2);
$data["IHD25USA"] = "$".number_format(($type[3]*0.25),2);
$data["IHDMarketUSA"] = "$".number_format(($type[4]*1.0),2);
$data["total"] = "$".number_format((($type[1]*0.2) + ($type[2]*1.0) + ($type[3]*0.25) + ($type[4]*1.0)),2);

echo json_encode($data);
