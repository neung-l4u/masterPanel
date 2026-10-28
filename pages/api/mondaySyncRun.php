<?php
/**
 * Triggers one mirror sync pass on behalf of a signed-in staff member.
 *
 * This exists so sync_token never reaches the browser. The page can only ask
 * for "sync TH"; it never learns the secret that authorises it.
 *
 * One country per request (a full run takes minutes and the page wants
 * progress), or subs=1 for the standalone subscriptions pass.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/**
 * Emit one JSON response and stop. Never receives the token or the URL.
 *
 * Releases the sync lock first where one is held. PHP would drop it at script
 * end anyway, but every reply below exits from a different depth and relying
 * on teardown order is how a lock survives the request that owns it.
 */
function mondaySyncReply(array $payload, int $status = 200): void
{
    global $lock;

    if (isset($lock) && is_resource($lock)) {
        flock($lock, LOCK_UN);
        fclose($lock);
        $lock = null;
    }

    http_response_code($status);
    echo json_encode($payload);
    exit;
}

// This file is reachable by URL on its own, so it carries its own guard rather
// than assuming main.php's ran.
//
// Deliberately STRICTER than main.php, which accepts `!isset($_SESSION['id'])
// && !isset($_COOKIE['id'])`. That passes on any request carrying a bare
// `Cookie: id=<anything>`: the cookie holds an unsigned staff id, so it is
// trivially forged. Elsewhere that buys an attacker a page view; here it would
// buy unauthenticated multi-minute Monday syncs, repeatable in a loop, each
// rewriting mirror tables and spending API quota.
//
// So: a real server-side session is required, and the id behind it must still
// be an active staff row -- the same check index.php makes on the cookie, which
// main.php skips. A session for a disabled or deleted account cannot sync.
$staffId = $_SESSION['id'] ?? null;

if ($staffId === null || $staffId === '') {
    mondaySyncReply(['ok' => false, 'error' => 'auth', 'message' => 'Not signed in.'], 403);
}

// A sync writes to the mirror and costs minutes of Monday API time, so a GET,
// prefetch or crawler must never start one.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    mondaySyncReply(['ok' => false, 'error' => 'bad_request', 'message' => 'POST required.'], 405);
}

$moduleDir = __DIR__ . '/../../modules/upgrade-downgrade';

require_once $moduleDir . '/sync/mondayClient.php';   // UP_DOWN_PROJECT_BOARDS

// The staff row is checked against the panel's own database, not the module's.
require_once __DIR__ . '/../../assets/db/db.php';
require_once __DIR__ . '/../../assets/db/initDB.php';

$staff = $db->query(
    'SELECT sID FROM `staffs` WHERE sID = ? AND sStatus = 1 AND sDeleteAt IS NULL',
    $staffId
)->fetchArray();

if (empty($staff)) {
    mondaySyncReply(['ok' => false, 'error' => 'auth', 'message' => 'Not signed in.'], 403);
}

// Release the session lock only once the request is authorised: the sync below
// runs for minutes and would otherwise block every other request from this
// staff member.
session_write_close();

$configFile = $moduleDir . '/assets/config.php';
$config     = is_file($configFile) ? include $configFile : [];
$token      = (is_array($config) && isset($config['sync_token'])) ? (string) $config['sync_token'] : '';

if ($token === '') {
    mondaySyncReply([
        'ok'      => false,
        'error'   => 'not_configured',
        'message' => 'Sync is not configured: set sync_token in modules/upgrade-downgrade/assets/config.php',
    ]);
}

// Either one country, or the standalone subscriptions pass. Never both.
$country = isset($_POST['country']) && is_string($_POST['country'])
    ? strtoupper(trim($_POST['country'])) : '';
$subsRun = isset($_POST['subs']) && $_POST['subs'] === '1';

if ($subsRun) {
    $scope = 'subscriptions';
    $query = ['token' => $token, 'format' => 'json', 'subsonly' => '1', 'subs' => '1'];
} else {
    if (!isset(UP_DOWN_PROJECT_BOARDS[$country])) {
        mondaySyncReply(['ok' => false, 'error' => 'bad_request', 'message' => 'Unknown country.'], 400);
    }
    $scope = $country;
    $query = ['token' => $token, 'format' => 'json', 'countries' => $country, 'subs' => '0'];
}

// The script is an entry point (calls exit, reads $_GET), so it is called over
// HTTP rather than included. The URL is built from this file's own location.
$https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$scheme = $https ? 'https' : 'http';
$port   = (int) ($_SERVER['SERVER_PORT'] ?? ($https ? 443 : 80));
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Host is caller-controlled: keep only host characters, and pin the TCP
// connection to this server below so a forged Host cannot carry the token away.
$hostName = preg_replace('/:\d+$/', '', $host);
if (!preg_match('/^[A-Za-z0-9.\-]+$/', $hostName)) {
    mondaySyncReply(['ok' => false, 'error' => 'bad_request', 'message' => 'Bad host.'], 400);
}
$base = rtrim(str_replace(chr(92), '/',dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
$url  = $scheme . '://' . $hostName . ':' . $port . $base
      . '/modules/upgrade-downgrade/sync/syncMondayMirror.php?' . http_build_query($query);

// One sync per scope at a time. A run takes minutes, so a double-clicked
// button, an impatient retry after a browser-side timeout, or two staff on the
// page together would otherwise have several passes writing the same mirror
// rows at once, each with its own synced_at -- and the loser's timestamp
// decides which rows the next run deletes as stale.
$lockDir = $moduleDir . '/assets/logs';
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0700, true);
}
$lockFile = $lockDir . '/.sync-' . preg_replace('/[^A-Za-z]/', '', $scope) . '.lock';
$lock     = @fopen($lockFile, 'c');

if ($lock === false) {
    mondaySyncReply([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'sync_failed',
        'message' => 'Could not acquire the sync lock. Check that '
                   . 'modules/upgrade-downgrade/assets/logs is writable.',
    ]);
}

if (!flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    mondaySyncReply([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'already_running',
        'message' => 'A sync for ' . $scope . ' is already running. Wait for it '
                   . 'to finish rather than starting a second one.',
    ]);
}

$ch = curl_init($url);
$opts = [
    CURLOPT_RETURNTRANSFER => true,
    // Longer than the script's own 300s limit, so a slow country is reported
    // by the script rather than cut off here with no summary.
    CURLOPT_TIMEOUT        => 330,
    // A redirect could carry the token off-host.
    CURLOPT_FOLLOWLOCATION => false,
];
// Pin the connection to loopback so a forged Host header cannot make us carry
// the token to another server: the name in the URL is whatever the caller
// sent, but the socket always goes to this machine.
//
// Loopback rather than SERVER_ADDR, and unconditional rather than best-effort.
// The target script is always on this same host, so loopback is always right;
// SERVER_ADDR is not, behind a proxy or load balancer. And leaving the pin
// unset whenever a candidate address looked unusable would mean the one
// environment that cannot resolve it is also the one that resolves the forged
// Host through real DNS, with the token attached.
$opts[CURLOPT_RESOLVE] = [$hostName . ':' . $port . ':127.0.0.1'];
curl_setopt_array($ch, $opts);
$body     = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false) {
    // curl_error() is deliberately not passed on: it can contain the host.
    // A timeout does NOT stop the server-side run, and an immediate retry
    // would start a second concurrent sync.
    mondaySyncReply([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'unreachable',
        'message' => 'Sync may still be running on the server. Check '
                   . 'modules/upgrade-downgrade/assets/logs/mirror-sync.log before retrying.',
    ]);
}

if ($httpCode === 403) {
    mondaySyncReply([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'not_configured',
        'message' => 'The sync script rejected the token. Check sync_token in config.php.',
    ]);
}

$decoded = json_decode((string) $body, true);

if (!is_array($decoded)) {
    // The script's fatals are plain text even under ?format=json (status 200,
    // header already sent). Pass the first line through: it names the real
    // problem. Load-bearing, not defensive.
    $firstLine = trim((string) strtok((string) $body, "\n"));
    mondaySyncReply([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'sync_failed',
        'message' => $firstLine !== '' ? $firstLine : 'The sync returned no usable output.',
    ]);
}

$decoded['scope'] = $scope;

// Through the helper like every other exit, so the lock is released here too.
mondaySyncReply($decoded);
