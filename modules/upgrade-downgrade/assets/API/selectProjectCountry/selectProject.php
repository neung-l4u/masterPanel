<?php
// 1. Basic configuration
$url = "https://api.monday.com/v2";
$token = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM"; // API Token from Developer Center

// Main Board ID (all countries)
$boardId = '1881439330';

// Currency to Country mapping
$currencyMapping = [
    'THB' => 'TH',
    'AUD' => 'AU',
    'NZD' => 'NZ',
    'GBP' => 'UK',
    'USD' => 'US',
    'CAD' => 'CA'
];

// Helper function to make API call
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
    
    if ($error) {
        return ['error' => $error];
    }
    
    return json_decode($response, true);
}

// Fetch all items from a SINGLE board with pagination
function fetchSingleBoard($url, $headers, $boardId) {
    $allItems = [];
    
    // Step 1: Get all groups with first page of items
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
          column_values (ids: ["status", "lookup_mkwh1gcr", "text9", "creation_log", "status0"]) {
            id
            text
            ... on MirrorValue {
              display_value
            }
          }
        }
      }
    }
  }
}';
    
    $result = callMondayAPI($url, $headers, $query);
    
    if (isset($result['error'])) {
        return $result;
    }
    
    if (!isset($result['data']['boards'][0]['groups'])) {
        return ['items' => []];
    }
    
    // Step 2: Collect items from each group and track cursors
    $groupCursors = [];
    
    foreach ($result['data']['boards'][0]['groups'] as $group) {
        $groupTitle = $group['title'];
        
        foreach ($group['items_page']['items'] as $item) {
            $item['group_title'] = $groupTitle;
            $allItems[] = $item;
        }
        
        if (!empty($group['items_page']['cursor'])) {
            $groupCursors[] = [
                'title' => $groupTitle,
                'cursor' => $group['items_page']['cursor']
            ];
        }
    }
    
    // Step 3: Paginate remaining items for each group that has a cursor
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
      column_values (ids: ["status", "lookup_mkwh1gcr", "text9", "creation_log", "status0"]) {
        id
        text
        ... on MirrorValue {
          display_value
        }
      }
    }
  }
}';
            
            $nextResult = callMondayAPI($url, $headers, $nextQuery);
            
            if (isset($nextResult['error'])) {
                return $nextResult;
            }
            
            if (isset($nextResult['data']['next_items_page'])) {
                foreach ($nextResult['data']['next_items_page']['items'] as $item) {
                    $item['group_title'] = $groupTitle;
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

// 2. Set Headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// 2. Set Headers
$headers = [
    "Content-Type: application/json",
    "Authorization: " . $token
];

// 3. Fetch all items with pagination
$result = fetchSingleBoard($url, $headers, $boardId);

// 4. Validate and save to file
date_default_timezone_set('Asia/Bangkok');
$timestamp = date('ymd-Hi'); // format: 260205-1126 (Bangkok time)
$fileDir = __DIR__ . '/file/ALL';
$filePath = $fileDir . '/ALL_monday_data' . $timestamp . '.json';

// Create folder if not exists
if (!is_dir($fileDir)) {
    mkdir($fileDir, 0755, true);
}

if (isset($result['error'])) {
    echo "Error: " . $result['error'];
} else {
    // Restructure output data
    $output = [
        'data' => [
            'items' => $result['items'],
            'total_count' => count($result['items']),
            'currency_mapping' => $currencyMapping
        ]
    ];
    
    // Save file
    file_put_contents($filePath, json_encode($output, JSON_UNESCAPED_UNICODE));
}
?>