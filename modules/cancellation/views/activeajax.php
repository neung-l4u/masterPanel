<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$data['mode'] = !empty($_POST['mode']) ? $_POST['mode'] : "save";
$data['country'] = !empty($_POST['country']) ? $_POST['country'] : null;
$data['city'] = !empty($_POST['city']) ? $_POST['city'] : null;
$data['shopName'] = !empty($_POST['shopName']) ? $_POST['shopName'] : null;
$data['tradingName'] = !empty($_POST['tradingName']) ? $_POST['tradingName'] : null;
$data['streetAddress'] = !empty($_POST['streetAddress']) ? $_POST['streetAddress'] : null;
$data['state'] = !empty($_POST['state']) ? $_POST['state'] : null;
$data['zip'] = !empty($_POST['zip']) ? $_POST['zip'] : null;
$data['first_name'] = !empty($_POST['first_name']) ? $_POST['first_name'] : null;
$data['last_name'] = !empty($_POST['last_name']) ? $_POST['last_name'] : null;
$data['mobile'] = !empty($_POST['mobile']) ? $_POST['mobile'] : null;
$data['email'] = !empty($_POST['email']) ? $_POST['email'] : null;
$data['reason'] = !empty($_POST['reason']) ? $_POST['reason'] : null;
$data['other'] = !empty($_POST['other']) ? $_POST['other'] : null;
$data['lastDate'] = !empty($_POST['lastDate']) ? $_POST['lastDate'] : null;
$data['feedback'] = !empty($_POST['feedback']) ? $_POST['feedback'] : null;
$data['testMode'] = !empty($_POST['testMode']) ? $_POST['testMode'] : 0;
$data['industrial'] = !empty($_POST['industrial']) ? $_POST['industrial'] : null;

$params['result'] = "Default Text";
$params['timestamp'] = date("Y-m-d H:i:s");

$countryMap = [
    "Australia" => "AU",
    "New Zealand" => "NZ",
    "Thailand" => "TH",
    "United States" => "US",
    "Canada" => "CA",
    "United Kingdom" => "UK"
];

if (!empty($data['country']) && isset($countryMap[$data['country']])) {
    $data['country'] = $countryMap[$data['country']];
} else {
    $data['country'] = null; // หรือ default ค่า fallback
}



if ($data['mode'] == "save"){

    try {
        $insert = $db->query(
            'INSERT INTO `Cancellation` (`industrial`,`county`, `city`, `shopname`, `trading`, `address`, `state`, `zip`, `firstname`, `lastname`, `mobile`, `email`, `other`, `reason`, `lastdate`, `feedback`, `test`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $data['industrial'], $data['country'], $data['city'], $data['shopName'], $data['tradingName'], $data['streetAddress'], $data['state'], $data['zip'], $data['first_name'], $data['last_name'], $data['mobile'], $data['email'], $data['other'], $data['reason'], $data['lastDate'], $data['feedback'], $data['testMode']
        );


        $params['result'] = "Save to Database by Bas";
    } catch (Exception $e) {
        http_response_code(500);
        $params['result'] = "DB Error: " . $e->getMessage(); // สำหรับ dev log
    }

}

echo json_encode($params);