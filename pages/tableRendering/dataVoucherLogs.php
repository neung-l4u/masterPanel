<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDBvoucher.php";

$result = $db->query('SELECT * FROM voucherLogs ORDER BY create_at DESC')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    $date = $row["create_at"];
    $shopName = $row["shopName"];

    $jsonShop = json_decode($row["dataShop"], true);
    $jsonVoucher = json_decode($row["dataVoucher"], true);
    $jsonTextShop = json_encode($jsonShop, JSON_PRETTY_PRINT);
    $jsonTextVoucher = json_encode($jsonVoucher, JSON_PRETTY_PRINT);

    // $shopLogsBtn = '<svg class="clickable" onclick="viewJsonShop('.htmlspecialchars($row["dataShop"]).')" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';
    // $voucherLogsBtn = '<svg class="clickable" onclick="viewJsonVoucher('.htmlspecialchars($row["dataVoucher"]).')" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';

    $shopLogsBtn = '<i class="bi bi-shop-window clickable mr-3" onclick="viewJsonShop('.htmlspecialchars($row["dataShop"]).')" style="font-size:20px;"></i>';
    $voucherLogsBtn = '<i class="bi bi-ticket-detailed clickable" onclick="viewJsonVoucher('.htmlspecialchars($row["dataVoucher"]).')" style="font-size:20px;"></i>';

    $data["data"][] = array(
        $date,
        $shopName,
        $jsonShop["customer_name"] . " : " . $jsonShop["customer_email"],
        $jsonShop["recipent_name"] . " : " . $jsonShop["receipient_email"],
        $jsonShop["total_amount"] . " " . $jsonShop["currency"],
        $shopLogsBtn . $voucherLogsBtn
    );//array
}//foreach

echo json_encode($data);