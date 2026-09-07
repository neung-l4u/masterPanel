<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";
include_once __DIR__ . '/../../../../assets/php/notify.php';

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");

$result["result"] = "";
$result["msg"] = "";

$act = !empty($_POST["act"]) ? $_POST["act"] : null;
$dataLogs = !empty($_POST["payload"]) ? $_POST["payload"] : null;
$dataStripe = !empty($_POST["stripePayload"]) ? $_POST["stripePayload"] : null;
$country = !empty($_POST["country"]) ? $_POST["country"] : null;
$contractURL = !empty($_POST["contractURL"]) ? $_POST["contractURL"] : null;
$stripeResult = !empty($_POST["stripeRes"]) ? $_POST["stripeRes"] : null;
$testMode = !empty($_POST["testMail"]) ? $_POST["testMail"] : 0;
$logID = !empty($_POST["logID"]) ? $_POST["logID"] : null;

$dataLogs = json_encode($dataLogs);
$dataStripe = json_encode($dataStripe);
$status = 1;
$signupBy = !empty($_SESSION['id']) ? $_SESSION['id'] : 0;

if (!is_null($stripeResult)) {
    $trimmed = trim($stripeResult);
    json_decode($trimmed);

    if (json_last_error() !== JSON_ERROR_NONE || $trimmed === "null") {
        $stripeResult = json_encode($stripeResult);
    }
}

if ($act === "add") {
    $logsToDB =  $db->query('INSERT INTO `logssignup`(`dataLogs`, `dataStripe`, `dataContract`, `countryCode`, `status`, `test`, `createAt`, `createBy`) VALUES (?,?,?,?,?,?,?,?)'
    , $dataLogs, $dataStripe, $contractURL, $country, $status, $testMode, $timestamp, $signupBy );
} elseif ($act === "update") {
    $resToDB = $db->query('UPDATE `logssignup` SET `stripeResult`=? WHERE id=?', $stripeResult, $logID);

    // Alert IT when the signup finished without a usable Stripe result, so the
    // billing gap is caught the same day instead of surfacing in a later audit.
    // Test Mode is a deliberate no-charge path and is not a problem.
    $decoded = json_decode((string)$stripeResult, true);
    $isTestMode = is_string($decoded) && stripos($decoded, 'test mode') !== false;
    $hasCustomer = is_array($decoded) && !empty($decoded['customer_id']);

    if (!$isTestMode && !$hasCustomer) {
        $shop = '';
        $logRow = $db->query('SELECT dataLogs FROM logssignup WHERE id = ?', $logID)->fetchAll();
        if (!empty($logRow)) {
            $logJson = json_decode($logRow[0]['dataLogs'], true);
            $shop = $logJson['ShopName'] ?? '';
        }

        $reason = 'No Stripe result recorded';
        if (is_array($decoded) && !empty($decoded['error'])) {
            $reason = 'Stripe error: ' . $decoded['error'];
        } elseif (is_string($decoded) && $decoded !== '') {
            $reason = 'Stripe returned: ' . $decoded;
        }

        notifyTeam(
            5,                                  // IT
            'signup_no_stripe',
            'Signup without Stripe result',
            trim(($shop !== '' ? $shop . ' - ' : '') . $reason),
            'main.php?p=viewLogs',
            (int)$logID
        );
    }
}

$lastInsertId = $db->lastInsertId();

$result["result"] = "success";
$result["logID"] = $lastInsertId;
$result["msg"] = "Save to DB successfully!";

echo json_encode($result);