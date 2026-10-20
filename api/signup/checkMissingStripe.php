<?php
/**
 * Sweep for signups that never recorded a Stripe result.
 *
 * The signup form writes stripeResult in a second request, so a customer who
 * closes the tab mid-flow leaves it NULL and nothing server-side ever notices.
 * This catches those and alerts IT. Safe to run repeatedly - notifyTeam
 * de-duplicates on (type, refID).
 *
 * Run from cron, e.g. hourly:
 *   php /var/www/html/api/signup/checkMissingStripe.php
 */
require_once __DIR__ . '/../../assets/db/db.php';
require_once __DIR__ . '/../../assets/db/initDB.php';
require_once __DIR__ . '/../../assets/php/notify.php';

global $db;

$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    session_start();
    if (empty($_SESSION['id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header('Content-Type: application/json');
}

// Give the signup flow time to finish before calling it abandoned, and don't
// re-litigate old history - only look at the recent window.
$graceMinutes = 30;
$lookbackDays = 7;

$rows = $db->query(
    'SELECT id, dataLogs, countryCode, createAt
     FROM logssignup
     WHERE deleteStatus = 0
       AND test = 0
       AND (stripeResult IS NULL OR stripeResult = "")
       AND createAt < DATE_SUB(NOW(), INTERVAL ? MINUTE)
       AND createAt > DATE_SUB(NOW(), INTERVAL ? DAY)
     ORDER BY createAt DESC',
    $graceMinutes, $lookbackDays
)->fetchAll();

$alerted = 0;
foreach ($rows as $row) {
    $logJson = json_decode($row['dataLogs'], true);
    $shop = $logJson['ShopName'] ?? '(unknown shop)';

    $ok = notifyTeam(
        5,                                  // IT
        'signup_no_stripe',
        'Signup without Stripe result',
        $shop . ' (' . $row['countryCode'] . ') - no Stripe result since ' . $row['createAt'],
        'main.php?p=viewLogs',
        (int)$row['id']
    );
    if ($ok) $alerted++;
}

$summary = ['success' => true, 'scanned' => count($rows), 'alerted' => $alerted];
echo $isCli ? json_encode($summary) . PHP_EOL : json_encode($summary);
