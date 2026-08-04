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

$projects = $db->query('SELECT pj.*,
                ct.name AS "country", ct.code AS "countryCode",
                st.name AS "projectType",
                sf.sNickName AS "PO", sf.sName AS "POFullName", sf.sEmail AS "POEmail",
                dp.name AS "hostingProvidersName"
        FROM `tb_project` pj
        JOIN `Countries` ct ON pj.`countryID` = ct.`id`
        JOIN `tb_shopType` st ON pj.`shopTypeID` = st.id
        JOIN `staffs` sf ON pj.`projectOwner` = sf.sID
        LEFT JOIN `DomainProviders` dp ON pj.`hostingProvidersID` = dp.id
        WHERE pj.`projectID` = ?;' ,$param['id'])->fetchArray();



$folderName = "upload/". $param['id'] . "-" . sanitizeFolderName($projects["projectName"])."/";


$projects['country'] = $projects['country'];
$projects['PO'] = $projects['PO'];
$projects['projectName'] = $projects['projectName'];
$projects['selectedTemplate'] = "Thai ".$projects['projectType']." No. ".$projects['selectedTemplate'];
$projects['shopType'] = "Thai ".$projects['projectType'];
$projects['template'] = $projects['selectedTemplate'];
$projects['projectID'] = $param['id'];
$projects['resources'] = "https://report.localforyou.com/modules/templates/".$folderName;
$projects['dueDate'] = $dueDate;
$projects['brief'] = "https://report.localforyou.com/pages/tpSubmittedDetails.php?projectID=".$param['id'];

if (empty($projects['projectCode'])){
    $projects['projectCode'] = "WEB-" . $projects['projectName'];

    $db->query('UPDATE `tb_project` SET `projectCode` = ?, `statusID` = 2 WHERE `projectID` = ?;', $projects['projectCode'], $param['id']);
}

echo json_encode($projects);