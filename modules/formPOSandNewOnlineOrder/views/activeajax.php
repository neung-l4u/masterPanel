<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB2.php';

date_default_timezone_set('Asia/Bangkok');

function v($k) { return isset($_POST[$k]) && $_POST[$k] !== '' ? trim($_POST[$k]) : null; }
function vNum($k) { $x = v($k); return ($x === null || $x === '') ? null : (float)$x; }
function vInt($k) { $x = v($k); return ($x === null || $x === '') ? null : (int)$x; }
function vList($k) {
    if (empty($_POST[$k])) return null;
    if (is_array($_POST[$k])) {
        $arr = array_filter(array_map('trim', $_POST[$k]), fn($s) => $s !== '');
        return $arr ? implode(', ', $arr) : null;
    }
    return trim($_POST[$k]);
}

function csvFromAssoc($row) {
    $fp = fopen('php://temp', 'r+');
    fputcsv($fp, array_keys($row));
    fputcsv($fp, array_values($row));
    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);
    return $csv;
}

function dayValue($dayKey) {
    $sel = v($dayKey);
    if ($sel === 'Open') {
        $open = v($dayKey . 'OpenTime');
        $close = v($dayKey . 'CloseTime');
        return ($open && $close) ? ($open . '-' . $close) : 'Open';
    }
    return $sel;
}

$submissionId = bin2hex(random_bytes(16));
$dateThai     = date("d/m/") . (intval(date("Y")) + 543) . date(" H:i:s");
$customerMode = v('customerMode') === 'oldcustomer' ? 'oldcustomer' : 'newcustomer';

// ===== File upload handling =====
// Folder name pattern: <id>_<shopEmail>_<tradingName>  (sanitized)
function slugify($s) {
    $s = preg_replace('/[^A-Za-z0-9]+/', '-', (string)$s);
    $s = trim($s, '-');
    return $s !== '' ? substr($s, 0, 60) : 'na';
}

function csvFileSlug($s) {
    $s = preg_replace('/[^A-Za-z0-9]+/', '', (string)$s);
    return $s !== '' ? strtolower(substr($s, 0, 80)) : 'shop';
}
$idShort   = substr($submissionId, 0, 8);
$emailSlug = slugify($_POST['shopEmail']   ?? '');
$nameSlug  = slugify($_POST['shop_name'] ?? ($_POST['tradingName'] ?? ''));
$folder    = $idShort . '_' . $emailSlug . '_' . $nameSlug;

$uploadDir = __DIR__ . '/../assets/uploads/' . $folder . '/';
$uploadUrl = 'https://report.localforyou.com/modules/formPOSandNewOnlineOrder/assets/uploads/' . $folder . '/';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }

$ALLOWED_EXT = ['jpg','jpeg','png','gif','webp','pdf'];
$MAX_SIZE    = 10 * 1024 * 1024; // 10 MB

function saveUpload($field, $uploadDir, $uploadUrl, $allowed, $maxSize, $prefix = '') {
    if (empty($_FILES[$field]) || empty($_FILES[$field]['name'])) return null;

    // Multi-file (e.g. logoMenuPictures[])
    if (is_array($_FILES[$field]['name'])) {
        $urls = [];
        $count = count($_FILES[$field]['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES[$field]['error'][$i] !== UPLOAD_ERR_OK) continue;
            $url = moveOne(
                $_FILES[$field]['name'][$i],
                $_FILES[$field]['tmp_name'][$i],
                $_FILES[$field]['size'][$i],
                $uploadDir, $uploadUrl, $allowed, $maxSize, $prefix
            );
            if ($url) $urls[] = $url;
        }
        return $urls ? implode(',', $urls) : null;
    }

    // Single file
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    return moveOne(
        $_FILES[$field]['name'],
        $_FILES[$field]['tmp_name'],
        $_FILES[$field]['size'],
        $uploadDir, $uploadUrl, $allowed, $maxSize, $prefix
    );
}

function moveOne($origName, $tmp, $size, $uploadDir, $uploadUrl, $allowed, $maxSize, $prefix = '') {
    if ($size <= 0 || $size > $maxSize) return null;
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) return null;
    $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
    $name = $prefix . $safe . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $name;
    if (!move_uploaded_file($tmp, $dest)) return null;
    return $uploadUrl . $name;
}

function normalizeUploadUrl($url) {
    if ($url === null || $url === '') return $url;
    return str_replace(
        'https://report.localforyou.com/modules/formPOSandNewOnlineOrder/uploads/',
        'https://report.localforyou.com/modules/formPOSandNewOnlineOrder/assets/uploads/',
        $url
    );
}

function countryMeta($country) {
    $map = [
        'AU' => ['countryCode' => 'AU', 'telCode' => '61'],
        'Australia' => ['countryCode' => 'AU', 'telCode' => '61'],
        'USA' => ['countryCode' => 'USA', 'telCode' => '1'],
        'United States' => ['countryCode' => 'USA', 'telCode' => '1'],
        'UK' => ['countryCode' => 'UK', 'telCode' => '44'],
        'United Kingdom' => ['countryCode' => 'UK', 'telCode' => '44'],
        'NZ' => ['countryCode' => 'NZ', 'telCode' => '64'],
        'New Zealand' => ['countryCode' => 'NZ', 'telCode' => '64'],
    ];
    return $map[$country] ?? ['countryCode' => $country, 'telCode' => ''];
}

function makeEftposModel($eftposModel) {
    $map = [
        'Portable ($225+GST)' => 'Portable Eftpos $225+GST (No receipt)',
        'Portable Eftpos $225+GST (No receipt)' => 'Portable Eftpos $225+GST (No receipt)',
        'Standard ($525+GST)' => 'Standard Eftpos $525+ GST (receipt)',
        'Standard Eftpos $525+ GST (receipt)' => 'Standard Eftpos $525+ GST (receipt)',
        'Only online payment (No eftpos)' => 'Only online payment (No eftpos)',
    ];
    return $map[$eftposModel] ?? $eftposModel;
}

$logoUrl    = saveUpload('logoMenuPictures',        $uploadDir, $uploadUrl, $ALLOWED_EXT, $MAX_SIZE);
$bizRegUrl  = saveUpload('businessRegistrationDoc', $uploadDir, $uploadUrl, $ALLOWED_EXT, $MAX_SIZE, 'BRD_');
$bankUrl    = saveUpload('bankStatementDoc',        $uploadDir, $uploadUrl, $ALLOWED_EXT, $MAX_SIZE, 'BS_');
$dirIdUrl   = saveUpload('directorIdDoc',           $uploadDir, $uploadUrl, $ALLOWED_EXT, $MAX_SIZE, 'DirectorID_');

$logoUrl   = normalizeUploadUrl($logoUrl);
$bizRegUrl = normalizeUploadUrl($bizRegUrl);
$bankUrl   = normalizeUploadUrl($bankUrl);
$dirIdUrl  = normalizeUploadUrl($dirIdUrl);

// Required document checks
foreach ([
    'businessRegistrationDoc' => $bizRegUrl,
    'bankStatementDoc'        => $bankUrl,
    'directorIdDoc'           => $dirIdUrl,
] as $f => $u) {
    if (!$u) {
        http_response_code(400);
        echo json_encode(['success' => false, 'result' => "Missing or invalid file: $f (allowed: " . implode(',', $ALLOWED_EXT) . ", max " . ($MAX_SIZE / 1024 / 1024) . "MB)"]);
        exit;
    }
}

$row = [
    'submissionId'            => $submissionId,
    'shop_name'               => v('shop_name'),
    'shopEmail'               => v('shopEmail'),
    'shopPhone'               => (function () {
        $phone = trim((string) v('shopPhone'));
        $cc    = trim((string) v('countryCode'));
        if ($cc === '') {
            $countryCodes = [
                'AU' => '+61',
                'Australia' => '+61',
                'USA' => '+1',
                'UK' => '+44',
                'NZ' => '+64',
                'New Zealand' => '+64',
            ];
            $cc = $countryCodes[v('country')] ?? '';
        }
        if ($phone === '') return null;
        if ($phone[0] === '+') return $phone;                    // already has prefix
        $local = ltrim($phone, '0');                              // drop leading 0 (e.g. 0959... -> 959...)
        return $cc ? ($cc . $local) : $phone;
    })(),
    'managerName'             => v('managerName'),
    'country'                 => v('country'),
    'currency'                => v('currency'),
    'tradingName'             => v('tradingName'),
    'tradingAddress'          => v('tradingAddress'),
    'terminalDeliveryAddress' => v('terminalDeliveryAddress'),
    'serviceProvided'         => vList('serviceProvided'),

    'openingHours' => json_encode([
        'mon' => dayValue('openMon'),
        'tue' => dayValue('openTue'),
        'wed' => dayValue('openWed'),
        'thu' => dayValue('openThu'),
        'fri' => dayValue('openFri'),
        'sat' => dayValue('openSat'),
        'sun' => dayValue('openSun'),
    ], JSON_UNESCAPED_UNICODE),

    'eftposModel'         => v('eftposModel'),
    'eftposQty'           => v('eftposQty'),
    'hasOwnWebsite'       => v('hasOwnWebsite'),
    'thirdPartyPlatforms' => (v('thirdPartyPlatforms') === 'Other')
                                ? (v('thirdPartyOther') ?: 'Other')
                                : v('thirdPartyPlatforms'),

    'restaurantAddress' => json_encode([
        'countryCode'     => v('countryCode'),
        'streetAddress'   => v('streetAddress'),
        'city'            => v('city'),
        'stateRegion'     => v('stateRegion'),
        'cuisineSelector' => (function () {
            $list = vList('cuisineSelector');
            $other = v('cuisineOther');
            if ($list && strpos($list, 'Other') !== false && $other) {
                $list = str_replace('Other', 'Other: ' . $other, $list);
            }
            return $list;
        })(),
    ], JSON_UNESCAPED_UNICODE),

    'deliveryServiceNeed' => v('deliveryServiceNeed'),
    'deliverBy'           => v('deliverBy'),
    'servicedArea'        => v('servicedArea'),
    'minimumOrder'        => vNum('minimumOrder'),
    'deliveryFee'         => vNum('deliveryFee'),

    'inhouseDelivery' => json_encode([
        'price0to3km'   => vNum('price0to3km'),
        'price4km'      => vNum('price4km'),
        'price5km'      => vNum('price5km'),
        'price6km'      => vNum('price6km'),
        'minimumOrder'  => vNum('inhouseMinimumOrder'),
    ], JSON_UNESCAPED_UNICODE),

    'logoStatus'              => (v('logoStatus') === 'Other')
                                    ? (v('logoStatusOther') ?: 'Other')
                                    : v('logoStatus'),
    'logoMenuPictures'        => $logoUrl,
    'gmbAccess'               => (v('gmbAccess') === 'Other')
                                    ? (v('gmbAccessOther') ?: 'Other')
                                    : v('gmbAccess'),
    'facebookPageAccess'      => (v('facebookPageAccess') === 'Other')
                                    ? (v('facebookPageAccessOther') ?: 'Other')
                                    : v('facebookPageAccess'),
    'domainHosting'           => v('domainHosting'),
    'marketingTricksOptIn'    => v('marketingTricksOptIn'),

    'documents' => json_encode([
        'businessRegistration' => $bizRegUrl,
        'bankStatement'        => $bankUrl,
        'directorId'           => $dirIdUrl,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),

    'BRD'        => $bizRegUrl ? basename($bizRegUrl) : null,
    'BS'         => $bankUrl ? basename($bankUrl) : null,
    'DirectorID' => $dirIdUrl ? basename($dirIdUrl) : null,
    'adyenAgree' => v('adyenAgree') === 'agreed' ? 'agreed' : '',
    'status'   => ($customerMode === 'oldcustomer') ? 'Old Client' : 'New Client',
    'dateThai' => $dateThai,
];

// Minimum required fields
$required = ($customerMode === 'oldcustomer')
    ? ['shop_name','shopEmail','shopPhone','managerName','country','currency','adyenAgree']
    : ['shop_name','shopEmail','shopPhone','managerName','country','currency','tradingName','tradingAddress','terminalDeliveryAddress','serviceProvided','eftposModel','eftposQty','hasOwnWebsite','thirdPartyPlatforms','streetAddress','city','stateRegion','deliveryServiceNeed','deliverBy','servicedArea','minimumOrder','deliveryFee','logoStatus','gmbAccess','facebookPageAccess','domainHosting','adyenAgree'];
foreach ($required as $r) {
    if ($row[$r] === null || $row[$r] === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'result' => "Missing required field: $r"]);
        exit;
    }
}

if ($customerMode !== 'oldcustomer') {
    if (v('cuisineSelector') === null || vList('cuisineSelector') === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'result' => 'Missing required field: cuisineSelector']);
        exit;
    }
    foreach (['openMon','openTue','openWed','openThu','openFri','openSat','openSun'] as $dayKey) {
        if (v($dayKey) === null || v($dayKey) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'result' => "Missing opening status for: $dayKey"]);
            exit;
        }
        if (v($dayKey) === 'Open' && (!v($dayKey . 'OpenTime') || !v($dayKey . 'CloseTime'))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'result' => "Missing opening time for: $dayKey"]);
            exit;
        }
    }
}

if ($row['shopEmail'] !== null && !filter_var($row['shopEmail'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'result' => 'Invalid shop email address']);
    exit;
}

$rawPhone = trim((string) v('shopPhone'));
if ($rawPhone !== '' && !preg_match('/^[0-9]+$/', $rawPhone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'result' => 'Shop phone number must contain numbers only']);
    exit;
}

$cols   = array_keys($row);
$place  = implode(',', array_fill(0, count($cols), '?'));
$colSql = '`' . implode('`,`', $cols) . '`';
$sql    = "INSERT INTO `POSandNewOnlineOrder` ($colSql) VALUES ($place)";

try {
    $db->query($sql, ...array_values($row));

    $csvContent = csvFromAssoc($row);
    $csvFileName = date('ymd') . '-' . csvFileSlug($row['shop_name']) . '.csv';
    $csvFilePath = $uploadDir . $csvFileName;
    $csvFileUrl = $uploadUrl . $csvFileName;
    file_put_contents($csvFilePath, $csvContent);

    // ===== Forward to Make.com webhook (non-blocking on failure) =====
    $webhookUrl = 'https://hook.us1.make.com/pbgdj10u6ewd5s3h4gkxbf43tp8iyfl7';

    // Start with everything stored in DB
    $payload = $row;

    // Decode JSON columns so Make sees structured data
    foreach (['openingHours','restaurantAddress','inhouseDelivery','documents'] as $jk) {
        if (isset($payload[$jk]) && is_string($payload[$jk])) {
            $decoded = json_decode($payload[$jk], true);
            if ($decoded !== null) $payload[$jk] = $decoded;
        }
    }

    // Identifiers / file folder
    $payload['submissionId'] = $submissionId;
    $payload['uploadFolder'] = $folder;
    $payload['eftposModel'] = makeEftposModel($row['eftposModel']);

    // Extra meta fields posted by the form (not stored in DB)
    $payload['customerMode'] = $customerMode;
    $payload['customerType'] = $customerMode;
    $payload['testMode']     = v('testMode');
    $payload['leadSource']   = v('leadSource');
    $payload['formVersion']  = v('formVersion');
    $payload['emailVersion'] = v('emailVersion');
    $payload['timeStamps']   = v('timeStamps');
    $countryMeta = countryMeta(v('country'));
    $payload['countryCode']  = $countryMeta['countryCode'];
    $payload['telCode']      = $countryMeta['telCode'];
    $payload['dateforMonday'] = date('dmy');
    $payload['csvfileTomake'] = $csvFileUrl;

    if ($customerMode === 'newcustomer') {
        $payload['csvFileName'] = $csvFileName;
    }

    // Raw "Other" text inputs (in case Make wants the unmerged value)
    $payload['rawOther'] = [
        'cuisineOther'            => v('cuisineOther'),
        'thirdPartyOther'         => v('thirdPartyOther'),
        'logoStatusOther'         => v('logoStatusOther'),
        'gmbAccessOther'          => v('gmbAccessOther'),
        'facebookPageAccessOther' => v('facebookPageAccessOther'),
    ];

    $webhookOk  = false;
    $webhookErr = null;
    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_DNS_CACHE_TIMEOUT => 120,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp === false) {
        $webhookErr = curl_error($ch);
    } else {
        $webhookOk = ($code >= 200 && $code < 300);
        if (!$webhookOk) $webhookErr = "HTTP $code: " . substr($resp, 0, 200);
    }
    curl_close($ch);

    echo json_encode([
        'success'      => true,
        'result'       => 'Save to Database Success',
        'submissionId' => $submissionId,
        'webhook'      => ['ok' => $webhookOk, 'error' => $webhookErr],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'result'  => 'DB Error: ' . $e->getMessage(),
    ]);
}
