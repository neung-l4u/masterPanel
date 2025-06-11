<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["filterShopType"] = !empty($_POST['shopType']) ? $_POST['shopType'] : '';
$params["filterSystem"] = !empty($_POST['system']) ? $_POST['system'] : '';
$params["filterStatus"] = !empty($_POST['fstatus']) ? $_POST['fstatus'] : '';

$sql = 'SELECT * FROM `websiteList` WHERE  delete_at IS NULL ';
$where1 = "";
$where2 = "";
$where3 = "";
$order = " ORDER BY wID DESC";

// filter

if (!empty($params["filterShopType"])){
    $where1 = " AND wIndustry = '".$params["filterShopType"]."'";
}
if (!empty($params["filterSystem"])){
    if ($params["filterSystem"] === "AM") {
        $where2 = " AND wSystemAmelia = 1";
    } else if ($params["filterSystem"] === "GF") {
        $where2 = " AND wSystemGloriaFood = 1";
    } else if ($params["filterSystem"] === "VC") {
        $where2 = " AND wSystemVoucher = 1";
    }
}
if (!empty($params["filterStatus"])){
    $where3 = " AND wLiveStatus = '".$params["filterStatus"]."'";
}


$sql = $sql . $where1 . $where2 . $where3 . $order;
$result = $db->query($sql)->fetchAll();

$data = array("data"=> array());

$i=1;
foreach ($result as $row) {
    $No = $row["wID"];
    $btn["URL"] = '<a href="'.$row["wWordpressURL"].'" target="_blank" title="WP-Admin"><i class="bi bi-box-arrow-up-right"></i></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail"><i class="bi bi-file-earmark-text"></i></a>';
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["wID"].')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btn["delete"] = '<a href="#" onclick="setDel('.$row["wID"].')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';
    
    $data["data"][] = array(
        $i,
        dash($row["wProject"]),
        '<small>'.dashAndShort($row["wLocation"]).'</small>',
        '<small>'.dashAndShort($row["wOwner"]).'</small>',
        dash($row["wOwnerEmail"]),
        $btn["URL"]." ".$btn["detail"]." ".$btn["edit"]." ".$btn["delete"]
    );
    $i++;
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//

function dash($param){
    if (empty($param)) { return "-"; }
    else {
        return $param;
    }
}

function dashAndShort($param): string
{
    if (empty($param)) { return "-"; }
    else {
        $location = mb_substr($param, 0, 15).'...';
        return '<abbr title="'.$param.'">'.$location.'</abbr>';
    }
}