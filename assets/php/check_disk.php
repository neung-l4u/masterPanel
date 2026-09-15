<?php
/**
 * Daily disk-space sweep.
 *
 * Asks each WHM server for its accounts' disk usage and posts one Google Chat
 * message per account that has crossed DISK_WARN_PERCENT. Set high on purpose:
 * warning from 85% produced dozens of alerts for accounts that were not in any
 * danger, and the noise buried the few that were. Run from cron once a day:
 *
 *   30 8 * * * /usr/local/bin/php /path/to/assets/php/check_disk.php >/dev/null 2>&1
 *
 * Does nothing at all when whm_tokens.json is absent, so it is safe to deploy
 * before the tokens are in place.
 */
date_default_timezone_set('Asia/Bangkok');
error_reporting(E_ERROR | E_PARSE);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script runs from cron only.');
}

define('DISK_WARN_PERCENT', 99);

require_once __DIR__ . '/whmDiskCheck.php';
require_once __DIR__ . '/../../assets/db/db.php';
require_once __DIR__ . '/../../assets/db/initDB.php';

define('MONITOR_FUNCTIONS_ONLY', true);
require_once __DIR__ . '/check_monitor.php';   // renderTemplate(), sendNotifications()

$servers = whmServers();
if (!$servers) {
    fwrite(STDERR, "No whm_tokens.json configured — nothing to check.\n");
    exit(0);
}

foreach (array_keys($servers) as $svID) {
    $usage = whmAccountUsage((string) $svID);
    if (!$usage) continue;

    //Cache what WHM reported so the monitor page can sort and filter on it
    //without calling the API itself.
    if ($db->query("SHOW TABLES LIKE 'disk_usage'")->fetchArray()) {
        foreach ($usage as $acct => $d) {
            $db->query(
                "INSERT INTO disk_usage (cpanel_user, svID, used, quota, percent, checked_at)
                      VALUES (?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE svID=VALUES(svID), used=VALUES(used),
                      quota=VALUES(quota), percent=VALUES(percent), checked_at=NOW()",
                $acct, (int) $svID, $d['used'], $d['limit'], $d['percent']
            );
        }
    }

    // Match cPanel accounts back to the websites we watch.
    $sites = $db->query(
        "SELECT wCPanelUser, wDomain FROM websiteList
          WHERE delete_at IS NULL AND svID = ? AND wCPanelUser <> '' AND wCPanelUser IS NOT NULL",
        (int) $svID
    )->fetchAll();

    foreach ($sites as $site) {
        $acct = $usage[$site['wCPanelUser']] ?? null;
        if (!$acct || $acct['percent'] === null) continue;
        if ($acct['percent'] < DISK_WARN_PERCENT) continue;

        $domain = preg_replace('#^www\.#i', '',
            (string) parse_url(
                preg_match('#^https?://#i', $site['wDomain']) ? $site['wDomain'] : 'https://' . $site['wDomain'],
                PHP_URL_HOST
            ));

        $monitor = ['name' => $domain, 'url' => $site['wDomain'],
                    'notify_email' => '', 'notify_line' => '', 'notify_webhook' => ''];

        $msg = renderTemplate($db, 'disk_warn', $monitor, [
            'diskUsed'    => $acct['used'],
            'diskLimit'   => $acct['limit'],
            'diskPercent' => $acct['percent'],
            'cpanelUser'  => $site['wCPanelUser'],
        ]);
        if ($msg) sendNotifications($monitor, $msg['subject'], $msg['body'], $msg['mention']);
    }
}
