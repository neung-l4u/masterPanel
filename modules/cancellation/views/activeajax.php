<?php
 global $db;
 session_start();
 include 'assets/db/db.php';
 include "assets/db/initDB.php";

$mode = $_POST['mode'];
$country = $_POST['country'];
$city = $_POST['city'];
$shopname = $_POST['shopname'];
$tradingname = $_POST['tradingname'];
$address = $_POST['address'];
$state = $_POST['state'];
$zip = $_POST['zip'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$mobile = $_POST['mobile'];
$email = $_POST['email'];
$reason = $_POST['reason'];
$other = $_POST['other'];
$lastdate = $_POST['lastdate'];
$feedback = $_POST['feedback'];

if (empty($other)){
    $other = null;
}
if ($mode == "save"){
    $insert = $db->query('INSERT INTO `Cancellation`
                            (`county`, `city`, `shopname`, `trading`, `address`, `state`, `zip`, `firstname`, `lastname`, `mobile`, `email`, `other`, `reason`, `lastdate`, `feedback`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);'
        ,$country, $city, $shopname, $tradingname, $address, $state, $zip, $firstname, $lastname, $mobile, $email, $other, $reason, $lastdate, $feedback
    );

    $params['result'] = "Save to Database by Bas";
}else if ($mode == "update") {

}else if ($mode == "read") {

}

//$params['name'] = 'L4U = '.$shop_name;

 echo json_encode($params);
?>