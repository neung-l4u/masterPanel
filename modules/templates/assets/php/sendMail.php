<?php
global $db;
require_once "../db/db.php";
require_once "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$param = array();
$to = "";
$cc = "";
$bcc = "";
$result = array(
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0
);

$id = $_REQUEST["projectID"];
$loginID = $_REQUEST["loginID"];
$json = $_REQUEST["payload"];
$page = $_REQUEST["page"];
//////////////////////// test data ////////////////////////////////////////
/*
$id = 35;
$loginID = 15;
$page = "home";
$json = array(
    "01-HeaderBgIMG" =>  "bgContactHeadBackground_35_250103111342.jpeg",
    "02-BgIMG" =>  "bgContactBackground_35_250103423461.jpeg",
    "03-UsSubHeadline1" =>  "Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...",
    "04-UsSubHeadline2" =>  "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis, nunc consequat consequat lacinia, dui.",
    "05-PromotionHeadline" =>  "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam bibendum urna at.",
    "06-PromotionSubHeadline" =>  "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent congue."
);*/
////////////////////////// end test ////////////////////////////////////////

$project = $db->query(
    '
        SELECT pj.`projectName`,st.name AS "shopType", pj.`selectedTemplate`, ct.name AS "country", 
               pj.`projectTimestamp`, sf.sNickName AS "PO", pj.`email`, pj.`phone`, pj.`address`, 
               pj.`openingHours`, pj.`pickupAndDelivery`, pj.`logo`, pj.`colorTheme1`, pj.`colorTheme2`, 
               pj.`colorTheme3`, pj.`domainName`, pj.`domainHave`, dp.name AS "domainProvider", pj.`domainUser`, 
               pj.`domainPass`, pj.`hostingName`, pj.`hostingHave`, hp.name AS "hostingProvider", pj.`hostingUser`, 
               pj.`hostingPass`, pj.`gloriaHave`, pj.`orderURL`, pj.`tableURL`, pj.`orderOther`, pj.`resOtherSystem`, 
               pj.`amelia`, pj.`voucher`, pj.`bookOther`, pj.`masOtherSystem`, pj.`needEmail`, pj.`facebookURL`, 
               pj.`instagramURL`, pj.`youtubeURL`, pj.`tiktokURL` 
        FROM `tb_project` pj , `Countries` ct , `tb_shopType` st, `staffs` sf, `domainproviders` dp, `hostingproviders` hp 
        WHERE pj.`projectID` = ? AND pj.`countryID` = ct.`id` AND pj.`shopTypeID` = st.id AND 
              pj.`projectOwner` = sf.sID AND pj.`domainProvidersID` = dp.id AND pj.`hostingProvidersID` = hp.id
    ',$id)->fetchArray();

$topData = "";


$topData = '<div><b>- - Project - -</b></div>';
$topData .= '<div><b>Project ID: </b>'. $id .'</div>';
$topData .= '<div><b>Project Name: </b>'.$project['projectName'].'</div>';
$topData .= '<div><b>Project Type: </b>'.$project['shopType'].' Template No. - 0'.$project['selectedTemplate'];
$topData .= '<div><b>Project Owner: </b>'.$project['PO'].'</div>';
$topData .= '<div><b>Page: </b>'.$page.'</div>';
$topData .= '<div><b>Country: </b>'.$project['country'].'</div>';
$topData .= '<br>- - - - - - - - - - - - - - - - - - - - - - - - - - -<br><br>';

$topData .= '<div><b>- - Detail Project & Theme - -</b></div>';  
$topData .= '<div><b>Logo: </b>'.$project['logo'].'</div>';
$topData .= '<div><b>Color1: </b>'.$project['colorTheme1'].'</div>';
$topData .= '<div><b>Color2: </b>'.$project['colorTheme2'].'</div>';
$topData .= '<div><b>Color3: </b>'.$project['colorTheme3'].'</div>';
$topData .= '<br>';
$topData .= '<div><b>Email: </b>'.$project['email'].'</div>';
$topData .= '<div><b>Phone: </b>'.$project['phone'].'</div>';
$topData .= '<div><b>Facebook: </b>'.$project['facebookURL'].'</div>';
$topData .= '<div><b>IG: </b>'.$project['instagramURL'].'</div>';
$topData .= '<div><b>Youtube: </b>'.$project['youtubeURL'].'</div>';
$topData .= '<div><b>Tiktok: </b>'.$project['tiktokURL'].'</div>';
$topData .= '<div><b>Localtion: </b>'.$project['address'].'</div>';
$topData .= '<br>';
$topData .= '<div><b>Opening Hours: </b>'.$project['openingHours'].'</div>';
$topData .= '<div><b>Pickup & Delivery: </b>'.$project['pickupAndDelivery'].'</div>';
$topData .= '<br>';

if ($project['gloriaHave'] == 1){
    $topData .= '<div><b>System: Gloria Food </b></div>';
    $topData .= '<div><b>Order URL: </b>'.$project['orderURL'].'</div>';
    $topData .= '<div><b>Table URL: </b>'.$project['tableURL'].'</div>';
}else { 
    $topData .= '<div><b>System: Amelia </b></div>';
};

$topData .= '<br>- - - - - - - - - - - - - - - - - - - - - - - - - - -<br><br>';
$topData .= '<div><b>- - Detail Domain - -</b></div>';    
$topData .= '<div><b>Domain Name: </b>'.$project['domainName'].'</div>';
$topData .= '<div><b>Domain Provider: </b>'.$project['domainProvider'].'</div>';
$topData .= '<div><b>User: </b>'.$project['domainUser'].'</div>';
$topData .= '<div><b>Password: </b>'.$project['domainPass'].'</div>';
$topData .= '<div><b>Hosting Provider: </b>'.$project['hostingProvider'].'</div>';
$topData .= '<div><b>User: </b>'.$project['hostingUser'].'</div>';
$topData .= '<div><b>Password: </b>'.$project['hostingPass'].'</div>';

$message = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title></head><body><div>'.$topData.'</div><hr><pre>'.json_encode($json, JSON_PRETTY_PRINT).'</pre></body></html>';

$loginPerson = $db->query('SELECT sEmail FROM staffs WHERE sID = ?;',$loginID)->fetchAll();
$loginEmail = $loginPerson['sEmail'];

$peoples = $db->query('SELECT * FROM TemplateSubmissionSettings WHERE status=?;',1)->fetchAll();
$people = array("To" => array(), "Cc" => array(), "Bcc" => array());

foreach ($peoples as $row) {
    $people[$row['channel']][] =  $row['email'];
}

//if(count($people['To']) > 0){ $to = implode(', ', $people['To']); }
if (!in_array($loginEmail, $people['To'])) {
    $people['To'][] = $loginEmail;
    $to = implode(', ', array_reverse($people['To']));
}else{
    $to = implode(', ', $people['To']);
}
if(count($people['Cc']) > 0){ $cc = implode(', ', $people['Cc']); }
if(count($people['Bcc']) > 0){ $bcc = implode(',', $people['Bcc']); }


$param = array(
    "To" => $to,
    "Cc" => $cc,
    "Bcc" => $bcc,
    "Subject" => "New Template Submission",
    "Message" => $message,
    "Timestamp" => $timestamp,
    "Date" => $date,
    "Status" => 1,
    "Type" => "TemplateSubmission"
);





$system = array(
    "emailSenderName" => "Template Submission Form",
    "emailSenderEmail" => "administrator@localforyou.com",
    "emailSubject" => "New " . $project['shopType'] . " Website Submitted",
    "emailAdministrator" => "neung@localforyou.com"
);

$system["emailBody"] = $param["Message"];

$system["emailAlertTo"] = $param["To"];

$mailHeaders = [
    'From' => 'Template Submission Form <noreply@localforyou.com>',
    'Cc' => $param['Cc'],
    'Bcc' => $param['Bcc'],
    'Reply-To' => 'neung@localforyou.com',
    'X-Sender' => 'LocalForYou <neung@localforyou.com>',
    'X-Mailer' => 'PHP/' . phpversion(),
    'X-Priority' => '1',
    'Return-Path' => 'neung@localforyou.com',
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=utf-8'
];


    $result['email'] = $param['To'];

    if (mail($system["emailAlertTo"], $system["emailSubject"], $system["emailBody"], $mailHeaders)) {
        $result['success'] = true;
        $result['result'] = 1;
        $result['msg'] = "Send email successful";
    }

echo json_encode($result);

?>