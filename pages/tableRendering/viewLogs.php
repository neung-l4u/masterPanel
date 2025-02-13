<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT * FROM `logssignup`')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    // $data = $row["data"];
    // $shortData = shorten($data, 10);

    $json = json_decode($row["data"], true);

    $viewLogsBtn = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" onclick="viewJson('.$json.')">View</button>';


    $data["data"][] = array(
        $row["id"],
        $row["countryCode"],
        $row["data"].$viewLogsBtn,
        $row["status"]
    );
}

echo json_encode($data);