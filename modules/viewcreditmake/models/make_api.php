<?php
/**
 * Make.com API client. GET only, by design — this module is read-only and must
 * never expose an endpoint that can run, edit or toggle a scenario.
 * curl usage follows modules/quotation/Monday/monday_data.php.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/aggregate.php';

/** Cache dir lives inside the module; gitignored alongside config.php. */
function mc_cacheDir() {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir;
}

/**
 * One GET against the Make API, with exponential backoff on 429/5xx.
 * Returns the decoded body, or throws. The token is never included in a
 * thrown message — those messages travel to the browser.
 */
function mc_get($path, $tries = 4) {
    global $makeZone, $makeToken;
    $base  = 'https://' . $makeZone . '.make.com/api/v2';
    // Only the path (no query) goes into error text: query strings are safe here
    // but keeping it short makes the UI banners readable.
    $safePath = explode('?', $path)[0];
    $delay = 1.0;

    for ($i = 0; $i < $tries; $i++) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $base . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Token ' . $makeToken,
                'Accept: application/json',
            ],
        ]);
        $raw    = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $hlen   = (int)curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $err    = curl_error($curl);
        curl_close($curl);

        if ($raw === false) throw new Exception('เชื่อมต่อ Make ไม่ได้: ' . $err);

        $headers = substr($raw, 0, $hlen);
        $body    = substr($raw, $hlen);

        if ($status >= 200 && $status < 300) {
            $json = json_decode($body, true);
            if (!is_array($json)) throw new Exception('Make ตอบข้อมูลที่อ่านไม่ได้ (' . $safePath . ')');
            return $json;
        }

        // 5xx is usually a dead scenario, not congestion — don't burn the backoff budget on it.
        if ($status >= 500 && $i >= 1) throw new Exception('Make API ' . $status . ' ที่ ' . $safePath);

        if ($status === 429 || $status >= 500) {
            // Retry-After is seconds per RFC; Make sends it on 429.
            $wait = $delay;
            if (preg_match('/retry-after:\s*(\d+)/i', $headers, $m) && (int)$m[1] > 0) {
                $wait = (int)$m[1];
            }
            usleep((int)($wait * 1000000));
            $delay *= 2;
            continue;
        }

        // Make answers 401 (not 404) for a path that does not exist, so the status
        // code alone cannot tell "bad token" from "wrong path".
        if ($status === 401) throw new Exception('Make API 401 ที่ ' . $safePath . ' (token ไม่ถูกต้อง หรือ path ไม่มีอยู่)');
        throw new Exception('Make API ' . $status . ' ที่ ' . $safePath);
    }
    throw new Exception('Make API ไม่ตอบหลังลอง ' . $tries . ' ครั้ง ที่ ' . $safePath);
}

/** All scenarios in the org, following pagination. */
function mc_fetchScenarios() {
    global $makeOrgId;
    $all = [];
    for ($offset = 0; ; $offset += 100) {
        $page = mc_get('/scenarios?organizationId=' . urlencode($makeOrgId)
            . '&pg[limit]=100&pg[offset]=' . $offset);
        $list = $page['scenarios'] ?? [];
        $all = array_merge($all, $list);
        if (count($list) < 100) break;
    }
    return $all;
}

/**
 * Build the snapshot incrementally, a slice per request.
 *
 * The org has 234 scenarios and /scenarios/{id}/usage costs ~0.9s each with
 * rate-limit pacing — about 208s for a full pass, far over Apache's 30s
 * max_execution_time. So each call fetches for at most $budgetSec, appends to
 * a partial file on disk, and returns progress; the client polls until
 * complete === true. Finished snapshots are served for 10 minutes.
 *
 * ponytail: on-demand incremental build, no cron. If the first load being slow
 * ever matters, warm it from a cron job hitting this with a long CLI budget.
 */
function mc_snapshot($refresh = false, $budgetSec = 20) {
    $done    = mc_cacheDir() . '/snapshot.json';
    $partial = mc_cacheDir() . '/snapshot_partial.json';

    if (!$refresh && is_file($done) && (time() - filemtime($done)) < 600) {
        $hit = json_decode(file_get_contents($done), true);
        if (is_array($hit)) { $hit['complete'] = true; return $hit; }
    }
    if ($refresh) { @unlink($partial); @unlink($done); }

    // Resume an in-progress build, or start one.
    $state = is_file($partial) ? json_decode(file_get_contents($partial), true) : null;
    if (!is_array($state) || empty($state['pending'])) {
        $scenarios = mc_fetchScenarios();
        $state = [
            'startedAt' => gmdate('c'),
            'pending'   => array_map(fn($s) => [
                'id'         => $s['id'],
                'name'       => $s['name'] ?? '',
                'isActive'   => !empty($s['isActive']),
                'isPaused'   => !empty($s['isPaused']),
                'scheduling' => $s['scheduling'] ?? null,
            ], $scenarios),
            'total'     => count($scenarios),
            'scenarios' => [],
            'failed'    => [],
            'orgDaily'  => null,
            'timezone'  => null,
        ];
    }

    $deadline = microtime(true) + $budgetSec;

    // Org totals first: they carry the timezone offset the whole UI depends on.
    if ($state['orgDaily'] === null) {
        global $makeOrgId;
        try {
            $org = mc_get('/organizations/' . urlencode($makeOrgId) . '/usage');
            $state['orgDaily'] = mc_normalizeUsage($org);
            // The org usage endpoint carries no timezone name, but its dates are
            // stamped with the org's UTC offset — that offset is the day boundary
            // Make bills on, so surface it rather than guessing from timezoneId.
            $state['timezone'] = mc_offsetOf($org['data'][0]['date'] ?? null) ?? 'ไม่ทราบ';
        } catch (Exception $e) {
            // Non-fatal: per-scenario data still works, just no unaccounted figure.
            $state['orgDaily'] = [];
            $state['timezone'] = 'ไม่ทราบ';
        }
    }

    while (!empty($state['pending']) && microtime(true) < $deadline) {
        $meta = array_shift($state['pending']);
        try {
            $state['scenarios'][] = [
                'meta'  => $meta,
                'usage' => mc_normalizeUsage(mc_get('/scenarios/' . $meta['id'] . '/usage')),
            ];
        } catch (Exception $e) {
            // Isolated per scenario: one dead scenario must not sink the request.
            $state['failed'][] = [
                'id'    => $meta['id'],
                'name'  => $meta['name'],
                'error' => $e->getMessage(),
            ];
        }
        usleep(150000); // stay under the per-minute rate limit
    }

    $complete = empty($state['pending']);
    $snap = [
        'fetchedAt' => gmdate('c'),
        'timezone'  => $state['timezone'] ?? 'ไม่ทราบ',
        'scenarios' => $state['scenarios'],
        'orgDaily'  => $state['orgDaily'] ?? [],
        'failed'    => $state['failed'],
        'complete'  => $complete,
        'loaded'    => count($state['scenarios']) + count($state['failed']),
        'total'     => $state['total'],
    ];

    if ($complete) {
        file_put_contents($done, json_encode($snap));
        @unlink($partial);
    } else {
        file_put_contents($partial, json_encode($state));
    }
    return $snap;
}
