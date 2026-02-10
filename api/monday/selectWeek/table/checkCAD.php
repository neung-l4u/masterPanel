<?php
// Check CAD items with Project Stage = Completed

$latestFile = __DIR__ . '/../file/ALL/ALL_monday_data260205-1736.json';
$jsonData = json_decode(file_get_contents($latestFile), true);

$cadItems = [];
$cadCompleted = [];

if (isset($jsonData['data']['items'])) {
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
        
        if ($currency === 'cad') {
            $cadItems[] = [
                'name' => $item['name'],
                'status' => $status,
                'projectStage' => $projectStage,
                'group' => $item['group_title'] ?? 'N/A'
            ];
            
            if ($projectStage === 'Completed') {
                $cadCompleted[] = [
                    'name' => $item['name'],
                    'status' => $status,
                    'projectStage' => $projectStage,
                    'group' => $item['group_title'] ?? 'N/A'
                ];
            }
        }
    }
}

echo "<h2>CAD Items Summary</h2>";
echo "Total CAD items: " . count($cadItems) . "<br>";
echo "CAD with Project Stage = Completed: " . count($cadCompleted) . "<br><br>";

echo "<h3>All CAD Items</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>#</th><th>Name</th><th>Status</th><th>Project Stage</th><th>Group</th></tr>";
$i = 1;
foreach ($cadItems as $item) {
    $highlight = ($item['projectStage'] === 'Completed') ? "style='background-color: #90EE90;'" : "";
    echo "<tr $highlight><td>$i</td><td>{$item['name']}</td><td>{$item['status']}</td><td>{$item['projectStage']}</td><td>{$item['group']}</td></tr>";
    $i++;
}
echo "</table>";

echo "<h3>CAD with Completed (highlighted green above)</h3>";
echo "Count: " . count($cadCompleted);
