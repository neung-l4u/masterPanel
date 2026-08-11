<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

// Helper function to parse discount code JSON
function parseDiscountCode($code) {
    if (empty($code)) return ['priceId' => '', 'discount' => '-'];
    
    // Keep original for fallback
    $original = $code;
    
    // Try multiple times for double-encoded JSON
    for ($i = 0; $i < 3; $i++) {
        $code = stripslashes($code);
        $decoded = json_decode($code, true);
        
        if (is_array($decoded) && !empty($decoded)) {
            $priceId = array_key_first($decoded);
            $discount = $decoded[$priceId] ?? '';
            // Clean up discount value - remove backslashes and quotes
            $discount = str_replace(["\\", '"', '"', '"'], '', $discount);
            $discount = trim($discount);
            return [
                'priceId' => $priceId,
                'discount' => empty($discount) ? '-' : $discount
            ];
        }
        
        // If result is string, might be double-encoded
        if (is_string($decoded) || $decoded === null) {
            // Try decode the result again
            $test = json_decode($code);
            if (is_string($test)) {
                $code = $test;
            }
        }
    }
    
    // Try direct extraction with regex as last resort
    if (preg_match('/"([^"]+)":"?([^",}]*)"?/', $original, $matches)) {
        return [
            'priceId' => $matches[1],
            'discount' => empty($matches[2]) ? '-' : $matches[2]
        ];
    }
    
    // Not valid JSON, return as-is
    return ['priceId' => $original, 'discount' => '-'];
}

// Country to account mapping
$COUNTRY_TO_ACCOUNT = [
    'Australia' => 'au',
    'New Zealand' => 'au',
    'United Kingdom' => 'au',
    'United States' => 'us',
    'Canada' => 'us',
    'Thailand' => 'th',
];

// Country to country code mapping
$COUNTRY_TO_CODE = [
    'Australia' => 'au',
    'New Zealand' => 'nz',
    'United Kingdom' => 'uk',
    'United States' => 'us',
    'Canada' => 'ca',
    'Thailand' => 'th',
];

// Country to currency mapping
$COUNTRY_TO_CURRENCY = [
    'Australia' => 'aud',
    'New Zealand' => 'nzd',
    'United Kingdom' => 'gbp',
    'United States' => 'usd',
    'Canada' => 'cad',
    'Thailand' => 'thb',
];

// Get filter parameters
$dateStart = $_POST['dateStart'] ?? '';
$dateEnd = $_POST['dateEnd'] ?? '';
$countryFilter = $_POST['country'] ?? '';

// Build query
$query = 'SELECT l.id, l.dataLogs, l.dataStripe, l.stripeResult, l.countryCode, l.createAt FROM logssignup l WHERE 1=1';

// Add date range filter
if (!empty($dateStart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart)) {
    $query .= ' AND DATE(l.createAt) >= "' . $dateStart . '"';
}
if (!empty($dateEnd) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
    $query .= ' AND DATE(l.createAt) <= "' . $dateEnd . '"';
}

// Add country filter
if (!empty($countryFilter) && preg_match('/^[A-Z]{2}$/', $countryFilter)) {
    $query .= ' AND l.countryCode = "' . $countryFilter . '"';
}

$query .= ' ORDER BY l.createAt DESC';

$result = $db->query($query)->fetchAll();

$data = array("data" => array());

foreach ($result as $row) {
    $json = json_decode($row["dataLogs"], true);
    $stripeJson = json_decode($row["dataStripe"], true);
    $stripeResultJson = json_decode($row["stripeResult"], true);
    
    // Skip if no dataLogs
    if (empty($json)) continue;
    
    $mainProduct = $json["MainProduct"] ?? '';
    $mainAddons = $json["MainAddons"] ?? '';
    
    // Check if MainProduct or MainAddons contains AI-related terms
    $isAI = false;
    $aiTerms = ['AI Receptionist', 'AI +', 'AI+'];
    
    foreach ($aiTerms as $term) {
        if (stripos($mainProduct, $term) !== false || stripos($mainAddons, $term) !== false) {
            $isAI = true;
            break;
        }
    }
    
    // Skip if not AI-related
    if (!$isAI) continue;
    
    // Get country name from country code
    $countryName = match ($row["countryCode"]) {
        "AU" => "Australia",
        "NZ" => "New Zealand",
        "UK" => "United Kingdom",
        "US" => "United States",
        "CA" => "Canada",
        "TH" => "Thailand",
        default => $row["countryCode"],
    };
    
    // Get mappings
    $account = $COUNTRY_TO_ACCOUNT[$countryName] ?? strtolower($row["countryCode"]);
    $countryCode = $COUNTRY_TO_CODE[$countryName] ?? strtolower($row["countryCode"]);
    $currency = $COUNTRY_TO_CURRENCY[$countryName] ?? 'aud';
    
    // Get fields
    $shopName = $json["ShopName"] ?? '';
    $email = $json["Email"] ?? '';
    
    // Get discount codes as-is (JSON format)
    $mainPriceId = $json["usageMainDiscountCode"] ?? '';
    $mainDiscount = ''; // Not shown separately
    
    $addonPriceId = $json["usageAddonDiscountCode"] ?? '';
    $addonDiscount = '';
    // Get customer_id from stripeResult (format: {"message":"Success","customer_id":"cus_xxx","invoice_id":"xxx"})
    $stripeID = $stripeResultJson["customer_id"] ?? 
                 $stripeResultJson["stripeID"] ?? $stripeResultJson["customer"] ?? $stripeResultJson["customerId"] ?? 
                 $stripeJson["stripeID"] ?? $stripeJson["customerId"] ?? '';
    
    // Determine priceId for webhook - use the extracted price_id
    $priceId = '';
    if (stripos($mainProduct, 'AI') !== false) {
        $priceId = $mainPriceId;
    } elseif (stripos($mainAddons, 'AI') !== false) {
        $priceId = $addonPriceId;
    }
    
    // Create payload for webhook
    $payload = json_encode([
        'stripeID' => $stripeID,
        'customerEmail' => $email,
        'shopName' => $shopName,
        'account' => $account,
        'country' => $countryCode,
        'currency' => $currency,
        'priceId' => $priceId,
    ]);
    
    // Send Mail button - disabled if no customer ID
    if (empty($stripeID)) {
        $sendMailBtn = '<button class="btn btn-sm btn-secondary" disabled title="No Customer ID">
            <i class="bi bi-envelope"></i> Send Mail
        </button>';
    } else {
        $sendMailBtn = '<button class="btn btn-sm btn-primary send-mail-btn" 
            data-payload="' . htmlspecialchars($payload, ENT_QUOTES, 'UTF-8') . '" 
            onclick="sendMail(this)">
            <i class="bi bi-envelope"></i> Send Mail
        </button>';
    }
    
    $data["data"][] = array(
        $row["createAt"],
        $shopName,
        $countryName,
        $mainProduct,
        $mainPriceId,       // Price ID with discount (JSON format)
        $mainAddons,
        $addonPriceId,      // Add-on Price ID
        $stripeID,
        $email,
        $sendMailBtn
    );
}

echo json_encode($data);
