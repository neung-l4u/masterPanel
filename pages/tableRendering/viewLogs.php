<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT l.id, l.data, l.countryCode, l.createAt, s.status FROM logssignup l, logsstatus s WHERE l.status = s.id ORDER BY l.createAt DESC')->fetchAll();

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

    $json = json_decode($row["data"], true);
    $jsonText = json_encode($json, JSON_PRETTY_PRINT);

    $shopname = $json["ShopName"];
    
    $signupLogsBtn = '<button type="button" class="btn btn-primary" onclick="viewJson('.htmlspecialchars($row["data"]).');">View</button>';
    //$stripeLogsBtn = '<button type="button" class="btn btn-primary" onclick="viewJson('.htmlspecialchars($row["data"]).');">View</button>';

    $data["data"][] = array(
        $date,
        $country,
        $shopname,
        $signupLogsBtn,
        //$stripeLogsBtn,
        $row["status"]
    );
}

echo json_encode($data);