<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../db/db.php';

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'db_localforyou';
$db = new db($dbHost, $dbUser, $dbPass, $dbName);

// Only run the main loop when executed directly as cron (not included by actionMonitor.php)
if (!defined('MONITOR_FUNCTIONS_ONLY')) {
    $monitors = $db->query(
        "SELECT * FROM monitors
         WHERE is_active = 1
           AND delete_at IS NULL
           AND (last_checked_at IS NULL
                OR last_checked_at <= NOW() - INTERVAL check_interval MINUTE)"
    )->fetchAll();

    foreach ($monitors as $monitor) {
        $result = checkTarget($monitor);
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
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'MasterPanel-Monitor/1.0',
    ]);
    $startMs    = microtime(true);
    curl_exec($ch);
    $responseMs = (int) round((microtime(true) - $startMs) * 1000);
    $httpCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    $status    = ($httpCode >= 200 && $httpCode < 400) ? 'up' : 'down';
    $errorMsg  = ($status === 'down') ? ("HTTP {$httpCode}" . ($curlError ? " / {$curlError}" : '')) : null;

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

    return compact('status', 'httpCode', 'responseMs', 'errorMsg', 'sslExpiry', 'sslDaysLeft');
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

function handleNotifications(object $db, array $monitor, array $result): void {
    $prevStatus = $monitor['last_status'];
    $newStatus  = $result['status'];

    // Status changed → send alert or recovery
    if ($prevStatus !== $newStatus && $prevStatus !== 'unknown') {
        $subject = $newStatus === 'down'
            ? "[DOWN] {$monitor['name']} is unreachable"
            : "[RECOVERED] {$monitor['name']} is back online";
        $body = $newStatus === 'down'
            ? "Monitor: {$monitor['name']}\nURL: {$monitor['url']}\nStatus: DOWN\nHTTP: {$result['httpCode']}\nError: {$result['errorMsg']}\nTime: " . date('Y-m-d H:i:s')
            : "Monitor: {$monitor['name']}\nURL: {$monitor['url']}\nStatus: RECOVERED\nResponse: {$result['responseMs']}ms\nTime: " . date('Y-m-d H:i:s');

        sendNotifications($monitor, $subject, $body);
    }

    // SSL expiring ≤ 30 days — send once per day
    if ($result['sslDaysLeft'] !== null && $result['sslDaysLeft'] <= 30) {
        $lastLog = $db->query(
            "SELECT checked_at FROM monitor_logs
             WHERE monitor_id = ? AND ssl_days_left <= 30
               AND DATE(checked_at) = CURDATE()
               AND check_type = 'auto'
             ORDER BY id DESC LIMIT 1 OFFSET 1"
        , $monitor['id'])->fetchArray();

        if (empty($lastLog)) {
            $subject = "[SSL WARNING] {$monitor['name']} — {$result['sslDaysLeft']} days left";
            $body    = "Monitor: {$monitor['name']}\nURL: {$monitor['url']}\nSSL Expiry: {$result['sslExpiry']}\nDays Left: {$result['sslDaysLeft']}";
            sendNotifications($monitor, $subject, $body);
        }
    }
}

function sendNotifications(array $monitor, string $subject, string $body): void {
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

    // Webhook
    if (!empty($monitor['notify_webhook'])) {
        $payload = json_encode(['subject' => $subject, 'body' => $body, 'timestamp' => date('c')]);
        $ch = curl_init($monitor['notify_webhook']);
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
