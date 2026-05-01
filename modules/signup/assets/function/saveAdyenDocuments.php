<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$timestamp = date("Y-m-d H:i:s");

$result["result"] = "";
$result["msg"] = "";

// Get POST data
$country = !empty($_POST["country"]) ? trim($_POST["country"]) : null;
$shopName = !empty($_POST["shopName"]) ? trim($_POST["shopName"]) : null;
$email = !empty($_POST["email"]) ? trim($_POST["email"]) : null;
$adyenAgreement = !empty($_POST["adyenAgreement"]) ? trim($_POST["adyenAgreement"]) : null;
$businessRegDoc = !empty($_POST["businessRegistrationDoc"]) ? trim($_POST["businessRegistrationDoc"]) : null;
$bankStatementDoc = !empty($_POST["bankStatementDoc"]) ? trim($_POST["bankStatementDoc"]) : null;
$directorIdDoc = !empty($_POST["directorIdDoc"]) ? trim($_POST["directorIdDoc"]) : null;
$uploadFolder = !empty($_POST["uploadFolder"]) ? trim($_POST["uploadFolder"]) : null;

// Validate required fields
if (empty($country) || empty($shopName) || empty($email) || empty($uploadFolder)) {
    $result["result"] = "error";
    $result["msg"] = "Missing required fields: country, shopName, email, or uploadFolder";
    echo json_encode($result);
    exit;
}

// Only store 'agreed' if checkbox was checked, otherwise NULL
$adyenAgreementValue = ($adyenAgreement === "agreed") ? "agreed" : null;

try {
    // Insert into adyen_documents table
    $insertResult = $db->query(
        'INSERT INTO `adyen_documents` 
        (`country`, `shop_name`, `email`, `adyen_agreement`, 
         `business_registration_doc`, `bank_statement_doc`, `director_id_doc`, 
         `upload_folder`, `created_at`, `updated_at`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        $country,
        $shopName,
        $email,
        $adyenAgreementValue,
        $businessRegDoc,
        $bankStatementDoc,
        $directorIdDoc,
        $uploadFolder,
        $timestamp,
        $timestamp
    );

    $lastInsertId = $db->lastInsertId();

    $result["result"] = "success";
    $result["id"] = $lastInsertId;
    $result["msg"] = "Adyen documents saved successfully!";

} catch (Exception $e) {
    $result["result"] = "error";
    $result["msg"] = "Database error: " . $e->getMessage();
}

echo json_encode($result);
