<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDBvoucher.php";

$result = $db->query('SELECT * FROM voucherlogs ORDER BY create_at DESC')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    $date = $row["create_at"];

    $shopName = $row["shopName"];

    $json = json_decode($row["data"], true);
    $jsonText = json_encode($json, JSON_PRETTY_PRINT);

    $voucherLogsBtn = '<svg class="clickable" onclick="viewJson('.htmlspecialchars($row["data"]).')" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';
    
    $data["data"][] = array(
        $date,
        $shopName,
        $json["customerName"] . " " . $json["customerEmail"],
        $json["servicesName"],
        $json["voucherAmount"] . " " . $json["currency"],
        $voucherLogsBtn,
    );//array
}//foreach

echo json_encode($data);