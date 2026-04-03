<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

// Get filter parameters
$dateStart = $_POST['dateStart'] ?? '';
$dateEnd = $_POST['dateEnd'] ?? '';
$countryFilter = $_POST['country'] ?? '';
$shopTypeFilter = $_POST['shopType'] ?? '';
$saleFilter = $_POST['sale'] ?? '';

// Build query with filters
$query = 'SELECT l.id, l.dataLogs, l.dataStripe, l.stripeResult, l.dataContract, l.countryCode, l.createAt, s.status FROM logssignup l, logsstatus s WHERE l.status = s.id';

// Add date range filter (using simple validation for date format YYYY-MM-DD)
if (!empty($dateStart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart)) {
    $query .= ' AND DATE(l.createAt) >= "' . $dateStart . '"';
}
if (!empty($dateEnd) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
    $query .= ' AND DATE(l.createAt) <= "' . $dateEnd . '"';
}

// Add country filter (2-letter country codes only)
if (!empty($countryFilter) && preg_match('/^[A-Z]{2}$/', $countryFilter)) {
    $query .= ' AND l.countryCode = "' . $countryFilter . '"';
}

$query .= ' ORDER BY l.createAt DESC';

$result = $db->query($query)->fetchAll();

// Pre-load staff data for sales agent lookup
$staffRows = $db->query('SELECT sName, sNickName, sPic FROM staffs WHERE sDeleteAt IS NULL')->fetchAll();
$staffMap = [];
$staffByNick = []; // Map by nickname for quick lookup
foreach ($staffRows as $s) {
    // Map by full name
    $staffMap[strtolower($s['sName'])] = ['nick' => $s['sNickName'], 'pic' => $s['sPic']];
    
    // Also map by "Nickname Lastname" format (e.g., "Honey Tummaput")
    $nameParts = explode(' ', $s['sName']);
    if (count($nameParts) >= 2) {
        $lastName = end($nameParts);
        $nickLastFormat = strtolower($s['sNickName'] . ' ' . $lastName);
        $staffMap[$nickLastFormat] = ['nick' => $s['sNickName'], 'pic' => $s['sPic']];
    }
    
    // Map by nickname only (e.g., "Pluem" -> staff data)
    $staffByNick[strtolower($s['sNickName'])] = ['nick' => $s['sNickName'], 'pic' => $s['sPic']];
}

$data = array("data"=> array());

foreach ($result as $row) {
    $date = $row["createAt"];

    $country = match ($row["countryCode"]) {
        "AU" => "Australia",
        "NZ" => "New Zealand",
        "UK" => "United Kingdom",
        "CA" => "Canada",
        "US" => "United States",
        "TH" => "Thailand",
        default => $row["countryCode"],
    };

    $json = json_decode($row["dataLogs"], true);
    
    // Remove sensitive fields for staff view
    if (isset($json["usageMainDiscountCode"])) unset($json["usageMainDiscountCode"]);
    if (isset($json["usageAddonDiscountCode"])) unset($json["usageAddonDiscountCode"]);
    if (isset($json["EmailDirectDebit"])) unset($json["EmailDirectDebit"]);
    if (isset($json["BSB"])) unset($json["BSB"]);
    if (isset($json["EmailInvoice"])) unset($json["EmailInvoice"]);
    
    $shopType = showType($json["CustomerType"]);
    
    // Apply shop type filter
    if (!empty($shopTypeFilter) && $shopType !== $shopTypeFilter) {
        continue;
    }
    
    // Apply sale agent filter (filter by nickname)
    $salesAgent = $json["formSalesAgent"] ?? $json["ShopAgent"] ?? '';
    if (!empty($saleFilter)) {
        // Extract nickname from salesAgent (first word)
        $agentParts = explode(' ', $salesAgent);
        $agentNickname = strtolower($agentParts[0]); // e.g., "pluem" from "Pluem Pluemkamol"
        
        // Compare with filter (which is now just the nickname)
        if ($agentNickname !== strtolower($saleFilter)) {
            continue;
        }
    }

    $jsonText = json_encode($json, JSON_PRETTY_PRINT);

    $shopName = $json["ShopName"];
    $contractURL = $row["dataContract"];

    if ($row["stripeResult"] !== null) {
        $stripeResult = htmlspecialchars($row["stripeResult"]);
    }
    else {
        $stripeResult = "";
    }

    $signupLogsBtn = '<svg class="clickable" onclick="viewJson('.htmlspecialchars($row["dataLogs"]).', '.$stripeResult.')" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>';
    $stripeLogsBtn = (empty($row["dataStripe"])) ? "-" : '<svg class="clickable" height="1.5em" onclick="viewJson('.htmlspecialchars($row["dataStripe"]).', '.$stripeResult.')" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M253.3 35.1c6.1-11.8 1.5-26.3-10.2-32.4s-26.3-1.5-32.4 10.2L117.6 192 32 192c-17.7 0-32 14.3-32 32s14.3 32 32 32L83.9 463.5C91 492 116.6 512 146 512L430 512c29.4 0 55-20 62.1-48.5L544 256c17.7 0 32-14.3 32-32s-14.3-32-32-32l-85.6 0L365.3 12.9C359.2 1.2 344.7-3.4 332.9 2.7s-16.3 20.6-10.2 32.4L404.3 192l-232.6 0L253.3 35.1zM192 304l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16s16 7.2 16 16zm96-16c8.8 0 16 7.2 16 16l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16zm128 16l0 96c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-96c0-8.8 7.2-16 16-16s16 7.2 16 16z"/></svg>';
    $contractLogsBtn = (empty($contractURL)) ? "-" : '<a href="'.$contractURL.'" target="_blank"><svg height="1.5em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-19.3c-2.7 1.1-5.4 2-8.2 2.7l-60.1 15c-3 .7-6 1.2-9 1.4c-.9 .1-1.8 .2-2.7 .2l-64 0c-6.1 0-11.6-3.4-14.3-8.8l-8.8-17.7c-1.7-3.4-5.1-5.5-8.8-5.5s-7.2 2.1-8.8 5.5l-8.8 17.7c-2.9 5.9-9.2 9.4-15.7 8.8s-12.1-5.1-13.9-11.3L144 381l-9.8 32.8c-6.1 20.3-24.8 34.2-46 34.2L80 448c-8.8 0-16-7.2-16-16s7.2-16 16-16l8.2 0c7.1 0 13.3-4.6 15.3-11.4l14.9-49.5c3.4-11.3 13.8-19.1 25.6-19.1s22.2 7.8 25.6 19.1l11.6 38.6c7.4-6.2 16.8-9.7 26.8-9.7c15.9 0 30.4 9 37.5 23.2l4.4 8.8 8.9 0c-3.1-8.8-3.7-18.4-1.4-27.8l15-60.1c2.8-11.3 8.6-21.5 16.8-29.7L384 203.6l0-43.6-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM549.8 139.7c-15.6-15.6-40.9-15.6-56.6 0l-29.4 29.4 71 71 29.4-29.4c15.6-15.6 15.6-40.9 0-56.6l-14.4-14.4zM311.9 321c-4.1 4.1-7 9.2-8.4 14.9l-15 60.1c-1.4 5.5 .2 11.2 4.2 15.2s9.7 5.6 15.2 4.2l60.1-15c5.6-1.4 10.8-4.3 14.9-8.4L512.1 262.7l-71-71L311.9 321z"/></svg></a>';

    // Sales Agent lookup
    $salesAgent = $json["formSalesAgent"] ?? $json["ShopAgent"] ?? '';
    $saleHtml = '-';
    if (!empty($salesAgent)) {
        $agentKey = strtolower($salesAgent);
        $staffData = null;
        
        // Try 1: Match by full name
        if (isset($staffMap[$agentKey])) {
            $staffData = $staffMap[$agentKey];
        }
        // Try 2: Extract first word (nickname) and match by nickname
        else {
            $agentParts = explode(' ', $salesAgent);
            $firstWord = strtolower($agentParts[0]); // e.g., "Pluem" from "Pluem Pluemkamol"
            if (isset($staffByNick[$firstWord])) {
                $staffData = $staffByNick[$firstWord];
            }
        }
        
        // Display staff data if found
        if ($staffData) {
            $pic = $staffData['pic'] ?? 'no_pic.png';
            $nick = htmlspecialchars($staffData['nick']);
            $saleHtml = '<img src="dist/img/crews/'.$pic.'" class="rounded-circle mr-2" style="width:28px;height:28px;object-fit:cover;" onerror="this.src=\'dist/img/crews/no_pic.png\'" alt="">' . $nick;
        } else {
            $saleHtml = '<i class="bi bi-person"></i> ' . htmlspecialchars($salesAgent);
        }
    }

    $data["data"][] = array(
        $date,
        $country,
        $shopType,
        $shopName,
        $signupLogsBtn,
        // $stripeLogsBtn,
        $contractLogsBtn,
        // $row["status"]
        $saleHtml
    );//array
}//foreach

echo json_encode($data);

function showType($shopType){
    switch ($shopType) {
        case "Thai Restaurants & Takeaways":
            return "Restaurant";
            break;
        case "Thai Massage":
            return "Massage";
            break;
        case "Restaurants & Takeaways":
            return "Restaurant";
    }
}
?>
