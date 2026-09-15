<?php
/**
 * JSON endpoints for the credit dashboard. GET only, read-only.
 * ?act=usage | logs | forecast
 *
 * Every response is built server-side: MAKE_TOKEN lives in config.php and is
 * never echoed, never sent to the client, never placed in an error message.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Internal data (every scenario name + org credit spend) — reuse the panel's
// central session rather than inventing a second login.
if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ต้องเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Release the session lock as soon as auth is verified. PHP serialises requests
 * that hold the same session open, so a multi-minute log fetch would otherwise
 * block every other tab/poll for this user. Nothing below writes to $_SESSION.
 */
session_write_close();

require_once __DIR__ . '/make_api.php';
require_once __DIR__ . '/logs.php';

/**
 * initDB.php sets date_default_timezone_set("Asia/Bangkok") panel-wide. Every
 * date here is a calendar day in the ORG timezone (UTC+10) instead, so all date
 * math uses gmdate/UTC helpers and never the ambient default.
 */
function mc_today($offsetMinutes) {
    return gmdate('Y-m-d', time() + (int)$offsetMinutes * 60);
}

function mc_fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function mc_isDate($s) {
    return is_string($s) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)
        && (bool)strtotime($s . ' 00:00:00 UTC');
}

/**
 * A missing date falls back to a default, but a date that was SUPPLIED and is
 * malformed is an error rather than a silent fallback to a different range —
 * silently charting the wrong week is worse than refusing.
 */
function mc_dateParam($name, $default) {
    if (!isset($_GET[$name]) || $_GET[$name] === '') return $default;
    if (!mc_isDate($_GET[$name])) mc_fail('วันที่ ' . $name . ' ต้องอยู่ในรูปแบบ YYYY-MM-DD');
    return $_GET[$name];
}

$act     = $_GET['act'] ?? 'usage';
$refresh = ($_GET['refresh'] ?? '') === '1';

/** Per-scenario aggregates for a range, biggest spender first. */
function mc_aggregates($snap, $from, $to) {
    $out = [];
    foreach ($snap['scenarios'] as $s) {
        $out[] = mc_aggregateScenario($s['meta'], $s['usage'], $from, $to);
    }
    usort($out, fn($a, $b) => $b['totalCredits'] <=> $a['totalCredits']);
    return $out;
}

try {
    if ($act === 'usage') {
        $snap = mc_snapshot($refresh);
        $off  = mc_offsetMinutes($snap['timezone']);
        $to   = mc_dateParam('to', mc_today($off));
        $from = mc_dateParam('from', mc_shiftDate($to, -6));
        if ($from > $to) mc_fail('วันที่เริ่มต้องไม่เกินวันที่สิ้นสุด');

        $scenarios = mc_aggregates($snap, $from, $to);
        $scenarioTotal = array_sum(array_column($scenarios, 'totalCredits'));

        $orgTotal = 0;
        foreach ($snap['orgDaily'] as $d) {
            if ($d['date'] >= $from && $d['date'] <= $to) $orgTotal += $d['credits'];
        }

        echo json_encode([
            'from'                  => $from,
            'to'                    => $to,
            'fetchedAt'             => $snap['fetchedAt'],
            'timezone'              => $snap['timezone'],
            'timezoneOffsetMinutes' => $off,
            'complete'              => $snap['complete'],
            'loaded'                => $snap['loaded'],
            'total'                 => $snap['total'],
            'scenarios'             => $scenarios,
            'totals'                => [
                'credits'    => $scenarioTotal,
                'operations' => array_sum(array_column($scenarios, 'totalOperations')),
                'orgTotal'   => $orgTotal,
                // Normally non-zero: deleted scenarios, or ones outside the teams
                // this token can see, still bill to the org.
                'unaccountedCredits' => $orgTotal - $scenarioTotal,
            ],
            'failed' => $snap['failed'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($act === 'forecast') {
        $remaining = $_GET['remainingCredits'] ?? null;
        $renewal   = $_GET['renewalDate'] ?? null;
        $buffer    = ($_GET['buffer'] ?? '') === '' ? 1.2 : $_GET['buffer'];

        if (!is_numeric($remaining) || (float)$remaining < 0)
            mc_fail('เครดิตคงเหลือต้องเป็นตัวเลขที่ไม่ติดลบ');
        if (!mc_isDate($renewal)) mc_fail('วันต่ออายุต้องอยู่ในรูปแบบ YYYY-MM-DD');
        if (!is_numeric($buffer) || (float)$buffer <= 0) mc_fail('buffer ต้องมากกว่า 0');

        $snap  = mc_snapshot($refresh);
        $off   = mc_offsetMinutes($snap['timezone']);
        $today = mc_today($off);

        $usages = array_map(fn($s) => $s['usage'], $snap['scenarios']);
        $result = mc_forecast($usages, $today, $renewal, (float)$remaining, (float)$buffer);

        $per = [];
        foreach (mc_aggregates($snap, mc_shiftDate($today, -6), $today) as $s) {
            $per[] = ['id' => $s['id'], 'name' => $s['name'], 'avgDaily7' => $s['totalCredits'] / 7];
        }

        echo json_encode(array_merge($result, [
            'buffer'                => (float)$buffer,
            'today'                 => $today,
            'renewalDate'           => $renewal,
            'fetchedAt'             => $snap['fetchedAt'],
            'timezone'              => $snap['timezone'],
            'timezoneOffsetMinutes' => $off,
            'complete'              => $snap['complete'],
            'loaded'                => $snap['loaded'],
            'total'                 => $snap['total'],
            'perScenario'           => $per,
            'failed'                => $snap['failed'],
        ]), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($act === 'logs') {
        $snap = mc_snapshot($refresh);
        if (!$snap['complete']) {
            // Logs iterate every scenario, so the scenario list must be complete first.
            echo json_encode([
                'complete' => false, 'loaded' => $snap['loaded'], 'total' => $snap['total'],
                'rows' => [], 'failed' => [], 'truncated' => [],
                'timezone' => $snap['timezone'],
                'timezoneOffsetMinutes' => mc_offsetMinutes($snap['timezone']),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $off  = mc_offsetMinutes($snap['timezone']);
        $to   = mc_dateParam('to', mc_today($off));
        $from = mc_dateParam('from', $to);
        if ($from > $to) mc_fail('วันที่เริ่มต้องไม่เกินวันที่สิ้นสุด');

        echo json_encode(mc_logs($snap, $from, $to, $refresh), JSON_UNESCAPED_UNICODE);
        exit;
    }

    mc_fail('ไม่รู้จักคำสั่งนี้', 404);
} catch (Exception $e) {
    // Message text comes from mc_get, which never embeds the token.
    mc_fail($e->getMessage(), 502);
}
