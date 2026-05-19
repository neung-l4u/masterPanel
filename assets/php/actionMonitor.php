<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
include '../../assets/security/Sanitizer.php';

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
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
         WHERE delete_at IS NULL AND wLiveStatus = 'Live'
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
    $params['inserted'] = $inserted;
    $params['status']   = 'ok';
}

echo json_encode($params);
