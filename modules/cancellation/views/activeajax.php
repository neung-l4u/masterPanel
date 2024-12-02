<?php
// global $db;
// session_start();
// include '../../assets/db/db.php';
// include "../../assets/db/initDB.php";

$result = array(
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0
);

$param = array(
    "mode" => !empty($_REQUEST["mode"]) ? $_REQUEST["mode"] : "-",
    "token" => !empty($_REQUEST["token"]) ? $_REQUEST["token"] : "-",
    "country" => !empty($_REQUEST["country"]) ? $_REQUEST["country"] : "-",
    "shopname" => !empty($_REQUEST["shopname"]) ? $_REQUEST["shopname"] : "-",
    "tradingname" => !empty($_REQUEST["tradingname"]) ? $_REQUEST["tradingname"] : "-",
    "address" => !empty($_REQUEST["address"]) ? $_REQUEST["address"] : "-",
    "city" => !empty($_REQUEST["city"]) ? $_REQUEST["city"] : "-",
    "state" => !empty($_REQUEST["state"]) ? $_REQUEST["state"] : "-",
    "zip" => !empty($_REQUEST["zip"]) ? $_REQUEST["zip"] : "-",
    "firstname" => !empty($_REQUEST["firstname"]) ? $_REQUEST["firstname"] : "-",
    "lastname" => !empty($_REQUEST["lastname"]) ? $_REQUEST["lastname"] : "-",
    "mobile" => !empty($_REQUEST["mobile"]) ? $_REQUEST["mobile"] : "-",
    "email" => !empty($_REQUEST["email"]) ? $_REQUEST["email"] : "-",
    "reason" => !empty($_REQUEST["reason"]) ? $_REQUEST["reason"] : "-",
    "other" => !empty($_REQUEST["other"]) ? $_REQUEST["other"] : "-",
    "lastdate" => !empty($_REQUEST["lastdate"]) ? $_REQUEST["lastdate"] : "-",
    "feedback" => !empty($_REQUEST["feedback"]) ? $_REQUEST["feedback"] : "-",
    "thisPage" => "activeajax",

     "token" => !empty($_REQUEST["token"]) ? $_REQUEST["token"] : "-",
);

    // if ($_REQUEST["mode"] == "send"){

    //  

    // }else{

    // }
 echo json_encode($param);
?>