# Manual Monday Mirror Sync Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give staff an authenticated admin page that refreshes the Monday mirror on demand, per country or all at once, without shell access.

**Architecture:** A new page at `main.php?p=mondaySync` posts one request per country to a server-side proxy. The proxy holds `sync_token` and calls the existing `sync/syncMondayMirror.php` over HTTP. The sync logic itself is unchanged; the script gains only a time limit on its HTTP path, an opt-in JSON output format, and a flag to skip the subscriptions pass.

**Tech Stack:** PHP 7.4+ (procedural, matching the module), jQuery 3.7 + Bootstrap 5.3 (already loaded by `main.php`), MySQL via the module's `$db` wrapper.

> **Tasks 1 and 2 are built and committed.** Their code blocks below are the
> original plan text and no longer match what shipped: the sync script also
> validates country codes against the board list and takes `?subsonly=1`
> (never a magic country code), and `upDownMirrorStatus()` judges freshness by
> the **oldest** country and returns an extra `newestSync`. Read the committed
> files, not these blocks, when you need their behaviour. Tasks 3–5 are current.

## Global Constraints

- **The CLI contract is frozen.** `php sync/syncMondayMirror.php [COUNTRIES...]` must keep its exact current stdout, log format, and exit codes. The documented cron line in `sync/README.md` depends on it byte for byte. Every change in Task 1 is gated behind `!$isCli` or an opt-in query parameter.
- **`sync_token` never reaches the browser.** It is read server-side only. Emitting it into HTML or JavaScript would let any page viewer trigger unlimited multi-minute Monday syncs.
- **Country codes are exactly these six:** `CA`, `AU`, `NZ`, `UK`, `US`, `TH` (from `UP_DOWN_PROJECT_BOARDS` in `sync/mondayClient.php:11-18`). Validate against this list; never interpolate a user-supplied country into SQL or a log line.
- **Staleness threshold is 48 hours,** from `UP_DOWN_STALE_AFTER_HOURS` in `models/shopLookup.php:10`. Reuse the constant; do not hardcode 48.
- **A country that errors keeps its existing rows.** This is existing deliberate behaviour. The UI reports the error and moves on; it must never present a failed country as emptied.
- Indentation is 4 spaces. PHP functions in this module are prefixed `upDown`.

---

## File Structure

| File | Responsibility |
| --- | --- |
| `modules/upgrade-downgrade/sync/syncMondayMirror.php` (modify) | Entry point. Gains HTTP time limit, `?format=json`, `?subs=` flag. |
| `modules/upgrade-downgrade/sync/mondayMirror.php` (modify) | Gains a `$withSubscriptions` parameter on `upDownRunSync()`. |
| `modules/upgrade-downgrade/models/syncStatus.php` (create) | Reads mirror freshness and per-country counts. The only place that knows these queries. |
| `pages/api/mondaySyncRun.php` (create) | Authenticated proxy. Holds the token, calls the sync, returns JSON. |
| `pages/mondaySync.php` (create) | The page: country checkboxes, buttons, results table. |
| `assets/php/page_navigate.php` (modify) | Registers the `mondaySync` route. |
| `modules/upgrade-downgrade/tests/test_sync_status.php` (create) | Tests the status model. |
| `modules/upgrade-downgrade/tests/test_sync_endpoint.php` (create) | Tests the script's HTTP contract. |

---

### Task 1: Sync script HTTP affordances

The script already supports HTTP with a token. It is missing three things the page needs: a time limit long enough for a big country, a machine-readable output, and a way to skip the subscriptions pass.

**Why the subscriptions flag:** `upDownRunSync()` runs the subscriptions pass unconditionally on every call (`sync/mondayMirror.php:466`). The page issues one request per country, so without a flag a six-country sync would run that pass six times. The page will skip it per country and run it once at the end.

**Files:**
- Modify: `modules/upgrade-downgrade/sync/mondayMirror.php:453-469`
- Modify: `modules/upgrade-downgrade/sync/syncMondayMirror.php:24-40` and its tail
- Test: `modules/upgrade-downgrade/tests/test_sync_endpoint.php`

**Interfaces:**
- Consumes: `upDownRunSync($db, ?array $onlyCountries)` as it exists today.
- Produces:
  - `upDownRunSync($db, ?array $onlyCountries = null, bool $withSubscriptions = true): array` — when `$withSubscriptions` is false, `$summary['subscriptions']` is `null`.
  - HTTP `?format=json` response body:
    ```json
    {"ok": true, "elapsed": 18.2, "syncedAt": "2026-10-28 14:03:11",
     "countries": [{"country":"TH","error":null,"shops":30,"inserted":0,
                    "updated":30,"deleted":0,"withEmail":14}],
     "subscriptions": null}
    ```
    `ok` is false when any country or the subscriptions pass reports an error.
    On an error row, `inserted`/`updated`/`withEmail` are absent — the existing
    error returns omit them (`sync/mondayMirror.php:304`), so consumers must
    treat them as optional.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_sync_endpoint.php`:

```php
<?php
/**
 * The sync script's HTTP contract. These guard the boundary the admin page
 * depends on, and the CLI/cron contract it must not disturb.
 *
 * The script is invoked as a subprocess rather than included: it calls exit()
 * and reads superglobals, so including it would end the test run. The HTTP
 * path cannot be entered from CLI at all, so the switches it adds are asserted
 * against the source; what is actually executed here is the CLI path, which is
 * the one that must not change.
 */
date_default_timezone_set("Asia/Bangkok");

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

$script = realpath(__DIR__ . '/../sync/syncMondayMirror.php');

function runCli(array $args): string {
    global $script;
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script);
    foreach ($args as $a) { $cmd .= ' ' . escapeshellarg($a); }
    return (string) shell_exec($cmd . ' 2>&1');
}

echo "== CLI contract is preserved ==\n";

$out = runCli(['ZZ']);
test("invalid country still fatals",
     strpos($out, 'country filter given but no valid country codes') !== false);
test("fatal output is not JSON",
     strpos(trim($out), '{') !== 0);

echo "== HTTP affordances are present and gated ==\n";

$src = file_get_contents($script);

test("sets a time limit on the HTTP path",
     preg_match('/set_time_limit\(\s*300\s*\)/', $src) === 1);
test("json output is opt-in via ?format",
     strpos($src, "\$_GET['format']") !== false);
test("subscriptions pass is opt-out, not opt-in",
     strpos($src, "\$_GET['subs']") !== false
     && preg_match('/\$withSubs\s*=\s*!\(/', $src) === 1);
test("both switches are declared outside the HTTP block so CLI has defaults",
     preg_match('/\$wantsJson\s*=\s*false;\s*\n\s*\$withSubs\s*=\s*true;/', $src) === 1);

echo "== upDownRunSync honours the subscriptions flag ==\n";

require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

$ref    = new ReflectionFunction('upDownRunSync');
$params = $ref->getParameters();

test("upDownRunSync takes a third parameter", count($params) === 3);
test("third parameter is \$withSubscriptions",
     isset($params[2]) && $params[2]->getName() === 'withSubscriptions');
test("it defaults to true so existing callers are unchanged",
     isset($params[2]) && $params[2]->isDefaultValueAvailable()
     && $params[2]->getDefaultValue() === true);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run:
```bash
php modules/upgrade-downgrade/tests/test_sync_endpoint.php
```
Expected: FAIL on "sets a time limit on the HTTP path", "json output is opt-in via ?format", "subscriptions pass is opt-out", "both switches are declared outside the HTTP block", "upDownRunSync takes a third parameter", "third parameter is $withSubscriptions", and "it defaults to true".

- [ ] **Step 3: Add the subscriptions flag to upDownRunSync**

In `modules/upgrade-downgrade/sync/mondayMirror.php`, replace the function at line 453 with:

```php
/**
 * @param bool $withSubscriptions Run the subscriptions pass. The admin page
 *        syncs one country per request and would otherwise repeat this pass
 *        once per country; it passes false and runs it once at the end.
 *        When false, $summary['subscriptions'] is null.
 */
function upDownRunSync($db, ?array $onlyCountries = null, bool $withSubscriptions = true): array
{
    $config   = upDownLoadConfig();
    $token    = $config['monday_token'];
    $syncedAt = date('Y-m-d H:i:s');

    $countries = $onlyCountries ?? array_keys(UP_DOWN_PROJECT_BOARDS);
    $summary   = ['syncedAt' => $syncedAt, 'countries' => [], 'subscriptions' => null];

    foreach ($countries as $country) {
        $summary['countries'][] = upDownSyncCountry($db, $token, $country, $syncedAt);
    }

    if ($withSubscriptions) {
        $summary['subscriptions'] = upDownSyncSubscriptions($db, $token, $syncedAt);
    }

    return $summary;
}
```

- [ ] **Step 4: Add the HTTP affordances to the entry point**

In `modules/upgrade-downgrade/sync/syncMondayMirror.php`, replace the whole `if (!$isCli) { ... }` block (lines 24-40) with:

```php
// Defaults that only the HTTP path can change. The CLI keeps text output and
// the full run, so the documented cron command behaves exactly as before.
$wantsJson = false;
$withSubs  = true;

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

    // A full country can take minutes; the default limit would kill the
    // largest board partway and leave the caller with no summary at all.
    set_time_limit(300);

    $wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';
    // Opt-out: a caller syncing one country at a time runs this pass once at
    // the end rather than repeating it per country.
    $withSubs  = !(isset($_GET['subs']) && $_GET['subs'] === '0');

    header('Content-Type: ' . ($wantsJson ? 'application/json' : 'text/plain') . '; charset=utf-8');
}
```

Then change the `upDownRunSync` call to pass the flag:

```php
try {
    $summary = upDownRunSync($db, $only, $withSubs);
} catch (Throwable $e) {
    upDownSyncFatal($e->getMessage(), $isCli);
}
```

Guard the subscriptions block in the tail, which currently assumes the pass ran. Replace:

```php
$s = $summary['subscriptions'];
if ($s['error'] !== null) {
```

with:

```php
$s = $summary['subscriptions'];
if ($s === null) {
    // Skipped by ?subs=0. Say so rather than printing a zero line, which would
    // read as "no subscriptions found".
    $lines[] = "  subscriptions: skipped";
} elseif ($s['error'] !== null) {
```

The rest of that branch is unchanged.

- [ ] **Step 5: Replace the output tail**

Replace everything from `$elapsed = round(...)` to the final `exit(...)` at the end of the file with:

```php
$elapsed = round(microtime(true) - $started, 1);
$stamp   = date('Y-m-d H:i:s');
$status  = $hadError ? 'with errors' : 'ok';

$output = "[{$stamp} +07] ud mirror sync {$status} ({$elapsed}s)\n"
        . implode("\n", $lines) . "\n";

// The log gets the human-readable form either way: an HTTP-triggered run must
// leave the same audit trail a cron run does.
upDownSyncLog($output);

if ($wantsJson) {
    echo json_encode([
        'ok'            => !$hadError,
        'elapsed'       => $elapsed,
        'syncedAt'      => $summary['syncedAt'],
        'countries'     => $summary['countries'],
        'subscriptions' => $summary['subscriptions'],
    ], JSON_UNESCAPED_UNICODE);
    exit($hadError ? 1 : 0);
}

echo $output;
exit($hadError ? 1 : 0);
```

Check that `$elapsed`, `$stamp`, `$status`, `$output`, `upDownSyncLog(...)`, `echo` and `exit` each appear exactly once in this tail — the previous version ended with its own copies and leaving them would log every run twice.

- [ ] **Step 6: Run tests to verify they pass**

Run:
```bash
php modules/upgrade-downgrade/tests/test_sync_endpoint.php
```
Expected: `Passed: 10  Failed: 0`

Confirm the CLI contract really is untouched:
```bash
php modules/upgrade-downgrade/sync/syncMondayMirror.php ZZ
```
Expected: `ud mirror sync FATAL: country filter given but no valid country codes in it`, exit 1 — identical to before.

- [ ] **Step 7: Commit**

```bash
git add modules/upgrade-downgrade/sync/syncMondayMirror.php \
        modules/upgrade-downgrade/sync/mondayMirror.php \
        modules/upgrade-downgrade/tests/test_sync_endpoint.php
git commit -m "feat(upgrade-downgrade): add JSON output and subscriptions flag to mirror sync

The admin sync page needs a machine-readable summary and a way to sync one
country without repeating the subscriptions pass each time. Both are opt-in
query parameters on the HTTP path, so the CLI and its cron contract are
unchanged. The HTTP path also gains set_time_limit(300): a large country would
otherwise be killed by the default limit partway through.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 2: Mirror status model

The page shows when the mirror was last synced and how much each country holds. Those queries belong in the module's models directory alongside `shopLookup.php`, not inline in a page.

**Files:**
- Create: `modules/upgrade-downgrade/models/syncStatus.php`
- Test: `modules/upgrade-downgrade/tests/test_sync_status.php`

**Interfaces:**
- Consumes: `UP_DOWN_STALE_AFTER_HOURS` from `models/shopLookup.php:10`; `UP_DOWN_PROJECT_BOARDS` from `sync/mondayClient.php:11`; the `$db` wrapper's `query(...)->fetchAll()` / `->fetchArray()`.
- Produces:
  - `upDownMirrorStatus($db): array` →
    ```php
    ['lastSync' => '2026-10-05 06:00:04'|null, 'stale' => true,
     'ageHours' => 551.2|null, 'totalShops' => 2482, 'totalSubs' => 915]
    ```
    `lastSync` is null and `stale` true on an empty mirror.
  - `upDownMirrorCountryCounts($db): array` → one row per country in
    `UP_DOWN_PROJECT_BOARDS` order, each
    `['country' => 'TH', 'shops' => 30, 'withEmail' => 14, 'lastSync' => '...'|null]`.
    Countries absent from the mirror appear with zeros, so the page lists all
    six whether or not they have ever synced.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_sync_status.php`:

```php
<?php
/**
 * The mirror status model. Runs against the real database like the other
 * mirror tests, and only reads -- it must never write to the mirror.
 */
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../models/shopLookup.php';
require_once __DIR__ . '/../models/syncStatus.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== upDownMirrorStatus ==\n";
$status = upDownMirrorStatus($db);

foreach (['lastSync', 'stale', 'ageHours', 'totalShops', 'totalSubs'] as $k) {
    test("returns key {$k}", array_key_exists($k, $status));
}
test("stale is a bool", is_bool($status['stale']));
test("totalShops is an int", is_int($status['totalShops']));
test("totalSubs is an int", is_int($status['totalSubs']));

// An empty mirror must read as stale, not as fresh-with-zero-rows: the page
// uses this flag to decide whether to warn, and "never synced" is the most
// important case to warn about.
test("an empty mirror is reported stale",
     $status['lastSync'] !== null || $status['stale'] === true);

// Freshness must agree with the constant the customer-facing banner uses, so
// the two cannot drift apart.
if ($status['lastSync'] !== null) {
    $expectedStale = (strtotime($status['lastSync']) < time() - UP_DOWN_STALE_AFTER_HOURS * 3600);
    test("staleness matches UP_DOWN_STALE_AFTER_HOURS",
         $status['stale'] === $expectedStale);
    test("ageHours is a float when there is data", is_float($status['ageHours']));
} else {
    test("staleness matches UP_DOWN_STALE_AFTER_HOURS (no data; skipped)", true);
    test("ageHours is null when there is no data", $status['ageHours'] === null);
}

echo "== upDownMirrorCountryCounts ==\n";
$counts = upDownMirrorCountryCounts($db);

test("returns one row per configured country",
     count($counts) === count(UP_DOWN_PROJECT_BOARDS));

$seen = array_column($counts, 'country');
foreach (array_keys(UP_DOWN_PROJECT_BOARDS) as $cc) {
    test("includes {$cc} even with no rows", in_array($cc, $seen, true));
}

foreach ($counts as $row) {
    foreach (['country', 'shops', 'withEmail', 'lastSync'] as $k) {
        test("{$row['country']} row has {$k}", array_key_exists($k, $row));
    }
    test("{$row['country']} withEmail never exceeds shops",
         $row['withEmail'] <= $row['shops']);
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run:
```bash
php modules/upgrade-downgrade/tests/test_sync_status.php
```
Expected: FAIL — `Failed opening required '.../models/syncStatus.php'`.

- [ ] **Step 3: Write the model**

Create `modules/upgrade-downgrade/models/syncStatus.php`:

```php
<?php
/**
 * Mirror freshness, for the admin sync page.
 *
 * Read-only, and the only place outside shopLookup.php that knows the mirror
 * schema. The page deals in the arrays these return.
 *
 * Staleness reuses UP_DOWN_STALE_AFTER_HOURS from shopLookup.php rather than
 * repeating 48: the admin warning and the customer-facing "may be out of date"
 * banner must flip at the same moment, or staff see green while customers are
 * being warned.
 */

/**
 * Overall mirror freshness.
 *
 * @return array{lastSync: ?string, stale: bool, ageHours: ?float,
 *               totalShops: int, totalSubs: int}
 */
function upDownMirrorStatus($db): array
{
    $row = $db->query(
        "SELECT MAX(synced_at) AS last_sync, COUNT(*) AS total FROM l4u_up_down_shops"
    )->fetchArray();

    $subs = $db->query(
        "SELECT COUNT(*) AS total FROM l4u_up_down_subscriptions"
    )->fetchArray();

    $lastSync = !empty($row['last_sync']) ? (string) $row['last_sync'] : null;

    // No rows means the sync has never run here. That is the case most worth
    // flagging, so it reports stale rather than falling through as fresh.
    if ($lastSync === null) {
        return [
            'lastSync'   => null,
            'stale'      => true,
            'ageHours'   => null,
            'totalShops' => 0,
            'totalSubs'  => (int) ($subs['total'] ?? 0),
        ];
    }

    $ageSeconds = time() - strtotime($lastSync);

    return [
        'lastSync'   => $lastSync,
        'stale'      => $ageSeconds > UP_DOWN_STALE_AFTER_HOURS * 3600,
        'ageHours'   => (float) round($ageSeconds / 3600, 1),
        'totalShops' => (int) ($row['total'] ?? 0),
        'totalSubs'  => (int) ($subs['total'] ?? 0),
    ];
}

/**
 * Per-country mirror contents, in the configured board order.
 *
 * Every configured country is returned whether or not it has rows: a country
 * that has never synced is exactly the one staff need to see listed, and
 * omitting it would silently drop it from the page's checkboxes.
 *
 * @return list<array{country: string, shops: int, withEmail: int, lastSync: ?string}>
 */
function upDownMirrorCountryCounts($db): array
{
    $rows = $db->query(
        "SELECT country,
                COUNT(*)                                     AS shops,
                SUM(CASE WHEN email <> '' THEN 1 ELSE 0 END) AS with_email,
                MAX(synced_at)                               AS last_sync
         FROM l4u_up_down_shops
         GROUP BY country"
    )->fetchAll();

    $byCountry = [];
    foreach ($rows as $r) {
        $byCountry[(string) $r['country']] = $r;
    }

    $out = [];
    foreach (array_keys(UP_DOWN_PROJECT_BOARDS) as $cc) {
        $r = $byCountry[$cc] ?? null;
        $out[] = [
            'country'   => $cc,
            'shops'     => $r ? (int) $r['shops'] : 0,
            'withEmail' => $r ? (int) $r['with_email'] : 0,
            'lastSync'  => ($r && !empty($r['last_sync'])) ? (string) $r['last_sync'] : null,
        ];
    }

    return $out;
}
```

- [ ] **Step 4: Run test to verify it passes**

Run:
```bash
php modules/upgrade-downgrade/tests/test_sync_status.php
```
Expected: `Failed: 0`

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/models/syncStatus.php \
        modules/upgrade-downgrade/tests/test_sync_status.php
git commit -m "feat(upgrade-downgrade): add mirror status model

Reports mirror freshness and per-country row counts for the admin sync page.
Staleness reuses UP_DOWN_STALE_AFTER_HOURS so the admin warning and the
customer-facing stale banner cannot drift apart, and every configured country
is returned even with no rows -- a country that has never synced is the one
staff most need to see.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 3: Authenticated sync proxy

The page cannot call the sync script directly: that would require the token in JavaScript. This endpoint holds the token server-side, checks the session, and relays the result.

**Files:**
- Create: `pages/api/mondaySyncRun.php`

**Interfaces:**
- Consumes: `sync_token` from `modules/upgrade-downgrade/assets/config.php`; `UP_DOWN_PROJECT_BOARDS` from `sync/mondayClient.php`; the JSON contract from Task 1.
- Produces: `POST pages/api/mondaySyncRun.php` with body `country=TH` or `subs=1`.
  Returns JSON in both shapes below; the page's JavaScript branches on `ok` only.
  ```json
  {"ok": true,  "scope": "TH", "elapsed": 18.2,
   "countries": [ ... ], "subscriptions": null}
  {"ok": false, "scope": "TH", "error": "not_configured",
   "message": "Sync is not configured — set sync_token in config.php"}
  ```
  `error` is one of `auth`, `bad_request`, `not_configured`, `unreachable`, `sync_failed`.

- [ ] **Step 1: Write the endpoint**

Create `pages/api/mondaySyncRun.php`:

```php
<?php
/**
 * Triggers one mirror sync pass on behalf of a signed-in staff member.
 *
 * This exists so sync_token never reaches the browser. The page can only ask
 * for "sync TH"; it never learns the secret that authorises it, so a viewer
 * who reads the page source cannot drive the sync script directly.
 *
 * One country per request, because a full six-country run takes about three
 * minutes and the page wants progress as each one finishes.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// This file is reachable by URL on its own, so it repeats the guard from
// main.php rather than assuming it ran. Inheriting a guard only protects code
// that actually executes behind it.
if (!isset($_SESSION['id']) && !isset($_COOKIE['id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'auth', 'message' => 'Not signed in.']);
    exit;
}

// A sync writes to the mirror and costs minutes of Monday API time, so it is
// not something a GET (or a prefetch, or a crawler) should ever start.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'bad_request', 'message' => 'POST required.']);
    exit;
}

$moduleDir = __DIR__ . '/../../modules/upgrade-downgrade';

require_once $moduleDir . '/sync/mondayClient.php';   // UP_DOWN_PROJECT_BOARDS

$configFile = $moduleDir . '/assets/config.php';
$config     = is_file($configFile) ? include $configFile : [];
$token      = isset($config['sync_token']) ? (string) $config['sync_token'] : '';

if ($token === '') {
    echo json_encode([
        'ok'      => false,
        'error'   => 'not_configured',
        'message' => 'Sync is not configured — set sync_token in '
                   . 'modules/upgrade-downgrade/assets/config.php',
    ]);
    exit;
}

// Either one country, or the standalone subscriptions pass. Never both: the
// page runs the countries first and the subscriptions pass once at the end.
$country = isset($_POST['country']) ? strtoupper(trim((string) $_POST['country'])) : '';
$subsRun = isset($_POST['subs']) && $_POST['subs'] === '1';

if ($subsRun) {
    $scope = 'subscriptions';
    // No countries, subscriptions on: the script treats an absent country
    // filter as "all", so the filter is given explicitly as an empty list.
    $query = ['token' => $token, 'format' => 'json', 'subsonly' => '1', 'subs' => '1'];
} else {
    if (!isset(UP_DOWN_PROJECT_BOARDS[$country])) {
        echo json_encode([
            'ok'      => false,
            'error'   => 'bad_request',
            'message' => 'Unknown country.',
        ]);
        exit;
    }
    $scope = $country;
    $query = ['token' => $token, 'format' => 'json', 'countries' => $country, 'subs' => '0'];
}

// Built against this same host. The script is called over HTTP rather than
// included because it is an entry point: it calls exit() and reads $_GET, so
// including it would terminate this response instead.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$url    = $scheme . '://' . $host . $base
        . '/modules/upgrade-downgrade/sync/syncMondayMirror.php?' . http_build_query($query);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    // Longer than the script's own 300s limit, so a slow country is reported
    // by the script rather than cut off here with no summary.
    CURLOPT_TIMEOUT        => 330,
    // The sync script is on this host; following a redirect would mean
    // something is misconfigured, and chasing it could carry the token offsite.
    CURLOPT_FOLLOWLOCATION => false,
]);
$body     = curl_exec($ch);
$curlErr  = curl_error($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false) {
    echo json_encode([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'unreachable',
        // A timeout here does NOT stop the server-side run, and staff who
        // retry immediately would start a second concurrent sync.
        'message' => 'Sync may still be running on the server — check '
                   . 'modules/upgrade-downgrade/assets/logs/mirror-sync.log '
                   . 'before retrying. (' . $curlErr . ')',
    ]);
    exit;
}

if ($httpCode === 403) {
    echo json_encode([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'not_configured',
        'message' => 'The sync script rejected the token. Check sync_token in config.php.',
    ]);
    exit;
}

$decoded = json_decode((string) $body, true);

if (!is_array($decoded)) {
    // The script fatals in plain text. Pass the first line through: it names
    // the real problem (no database, bad config) far better than a generic
    // failure message would.
    $firstLine = trim(strtok((string) $body, "\n"));
    echo json_encode([
        'ok'      => false,
        'scope'   => $scope,
        'error'   => 'sync_failed',
        'message' => $firstLine !== '' ? $firstLine : 'The sync returned no usable output.',
    ]);
    exit;
}

$decoded['scope'] = $scope;
echo json_encode($decoded);
```

- [ ] **Step 2: (already done in Task 1 — no code to write)**

This step originally asked you to teach the script a `countries=NONE` escape
hatch. **Do not do that.** Task 1 solved the same problem differently and
better, and the code is already committed:

- The filter now validates each code against `UP_DOWN_PROJECT_BOARDS`. Before
  that, `ZZ` passed the A-Z strip, reached `upDownRunSync`, and was only
  reported as "unknown country" *after* a full live sync had queried Monday and
  rewritten the subscriptions table — about 90 seconds of API calls to answer a
  typo.
- `?subsonly=1` is the parameter for "subscriptions pass, no countries". It is
  read inside the `!$isCli` block only, so no CLI argument can reach it — an
  earlier attempt used a magic country code and made
  `php syncMondayMirror.php SUBSONLY` run a live write. Step 1's `$query`
  already uses the parameter. Combining it with `subs=0` is refused.
- **A fatal is plain text even under `?format=json`.** `upDownSyncFatal` writes
  its timestamped line and exits, after the `application/json` header is
  already sent. Step 1's endpoint handles this (`json_decode` returns null, and
  it reports `sync_failed` with the first line of the body) — keep that branch;
  it is not defensive padding.
- A filter mixing valid and invalid codes is refused outright rather than
  silently syncing the subset.

Read `modules/upgrade-downgrade/sync/syncMondayMirror.php` around the `$only`
filter to see the committed behaviour before writing Step 1's endpoint, and
confirm the token you send matches it. Write no code for this step.

- [ ] **Step 3: Verify the auth guard rejects an anonymous request**

Run:
```bash
curl -s -o /dev/null -w "%{http_code}\n" -X POST \
  -d "country=TH" http://localhost/masterPanel/pages/api/mondaySyncRun.php
```
Expected: `403`

- [ ] **Step 4: Verify the country guard still fatals cheaply**

Run both, and time them:
```bash
php modules/upgrade-downgrade/sync/syncMondayMirror.php ZZ
php modules/upgrade-downgrade/sync/syncMondayMirror.php 123
```
Expected: `ud mirror sync FATAL: unknown country code(s): ZZ` for the first,
`ud mirror sync FATAL: country filter given but no valid country codes in it`
for the second, exit 1 for both, and **each returning in under a second**. A
run that takes ~90 seconds means the membership check is gone and the script
is syncing Monday to answer a typo.

- [ ] **Step 5: Commit**

```bash
git add pages/api/mondaySyncRun.php
git commit -m "feat(masterPanel): add authenticated proxy for Monday mirror sync

Holds sync_token server-side so it never reaches the browser: the page can ask
to sync a country but never learns the secret that authorises it. Repeats
main.php's session guard because this file is reachable by URL on its own, and
refuses GET so a prefetch cannot start a three-minute Monday run.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 4: The admin page and its route

**Files:**
- Create: `pages/mondaySync.php`
- Modify: `assets/php/page_navigate.php`

**Interfaces:**
- Consumes: `upDownMirrorStatus($db)` and `upDownMirrorCountryCounts($db)` from Task 2; the proxy from Task 3.
- Produces: the route key `mondaySync`, reachable as `main.php?p=mondaySync`.

- [ ] **Step 1: Register the route**

In `assets/php/page_navigate.php`, add this case inside the `switch ($menuPage)` block, immediately before `case "home":`:

```php
    case "mondaySync":
        $showPage = "mondaySync.php";
        $activeMenu["lv1"] = "mondaySync";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        $title = "Master panel : Monday Mirror Sync";
        break;
```

- [ ] **Step 2: Write the page**

Create `pages/mondaySync.php`:

```php
<?php
/**
 * Manual Monday mirror sync.
 *
 * The customer-mode email lookup reads a local mirror, not Monday, so an edit
 * made in Monday is invisible to the form until the mirror is synced. This
 * page runs that sync without shell access.
 *
 * It syncs one country per request: a full run takes about three minutes, so a
 * single request would risk the time limit and would show no progress until it
 * finished. The subscriptions pass runs once, after the countries.
 */

global $db;

$moduleDir = __DIR__ . '/../modules/upgrade-downgrade';
require_once $moduleDir . '/sync/mondayClient.php';
require_once $moduleDir . '/models/shopLookup.php';
require_once $moduleDir . '/models/syncStatus.php';

$status    = upDownMirrorStatus($db);
$counts    = upDownMirrorCountryCounts($db);
$countries = array_keys(UP_DOWN_PROJECT_BOARDS);
?>

<div class="content-wrapper">
  <section class="content pt-3">
    <div class="container-fluid">

      <div class="card">
        <div class="card-header">
          <h3 class="card-title mb-0">
            <i class="bi bi-arrow-repeat"></i> Monday Mirror Sync
          </h3>
        </div>

        <div class="card-body">

          <p class="text-muted">
            The customer upgrade/downgrade form looks a shop up by email from a
            local mirror of Monday. Edits made in Monday appear there only after
            a sync.
          </p>

          <?php if ($status['lastSync'] === null): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Never synced.</strong>
              The mirror is empty, so no customer can be found by email.
            </div>
          <?php elseif ($status['stale']): ?>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill"></i>
              Oldest country last synced
              <strong><?php echo htmlspecialchars($status['lastSync']); ?></strong>
              — <?php echo number_format($status['ageHours'] / 24, 1); ?> days ago.
              Customers of those shops are being shown a "may be out of date" warning.
              <?php if ($status['newestSync'] !== $status['lastSync']): ?>
                <br><small>Most recent country:
                <?php echo htmlspecialchars($status['newestSync']); ?>. Per-country
                dates are listed below.</small>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle-fill"></i>
              Every country synced within
              <?php echo number_format($status['ageHours'], 1); ?> hours
              (oldest: <strong><?php echo htmlspecialchars($status['lastSync']); ?></strong>).
            </div>
          <?php endif; ?>

          <p class="text-muted small">
            Mirror holds <?php echo number_format($status['totalShops']); ?> shops
            and <?php echo number_format($status['totalSubs']); ?> active subscriptions.
          </p>

          <div class="row">
            <?php foreach ($counts as $row): ?>
              <div class="col-md-4 col-sm-6 mb-2">
                <label class="d-flex align-items-center gap-2 mb-0">
                  <input type="checkbox" class="ud-country"
                         value="<?php echo htmlspecialchars($row['country']); ?>">
                  <?php
                    // Each country carries its own date, because that is where
                    // the actionable signal is: one country weeks behind while
                    // the rest are current is the normal failure here, and a
                    // single summary line cannot show it.
                    $rowStale = $row['lastSync'] === null
                        || strtotime($row['lastSync']) < time() - UP_DOWN_STALE_AFTER_HOURS * 3600;
                  ?>
                  <span>
                    <strong><?php echo htmlspecialchars($row['country']); ?></strong>
                    <small class="<?php echo $rowStale ? 'text-danger' : 'text-muted'; ?>">
                      <?php echo number_format($row['shops']); ?> shops,
                      <?php echo number_format($row['withEmail']); ?> with email
                      <?php if ($row['lastSync'] === null): ?>
                        — never synced
                      <?php else: ?>
                        — <?php echo htmlspecialchars(substr($row['lastSync'], 0, 10)); ?>
                      <?php endif; ?>
                    </small>
                  </span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-3">
            <button id="udSyncSelected" class="btn btn-primary">
              <i class="bi bi-arrow-repeat"></i> Sync selected
            </button>
            <button id="udSyncAll" class="btn btn-outline-primary">
              <i class="bi bi-globe"></i> Sync all
            </button>
            <span id="udSyncNote" class="text-muted small ms-2"></span>
          </div>

          <hr>

          <h5>Results</h5>
          <table class="table table-sm">
            <thead>
              <tr>
                <th style="width:120px">Scope</th>
                <th>Result</th>
                <th style="width:90px" class="text-end">Time</th>
              </tr>
            </thead>
            <tbody id="udSyncResults">
              <tr><td colspan="3" class="text-muted">No run yet.</td></tr>
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </section>
</div>

<script>
(function () {
    // Every country the page offers, in board order, so "Sync all" and the
    // checkbox list cannot disagree about what "all" means.
    var ALL_COUNTRIES = <?php echo json_encode($countries); ?>;

    var $results = $('#udSyncResults');
    var $note    = $('#udSyncNote');
    var $buttons = $('#udSyncSelected, #udSyncAll');
    var running  = false;

    // Returns the row so its pending state can be replaced in place when the
    // request finishes.
    function addRow(scope) {
        var $tr = $('<tr>').append(
            $('<td>').text(scope),
            $('<td>').html('<span class="spinner-border spinner-border-sm"></span> syncing…'),
            $('<td>').addClass('text-end').text('')
        );
        $results.append($tr);
        return $tr;
    }

    // Values are written with .text(), never .html(): every one of them comes
    // from Monday or from a PHP error message, and neither is trusted markup.
    function finishRow($tr, data) {
        var $cell = $tr.children().eq(1).empty();
        var $time = $tr.children().eq(2);

        $time.text(data.elapsed ? data.elapsed + 's' : '');

        if (!data.ok) {
            $tr.addClass('table-danger');
            $cell.append($('<strong>').text('Failed: '))
                 .append(document.createTextNode(data.message || 'Unknown error.'));
            return;
        }

        var parts = [];

        (data.countries || []).forEach(function (c) {
            if (c.error) {
                // The sync leaves a failed country's rows in place; saying so
                // stops anyone reading this as "the country was emptied".
                $tr.addClass('table-danger');
                parts.push(c.country + ': ' + c.error + ' (previous data kept)');
            } else {
                parts.push(
                    c.shops + ' shops (' + c.inserted + ' new, ' + c.updated +
                    ' updated, ' + c.deleted + ' removed), ' + c.withEmail + ' with email'
                );
            }
        });

        var s = data.subscriptions;
        if (s) {
            if (s.error) {
                $tr.addClass('table-danger');
                parts.push('subscriptions: ' + s.error + ' (previous data kept)');
            } else {
                parts.push(
                    s.subscriptions + ' active (' + s.matched.relation + ' by relation, ' +
                    s.matched.name + ' by name, ' + s.matched.none + ' unmatched)'
                );
            }
        }

        if (!$tr.hasClass('table-danger')) { $tr.addClass('table-success'); }
        $cell.text(parts.join(' · ') || 'Done.');
    }

    function postSync(payload) {
        return $.ajax({
            url: 'pages/api/mondaySyncRun.php',
            method: 'POST',
            data: payload,
            dataType: 'json'
        });
    }

    function failed($tr) {
        finishRow($tr, {
            ok: false,
            message: 'Request failed. The sync may still be running — check ' +
                     'mirror-sync.log before retrying.'
        });
    }

    // Sequential, not parallel: each country is a multi-second Monday run, and
    // firing six at once would multiply the API load for no gain, since the
    // user waits on the slowest either way.
    function runQueue(countries) {
        if (running) { return; }
        running = true;
        $buttons.prop('disabled', true);
        $results.empty();

        var i = 0;

        function next() {
            if (i >= countries.length) { return runSubscriptions(); }

            var country = countries[i++];
            var $tr     = addRow(country);

            postSync({ country: country })
                .done(function (data) { finishRow($tr, data); })
                .fail(function () { failed($tr); })
                .always(next);
        }

        // Skipped on every country request and run once here: it reads the
        // whole Active Subscriptions board, so running it per country would
        // repeat the same work six times.
        function runSubscriptions() {
            var $tr = addRow('subscriptions');

            postSync({ subs: '1' })
                .done(function (data) { finishRow($tr, data); })
                .fail(function () { failed($tr); })
                .always(function () {
                    running = false;
                    $buttons.prop('disabled', false);
                    $note.text('Done. Reload the page to update the counts above.');
                });
        }

        $note.text('Syncing… this can take a few minutes. Leave this page open.');
        next();
    }

    $('#udSyncSelected').on('click', function () {
        var picked = $('.ud-country:checked').map(function () {
            return $(this).val();
        }).get();

        if (!picked.length) {
            $note.text('Pick at least one country first.');
            return;
        }
        runQueue(picked);
    });

    $('#udSyncAll').on('click', function () {
        runQueue(ALL_COUNTRIES);
    });
})();
</script>
```

- [ ] **Step 3: Verify the page loads behind the login guard**

Open `http://localhost/masterPanel/main.php?p=mondaySync` while signed in.
Expected: the card renders, six country checkboxes appear, and the status banner reflects `MAX(synced_at)` from the mirror.

Then confirm the guard, signed out:
```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" \
  "http://localhost/masterPanel/main.php?p=mondaySync"
```
Expected: `302` with a redirect to `index.php`.

- [ ] **Step 4: Verify a real sync of the smallest country**

Set `sync_token` in `modules/upgrade-downgrade/assets/config.php` to a long random string, then on the page tick only **TH** and press "Sync selected".

Expected: the TH row shows a spinner, then turns green reading roughly `30 shops (0 new, 30 updated, 0 removed), 14 with email`, followed by a `subscriptions` row. Confirm both runs were logged:

```bash
tail -8 modules/upgrade-downgrade/assets/logs/mirror-sync.log
```
Expected: two fresh entries timestamped just now — one listing TH with `subscriptions: skipped`, one for the subscriptions pass.

- [ ] **Step 5: Verify the not-configured path reads clearly**

Temporarily blank `sync_token` in `assets/config.php`, reload the page, and press "Sync selected" with TH ticked.

Expected: a red row reading `Failed: Sync is not configured — set sync_token in modules/upgrade-downgrade/assets/config.php`. Restore the token afterwards.

- [ ] **Step 6: Commit**

```bash
git add pages/mondaySync.php assets/php/page_navigate.php
git commit -m "feat(masterPanel): add Monday mirror sync admin page

Staff can refresh the mirror behind the customer email lookup without shell
access, per country or all at once. Countries run sequentially, one request
each, so progress is visible and no single request risks the time limit; the
subscriptions pass runs once at the end rather than repeating per country.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 5: Document the page and the remaining cron gap

The sync README is the operational reference for this mirror. It currently says the sync is CLI-only and that no schedule exists; both need updating now that a UI exists — and the cron gap must stay visible, because the button does not close it.

**Files:**
- Modify: `modules/upgrade-downgrade/sync/README.md`

- [ ] **Step 1: Document the admin page**

In `modules/upgrade-downgrade/sync/README.md`, immediately after the paragraph beginning `It is CLI-only unless `sync_token` is set`, add:

```markdown
### From the admin page

Staff can run the sync from `main.php?p=mondaySync` in the master panel. The
page lists every country with its current mirrored row count, syncs the ones
that are ticked (or all six), and reports each result as it lands.

It syncs **one country per request**, then runs the subscriptions pass once at
the end. A full run through the page therefore makes seven requests rather than
one: the subscriptions pass reads the whole Active Subscriptions board, so
repeating it per country would be six times the work for the same result.

The page requires `sync_token` to be set in `assets/config.php`. The token is
read server-side by `pages/api/mondaySyncRun.php` and is never sent to the
browser — do not move it into the page's JavaScript, or anyone who can load the
page can trigger unlimited multi-minute Monday runs.

Closing the browser does not stop a run in progress. If a request times out,
check `assets/logs/mirror-sync.log` before retrying: the server-side sync is
probably still going, and a retry would start a second one for the same country.
```

- [ ] **Step 2: Correct the schedule note**

Replace the existing `> **NOT YET REGISTERED.** ...` blockquote with:

```markdown
> **STILL NOT REGISTERED.** No scheduled task exists for this. The admin page
> (above) lets staff sync on demand, which is not a substitute: without cron the
> mirror only updates when somebody remembers to press the button. Register the
> cron below at deploy, and treat the page as the tool for when staff need data
> sooner than the next scheduled run.
```

- [ ] **Step 3: Commit**

```bash
git add modules/upgrade-downgrade/sync/README.md
git commit -m "docs(upgrade-downgrade): document the admin sync page

Records how the page drives the sync, why it issues one request per country,
and that sync_token must stay server-side. The schedule warning is kept and
sharpened: an on-demand button is not a replacement for the unregistered cron.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Verification

After all tasks, confirm nothing in the existing module regressed:

```bash
php modules/upgrade-downgrade/tests/test_mirror_schema.php
php modules/upgrade-downgrade/tests/test_mirror_repo.php
php modules/upgrade-downgrade/tests/test_mirror_orchestration.php
php modules/upgrade-downgrade/tests/test_shop_lookup.php
php modules/upgrade-downgrade/tests/test_lookup_endpoint.php
php modules/upgrade-downgrade/tests/test_sync_status.php
php modules/upgrade-downgrade/tests/test_sync_endpoint.php
```
Expected: `Failed: 0` from each.

And that the cron contract is intact:

```bash
php modules/upgrade-downgrade/sync/syncMondayMirror.php TH
```
Expected: the usual text summary on stdout, exit 0, one new line in `assets/logs/mirror-sync.log`. No JSON anywhere in the output.
