<?php
/**
 * Revenue Data API — fetches from Monday.com boards
 * Usage: selectData.php?type=stripe|yelp|sms&country=au|us|th (country optional, defaults to all)
 * 
 * Group filtering:
 *   stripe → groups containing "payout" or "transaction" (AU, US, TH)
 *   yelp   → groups containing "Report_Yelp_Ads" (US only)
 *   sms    → groups containing "SMS_Marketing" (US only)
 */
ini_set('memory_limit', '512M');
set_time_limit(300);
header('Content-Type: application/json');

$url = "https://api.monday.com/v2";
$token = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";

$boardIds = [
    'au' => '5026435242',
    'us' => '5026427192',
    'th' => '5026435384'
];

// Common column IDs across all boards
$columnIds = [
    "text_mm0832vx",   // id
    "text_mm08ka8n",   // Type
    "text_mm08qfz7",   // Source
    "text_mm086z4k",   // Amount
    "text_mm08p503",   // Fee
    "text_mm08ab90",   // Net
    "text_mm08xpqq",   // Currency
    "date_mm086vct",   // Created (UTC)
    "date_mm08h2s1",   // Available On (UTC)
    "text_mm08js6t",   // Description
    "text_mm082qz9",   // Revenue (text)
];

$columnIdsStr = '["' . implode('","', $columnIds) . '"]';

// Parse params
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'stripe';
$country = isset($_GET['country']) ? strtolower(trim($_GET['country'])) : 'all';
$saveCache = isset($_GET['save']) && $_GET['save'] === '1';

if (!in_array($type, ['stripe', 'yelp', 'sms'])) {
    echo json_encode(['error' => 'Invalid type. Use: stripe, yelp, sms']);
    exit;
}

// Determine which boards to query
$boardsToQuery = [];
if ($country === 'all') {
    if ($type === 'stripe') {
        $boardsToQuery = ['au', 'us', 'th'];
    } elseif ($type === 'yelp' || $type === 'sms') {
        $boardsToQuery = ['us']; // Yelp and SMS only on US board
    }
} else {
    if (!isset($boardIds[$country])) {
        echo json_encode(['error' => 'Invalid country. Use: au, us, th']);
        exit;
    }
    $boardsToQuery = [$country];
}

$headers = [
    "Content-Type: application/json",
    "Authorization: " . $token
];

// Helper: make Monday.com API call
function callMondayAPI($url, $headers, $query) {
    $data = json_encode(['query' => $query]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) return ['error' => $error];
    return json_decode($response, true);
}

// Helper: check if group title matches the type filter
function groupMatchesType($groupTitle, $type) {
    $title = strtolower($groupTitle);
    switch ($type) {
        case 'stripe':
            return (strpos($title, 'payout') !== false || strpos($title, 'transaction') !== false)
                && strpos($title, 'yelp') === false
                && strpos($title, 'sms') === false;
        case 'yelp':
            return strpos($title, 'report_yelp_ads') !== false;
        case 'sms':
            return strpos($title, 'sms_marketing') !== false;
        default:
            return false;
    }
}

// Fetch items from a single board with pagination, filtered by group type
function fetchBoardItems($url, $headers, $boardId, $type, $countryCode, $columnIdsStr) {
    $allItems = [];

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

    $result = callMondayAPI($url, $headers, $query);

    if (isset($result['error'])) return $result;
    if (!isset($result['data']['boards'][0]['groups'])) return ['items' => []];

    $groupCursors = [];

    foreach ($result['data']['boards'][0]['groups'] as $group) {
        $groupTitle = $group['title'];

        // Filter groups by type
        if (!groupMatchesType($groupTitle, $type)) continue;

        foreach ($group['items_page']['items'] as $item) {
            $item['group_title'] = $groupTitle;
            $item['country'] = strtoupper($countryCode);
            $allItems[] = $item;
        }

        if (!empty($group['items_page']['cursor'])) {
            $groupCursors[] = [
                'title' => $groupTitle,
                'cursor' => $group['items_page']['cursor']
            ];
        }
    }

    // Paginate remaining items
    foreach ($groupCursors as $groupInfo) {
        $cursor = $groupInfo['cursor'];
        $groupTitle = $groupInfo['title'];

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
            $nextResult = callMondayAPI($url, $headers, $nextQuery);
            if (isset($nextResult['error'])) return $nextResult;

            if (isset($nextResult['data']['next_items_page'])) {
                foreach ($nextResult['data']['next_items_page']['items'] as $item) {
                    $item['group_title'] = $groupTitle;
                    $item['country'] = strtoupper($countryCode);
                    $allItems[] = $item;
                }
                $cursor = $nextResult['data']['next_items_page']['cursor'];
            } else {
                $cursor = null;
            }
        }
    }

    return ['items' => $allItems];
}

// Main: fetch from all required boards
$allItems = [];
$errors = [];

foreach ($boardsToQuery as $cc) {
    $result = fetchBoardItems($url, $headers, $boardIds[$cc], $type, $cc, $columnIdsStr);
    if (isset($result['error'])) {
        $errors[] = $cc . ': ' . $result['error'];
    } else {
        $allItems = array_merge($allItems, $result['items']);
    }
}

date_default_timezone_set('Asia/Bangkok');

// Build output
$output = [
    'type' => $type,
    'country' => $country === 'all' ? 'ALL' : strtoupper($country),
    'boards_queried' => array_map('strtoupper', $boardsToQuery),
    'fetched_at' => date('Y-m-d H:i:s'),
    'total_count' => count($allItems),
    'items' => $allItems,
];

if (!empty($errors)) {
    $output['errors'] = $errors;
}

// Save cache file if requested
if ($saveCache) {
    $timestamp = date('ymd-Hi');
    $cacheDir = __DIR__ . '/cache/' . $type;
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . $type . '_' . ($country === 'all' ? 'ALL' : strtoupper($country)) . '_' . $timestamp . '.json';
    file_put_contents($cacheFile, json_encode($output, JSON_UNESCAPED_UNICODE));
    $output['cache_file'] = basename($cacheFile);
}

// Return JSON
echo json_encode($output, JSON_UNESCAPED_UNICODE);
