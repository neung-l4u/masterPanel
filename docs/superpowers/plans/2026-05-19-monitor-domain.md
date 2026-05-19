# Domain Monitor System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a domain/URL monitoring system inside masterPanel that auto-imports client websites, checks HTTP status + SSL expiry on a per-target interval, and sends notifications on status changes.

**Architecture:** PHP cron script (`check_monitor.php`) runs every 1 minute via Windows Task Scheduler, queries monitors due for a check, fires cURL for HTTP + openssl stream for SSL, writes to `monitor_logs`, and sends notifications only on status change. The UI (`monitor.php`) renders stats cards + DataTable with CRUD modals, auto-importing from `websiteList` on load.

**Tech Stack:** PHP 7.4+, MySQL (custom `db` class with `$db->query()`), Bootstrap 5, jQuery DataTables, cURL, openssl stream context, Line Notify API, PHP `mail()`

---

## File Map

| File | Action | Responsibility |
|------|--------|----------------|
| `assets/php/check_monitor.php` | Create | Cron script: fetch due monitors, cURL check, SSL check, log, notify |
| `assets/php/actionMonitor.php` | Create | AJAX handler: CRUD, manual check, stats, log data, sync |
| `pages/monitor.php` | Modify | Main page: auto-import, stats cards, DataTable, modals |
| `pages/tableRendering/datamonitor.php` | Modify | DataTables JSON endpoint |

---

## Task 1: Create Database Tables

**Files:**
- Run SQL directly in phpMyAdmin or MySQL CLI against `db_localforyou`

- [ ] **Step 1: Create `monitors` table**

Run in phpMyAdmin → `db_localforyou`:

```sql
CREATE TABLE `monitors` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `name`             VARCHAR(255) NOT NULL,
  `url`              VARCHAR(500) NOT NULL,
  `category`         ENUM('client','competitor','third_party','payment_gateway','api_endpoint','supplier') NOT NULL DEFAULT 'client',
  `source_wID`       INT NULL,
  `check_interval`   INT NOT NULL DEFAULT 5,
  `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
  `notify_email`     VARCHAR(500) NULL,
  `notify_line`      VARCHAR(500) NULL,
  `notify_webhook`   VARCHAR(500) NULL,
  `last_checked_at`  DATETIME NULL,
  `last_status`      ENUM('up','down','unknown') DEFAULT 'unknown',
  `last_response_ms` INT NULL,
  `ssl_expiry_date`  DATE NULL,
  `ssl_days_left`    INT NULL,
  `create_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at`        DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `delete_at`        DATETIME NULL
);
```

- [ ] **Step 2: Create `monitor_logs` table**

```sql
CREATE TABLE `monitor_logs` (
  `id`            BIGINT AUTO_INCREMENT PRIMARY KEY,
  `monitor_id`    INT NOT NULL,
  `checked_at`    DATETIME NOT NULL,
  `status`        ENUM('up','down') NOT NULL,
  `http_code`     SMALLINT NULL,
  `response_ms`   INT NULL,
  `ssl_expiry`    DATE NULL,
  `ssl_days_left` INT NULL,
  `error_msg`     TEXT NULL,
  `check_type`    ENUM('auto','manual') DEFAULT 'auto',
  INDEX `idx_monitor_checked` (`monitor_id`, `checked_at`)
);
```

- [ ] **Step 3: Verify tables exist**

Run: `SHOW TABLES LIKE 'monitor%';`  
Expected output: rows for `monitors` and `monitor_logs`

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "feat: create monitors and monitor_logs tables"
```

---

## Task 2: Create `check_monitor.php` — Core Check Engine

**Files:**
- Create: `assets/php/check_monitor.php`

This is a standalone CLI/cron script. No session. No HTML output.

- [ ] **Step 1: Create the file with DB connection + due-monitor query**

Create `assets/php/check_monitor.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../db/db.php';

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'db_localforyou';
$db = new db($dbHost, $dbUser, $dbPass, $dbName);

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
```

- [ ] **Step 2: Test the script manually from CLI**

Open PowerShell, run:
```powershell
php "c:\xampp\htdocs\masterPanel\assets\php\check_monitor.php"
```
Expected: no output (script is silent). If DB error, message will print.

- [ ] **Step 3: Commit**

```bash
git add assets/php/check_monitor.php
git commit -m "feat: add check_monitor.php cron script with HTTP + SSL check and notifications"
```

---

## Task 3: Create `actionMonitor.php` — AJAX Handler

**Files:**
- Create: `assets/php/actionMonitor.php`

Handles: `save`, `loadUpdate`, `setDelete`, `manualCheck`, `getStats`, `getLogs`, `syncWebsiteList`

- [ ] **Step 1: Create the file**

Create `assets/php/actionMonitor.php`:

```php
<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
include '../../assets/security/Sanitizer.php';

$act    = !empty($_POST['act']) ? $_POST['act'] : '';
$id     = !empty($_POST['id'])  ? (int)$_POST['id'] : 0;
$params = [];

// ── SAVE (add / edit) ──────────────────────────────────────────────────────
if ($act === 'save') {
    $formAction     = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';
    $inputName      = !empty($_POST['inputName'])     ? $_POST['inputName']     : '';
    $inputUrl       = !empty($_POST['inputUrl'])       ? $_POST['inputUrl']       : '';
    $inputCategory  = !empty($_POST['inputCategory'])  ? $_POST['inputCategory']  : 'client';
    $inputInterval  = !empty($_POST['inputInterval'])  ? (int)$_POST['inputInterval'] : 5;
    $inputEmail     = !empty($_POST['inputEmail'])     ? $_POST['inputEmail']     : '';
    $inputLine      = !empty($_POST['inputLine'])      ? $_POST['inputLine']      : '';
    $inputWebhook   = !empty($_POST['inputWebhook'])   ? $_POST['inputWebhook']   : '';
    $inputActive    = isset($_POST['inputActive'])     ? (int)$_POST['inputActive'] : 1;
    $editID         = !empty($_POST['editID'])         ? (int)$_POST['editID']    : 0;

    if ($formAction === 'add') {
        $db->query(
            "INSERT INTO monitors (name, url, category, check_interval, is_active, notify_email, notify_line, notify_webhook)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            $inputName, $inputUrl, $inputCategory, $inputInterval,
            $inputActive, $inputEmail, $inputLine, $inputWebhook
        );
        $params['insertedID'] = $db->lastInsertID();
    } else {
        $db->query(
            "UPDATE monitors SET name=?, url=?, category=?, check_interval=?, is_active=?,
             notify_email=?, notify_line=?, notify_webhook=?, update_at=NOW()
             WHERE id=? AND delete_at IS NULL",
            $inputName, $inputUrl, $inputCategory, $inputInterval,
            $inputActive, $inputEmail, $inputLine, $inputWebhook, $editID
        );
        $params['affected'] = $db->affectedRows();
    }
    $params['status'] = 'ok';

// ── LOAD FOR EDIT ──────────────────────────────────────────────────────────
} elseif ($act === 'loadUpdate') {
    $row = $db->query("SELECT * FROM monitors WHERE id=? AND delete_at IS NULL", $id)->fetchArray();
    $params = $row ?: [];
    $params['status'] = $row ? 'ok' : 'not_found';

// ── DELETE ─────────────────────────────────────────────────────────────────
} elseif ($act === 'setDelete') {
    $db->query("UPDATE monitors SET delete_at=NOW() WHERE id=?", $id);
    $params['status'] = 'ok';

// ── MANUAL CHECK ───────────────────────────────────────────────────────────
} elseif ($act === 'manualCheck') {
    $monitor = $db->query("SELECT * FROM monitors WHERE id=? AND delete_at IS NULL", $id)->fetchArray();
    if (!$monitor) {
        $params['status'] = 'not_found';
    } else {
        require_once __DIR__ . '/check_monitor.php';
        // check_monitor.php defines checkTarget() and saveResult() as global functions
        // but also runs the main loop — we need just the functions.
        // To avoid re-running the loop, check_monitor.php guards with CRON_RUN constant.
        // (See Task 2 Step 1 update below — add guard to check_monitor.php)
        $result = checkTarget($monitor);

        // Insert log with check_type = 'manual'
        $db->query(
            "INSERT INTO monitor_logs (monitor_id, checked_at, status, http_code, response_ms, ssl_expiry, ssl_days_left, error_msg, check_type)
             VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, 'manual')",
            $monitor['id'], $result['status'], $result['httpCode'],
            $result['responseMs'], $result['sslExpiry'], $result['sslDaysLeft'], $result['errorMsg']
        );
        $db->query(
            "UPDATE monitors SET last_checked_at=NOW(), last_status=?, last_response_ms=?,
             ssl_expiry_date=?, ssl_days_left=?, update_at=NOW() WHERE id=?",
            $result['status'], $result['responseMs'],
            $result['sslExpiry'], $result['sslDaysLeft'], $monitor['id']
        );
        $params = array_merge($params, $result);
        $params['status'] = 'ok';
    }

// ── STATS CARDS ────────────────────────────────────────────────────────────
} elseif ($act === 'getStats') {
    $rows = $db->query(
        "SELECT
            SUM(last_status = 'up')   AS cnt_up,
            SUM(last_status = 'down') AS cnt_down,
            SUM(last_status = 'unknown') AS cnt_unknown,
            COUNT(*) AS cnt_total,
            SUM(ssl_days_left IS NOT NULL AND ssl_days_left <= 30 AND ssl_days_left >= 0) AS cnt_ssl_warn
         FROM monitors WHERE is_active=1 AND delete_at IS NULL"
    )->fetchArray();
    $params = $rows;
    $params['status'] = 'ok';

// ── GET LOGS ───────────────────────────────────────────────────────────────
} elseif ($act === 'getLogs') {
    $logs = $db->query(
        "SELECT checked_at, status, http_code, response_ms, ssl_days_left, error_msg, check_type
         FROM monitor_logs WHERE monitor_id=? ORDER BY checked_at DESC LIMIT 100",
        $id
    )->fetchAll();
    $params['data']   = $logs;
    $params['status'] = 'ok';

// ── GET DOWNTIME SUMMARY ───────────────────────────────────────────────────
} elseif ($act === 'getDowntime') {
    // Uptime % over last 30 days
    $totals = $db->query(
        "SELECT
            SUM(status='up')   AS cnt_up,
            SUM(status='down') AS cnt_down,
            COUNT(*)           AS cnt_total
         FROM monitor_logs
         WHERE monitor_id=? AND checked_at >= NOW() - INTERVAL 30 DAY",
        $id
    )->fetchArray();

    $uptimePct = $totals['cnt_total'] > 0
        ? round(($totals['cnt_up'] / $totals['cnt_total']) * 100, 2)
        : null;

    // Downtime incidents: consecutive 'down' blocks
    $allLogs = $db->query(
        "SELECT checked_at, status FROM monitor_logs
         WHERE monitor_id=? AND checked_at >= NOW() - INTERVAL 30 DAY
         ORDER BY checked_at ASC",
        $id
    )->fetchAll();

    $incidents  = [];
    $downStart  = null;
    $lastDownAt = null;

    foreach ($allLogs as $log) {
        if ($log['status'] === 'down') {
            if ($downStart === null) $downStart = $log['checked_at'];
            $lastDownAt = $log['checked_at'];
        } else {
            if ($downStart !== null) {
                $durationSec = strtotime($lastDownAt) - strtotime($downStart);
                $incidents[] = ['start' => $downStart, 'end' => $lastDownAt, 'duration_min' => (int)ceil($durationSec / 60)];
                $downStart   = null;
                $lastDownAt  = null;
            }
        }
    }
    // Still down
    if ($downStart !== null) {
        $durationSec = strtotime($lastDownAt) - strtotime($downStart);
        $incidents[] = ['start' => $downStart, 'end' => null, 'duration_min' => (int)ceil($durationSec / 60)];
    }

    $params['uptime_pct'] = $uptimePct;
    $params['incidents']  = $incidents;
    $params['status']     = 'ok';

// ── SYNC FROM WEBSITE LIST ─────────────────────────────────────────────────
} elseif ($act === 'syncWebsiteList') {
    $websites = $db->query(
        "SELECT wID, wProject, wDomain FROM websiteList
         WHERE delete_at IS NULL AND wLiveStatus = 'Live'
           AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)"
    )->fetchAll();

    $inserted = 0;
    foreach ($websites as $w) {
        $url = trim($w['wDomain']);
        if (empty($url)) continue;
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
        $db->query(
            "INSERT INTO monitors (name, url, category, source_wID, check_interval) VALUES (?, ?, 'client', ?, 5)",
            $w['wProject'], $url, $w['wID']
        );
        $inserted++;
    }
    $params['inserted'] = $inserted;
    $params['status']   = 'ok';
}

echo json_encode($params);
```

- [ ] **Step 2: Update `check_monitor.php` to guard against re-execution when included**

Edit `assets/php/check_monitor.php` — wrap the main loop with a guard so `actionMonitor.php` can safely `require_once` it for the `checkTarget()` function:

At the top of `check_monitor.php`, after the `$db = new db(...)` line, change the main loop block:

```php
// Only run the main loop when executed directly as cron (not included)
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
```

And in `actionMonitor.php`, before the `require_once`, add:

```php
define('MONITOR_FUNCTIONS_ONLY', true);
require_once __DIR__ . '/check_monitor.php';
```

- [ ] **Step 3: Commit**

```bash
git add assets/php/actionMonitor.php assets/php/check_monitor.php
git commit -m "feat: add actionMonitor.php AJAX handler and guard check_monitor.php for include"
```

---

## Task 4: Create `datamonitor.php` — DataTables Endpoint

**Files:**
- Modify: `pages/tableRendering/datamonitor.php`

- [ ] **Step 1: Write the file**

Replace contents of `pages/tableRendering/datamonitor.php`:

```php
<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

$category = !empty($_POST['category']) ? $_POST['category'] : '';
$status   = !empty($_POST['status'])   ? $_POST['status']   : '';

$qb = new QueryBuilder();
$qb->eq('m.category',    $category)
   ->eq('m.last_status', $status);

$baseSql = "SELECT m.* FROM monitors m WHERE m.delete_at IS NULL AND m.is_active = 1";
$result  = $qb->execute($db, $baseSql, 'ORDER BY m.id DESC')->fetchAll();

$data = ['data' => []];
$i    = 1;

foreach ($result as $row) {
    // Status badge
    $badgeClass = match($row['last_status']) {
        'up'      => 'success',
        'down'    => 'danger',
        default   => 'secondary',
    };
    $statusBadge = '<span class="badge bg-' . $badgeClass . '">' . esc($row['last_status']) . '</span>';

    // SSL column
    $sslDays = $row['ssl_days_left'];
    if ($sslDays === null) {
        $sslDisplay = '<span class="text-muted">-</span>';
    } elseif ($sslDays < 0) {
        $sslDisplay = '<span class="badge bg-danger">Expired</span>';
    } elseif ($sslDays <= 30) {
        $sslDisplay = '<span class="badge bg-warning text-dark">⚠ ' . $sslDays . 'd</span>';
    } else {
        $sslDisplay = '<span class="text-success">✓ ' . $sslDays . 'd</span>';
    }

    // Last check
    $lastCheck = $row['last_checked_at'] ?? '-';

    // URL display
    $urlDisplay = '<a href="' . escUrl($row['url']) . '" target="_blank" class="text-truncate d-inline-block" style="max-width:200px" title="' . escAttr($row['url']) . '">' . esc($row['url']) . '</a>';

    // Category badge
    $catColors = [
        'client'          => 'primary',
        'competitor'      => 'warning',
        'third_party'     => 'info',
        'payment_gateway' => 'success',
        'api_endpoint'    => 'dark',
        'supplier'        => 'secondary',
    ];
    $catColor   = $catColors[$row['category']] ?? 'secondary';
    $catDisplay = '<span class="badge bg-' . $catColor . '">' . esc($row['category']) . '</span>';

    // Actions
    $monId  = (int)$row['id'];
    $btnCheck   = '<a href="#" onclick="manualCheck(' . $monId . ')" title="Check Now"><i class="bi bi-arrow-clockwise text-primary"></i></a>';
    $btnEdit    = '<a href="#" onclick="setEdit(' . $monId . ')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btnLogs    = '<a href="#" onclick="viewLogs(' . $monId . ')" title="Logs"><i class="bi bi-journal-text text-secondary"></i></a>';
    $btnDown    = '<a href="#" onclick="viewDowntime(' . $monId . ')" title="Downtime"><i class="bi bi-graph-down text-warning"></i></a>';
    $btnDelete  = '<a href="#" onclick="setDel(' . $monId . ')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';

    $data['data'][] = [
        $i,
        esc($row['name']),
        $urlDisplay,
        $catDisplay,
        $row['check_interval'] . ' min',
        $statusBadge,
        $sslDisplay,
        esc($lastCheck),
        $btnCheck . ' ' . $btnEdit . ' ' . $btnLogs . ' ' . $btnDown . ' ' . $btnDelete,
    ];
    $i++;
}

echo json_encode($data);
```

- [ ] **Step 2: Verify JSON output manually**

Open browser: `http://localhost/masterPanel/pages/tableRendering/datamonitor.php`  
Expected: `{"data":[]}` (empty array since no monitors yet — no errors)

- [ ] **Step 3: Commit**

```bash
git add pages/tableRendering/datamonitor.php
git commit -m "feat: add datamonitor.php DataTables endpoint"
```

---

## Task 5: Build `monitor.php` — Main UI

**Files:**
- Modify: `pages/monitor.php`

- [ ] **Step 1: Replace monitor.php with full UI**

Replace entire contents of `pages/monitor.php`:

```php
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db;

// Auto-import from websiteList on page load
$toImport = $db->query(
    "SELECT wID, wProject, wDomain FROM websiteList
     WHERE delete_at IS NULL AND wLiveStatus = 'Live'
       AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)"
)->fetchAll();

foreach ($toImport as $w) {
    $url = trim($w['wDomain']);
    if (empty($url)) continue;
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    $db->query(
        "INSERT INTO monitors (name, url, category, source_wID, check_interval) VALUES (?, ?, 'client', ?, 5)",
        $w['wProject'], $url, $w['wID']
    );
}
?>
<style>
    .filterCol { width: 30%; max-width: 280px; }
    .filterLabel { width: 100px; }
    .filterSelect { width: 100% !important; }
    div.dataTables_wrapper div.dataTables_length select { width: 100%; }
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-activity me-2"></i>Domain Monitor
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active">Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Stats Cards -->
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Up</div><div class="fs-4 fw-bold text-success" id="statUp">-</div></div>
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Down</div><div class="fs-4 fw-bold text-danger" id="statDown">-</div></div>
                            <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">SSL ≤ 30d</div><div class="fs-4 fw-bold text-warning" id="statSsl">-</div></div>
                            <i class="bi bi-shield-exclamation text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Total</div><div class="fs-4 fw-bold" id="statTotal">-</div></div>
                            <i class="bi bi-globe fs-3 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters + Toolbar -->
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0"><i class="bi bi-funnel me-1"></i>Filters
                    <button class="btn btn-sm btn-outline-secondary px-2 pt-0 pb-1 ms-1" onclick="filterAll()">
                        <small><i class="bi bi-x-lg"></i> Clear</small>
                    </button>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info" onclick="syncWebsiteList()">
                        <i class="bi bi-arrow-repeat"></i> Sync Website List
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openFormModal()">
                        <i class="bi bi-plus"></i> Add Monitor
                    </button>
                </div>
            </div>
            <div class="d-flex flex-row gap-4">
                <div class="filterCol">
                    <label class="form-label filterLabel">Category</label>
                    <select class="form-select filterSelect" id="filterCategory" onchange="reloadTable()">
                        <option value="">All</option>
                        <option value="client">Client</option>
                        <option value="competitor">Competitor</option>
                        <option value="third_party">Third Party</option>
                        <option value="payment_gateway">Payment Gateway</option>
                        <option value="api_endpoint">API Endpoint</option>
                        <option value="supplier">Supplier</option>
                    </select>
                </div>
                <div class="filterCol">
                    <label class="form-label filterLabel">Status</label>
                    <select class="form-select filterSelect" id="filterStatus" onchange="reloadTable()">
                        <option value="">All</option>
                        <option value="up">Up</option>
                        <option value="down">Down</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card p-3">
            <div class="card-body">
                <table id="monitorTable" class="table table-borderless table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th width="4%">#</th>
                            <th>Name</th>
                            <th>URL</th>
                            <th width="12%">Category</th>
                            <th width="8%">Interval</th>
                            <th width="8%">Status</th>
                            <th width="9%">SSL</th>
                            <th width="14%">Last Check</th>
                            <th width="10%"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- ── FORM MODAL (Add/Edit) ── -->
        <div class="modal fade" id="formModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-file-earmark-plus"></i> <span id="formModalTitle">Add Monitor</span></h5>
                        <button type="button" class="close" onclick="closeFormModal()"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="container py-2">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="inputName" placeholder="e.g. True Thai Cairns">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Category</label>
                                    <select class="form-select" id="inputCategory">
                                        <option value="client">Client</option>
                                        <option value="competitor">Competitor</option>
                                        <option value="third_party">Third Party</option>
                                        <option value="payment_gateway">Payment Gateway</option>
                                        <option value="api_endpoint">API Endpoint</option>
                                        <option value="supplier">Supplier</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" class="form-control" id="inputUrl" placeholder="https://www.example.com">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Check Interval (minutes)</label>
                                    <input type="number" class="form-control" id="inputInterval" value="5" min="1" max="1440">
                                </div>
                                <div class="form-group col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="inputActive" checked>
                                        <label class="form-check-label" for="inputActive">Active</label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6>Notifications</h6>
                            <div class="form-group">
                                <label>Email (comma-separated)</label>
                                <input type="text" class="form-control" id="inputEmail" placeholder="alert@company.com, dev@company.com">
                            </div>
                            <div class="form-group">
                                <label>Line Notify Token</label>
                                <input type="text" class="form-control" id="inputLine" placeholder="Line Notify token">
                            </div>
                            <div class="form-group">
                                <label>Webhook URL</label>
                                <input type="text" class="form-control" id="inputWebhook" placeholder="https://hooks.example.com/...">
                            </div>
                            <input type="hidden" id="editID" value="">
                            <input type="hidden" id="formAction" value="add">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="closeFormModal()">Close</button>
                        <button class="btn btn-primary" onclick="formSave()">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── LOG MODAL ── -->
        <div class="modal fade" id="logModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-journal-text"></i> Check Logs</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-striped" id="logTable">
                            <thead>
                                <tr>
                                    <th>Checked At</th><th>Status</th><th>HTTP</th>
                                    <th>Response</th><th>SSL Days</th><th>Type</th><th>Error</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── DOWNTIME MODAL ── -->
        <div class="modal fade" id="downtimeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-graph-down"></i> Downtime Summary (Last 30 Days)</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Uptime:</strong> <span id="uptimePct" class="fs-5 fw-bold text-success">-</span>
                        </div>
                        <table class="table table-sm table-bordered" id="downtimeTable">
                            <thead>
                                <tr><th>Start</th><th>End</th><th>Duration (min)</th></tr>
                            </thead>
                            <tbody id="downtimeTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</div><!-- /.content -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>

<script>
const formModal     = new bootstrap.Modal(document.getElementById('formModal'), {});
const logModal      = new bootstrap.Modal(document.getElementById('logModal'), {});
const downtimeModal = new bootstrap.Modal(document.getElementById('downtimeModal'), {});

// ── Stats ──────────────────────────────────────────────
function loadStats() {
    $.post('assets/php/actionMonitor.php', { act: 'getStats' }, function(res) {
        $('#statUp').text(res.cnt_up ?? 0);
        $('#statDown').text(res.cnt_down ?? 0);
        $('#statSsl').text(res.cnt_ssl_warn ?? 0);
        $('#statTotal').text(res.cnt_total ?? 0);
    }, 'json');
}

// ── Table ──────────────────────────────────────────────
function reloadTable() {
    $('#monitorTable').DataTable().ajax.reload();
    loadStats();
}

function filterAll() {
    $('#filterCategory').val('');
    $('#filterStatus').val('');
    reloadTable();
}

// ── Form Modal ─────────────────────────────────────────
function openFormModal() {
    resetForm();
    $('#formModalTitle').text('Add Monitor');
    formModal.show();
}

function closeFormModal() {
    formModal.hide();
    $('.modal-backdrop').hide();
}

function resetForm() {
    $('#inputName').val('');
    $('#inputUrl').val('');
    $('#inputCategory').val('client');
    $('#inputInterval').val(5);
    $('#inputActive').prop('checked', true);
    $('#inputEmail').val('');
    $('#inputLine').val('');
    $('#inputWebhook').val('');
    $('#editID').val('');
    $('#formAction').val('add');
}

function formSave() {
    $.post('assets/php/actionMonitor.php', {
        act:           'save',
        formAction:    $('#formAction').val(),
        inputName:     $('#inputName').val(),
        inputUrl:      $('#inputUrl').val(),
        inputCategory: $('#inputCategory').val(),
        inputInterval: $('#inputInterval').val(),
        inputActive:   $('#inputActive').prop('checked') ? 1 : 0,
        inputEmail:    $('#inputEmail').val(),
        inputLine:     $('#inputLine').val(),
        inputWebhook:  $('#inputWebhook').val(),
        editID:        $('#editID').val(),
    }, function(res) {
        closeFormModal();
        reloadTable();
    }, 'json');
}

function setEdit(id) {
    $.post('assets/php/actionMonitor.php', { act: 'loadUpdate', id: id }, function(res) {
        $('#inputName').val(res.name);
        $('#inputUrl').val(res.url);
        $('#inputCategory').val(res.category);
        $('#inputInterval').val(res.check_interval);
        $('#inputActive').prop('checked', res.is_active == 1);
        $('#inputEmail').val(res.notify_email);
        $('#inputLine').val(res.notify_line);
        $('#inputWebhook').val(res.notify_webhook);
        $('#editID').val(res.id);
        $('#formAction').val('edit');
        $('#formModalTitle').text('Edit Monitor');
        formModal.show();
    }, 'json');
}

function setDel(id) {
    if (!confirm('Delete this monitor?')) return;
    $.post('assets/php/actionMonitor.php', { act: 'setDelete', id: id }, function() {
        reloadTable();
    }, 'json');
}

// ── Manual Check ───────────────────────────────────────
function manualCheck(id) {
    $.post('assets/php/actionMonitor.php', { act: 'manualCheck', id: id }, function(res) {
        reloadTable();
        alert('Check done: ' + res.status + ' (HTTP ' + (res.httpCode || '-') + ', ' + (res.responseMs || '-') + 'ms)');
    }, 'json');
}

// ── Logs Modal ─────────────────────────────────────────
function viewLogs(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getLogs', id: id }, function(res) {
        const rows = res.data.map(r => `
            <tr>
                <td>${r.checked_at}</td>
                <td><span class="badge bg-${r.status === 'up' ? 'success' : 'danger'}">${r.status}</span></td>
                <td>${r.http_code ?? '-'}</td>
                <td>${r.response_ms != null ? r.response_ms + 'ms' : '-'}</td>
                <td>${r.ssl_days_left ?? '-'}</td>
                <td>${r.check_type}</td>
                <td><small>${r.error_msg ?? ''}</small></td>
            </tr>`).join('');
        $('#logTableBody').html(rows || '<tr><td colspan="7" class="text-center">No logs</td></tr>');
        logModal.show();
    }, 'json');
}

// ── Downtime Modal ─────────────────────────────────────
function viewDowntime(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getDowntime', id: id }, function(res) {
        $('#uptimePct').text(res.uptime_pct !== null ? res.uptime_pct + '%' : 'No data');
        const rows = (res.incidents || []).map(inc => `
            <tr>
                <td>${inc.start}</td>
                <td>${inc.end ?? '<span class="text-danger">Still down</span>'}</td>
                <td>${inc.duration_min}</td>
            </tr>`).join('');
        $('#downtimeTableBody').html(rows || '<tr><td colspan="3" class="text-center">No downtime incidents</td></tr>');
        downtimeModal.show();
    }, 'json');
}

// ── Sync Website List ──────────────────────────────────
function syncWebsiteList() {
    $.post('assets/php/actionMonitor.php', { act: 'syncWebsiteList' }, function(res) {
        alert('Synced: ' + res.inserted + ' new monitors added.');
        reloadTable();
    }, 'json');
}

// ── Init ───────────────────────────────────────────────
$(() => {
    loadStats();

    $('#monitorTable').DataTable({
        pagingType: 'full_numbers',
        pageLength: 14,
        lengthMenu: [[14, 25, 50, -1], ['Fit', 25, 50, 'All']],
        ajax: {
            url:     'pages/tableRendering/datamonitor.php',
            type:    'POST',
            dataSrc: 'data',
            data: function(d) {
                d.category = $('#filterCategory').val();
                d.status   = $('#filterStatus').val();
            }
        },
        columnDefs: [
            { targets: -1, className: 'dt-body-right', orderable: false }
        ],
    });
});
</script>
```

- [ ] **Step 2: Commit**

```bash
git add pages/monitor.php
git commit -m "feat: build monitor.php UI with stats, DataTable, CRUD modals, manual check, logs, downtime"
```

---

## Task 6: Setup Windows Task Scheduler for Cron

- [ ] **Step 1: Open Task Scheduler**

Start menu → search "Task Scheduler" → Open

- [ ] **Step 2: Create Basic Task**

1. Click "Create Basic Task…"
2. Name: `MasterPanel Monitor Check`
3. Trigger: Daily → repeat every **1 minute** for a duration of **1 day** (set repeat interval in "Advanced settings" of trigger)
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `"C:\xampp\htdocs\masterPanel\assets\php\check_monitor.php"`
   - Start in: `C:\xampp\htdocs\masterPanel\assets\php`
5. Finish

- [ ] **Step 3: Test by running task manually**

In Task Scheduler → right-click task → "Run"  
Check `monitor_logs` table: `SELECT * FROM monitor_logs ORDER BY id DESC LIMIT 5;`  
Expected: rows appear with `check_type = 'auto'`

- [ ] **Step 4: Commit task scheduler notes**

```bash
git add -A
git commit -m "feat: complete domain monitor system — all files implemented"
```

---

## Task 7: End-to-End Smoke Test

- [ ] **Step 1: Open monitor page**

Navigate to: `http://localhost/masterPanel/main.php?p=monitor`  
Expected: page loads, stats cards show counts, DataTable shows imported client websites

- [ ] **Step 2: Add a manual non-client monitor**

Click "+ Add Monitor" → fill:
- Name: `Google`
- URL: `https://www.google.com`
- Category: `third_party`
- Interval: `5`

Click Save. Expected: row appears in table with status `unknown`.

- [ ] **Step 3: Run manual check**

Click the refresh icon (Check Now) on the Google row.  
Expected: alert shows `up (HTTP 200, ~XXXms)`, row updates to green `up` badge.

- [ ] **Step 4: Test manual check on a down URL**

Add a monitor with URL `https://this-domain-definitely-does-not-exist-xyz.com`, category `third_party`.  
Click Check Now.  
Expected: alert shows `down`, row shows red `down` badge.

- [ ] **Step 5: View logs**

Click the logs icon on any checked monitor.  
Expected: Log Modal opens showing rows with `checked_at`, `status`, `http_code`, `response_ms`.

- [ ] **Step 6: View downtime summary**

Click the graph icon on any checked monitor.  
Expected: Downtime Modal opens showing uptime % and incidents table.

- [ ] **Step 7: Test Sync Website List button**

Click "Sync Website List".  
Expected: alert says `Synced: 0 new monitors added` (already imported on load).

- [ ] **Step 8: Final commit if any fixes applied**

```bash
git add -A
git commit -m "fix: smoke test corrections"
```

---

## Self-Review

**Spec coverage check:**
- ✅ `monitors` + `monitor_logs` tables — Task 1
- ✅ HTTP status + response time check — Task 2 (`checkTarget`)
- ✅ SSL cert expiry check — Task 2 (`checkTarget` openssl)
- ✅ Per-target `check_interval` — Task 2 (WHERE clause), Task 5 (form field)
- ✅ Auto + manual check — Task 2 (cron loop) + Task 3 (`manualCheck`)
- ✅ Notification: email, Line Notify, webhook — Task 2 (`sendNotifications`)
- ✅ Notify on status change only — Task 2 (`handleNotifications` prevStatus check)
- ✅ SSL warning ≤ 30 days, once/day — Task 2 (`handleNotifications`)
- ✅ Auto-import from websiteList on page load — Task 5 (PHP block at top of monitor.php)
- ✅ Manual sync button — Task 3 (`syncWebsiteList`), Task 5 (button + JS)
- ✅ Stats cards (up/down/ssl/total) — Task 3 (`getStats`), Task 5 (cards + `loadStats()`)
- ✅ DataTable with filters (category, status) — Task 4, Task 5
- ✅ CRUD modals (add/edit/delete) — Task 3, Task 5
- ✅ Log modal (last 100 logs) — Task 3 (`getLogs`), Task 5
- ✅ Downtime modal (uptime%, incidents) — Task 3 (`getDowntime`), Task 5
- ✅ Windows Task Scheduler setup — Task 6
- ✅ Category badges (6 types) — Task 4
- ✅ Status badge colors — Task 4

**Type consistency:** `checkTarget()` returns `compact('status','httpCode','responseMs','errorMsg','sslExpiry','sslDaysLeft')` — used consistently in `saveResult()`, `handleNotifications()`, and `manualCheck` in actionMonitor.php ✓

**MONITOR_FUNCTIONS_ONLY guard** defined in actionMonitor.php before require_once, checked in check_monitor.php ✓
