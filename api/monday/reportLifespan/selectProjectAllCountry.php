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

// Life Span extra column IDs per country
$lifeSpanColumns = [
    'AU' => ['live_date', 'date_mm26gw5p', 'date_mkzs3896', 'date_mm26kg00', 'connect_boards06'],
    'TH' => ['live_date', 'date_mm267zrn', 'date_mkzsde4j', 'date_mm26989b', 'connect_boards06'],
    'CA' => ['live_date', 'date_mm26scn7', 'date_mkzs7czr', 'date_mm26zjma', 'connect_boards06'],
    'UK' => ['live_date', 'date_mm26rep1', 'date_mkzswq7q', 'date_mm26d16s', 'connect_boards06'],
    'US' => ['live_date', 'date_mm26ntpg', 'date_mkzs790g', 'date_mm26jsve', 'connect_boards06'],
    'NZ' => ['live_date', 'date_mm26gdzw', 'date_mkzs82rp', 'date_mm26tp5b', 'connect_boards06'],
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

// Increase limits for large data
ini_set('memory_limit', '512M');
set_time_limit(300);

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

// Resolve country code from board ID
function resolveCountryFromBoardId($boardId, $countryBoards) {
    foreach ($countryBoards as $code => $id) {
        if ($id === $boardId) return $code;
    }
    return null;
}

// Build column IDs string for a given country (base + life span columns)
function buildColumnIds($country, $lifeSpanColumns) {
    $baseColumns = ["color1", "primary_contact", "contact_mobile", "country2", "creation_log"];
    if (isset($lifeSpanColumns[$country])) {
        $baseColumns = array_merge($baseColumns, $lifeSpanColumns[$country]);
        $baseColumns = array_unique($baseColumns);
    }
    return '"' . implode('", "', $baseColumns) . '"';
}

// Fetch all items from a SINGLE board with pagination
function fetchSingleBoard($url, $headers, $boardId, $country, $lifeSpanColumns) {
    $allItems = [];
    $columnIds = buildColumnIds($country, $lifeSpanColumns);
    
    $query = 'query {
  boards (ids: [' . $boardId . ']) {
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
          column_values (ids: [' . $columnIds . ']) {
            id
            text
            ... on BoardRelationValue {
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
    
    if (!isset($result['data']['boards'][0])) {
        return ['items' => [], 'board' => null];
    }
    
    $board = $result['data']['boards'][0];
    $boardName = $board['name'];
    $groupCursors = [];
    
    // Normalize board relation columns: copy display_value into text when text is empty
    $normalizeItems = function(&$items) {
        foreach ($items as &$item) {
            if (isset($item['column_values'])) {
                foreach ($item['column_values'] as &$col) {
                    if (empty($col['text']) && !empty($col['display_value'])) {
                        $col['text'] = $col['display_value'];
                    }
                }
            }
        }
    };
    
    foreach ($board['groups'] as $group) {
        $groupTitle = $group['title'];
        
        $groupItems = $group['items_page']['items'];
        $normalizeItems($groupItems);
        foreach ($groupItems as $item) {
            $item['group_title'] = $groupTitle;
            $item['board_name'] = $boardName;
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
      column_values (ids: [' . $columnIds . ']) {
        id
        text
        ... on BoardRelationValue {
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
                $nextItems = $nextResult['data']['next_items_page']['items'];
                $normalizeItems($nextItems);
                foreach ($nextItems as $item) {
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
    
    return ['items' => $allItems, 'board' => $board];
}

// Fetch multiple boards ONE AT A TIME and merge results
function fetchAllBoards($url, $headers, $boardIds, $countryBoards, $lifeSpanColumns) {
    $allItems = [];
    $allBoards = [];
    
    foreach ($boardIds as $boardId) {
        $country = resolveCountryFromBoardId($boardId, $countryBoards);
        $result = fetchSingleBoard($url, $headers, $boardId, $country, $lifeSpanColumns);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        $allItems = array_merge($allItems, $result['items']);
        if ($result['board']) {
            $allBoards[] = $result['board'];
        }
    }
    
    return ['items' => $allItems, 'boards' => $allBoards];
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

// 3. Determine which board IDs to fetch
if (!empty($countryFilter) && !(count($countryFilter) === 1 && $countryFilter[0] === 'ALL')) {
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
    // No filter or ?country=ALL: fetch all country boards
    $boardIds = array_values($countryBoards);
    $countryFilter = []; // Reset so output goes to ALL path
}

$result = fetchAllBoards($url, $headers, $boardIds, $countryBoards, $lifeSpanColumns);

// 4. Validate and output
date_default_timezone_set('Asia/Bangkok');
$timestamp = date('ymd-Hi');

if (isset($result['error'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $result['error']], JSON_UNESCAPED_UNICODE);
} else {
    if (!empty($countryFilter)) {
        // Per-country filter: output JSON to browser
        header('Content-Type: application/json; charset=utf-8');
        
        $allOutput = [];
        foreach ($countryFilter as $filterCountry) {
            $countryBoardId = isset($countryBoards[$filterCountry]) ? $countryBoards[$filterCountry] : null;
            if (!$countryBoardId) continue;
            
            $filteredItems = [];
            foreach ($result['items'] as $item) {
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
            
            $allOutput[$filterCountry] = [
                'items' => $filteredItems,
                'total_count' => count($filteredItems)
            ];
        }
        
        echo json_encode([
            'data' => $allOutput,
            'currency_mapping' => $currencyMapping
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // No filter / ALL: save to file
        $fileDir = __DIR__ . '/file/ALL_COUNTRY';
        $filePath = $fileDir . '/ALL_monday_data_ALL_COUNTRY' . $timestamp . '.json';
        
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
        echo "Saved " . count($result['items']) . " items for ALL countries to $filePath\n";
    }
}
?>
