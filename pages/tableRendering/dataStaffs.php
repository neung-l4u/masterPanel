<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT s.sID, s.sEmail, s.sMobile, s.sName, s.sNickName, l.lName, s.sStatus FROM `staffs` s , `userLevel` l WHERE s.sDeleteAt IS NULL  AND s.sLevel = l.lID;')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    $on = '<a href="#" onclick="setStatus('.$row["sID"].','.$row["sStatus"].')"><i class="bi bi-toggle-on text-success"></i></a>';
    $off = '<a href="#" onclick="setStatus('.$row["sID"].','.$row["sStatus"].')"><i class="bi bi-toggle-off text-muted"></i></a>';

    $btn["status"] = ($row["sStatus"] == 1) ? $on : $off;
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["sID"].')"><i class="bi bi-pencil-square"></i></svg></a>';
    $btn["del"] = '<a href="#" onclick="setDel('.$row["sID"].')" class="ml-2"><i class="bi bi-trash text-danger"></i></a>';

    $data["data"][] = array(
        $btn["status"],
        showName($row["sNickName"],$row["sName"]),
        $row["lName"],
        $row["sEmail"],
        $row["sMobile"],
        $btn["edit"] . " " .$btn["del"]
    );
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//