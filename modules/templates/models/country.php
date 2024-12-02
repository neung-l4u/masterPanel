<?php
global $db;

include '../assets/db/db.php';
include "../assets/db/initDB.php";

//ประกาศตัวแปร
$param['act'] = (!empty($_REQUEST['act'])) ? trim($_REQUEST['act']) : '';
$param['token'] = (!empty($_REQUEST['token'])) ? trim($_REQUEST['token']) : '';

//ส่งค่ากลับ
$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];

if ( $param['act'] == 'readForSelectInput' ) {
    $countries = $db->query('SELECT * FROM `Countries` WHERE `status` = 1 ORDER BY `name`')->fetchAll();
    $row = array();
    foreach ($countries as $country) {
        $row[] = $country;
    }

    $return['result'] = 'success';
    $return['msg'] = count($row).' Countries found';
    $return['data'] = $row;
}

echo json_encode($return);