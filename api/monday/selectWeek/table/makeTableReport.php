<?php
require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';

$day = !empty($_GET['day']) ? $_GET['day'] : date('Y-m-d');

// Calculate week range
$obj_day = new DateTime($day);
$dateIndex = $obj_day->format('w');
$endDateDiff = 6 - $dateIndex;

$obj_startDate = new DateTime($day);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d 00:00:00');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->modify("+$endDateDiff days")->format('Y-m-d 23:59:59');

// Calculate previous week range
$obj_prevStartDate = new DateTime($startDate);
$prevStartDate = $obj_prevStartDate->modify('-7 days')->format('Y-m-d 00:00:00');

$obj_prevEndDate = new DateTime($endDate);
$prevEndDate = $obj_prevEndDate->modify('-7 days')->format('Y-m-d 23:59:59');

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
// Currency to Country mapping (lowercase)
$currencyMapping = [
    'thb' => 'TH',
    'aud' => 'AU',
    'nzd' => 'NZ',
    'gbp' => 'UK',
    'usd' => 'US',
    'cad' => 'CA'
];

$mondayData = [];
$processedAccounts = []; // Track counted Account Names (by country)
$activeByType = []; // Customer Type from mirror column
$activeByTypeAccounts = []; // Track unique accounts per type

// Check JSON structure (supports both old and new format)
if (isset($jsonData['data']['items'])) {
    // New structure: items array with column_values
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $activeStatus = '';
        $projectStage = '';
        $accountName = '';
        $customerType = '';
        
        // Extract values from column_values
        // status = Active Subscription, status0 = Currency, lookup_mkwh1gcr = Project Stage, text9 = Account Name
        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'status0') {
                $currency = $col['text'];
            }
            if ($col['id'] === 'status') {
                $activeStatus = $col['text'];
            }
            if ($col['id'] === 'lookup_mkwh1gcr') {
                // Support both text and display_value (MirrorValue)
                $projectStage = !empty($col['display_value']) ? $col['display_value'] : $col['text'];
            }
            if ($col['id'] === 'text9') {
                $accountName = $col['text'] ?? '';
            }
            if ($col['id'] === 'mirror') {
                $customerType = !empty($col['display_value']) ? $col['display_value'] : ($col['text'] ?? '');
            }
        }
        
        // Check Country from text9 (Account Name) first
        $country = null;
        if (!empty($accountName) && preg_match('/- (AU|NZ|UK|USA|US|TH|CA)$/i', $accountName, $matches)) {
            $countryFromText = strtoupper($matches[1]);
            if ($countryFromText === 'USA') $countryFromText = 'US';
            $country = $countryFromText;
        }
        
        // Fallback: if no country from text9, use Currency (status0)
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
        // Unique by text9 (Account Name)
        if (($activeStatus === 'Active Subscription' || $activeStatus === 'active' || $activeStatus === 'Request Cancellation') && $projectStage === 'Completed') {
            if (!empty($accountName) && !in_array($accountName, $processedAccounts[$country])) {
                $mondayData[$country]['active']++;
                $processedAccounts[$country][] = $accountName;
                // Track Customer Type
                $type = !empty($customerType) ? $customerType : 'Unknown';
                if (!isset($activeByType[$type])) { $activeByType[$type] = 0; $activeByTypeAccounts[$type] = []; }
                if (!in_array($accountName, $activeByTypeAccounts[$type])) {
                    $activeByType[$type]++;
                    $activeByTypeAccounts[$type][] = $accountName;
                }
            } elseif (empty($accountName)) {
                $mondayData[$country]['active']++;
                $type = !empty($customerType) ? $customerType : 'Unknown';
                if (!isset($activeByType[$type])) $activeByType[$type] = 0;
                $activeByType[$type]++;
            }
        }
        
        
    }
} else {
    // Old structure: boards > groups > items
    $boardMapping = [
        'Projects | TH' => 'TH',
        'Projects | CA' => 'CA',
        'Projects | UK' => 'UK',
        'Projects | USA' => 'US',
        'Projects | NZ' => 'NZ',
        'Projects | AU' => 'AU'
    ];
    
    $activeGroups = ['New Projects', 'Building', 'Final Check Pending', 'Ready to Go Live', 'Pause', 'Suspend', 'Completed Projects'];
    
    foreach ($jsonData['data']['boards'] as $board) {
        $boardName = $board['name'];
        $country = isset($boardMapping[$boardName]) ? $boardMapping[$boardName] : null;
        
        if (!$country) continue;
        
        $mondayData[$country] = ['active' => 0];
        
        foreach ($board['groups'] as $group) {
            $groupTitle = $group['title'];
            $itemCount = count($group['items_page']['items']);
            
            if (in_array($groupTitle, $activeGroups)) {
                $mondayData[$country]['active'] += $itemCount;
            }
        }
    }
}

// ========== 3. Get Cancellation data from Database (Weekly Drop) ==========
$cancellations = $db->query('SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $startDate, $endDate)->fetchAll();

$weeklyDrop = [];
$unsubByType = [];
foreach ($cancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($weeklyDrop[$country])) {
        $weeklyDrop[$country] = 0;
    }
    $weeklyDrop[$country]++;
    $type = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
    if (!isset($unsubByType[$type])) $unsubByType[$type] = 0;
    $unsubByType[$type]++;
}

// ========== 4. Get Signup data from Database (Weekly Signup) ==========
$signups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $startDate, $endDate)->fetchAll();

$weeklySignup = [];
$productPopularity = [];
$processedShops = [];
$signupByType = [];
foreach ($signups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'];
    $country = $dataLogs['Country'] ?: 'Unknown';
    $product = $dataLogs['MainProduct'] ?: 'Unknown';
    $custType = $dataLogs['CustomerType'] ?? 'Unknown';
    
    if (in_array($shopName, $processedShops)) {
        continue;
    }
    $processedShops[] = $shopName;
    
    if (!isset($weeklySignup[$country])) {
        $weeklySignup[$country] = 0;
    }
    $weeklySignup[$country]++;
    
    // Track signup by Customer Type
    $ct = !empty($custType) ? $custType : 'Unknown';
    if (!isset($signupByType[$ct])) $signupByType[$ct] = 0;
    $signupByType[$ct]++;
    
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

// ========== 5. Get previous week data ==========
// Previous week cancellations
$prevCancellations = $db->query('SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $prevStartDate, $prevEndDate)->fetchAll();

$prevWeeklyDrop = [];
$prevUnsubByType = [];
foreach ($prevCancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($prevWeeklyDrop[$country])) {
        $prevWeeklyDrop[$country] = 0;
    }
    $prevWeeklyDrop[$country]++;
    $type = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
    if (!isset($prevUnsubByType[$type])) $prevUnsubByType[$type] = 0;
    $prevUnsubByType[$type]++;
}

// Previous week signups
$prevSignups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $prevStartDate, $prevEndDate)->fetchAll();

$prevWeeklySignup = [];
$processedShops = [];
$prevSignupByType = [];
foreach ($prevSignups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'];
    $country = $dataLogs['Country'] ?: 'Unknown';
    $custType = $dataLogs['CustomerType'] ?? 'Unknown';
    
    if (in_array($shopName, $processedShops)) {
        continue;
    }
    $processedShops[] = $shopName;
    
    if (!isset($prevWeeklySignup[$country])) {
        $prevWeeklySignup[$country] = 0;
    }
    $prevWeeklySignup[$country]++;
    
    $ct = !empty($custType) ? $custType : 'Unknown';
    if (!isset($prevSignupByType[$ct])) $prevSignupByType[$ct] = 0;
    $prevSignupByType[$ct]++;
}

// ========== 6. Aggregate data and calculate ==========
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
    
    // Calculate % change
    $netChange = $signup - $drop;
    $percentChange = $active > 0 ? round(($netChange / $active) * 100, 2) : 0;
    
    // Determine Status
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

<?php
$allTypes = array_unique(array_merge(
    array_keys($activeByType),
    array_keys($signupByType),
    array_keys($unsubByType),
    array_keys($prevSignupByType),
    array_keys($prevUnsubByType)
));
$allTypes = array_filter($allTypes, function($t) { return $t !== 'Unknown'; });
sort($allTypes);
?>
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Customer Type</b><br>
    <small>Active from Monday.com (Board Active Subscription), Signup and unsub from Database</small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th>Customer Type</th>
        <th>Active</th>
        <th>New Signup</th>
        <th>Unsubscribe</th>
    </tr>
    <?php 
    $totalTypeActive = 0;
    $totalTypeSignup = 0;
    $totalTypeUnsub = 0;
    foreach ($allTypes as $type): 
        $tActive = isset($activeByType[$type]) ? $activeByType[$type] : 0;
        $tSignup = isset($signupByType[$type]) ? $signupByType[$type] : 0;
        $tUnsub = isset($unsubByType[$type]) ? $unsubByType[$type] : 0;
        $totalTypeActive += $tActive;
        $totalTypeSignup += $tSignup;
        $totalTypeUnsub += $tUnsub;
    ?>
    <tr>
        <td><b><?php echo $type; ?></b></td>
        <td style="text-align: center;"><?php echo number_format($tActive); ?></td>
        <td style="text-align: center; color: green;"><?php echo $tSignup > 0 ? '+' . $tSignup : '0'; ?></td>
        <td style="text-align: center; color: red;"><?php echo $tUnsub > 0 ? '-' . $tUnsub : '0'; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td>Total</td>
        <td style="text-align: center;"><?php echo number_format($totalTypeActive); ?></td>
        <td style="text-align: center; color: green;"><?php echo $totalTypeSignup > 0 ? '+' . $totalTypeSignup : '0'; ?></td>
        <td style="text-align: center; color: red;"><?php echo $totalTypeUnsub > 0 ? '-' . $totalTypeUnsub : '0'; ?></td>
    </tr>
</table>

<!-- Week-over-Week Comparison Table -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Week-over-Week Comparison</b><br>
    <small>Previous Week: <?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?></small><br>
    <small>This Week: <?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th rowspan="2">Country</th>
        <th colspan="2">Previous Week</th>
        <th colspan="2">This Week</th>
        <th colspan="2">Change</th>
    </tr>
    <tr style="background-color: #e8f0f8;">
        <th>Signup</th>
        <th>Unsub</th>
        <th>Signup</th>
        <th>Unsub</th>
        <th>Signup</th>
        <th>Unsub</th>
    </tr>
    <?php 
    $totalPrevSignup = 0;
    $totalPrevDrop = 0;
    $totalCurrSignup = 0;
    $totalCurrDrop = 0;
    
    foreach ($allCountries as $country): 
        $prevSignup = isset($prevWeeklySignup[$country]) ? $prevWeeklySignup[$country] : 0;
        $prevDrop = isset($prevWeeklyDrop[$country]) ? $prevWeeklyDrop[$country] : 0;
        $currSignup = isset($weeklySignup[$country]) ? $weeklySignup[$country] : 0;
        $currDrop = isset($weeklyDrop[$country]) ? $weeklyDrop[$country] : 0;
        
        $signupDiff = $currSignup - $prevSignup;
        $dropDiff = $currDrop - $prevDrop;
        
        $totalPrevSignup += $prevSignup;
        $totalPrevDrop += $prevDrop;
        $totalCurrSignup += $currSignup;
        $totalCurrDrop += $currDrop;
    ?>
    <tr>
        <td><b><?php echo $country; ?></b></td>
        <td style="text-align: center;"><?php echo $prevSignup; ?></td>
        <td style="text-align: center;"><?php echo $prevDrop; ?></td>
        <td style="text-align: center;"><?php echo $currSignup; ?></td>
        <td style="text-align: center;"><?php echo $currDrop; ?></td>
        <td style="text-align: center; color: <?php echo $signupDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($signupDiff >= 0 ? '+' : '') . $signupDiff; ?></td>
        <td style="text-align: center; color: <?php echo $dropDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($dropDiff >= 0 ? '+' : '') . $dropDiff; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td>Total Balance</td>
        <td style="text-align: center;"><?php echo $totalPrevSignup; ?></td>
        <td style="text-align: center;"><?php echo $totalPrevDrop; ?></td>
        <td style="text-align: center;"><?php echo $totalCurrSignup; ?></td>
        <td style="text-align: center;"><?php echo $totalCurrDrop; ?></td>
        <?php 
        $totalSignupDiff = $totalCurrSignup - $totalPrevSignup;
        $totalDropDiff = $totalCurrDrop - $totalPrevDrop;
        ?>
        <td style="text-align: center; color: <?php echo $totalSignupDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($totalSignupDiff >= 0 ? '+' : '') . $totalSignupDiff; ?></td>
        <td style="text-align: center; color: <?php echo $totalDropDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($totalDropDiff >= 0 ? '+' : '') . $totalDropDiff; ?></td>
    </tr>
</table>

<!-- Customer Type Week-over-Week Comparison Table -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Customer Type Week-over-Week</b><br>
    <small>Previous Week: <?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?></small><br>
    <small>This Week: <?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th rowspan="2">Customer Type</th>
        <th colspan="2">Previous Week</th>
        <th colspan="2">This Week</th>
        <th colspan="2">Change</th>
    </tr>
    <tr style="background-color: #e8f0f8;">
        <th>Signup</th>
        <th>Unsub</th>
        <th>Signup</th>
        <th>Unsub</th>
        <th>Signup</th>
        <th>Unsub</th>
    </tr>
    <?php 
    $tPrevSignup = 0; $tPrevUnsub = 0; $tCurrSignup = 0; $tCurrUnsub = 0;
    foreach ($allTypes as $type):
        $pSignup = isset($prevSignupByType[$type]) ? $prevSignupByType[$type] : 0;
        $pUnsub = isset($prevUnsubByType[$type]) ? $prevUnsubByType[$type] : 0;
        $cSignup = isset($signupByType[$type]) ? $signupByType[$type] : 0;
        $cUnsub = isset($unsubByType[$type]) ? $unsubByType[$type] : 0;
        $sDiff = $cSignup - $pSignup;
        $uDiff = $cUnsub - $pUnsub;
        $tPrevSignup += $pSignup; $tPrevUnsub += $pUnsub;
        $tCurrSignup += $cSignup; $tCurrUnsub += $cUnsub;
    ?>
    <tr>
        <td><b><?php echo $type; ?></b></td>
        <td style="text-align: center;"><?php echo $pSignup; ?></td>
        <td style="text-align: center;"><?php echo $pUnsub; ?></td>
        <td style="text-align: center;"><?php echo $cSignup; ?></td>
        <td style="text-align: center;"><?php echo $cUnsub; ?></td>
        <td style="text-align: center; color: <?php echo $sDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($sDiff >= 0 ? '+' : '') . $sDiff; ?></td>
        <td style="text-align: center; color: <?php echo $uDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($uDiff >= 0 ? '+' : '') . $uDiff; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td>Total Balance</td>
        <td style="text-align: center;"><?php echo $tPrevSignup; ?></td>
        <td style="text-align: center;"><?php echo $tPrevUnsub; ?></td>
        <td style="text-align: center;"><?php echo $tCurrSignup; ?></td>
        <td style="text-align: center;"><?php echo $tCurrUnsub; ?></td>
        <?php $tsDiff = $tCurrSignup - $tPrevSignup; $tuDiff = $tCurrUnsub - $tPrevUnsub; ?>
        <td style="text-align: center; color: <?php echo $tsDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($tsDiff >= 0 ? '+' : '') . $tsDiff; ?></td>
        <td style="text-align: center; color: <?php echo $tuDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($tuDiff >= 0 ? '+' : '') . $tuDiff; ?></td>
    </tr>
</table>

<!-- Product Popularity Table -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity This Week</b><br>
    <small>Products signed up by customers this week</small>
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


