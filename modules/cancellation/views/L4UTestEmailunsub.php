<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
$when = date('Y-m-d H:i:s');

$json = $_REQUEST["allData"];

$data['mode'] = !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : 'confirm';
$data['shopName'] = !empty($_REQUEST['shopName']) ? $_REQUEST['shopName'] : "-";
$data['email'] = !empty($_REQUEST['email']) ? $_REQUEST['email'] : "-"; //customer email
$data['county'] = !empty($_REQUEST['county']) ? $_REQUEST['county'] : "-";
$data['tradingName'] = !empty($_REQUEST['tradingName']) ? $_REQUEST['tradingName'] : "-";
$data['streetAddress'] = !empty($_REQUEST['streetAddress']) ? $_REQUEST['streetAddress'] : "-";
$data['city'] = !empty($_REQUEST['city']) ? $_REQUEST['city'] : "-";
$data['state'] = !empty($_REQUEST['state']) ? $_REQUEST['state'] : "-";
$data['zip'] = !empty($_REQUEST['zip']) ? $_REQUEST['zip'] : "-";
$data['firstName'] = !empty($_REQUEST['firstName']) ? $_REQUEST['firstName'] : "-";
$data['lastName'] = !empty($_REQUEST['lastName']) ? $_REQUEST['lastName'] : "-";
$data['mobile'] = !empty($_REQUEST['mobile']) ? $_REQUEST['mobile'] : "-";
$data['reason'] = !empty($_REQUEST['reason']) ? $_REQUEST['reason'] : "-";
$data['other'] = !empty($_REQUEST['other']) ? $_REQUEST['other'] : "-";
$date['lastdate'] = !empty($_REQUEST['lastdate']) ? $_REQUEST['lastdate'] : "-";
$data['feedback'] = !empty($_REQUEST['feedback']) ? $_REQUEST['feedback'] : "-";

$address = $data['streetAddress']." ".$data['city']." ".$data['state']." ".$data['zip']." ".$data['country'];
$fullName= $data['firstName']." ".$data['lastName'];

$reason = $data['reason'];
$boxother = $data['other'];
$textReason = "";

if (!empty($reason)){
    $textReason = $reason;
}else if (!empty($boxother)){
    $textReason = $boxother;
}else{
    $textReason = "-";
}







//$data['adminEmail'] = "admin@localforyou.com";
$data['adminEmail'] = "noreply@localforyou.com";
$data['l4uStaff'] = "bas@localforyou.com";  //อีเมล์ผู้ดูแลระบบ
//$date['l4uDev'] = "mark@localforyou.com"; //อีเมล์ผู้ดูแลระบบ
$date['l4uDev'] = "bas@localforyou.com"; //อีเมล์ผู้ดูแลระบบ
//$data['l4uSuperAdmin'] = "neung@localforyou.com";  //อีเมล์ผู้ดูแลระบบ
$data['l4uSuperAdmin'] = "bas@localforyou.com";


/// default result value ///
$result['result'] = 0;
$result['msg'] = "";
$result['email'] = $data['email'];
$result['mode'] = $data['mode'];

/////////////////////////


//////// test data /////
// $data['adminEmail'] = "bas@localforyou.com";
// $data['l4uStaff'] = "bas@localforyou.com";
// $data['l4uSuperAdmin'] = "bas@localforyou.com";
// $result['email'] = "bas@localforyou.com";
// $result['mode'] = "send";
// $data['mode'] = "send";
// $data['shopName'] = "Bas Shop";
// $data['fullName'] = "Neung";
// $data['email'] = "bas@localforyou.com";
///// end test data ////

if(!empty($data['email'])) {
    $data['subject'] = "L4U : Unsubscribe Request";

    $headers = [
        'From' => 'Local For You <'.$data['adminEmail'].'>',
        'Cc' => $data['adminEmail'],
        'Bcc' => $data['l4uSuperAdmin'].','.$date['l4uDev'].','.$data['l4uStaff'],
        'Reply-To' => $data['adminEmail'],
        'X-Sender' => 'Local For You <'.$data['adminEmail'].'>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => $data['l4uSuperAdmin'], //for error
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=utf-8'
    ];

    $message = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Subject : Unsubscribe email</h4><p>Hi, Team<br>Our Customer requests to unsubscribed our services</p><br><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Name</th><td>'.$data['shopName'].'</td></tr><tr><th>Trading Name</th><td>'.$data['tradingName'].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$data['county'].'</td></tr><tr><th>Street Address</th><td>'.$address.'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$data['shopName'].'</td></tr><tr><th>Email</th><td>'.$data['email'].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$data['mobile'].'</td></tr></table><table><caption>Detail</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Reason</th><td>'.$textReason.'</td></tr><tr><th>Last Date</th><td>'.$data['lastdate'].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Reason</th><td>'.$data['feedback'].'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Bas - Distributed By: Local For You</p></div></body></html>';
    $result['email'] = $data['email'];
    $result['mode'] = $data['mode'];

    if (mail($data['email'], $data['subject'], $message, $headers)) {
        $result['result'] = 1;
        $result['msg'] = "Send email successful";
    } else {
        $result['result'] = 0;
        $result['msg'] = "Send email fail!!";
    }
}
echo json_encode($result);
?>