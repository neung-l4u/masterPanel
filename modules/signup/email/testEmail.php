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

/*$param = array(
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
    "formState" => !empty($_REQUEST["formState"]) ? $_REQUEST["formState"] : "-",
    "formAddress" => !empty($_REQUEST["ShippingAddress"]) ? $_REQUEST["ShippingAddress"] : "-",
    "formFullName" => !empty($_REQUEST["formFullName"]) ? $_REQUEST["formFullName"] : "-",
    "formEmail" => !empty($_REQUEST["formEmail"]) ? $_REQUEST["formEmail"] : "-",
    "formMobile" => !empty($_REQUEST["formMobile"]) ? $_REQUEST["formMobile"] : "-",
    "formBestTime" => !empty($_REQUEST["formBestTime"]) ? $_REQUEST["formBestTime"] : "-",
    "formstartProjectAs" => !empty($_REQUEST["formstartProjectAs"]) ? $_REQUEST["formstartProjectAs"] : false,
    "formstartProjectOther" => !empty($_REQUEST["formstartProjectOther"]) ? $_REQUEST["formstartProjectOther"] : "-",
    "formstartprojectNote" => !empty($_REQUEST["formstartprojectNote"]) ? $_REQUEST["formstartprojectNote"] : "-",
    "formNote" => !empty($_REQUEST["formNote"]) ? $_REQUEST["formNote"] : "-",
    "formPOSUsing" => !empty($_REQUEST["formPOSUsing"]) ? $_REQUEST["formPOSUsing"] : "-",
    "formPOSUsingOther" => !empty($_REQUEST["formPOSUsingOther"]) ? $_REQUEST["formPOSUsingOther"] : "-",
    "formNoPOSProvider" => !empty($_REQUEST["formNoPOSProvider"]) ? $_REQUEST["formNoPOSProvider"] : false,
    "formYesPOSProvider" => !empty($_REQUEST["formYesPOSProvider"]) ? $_REQUEST["formYesPOSProvider"] : "-",
    "acceptAutoPilotAI" => !empty($_REQUEST["acceptAutoPilotAI"]) ? $_REQUEST["acceptAutoPilotAI"] : false,
    "mode" => !empty($_REQUEST["mode"]) ? $_REQUEST["mode"] : "",
    "token" => !empty($_REQUEST["token"]) ? $_REQUEST["token"] : "",
    "formInitialProductOffering" => !empty($_REQUEST["formInitialProductOffering"]) ? $_REQUEST["formInitialProductOffering"] : "",
    "testMail" => !empty($_REQUEST["testMail"]) ? $_REQUEST["testMail"] : "0",
);*/




// test data
/* $param = array(
     "formDate" => "26/02/2025",
     "leadSource" => "Sign Up Form",
     "formVersion" => "2.9.7 UK+Promotion",
     "MainProduct" => "Social Media Marketing Bundle (Incl Website Hosting and Booking System) - $247.00  + GST /Month",
     "formSalesAgent" => "Pluem Pluemkamol",
     "formContractPeriod" => "12 months",
     "formRefPerson" => "-",
     "formRefPartner" => "-",
     "formCoupon" => "-",
     "formRefShop" => "-",
     "formFirstTimePayment" => "271.70 AUD",
     "formPaymentMethod" => "Credit Card",
     "formFlyer" => "Do not need",
     "formDineIn" => "Do not need",
     "formMagnet" => "Do not need",
     "formSocialMedia" => "Do not need",
     "formMenuDesign" => "Do not need",
     "formWebsiteMakeOver" => "Do not need",
     "formADVPromo" => "Do not need",
     "formWebHosting" => "Do not need",
     "formInfluencer" => "Do not need",
     "formCustomerType" => "Thai Massage",
     "formShopName" => "Byron Bay Thai Massage",
     "formCountry" => "Australia",
     "formAddress" => "1 Skinners Shoot Rd ,Byron Bay ,NS : New South Wales ,2481 ,Australia",
     "formFullName" => "Yupan Ardyata",
     "formEmail" => "yupanb@hotmail.com",
     "formMobile" => "+61447111238",
     "formBestTime" => "After 12pm",
     "acceptAutoPilotAI" => true,
     "formNote" => "off every Wednesday",
     "dateStart" => "2025-03-12",
     "formstartprojectNote" => "-",
     "mode" => "alert",
     "token" => "6.552534",
     "formInitialProductOffering" => "Localforyou Booking System (Incl Website Hosting and Booking System) - $97.00 /Month",
     "testMail" => "1"
 );*///Test Email Old Version

 $param = array(
    "formDate" => "11/04/2025",
    "leadSource" => "Sign Up Form",
    "formVersion" => "2.9.7 UK+Promotion",
    "MainProduct" => "Social Media Marketing Solo - $199.00  + GST /Month",
    "formSalesAgent" => "Test Dev",
    "formContractPeriod" => "No contract",
    "formRefPerson" => "-",
    "formRefPartner" => "-",
    "formCoupon" => "-",
    "formRefShop" => "-",
    "formFirstTimePayment" => "218.90 AUD",
    "formPaymentMethod" => "Credit Card",
    "formFlyer" => "Do not need",
    "formDineIn" => "Do not need",
    "formMagnet" => "Do not need",
    "formSocialMedia" => "Do not need",
    "formMenuDesign" => "Do not need",
    "formWebsiteMakeOver" => "Do not need",
    "formADVPromo" => "Do not need",
    "formWebHosting" => "Do not need",
    "formInfluencer" => "Do not need",
    "formCustomerType" => "Thai Restaurants & Takeaways",
    "formShopName" => "Test By Bas",
    "formCountry" => "Australia",
    "formAddress" => "80 Victoria Ave, Perth WA 6000 ,4701 ,Australia",
    "formFullName" => "Lucas Young",
    "formEmail" => "malimongcon@gmail.com",
    "formMobile" => "+61930396203",
    "formBestTime" => "9am - 5pm",
    "acceptAutoPilotAI" => true,
    "formNote" => "-",
    "formPOSUsing" => "-",
    "posProvider" => "-",
    "formstartProjectAs" => "As soon as possible.",
    "formstartProjectOther" => "2025-04-11",
    "formstartprojectNote" => "-",
    "posProvider" => "-",
    "mode" => "alert",
    "token" => "6.552534",
    "formInitialProductOffering" => "Social Media Marketing Solo - $199.00  + GST /Month",
    //New//
     "formShopNumber" => "123456789",
    "formTradingName" => "Test By Bas Trading Name",
     "formShopPhoneNumber" => "+61957176415",
    "formShopWebsite" => "testbybas.com",

     "formOwnerFirstLanguageTH" => false,
     "formOwnerFirstLanguageEng" => false,
     "formOwnerFirstLanguageEngTH" => true,

     "Cuisine" => "Thai,Pizza",

//     "formCuisine0" => false,
//     "formCuisine1" => false,
//     "formCuisine2" => false,
//     "formCuisine3" => false,
//     "formCuisine4" => false,
//     "formCuisine5" => false,
//     "formCuisine6" => false,
//     "formCuisine7" => true,
//     "formCuisine8" => false,
//     "formCuisine9" => false,
//     "formCuisine10" => false,
//     "formCuisine11" => false,
//     "formCuisine12" => false,
//     "formCuisine13" => false,
//     "formCuisine14" => false,
//     "formCuisine15" => false,
//     "formCuisine16" => true,
//     "formCuisine17" => false,
//     "formCuisine18" => false,
//     "formCuisine19" => true,
//     "formCuisine20" => false,
//     "formCuisine21" => false,
//     "formCuisine22" => false,
//     "formCuisine23" => false,
//     "formCuisine24" => false,
//     "formCuisine25" => false,
//     "formCuisine26" => false,
//     "formCuisine27" => false,
//     "formCuisine28" => false,
//     "formCuisine29" => false,

     "formSetupFee0" => false,
     "formSetupFee3" => false,
     "formSetupFee12" => true,

     "formLoginEmailOnlineOrderingSystem" => "malimongcon",
     "formPasswordOnlineOrderingSystem" => "Localeats",
     "formLoginEmailBookingSystem" => "-",
     "formPasswordBookingSystem" => "-",
     "formPinkUp" => true,
     "formTableReservation" => true,
     "formDineInTableOrdering" => true,
     "dineInTable" => "10",
     "dineInSize" => "A5",
     "delivery" => true,
     "deliveryYourOwn" => false,
     "deliverySystemDriver" => true,
     "ihdEmail" => "malimongconihdEmail",
     "ihdPw" => "LocaleatsIHDPassword",
     "ihdToken" => "LocaleatsIhdToken",

     "cash" => true,
     "cardCounter" => true,
     "callBack" => true,
     "payOnline" => true,

     "facebook" => "Facebook/Malimongcon",
     "tiktok" => "-",
     "instagram" => "-",
     "yelp" => "-",
     "websiteDomainName" => "malimongkhon.com",
     "keepWebsite" => true,
     "ownDomain" => false,
     "websiteNewDomain" => "BasLocal.com",
     "loginInfoU" => "BasLocal",
     "loginInfoP" => "BasLocal#2023!",
     "loginInfoComments" => "test comments",
     "loginInfoRegistered" => "Registered with L4U",
     "firstOrderDiscount0" => false,
     "firstOrderDiscount10" => false,
     "firstOrderDiscount15" => false,
     "firstOrderDiscount20" => true,
     "firstOrderDiscountOther" => false,
     "firstOrderDiscountOtherValue" => false,

     //End New//
    "testMail" => "1"//1=Test 0=Production
);

$cuisineMap = array(
    "formCuisine0" => "Thai",
    "formCuisine1" => "American",
    "formCuisine2" => "Asian",
    "formCuisine3" => "Breakfast",
    "formCuisine4" => "Burger",
    "formCuisine5" => "Cafe",
    "formCuisine6" => "Caribbean",
    "formCuisine7" => "Chinese",
    "formCuisine8" => "Coffee",
    "formCuisine9" => "Fast Foods",
    "formCuisine10" => "Fish and Chips",
    "formCuisine11" => "French",
    "formCuisine12" => "Greek",
    "formCuisine13" => "Indian",
    "formCuisine14" => "Italian",
    "formCuisine15" => "Japanese",
    "formCuisine16" => "Kebab",
    "formCuisine17" => "Lebanese",
    "formCuisine18" => "Mexican",
    "formCuisine19" => "North Indian",
    "formCuisine20" => "Pasta",
    "formCuisine21" => "Pizza",
    "formCuisine22" => "Salads",
    "formCuisine23" => "Sandwiches",
    "formCuisine24" => "Seafood",
    "formCuisine25" => "Spanish",
    "formCuisine26" => "Sushi",
    "formCuisine27" => "Turkish",
    "formCuisine28" => "Vietnamese",
    "formCuisine29" => $param["formCuisineOther"], // ถ้ามีค่า other
);//Menu


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
if ($param["keepWebsite"] = true){
    $keepWebsite = "Yes";
}else if ($param["keepWebsite"] = false){
    $keepWebsite = "No";
}

$ownDomain = "";
if ($param["ownDomain"] = true){
    $ownDomain = "Yes";
}else if ($param["ownDomain"] = false){
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
if ($param["acceptAutoPilotAI"] = true){
    $autoPilotAI = "I acknowledge and agree.";
    echo "AutoPilotAI Success";
}else if ($param["acceptAutoPilotAI"] = false){
    $autoPilotAI = "I don't know.";
    echo "AutoPilotAI Fail";
}


$cuisine = [];
$cuisineSt = "";
foreach ($cuisineMap as $key => $label) {
    if (isset($param[$key]) && $param[$key] == true) {
        $cuisine[] = $label;
    }
}
// แสดงผล
if (count($cuisine) > 0) {
    $cuisineSt = implode(", ", $cuisine);
} else {
    $cuisineSt = "No Cuisine Selected.";
}


//End if Cuisine
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
if ($param["formPinkUp"] = false){
    $pinkUp = "No";
    echo "Not in Pink Up";
}else if ($param["formPinkUp"] = true){
    $pinkUp = "Yes";
    echo "Pink Up Success";
}

$tableReservation = "";
if ($param["formTableReservation"] = false){
    $tableReservation = "No";
    echo "Not in Table Reservation";
}else if ($param["formTableReservation"] = true){
    $tableReservation = "Yes";
    echo "Table Reservation Success";
}

$dineIn = "";
if ($param["formDineInTableOrdering"] = false){
    $dineIn = "Not in use";
    echo "Not in Dine In";
}else if ($param["formDineInTableOrdering"] = true){
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



$noPOS = $param["formNoPOSProvider"];
$yesPOS = $param["formYesPOSProvider"];
$posProvider = "";

if ($noPOS = false){
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




$param['mode'] = "alert";
//end test data

$system = array(
//    "emailSenderName" => "Signup Form",
//    "emailSenderEmail" => "neung@localforyou.com",
//    "emailSubject" => "New ". $param["formCountry"]." Subscriber",
//    "emailAdministrator" => "neung@localforyou.com"
    /////Test /////
    "emailSenderName" => "Signup Form",
    "emailSenderEmail" => "bas@localforyou.com",
    "emailSubject" => "New ". $param["formCountry"]." Subscriber",
    "emailAdministrator" => "bas@localforyou.com"
    /// Test////
);

//$system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Customer Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>Street Address</th><td>'.$param["formAddress"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>form version</th><td>'.$param["formVersion"].'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';
//Production

if ($param["formCustomerType"] == "Thai Restaurants & Takeaways") {
    $system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Setup fee</th><td>'.$setupFee.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr><th>1st Order Discount</th><td>'.$firstOrderDiscount.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr><tr><th>AI-powered marketing</th><td>'.$autoPilotAI.'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Cuisine</th><td>'.$cuisineSt.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr><th>Trading Name</th><td>'.$param["formTradingName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Number</th><td>'.$param["formShopNumber"].'</td></tr><tr><th>Shop Phone Number</th><td>'.$param["formShopPhoneNumber"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>Street Address</th><td>'.$param["formAddress"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Website</th><td>'.$param["formShopWebsite"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Owner First Language</th><td>'.$ownerFirstLanguage.'</td></tr><tr><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>'.$systemForShop.' System</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Login Email User</th><td>'.$loginSystem.'</td></tr><tr><th>Login Email Password</th><td>'.$passwordSystem.'</td></tr></table><table><caption>POS</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>POS Brand</th><td>'.$param["formPOSUsing"].'</td></tr><tr><th>End contract</th><td>'.$posProvider.'</td></tr></table><table><caption>Services</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Pick up</th><td>'.$pinkUp.'</td></tr><tr><th>Table Reservation</th><td>'.$tableReservation.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Dine-In table ordering</th><td>'.$dineIn.'</td></tr></table><table><caption>Delivery</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Your own driver only</th><td>'.$deliveryYourOwn.'</td></tr><tr><th>Connect to Delivery Driver network (IHD)</th><td>'.$driverNetwork.'</td></tr><tr><th>IHD Email</th><td>'.$param["ihdEmail"].'</td></tr><tr><th>IHD Password</th><td>'.$param["ihdPw"].'</td></tr><tr><th>IHD Token</th><td>'.$param["ihdToken"].'</td></tr></table><table><caption>Payment Options</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Cash</th><td>'.$cash.'</td></tr><tr><th>Card at Counter</th><td>'.$cardCounter.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Call back and take card over phone</th><td>'.$callBack.'</td></tr><tr><th>Online Payments via Stripe</th><td>'.$payOnline.'</td></tr></table><table><caption>Social Networks</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Facebook</th><td>'.$param["facebook"].'</td></tr><tr><th>Instagram</th><td>'.$param["instagram"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>TikTok</th><td>'.$param["tiktok"].'</td></tr><tr><th>Yelp</th><td>'.$param["yelp"].'</td></tr></table><table><caption>Domain Name</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Original Website</th><td>'.$param["websiteDomainName"].'</td></tr><tr><th>Keep existing website</th><td>'.$keepWebsite.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>I Own this domain name</th><td>'.$ownDomain.'</td></tr><tr><th>User Login</th><td>'.$param["loginInfoU"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Password Login</th><td>'.$param["loginInfoP"].'</td></tr><tr><th>Comment</th><td>'.$param["loginInfoComments"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Registered</th><td>'.$param["loginInfoRegistered"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr><th>form version</th><td>'.$param["formVersion"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Timestamps</th><td>'.$timeStamps.'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';
}else if ($param["formCustomerType"] == "Restaurants & Takeaways") {
    $system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Setup fee</th><td>'.$setupFee.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr><th>1st Order Discount</th><td>'.$firstOrderDiscount.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr><tr><th>AI-powered marketing</th><td>'.$autoPilotAI.'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Cuisine</th><td>'.$cuisineSt.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr><th>Trading Name</th><td>'.$param["formTradingName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Number</th><td>'.$param["formShopNumber"].'</td></tr><tr><th>Shop Phone Number</th><td>'.$param["formShopPhoneNumber"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr><th>Street Address</th><td>'.$param["formAddress"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Website</th><td>'.$param["formShopWebsite"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Owner First Language</th><td>'.$ownerFirstLanguage.'</td></tr><tr><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>'.$systemForShop.' System</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Login Email User</th><td>'.$loginSystem.'</td></tr><tr><th>Login Email Password</th><td>'.$passwordSystem.'</td></tr></table><table><caption>POS</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>POS Brand</th><td>'.$param["formPOSUsing"].'</td></tr><tr><th>End contract</th><td>'.$posProvider.'</td></tr></table><table><caption>Services</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Pick up</th><td>'.$pinkUp.'</td></tr><tr><th>Table Reservation</th><td>'.$tableReservation.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Dine-In table ordering</th><td>'.$dineIn.'</td></tr></table><table><caption>Delivery</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Your own driver only</th><td>'.$deliveryYourOwn.'</td></tr><tr><th>Connect to Delivery Driver network (IHD)</th><td>'.$driverNetwork.'</td></tr><tr><th>IHD Email</th><td>'.$param["ihdEmail"].'</td></tr><tr><th>IHD Password</th><td>'.$param["ihdPw"].'</td></tr><tr><th>IHD Token</th><td>'.$param["ihdToken"].'</td></tr></table><table><caption>Payment Options</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Cash</th><td>'.$cash.'</td></tr><tr><th>Card at Counter</th><td>'.$cardCounter.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Call back and take card over phone</th><td>'.$callBack.'</td></tr><tr><th>Online Payments via Stripe</th><td>'.$payOnline.'</td></tr></table><table><caption>Social Networks</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Facebook</th><td>'.$param["facebook"].'</td></tr><tr><th>Instagram</th><td>'.$param["instagram"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>TikTok</th><td>'.$param["tiktok"].'</td></tr><tr><th>Yelp</th><td>'.$param["yelp"].'</td></tr></table><table><caption>Domain Name</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Original Website</th><td>'.$param["websiteDomainName"].'</td></tr><tr><th>Keep existing website</th><td>'.$keepWebsite.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>I Own this domain name</th><td>'.$ownDomain.'</td></tr><tr><th>User Login</th><td>'.$param["loginInfoU"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Password Login</th><td>'.$param["loginInfoP"].'</td></tr><tr><th>Comment</th><td>'.$param["loginInfoComments"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Registered</th><td>'.$param["loginInfoRegistered"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr><th>form version</th><td>'.$param["formVersion"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Timestamps</th><td>'.$timeStamps.'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';
}else if ($param["formCustomerType"] == "Thai Massage") {
    $system["emailBody"] = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>L4U</title><style>@media (min-width:700px){div.container{width:80%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:18px}table{width:90%;font-size:18px;border-collapse:collapse;margin-bottom:20px;margin-left:auto;margin-right:auto}td,th{font-size:16px;border:1px solid #aaa;padding:10px}th{text-align:left;background-color:#d6e6f4;color:#333;width:210px;max-width:300px}caption{font-weight:700;font-size:16px;margin-bottom:5px}hr{margin:15px 0 15px 0}}@media (max-width:1200px){div.container{width:90%;margin-left:auto;margin-right:auto}p{font-size:14px}body{font-size:16px}table{width:100%;font-size:18px;border-collapse:collapse;border:1px solid red;margin-bottom:30px;margin-left:auto;margin-right:auto}td,th{font-size:14px;border:1px solid #000;padding:10px}th{text-align:left;background-color:#666;color:#eee;width:30%;max-width:100px}caption{font-weight:700;font-size:14px;margin-bottom:15px}hr{margin:30px 0 30px 0}.no-margin{margin-bottom:0}}</style></head><body><div class="container"><img src="https://signup.localforyou.com/devV2.7/assets/img/newL4U-logo-100x100-2.png" alt="Logo" style="display:block;margin-left:auto;margin-right:auto"><h4>Date: '. $param["formDate"].'</h4><p>Hi, Team<br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.</p><br><table class="mobile"><caption>Sign Up Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Product</th><td>'.$param["MainProduct"].'</td></tr><tr><th>Sales Agent</th><td>'.$param["formSalesAgent"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Contract Period</th><td>'.$param["formContractPeriod"].'</td></tr><tr><th>Using coupon</th><td>'.$param["formCoupon"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>First time Payment</th><td>'.$param["formFirstTimePayment"].'</td></tr><tr><th>Setup fee</th><td>'.$setupFee.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Payment Method</th><td>'.$param["formPaymentMethod"].'</td></tr><tr><th>1st Order Discount</th><td>'.$firstOrderDiscount.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Person)</th><td>'.$param["formRefPerson"].'</td></tr><tr><th>Referred By (JV)</th><td>'.$param["formRefPartner"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Referred By (Restaurant)</th><td>'.$param["formRefShop"].'</td></tr><tr><th>Start Project Date</th><td>'.$dateStart.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Start Project Note</th><td>'.$param["formstartprojectNote"].'</td></tr><tr><th>AI-powered marketing</th><td>'.$autoPilotAI.'</td></tr></table><table><caption>Business Info</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Type</th><td>'.$param["formCustomerType"].'</td></tr><tr><th>Shop Name</th><td>'.$param["formShopName"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Trading Name</th><td>'.$param["formTradingName"].'</td></tr><tr><th>Shop Number</th><td>'.$param["formShopNumber"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Shop Phone Number</th><td>'.$param["formShopPhoneNumber"].'</td></tr><tr><th>Country</th><td>'.$param["formCountry"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Street Address</th><td>'.$param["formAddress"].'</td></tr><tr><th>Shop Website</th><td>'.$param["formShopWebsite"].'</td></tr></table><table><caption>Contact</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Name</th><td>'.$param["formFullName"].'</td></tr><tr><th>Email</th><td>'.$param["formEmail"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Mobile</th><td>'.$param["formMobile"].'</td></tr><tr><th>Best time to contact</th><td>'.$param["formBestTime"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Owner First Language</th><td>'.$ownerFirstLanguage.'</td></tr><tr><th>Note</th><td>'.$param["formNote"].'</td></tr></table><table><caption>'.$systemForShop.' System</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Login Email User</th><td>'.$loginSystem.'</td></tr><tr><th>Login Email Password</th><td>'.$passwordSystem.'</td></tr></table><table><caption>Payment Options</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Cash</th><td>'.$cash.'</td></tr><tr><th>Card at Counter</th><td>'.$cardCounter.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Call back and take card over phone</th><td>'.$callBack.'</td></tr><tr><th>Online Payments via Stripe</th><td>'.$payOnline.'</td></tr></table><table><caption>Social Networks</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Facebook</th><td>'.$param["facebook"].'</td></tr><tr><th>Instagram</th><td>'.$param["instagram"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>TikTok</th><td>'.$param["tiktok"].'</td></tr><tr><th>Yelp</th><td>'.$param["yelp"].'</td></tr></table><table><caption>Domain Name</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Original Website</th><td>'.$param["websiteDomainName"].'</td></tr><tr><th>Keep existing website</th><td>'.$keepWebsite.'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>I Own this domain name</th><td>'.$ownDomain.'</td></tr><tr><th>User Login</th><td>'.$param["loginInfoU"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Password Login</th><td>'.$param["loginInfoP"].'</td></tr><tr><th>Comment</th><td>'.$param["loginInfoComments"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Registered</th><td>'.$param["loginInfoRegistered"].'</td></tr></table><table class="mobile"><caption>Add-ons</caption><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Printed Flyers</th><td>'.$param["formFlyer"].'</td></tr><tr><th>Dine-in System</th><td>'.$param["formDineIn"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Fridge Magnet</th><td>'.$param["formMagnet"].'</td></tr><tr><th>Social Media Posts</th><td>'.$param["formSocialMedia"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Digital Menu Design</th><td>'.$param["formMenuDesign"].'</td></tr><tr><th>Website Make Over:</th><td>'.$param["formWebsiteMakeOver"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Adv Promo</th><td>'.$param["formADVPromo"].'</td></tr><tr><th>Web Hosting</th><td>'.$param["formWebHosting"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Influencer</th><td>'.$param["formInfluencer"].'</td></tr></table><hr><table class="mobile no-margin"><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Lead Source</th><td>'.$param["leadSource"].'</td></tr><tr><th>form version</th><td>'.$param["formVersion"].'</td></tr><tr style="background-color:#f2f2f2" bgcolor="#f2f2f2"><th>Timestamps</th><td>'.$timeStamps.'</td></tr></table><p style="font-size:12px;display:block;margin-left:auto;margin-right:auto;width:50%">Author: Neung - Distributed By: Local For You</p></div></body></html>';
}


//for test


if ( (str_contains($param["MainProduct"], 'Solo')) or (str_contains($param["MainProduct"], 'Yelp')) ) {
//    $system["emailAlertTo"] = "promotion@localforyou.com";
    $system["emailAlertTo"] = "bas@localforyou.com";//test
//    $result["case"] = "Send Email To AC Team";
}else {
//    $system["emailAlertTo"] = "admin@localforyou.com";
    $system["emailAlertTo"] = "bas@localforyou.com";//test
//    $result["case"] = "Send Email To CS Team";
}

if(empty($param['testMail'])) {
    $mailHeaders = [
//        'From' => 'SignUp Form <noreply@localforyou.com>',
//        'Cc' => 'sales@localforyou.com, stevew@localforyou.com',
//        'Bcc' => 'bas@localforyou.com, neung@localforyou.com, mark@localforyou.com',
//        'Reply-To' => 'neung@localforyou.com',
//        'X-Sender' => 'LocalForYou <neung@localforyou.com>',
//        'X-Mailer' => 'PHP/' . phpversion(),
//        'X-Priority' => '1',
//        'Return-Path' => 'neung@localforyou.com',
//        'MIME-Version' => '1.0',
//        'Content-Type' => 'text/html; charset=utf-8'
        'From' => 'SignUp Form <noreply@localforyou.com>',
        'Cc' => 'malimongcon@gmail.com',
        'Bcc' => 'malimongcon@gmail.com',
        'Reply-To' => 'malimongcon@gmail.com',
        'X-Sender' => 'LocalForYou <malimongcon@gmail.com>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => 'malimongcon@gmail.com',
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=utf-8'
    ];
} else {
    $system["emailAlertTo"] = "bas@localforyou.com";
//    $mailHeaders = [
//        'From' => 'Test SignUp Form <noreply@localforyou.com>',
//        'Cc' => 'bas@localforyou.com, mark@localforyou.com',
//        'Reply-To' => 'neung@localforyou.com',
//        'X-Sender' => 'LocalForYou <neung@localforyou.com>',
//        'X-Mailer' => 'PHP/' . phpversion(),
//        'X-Priority' => '1',
//        'Return-Path' => 'neung@localforyou.com',
//        'MIME-Version' => '1.0',
//        'Content-Type' => 'text/html; charset=utf-8'
//    ];
    $mailHeaders = [
        'From' => 'Test SignUp Form <noreply@localforyou.com>',
        'Cc' => 'bas@localforyou.com',
        'Reply-To' => 'bas@localforyou.com',
        'X-Sender' => 'LocalForYou <bas@localforyou.com>',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1',
        'Return-Path' => 'bas@localforyou.com',
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
echo "<br><br><br><br><br><br><br><br><br><br>";
echo $param["Cuisine"];
echo "<br><br><br><br><br><br><br><br><br><br>";
//echo "<pre>".print_r($param, true)."</pre>";

?>