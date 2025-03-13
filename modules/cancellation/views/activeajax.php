<?php
global $db;
session_start();
header('Content-Type: application/json');
include 'assets/db/db.php';
include "assets/db/initDB.php";

$date = date('Y-m-d H:i:s');


$data['mode'] = !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : "save";
$data['country'] = !empty($_REQUEST['country']) ? $_REQUEST['country'] : "";
$data['city'] = !empty($_REQUEST['city']) ? $_REQUEST['city'] : "";
$data['shopName'] = !empty($_REQUEST['shopName']) ? $_REQUEST['shopName'] : "";
$data['tradingName'] = !empty($_REQUEST['tradingName']) ? $_REQUEST['tradingName'] : "";
$data['streetAddress'] = !empty($_REQUEST['streetAddress']) ? $_REQUEST['streetAddress'] : "";
$data['state'] = !empty($_REQUEST['state']) ? $_REQUEST['state'] : "";
$data['zip'] = !empty($_REQUEST['zip']) ? $_REQUEST['zip'] : "";
$data['first_name'] = !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : "";
$data['last_name'] = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : "";
$data['mobile'] = !empty($_REQUEST['mobile']) ? $_REQUEST['mobile'] : "";
$data['email'] = !empty($_REQUEST['email']) ? $_REQUEST['email'] : "";
$data['reason'] = !empty($_REQUEST['reason']) ? $_REQUEST['reason'] : "";
$data['other'] = !empty($_REQUEST['other']) ? $_REQUEST['other'] : null;
$data['lastDate'] = !empty($_REQUEST['lastDate']) ? $_REQUEST['lastDate'] : "";
$data['feedback'] = !empty($_REQUEST['feedback']) ? $_REQUEST['feedback'] : "";


//$mode = $_POST['mode'];
//$country = $_POST['country'];
//$city = $_POST['city'];
//$shopName = $_POST['shopName'];
//$tradingName = $_POST['tradingName'];
//$address = $_POST['streetAddress'];
//$state = $_POST['state'];
//$zip = $_POST['zip'];
//$firstName = $_POST['first_name'];
//$lastName = $_POST['last_name'];
//$mobile = $_POST['mobile'];
//$email = $_POST['email'];
//$reason = $_POST['reason'];
//$other = $_POST['other'];
//$lastDate = $_POST['lastDate'];
//$feedback = $_POST['feedback'];


if ($data['mode'] == "save"){
    $insert = $db->query('INSERT INTO `Cancellation`
                            (`county`, `city`, `shopname`, `trading`, `address`, `state`, `zip`, `firstname`, `lastname`, `mobile`, `email`, `other`, `reason`, `lastdate`, `feedback`, `timestamp`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);'
        ,$data['country'] ,$data['city'], $data['shopName'], $data['tradingName'], $data['streetAddress'], $data['state'], $data['zip'], $data['first_name'], $data['last_name'], $data['mobile'], $data['email'], $data['other'], $data['reason'], $data['lastDate'], $data['feedback'], $date
    );

    $params['result'] = "Save to Database by Bas";
}

//$params['name'] = 'L4U = '.$shop_name;

echo json_encode($params);
?>