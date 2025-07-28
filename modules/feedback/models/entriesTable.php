<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$result = $db->query('SELECT * FROM feedback')->fetchAll();

$data = array("data"=> array());

$i = 1;

foreach ($result as $row) {
    $date = date("d-m-y H:i", strtotime($row["createAt"]));
    $dateOnly = explode(' ', $date)[0];
    $timeOnly = explode(' ', $date)[1];

    $json = json_decode($row["dataLogs"], true);
    $cusName = $json["name"];
    $shopName = $json["shopName"];
    $email = $json["email"];
    $shopType = $json["shopType"];
    $description = $json["description"];

    $fileName = $json["fileName"];
    $filePath = "../" . $json["filePath"];

    if (empty($fileName)) {
        $attachFile = "-";
    } else {
        $attachFile = "<a href='$filePath' target='_blank'><i class='bi bi-image'></i></a>";
    }

    if ($json["package"] == "other") {
        $package = $json["otherInput"];
    } else {
        $package = $json["package"];
    }


    $detail = array(
        "name" => $cusName,
        "shopName" => $shopName,
        "email" => $email,
        "shopType" => $shopType,
        "package" => $package,
        "description" => $description,
        "attachFile" => $attachFile,
        "date" => $dateOnly,
        "time" => $timeOnly,
    );
    $detail = htmlspecialchars(json_encode($detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES);

    $detailBtn = "<a class='viewDetail' onclick='viewDetail(" . $detail . ")'><i class='bi bi-file-earmark-text'></i></a><h3 class='d-none'>" . $email . "</h3>";

    $data["data"][] = array(
        $i,
        $cusName,
        $shopName,
        $shopType,
        $package,
        $detailBtn,
        $dateOnly,
    );//array

    $i++;
}//foreach

echo json_encode($data);