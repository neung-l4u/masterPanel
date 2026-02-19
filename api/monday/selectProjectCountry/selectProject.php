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




// Fetch all items from a SINGLE board with pagination
function fetchSingleBoard($url, $headers, $boardId) {
    $allItems = [];
    
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
    
    if (!isset($result['data']['boards'][0])) {
        return ['items' => [], 'board' => null];
    }
    
    $board = $result['data']['boards'][0];
    $boardName = $board['name'];
    $groupCursors = [];
    
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
    
    return ['items' => $allItems, 'board' => $board];
}

// Fetch subscription status from main board (1881439330) with pagination
function fetchSubscriptionBoard($url, $headers, $boardId) {
    $allItems = [];

    $query = 'query {
  boards (ids: [' . $boardId . ']) {
    name
    groups {
      id
      title
      items_page (limit: 500) {
        cursor
        items {
          id
          name
          column_values (ids: ["status", "lookup_mkwh1gcr", "text9", "creation_log", "status0", "date", "mirror"]) {
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
    if (isset($result['error'])) return $result;
    if (!isset($result['data']['boards'][0])) return ['items' => []];

    $board = $result['data']['boards'][0];
    $groupCursors = [];

    foreach ($board['groups'] as $group) {
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
      column_values (ids: ["status", "lookup_mkwh1gcr", "text9", "creation_log", "status0", "date", "mirror"]) {
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
            if (isset($nextResult['error'])) return $nextResult;
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


// Fetch multiple boards ONE AT A TIME and merge results
function fetchAllBoards($url, $headers, $boardIds) {
    $allItems = [];
    $allBoards = [];
    
    foreach ($boardIds as $boardId) {
        $result = fetchSingleBoard($url, $headers, $boardId);
        
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

$result = fetchAllBoards($url, $headers, $boardIds);

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

        // country2 text => country code
        $country2ToCode = [
            'australia'      => 'AU',
            'canada'         => 'CA',
            'new zealand'    => 'NZ',
            'thailand'       => 'TH',
            'united kingdom' => 'UK',
            'united states'  => 'US',
        ];

        // currency (status0) => country code
        $currencyToCountry2 = [
            'thb' => 'TH',
            'aud' => 'AU',
            'nzd' => 'NZ',
            'gbp' => 'UK',
            'usd' => 'US',
            'cad' => 'CA'
        ];

        // Normalize shop name: strip country suffix + collapse spaces
        $normalizeShop2 = function($s) {
            $s = preg_replace('/\s*-\s*(TH|CA|UK|US|USA|NZ|AU)(\s*\(.*?\))?\s*$/i', '', $s);
            return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
        };

        // Fetch subscription data once for all countries
        $subResult2 = fetchSubscriptionBoard($url, $headers, $mainBoardId);
        $subByCountryName2 = [];
        $subByNameOnly2    = [];

        if (!isset($subResult2['error'])) {
            $activeStatuses2 = ['active', 'active subscription', 'request cancellation'];
            foreach ($subResult2['items'] as $subItem) {
                $statusText  = null;
                $shopName    = null;
                $currency    = null;
                $productName = $subItem['name'];
                foreach ($subItem['column_values'] as $cv) {
                    if ($cv['id'] === 'status')  { $statusText = $cv['text'] ?? null; }
                    if ($cv['id'] === 'text9')   { $shopName   = $cv['text'] ?? null; }
                    if ($cv['id'] === 'status0') { $currency   = $cv['text'] ?? null; }
                }
                if (empty($statusText) || !in_array(strtolower(trim($statusText)), $activeStatuses2)) continue;
                if (empty($shopName)) continue;

                $shopKey = $normalizeShop2($shopName);
                $country = $currency ? ($currencyToCountry2[strtolower(trim($currency))] ?? null) : null;
                $entry   = ['monday_item_id' => $subItem['id'], 'text' => $productName, 'status' => $statusText];

                if ($country) {
                    $subByCountryName2[$country][$shopKey][] = $entry;
                }
                $subByNameOnly2[$shopKey][] = $entry;
            }
        }

        $allOutput = [];
        foreach ($countryFilter as $filterCountry) {
            $countryBoardId = isset($countryBoards[$filterCountry]) ? $countryBoards[$filterCountry] : null;
            if (!$countryBoardId) continue;

            $filteredItems = [];
            foreach ($result['items'] as $item) {
                // Detect country from country2 column value
                $itemCountry = null;
                foreach ($item['column_values'] as $cv) {
                    if ($cv['id'] === 'country2' && !empty($cv['text'])) {
                        $itemCountry = $country2ToCode[strtolower(trim($cv['text']))] ?? null;
                        break;
                    }
                }
                // Fallback: detect from board_name
                if (!$itemCountry) {
                    $bn = $item['board_name'] ?? '';
                    if (strpos($bn, '| TH') !== false) $itemCountry = 'TH';
                    elseif (strpos($bn, '| CA') !== false) $itemCountry = 'CA';
                    elseif (strpos($bn, '| UK') !== false) $itemCountry = 'UK';
                    elseif (strpos($bn, '| USA') !== false || strpos($bn, '| US') !== false) $itemCountry = 'US';
                    elseif (strpos($bn, '| NZ') !== false) $itemCountry = 'NZ';
                    elseif (strpos($bn, '| AU') !== false) $itemCountry = 'AU';
                }

                if ($itemCountry !== $filterCountry) continue;

                // Merge subscription data
                $projectKey = $normalizeShop2($item['name']);
                $entries    = [];
                if ($itemCountry && isset($subByCountryName2[$itemCountry][$projectKey])) {
                    $entries = $subByCountryName2[$itemCountry][$projectKey];
                } elseif (isset($subByNameOnly2[$projectKey])) {
                    $entries = $subByNameOnly2[$projectKey];
                }

                $numbered = [];
                foreach ($entries as $idx => $e) {
                    $numbered[] = [
                        'id'             => 'product' . ($idx + 1),
                        'text'           => $e['text'],
                        'status'         => $e['status'],
                        'monday_item_id' => $e['monday_item_id']
                    ];
                }
                $item['column_active_subSubscription'] = $numbered;
                $filteredItems[] = $item;
            }

            $allOutput[$filterCountry] = [
                'items'       => $filteredItems,
                'total_count' => count($filteredItems)
            ];
        }

        echo json_encode([
            'data' => $allOutput,
            'currency_mapping' => $currencyMapping
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // No filter / ALL: save to file
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
        $savedCount = count($result['items']);

        // ========== Query 2: Fetch subscription status from main board ==========
        $subResult = fetchSubscriptionBoard($url, $headers, $mainBoardId);

        if (isset($subResult['error'])) {
            echo "Saved $savedCount items to $filePath\n";
            echo "Warning: Subscription query failed: " . $subResult['error'] . "\n";
        } else {
            $activeStatuses = ['active', 'active subscription', 'request cancellation'];

            // currency (status0) => country code
            $currencyToCountry = [
                'thb' => 'TH',
                'aud' => 'AU',
                'nzd' => 'NZ',
                'gbp' => 'UK',
                'usd' => 'US',
                'cad' => 'CA'
            ];

            // board_name suffix => country code (for project items)
            $boardNameToCountry = [
                '| TH'  => 'TH',
                '| CA'  => 'CA',
                '| UK'  => 'UK',
                '| USA' => 'US',
                '| US'  => 'US',
                '| NZ'  => 'NZ',
                '| AU'  => 'AU',
            ];

            // Normalize shop name: strip country suffix (- AU, - UK, etc.) + collapse spaces
            $normalizeShop = function($s) {
                $s = preg_replace('/\s*-\s*(TH|CA|UK|US|USA|NZ|AU)(\s*\(.*?\))?\s*$/i', '', $s);
                return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
            };

            // Build lookup:
            // $subByCountryName[country][normalizedShopName] => [entries]
            // $subByNameOnly[normalizedShopName]             => [entries]  (fallback)
            $subByCountryName = [];
            $subByNameOnly    = [];

            foreach ($subResult['items'] as $subItem) {
                $statusText  = null;
                $shopName    = null;  // from text9
                $currency    = null;  // from status0
                $productName = $subItem['name']; // subscription product name

                foreach ($subItem['column_values'] as $cv) {
                    if ($cv['id'] === 'status')  { $statusText = $cv['text'] ?? null; }
                    if ($cv['id'] === 'text9')   { $shopName   = $cv['text'] ?? null; }
                    if ($cv['id'] === 'status0') { $currency   = $cv['text'] ?? null; }
                }

                if (empty($statusText)) continue;
                if (!in_array(strtolower(trim($statusText)), $activeStatuses)) continue;
                if (empty($shopName)) continue;

                $shopKey = $normalizeShop($shopName);
                $country = isset($currency) ? ($currencyToCountry[strtolower(trim($currency))] ?? null) : null;

                $entry = [
                    'monday_item_id' => $subItem['id'],
                    'text'           => $productName,
                    'status'         => $statusText
                ];

                // Index by country + shop name
                if ($country) {
                    if (!isset($subByCountryName[$country][$shopKey])) {
                        $subByCountryName[$country][$shopKey] = [];
                    }
                    $subByCountryName[$country][$shopKey][] = $entry;
                }

                // Index by shop name only (fallback)
                if (!isset($subByNameOnly[$shopKey])) {
                    $subByNameOnly[$shopKey] = [];
                }
                $subByNameOnly[$shopKey][] = $entry;
            }

            // Helper: get country code from project item's board_name
            $getCountryFromBoard = function($boardName) use ($boardNameToCountry) {
                foreach ($boardNameToCountry as $suffix => $code) {
                    if (strpos($boardName, $suffix) !== false) return $code;
                }
                return null;
            };

            // Merge into saved JSON
            $savedJson = json_decode(file_get_contents($filePath), true);
            $mergedCount = 0;

            foreach ($savedJson['data']['items'] as &$projectItem) {
                $projectCountry = $getCountryFromBoard($projectItem['board_name'] ?? '');
                $projectKey     = $normalizeShop($projectItem['name']);

                // Strategy 1: match by country (from board_name) + shop name (text9)
                $entries = [];
                if ($projectCountry && isset($subByCountryName[$projectCountry][$projectKey])) {
                    $entries = $subByCountryName[$projectCountry][$projectKey];
                }

                // Strategy 2: fallback — shop name only (no country filter)
                if (empty($entries) && isset($subByNameOnly[$projectKey])) {
                    $entries = $subByNameOnly[$projectKey];
                }

                // Build numbered product list
                if (!empty($entries)) {
                    $numbered = [];
                    foreach ($entries as $idx => $e) {
                        $numbered[] = [
                            'id'             => 'product' . ($idx + 1),
                            'text'           => $e['text'],
                            'status'         => $e['status'],
                            'monday_item_id' => $e['monday_item_id']
                        ];
                    }
                    $projectItem['column_active_subSubscription'] = $numbered;
                    $mergedCount++;
                } else {
                    $projectItem['column_active_subSubscription'] = [];
                }
            }
            unset($projectItem);

            // Save merged JSON back to same file
            file_put_contents($filePath, json_encode($savedJson, JSON_UNESCAPED_UNICODE));
            echo "Saved $savedCount project items to $filePath\n";
            echo "Merged subscription data into $mergedCount shops.\n";
        }
    }
}
?>