<?php
// Returns the Shop Type status-column labels from the Lead Management board.
// Consumed by modules/signupMini/index.php to populate the Shop Type dropdown
// with the exact labels that exist in Monday, so submitted values always match
// (no more "missingLabel" rejects in the downstream Make.com scenario).

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- Config ---------------------------------------------------------------
$url      = 'https://api.monday.com/v2';
$token    = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
$boardId  = '1971942614';           // Lead Management board
$columnId = 'status_mkn4hemw';      // "Shop Type" status column

$cacheFile = __DIR__ . '/cache/shopType.json';
$cacheTtl  = 3600; // seconds

// --- Serve from cache if fresh -------------------------------------------
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if (isset($cached['data']) && !empty($cached['data'])) {
        $cached['source'] = 'cache';
        echo json_encode($cached, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// --- Fetch labels from Monday --------------------------------------------
$query = 'query { boards (ids: ' . $boardId . ') { columns (ids: ["' . $columnId . '"]) { title settings_str } } }';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . $token,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$labels = [];
if ($httpCode === 200 && $response) {
    $result   = json_decode($response, true);
    $settings = $result['data']['boards'][0]['columns'][0]['settings_str'] ?? '';
    $parsed   = $settings ? json_decode($settings, true) : null;

    if (isset($parsed['labels']) && is_array($parsed['labels'])) {
        // Preserve Monday's numeric label index as the id.
        foreach ($parsed['labels'] as $id => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue; // skip empty label slots
            }
            $labels[] = ['id' => (string) $id, 'label' => $label];
        }
        // Sort by numeric id so ordering is stable.
        usort($labels, function ($a, $b) {
            return (int) $a['id'] - (int) $b['id'];
        });
    }
}

// --- Fail: let the caller fall back --------------------------------------
if (empty($labels)) {
    // Serve stale cache if we have any, rather than nothing.
    if (is_file($cacheFile)) {
        $stale = json_decode(file_get_contents($cacheFile), true);
        if (isset($stale['data']) && !empty($stale['data'])) {
            $stale['source'] = 'stale-cache';
            echo json_encode($stale, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Unable to load Shop Type labels from Monday']);
    exit;
}

// --- Success: cache + return ---------------------------------------------
$payload = ['success' => true, 'data' => $labels, 'source' => 'monday'];

if (!is_dir(dirname($cacheFile))) {
    @mkdir(dirname($cacheFile), 0755, true);
}
@file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_UNICODE));

echo json_encode($payload, JSON_UNESCAPED_UNICODE);
