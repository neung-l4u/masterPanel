<?php
session_start();
require_once '../db/db.php';

// Set timezone
date_default_timezone_set("Asia/Bangkok");

// Determine which database configuration to use
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8888']);
if ($isLocalhost) {
    // Local development - use default db() constructor
    $useLocalDB = true;
} else {
    // Production server - use server credentials
    require_once '../db/initDB_server.php';
    $useLocalDB = false;
}

function getRequestData($key, $default = 'undefined')
{
    return isset($_REQUEST[$key]) && !empty($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100;
}

function validateMobile($mobile) {
    $cleanMobile = preg_replace('/[^0-9+]/', '', $mobile);
    return strlen($cleanMobile) >= 8 && strlen($cleanMobile) <= 15;
}

function validateName($name) {
    return strlen(trim($name)) >= 2 && strlen(trim($name)) <= 50 && preg_match('/^[a-zA-Z\s\-\']+$/', $name);
}

function validateShopName($shopName) {
    return strlen(trim($shopName)) >= 2 && strlen(trim($shopName)) <= 200;
}

function validateAddress($address) {
    return strlen(trim($address)) >= 10 && strlen(trim($address)) <= 500;
}

function validateCountry($country) {
    return in_array($country, ['AU', 'NZ']);
}

function validatePrinterModel($model) {
    return in_array($model, ['TM-T82IIIL', 'TM-M30']);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get and sanitize input data
$data = [
    'firstName' => sanitizeInput(getRequestData('firstName', '')),
    'lastName' => sanitizeInput(getRequestData('lastName', '')),
    'email' => sanitizeInput(getRequestData('email', '')),
    'mobile' => sanitizeInput(getRequestData('mobile', '')),
    'shopName' => sanitizeInput(getRequestData('shopName', '')),
    'address' => sanitizeInput(getRequestData('address', '')),
    'printerModel' => sanitizeInput(getRequestData('printerModel', '')),
    'printerFullName' => sanitizeInput(getRequestData('printerFullName', '')),
    'price' => sanitizeInput(getRequestData('price', '')),
    'country' => sanitizeInput(getRequestData('country', '')),
];

$result = [
    'result' => 0,
    'msg' => "",
    'email' => $data['email'],
    'order_id' => null
];

// Enhanced validation with specific error messages
$validationErrors = [];

// Check required fields
if (empty($data['firstName'])) {
    $validationErrors[] = "First name is required";
} elseif (!validateName($data['firstName'])) {
    $validationErrors[] = "First name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes";
}

if (empty($data['lastName'])) {
    $validationErrors[] = "Last name is required";
} elseif (!validateName($data['lastName'])) {
    $validationErrors[] = "Last name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes";
}

if (empty($data['email'])) {
    $validationErrors[] = "Email address is required";
} elseif (!validateEmail($data['email'])) {
    $validationErrors[] = "Please enter a valid email address (max 100 characters)";
}

if (empty($data['mobile'])) {
    $validationErrors[] = "Mobile number is required";
} elseif (!validateMobile($data['mobile'])) {
    $validationErrors[] = "Please enter a valid mobile number (8-15 digits)";
}

if (empty($data['shopName'])) {
    $validationErrors[] = "Shop name is required";
} elseif (!validateShopName($data['shopName'])) {
    $validationErrors[] = "Shop name must be 2-200 characters";
}

if (empty($data['address'])) {
    $validationErrors[] = "Shipping address is required";
} elseif (!validateAddress($data['address'])) {
    $validationErrors[] = "Address must be 10-500 characters";
}

if (empty($data['country'])) {
    $validationErrors[] = "Country selection is required";
} elseif (!validateCountry($data['country'])) {
    $validationErrors[] = "Please select a valid country (AU or NZ)";
}

if (empty($data['printerModel'])) {
    $validationErrors[] = "Printer model selection is required";
} elseif (!validatePrinterModel($data['printerModel'])) {
    $validationErrors[] = "Please select a valid printer model";
}

if (!empty($validationErrors)) {
    $result['msg'] = implode('. ', $validationErrors);
    $result['validation_errors'] = $validationErrors;
    echo json_encode($result);
    exit;
}

try {
    // Initialize database connection
    if ($useLocalDB) {
        $db = new db(); // Use default local credentials
    }
    // else: $db is already initialized from initDB_server.php
    
    // Insert into database
    $supplierEmail = 'andrew@aussiepos.com.au';
    
    $db->query("INSERT INTO printer_orders (
        first_name, last_name, email, mobile, shop_name, address, 
        country, printer_model, printer_full_name, price, supplier_email
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        $data['firstName'],
        $data['lastName'], 
        $data['email'],
        $data['mobile'],
        $data['shopName'],
        $data['address'],
        $data['country'],
        $data['printerModel'],
        $data['printerFullName'],
        $data['price'],
        $supplierEmail
    );
    
    $orderId = $db->lastInsertID();
    
    $result = [
        'result' => 1,
        'msg' => "Order saved successfully",
        'email' => $data['email'],
        'order_id' => $orderId
    ];
    
    $db->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $result['msg'] = "Database error occurred: " . $e->getMessage();
}

echo json_encode($result);
?>
