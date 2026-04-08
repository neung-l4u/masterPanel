<?php
session_start();
require_once 'config.php';

// Set timezone
date_default_timezone_set("Asia/Bangkok");

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

// Check database connection
if (!$pdo) {
    $result['msg'] = "Database connection failed";
    echo json_encode($result);
    exit;
}

try {
    // Insert into database first
    $sql = "INSERT INTO printer_orders (
        first_name, last_name, email, mobile, shop_name, address, 
        country, printer_model, printer_full_name, price, supplier_email
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Updated email configuration based on user changes
    $supplierEmail = 'andrew@aussiepos.com.au'; // Updated to use andrew@aussiepos.com.au for both dev and prod
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
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
    ]);
    
    $orderId = $pdo->lastInsertId();
    $result['order_id'] = $orderId;
    
    // Prepare data for Make.com webhook
    $makeData = [
        'order_id' => $orderId,
        'customer' => [
            'first_name' => $data['firstName'],
            'last_name' => $data['lastName'],
            'full_name' => $data['firstName'] . ' ' . $data['lastName'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'shop_name' => $data['shopName'],
            'address' => $data['address'],
            'country' => $data['country']
        ],
        'printer' => [
            'model' => $data['printerModel'],
            'full_name' => $data['printerFullName'],
            'price' => $data['price']
        ],
        'order_info' => [
            'date' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'supplier_email' => $supplierEmail
        ]
    ];
    
    // Make.com Webhook URL
    $makeWebhookUrl = 'https://hook.us1.make.com/1l4rd87mrfzngjq7a46dilsznhq5y3l7';
    
    // Send data to Make.com
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $makeWebhookUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($makeData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($makeData))
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $makeResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Update database with Make.com status
    if ($httpCode === 200 && empty($curlError)) {
        $updateSql = "UPDATE printer_orders SET email_sent = 1, email_error = NULL WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$orderId]);
        
        error_log("Make.com webhook sent successfully for order #$orderId");
        
        $result = [
            'result' => 1,
            'msg' => "Order saved and sent to Make.com successfully",
            'email' => $data['email'],
            'order_id' => $orderId
        ];
    } else {
        $errorMsg = "Make.com webhook failed - HTTP: $httpCode, Error: $curlError";
        $updateSql = "UPDATE printer_orders SET email_sent = 0, email_error = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$errorMsg, $orderId]);
        
        error_log("Make.com webhook failed for order #$orderId: $errorMsg");
        
        $result = [
            'result' => 0,
            'msg' => "Order saved but Make.com webhook failed: " . $errorMsg,
            'email' => $data['email'],
            'order_id' => $orderId,
            'webhook_error' => $errorMsg
        ];
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $result['msg'] = "Database error occurred";
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $result['msg'] = "An error occurred while processing your order";
}

echo json_encode($result);
?>
