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

// Helper function to make API call (single)
function callMondayAPI($url, $headers, $query) {
    $batched = callMondayAPIBatch($url, $headers, ['_' => $query]);
    return $batched['_'];
}

// Helper to fire multiple GraphQL queries in parallel via curl_multi.
// $queries is an associative array: key => queryString.
// Returns an associative array: key => decoded response (or ['error' => msg]).
// Concurrency is capped to avoid Monday API rate / complexity limits.
function callMondayAPIBatch($url, $headers, $queries, $maxConcurrent = 10) {
    if (empty($queries)) return [];

    $results = [];
    $chunks = array_chunk($queries, $maxConcurrent, true);

    foreach ($chunks as $chunk) {
        $mh = curl_multi_init();
        $handles = [];

        foreach ($chunk as $key => $query) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = $ch;
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running > 0) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running > 0 && $status === CURLM_OK);

        foreach ($handles as $key => $ch) {
            $err = curl_error($ch);
            if ($err) {
                $results[$key] = ['error' => $err];
            } else {
                $body = curl_multi_getcontent($ch);
                $results[$key] = json_decode($body, true);
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
    }

    return $results;
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

// Fetch all items from a SINGLE board.
// Strategy: split work into per-group queries and run them in parallel via curl_multi.
//   1) Lightweight metadata query (board name + group list) -- 1 request
//   2) First page of every group -- N requests in parallel (chunked)
//   3) Round-by-round parallel pagination until all cursors are exhausted
function fetchSingleBoard($url, $headers, $boardId, $country, $lifeSpanColumns) {
    $allItems = [];
    $columnIds = buildColumnIds($country, $lifeSpanColumns);

    // Helper: normalize BoardRelation text from display_value when text is empty
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

    // Helper: build the items_page selection set (shared by first-page and next-page queries)
    $itemsSelection = 'items {
      id
      name
      column_values (ids: [' . $columnIds . ']) {
        id
        text
        ... on BoardRelationValue {
          display_value
        }
      }
    }';

    // ---- Step 1: lightweight metadata query (board name + group ids/titles only) ----
    $metaQuery = 'query { boards(ids: [' . $boardId . ']) { id name groups { id title } } }';
    $meta = callMondayAPI($url, $headers, $metaQuery);

    if (isset($meta['error']))  return $meta;
    if (isset($meta['errors'])) return ['error' => 'Monday GraphQL: ' . json_encode($meta['errors'])];
    if (!isset($meta['data']['boards'][0])) {
        return ['items' => [], 'board' => null];
    }

    $board = $meta['data']['boards'][0];
    $boardName = $board['name'];
    $groups = $board['groups'];

    // ---- Step 2: first page of every group, in parallel ----
    $firstPageQueries = [];
    $groupTitleByKey = [];
    foreach ($groups as $g) {
        $key = 'g_' . $g['id'];
        $groupTitleByKey[$key] = $g['title'];
        $firstPageQueries[$key] = 'query {
  boards (ids: [' . $boardId . ']) {
    groups (ids: ["' . $g['id'] . '"]) {
      items_page (limit: 100) {
        cursor
        ' . $itemsSelection . '
      }
    }
  }
}';
    }

    $t0 = microtime(true);
    $firstResults = callMondayAPIBatch($url, $headers, $firstPageQueries);
    error_log("[lifespan] step2 first-page-of-each-group (" . count($firstPageQueries) . " queries) took=" . round(microtime(true)-$t0,2) . "s");

    // Cursor queue for next round of pagination
    $cursors = []; // each entry: ['title' => ..., 'cursor' => ...]

    foreach ($firstResults as $key => $r) {
        if (isset($r['error']))  return $r;
        if (isset($r['errors'])) return ['error' => 'Monday GraphQL: ' . json_encode($r['errors'])];

        $groupNode = $r['data']['boards'][0]['groups'][0] ?? null;
        if (!$groupNode || !isset($groupNode['items_page'])) continue;

        $page  = $groupNode['items_page'];
        $items = $page['items'];
        $normalizeItems($items);
        foreach ($items as $item) {
            $item['group_title']  = $groupTitleByKey[$key];
            $item['board_name']   = $boardName;
            $item['country_code'] = $country;
            $allItems[] = $item;
        }
        if (!empty($page['cursor'])) {
            $cursors[] = ['title' => $groupTitleByKey[$key], 'cursor' => $page['cursor']];
        }
    }

    // ---- Step 3: round-by-round parallel pagination ----
    $round = 0;
    while (!empty($cursors)) {
        $round++;
        $batchQueries = [];
        foreach ($cursors as $i => $c) {
            $batchQueries[$i] = 'query {
  next_items_page (limit: 100, cursor: "' . $c['cursor'] . '") {
    cursor
    ' . $itemsSelection . '
  }
}';
        }

        $tr = microtime(true);
        $batchResults = callMondayAPIBatch($url, $headers, $batchQueries);
        error_log("[lifespan] step3 round=$round (" . count($batchQueries) . " queries) took=" . round(microtime(true)-$tr,2) . "s");

        $newCursors = [];
        foreach ($batchResults as $i => $r) {
            $title = $cursors[$i]['title'];
            if (isset($r['error']))  return $r;
            if (isset($r['errors'])) return ['error' => 'Monday GraphQL: ' . json_encode($r['errors'])];
            if (!isset($r['data']['next_items_page'])) continue;

            $page  = $r['data']['next_items_page'];
            $items = $page['items'];
            $normalizeItems($items);
            foreach ($items as $item) {
                $item['group_title']  = $title;
                $item['board_name']   = $boardName;
                $item['country_code'] = $country;
                $allItems[] = $item;
            }
            if (!empty($page['cursor'])) {
                $newCursors[] = ['title' => $title, 'cursor' => $page['cursor']];
            }
        }
        $cursors = $newCursors;
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
                if (isset($item['country_code']) && $item['country_code'] === $filterCountry) {
                    $filteredItems[] = $item;
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
