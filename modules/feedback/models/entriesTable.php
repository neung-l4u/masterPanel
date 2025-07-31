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

    $fullDesc = $json["description"];
    $shortDesc = shortDesc($fullDesc);

    $fileName = $json["fileName"];
    //$modulePath = "../"; // Local path
    $modulePath = "https://report.localforyou.com/modules/feedback"; // Server path
    $filePath = $modulePath . $json["filePath"];

    if (empty($fileName)) {
        $attachFile = "-";
    } else {
        $attachFile = "<a href='$filePath' target='_blank'><i class='bi bi-image'></i></a><p class='d-none'>$filePath</p>";
    }

    if ($json["package"] == "Other") {
        $package = "Other: ".$json["otherInput"];
    } else {
        $package = $json["package"];
    }

    // $detail = array(
    //     "name" => $cusName,
    //     "shopName" => $shopName,
    //     "email" => $email,
    //     "shopType" => $shopType,
    //     "package" => $package,
    //     "description" => $description,
    //     "attachFile" => $attachFile,
    //     "date" => $dateOnly,
    //     "time" => $timeOnly,
    // );
    // $detail = htmlspecialchars(json_encode($detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES);

    //$detailBtn = "<a class='viewDetail' onclick='viewDetail(" . $detail . ")'><i class='bi bi-file-earmark-text'></i></a><h3 class='d-none'>" . $email . "</h3>";

    $data["data"][] = array(
        $i,
        $cusName,
        $shopName,
        $email,
        $shopType,
        $package,
        $shortDesc,
        $fullDesc,
        $attachFile,
        $dateOnly,
    );//array

    $i++;
}//foreach

echo json_encode($data);

function shortDesc($param): string
{
    $desc = mb_substr($param, 0, 20).'...';
    return "<p class='description' data-bs-toggle='tooltip' title='$param'>$desc</p>";
}