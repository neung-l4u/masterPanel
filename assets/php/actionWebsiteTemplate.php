<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_POST['editID']) ? $_POST['editID'] : "";
$params["by"] = $_SESSION['id'];
$params["templateWebsite"] = !empty($_POST['templateWebsite']) ? $_POST['templateWebsite'] : "1";
$params["linkwebsite"] = !empty($_POST['linkwebsite']) ? $_POST['linkwebsite'] : "https://www.localforyou.com/";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

if ($params ["action"] == "loadUpdate"){
    $row = $db->query('SELECT `id`, `wpTemplate`, `wpLinkCM` FROM `WebsiteTemplateDetail` WHERE id = ?;',$params ["id"])->fetchArray();
    $params["editID"] = $row["id"];
    $params["templateWebsite"] = $row["wpTemplate"];
    $params["linkwebsite"] = $row["wpLinkCM"];

}elseif ($params ["action"] == "save"){
    $params["txt"] = "Got it";


    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `WebsiteTemplateDetail`
                                (`wpTemplate`, `wpLinkCM`, `wpCrateBy`) 
                                VALUES (?, ?, ?);'
            ,$params["templateWebsite"],$params["linkwebsite"],$params["by"]
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){
        $update = $db->query('UPDATE `WebsiteTemplateDetail` SET 
                                `wpTemplate`= ?, `wpLinkCM` = ?, `wpCrateBy` =?,`wpUpdateBy`= ?, wpUpdateAt = NOW() 
                                WHERE id = ? ;'
            ,$params["templateWebsite"],$params["linkwebsite"],$params["by"],$params["by"],$params ["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

};

if ($params ["action"] == "setDelete"){
    $delete = $db->query('UPDATE `WebsiteTemplateDetail` SET `wpDeleteAt` = NOW(), `wpDeleteBy` = ? WHERE id = ?;', $_SESSION['id'], $params ["id"]);
    $params["success"] = $delete->affectedRows() > 0;
}

echo json_encode($params);