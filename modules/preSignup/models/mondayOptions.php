<?php
/**
 * Serves the pre sign-up form's dropdown choices, read live from the Lead
 * Management board.
 *
 * The form mirrors Monday's own labels rather than keeping its own copy, so a
 * label added or renamed in Monday reaches the form without a deploy, and a
 * submitted value is always one Monday recognises.
 *
 * Labels are cached on disk. Monday being slow or down must not stop a sales
 * rep opening a lead, so a stale cache is served in preference to an error,
 * and the form keeps a hardcoded fallback for the case where there is no
 * cache at all.
 */

header('Content-Type: application/json; charset=utf-8');

$configPath = __DIR__ . '/../assets/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'not_configured']);
    exit;
}

$config   = require $configPath;
$url      = (string) ($config['monday_api_url'] ?? 'https://api.monday.com/v2');
$token    = (string) ($config['monday_token'] ?? '');
$boardId  = (string) ($config['monday_board_id'] ?? '');
$columns  = (array)  ($config['monday_columns'] ?? []);
$cacheTtl = (int)    ($config['options_cache_ttl'] ?? 3600);

$cacheFile = __DIR__ . '/../assets/cache/mondayOptions.json';

/** Serve a cached payload, tagging where it came from. */
function preSignupServeCache(string $file, string $source): bool
{
    if (!is_file($file)) {
        return false;
    }
    $cached = json_decode((string) file_get_contents($file), true);
    if (!is_array($cached) || empty($cached['data'])) {
        return false;
    }
    $cached['source'] = $source;
    echo json_encode($cached, JSON_UNESCAPED_UNICODE);
    return true;
}

// --- Fresh cache wins: no call to Monday at all ---------------------------
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    if (preSignupServeCache($cacheFile, 'cache')) {
        exit;
    }
}

// --- Ask Monday ----------------------------------------------------------
$options = [];

if ($token !== '' && $boardId !== '' && $columns) {
    $ids   = '"' . implode('", "', array_values($columns)) . '"';
    $query = 'query { boards (ids: ' . $boardId . ') { columns (ids: [' . $ids . ']) { id title settings_str } } }';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $query]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
        ],
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status !== 200) {
        error_log('preSignup: Monday options fetch failed (HTTP ' . $status . ') ' . $curlErr);
    } else {
        $decoded = json_decode((string) $response, true);
        $fetched = $decoded['data']['boards'][0]['columns'] ?? [];

        // Index Monday's reply by column id so it can be mapped back onto the
        // form field names, whatever order Monday returns them in.
        $byId = [];
        foreach ($fetched as $column) {
            $byId[(string) ($column['id'] ?? '')] = $column;
        }

        foreach ($columns as $field => $columnId) {
            $settings = $byId[$columnId]['settings_str'] ?? '';
            $parsed   = $settings ? json_decode($settings, true) : null;
            $labels   = $parsed['labels'] ?? null;

            if (!is_array($labels)) {
                continue;
            }

            $list = [];
            foreach ($labels as $labelId => $label) {
                // A status column keeps empty slots for deleted labels. They
                // are not choices and must not reach the form.
                $label = is_array($label) ? (string) ($label['name'] ?? '') : (string) $label;
                $label = trim($label);
                if ($label === '') {
                    continue;
                }
                $list[] = ['id' => (string) $labelId, 'label' => $label];
            }

            // Monday's label ids are its own insertion order. Sorting by them
            // keeps the dropdown stable between requests.
            usort($list, static function (array $a, array $b): int {
                return (int) $a['id'] <=> (int) $b['id'];
            });

            if ($list) {
                $options[$field] = $list;
            }
        }
    }
}

// --- Monday gave us nothing usable: fall back to any cache we hold --------
if (!$options) {
    if (preSignupServeCache($cacheFile, 'stale-cache')) {
        exit;
    }
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Unable to load options from Monday']);
    exit;
}

$payload = ['success' => true, 'data' => $options, 'source' => 'monday'];

if (!is_dir(dirname($cacheFile))) {
    @mkdir(dirname($cacheFile), 0755, true);
}
@file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_UNICODE));

echo json_encode($payload, JSON_UNESCAPED_UNICODE);
