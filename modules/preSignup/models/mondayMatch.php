<?php
/**
 * Finds the Lead items that might belong to a pre sign-up, and stores
 * Monday's Opportunity ID against it once one item is settled on.
 *
 * A lead reaches the board two ways: automatically from a chat platform, and
 * manually through this module. So by the time a rep submits a pre sign-up,
 * an item for that shop may already exist and must not be duplicated.
 *
 * This endpoint deliberately does **not** decide. Shops with several branches
 * share one phone number, so a phone match can point at two real and
 * different items; picking one would quietly file the lead against the wrong
 * branch, which is worse than leaving it unlinked. It returns what it found,
 * with the address of each candidate so a person can tell the branches apart.
 *
 *   GET  ?ref=0000001            -> candidates for that pre sign-up
 *   POST ref=0000001&itemId=123  -> confirm that item, store its Opportunity ID
 */

global $db;
header('Content-Type: application/json; charset=utf-8');

include '../assets/db/db.php';
include '../assets/db/initDB.php';

$configPath = __DIR__ . '/../assets/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'not_configured']);
    exit;
}

$config     = require $configPath;
$apiUrl     = (string) ($config['monday_api_url'] ?? 'https://api.monday.com/v2');
$token      = (string) ($config['monday_token'] ?? '');
$boardId    = (string) ($config['monday_board_id'] ?? '');
$itemCols   = (array)  ($config['monday_item_columns'] ?? []);
$oppColumn  = (string) ($itemCols['opportunityId'] ?? '');

/** Run a GraphQL query against Monday. Returns null when the call failed. */
function preSignupMondayQuery(string $apiUrl, string $token, string $query): ?array
{
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $query]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
        ],
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status !== 200) {
        error_log('preSignup: Monday query failed (HTTP ' . $status . ') ' . $error);
        return null;
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded) || isset($decoded['errors'])) {
        error_log('preSignup: Monday returned errors: ' . substr((string) $response, 0, 500));
        return null;
    }

    return $decoded['data'] ?? null;
}

/** Digits only, so +61 412 345 678 and 0412345678 compare equal. */
function preSignupDigits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

/**
 * Compare two phone numbers written in different conventions.
 *
 * A local 0412345678 and an international +61412345678 are the same number
 * with a different prefix, so the shorter is compared against the tail of the
 * longer. Seven digits is the shortest run worth trusting; below that, too
 * many unrelated numbers collide.
 */
function preSignupPhonesMatch(string $a, string $b): bool
{
    $a = preSignupDigits($a);
    $b = preSignupDigits($b);

    if ($a === '' || $b === '') {
        return false;
    }
    if ($a === $b) {
        return true;
    }

    $short = strlen($a) <= strlen($b) ? $a : $b;
    $long  = $short === $a ? $b : $a;

    if (strlen($short) < 7) {
        return false;
    }

    return substr($long, -strlen($short)) === substr($short, -strlen($short))
        || substr($long, -(strlen($short) - 1)) === substr($short, -(strlen($short) - 1));
}

$ref = isset($_REQUEST['ref']) ? trim((string) $_REQUEST['ref']) : '';

if (!preg_match('/^(?:prelead-)?0*(\d+)$/i', $ref, $matches)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_ref']);
    exit;
}

$id  = (int) $matches[1];
$row = $db->query(
    'SELECT `id`, `shopName`, `email`, `mobNumber`, `shopNumber`, `conversationLink`,
            `streetAddress`, `citySuburb`, `state`, `postalCode`, `opportunityId`
       FROM `presignup`
      WHERE `id` = ?',
    [$id]
)->fetchArray();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'not_found']);
    exit;
}

if ($token === '' || $boardId === '' || $oppColumn === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'not_configured']);
    exit;
}

// --- Confirming a chosen item -------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $itemId = isset($_POST['itemId']) ? preg_replace('/\D+/', '', (string) $_POST['itemId']) : '';

    if ($itemId === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'invalid_item_id']);
        exit;
    }

    $data = preSignupMondayQuery($apiUrl, $token, 'query { items (ids: ' . $itemId . ') { id name board { id } column_values (ids: ["' . $oppColumn . '"]) { id text } } }');

    if ($data === null) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'monday_unavailable']);
        exit;
    }

    $item = $data['items'][0] ?? null;

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'item_not_found']);
        exit;
    }

    // An item on another board is not a lead for this pre sign-up, whatever
    // id was posted.
    if ((string) ($item['board']['id'] ?? '') !== $boardId) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'item_on_other_board']);
        exit;
    }

    $opportunityId = trim((string) ($item['column_values'][0]['text'] ?? ''));

    $db->query(
        'UPDATE `presignup` SET `opportunityId` = ?, `mondayItemId` = ? WHERE `id` = ?',
        [$opportunityId, $itemId, $id]
    );

    echo json_encode([
        'success' => true,
        'ref'     => 'prelead-' . str_pad((string) $id, 7, '0', STR_PAD_LEFT),
        'itemId'  => $itemId,
        // Empty when the Monday automation has not yet copied the auto_number
        // into its text mirror. The link is recorded either way.
        'opportunityId' => $opportunityId,
        'opportunityIdPending' => $opportunityId === '',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Looking for candidates ---------------------------------------------
// Respond.io Link is checked first and on its own: it identifies one
// conversation, so a hit is as close to certain as this gets. Phone, email and
// shop name are weaker and are reported as what they are.
$candidates = [];

$readCols = array_values(array_unique(array_merge(
    [$oppColumn],
    array_values($itemCols),
    ['name']
)));
$colIds = '"' . implode('", "', array_filter($readCols, static function (string $c): bool {
    return $c !== 'name';
})) . '"';

$respondLink = trim((string) $row['conversationLink']);

if ($respondLink !== '') {
    $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $respondLink);
    $data = preSignupMondayQuery($apiUrl, $token, 'query { items_page_by_column_values (board_id: ' . $boardId . ', limit: 20, columns: [{column_id: "' . ($itemCols['conversationLink'] ?? 'link_mm4xqfx5') . '", column_values: ["' . $escaped . '"]}]) { items { id name column_values (ids: [' . $colIds . ']) { id text } } } }');

    foreach (($data['items_page_by_column_values']['items'] ?? []) as $item) {
        $candidates[(string) $item['id']] = ['item' => $item, 'reasons' => ['conversation_link']];
    }
}

// Scan the board for the weaker signals. The Lead board is small enough to
// walk in one page; if it outgrows that, this is the part to paginate.
$data = preSignupMondayQuery($apiUrl, $token, 'query { boards (ids: ' . $boardId . ') { items_page (limit: 500) { cursor items { id name column_values (ids: [' . $colIds . ']) { id text } } } } }');

if ($data === null && !$candidates) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'monday_unavailable']);
    exit;
}

$wantEmail = strtolower(trim((string) $row['email']));
$wantName  = strtolower(trim((string) $row['shopName']));

foreach (($data['boards'][0]['items_page']['items'] ?? []) as $item) {
    $values = [];
    foreach (($item['column_values'] ?? []) as $value) {
        $values[(string) $value['id']] = trim((string) ($value['text'] ?? ''));
    }

    $reasons = [];

    foreach (['mobNumber', 'shopNumber'] as $field) {
        $ours = trim((string) $row[$field]);
        if ($ours === '') {
            continue;
        }
        foreach (['mobNumber', 'shopNumber'] as $theirField) {
            $theirs = $values[$itemCols[$theirField] ?? ''] ?? '';
            if ($theirs !== '' && preSignupPhonesMatch($ours, $theirs)) {
                $reasons[] = 'phone';
                break 2;
            }
        }
    }

    $theirEmail = strtolower($values[$itemCols['email'] ?? ''] ?? '');
    if ($wantEmail !== '' && $theirEmail !== '' && $wantEmail === $theirEmail) {
        $reasons[] = 'email';
    }

    $theirName = strtolower(trim((string) ($item['name'] ?? '')));
    if ($wantName !== '' && $theirName !== '' && $wantName === $theirName) {
        $reasons[] = 'shop_name';
    }

    if (!$reasons) {
        continue;
    }

    $key = (string) $item['id'];
    if (isset($candidates[$key])) {
        $candidates[$key]['reasons'] = array_values(array_unique(
            array_merge($candidates[$key]['reasons'], $reasons)
        ));
    } else {
        $candidates[$key] = ['item' => $item, 'reasons' => $reasons];
    }
}

// Shape each candidate for a person to read. The address is included because
// it is what distinguishes two branches of the same shop sharing one number.
$out = [];
foreach ($candidates as $itemId => $candidate) {
    $values = [];
    foreach (($candidate['item']['column_values'] ?? []) as $value) {
        $values[(string) $value['id']] = trim((string) ($value['text'] ?? ''));
    }

    $address = array_filter([
        $values[$itemCols['streetAddress'] ?? ''] ?? '',
        $values[$itemCols['citySuburb'] ?? ''] ?? '',
        $values[$itemCols['state'] ?? ''] ?? '',
        $values[$itemCols['postalCode'] ?? ''] ?? '',
    ]);

    $out[] = [
        'itemId'        => (string) $itemId,
        'name'          => (string) ($candidate['item']['name'] ?? ''),
        'opportunityId' => $values[$oppColumn] ?? '',
        'address'       => implode(', ', $address),
        'phone'         => $values[$itemCols['mobNumber'] ?? ''] ?? '',
        'email'         => $values[$itemCols['email'] ?? ''] ?? '',
        'matchedOn'     => $candidate['reasons'],
        'url'           => 'https://localforyou.monday.com/boards/' . $boardId . '/pulses/' . $itemId,
    ];
}

// A link on the conversation itself outranks a shared phone number, so the
// strongest evidence is listed first.
usort($out, static function (array $a, array $b): int {
    $weigh = static function (array $c): int {
        $score = 0;
        if (in_array('conversation_link', $c['matchedOn'], true)) $score += 100;
        if (in_array('email', $c['matchedOn'], true))           $score += 10;
        if (in_array('shop_name', $c['matchedOn'], true))       $score += 5;
        if (in_array('phone', $c['matchedOn'], true))           $score += 1;
        return $score;
    };
    return $weigh($b) <=> $weigh($a);
});

echo json_encode([
    'success'    => true,
    'ref'        => 'prelead-' . str_pad((string) $id, 7, '0', STR_PAD_LEFT),
    'linked'     => trim((string) $row['opportunityId']) !== '',
    'candidates' => $out,
    // Whoever calls this decides. One candidate still wants a human nod, and
    // several may be genuinely different branches sharing a phone number.
    'advice'     => count($out) === 0
        ? 'no_match_treat_as_new'
        : (count($out) === 1 ? 'confirm_single_candidate' : 'ask_which_branch'),
], JSON_UNESCAPED_UNICODE);
