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
    $shopType = $db->query('SELECT `id`, `name` FROM `tb_shopType` WHERE status = ? ORDER BY `id`',1)->fetchAll();
    $row = array();
    foreach ($shopType as $type) {
        $row[] = $type;
    }

    $return['result'] = 'success';
    $return['msg'] = count($row).' Shop type found';
    $return['data'] = $row;
}

echo json_encode($return);