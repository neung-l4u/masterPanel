<?php
$result = array(
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0,

);


$param = array(
    "formDate" => !empty($_REQUEST["formDate"]) ? $_REQUEST["formDate"] : "-",
    "leadSource" => !empty($_REQUEST["leadSource"]) ? $_REQUEST["leadSource"] : "-",
    "formVersion" => !empty($_REQUEST["formVersion"]) ? $_REQUEST["formVersion"] : "-",
    "formMessage" => !empty($_REQUEST["formMessage"]) ? $_REQUEST["formMessage"] : "-",
    "formProduct" => !empty($_REQUEST["formProduct"]) ? $_REQUEST["formProduct"] : "-",
    "MainProduct" => !empty($_REQUEST["MainProduct"]) ? $_REQUEST["MainProduct"] : "-",
    "formSalesAgent" => !empty($_REQUEST["formSalesAgent"]) ? $_REQUEST["formSalesAgent"] : "-",
    "formContractPeriod" => !empty($_REQUEST["formContractPeriod"]) ? $_REQUEST["formContractPeriod"] : "-",
    "formRefPerson" => !empty($_REQUEST["formRefPerson"]) ? $_REQUEST["formRefPerson"] : "-",
    "formRefPartner" => !empty($_REQUEST["formRefPartner"]) ? $_REQUEST["formRefPartner"] : "-",
    "formCoupon" => !empty($_REQUEST["formCoupon"]) ? $_REQUEST["formCoupon"] : "-",
    "formRefShop" => !empty($_REQUEST["formRefShop"]) ? $_REQUEST["formRefShop"] : "-",
    "formFirstTimePayment" => !empty($_REQUEST["formFirstTimePayment"]) ? $_REQUEST["formFirstTimePayment"] : "-",
    "formPaymentMethod" => !empty($_REQUEST["formPaymentMethod"]) ? $_REQUEST["formPaymentMethod"] : "-",
    "formFlyer" => !empty($_REQUEST["formFlyer"]) ? $_REQUEST["formFlyer"] : "Do not need",
    "formDineIn" => !empty($_REQUEST["formDineIn"]) ? $_REQUEST["formDineIn"] : "Do not need",
    "formMagnet" => !empty($_REQUEST["formMagnet"]) ? $_REQUEST["formMagnet"] : "Do not need",
    "formSocialMedia" => !empty($_REQUEST["formSocialMedia"]) ? $_REQUEST["formSocialMedia"] : "Do not need",
    "formMenuDesign" => !empty($_REQUEST["formMenuDesign"]) ? $_REQUEST["formMenuDesign"] : "Do not need",
    "formWebsiteMakeOver" => !empty($_REQUEST["formWebsiteMakeOver"]) ? $_REQUEST["formWebsiteMakeOver"] : "Do not need",
    "formADVPromo" => !empty($_REQUEST["formADVPromo"]) ? $_REQUEST["formADVPromo"] : "Do not need",
    "formWebHosting" => !empty($_REQUEST["formWebHosting"]) ? $_REQUEST["formWebHosting"] : "Do not need",
    "formInfluencer" => !empty($_REQUEST["formInfluencer"]) ? $_REQUEST["formInfluencer"] : "Do not need",
    "formCustomerType" => !empty($_REQUEST["formCustomerType"]) ? $_REQUEST["formCustomerType"] : "-",
    "formShopName" => !empty($_REQUEST["formShopName"]) ? $_REQUEST["formShopName"] : "-",
    "formCountry" => !empty($_REQUEST["formCountry"]) ? $_REQUEST["formCountry"] : "-",
    "formAddress" => !empty($_REQUEST["ShippingAddress"]) ? $_REQUEST["ShippingAddress"] : "-",
    "formFullName" => !empty($_REQUEST["formFullName"]) ? $_REQUEST["formFullName"] : "-",
    "formEmail" => !empty($_REQUEST["formEmail"]) ? $_REQUEST["formEmail"] : "-",
    "formMobile" => !empty($_REQUEST["formMobile"]) ? $_REQUEST["formMobile"] : "-",
    "formBestTime" => !empty($_REQUEST["formBestTime"]) ? $_REQUEST["formBestTime"] : "-",
    "formstartProjectAs" => !empty($_REQUEST["formstartProjectAs"]) ? $_REQUEST["formstartProjectAs"] : false,
    "formstartProjectOther" => !empty($_REQUEST["formstartProjectOther"]) ? $_REQUEST["formstartProjectOther"] : "-",
    "formstartprojectNote" => !empty($_REQUEST["formstartprojectNote"]) ? $_REQUEST["formstartprojectNote"] : "-",
    "formNote" => !empty($_REQUEST["formNote"]) ? $_REQUEST["formNote"] : "-",
    "formPOSUsing" => !empty($_REQUEST["formPOSUsing"]) ? $_REQUEST["formPOSUsing"] : "none",
    "formPOSUsingOther" => !empty($_REQUEST["formPOSUsingOther"]) ? $_REQUEST["formPOSUsingOther"] : "-",
    "formNoPOSProvider" => !empty($_REQUEST["formNoPOSProvider"]) ? $_REQUEST["formNoPOSProvider"] : false,
    "formYesPOSProvider" => !empty($_REQUEST["formYesPOSProvider"]) ? $_REQUEST["formYesPOSProvider"] : "-",
    "acceptAutoPilotAI" => !empty($_REQUEST["acceptAutoPilotAI"]) ? $_REQUEST["acceptAutoPilotAI"] : false,
    "mode" => !empty($_REQUEST["mode"]) ? $_REQUEST["mode"] : "",
    "token" => !empty($_REQUEST["token"]) ? $_REQUEST["token"] : "",
    "formInitialProductOffering" => !empty($_REQUEST["formInitialProductOffering"]) ? $_REQUEST["formInitialProductOffering"] : "",
    "testMail" => !empty($_REQUEST["testMail"]) ? $_REQUEST["testMail"] : "0",
);

$noPOS = $param["formNoPOSProvider"];
$yesPOS = $param["formYesPOSProvider"];
$posProvider = "";

if ($noPOS = false){
    echo "-";
}

if (!empty($noPOS)) {
    $posProvider = $noPOS;
}else if (!empty($yesPOS)) {
    $posProvider = $yesPOS;
}else{
    $posProvider = "-";
}//POS


$startDate = $param["formstartProjectAs"];
$otherDate = $param["formstartProjectOther"];
$dateStart = "";

if (!empty($startDate)) {
     $dateStart = $startDate; 
}else if (!empty($otherDate)) {
     $dateStart = $otherDate; }
else{ $dateStart = "-"; } //Date Start Project

// test data
 /*$param = array(
     "formDate" => "14/02/2025",
     "leadSource" => "Test Sign Up Form",
     "formVersion" => "2.9.5 UK+Promotion",
     "MainProduct" => "Promotion - Direct Marketing Solo",
     "formSalesAgent" => "Honey Tummaput",
     "formContractPeriod" => "",
     "formRefPerson" => "",
     "formRefPartner" => "",
     "formCoupon" => "",
     "formRefShop" => "",
     "formFirstTimePayment" => "109.00",
     "formPaymentMethod" => "Invoice",
     "formFlyer" => "",
     "formDineIn" => "",
     "formMagnet" => "",
     "formSocialMedia" => "",
     "formMenuDesign" => "",
     "formWebsiteMakeOver" => "",
     "formADVPromo" => "",
     "formWebHosting" => "",
     "formInfluencer" => "",
     "formCustomerType" => "Thai Massage",
     "formShopName" => "Lifestyle Thai Massage Health Wellbeing Glasgow",
     "formCountry" => "UK",
     "formState" => "",
     "formFullName" => "Jeeranun Martin",
     "formEmail" => "lifestylethaimassagestudio@gmail.com",
     "formMobile" => "+447707223389",
     "formBestTime" => "thursday - friday at 4-5 pm",
     "acceptAutoPilotAI" => true,
     "formNote" => "This is test data",
     "formstartProjectAs" => false,
    "formstartProjectOther" => "2025-02-28",
    "formstartprojectNote" => "",
     "mode" => "alert",
     "token" => "6.552534",
     "formInitialProductOffering" => "",
     "testMail" => "1"
 );

$param['mode'] = "alert";*/
//end test data

$system = array(
    "emailSenderName" => "Signup Form",
    "emailSenderEmail" => "neung@localforyou.com",
    "emailSubject" => "New ". $param["formCountry"]." Subscriber",
    "emailAdministrator" => "neung@localforyou.com"
);

$system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Customer Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>Street Address</th><td>'.$param["formAddress"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>POS</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>POS Brand</th><td>'.$param["formPOSUsing"].'</td></tr><tr><th>End contract</th><td>'.$posProvider.'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>form version</th><td>'.$param["formVersion"].'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';

if ( (str_contains($param["MainProduct"], 'Solo')) or (str_contains($param["MainProduct"], 'Yelp')) ) {
    $system["emailAlertTo"] = "promotion@localforyou.com";
//    $result["case"] = "Send Email To AC Team";
}else {
    $system["emailAlertTo"] = "admin@localforyou.com";
//    $result["case"] = "Send Email To CS Team";
}

if(empty($param['testMail'])) {
    $mailHeaders = [
        'From' => 'SignUp Form <noreply@localforyou.com>',
        'Cc' => 'sales@localforyou.com, stevew@localforyou.com, bas@localforyou.com, mark@localforyou.com',
        'Bcc' => ' neung@localforyou.com',
        'Reply-To' => 'neung@localforyou.com',
        'X-Sender' => 'LocalForYou <neung@localforyou.com>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => 'neung@localforyou.com',
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=utf-8'
    ];
} else {
    $system["emailAlertTo"] = "neung@localforyou.com";
    $mailHeaders = [
        'From' => 'Test SignUp Form <noreply@localforyou.com>',
        'Cc' => 'bas@localforyou.com, mark@localforyou.com',
        'Reply-To' => 'neung@localforyou.com',
        'X-Sender' => 'LocalForYou <neung@localforyou.com>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => 'neung@localforyou.com',
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=utf-8'
    ];
}

if($param['mode'] == "alert") {
    $result['email'] = $system["emailAlertTo"];
    $result['mode'] = $param['mode'];
    $result['product'] = $param["MainProduct"];

    if (mail($system["emailAlertTo"], $system["emailSubject"], $system["emailBody"], $mailHeaders)) {
        $result['success'] = true;
        $result['result'] = 1;
        $result['msg'] = "Send email successful";
    }
}//if

echo json_encode($result);
//prview arrey $oaram
/*echo "<br><br>";
echo "<pre>".print_r($param, true)."</pre>";*/

?>