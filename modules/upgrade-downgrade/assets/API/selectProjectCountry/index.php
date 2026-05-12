<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$country = isset($_GET['country']) ? strtoupper(trim($_GET['country'])) : '';

if (empty($country)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing country parameter']);
    exit;
}

// Cached JSON files directory
$fileDir = __DIR__ . '/file/ALL';

if (!is_dir($fileDir)) {
    http_response_code(404);
    echo json_encode(['error' => 'No cached data found']);
    exit;
}

// Currency code to country code mapping (for Subscriptions board format)
$currencyToCountry = [
    'thb' => 'TH',
    'aud' => 'AU',
    'nzd' => 'NZ',
    'gbp' => 'UK',
    'usd' => 'US',
    'cad' => 'CA'
];

// Country code to full name mapping (for Projects board format)
$countryNames = [
    'TH' => 'Thailand',
    'AU' => 'Australia',
    'NZ' => 'New Zealand',
    'UK' => 'United Kingdom',
    'US' => 'United States',
    'CA' => 'Canada'
];

// Try ALL files from newest to oldest, pick the first that yields results
$files = glob($fileDir . '/ALL_monday_data*.json');
usort($files, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

$result = [];

foreach ($files as $candidateFile) {
    $raw = file_get_contents($candidateFile);
    $json = json_decode($raw, true);
    if (!$json || !isset($json['data'])) continue;

    // Extract items from either format
    $items = [];
    if (isset($json['data']['items'])) {
        $items = $json['data']['items'];
    } elseif (isset($json['data']['boards'])) {
        foreach ($json['data']['boards'] as $board) {
            foreach ($board['groups'] as $group) {
                if (isset($group['items_page']['items'])) {
                    foreach ($group['items_page']['items'] as $item) {
                        $item['group_title'] = $group['title'] ?? '';
                        $item['board_name'] = $board['name'] ?? '';
                        $items[] = $item;
                    }
                }
            }
        }
    }
    if (empty($items)) continue;

    // Detect format by checking first item's column IDs
    $firstCols = [];
    if (isset($items[0]['column_values'])) {
        foreach ($items[0]['column_values'] as $col) {
            $firstCols[] = $col['id'];
        }
    }

    // Format A: Projects board (has country2, color1, primary_contact, contact_mobile)
    $isProjectsFormat = in_array('country2', $firstCols);
    // Format B: Subscriptions board (has status, text9, status0)
    $isSubscriptionsFormat = in_array('text9', $firstCols) && in_array('status0', $firstCols);

    if (!$isProjectsFormat && !$isSubscriptionsFormat) continue;

    foreach ($items as $item) {
        $cols = [];
        if (isset($item['column_values'])) {
            foreach ($item['column_values'] as $col) {
                $cols[$col['id']] = $col['text'] ?? ($col['display_value'] ?? '');
            }
        }

        if ($isProjectsFormat) {
            // Filter by country
            if ($country !== 'ALL') {
                $itemCountry = $cols['country2'] ?? '';
                $targetCountry = $countryNames[$country] ?? $country;
                if (stripos($itemCountry, $targetCountry) === false) {
                    continue;
                }
            }
            $result[] = [
                'shop_id'    => (string) $item['id'],
                'shop_name'  => $item['name'] ?? '',
                'shop_type'  => $cols['color1'] ?? '',
                'owner_name' => $cols['primary_contact'] ?? '',
                'phone'      => $cols['contact_mobile'] ?? '',
                'country'    => $cols['country2'] ?? ''
            ];
        } else {
            // Subscriptions format: currency → country code
            $currency = strtolower(trim($cols['status0'] ?? ''));
            $itemCountryCode = $currencyToCountry[$currency] ?? '';

            if ($country !== 'ALL' && strtoupper($itemCountryCode) !== $country) {
                continue;
            }
            $result[] = [
                'shop_id'    => (string) $item['id'],
                'shop_name'  => $cols['text9'] ?? ($item['name'] ?? ''),
                'shop_type'  => $item['name'] ?? '',
                'owner_name' => '',
                'phone'      => '',
                'country'    => $countryNames[$itemCountryCode] ?? $itemCountryCode
            ];
        }
    }

    // If we got results from this file, stop searching
    if (!empty($result)) break;
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
