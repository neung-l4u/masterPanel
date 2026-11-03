<?php
/**
 * Resolves a pre sign-up reference and sends the customer to the signup form
 * with what the sales rep already collected filled in.
 *
 * Reached as resolve.php?ref=prelead-0000001, which is what
 * report.localforyou.com/prelead-0000001 rewrites to.
 *
 * The link is deliberately reusable: a customer who closes the tab half way
 * through signup must be able to open the same link again. Each open is
 * counted so an unused link is visible on the entries page.
 */

global $db;
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$configPath = __DIR__ . '/../assets/config.php';
$config     = is_file($configPath) ? require $configPath : [];
$signupUrl  = (string) ($config['signup_form_url'] ?? '');

$ref = isset($_GET['ref']) ? trim((string) $_GET['ref']) : '';

/** Show the customer a dead end without exposing why. */
function preSignupNotFound(string $ref): void
{
    http_response_code(404);
    $safeRef = htmlspecialchars($ref, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Link not found</title>'
       . '<link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">'
       . '</head><body class="bg-light">'
       . '<div class="container" style="max-width: 540px; padding-top: 12vh;">'
       . '<div class="card shadow-sm"><div class="card-body text-center p-5">'
       . '<h1 class="h4 mb-3">We could not find that link</h1>'
       . '<p class="text-muted mb-0">The reference <code>' . $safeRef . '</code> is not one we recognise. '
       . 'Please check the link you were sent, or contact the person who sent it.</p>'
       . '</div></div></div></body></html>';
}

// prelead-0000001, or the bare number. Anything else is not ours.
if (!preg_match('/^(?:prelead-)?0*(\d+)$/i', $ref, $matches)) {
    preSignupNotFound($ref);
    exit;
}

$id  = (int) $matches[1];
$row = $db->query('SELECT * FROM `presignup` WHERE `id` = ?', [$id])->fetchArray();

if (!$row) {
    preSignupNotFound($ref);
    exit;
}

if (($row['status'] ?? '') === 'cancelled') {
    preSignupNotFound($ref);
    exit;
}

// Record the open. First open sets openedAt; later ones only bump the count,
// so the entries page can tell "never opened" from "opened repeatedly".
$db->query(
    'UPDATE `presignup`
        SET `openCount` = `openCount` + 1,
            `openedAt`  = COALESCE(`openedAt`, NOW()),
            `status`    = CASE WHEN `status` = \'open\' THEN \'visited\' ELSE `status` END
      WHERE `id` = ?',
    [$id]
);

if ($signupUrl === '') {
    http_response_code(500);
    echo 'The signup form location is not configured.';
    exit;
}

// Map our columns onto the signup form's own field names. Only fields the
// signup form actually reads are sent; anything else would be ignored and is
// better read off the entries page.
// Only the reference travels in the link. The signup form reads the collected
// values from models/lookup.php, which keeps this URL short, keeps the
// customer's own details out of their address bar and browser history, and
// means a field added to the form later changes nothing here.
//
// A lead opened automatically from a chat platform uses this same shape, so
// there is one link format whichever way the lead arrived.
$padded    = str_pad((string) $id, 7, '0', STR_PAD_LEFT);
$separator = strpos($signupUrl, '?') === false ? '?' : '&';

header('Location: ' . $signupUrl . $separator . 'prelead=' . $padded, true, 302);
exit;
