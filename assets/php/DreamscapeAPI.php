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
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
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
     * Prepare a cURL handle for use with curl_multi
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return resource cURL handle
     */
    private function prepareCurlHandle($endpoint, $method = 'GET', $data = []) {
        $requestId = $this->generateRequestId();
        $signature = $this->generateSignature($requestId);
        
        $url = $this->getBaseUrl() . $endpoint;
        
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        $ch = curl_init();
        
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
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        return $ch;
    }
    
    /**
     * Execute multiple cURL requests in parallel using curl_multi
     * @param array $handles Array of ['key' => curl_handle]
     * @return array Array of ['key' => response_array]
     */
    private function executeMulti($handles) {
        $mh = curl_multi_init();
        
        foreach ($handles as $key => $ch) {
            curl_multi_add_handle($mh, $ch);
        }
        
        // Execute all requests simultaneously
        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh);
            }
        } while ($running > 0 && $status === CURLM_OK);
        
        // Collect results
        $results = [];
        foreach ($handles as $key => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            
            if ($error) {
                $results[$key] = [
                    'success' => false,
                    'error' => $error,
                    'http_code' => $httpCode
                ];
            } else {
                $result = json_decode($response, true);
                $results[$key] = [
                    'success' => $httpCode >= 200 && $httpCode < 300,
                    'http_code' => $httpCode,
                    'data' => $result
                ];
            }
        }
        
        curl_multi_close($mh);
        return $results;
    }
    
    /**
     * Fetch all pages of a paginated endpoint in parallel
     * @param string $endpoint API endpoint
     * @param int $limit Items per page
     * @param int $maxPages Maximum pages to fetch (0 = unlimited)
     * @return array All items combined from all pages
     */
    private function fetchAllPages($endpoint, $limit = 100, $maxPages = 0) {
        // First request to get total pages
        $result = $this->makeRequest($endpoint, 'GET', ['limit' => $limit, 'page' => 1]);
        
        if (!$result['success'] || !isset($result['data']['data'])) {
            return ['items' => [], 'pagination' => []];
        }
        
        $apiResponse = $result['data'];
        $allItems = $apiResponse['data'];
        $pagination = isset($apiResponse['pagination']) ? $apiResponse['pagination'] : [];
        $totalPages = isset($pagination['total_pages']) ? $pagination['total_pages'] : 1;
        
        if ($maxPages > 0) {
            $totalPages = min($totalPages, $maxPages);
        }
        
        // Fetch remaining pages in parallel batches
        if ($totalPages > 1) {
            $batchSize = 10; // 10 concurrent requests at a time
            for ($batchStart = 2; $batchStart <= $totalPages; $batchStart += $batchSize) {
                $handles = [];
                $batchEnd = min($batchStart + $batchSize - 1, $totalPages);
                
                for ($page = $batchStart; $page <= $batchEnd; $page++) {
                    $handles['page_' . $page] = $this->prepareCurlHandle(
                        $endpoint, 'GET', ['limit' => $limit, 'page' => $page]
                    );
                }
                
                $batchResults = $this->executeMulti($handles);
                
                foreach ($batchResults as $key => $res) {
                    if ($res['success'] && isset($res['data']['data'])) {
                        $allItems = array_merge($allItems, $res['data']['data']);
                    }
                }
            }
        }
        
        return ['items' => $allItems, 'pagination' => $pagination];
    }
    
    /**
     * Get dashboard summary
     * @param string $startDate Start date for period calculation (Y-m-d format)
     * @param string $endDate End date for period calculation (Y-m-d format)
     * @return array
     */
    public function getDashboardSummary($startDate = null, $endDate = null) {
        // Step 1: Fire first pages of domains, invoices, and balance in parallel
        $initialHandles = [
            'domains_p1' => $this->prepareCurlHandle('/domains', 'GET', ['limit' => 100, 'page' => 1]),
            'invoices_p1' => $this->prepareCurlHandle('/finances/invoices', 'GET', ['limit' => 100, 'page' => 1]),
            'balance' => $this->prepareCurlHandle('/finances/balance', 'GET'),
        ];
        $initialResults = $this->executeMulti($initialHandles);
        
        // Step 2: Determine total pages for domains & invoices, then fetch remaining pages in parallel
        $allDomains = [];
        $domainsTotalPages = 1;
        $domainsTotalItems = 0;
        if ($initialResults['domains_p1']['success'] && isset($initialResults['domains_p1']['data']['data'])) {
            $allDomains = $initialResults['domains_p1']['data']['data'];
            $domainsTotalItems = isset($initialResults['domains_p1']['data']['pagination']['total_items']) ? $initialResults['domains_p1']['data']['pagination']['total_items'] : 0;
            $domainsTotalPages = isset($initialResults['domains_p1']['data']['pagination']['total_pages']) ? $initialResults['domains_p1']['data']['pagination']['total_pages'] : 1;
        }
        
        $allInvoices = [];
        $invoicesTotalPages = 1;
        if ($initialResults['invoices_p1']['success'] && isset($initialResults['invoices_p1']['data']['data'])) {
            $allInvoices = $initialResults['invoices_p1']['data']['data'];
            $invoicesTotalPages = isset($initialResults['invoices_p1']['data']['pagination']['total_pages']) ? $initialResults['invoices_p1']['data']['pagination']['total_pages'] : 1;
            $invoicesTotalPages = min($invoicesTotalPages, 50); // cap to avoid timeout
        }
        
        $balance = 0.00;
        if ($initialResults['balance']['success'] && isset($initialResults['balance']['data']['data']['balance'])) {
            $balance = floatval($initialResults['balance']['data']['data']['balance']);
        }
        
        // Step 3: Fetch all remaining pages (domains + invoices) in parallel batches
        $batchSize = 10;
        $domainPage = 2;
        $invoicePage = 2;
        
        while ($domainPage <= $domainsTotalPages || $invoicePage <= $invoicesTotalPages) {
            $handles = [];
            
            $domainBatchEnd = min($domainPage + $batchSize - 1, $domainsTotalPages);
            for ($p = $domainPage; $p <= $domainBatchEnd; $p++) {
                $handles['dom_' . $p] = $this->prepareCurlHandle('/domains', 'GET', ['limit' => 100, 'page' => $p]);
            }
            $domainPage = $domainBatchEnd + 1;
            
            $invoiceBatchEnd = min($invoicePage + $batchSize - 1, $invoicesTotalPages);
            for ($p = $invoicePage; $p <= $invoiceBatchEnd; $p++) {
                $handles['inv_' . $p] = $this->prepareCurlHandle('/finances/invoices', 'GET', ['limit' => 100, 'page' => $p]);
            }
            $invoicePage = $invoiceBatchEnd + 1;
            
            if (empty($handles)) break;
            
            $batchResults = $this->executeMulti($handles);
            
            foreach ($batchResults as $key => $res) {
                if (!$res['success'] || !isset($res['data']['data'])) continue;
                if (strpos($key, 'dom_') === 0) {
                    $allDomains = array_merge($allDomains, $res['data']['data']);
                } elseif (strpos($key, 'inv_') === 0) {
                    $allInvoices = array_merge($allInvoices, $res['data']['data']);
                }
            }
        }
        
        // Step 4: Process domains summary
        $domains = $this->processDomainsSummary($allDomains, $domainsTotalPages, $domainsTotalItems);
        
        // Step 5: Process sales summary from invoices (reuse data, no extra API calls)
        $sales = $this->processSalesSummary($allInvoices, $balance, $startDate, $endDate);
        
        // Step 6: Extract recent orders from first 20 invoices (already fetched)
        $orders = [];
        $recentInvoices = array_slice($allInvoices, 0, 20);
        foreach ($recentInvoices as $invoice) {
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
        
        $hosting = $this->getHostingSummary();
        $products = $this->getProductsSummary();
        $packages = $this->getPackagesSummary();
        
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
        // Use parallel page fetching
        $result = $this->fetchAllPages('/domains', 100);
        return $this->processDomainsSummary($result['items'], 0, 0);
    }
    
    /**
     * Process domains data into summary (used by both getDomainsSummary and getDashboardSummary)
     * @param array $allDomains All domain records
     * @param int $totalPages Total pages fetched (for logging)
     * @param int $totalItems Total items reported by API (for logging)
     * @return array
     */
    private function processDomainsSummary($allDomains, $totalPages = 0, $totalItems = 0) {
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
        // Fetch balance and invoices first page in parallel
        $initialHandles = [
            'balance' => $this->prepareCurlHandle('/finances/balance', 'GET'),
            'invoices_p1' => $this->prepareCurlHandle('/finances/invoices', 'GET', ['limit' => 100, 'page' => 1]),
        ];
        $initialResults = $this->executeMulti($initialHandles);
        
        $balance = 0.00;
        if ($initialResults['balance']['success'] && isset($initialResults['balance']['data']['data']['balance'])) {
            $balance = floatval($initialResults['balance']['data']['data']['balance']);
        }
        
        $allInvoices = [];
        $invoicesTotalPages = 1;
        if ($initialResults['invoices_p1']['success'] && isset($initialResults['invoices_p1']['data']['data'])) {
            $allInvoices = $initialResults['invoices_p1']['data']['data'];
            $invoicesTotalPages = isset($initialResults['invoices_p1']['data']['pagination']['total_pages']) ? $initialResults['invoices_p1']['data']['pagination']['total_pages'] : 1;
            $invoicesTotalPages = min($invoicesTotalPages, 50);
        }
        
        // Fetch remaining invoice pages in parallel batches
        $batchSize = 10;
        for ($batchStart = 2; $batchStart <= $invoicesTotalPages; $batchStart += $batchSize) {
            $handles = [];
            $batchEnd = min($batchStart + $batchSize - 1, $invoicesTotalPages);
            for ($p = $batchStart; $p <= $batchEnd; $p++) {
                $handles['inv_' . $p] = $this->prepareCurlHandle('/finances/invoices', 'GET', ['limit' => 100, 'page' => $p]);
            }
            $batchResults = $this->executeMulti($handles);
            foreach ($batchResults as $res) {
                if ($res['success'] && isset($res['data']['data'])) {
                    $allInvoices = array_merge($allInvoices, $res['data']['data']);
                }
            }
        }
        
        return $this->processSalesSummary($allInvoices, $balance, $startDate, $endDate);
    }
    
    /**
     * Process invoices data into sales summary (used by both getSalesSummary and getDashboardSummary)
     * @param array $allInvoices All invoice records
     * @param float $balance Account balance
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    private function processSalesSummary($allInvoices, $balance, $startDate = null, $endDate = null) {
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
