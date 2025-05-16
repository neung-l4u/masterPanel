<?php
date_default_timezone_set("Asia/Bangkok");
$timeStamps = date("H:i D ,d M Y")." (BKK)";
$result = array(
    "when" => date("Y-m-d H:i:s"),
    "success" => false,
    "msg" => "Send email fail!!",
    "result" => 0,
//    "case" => "No Case"
);

//formProduct: $("#currentlyPackage option:selected").text(), อันนี้เลิกใช้ ใช้ MainProduct แทน


$param = array(
    "formDate" => !empty($_POST["formDate"]) ? $_POST["formDate"] : "-",
    "leadSource" => !empty($_POST["leadSource"]) ? $_POST["leadSource"] : "-",
    "formVersion" => !empty($_POST["formVersion"]) ? $_POST["formVersion"] : "-",
    "formMessage" => !empty($_POST["formMessage"]) ? $_POST["formMessage"] : "-",
    "formProduct" => !empty($_POST["formProduct"]) ? $_POST["formProduct"] : "-",
    "MainProduct" => !empty($_POST["MainProduct"]) ? $_POST["MainProduct"] : "-",
    "formSalesAgent" => !empty($_POST["formSalesAgent"]) ? $_POST["formSalesAgent"] : "-",
    "formContractPeriod" => !empty($_POST["formContractPeriod"]) ? $_POST["formContractPeriod"] : "-",
    "formRefPerson" => !empty($_POST["formRefPerson"]) ? $_POST["formRefPerson"] : "-",
    "formRefPartner" => !empty($_POST["formRefPartner"]) ? $_POST["formRefPartner"] : "-",
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
    "formAddress" => !empty($_POST["ShippingAddress"]) ? $_POST["ShippingAddress"] : "-",
    "formFullName" => !empty($_POST["formFullName"]) ? $_POST["formFullName"] : "-",
    "formEmail" => !empty($_POST["formEmail"]) ? $_POST["formEmail"] : "-",
    "formMobile" => !empty($_POST["formMobile"]) ? $_POST["formMobile"] : "-",
    "formBestTime" => !empty($_POST["formBestTime"]) ? $_POST["formBestTime"] : "-",
    "formstartProjectAs" => !empty($_POST["formstartProjectAs"]) ? $_POST["formstartProjectAs"] : false,
    "formstartProjectOther" => !empty($_POST["formstartProjectOther"]) ? $_POST["formstartProjectOther"] : "-",
    "formstartprojectNote" => !empty($_POST["formstartprojectNote"]) ? $_POST["formstartprojectNote"] : "-",
    "formNote" => !empty($_POST["formNote"]) ? $_POST["formNote"] : "-",
    "formPOSUsing" => !empty($_POST["formPOSUsing"]) ? $_POST["formPOSUsing"] : "none",
    "formPOSUsingOther" => !empty($_POST["formPOSUsingOther"]) ? $_POST["formPOSUsingOther"] : "-",
    "formNoPOSProvider" => !empty($_POST["formNoPOSProvider"]) ? $_POST["formNoPOSProvider"] : false,
    "formYesPOSProvider" => !empty($_POST["formYesPOSProvider"]) ? $_POST["formYesPOSProvider"] : "-",
    "acceptAutoPilotAI" => !empty($_POST["acceptAutoPilotAI"]) ? $_POST["acceptAutoPilotAI"] : false,
    "mode" => !empty($_POST["mode"]) ? $_POST["mode"] : "",
    "token" => !empty($_POST["token"]) ? $_POST["token"] : "",
    "formInitialProductOffering" => !empty($_POST["formInitialProductOffering"]) ? $_POST["formInitialProductOffering"] : "",
    //new//
    "formShopNumber" => !empty($_POST["formShopNumber"]) ? $_POST["formShopNumber"] : "-",
    "formTradingName" => !empty($_POST["formTradingName"]) ? $_POST["formTradingName"] : "-",
    "formShopPhoneNumber" => !empty($_POST["formShopPhoneNumber"]) ? $_POST["formShopPhoneNumber"] : "-",
    "formShopWebsite" => !empty($_POST["formShopWebsite"]) ? $_POST["formShopWebsite"] : "-",
    "formOwnerFirstLanguageTH" => !empty($_POST["formOwnerFirstLanguageTh"]) ? $_POST["formOwnerFirstLanguageTh"] : false,
    "formOwnerFirstLanguageEng" => !empty($_POST["formOwnerFirstLanguageEng"]) ? $_POST["formOwnerFirstLanguageEng"] : false,
    "formOwnerFirstLanguageEngTH" => !empty($_POST["formOwnerFirstLanguageEngTH"]) ? $_POST["formOwnerFirstLanguageEngTH"] : false,

    "cuisinesOther" => !empty($_POST["cuisinesOther"]) ? $_POST["cuisinesOther"] : "-",
    "formCuisineOther" => !empty($_POST["formCuisineOther"]) ? $_POST["formCuisineOther"] : "-",

    "formSetupFee0" => !empty($_POST["formSetupFee0"]) ? $_POST["formSetupFee0"] : false,
    "formSetupFee3" => !empty($_POST["formSetupFee3"]) ? $_POST["formSetupFee3"] : false,
    "formSetupFee12" => !empty($_POST["formSetupFee12"]) ? $_POST["formSetupFee12"] : false,

    "formLoginEmailBookingSystem" => !empty($_POST["formLoginEmailBookingSystem"]) ? $_POST["formLoginEmailBookingSystem"] : "-",
    "formPasswordBookingSystem" => !empty($_POST["formPasswordBookingSystem"]) ? $_POST["formPasswordBookingSystem"] : "-",

    "formLoginEmailOnlineOrderingSystem" => !empty($_POST["formLoginEmailOnlineOrderingSystem"]) ? $_POST["formLoginEmailOnlineOrderingSystem"] : "-",
    "formPasswordOnlineOrderingSystem" => !empty($_POST["formPasswordOnlineOrderingSystem"]) ? $_POST["formPasswordOnlineOrderingSystem"] : "-",

    "formPinkUp" => !empty($_POST["formPinkUp"]) ? $_POST["formPinkUp"] : false,
    "formTableReservation" => !empty($_POST["formTableReservation"]) ? $_POST["formTableReservation"] : false,

    "formDineInTableOrdering" => !empty($_POST["formDineInTableOrdering"]) ? $_POST["formDineInTableOrdering"] : false,
    "dineInTable" => !empty($_POST["dineInTable"]) ? $_POST["dineInTable"] : "-",
    "dineInSize" => !empty($_POST["dineInSize"]) ? $_POST["dineInSize"] : "-",

    "delivery" => !empty($_POST["delivery"]) ? $_POST["delivery"] : false,
    "deliveryYourOwn" => !empty($_POST["deliveryYourOwn"]) ? $_POST["deliveryYourOwn"] : false,
    "deliverySystemDriver" => !empty($_POST["deliverySystemDriver"]) ? $_POST["deliverySystemDriver"] : false,
    "ihdEmail" => !empty($_POST["ihdEmail"]) ? $_POST["ihdEmail"] : "-",
    "ihdPw" => !empty($_POST["ihdPw"]) ? $_POST["ihdPw"] : "-",
    "ihdToken" => !empty($_POST["ihdToken"]) ? $_POST["ihdToken"] : "-",

    "cash" => !empty($_POST["cash"]) ? $_POST["cash"] : false,
    "cardCounter" => !empty($_POST["cardCounter"]) ? $_POST["cardCounter"] : false,
    "callBack" => !empty($_POST["callBack"]) ? $_POST["callBack"] : false,
    "payOnline" => !empty($_POST["payOnline"]) ? $_POST["payOnline"] : false,

    "facebook" => !empty($_POST["facebook"]) ? $_POST["facebook"] : "-",
    "tiktok" => !empty($_POST["tiktok"]) ? $_POST["tiktok"] : "-",
    "instagram" => !empty($_POST["instagram"]) ? $_POST["instagram"] : "-",
    "yelp" => !empty($_POST["yelp"]) ? $_POST["yelp"] : "-",

    "websiteDomainName" => !empty($_POST["websiteDomainName"]) ? $_POST["websiteDomainName"] : "-",
    "keepWebsite" => !empty($_POST["keepWebsite"]) ? $_POST["keepWebsite"] : false,
    "ownDomain" => !empty($_POST["ownDomain"]) ? $_POST["ownDomain"] : false,

    "websiteNewDomain" => !empty($_POST["websiteNewDomain"]) ? $_POST["websiteNewDomain"] : "-",

    "loginInfoU" => !empty($_POST["loginInfoU"]) ? $_POST["loginInfoU"] : "-",
    "loginInfoP" => !empty($_POST["loginInfoP"]) ? $_POST["loginInfoP"] : "-",
    "loginInfoComments" => !empty($_POST["loginInfoComments"]) ? $_POST["loginInfoComments"] : "-",
    "loginInfoRegistered" => !empty($_POST["loginInfoRegistered"]) ? $_POST["loginInfoRegistered"] : "-",

    "firstOrderDiscount0" => !empty($_POST["firstOrderDiscount0"]) ? $_POST["firstOrderDiscount0"] : false,
    "firstOrderDiscount10" => !empty($_POST["firstOrderDiscount10"]) ? $_POST["firstOrderDiscount10"] : false,
    "firstOrderDiscount15" => !empty($_POST["firstOrderDiscount15"]) ? $_POST["firstOrderDiscount15"] : false,
    "firstOrderDiscount20" => !empty($_POST["firstOrderDiscount20"]) ? $_POST["firstOrderDiscount20"] : false,
    "firstOrderDiscountOther" => !empty($_POST["firstOrderDiscountOther"]) ? $_POST["firstOrderDiscountOther"] : false,
    "firstOrderDiscountOtherValue" => !empty($_POST["firstOrderDiscountOtherValue"]) ? $_POST["firstOrderDiscountOtherValue"] : "-",
    //end new//
    "testMail" => !empty($_POST["testMail"]) ? $_POST["testMail"] : "0"

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
    $dateStart = $otherDate;
}else{
    $dateStart = "-";
}

$keepWebsite = "";
if ($param["keepWebsite"] == true){
    $keepWebsite = "Yes";
}else if ($param["keepWebsite"] == false){
    $keepWebsite = "No";
}

$ownDomain = "";
if ($param["ownDomain"] == true){
    $ownDomain = "Yes";
}else if ($param["ownDomain"] == false){
    $ownDomain = "No";
}

$firstOrderDiscount = "";
if ($param["firstOrderDiscount0"] == true){
    $firstOrderDiscount = "0%";
    echo "Discount 0%";
} else if ($param["firstOrderDiscount10"] == true){
    $firstOrderDiscount = "10%";
    echo "Discount 10%";
} else if ($param["firstOrderDiscount15"] == true){
    $firstOrderDiscount = "15%";
    echo "Discount 15%";
} else if ($param["firstOrderDiscount20"] == true){
    $firstOrderDiscount = "20%";
    echo "Discount 20%";
} else if ($param["firstOrderDiscountOther"] == true){
    $firstOrderDiscount = $param["firstOrderDiscountOtherValue"] . "%";
    echo "Custom Discount " . $firstOrderDiscount;
} else {
    echo "No Discount Selected";
}

$autoPilotAI = "";
if ($param["acceptAutoPilotAI"] == true){
    $autoPilotAI = "I acknowledge and agree.";
}else if ($param["acceptAutoPilotAI"] == false){
    $autoPilotAI = "I acknowledge and agree.";
}



$systemShop = $param["formCustomerType"];
$systemForShop = "";

if ($systemShop == "Thai Massage"){
    $systemForShop = "Booking";
}else if ($systemShop == "Thai Restaurants & Takeaways"){
    $systemForShop = "Online Ordering";
}

$loginSystemBooking = $param["formLoginEmailBookingSystem"];
$passwordSystemBooking = $param["formPasswordBookingSystem"];

$loginSystemOnline = $param["formLoginEmailOnlineOrderingSystem"];
$passwordSystemOnline = $param["formPasswordOnlineOrderingSystem"];

$loginSystem = "";
$passwordSystem = "";

if ($systemShop == "Thai Massage"){
    $loginSystem = $loginSystemBooking;
    $passwordSystem = $passwordSystemBooking;
}else if ($systemShop == "Thai Restaurants & Takeaways"){
    $loginSystem = $loginSystemOnline;
    $passwordSystem = $passwordSystemOnline;
}//end password system

$pinkUp = "";
if ($param["formPinkUp"] == false){
    $pinkUp = "No";
    echo "Not in Pink Up";
}else if ($param["formPinkUp"] == true){
    $pinkUp = "Yes";
    echo "Pink Up Success";
}

$tableReservation = "";
if ($param["formTableReservation"] == false){
    $tableReservation = "No";
    echo "Not in Table Reservation";
}else if ($param["formTableReservation"] == true){
    $tableReservation = "Yes";
    echo "Table Reservation Success";
}

$dineIn = "";
if ($param["formDineInTableOrdering"] == false){
    $dineIn = "No";
    echo "Not in Dine In";
}else if ($param["formDineInTableOrdering"] == true){
    $dineIn = "Table :".$param["dineInTable"].", ". "Size :".$param["dineInSize"];
    echo "Dine In Success";
}



$delivery = "";
$deliveryYourOwn = "";
$driverNetwork = "";

if ($param["deliveryYourOwn"] == true){
    $deliveryYourOwn = "Your own driver only";
} else {
    $deliveryYourOwn = "-";
}

if ($param["deliverySystemDriver"] == true){
    $driverNetwork = "I Need IHD";
} else {
    $driverNetwork = "-";
}


$setupFee0 = $param["formSetupFee0"];
$setupFee3 = $param["formSetupFee3"];
$setupFee12 = $param["formSetupFee12"];
$setupFee = "";

if ($setupFee0 == true) {
    $setupFee = "0.00 + GST";
    echo "0.00 + GST Success<br>";
} else {
    echo "0.00 + GST Fail<br>";
}

if ($setupFee3 == true) {
    $setupFee = "149.00 + GST";
    echo "149.00 + GST Success<br>";
} else {
    echo "149.00 + GST Fail<br>";
}

if ($setupFee12 == true) {
    $setupFee = "399.00 + GST";
    echo "399.00 + GST Success<br>";
} else {
    echo "399.00 + GST Fail<br>";
}
//End Setup Fee

$ownerFirstLanguage = "";

if ($param["formOwnerFirstLanguageTH"] == true) {
    $ownerFirstLanguage = "TH";
    echo "Owner Language: " . $ownerFirstLanguage . "<br>";
} else if ($param["formOwnerFirstLanguageEng"] == true) {
    $ownerFirstLanguage = "EN";
    echo "Owner Language: " . $ownerFirstLanguage . "<br>";
} else if ($param["formOwnerFirstLanguageEngTH"] == true) {
    $ownerFirstLanguage = "EN-TH";
    echo "Owner Language: " . $ownerFirstLanguage . "<br>";
}
//End if languagae


//$cuisine = $param["Cuisine"];
//$food =  implode(",",$cuisine);





$noPOS = $param["formNoPOSProvider"];
$yesPOS = $param["formYesPOSProvider"];
$posProvider = "";

if ($noPOS == false){
    echo "-";
}

$cash = "";
$cardCounter = "";
$callBack = "";
$payOnline = "";

// เช็กทีละอันแบบแยก
if ($param["cash"] == true){
    $cash = "Yes";
    echo "Cash<br>";
} else {
    $cash = "No";
    echo "Cash Fail<br>";
}

if ($param["cardCounter"] == true){
    $cardCounter = "Yes";
    echo "Card Counter Success<br>";
} else {
    $cardCounter = "No";
    echo "Card Counter Fail<br>";
}

if ($param["callBack"] == true){
    $callBack = "Yes";
    echo "Call back and take card over phone Success<br>";
} else {
    $callBack = "No";
    echo "Call back and take card over phone Fail<br>";
}

if ($param["payOnline"] == true){
    $payOnline = "Yes";
    echo "Online Payments via Stripe Success<br>";
} else {
    $payOnline = "No";
    echo "Online Payments via Stripe Fail<br>";
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

if (($param["formCustomerType"] == "Thai Restaurants & Takeaways") or ($param["formCustomerType"] == "Restaurants & Takeaways")) {
    $system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Setup fee</th><td>'.$setupFee.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr><th>1st Order Discount</th><td>'.$firstOrderDiscount.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr><tr><th>AI-powered marketing</th><td>'.$autoPilotAI.'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Cuisine</th><td>'.$param["cuisinesOther"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr><th>Trading Name</th><td>'.$param["formTradingName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Number</th><td>'.$param["formShopNumber"].'</td></tr><tr><th>Shop Phone Number</th><td>'.$param["formShopPhoneNumber"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>Street Address</th><td>'.$param["formAddress"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Website</th><td>'.$param["formShopWebsite"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Owner First Language</th><td>'.$ownerFirstLanguage.'</td></tr><tr><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>'.$systemForShop.' System</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Login Email User</th><td>'.$loginSystem.'</td></tr><tr><th>Login Email Password</th><td>'.$passwordSystem.'</td></tr></table><table><caption>POS</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>POS Brand</th><td>'.$param["formPOSUsing"].'</td></tr><tr><th>End contract</th><td>'.$posProvider.'</td></tr></table><table><caption>Services</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Pick up</th><td>'.$pinkUp.'</td></tr><tr><th>Table Reservation</th><td>'.$tableReservation.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Dine-In table ordering</th><td>'.$dineIn.'</td></tr></table><table><caption>Delivery</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Your own driver only</th><td>'.$deliveryYourOwn.'</td></tr><tr><th>Connect to Delivery Driver network (IHD)</th><td>'.$driverNetwork.'</td></tr><tr><th>IHD Email</th><td>'.$param["ihdEmail"].'</td></tr><tr><th>IHD Password</th><td>'.$param["ihdPw"].'</td></tr><tr><th>IHD Token</th><td>'.$param["ihdToken"].'</td></tr></table><table><caption>Payment Options</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Cash</th><td>'.$cash.'</td></tr><tr><th>Card at Counter</th><td>'.$cardCounter.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Call back and take card over phone</th><td>'.$callBack.'</td></tr><tr><th>Online Payments via Stripe</th><td>'.$payOnline.'</td></tr></table><table><caption>Social Networks</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Facebook</th><td>'.$param["facebook"].'</td></tr><tr><th>Instagram</th><td>'.$param["instagram"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>TikTok</th><td>'.$param["tiktok"].'</td></tr><tr><th>Yelp</th><td>'.$param["yelp"].'</td></tr></table><table><caption>Domain Name</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Original Website</th><td>'.$param["websiteDomainName"].'</td></tr><tr><th>Keep existing website</th><td>'.$keepWebsite.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>I Own this domain name</th><td>'.$ownDomain.'</td></tr><tr><th>User Login</th><td>'.$param["loginInfoU"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Password Login</th><td>'.$param["loginInfoP"].'</td></tr><tr><th>Comment</th><td>'.$param["loginInfoComments"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Registered</th><td>'.$param["loginInfoRegistered"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><div><p>Lead Source : '.$param["leadSource"].'</p><p>Form Version : '.$param["formVersion"].'</p><p>Email Version : 2.0</p><p>Timestamps : '.$timeStamps.'</p></div><hr><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: IT Team - Distributed By: Local For You</p></div></body></html>';
}else if ($param["formCustomerType"] == "Thai Massage") {
    $system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Setup fee</th><td>'.$setupFee.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr><th>1st Order Discount</th><td>'.$firstOrderDiscount.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr><tr><th>AI-powered marketing</th><td>'.$autoPilotAI.'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Trading Name</th><td>'.$param["formTradingName"].'</td></tr><tr><th>Shop Number</th><td>'.$param["formShopNumber"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Phone Number</th><td>'.$param["formShopPhoneNumber"].'</td></tr><tr><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Street Address</th><td>'.$param["formAddress"].'</td></tr><tr><th>Shop Website</th><td>'.$param["formShopWebsite"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Owner First Language</th><td>'.$ownerFirstLanguage.'</td></tr><tr><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>'.$systemForShop.' System</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Login Email User</th><td>'.$loginSystem.'</td></tr><tr><th>Login Email Password</th><td>'.$passwordSystem.'</td></tr></table><table><caption>POS</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>POS Brand</th><td>'.$param["formPOSUsing"].'</td></tr><tr><th>End contract</th><td>'.$posProvider.'</td></tr></table><table><caption>Payment Options</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Cash</th><td>'.$cash.'</td></tr><tr><th>Card at Counter</th><td>'.$cardCounter.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Call back and take card over phone</th><td>'.$callBack.'</td></tr><tr><th>Online Payments via Stripe</th><td>'.$payOnline.'</td></tr></table><table><caption>Social Networks</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Facebook</th><td>'.$param["facebook"].'</td></tr><tr><th>Instagram</th><td>'.$param["instagram"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>TikTok</th><td>'.$param["tiktok"].'</td></tr><tr><th>Yelp</th><td>'.$param["yelp"].'</td></tr></table><table><caption>Domain Name</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Original Website</th><td>'.$param["websiteDomainName"].'</td></tr><tr><th>Keep existing website</th><td>'.$keepWebsite.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>I Own this domain name</th><td>'.$ownDomain.'</td></tr><tr><th>User Login</th><td>'.$param["loginInfoU"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Password Login</th><td>'.$param["loginInfoP"].'</td></tr><tr><th>Comment</th><td>'.$param["loginInfoComments"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Registered</th><td>'.$param["loginInfoRegistered"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><div><hr><p>Lead Source : '.$param["leadSource"].'</p><p>Form Version : '.$param["formVersion"].'</p><p>Email Version : 2.0</p><p>Timestamps : '.$timeStamps.'</p></div><hr><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: IT Team - Distributed By: Local For You</p></div></body></html>';
}

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
        'Cc' => 'sales@localforyou.com, stevew@localforyou.com',
        'Bcc' => 'bas@localforyou.com, neung@localforyou.com, mark@localforyou.com',
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
//echo "<br><br>";
//echo "<pre>".print_r($param, true)."</pre>";

?>