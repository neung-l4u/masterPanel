<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["filterInactive"] = !empty($_POST['includeInactive']) ? 1 : 0;

if($params["filterInactive"] == 1){
    $result = $db->query('SELECT s.sID, s.sEmail, s.sMobile, s.sName, s.sNickName, l.lName, s.sStatus FROM `staffs` s , `userLevel` l WHERE s.sDeleteAt IS NULL  AND s.sLevel = l.lID;')->fetchAll();
}else{
    $result = $db->query('SELECT s.sID, s.sEmail, s.sMobile, s.sName, s.sNickName, l.lName, s.sStatus FROM `staffs` s , `userLevel` l WHERE s.sDeleteAt IS NULL  AND s.sLevel = l.lID AND sStatus=1;')->fetchAll();
}


$data = array("data"=> array());

foreach ($result as $row) {
    $on = '<a href="#" onclick="setStatus('.$row["sID"].','.$row["sStatus"].')"><i class="bi bi-toggle-on text-success"></i></a>';
    $off = '<a href="#" onclick="setStatus('.$row["sID"].','.$row["sStatus"].')"><i class="bi bi-toggle-off text-muted"></i></a>';

    $btn["status"] = ($row["sStatus"] == 1) ? $on : $off;
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["sID"].')"><i class="bi bi-pencil-square"></i></svg></a>';
    $btn["del"] = '<a href="#" onclick="setDel('.$row["sID"].')" class="ml-2"><i class="bi bi-trash text-danger"></i></a>';

    $data["data"][] = array(
        $btn["status"],
        '<i class="bi bi-person"></i>&nbsp;'.showName($row["sNickName"],$row["sName"]),
        '<i class="bi bi-person-circle"></i>&nbsp;'.$row["lName"],
        '<i class="bi bi-envelope"></i>&nbsp;'.$row["sEmail"],
        '<i class="bi bi-telephone"></i>&nbsp;'.$row["sMobile"],
        $btn["edit"] . " " .$btn["del"]
    );
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//