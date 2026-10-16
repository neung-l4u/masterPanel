<?php
/**
 * Mirror Monday into l4u_up_down_shops / l4u_up_down_subscriptions.
 *
 * Intended to run twice a day:
 *
 *   php modules/upgrade-downgrade/sync/syncMondayMirror.php
 *
 * Optionally limited to some countries:
 *
 *   php modules/upgrade-downgrade/sync/syncMondayMirror.php TH AU
 *
 * Nothing on the customer path depends on this script succeeding: the form
 * falls back to manual entry when the lookup finds nothing, so a stopped cron
 * degrades convenience, never correctness.
 */

date_default_timezone_set("Asia/Bangkok");

// CLI only unless an explicit token is configured -- this must not be
// triggerable by anyone who can reach the web server.
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    $configFile = __DIR__ . '/../assets/config.php';
    $config     = is_file($configFile) ? include $configFile : [];
    $expected   = isset($config['sync_token']) ? $config['sync_token'] : null;
    $given      = isset($_GET['token']) ? $_GET['token'] : '';

    if (empty($expected) || !hash_equals($expected, $given)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Forbidden\n";
        exit(1);
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$started = microtime(true);

/**
 * Append one line to the rolling log.
 *
 * Defined before anything that can fail, because a failure that leaves no trace
 * in the log is the thing this script most needs to avoid: cron discards stdout,
 * so the log is usually the only evidence an overnight run ever happened.
 */
function upDownSyncLog(string $text): void
{
    $logDir = __DIR__ . '/../assets/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0700, true);
    }
    // Suppressed and never checked: losing the log must not fail the sync.
    @file_put_contents($logDir . '/mirror-sync.log', $text, FILE_APPEND | LOCK_EX);
}

/** Report a fatal, to the log as well as the caller, and stop. */
function upDownSyncFatal(string $message, bool $isCli): void
{
    $line = '[' . date('Y-m-d H:i:s') . " +07] ud mirror sync FATAL: {$message}\n";
    upDownSyncLog($line);
    // On the web path the message goes to the response, so it must not carry a
    // stack trace or a filesystem path beyond what the message itself says.
    fwrite($isCli ? STDERR : STDOUT, $line);
    exit(1);
}

// The database connection is a require, so a connection failure throws before
// any of our own code runs. Without this it surfaces as an uncaught
// mysqli_sql_exception: a stack trace to the HTTP response on the web path, and
// no log entry at all on either.
try {
    require_once __DIR__ . '/../assets/db/db.php';
    require_once __DIR__ . '/../assets/db/initDB.php';
    require_once __DIR__ . '/mirrorMatch.php';
    require_once __DIR__ . '/mirrorRepo.php';
    require_once __DIR__ . '/mondayClient.php';
    require_once __DIR__ . '/mondayMirror.php';
} catch (Throwable $e) {
    upDownSyncFatal('cannot initialise: ' . $e->getMessage(), $isCli);
}

// Country filter: CLI arguments, or ?countries=TH,AU over HTTP.
// Values are cast and stripped to A-Z: they reach no query, but an unsanitised
// value would still forge a line in the log below.
$only = null;
if ($isCli && $argc > 1) {
    $only = array_slice($argv, 1);
} elseif (!$isCli && !empty($_GET['countries']) && is_string($_GET['countries'])) {
    $only = explode(',', $_GET['countries']);
}
if ($only !== null) {
    $only = array_values(array_filter(array_map(
        function ($c) { return preg_replace('/[^A-Z]/', '', strtoupper(trim((string) $c))); },
        $only
    )));
    // An empty filter would otherwise sync no countries at all and still report
    // success, which reads as a healthy run that did nothing.
    if ($only === []) {
        upDownSyncFatal('country filter given but no valid country codes in it', $isCli);
    }
}

$lines    = [];
$hadError = false;

try {
    $summary = upDownRunSync($db, $only);
} catch (Throwable $e) {
    upDownSyncFatal($e->getMessage(), $isCli);
}

foreach ($summary['countries'] as $c) {
    if ($c['error'] !== null) {
        $hadError = true;
        $lines[] = "  {$c['country']}: ERROR {$c['error']} (rows left untouched)";
        continue;
    }
    $lines[] = sprintf(
        "  %s: %d shops (%d new, %d updated, %d removed), %d with an email",
        $c['country'], $c['shops'], $c['inserted'], $c['updated'],
        $c['deleted'], $c['withEmail']
    );
}

$s = $summary['subscriptions'];
if ($s['error'] !== null) {
    $hadError = true;
    $lines[] = "  subscriptions: ERROR {$s['error']} (rows left untouched)";
} else {
    $lines[] = sprintf(
        "  subscriptions: %d active (%d by relation, %d by name, %d unmatched), %d removed",
        $s['subscriptions'], $s['matched']['relation'],
        $s['matched']['name'], $s['matched']['none'], $s['deleted']
    );
    // A rising name/none count means Monday's relation column is being left
    // unset and somebody should look at the board.
    if ($s['matched']['name'] > 0 || $s['matched']['none'] > 0) {
        $lines[] = "  NOTE: some subscriptions did not resolve via the Monday relation column";
    }
}

$elapsed = round(microtime(true) - $started, 1);
$stamp   = date('Y-m-d H:i:s');
$status  = $hadError ? 'with errors' : 'ok';

$output = "[{$stamp} +07] ud mirror sync {$status} ({$elapsed}s)\n"
        . implode("\n", $lines) . "\n";
echo $output;

// Keep a log so an overnight failure is still visible in the morning.
upDownSyncLog($output);

exit($hadError ? 1 : 0);
