<?php
/**
 * Reads disk usage for hosting accounts from WHM, so the team hears about a
 * filling disk before it takes a client's site down.
 *
 * Credentials live in whm_tokens.json (gitignored), keyed by svID from L4UServers.
 * Everything degrades quietly: no token file, no network, or an API error simply
 * yields no data rather than breaking the monitor run.
 */

/** @return array<string,array{host:string,user:string,token:string}> */
function whmServers(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $file = __DIR__ . '/whm_tokens.json';
    if (!is_readable($file)) return $cfg = [];

    $parsed = json_decode((string) file_get_contents($file), true);
    if (!is_array($parsed)) return $cfg = [];

    $cfg = [];
    foreach ($parsed as $svID => $row) {
        if (!is_array($row)) continue;                      // skip _comment
        if (empty($row['host']) || empty($row['user']) || empty($row['token'])) continue;
        if (str_contains($row['token'], 'PASTE_TOKEN')) continue;   // untouched template
        $cfg[(string) $svID] = $row;
    }
    return $cfg;
}

/**
 * Accounts on one server with their disk usage.
 * @return array<string,array{used:string,limit:string,percent:?float}> keyed by cPanel user
 */
function whmAccountUsage(string $svID): array {
    $servers = whmServers();
    if (!isset($servers[$svID])) return [];
    $s = $servers[$svID];

    $url = "https://{$s['host']}:2087/json-api/listaccts?api.version=1";
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: whm {$s['user']}:{$s['token']}"],
    ]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) return [];
    $data = json_decode($body, true);
    if (!isset($data['data']['acct']) || !is_array($data['data']['acct'])) return [];

    $out = [];
    foreach ($data['data']['acct'] as $a) {
        if (empty($a['user'])) continue;
        $used  = (string) ($a['diskused']  ?? '');
        $limit = (string) ($a['disklimit'] ?? '');
        $out[$a['user']] = [
            'used'    => $used,
            'limit'   => $limit,
            'percent' => diskPercent($used, $limit),
        ];
    }
    return $out;
}

/**
 * Percentage used, or null when the account has no quota ("unlimited") or the
 * values cannot be read. WHM reports sizes like "512M", "20G", "0".
 */
function diskPercent(string $used, string $limit): ?float {
    $u = sizeToMB($used);
    $l = sizeToMB($limit);
    if ($u === null || $l === null || $l <= 0) return null;
    return round($u / $l * 100, 1);
}

function sizeToMB(string $v): ?float {
    $v = trim($v);
    if ($v === '' || strcasecmp($v, 'unlimited') === 0) return null;
    if (!preg_match('/^([0-9.]+)\s*([KMGT])?/i', $v, $m)) return null;
    $n = (float) $m[1];
    return match (strtoupper($m[2] ?? 'M')) {
        'K' => $n / 1024,
        'G' => $n * 1024,
        'T' => $n * 1024 * 1024,
        default => $n,
    };
}
