<?php
/**
 * Assemble the brief from both sources.
 *
 * The one place that decides how to degrade. A gcal outage still produces a
 * brief, from Monday alone, flagged so nobody reads stale minutes as current.
 * A Monday outage produces nothing: Monday is the authority on who is a
 * customer, so without it there is no report to make, and inventing an empty
 * one would read as "we have no customers".
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/fetchGcal.php';
require_once __DIR__ . '/fetchMonday.php';
require_once __DIR__ . '/matchShops.php';
require_once __DIR__ . '/buildBrief.php';

/**
 * Strip the gaps and flags that only say "gcal was down".
 *
 * With gcal unreachable the rules see an empty shop list, so every Monday shop
 * earns an unmatched_monday gap -- about 38 identical rows on the real board,
 * in the one section whose whole value is that a genuine gap stands out. The
 * count staff watch would become noise and hide the real findings, and the same
 * flood reaches the Chat message. The per-row "not in gcal" flag is just as
 * empty when it is true of every shop.
 *
 * buildBrief is right to raise those gaps: judging one shop at a time, each
 * really has no gcal match. It simply cannot know the whole upstream was down.
 * That knowledge lives here, so the suppression does too -- on the path both
 * the dashboard and the message take, so the two cannot disagree.
 *
 * Gaps of every other kind survive, because those are still true.
 */
function arayaSuppressOutageNoise(array $brief): array
{
    if (empty($brief['gcal_unavailable'])) {
        return $brief;
    }

    $brief['gaps'] = array_values(array_filter(
        $brief['gaps'] ?? [],
        fn($g) => ($g['kind'] ?? '') !== 'unmatched_monday'
    ));

    // The outage banner carries the message instead of 38 identical badges.
    foreach ($brief['live'] ?? [] as $i => $row) {
        if (!empty($row['gcal_missing'])) {
            $brief['live'][$i]['gcal_missing'] = false;
        }
    }

    return $brief;
}

/**
 * The failed-call list, or null when it could not be fetched.
 *
 * Null, not an empty array: "gcal says there were no errors" and "we could not
 * ask" must stay distinguishable until the last moment. The brief is still
 * produced either way; only the error section depends on this. The log line
 * carries the exception message alone, and those never contain the admin key,
 * the URL or the response body.
 */
function arayaTryFetchCallErrors(array $config, bool $useCache): ?array
{
    try {
        return arayaFetchCallErrors($config, $useCache);
    } catch (Throwable $e) {
        error_log('[arayaBrief] call-errors fetch failed: ' . $e->getMessage());
        return null;
    }
}

function arayaLoadBrief(bool $useFixture = false, bool $useCache = true): array
{
    $today = (new DateTimeImmutable('now'))->format('Y-m-d');

    if ($useFixture) {
        $raw = json_decode(file_get_contents(__DIR__ . '/../assets/fixtures/sample.json'), true);
        $match = arayaMatchShops($raw['monday'], $raw['gcal']);
        return arayaBuildBrief($match, $today, $raw['call_errors'] ?? [])
            + ['gcal_unavailable' => false, 'call_errors_unavailable' => false];
    }

    $config = arayaLoadConfig();

    // Monday first: if it fails there is nothing to report, so fail loudly.
    $monday = arayaFetchMonday($config, $useCache);

    $gcalFailed = false;
    try {
        $gcal = arayaFetchGcal($config, $useCache);
    } catch (Throwable $e) {
        error_log('[arayaBrief] gcal fetch failed: ' . $e->getMessage());
        $gcal = [];
        $gcalFailed = true;
    }

    // Same host as gcal: if the summary just failed, asking again would only
    // spend another timeout. The gcal banner already tells the reader.
    $callErrors = $gcalFailed ? null : arayaTryFetchCallErrors($config, $useCache);

    $brief = arayaBuildBrief(arayaMatchShops($monday, $gcal), $today, $callErrors);
    $brief['gcal_unavailable'] = $gcalFailed;
    $brief['call_errors_unavailable'] = $callErrors === null;
    return arayaSuppressOutageNoise($brief);
}
