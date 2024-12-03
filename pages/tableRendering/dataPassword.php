<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT p.id, p.pwName, p.pwLink, p.pwUser, p.pwPass, p.pwNote, l.lName FROM `passwordmanager` p , `userLevel` l WHERE  p.pwLevel = l.lID;')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {

    if($row["pwLink"] == null){
        $row["pwLink"] = "-";
    }else{
        $row["pwLink"] = '<a href="'.$row["pwLink"].'">'.$row["pwLink"].'</a>';
    }

    if($row["pwNote"] == null){
        $row["pwNote"] = "-";
    }else{
        $row["pwNote"];
    }

    $data["data"][] = array(
        $row["pwName"],
        $row["pwLink"],
        $row["pwUser"],
        $row["pwPass"],
        $row["pwNote"],
    );
}

echo json_encode($data);