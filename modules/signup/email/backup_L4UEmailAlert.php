<?php
$result = array(
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0
);

$param = array(
    "formDate" => !empty($_POST["formDate"]) ? $_POST["formDate"] : "-",
    "leadSource" => !empty($_POST["leadSource"]) ? $_POST["leadSource"] : "-",
    "formVersion" => !empty($_POST["formVersion"]) ? $_POST["formVersion"] : "-",
    "formMessage" => !empty($_POST["formMessage"]) ? $_POST["formMessage"] : "-",
    "formProduct" => !empty($_POST["formProduct"]) ? $_POST["formProduct"] : "-",
    "formSalesAgent" => !empty($_POST["formSalesAgent"]) ? $_POST["formSalesAgent"] : "-",
    "formContractPeriod" => !empty($_POST["formContractPeriod"]) ? $_POST["formContractPeriod"] : "-",
    "formRefPerson" => !empty($_POST["formRefPerson"]) ? $_POST["formRefPerson"] : "-",
    "formCoupon" => !empty($_POST["formCoupon"]) ? $_POST["formCoupon"] : "-",
    "formRefShop" => !empty($_POST["formRefShop"]) ? $_POST["formRefShop"] : "-",
    "formFirstTimePayment" => !empty($_POST["formFirstTimePayment"]) ? $_POST["formFirstTimePayment"] : "-",
    "formPaymentMethod" => !empty($_POST["formPaymentMethod"]) ? $_POST["formPaymentMethod"] : "-",
    "formFlyer" => !empty($_POST["formFlyer"]) ? $_POST["formFlyer"] : "Do not need",
    "formDineIn" => !empty($_POST["formDineIn"]) ? $_POST["formDineIn"] : "Do not need",
    "formMagnet" => !empty($_POST["formMagnet"]) ? $_POST["formMagnet"] : "Do not need",
    "formSocialMedia" => !empty($_POST["formSocialMedia"]) ? $_POST["formSocialMedia"] : "Do not need",
    "formMenuDesign" => !empty($_POST["formMenuDesign"]) ? $_POST["formMenuDesign"] : "Do not need",
    "formWebsiteMakeOver" => !empty($_POST["formWebsiteMakeOver"]) ? $_POST["formWebsiteMakeOver"] : "Do not need",
    "formADVPromo" => !empty($_POST["formADVPromo"]) ? $_POST["formADVPromo"] : "Do not need",
    "formWebHosting" => !empty($_POST["formWebHosting"]) ? $_POST["formWebHosting"] : "Do not need",
    "formInfluencer" => !empty($_POST["formInfluencer"]) ? $_POST["formInfluencer"] : "Do not need",
    "formCustomerType" => !empty($_POST["formCustomerType"]) ? $_POST["formCustomerType"] : "-",
    "formShopName" => !empty($_POST["formShopName"]) ? $_POST["formShopName"] : "-",
    "formCountry" => !empty($_POST["formCountry"]) ? $_POST["formCountry"] : "-",
    "formState" => !empty($_POST["formState"]) ? $_POST["formState"] : "-",
    "formFullName" => !empty($_POST["formFullName"]) ? $_POST["formFullName"] : "-",
    "formEmail" => !empty($_POST["formEmail"]) ? $_POST["formEmail"] : "-",
    "formMobile" => !empty($_POST["formMobile"]) ? $_POST["formMobile"] : "-",
    "formBestTime" => !empty($_POST["formBestTime"]) ? $_POST["formBestTime"] : "-",
    "formstartProjectAs" => !empty($_POST["formstartProjectAs"]) ? $_POST["formstartProjectAs"] : false,
    "formstartProjectOther" => !empty($_POST["formstartProjectOther"]) ? $_POST["formstartProjectOther"] : "-",
    "formstartprojectNote" => !empty($_POST["formstartprojectNote"]) ? $_POST["formstartprojectNote"] : "-",
    "formNote" => !empty($_POST["formNote"]) ? $_POST["formNote"] : "-",
    "acceptAutoPilotAI" => !empty($_POST["acceptAutoPilotAI"]) ? $_POST["acceptAutoPilotAI"] : false,
    "mode" => !empty($_POST["mode"]) ? $_POST["mode"] : "",
    "token" => !empty($_POST["token"]) ? $_POST["token"] : "",
    "formInitialProductOffering" => !empty($_POST["formInitialProductOffering"]) ? $_POST["formInitialProductOffering"] : ""
);

// test data
/*$param = array(
    "formDate" => "05/02/2025",
    "leadSource" => "Sign Up Form",
    "formVersion" => "2.7.1",
    "formProduct" => "Pro Online Ordering Systems",
    "formSalesAgent" => "Sorasak Thanomsap",
    "formContractPeriod" => "0 Months",
    "formRefPerson" => "Jane Doe",
    "formCoupon" => "",
    "formRefShop" => "L4U test shop",
    "formFirstTimePayment" => "$1.0 AUD",
    "formPaymentMethod" => "Invoice",
    "formFlyer" => "Do not need",
    "formDineIn" => "Do not need",
    "formMagnet" => "Do not need",
    "formSocialMedia" => "Do not need",
    "formMenuDesign" => "Do not need",
    "formWebsiteMakeOver" => "Do not need",
    "formADVPromo" => "Do not need",
    "formWebHosting" => "Do not need",
    "formInfluencer" => "Do not need",
    "formCustomerType" => "Thai Restaurants &amp; Takeaways",
    "formShopName" => "Bas Test Shop",
    "formCountry" => "Australia",
    "formState" => "VI : Victoria",
    "formFullName" => "Neung Test Shop AU",
    "formEmail" => "bas@localforyou.com",
    "formMobile" => "0891234567",
    "formBestTime" => "All day All night",
    "formstartProjectAs" => "Do not need",
    "formstartProjectOther" => "Do not need",
    "formstartprojectNote" => "Do not need",
    "acceptAutoPilotAI" => true,
    "formNote" => "This is test data",
    "mode" => "alert",
    "token" => "6.552534",
    "formInitialProductOffering" => "Pro Online Ordering Systems"
);

$param['mode'] = "alert";*/
// end test data

$system = array(
    "emailSenderName" => "Signup Form",
    "emailSenderEmail" => "neung@localforyou.com",
    "emailSubject" => "New ". $param["formCountry"]." Subscriber",
    "emailAdministrator" => "neung@localforyou.com"
);

$system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["formProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$param["formstartProjectAs"].'</td></tr><tr><th>Start Project Date Other</th><td>'.$param["formstartProjectOther"].'</td></tr><tr><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Customer Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>State</th><td>'.$param["formState"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>form version</th><td>'.$param["formVersion"].'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';

if (str_contains($param["formProduct"], 'Solo')){
    $system["emailAlertTo"] = "promotion@localforyou.com";
}else {
    $system["emailAlertTo"] = "admin@localforyou.com";
}

//$system["emailAlertTo"] = "iamatomix@gmail.com"; // test data

$mailHeaders = [
    'From' => 'SignUp Form <noreply@localforyou.com>',
    'Cc' => 'sales@localforyou.com, stevew@localforyou.com',
    'Bcc' => 'bas@localforyou.com, neung@localforyou.com',
    'Reply-To' => 'neung@localforyou.com',
    'X-Sender' => 'LocalForYou <neung@localforyou.com>',
    'X-Mailer' => 'PHP/' . phpversion(),
    'X-Priority' => '1',
    'Return-Path' => 'neung@localforyou.com',
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=utf-8'
];

if($param['mode'] == "alert") {
    $result['email'] = $system["emailAlertTo"];
    $result['mode'] = $param['mode'];
    $result['product'] = $param["formProduct"];

    if (mail($system["emailAlertTo"], $system["emailSubject"], $system["emailBody"], $mailHeaders)) {
        $result['success'] = true;
        $result['result'] = 1;
        $result['msg'] = "Send email successful";
    }
}//if

echo json_encode($result);
?>