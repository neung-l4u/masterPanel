<?php
/**
 * Yelp Ads Data API — Excel billing import
 *
 * Usage:
 *   GET  ?action=excel_list               — list uploaded billing Excel files
 *   GET  ?action=excel_data&file=xxx.xlsx — parse billing Excel → structured JSON
 *   POST ?action=excel_upload             — upload a new billing Excel file
 */
header('Content-Type: application/json');
ini_set('memory_limit', '256M');

$config = require __DIR__ . '/yelp_config.php';
$uploadDir = $config['billing_upload_dir'] ?? __DIR__ . '/billing_uploads/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$action = $_GET['action'] ?? $_POST['action'] ?? 'excel_list';
$allowedExt = ['xlsx', 'xls', 'csv', 'pdf', 'png', 'jpg', 'jpeg', 'webp'];

// ========== EXCEL LIST ==========
if ($action === 'excel_list') {
    $files = [];
    foreach (scandir($uploadDir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) continue;
        $full = $uploadDir . $f;
        $files[] = [
            'filename' => $f,
            'size' => filesize($full),
            'modified' => date('Y-m-d H:i:s', filemtime($full)),
            'type' => $ext,
        ];
    }
    usort($files, function($a, $b) { return strcmp($b['modified'], $a['modified']); });
    echo json_encode(['files' => $files]);
    exit;
}

// ========== EXCEL UPLOAD ==========
if ($action === 'excel_upload') {
    if (!isset($_FILES['file'])) {
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        echo json_encode(['error' => 'File type .' . $ext . ' not allowed. Allowed: ' . implode(', ', $allowedExt)]);
        exit;
    }
    $dest = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => true, 'filename' => basename($file['name'])]);
    } else {
        echo json_encode(['error' => 'Failed to save file']);
    }
    exit;
}

// ========== EXCEL DELETE ==========
if ($action === 'excel_delete') {
    $filename = $_GET['file'] ?? $_POST['file'] ?? '';
    if (!$filename) {
        echo json_encode(['error' => 'Provide file parameter']);
        exit;
    }
    $filepath = $uploadDir . basename($filename);
    if (!file_exists($filepath)) {
        echo json_encode(['error' => 'File not found: ' . basename($filename)]);
        exit;
    }
    if (unlink($filepath)) {
        echo json_encode(['success' => true, 'deleted' => basename($filename)]);
    } else {
        echo json_encode(['error' => 'Failed to delete file']);
    }
    exit;
}

// ========== EXCEL DATA ==========
if ($action === 'excel_data') {
    $filename = $_GET['file'] ?? '';
    if (!$filename) {
        echo json_encode(['error' => 'Provide file parameter']);
        exit;
    }
    $filepath = $uploadDir . basename($filename);
    if (!file_exists($filepath)) {
        echo json_encode(['error' => 'File not found: ' . basename($filename)]);
        exit;
    }

    require_once __DIR__ . '/../../vendor/autoload.php';

    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filepath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filepath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
    } catch (\Exception $e) {
        echo json_encode(['error' => 'Failed to parse Excel: ' . $e->getMessage()]);
        exit;
    }

    // Extract Grand Total, Total, and Rebate from header/sidebar area
    // These can appear in any column pair (label in col N, value in col N+1)
    $grandTotal = null;
    $rebate = null;
    $totalRaw = null;
    for ($i = 0; $i < min(10, count($rows)); $i++) {
        $row = $rows[$i];
        $colCount = count($row);
        for ($j = 0; $j < $colCount; $j++) {
            $val = trim((string)($row[$j] ?? ''));
            if ($val === '' || $j + 1 >= $colCount) continue;
            $nextVal = $row[$j + 1] ?? null;
            if ($nextVal === null || $nextVal === '') continue;
            $numVal = floatval($nextVal);
            if (strcasecmp($val, 'Grand Total:') === 0) {
                $grandTotal = $numVal;
            } elseif (strcasecmp($val, 'Rebate:') === 0) {
                $rebate = $numVal;
            } elseif (strcasecmp($val, 'Total:') === 0) {
                $totalRaw = $numVal;
            }
        }
    }

    // Parse data rows (row 3+ = index 2+)
    $items = [];
    $byBusiness = [];
    $byFeature = [];
    $byMonth = [];
    $totalRevenue = 0;
    $businessSet = [];

    for ($i = 2; $i < count($rows); $i++) {
        $row = $rows[$i];
        $business = trim($row[0] ?? '');
        $address = trim($row[1] ?? '');
        $dates = trim($row[2] ?? '');
        $revenue = floatval($row[3] ?? 0);
        $feature = trim($row[4] ?? '');
        if (empty($business)) continue;

        $items[] = compact('business', 'address', 'dates', 'revenue', 'feature');
        $totalRevenue += $revenue;
        $businessSet[$business] = $address;

        if (!isset($byBusiness[$business]))
            $byBusiness[$business] = ['revenue' => 0, 'features' => [], 'address' => $address];
        $byBusiness[$business]['revenue'] += $revenue;
        if (!isset($byBusiness[$business]['features'][$feature]))
            $byBusiness[$business]['features'][$feature] = 0;
        $byBusiness[$business]['features'][$feature] += $revenue;

        if (!isset($byFeature[$feature]))
            $byFeature[$feature] = ['revenue' => 0, 'count' => 0];
        $byFeature[$feature]['revenue'] += $revenue;
        $byFeature[$feature]['count']++;

        $monthKey = '';
        if (preg_match('/(\d{2})\/\d{2}\/(\d{4})/', $dates, $dm))
            $monthKey = $dm[2] . '-' . $dm[1];
        if ($monthKey) {
            if (!isset($byMonth[$monthKey]))
                $byMonth[$monthKey] = ['revenue' => 0, 'items' => 0, 'businesses' => []];
            $byMonth[$monthKey]['revenue'] += $revenue;
            $byMonth[$monthKey]['items']++;
            $byMonth[$monthKey]['businesses'][$business] = true;
        }
    }

    foreach ($byMonth as &$m) {
        $m['business_count'] = count($m['businesses']);
        unset($m['businesses']);
    }
    unset($m);
    ksort($byMonth);
    uasort($byBusiness, function($a, $b) { return $b['revenue'] <=> $a['revenue']; });
    uasort($byFeature, function($a, $b) { return $b['revenue'] <=> $a['revenue']; });

    echo json_encode([
        'filename' => basename($filename),
        'summary' => [
            'total_revenue' => round($totalRevenue, 2),
            'rebate' => $rebate,
            'grand_total' => round($grandTotal ?? ($totalRevenue - ($rebate ?? 0)), 2),
            'total_raw' => round($totalRaw ?? $totalRevenue, 2),
            'business_count' => count($businessSet),
            'item_count' => count($items),
            'feature_count' => count($byFeature),
        ],
        'by_business' => $byBusiness,
        'by_feature' => $byFeature,
        'by_month' => $byMonth,
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

echo json_encode(['error' => 'Unknown action. Use: excel_list, excel_upload, excel_data']);
