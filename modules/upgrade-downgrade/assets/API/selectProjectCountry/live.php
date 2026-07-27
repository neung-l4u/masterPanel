<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── CONFIG ────────────────────────────────────────────────────────────────────
$MONDAY_TOKEN = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";

$CUSTOMER_IDS = [
    'CA' => [5026278085],   // e.g. [1234567890]
    'AU' => [5026277183],
    'NZ' => [5026295114],
    'UK' => [5026295223],
    'US' => [5026295245],
    'TH' => [5026295175],
    'ALL' => [],  // leave empty to query all per-country boards above
];

// Map country code → Monday board_id(s) — fill in your actual board IDs
$PROJECT_IDS = [
    'TH' => [1943203205],
    'CA' => [1943203287],
    'UK' => [1943203305],
    'NZ' => [1943203264],
    'AU' => [1943203246],
    'US' => [1940392927],
    'ALL' => [],
];

$COUNTRY_NAMES = [
    'TH' => 'Thailand',
    'AU' => 'Australia',
    'NZ' => 'New Zealand',
    'UK' => 'United Kingdom',
    'US' => 'United States',
    'CA' => 'Canada',
];

// Known business type keywords for shop_type auto-detection
$SHOP_TYPE_KEYWORDS = ['massage', 'restaurant', 'thai', 'cafe', 'salon', 'spa', 'bar', 'grill', 'kitchen', 'food'];
// ─────────────────────────────────────────────────────────────────────────────

$country = isset($_GET['country']) ? strtoupper(trim($_GET['country'])) : '';

if (empty($country)) {
    echo json_encode(['error' => 'Missing country parameter']);
    exit;
}

// ── Cache ─────────────────────────────────────────────────────────────────────
$CACHE_TTL = 3600; // seconds
$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/projects_' . $country . '.json';
$bust      = isset($_GET['bust']) && $_GET['bust'] === '1';

if (!$bust && !isset($_GET['debug']) && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $CACHE_TTL) {
    echo file_get_contents($cacheFile);
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────

// Resolve board IDs to query
if ($country === 'ALL') {
    if (!empty($PROJECT_IDS['ALL'])) {
        $boardIds = $PROJECT_IDS['ALL'];
    } else {
        $boardIds = [];
        foreach ($PROJECT_IDS as $cc => $ids) {
            if ($cc !== 'ALL') $boardIds = array_merge($boardIds, $ids);
        }
        $boardIds = array_unique($boardIds);
    }
} else {
    $boardIds = $PROJECT_IDS[$country] ?? [];
}

if (empty($boardIds)) {
    echo json_encode([]);
    exit;
}

$boardIdList = implode(',', array_map('intval', $boardIds));
$countryName = $COUNTRY_NAMES[$country] ?? '';

// Customer board column IDs
$COL_CUST_PHONE = 'phone_mm00s5xm';
$COL_CUST_EMAIL = 'email_mm00bymb';
$COL_CUST_NAME  = '';  // item name = customer name

// ── GraphQL query with cursor-based pagination + linked customer ──────────────
function queryMonday(string $token, string $boardIdList, int $limit, ?string $cursor): array
{
    $cursorArg  = $cursor ? ', cursor: "' . addslashes($cursor) . '"' : '';
    $gql = <<<GQL
    {
      boards(ids: [$boardIdList]) {
        id
        name
        items_page(limit: $limit$cursorArg) {
          cursor
          items {
            id
            name
            column_values {
              id
              text
              value
              ... on BoardRelationValue {
                linked_items {
                  id
                  name
                  column_values {
                    id
                    text
                    value
                  }
                }
              }
            }
          }
        }
      }
    }
    GQL;

    $ch = curl_init('https://api.monday.com/v2');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
            'API-Version: 2024-01',
        ],
        CURLOPT_TIMEOUT        => 60,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['error' => $err];
    return json_decode($raw, true) ?? ['error' => 'Invalid JSON'];
}

$debug  = isset($_GET['debug']) && $_GET['debug'] === '1';
$debug2 = isset($_GET['debug']) && $_GET['debug'] === '2';

// In debug mode: fetch first item with ALL columns to inspect column IDs
if ($debug) {
    $debugBoard = isset($_GET['board_id']) ? intval($_GET['board_id']) : null;
    $debugBoardList = $debugBoard ? (string)$debugBoard : $boardIdList;
    $gql = <<<GQL
    {
      boards(ids: [$debugBoardList]) {
        id
        name
        items_page(limit: 1) {
          items {
            id
            name
            column_values {
              id
              text
            }
          }
        }
      }
    }
    GQL;

    $ch = curl_init('https://api.monday.com/v2');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $MONDAY_TOKEN,
            'API-Version: 2024-01',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    $simplified = [];
    foreach (($decoded['data']['boards'] ?? []) as $board) {
        foreach (($board['items_page']['items'] ?? []) as $item) {
            $cols = [];
            foreach ($item['column_values'] as $col) {
                if (!empty($col['text'])) {
                    $cols[$col['id']] = $col['text'];
                }
            }
            $simplified[] = ['id' => $item['id'], 'name' => $item['name'], 'non_empty_cols' => $cols];
        }
    }
    echo json_encode($simplified, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

set_time_limit(300);

$result   = [];
$cursor   = null;
$limit    = $debug2 ? 1 : 50;
$maxPages = $debug2 ? 1 : 40;

for ($page = 0; $page < $maxPages; $page++) {
    $resp = queryMonday($MONDAY_TOKEN, $boardIdList, $limit, $cursor);

    if (isset($resp['error'])) break;
    if (!isset($resp['data']['boards'])) break;

    $nextCursor = null;

    foreach ($resp['data']['boards'] as $board) {
        $boardId = (string) ($board['id'] ?? '');
        $page_data = $board['items_page'] ?? [];
        $nextCursor = $page_data['cursor'] ?? null;
        $items = $page_data['items'] ?? [];

        foreach ($items as $item) {
            $cols = [];
            $relationColId = '';
            foreach ($item['column_values'] as $col) {
                $cols[$col['id']] = ['text' => $col['text'] ?? '', 'value' => $col['value'] ?? ''];
                if (empty($relationColId) && strpos($col['id'], 'board_relation_') === 0) {
                    $relationColId = $col['id'];
                }
            }

            // Auto-detect country column: color_ whose text = country code
            $detectedCountry = $country;
            $shopType        = '';

            // Priority: color1 column (Customer Type/Industrial Type)
            if (!empty($cols['color1']['text'])) {
                $shopType = $cols['color1']['text'];
            }

            foreach ($cols as $id => $cv) {
                if (strpos($id, 'color_') === 0 && $cv['text'] === $country) {
                    $detectedCountry = $cv['text'];
                }
                if (empty($shopType) && strpos($id, 'color_') === 0) {
                    $val = $cv['text'];
                    if ($val && $val !== $country) {
                        $valLower = strtolower($val);
                        foreach ($SHOP_TYPE_KEYWORDS as $kw) {
                            if (strpos($valLower, $kw) !== false) {
                                $shopType = $val;
                                break;
                            }
                        }
                    }
                }
            }

            // debug2: show detected columns for first item
            if ($debug2) {
                echo json_encode([
                    'item_id'          => $item['id'],
                    'item_name'        => $item['name'],
                    'relation_col'     => $relationColId,
                    'relation_value'   => $relationColId ? ($cols[$relationColId] ?? null) : null,
                    'detected_type'    => $shopType,
                    'detected_country' => $detectedCountry,
                ], JSON_PRETTY_PRINT);
                exit;
            }

            // Extract owner name and phone — prefer direct columns, fallback to linked_items
            $ownerName = $cols['primary_contact']['text'] ?? '';
            $phone     = $cols['contact_mobile']['text'] ?? ($cols['shop_number']['text'] ?? '');

            if (empty($ownerName) || empty($phone)) {
                foreach ($item['column_values'] as $col) {
                    if (strpos($col['id'], 'board_relation_') !== 0) continue;
                    foreach ($col['linked_items'] ?? [] as $cust) {
                        if (empty($ownerName)) $ownerName = $cust['name'] ?? '';
                        foreach ($cust['column_values'] ?? [] as $cc) {
                            if (empty($phone) && strpos($cc['id'], 'phone_') === 0) {
                                $phone = $cc['text'] ?: (json_decode($cc['value'] ?? '', true)['phone'] ?? '');
                            }
                        }
                        break;
                    }
                    if ($ownerName && $phone) break;
                }
            }

            // Fallback: detect shop type from shop name if color columns gave nothing
            if (empty($shopType)) {
                $nameLower = strtolower($item['name'] ?? '');
                foreach ($SHOP_TYPE_KEYWORDS as $kw) {
                    if (strpos($nameLower, $kw) !== false) {
                        $shopType = ucfirst($kw);
                        break;
                    }
                }
            }

            $result[] = [
                'shopId'    => (string) $item['id'],
                'shopName'  => $item['name'] ?? '',
                'shopType'  => $shopType,
                'ownerName' => $ownerName,
                'phone'     => $phone,
                'country'   => $detectedCountry,
                'boardId'   => $boardId,
            ];
        }
    }

    if (!$nextCursor) break;
    $cursor = $nextCursor;
}

$output = json_encode($result, JSON_UNESCAPED_UNICODE);

if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
file_put_contents($cacheFile, $output);

echo $output;
