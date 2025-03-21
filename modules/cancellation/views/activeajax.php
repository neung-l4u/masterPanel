<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$data['mode'] = !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : "save";
$data['country'] = !empty($_REQUEST['country']) ? $_REQUEST['country'] : null;
$data['city'] = !empty($_REQUEST['city']) ? $_REQUEST['city'] : null;
$data['shopName'] = !empty($_REQUEST['shopName']) ? $_REQUEST['shopName'] : null;
$data['tradingName'] = !empty($_REQUEST['tradingName']) ? $_REQUEST['tradingName'] : null;
$data['streetAddress'] = !empty($_REQUEST['streetAddress']) ? $_REQUEST['streetAddress'] : null;
$data['state'] = !empty($_REQUEST['state']) ? $_REQUEST['state'] : null;
$data['zip'] = !empty($_REQUEST['zip']) ? $_REQUEST['zip'] : null;
$data['first_name'] = !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : null;
$data['last_name'] = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
$data['mobile'] = !empty($_REQUEST['mobile']) ? $_REQUEST['mobile'] : null;
$data['email'] = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null;
$data['reason'] = !empty($_REQUEST['reason']) ? $_REQUEST['reason'] : null;
$data['other'] = !empty($_REQUEST['other']) ? $_REQUEST['other'] : null;
$data['lastDate'] = !empty($_REQUEST['lastDate']) ? $_REQUEST['lastDate'] : null;
$data['feedback'] = !empty($_REQUEST['feedback']) ? $_REQUEST['feedback'] : null;

$params['result'] = "Default Text";

if ($data['mode'] == "save"){
    $insert = $db->query('INSERT INTO `Cancellation`
                            (`county`, `city`, `shopname`, `trading`, `address`, `state`, `zip`, `firstname`, `lastname`, `mobile`, `email`, `other`, `reason`, `lastdate`, `feedback`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);'
        ,$data['country'] ,$data['city'], $data['shopName'], $data['tradingName'], $data['streetAddress'], $data['state'], $data['zip'], $data['first_name'], $data['last_name'], $data['mobile'], $data['email'], $data['other'], $data['reason'], $data['lastDate'], $data['feedback']
    );

    $params['result'] = "Save to Database by Bas";
}

//$params['name'] = 'L4U = '.$shop_name;

echo json_encode($params);
?>