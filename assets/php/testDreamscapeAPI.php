<?php
/**
 * Test Dreamscape API Connection
 * This script tests various API endpoints to see what data is available
 */

require_once 'DreamscapeAPI.php';

// Create API instance
$api = new DreamscapeAPI();

echo "<h1>Dreamscape API Test</h1>";
echo "<hr>";

// Test 1: Get Account Balance
echo "<h2>Test 1: Account Balance</h2>";
$result = $api->getAccountBalance();
echo "<pre>";
print_r($result);
echo "</pre>";
echo "<hr>";

// Test 2: Get Domains
echo "<h2>Test 2: Get Domains</h2>";
$result = $api->getDomains(['limit' => 10]);
echo "<pre>";
print_r($result);
echo "</pre>";
echo "<hr>";

// Test 3: Get Customers
echo "<h2>Test 3: Get Customers</h2>";
$result = $api->getCustomers(['limit' => 10]);
echo "<pre>";
print_r($result);
echo "</pre>";
echo "<hr>";

// Test 4: Get Invoices
echo "<h2>Test 4: Get Invoices</h2>";
$result = $api->getInvoices(['limit' => 10]);
echo "<pre>";
print_r($result);
echo "</pre>";
echo "<hr>";

// Test 5: Check Domain Availability
echo "<h2>Test 5: Check Domain Availability</h2>";
$result = $api->checkDomainAvailability(['test123.com.au', 'example.com.au']);
echo "<pre>";
print_r($result);
echo "</pre>";
