<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $topData, $topDate;
require_once "../assets/db/db.php";
require_once "../assets/db/initDB.php";
include_once "../assets/php/shareFunction.php";
header('Content-Type: text/html; charset=UTF-8');

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$cutday = explode("-","$date");

$month = $cutday[1];
$day = $cutday[2];
$year = $cutday[0];

$exday = $year.$month.$day;

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

$id = $_REQUEST["projectID"];
$loginID = $_REQUEST["loginID"];
$pageDetails = array();

$project = $db->query(
    'SELECT pj.`projectName`,st.name AS "shopType", pj.`selectedTemplate`, ct.name AS "country", 
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

$openingHours = explode("__", $project["openingHours"]);
$openingSunday = "Sunday : " . (($openingHours[6] !== "") ? $openingHours[6] : "-");
$openingMonday = "Monday : " . (($openingHours[0] !== "") ? $openingHours[0] : "-");
$openingTuesday = "Tuesday : " . (($openingHours[1] !== "") ? $openingHours[1] : "-");
$openingWednesday = "Wednesday : " . (($openingHours[2] !== "") ? $openingHours[2] : "-");
$openingThursday = "Thursday : " . (($openingHours[3] !== "") ? $openingHours[3] : "-");
$openingFriday = "Friday : " . (($openingHours[4] !== "") ? $openingHours[4] : "-");
$openingSaturday = "Saturday : " . (($openingHours[5] !== "") ? $openingHours[5] : "-");
$openingHours = $openingSunday.'<br>'.$openingMonday.'<br>'.$openingTuesday.'<br>'.$openingWednesday.'<br>'.$openingThursday.'<br>'.$openingFriday.'<br>'.$openingSaturday;

$pickupAndDelivery = explode("__", $project["pickupAndDelivery"]);
$pickupSunday = "Sunday : " . (($pickupAndDelivery[6] !== "") ? $pickupAndDelivery[6] : "-");
$pickupMonday = "Monday : " . (($pickupAndDelivery[0] !== "") ? $pickupAndDelivery[0] : "-");
$pickupTuesday = "Tuesday : " . (($pickupAndDelivery[1] !== "") ? $pickupAndDelivery[1] : "-");
$pickupWednesday = "Wednesday : " . (($pickupAndDelivery[2] !== "") ? $pickupAndDelivery[2] : "-");
$pickupThursday = "Thursday : " . (($pickupAndDelivery[3] !== "") ? $pickupAndDelivery[3] : "-");
$pickupFriday = "Friday : " . (($pickupAndDelivery[4] !== "") ? $pickupAndDelivery[4] : "-");
$pickupSaturday = "Saturday : " . (($pickupAndDelivery[5] !== "") ? $pickupAndDelivery[5] : "-");
$pickupAndDelivery = $pickupSunday.'<br>'.$pickupMonday.'<br>'.$pickupTuesday.'<br>'.$pickupWednesday.'<br>'.$pickupThursday.'<br>'.$pickupFriday.'<br>'.$pickupSaturday;

$pageDetails = $db->query(
    'SELECT `home`, `about`, `services`, `contact`
        FROM `templatepagedetails`
        WHERE projectID = ?', $id
)->fetchArray();

$json = $project['shopType'] == "Restaurant"
     ? [
         'home' => json_decode($pageDetails['home'], true),
         'about' => json_decode($pageDetails['about'], true),
         'contact' => json_decode($pageDetails['contact'], true),
       ]
     : array(
         'home' => json_decode($pageDetails['home'], true),
         'about' => json_decode($pageDetails['about'], true),
         'contact' => json_decode($pageDetails['contact'], true),
         'services' => json_decode($pageDetails['services'], true),
    );
$prettyJson = json_encode($json, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

$folderName = "../upload/". $id . "-" . sanitizeFolderName($project["projectName"])."/";

$topData .= '<h2>Template Submission Form</>';
$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Project Details</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Project Owner</td><td>'.$project['PO'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Project Name</td><td>'.$project['projectName'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Project Type</td><td>'.$project['shopType'].' | Template No. 0'.$project['selectedTemplate'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Country</td><td>'.$project['country'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Due Date</td><td>'.$dueDate.'</td></tr>';
$topData .= '</tbody>';
$topData .= '</table><br>';

$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Project Resources</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Project Code</td><td>WEB-'.date("ymd")." ".$project['projectName'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Resources</td><td>'.$folderName.'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Project ID</td><td>'.$id.'</td></tr>';
$topData .= '</tbody>';
$topData .= '</table><br>';

$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Basic Detail</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Email</td><td>'.$project['email'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Phone</td><td>'.$project['phone'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Location</td><td>'.$project['address'].'</td></tr>';


$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Opening Hours</td><td>'.$openingHours.'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Pickup & Delivery</td><td>'.$pickupAndDelivery.'</td></tr>';
$topData .= '</tbody>';
$topData .= '</table><br>';


$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Theme</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Logo</td><td>'.$project['logo'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Template</td><td>'.$project['shopType'].' Template No. 0'.$project['selectedTemplate'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Primary Color</td><td>'.$project['colorTheme1'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Secondary Color</td><td>'.$project['colorTheme2'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Accent Color</td><td>'.$project['colorTheme3'].'</td></tr>';
$topData .= '</tbody>';
$topData .= '</table><br>';


$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Social Media</th></tr></thead>';
$topData .= '<tbody>';
if (empty($project['facebookURL']) && empty($project['instagramURL']) && empty($project['youtubeURL']) && empty($project['tiktokURL'])){
    $topData .= '<tr><td colspan="2" style="width: 150px; font-weight: bold; background-color: #f8f9fa; text-align: center;">No data</td></tr>';
}
if (!empty($project['facebookURL'])){
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Facebook</td><td>'.$project['facebookURL'].'</td></tr>';
}
if (!empty($project['instagramURL'])){
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Instagram</td><td>'.$project['instagramURL'].'</td></tr>';
}
if (!empty($project['youtubeURL'])){
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Youtube</td><td>'.$project['youtubeURL'].'</td></tr>';
}
if (!empty($project['tiktokURL'])){
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Tiktok</td><td>'.$project['tiktokURL'].'</td></tr>';
}
$topData .= '</tbody>';
$topData .= '</table><br>';

$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
if ($project['gloriaHave'] == 1){
    $topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">System: Gloria Food</th></tr></thead>';
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Order URL</td><td>'.$project['orderURL'].'</td></tr>';
    $topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Table URL</td><td>'.$project['tableURL'].'</td></tr>';
}

if ($project['amelia'] == 1){
    $topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">System: Amelia</th></tr></thead>';
}
$topData .= '</table><br>';

$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Domain Details</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Domain Name</td><td>'.$project['domainName'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Domain Provider</td><td>'.$project['domainProvider'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Username</td><td>'.$project['domainUser'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Password</td><td>'.$project['domainPass'].'</td></tr>';
$topData .= '</tbody>';
$topData .= '</table><br>';


$topData .= '<table width="650px" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">';
$topData .= '<thead><tr><th colspan="2" style="background-color: #1827B8; color: white; padding: 10px; text-align: left;">Hostimg Details</th></tr></thead>';
$topData .= '<tbody>';
$topData .= '<tr><td style="width: 150px; font-weight: bold; background-color: #f8f9fa;">Hosting Provider</td><td>'.$project['HostingProvider'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Username</td><td>'.$project['hostingUser'].'</td></tr>';
$topData .= '<tr><td style="font-weight: bold; background-color: #f8f9fa;">Password</td><td>'.$project['hostingPass'].'</td></tr>';
$topData .= '</table><br>';

$message = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title></head><body><div>'.$topData.'</div><hr><pre>'. $prettyJson .'</pre></body></html>';
// $message = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title></head><body><div>'.$topData.'</div><hr><pre>Please Get json data form DB</pre></body></html>';

echo $message;