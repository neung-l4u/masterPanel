<?php
/**
 * Discovery script — fetch board structure (groups + columns) from Monday.com
 * Usage: getBoardStructure.php?board=au|us|th
 */
header('Content-Type: application/json');

$url = "https://api.monday.com/v2";
$token = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";

$boards = [
    'au' => '5026435242',
    'us' => '5026427192',
    'th' => '5026435384'
];

$board = isset($_GET['board']) ? strtolower($_GET['board']) : 'au';
if (!isset($boards[$board])) {
    echo json_encode(['error' => 'Invalid board. Use: au, us, th']);
    exit;
}

$boardId = $boards[$board];

$headers = [
    "Content-Type: application/json",
    "Authorization: " . $token
];

$query = 'query {
  boards (ids: ' . $boardId . ') {
    name
    columns {
      id
      title
      type
    }
    groups {
      id
      title
    }
  }
}';

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
    echo json_encode(['error' => $error]);
    exit;
}

$result = json_decode($response, true);

// Pretty output
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
