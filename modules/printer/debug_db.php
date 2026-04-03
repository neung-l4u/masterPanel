<?php
// Debug database connection and table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'assets/db/db.php';

echo "<h2>Database Debug Information</h2>";

// Try to connect to database
try {
    $db = new db();
    echo "<p style='color: green;'>✅ Database connection successful (using mysqli class)</p>";
    
    // Check if table exists
    $tables = $db->query("SHOW TABLES LIKE 'printer_orders'")->fetchAll();
    if (count($tables) > 0) {
        echo "<p style='color: green;'>✅ Table 'printer_orders' exists</p>";
        
        // Show table structure
        $structure = $db->query("DESCRIBE printer_orders")->fetchAll();
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($structure as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Count existing records
        $countResult = $db->query("SELECT COUNT(*) as count FROM printer_orders")->fetchArray();
        $count = $countResult['count'];
        echo "<p>Current records in table: <strong>$count</strong></p>";
        
        // Show recent records
        if ($count > 0) {
            $orders = $db->query("SELECT * FROM printer_orders ORDER BY order_date DESC LIMIT 5")->fetchAll();
            echo "<h3>Recent Orders:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            $first = true;
            foreach ($orders as $row) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Table 'printer_orders' does not exist</p>";
        echo "<p>Run the SQL script to create the table:</p>";
        echo "<pre>CREATE TABLE IF NOT EXISTS `printer_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `shop_name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `country` varchar(2) NOT NULL,
  `printer_model` varchar(50) NOT NULL,
  `printer_full_name` varchar(255) NOT NULL,
  `price` varchar(50) NOT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `email_error` text DEFAULT NULL,
  `supplier_email` varchar(100) NOT NULL,
  `order_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','processed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Check your database settings:</p>";
    echo "<ul>";
    echo "<li>Host: localhost</li>";
    echo "<li>Database: DB_Localforyou</li>";
    echo "<li>Username: root</li>";
    echo "<li>Password: root</li>";
    echo "</ul>";
}

// Test form data processing
echo "<hr><h2>Test Form Data Processing</h2>";
if ($_POST) {
    echo "<h3>Received POST data:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Test the saveOrderDB.php processing
    echo "<h3>Processing with saveOrderDB.php logic:</h3>";
    
    function getRequestData($key, $default = 'undefined') {
        return isset($_REQUEST[$key]) && !empty($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }
    
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
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
    
    echo "<h4>Sanitized data:</h4>";
    echo "<pre>" . print_r($data, true) . "</pre>";
    
} else {
    echo "<form method='POST'>";
    echo "<p>Test form (fill out to test database insertion):</p>";
    echo "<input type='text' name='firstName' placeholder='First Name' required><br><br>";
    echo "<input type='text' name='lastName' placeholder='Last Name' required><br><br>";
    echo "<input type='email' name='email' placeholder='Email' required><br><br>";
    echo "<input type='text' name='mobile' placeholder='Mobile' required><br><br>";
    echo "<input type='text' name='shopName' placeholder='Shop Name' required><br><br>";
    echo "<textarea name='address' placeholder='Address' required></textarea><br><br>";
    echo "<select name='country' required><option value=''>Select Country</option><option value='AU'>Australia</option><option value='NZ'>New Zealand</option></select><br><br>";
    echo "<select name='printerModel' required><option value=''>Select Printer</option><option value='TM-T82IIIL'>TM-T82IIIL</option><option value='TM-M30'>TM-M30</option></select><br><br>";
    echo "<input type='text' name='printerFullName' placeholder='Printer Full Name' required><br><br>";
    echo "<input type='text' name='price' placeholder='Price' required><br><br>";
    echo "<input type='submit' value='Test Database Insert'>";
    echo "</form>";
}
?>
