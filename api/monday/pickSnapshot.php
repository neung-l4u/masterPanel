<?php
/**
 * Pick the Monday.com JSON snapshot that best represents a given period.
 *
 * Monday snapshots are written by selectWeek/selectProject.php and named using
 * a Bangkok-time stamp, e.g.:
 *   ALL_monday_data260307-2359.json
 *   ALL_monday_data_ALL_COUNTRY260307-2359.json
 *
 * For a report covering a date range, the correct snapshot is the newest one
 * whose timestamp is <= the period's endDate. This matches the snapshot the
 * Weekly Summary email used at that time, so the dashboard can reproduce the
 * same Active numbers for historical periods instead of always showing the
 * current Monday state.
 *
 * @param array  $files    List of candidate file paths (same naming pattern).
 * @param string $endDate  "Y-m-d H:i:s" end-of-period in Bangkok time.
 * @return string|null     Chosen file path, or null if $files is empty.
 */
function pickSnapshotForPeriod(array $files, $endDate)
{
    if (empty($files)) {
        return null;
    }

    // Target stamp in the same YYMMDD-HHmm format as filenames
    $targetStamp = (new DateTime($endDate))->format('ymd-Hi');

    $chosen = null;
    foreach ($files as $f) {
        // Matches both ALL and ALL_COUNTRY filenames (stamp sits right before .json)
        if (preg_match('/(\d{6}-\d{4})\.json$/', basename($f), $m)) {
            $stamp = $m[1];
            if (strcmp($stamp, $targetStamp) <= 0) {
                if ($chosen === null || strcmp($stamp, $chosen['stamp']) > 0) {
                    $chosen = ['path' => $f, 'stamp' => $stamp];
                }
            }
        }
    }

    if ($chosen) {
        return $chosen['path'];
    }

    // Fallback: period is older than every available snapshot → use the
    // earliest one we have (best-effort historical approximation).
    sort($files);
    return $files[0];
}
