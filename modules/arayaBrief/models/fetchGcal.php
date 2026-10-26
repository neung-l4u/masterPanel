<?php
/**
 * Fetch per-shop minutes and billing state from gcal-booking-api.
 *
 * Throws RuntimeException on any failure and never returns an empty array to
 * mean "failed": an empty list is indistinguishable from "no shops" and would
 * make the brief report the whole customer base as gone. The caller decides how
 * to degrade.
 *
 * Messages never include the admin key, the response body or the URL.
 */

require_once __DIR__ . '/cache.php';

/** Parse a gcal response into shop rows. No network; throws on anything unexpected. */
function arayaParseGcalResponse(int $status, string $body): array
{
    if ($status !== 200) {
        throw new RuntimeException("gcal araya-summary returned HTTP {$status}.");
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded) || !array_key_exists('shops', $decoded) || !is_array($decoded['shops'])) {
        throw new RuntimeException('gcal araya-summary response has no shops array.');
    }
    $shops = [];
    foreach ($decoded['shops'] as $i => $shop) {
        if (!is_array($shop)) {
            throw new RuntimeException("gcal araya-summary shop #{$i} is not an object.");
        }
        $shops[] = $shop;
    }
    if ($shops === []) {
        // A live deployment always has shops; an empty list means something upstream is wrong.
        throw new RuntimeException('gcal araya-summary returned zero shops.');
    }
    return $shops;
}

/** The single GET. Returns [status, body]; throws on a transport error. */
function arayaGcalHttpGet(array $config, string $path = '/api/admin/araya-summary'): array
{
    $ch = curl_init($config['gcal_base_url'] . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET        => true,
        CURLOPT_HTTPHEADER     => ['X-Admin-Key: ' . $config['gcal_admin_key']],
        CURLOPT_TIMEOUT        => 20,
    ]);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($body === false) {
        // Code only: curl's text can echo the URL.
        throw new RuntimeException("gcal request failed (curl error {$errno}).");
    }
    return [$status, (string) $body];
}

function arayaFetchGcal(array $config, bool $useCache = true): array
{
    if ($useCache) {
        $cached = arayaCacheGet('gcal', 300);
        if ($cached !== null && isset($cached['shops']) && is_array($cached['shops']) && $cached['shops'] !== []) {
            return $cached['shops'];
        }
    }

    [$status, $body] = arayaGcalHttpGet($config);
    $shops = arayaParseGcalResponse($status, $body);

    arayaCachePut('gcal', ['shops' => $shops]);
    return $shops;
}

/**
 * Parse the call-errors response into error rows. No network.
 *
 * Unlike the shop list, an empty list is a legitimate answer here: a quiet
 * month has no failed calls. What must never pass for it is a failure, so a
 * non-200, a body that is not JSON, or a payload without an `errors` array all
 * throw. Rows are passed through; arayaBuildBrief normalises them.
 */
function arayaParseCallErrorsResponse(int $status, string $body): array
{
    if ($status !== 200) {
        throw new RuntimeException("gcal araya-call-errors returned HTTP {$status}.");
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded) || !array_key_exists('errors', $decoded) || !is_array($decoded['errors'])) {
        throw new RuntimeException('gcal araya-call-errors response has no errors array.');
    }
    $errors = [];
    foreach ($decoded['errors'] as $i => $row) {
        if (!is_array($row)) {
            throw new RuntimeException("gcal araya-call-errors row #{$i} is not an object.");
        }
        $errors[] = $row;
    }
    return $errors;
}

/** Failed calls from the last 30 days, newest first. Throws on any failure; cached 5 minutes. */
function arayaFetchCallErrors(array $config, bool $useCache = true): array
{
    if ($useCache) {
        $cached = arayaCacheGet('gcal_call_errors', 300);
        // An empty list is a valid cached answer, unlike the shop list.
        if ($cached !== null && isset($cached['errors']) && is_array($cached['errors'])) {
            return $cached['errors'];
        }
    }

    [$status, $body] = arayaGcalHttpGet($config, '/api/admin/araya-call-errors');
    $errors = arayaParseCallErrorsResponse($status, $body);

    arayaCachePut('gcal_call_errors', ['errors' => $errors]);
    return $errors;
}
