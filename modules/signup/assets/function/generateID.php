<?php
header('Content-Type: application/json');

$result = ["result" => "", "msg" => "", "storeID" => "", "customerID" => ""];

$country = !empty($_POST["country"]) ? trim($_POST["country"]) : null;
$formType = !empty($_POST["formType"]) ? trim($_POST["formType"]) : null;

if (is_null($country) || is_null($formType)) {
    $result["result"] = "fail";
    $result["msg"] = "Missing country or formType parameter.";
    echo json_encode($result);
    exit;
}

// Map formType to project type code
$projectTypeMap = [
    "Thai Restaurants & Takeaways" => "01",
    "Restaurants & Takeaways"      => "02",
    "Thai Massage"                 => "03"
];

// Map form country code to ID country code
$countryCodeMap = [
    "AU" => "AU",
    "NZ" => "NZ",
    "UK" => "UK",
    "CA" => "CA",
    "US" => "USA",
    "TH" => "TH"
];

$projectTypeCode = isset($projectTypeMap[$formType]) ? $projectTypeMap[$formType] : "99";
$countryCode = isset($countryCodeMap[$country]) ? $countryCodeMap[$country] : $country;

$counterFile = __DIR__ . '/id_counters.json';

// Open file with exclusive lock for atomic read-increment-write
$fp = fopen($counterFile, 'c+');
if (!$fp) {
    $result["result"] = "fail";
    $result["msg"] = "Cannot open counter file.";
    echo json_encode($result);
    exit;
}

if (flock($fp, LOCK_EX)) {
    $fileSize = filesize($counterFile);
    $content = $fileSize > 0 ? fread($fp, $fileSize) : '{}';
    $counters = json_decode($content, true);

    if (!$counters) {
        $counters = ["project" => [], "customer" => []];
    }

    // Initialize country keys if not exist
    if (!isset($counters["project"][$country])) {
        $counters["project"][$country] = [];
    }
    if (!isset($counters["customer"][$country])) {
        $counters["customer"][$country] = 0;
    }

    // Initialize project type counter if not exist
    if (!isset($counters["project"][$country][$projectTypeCode])) {
        $counters["project"][$country][$projectTypeCode] = 0;
    }

    // Increment counters
    $counters["project"][$country][$projectTypeCode]++;
    $counters["customer"][$country]++;

    $newProjectNo = $counters["project"][$country][$projectTypeCode];
    $newCustomerNo = $counters["customer"][$country];

    // Generate IDs
    // Project ID format: {CountryCode}{ProjectType}{RunningNo 6-digits}
    // Example: AU01000987
    $storeID = $countryCode . $projectTypeCode . str_pad($newProjectNo, 6, '0', STR_PAD_LEFT);

    // Customer ID format: L4U{CountryCode}{RunningNo 6-digits}
    // Example: L4UAU000993
    $customerID = "L4U" . $countryCode . str_pad($newCustomerNo, 6, '0', STR_PAD_LEFT);

    // Write updated counters back to file
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($counters, JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);

    $result["result"] = "success";
    $result["msg"] = "ID generated successfully.";
    $result["storeID"] = $storeID;
    $result["customerID"] = $customerID;
    $result["projectNo"] = $newProjectNo;
    $result["customerNo"] = $newCustomerNo;
} else {
    $result["result"] = "fail";
    $result["msg"] = "Cannot lock counter file.";
}

fclose($fp);
echo json_encode($result);
