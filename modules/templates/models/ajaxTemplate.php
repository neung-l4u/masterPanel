<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include "../assets/php/share_function.php";

$params['result'] = "";
$data[''] = "";

$data['projectID'] = !empty($_REQUEST['projectID']) ? $_REQUEST['projectID'] : null;
$data['loginID'] = !empty($_REQUEST['loginID']) ? $_REQUEST['loginID'] : null;
$data['page'] = !empty($_REQUEST['page']) ? $_REQUEST['page'] : "home";
$data['payload'] = !empty($_REQUEST['payload']) ? $_REQUEST['payload'] : null;
$data['mode'] = !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
$data['token'] = !empty($_REQUEST['token']) ? $_REQUEST['token'] : "no token";

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
