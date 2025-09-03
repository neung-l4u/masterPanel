<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT s.sID, s.sName, s.sNickName, s.ext, t.name AS "team" FROM `staffs` s, `team` t WHERE s.sDeleteAt IS NULL AND s.teamID = t.id AND sStatus=1;')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {
    $extJson = $json = json_decode($row["ext"], true);
    $zoomExt = $json["ext"];
    $zoomLicense = !empty($json["license"]) ? $json["license"] : "-";
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["sID"].')"><i class="bi bi-pencil-square"></i></svg></a>';

    $data["data"][] = array(
        ++$i,
        '<i class="bi bi-person"></i>&nbsp;'.showName($row["sNickName"],$row["sName"]),
        $row["team"],
        $zoomExt,
        $zoomLicense,
        $btn["edit"]
    );
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//