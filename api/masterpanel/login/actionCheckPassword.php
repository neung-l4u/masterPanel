<?php
global $db;
session_start();
include '../../../assets/db/db.php';
include "../../../assets/db/initDB.php";



$params["email"] = !empty($_POST['email']) ? $_POST['email'] : "";
$params["act"] = !empty($_POST['act']) ? $_POST['act'] : "";


if ($params ["act"] == "checkEmail"){
    function encode_safe($id) {
        return base64_encode($id . '|L4U');
    }

    function decode_safe($encoded) {
        $decoded = base64_decode($encoded);
        list($id, $secret) = explode('|', $decoded);
        if ($secret === 'L4U') {
            return $id;
        }
        return false;
    }

    $row = $db->query('SELECT * FROM `staffs` WHERE sEmail = ?;',$params["email"])->fetchArray();
    $params["sID"] = $row["sID"];
    $params["sEmail"] = $row["sEmail"];



    $en = encode_safe($params["sID"]);
    $de = decode_safe($en);

    $params["en"] = $en;

    if (!empty($row)) {
        $params["sID"] = $row["sID"];
        $params["sEmail"] = $row["sEmail"];
        $params["en"] = encode_safe($params["sID"]);
        $params["status"] = "Correct";
    }else {
        $params["status"] = "not_found";
    }
//
//    if(!empty($de)){
//        echo '<div>Decode text: '.$de.'</div>';
//    }else{
//        echo '<div>Error code</div>';
//    }


}

echo json_encode($params);


