<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include "../../assets/php/shareFunction.php";

$result = $db->query('SELECT pj.saveFlag, pj.projectID, pj.projectName, t.name AS "shopType", pj.selectedTemplate, pj.statusID, s.sNickName AS "owner", c.name AS "countryName", c.code AS "countryCode", pj.projectTimestamp, 
                                   pd.home AS "homePage", pd.about AS "aboutPage", pd.services AS "servicesPage", pd.contact AS "contactPage", s.sNickName AS "PO", pj.updateAt AS "updateAt"
                            FROM `tb_project` pj 
                            LEFT JOIN `templatepagedetails` pd ON pj.projectID = pd.projectID 
                            LEFT JOIN `Countries` c ON pj.countryID = c.id
                            LEFT JOIN `staffs` s ON pj.projectOwner = s.sID
                            LEFT JOIN `tb_shopType` t ON pj.shopTypeID = t.id
                            WHERE pj.deleteAt IS NULL;')->fetchAll();

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$cutday = explode("-","$date");

$month = $cutday[1];
$day = $cutday[2];
$year = $cutday[0];

$exday = $year.$month.$day;

$startDate = $date;
$businessDaysToAdd = 7;
$holidays = [];

//$project = $db->query('');

$data = array("data"=> array());

foreach ($result as $row) {
    // $data = $row["data"];
    // $shortData = shorten($data, 10);

    $submitDate = $row["updateAt"];
    $startDate = date('Y-m-d', strtotime($submitDate));
    //$dueDate = addBusinessDays($submitDate, $businessDaysToAdd, $holidays);
    $dueDate = calculateDueDate($startDate);

    $temPage = ($row["shopType"] === "Restaurant") ? 'Restaurant' : 'Massage';
    $temPage = $temPage." ".$row["selectedTemplate"];

    $details = '<a href="pages/tpSubmittedDetails.php?projectID='.$row["projectID"].'" target="_blank"><svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" class="mr-3" height="1.2em" version="1.1" viewBox="0 0 100 99.7"><path d="M80,47.3c-2.4,0-4.4,2-4.4,4.4v37.1H11.2V24.4h37.1c2.4,0,4.4-2,4.4-4.4s-2-4.4-4.4-4.4H6.9c-2.4,0-4.4,2-4.4,4.4v73.1c0,2.4,2,4.4,4.4,4.4h73.1c2.4,0,4.4-2,4.4-4.4v-41.5c0-2.4-2-4.3-4.4-4.3Z"/><path d="M93.1,2.5h-26.3c-2.4,0-4.4,2-4.4,4.4s2,4.4,4.4,4.4h15.8l-45,45c-1.7,1.7-1.7,4.5,0,6.2s2,1.3,3.1,1.3,2.2-.4,3.1-1.3l45-45v15.8c0,2.4,2,4.4,4.4,4.4s4.4-2,4.4-4.4V6.9c-.1-2.4-2.1-4.4-4.5-4.4Z"/></a>' ;

        $data["data"][] = array(
        $dueDate,
        $temPage,
        $row["countryCode"]." : ". $row["projectName"],
        $details,
        $row["PO"]
    );
}

echo json_encode($data);