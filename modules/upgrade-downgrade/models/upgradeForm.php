<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result = ['result' => '', 'msg' => ''];

$payload   = !empty($_POST['payload'])  ? $_POST['payload']  : 'no data';
$stripeID  = !empty($_POST['stripeID']) ? $_POST['stripeID'] : '';

// Parse payload to extract key fields for indexed columns
$data = json_decode($payload, true) ?: [];

$formType       = 'upgrade';
$formVersion    = $data['formVersion']      ?? null;
$formMode       = $data['formMode']         ?? 'customer';
$testMode       = ($data['testMode'] ?? 'false') === 'true' ? 1 : 0;
$mondayId       = $data['mondayProjectId']  ?? null;
$shopName       = $data['shopName']         ?? null;
$emailAddress   = $data['emailAddress']     ?? null;
$country        = $data['country']          ?? null;
$requestType    = $data['upgradeType']      ?? null;
$submittedAt    = $data['date']             ?? null;

$db->query(
    'INSERT INTO `upgrade_downgrade_logs`
        (`formType`, `formVersion`, `formMode`, `testMode`,
         `stripeId`, `mondayProjectId`, `shopName`, `emailAddress`,
         `country`, `requestType`, `payload`, `submittedAt`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    $formType, $formVersion, $formMode, $testMode,
    $stripeID, $mondayId, $shopName, $emailAddress,
    $country, $requestType, $payload, $submittedAt
);

$result['result'] = 'success';
$result['msg']    = 'Saved to DB successfully.';

echo json_encode($result);
?>
