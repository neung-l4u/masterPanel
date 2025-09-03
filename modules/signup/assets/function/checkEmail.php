<?php
global $db;
include '../db/db.php';
include "../db/initDB_stripe.php";

$result["result"] = "";

$email = !empty($_POST["email"]) ? $_POST["email"] : null;

$findEmail = $db->query('SELECT email FROM `users` WHERE `email` LIKE ?', '%' . $email . '%')->fetchAll();
$rowCount  = count($findEmail);

if ($rowCount > 0) {
    $result["result"] = "used";
} else {
    $result["result"] = "unused";
}

echo json_encode($result);