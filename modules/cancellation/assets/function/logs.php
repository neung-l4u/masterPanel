<?php
date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$fileName = 'Logs#'.$date.'.txt';
$filePath = '../../logs/'.$fileName;

$result["result"] = "";
$result["msg"] = "";

$json = $_REQUEST["allData"];

/*$Country = !empty($_REQUEST["Country"])?$_REQUEST["Country"]:"-";
$CustomerType = !empty($_REQUEST["CustomerType"])?$_REQUEST["CustomerType"]:"-";
$FirstName = !empty($_REQUEST["FirstName"])?$_REQUEST["FirstName"]:"-";
$LastName = !empty($_REQUEST["LastName"])?$_REQUEST["LastName"]:"-";
$Mobile = !empty($_REQUEST["Mobile"])?$_REQUEST["Mobile"]:"-";
$Email = !empty($_REQUEST["Email"])?$_REQUEST["Email"]:"-";
$BestTimeToContact = !empty($_REQUEST["BestTimeToContact"])?$_REQUEST["BestTimeToContact"]:"-";
$ShopName = !empty($_REQUEST["ShopName"])?$_REQUEST["ShopName"]:"-";
$ABN = !empty($_REQUEST["ABN"])?$_REQUEST["ABN"]:"-";
$TradingName = !empty($_REQUEST["TradingName"])?$_REQUEST["TradingName"]:"-";
$ShopNumber = !empty($_REQUEST["ShopNumber"])?$_REQUEST["ShopNumber"]:"-";
$Website = !empty($_REQUEST["Website"])?$_REQUEST["Website"]:"-";
$Language = !empty($_REQUEST["Language"])?$_REQUEST["Language"]:"-";
$ShopNumber2 = !empty($_REQUEST["ShopNumber2"])?$_REQUEST["ShopNumber2"]:"-";
$Address1 = !empty($_REQUEST["Address1"])?$_REQUEST["Address1"]:"-";
$Address2 = !empty($_REQUEST["Address2"])?$_REQUEST["Address2"]:"-";
$City = !empty($_REQUEST["City"])?$_REQUEST["City"]:"-";
$State = !empty($_REQUEST["State"])?$_REQUEST["State"]:"-";
$PostelCode = !empty($_REQUEST["PostelCode"])?$_REQUEST["PostelCode"]:"-";
$CountryText = !empty($_REQUEST["CountryText"])?$_REQUEST["CountryText"]:"-";
$ShipNumber = !empty($_REQUEST["ShipNumber"])?$_REQUEST["ShipNumber"]:"-";
$ShippingAddress = !empty($_REQUEST["ShippingAddress"])?$_REQUEST["ShippingAddress"]:"-";
$Cuisine = !empty($_REQUEST["Cuisine"])?$_REQUEST["Cuisine"]:"-";
$OtherCuisine = !empty($_REQUEST["OtherCuisine"])?$_REQUEST["OtherCuisine"]:"-";
$MainProduct = !empty($_REQUEST["MainProduct"])?$_REQUEST["MainProduct"]:"-";
$LoginEmail = !empty($_REQUEST["LoginEmail"])?$_REQUEST["LoginEmail"]:"-";
$Service = !empty($_REQUEST["Service"])?$_REQUEST["Service"]:"-";
$Delivery = !empty($_REQUEST["Delivery"])?$_REQUEST["Delivery"]:"-";
$TableNumber = !empty($_REQUEST["TableNumber"])?$_REQUEST["TableNumber"]:"-";
$TableSize = !empty($_REQUEST["TableSize"])?$_REQUEST["TableSize"]:"-";
$Payment = !empty($_REQUEST["Payment"])?$_REQUEST["Payment"]:"-";
$Facebook = !empty($_REQUEST["Facebook"])?$_REQUEST["Facebook"]:"-";
$TikTok = !empty($_REQUEST["TikTok"])?$_REQUEST["TikTok"]:"-";
$Instagram = !empty($_REQUEST["Instagram"])?$_REQUEST["Instagram"]:"-";
$Yelp = !empty($_REQUEST["Yelp"])?$_REQUEST["Yelp"]:"-";
$WebsiteURL = !empty($_REQUEST["WebsiteURL"])?$_REQUEST["WebsiteURL"]:"-";
$NewDomain = !empty($_REQUEST["NewDomain"])?$_REQUEST["NewDomain"]:"-";
$KeepWebsite = !empty($_REQUEST["KeepWebsite"])?$_REQUEST["KeepWebsite"]:"No";
$OwnDomain = !empty($_REQUEST["OwnDomain"])?$_REQUEST["OwnDomain"]:"No";
$Flyer = !empty($_REQUEST["Flyer"])?$_REQUEST["Flyer"]:"-";
$FridgeMagnet = !empty($_REQUEST["FridgeMagnet"])?$_REQUEST["FridgeMagnet"]:"-";
$AddOn1 = !empty($_REQUEST["AddOn1"])?$_REQUEST["AddOn1"]:"-";
$AddOn2 = !empty($_REQUEST["AddOn2"])?$_REQUEST["AddOn2"]:"-";
$AddOn3 = !empty($_REQUEST["AddOn3"])?$_REQUEST["AddOn3"]:"-";
$AddOn4 = !empty($_REQUEST["AddOn4"])?$_REQUEST["AddOn4"]:"-";
$AddOn5 = !empty($_REQUEST["AddOn5"])?$_REQUEST["AddOn5"]:"-";
$AddOn6 = !empty($_REQUEST["AddOn6"])?$_REQUEST["AddOn6"]:"-";
$AddOn7 = !empty($_REQUEST["AddOn7"])?$_REQUEST["AddOn7"]:"-";
$OrderDiscount = !empty($_REQUEST["OrderDiscount"])?$_REQUEST["OrderDiscount"]:"-";
$OtherDiscount = !empty($_REQUEST["OtherDiscount"])?$_REQUEST["OtherDiscount"]:"-";
$DiscountCode = !empty($_REQUEST["DiscountCode"])?$_REQUEST["DiscountCode"]:"-";
$usageMainDiscountCode = !empty($_REQUEST["usageMainDiscountCode"])?$_REQUEST["usageMainDiscountCode"]:"-";
$usageAddonDiscountCode = !empty($_REQUEST["usageAddonDiscountCode"])?$_REQUEST["usageAddonDiscountCode"]:"-";
$SubTotal = !empty($_REQUEST["SubTotal"])?$_REQUEST["SubTotal"]:"-";
$GST = !empty($_REQUEST["GST"])?$_REQUEST["GST"]:"-";
$Total = !empty($_REQUEST["Total"])?$_REQUEST["Total"]:"-";
$RealCharge = !empty($_REQUEST["RealCharge"])?$_REQUEST["RealCharge"]:"-";
$PaymentMethod = !empty($_REQUEST["PaymentMethod"])?$_REQUEST["PaymentMethod"]:"-";
$CardNumber = !empty($_REQUEST["CardNumber"])?$_REQUEST["CardNumber"]:"-";
$ExpDate = !empty($_REQUEST["ExpDate"])?$_REQUEST["ExpDate"]:"-";
$CVV = !empty($_REQUEST["CVV"])?$_REQUEST["CVV"]:"-";
$CardName = !empty($_REQUEST["CardName"])?$_REQUEST["CardName"]:"-";
$EmailDirectDebit = !empty($_REQUEST["EmailDirectDebit"])?$_REQUEST["EmailDirectDebit"]:"-";
$BSB = !empty($_REQUEST["BSB"])?$_REQUEST["BSB"]:"-";
$EmailInvoice = !empty($_REQUEST["EmailInvoice"])?$_REQUEST["EmailInvoice"]:"-";
$AccountNumber = !empty($_REQUEST["AccountNumber"])?$_REQUEST["AccountNumber"]:"-";
$AdditionNote = !empty($_REQUEST["AdditionNote"])?$_REQUEST["AdditionNote"]:"-";
$ShopAgent = !empty($_REQUEST["ShopAgent"])?$_REQUEST["ShopAgent"]:"-";
$ReferredByPerson = !empty($_REQUEST["ReferredByPerson"])?$_REQUEST["ReferredByPerson"]:"-";
$ReferredByShop = !empty($_REQUEST["ReferredByShop"])?$_REQUEST["ReferredByShop"]:"-";
$CustomerStripeID = !empty($_REQUEST["CustomerStripeID"])?$_REQUEST["CustomerStripeID"]:"-";*/


/*$message = "----- $fileName -> $timestamp -----
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
----- END -----



";*//*$message = "----- $fileName -> $timestamp -----
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
----- END -----



";*/


$message = "----- $fileName -> $timestamp -----

";
$message .= json_encode($json, JSON_PRETTY_PRINT);
$message .= "

----- END -----";

if (file_put_contents($filePath,  PHP_EOL . $message,FILE_APPEND) !== false) {
    $result["result"] = "success";
    $result["msg"] = "File created successfully!";
} else {
    $result["result"] = "fail";
    $result["msg"] = "Error creating fail!";
}
echo json_encode($result);