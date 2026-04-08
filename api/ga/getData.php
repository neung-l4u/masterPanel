<?php
session_start();
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

header('Content-Type: application/json; charset=utf-8');

// Google Analytics Data API v1beta endpoint
$apiUrl = 'https://analyticsdata.googleapis.com/v1beta:runReport';
$propertyId = '369289543';
$credPath = __DIR__ . '/../../credentials/a-service-account.json';

if (!file_exists($credPath)) {
    echo json_encode(['error' => 'Service account credentials not found.']);
    exit;
}

// Get JWT token from service account
function getAccessToken($credPath) {
    $cred = json_decode(file_get_contents($credPath), true);
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $cred['client_email'],
        'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);
    
    // Base64url encode
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    $signature = '';
    openssl_sign(base64url_encode($header) . '.' . base64url_encode($payload), $signature, $cred['private_key'], OPENSSL_ALGO_SHA256);
    $jwt = base64url_encode($header) . '.' . base64url_encode($payload) . '.' . base64url_encode($signature);
    
    // Exchange JWT for access token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    return $tokenData['access_token'] ?? null;
}

$period = $_GET['period'] ?? '7days';
switch ($period) {
    case '7days':   $startDate = '7daysAgo';  $endDate = 'today'; break;
    case '14days':  $startDate = '14daysAgo'; $endDate = 'today'; break;
    case '30days':  $startDate = '30daysAgo'; $endDate = 'today'; break;
    case '90days':  $startDate = '90daysAgo'; $endDate = 'today'; break;
    default:        $startDate = '7daysAgo';  $endDate = 'today'; break;
}

// Global variables for helper function
$GLOBALS['startDate'] = $startDate;
$GLOBALS['endDate'] = $endDate;

try {
    $accessToken = getAccessToken($credPath);
    if (!$accessToken) {
        echo json_encode(['error' => 'Failed to get access token']);
        exit;
    }

    // Helper function to make GA API request
    function runReport($accessToken, $propertyId, $dimensions, $metrics, $limit = null) {
        $body = [
            'dateRanges' => [['startDate' => $GLOBALS['startDate'], 'endDate' => $GLOBALS['endDate']]],
            'metrics' => array_map(function($m) { return ['name' => $m]; }, $metrics)
        ];
        if (!empty($dimensions)) {
            $body['dimensions'] = array_map(function($d) { return ['name' => $d]; }, $dimensions);
        }
        if ($limit) {
            $body['limit'] = $limit;
        }
        
        $ch = curl_init('https://analyticsdata.googleapis.com/v1beta/properties/' . $propertyId . ':runReport');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed: ' . $response);
        }
        
        return json_decode($response, true);
    }

    // ── 1. Summary metrics ──
    $summaryResponse = runReport($accessToken, $propertyId, [], [
        'activeUsers',
        'sessions',
        'screenPageViews',
        'averageSessionDuration',
        'bounceRate',
        'newUsers'
    ]);
    
    $summaryRow = $summaryResponse['rows'][0] ?? null;
    $summary = [
        'activeUsers'   => $summaryRow ? (int)$summaryRow['metricValues'][0]['value'] : 0,
        'sessions'      => $summaryRow ? (int)$summaryRow['metricValues'][1]['value'] : 0,
        'pageViews'     => $summaryRow ? (int)$summaryRow['metricValues'][2]['value'] : 0,
        'avgDuration'   => $summaryRow ? round((float)$summaryRow['metricValues'][3]['value']) : 0,
        'bounceRate'    => $summaryRow ? round((float)$summaryRow['metricValues'][4]['value'] * 100, 1) : 0,
        'newUsers'      => $summaryRow ? (int)$summaryRow['metricValues'][5]['value'] : 0,
    ];

    // ── 2. Users by country (top 10) ──
    $countryResponse = runReport($accessToken, $propertyId, ['country'], ['activeUsers'], 10);
    $byCountry = [];
    foreach ($countryResponse['rows'] as $row) {
        $byCountry[] = [
            'country' => $row['dimensionValues'][0]['value'],
            'users'   => (int)$row['metricValues'][0]['value'],
        ];
    }

    // ── 3. Top pages (top 10) ──
    $pageResponse = runReport($accessToken, $propertyId, ['pagePath'], ['screenPageViews', 'activeUsers'], 10);
    $topPages = [];
    foreach ($pageResponse['rows'] as $row) {
        $topPages[] = [
            'page'      => $row['dimensionValues'][0]['value'],
            'pageViews' => (int)$row['metricValues'][0]['value'],
            'users'     => (int)$row['metricValues'][1]['value'],
        ];
    }

    // ── 4. Traffic sources (top 10) ──
    $trafficResponse = runReport($accessToken, $propertyId, ['sessionSource'], ['sessions'], 10);
    $trafficSources = [];
    foreach ($trafficResponse['rows'] as $row) {
        $trafficSources[] = [
            'source'   => $row['dimensionValues'][0]['value'],
            'sessions' => (int)$row['metricValues'][0]['value'],
        ];
    }

    // ── 5. Daily trend ──
    $dailyResponse = runReport($accessToken, $propertyId, ['date'], ['activeUsers', 'sessions']);
    $daily = [];
    foreach ($dailyResponse['rows'] as $row) {
        $daily[] = [
            'date'     => $row['dimensionValues'][0]['value'],
            'users'    => (int)$row['metricValues'][0]['value'],
            'sessions' => (int)$row['metricValues'][1]['value'],
        ];
    }

    // ── 6. Device category ──
    $deviceResponse = runReport($accessToken, $propertyId, ['deviceCategory'], ['activeUsers']);
    $devices = [];
    foreach ($deviceResponse['rows'] as $row) {
        $devices[] = [
            'device' => $row['dimensionValues'][0]['value'],
            'users'  => (int)$row['metricValues'][0]['value'],
        ];
    }

    echo json_encode([
        'summary'        => $summary,
        'byCountry'      => $byCountry,
        'topPages'       => $topPages,
        'trafficSources' => $trafficSources,
        'daily'          => $daily,
        'devices'        => $devices,
        'period'         => $period,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
