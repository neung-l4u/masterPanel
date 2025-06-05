<?php
$menuPage = isset($_GET["p"]) ? $_GET["p"] : "";

$showPage = "home.php";

switch ($menuPage){
    case "home":
        $showPage = "home.php";
        break;
    case "time":
        $showPage = "timestamp.php";
        break;
    case "timeOff":
        $showPage = "timeOff.php";
        break;
    default:
        $showPage = "home.php";
}