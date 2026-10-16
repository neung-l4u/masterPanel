<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result = ['result' => '', 'msg' => '', 'credit' => [
    'attempted'   => false,
    'granted'     => false,
    'reason'      => null,
    'minutes'     => null,
    'new_balance' => null,
    'claimed_at'  => null,
]];

$payload = !empty($_POST['payload']) ? $_POST['payload'] : 'no data';
$data    = json_decode($payload, true) ?: [];

// The feedback row is written first and unconditionally. The credit is a
// reward for submitting feedback, never a gate on recording it, so nothing
// below may cost us the submission.
$db->query('INSERT INTO `feedback`(`dataLogs`) VALUES (?)', [$payload]);
$feedbackId = $db->lastInsertID();

$result['result'] = 'success';
$result['msg']    = 'Save to DB successfully!';

/**
 * Ask the gcal API to grant the 30-minute goodwill credit.
 *
 * Called from here, server-side, rather than from the browser: the shared
 * token would otherwise be readable by anyone who opened the feedback page,
 * and anyone holding it could credit any shop.
 *
 * Returns the credit block for the response. Every failure is a refusal with a
 * reason, never an exception: the customer has already been thanked for
 * feedback that is already saved.
 */
function upDownRequestFeedbackCredit(string $shopId, string $email, string $feedbackRef): array
{
    $out = [
        'attempted'   => true,
        'granted'     => false,
        'reason'      => null,
        'minutes'     => null,
        'new_balance' => null,
        'claimed_at'  => null,
    ];

    $configPath = __DIR__ . '/../assets/config.php';
    if (!is_file($configPath)) {
        $out['reason'] = 'not_configured';
        return $out;
    }

    $config  = require $configPath;
    $baseUrl = rtrim((string) ($config['gcal_base_url'] ?? ''), '/');
    $token   = (string) ($config['feedback_credit_token'] ?? '');

    // A half-configured deployment must not look like a working one, and must
    // not look like a refused credit either: 'not_configured' is a deployment
    // problem, distinct from the shop having already claimed.
    if ($baseUrl === '' || $token === '') {
        $out['reason'] = 'not_configured';
        return $out;
    }

    $body = json_encode([
        'shop_id'      => $shopId,
        'email'        => $email,
        'feedback_ref' => $feedbackRef,
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($baseUrl . '/api/feedback-credit');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Feedback-Token: ' . $token,
        ],
        // Short, and deliberately so: the customer is waiting on this request
        // before they see their thank-you page. A credit we failed to grant can
        // be granted by hand from the Minutes page; a page that hangs cannot be
        // recovered.
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status === 0) {
        error_log('feedback credit transport failed for shop ' . $shopId . ': ' . $curlErr);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    if ($status === 401) {
        // A token problem is ours, not the customer's. They are told only that
        // their feedback arrived.
        error_log('feedback credit rejected the token for shop ' . $shopId);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded)) {
        error_log('feedback credit returned an unparseable body for shop ' . $shopId . ': HTTP ' . $status);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    $out['granted'] = !empty($decoded['granted']);
    if ($out['granted']) {
        $out['minutes']     = isset($decoded['minutes']) ? (int) $decoded['minutes'] : null;
        $out['new_balance'] = isset($decoded['new_balance']) ? (int) $decoded['new_balance'] : null;
    } else {
        $out['reason']      = isset($decoded['reason']) ? (string) $decoded['reason'] : 'credit_service_unavailable';
        $out['claimed_at']  = isset($decoded['claimed_at']) ? (string) $decoded['claimed_at'] : null;
        $out['new_balance'] = isset($decoded['new_balance']) ? (int) $decoded['new_balance'] : null;
    }

    return $out;
}

// Only a submission carrying a real shop_id asks for a credit. The form posts
// mode=lead whenever it had no shop_id to work with, so a truncated URL never
// reaches this branch -- but shop_id is checked directly rather than trusting
// the mode, because it is what the credit actually keys on.
$mode   = (string) ($data['mode'] ?? 'lead');
$shopId = trim((string) ($data['shop_id'] ?? ''));

if ($mode === 'araya' && $shopId !== '') {
    try {
        $result['credit'] = upDownRequestFeedbackCredit(
            $shopId,
            trim((string) ($data['email'] ?? '')),
            $feedbackId ? ('feedback-' . $feedbackId) : ''
        );
    } catch (Throwable $e) {
        // The feedback row is already committed. A credit we could not request
        // is recoverable by hand; losing the feedback is not.
        error_log('feedback credit call threw for shop ' . $shopId . ': ' . $e->getMessage());
        $result['credit']['attempted'] = true;
        $result['credit']['reason']    = 'credit_service_unavailable';
    }

    // Record what happened alongside the submission, so an admin chasing an
    // ungranted credit can see the outcome without reading web server logs.
    if ($feedbackId) {
        $db->query(
            'UPDATE `feedback` SET `dataLogs` = ? WHERE `id` = ?',
            [
                json_encode(
                    array_merge($data, ['creditOutcome' => $result['credit']]),
                    JSON_UNESCAPED_UNICODE
                ),
                $feedbackId,
            ]
        );
    }
}

echo json_encode($result);
