<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";
require_once dirname(__DIR__, 4) . '/api/invoice/convertToBahtText.php';
require_once dirname(__DIR__, 4) . '/api/invoice/thApoMondayHelper.php';

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
$signupPayload = json_decode($_POST['signupPayload'] ?? '{}', true);
if (!is_array($signupPayload)) {
    $signupPayload = [];
}
foreach (['creditCardNumber', 'creditExpireDate', 'creditCCV', 'stripePassword', 'ref_Domain_P', 'ref_IHD_Password', 'passwordBooking', 'bsbDirectDebit', 'acnDirectDebit', 'routingDirectDebit'] as $sensitiveKey) {
    unset($signupPayload[$sensitiveKey]);
}

$data = !empty($invoiceID) ? json_decode($invoiceID, true) : null;
$invID = isset($data['invoice_id']) ? $data['invoice_id'] : null;

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




$lastInsertId = null;
$idInvoice    = null;
$dataInvoice  = [];

if ($act === "add") {
    // --- Always create new thCustomer per submit ---
    $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $customerCode = '';
    for ($i = 0; $i < 8; $i++) {
        $customerCode .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $db->query(
        'INSERT INTO `thCustomer`(`customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type`, `sale`, `bankName`, `bankNumber`, `bankAccName`) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
        $customerCode, $nameQuotation, $addressQuotation, $taxNumberQuotation, $emailQuotation, $phoneQuotation, $taxType, $shopAgent, $bankName, $bankThaiNumber, $bankThaiName
    );
    $customerId = $db->lastInsertID();

    // --- Generate invoiceID (prefix-XXXX) ---
    $lastInvoiceRows = $db->query(
        'SELECT `invoiceID` FROM `thInvoice` WHERE `customer_id` = ? ORDER BY `id` DESC LIMIT 1',
        $customerId
    )->fetchAll();
    $lastInvoiceRow = $lastInvoiceRows[0] ?? [];

    if (!empty($lastInvoiceRow['invoiceID'])) {
        $parts  = explode('-', $lastInvoiceRow['invoiceID']);
        $seq    = isset($parts[1]) ? (int)$parts[1] : 0;
        $newSeq = str_pad($seq + 1, 4, '0', STR_PAD_LEFT);
        $generatedInvoiceID = $customerCode . '-' . $newSeq;
    } else {
        $generatedInvoiceID = $customerCode . '-0001';
    }
    // --- End invoiceID generation ---

    // Extract net_payment from productQuotation for amount
    $productArr = json_decode($productQuotation, true);
    $netPayment = isset($productArr['summary']['net_payment']) ? (float)str_replace(',', '', $productArr['summary']['net_payment']) : (float)$grandTotal;

    $thBathIn = convertToBahtText($netPayment);
    $db->query(
        'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`) VALUES (?,?,?,?,?,?,?,?)',
        $customerId, $generatedInvoiceID, 'one_time', $productQuotation, $netPayment, $thBathIn, 'pending', 'signup'
    );

    $lastInsertId = $db->lastInsertID();
    queueThApoMondayPayload($db, (int)$lastInsertId, $signupPayload);
    $result["_debug_customerId"] = $customerId ?? null;
    $result["_debug_invoiceID"]  = $generatedInvoiceID ?? null;
    $result["_debug_netPayment"] = $netPayment ?? null;

} elseif ($act === "update") {
    $resToDB = $db->query('UPDATE `thInvoice` SET `invoiceID`=? WHERE id=?', $invID, $quotationID);
} elseif ($act === "callDataBase"){
    $logsInDatabase = $db->query(
        'SELECT i.*, c.`email` AS customerEmail, c.`phone` AS customerPhone,
                c.`taxNumber`, c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName,
                c.`type`, c.`sale`, c.`name`, c.`address`
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE i.`id` = ?',
        $quotationID
    )->fetchAll();
    $dataInvoice = [];
    foreach ($logsInDatabase as $row) {
        $idInvoice = $row['id'];
        $dataInvoice[] = [
            'id'            => $row['id'],
            'type'          => $row['type'],
            'name'          => $row['name'],
            'address'       => $row['address'],
            'sale'          => $row['sale'],
            'product'       => $row['product'],
            'taxNumber'     => $row['taxNumber'],
            'invoiceID'     => $row['invoiceID'],
            'customerEmail' => $row['customerEmail'],
            'customerPhone' => $row['customerPhone'],
            'bankName'      => $row['bankName'],
            'bankThaiNumber'=> $row['bankThaiNumber'],
            'bankThaiName'  => $row['bankThaiName'],
            'amount'        => $row['amount'],
            'status'        => $row['status'],
            'createdAt'     => $row['createdAt'],
        ];
    }
}



$result["result"] = "success";
$result["quotationID"] = $lastInsertId;
$result["idInvoice"] = $idInvoice;
$result["dataInvoice"] = $dataInvoice;
$result["msg"] = "Save to DB Quotation successfully!";

echo json_encode($result);