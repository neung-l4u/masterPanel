<?php
/**
 * Date Range Report endpoint
 *
 * Returns aggregated stats for a date range (from `start` to `end` inclusive),
 * shaped to match the weekly/monthly/yearly JSON consumed by pages/report.php.
 *
 * Query params:
 *   start  = YYYY-MM-DD  (range start; inclusive)
 *   end    = YYYY-MM-DD  (range end; inclusive)
 *   day    = YYYY-MM-DD  (legacy: single day; used when start/end omitted)
 *   format = json        (return JSON)
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';
require_once __DIR__ . '/../../pickSnapshot.php';

if (!empty($_GET['start']) && !empty($_GET['end'])) {
    $startParam = $_GET['start'];
    $endParam   = $_GET['end'];
} else {
    // Legacy single-day mode
    $day = !empty($_GET['day']) ? $_GET['day'] : date('Y-m-d');
    $startParam = $endParam = $day;
}

// Ensure start <= end (swap if user picked them in reverse order)
if (strtotime($startParam) > strtotime($endParam)) {
    $tmp = $startParam; $startParam = $endParam; $endParam = $tmp;
}

// Full-day range covering start..end in local time
$startDate = (new DateTime($startParam))->format('Y-m-d 00:00:00');
$endDate   = (new DateTime($endParam))->format('Y-m-d 23:59:59');

// ========== 1. Read latest Monday.com JSON (shared with Weekly) ==========
$jsonDir = __DIR__ . '/../../selectWeek/file/ALL/';
$files = glob($jsonDir . 'ALL_monday_data*.json');

if (empty($files)) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Monday JSON file not found']);
    exit;
}
// Pick snapshot whose timestamp is closest to (but not after) the selected day
// so Active reflects Monday state at end-of-day, matching Weekly email behavior.
$latestFile = pickSnapshotForPeriod($files, $endDate);
$jsonData = json_decode(file_get_contents($latestFile), true);

// ========== 2. Parse Monday data by country (Active) ==========
$currencyMapping = [
    'thb' => 'TH', 'aud' => 'AU', 'nzd' => 'NZ',
    'gbp' => 'UK', 'usd' => 'US', 'cad' => 'CA'
];

$mondayData = [];
$processedAccounts = [];
$activeByType = [];
$activeByTypeAccounts = [];

if (isset($jsonData['data']['items'])) {
    foreach ($jsonData['data']['items'] as $item) {
        $currency = $activeStatus = $projectStage = $accountName = $customerType = '';

        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'status0') $currency = $col['text'];
            if ($col['id'] === 'status')  $activeStatus = $col['text'];
            if ($col['id'] === 'lookup_mkwh1gcr') {
                $projectStage = !empty($col['display_value']) ? $col['display_value'] : $col['text'];
            }
            if ($col['id'] === 'text9')  $accountName = $col['text'] ?? '';
            if ($col['id'] === 'mirror') {
                $customerType = !empty($col['display_value']) ? $col['display_value'] : ($col['text'] ?? '');
            }
        }

        $country = null;
        if (!empty($accountName) && preg_match('/- (AU|NZ|UK|USA|US|TH|CA)$/i', $accountName, $m)) {
            $country = strtoupper($m[1]);
            if ($country === 'USA') $country = 'US';
        }
        if (!$country) {
            $cLower = strtolower($currency ?? '');
            $country = isset($currencyMapping[$cLower]) ? $currencyMapping[$cLower] : null;
        }
        if (!$country) continue;

        if (!isset($mondayData[$country])) {
            $mondayData[$country] = ['active' => 0];
            $processedAccounts[$country] = [];
        }

        if (($activeStatus === 'Active Subscription' || $activeStatus === 'active' || $activeStatus === 'Request Cancellation')
            && $projectStage === 'Completed') {
            if (!empty($accountName) && !in_array($accountName, $processedAccounts[$country])) {
                $mondayData[$country]['active']++;
                $processedAccounts[$country][] = $accountName;
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
}

// ========== 3. Cancellations in this day ==========
$cancellations = $db->query(
    'SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?',
    $startDate, $endDate
)->fetchAll();

$dailyDrop = [];
$unsubByType = [];
foreach ($cancellations as $row) {
    $country = $row['county'] ?: 'Unknown';
    if (!isset($dailyDrop[$country])) $dailyDrop[$country] = 0;
    $dailyDrop[$country]++;
    $type = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
    if (!isset($unsubByType[$type])) $unsubByType[$type] = 0;
    $unsubByType[$type]++;
}

// ========== 4. Signups in this day ==========
$signups = $db->query(
    'SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0',
    $startDate, $endDate
)->fetchAll();

$dailySignup = [];
$signupByType = [];
$processedShops = [];
foreach ($signups as $row) {
    $dataLogs = json_decode($row['dataLogs'], true);
    $shopName = $dataLogs['ShopName'] ?? '';
    $country  = $dataLogs['Country']  ?: 'Unknown';
    $custType = $dataLogs['CustomerType'] ?? 'Unknown';

    if ($shopName !== '' && in_array($shopName, $processedShops)) continue;
    $processedShops[] = $shopName;

    if (!isset($dailySignup[$country])) $dailySignup[$country] = 0;
    $dailySignup[$country]++;

    $ct = !empty($custType) ? $custType : 'Unknown';
    if (!isset($signupByType[$ct])) $signupByType[$ct] = 0;
    $signupByType[$ct]++;
}

// ========== 5. Aggregate per country ==========
$allCountries = array_unique(array_merge(
    array_keys($mondayData),
    array_keys($dailyDrop),
    array_keys($dailySignup)
));
sort($allCountries);

$reportData = [];
$totalActive = $totalDrop = $totalSignup = 0;

foreach ($allCountries as $country) {
    $active = isset($mondayData[$country]) ? $mondayData[$country]['active'] : 0;
    $drop   = isset($dailyDrop[$country])  ? $dailyDrop[$country]  : 0;
    $signup = isset($dailySignup[$country]) ? $dailySignup[$country] : 0;

    $netChange = $signup - $drop;
    $percentChange = $active > 0 ? round(($netChange / $active) * 100, 2) : 0;

    if ($percentChange >= 5)        { $status = 'High';     $statusColor = 'green'; }
    elseif ($percentChange >= 0)    { $status = 'Mid';      $statusColor = 'orange'; }
    elseif ($percentChange >= -10)  { $status = 'Low';      $statusColor = 'red'; }
    else                            { $status = 'Very Low'; $statusColor = 'darkred'; }

    $reportData[] = [
        'country' => $country,
        'active'  => $active,
        'signup'  => $signup,
        'drop'    => $drop,
        'percentChange' => $percentChange,
        'status'        => $status,
        'statusColor'   => $statusColor,
    ];

    $totalActive += $active;
    $totalDrop   += $drop;
    $totalSignup += $signup;
}

$totalNetChange    = $totalSignup - $totalDrop;
$totalPercentChange = $totalActive > 0 ? round(($totalNetChange / $totalActive) * 100, 2) : 0;

if     ($totalPercentChange >= 5)   { $totalStatus = 'High'; }
elseif ($totalPercentChange >= 0)   { $totalStatus = 'Mid'; }
elseif ($totalPercentChange >= -10) { $totalStatus = 'Low'; }
else                                { $totalStatus = 'Very Low'; }

// ========== 6. Output JSON ==========
ob_end_clean();
header('Content-Type: application/json');
echo json_encode([
    'period'       => ['start' => $startDate, 'end' => $endDate],
    'reportData'   => $reportData,
    'totals'       => [
        'active'        => $totalActive,
        'signup'        => $totalSignup,
        'drop'          => $totalDrop,
        'percentChange' => $totalPercentChange,
        'status'        => $totalStatus,
    ],
    'customerType' => $activeByType,
    'signupByType' => $signupByType,
    'unsubByType'  => $unsubByType,
], JSON_UNESCAPED_UNICODE);
exit;
