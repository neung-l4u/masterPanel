<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";


$params["mode"] = !empty($_POST['mode']) ? $_POST['mode'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["password"] = !empty($_POST['password']) ? $_POST['password'] : "";
$salt = "L4U";

$passwordToDatabase = md5($salt.$params["password"]);

    if ($params["mode"] == "resetPassword"){
        $update = $db->query('UPDATE `staffs` SET `sPassword`= ? WHERE `sID` = ? ;'
            ,$passwordToDatabase, $params["id"] );
    }

echo json_encode($params);


