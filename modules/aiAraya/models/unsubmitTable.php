<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$result = $db->query('SELECT * FROM order_tracking WHERE statusUser = "pending" ORDER BY id DESC')->fetchAll();

$data = array("data"=> array());

$i = 1;

foreach ($result as $row) {
    $date = date("d-m-y H:i", strtotime($row["startedAt"]));
    $shopName = htmlspecialchars($row["shopName"] ?? "");
    $customerEmail = htmlspecialchars($row["customerEmail"] ?? "");
    $statusUser = htmlspecialchars($row["statusUser"] ?? "");

    $data["data"][] = array(
        $i,
        $shopName,
        $customerEmail,
        $statusUser,
        $date,
    );

    $i++;
}

echo json_encode($data);