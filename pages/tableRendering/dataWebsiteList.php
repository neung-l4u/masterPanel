<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

$params = [
    "shopType" => !empty($_POST['shopType']) ? $_POST['shopType'] : '',
    "system"   => !empty($_POST['system'])   ? $_POST['system']   : '',
    "liveStatus"   => !empty($_POST['liveStatus'])  ? $_POST['liveStatus']  : '',
    "template" => !empty($_POST['template']) ? $_POST['template'] : '',
    "country"  => !empty($_POST['country'])  ? $_POST['country']  : '',
    "server"   => !empty($_POST['server'])   ? $_POST['server']   : '',
];

$qb = new QueryBuilder();
$qb->eq('w.wIndustry', $params["shopType"]);

if ($params["system"] === "AM")      $qb->raw("AND w.wSystemAmelia = 1");
elseif ($params["system"] === "GF")  $qb->raw("AND w.wSystemGloriaFood = 1");
elseif ($params["system"] === "VC")  $qb->raw("AND w.wSystemVoucher = 1");

$qb->eq('w.wLiveStatus', $params["liveStatus"])
   ->eq('w.wTemplateUsed', $params["template"])
   ->eq('w.countryID', $params["country"])
   ->eq('sv.svID', $params["server"]);

$baseSql = "SELECT * FROM websiteList w LEFT JOIN L4UServers sv ON w.svID = sv.svID WHERE delete_at IS NULL";
$result = $qb->execute($db, $baseSql, 'ORDER BY w.wID DESC')->fetchAll();

$data = array("data"=> array());

$i=1;
foreach ($result as $row) {
    $No = $row["wID"];
    $statusWebsite = !empty($row["wLiveStatus"]) ? $row["wLiveStatus"] : '-';
    $url = '<a href="'.escUrl($row["wDomain"]).'" target="_blank" title="WP-Link">'.esc($row["wDomain"]).'</a>';
    $link = !empty($row["wDomain"]) ? $url : '-';
    $server = $row['svName'] ?? '-';
    $btn["URL"] = '<a href="'.escUrl($row["wWordpressURL"]).'" target="_blank" title="WP-Admin"><i class="bi bi-box-arrow-up-right"></i></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail"><i class="bi bi-file-earmark-text"></i></a>';
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["wID"].')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btn["delete"] = '<a href="#" onclick="setDel('.$row["wID"].')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';
    
    $data["data"][] = array(
        $i,
        '<a href="#" onclick="viewDetail('.intval($row["wID"]).')" title="Detail" class="linkDetail">'.esc(dash($row["wProject"])).'</a>',
        $link,
        $server,
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
        return '<abbr title="'.esc($param).'">'.esc($location).'</abbr>';
    }
}