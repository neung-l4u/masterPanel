# Domain Monitor System — Design Spec
Date: 2026-05-19

## Overview

A domain/URL monitoring system built into masterPanel. Monitors HTTP status, response time, and SSL certificate expiry for multiple target categories. Sends notifications on status changes via email, Line Notify, and webhooks.

---

## Database Schema

### Table: `monitors`

```sql
CREATE TABLE `monitors` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `name`            VARCHAR(255) NOT NULL,
  `url`             VARCHAR(500) NOT NULL,
  `category`        ENUM('client','competitor','third_party','payment_gateway','api_endpoint','supplier') NOT NULL,
  `source_wID`      INT NULL,                          -- FK websiteList.wID (auto-imported)
  `check_interval`  INT NOT NULL DEFAULT 5,            -- minutes
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `notify_email`    VARCHAR(500) NULL,                 -- comma-separated emails
  `notify_line`     VARCHAR(500) NULL,                 -- Line Notify token
  `notify_webhook`  VARCHAR(500) NULL,
  `last_checked_at` DATETIME NULL,
  `last_status`     ENUM('up','down','unknown') DEFAULT 'unknown',
  `last_response_ms` INT NULL,
  `ssl_expiry_date` DATE NULL,
  `ssl_days_left`   INT NULL,
  `create_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_at`       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `delete_at`       DATETIME NULL
);
```

### Table: `monitor_logs`

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

---

## Architecture

### Approach
PHP Cron (Windows Task Scheduler) + DataTables UI — fits existing XAMPP stack.

### Files

| File | Purpose |
|------|---------|
| `assets/php/check_monitor.php` | Standalone cron script — no session, runs every 1 min via Task Scheduler |
| `assets/php/actionMonitor.php` | CRUD, manual check, dashboard stats, log data |
| `pages/monitor.php` | Main page — stats cards + DataTable + modals |
| `pages/tableRendering/datamonitor.php` | DataTables server-side JSON |

`assets/php/page_navigate.php` — `case "monitor"` already exists.

### Data Flow

```
Windows Task Scheduler (every 1 min)
  → check_monitor.php
      → SELECT monitors WHERE is_active=1 AND (last_checked_at IS NULL
          OR last_checked_at <= NOW() - INTERVAL check_interval MINUTE)
      → foreach target:
          → cURL: HTTP status code + response time
          → openssl stream context: SSL cert expiry date
          → INSERT monitor_logs
          → UPDATE monitors (last_status, last_checked_at, ssl_*)
          → IF status changed:
              → 'down'     → send alert notifications
              → 'up'       → send recovery notifications
          → IF ssl_days_left <= 30 → send SSL warning (once per day)
```

### Notification Channels
- **Email** — PHP `mail()`, send to `notify_email` (comma-separated)
- **Line Notify** — HTTP POST to `https://notify-api.line.me/api/notify` with token
- **Webhook** — HTTP POST JSON payload to `notify_webhook` URL
- Only fires on **status change** (not every check) to avoid spam
- SSL warning fires once per day when `ssl_days_left <= 30`

---

## Auto-import from websiteList

Triggered on `monitor.php` page load (PHP side, before rendering):

```
SELECT wID, wProject, wDomain FROM websiteList
WHERE delete_at IS NULL AND wLiveStatus = 'Live'
  AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)
→ INSERT into monitors (name=wProject, url=wDomain, category='client', source_wID=wID)
```

Also available as manual "Sync from Website List" button for re-syncing.

---

## UI — monitor.php

### Stats Cards (top)
- ✅ Up count
- 🔴 Down count  
- ⚠️ SSL expiring ≤ 30 days
- Total monitors

### Filters
- Category dropdown
- Status dropdown (up / down / unknown)
- "Sync Website List" button
- "+ Add" button

### DataTable columns
`#` | `Name` | `URL` | `Category` | `Interval` | `Status` | `SSL` | `Last Check` | `Actions`

Actions per row: **[Check Now]** **[Edit]** **[Logs]** **[Delete]**

### Status Badge
- 🟢 `up` → `badge-success`
- 🔴 `down` → `badge-danger`
- ⚪ `unknown` → `badge-secondary`

### SSL Column
- ✅ > 30 days remaining
- ⚠️ ≤ 30 days (warning color)
- 🔴 expired

### Modals
1. **Form Modal** (Add/Edit) — name, url, category, check_interval, notify_email, notify_line, notify_webhook, is_active toggle
2. **Log Modal** — paginated `monitor_logs` for selected target: checked_at, status, http_code, response_ms, ssl_days_left, error_msg
3. **Downtime Modal** — uptime % (last 30 days), list of downtime incidents with start time + duration

---

## check_monitor.php — SSL Check Method

Use PHP stream context with SSL capture:

```php
$context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
$client  = stream_socket_client("ssl://{$host}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
$cert    = stream_context_get_params($client)["options"]["ssl"]["peer_certificate"];
$info    = openssl_x509_parse($cert);
$expiry  = date("Y-m-d", $info["validTo_time_t"]);
```

---

## Out of Scope
- No authentication per-monitor (uses existing masterPanel session)
- No public status page
- No SMS notifications
