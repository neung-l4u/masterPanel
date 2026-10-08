<?php
/**
 * Mirror orchestration: fetch from Monday, match, write.
 *
 * The extractors at the top are pure -- they take a decoded response and
 * return rows -- so the response shapes can be tested against fixtures
 * without touching the network.
 */

const UDM_PAGE_LIMIT     = 100;  // keeps nested column queries under Monday's complexity cap
const UDM_MAX_PAGES      = 20;   // 100 * 20 = 2000, covers the largest board (AU ~1300)
const UDM_CONTACT_CHUNK  = 100;

// ── Pure extractors ──────────────────────────────────────────────────────────

/**
 * One row per project item. `contact_id` comes specifically from
 * connect_boards0: a project item carries several board-relation columns and
 * only that one points at Contacts & Accounts (board 1862866069).
 */
function udmExtractProjectRows(array $response, string $country, int $boardId): array
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
function udmExtractContactFields(array $contactItem): array
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
        'email'      => udmNormalizeEmail($email),
    ];
}

/**
 * Active subscription rows. The active test is a case-insensitive "contains
 * active", identical to the existing getSubscriptions.php, so this mirror and
 * the staff-mode live call agree on what counts.
 */
function udmExtractSubscriptionRows(array $response): array
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
                // The mirror column is the customer-facing name ("Pro Online
                // Ordering System"); the item name carries the SKU and price.
                'product_group'  => $group !== '' ? $group : (string) ($item['name'] ?? ''),
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

function udmProjectPageQuery(int $boardId, ?string $cursor): string
{
    $cursorArg = $cursor ? ', cursor: "' . addslashes($cursor) . '"' : '';
    $limit     = UDM_PAGE_LIMIT;

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

function udmSubscriptionPageQuery(?string $cursor): string
{
    $cursorArg = $cursor ? ', cursor: "' . addslashes($cursor) . '"' : '';
    $limit     = UDM_PAGE_LIMIT;
    $board     = UDM_SUBSCRIPTIONS_BOARD;

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

function udmContactBatchQuery(array $ids): string
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
function udmFetchAllPages(string $token, callable $queryFor, callable $extract): array
{
    $rows   = [];
    $cursor = null;

    for ($page = 0; $page < UDM_MAX_PAGES; $page++) {
        $res = udmMondayRequestRetrying($token, $queryFor($cursor));

        if (isset($res['error']) || isset($res['errors']) || !isset($res['data'])) {
            $msg = $res['error'] ?? ($res['errors'][0]['message'] ?? 'unknown Monday error');
            return ['rows' => [], 'error' => $msg];
        }

        $rows = array_merge($rows, $extract($res));

        $cursor = $res['data']['boards'][0]['items_page']['cursor'] ?? null;
        if (!$cursor) break;
    }

    // Exhausting the page cap with a cursor still pending means the board is
    // larger than UDM_MAX_PAGES * UDM_PAGE_LIMIT. Returning the partial set
    // here would hand a truncated board to the stale-row deletion, which is
    // the exact failure this function exists to prevent.
    if ($cursor) {
        return ['rows' => [], 'error' => 'page cap reached with more pages pending; raise UDM_MAX_PAGES'];
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
function udmSyncCountry($db, string $token, string $country, string $syncedAt): array
{
    $boardId = UDM_PROJECT_BOARDS[$country] ?? null;
    if ($boardId === null) {
        return ['country' => $country, 'error' => 'unknown country', 'shops' => 0, 'deleted' => 0];
    }

    $fetched = udmFetchAllPages(
        $token,
        fn($cursor) => udmProjectPageQuery($boardId, $cursor),
        fn($res) => udmExtractProjectRows($res, $country, $boardId)
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

    foreach (array_chunk($contactIds, UDM_CONTACT_CHUNK) as $chunk) {
        $res = udmMondayRequestRetrying($token, udmContactBatchQuery($chunk));

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
            $contacts[(string) $item['id']] = udmExtractContactFields($item);
        }
    }

    $inserted = $updated = 0;

    foreach ($rows as $row) {
        $c = $contacts[$row['contact_id']] ?? ['owner_name' => '', 'phone' => '', 'email' => ''];

        $action = udmUpsertShop($db, [
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

    $deleted = udmDeleteStaleShops($db, $country, $syncedAt);

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
function udmSyncSubscriptions($db, string $token, string $syncedAt): array
{
    $fetched = udmFetchAllPages(
        $token,
        fn($cursor) => udmSubscriptionPageQuery($cursor),
        fn($res) => udmExtractSubscriptionRows($res)
    );

    if ($fetched['error'] !== null) {
        return ['error' => $fetched['error'], 'subscriptions' => 0,
                'matched' => ['relation' => 0, 'name' => 0, 'none' => 0], 'deleted' => 0];
    }

    $rows = $fetched['rows'];

    // One combined name map across countries: the subscriptions board is global
    // and carries no country of its own.
    $nameMap = [];
    foreach (array_keys(UDM_PROJECT_BOARDS) as $cc) {
        $nameMap += udmLoadShopNameMap($db, $cc);
    }

    $matched = ['relation' => 0, 'name' => 0, 'none' => 0];

    foreach ($rows as $row) {
        $resolved = udmResolveShopId($row, $nameMap);
        $matched[$resolved['matched_by']]++;

        udmUpsertSubscription($db, [
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

    $deleted = $rows === [] ? 0 : udmDeleteStaleSubscriptions($db, $syncedAt);

    return ['error' => null, 'subscriptions' => count($rows),
            'matched' => $matched, 'deleted' => $deleted];
}

/**
 * Full run. One timestamp for the whole run, so "not refreshed by this run"
 * is a single unambiguous comparison.
 */
function udmRunSync($db, ?array $onlyCountries = null): array
{
    $config   = udmLoadConfig();
    $token    = $config['monday_token'];
    $syncedAt = date('Y-m-d H:i:s');

    $countries = $onlyCountries ?? array_keys(UDM_PROJECT_BOARDS);
    $summary   = ['syncedAt' => $syncedAt, 'countries' => [], 'subscriptions' => null];

    foreach ($countries as $country) {
        $summary['countries'][] = udmSyncCountry($db, $token, $country, $syncedAt);
    }

    $summary['subscriptions'] = udmSyncSubscriptions($db, $token, $syncedAt);

    return $summary;
}
