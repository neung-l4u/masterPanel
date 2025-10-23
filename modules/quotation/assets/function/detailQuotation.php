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
$shopAgent = !empty($_POST["shopAgent"]) ? $_POST["shopAgent"] : null;
$price = !empty($_POST["price"]) ? $_POST["price"] : null;
$finalPrice = !empty($_POST["finalPrice"]) ? $_POST["finalPrice"] : null;
$quotationID = !empty($_POST["quotationID"]) ? $_POST["quotationID"] : null;

$price = json_encode($price);

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

$thaiPrice = ThaiRead($finalPrice);

$codeId = "";

if ($shopAgent == "ปิยะกร จ้อยเอม"){
    $codeId = "QTLCETH-A";
}else if($shopAgent == "อนิรุตมิ์ จิราสิรินันทชัย"){
    $codeId = "QTLCETH-B";
}else if($shopAgent == "วิมล ปลื้มกมล"){
    $codeId = "QTLCETH-C";
}else if($shopAgent == "นิธิพันธ์ ธรรมพุฒ"){
    $codeId = "QTLCETH-D";
}else if($shopAgent == "ชมภูนุช จุลไกรอานิสงส์"){
    $codeId = "QTLCETH-E";
}else if($shopAgent == "พฤกษ์ ปฏิพัทธศิลปกิจ"){
    $codeId = "QTLCETH-F";
}else if($shopAgent == "พรนภา กันทาทำ"){
    $codeId = "QTLCETH-G";
}else if($shopAgent == "สุชานันท์ ราชเจริญ"){
    $codeId = "QTLCETH-H";
}else if($shopAgent == "วรรษชล ธรรมจะดี"){
    $codeId = "QTLCETH-I";
}else{
    $codeId = "QTLCETH-Z";
}


if ($act == "add") {
    $insert = $db->query(
        'INSERT INTO quotation (sale, data, thaiprice, quotationNumber) VALUES (?, ?, ?, ?)',
        $shopAgent, $price, $thaiPrice, $codeId
    );

    if ($insert) {
        $lastInsertId = $db->lastInsertId();
        $result["result"] = "success";
        $result["msg"] = "Quotation inserted successfully";
        $result["quotationID"] = $lastInsertId;
    } else {
        $result["result"] = "fail";
        $result["msg"] = "Insert failed";
    }
}

// === อัปเดตเลขใบเสนอราคา ===
else if ($act == "update") {
    $row = $db->query('SELECT COUNT(*) AS total_rows FROM quotation WHERE sale = ?', $shopAgent)->fetchArray();
    $countSale = $row["total_rows"];

    $update = $db->query(
        'UPDATE quotation SET quotationNumber = CONCAT(quotationNumber, LPAD(?, 4, "0")) WHERE id = ?',
        $countSale, $quotationID
    );

    if ($update) {
        $result["result"] = "success";
        $result["msg"] = "Quotation number updated successfully";
        $result["quotationID"] = $quotationID;
    } else {
        $result["result"] = "fail";
        $result["msg"] = "Update failed";
    }
}

// === ไม่พบ act ===
else {
    $result["result"] = "fail";
    $result["msg"] = "Invalid action";
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);