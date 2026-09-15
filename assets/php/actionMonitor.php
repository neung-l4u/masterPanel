<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
include '../../assets/security/Sanitizer.php';
require_once __DIR__ . '/monitorScope.php';

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

/**
 * Is a full sweep in progress? The cron job runs as root and its lock file is
 * root-owned, so the web user often cannot open it at all — that is not the same
 * as the lock being held, and must not be reported as "busy". Fall back to looking
 * for the process itself, and treat "cannot tell" as not running.
 */
/**
 * Is another *button-triggered* sweep already running?
 *
 * Only these block each other. The scheduled cron run is deliberately ignored: it
 * works through whatever is due while this sweep re-checks a chosen set, and making
 * the user wait for it meant the button was unusable most of the time. The narrow
 * risk that remains — cron reaching the same site at the same moment and alerting
 * twice — is handled by check_monitor.php's own per-user lock.
 */
function monitorSweepRunning(): bool {
    $out = @shell_exec('ps -eo args 2>/dev/null');
    if ($out !== null) {
        foreach (explode("\n", (string) $out) as $line) {
            //Only a manual sweep carries --all or --down; cron passes no flag.
            if (str_contains($line, 'check_monitor.php')
                && (str_contains($line, '--all') || str_contains($line, '--down'))) {
                return true;
            }
        }
    }

    $marker = sys_get_temp_dir() . '/monitor_manual_sweep.marker';
    if (is_readable($marker)) {
        //Only trust a recent marker, so a crashed run cannot block the button forever.
        if (time() - (int) @filemtime($marker) < 60) { return true; }
        @unlink($marker);
    }
    return false;
}

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
    $db->query("UPDATE monitors SET delete_at=NOW() WHERE id=? AND delete_at IS NULL", $id);
    $params['status'] = 'ok';

// ── MANUAL CHECK ───────────────────────────────────────────────────────────
} elseif ($act === 'manualCheck') {
    $monitor = $db->query("SELECT * FROM monitors WHERE id=? AND delete_at IS NULL", $id)->fetchArray();
    if (!$monitor) {
        $params['status'] = 'not_found';
    } else {
        define('MONITOR_FUNCTIONS_ONLY', true);
        require_once __DIR__ . '/check_monitor.php';
        $result = checkTarget($monitor);

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

// ── CHECK EVERY MONITOR NOW ────────────────────────────────────────────────
// A full sweep takes ~20 minutes, far longer than a browser will wait, so the
// work is handed to the same cron script running in the background. It writes
// its own lock file, so a second click while one is running does nothing.
} elseif ($act === 'checkAllNow') {
    if (monitorSweepRunning()) {
        $params['status'] = 'already_running';
    } else {
        $script = escapeshellarg(__DIR__ . '/check_monitor.php');
        $php    = PHP_BINARY && str_contains(PHP_BINARY, 'php') ? PHP_BINARY : 'php';
        // Detach so the browser gets an answer immediately.
        //Re-check only the failing sites when asked from the Down list.
        $scope = ($_POST['scope'] ?? '') === 'down' ? '--down' : '--all';
        //Recorded before launching so progress counts only this run's checks.
        $params['startedAt'] = date('Y-m-d H:i:s');
        $params['scope']     = $scope === '--down' ? 'down' : 'all';
        //Claim the run immediately: the child takes a second to show up in `ps`,
        //and without this a quick second click starts a duplicate sweep.
        @touch(sys_get_temp_dir() . '/monitor_manual_sweep.marker');
        @exec(escapeshellarg($php) . ' ' . $script . ' ' . $scope . ' > /dev/null 2>&1 &');
        $params['status'] = 'started';
    }

// ── PROGRESS OF A RUNNING SWEEP ────────────────────────────────────────────
} elseif ($act === 'checkAllProgress') {
    $running = monitorSweepRunning();

    //Count only the monitors in this sweep's scope, so the scheduled cron running
    //alongside it cannot push the number past the total.
    $since = $_POST['since'] ?? date('Y-m-d H:i:s');
    $row = (($_POST['scope'] ?? '') === 'down')
        ? $db->query(
            "SELECT COUNT(*) AS checked FROM monitors
              WHERE is_active = 1 AND delete_at IS NULL
                AND last_status = 'down' AND last_checked_at >= ?", $since
          )->fetchArray()
        : $db->query(
            "SELECT COUNT(*) AS checked FROM monitors
              WHERE is_active = 1 AND delete_at IS NULL
                AND last_checked_at >= ?", $since
          )->fetchArray();
    $total = (($_POST['scope'] ?? '') === 'down')
        ? $db->query("SELECT COUNT(*) AS n FROM monitors WHERE is_active=1 AND delete_at IS NULL AND last_status='down'")->fetchArray()
        : $db->query("SELECT COUNT(*) AS n FROM monitors WHERE is_active=1 AND delete_at IS NULL")->fetchArray();

    //What the run has actually found so far, so the user is not left guessing why
    //nothing reached Google Chat: alerts fire on a status *change*, and a site that
    //was already down and is still down is not a change.
    //Outcome for the monitors this sweep touched, counted once each by their
    //current state. For a "down" sweep that means: how many came back up, and how
    //many are still failing.
    //Restrict to the monitors this sweep was asked to look at, so the scheduled
    //cron working through other sites at the same time cannot inflate the numbers.
    //A "down" sweep started from a monitor that had failed at least once today.
    $outcome = (($_POST['scope'] ?? '') === 'down')
        ? $db->query(
            "SELECT
                SUM(m.last_status = 'up')   AS recovered,
                SUM(m.last_status = 'down') AS still_down
             FROM monitors m
             WHERE m.is_active = 1 AND m.delete_at IS NULL
               AND m.last_checked_at >= ?
               AND EXISTS (SELECT 1 FROM monitor_logs l
                            WHERE l.monitor_id = m.id
                              AND l.status = 'down'
                              AND l.checked_at >= DATE_SUB(?, INTERVAL 1 DAY))", $since, $since
          )->fetchArray()
        : $db->query(
            "SELECT
                SUM(last_status = 'up')   AS recovered,
                SUM(last_status = 'down') AS still_down
             FROM monitors
             WHERE is_active = 1 AND delete_at IS NULL AND last_checked_at >= ?", $since
          )->fetchArray();

    //Name the site being worked on, so the wait is legible.
    $current = $db->query(
        "SELECT name FROM monitors
          WHERE is_active = 1 AND delete_at IS NULL AND last_checked_at >= ?
          ORDER BY last_checked_at DESC LIMIT 1", $since
    )->fetchArray();

    $params['running']   = $running ? 1 : 0;
    $params['checked']   = (int) ($row['checked'] ?? 0);
    $params['total']     = (int) ($total['n'] ?? 0);
    $params['recovered'] = (int) ($outcome['recovered'] ?? 0);
    $params['stillDown'] = (int) ($outcome['still_down'] ?? 0);
    $params['current']   = $current['name'] ?? '';
    $params['status']    = 'ok';

// ── GET LIST (split-panel UI) ──────────────────────────────────────────────
} elseif ($act === 'getList') {
    $category = !empty($_POST['category']) ? $_POST['category'] : '';
    $status   = !empty($_POST['status'])   ? $_POST['status']   : '';

    include_once __DIR__ . '/../security/QueryBuilder.php';
    $qb = new QueryBuilder();
    //Columns are qualified because of the joins below.
    $qb->eq('m.category', $category)->eq('m.last_status', $status);

    //Disk usage comes from the cache table that check_disk.php fills. Left-joined so
    //the list still works before that job has run, or for a site with no cPanel account.
    $hasDisk = (bool) $db->query("SHOW TABLES LIKE 'disk_usage'")->fetchArray();
    $diskCols = $hasDisk
        ? ", du.percent AS disk_percent, du.used AS disk_used, du.quota AS disk_quota"
        : ", NULL AS disk_percent, NULL AS disk_used, NULL AS disk_quota";
    $diskJoin = $hasDisk
        ? " LEFT JOIN websiteList w ON w.wID = m.source_wID AND w.delete_at IS NULL"
        . " LEFT JOIN disk_usage du ON du.cpanel_user = w.wCPanelUser"
        : "";

    $baseSql = "SELECT m.id, m.name, m.url, m.category, m.check_interval, m.last_status,"
             . " m.last_checked_at, m.last_response_ms, m.ssl_days_left{$diskCols}"
             . " FROM monitors m{$diskJoin}"
             . " WHERE m.delete_at IS NULL AND m.is_active = 1";

    // Problems first: a list sorted by id buries the handful of down sites among hundreds.
    $rows = $qb->execute($db, $baseSql,
        "ORDER BY FIELD(m.last_status,'down','unknown','up'), m.name ASC")->fetchAll();

    //Usage Quota answers a different question — "what is filling up the servers?" —
    //so it lists every hosting account, not only the ones we monitor. Accounts behind
    //Draft/Unpublished sites still consume real disk, and those are exactly the ones
    //the team needs to see to decide whether to clear them out or raise the quota.
    if (($_POST['sort'] ?? '') === 'quota' && $hasDisk) {
        $rows = $db->query(
            "SELECT m.id, w.wProject AS name, w.wDomain AS url,
                    COALESCE(m.category, '-') AS category,
                    m.check_interval, m.last_status, m.last_checked_at,
                    m.last_response_ms, m.ssl_days_left,
                    du.percent AS disk_percent, du.used AS disk_used, du.quota AS disk_quota,
                    du.cpanel_user, w.wLiveStatus
               FROM disk_usage du
               JOIN websiteList w
                 ON w.wCPanelUser = du.cpanel_user AND w.delete_at IS NULL
               LEFT JOIN monitors m
                 ON m.source_wID = w.wID AND m.delete_at IS NULL AND m.is_active = 1
              WHERE du.percent IS NOT NULL
              ORDER BY du.percent DESC, w.wProject ASC"
        )->fetchAll();
    }
    $params['data']   = $rows;
    $params['status'] = 'ok';

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
         WHERE delete_at IS NULL AND wLiveStatus IN (" . monitorStatusSql() . ")
           AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)"
    )->fetchAll();

    $inserted = 0;
    foreach ($websites as $w) {
        $url = trim($w['wDomain']);
        if (empty($url)) continue;
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
        $db->query(
            "INSERT INTO monitors (name, url, category, source_wID, check_interval) VALUES (?, ?, 'client', ?, 5)",
            $w['wProject'], $url, $w['wID']
        );
        $inserted++;
    }

    // Pause monitors whose website is no longer ours, and resume the ones back Live.
    $db->query(
        "UPDATE monitors m
            LEFT JOIN websiteList w ON w.wID = m.source_wID
            SET m.is_active = 0, m.update_at = NOW()
          WHERE m.source_wID IS NOT NULL
            AND m.delete_at IS NULL
            AND m.is_active = 1
            AND (w.wID IS NULL OR w.delete_at IS NOT NULL OR w.wLiveStatus NOT IN (" . monitorStatusSql() . "))"
    );
    $params['paused'] = $db->affectedRows();

    $db->query(
        "UPDATE monitors m
            JOIN websiteList w ON w.wID = m.source_wID
            SET m.is_active = 1, m.last_status = 'unknown', m.update_at = NOW()
          WHERE m.source_wID IS NOT NULL
            AND m.delete_at IS NULL
            AND m.is_active = 0
            AND w.delete_at IS NULL
            AND w.wLiveStatus IN (" . monitorStatusSql() . ")"
    );
    $params['resumed'] = $db->affectedRows();


    // A website can be renamed or moved to a new domain after it was imported.
    // Refresh name/url when the hostname really differs (ignoring www. and trailing /),
    // so alerts never point at a domain the client no longer uses.
    $db->query(
        "UPDATE monitors m
            JOIN websiteList w ON w.wID = m.source_wID
            SET m.name = w.wProject,
                m.url  = CONCAT('https://', TRIM(TRAILING '/' FROM
                           REPLACE(REPLACE(w.wDomain, 'https://', ''), 'http://', ''))),
                m.last_status = 'unknown',
                m.update_at = NOW()
          WHERE m.source_wID IS NOT NULL
            AND m.delete_at IS NULL
            AND w.delete_at IS NULL
            AND w.wDomain <> ''
            AND REPLACE(REPLACE(REPLACE(TRIM(TRAILING '/' FROM m.url), 'https://', ''), 'http://', ''), 'www.', '')
             <> REPLACE(REPLACE(REPLACE(TRIM(TRAILING '/' FROM w.wDomain), 'https://', ''), 'http://', ''), 'www.', '')"
    );
    $params['retargeted'] = $db->affectedRows();

    $params['inserted'] = $inserted;
    $params['status']   = 'ok';
}

echo json_encode($params);
