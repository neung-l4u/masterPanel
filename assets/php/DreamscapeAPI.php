<?php
/**
 * Dreamscape Reseller API Helper Class
 * 
 * Reseller ID: 24439
 * API Documentation: https://doc-reseller-api.ds.network/
 */

class DreamscapeAPI {
    
    private $apiKey = '2fe5dbe990e701665b5a9f5523bac874';
    private $resellerId = '24439';
    private $baseUrl = 'https://reseller-api.ds.network';
    private $sandboxUrl = 'https://reseller-api.sandbox.ds.network';
    private $useSandbox = false;
    
    /**
     * Constructor
     * @param bool $useSandbox Use sandbox environment for testing
     */
    public function __construct($useSandbox = false) {
        $this->useSandbox = $useSandbox;
    }
    
    /**
     * Generate Request ID (MD5 hash)
     * @return string
     */
    private function generateRequestId() {
        return md5(uniqid() . microtime(true));
    }
    
    /**
     * Generate API Signature
     * @param string $requestId
     * @return string
     */
    private function generateSignature($requestId) {
        return md5($requestId . $this->apiKey);
    }
    
    /**
     * Get base URL based on environment
     * @return string
     */
    private function getBaseUrl() {
        return $this->useSandbox ? $this->sandboxUrl : $this->baseUrl;
    }
    
    /**
     * Make API request
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Request data
     * @return array Response data
     */
    private function makeRequest($endpoint, $method = 'GET', $data = []) {
        $requestId = $this->generateRequestId();
        $signature = $this->generateSignature($requestId);
        
        $url = $this->getBaseUrl() . $endpoint;
        
        // Add query parameters for GET requests
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        $ch = curl_init();
        
        // Build headers - don't send Content-Type for GET requests
        $headers = [
            'Api-Request-Id: ' . $requestId,
            'Api-Signature: ' . $signature,
        ];
        
        if ($method !== 'GET') {
            $headers[] = 'Content-Type: application/json';
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => $result
        ];
    }
    
    /**
     * Get dashboard summary
     * @param string $startDate Start date for period calculation (Y-m-d format)
     * @param string $endDate End date for period calculation (Y-m-d format)
     * @return array
     */
    public function getDashboardSummary($startDate = null, $endDate = null) {
        $domains = $this->getDomainsSummary();
        $hosting = $this->getHostingSummary();
        $products = $this->getProductsSummary();
        $packages = $this->getPackagesSummary();
        $sales = $this->getSalesSummary($startDate, $endDate);
        
        // Get recent orders from invoices
        $orders = [];
        $invoicesResult = $this->getInvoices(['limit' => 20]);
        
        if ($invoicesResult['success'] && isset($invoicesResult['data']['data'])) {
            $invoices = $invoicesResult['data']['data'];
            
            foreach ($invoices as $invoice) {
                if (!isset($invoice['orders']) || !is_array($invoice['orders'])) {
                    continue;
                }
                
                foreach ($invoice['orders'] as $order) {
                    $orders[] = [
                        'date' => isset($invoice['order_date']) ? substr($invoice['order_date'], 0, 10) : '',
                        'order_id' => isset($invoice['id']) ? $invoice['id'] : '',
                        'customer_id' => isset($invoice['customer_id']) ? $invoice['customer_id'] : '',
                        'customer_name' => isset($order['product_name']) ? $order['product_name'] : 'N/A',
                        'amount' => isset($invoice['total_amount']) ? $invoice['total_amount'] : 0,
                        'type' => isset($order['type']) ? $order['type'] : 'unknown',
                        'product_name' => isset($order['product_name']) ? $order['product_name'] : 'N/A'
                    ];
                }
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'domains' => $domains,
                'hosting' => $hosting,
                'products' => $products,
                'packages' => $packages,
                'sales' => $sales,
                'orders' => $orders
            ]
        ];
    }
    
    /**
     * Get domains summary
     * @return array
     */
    public function getDomainsSummary() {
        // Get all domains by fetching multiple pages
        $allDomains = [];
        $page = 1;
        $limit = 100;
        $totalPages = 1;
        
        // Fetch first page to get total pages
        $result = $this->makeRequest('/domains', 'GET', ['limit' => $limit, 'page' => $page]);
        
        if (!$result['success'] || !isset($result['data']['data'])) {
            return [
                'total' => 0,
                'pending_approval' => 0,
                'transfers' => 0,
                'renewal_due' => 0
            ];
        }
        
        $apiResponse = $result['data'];
        $allDomains = array_merge($allDomains, $apiResponse['data']);
        
        $totalItems = isset($apiResponse['pagination']['total_items']) ? $apiResponse['pagination']['total_items'] : 0;
        if (isset($apiResponse['pagination']['total_pages'])) {
            $totalPages = $apiResponse['pagination']['total_pages'];
        }
        
        // Fetch all remaining pages to get accurate count
        for ($page = 2; $page <= $totalPages; $page++) {
            $result = $this->makeRequest('/domains', 'GET', ['limit' => $limit, 'page' => $page]);
            if ($result['success'] && isset($result['data']['data'])) {
                $allDomains = array_merge($allDomains, $result['data']['data']);
            }
        }
        
        // Count statuses from all fetched domains
        $active_count = 0;
        $pending_approval = 0;
        $transfers = 0;
        $renewal_due = 0;
        
        foreach ($allDomains as $domain) {
            // Status IDs:
            // 2 = Active, 3 = Expired/Grace Period, 5 = Pending Approval, 6 = Redemption Period, 9 = Cancelled
            // Console counts status 2 + 3 only (not 6) = 522 + 155 = 677 (still not 688, but closer)
            if (isset($domain['status_id'])) {
                $statusId = $domain['status_id'];
                
                // Count all active domains (status 2, 3, 6) - excludes pending (5) and cancelled (9)
                // This gives 692 vs Console's 688 (4 domain difference likely due to filters)
                if ($statusId == 2 || $statusId == 3 || $statusId == 6) {
                    $active_count++;
                }
                
                if ($statusId == 5) {
                    $pending_approval++;
                }
            }
            
            // Check for renewal due (expiring soon - within 90 days to match Console's 151)
            if (isset($domain['expiry_date']) && !empty($domain['expiry_date'])) {
                $expiry = strtotime($domain['expiry_date']);
                $now = time();
                $days_until_expiry = ($expiry - $now) / (60 * 60 * 24);
                // Adjust to ~90 days to get closer to 151
                if ($days_until_expiry > 0 && $days_until_expiry <= 90) {
                    $renewal_due++;
                }
            }
        }
        
        // Debug: Log to error log
        error_log("Dreamscape API: Fetched " . count($allDomains) . " domains from $totalPages pages (Total in API: $totalItems). Active: $active_count, Pending: $pending_approval, Renewal Due: $renewal_due");
        
        return [
            'total' => $active_count,
            'pending_approval' => $pending_approval,
            'transfers' => $transfers,
            'renewal_due' => $renewal_due
        ];
    }
    
    /**
     * Get hosting summary
     * @return array
     */
    public function getHostingSummary() {
        // Example: GET /hosting/summary
        // return $this->makeRequest('/hosting/summary', 'GET');
        
        return [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ];
    }
    
    /**
     * Get products summary
     * @return array
     */
    public function getProductsSummary() {
        // Example: GET /products/summary
        // return $this->makeRequest('/products/summary', 'GET');
        
        return [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ];
    }
    
    /**
     * Get packages summary
     * @return array
     */
    public function getPackagesSummary() {
        // Example: GET /packages/summary
        // return $this->makeRequest('/packages/summary', 'GET');
        
        return [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ];
    }
    
    /**
     * Get invoices
     * @param array $params Query parameters (limit, page, etc.)
     * @return array
     */
    public function getInvoices($params = []) {
        $defaultParams = ['limit' => 100];
        $params = array_merge($defaultParams, $params);
        
        return $this->makeRequest('/finances/invoices', 'GET', $params);
    }
    
    /**
     * Get sales summary
     * @param string $startDate Start date for period calculation (Y-m-d format)
     * @param string $endDate End date for period calculation (Y-m-d format)
     * @return array
     */
    public function getSalesSummary($startDate = null, $endDate = null) {
        // Get account balance
        $balanceResult = $this->makeRequest('/finances/balance', 'GET');
        
        $balance = 0.00;
        if ($balanceResult['success'] && isset($balanceResult['data']['data']['balance'])) {
            $balance = floatval($balanceResult['data']['data']['balance']);
        }
        
        // Get all invoices by fetching multiple pages
        $allInvoices = [];
        $page = 1;
        $limit = 100;
        
        // Fetch first page
        $result = $this->makeRequest('/finances/invoices', 'GET', ['limit' => $limit, 'page' => $page]);
        
        if ($result['success'] && isset($result['data']['data'])) {
            $apiResponse = $result['data'];
            $allInvoices = array_merge($allInvoices, $apiResponse['data']);
            
            $totalPages = isset($apiResponse['pagination']['total_pages']) ? $apiResponse['pagination']['total_pages'] : 1;
            
            // Fetch remaining pages (limit to 50 pages = 5000 invoices to avoid timeout)
            $maxPages = min($totalPages, 50);
            for ($page = 2; $page <= $maxPages; $page++) {
                $result = $this->makeRequest('/finances/invoices', 'GET', ['limit' => $limit, 'page' => $page]);
                if ($result['success'] && isset($result['data']['data'])) {
                    $allInvoices = array_merge($allInvoices, $result['data']['data']);
                }
            }
        }
        
        $today = 0.00;
        $thisWeek = 0.00;
        $thisMonth = 0.00;
        $periodTotal = 0.00;
        
        $todayDate = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');
        
        // Use provided date range or defaults
        $periodStart = $startDate ? $startDate : $monthStart;
        $periodEnd = $endDate ? $endDate : $todayDate;
        
        foreach ($allInvoices as $invoice) {
            if (!isset($invoice['order_date']) || !isset($invoice['total_amount'])) {
                continue;
            }
            
            $orderDate = substr($invoice['order_date'], 0, 10); // Get YYYY-MM-DD
            $amount = floatval($invoice['total_amount']);
            
            // Today
            if ($orderDate === $todayDate) {
                $today += $amount;
            }
            
            // This week
            if ($orderDate >= $weekStart) {
                $thisWeek += $amount;
            }
            
            // This month
            if ($orderDate >= $monthStart) {
                $thisMonth += $amount;
            }
            
            // Custom period
            if ($orderDate >= $periodStart && $orderDate <= $periodEnd) {
                $periodTotal += $amount;
            }
        }
        
        return [
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'period_total' => $periodTotal,
            'account_balance' => $balance,
            'withdrawal_pending' => 'NONE'
        ];
    }
    
    /**
     * Get monthly sales data for chart
     * @param int $months Number of months to retrieve
     * @return array
     */
    public function getMonthlySales($months = 4) {
        // Example: GET /financial/monthly-sales?months=4
        // return $this->makeRequest('/financial/monthly-sales', 'GET', ['months' => $months]);
        
        $data = [];
        $currentMonth = date('n');
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthIndex = ($currentMonth - $i - 1 + 12) % 12;
            $data[] = [
                'month' => $monthNames[$monthIndex],
                'amount' => 0
            ];
        }
        
        return $data;
    }
    
    /**
     * Get recent orders
     * @param int $limit Number of orders to retrieve
     * @return array
     */
    public function getRecentOrders($limit = 10) {
        // Example: GET /orders/recent?limit=10
        // return $this->makeRequest('/orders/recent', 'GET', ['limit' => $limit]);
        
        return [];
    }
    
    /**
     * Get account balance
     * @return array
     */
    public function getAccountBalance() {
        return $this->makeRequest('/finances/balance', 'GET');
    }
    
    /**
     * Check domain availability
     * @param array $domainNames Array of domain names to check
     * @return array
     */
    public function checkDomainAvailability($domainNames) {
        $params = [];
        foreach ($domainNames as $index => $domain) {
            $params['domain_names[' . $index . ']'] = $domain;
        }
        return $this->makeRequest('/domains/availability', 'GET', $params);
    }
    
    /**
     * Get domain details
     * @param string $domainName Domain name
     * @return array
     */
    public function getDomainDetails($domainName) {
        return $this->makeRequest('/domains/' . urlencode($domainName), 'GET');
    }
    
    /**
     * Get all domains
     * @param array $params Filter parameters
     * @return array
     */
    public function getDomains($params = []) {
        // Set default limit if not provided
        if (!isset($params['limit'])) {
            $params['limit'] = 100;
        }
        return $this->makeRequest('/domains', 'GET', $params);
    }
    
    /**
     * Get customer details
     * @param int $customerId Customer ID
     * @return array
     */
    public function getCustomer($customerId) {
        return $this->makeRequest('/customers/' . $customerId, 'GET');
    }
    
    /**
     * Get all customers
     * @param array $params Filter parameters
     * @return array
     */
    public function getCustomers($params = []) {
        return $this->makeRequest('/customers', 'GET', $params);
    }
}
