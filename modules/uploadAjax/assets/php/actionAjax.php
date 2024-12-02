<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

$mode = $_POST['mode'];
$shop_name = $_POST['shop_name'];
$shop_type = $_POST['shop_type'];
$template = $_POST['template'];
$po_name = $_POST['po_name'];
$address = $_POST['address'];
$token = $_POST['token'];

if ($mode == "save"){
    $insert = $db->query('INSERT INTO `testAjaxData`
                            (`sType`, `sTemplate`, `sPO`, `sName`, `sAddress`) 
                            VALUES (?, ?, ?, ?, ?);'
        ,$shop_type, $template, $po_name, $shop_name, $address
    );

    $params['result'] = "Save to Database";
}else if ($mode == "update") {

}else if ($mode == "read") {

}



//$params['name'] = 'L4U = '.$shop_name;

echo json_encode($params);