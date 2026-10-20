<?php
/**
 * Fetch the AI Receptionist client tracker from Monday.
 *
 * Throws RuntimeException on any failure, never an empty array: the caller
 * treats a Monday failure as "do not send". Paginates until the cursor is
 * exhausted so a board over one page is never silently truncated.
 *
 * Messages never include the token or the response body.
 */

require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/config.php';

const ARAYA_MONDAY_BOARD_ID = 2032016220;
const ARAYA_MONDAY_PAGE_SIZE = 100;
const ARAYA_MONDAY_MAX_PAGES = 50;

/** Board column id => flat output key. Ids are verbatim from the spec. */
const ARAYA_MONDAY_COLUMNS = [
    'color_mks6nf9m'           => 'live_status',
    'status'                   => 'setup_status',
    'multiple_person_mm1jwyxn' => 'po',
    'date4'                    => 'register_date',
    'date_mm7zba5t'            => 'live_date',
    'text_mm7z30rd'            => 'reason_for_cancel',
    'text_mm1b8q0z'            => 'note',
    'text_mm2y84v4'            => 'calendar_id',
    // text_mks6asn5 is the column gcal's own app/monday_notify.py matches
    // shop_email against (MONDAY_EMAIL_COLUMN_ID), so it is the field proven in
    // production to correspond to gcal's shop_email. Only 'email' is used for
    // matching. email_mm7r176d ("Email Address(email type)") is read by nothing
    // else in either repo; it is carried as email_alt for display only, so the
    // team can see whether the two ever disagree.
    'text_mks6asn5'            => 'email',
    'email_mm7r176d'           => 'email_alt',
];

const ARAYA_MONDAY_DATE_KEYS = ['register_date', 'live_date'];

/** GraphQL for the first page, or for a later page when $cursor is given. */
function arayaMondayBuildQuery(?string $cursor): string
{
    $ids = json_encode(array_keys(ARAYA_MONDAY_COLUMNS));
    $fields = "cursor items { id name column_values(ids: {$ids}) { id text value } }";
    $limit = ARAYA_MONDAY_PAGE_SIZE;
    if ($cursor === null) {
        $board = ARAYA_MONDAY_BOARD_ID;
        return "query { boards(ids: [{$board}]) { items_page(limit: {$limit}) { {$fields} } } }";
    }
    // json_encode yields a correctly escaped GraphQL string literal.
    return 'query { next_items_page(limit: ' . $limit . ', cursor: ' . json_encode($cursor) . ") { {$fields} } }";
}

/** Trimmed string value; non-strings read as empty. UTF-8 safe. */
function arayaMondayClean($value): string
{
    if (!is_string($value)) {
        return '';
    }
    return arayaTrimUtf8($value) ?? '';
}

/** A YYYY-MM-DD string or ''. Prefers the JSON value, falls back to text. */
function arayaMondayDate(?string $text, $value): string
{
    $candidates = [];
    if (is_string($value) && $value !== '') {
        $decoded = json_decode($value, true);
        if (is_array($decoded) && isset($decoded['date']) && is_string($decoded['date'])) {
            $candidates[] = $decoded['date'];
        }
    }
    $candidates[] = $text ?? '';
    foreach ($candidates as $c) {
        $c = arayaMondayClean($c);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $c)) {
            return $c;
        }
    }
    return '';
}

/** One Monday item => the flat row matchShops and buildBrief read. */
function arayaFlattenMondayItem(array $item): array
{
    $row = ['name' => arayaMondayClean($item['name'] ?? '')];
    foreach (ARAYA_MONDAY_COLUMNS as $key) {
        $row[$key] = '';
    }
    foreach (($item['column_values'] ?? []) as $cv) {
        if (!is_array($cv) || !isset($cv['id']) || !is_string($cv['id']) || !isset(ARAYA_MONDAY_COLUMNS[$cv['id']])) {
            continue;
        }
        $key = ARAYA_MONDAY_COLUMNS[$cv['id']];
        $text = isset($cv['text']) && is_string($cv['text']) ? $cv['text'] : null;
        if (in_array($key, ARAYA_MONDAY_DATE_KEYS, true)) {
            $row[$key] = arayaMondayDate($text, $cv['value'] ?? null);
        } elseif (($key === 'email' || $key === 'email_alt') && arayaMondayClean($text) === '') {
            // An email column's text can be empty while the JSON value still carries it.
            $decoded = is_string($cv['value'] ?? null) ? json_decode($cv['value'], true) : null;
            $row[$key] = is_array($decoded) ? arayaMondayClean($decoded['email'] ?? '') : '';
        } else {
            $row[$key] = arayaMondayClean($text);
        }
    }
    return $row;
}

/**
 * Parse one decoded GraphQL response page.
 *
 * Returns ['rows' => [...], 'cursor' => ?string]. Throws on a GraphQL error or
 * a missing page, so a failed page can never read as an empty one.
 */
function arayaParseMondayPage(array $decoded, bool $firstPage): array
{
    if (!empty($decoded['errors'])) {
        $msg = is_array($decoded['errors']) ? ($decoded['errors'][0]['message'] ?? null) : null;
        $msg = is_string($msg) ? mb_substr($msg, 0, 200) : 'unknown error';
        throw new RuntimeException("Monday returned a GraphQL error: {$msg}");
    }
    $data = $decoded['data'] ?? null;
    if (!is_array($data)) {
        throw new RuntimeException('Monday response has no data.');
    }

    $page = $firstPage ? ($data['boards'][0]['items_page'] ?? null) : ($data['next_items_page'] ?? null);
    if (!is_array($page)) {
        throw new RuntimeException($firstPage ? 'Monday response is missing the board.' : 'Monday response is missing the next page.');
    }
    if (!isset($page['items']) || !is_array($page['items'])) {
        throw new RuntimeException('Monday page has no items list.');
    }

    $rows = [];
    foreach ($page['items'] as $item) {
        if (!is_array($item)) {
            throw new RuntimeException('Monday page contains a malformed item.');
        }
        $rows[] = arayaFlattenMondayItem($item);
    }
    $cursor = $page['cursor'] ?? null;
    return ['rows' => $rows, 'cursor' => (is_string($cursor) && $cursor !== '') ? $cursor : null];
}

/** One POST. Returns the decoded body; throws on transport or HTTP failure. */
function arayaMondayPost(string $token, string $gql): array
{
    $ch = curl_init('https://api.monday.com/v2');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
            'API-Version: 2024-10',
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException("Monday request failed (curl error {$errno}).");
    }
    if ($status !== 200) {
        throw new RuntimeException("Monday returned HTTP {$status}.");
    }
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Monday returned a body that is not JSON.');
    }
    return $decoded;
}

function arayaFetchMonday(array $config, bool $useCache = true): array
{
    if ($useCache) {
        $cached = arayaCacheGet('monday', 300);
        if ($cached !== null && isset($cached['items']) && is_array($cached['items']) && $cached['items'] !== []) {
            return $cached['items'];
        }
    }

    $rows = [];
    $cursor = null;
    $seen = [];
    for ($page = 0; $page < ARAYA_MONDAY_MAX_PAGES; $page++) {
        $decoded = arayaMondayPost($config['monday_token'], arayaMondayBuildQuery($cursor));
        $parsed = arayaParseMondayPage($decoded, $cursor === null);
        $rows = array_merge($rows, $parsed['rows']);
        $cursor = $parsed['cursor'];
        if ($cursor === null) {
            break;
        }
        if (isset($seen[$cursor])) {
            throw new RuntimeException('Monday pagination returned a repeated cursor.');
        }
        $seen[$cursor] = true;
    }
    if ($cursor !== null) {
        throw new RuntimeException('Monday pagination did not finish within the page limit.');
    }
    if ($rows === []) {
        throw new RuntimeException('Monday returned zero items for the client tracker.');
    }

    arayaCachePut('monday', ['items' => $rows]);
    return $rows;
}
