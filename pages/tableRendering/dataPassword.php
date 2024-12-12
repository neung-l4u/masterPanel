<?php
session_start();
global $db;
$loginID = $_SESSION['id'];
$teamID = $_SESSION['teamID'];
$levelID = $_SESSION['level'];
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT p.id, p.pwName, p.pwLink, p.pwUser, p.pwPass, p.pwNote, l.lName, p.pwLevel, t.name, p.pwTeam, p.pwType
                      FROM `passwordmanager` p, `userLevel` l, `team` t
                      WHERE p.pwLevel = l.lID 
                      AND p.pwTeam = t.id
                      AND p.pwLevel >= ?
                      AND (p.pwTeam = ? OR p.pwTeam = 0);
                      ', $levelID, $teamID)->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {

    $row["pwLink"] = ($row["pwLink"] == null) ? "-" : '<a href="'.$row["pwLink"].'" target="_blank">'.$row["pwLink"].'</a>';
    
    $fullNote = $row["pwNote"] = ($row["pwNote"] == null) ? "-" : $row["pwNote"];
    $shortNote = mb_substr($fullNote, 0, 14) . "-" . '...';
    if ($fullNote == "-"){
        $row["pwNote"] = $fullNote;
    } else {
        $row["pwNote"] = "<abbr title='$fullNote'>" . $shortNote . "</abbr>";
    }

    $row["copyUser"] = '<input type="text" id="' . $row["pwUser"] . '" value="' . $row["pwUser"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwUser"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

    $row["copyPass"] = '<input type="text" id="' . $row["pwPass"] . '" value="' . $row["pwPass"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwPass"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

    $typeLabel = ($row["pwType"] == 1) ? "Internal" : (($row["pwType"] == 2) ? "Client" : "");

    $data["data"][] = array(
        "<b>" . $typeLabel . "</b>",
        "<b>" .  $row["name"] . " : " . $row["lName"] . "</b>",
        $row["pwName"],
        $row["pwLink"],
        $row["copyUser"] . " " . $row["pwUser"],
        $row["copyPass"] . " " . $row["pwPass"],
        $row["pwNote"]
    );
}

echo json_encode($data);