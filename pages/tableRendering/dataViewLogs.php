<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT l.id, l.dataLogs, l.dataStripe, l.dataContract, l.countryCode, l.createAt, s.status FROM logssignup l, logsstatus s WHERE l.status = s.id ORDER BY l.createAt DESC')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    // $data = $row["data"];
    // $shortData = shorten($data, 10);
    $date = $row["createAt"];

    switch ($row["countryCode"]) {
        case "AU":
            $country = "Australia";
            break;
        case "NZ":
            $country = "New Zealand";
            break;
        case "UK":
            $country = "United Kingdom";
            break;
        case "CA":
            $country = "CANADA";
            break;
        case "US":
            $country = "United States";
            break;
        case "TH":
            $country = "Thailand";
            break;
        default:
            $country = $row["countryCode"];
            break;
    }

    if (empty($row["dataLogs"])) {

    }

    $json = json_decode($row["dataLogs"], true);
    $jsonText = json_encode($json, JSON_PRETTY_PRINT);

    $shopname = $json["ShopName"];
    $contractURL = $row["dataContract"];

    $signupLogsBtn = '<svg class="clickable" onclick="viewJson('.htmlspecialchars($row["dataLogs"]).');" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';
    $stripeLogsBtn = '<svg class="clickable" onclick="viewJson('.htmlspecialchars($row["dataStripe"]).');" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';
    $contractLogsBtn = (empty($contractURL)) ? "-" : '<a href="'.$contractURL.'" target="_blank"><img style="height: 1.2em;" src="assets/img/icons/link.png" alt="URL Link"></a>';

        $data["data"][] = array(
        $date,
        $country,
        $shopname,
        $signupLogsBtn,
        $stripeLogsBtn,
        $contractLogsBtn,
        $row["status"]
    );
}

echo json_encode($data);