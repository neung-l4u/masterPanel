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

function arayaLoadBrief(bool $useFixture = false, bool $useCache = true): array
{
    $today = (new DateTimeImmutable('now'))->format('Y-m-d');

    if ($useFixture) {
        $raw = json_decode(file_get_contents(__DIR__ . '/../assets/fixtures/sample.json'), true);
        $match = arayaMatchShops($raw['monday'], $raw['gcal']);
        return arayaBuildBrief($match, $today) + ['gcal_unavailable' => false];
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

    $brief = arayaBuildBrief(arayaMatchShops($monday, $gcal), $today);
    $brief['gcal_unavailable'] = $gcalFailed;
    return $brief;
}
