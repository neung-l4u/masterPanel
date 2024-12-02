<?php
global $db, $date;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'no no';
$params["revCountry"] = !empty($_REQUEST['revCountry']) ? $_REQUEST['revCountry'] : 0;
$params["revProduct"] = !empty($_REQUEST['revProduct']) ? $_REQUEST['revProduct'] : 0;
$params["revMonth"] = !empty($_REQUEST['revMonth']) ? $_REQUEST['revMonth'] : 01;
$params["revYear"] = !empty($_REQUEST['revYear']) ? $_REQUEST['revYear'] : 2022;
$params["revValue"] = !empty($_REQUEST['revValue']) ? $_REQUEST['revValue'] : 0;
//$params["revTest"] = $_POST['revValue'];

$params['page'] = 'actionRevenueRestaurant.php';
$params['message'] = 'Success.php';

$insert = $db->query('INSERT INTO `RevenueDetail`
                                (`pID`, `rCountryID`, `rMonth`, `rYear`, `rValue`, `rCreateBy`) 
                                VALUES (?, ?, ?, ?, ?, ?);'
    ,$params["revProduct"],$params["revCountry"],$params["revMonth"],$params["revYear"],$params["revValue"],1
);

$params["affected"] = $insert->affectedRows();
$params["insertedID"] = $db->lastInsertID();

echo json_encode($params);