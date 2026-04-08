<?php
require_once '../../../assets/db/db.php';
require_once '../../../assets/db/initDB.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'active';

// ============================================================
// TYPE: active — read latest JSON from selectProjectCountry
// ============================================================
if ($type === 'active') {
    $jsonDir = __DIR__ . '/../selectProjectCountry/file/ALL/';
    $files = glob($jsonDir . 'ALL_monday_data*.json');

    if (empty($files)) {
        echo json_encode(['error' => 'No JSON file found in selectProjectCountry/file/ALL/']);
        exit;
    }

    rsort($files);
    $latestFile = $files[0];
    $filename   = basename($latestFile);

    $jsonData = json_decode(file_get_contents($latestFile), true);
    if (!$jsonData) {
        echo json_encode(['error' => 'Failed to parse JSON file']);
        exit;
    }

    $countryMap = [
        'Thailand'       => 'TH',
        'Australia'      => 'AU',
        'New Zealand'    => 'NZ',
        'United Kingdom' => 'UK',
        'United States'  => 'US',
        'Canada'         => 'CA',
        'Norway'         => 'NW',
    ];

    $activeByCountry = [];
    $activeByType    = [];
    $totalActive     = 0;

    if (isset($jsonData['data']['items'])) {
        foreach ($jsonData['data']['items'] as $item) {
            $country  = '';
            $custType = '';

            foreach ($item['column_values'] as $col) {
                if ($col['id'] === 'country2') $country  = $col['text'] ?? '';
                if ($col['id'] === 'color1')   $custType = $col['text'] ?? '';
            }

            // Count only if has at least one "Active Subscription"
            $hasActive = false;
            if (!empty($item['column_active_subSubscription'])) {
                foreach ($item['column_active_subSubscription'] as $sub) {
                    if (($sub['status'] ?? '') === 'Active Subscription') {
                        $hasActive = true;
                        break;
                    }
                }
            }
            if (!$hasActive) continue;

            $code = isset($countryMap[$country]) ? $countryMap[$country] : ($country ?: 'Unknown');
            if (!isset($activeByCountry[$code])) $activeByCountry[$code] = 0;
            $activeByCountry[$code]++;

            $t = !empty($custType) ? $custType : 'Unknown';
            if (!isset($activeByType[$t])) $activeByType[$t] = 0;
            $activeByType[$t]++;

            $totalActive++;
        }
    }

    arsort($activeByCountry);
    arsort($activeByType);

    echo json_encode([
        'total'     => $totalActive,
        'byCountry' => $activeByCountry,
        'byType'    => $activeByType,
        'filename'  => $filename,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// TYPE: signupunsub — query DB for signup & unsub in a period
// ============================================================
if ($type === 'signupunsub') {
    $period = $_GET['period'] ?? 'week';
    $date   = $_GET['date']   ?? date('Y-m-d');

    if ($period === 'week') {
        $obj = new DateTime($date);
        $dow = (int)$obj->format('w');     // 0=Sun … 6=Sat
        $objS = new DateTime($date);
        $startDate = $objS->modify("-{$dow} day")->format('Y-m-d 00:00:00');
        $objE = new DateTime($date);
        $endDate   = $objE->modify('+' . (6 - $dow) . ' days')->format('Y-m-d 23:59:59');
        $periodLabel = 'Week';
    } else {
        // month: date is YYYY-MM
        if (strlen($date) === 7) $date .= '-01';
        $obj = new DateTime($date);
        $startDate   = $obj->format('Y-m-01 00:00:00');
        $endDate     = $obj->format('Y-m-t 23:59:59');
        $periodLabel = 'Month';
    }

    // --- Signup ---
    $signups = $db->query(
        'SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0',
        $startDate, $endDate
    )->fetchAll();

    $signupByCountry = [];
    $signupByType    = [];
    $signupDetail    = [];
    $processedShops  = [];
    $totalSignup     = 0;

    foreach ($signups as $row) {
        $dl       = json_decode($row['dataLogs'], true);
        $shopName = $dl['ShopName'] ?? '';
        $country  = $dl['Country']      ?: 'Unknown';
        $custType = $dl['CustomerType'] ?? 'Unknown';
        $product  = $dl['MainProduct']  ?? '';

        if (in_array($shopName, $processedShops)) continue;
        $processedShops[] = $shopName;

        if (!isset($signupByCountry[$country])) $signupByCountry[$country] = 0;
        $signupByCountry[$country]++;

        $ct = !empty($custType) ? $custType : 'Unknown';
        if (!isset($signupByType[$ct])) $signupByType[$ct] = 0;
        $signupByType[$ct]++;

        $signupDetail[] = ['shop' => $shopName, 'country' => $country, 'type' => $ct, 'product' => $product];
        $totalSignup++;
    }

    // --- Unsub ---
    $cancellations = $db->query(
        'SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?',
        $startDate, $endDate
    )->fetchAll();

    $unsubByCountry = [];
    $unsubByType    = [];
    $totalUnsub     = 0;

    foreach ($cancellations as $row) {
        $country = $row['county'] ?: 'Unknown';
        if (!isset($unsubByCountry[$country])) $unsubByCountry[$country] = 0;
        $unsubByCountry[$country]++;

        $t = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';
        if (!isset($unsubByType[$t])) $unsubByType[$t] = 0;
        $unsubByType[$t]++;

        $totalUnsub++;
    }

    arsort($signupByCountry);
    arsort($unsubByCountry);

    echo json_encode([
        'period' => ['start' => $startDate, 'end' => $endDate, 'label' => $periodLabel],
        'signup' => [
            'total'     => $totalSignup,
            'byCountry' => $signupByCountry,
            'byType'    => $signupByType,
            'detail'    => $signupDetail,
        ],
        'unsub' => [
            'total'     => $totalUnsub,
            'byCountry' => $unsubByCountry,
            'byType'    => $unsubByType,
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['error' => 'Invalid type parameter']);
