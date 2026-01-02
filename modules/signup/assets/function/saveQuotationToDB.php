<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$dateThai = date("d/m/Y");

$result["result"] = "";
$result["msg"] = "";

$act = !empty($_POST["act"]) ? $_POST["act"] : null;
$checkBoxWantTAX = !empty($_POST["checkBoxWantTAX"]) ? $_POST["checkBoxWantTAX"] : null;
$taxType = !empty($_POST["taxType"]) ? $_POST["taxType"] : null;
$nameQuotation = !empty($_POST["nameQuotation"]) ? $_POST["nameQuotation"] : null;
$phoneQuotation = !empty($_POST["phoneQuotation"]) ? $_POST["phoneQuotation"] : null;
$emailQuotation = !empty($_POST["emailQuotation"]) ? $_POST["emailQuotation"] : null;
$addressQuotation = !empty($_POST["addressQuotation"]) ? $_POST["addressQuotation"] : null;
$taxNumberQuotation = !empty($_POST["taxNumberQuotation"]) ? $_POST["taxNumberQuotation"] : null;
$productQuotation = !empty($_POST["productQuotation"]) ? $_POST["productQuotation"] : null;
$invoiceID = !empty($_POST["invoiceID"]) ? $_POST["invoiceID"] : null;
$finalPrice = !empty($_POST["finalPrice"]) ? $_POST["finalPrice"] : null;
$shopAgent = !empty($_POST["shopAgent"]) ? $_POST["shopAgent"] : null;
$quotationID = !empty($_POST["quotationID"]) ? $_POST["quotationID"] : null;
$invoiceID = !empty($_POST["invoiceID"]) ? $_POST["invoiceID"] : null;
$grandTotal = !empty($_POST["grandTotal"]) ? $_POST["grandTotal"] : null;
$bankName = !empty($_POST["bankName"]) ? $_POST["bankName"] : null;
$bankThaiNumber = !empty($_POST["bankThaiNumber"]) ? $_POST["bankThaiNumber"] : null;
$bankThaiName = !empty($_POST["bankThaiName"]) ? $_POST["bankThaiName"] : null;
$test = !empty($_POST["test"]) ? $_POST["test"] : 0;

$data = json_decode($invoiceID, true);
$invID = $data['invoice_id'];

$productQuotation = json_encode($productQuotation);
function ThaiRead($amount_number)
{
    $amount_number = number_format($amount_number, 2, ".","");
    $pt = strpos($amount_number , ".");
    $number = $fraction = "";
    if ($pt === false)
        $number = $amount_number;
    else
    {
        $number = substr($amount_number, 0, $pt);
        $fraction = substr($amount_number, $pt + 1);
    }

    $ret = "";
    $baht = ReadNumber ($number);
    if ($baht != "")
        $ret .= $baht . "บาท";

    $satang = ReadNumber($fraction);
    if ($satang != "")
        $ret .=  $satang . "สตางค์";
    else
        $ret .= "ถ้วน";
    return $ret;
}

function ReadNumber($number)
{
    $position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
    $number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
    $number = $number + 0;
    $ret = "";
    if ($number == 0) return $ret;
    if ($number > 1000000)
    {
        $ret .= ReadNumber(intval($number / 1000000)) . "ล้าน";
        $number = intval(fmod($number, 1000000));
    }

    $divider = 100000;
    $pos = 0;
    while($number > 0)
    {
        $d = intval($number / $divider);
        $ret .= (($divider == 10) && ($d == 2)) ? "ยี่" :
            ((($divider == 10) && ($d == 1)) ? "" :
                ((($divider == 1) && ($d == 1) && ($ret != "")) ? "เอ็ด" : $number_call[$d]));
        $ret .= ($d ? $position_call[$pos] : "");
        $number = $number % $divider;
        $divider = $divider / 10;
        $pos++;
    }
    return $ret;
}

$thaiPrice = ThaiRead($grandTotal);




if ($act === "add") {
    $logsToDB =  $db->query('INSERT INTO `invoice`(`checkdata`, `type`, `name`, `address`, `sale`,`thaiPrice`, `product`, `taxNumber`, `customerEmail`,`customerPhone`,`bankName`,`bankThaiNumber`,`bankThaiName`,`test`,`dateThai`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    , $checkBoxWantTAX, $taxType, $nameQuotation, $addressQuotation,$shopAgent, $thaiPrice,$productQuotation, $taxNumberQuotation, $emailQuotation, $phoneQuotation, $bankName ,$bankThaiNumber ,$bankThaiName ,$test , $dateThai );

    $lastInsertId = $db->lastInsertId();

} elseif ($act === "update") {
    $resToDB = $db->query('UPDATE `invoice` SET `invoiceID`=? WHERE id=?', $invID, $quotationID);
} elseif ($act === "callDataBase"){
    $logsInDatabase =  $db->query('SELECT * FROM `invoice` WHERE `id` = ?', $quotationID)->fetchAll();
    $dataInvoice = [];
    foreach ($logsInDatabase as $row) {
        $idInvoice = $row['id'];

        $dataInvoice[] = [
            'id' => $row['id'],
            'checkdata' => $row['checkdata'],
            'type' => $row['type'],
            'name' => $row['name'],
            'address' => $row['address'],
            'country' => $row['country'],
            'sale' => $row['sale'],
            'thaiPrice' => $row['thaiPrice'],
            'product' => $row['product'],
            'taxNumber' => $row['taxNumber'],
            'invoiceID' => $row['invoiceID'],
            'customerEmail' => $row['customerEmail'],
            'customerPhone' => $row['customerPhone'],
            'dateThai' => $row['dateThai'],
            'bankName' => $row['bankName'],
            'bankThaiNumber' => $row['bankThaiNumber'],
            'bankThaiName' => $row['bankThaiName'],
            'test' => $row['test'],
            'createAt' => $row['createAt'],
        ];
    }
}



$result["result"] = "success";
$result["quotationID"] = $lastInsertId;
$result["idInvoice"] = $idInvoice;
$result["dataInvoice"] = $dataInvoice;
$result["msg"] = "Save to DB Quotation successfully!";

echo json_encode($result);