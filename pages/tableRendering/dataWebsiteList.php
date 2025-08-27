<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params = [
    "shopType" => !empty($_POST['shopType']) ? $_POST['shopType'] : '',
    "system"   => !empty($_POST['system'])   ? $_POST['system']   : '',
    "liveStatus"   => !empty($_POST['liveStatus'])  ? $_POST['liveStatus']  : '',
    "template" => !empty($_POST['template']) ? $_POST['template'] : '',
    "country"  => !empty($_POST['country'])  ? $_POST['country']  : '',
    "server"   => !empty($_POST['server'])   ? $_POST['server']   : '',
];

$sql   = "SELECT * FROM `websiteList` WHERE delete_at IS NULL";
$where = "";
$order = " ORDER BY wID DESC";

if ($params["shopType"] !== '') {
    $where .= " AND wIndustry = '".$params["shopType"]."'";
}

if ($params["system"] !== '') {
    if ($params["system"] === "AM") {
        $where .= " AND wSystemAmelia = 1";
    } elseif ($params["system"] === "GF") {
        $where .= " AND wSystemGloriaFood = 1";
    } elseif ($params["system"] === "VC") {
        $where .= " AND wSystemVoucher = 1";
    }
}
if ($params["liveStatus"] !== '') {
    $where .= " AND wLiveStatus = '".$params["liveStatus"]."'";
}

if ($params["template"] !== '') {
    $where .= " AND wTemplateUsed = '".$params["template"]."'";
}

if ($params["country"] !== '') {
    $where .= " AND wCountry = '".$params["country"]."'";
}

if ($params["server"] !== '') {
    $where .= " AND svID = '".$params["server"]."'";
}

$sql = $sql . $where . $order;
$result = $db->query($sql)->fetchAll();

$data = array("data"=> array());

$i=1;
foreach ($result as $row) {
    $No = $row["wID"];
    $statusWebsite = $row["wLiveStatus"];
    $url = '<a href="'.$row["wDomain"].'" target="_blank" title="WP-Link">'.$row["wDomain"].'</a>';
    $btn["URL"] = '<a href="'.$row["wWordpressURL"].'" target="_blank" title="WP-Admin"><i class="bi bi-box-arrow-up-right"></i></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail"><i class="bi bi-file-earmark-text"></i></a>';
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["wID"].')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btn["delete"] = '<a href="#" onclick="setDel('.$row["wID"].')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';
    
    $data["data"][] = array(
        $i,
        '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail" class="linkDetail">'.dash($row["wProject"]).'</a>',
        $url,
        $statusWebsite,
        $btn["URL"]." ".$btn["edit"]." ".$btn["delete"]
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