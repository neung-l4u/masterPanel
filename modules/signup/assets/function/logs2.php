<?php
date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$fileName = 'Local#'.$date.'.txt';
$filePath = '../../logs/'.$fileName;

$result["result"] = "";
$result["msg"] = "";



$Country = !empty($_POST["Country"])?$_POST["Country"]:"-";
$CustomerType = !empty($_POST["CustomerType"])?$_POST["CustomerType"]:"-";
$FirstName = !empty($_POST["FirstName"])?$_POST["FirstName"]:"-";
$LastName = !empty($_POST["LastName"])?$_POST["LastName"]:"-";
$Mobile = !empty($_POST["Mobile"])?$_POST["Mobile"]:"-";
$Email = !empty($_POST["Email"])?$_POST["Email"]:"-";
$BestTimeToContact = !empty($_POST["BestTimeToContact"])?$_POST["BestTimeToContact"]:"-";
$ShopName = !empty($_POST["ShopName"])?$_POST["ShopName"]:"-";
$ABN = !empty($_POST["ABN"])?$_POST["ABN"]:"-";
$TradingName = !empty($_POST["TradingName"])?$_POST["TradingName"]:"-";
$ShopNumber = !empty($_POST["ShopNumber"])?$_POST["ShopNumber"]:"-";
$Website = !empty($_POST["Website"])?$_POST["Website"]:"-";
$Language = !empty($_POST["Language"])?$_POST["Language"]:"-";
$ShopNumber2 = !empty($_POST["ShopNumber2"])?$_POST["ShopNumber2"]:"-";
$Address1 = !empty($_POST["Address1"])?$_POST["Address1"]:"-";
$Address2 = !empty($_POST["Address2"])?$_POST["Address2"]:"-";
$City = !empty($_POST["City"])?$_POST["City"]:"-";
$State = !empty($_POST["State"])?$_POST["State"]:"-";
$PostelCode = !empty($_POST["PostelCode"])?$_POST["PostelCode"]:"-";
$CountryText = !empty($_POST["CountryText"])?$_POST["CountryText"]:"-";
$ShipNumber = !empty($_POST["ShipNumber"])?$_POST["ShipNumber"]:"-";
$ShippingAddress = !empty($_POST["ShippingAddress"])?$_POST["ShippingAddress"]:"-";
$Cuisine = !empty($_POST["Cuisine"])?$_POST["Cuisine"]:"-";
$OtherCuisine = !empty($_POST["OtherCuisine"])?$_POST["OtherCuisine"]:"-";
$MainProduct = !empty($_POST["MainProduct"])?$_POST["MainProduct"]:"-";
$LoginEmail = !empty($_POST["LoginEmail"])?$_POST["LoginEmail"]:"-";
$Service = !empty($_POST["Service"])?$_POST["Service"]:"-";
$Delivery = !empty($_POST["Delivery"])?$_POST["Delivery"]:"-";
$TableNumber = !empty($_POST["TableNumber"])?$_POST["TableNumber"]:"-";
$TableSize = !empty($_POST["TableSize"])?$_POST["TableSize"]:"-";
$Payment = !empty($_POST["Payment"])?$_POST["Payment"]:"-";
$Facebook = !empty($_POST["Facebook"])?$_POST["Facebook"]:"-";
$TikTok = !empty($_POST["TikTok"])?$_POST["TikTok"]:"-";
$Instagram = !empty($_POST["Instagram"])?$_POST["Instagram"]:"-";
$Yelp = !empty($_POST["Yelp"])?$_POST["Yelp"]:"-";
$WebsiteURL = !empty($_POST["WebsiteURL"])?$_POST["WebsiteURL"]:"-";
$NewDomain = !empty($_POST["NewDomain"])?$_POST["NewDomain"]:"-";
$KeepWebsite = !empty($_POST["KeepWebsite"])?$_POST["KeepWebsite"]:"No";
$OwnDomain = !empty($_POST["OwnDomain"])?$_POST["OwnDomain"]:"No";
$Flyer = !empty($_POST["Flyer"])?$_POST["Flyer"]:"-";
$FridgeMagnet = !empty($_POST["FridgeMagnet"])?$_POST["FridgeMagnet"]:"-";
$AddOn1 = !empty($_POST["AddOn1"])?$_POST["AddOn1"]:"-";
$AddOn2 = !empty($_POST["AddOn2"])?$_POST["AddOn2"]:"-";
$AddOn3 = !empty($_POST["AddOn3"])?$_POST["AddOn3"]:"-";
$AddOn4 = !empty($_POST["AddOn4"])?$_POST["AddOn4"]:"-";
$AddOn5 = !empty($_POST["AddOn5"])?$_POST["AddOn5"]:"-";
$AddOn6 = !empty($_POST["AddOn6"])?$_POST["AddOn6"]:"-";
$AddOn7 = !empty($_POST["AddOn7"])?$_POST["AddOn7"]:"-";
$OrderDiscount = !empty($_POST["OrderDiscount"])?$_POST["OrderDiscount"]:"-";
$OtherDiscount = !empty($_POST["OtherDiscount"])?$_POST["OtherDiscount"]:"-";
$DiscountCode = !empty($_POST["DiscountCode"])?$_POST["DiscountCode"]:"-";
$usageMainDiscountCode = !empty($_POST["usageMainDiscountCode"])?$_POST["usageMainDiscountCode"]:"-";
$usageAddonDiscountCode = !empty($_POST["usageAddonDiscountCode"])?$_POST["usageAddonDiscountCode"]:"-";
$SubTotal = !empty($_POST["SubTotal"])?$_POST["SubTotal"]:"-";
$GST = !empty($_POST["GST"])?$_POST["GST"]:"-";
$Total = !empty($_POST["Total"])?$_POST["Total"]:"-";
$RealCharge = !empty($_POST["RealCharge"])?$_POST["RealCharge"]:"-";
$PaymentMethod = !empty($_POST["PaymentMethod"])?$_POST["PaymentMethod"]:"-";
$CardNumber = !empty($_POST["CardNumber"])?$_POST["CardNumber"]:"-";
$ExpDate = !empty($_POST["ExpDate"])?$_POST["ExpDate"]:"-";
$CVV = !empty($_POST["CVV"])?$_POST["CVV"]:"-";
$CardName = !empty($_POST["CardName"])?$_POST["CardName"]:"-";
$EmailDirectDebit = !empty($_POST["EmailDirectDebit"])?$_POST["EmailDirectDebit"]:"-";
$BSB = !empty($_POST["BSB"])?$_POST["BSB"]:"-";
$EmailInvoice = !empty($_POST["EmailInvoice"])?$_POST["EmailInvoice"]:"-";
$AccountNumber = !empty($_POST["AccountNumber"])?$_POST["AccountNumber"]:"-";
$AdditionNote = !empty($_POST["AdditionNote"])?$_POST["AdditionNote"]:"-";
$ShopAgent = !empty($_POST["ShopAgent"])?$_POST["ShopAgent"]:"-";
$ReferredByPerson = !empty($_POST["ReferredByPerson"])?$_POST["ReferredByPerson"]:"-";
$ReferredByShop = !empty($_POST["ReferredByShop"])?$_POST["ReferredByShop"]:"-";
$CustomerStripeID = !empty($_POST["CustomerStripeID"])?$_POST["CustomerStripeID"]:"-";
$formstartProjectAs = !empty($_POST["formstartProjectAs"]) ? $_POST["formstartProjectAs"] : false;
$formstartProjectOther = !empty($_POST["formstartProjectOther"]) ? $_POST["formstartProjectOther"] : "-";
$formstartprojectNote = !empty($_POST["formstartprojectNote"]) ? $_POST["formstartprojectNote"] : "-";
$formPOSUsing = !empty($_POST["formPOSUsing"]) ? $_POST["formPOSUsing"] : "-";
$formPOSUsingOther = !empty($_POST["formPOSUsingOther"]) ? $_POST["formPOSUsingOther"] : "-";
$formNoPOSProvider = !empty($_POST["formNoPOSProvider"]) ? $_POST["formNoPOSProvider"] : false;
$formYesPOSProvider = !empty($_POST["formYesPOSProvider"]) ? $_POST["formYesPOSProvider"] : "-";

$noPOS = $formNoPOSProvider;
$yesPOS = $formYesPOSProvider;
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

$startDate = $formstartProjectAs;
$otherDate = $formstartProjectOther;
$dateStart = "";

if (!empty($startDate)) {
    $dateStart = $startDate;
}else if (!empty($otherDate)) {
    $dateStart = $otherDate;
}else{
    $dateStart = "-";
}



$message = "----- $fileName -> $timestamp -----
Country: $Country|
CustomerType: $CustomerType|

FirstName: $FirstName|
LastName: $LastName|
Mobile: $Mobile|
Email: $Email|
BestTimeToContact: $BestTimeToContact|

ShopName: $ShopName|
ABN: $ABN|
TradingName: $TradingName|
ShopNumber: $ShopNumber|
Website: $Website|
Language: $Language|

ShopNumber2: $ShopNumber2|
Address1: $Address1|
Address2: $Address2|
City: $City|
State: $State|
PostelCode: $PostelCode|
ShippingAddress: $ShippingAddress|

Cuisine: $Cuisine|
OtherCuisine: $OtherCuisine|

LoginEmail: $LoginEmail|
Service: $Service|
Table: $TableNumber|
TableSize: $TableSize|
Payment: $Payment|

Facebook: $Facebook|
TikTok: $TikTok|
Instagram: $Instagram|
Yelp: $Yelp|

WebsiteURL: $WebsiteURL|
NewDomain: $NewDomain|
KeepWebsite: $KeepWebsite|
OwnDomain: $OwnDomain|

MainProduct: $MainProduct|

Flyer: $Flyer|
FridgeMagnet: $FridgeMagnet|

AddOn1: $AddOn1|
AddOn2: $AddOn2|
AddOn3: $AddOn3|
AddOn4: $AddOn4|
AddOn5: $AddOn5|
AddOn6: $AddOn6|
AddOn7: $AddOn7|

OrderDiscount: $OrderDiscount|
OtherDiscount: $OtherDiscount|
DiscountCode: $DiscountCode|

PaymentMethod: $PaymentMethod|

CardNumber: $CardNumber|
ExpDate: $ExpDate|
CVV: $CVV|
CardName: $CardName|

EmailDirectDebit: $EmailDirectDebit|
BSB: $BSB|

EmailInvoice: $EmailInvoice|
AccountNumber: $AccountNumber|

SubTotal: $SubTotal|
GST: $GST|
Total: $Total|
RealCharge: $RealCharge|

AdditionNote: $AdditionNote|

ShopAgent: $ShopAgent|
ReferredByPerson: $ReferredByPerson|
ReferredByShop: $ReferredByShop|

CustomerStripeID: $CustomerStripeID|
Use Main Discount Code: $usageMainDiscountCode|
Use Addon Discount Code: $usageAddonDiscountCode|

POS Brand : $formPOSUsing|
End contract : $posProvider|

Start Project Date: $dateStart|
----- END -----



";

if (file_put_contents($filePath,  PHP_EOL . $message,FILE_APPEND) !== false) {
    $result["result"] = "success";
    $result["msg"] = "File created successfully!";
} else {
    $result["result"] = "fail";
    $result["msg"] = "Error creating fail!";
}
echo json_encode($result);