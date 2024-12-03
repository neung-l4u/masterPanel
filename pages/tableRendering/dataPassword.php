<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$result = $db->query('SELECT p.id, p.pwName, p.pwLink, p.pwUser, p.pwPass, p.pwNote, l.lName FROM `passwordmanager` p , `userLevel` l WHERE  p.pwLevel = l.lID;')->fetchAll();

$data = array("data"=> array());

foreach ($result as $row) {

    $row["pwLink"] = ($row["pwLink"] == null) ? "-" : '<a href="'.$row["pwLink"].'">'.$row["pwLink"].'</a>';
    $row["pwNote"] = ($row["pwNote"] == null) ? "-" : $row["pwNote"];

    $row["copyUser"] = '<input type="text" id="' . $row["pwUser"] . '" value="' . $row["pwUser"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwUser"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>';

    $row["copyPass"] = '<input type="text" id="' . $row["pwPass"] . '" value="' . $row["pwPass"] . '" readonly style="position: absolute; left: -9999px;">
    <button onclick="copyText(\'' . $row["pwPass"] . '\')" style="color: #bbb; border: none; background: none;"><i class="far fa-copy"></i></button>
    
    <script>
        function copyText(elementId) {
            var copyText = document.getElementById(elementId);
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert("Copied the text: " + copyText.value);
            }).catch(function(error) {
                console.error("Error copying text: ", error);
            });
        }
    </script>';

    $data["data"][] = array(
        $row["pwName"],
        $row["pwLink"],
        $row["pwUser"] . " " . $row["copyUser"],
        $row["pwPass"] . " " . $row["copyPass"],
        $row["pwNote"],
    );
}

echo json_encode($data);