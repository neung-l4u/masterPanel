<?php
require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';

$day = !empty($_GET['day']) ? $_GET['day'] : date('Y-m-d');

// Calculate month range
$obj_day = new DateTime($day);
$startDate = $obj_day->format('Y-m-01 00:00:00');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->format('Y-m-t 23:59:59');

// Calculate previous month range
$obj_prevMonth = new DateTime($startDate);
$obj_prevMonth->modify('-1 month');
$prevStartDate = $obj_prevMonth->format('Y-m-01 00:00:00');
$prevEndDate = $obj_prevMonth->format('Y-m-t 23:59:59');

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
$cancelledLastMonth = []; // Accounts cancelled during previous month (to calculate Previous Active)
$processedAccounts = []; // Track counted Account Names (by country)
$cancelledLastMonthAccounts = []; // Track unique cancelled accounts (by country)
$firstDayOfCurrentMonth = date('Y-m-01'); // For Cancellation Date check
$firstDayOfPrevMonth = (new DateTime($firstDayOfCurrentMonth))->modify('-1 month')->format('Y-m-d');

// Check JSON structure (supports both old and new format)
if (isset($jsonData['data']['items'])) {
    // New structure: items array with column_values
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $activeStatus = '';
        $projectStage = '';
        $accountName = '';
        $dateValue = '';
        
        // Extract values from column_values
        // status = Active Subscription, status0 = Currency, lookup_mkwh1gcr = Project Stage, text9 = Account Name, date = Cancellation Date
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
            if ($col['id'] === 'date') {
                $dateValue = $col['text'] ?? '';
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
        if (!isset($cancelledLastMonth[$country])) {
            $cancelledLastMonth[$country] = 0;
            $cancelledLastMonthAccounts[$country] = [];
        }
        
        $cancelDate = !empty($dateValue) ? substr($dateValue, 0, 10) : '';
        
        // Count Active (This Month): cancelDate != first day of current month
        // Unique by text9 (Account Name)
        if (($activeStatus === 'Active Subscription' || $activeStatus === 'active' || $activeStatus === 'Request Cancellation') && $projectStage === 'Completed' && $cancelDate !== $firstDayOfCurrentMonth) {
            if (!empty($accountName) && !in_array($accountName, $processedAccounts[$country])) {
                $mondayData[$country]['active']++;
                $processedAccounts[$country][] = $accountName;
            } elseif (empty($accountName)) {
                $mondayData[$country]['active']++;
            }
        }
        
        // Count cancelled during previous month: cancelDate >= firstDayOfPrevMonth AND cancelDate < firstDayOfCurrentMonth
        // These accounts were active last month but cancelled since then
        // Previous Month Active = Current Active + cancelledLastMonth
        if ($projectStage === 'Completed' && $cancelDate >= $firstDayOfPrevMonth && $cancelDate < $firstDayOfCurrentMonth) {
            if (!empty($accountName) && !in_array($accountName, $cancelledLastMonthAccounts[$country])) {
                $cancelledLastMonth[$country]++;
                $cancelledLastMonthAccounts[$country][] = $accountName;
            } elseif (empty($accountName)) {
                $cancelledLastMonth[$country]++;
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

// ========== 3. Get Cancellation data from Database (Monthly Drop) ==========
$cancellations = $db->query('SELECT county FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $startDate, $endDate)->fetchAll();

$monthlyDrop = [];
foreach ($cancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($monthlyDrop[$country])) {
        $monthlyDrop[$country] = 0;
    }
    $monthlyDrop[$country]++;
}

// ========== 4. Get Signup data from Database (Monthly Signup) ==========
$signups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $startDate, $endDate)->fetchAll();

$monthlySignup = [];
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
    
    if (!isset($monthlySignup[$country])) {
        $monthlySignup[$country] = 0;
    }
    $monthlySignup[$country]++;
    
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

// ========== 5. Get previous month data ==========
// Previous month cancellations
$prevCancellations = $db->query('SELECT county FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $prevStartDate, $prevEndDate)->fetchAll();

$prevMonthlyDrop = [];
foreach ($prevCancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($prevMonthlyDrop[$country])) {
        $prevMonthlyDrop[$country] = 0;
    }
    $prevMonthlyDrop[$country]++;
}

// Previous month signups
$prevSignups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $prevStartDate, $prevEndDate)->fetchAll();

$prevMonthlySignup = [];
$prevProductPopularity = [];
$processedShops = [];
foreach ($prevSignups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'];
    $country = $dataLogs['Country'] ?: 'Unknown';
    $product = $dataLogs['MainProduct'] ?: 'Unknown';
    
    if (in_array($shopName, $processedShops)) {
        continue;
    }
    $processedShops[] = $shopName;
    
    if (!isset($prevMonthlySignup[$country])) {
        $prevMonthlySignup[$country] = 0;
    }
    $prevMonthlySignup[$country]++;
    
    // Track previous month product data by country
    $key = $product . '|' . $country;
    if (!isset($prevProductPopularity[$key])) {
        $prevProductPopularity[$key] = ['product' => $product, 'country' => $country, 'count' => 0];
    }
    $prevProductPopularity[$key]['count']++;
}

// Sort previous month products by popularity
usort($prevProductPopularity, function($a, $b) {
    return $b['count'] - $a['count'];
});

// ========== 6. Aggregate data and calculate ==========
$allCountries = array_unique(array_merge(
    array_keys($mondayData),
    array_keys($monthlyDrop),
    array_keys($monthlySignup)
));
sort($allCountries);

$reportData = [];
$totalActive = 0;
$totalDrop = 0;
$totalSignup = 0;

foreach ($allCountries as $country) {
    $active = isset($mondayData[$country]) ? $mondayData[$country]['active'] : 0;
    $drop = isset($monthlyDrop[$country]) ? $monthlyDrop[$country] : 0;
    $signup = isset($monthlySignup[$country]) ? $monthlySignup[$country] : 0;
    
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

<!-- <p style="font: 14px roboto, sans-serif; margin-bottom: 10px;">
    <b>Monthly Report</b><br>
    <small>This Month: /*<?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small>
</p> -->

<!-- <table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
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
</table> -->

<!-- Month-over-Month Comparison Table -->
<p style="font: 14px roboto, sans-serif; margin-bottom: 10px;">
    <b>Month-over-Month Comparison</b><br>
    <small>Previous Month: <?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?></small><br>
    <small>This Month: <?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?></small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th rowspan="2">Country</th>
        <th colspan="3">Previous Month</th>
        <th colspan="3">This Month</th>
        <th colspan="3">Change</th>
    </tr>
    <tr style="background-color: #e8f0f8;">
        <th>Active</th>
        <th>Signup</th>
        <th>Unsub</th>
        <th>Active</th>
        <th>Signup</th>
        <th>Unsub</th>
        <th>Active</th>
        <th>Signup</th>
        <th>Unsub</th>
    </tr>
    <?php 
    $totalPrevSignup = 0;
    $totalPrevDrop = 0;
    $totalPrevActive = 0;
    $totalCurrSignup = 0;
    $totalCurrDrop = 0;
    $totalCurrActive = 0;
    
    foreach ($allCountries as $country): 
        $currActive = isset($mondayData[$country]) ? $mondayData[$country]['active'] : 0;
        $cancelled = isset($cancelledLastMonth[$country]) ? $cancelledLastMonth[$country] : 0;
        $prevActive = $currActive + $cancelled; // Previous Active = Current Active + cancelled during previous month
        $prevSignup = isset($prevMonthlySignup[$country]) ? $prevMonthlySignup[$country] : 0;
        $prevDrop = isset($prevMonthlyDrop[$country]) ? $prevMonthlyDrop[$country] : 0;
        $currSignup = isset($monthlySignup[$country]) ? $monthlySignup[$country] : 0;
        $currDrop = isset($monthlyDrop[$country]) ? $monthlyDrop[$country] : 0;
        
        $activeDiff = $currActive - $prevActive;
        $signupDiff = $currSignup - $prevSignup;
        $dropDiff = $currDrop - $prevDrop;
        
        $totalPrevActive += $prevActive;
        $totalPrevSignup += $prevSignup;
        $totalPrevDrop += $prevDrop;
        $totalCurrActive += $currActive;
        $totalCurrSignup += $currSignup;
        $totalCurrDrop += $currDrop;
    ?>
    <tr>
        <td><b><?php echo $country; ?></b></td>
        <td style="text-align: center;"><?php echo number_format($prevActive); ?></td>
        <td style="text-align: center;"><?php echo $prevSignup; ?></td>
        <td style="text-align: center;"><?php echo $prevDrop; ?></td>
        <td style="text-align: center;"><?php echo number_format($currActive); ?></td>
        <td style="text-align: center;"><?php echo $currSignup; ?></td>
        <td style="text-align: center;"><?php echo $currDrop; ?></td>
        <td style="text-align: center; color: <?php echo $activeDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($activeDiff >= 0 ? '+' : '') . $activeDiff; ?></td>
        <td style="text-align: center; color: <?php echo $signupDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($signupDiff >= 0 ? '+' : '') . $signupDiff; ?></td>
        <td style="text-align: center; color: <?php echo $dropDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($dropDiff >= 0 ? '+' : '') . $dropDiff; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td>Total Balance</td>
        <td style="text-align: center;"><?php echo number_format($totalPrevActive); ?></td>
        <td style="text-align: center;"><?php echo $totalPrevSignup; ?></td>
        <td style="text-align: center;"><?php echo $totalPrevDrop; ?></td>
        <td style="text-align: center;"><?php echo number_format($totalCurrActive); ?></td>
        <td style="text-align: center;"><?php echo $totalCurrSignup; ?></td>
        <td style="text-align: center;"><?php echo $totalCurrDrop; ?></td>
        <?php 
        $totalActiveDiff = $totalCurrActive - $totalPrevActive;
        $totalSignupDiff = $totalCurrSignup - $totalPrevSignup;
        $totalDropDiff = $totalCurrDrop - $totalPrevDrop;
        ?>
        <td style="text-align: center; color: <?php echo $totalActiveDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($totalActiveDiff >= 0 ? '+' : '') . $totalActiveDiff; ?></td>
        <td style="text-align: center; color: <?php echo $totalSignupDiff >= 0 ? 'green' : 'red'; ?>;"><?php echo ($totalSignupDiff >= 0 ? '+' : '') . $totalSignupDiff; ?></td>
        <td style="text-align: center; color: <?php echo $totalDropDiff <= 0 ? 'green' : 'red'; ?>;"><?php echo ($totalDropDiff >= 0 ? '+' : '') . $totalDropDiff; ?></td>
    </tr>
</table>

<!-- Product Popularity This Month (Grouped by Country) -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity This Month</b><br>
    <small>Products signed up by customers this month (<?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?>)</small>
</p>

<?php
// Group current month products by country
$currByCountry = [];
$totalProducts = 0;
foreach ($productPopularity as $item) {
    $c = $item['country'];
    if (!isset($currByCountry[$c])) {
        $currByCountry[$c] = [];
    }
    $currByCountry[$c][] = $item;
    $totalProducts += $item['count'];
}
ksort($currByCountry);
?>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th>#</th>
        <th>Product</th>
        <th>Signups</th>
    </tr>
    <?php foreach ($currByCountry as $countryName => $products): 
        $countryTotal = 0;
        foreach ($products as $p) { $countryTotal += $p['count']; }
    ?>
    <tr style="background-color: #e8f0f8;">
        <td colspan="3" style="font-weight: bold;"><?php echo $countryName; ?> (<?php echo $countryTotal; ?>)</td>
    </tr>
    <?php 
        $countryRank = 1;
        foreach ($products as $item): 
    ?>
    <tr>
        <td style="text-align: center;"><?php echo $countryRank++; ?></td>
        <td><b><?php echo $item['product']; ?></b></td>
        <td style="text-align: center;"><?php echo $item['count']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td colspan="2">Total</td>
        <td style="text-align: center;"><?php echo $totalProducts; ?></td>
    </tr>
</table>

<!-- Product Popularity Previous Month (Grouped by Country) -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity Previous Month</b><br>
    <small>Products signed up by customers previous month (<?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?>)</small>
</p>

<?php
// Group previous month products by country
$prevByCountry = [];
$totalPrevProducts = 0;
foreach ($prevProductPopularity as $item) {
    $c = $item['country'];
    if (!isset($prevByCountry[$c])) {
        $prevByCountry[$c] = [];
    }
    $prevByCountry[$c][] = $item;
    $totalPrevProducts += $item['count'];
}
ksort($prevByCountry);
?>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th>#</th>
        <th>Product</th>
        <th>Signups</th>
    </tr>
    <?php foreach ($prevByCountry as $countryName => $products): 
        $countryTotal = 0;
        foreach ($products as $p) { $countryTotal += $p['count']; }
    ?>
    <tr style="background-color: #e8f0f8;">
        <td colspan="3" style="font-weight: bold;"><?php echo $countryName; ?> (<?php echo $countryTotal; ?>)</td>
    </tr>
    <?php 
        $countryRank = 1;
        foreach ($products as $item): 
    ?>
    <tr>
        <td style="text-align: center;"><?php echo $countryRank++; ?></td>
        <td><b><?php echo $item['product']; ?></b></td>
        <td style="text-align: center;"><?php echo $item['count']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td colspan="2">Total</td>
        <td style="text-align: center;"><?php echo $totalPrevProducts; ?></td>
    </tr>
</table>
