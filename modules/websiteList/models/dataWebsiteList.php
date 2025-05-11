<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$params["filterShopType"] = !empty($_REQUEST['shopType']) ? $_REQUEST['shopType'] : '';
$params["filterSystem"] = !empty($_REQUEST['system']) ? $_REQUEST['system'] : '';
$params["filterStatus"] = !empty($_REQUEST['fstatus']) ? $_REQUEST['fstatus'] : '';

$sql = 'SELECT * FROM `websitelist` WHERE  delete_at IS NULL ';
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
    $btn["URL"] = '<a href="'.$row["wWordpressURL"].'" target="_blank" title="WP-Admin"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg></a>';

    $data["data"][] = array(
        $i,
        dash($row["wProject"]),
        '<small>'.dashAndShort($row["wLocation"]).'</small>',
        '<small>'.dashAndShort($row["wOwner"]).'</small>',
        dash($row["wOwnerEmail"]),
        $btn["URL"].' '.$btn["detail"],

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