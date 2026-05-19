<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$MONDAY_TOKEN = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";
$BOARD_ID     = 1881439330;

$shopName = isset($_GET['shopName']) ? trim($_GET['shopName']) : '';

if (empty($shopName)) {
    echo json_encode([]);
    exit;
}

$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/subs_' . md5($shopName) . '.json';
$bust      = isset($_GET['bust']) && $_GET['bust'] === '1';

if (!$bust && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 900) {
    echo file_get_contents($cacheFile);
    exit;
}

$shopNameEsc = addslashes($shopName);

$gql = <<<GQL
{
  boards(ids: [$BOARD_ID]) {
    items_page(limit: 100, query_params: {
      rules: [{ column_id: "text9", compare_value: ["$shopNameEsc"], operator: contains_text }]
    }) {
      items {
        id
        name
        column_values(ids: ["status", "numbers", "status0"]) {
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

$data = json_decode($raw, true);
$result = [];

foreach (($data['data']['boards'][0]['items_page']['items'] ?? []) as $item) {
    $cols = [];
    foreach ($item['column_values'] as $col) {
        $cols[$col['id']] = $col['text'] ?? '';
    }

    if (stripos($cols['status'] ?? '', 'active') === false) continue;

    $result[] = [
        'id'       => $item['id'],
        'name'     => $item['name'],
        'price'    => $cols['numbers'] ?? '',
        'currency' => strtoupper($cols['status0'] ?? ''),
    ];
}

$output = json_encode($result, JSON_UNESCAPED_UNICODE);

if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
file_put_contents($cacheFile, $output);

echo $output;
