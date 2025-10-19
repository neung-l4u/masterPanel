<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
$id = $_POST['staff'];

$staff = $db->query('SELECT s.`sNickName` AS "nick", s.`sName` AS "full", s.`sNationality` AS "national", s.`teamID` AS "team", t.fullName AS "teamName", t.manager FROM `staffs` s LEFT JOIN `Team` t ON s.`teamID` = t.`id` WHERE s.sID = ?;',$id)->fetchArray();

$teams = [
    1 => "Customer Support",
    2 => "Account Manager",
    10 => "Account Manager",
    11 => "Account Manager",
    8 => "Account Manager",
    3 => "Sales",
    4 => "Human Resource",
    5 => "IT",
    12 => "Marketing",
    13 => "House Keeping"
];

$managers = [
    100 => "Aunyarut Aunyarut",
    13 => "Aom Kunrisa",
    16 => "Bee Kevalee",
    17 => "Boom Piyakorn",
    42 => "San Papawadee",
    45 => "Steve Waterson",
    49 => "Yok Nattiya",
    83 => "Patt Pattranit",
    100 => "Aunya Aunyarut"
];

$managers2 = [
    100 => "82961537",
    13 => "57652194",
    16 => "61031099",
    17 => "57649130",
    42 => "57717609",
    45 => "57647868",
    49 => "57650527",
    83 => "75103676",
    100 => "82961537"
];

//$data['staff'] = $staff;
$data['result'] = true;
$data['staffID'] = $id;


$data['staffName'] = showName($staff['nick'], $staff['full'], $staff['national']);
$data['team'] = $teams[$staff['team']];
$data['manager'] = $managers2[$staff['manager']];
$data['teamName'] = $staff['teamName'];

echo json_encode($data);

function showName($nick="", $full="", $nationality=""): string
{
    if($nationality == "Thai") {
        $temp = explode(" ", $full);
        return $nick.' '.$temp[0];
    }else{
        return $full;
    }
}