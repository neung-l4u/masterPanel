<?php
require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';

// Fixed date range: 2026-02-01 to 2026-02-07
$startDate = '2026-02-01 00:00:00';
$endDate = '2026-02-07 23:59:59';
$cutoffDate = '2026-02-08'; // Active = items with cancel date empty OR cancel date >= this

// ========== 1. Read latest JSON file from Monday ==========
$jsonDir = __DIR__ . '/../file/ALL/';
$files = glob($jsonDir . 'ALL_monday_data*.json');

if (empty($files)) {
    die("JSON file not found");
}

// Sort by latest modified time
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
$jsonData = json_decode(file_get_contents($latestFile), true);

// ========== 2. Parse Monday data by country ==========
$currencyMapping = [
    'thb' => 'TH',
    'aud' => 'AU',
    'nzd' => 'NZ',
    'gbp' => 'UK',
    'usd' => 'US',
    'cad' => 'CA'
];

$mondayData = [];
$processedAccounts = [];

if (isset($jsonData['data']['items'])) {
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $activeStatus = '';
        $projectStage = '';
        $accountName = '';
        $cancelDate = '';
        
        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'status0') {
                $currency = $col['text'];
            }
            if ($col['id'] === 'status') {
                $activeStatus = $col['text'];
            }
            if ($col['id'] === 'lookup_mkwh1gcr') {
                $projectStage = !empty($col['display_value']) ? $col['display_value'] : $col['text'];
            }
            if ($col['id'] === 'text9') {
                $accountName = $col['text'] ?? '';
            }
            if ($col['id'] === 'date') {
                $cancelDate = $col['text'] ?? '';
            }
        }
        
        // Determine country from text9 (Account Name)
        $country = null;
        if (!empty($accountName) && preg_match('/- (AU|NZ|UK|USA|US|TH|CA)$/i', $accountName, $matches)) {
            $countryFromText = strtoupper($matches[1]);
            if ($countryFromText === 'USA') $countryFromText = 'US';
            $country = $countryFromText;
        }
        
        // Fallback: use Currency
        if (!$country) {
            $currencyLower = strtolower($currency ?? '');
            $country = isset($currencyMapping[$currencyLower]) ? $currencyMapping[$currencyLower] : null;
        }
        
        if (!$country) continue;
        
        if (!isset($mondayData[$country])) {
            $mondayData[$country] = ['active' => 0];
            $processedAccounts[$country] = [];
        }
        
        // Count Active: (status = 'Active Subscription' OR 'active' OR 'Request Cancellation') AND (lookup_mkwh1gcr = 'Completed')
        // AND (cancel date is empty OR cancel date >= cutoffDate)
        if (($activeStatus === 'Active Subscription' || $activeStatus === 'active' || $activeStatus === 'Request Cancellation') && $projectStage === 'Completed') {
            
            // Filter by cancel date
            $includeItem = false;
            if (empty($cancelDate)) {
                $includeItem = true; // No cancel date = still active
            } else {
                // Parse cancel date (format: "2024-07-25 11:32")
                $cancelDateOnly = substr($cancelDate, 0, 10);
                if ($cancelDateOnly >= $cutoffDate) {
                    $includeItem = true; // Cancelled on or after cutoff = was still active before cutoff
                }
            }
            
            if ($includeItem) {
                if (!empty($accountName) && !in_array($accountName, $processedAccounts[$country])) {
                    $mondayData[$country]['active']++;
                    $processedAccounts[$country][] = $accountName;
                } elseif (empty($accountName)) {
                    $mondayData[$country]['active']++;
                }
            }
        }
    }
}

// ========== 3. Get Cancellation data from Database (Unsub) ==========
$cancellations = $db->query('SELECT county FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $startDate, $endDate)->fetchAll();

$weeklyDrop = [];
foreach ($cancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($weeklyDrop[$country])) {
        $weeklyDrop[$country] = 0;
    }
    $weeklyDrop[$country]++;
}

// ========== 4. Get Signup data from Database ==========
$signups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $startDate, $endDate)->fetchAll();

$weeklySignup = [];
$productPopularity = [];
$processedShops = [];
foreach ($signups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'];
    $country = $dataLogs['Country'] ?: 'Unknown';
    $product = $dataLogs['MainProduct'] ?: 'Unknown';
    
    if (in_array($shopName, $processedShops)) {
        continue;
    }
    $processedShops[] = $shopName;
    
    if (!isset($weeklySignup[$country])) {
        $weeklySignup[$country] = 0;
    }
    $weeklySignup[$country]++;
    
    // Track product data by country
    $key = $product . '|' . $country;
    if (!isset($productPopularity[$key])) {
        $productPopularity[$key] = ['product' => $product, 'country' => $country, 'count' => 0];
    }
    $productPopularity[$key]['count']++;
}

// Sort products by popularity
usort($productPopularity, function($a, $b) {
    return $b['count'] - $a['count'];
});

// ========== 5. Aggregate data and calculate ==========
$allCountries = array_unique(array_merge(
    array_keys($mondayData),
    array_keys($weeklyDrop),
    array_keys($weeklySignup)
));
sort($allCountries);

$reportData = [];
$totalActive = 0;
$totalDrop = 0;
$totalSignup = 0;

foreach ($allCountries as $country) {
    $active = isset($mondayData[$country]) ? $mondayData[$country]['active'] : 0;
    $drop = isset($weeklyDrop[$country]) ? $weeklyDrop[$country] : 0;
    $signup = isset($weeklySignup[$country]) ? $weeklySignup[$country] : 0;
    
    $netChange = $signup - $drop;
    $percentChange = $active > 0 ? round(($netChange / $active) * 100, 2) : 0;
    
    if ($percentChange >= 5) {
        $status = 'High';
        $statusColor = 'green';
    } elseif ($percentChange >= 0) {
        $status = 'Mid';
        $statusColor = 'orange';
    } elseif ($percentChange >= -10) {
        $status = 'Low';
        $statusColor = 'red';
    } else {
        $status = 'Very Low';
        $statusColor = 'darkred';
    }
    
    $reportData[] = [
        'country' => $country,
        'active' => $active,
        'signup' => $signup,
        'drop' => $drop,
        'percentChange' => $percentChange,
        'status' => $status,
        'statusColor' => $statusColor
    ];
    
    $totalActive += $active;
    $totalDrop += $drop;
    $totalSignup += $signup;
}

// Calculate Total
$totalNetChange = $totalSignup - $totalDrop;
$totalPercentChange = $totalActive > 0 ? round(($totalNetChange / $totalActive) * 100, 2) : 0;

if ($totalPercentChange >= 5) {
    $totalStatus = 'High';
    $totalStatusColor = 'green';
} elseif ($totalPercentChange >= 0) {
    $totalStatus = 'Mid';
    $totalStatusColor = 'orange';
} elseif ($totalPercentChange >= -10) {
    $totalStatus = 'Low';
    $totalStatusColor = 'red';
} else {
    $totalStatus = 'Very Low';
    $totalStatusColor = 'darkred';
}
?>

<p style="font: 14px roboto, sans-serif; margin-bottom: 10px;">
    <b>Weekly Report</b><br>
    <small>Period: <?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small><br>
    <small>Active cutoff: items with cancel date before <?php echo $cutoffDate; ?> excluded</small><br>
    <small>JSON file: <?php echo basename($latestFile); ?></small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th>Country</th>
        <th>Active</th>
        <th>New Signup</th>
        <th>Unsubscribe</th>
        <th>% Change</th>
        <th>Status</th>
    </tr>
    <?php foreach ($reportData as $row): ?>
    <tr>
        <td><b><?php echo $row['country']; ?></b></td>
        <td style="text-align: center;"><?php echo number_format($row['active']); ?></td>
        <td style="text-align: center; color: green;"><?php echo $row['signup'] > 0 ? '+' . $row['signup'] : '0'; ?></td>
        <td style="text-align: center; color: red;"><?php echo $row['drop'] > 0 ? '-' . $row['drop'] : '0'; ?></td>
        <td style="text-align: center;"><?php echo ($row['percentChange'] >= 0 ? '+' : '') . $row['percentChange']; ?>%</td>
        <td style="text-align: center; color: <?php echo $row['statusColor']; ?>; font-weight: bold;"><?php echo $row['status']; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td>Total</td>
        <td style="text-align: center;"><?php echo number_format($totalActive); ?></td>
        <td style="text-align: center; color: green;"><?php echo $totalSignup > 0 ? '+' . $totalSignup : '0'; ?></td>
        <td style="text-align: center; color: red;"><?php echo $totalDrop > 0 ? '-' . $totalDrop : '0'; ?></td>
        <td style="text-align: center;"><?php echo ($totalPercentChange >= 0 ? '+' : '') . $totalPercentChange; ?>%</td>
        <td style="text-align: center; color: <?php echo $totalStatusColor; ?>;"><?php echo $totalStatus; ?></td>
    </tr>
</table>

<!-- Product Popularity Table -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity</b><br>
    <small>Products signed up: <?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th>#</th>
        <th>Product</th>
        <th>Country</th>
        <th>Signups</th>
    </tr>
    <?php 
    $rank = 1;
    $totalProducts = 0;
    foreach ($productPopularity as $item) {
        $totalProducts += $item['count'];
    }
    foreach ($productPopularity as $item): 
    ?>
    <tr>
        <td style="text-align: center;"><?php echo $rank++; ?></td>
        <td><b><?php echo $item['product']; ?></b></td>
        <td style="text-align: center;"><?php echo $item['country']; ?></td>
        <td style="text-align: center;"><?php echo $item['count']; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td colspan="3">Total</td>
        <td style="text-align: center;"><?php echo $totalProducts; ?></td>
    </tr>
</table>
