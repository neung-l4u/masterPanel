<?php
/**
 * Monday API access for the mirror sync.
 *
 * Knows nothing about our schema: it loads credentials and moves GraphQL in
 * and JSON out. The token is read from the gitignored assets/config.php and is
 * never hardcoded here.
 */

/** Country code => Monday project board id. */
const UDM_PROJECT_BOARDS = [
    'CA' => 1943203287,
    'AU' => 1943203246,
    'NZ' => 1943203264,
    'UK' => 1943203305,
    'US' => 1940392927,
    'TH' => 1943203205,
];

const UDM_SUBSCRIPTIONS_BOARD = 1881439330;

/**
 * Load the module config.
 *
 * Throws rather than returning a default: a sync that silently runs without a
 * token would look like "Monday returned nothing" and quietly empty the mirror.
 */
function udmLoadConfig(): array
{
    $file = __DIR__ . '/../assets/config.php';

    if (!is_file($file)) {
        throw new RuntimeException(
            "Missing config: {$file}. Copy assets/config.example.php to assets/config.php and set monday_token."
        );
    }

    $config = include $file;

    if (!is_array($config) || empty($config['monday_token'])) {
        throw new RuntimeException(
            "monday_token is not set in {$file}."
        );
    }

    return $config;
}

/**
 * One Monday GraphQL request. Returns the decoded body, or
 * ['error' => '...'] for a transport-level failure.
 */
function udmMondayRequest(string $token, string $gql, int $timeout = 90): array
{
    $ch = curl_init('https://api.monday.com/v2');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
            'API-Version: 2024-01',
        ],
        CURLOPT_TIMEOUT        => $timeout,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err !== '') return ['error' => $err];

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : ['error' => 'Invalid JSON from Monday'];
}

/**
 * Retry transient Monday failures. Monday answers large nested queries with
 * subgraph/complexity errors under load, and a single bad page would otherwise
 * truncate a board mid-pagination -- the same reason live.php retries.
 *
 * Returns the last response, so the caller still sees the error after the final
 * attempt.
 */
if (!function_exists('udmMondayRequestRetrying')) {
function udmMondayRequestRetrying(string $token, string $gql, int $attempts = 3): array
{
    $res = ['error' => 'no attempt made'];

    for ($i = 0; $i < $attempts; $i++) {
        $res = udmMondayRequest($token, $gql);
        if (!isset($res['error']) && !isset($res['errors']) && isset($res['data'])) {
            return $res;
        }
        usleep(500000);
    }

    return $res;
}
}
