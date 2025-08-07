<?php

global $db;
include "../assets/db/db.php";
include "../assets/db/initDB.php";

header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$event = $data["event"] ?? null;
$shop_name = $data["shop_name"] ?? null;
$store_name = $data["store_name"] ?? null;

$result["result"] = "";
$result["msg"] = "";

if ($event === 'payment_success') {
    $create = $db->query(
            'INSERT INTO `voucherLogs` (`shopName`, `dataShop`) VALUES (?, ?)',
            $shop_name, $json
    );
} elseif ($event === 'coupon_generation_success') {
    $select = $db->query(
        'SELECT * FROM `voucherLogs` WHERE `shopName` = ? AND `status` = ? ORDER BY `id` DESC LIMIT 1',
        $store_name, "0"
    )->fetchArray();

    if (is_array($select) && isset($select['id'])) {
    $update = $db->query(
        'UPDATE `voucherLogs` SET `status` = ?, `dataVoucher` = ? WHERE `id` = ?',
        "1", $json, $select['id']
    );
}
}

$return['event'] = $data["event"];
$return['shop_name'] = $data["shop_name"];
$return['store_name'] = $data["store_name"];
$return['result'] = 'success';
$return['msg'] = 'voucherLogs success';

echo json_encode($return);