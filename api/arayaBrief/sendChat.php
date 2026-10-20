<?php
/**
 * Post the Araya daily brief to the "IT in house" Google Chat space.
 *
 * Run by cron at 07:00 Asia/Bangkok. Safe to run twice: a date that already
 * has a 'sent' row is skipped rather than posted again.
 *
 * CLI only. This file sits under the web root and posts to a team space, so a
 * public URL must never be able to trigger it.
 */
date_default_timezone_set("Asia/Bangkok");

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only\n";
    exit(1);
}

/*
 * The db wrapper reports a failure (e.g. cannot connect) with exit($message),
 * which ends the script with status 0 and would look like success to cron. Any
 * exit that does not pass through arayaFinish() is therefore turned into 1.
 *
 * Registered BEFORE the requires below: initDB.php opens the connection at
 * require time, so a database outage -- the exact case this guard exists
 * for -- would otherwise exit 0 before the guard existed.
 */
$arayaFinished = false;
function arayaFinish(int $code): void
{
    global $arayaFinished;
    $arayaFinished = true;
    exit($code);
}
register_shutdown_function(function () {
    global $arayaFinished;
    if (!$arayaFinished) {
        echo "\n[arayaBrief] aborted before completing\n";
        exit(1);
    }
});

// An uncaught mysqli exception's stack trace would otherwise carry the
// credentials passed to new db(...) into cron failure mail.
ini_set('zend.exception_ignore_args', '1');

// Every path is __DIR__-relative: cron runs with an arbitrary working directory.
require_once __DIR__ . '/../../modules/arayaBrief/models/loadBrief.php';
require_once __DIR__ . '/../../modules/arayaBrief/models/renderChat.php';
require_once __DIR__ . '/../../modules/arayaBrief/models/briefLog.php';
// The shared wrapper and its (gitignored) credentials set up the global $db.
require_once __DIR__ . '/../../assets/db/db.php';
require_once __DIR__ . '/../../assets/db/initDB.php';

/*
 * The wrapper connects as utf8 (really utf8mb3), which cannot hold a 4-byte
 * character. The brief's section headings are emoji, and a verified send
 * stored 16 of them as "?". On a server with a strict sql_mode the INSERT
 * fails outright AFTER the brief has posted: no sent row is written, so the
 * next run posts the whole thing a second time.
 */
$db->query('SET NAMES utf8mb4');

$today = (new DateTimeImmutable('now'))->format('Y-m-d');

try {
    global $db;

    // The "already sent" check and the post that follows it are not atomic on
    // their own: two overlapping runs -- a manual one during the cron fire --
    // would both find no row and both post. A named lock makes the whole
    // check-and-post sequence exclusive. Timeout 0: if another run holds it,
    // that run is doing this work, so there is nothing to wait for.
    $lock = $db->query('SELECT GET_LOCK(?, 0) AS got', ['araya_brief_send'])->fetchArray();
    if ((int)($lock['got'] ?? 0) !== 1) {
        echo "[arayaBrief] {$today} another run holds the lock, skipping\n";
        arayaFinish(0);
    }

    if (arayaBriefAlreadySentToday($db, $today)) {
        echo "[arayaBrief] {$today} already sent, skipping\n";
        arayaFinish(0);
    }

    // A Monday failure throws out of here: without Monday there is no report,
    // and posting an empty one would read as "we have no customers".
    // false, false: live data only, never a cached read posted as current.
    $brief = arayaLoadBrief(false, false);
    $message = arayaRenderChat($brief);

    $config = arayaLoadConfig();
    $ch = curl_init($config['chat_webhook_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=UTF-8'],
        CURLOPT_POSTFIELDS     => json_encode(['text' => $message], JSON_UNESCAPED_UNICODE),
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrNo = curl_errno($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        // Never log the webhook URL: it is the credential, and curl's error
        // TEXT can embed the host. The errno is just a number, so it carries
        // no secret while still saying which failure this was -- 6 is DNS, 7
        // is connection refused, 28 is a timeout. Without it every outage
        // reads identically as "HTTP 0" and nobody can tell them apart.
        throw new RuntimeException(
            "Chat webhook returned HTTP {$status}"
            . ($response === false ? " (curl errno {$curlErrNo})" : '')
        );
    }

    arayaBriefLogSend($db, $today, 'sent', $message);
    echo "[arayaBrief] {$today} sent\n";
    arayaFinish(0);

} catch (Throwable $e) {
    $why = $e->getMessage();
    // Printed BEFORE the log write is attempted. The db wrapper reports its
    // own failures with exit() rather than an exception on older PHP, so a
    // failing write would end the script right here and the real cause --
    // Monday, the webhook -- would never be printed at all.
    // Only echo: cron redirects stdout to the log, so error_log would write
    // the same sentence a second time into the same file.
    echo "[arayaBrief] {$today} failed: {$why}\n";
    try {
        global $db;
        arayaBriefLogSend($db, $today, 'failed', $why);
    } catch (Throwable $ignored) {
        // The log write failing must not hide the original error.
    }
    arayaFinish(1);
}
