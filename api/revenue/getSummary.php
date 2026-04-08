<?php
/**
 * Revenue Summary API — aggregates data from Monday.com boards
 * Returns totals by type (stripe_au, stripe_us, stripe_th, yelp, sms)
 * Grouped by month for MoM/YoY comparisons
 * 
 * Usage: getSummary.php           (serve from cache)
 *        getSummary.php?refresh=1  (force re-fetch from Monday.com ~3min)
 */
ini_set('memory_limit', '512M');
set_time_limit(600);
header('Content-Type: application/json');

// === CACHING ===
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . '/summary_latest.json';
$cacheTTL = 86400; // 24 hours
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cached = file_get_contents($cacheFile);
    if ($cached) {
        $cachedData = json_decode($cached, true);
        if ($cachedData) {
            $cachedData['source'] = 'cache';
            $cachedData['cache_age'] = time() - filemtime($cacheFile);
            echo json_encode($cachedData, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$apiUrl = "https://api.monday.com/v2";
$token = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";

$boardIds = [
    'au' => '5026435242',
    'us' => '5026427192',
    'th' => '5026435384'
];

$headers = [
    "Content-Type: application/json",
    "Authorization: " . $token
];

// Column IDs
$COL_AMOUNT      = 'text_mm086z4k';
$COL_FEE         = 'text_mm08p503';
$COL_NET         = 'text_mm08ab90';
$COL_CREATED     = 'date_mm086vct';
$COL_TYPE        = 'text_mm08ka8n';
$COL_SOURCE      = 'text_mm08qfz7';
$COL_DESCRIPTION = 'text_mm08js6t';
$COL_REVENUE_TXT = 'text_mm082qz9';
$COL_CURRENCY    = 'text_mm08xpqq';

// All columns we need
$columnIds = [
    $COL_AMOUNT, $COL_FEE, $COL_NET, $COL_CREATED,
    $COL_TYPE, $COL_SOURCE, $COL_DESCRIPTION,
    $COL_REVENUE_TXT, $COL_CURRENCY
];
$columnIdsStr = '["' . implode('","', $columnIds) . '"]';

// Monday.com API call helper
function callAPI($url, $headers, $query) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) return ['error' => $error];
    return json_decode($response, true);
}

// Parse number from text
function parseNum($v) {
    if (empty($v)) return 0;
    return floatval(preg_replace('/[^0-9.\-]/', '', str_replace('..', '.', $v)));
}

// Group matching
function getGroupType($groupTitle) {
    $t = strtolower($groupTitle);
    if (strpos($t, 'sms_marketing') !== false) return 'sms';
    if (strpos($t, 'report_yelp_ads') !== false) return 'yelp';
    if (strpos($t, 'payout') !== false || strpos($t, 'transaction') !== false) return 'stripe';
    return 'unknown';
}

function getGroupSubType($groupTitle) {
    $t = strtolower($groupTitle);
    if (strpos($t, 'payout') !== false) return 'payout';
    if (strpos($t, 'transaction') !== false) return 'transaction';
    return 'other';
}

// Extract month from item name for SMS (e.g., "SMS_Jan" -> month number)
function extractMonthFromName($name, $groupTitle) {
    $months = [
        'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
        'may' => '05', 'jun' => '06', 'jul' => '07', 'aug' => '08',
        'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12'
    ];
    $nameLower = strtolower($name);
    foreach ($months as $m => $num) {
        if (strpos($nameLower, $m) !== false) {
            // Extract year from group title
            if (preg_match('/(\d{4})/', $groupTitle, $yMatch)) {
                return $yMatch[1] . '-' . $num;
            }
        }
    }
    return null;
}

// Initialize accumulator for a revenue type
function initAccum() {
    return [
        'amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0,
        'count' => 0, 'charge_count' => 0,
        'by_month' => [],
        'top_sources' => []
    ];
}

function initMonthAccum() {
    return ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
}

// Process a batch of items into accumulators
function processItems(&$accum, $items, $groupTitle, $country, $columnIds) {
    global $COL_AMOUNT, $COL_FEE, $COL_NET, $COL_CREATED, $COL_TYPE, $COL_SOURCE, $COL_DESCRIPTION, $COL_REVENUE_TXT;
    
    $groupType = getGroupType($groupTitle);
    $groupSubType = getGroupSubType($groupTitle);
    
    foreach ($items as $item) {
        // Extract column values into associative array
        $cols = [];
        if (isset($item['column_values'])) {
            foreach ($item['column_values'] as $cv) {
                $cols[$cv['id']] = $cv['text'] ?? '';
            }
        }
        
        $amount = parseNum($cols[$COL_AMOUNT] ?? '');
        $fee = parseNum($cols[$COL_FEE] ?? '');
        $net = parseNum($cols[$COL_NET] ?? '');
        $revenue = parseNum($cols[$COL_REVENUE_TXT] ?? '');
        $created = $cols[$COL_CREATED] ?? '';
        $type = $cols[$COL_TYPE] ?? '';
        $source = $cols[$COL_SOURCE] ?? '';
        $description = $cols[$COL_DESCRIPTION] ?? '';
        
        // Determine month key
        $monthKey = null;
        if (!empty($created) && preg_match('/^\d{4}-\d{2}/', $created, $m)) {
            $monthKey = $m[0]; // "2025-01"
        }
        
        // For SMS/Yelp without created date, extract from name
        if ($monthKey === null && ($groupType === 'sms' || $groupType === 'yelp')) {
            $monthKey = extractMonthFromName($item['name'] ?? '', $groupTitle);
        }
        
        // Accumulate totals
        $accum['amount'] += $amount;
        $accum['fee'] += $fee;
        $accum['net'] += $net;
        $accum['revenue'] += $revenue;
        $accum['count']++;
        
        // Count charges
        if (strtolower($type) === 'charge') {
            $accum['charge_count']++;
            // Track top sources for customer aggregation
            if (!empty($source)) {
                if (!isset($accum['top_sources'][$source])) {
                    $accum['top_sources'][$source] = ['amount' => 0, 'count' => 0, 'description' => $description];
                }
                $accum['top_sources'][$source]['amount'] += $amount;
                $accum['top_sources'][$source]['count']++;
            }
        }
        
        // By month
        if ($monthKey) {
            if (!isset($accum['by_month'][$monthKey])) {
                $accum['by_month'][$monthKey] = initMonthAccum();
            }
            $accum['by_month'][$monthKey]['amount'] += $amount;
            $accum['by_month'][$monthKey]['fee'] += $fee;
            $accum['by_month'][$monthKey]['net'] += $net;
            $accum['by_month'][$monthKey]['revenue'] += $revenue;
            $accum['by_month'][$monthKey]['count']++;
            if (strtolower($type) === 'charge') {
                $accum['by_month'][$monthKey]['charge_count']++;
            }
        }
    }
}

// Fetch and aggregate one board
function fetchAndAggregate($apiUrl, $headers, $boardId, $country, $columnIdsStr) {
    $accums = [
        'stripe' => initAccum(),
        'yelp' => initAccum(),
        'sms' => initAccum()
    ];
    
    // Initial query with groups
    $query = 'query {
  boards (ids: ' . $boardId . ') {
    name
    groups {
      id
      title
      items_page (limit: 500) {
        cursor
        items {
          id
          name
          column_values (ids: ' . $columnIdsStr . ') {
            id
            text
          }
        }
      }
    }
  }
}';
    
    $result = callAPI($apiUrl, $headers, $query);
    if (isset($result['error'])) return ['error' => $result['error']];
    if (!isset($result['data']['boards'][0]['groups'])) return $accums;
    
    foreach ($result['data']['boards'][0]['groups'] as $group) {
        $groupTitle = $group['title'];
        $groupType = getGroupType($groupTitle);
        
        if ($groupType === 'unknown') continue;
        
        // Process initial items
        $items = $group['items_page']['items'] ?? [];
        processItems($accums[$groupType], $items, $groupTitle, $country, null);
        
        // Paginate with cursor
        $cursor = $group['items_page']['cursor'] ?? null;
        while ($cursor) {
            $nextQuery = 'query {
  next_items_page (limit: 500, cursor: "' . $cursor . '") {
    cursor
    items {
      id
      name
      column_values (ids: ' . $columnIdsStr . ') {
        id
        text
      }
    }
  }
}';
            $nextResult = callAPI($apiUrl, $headers, $nextQuery);
            if (isset($nextResult['error'])) break;
            
            $nextItems = $nextResult['data']['next_items_page']['items'] ?? [];
            processItems($accums[$groupType], $nextItems, $groupTitle, $country, null);
            
            $cursor = $nextResult['data']['next_items_page']['cursor'] ?? null;
        }
    }
    
    return $accums;
}

// ===== MAIN =====
date_default_timezone_set('Asia/Bangkok');
$startTime = microtime(true);

$revenue = [];
$errors = [];

foreach ($boardIds as $cc => $boardId) {
    $result = fetchAndAggregate($apiUrl, $headers, $boardId, $cc, $columnIdsStr);
    if (isset($result['error'])) {
        $errors[] = strtoupper($cc) . ': ' . $result['error'];
        continue;
    }
    
    // Store stripe by country
    $stripeKey = 'stripe_' . $cc;
    $stripeData = $result['stripe'];
    // Sort by_month
    ksort($stripeData['by_month']);
    // Remove top_sources from output (too large), keep top 20
    $topSources = $stripeData['top_sources'];
    arsort($topSources);
    $stripeData['top_charges'] = array_slice($topSources, 0, 20, true);
    unset($stripeData['top_sources']);
    $revenue[$stripeKey] = $stripeData;
    
    // SMS per country
    $smsData = $result['sms'];
    ksort($smsData['by_month']);
    unset($smsData['top_sources']);
    if ($smsData['count'] > 0) {
        $revenue['sms_' . $cc] = $smsData;
    }

    // Yelp only from US
    if ($cc === 'us') {
        $yelpData = $result['yelp'];
        ksort($yelpData['by_month']);
        unset($yelpData['top_sources']);
        $revenue['yelp'] = $yelpData;
    }
}

// Exchange rates to AUD (update periodically)
$toAUD = [
    'stripe_au' => 1.0,    // AUD
    'stripe_us' => 1.55,   // 1 USD = ~1.55 AUD
    'stripe_th' => 0.045,  // 1 THB = ~0.045 AUD
    'sms_au'    => 1.0,    // AUD
    'sms_us'    => 1.55,   // USD
    'sms_uk'    => 1.95,   // 1 GBP = ~1.95 AUD
    'yelp'      => 1.55,   // USD
];

// Calculate grand totals (original currencies mixed)
$grandTotal = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
// Grand total in AUD (converted)
$grandTotalAud = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
$allMonths = [];
foreach ($revenue as $key => $data) {
    $rate = $toAUD[$key] ?? 1.0;
    $grandTotal['amount'] += $data['amount'];
    $grandTotal['fee'] += $data['fee'];
    $grandTotal['net'] += $data['net'];
    $grandTotal['revenue'] += $data['revenue'];
    $grandTotal['count'] += $data['count'];
    $grandTotal['charge_count'] += $data['charge_count'];
    
    $grandTotalAud['amount'] += $data['amount'] * $rate;
    $grandTotalAud['fee'] += $data['fee'] * $rate;
    $grandTotalAud['net'] += $data['net'] * $rate;
    $grandTotalAud['revenue'] += $data['revenue'] * $rate;
    $grandTotalAud['count'] += $data['count'];
    $grandTotalAud['charge_count'] += $data['charge_count'];
    
    foreach ($data['by_month'] as $month => $mData) {
        if (!isset($allMonths[$month])) {
            $allMonths[$month] = initMonthAccum();
        }
        $allMonths[$month]['amount'] += $mData['amount'];
        $allMonths[$month]['fee'] += $mData['fee'];
        $allMonths[$month]['net'] += $mData['net'];
        $allMonths[$month]['revenue'] += $mData['revenue'];
        $allMonths[$month]['count'] += $mData['count'];
        $allMonths[$month]['charge_count'] += $mData['charge_count'];
    }
}
ksort($allMonths);

// MoM comparison
$currentMonth = date('Y-m');
$prevMonth = date('Y-m', strtotime('first day of last month'));
$mom = [
    'current_month' => $currentMonth,
    'previous_month' => $prevMonth,
    'current' => $allMonths[$currentMonth] ?? initMonthAccum(),
    'previous' => $allMonths[$prevMonth] ?? initMonthAccum(),
    'by_type' => []
];
foreach ($revenue as $key => $data) {
    $cur = $data['by_month'][$currentMonth] ?? initMonthAccum();
    $prev = $data['by_month'][$prevMonth] ?? initMonthAccum();
    $mom['by_type'][$key] = [
        'current' => $cur,
        'previous' => $prev,
        'change_pct' => $prev['amount'] > 0 ? round(($cur['amount'] - $prev['amount']) / $prev['amount'] * 100, 1) : null
    ];
}
$mom['change_pct'] = ($mom['previous']['amount'] > 0)
    ? round(($mom['current']['amount'] - $mom['previous']['amount']) / $mom['previous']['amount'] * 100, 1)
    : null;

// YoY comparison
$currentYear = date('Y');
$prevYear = (string)(intval($currentYear) - 1);
$currentYearMonths = array_filter($allMonths, function($k) use ($currentYear) { return strpos($k, $currentYear) === 0; }, ARRAY_FILTER_USE_KEY);
$prevYearMonths = array_filter($allMonths, function($k) use ($prevYear) { return strpos($k, $prevYear) === 0; }, ARRAY_FILTER_USE_KEY);

$yoyCurrent = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
$yoyPrevious = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
foreach ($currentYearMonths as $m) { foreach ($m as $k => $v) { $yoyCurrent[$k] += $v; } }
foreach ($prevYearMonths as $m) { foreach ($m as $k => $v) { $yoyPrevious[$k] += $v; } }

$yoy = [
    'current_year' => $currentYear,
    'previous_year' => $prevYear,
    'current' => $yoyCurrent,
    'previous' => $yoyPrevious,
    'change_pct' => $yoyPrevious['amount'] > 0 ? round(($yoyCurrent['amount'] - $yoyPrevious['amount']) / $yoyPrevious['amount'] * 100, 1) : null,
    'by_type' => []
];
foreach ($revenue as $key => $data) {
    $curYearSum = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
    $prevYearSum = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
    foreach ($data['by_month'] as $mk => $mv) {
        if (strpos($mk, $currentYear) === 0) { foreach ($mv as $k => $v) { $curYearSum[$k] += $v; } }
        if (strpos($mk, $prevYear) === 0) { foreach ($mv as $k => $v) { $prevYearSum[$k] += $v; } }
    }
    $yoy['by_type'][$key] = [
        'current' => $curYearSum,
        'previous' => $prevYearSum,
        'change_pct' => $prevYearSum['amount'] > 0 ? round(($curYearSum['amount'] - $prevYearSum['amount']) / $prevYearSum['amount'] * 100, 1) : null
    ];
}

// Top charges across all stripe accounts
$topCharges = [];
foreach ($revenue as $key => $data) {
    if (strpos($key, 'stripe_') === 0 && isset($data['top_charges'])) {
        foreach ($data['top_charges'] as $src => $info) {
            $topCharges[] = [
                'source' => $src,
                'account' => $key,
                'amount' => round($info['amount'], 2),
                'count' => $info['count'],
                'description' => $info['description']
            ];
        }
    }
}
usort($topCharges, function($a, $b) { return $b['amount'] <=> $a['amount']; });
$topCharges = array_slice($topCharges, 0, 30);

// Clean up revenue data (round numbers)
foreach ($revenue as $key => &$data) {
    $data['amount'] = round($data['amount'], 2);
    $data['fee'] = round($data['fee'], 2);
    $data['net'] = round($data['net'], 2);
    $data['revenue'] = round($data['revenue'], 2);
    foreach ($data['by_month'] as &$m) {
        $m['amount'] = round($m['amount'], 2);
        $m['fee'] = round($m['fee'], 2);
        $m['net'] = round($m['net'], 2);
        $m['revenue'] = round($m['revenue'], 2);
    }
    unset($data['top_charges']); // Already processed into topCharges
}
unset($data, $m);

$elapsed = round(microtime(true) - $startTime, 1);

$output = [
    'fetched_at' => date('Y-m-d H:i:s'),
    'elapsed_seconds' => $elapsed,
    'revenue' => $revenue,
    'grand_total' => [
        'amount' => round($grandTotal['amount'], 2),
        'fee' => round($grandTotal['fee'], 2),
        'net' => round($grandTotal['net'], 2),
        'revenue' => round($grandTotal['revenue'], 2),
        'count' => $grandTotal['count'],
        'charge_count' => $grandTotal['charge_count']
    ],
    'grand_total_aud' => [
        'amount' => round($grandTotalAud['amount'], 2),
        'fee' => round($grandTotalAud['fee'], 2),
        'net' => round($grandTotalAud['net'], 2),
        'revenue' => round($grandTotalAud['revenue'], 2),
        'count' => $grandTotalAud['count'],
        'charge_count' => $grandTotalAud['charge_count']
    ],
    'exchange_rates_to_aud' => $toAUD,
    'all_months' => $allMonths,
    'mom' => $mom,
    'yoy' => $yoy,
    'top_charges' => $topCharges
];

if (!empty($errors)) {
    $output['errors'] = $errors;
}

// Save to cache
$output['source'] = 'fresh';
file_put_contents($cacheFile, json_encode($output, JSON_UNESCAPED_UNICODE));

echo json_encode($output, JSON_UNESCAPED_UNICODE);
