<?php
global $db, $date;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_POST['action']) ? $_POST['action'] : 'no no';
$params["revCountry"] = !empty($_POST['revCountry']) ? $_POST['revCountry'] : 0;
$params["revProduct"] = !empty($_POST['revProduct']) ? $_POST['revProduct'] : 0;
$params["revMonth"] = !empty($_POST['revMonth']) ? $_POST['revMonth'] : 01;
$params["revYear"] = !empty($_POST['revYear']) ? $_POST['revYear'] : 2022;
$params["revValue"] = !empty($_POST['revValue']) ? $_POST['revValue'] : 0;
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