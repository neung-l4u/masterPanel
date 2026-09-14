<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

// Warn this many days before an SSL certificate expires. Let's Encrypt auto-renews
// at ~30 days, so a larger window just reports healthy certificates every day.
define('SSL_WARN_DAYS', 1);

// Wait this long before re-testing a site that just failed, to ride out a brief blip.
define('RECHECK_DELAY_SEC', 20);

// Google Chat webhook for the "Website Down" space, used for every monitor.
// Set MONITOR_CHAT_WEBHOOK in the environment, or drop the URL in chat_webhook.txt (gitignored).
$__hookFile = __DIR__ . '/chat_webhook.txt';
define('CHAT_WEBHOOK', getenv('MONITOR_CHAT_WEBHOOK')
    ?: (is_readable($__hookFile) ? trim(file_get_contents($__hookFile)) : ''));

// Only run the main loop when executed directly as cron (not included by actionMonitor.php)
if (!defined('MONITOR_FUNCTIONS_ONLY')) {
    // Cron only. Without this anyone who knows the URL can trigger a full run of
    // every monitor over the web, and hammer the server (or the Chat space) with it.
    if (PHP_SAPI !== 'cli') {
        http_response_code(403);
        exit('This script runs from cron only.');
    }

    // A full pass over every monitor takes longer than the 5-minute cron interval,
    // so refuse to start if the previous run is still going. Without this the runs
    // stack up and the same site gets checked (and alerted on) by several at once.
    // ponytail: single global lock; if checks ever need to run in parallel, shard by id instead.
    $lockFile = sys_get_temp_dir() . '/monitor_cron.lock';
    $lock     = fopen($lockFile, 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
        exit(0);   // previous run still in progress — skip this tick
    }

    require_once __DIR__ . '/../../assets/db/db.php';
    require_once __DIR__ . '/../../assets/db/initDB.php';
    $monitors = $db->query(
        "SELECT * FROM monitors
         WHERE is_active = 1
           AND delete_at IS NULL
           AND (last_checked_at IS NULL
                OR last_checked_at <= NOW() - INTERVAL check_interval MINUTE)"
    )->fetchAll();

    foreach ($monitors as $monitor) {
        $result = checkTarget($monitor);

        // Confirm before believing a failure: slow or briefly flaky sites time out once
        // and come straight back. A genuinely down site fails the retry too.
        // Only worth doing on a state change, so healthy sites cost nothing extra.
        if ($result['status'] === 'down' && $monitor['last_status'] !== 'down') {
            sleep(RECHECK_DELAY_SEC);
            $result = checkTarget($monitor);

            // A resolver hiccup looks identical to a dead domain, and can persist for
            // both tries. Give DNS one more chance, further apart, before alerting.
            if ($result['status'] === 'down' && $result['httpCode'] === 0
                && stripos((string)$result['errorMsg'], 'resolve host') !== false) {
                sleep(RECHECK_DELAY_SEC);
                $result = checkTarget($monitor);
            }
        }

        saveResult($db, $monitor, $result);
        handleNotifications($db, $monitor, $result);
    }
}

// -------------------------------------------------------

function checkTarget(array $monitor): array {
    $url        = trim($monitor['url']);
    $parsedHost = parse_url($url, PHP_URL_HOST);
    $scheme     = parse_url($url, PHP_URL_SCHEME);

    // HTTP check
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,   // measured: slowest healthy site ~16s, so 15s was cutting off real sites
        CURLOPT_CONNECTTIMEOUT => 10,   // measured: slowest real connect ~1s, 10s is already generous
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'MasterPanel-Monitor/1.0',
    ]);
    $startMs    = microtime(true);
    $body       = (string) curl_exec($ch);
    $responseMs = (int) round((microtime(true) - $startMs) * 1000);
    $httpCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    // WordPress fatal errors are served with HTTP 200, so the status code alone is not enough.
    // ponytail: substring match on the stock WP error page; add more needles if other failure pages show up.
    $isWpFatal = $httpCode >= 200 && $httpCode < 400
        && stripos($body, 'There has been a critical error') !== false;

    $status = ($httpCode >= 200 && $httpCode < 400 && !$isWpFatal) ? 'up' : 'down';
    if ($status === 'down') {
        $errorMsg = $isWpFatal
            ? "WordPress critical error (HTTP {$httpCode})"
            : ("HTTP {$httpCode}" . ($curlError ? " / {$curlError}" : ''));
    } else {
        $errorMsg = null;
    }

    // Work out WHY it is down, so the alert can say so in plain language.
    $reason    = 'up';
    $resolvedIP = null;
    if ($status === 'down') {
        $a = $parsedHost ? @dns_get_record($parsedHost, DNS_A) : [];
        $resolvedIP = $a[0]['ip'] ?? null;

        if ($isWpFatal) {
            $reason = 'wp_fatal';
        } elseif (!$resolvedIP) {
            // Nothing resolves — ask the registry whether the domain exists at all.
            $reason = domainReason($parsedHost);
        } else {
            $reason = 'down';   // DNS fine, server answered badly
        }
    }

    // SSL check (https only)
    $sslExpiry    = null;
    $sslDaysLeft  = null;
    if ($scheme === 'https' && $parsedHost) {
        $ctx = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
        $client = @stream_socket_client("ssl://{$parsedHost}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if ($client) {
            $certParams = stream_context_get_params($client);
            $cert       = $certParams['options']['ssl']['peer_certificate'] ?? null;
            if ($cert) {
                $certInfo    = openssl_x509_parse($cert);
                $expiryTs    = $certInfo['validTo_time_t'] ?? null;
                if ($expiryTs) {
                    $sslExpiry   = date('Y-m-d', $expiryTs);
                    $sslDaysLeft = (int) ceil(($expiryTs - time()) / 86400);
                }
            }
            fclose($client);
        }
    }

    return compact('status', 'httpCode', 'responseMs', 'errorMsg', 'sslExpiry', 'sslDaysLeft', 'reason', 'resolvedIP');
}

/**
 * Classify an unresolvable host via WHOIS on the registrable domain:
 * 'unregistered' (never bought / dropped), 'expired' (past expiry or on hold),
 * or 'down' when WHOIS is unavailable or inconclusive.
 * ponytail: text matching on WHOIS output; registrars word things differently,
 * so anything unrecognised falls back to the generic 'down' wording.
 */
function domainReason(?string $host): string {
    if (!$host) return 'down';
    $host = preg_replace('/^www\./i', '', $host);

    // Keep the last 3 labels for second-level TLDs (co.uk, com.au), else 2.
    $parts = explode('.', $host);
    $n     = count($parts);
    if ($n > 2 && strlen($parts[$n - 2]) <= 3 && strlen($parts[$n - 1]) <= 3) {
        $domain = implode('.', array_slice($parts, -3));
    } else {
        $domain = implode('.', array_slice($parts, -2));
    }

    $out = @shell_exec('whois ' . escapeshellarg($domain) . ' 2>/dev/null');
    if (!$out) return 'down';   // whois binary missing or query failed

    if (preg_match('/(no match|not found|no data found|no entries found|domain not registered|status:\s*free|status:\s*available)/i', $out)) {
        return 'unregistered';
    }
    if (preg_match('/(redemptionperiod|pendingdelete|serverhold|clienthold)/i', $out)) {
        return 'expired';
    }
    if (preg_match('/(?:expir\w*[^:\n]*|paid-till|renewal date)\s*:\s*([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{2}[-\/][A-Za-z]{3}[-\/][0-9]{4})/i', $out, $m)) {
        $ts = strtotime($m[1]);
        if ($ts && $ts < time()) return 'expired';
    }
    return 'down';
}

function saveResult(object $db, array $monitor, array $result): void {
    $db->query(
        "INSERT INTO monitor_logs (monitor_id, checked_at, status, http_code, response_ms, ssl_expiry, ssl_days_left, error_msg, check_type)
         VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, 'auto')",
        $monitor['id'],
        $result['status'],
        $result['httpCode'],
        $result['responseMs'],
        $result['sslExpiry'],
        $result['sslDaysLeft'],
        $result['errorMsg']
    );

    $db->query(
        "UPDATE monitors SET
            last_checked_at  = NOW(),
            last_status      = ?,
            last_response_ms = ?,
            ssl_expiry_date  = ?,
            ssl_days_left    = ?,
            update_at        = NOW()
         WHERE id = ?",
        $result['status'],
        $result['responseMs'],
        $result['sslExpiry'],
        $result['sslDaysLeft'],
        $monitor['id']
    );
}

/**
 * Render an alert from the monitor_templates row, falling back to the built-in
 * wording when the table is missing (production may not be migrated yet) or the
 * template is switched off. Returns null when the alert should not be sent.
 */
function renderTemplate(object $db, string $key, array $monitor, array $result): ?array {
    $vars = [
        '{name}'        => $monitor['name'],
        '{url}'         => $monitor['url'],
        '{domain}'      => preg_replace('#^www\.#i', '', (string) parse_url($monitor['url'], PHP_URL_HOST)),
        '{httpCode}'    => $result['httpCode']    ?? '',
        '{errorMsg}'    => $result['errorMsg']    ?? '',
        '{responseMs}'  => $result['responseMs']  ?? '',
        '{sslExpiry}'   => $result['sslExpiry']   ?? '',
        '{sslDaysLeft}' => $result['sslDaysLeft'] ?? '',
        '{resolvedIP}'  => $result['resolvedIP'] ?? '',
        '{time}'        => date('Y-m-d H:i:s'),
    ];

    $defaults = [
        'down'      => ['[DOWN] {name} is unreachable',  "Monitor: {name}\nURL: {url}\nStatus: DOWN\nHTTP: {httpCode}\nError: {errorMsg}\nTime: {time}", 1],
        'recovered' => ['[RECOVERED] {name} is back online', "Monitor: {name}\nURL: {url}\nStatus: RECOVERED\nResponse: {responseMs}ms\nTime: {time}", 0],
        'ssl'       => ['[SSL WARNING] {name} - {sslDaysLeft} days left', "Monitor: {name}\nURL: {url}\nSSL Expiry: {sslExpiry}\nDays Left: {sslDaysLeft}\nTime: {time}", 0],
        'wp_fatal'     => ['Wordpress There has been a critical error on this website', "Domain : {url}\nTime : {time}", 1],
        'unregistered' => ['Domain Live ใน Website list แต่ Down (ไม่ได้ถูกซื้อ)', "Domain : {url}\nTime : {time}", 1],
        'expired'      => ['Domain Live ใน Website list แต่ Down (แต่โดเมนหมดอายุ)', "Domain : {url}\nTime : {time}", 1],
        'ssl_expired'  => ['SSL หมดอายุแล้ว - เว็บเข้าไม่ได้', "Domain : {domain}\nSSL หมดอายุเมื่อ : {sslExpiry}\nTime : {time}", 1],
        'moved_away'   => ['Domain ไม่ได้ชี้มาที่ Server เราแล้ว (อาจยกเลิกบริการ)', "Domain : {domain}\nIP ปลายทาง : {resolvedIP}\nTime : {time}", 0],
    ];

    $tpl = null;
    // db::query() calls exit() on error, so check the table exists before reading it.
    if ($db->query("SHOW TABLES LIKE 'monitor_templates'")->fetchArray()) {
        $tpl = $db->query("SELECT title, body, mention_all, is_active FROM monitor_templates WHERE tpl_key = ?", $key)->fetchArray();
    }

    if ($tpl) {
        if ((int)$tpl['is_active'] !== 1) return null;
        $title = $tpl['title'];
        $body  = $tpl['body'];
        $mention = (int)$tpl['mention_all'] === 1;
    } else {
        if (!isset($defaults[$key])) $key = 'down';
        [$title, $body, $mention] = $defaults[$key];
        $mention = (bool)$mention;
    }

    return [
        'subject' => strtr($title, $vars),
        'body'    => strtr($body, $vars),
        'mention' => $mention,
    ];
}

/**
 * True when a down site no longer resolves to any server we run, i.e. the client
 * has moved their hosting elsewhere without telling us. Checked by IP rather than
 * by nameserver: plenty of our real clients put Cloudflare/GoDaddy in front of
 * their DNS while still hosting with us, and those must not be treated as gone.
 * Returns false whenever we cannot tell (behind a proxy, no server IPs on file).
 */
function hasLeftOurServers(object $db, ?string $ip): bool {
    if (!$ip) return false;

    // Cloudflare (and similar proxies) hide the real origin, so the address we see
    // says nothing about who hosts the site. Never call these departed.
    foreach (['104.16.','104.17.','104.18.','104.19.','104.20.','104.21.','104.22.',
              '104.23.','104.24.','104.25.','104.26.','104.27.','104.28.','104.29.',
              '104.30.','104.31.','172.64.','172.65.','172.66.','172.67.','172.68.',
              '172.69.','172.70.','172.71.','162.159.','198.41.','188.114.','190.93.',
              '197.234.','203.0.'] as $cf) {
        if (str_starts_with($ip, $cf)) return false;
    }

    static $ourIPs = null;
    if ($ourIPs === null) {
        $rows = $db->query("SELECT svIP FROM L4UServers WHERE svStatus = 1 AND svIP IS NOT NULL AND svIP <> ''")->fetchAll();
        $ourIPs = array_map(fn($r) => trim($r['svIP']), $rows);
    }
    if (!$ourIPs) return false;   // nothing to compare against — never guess

    return !in_array($ip, $ourIPs, true);
}

function handleNotifications(object $db, array $monitor, array $result): void {
    $prevStatus = $monitor['last_status'];
    $newStatus  = $result['status'];

    // Status changed → send alert or recovery
    if ($prevStatus !== $newStatus && $prevStatus !== 'unknown') {
        // Pick the template that matches WHY it went down, so the alert explains itself.
        $key = $newStatus === 'down' ? ($result['reason'] ?? 'down') : 'recovered';

        // A down site pointing at someone else's server is a departed client,
        // not an outage — say so instead of paging the team about a broken website.
        if ($key === 'down' && hasLeftOurServers($db, $result['resolvedIP'] ?? null)) {
            $key = 'moved_away';
        }
        $msg = renderTemplate($db, $key, $monitor, $result);
        if ($msg) sendNotifications($monitor, $msg['subject'], $msg['body'], $msg['mention']);
    }

    // SSL alerts, sent at most once per day per monitor.
    // Let's Encrypt renews itself at ~30 days, so warning that early is pure noise —
    // only warn once renewal has clearly not happened (SSL_WARN_DAYS), and again
    // once the certificate is actually expired.
    if ($result['sslDaysLeft'] !== null && $result['sslDaysLeft'] <= SSL_WARN_DAYS) {
        $key = $result['sslDaysLeft'] < 0 ? 'ssl_expired' : 'ssl';

        // cnt == 1 means the row just saved is today's first one in this band.
        $countRow = $db->query(
            "SELECT COUNT(*) AS cnt FROM monitor_logs
             WHERE monitor_id = ? AND ssl_days_left <= ?
               AND (? = 0 OR ssl_days_left < 0)
               AND DATE(checked_at) = CURDATE()
               AND check_type = 'auto'",
            $monitor['id'], SSL_WARN_DAYS, $key === 'ssl_expired' ? 1 : 0
        )->fetchArray();

        if (($countRow['cnt'] ?? 0) == 1) {
            $msg = renderTemplate($db, $key, $monitor, $result);
            if ($msg) sendNotifications($monitor, $msg['subject'], $msg['body'], $msg['mention']);
        }
    }
}

function sendNotifications(array $monitor, string $subject, string $body, bool $mentionAll = true): void {
    // Email
    if (!empty($monitor['notify_email'])) {
        $emails = array_map('trim', explode(',', $monitor['notify_email']));
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                mail($email, $subject, $body, "From: noreply@masterPanel\r\nContent-Type: text/plain; charset=UTF-8");
            }
        }
    }

    // Line Notify
    if (!empty($monitor['notify_line'])) {
        $msg = "\n{$subject}\n{$body}";
        $ch  = curl_init('https://notify-api.line.me/api/notify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['message' => $msg]),
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $monitor['notify_line']],
            CURLOPT_TIMEOUT        => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    // Webhook — per-monitor override, else the shared Google Chat space.
    $hook = !empty($monitor['notify_webhook']) ? $monitor['notify_webhook'] : CHAT_WEBHOOK;
    if (!empty($hook)) {
        // Google Chat webhook wants {"text": ...}; <users/all> pings everyone in the space.
        $payload = str_contains($hook, 'chat.googleapis.com')
            ? json_encode(['text' => ($mentionAll ? "<users/all> " : "") . "*{$subject}*\n```\n{$body}\n```"], JSON_UNESCAPED_UNICODE)
            : json_encode(['subject' => $subject, 'body' => $body, 'timestamp' => date('c')]);
        $ch = curl_init($hook);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
