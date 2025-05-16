<?php
global $db;
include "../assets/db/db.php";
include "../assets/db/initDB.php";

header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$shopName = !empty($data["shopName"]) ? $data["shopName"] : null;

$result["result"] = "";
$result["msg"] = "";

$logsToDB = $db->query(
    'INSERT INTO `voucherLogs` (`shopName`, `data`) VALUES (?, ?)',
    $shopName, $json );

$return['result'] = 'success';
$return['msg'] = 'voucherLogs success';
// $return['shopName'] = $param['shopName'];

echo json_encode($return);