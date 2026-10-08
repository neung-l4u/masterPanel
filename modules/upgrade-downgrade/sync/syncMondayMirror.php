<?php
/**
 * Mirror Monday into l4u_ud_shops / l4u_ud_subscriptions.
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

require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/mirrorMatch.php';
require_once __DIR__ . '/mirrorRepo.php';
require_once __DIR__ . '/mondayClient.php';
require_once __DIR__ . '/mondayMirror.php';

$started = microtime(true);

// Country filter: CLI arguments, or ?countries=TH,AU over HTTP.
$only = null;
if ($isCli && $argc > 1) {
    $only = array_map('strtoupper', array_slice($argv, 1));
} elseif (!$isCli && !empty($_GET['countries'])) {
    $only = array_map('strtoupper', array_filter(explode(',', $_GET['countries'])));
}

$lines    = [];
$hadError = false;

try {
    $summary = udmRunSync($db, $only);
} catch (Throwable $e) {
    fwrite($isCli ? STDERR : STDOUT, "FATAL: " . $e->getMessage() . "\n");
    exit(1);
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

// Keep a rolling log so an overnight failure is still visible in the morning.
$logDir = __DIR__ . '/../assets/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0700, true);
}
@file_put_contents($logDir . '/mirror-sync.log', $output, FILE_APPEND | LOCK_EX);

exit($hadError ? 1 : 0);
