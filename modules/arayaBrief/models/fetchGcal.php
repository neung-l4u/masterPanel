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
function arayaGcalHttpGet(array $config): array
{
    $ch = curl_init($config['gcal_base_url'] . '/api/admin/araya-summary');
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
