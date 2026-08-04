<?php
set_time_limit(300); // large boards (AU ~1300) need many slow paginated requests
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

// $CUSTOMER_IDS = [
//     'CA' => [5026278085],   // e.g. [1234567890]
//     'AU' => [5026277183],
//     'NZ' => [5026295114],
//     'UK' => [5026295223],
//     'US' => [5026295245],
//     'TH' => [5026295175],
//     'ALL' => [],  // leave empty to query all per-country boards above
// ];

// // Map country code → Monday board_id(s) — fill in your actual board IDs
// $PROJECT_IDS = [
//     'CA' => [5026295488],   // e.g. [1234567890]
//     'AU' => [5026295450],
//     'NZ' => [5026295509],
//     'UK' => [5026295557],
//     'US' => [5026295593],
//     'TH' => [5026295538],
//     'ALL' => [],  // leave empty to query all per-country boards above
// ];

$PROJECT_IDS = [
    'CA' => [1943203287],   // e.g. [1234567890]
    'AU' => [1943203246],
    'NZ' => [1943203264],
    'UK' => [1943203305],
    'US' => [1940392927],
    'TH' => [1943203205],
    'ALL' => [],  // leave empty to query all per-country boards above
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
                linked_item_ids
              }
            }
          }
        }
      }
    }
    GQL;

    return mondayRequest($token, $gql);
}

// Batch-fetch customer (Contacts & Accounts) records by id — much faster than
// resolving linked_items inline per project item.
function queryCustomers(string $token, array $ids): array
{
    if (empty($ids)) return [];
    $idList = implode(',', array_map('intval', $ids));
    $count  = count($ids);
    $gql = <<<GQL
    {
      items(ids: [$idList], limit: $count) {
        id
        name
        column_values(ids: ["text0", "text7", "contact_phone", "dup__of_mobile", "contact_email"]) {
          id
          text
        }
      }
    }
    GQL;

    $out = [];
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $resp = mondayRequest($token, $gql);
        if (isset($resp['data']['items'])) {
            foreach ($resp['data']['items'] as $cust) {
                $out[(string) $cust['id']] = $cust;
            }
            return $out;
        }
        usleep(500000);
    }
    return $out;
}

function mondayRequest(string $token, string $gql): array
{
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
        CURLOPT_TIMEOUT        => 90,
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
$limit    = $debug2 ? 1 : 100;  // filtered nested cols keep 100 under the subgraph complexity limit
$maxPages = $debug2 ? 1 : 20;   // 100 * 20 = 2000, covers largest board (AU ~1300)

$apiError = null;

for ($page = 0; $page < $maxPages; $page++) {
    // Retry transient Monday errors (subgraph fetch/timeout) so a single bad
    // page doesn't silently truncate a large board mid-pagination.
    $resp = null;
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $resp = queryMonday($MONDAY_TOKEN, $boardIdList, $limit, $cursor);
        if (!isset($resp['error']) && !isset($resp['errors']) && isset($resp['data']['boards'])) {
            $apiError = null;
            break;
        }
        $apiError = $resp['error'] ?? ($resp['errors'][0]['message'] ?? 'Unknown Monday API error');
        usleep(500000); // 0.5s backoff before retry
    }

    // Monday returns { errors: [...], data: null } on subgraph/complexity failures
    if ($apiError !== null) {
        break;
    }

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

            // Capture the linked customer id from the Contacts & Accounts relation.
            // The item has many board_relation_/connect_boards columns; only
            // "connect_boards0" links to the Contacts board (id 1862866069).
            // Customer details are resolved in a fast batch pass below.
            $custId = '';
            foreach ($item['column_values'] as $col) {
                if ($col['id'] !== 'connect_boards0') continue;
                $custId = (string) ($col['linked_item_ids'][0] ?? '');
                break;
            }

            $result[] = [
                'shopId'    => (string) $item['id'],
                'shopName'  => $item['name'] ?? '',
                'shopType'  => $shopType,
                'ownerName' => '',
                'phone'     => '',
                'country'   => $detectedCountry,
                '_custId'   => $custId,
            ];
        }
    }

    if (!$nextCursor) break;
    $cursor = $nextCursor;
}

// Don't cache/return an empty result caused by an API error — keeps stale-but-valid
// cache alive and surfaces the failure instead of poisoning the cache with []
if ($apiError && empty($result)) {
    http_response_code(502);
    echo json_encode(['error' => $apiError]);
    exit;
}

// ── Phase 2: batch-resolve customer (owner/phone) records ─────────────────────
// Collect unique customer ids, fetch them in chunks, then fill owner + phone.
// This avoids inline linked_items resolution, which is ~2x slower per page.
$custIds = array_values(array_unique(array_filter(array_map(
    static fn($r) => $r['_custId'],
    $result
))));

$customers = [];
foreach (array_chunk($custIds, 100) as $chunk) {
    $customers += queryCustomers($MONDAY_TOKEN, $chunk);
}

foreach ($result as &$row) {
    $cust = $customers[$row['_custId']] ?? null;
    unset($row['_custId']);
    if (!$cust) continue;

    $first = $last = $mobile = $altPhone = $email = '';
    foreach ($cust['column_values'] ?? [] as $cc) {
        switch ($cc['id']) {
            case 'text0':          $first    = $cc['text'] ?? ''; break;
            case 'text7':          $last     = $cc['text'] ?? ''; break;
            case 'contact_phone':  $mobile   = $cc['text'] ?? ''; break;
            case 'dup__of_mobile': $altPhone = $cc['text'] ?? ''; break;
            case 'contact_email':  $email    = $cc['text'] ?? ''; break;
        }
    }
    $owner = trim($first . ' ' . $last);
    if ($owner === '') $owner = $cust['name'] ?? '';
    $row['ownerName'] = $owner;
    $row['phone']     = $mobile ?: $altPhone ?: $email; // email fallback when no phone
}
unset($row);
// ──────────────────────────────────────────────────────────────────────────────

$output = json_encode($result, JSON_UNESCAPED_UNICODE);

if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
file_put_contents($cacheFile, $output);

echo $output;