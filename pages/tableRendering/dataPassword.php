<?php
session_start();
global $db;
$loginID = $_SESSION['id'];
$teamID = $_SESSION['teamID'];
$levelID = $_SESSION['level'];
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$teamCondition = "";

//(p.pwLevel >= ".$levelID." OR `pwTeam` IN (2,8,10,11,7,0))
if ($teamID==1){ //CS
    $teamCondition = " (pw.`pwTeam` IN (1,7)) OR "; //CS and other
} elseif (($teamID==2) or ($teamID==8) or ($teamID==10) or ($teamID==11)){ //AM > AU USA NZ UK
    $teamCondition = "( `pwTeam` IN (2,7,8,10,11)) OR "; //all AM and other
} elseif ($teamID==3){ //SE
    $teamCondition = " ( `pwTeam` IN (3,7)) OR "; //SE and other
} elseif (($teamID==4) or ($teamID==6)){ //HR or CEO
    $teamCondition = " ( `pwTeam` IN (4,6,7) ) OR "; //all AM and other
} elseif ($teamID==7){ //Other
    $teamCondition = " ( `pwTeam` = 7 ) OR "; //Other
} elseif ($teamID==5){ //IT
    $teamCondition = " ( `pwTeam` >= 0 ) OR "; //all
}
//$levelID

$result = $db->query('SELECT pw.`id`, pw.`pwName`, pw.`pwLink`, pw.`pwUser`, pw.`pwPass`, pw.`pwType`, te.`name` , ul.`lName` , pw.`pwNote`, pw.`pwShare` AS "share", pw.`pwLevel` AS "level"
                      FROM `passwordmanager`pw, `Team`te, `userLevel` ul
                      WHERE pw.`pwTeam` = te.`id`
                      AND pw.`pwLevel` = ul.lID
                      AND ('.$teamCondition.' (pw.`pwShare`=1));')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {

    $row["pwLink"] = (empty($row["pwLink"])) ? "-" : '<a href="'.$row["pwLink"].'" target="_blank"><img style="height: 1.2em;" src="assets/img/icons/link.png" alt="URL Link"></a>';

    $fullNote = (empty($row["pwNote"])) ? "-" : $row["pwNote"];

    if (empty($row["pwNote"])){
        $row["pwNote"] = "-";
    } else {
        $row["pwNote"] = '<abbr title="'.$row["pwNote"].'"><i class="far fa-eye"></i></abbr>';
    }

    if($levelID<=$row["level"]){
        $row["copyUser"] = '<input type="text" id="' . $row["pwUser"] . '" value="' . $row["pwUser"] . '" readonly style="position: absolute; left: -9999px;">
        <button onclick="copyText(\'' . $row["pwUser"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

        $row["copyPass"] = '<input type="text" id="' . $row["pwPass"] . '" value="' . $row["pwPass"] . '" readonly style="position: absolute; left: -9999px;">
        <button onclick="copyText(\'' . $row["pwPass"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

        $showUser = $row["copyUser"] . " " . $row["pwUser"];
        $showPass = $row["copyPass"] . " " . $row["pwPass"];
        $btn["edit"] = '<a href="#" onclick="setEdit('.$row["id"].')"><svg class="mr-3" xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 512 512"><path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z" fill="#0d6efd" /></svg></a>';
        $btn["del"] = '<a href="#" onclick="setDel('.$row["id"].')"><svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg></a>';
    }else{
        $showUser = '<span class="text-danger">Permission Denied</span>';

        $showPass = '<span class="text-danger">Permission Denied</span>';
        $btn["edit"] = '<svg class="mr-3" xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 512 512"><path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z" fill="#999999" /></svg>';
        $btn["del"] = '<svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#999999" /></svg>';
    }

    $typeLabel = $row["pwType"];



    $typeLabel = "<b>" .$row["name"].": ". $typeLabel . "</b>";
    $teamLevel = "<b>" . $row["lName"] . "</b>";

    $data["data"][] = array(
        
        $typeLabel,
        $teamLevel,
        $row["pwName"],
        $showUser,
        $showPass,
        $row["pwLink"],
        $row["pwNote"],
        $btn["edit"] . " " .$btn["del"]
    );
}

echo json_encode($data);