<?php
/**
 * getMondayBoardInfo.php
 * ดึง group_id และ column_id ทั้งหมดของ board
 * ใช้ครั้งเดียวเพื่อหา group_id ของ Completed Projects และ column_id ของ Active Subscriptions
 */
ob_start();
session_start();
if (empty($_SESSION['id'])) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
ob_clean();
header('Content-Type: application/json');

$token   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
$boardId = '1881439330';

$gql = <<<GQL
{
  boards(ids: {$boardId}) {
    name
    groups {
      id
      title
    }
    columns {
      id
      title
      type
    }
    items_page(limit: 3) {
      items {
        id
        name
        group {
          id
          title
        }
        column_values {
          id
          text
          value
        }
      }
    }
  }
}
GQL;

$ch = curl_init('https://api.monday.com/v2');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $gql]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . $token,
    'API-Version: 2024-01',
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$board = $data['data']['boards'][0] ?? null;

if (!$board) {
    echo json_encode(['success' => false, 'raw' => $data]);
    exit;
}

$items = $board['items_page']['items'] ?? [];
$itemsSample = array_map(function($item) {
    $cols = [];
    foreach ($item['column_values'] as $col) {
        if (!empty($col['text'])) {
            $cols[$col['id']] = $col['text'];
        }
    }
    return [
        'id'    => $item['id'],
        'name'  => $item['name'],
        'group' => $item['group']['title'] ?? '-',
        'columns_with_value' => $cols,
    ];
}, $items);

echo json_encode([
    'success'      => true,
    'board'        => $board['name'],
    'groups'       => $board['groups'],
    'columns'      => array_map(fn($c) => ['id' => $c['id'], 'title' => $c['title'], 'type' => $c['type']], $board['columns']),
    'items_sample' => $itemsSample,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
