<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include "../assets/php/share_function.php";

$params['result'] = "";
$data[''] = "";

$data['projectID'] = !empty($_POST['projectID']) ? $_POST['projectID'] : null;
$data['loginID'] = !empty($_POST['loginID']) ? $_POST['loginID'] : null;
$data['page'] = !empty($_POST['page']) ? $_POST['page'] : "home";
$data['payload'] = !empty($_POST['payload']) ? $_POST['payload'] : null;
$data['mode'] = !empty($_POST['mode']) ? $_POST['mode'] : "";
$data['token'] = !empty($_POST['token']) ? $_POST['token'] : "no token";

$project = $db->query('SELECT * FROM `templatepagedetails` WHERE `projectID` = ?;', $data['projectID'])->fetchArray();

if (count($project) <= 0) {
    $insert = $db->query(
        'INSERT INTO `templatepagedetails` (`projectID`, `createBy`) VALUES (?, ?);',
        $data['projectID'],
        $data['loginID']
    );
} 

$update = $db->query(
    'UPDATE `templatepagedetails` SET `'.$data['page'].'` = ? WHERE `projectID` = ?;',
    json_encode($data['payload']),
    $data['projectID']
);

$params['result'] = $data['page'];

echo json_encode($params);
