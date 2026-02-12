<?php
require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';

$day = !empty($_GET['day']) ? $_GET['day'] : date('Y-m-d');

// Calculate year range
$obj_day = new DateTime($day);
$startDate = $obj_day->format('Y-01-01 00:00:00');
$endDate = $obj_day->format('Y-12-31 23:59:59');

// Calculate previous year range
$obj_prevYear = new DateTime($startDate);
$obj_prevYear->modify('-1 year');
$prevStartDate = $obj_prevYear->format('Y-01-01 00:00:00');
$prevEndDate = $obj_prevYear->format('Y-12-31 23:59:59');

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
$cancelledLastYear = []; // Accounts cancelled during previous year (to calculate Previous Active)
$processedAccounts = []; // Track counted Account Names (by country)
$cancelledLastYearAccounts = []; // Track unique cancelled accounts (by country)
$activeByType = []; // Customer Type from mirror column
$activeByTypeAccounts = []; // Track unique accounts per type
$firstDayOfCurrentYear = date('Y-01-01'); // For Cancellation Date check
$firstDayOfPrevYear = (new DateTime($firstDayOfCurrentYear))->modify('-1 year')->format('Y-01-01');

// Check JSON structure (supports both old and new format)
if (isset($jsonData['data']['items'])) {
    // New structure: items array with column_values
    foreach ($jsonData['data']['items'] as $item) {
        $currency = '';
        $activeStatus = '';
        $projectStage = '';
        $accountName = '';
        $dateValue = '';
        $customerType = '';
        
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
        if (!isset($cancelledLastYear[$country])) {
            $cancelledLastYear[$country] = 0;
            $cancelledLastYearAccounts[$country] = [];
        }
        
        $cancelDate = !empty($dateValue) ? substr($dateValue, 0, 10) : '';
        
        // Count Active (This Year): cancelDate != first day of current year
        // Unique by text9 (Account Name)
        if (($activeStatus === 'Active Subscription' || $activeStatus === 'active' || $activeStatus === 'Request Cancellation') && $projectStage === 'Completed' && $cancelDate !== $firstDayOfCurrentYear) {
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
        
        // Count cancelled during previous year: cancelDate >= firstDayOfPrevYear AND cancelDate < firstDayOfCurrentYear
        // These accounts were active last year but cancelled since then
        // Previous Year Active = Current Active + cancelledLastYear
        if ($projectStage === 'Completed' && $cancelDate >= $firstDayOfPrevYear && $cancelDate < $firstDayOfCurrentYear) {
            if (!empty($accountName) && !in_array($accountName, $cancelledLastYearAccounts[$country])) {
                $cancelledLastYear[$country]++;
                $cancelledLastYearAccounts[$country][] = $accountName;
            } elseif (empty($accountName)) {
                $cancelledLastYear[$country]++;
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

// ========== 3. Get Cancellation data from Database (Yearly Drop) ==========
$cancellations = $db->query('SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $startDate, $endDate)->fetchAll();

$yearlyDrop = [];
$unsubByType = [];
foreach ($cancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($yearlyDrop[$country])) {
        $yearlyDrop[$country] = 0;
    }
    $yearlyDrop[$country]++;
    $type = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
    if (!isset($unsubByType[$type])) $unsubByType[$type] = 0;
    $unsubByType[$type]++;
}

// ========== 4. Get Signup data from Database (Yearly Signup) ==========
$signups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $startDate, $endDate)->fetchAll();

$yearlySignup = [];
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
    
    if (!isset($yearlySignup[$country])) {
        $yearlySignup[$country] = 0;
    }
    $yearlySignup[$country]++;
    
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

// ========== 5. Get previous year data ==========
// Previous year cancellations
$prevCancellations = $db->query('SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?', $prevStartDate, $prevEndDate)->fetchAll();

$prevYearlyDrop = [];
$prevUnsubByType = [];
foreach ($prevCancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($prevYearlyDrop[$country])) {
        $prevYearlyDrop[$country] = 0;
    }
    $prevYearlyDrop[$country]++;
    $type = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
    if (!isset($prevUnsubByType[$type])) $prevUnsubByType[$type] = 0;
    $prevUnsubByType[$type]++;
}

// Previous year signups
$prevSignups = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0', $prevStartDate, $prevEndDate)->fetchAll();

$prevYearlySignup = [];
$prevProductPopularity = [];
$processedShops = [];
$prevSignupByType = [];
foreach ($prevSignups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'];
    $country = $dataLogs['Country'] ?: 'Unknown';
    $product = $dataLogs['MainProduct'] ?: 'Unknown';
    $custType = $dataLogs['CustomerType'] ?? 'Unknown';
    
    if (in_array($shopName, $processedShops)) {
        continue;
    }
    $processedShops[] = $shopName;
    
    if (!isset($prevYearlySignup[$country])) {
        $prevYearlySignup[$country] = 0;
    }
    $prevYearlySignup[$country]++;
    
    $ct = !empty($custType) ? $custType : 'Unknown';
    if (!isset($prevSignupByType[$ct])) $prevSignupByType[$ct] = 0;
    $prevSignupByType[$ct]++;
    
    // Track previous year product data by country
    $key = $product . '|' . $country;
    if (!isset($prevProductPopularity[$key])) {
        $prevProductPopularity[$key] = ['product' => $product, 'country' => $country, 'count' => 0];
    }
    $prevProductPopularity[$key]['count']++;
}

// Sort previous year products by popularity
usort($prevProductPopularity, function($a, $b) {
    return $b['count'] - $a['count'];
});

// ========== 6. Aggregate data and calculate ==========
$allCountries = array_unique(array_merge(
    array_keys($mondayData),
    array_keys($yearlyDrop),
    array_keys($yearlySignup)
));
sort($allCountries);
?>

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
<!-- <p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
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
</table> -->

<!-- Year-over-Year Comparison Table -->
<p style="font: 14px roboto, sans-serif; margin-bottom: 10px;">
    <b>Year-over-Year Comparison</b><br>
    <small>Previous Year: <?php echo date('Y', strtotime($prevStartDate)); ?> (<?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?>)</small><br>
    <small>This Year: <?php echo date('Y', strtotime($startDate)); ?> (<?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?>)</small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th rowspan="2">Country</th>
        <th colspan="3">Previous Year</th>
        <th colspan="3">This Year</th>
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
        $cancelled = isset($cancelledLastYear[$country]) ? $cancelledLastYear[$country] : 0;
        $prevActive = $currActive + $cancelled; // Previous Active = Current Active + cancelled during previous year
        $prevSignup = isset($prevYearlySignup[$country]) ? $prevYearlySignup[$country] : 0;
        $prevDrop = isset($prevYearlyDrop[$country]) ? $prevYearlyDrop[$country] : 0;
        $currSignup = isset($yearlySignup[$country]) ? $yearlySignup[$country] : 0;
        $currDrop = isset($yearlyDrop[$country]) ? $yearlyDrop[$country] : 0;
        
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

<!-- Customer Type Year-over-Year Comparison Table -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Customer Type Year-over-Year</b><br>
    <small>Previous Year: <?php echo date('Y', strtotime($prevStartDate)); ?> (<?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?>)</small><br>
    <small>This Year: <?php echo date('Y', strtotime($startDate)); ?> (<?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?>)</small>
</p>

<table cellpadding="8" cellspacing="0" border="1" style="font: 13px roboto, sans-serif; border-collapse: collapse;">
    <tr style="background-color: #d6e6f4;">
        <th rowspan="2">Customer Type</th>
        <th colspan="2">Previous Year</th>
        <th colspan="2">This Year</th>
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

<!-- Product Popularity This Year (Grouped by Country) -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity This Year (Top 3 in each country)</b><br>
    <small>Products signed up by customers this year (<?php echo date('Y-m-d', strtotime($startDate)); ?> - <?php echo date('Y-m-d', strtotime($endDate)); ?>)</small>
</p>

<?php
// Group current year products by country
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
        $top3 = array_slice($products, 0, 3);
        foreach ($top3 as $item): 
    ?>
    <tr>
        <td style="text-align: center;"><?php echo $countryRank++; ?></td>
        <td><b><?php echo $item['product']; ?></b></td>
        <td style="text-align: center;"><?php echo $item['count']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
    <!-- <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td colspan="2">Total</td>
        <td style="text-align: center;"><?php echo $totalProducts; ?>
    </tr> -->
</table>

<!-- Product Popularity Previous Year (Grouped by Country) -->
<p style="font: 14px roboto, sans-serif; margin-top: 30px; margin-bottom: 10px;">
    <b>Product Popularity Previous Year (Top 3 in each country)</b><br>
    <small>Products signed up by customers previous year (<?php echo date('Y-m-d', strtotime($prevStartDate)); ?> - <?php echo date('Y-m-d', strtotime($prevEndDate)); ?>)</small>
</p>

<?php
// Group previous year products by country
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
        $top3 = array_slice($products, 0, 3);
        foreach ($top3 as $item): 
    ?>
    <tr>
        <td style="text-align: center;"><?php echo $countryRank++; ?></td>
        <td><b><?php echo $item['product']; ?></b></td>
        <td style="text-align: center;"><?php echo $item['count']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
    <!-- <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td colspan="2">Total</td>
        <td style="text-align: center;"><?php echo $totalPrevProducts; ?></td>
    </tr> -->
</table>

