<?php
// เช็ค Request Cancellation แยกตามประเทศ

$latestFile = __DIR__ . '/../file/ALL/ALL_monday_data260206-1423.json';
$jsonData = json_decode(file_get_contents($latestFile), true);

$currencyMapping = [
    'thb' => 'TH', 'aud' => 'AU', 'nzd' => 'NZ',
    'gbp' => 'UK', 'usd' => 'US', 'cad' => 'CA'
];

$requestCancelItems = [];

if (isset($jsonData['data']['items'])) {
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $activeStatus = '';
        $accountName = '';
        $projectStage = '';
        
        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'status0') $currency = $col['text'] ?? '';
            if ($col['id'] === 'status') $activeStatus = $col['text'] ?? '';
            if ($col['id'] === 'text9') $accountName = $col['text'] ?? '';
            if ($col['id'] === 'lookup_mkwh1gcr') {
                $projectStage = !empty($col['display_value']) ? $col['display_value'] : ($col['text'] ?? '');
            }
        }
        
        // เช็ค Country จาก text9
        $country = null;
        if (!empty($accountName) && preg_match('/- (AU|NZ|UK|USA|US|TH|CA)$/i', $accountName, $matches)) {
            $countryFromText = strtoupper($matches[1]);
            if ($countryFromText === 'USA') $countryFromText = 'US';
            $country = $countryFromText;
        }
        // Fallback: Currency
        if (!$country) {
            $currencyLower = strtolower($currency ?? '');
            $country = isset($currencyMapping[$currencyLower]) ? $currencyMapping[$currencyLower] : null;
        }
        
        if ($activeStatus === 'Request Cancellation') {
            $requestCancelItems[] = [
                'name' => $item['name'],
                'accountName' => $accountName,
                'country' => $country ?? 'Unknown',
                'currency' => $currency,
                'projectStage' => $projectStage,
                'status' => $activeStatus
            ];
        }
    }
}

echo "<h2>Request Cancellation Items</h2>";
echo "Total: " . count($requestCancelItems) . "<br><br>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>#</th><th>Item Name</th><th>Account Name (text9)</th><th>Country</th><th>Currency (status0)</th><th>Project Stage</th></tr>";
$i = 1;
foreach ($requestCancelItems as $item) {
    $highlight = ($item['country'] === 'UK') ? "style='background-color: #FFEB3B;'" : "";
    echo "<tr $highlight>";
    echo "<td>$i</td>";
    echo "<td>{$item['name']}</td>";
    echo "<td>{$item['accountName']}</td>";
    echo "<td>{$item['country']}</td>";
    echo "<td>{$item['currency']}</td>";
    echo "<td>{$item['projectStage']}</td>";
    echo "</tr>";
    $i++;
}
echo "</table>";

// แยกตามประเทศ
echo "<h3>Summary by Country</h3>";
$byCountry = [];
foreach ($requestCancelItems as $item) {
    $c = $item['country'];
    if (!isset($byCountry[$c])) $byCountry[$c] = 0;
    $byCountry[$c]++;
}
foreach ($byCountry as $c => $count) {
    echo "$c: $count<br>";
}
