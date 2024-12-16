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
    $shortNote = mb_substr($fullNote, 0, 10) . "-" . '...';
    if ($fullNote == "-"){
        $row["pwNote"] = $fullNote;
    } else {
        $row["pwNote"] = "<abbr title='$fullNote'>" . $shortNote . "</abbr>";
    }

    $row["copyUser"] = '<input type="text" id="' . $row["pwUser"] . '" value="' . $row["pwUser"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwUser"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

    $row["copyPass"] = '<input type="text" id="' . $row["pwPass"] . '" value="' . $row["pwPass"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwPass"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

    $typeLabel = ($row["pwType"] == 1) ? "Internal" : (($row["pwType"] == 2) ? "Client" : "0");
    if ($typeLabel === "0") {continue;}

    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["id"].')"><svg class="mr-3" xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 512 512"><path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z" fill="#0d6efd" /></svg></a>';
    $btn["del"] = '<a href="#" onclick="setDel('.$row["id"].')"><svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg></a>';

    $typeLabel = "<b>" . $typeLabel . "</b>";
    $teamLevel = "<b>" .  $row["name"] . " : " . $row["lName"] . "</b>";

    $data["data"][] = array(
        
        $typeLabel,
        $teamLevel,
        $row["pwName"],
        $row["pwLink"],
        $row["copyUser"] . " " . $row["pwUser"],
        $row["copyPass"] . " " . $row["pwPass"],
        $row["pwNote"],
        $btn["edit"] . " " .$btn["del"]
    );
}

echo json_encode($data);