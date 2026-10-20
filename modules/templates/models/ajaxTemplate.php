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
$data['page'] = !empty($_POST['page']) ? strtolower($_POST['page']) : "home";
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

// ✅ เพิ่มส่วนนี้: ถ้า mode เป็น "read" ให้ดึงข้อมูลจาก database
if ($data['mode'] === 'read') {
    // ดึงข้อมูลจาก database
    $project = $db->query('SELECT * FROM `templatepagedetails` WHERE `projectID` = ?;', $data['projectID'])->fetchArray();
    
    if (!empty($project[$data['page']])) {
        // ถ้ามีข้อมูลให้ decode JSON
        $params['result'] = 'success';
        $params['data'] = json_decode($project[$data['page']], true);
    } else {
        // ถ้าไม่มีข้อมูลให้ส่ง empty object
        $params['result'] = 'success';
        $params['data'] = null;
    }
} else {
    // ถ้า mode เป็น "save" ให้บันทึกข้อมูล
    $update = $db->query(
        'UPDATE `templatepagedetails` SET `'.$data['page'].'` = ? WHERE `projectID` = ?;',
        json_encode($data['payload']),
        $data['projectID']
    );

    $params['result'] = $data['page'];
}

echo json_encode($params);
