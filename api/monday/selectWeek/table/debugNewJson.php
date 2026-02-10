<?php
// Debug script to check new JSON structure

$fileDir = __DIR__ . '/../file/ALL';
$files = glob($fileDir . '/ALL_monday_data*.json');

usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
echo "Latest file: " . basename($latestFile) . "<br><br>";

$jsonData = json_decode(file_get_contents($latestFile), true);

$currencyMapping = [
    'thb' => 'TH',
    'aud' => 'AU',
    'nzd' => 'NZ',
    'gbp' => 'UK',
    'usd' => 'US',
    'cad' => 'CA'
];

$counts = [];
$statusCounts = [];
$cancelledWithCompleted = [];

if (isset($jsonData['data']['items'])) {
    echo "JSON Structure: data.items<br>";
    echo "Total items: " . count($jsonData['data']['items']) . "<br><br>";
    
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $status = '';
        $projectStage = '';
        
        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'status0') $currency = strtolower($col['text'] ?? '');
            if ($col['id'] === 'status') $status = $col['text'] ?? '';
            if ($col['id'] === 'lookup_mkwh1gcr') {
                $projectStage = !empty($col['display_value']) ? $col['display_value'] : ($col['text'] ?? '');
            }
        }
        
        $country = isset($currencyMapping[$currency]) ? $currencyMapping[$currency] : "Unknown($currency)";
        
        if (!isset($counts[$country])) $counts[$country] = ['total' => 0, 'active' => 0];
        $counts[$country]['total']++;
        
        if ($status === 'Active Subscription' || $status === 'active' || $projectStage === 'Completed') {
            $counts[$country]['active']++;
        }
        
        if (!isset($statusCounts[$status])) $statusCounts[$status] = 0;
        $statusCounts[$status]++;
        
        // เช็คว่า Cancelled items มี Completed หรือไม่
        if (stripos($status, 'Cancel') !== false && $projectStage === 'Completed') {
            $cancelledWithCompleted[] = [
                'name' => $item['name'],
                'status' => $status,
                'projectStage' => $projectStage,
                'group' => $item['group_title'] ?? 'N/A'
            ];
        }
    }
} else {
    echo "JSON Structure: NOT data.items<br>";
    echo "Keys: " . implode(', ', array_keys($jsonData['data'] ?? [])) . "<br>";
}

echo "<h3>=== Country Counts ===</h3>";
foreach ($counts as $c => $v) {
    echo "$c: Total={$v['total']}, Active={$v['active']}<br>";
}

echo "<h3>=== Status Values ===</h3>";
arsort($statusCounts);
foreach ($statusCounts as $s => $c) {
    echo "'$s': $c<br>";
}

echo "<h3>=== Cancelled items with Completed ===</h3>";
echo "Count: " . count($cancelledWithCompleted) . "<br>";
if (count($cancelledWithCompleted) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Name</th><th>Status</th><th>Project Stage</th><th>Group</th></tr>";
    foreach (array_slice($cancelledWithCompleted, 0, 10) as $item) {
        echo "<tr><td>{$item['name']}</td><td>{$item['status']}</td><td>{$item['projectStage']}</td><td>{$item['group']}</td></tr>";
    }
    echo "</table>";
}
