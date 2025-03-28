<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include_once "../assets/php/share_function.php";
//
date_default_timezone_set("Asia/Bangkok");
$date = date("y-m-d");
$dateFull = date("Y-m-d");
//$dateFull = "2025-03-14";
$timestamp = date("y-m-d H:i:s");

$cutday = explode("-","$date");

$month = $cutday[1];
$day = $cutday[2];
$year = $cutday[0];

$exday = $year.$month.$day;

$startDate = $date;
$businessDaysToAdd = 7;
$holidays = [];
$dueDate = addBusinessDays($startDate, $businessDaysToAdd, $holidays);
//End Due Date

$param['id'] = (!empty($_REQUEST['id'])) ? trim($_REQUEST['id']) : ''; //id

$projects = $db->query('SELECT pj.`projectName`, pj.`projectCode`,st.name AS "projectType", pj.`selectedTemplate`, ct.name AS "country", 
                sf.sNickName AS "PO"
        FROM `tb_project` pj , `Countries` ct , `tb_shopType` st, `staffs` sf
        WHERE pj.`projectID` = ? AND pj.`countryID` = ct.`id` AND pj.`shopTypeID` = st.id AND 
              pj.`projectOwner` = sf.sID ;' ,$param['id'])->fetchArray();



$folderName = "upload/". $param['id'] . "-" . sanitizeFolderName($projects["projectName"])."/";


$projects['selectedTemplate'] = "Thai ".$projects['projectType']." No. ".$projects['selectedTemplate'];
$projects['country'] = $projects['country'];
$projects['PO'] = $projects['PO'];
$projects['projectName'] = $projects['projectName'];
$projects['shopType'] = "Thai ".$projects['projectType'];
$projects['template'] = $projects['selectedTemplate'];
$projects['projectID'] = $param['id'];
$projects['resources'] = "https://report.localforyou.com/modules/templates/".$folderName;
$projects['dueDate'] = $dueDate;
$projects['brief'] = "https://report.localforyou.com/pages/tpSubmittedDetails.php?projectID=".$param['id'];

if (empty($projects['projectCode'])){
    $dataProject = $db->query('SELECT * FROM `tb_project` WHERE DATE(updateAt) = ?;' ,$dateFull)->fetchAll();
    $dateCount = Count($dataProject);
    $twoDigits = sprintf("%02d", $dateCount);
    $projects['projectCode'] = "WEB-" . $exday . "-" . $twoDigits . " " . $projects['projectName'];

    $db->query('UPDATE `tb_project` SET `projectCode` = ? WHERE `projectID` = ?;', $projects['projectCode'], $param['id']);
}

echo json_encode($projects);

