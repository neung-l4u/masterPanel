<?php
// 1. Basic configuration
$url = "https://api.monday.com/v2";
$token = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM"; // API Token from Developer Center

// Board ID per country
$countryBoards = [
    'TH' => '1943203205',
    'CA' => '1943203287',
    'UK' => '1943203305',
    'US' => '1940392927',
    'NZ' => '1943203264',
    'AU' => '1943203246'
];

// Main Board ID (all countries in one board)
$mainBoardId = '1881439330';

// Get country filter from query string (e.g. ?country=AU or ?country=AU,NZ,UK,US,CA,TH)
$countryFilter = !empty($_GET['country']) ? array_map('strtoupper', array_map('trim', explode(',', $_GET['country']))) : [];

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

// Function to fetch all items with pagination (per-group)
function fetchAllItems($url, $headers, $boardIds) {
    $allItems = [];
    
    // Build board IDs string (e.g. [123,456,...])
    $idsString = implode(',', $boardIds);
    
    // Step 1: Get all groups with first page of items
    $query = 'query {
  boards (ids: [' . $idsString . ']) {
    name
    id
    groups {
      id
      title
      items_page (limit: 500) {
        cursor
        items {
          id
          name
          column_values (ids: ["color1", "primary_contact", "contact_mobile", "country2"]) {
            id
            text
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
    
    if (!isset($result['data']['boards'])) {
        return ['items' => [], 'boards' => []];
    }
    
    // Step 2: Collect items from each board/group and track cursors
    $groupCursors = [];
    $boardsData = $result['data']['boards'];
    
    foreach ($boardsData as $board) {
        $boardName = $board['name'];
        
        foreach ($board['groups'] as $group) {
            $groupTitle = $group['title'];
            
            foreach ($group['items_page']['items'] as $item) {
                $item['group_title'] = $groupTitle;
                $item['board_name'] = $boardName;
                $allItems[] = $item;
            }
            
            if (!empty($group['items_page']['cursor'])) {
                $groupCursors[] = [
                    'title' => $groupTitle,
                    'board_name' => $boardName,
                    'cursor' => $group['items_page']['cursor']
                ];
            }
        }
    }
    
    // Step 3: Paginate remaining items for each group that has a cursor
    foreach ($groupCursors as $groupInfo) {
        $cursor = $groupInfo['cursor'];
        $groupTitle = $groupInfo['title'];
        $boardName = $groupInfo['board_name'];
        
        while ($cursor) {
            $nextQuery = 'query {
  next_items_page (limit: 500, cursor: "' . $cursor . '") {
    cursor
    items {
      id
      name
      column_values (ids: ["color1", "primary_contact", "contact_mobile", "country2"]) {
        id
        text
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
                    $item['board_name'] = $boardName;
                    $allItems[] = $item;
                }
                $cursor = $nextResult['data']['next_items_page']['cursor'];
            } else {
                $cursor = null;
            }
        }
    }
    
    return ['items' => $allItems, 'boards' => $boardsData];
}


// 2. Set Headers
$headers = [
    "Content-Type: application/json",
    "Authorization: " . $token
];

// 3. Determine which board IDs to fetch
if (!empty($countryFilter)) {
    // Fetch only selected country boards
    $boardIds = [];
    foreach ($countryFilter as $c) {
        if (isset($countryBoards[$c])) {
            $boardIds[] = $countryBoards[$c];
        }
    }
    if (empty($boardIds)) {
        die("Error: No valid country codes provided. Valid codes: " . implode(', ', array_keys($countryBoards)));
    }
} else {
    // No filter: fetch from main board (all countries)
    $boardIds = [$mainBoardId];
}

$result = fetchAllItems($url, $headers, $boardIds);

// 4. Validate and save to file
date_default_timezone_set('Asia/Bangkok');
$timestamp = date('ymd-Hi'); // format: 260205-1126 (Bangkok time)

if (isset($result['error'])) {
    echo "Error: " . $result['error'];
} else {
    if (!empty($countryFilter)) {
        // Save per-country files
        foreach ($countryFilter as $filterCountry) {
            $countryBoardId = isset($countryBoards[$filterCountry]) ? $countryBoards[$filterCountry] : null;
            if (!$countryBoardId) continue;
            
            // Filter items belonging to this country's board
            $filteredItems = [];
            foreach ($result['items'] as $item) {
                // Match by board_name containing country code
                if (isset($item['board_name'])) {
                    $bn = $item['board_name'];
                    $matchCountry = null;
                    if (strpos($bn, '| TH') !== false) $matchCountry = 'TH';
                    elseif (strpos($bn, '| CA') !== false) $matchCountry = 'CA';
                    elseif (strpos($bn, '| UK') !== false) $matchCountry = 'UK';
                    elseif (strpos($bn, '| USA') !== false || strpos($bn, '| US') !== false) $matchCountry = 'US';
                    elseif (strpos($bn, '| NZ') !== false) $matchCountry = 'NZ';
                    elseif (strpos($bn, '| AU') !== false) $matchCountry = 'AU';
                    
                    if ($matchCountry === $filterCountry) {
                        $filteredItems[] = $item;
                    }
                }
            }
            
            $fileDir = __DIR__ . '/file/' . $filterCountry;
            $filePath = $fileDir . '/' . $filterCountry . '_monday_data' . $timestamp . '.json';
            
            if (!is_dir($fileDir)) {
                mkdir($fileDir, 0755, true);
            }
            
            $output = [
                'data' => [
                    'items' => $filteredItems,
                    'total_count' => count($filteredItems),
                    'currency_mapping' => $currencyMapping,
                    'country' => $filterCountry
                ]
            ];
            
            file_put_contents($filePath, json_encode($output, JSON_UNESCAPED_UNICODE));
            echo "Saved " . count($filteredItems) . " items for $filterCountry\n";
        }
    } else {
        // No filter: save all items to file/ALL/
        $fileDir = __DIR__ . '/file/ALL';
        $filePath = $fileDir . '/ALL_monday_data' . $timestamp . '.json';
        
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }
        
        $output = [
            'data' => [
                'items' => $result['items'],
                'total_count' => count($result['items']),
                'currency_mapping' => $currencyMapping
            ]
        ];
        
        file_put_contents($filePath, json_encode($output, JSON_UNESCAPED_UNICODE));
        echo "Saved " . count($result['items']) . " items for ALL countries\n";
    }
}
?>