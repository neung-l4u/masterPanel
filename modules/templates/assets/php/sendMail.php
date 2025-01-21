<?php
global $db, $topData;
require_once "../db/db.php";
require_once "../db/initDB.php";
include_once "../php/share_function.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$startDate = $date;
$businessDaysToAdd = 7;
$holidays = [];
$dueDate = addBusinessDays($startDate, $businessDaysToAdd, $holidays);

$param = array();
$to = "";
$cc = "";
$bcc = "";
$result = array(
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0
);

$sendMail = false;

$id = $_REQUEST["projectID"];
$loginID = $_REQUEST["loginID"];
$json = $_REQUEST["payload"];
$page = $_REQUEST["page"];

$project = $db->query(
    '
        SELECT pj.`projectName`,st.name AS "shopType", pj.`selectedTemplate`, ct.name AS "country", 
               pj.`projectTimestamp`, sf.sNickName AS "PO", pj.`email`, pj.`phone`, pj.`address`, 
               pj.`openingHours`, pj.`pickupAndDelivery`, pj.`logo`, pj.`colorTheme1`, pj.`colorTheme2`, 
               pj.`colorTheme3`, pj.`domainName`, pj.`domainHave`, dp.name AS "domainProvider", pj.`domainUser`, 
               pj.`domainPass`, pj.`hostingName`, pj.`hostingHave`, hp.name AS "HostingProvider", pj.`hostingUser`, 
               pj.`hostingPass`, pj.`gloriaHave`, pj.`orderURL`, pj.`tableURL`, pj.`orderOther`, pj.`resOtherSystem`, 
               pj.`amelia`, pj.`voucher`, pj.`bookOther`, pj.`masOtherSystem`, pj.`needEmail`, pj.`facebookURL`, 
               pj.`instagramURL`, pj.`youtubeURL`, pj.`tiktokURL` 
        FROM `tb_project` pj , `Countries` ct , `tb_shopType` st, `staffs` sf, `DomainProviders` dp, `HostingProviders` hp 
        WHERE pj.`projectID` = ? AND pj.`countryID` = ct.`id` AND pj.`shopTypeID` = st.id AND 
              pj.`projectOwner` = sf.sID AND pj.`DomainProvidersID` = dp.id AND pj.`HostingProvidersID` = hp.id
    ',$id)->fetchArray();

$folderName = "../upload/". $id . "-" . sanitizeFolderName($project["projectName"])."/";



$topData = '<div><b>- - Project - -</b></div>';
$topData .= '<div><h2>Page: '.$page.'</h2></div>';
$topData .= '<div><b>Project ID: </b>'. $id .'</div>';
$topData .= '<div><b>Project Name: </b>'.$project['projectName'].'</div>';
$topData .= '<div><b>Project Type: </b>'.$project['shopType'].' Template No. - 0'.$project['selectedTemplate'];
$topData .= '<div><b>Project Owner: </b>'.$project['PO'].'</div>';
$topData .= '<div><b>Country: </b>'.$project['country'].'</div>';
$topData .= '<br><br>';
$topData .= '<div><b>Resources: </b>'.$folderName.'</div>';
$topData .= '<div><b>Due Date: </b>'.$dueDate.'</div>';
$topData .= '<br>- - - - - - - - - - - - - - - - - - - - - - - - - - -<br><br>';

$topData .= '<div><b>- - Detail Project & Theme - -</b></div>';  
$topData .= '<div><b>Logo: </b>'.$project['logo'].'</div>';
$topData .= '<div><b>Color1: </b>'.$project['colorTheme1'].'</div>';
$topData .= '<div><b>Color2: </b>'.$project['colorTheme2'].'</div>';
$topData .= '<div><b>Color3: </b>'.$project['colorTheme3'].'</div>';
$topData .= '<br>';
$topData .= '<div><b>Email: </b>'.$project['email'].'</div>';
$topData .= '<div><b>Phone: </b>'.$project['phone'].'</div>';


if ($project['facebookURL'] != ""){
    $socialmedic .= '<div><b>Facebook: </b>'.$project['facebookURL'].'</div>';
}
if ($project['instagramURL'] != ""){
    $socialmedic .= '<div><b>Instagram: </b>'.$project['instagramURL'].'</div>';
}
if ($project['youtubeURL'] != ""){
    $socialmedic .= '<div><b>Youtube: </b>'.$project['youtubeURL'].'</div>';
}
if ($project['tiktokURL'] != ""){
    $socialmedic .= '<div><b>Tiktok: </b>'.$project['tiktokURL'].'</div>';
}

$topData .= '<div><b>Localtion: </b>'.$project['address'].'</div>';
$topData .= '<br>';
$topData .= '<div><b>Opening Hours: </b>'.$project['openingHours'].'</div>';
$topData .= '<div><b>Pickup & Delivery: </b>'.$project['pickupAndDelivery'].'</div>';
$topData .= '<br>';

if ($project['gloriaHave'] == 1){
    $topData .= '<div><b>System: Gloria Food </b></div>';
    $topData .= '<div><b>Order URL: </b>'.$project['orderURL'].'</div>';
    $topData .= '<div><b>Table URL: </b>'.$project['tableURL'].'</div>';
}

if ($project['amelia'] == 1){
    $topData .= '<div><b>System: Amelia </b></div>';
}

$topData .= '<br>- - - - - - - - - - - - - - - - - - - - - - - - - - -<br><br>';
$topData .= '<div><b>- - Detail Domain - -</b></div>';    
$topData .= '<div><b>Domain Name: </b>'.$project['domainName'].'</div>';
$topData .= '<div><b>Domain Provider: </b>'.$project['domainProvider'].'</div>';
$topData .= '<div><b>User: </b>'.$project['domainUser'].'</div>';
$topData .= '<div><b>Password: </b>'.$project['domainPass'].'</div>';
$topData .= '<div><b>Hosting Provider: </b>'.$project['HostingProvider'].'</div>';
$topData .= '<div><b>User: </b>'.$project['hostingUser'].'</div>';
$topData .= '<div><b>Password: </b>'.$project['hostingPass'].'</div>';

$message = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title></head><body><div>'.$topData.'</div><hr><pre>'.json_encode($json, JSON_PRETTY_PRINT).'</pre></body></html>';

$loginPerson = $db->query('SELECT * FROM staffs WHERE sID=?;',$loginID)->fetchArray();
if($loginPerson['sEmail'] == $project['email']){
    $sendMail = true;
}else{
    $sendMail = false;
}

$peoples = $db->query('SELECT * FROM TemplateSubmissionSettings WHERE status=?;',1)->fetchAll();
$people = array("To" => array(), "Cc" => array(), "Bcc" => array());

foreach ($peoples as $row) {
    $people[$row['channel']][] =  $row['email'];
}

if(count($people['To']) > 0){ $to = implode(', ', $people['To']); }
if(count($people['Cc']) > 0){ $cc = implode(', ', $people['Cc']); }
if(count($people['Bcc']) > 0){ $bcc = implode(',', $people['Bcc']); }


if (!in_array($loginPerson['sEmail'], explode(', ', $to))) {
    $to .= ($to ? ', ' : '') . $loginPerson['sEmail'];
}

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
    "emailSubject" => "New " . $project['shopType'] . " Website Submited",
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
    $result['to'] = $param['To'];
    $result['cc'] = $param['Cc'];
    $result['bcc'] = $param['Bcc'];
    $result['param'] = $param;

//    if($sendMail){
        if (mail($system["emailAlertTo"], $system["emailSubject"], $system["emailBody"], $mailHeaders)) {
            $result['success'] = true;
            $result['result'] = 1;
            $result['msg'] = "Send email successful";

        }
echo json_encode($result);
    /*}else{
        $result['success'] = true;
        $result['result'] = 1;
        $result['msg'] = "Pause send email";
        echo '<pre>'.print_r($result, true).'</pre>';
    }*/