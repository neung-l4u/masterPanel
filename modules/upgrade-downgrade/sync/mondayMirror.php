<?php
/**
 * Mirror orchestration: fetch from Monday, match, write.
 *
 * The extractors at the top are pure -- they take a decoded response and
 * return rows -- so the response shapes can be tested against fixtures
 * without touching the network.
 */

const UP_DOWN_PAGE_LIMIT     = 100;  // keeps nested column queries under Monday's complexity cap
const UP_DOWN_MAX_PAGES      = 50;   // 100 * 50 = 5000. The binding board is
                                 // Active Subscriptions, measured at 2865
                                 // items; the per-country project boards are
                                 // far smaller (AU, the largest, is ~1300).
                                 // The guard below turns an overflow into an
                                 // error rather than a silent truncation, so
                                 // raising this is about headroom, not safety.
const UP_DOWN_CONTACT_CHUNK  = 100;

// ── Pure extractors ──────────────────────────────────────────────────────────

/**
 * One row per project item. `contact_id` comes specifically from
 * connect_boards0: a project item carries several board-relation columns and
 * only that one points at Contacts & Accounts (board 1862866069).
 */
function upDownExtractProjectRows(array $response, string $country, int $boardId): array
{
    $rows = [];

    foreach (($response['data']['boards'] ?? []) as $board) {
        foreach (($board['items_page']['items'] ?? []) as $item) {
            $shopType  = '';
            $contactId = '';

            foreach (($item['column_values'] ?? []) as $col) {
                if (($col['id'] ?? '') === 'color1') {
                    $shopType = (string) ($col['text'] ?? '');
                }
                if (($col['id'] ?? '') === 'connect_boards0') {
                    $contactId = (string) ($col['linked_item_ids'][0] ?? '');
                }
            }

            $rows[] = [
                'shop_id'    => (string) ($item['id'] ?? ''),
                'country'    => $country,
                'board_id'   => (string) $boardId,
                'shop_name'  => (string) ($item['name'] ?? ''),
                'shop_type'  => $shopType,
                'contact_id' => $contactId,
            ];
        }
    }

    return $rows;
}

/**
 * Owner name, phone and email from a Contacts item.
 *
 * Unlike live.php, the email is never used as a substitute for a missing
 * phone: here the email is the lookup key, and writing it into the phone field
 * would both lose the key and show an email where the form expects a number.
 */
function upDownExtractContactFields(array $contactItem): array
{
    $first = $last = $mobile = $altPhone = $email = '';

    foreach (($contactItem['column_values'] ?? []) as $cc) {
        switch ($cc['id'] ?? '') {
            case 'text0':          $first    = (string) ($cc['text'] ?? ''); break;
            case 'text7':          $last     = (string) ($cc['text'] ?? ''); break;
            case 'contact_phone':  $mobile   = (string) ($cc['text'] ?? ''); break;
            case 'dup__of_mobile': $altPhone = (string) ($cc['text'] ?? ''); break;
            case 'contact_email':  $email    = (string) ($cc['text'] ?? ''); break;
        }
    }

    $owner = trim($first . ' ' . $last);
    if ($owner === '') $owner = (string) ($contactItem['name'] ?? '');

    return [
        'owner_name' => $owner,
        'phone'      => $mobile !== '' ? $mobile : $altPhone,
        'email'      => upDownNormalizeEmail($email),
    ];
}

/**
 * Strip the currency, price and SKU a Monday item name carries, leaving the
 * product name a customer should see.
 *
 * Only used for the fallback when the mirror column is empty. Monday item names
 * follow "CUR - $N SKU Product Name", sometimes several joined by commas, e.g.
 * "USD - $199 SS01M12 Social Media Marketing Solo - 12 Months". Dropping the
 * price column from the response achieved nothing for these rows, because the
 * price was inside the name.
 *
 * Returns the original if cleaning would leave nothing: an unrecognised shape
 * should show something imperfect rather than a blank line.
 */
function upDownCleanProductName(string $name): string
{
    $out = [];

    foreach (preg_split('/\s*,\s*/', $name) as $part) {
        $p = trim($part);
        if ($p === '') continue;

        // Leading currency and price: "USD - $199 ", "USA PB - $97 ".
        $p = preg_replace('/^[A-Za-z][A-Za-z ]{0,12}-\s*\$?[\d,]+(?:\.\d+)?\s+/', '', $p, 1);
        // Leading SKU: uppercase letters then a digit, e.g. "LSB01M12 ".
        $p = preg_replace('/^[A-Z]{2,12}\d[A-Z0-9]*\s+/', '', $p, 1);
        // Trailing bare price some names carry: "... Handling Fee 172.5", "... $0".
        $p = preg_replace('/\s+\$?\d+(?:[.,]\d+)?$/', '', $p, 1);

        // Keep only what still reads as a name. A price-only part such as
        // "USD - $49" cleans down to debris like "USD -", which is worse to show
        // than the original, so require a word that is not left dangling on a
        // separator.
        $p = trim($p, " \t-");
        if ($p !== '' && preg_match('/[A-Za-z]{3}/', $p) && !preg_match('/^[A-Z]{2,4}$/', $p)) {
            $out[] = $p;
        }
    }

    return $out === [] ? trim($name) : implode(', ', $out);
}

/**
 * Active subscription rows. The active test is a case-insensitive "contains
 * active", identical to the existing getSubscriptions.php, so this mirror and
 * the staff-mode live call agree on what counts.
 */
function upDownExtractSubscriptionRows(array $response): array
{
    $rows = [];

    foreach (($response['data']['boards'] ?? []) as $board) {
        foreach (($board['items_page']['items'] ?? []) as $item) {
            $status = $price = $currency = $account = $linked = $group = '';

            foreach (($item['column_values'] ?? []) as $col) {
                switch ($col['id'] ?? '') {
                    case 'status':          $status   = (string) ($col['text'] ?? ''); break;
                    case 'numbers':         $price    = (string) ($col['text'] ?? ''); break;
                    case 'status0':         $currency = (string) ($col['text'] ?? ''); break;
                    case 'text9':           $account  = (string) ($col['text'] ?? ''); break;
                    case 'connect_boards':  $linked   = (string) ($col['linked_item_ids'][0] ?? ''); break;
                    case 'lookup_mkwgjt31': $group    = (string) ($col['display_value'] ?? ''); break;
                }
            }

            if (stripos($status, 'active') === false) continue;

            $rows[] = [
                'sub_id'         => (string) ($item['id'] ?? ''),
                'raw_name'       => (string) ($item['name'] ?? ''),
                // The mirror column is already the customer-facing name ("Pro
                // Online Ordering System"). It is empty on a sizeable minority
                // of rows, and the item name it falls back to carries the
                // currency, price and SKU -- so the fallback has to be cleaned,
                // or prices reach a list that is specified to show names only.
                'product_group'  => $group !== ''
                    ? $group
                    : upDownCleanProductName((string) ($item['name'] ?? '')),
                'price'          => $price === '' ? null : $price,
                'currency'       => strtoupper($currency),
                'account_name'   => $account,
                'linked_shop_id' => $linked,
            ];
        }
    }

    return $rows;
}

// ── Fetching ─────────────────────────────────────────────────────────────────

function upDownProjectPageQuery(int $boardId, ?string $cursor): string
{
    $cursorArg = $cursor ? ', cursor: "' . addslashes($cursor) . '"' : '';
    $limit     = UP_DOWN_PAGE_LIMIT;

    return <<<GQL
    {
      boards(ids: [$boardId]) {
        id
        items_page(limit: $limit$cursorArg) {
          cursor
          items {
            id
            name
            column_values(ids: ["color1", "connect_boards0"]) {
              id
              text
              ... on BoardRelationValue { linked_item_ids }
            }
          }
        }
      }
    }
    GQL;
}

function upDownSubscriptionPageQuery(?string $cursor): string
{
    $cursorArg = $cursor ? ', cursor: "' . addslashes($cursor) . '"' : '';
    $limit     = UP_DOWN_PAGE_LIMIT;
    $board     = UP_DOWN_SUBSCRIPTIONS_BOARD;

    return <<<GQL
    {
      boards(ids: [$board]) {
        items_page(limit: $limit$cursorArg) {
          cursor
          items {
            id
            name
            column_values(ids: ["status", "numbers", "status0", "text9",
                                "connect_boards", "lookup_mkwgjt31"]) {
              id
              text
              ... on BoardRelationValue { linked_item_ids }
              ... on MirrorValue { display_value }
            }
          }
        }
      }
    }
    GQL;
}

function upDownContactBatchQuery(array $ids): string
{
    $idList = implode(',', array_map('intval', $ids));
    $count  = count($ids);

    return <<<GQL
    {
      items(ids: [$idList], limit: $count) {
        id
        name
        column_values(ids: ["text0", "text7", "contact_phone",
                            "dup__of_mobile", "contact_email"]) {
          id
          text
        }
      }
    }
    GQL;
}

/**
 * Page a board to exhaustion. Returns ['rows' => array, 'error' => ?string].
 *
 * A page that fails after its retries aborts the whole board with an error
 * rather than returning what it got: a truncated board looks like a set of
 * closed projects, and stale-row deletion would then delete live shops.
 */
function upDownFetchAllPages(string $token, callable $queryFor, callable $extract): array
{
    $rows   = [];
    $cursor = null;

    for ($page = 0; $page < UP_DOWN_MAX_PAGES; $page++) {
        $res = upDownMondayRequestRetrying($token, $queryFor($cursor));

        if (isset($res['error']) || isset($res['errors']) || !isset($res['data'])) {
            $msg = $res['error'] ?? ($res['errors'][0]['message'] ?? 'unknown Monday error');
            return ['rows' => [], 'error' => $msg];
        }

        $rows = array_merge($rows, $extract($res));

        $cursor = $res['data']['boards'][0]['items_page']['cursor'] ?? null;
        if (!$cursor) break;
    }

    // Exhausting the page cap with a cursor still pending means the board is
    // larger than UP_DOWN_MAX_PAGES * UP_DOWN_PAGE_LIMIT. Returning the partial set
    // here would hand a truncated board to the stale-row deletion, which is
    // the exact failure this function exists to prevent.
    if ($cursor) {
        return ['rows' => [], 'error' => 'page cap reached with more pages pending; raise UP_DOWN_MAX_PAGES'];
    }

    return ['rows' => $rows, 'error' => null];
}

// ── Orchestration ────────────────────────────────────────────────────────────

/**
 * Mirror one country's projects, with their contact details.
 *
 * On any fetch error the country is skipped with its existing rows left
 * intact, mirroring live.php's refusal to cache an error as an empty result.
 */
function upDownSyncCountry($db, string $token, string $country, string $syncedAt): array
{
    $boardId = UP_DOWN_PROJECT_BOARDS[$country] ?? null;
    if ($boardId === null) {
        return ['country' => $country, 'error' => 'unknown country', 'shops' => 0, 'deleted' => 0];
    }

    $fetched = upDownFetchAllPages(
        $token,
        fn($cursor) => upDownProjectPageQuery($boardId, $cursor),
        fn($res) => upDownExtractProjectRows($res, $country, $boardId)
    );

    if ($fetched['error'] !== null) {
        return ['country' => $country, 'error' => $fetched['error'], 'shops' => 0, 'deleted' => 0];
    }

    $rows = $fetched['rows'];
    if ($rows === []) {
        // An empty board is indistinguishable from a silent failure, and
        // acting on it would delete every shop in the country.
        return ['country' => $country, 'error' => 'board returned zero items', 'shops' => 0, 'deleted' => 0];
    }

    // Resolve contacts in batches: one request per 100 ids rather than per shop.
    $contactIds = array_values(array_unique(array_filter(array_column($rows, 'contact_id'))));
    $contacts   = [];

    foreach (array_chunk($contactIds, UP_DOWN_CONTACT_CHUNK) as $chunk) {
        $res = upDownMondayRequestRetrying($token, upDownContactBatchQuery($chunk));

        // A chunk that failed its retries must abort the country before any
        // write. Proceeding would upsert every shop in the chunk with an empty
        // email, and because the upsert sets email = VALUES(email)
        // unconditionally, that ERASES the previously-mirrored address. The
        // email is the customer-mode lookup key, so those shops would silently
        // become unfindable -- and the country would still report success and
        // run its stale-row deletion.
        //
        // Note the test is isset($res['data']), not the absence of an error
        // key: Monday also answers with error_message/error_code on rate
        // limits, which an error-key check would read as success.
        if (!isset($res['data']['items'])) {
            $msg = $res['error'] ?? ($res['errors'][0]['message'] ?? 'unknown Monday error');
            return ['country' => $country, 'error' => 'contact batch failed: ' . $msg,
                    'shops' => 0, 'deleted' => 0];
        }

        // A success-shaped response whose items array is empty reaches the same
        // outcome as an outright failure: every shop in the chunk takes the
        // blank-email fallback and its mirrored address is erased. Monday
        // answers this way when all the requested ids are inaccessible, so it
        // cannot be distinguished from a scope or permission problem -- and
        // erring toward "do not erase" is the safe direction.
        if ($res['data']['items'] === [] && $chunk !== []) {
            return ['country' => $country,
                    'error' => 'contact batch returned no items for ' . count($chunk) . ' requested ids',
                    'shops' => 0, 'deleted' => 0];
        }

        foreach ($res['data']['items'] as $item) {
            $contacts[(string) $item['id']] = upDownExtractContactFields($item);
        }
    }

    $inserted = $updated = 0;

    foreach ($rows as $row) {
        $c = $contacts[$row['contact_id']] ?? ['owner_name' => '', 'phone' => '', 'email' => ''];

        $action = upDownUpsertShop($db, [
            'shop_id'    => $row['shop_id'],
            'country'    => $row['country'],
            'email'      => $c['email'],
            'shop_name'  => $row['shop_name'],
            'shop_type'  => $row['shop_type'],
            'owner_name' => $c['owner_name'],
            'phone'      => $c['phone'],
            'board_id'   => $row['board_id'],
        ], $syncedAt);

        $action === 'inserted' ? $inserted++ : $updated++;
    }

    $deleted = upDownDeleteStaleShops($db, $country, $syncedAt);

    return [
        'country'   => $country,
        'error'     => null,
        'shops'     => count($rows),
        'inserted'  => $inserted,
        'updated'   => $updated,
        'deleted'   => $deleted,
        'withEmail' => count(array_filter($rows, fn($r) => ($contacts[$r['contact_id']]['email'] ?? '') !== '')),
    ];
}

/**
 * Mirror the Active Subscriptions board and join each row to a mirrored shop.
 *
 * Runs after every country, because the name fallback needs the shop table
 * already populated.
 */
function upDownSyncSubscriptions($db, string $token, string $syncedAt): array
{
    $fetched = upDownFetchAllPages(
        $token,
        fn($cursor) => upDownSubscriptionPageQuery($cursor),
        fn($res) => upDownExtractSubscriptionRows($res)
    );

    if ($fetched['error'] !== null) {
        return ['error' => $fetched['error'], 'subscriptions' => 0,
                'matched' => ['relation' => 0, 'name' => 0, 'none' => 0], 'deleted' => 0];
    }

    $rows = $fetched['rows'];

    // One combined name map across countries: the subscriptions board is global
    // and carries no country of its own.
    $nameMap = [];
    foreach (array_keys(UP_DOWN_PROJECT_BOARDS) as $cc) {
        $nameMap += upDownLoadShopNameMap($db, $cc);
    }

    $matched = ['relation' => 0, 'name' => 0, 'none' => 0];

    foreach ($rows as $row) {
        $resolved = upDownResolveShopId($row, $nameMap);
        $matched[$resolved['matched_by']]++;

        upDownUpsertSubscription($db, [
            'sub_id'        => $row['sub_id'],
            'shop_id'       => $resolved['shop_id'],
            'raw_name'      => $row['raw_name'],
            'product_group' => $row['product_group'],
            'price'         => $row['price'],
            'currency'      => $row['currency'],
            'account_name'  => $row['account_name'],
            'matched_by'    => $resolved['matched_by'],
        ], $syncedAt);
    }

    $deleted = $rows === [] ? 0 : upDownDeleteStaleSubscriptions($db, $syncedAt);

    return ['error' => null, 'subscriptions' => count($rows),
            'matched' => $matched, 'deleted' => $deleted];
}

/**
 * Full run. One timestamp for the whole run, so "not refreshed by this run"
 * is a single unambiguous comparison.
 */
function upDownRunSync($db, ?array $onlyCountries = null): array
{
    $config   = upDownLoadConfig();
    $token    = $config['monday_token'];
    $syncedAt = date('Y-m-d H:i:s');

    $countries = $onlyCountries ?? array_keys(UP_DOWN_PROJECT_BOARDS);
    $summary   = ['syncedAt' => $syncedAt, 'countries' => [], 'subscriptions' => null];

    foreach ($countries as $country) {
        $summary['countries'][] = upDownSyncCountry($db, $token, $country, $syncedAt);
    }

    $summary['subscriptions'] = upDownSyncSubscriptions($db, $token, $syncedAt);

    return $summary;
}
