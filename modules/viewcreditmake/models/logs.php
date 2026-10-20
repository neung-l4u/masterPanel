<?php
/**
 * Event log ("Credit Usage") fetch — the rows behind Make's own Credit Usage page.
 *
 * Make offers no org-level log endpoint, so this walks every scenario:
 *   - from/to are epoch MILLISECONDS
 *   - pg[limit] caps at 50 (asking for more returns HTTP 400)
 *   - only eventType === 'EXECUTION_END' carries billed totals; counting the
 *     other event types double-counts every run
 */
require_once __DIR__ . '/make_api.php';

const MC_PAGE_CAP = 20; // 20 x 50 = 1000 events per scenario per range

/**
 * Like mc_snapshot, this is built incrementally across requests: 234 scenarios
 * x at least one log call each cannot finish inside Apache's 30s limit.
 */
function mc_logs($snap, $from, $to, $refresh = false, $budgetSec = 20) {
    $key     = preg_replace('/[^0-9-]/', '', $from . '_' . $to);
    $done    = mc_cacheDir() . '/logs_' . $key . '.json';
    $partial = mc_cacheDir() . '/logs_' . $key . '_partial.json';

    if (!$refresh && is_file($done) && (time() - filemtime($done)) < 600) {
        $hit = json_decode(file_get_contents($done), true);
        if (is_array($hit)) { $hit['complete'] = true; return $hit; }
    }
    if ($refresh) { @unlink($done); @unlink($partial); }

    $off = mc_offsetMinutes($snap['timezone']) ?? 0;
    // Trap 4: shift the calendar range by the org offset before going to epoch ms.
    list($fromMs, $toMs) = mc_logWindowMs($from, $to, $off);

    $state = is_file($partial) ? json_decode(file_get_contents($partial), true) : null;
    if (!is_array($state) || !isset($state['pending'])) {
        $state = [
            'pending'   => array_map(fn($s) => ['id' => $s['meta']['id'], 'name' => $s['meta']['name']],
                                     $snap['scenarios']),
            'total'     => count($snap['scenarios']),
            'rows'      => [],
            'failed'    => [],
            'truncated' => [],
        ];
    }

    $deadline = microtime(true) + $budgetSec;

    while (!empty($state['pending']) && microtime(true) < $deadline) {
        $sc = array_shift($state['pending']);
        try {
            for ($page = 0; $page < MC_PAGE_CAP; $page++) {
                $q = http_build_query([
                    'from'        => (string)$fromMs,
                    'to'          => (string)$toMs,
                    'pg[limit]'   => '50',
                    'pg[offset]'  => (string)($page * 50),
                    'pg[sortDir]' => 'desc',
                ]);
                $res   = mc_get('/scenarios/' . $sc['id'] . '/logs?' . $q);
                $batch = $res['scenarioLogs'] ?? [];

                foreach ($batch as $r) {
                    // Only EXECUTION_END carries the billed totals.
                    if (!empty($r['eventType']) && $r['eventType'] !== 'EXECUTION_END') continue;
                    $state['rows'][] = [
                        'scenarioId'   => (int)($r['scenarioId'] ?? $sc['id']),
                        'scenarioName' => (string)($r['scenarioName'] ?? $sc['name']),
                        'timestamp'    => (string)($r['timestamp'] ?? ''),
                        'credits'      => (float)($r['centicredits'] ?? 0) / 100,
                        'operations'   => (float)($r['operations'] ?? 0),
                        'transfer'     => (float)($r['transfer'] ?? 0),
                        'duration'     => (float)($r['duration'] ?? 0),
                        'status'       => (int)($r['status'] ?? 0),
                        'type'         => (string)($r['type'] ?? ''),
                    ];
                }
                if (count($batch) < 50) break;
                // Hit the cap: report it rather than silently serving a short list.
                if ($page === MC_PAGE_CAP - 1) {
                    $state['truncated'][] = ['id' => $sc['id'], 'name' => $sc['name']];
                }
                usleep(150000);
            }
        } catch (Exception $e) {
            $state['failed'][] = ['id' => $sc['id'], 'name' => $sc['name'], 'error' => $e->getMessage()];
        }
        usleep(150000);
    }

    $complete = empty($state['pending']);
    $rows = $state['rows'];
    if ($complete) {
        usort($rows, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp'])); // newest first
    }

    $out = [
        'from'                  => $from,
        'to'                    => $to,
        'timezone'              => $snap['timezone'],
        'timezoneOffsetMinutes' => $off,
        'fetchedAt'             => gmdate('c'),
        'complete'              => $complete,
        'loaded'                => $state['total'] - count($state['pending']),
        'total'                 => $state['total'],
        'totals'                => [
            'events'     => count($rows),
            'credits'    => array_sum(array_column($rows, 'credits')),
            'operations' => array_sum(array_column($rows, 'operations')),
        ],
        'rows'      => $rows,
        'truncated' => $state['truncated'],
        'failed'    => $state['failed'],
    ];

    if ($complete) {
        $out['rows'] = $rows;
        file_put_contents($done, json_encode($out));
        @unlink($partial);
    } else {
        $state['rows'] = $rows;
        file_put_contents($partial, json_encode($state));
    }
    return $out;
}
