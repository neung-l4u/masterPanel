# Customer-Mode Product Lookup by Email — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a customer in the upgrade form's customer mode enter their email and have the form prefill their shop details and their real active products, served from a local MySQL mirror of Monday refreshed twice a day.

**Architecture:** A cron script mirrors three Monday sources (six project boards, the Contacts board, the Active Subscriptions board) into two MySQL tables. A read-only endpoint answers one indexed query against that mirror. The form's JS calls it on email blur and fills fields that stay editable. The sync writes, the lookup reads, and they share no code path, so a sync failure can never break the form.

**Tech Stack:** PHP 8 (no framework), the repo's hand-rolled `db` mysqli wrapper, MySQL 8 / MariaDB (`db_localforyou`), jQuery 3, Bootstrap 5, Monday GraphQL API v2 (`API-Version: 2024-01`), plain-PHP test scripts (no PHPUnit in this repo).

**Spec:** `docs/superpowers/specs/2026-10-05-customer-email-product-lookup-design.md`

## Global Constraints

These apply to every task. Copy exact values; do not paraphrase.

- **Database:** `db_localforyou`. Tables: `l4u_ud_shops`, `l4u_ud_subscriptions`.
- **DB access:** always via the repo's `db` class. Include order is fixed and must be exactly this, in this order:
  `require_once` the module's `assets/db/db.php`, then `assets/db/initDB.php`, which defines `$db`. Never instantiate `new db(...)` directly. Never interpolate a value into SQL — pass it as a bound parameter to `$db->query()`.
- **Monday API:** endpoint `https://api.monday.com/v2`, POST, headers `Content-Type: application/json`, `Authorization: <token>`, `API-Version: 2024-01`.
- **Monday token:** read from `modules/upgrade-downgrade/assets/config.php`, which is gitignored. **Never hardcode the token in any new file.** (The existing `getSubscriptions.php:5` and `live.php:15` hardcode it; leave those two alone — migrating them is out of scope.)
- **Monday board ids** (project boards, keyed by country code):
  `CA 1943203287`, `AU 1943203246`, `NZ 1943203264`, `UK 1943203305`, `US 1940392927`, `TH 1943203205`
- **Monday board ids** (other): Active Subscriptions `1881439330`, Contacts & Accounts `1862866069`
- **Monday column ids** — exact strings, these are not guessable:
  - Project board: `connect_boards0` = board_relation to Contacts; `color1` = Customer/Industrial Type
  - Contacts board: `text0` = first name, `text7` = last name, `contact_phone` = mobile, `dup__of_mobile` = alt phone, `contact_email` = email
  - Active Subscriptions board: `status` = Subscription Status, `numbers` = Amount, `status0` = Currency, `text9` = Account Name, `connect_boards` = board_relation to Projects, `lookup_mkwgjt31` = mirror "Product Group"
- **Active filter:** a subscription counts as active when `stripos($statusText, 'active') !== false`. This matches the existing `getSubscriptions.php` behaviour exactly.
- **Timezone:** every entry-point file starts with `date_default_timezone_set("Asia/Bangkok");`
- **Charset:** `utf8mb4` / `utf8mb4_unicode_ci` on every table.
- **Naming:** new PHP functions are prefixed `udm` (upgrade-downgrade mirror), e.g. `udmNormalizeEmail`. This mirrors the `qr`/`gr` prefixes the QRCodeDynamicLink and googleReview modules use.
- **Email normalization:** `strtolower(trim($email))`. Stored normalized, queried normalized, so the index is usable without `LOWER()`.
- **Staleness threshold:** 48 hours. Past it the lookup response carries `"stale": true` but still returns the data.
- **Must not touch:** `assets/API/getSubscriptions.php`, `assets/API/selectProjectCountry/live.php`, any staff-mode code path, `views/downgradeForm.php`, `controllers/downgradeForm.js`.
- **Test command shape:** `php modules/upgrade-downgrade/tests/<name>.php` run from the repo root `c:\xampp\htdocs\masterPanel`. A test prints `PASS:`/`FAIL:` lines then `Passed: N  Failed: M` and exits non-zero if anything failed.
- **Commits:** one per task, Conventional Commits, scope `upgrade-downgrade`. End every commit message with:
  `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`

## File Structure

New files, each with one responsibility:

| File | Responsibility |
| --- | --- |
| `modules/upgrade-downgrade/assets/db/migration_ud_mirror.sql` | The two `CREATE TABLE`s. Nothing else. |
| `modules/upgrade-downgrade/assets/config.example.php` | Committed template showing the token key. |
| `modules/upgrade-downgrade/assets/config.php` | Real token. Gitignored. |
| `modules/upgrade-downgrade/.gitignore` | Ignores `assets/config.php`. |
| `modules/upgrade-downgrade/sync/mondayClient.php` | Monday HTTP + config loading. Knows nothing about our schema. |
| `modules/upgrade-downgrade/sync/mirrorMatch.php` | Pure functions: email normalization, shop-name normalization, `udmResolveShopId`. No I/O, no DB. |
| `modules/upgrade-downgrade/sync/mirrorRepo.php` | All writes to the two tables: upserts, stale-row deletion. |
| `modules/upgrade-downgrade/sync/mondayMirror.php` | Orchestration: fetch from Monday, match, write. Returns a summary array. |
| `modules/upgrade-downgrade/sync/syncMondayMirror.php` | Cron entry point. CLI-only with a token-guarded web fallback. |
| `modules/upgrade-downgrade/models/shopLookup.php` | The single read query. One function. |
| `modules/upgrade-downgrade/assets/API/lookupByEmail.php` | HTTP endpoint: validate, call lookup, encode JSON. No business logic. |
| `modules/upgrade-downgrade/tests/test_mirror_match.php` | Pure-function tests. No DB. |
| `modules/upgrade-downgrade/tests/test_mirror_repo.php` | Upsert/delete tests against fixture rows. |
| `modules/upgrade-downgrade/tests/test_shop_lookup.php` | Read-query tests against fixture rows. |
| `modules/upgrade-downgrade/tests/test_lookup_endpoint.php` | Endpoint input-handling tests. |

Modified:

| File | Change |
| --- | --- |
| `modules/upgrade-downgrade/views/upgradeForm.php` | Customer-mode product list container + lookup status line. |
| `modules/upgrade-downgrade/controllers/upgradeForm.js` | New customer-lookup IIFE. |

`mirrorMatch.php` is split out from `mondayMirror.php` deliberately: the matching rules are the part most likely to be wrong, and keeping them free of I/O means they can be tested exhaustively without a database or a network call.

---

### Task 1: Schema and config scaffolding

**Files:**

- Create: `modules/upgrade-downgrade/assets/db/migration_ud_mirror.sql`
- Create: `modules/upgrade-downgrade/assets/config.example.php`
- Create: `modules/upgrade-downgrade/.gitignore`
- Create: `modules/upgrade-downgrade/tests/test_mirror_schema.php`

**Interfaces:**

- Consumes: nothing.
- Produces: tables `l4u_ud_shops` and `l4u_ud_subscriptions` with the exact column names every later task uses; `modules/upgrade-downgrade/assets/config.php` returning an array with key `monday_token`.

- [ ] **Step 1: Write the migration SQL**

Create `modules/upgrade-downgrade/assets/db/migration_ud_mirror.sql`:

```sql
-- ============================================================
-- Migration: l4u_ud_shops, l4u_ud_subscriptions
-- Database : db_localforyou
-- Purpose  : local mirror of Monday projects/contacts/subscriptions so the
--            customer-mode upgrade form can look a shop up by email fast
--            enough to sit in a customer's typing flow.
-- ============================================================

CREATE TABLE IF NOT EXISTS `l4u_ud_shops` (
    `shop_id`     VARCHAR(20)  NOT NULL COMMENT 'Monday project item id',
    `country`     CHAR(2)      NOT NULL,
    `email`       VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'lowercased, trimmed',
    `shop_name`   VARCHAR(255) NOT NULL DEFAULT '',
    `shop_type`   VARCHAR(100) NOT NULL DEFAULT '',
    `owner_name`  VARCHAR(255) NOT NULL DEFAULT '',
    `phone`       VARCHAR(50)  NOT NULL DEFAULT '',
    `board_id`    VARCHAR(20)  NOT NULL DEFAULT '',
    `synced_at`   DATETIME     NOT NULL,

    PRIMARY KEY (`shop_id`),
    INDEX `idx_country_email` (`country`, `email`),
    INDEX `idx_country_name`  (`country`, `shop_name`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Mirror of Monday project items with their linked contact details';

CREATE TABLE IF NOT EXISTS `l4u_ud_subscriptions` (
    `sub_id`        VARCHAR(20)   NOT NULL COMMENT 'Monday subscription item id',
    `shop_id`       VARCHAR(20)   NOT NULL DEFAULT '' COMMENT 'FK to l4u_ud_shops.shop_id',
    `raw_name`      VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'item name incl. SKU and price',
    `product_group` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'mirror lookup_mkwgjt31, customer-facing',
    `price`         DECIMAL(12,2) DEFAULT NULL,
    `currency`      CHAR(3)       NOT NULL DEFAULT '',
    `account_name`  VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'text9, kept for fallback matching',
    `matched_by`    ENUM('relation','name','none') NOT NULL DEFAULT 'none',
    `synced_at`     DATETIME      NOT NULL,

    PRIMARY KEY (`sub_id`),
    INDEX `idx_shop`       (`shop_id`),
    INDEX `idx_matched_by` (`matched_by`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Mirror of active Monday subscriptions joined to mirrored shops';
```

- [ ] **Step 2: Apply the migration**

Run from the repo root:

```bash
/c/xampp/mysql/bin/mysql -u root -p db_localforyou < modules/upgrade-downgrade/assets/db/migration_ud_mirror.sql
```

(Use the password from `modules/upgrade-downgrade/assets/db/initDB.php`. If the MySQL client is not on PATH, the path above is the XAMPP default.)

Expected: no output, exit 0.

- [ ] **Step 3: Write the schema test**

Create `modules/upgrade-downgrade/tests/test_mirror_schema.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

function columnsOf($db, $table) {
    $rows = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    $out = [];
    foreach ($rows as $r) { $out[$r['Field']] = $r['Type']; }
    return $out;
}

echo "== l4u_ud_shops ==\n";
$shops = columnsOf($db, 'l4u_ud_shops');
foreach (['shop_id','country','email','shop_name','shop_type',
          'owner_name','phone','board_id','synced_at'] as $col) {
    test("has column {$col}", isset($shops[$col]));
}

echo "== l4u_ud_subscriptions ==\n";
$subs = columnsOf($db, 'l4u_ud_subscriptions');
foreach (['sub_id','shop_id','raw_name','product_group','price',
          'currency','account_name','matched_by','synced_at'] as $col) {
    test("has column {$col}", isset($subs[$col]));
}
test("matched_by is the expected enum",
     isset($subs['matched_by']) &&
     strpos($subs['matched_by'], "'relation'") !== false &&
     strpos($subs['matched_by'], "'name'") !== false &&
     strpos($subs['matched_by'], "'none'") !== false);

echo "== indexes ==\n";
$idx = $db->query("SHOW INDEX FROM `l4u_ud_shops`")->fetchAll();
$names = array_column($idx, 'Key_name');
test("l4u_ud_shops has idx_country_email", in_array('idx_country_email', $names, true));

$idx2 = $db->query("SHOW INDEX FROM `l4u_ud_subscriptions`")->fetchAll();
$names2 = array_column($idx2, 'Key_name');
test("l4u_ud_subscriptions has idx_shop", in_array('idx_shop', $names2, true));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 4: Run the schema test**

Run: `php modules/upgrade-downgrade/tests/test_mirror_schema.php`
Expected: every line `PASS:`, final line `Passed: 23  Failed: 0`, exit 0.

- [ ] **Step 5: Create the config template and gitignore**

Create `modules/upgrade-downgrade/assets/config.example.php`:

```php
<?php
/**
 * Upgrade/downgrade module configuration.
 *
 * Copy this file to config.php and fill in the values. config.php is
 * gitignored: the Monday token must never be committed.
 */

return [
    // Monday.com API token. Needs read access to the per-country project
    // boards, the Contacts & Accounts board, and the Active Subscriptions board.
    'monday_token' => '',

    // Shared secret for triggering sync/syncMondayMirror.php over HTTP.
    // Leave empty to allow CLI invocation only, which is the safe default.
    'sync_token' => '',
];
```

Create `modules/upgrade-downgrade/.gitignore`:

```gitignore
assets/config.php
```

- [ ] **Step 6: Create the real config (not committed)**

Copy the template and paste in the token currently hardcoded at
`modules/upgrade-downgrade/assets/API/getSubscriptions.php:5`:

```bash
cp modules/upgrade-downgrade/assets/config.example.php modules/upgrade-downgrade/assets/config.php
```

Then edit `config.php` and set `monday_token`. Verify it is ignored:

Run: `git check-ignore -v modules/upgrade-downgrade/assets/config.php`
Expected: a line naming `modules/upgrade-downgrade/.gitignore`. If it prints nothing, the gitignore is wrong — fix it before committing.

- [ ] **Step 7: Commit**

```bash
git add modules/upgrade-downgrade/assets/db/migration_ud_mirror.sql \
        modules/upgrade-downgrade/assets/config.example.php \
        modules/upgrade-downgrade/.gitignore \
        modules/upgrade-downgrade/tests/test_mirror_schema.php
git commit -m "feat(upgrade-downgrade): add Monday mirror tables and module config

The customer-mode form needs to find a shop by email fast enough to sit in a
customer's typing flow, which Monday's API cannot do. These two tables hold a
local mirror. Shops and subscriptions are separate tables because one shop
carries one to three or more subscriptions.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 2: Matching rules (pure functions)

**Files:**

- Create: `modules/upgrade-downgrade/sync/mirrorMatch.php`
- Create: `modules/upgrade-downgrade/tests/test_mirror_match.php`

**Interfaces:**

- Consumes: nothing. No DB, no network, no globals.
- Produces:
  - `udmNormalizeEmail(?string $email): string`
  - `udmNormalizeShopName(?string $name): string`
  - `udmResolveShopId(array $subscription, array $shopsByName): array` returning `['shop_id' => string, 'matched_by' => 'relation'|'name'|'none']`

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_mirror_match.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../sync/mirrorMatch.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== udmNormalizeEmail ==\n";
test("lowercases", udmNormalizeEmail('Owner@Shop.COM') === 'owner@shop.com');
test("trims", udmNormalizeEmail('  a@b.com  ') === 'a@b.com');
test("trims non-breaking space", udmNormalizeEmail("a@b.com\xC2\xA0") === 'a@b.com');
test("trims a leading non-breaking space", udmNormalizeEmail("\xC2\xA0a@b.com") === 'a@b.com');
test("null becomes empty string", udmNormalizeEmail(null) === '');
test("empty stays empty", udmNormalizeEmail('') === '');

echo "== udmNormalizeShopName ==\n";
test("lowercases", udmNormalizeShopName('Cher Thai Cuisine') === 'cher thai cuisine');
test("collapses inner whitespace",
     udmNormalizeShopName("Ivory  Thai \t @ Zetland") === 'ivory thai @ zetland');
test("trims", udmNormalizeShopName('  Demo Eats - USA  ') === 'demo eats - usa');
test("null becomes empty string", udmNormalizeShopName(null) === '');
test("keeps non-ASCII intact", udmNormalizeShopName('ร้านอาหารไทย') === 'ร้านอาหารไทย');

// A byte-set trim() would eat the trailing 0xA0 of ภ (U+0E20 = E0 B8 A0),
// leaving invalid UTF-8 that normalizes to '' and silently kills the fallback.
test("keeps a name ending in a 0xA0-byte character", udmNormalizeShopName('ลาภ') === 'ลาภ');
test("keeps that character mid-name", udmNormalizeShopName('ร้านภ') === 'ร้านภ');
test("keeps a leading U+0080-00BF character", udmNormalizeShopName('© Shop') === '© shop');
test("strips a surrounding nbsp", udmNormalizeShopName("\xC2\xA0Cher Thai\xC2\xA0") === 'cher thai');
test("collapses an inner nbsp", udmNormalizeShopName("Cher\xC2\xA0Thai") === 'cher thai');

echo "== udmResolveShopId: relation wins ==\n";
$shopsByName = [
    'cher thai cuisine' => '1879900670',
    'ivory thai @ zetland' => '1879899645',
];

$r = udmResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Anything'], $shopsByName);
test("relation present -> that id", $r['shop_id'] === '1879900670');
test("relation present -> matched_by relation", $r['matched_by'] === 'relation');

$r = udmResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Ivory Thai @ Zetland'], $shopsByName);
test("relation beats a conflicting name", $r['shop_id'] === '1879900670');
test("relation beats a conflicting name -> relation", $r['matched_by'] === 'relation');

echo "== udmResolveShopId: name fallback ==\n";
$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => 'Cher Thai Cuisine'], $shopsByName);
test("exact name -> shop id", $r['shop_id'] === '1879900670');
test("exact name -> matched_by name", $r['matched_by'] === 'name');

$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => '  CHER   thai cuisine '], $shopsByName);
test("name differing by case and padding still matches", $r['shop_id'] === '1879900670');
test("normalized name match -> matched_by name", $r['matched_by'] === 'name');

echo "== udmResolveShopId: no match ==\n";
$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => 'Nowhere Noodles'], $shopsByName);
test("unknown name -> empty shop id", $r['shop_id'] === '');
test("unknown name -> matched_by none", $r['matched_by'] === 'none');

$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => ''], $shopsByName);
test("no relation and no name -> none", $r['matched_by'] === 'none');

$r = udmResolveShopId([], $shopsByName);
test("missing keys do not throw", $r['matched_by'] === 'none');

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_mirror_match.php`
Expected: FAIL — PHP fatal error, `require_once(): Failed opening required .../sync/mirrorMatch.php`.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/sync/mirrorMatch.php`:

```php
<?php
/**
 * Pure matching rules for the Monday mirror.
 *
 * Deliberately free of database and network access: these rules are the part
 * of the sync most likely to be wrong, so they are tested exhaustively on
 * their own.
 */

/**
 * Canonical form for storing and querying an email: lowercase, trimmed.
 * Stored normalized and queried normalized, so idx_country_email is usable
 * without a LOWER() call that would defeat it.
 */
function udmNormalizeEmail(?string $email): string
{
    if ($email === null) return '';
    // Strips the non-breaking space that copy-paste from Monday carries in.
    // A /u regex, not trim(): trim() takes a BYTE set, so listing "\xC2\xA0"
    // there strips a lone trailing 0xA0 byte and truncates any multibyte
    // character whose encoding ends in 0xA0, leaving invalid UTF-8.
    $email = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $email);
    return strtolower($email);
}

/**
 * Canonical form for comparing shop names: lowercase, trimmed, inner runs of
 * whitespace collapsed to one space. Used only for the fallback match when a
 * subscription has no relation to a project item.
 */
function udmNormalizeShopName(?string $name): string
{
    if ($name === null) return '';
    // See udmNormalizeEmail: a byte-set trim() would corrupt a name ending in
    // a multibyte character whose encoding ends in 0xA0 -- Thai U+0E20 is
    // E0 B8 A0, so 'ลาภ' would be truncated to invalid UTF-8 and then
    // normalize to an empty string, silently breaking the name fallback.
    $name = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $name);
    $name = preg_replace('/[\s\x{00A0}]+/u', ' ', $name);
    return mb_strtolower($name, 'UTF-8');
}

/**
 * Decide which mirrored shop a subscription belongs to.
 *
 * The Monday board-relation column (`connect_boards`, titled "Projects") is
 * authoritative: it points straight at the project item, and a 100-row sample
 * had it set on every row. The normalized-name fallback exists because that
 * sample does not prove the whole board, and `matched_by` makes how often the
 * fallback fires measurable.
 *
 * @param array $subscription Needs 'linked_shop_id' and 'account_name'.
 * @param array $shopsByName  Map of normalized shop name => shop_id.
 * @return array{shop_id: string, matched_by: string}
 */
function udmResolveShopId(array $subscription, array $shopsByName): array
{
    $linked = (string) ($subscription['linked_shop_id'] ?? '');
    if ($linked !== '') {
        return ['shop_id' => $linked, 'matched_by' => 'relation'];
    }

    $key = udmNormalizeShopName($subscription['account_name'] ?? '');
    if ($key !== '' && isset($shopsByName[$key])) {
        return ['shop_id' => (string) $shopsByName[$key], 'matched_by' => 'name'];
    }

    return ['shop_id' => '', 'matched_by' => 'none'];
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_mirror_match.php`
Expected: all `PASS:`, every assertion passing, exit 0. (Count the assertions in
the test above rather than trusting a number here.)

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/sync/mirrorMatch.php \
        modules/upgrade-downgrade/tests/test_mirror_match.php
git commit -m "feat(upgrade-downgrade): add mirror matching rules

The Monday relation column is authoritative for joining a subscription to its
project, with a normalized shop-name fallback for rows that leave it unset.
matched_by records which path fired so the fallback rate is measurable rather
than invisible.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 3: Mirror repository (writes)

**Files:**

- Create: `modules/upgrade-downgrade/sync/mirrorRepo.php`
- Create: `modules/upgrade-downgrade/tests/test_mirror_repo.php`

**Interfaces:**

- Consumes: `$db` (the `db` class instance from `initDB.php`).
- Produces:
  - `udmUpsertShop($db, array $shop, string $syncedAt): string` — returns `'inserted'` or `'updated'`. `$shop` keys: `shop_id`, `country`, `email`, `shop_name`, `shop_type`, `owner_name`, `phone`, `board_id`.
  - `udmUpsertSubscription($db, array $sub, string $syncedAt): string` — returns `'inserted'` or `'updated'`. `$sub` keys: `sub_id`, `shop_id`, `raw_name`, `product_group`, `price`, `currency`, `account_name`, `matched_by`.
  - `udmDeleteStaleShops($db, string $country, string $syncedAt): int`
  - `udmDeleteStaleSubscriptions($db, string $syncedAt): int`
  - `udmLoadShopNameMap($db, string $country): array` — normalized shop name => shop_id.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_mirror_repo.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) use (&$db) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    // Clean up even on the failure path, so a crashed run does not leave
    // fixture rows behind for the next one.
    if (isset($db) && function_exists('cleanup')) cleanup($db);
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

// Fixture rows use a reserved country code 'ZZ' and TEST_ ids so they can
// never collide with mirrored production data.
// ZY is included because the country-isolation test below creates a row there;
// without it, a run that dies early leaves TEST_SHOP_C behind and the next
// run's cleanup does not clear it.
function cleanup($db) {
    $db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
    $db->query("DELETE FROM l4u_ud_shops WHERE country IN ('ZZ','ZY')");
}
cleanup($db);

// Deliberately far in the past. udmDeleteStaleSubscriptions is global by
// design (the subscriptions board is one board, synced in one pass), so a
// fixture timestamp anywhere near today would make this test delete real
// mirrored rows synced earlier the same day.
$now   = '2000-01-01 06:00:00';
$later = '2000-01-01 18:00:00';

$shopA = [
    'shop_id' => 'TEST_SHOP_A', 'country' => 'ZZ',
    'email' => 'owner@testshop.com', 'shop_name' => 'Test Shop A',
    'shop_type' => 'Thai Restaurants', 'owner_name' => 'Somchai Test',
    'phone' => '+66812345678', 'board_id' => '1943203205',
];

echo "== udmUpsertShop ==\n";
test("first write reports inserted", udmUpsertShop($db, $shopA, $now) === 'inserted');
test("second write reports updated", udmUpsertShop($db, $shopA, $now) === 'updated');

$row = $db->query("SELECT * FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("stores shop_name", $row['shop_name'] === 'Test Shop A');
test("stores email", $row['email'] === 'owner@testshop.com');

// The fixture email above is already lowercase and unpadded, so it cannot tell
// a normalized write from a raw one. Task 7's lookup queries by the normalized
// form, so an unnormalized write here would make that lookup silently miss.
$shopMixed = $shopA;
$shopMixed['shop_id'] = 'TEST_SHOP_MIXEDMAIL';
$shopMixed['email']   = "  Owner.MixedCase@TestShop.COM\xC2\xA0";
// Written at $later, not $now, so it counts as "seen in this run" and the
// stale-shop deletion further down still deletes exactly TEST_SHOP_B.
udmUpsertShop($db, $shopMixed, $later);
$rowMixed = $db->query("SELECT email FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_MIXEDMAIL')->fetchArray();
test("normalizes a mixed-case padded email before storing",
     $rowMixed['email'] === 'owner.mixedcase@testshop.com');
test("stores synced_at", $row['synced_at'] === $now);

$shopA['shop_name'] = 'Test Shop A Renamed';
udmUpsertShop($db, $shopA, $later);
$row = $db->query("SELECT * FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("upsert overwrites a changed name", $row['shop_name'] === 'Test Shop A Renamed');
test("upsert advances synced_at", $row['synced_at'] === $later);

echo "== udmUpsertSubscription ==\n";
$subA = [
    'sub_id' => 'TEST_SUB_A', 'shop_id' => 'TEST_SHOP_A',
    'raw_name' => 'THB - 4900 LSS01M00 Local Starter Solo - No Contract',
    'product_group' => 'Local Starter Solo', 'price' => '4900.00',
    'currency' => 'THB', 'account_name' => 'Test Shop A',
    'matched_by' => 'relation',
];
test("first write reports inserted", udmUpsertSubscription($db, $subA, $later) === 'inserted');
test("second write reports updated", udmUpsertSubscription($db, $subA, $later) === 'updated');

$row = $db->query("SELECT * FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')->fetchArray();
test("stores product_group", $row['product_group'] === 'Local Starter Solo');
test("stores price as decimal", (float) $row['price'] === 4900.0);
test("stores matched_by", $row['matched_by'] === 'relation');

$subNull = $subA;
$subNull['sub_id'] = 'TEST_SUB_NULLPRICE';
$subNull['price']  = null;
udmUpsertSubscription($db, $subNull, $later);
$row = $db->query("SELECT price FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_NULLPRICE')->fetchArray();
test("a null price stays null, not 0.00", $row['price'] === null);

echo "== udmLoadShopNameMap ==\n";
$map = udmLoadShopNameMap($db, 'ZZ');
test("keys are normalized names", isset($map['test shop a renamed']));
test("value is the shop id", ($map['test shop a renamed'] ?? '') === 'TEST_SHOP_A');

echo "== stale deletion ==\n";
// A shop written in the earlier run and not refreshed in this one is stale.
$shopB = $shopA;
$shopB['shop_id']   = 'TEST_SHOP_B';
$shopB['shop_name'] = 'Test Shop B';
$shopB['email']     = 'b@testshop.com';
udmUpsertShop($db, $shopB, $now);   // older timestamp

$deleted = udmDeleteStaleShops($db, 'ZZ', $later);
test("deletes the shop not seen in this run", $deleted === 1);
test("keeps the shop seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')
              ->fetchArray()['c'] === 1);
test("stale shop is gone",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_B')
              ->fetchArray()['c'] === 0);

$subStale = $subA;
$subStale['sub_id'] = 'TEST_SUB_STALE';
udmUpsertSubscription($db, $subStale, $now);   // older timestamp
// Count real rows first: the delete is global, so this also proves it cannot
// reach anything outside the fixture window.
$realBefore = (int) $db->query(
    "SELECT COUNT(*) AS c FROM l4u_ud_subscriptions WHERE sub_id NOT LIKE 'TEST\_%'"
)->fetchArray()['c'];

$deletedSubs = udmDeleteStaleSubscriptions($db, $later);
test("deletes exactly the one stale fixture subscription", $deletedSubs === 1);
test("the stale fixture subscription is gone",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_STALE')
              ->fetchArray()['c'] === 0);
test("no real subscription row was touched",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_subscriptions WHERE sub_id NOT LIKE 'TEST\_%'")
              ->fetchArray()['c'] === $realBefore);
test("keeps subscriptions seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')
              ->fetchArray()['c'] === 1);

echo "== country isolation ==\n";
// Deleting stale rows for one country must not touch another country's rows.
$shopC = $shopA;
$shopC['shop_id'] = 'TEST_SHOP_C';
$shopC['country'] = 'ZY';
$shopC['email']   = 'c@testshop.com';
udmUpsertShop($db, $shopC, $now);
udmDeleteStaleShops($db, 'ZZ', $later);
test("a different country's stale row survives",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_C')
              ->fetchArray()['c'] === 1);
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZY'");

cleanup($db);
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_mirror_repo.php`
Expected: FAIL — `Failed opening required .../sync/mirrorRepo.php`.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/sync/mirrorRepo.php`:

```php
<?php
/**
 * All writes to the Monday mirror tables.
 *
 * Every statement is a prepared statement through the repo's db wrapper; no
 * value is ever interpolated into SQL.
 */

/**
 * Insert or update one mirrored shop. Returns 'inserted' or 'updated'.
 *
 * MySQL reports affected_rows as 1 for an insert and 2 for an update that
 * changed something, but 0 for an update that changed nothing -- and a sync
 * re-running over unchanged data hits that case constantly. So existence is
 * checked first rather than inferred from affected_rows.
 */
function udmUpsertShop($db, array $shop, string $syncedAt): string
{
    $existing = $db->query(
        "SELECT shop_id FROM l4u_ud_shops WHERE shop_id = ?",
        (string) $shop['shop_id']
    )->fetchArray();
    $action = empty($existing) ? 'inserted' : 'updated';

    $db->query(
        "INSERT INTO l4u_ud_shops
            (shop_id, country, email, shop_name, shop_type,
             owner_name, phone, board_id, synced_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            country    = VALUES(country),
            email      = VALUES(email),
            shop_name  = VALUES(shop_name),
            shop_type  = VALUES(shop_type),
            owner_name = VALUES(owner_name),
            phone      = VALUES(phone),
            board_id   = VALUES(board_id),
            synced_at  = VALUES(synced_at)",
        (string) $shop['shop_id'],
        (string) $shop['country'],
        udmNormalizeEmail($shop['email'] ?? ''),
        (string) ($shop['shop_name']  ?? ''),
        (string) ($shop['shop_type']  ?? ''),
        (string) ($shop['owner_name'] ?? ''),
        (string) ($shop['phone']      ?? ''),
        (string) ($shop['board_id']   ?? ''),
        $syncedAt
    );

    return $action;
}

/**
 * Insert or update one mirrored subscription. Returns 'inserted' or 'updated'.
 *
 * price is bound as a string so a null stays null instead of being coerced to
 * 0.00, which would misreport a free or unpriced line as costing nothing.
 */
function udmUpsertSubscription($db, array $sub, string $syncedAt): string
{
    $existing = $db->query(
        "SELECT sub_id FROM l4u_ud_subscriptions WHERE sub_id = ?",
        (string) $sub['sub_id']
    )->fetchArray();
    $action = empty($existing) ? 'inserted' : 'updated';

    $price = $sub['price'];
    $price = ($price === null || $price === '') ? null : (string) $price;

    $db->query(
        "INSERT INTO l4u_ud_subscriptions
            (sub_id, shop_id, raw_name, product_group, price,
             currency, account_name, matched_by, synced_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            shop_id       = VALUES(shop_id),
            raw_name      = VALUES(raw_name),
            product_group = VALUES(product_group),
            price         = VALUES(price),
            currency      = VALUES(currency),
            account_name  = VALUES(account_name),
            matched_by    = VALUES(matched_by),
            synced_at     = VALUES(synced_at)",
        (string) $sub['sub_id'],
        (string) ($sub['shop_id']       ?? ''),
        (string) ($sub['raw_name']      ?? ''),
        (string) ($sub['product_group'] ?? ''),
        $price,
        (string) ($sub['currency']      ?? ''),
        (string) ($sub['account_name']  ?? ''),
        (string) ($sub['matched_by']    ?? 'none'),
        $syncedAt
    );

    return $action;
}

/**
 * Map of normalized shop name => shop_id for one country, for the fallback
 * match in udmResolveShopId.
 */
function udmLoadShopNameMap($db, string $country): array
{
    $rows = $db->query(
        "SELECT shop_id, shop_name FROM l4u_ud_shops WHERE country = ?",
        $country
    )->fetchAll();

    $map = [];
    foreach ($rows as $r) {
        $key = udmNormalizeShopName($r['shop_name']);
        if ($key !== '') $map[$key] = (string) $r['shop_id'];
    }
    return $map;
}

/**
 * Drop shops in this country that the current run did not refresh, so closed
 * projects disappear from the mirror instead of lingering forever.
 *
 * Scoped by country because a country whose Monday fetch failed is skipped
 * entirely; deleting globally would wipe the rows of every skipped country.
 */
function udmDeleteStaleShops($db, string $country, string $syncedAt): int
{
    $db->query(
        "DELETE FROM l4u_ud_shops WHERE country = ? AND synced_at < ?",
        $country, $syncedAt
    );
    return (int) $db->affectedRows();
}

/**
 * Drop subscriptions the current run did not refresh -- cancellations, and
 * rows whose status stopped containing "active".
 *
 * Not scoped by country: the subscriptions board is one global board, synced
 * in one pass, so anything older than this run's timestamp is genuinely gone.
 */
function udmDeleteStaleSubscriptions($db, string $syncedAt): int
{
    $db->query(
        "DELETE FROM l4u_ud_subscriptions WHERE synced_at < ?",
        $syncedAt
    );
    return (int) $db->affectedRows();
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_mirror_repo.php`
Expected: all `PASS:`, final line `Passed: 22  Failed: 0`, exit 0.

If `affectedRows()` returns an unexpected value, check `assets/db/db.php` — `affectedRows()` reads `$this->query->affected_rows` from the statement, so it must be called immediately after the `DELETE` with no intervening query.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/sync/mirrorRepo.php \
        modules/upgrade-downgrade/tests/test_mirror_repo.php
git commit -m "feat(upgrade-downgrade): add mirror write repository

Upserts report inserted vs updated by checking existence first: affected_rows
reports 0 for an update that changed nothing, which is the common case when a
sync re-runs over unchanged data.

Stale-row deletion is scoped by country for shops and global for subscriptions,
matching how each is fetched -- a country skipped after a failed fetch must keep
its rows.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 4: Monday client

**Files:**

- Create: `modules/upgrade-downgrade/sync/mondayClient.php`
- Create: `modules/upgrade-downgrade/tests/test_monday_client.php`

**Interfaces:**

- Consumes: `assets/config.php`.
- Produces:
  - `udmLoadConfig(): array` — the config array; throws `RuntimeException` when the file is missing or `monday_token` is empty.
  - `udmMondayRequest(string $token, string $gql, int $timeout = 90): array` — decoded response, or `['error' => '...']`.
  - `udmMondayRequestRetrying(string $token, string $gql, int $attempts = 3): array` — retries transient failures with a 0.5s backoff, matching `live.php`'s existing behaviour.
  - `UDM_PROJECT_BOARDS` — `array<string,int>` country code => board id.
  - `UDM_SUBSCRIPTIONS_BOARD` — `int`.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_monday_client.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../sync/mondayClient.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== board constants ==\n";
test("six project boards", count(UDM_PROJECT_BOARDS) === 6);
test("TH board matches live.php", UDM_PROJECT_BOARDS['TH'] === 1943203205);
test("AU board matches live.php", UDM_PROJECT_BOARDS['AU'] === 1943203246);
test("NZ board matches live.php", UDM_PROJECT_BOARDS['NZ'] === 1943203264);
test("CA board matches live.php", UDM_PROJECT_BOARDS['CA'] === 1943203287);
test("UK board matches live.php", UDM_PROJECT_BOARDS['UK'] === 1943203305);
test("US board matches live.php", UDM_PROJECT_BOARDS['US'] === 1940392927);
test("subscriptions board id", UDM_SUBSCRIPTIONS_BOARD === 1881439330);

echo "== udmLoadConfig ==\n";
$config = udmLoadConfig();
test("returns an array", is_array($config));
test("monday_token is present and non-empty",
     isset($config['monday_token']) && $config['monday_token'] !== '');
test("the token is not the committed placeholder", $config['monday_token'] !== 'YOUR_TOKEN_HERE');

echo "== udmMondayRequest: no token means an error, not a crash ==\n";
$res = udmMondayRequest('clearly-not-a-valid-token', '{ me { id } }', 15);
test("returns an array", is_array($res));
test("reports a problem rather than looking successful",
     isset($res['error']) || isset($res['errors']) || isset($res['error_message']));

echo "== udmMondayRequest: a real query works ==\n";
$res = udmMondayRequest($config['monday_token'], '{ me { id name } }', 30);
test("no transport error", !isset($res['error']));
test("returns data.me.id", !empty($res['data']['me']['id']));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_monday_client.php`
Expected: FAIL — `Failed opening required .../sync/mondayClient.php`.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/sync/mondayClient.php`:

```php
<?php
/**
 * Monday API access for the mirror sync.
 *
 * Knows nothing about our schema: it loads credentials and moves GraphQL in
 * and JSON out. The token is read from the gitignored assets/config.php and is
 * never hardcoded here.
 */

/** Country code => Monday project board id. */
const UDM_PROJECT_BOARDS = [
    'CA' => 1943203287,
    'AU' => 1943203246,
    'NZ' => 1943203264,
    'UK' => 1943203305,
    'US' => 1940392927,
    'TH' => 1943203205,
];

const UDM_SUBSCRIPTIONS_BOARD = 1881439330;

/**
 * Load the module config.
 *
 * Throws rather than returning a default: a sync that silently runs without a
 * token would look like "Monday returned nothing" and quietly empty the mirror.
 */
function udmLoadConfig(): array
{
    $file = __DIR__ . '/../assets/config.php';

    if (!is_file($file)) {
        throw new RuntimeException(
            "Missing config: {$file}. Copy assets/config.example.php to assets/config.php and set monday_token."
        );
    }

    $config = include $file;

    if (!is_array($config) || empty($config['monday_token'])) {
        throw new RuntimeException(
            "monday_token is not set in {$file}."
        );
    }

    return $config;
}

/**
 * One Monday GraphQL request. Returns the decoded body, or
 * ['error' => '...'] for a transport-level failure.
 */
function udmMondayRequest(string $token, string $gql, int $timeout = 90): array
{
    $ch = curl_init('https://api.monday.com/v2');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: ' . $token,
            'API-Version: 2024-01',
        ],
        CURLOPT_TIMEOUT        => $timeout,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err !== '') return ['error' => $err];

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : ['error' => 'Invalid JSON from Monday'];
}

/**
 * Retry transient Monday failures. Monday answers large nested queries with
 * subgraph/complexity errors under load, and a single bad page would otherwise
 * truncate a board mid-pagination -- the same reason live.php retries.
 *
 * Returns the last response, so the caller still sees the error after the final
 * attempt.
 */
function udmMondayRequestRetrying(string $token, string $gql, int $attempts = 3): array
{
    $res = ['error' => 'no attempt made'];

    for ($i = 0; $i < $attempts; $i++) {
        $res = udmMondayRequest($token, $gql);
        if (!isset($res['error']) && !isset($res['errors']) && isset($res['data'])) {
            return $res;
        }
        usleep(500000);
    }

    return $res;
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_monday_client.php`
Expected: all `PASS:`, final line `Passed: 15  Failed: 0`, exit 0.

This test makes two real Monday API calls. If it fails on the last two assertions, the token in `assets/config.php` is wrong or expired — check it before blaming the code.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/sync/mondayClient.php \
        modules/upgrade-downgrade/tests/test_monday_client.php
git commit -m "feat(upgrade-downgrade): add Monday client reading the token from config

The token lives in the gitignored assets/config.php rather than in source.
udmLoadConfig throws on a missing token instead of defaulting, because a sync
running tokenless looks exactly like Monday returning nothing and would empty
the mirror.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 5: Sync orchestration

**Files:**

- Create: `modules/upgrade-downgrade/sync/mondayMirror.php`
- Create: `modules/upgrade-downgrade/tests/test_monday_mirror.php`

**Interfaces:**

- Consumes: `udmLoadConfig`, `udmMondayRequestRetrying`, `UDM_PROJECT_BOARDS`, `UDM_SUBSCRIPTIONS_BOARD` (Task 4); `udmResolveShopId`, `udmNormalizeShopName`, `udmNormalizeEmail` (Task 2); all of `mirrorRepo.php` (Task 3).
- Produces:
  - `udmExtractProjectRows(array $response, string $country, int $boardId): array` — pure; one row per project item with keys `shop_id`, `country`, `board_id`, `shop_name`, `shop_type`, `contact_id`.
  - `udmExtractContactFields(array $contactItem): array` — pure; `['owner_name' => string, 'phone' => string, 'email' => string]`.
  - `udmExtractSubscriptionRows(array $response): array` — pure; active rows only, keys `sub_id`, `raw_name`, `product_group`, `price`, `currency`, `account_name`, `linked_shop_id`.
  - `udmSyncCountry($db, string $token, string $country, string $syncedAt): array` — summary for one country.
  - `udmSyncSubscriptions($db, string $token, string $syncedAt): array` — summary for the subscriptions board.
  - `udmRunSync($db, ?array $onlyCountries = null): array` — full run summary.

- [ ] **Step 1: Write the failing test for the pure extractors**

Create `modules/upgrade-downgrade/tests/test_monday_mirror.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== udmExtractProjectRows ==\n";

// Shape copied from a real live.php response: a project item carries many
// board_relation columns and only connect_boards0 points at Contacts.
$projectResponse = [
    'data' => ['boards' => [[
        'id' => '1943203205',
        'items_page' => [
            'cursor' => null,
            'items'  => [
                [
                    'id' => '1879900670',
                    'name' => 'Cher Thai Cuisine',
                    'column_values' => [
                        ['id' => 'color1', 'text' => 'Thai Restaurants & Takeaways', 'value' => ''],
                        ['id' => 'connect_boards0', 'text' => 'Somchai', 'value' => '', 'linked_item_ids' => ['555000111']],
                        ['id' => 'board_relation_other', 'text' => '', 'value' => '', 'linked_item_ids' => ['999']],
                    ],
                ],
                [
                    'id' => '1879899645',
                    'name' => 'Ivory Thai @ Zetland',
                    'column_values' => [
                        ['id' => 'color1', 'text' => '', 'value' => ''],
                        ['id' => 'connect_boards0', 'text' => '', 'value' => '', 'linked_item_ids' => []],
                    ],
                ],
            ],
        ],
    ]]],
];

$rows = udmExtractProjectRows($projectResponse, 'TH', 1943203205);
test("returns one row per item", count($rows) === 2);
test("shop_id is the item id", $rows[0]['shop_id'] === '1879900670');
test("shop_name is the item name", $rows[0]['shop_name'] === 'Cher Thai Cuisine');
test("shop_type comes from color1", $rows[0]['shop_type'] === 'Thai Restaurants & Takeaways');
test("country is the requested country", $rows[0]['country'] === 'TH');
test("board_id is the requested board", $rows[0]['board_id'] === '1943203205');
// Also proves the other board_relation_* columns are ignored: the fixture's
// second relation column carries '999', so a wrong column id would show here.
test("contact_id comes from connect_boards0", $rows[0]['contact_id'] === '555000111');
test("an unlinked item has an empty contact_id", $rows[1]['contact_id'] === '');
test("an empty color1 gives an empty shop_type", $rows[1]['shop_type'] === '');

$rows = udmExtractProjectRows(['data' => ['boards' => []]], 'TH', 1943203205);
test("an empty board gives no rows", $rows === []);
$rows = udmExtractProjectRows(['error' => 'boom'], 'TH', 1943203205);
test("an error response gives no rows", $rows === []);

echo "== udmExtractContactFields ==\n";
$contact = [
    'id' => '555000111',
    'name' => 'Fallback Name',
    'column_values' => [
        ['id' => 'text0', 'text' => 'Somchai'],
        ['id' => 'text7', 'text' => 'Jaidee'],
        ['id' => 'contact_phone', 'text' => '+66812345678'],
        ['id' => 'dup__of_mobile', 'text' => '+66899999999'],
        ['id' => 'contact_email', 'text' => 'Somchai@Shop.COM'],
    ],
];
$f = udmExtractContactFields($contact);
test("joins first and last name", $f['owner_name'] === 'Somchai Jaidee');
test("prefers contact_phone", $f['phone'] === '+66812345678');
test("normalizes the email", $f['email'] === 'somchai@shop.com');

$noPhone = $contact;
$noPhone['column_values'][2]['text'] = '';
$f = udmExtractContactFields($noPhone);
test("falls back to dup__of_mobile", $f['phone'] === '+66899999999');

// Unlike live.php, email is NEVER used as a phone substitute here: the email
// is the lookup key and must stay in its own field.
$noPhones = $contact;
$noPhones['column_values'][2]['text'] = '';
$noPhones['column_values'][3]['text'] = '';
$f = udmExtractContactFields($noPhones);
test("with no phone at all, phone is empty, not the email", $f['phone'] === '');
test("the email is still populated", $f['email'] === 'somchai@shop.com');

$nameless = ['id' => '1', 'name' => 'Fallback Name', 'column_values' => [
    ['id' => 'text0', 'text' => ''], ['id' => 'text7', 'text' => ''],
]];
$f = udmExtractContactFields($nameless);
test("falls back to the item name when both name parts are empty",
     $f['owner_name'] === 'Fallback Name');

echo "== udmExtractSubscriptionRows ==\n";
$subResponse = [
    'data' => ['boards' => [[
        'items_page' => [
            'cursor' => null,
            'items' => [
                [
                    'id' => '1886326536',
                    'name' => 'PO01M00 Pro Online Ordering System',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Active'],
                        ['id' => 'numbers', 'text' => '49'],
                        ['id' => 'status0', 'text' => 'aud'],
                        ['id' => 'text9',   'text' => 'Cher Thai Cuisine'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => ['1879900670']],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => 'Pro Online Ordering System'],
                    ],
                ],
                [
                    'id' => '1886326999',
                    'name' => 'Cancelled thing',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Cancelled'],
                        ['id' => 'numbers', 'text' => '10'],
                        ['id' => 'status0', 'text' => 'AUD'],
                        ['id' => 'text9',   'text' => 'Cher Thai Cuisine'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => ['1879900670']],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => 'Old Product'],
                    ],
                ],
                [
                    'id' => '1886327000',
                    'name' => 'Unlinked active thing',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Active'],
                        ['id' => 'numbers', 'text' => ''],
                        ['id' => 'status0', 'text' => ''],
                        ['id' => 'text9',   'text' => 'Ivory Thai @ Zetland'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => []],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => ''],
                    ],
                ],
            ],
        ],
    ]]],
];

$subs = udmExtractSubscriptionRows($subResponse);
test("drops non-active rows", count($subs) === 2);
test("sub_id is the item id", $subs[0]['sub_id'] === '1886326536');
test("raw_name is the item name", $subs[0]['raw_name'] === 'PO01M00 Pro Online Ordering System');
test("product_group comes from the mirror column",
     $subs[0]['product_group'] === 'Pro Online Ordering System');
test("price comes from numbers", $subs[0]['price'] === '49');
test("currency is uppercased", $subs[0]['currency'] === 'AUD');
test("account_name comes from text9", $subs[0]['account_name'] === 'Cher Thai Cuisine');
test("linked_shop_id comes from connect_boards", $subs[0]['linked_shop_id'] === '1879900670');
test("an empty numbers field becomes a null price", $subs[1]['price'] === null);
test("an unlinked row has an empty linked_shop_id", $subs[1]['linked_shop_id'] === '');
test("product_group falls back to the item name when the mirror is empty",
     $subs[1]['product_group'] === 'Unlinked active thing');

// The existing getSubscriptions.php uses a case-insensitive "contains active"
// test, so these must all count as active.
foreach (['Active', 'active', 'ACTIVE', 'Active - paid', 'Pending Active'] as $status) {
    $r = $subResponse;
    $r['data']['boards'][0]['items_page']['items'] = [[
        'id' => 'X', 'name' => 'n', 'column_values' => [['id' => 'status', 'text' => $status]],
    ]];
    test("status '{$status}' counts as active", count(udmExtractSubscriptionRows($r)) === 1);
}
foreach (['Cancelled', 'Paused', ''] as $status) {
    $r = $subResponse;
    $r['data']['boards'][0]['items_page']['items'] = [[
        'id' => 'X', 'name' => 'n', 'column_values' => [['id' => 'status', 'text' => $status]],
    ]];
    test("status '{$status}' does not count as active", count(udmExtractSubscriptionRows($r)) === 0);
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_monday_mirror.php`
Expected: FAIL — `Failed opening required .../sync/mondayMirror.php`.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/sync/mondayMirror.php`:

```php
<?php
/**
 * Mirror orchestration: fetch from Monday, match, write.
 *
 * The extractors at the top are pure -- they take a decoded response and
 * return rows -- so the response shapes can be tested against fixtures
 * without touching the network.
 */

const UDM_PAGE_LIMIT     = 100;  // keeps nested column queries under Monday's complexity cap
const UDM_MAX_PAGES      = 50;   // 100 * 50 = 5000. The binding board is
                                 // Active Subscriptions, measured at 2865
                                 // items; the per-country project boards are
                                 // far smaller (AU, the largest, is ~1300).
                                 // The guard below turns an overflow into an
                                 // error rather than a silent truncation, so
                                 // raising this is about headroom, not safety.
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
```

- [ ] **Step 4: Run the test to verify the extractors pass**

Run: `php modules/upgrade-downgrade/tests/test_monday_mirror.php`
Expected: all `PASS:`, every assertion passing, exit 0. (Count the assertions in
the test above rather than trusting a number here; two of them sit inside
`foreach` loops.)

- [ ] **Step 4b: Add the orchestration tests**

The tests above cover only the pure extractors. The error paths through
`udmFetchAllPages` and `udmSyncCountry` are where a mistake silently corrupts or
empties the mirror, so they get their own tests. Both are driven by stubs - no
network, no database.

First, so the stubs can take effect, guard the five repo functions in
`sync/mirrorRepo.php` and `udmMondayRequestRetrying` in `sync/mondayClient.php`
with `if (!function_exists('<name>')) { ... }`, leaving their bodies unchanged.

Create `modules/upgrade-downgrade/tests/test_mirror_orchestration.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

// ---- Stubs ----------------------------------------------------------------
// udmMondayRequestRetrying and the repo writers are replaced with recorders, so
// these tests exercise the orchestration's decisions without a network call or
// a database write. Declared BEFORE the real files load; the production files
// guard with function_exists, so these definitions win.

$GLOBALS['stub_responses'] = [];
$GLOBALS['stub_calls']     = [];
$GLOBALS['stub_writes']    = [];

function udmMondayRequestRetrying(string $token, string $gql, int $attempts = 3): array
{
    $GLOBALS['stub_calls'][] = $gql;
    $next = array_shift($GLOBALS['stub_responses']);
    return $next ?? ['error' => 'stub exhausted'];
}

function udmUpsertShop($db, array $shop, string $syncedAt): string
{
    $GLOBALS['stub_writes'][] = ['upsert_shop', $shop['shop_id'], $shop['email']];
    return 'inserted';
}

function udmUpsertSubscription($db, array $sub, string $syncedAt): string
{
    $GLOBALS['stub_writes'][] = ['upsert_sub', $sub['sub_id'], $sub['shop_id']];
    return 'inserted';
}

function udmDeleteStaleShops($db, string $country, string $syncedAt): int
{
    $GLOBALS['stub_writes'][] = ['delete_shops', $country];
    return 0;
}

function udmDeleteStaleSubscriptions($db, string $syncedAt): int
{
    $GLOBALS['stub_writes'][] = ['delete_subs'];
    return 0;
}

function udmLoadShopNameMap($db, string $country): array { return []; }

require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

function resetStubs(array $responses) {
    $GLOBALS['stub_responses'] = $responses;
    $GLOBALS['stub_calls']     = [];
    $GLOBALS['stub_writes']    = [];
}

/** One page of project items; a non-null cursor forces another page. */
function projectPage(array $ids, $cursor) {
    $items = [];
    foreach ($ids as $id) {
        $items[] = ['id' => (string) $id, 'name' => "Shop {$id}", 'column_values' => [
            ['id' => 'color1', 'text' => 'Thai'],
            ['id' => 'connect_boards0', 'text' => '', 'linked_item_ids' => ["c{$id}"]],
        ]];
    }
    return ['data' => ['boards' => [['id' => '1', 'items_page' => [
        'cursor' => $cursor, 'items' => $items,
    ]]]]];
}

function contactBatch(array $ids) {
    $items = [];
    foreach ($ids as $id) {
        $items[] = ['id' => (string) $id, 'name' => 'N', 'column_values' => [
            ['id' => 'text0', 'text' => 'First'],
            ['id' => 'contact_email', 'text' => "{$id}@shop.com"],
        ]];
    }
    return ['data' => ['items' => $items]];
}

echo "== udmFetchAllPages: a failed page discards partial rows ==\n";
resetStubs([projectPage([1, 2], 'CURSOR1'), ['error' => 'curl timeout']]);
$r = udmFetchAllPages('tok',
    function ($c) { return udmProjectPageQuery(1943203205, $c); },
    function ($res) { return udmExtractProjectRows($res, 'TH', 1943203205); });
test("reports the error", $r['error'] === 'curl timeout');
test("returns NO rows, not the first page's two", $r['rows'] === []);

echo "== udmFetchAllPages: an error_message body is not success ==\n";
// Monday answers rate limits with error_message and no data. An error-key check
// would read this as success; the isset($res['data']) test catches it.
resetStubs([['error_message' => 'Rate limit exceeded', 'error_code' => 'RateLimit']]);
$r = udmFetchAllPages('tok',
    function ($c) { return udmProjectPageQuery(1943203205, $c); },
    function ($res) { return udmExtractProjectRows($res, 'TH', 1943203205); });
test("treated as an error", $r['error'] !== null);
test("returns no rows", $r['rows'] === []);

echo "== udmFetchAllPages: the page cap does not truncate silently ==\n";
// Every page carries a cursor, so the loop exhausts UDM_MAX_PAGES with more
// pending. Returning the partial set would feed a truncated board to the
// stale-row deletion.
$always = [];
for ($i = 0; $i < UDM_MAX_PAGES + 2; $i++) { $always[] = projectPage([$i], 'MORE'); }
resetStubs($always);
$r = udmFetchAllPages('tok',
    function ($c) { return udmProjectPageQuery(1943203205, $c); },
    function ($res) { return udmExtractProjectRows($res, 'TH', 1943203205); });
test("reports an error rather than success", $r['error'] !== null);
test("the error names the page cap", strpos($r['error'], 'page cap') !== false);
test("returns no rows", $r['rows'] === []);
test("stopped at the cap", count($GLOBALS['stub_calls']) === UDM_MAX_PAGES);

echo "== udmSyncCountry: a failed project fetch writes nothing ==\n";
resetStubs([['error' => 'boom']]);
$s = udmSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports the error", $s['error'] !== null);
test("no writes at all", $GLOBALS['stub_writes'] === []);

echo "== udmSyncCountry: a zero-item board writes nothing ==\n";
resetStubs([projectPage([], null)]);
$s = udmSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports zero items as an error", $s['error'] === 'board returned zero items');
test("no writes at all", $GLOBALS['stub_writes'] === []);

echo "== udmSyncCountry: a failed CONTACT batch must not erase emails ==\n";
// The upsert sets email = VALUES(email) unconditionally, so writing a shop with
// an empty email ERASES the mirrored address -- and the email is the lookup key.
resetStubs([
    projectPage([1, 2], null),                                      // projects fine
    ['errors' => [['message' => 'complexity budget exhausted']]],   // contacts fail
]);
$s = udmSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports an error instead of partial success", $s['error'] !== null);
test("the error names the contact batch", strpos($s['error'], 'contact batch') !== false);
// Inspects the email actually passed to the upsert, rather than merely
// observing that nothing was written: the name has to be true for the reason it
// claims, and this also catches a write that slips through with a blank address.
test("no shop was written with a blank email",
     array_filter($GLOBALS['stub_writes'],
         function ($w) { return $w[0] === 'upsert_shop' && $w[2] === ''; }) === []);
test("nothing was written at all", $GLOBALS['stub_writes'] === []);

echo "== udmSyncCountry: an EMPTY contacts result must not erase emails ==\n";
// Success-shaped body, empty items array. Without a guard this writes every
// shop with a blank email and runs the stale deletion, reporting no error.
resetStubs([projectPage([1, 2], null), ['data' => ['items' => []]]]);
$s = udmSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports an error", $s['error'] !== null);
test("the error names the empty contact batch",
     strpos($s['error'], 'no items') !== false);
test("no shop was written with a blank email",
     array_filter($GLOBALS['stub_writes'],
         function ($w) { return $w[0] === 'upsert_shop' && $w[2] === ''; }) === []);
test("stale deletion did NOT run",
     !in_array(['delete_shops', 'TH'], $GLOBALS['stub_writes'], true));

echo "== udmSyncCountry: the happy path still writes and deletes ==\n";
resetStubs([projectPage([1, 2], null), contactBatch(['c1', 'c2'])]);
$s = udmSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("no error", $s['error'] === null);
test("both shops written", count(array_filter($GLOBALS['stub_writes'],
     function ($w) { return $w[0] === 'upsert_shop'; })) === 2);
test("emails came through", $GLOBALS['stub_writes'][0][2] === 'c1@shop.com');
test("counts the shops with an email", $s['withEmail'] === 2);
test("stale deletion ran", in_array(['delete_shops', 'TH'], $GLOBALS['stub_writes'], true));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 4c: Run the orchestration tests**

Run: `php modules/upgrade-downgrade/tests/test_mirror_orchestration.php`
Expected: every assertion passes, exit 0. No network call is made and no
database row is touched - if the run is slow or hangs, a stub is not taking
effect and a real Monday request is going out.

Then re-run the earlier suites to confirm the `function_exists` guards changed
nothing:

```bash
for t in modules/upgrade-downgrade/tests/test_*.php; do echo "== $t"; php "$t" | tail -1; done
```

Expected: every file ends `Failed: 0`.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/sync/mondayMirror.php \
        modules/upgrade-downgrade/tests/test_monday_mirror.php
git commit -m "feat(upgrade-downgrade): add Monday mirror sync orchestration

Response extraction is pure and fixture-tested; fetching, matching and writing
sit on top of it.

A country whose fetch fails or returns zero items is skipped with its rows left
intact. A truncated or empty board is indistinguishable from every project
having closed, and acting on it would delete live shops.

Unlike live.php, the contact email is kept in its own field rather than used as
a substitute for a missing phone, because here it is the lookup key.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 6: Cron entry point

**Files:**

- Create: `modules/upgrade-downgrade/sync/syncMondayMirror.php`

**Interfaces:**

- Consumes: `udmRunSync` (Task 5), `$db`.
- Produces: a CLI/HTTP entry point. Exit 0 when every country synced; exit 1 when any country or the subscriptions pass errored.

- [ ] **Step 1: Write the entry point**

Create `modules/upgrade-downgrade/sync/syncMondayMirror.php`. The CLI/token guard follows `modules/QRCodeDynamicLink/public/cron.php`:

```php
<?php
/**
 * Mirror Monday into l4u_ud_shops / l4u_ud_subscriptions.
 *
 * Intended to run twice a day:
 *
 *   php modules/upgrade-downgrade/sync/syncMondayMirror.php
 *
 * Optionally limited to some countries:
 *
 *   php modules/upgrade-downgrade/sync/syncMondayMirror.php TH AU
 *
 * Nothing on the customer path depends on this script succeeding: the form
 * falls back to manual entry when the lookup finds nothing, so a stopped cron
 * degrades convenience, never correctness.
 */

date_default_timezone_set("Asia/Bangkok");

// CLI only unless an explicit token is configured -- this must not be
// triggerable by anyone who can reach the web server.
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    $configFile = __DIR__ . '/../assets/config.php';
    $config     = is_file($configFile) ? include $configFile : [];
    $expected   = isset($config['sync_token']) ? $config['sync_token'] : null;
    $given      = isset($_GET['token']) ? $_GET['token'] : '';

    if (empty($expected) || !hash_equals($expected, $given)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Forbidden\n";
        exit(1);
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/mirrorMatch.php';
require_once __DIR__ . '/mirrorRepo.php';
require_once __DIR__ . '/mondayClient.php';
require_once __DIR__ . '/mondayMirror.php';

$started = microtime(true);

// Country filter: CLI arguments, or ?countries=TH,AU over HTTP.
$only = null;
if ($isCli && $argc > 1) {
    $only = array_map('strtoupper', array_slice($argv, 1));
} elseif (!$isCli && !empty($_GET['countries'])) {
    $only = array_map('strtoupper', array_filter(explode(',', $_GET['countries'])));
}

$lines    = [];
$hadError = false;

try {
    $summary = udmRunSync($db, $only);
} catch (Throwable $e) {
    fwrite($isCli ? STDERR : STDOUT, "FATAL: " . $e->getMessage() . "\n");
    exit(1);
}

foreach ($summary['countries'] as $c) {
    if ($c['error'] !== null) {
        $hadError = true;
        $lines[] = "  {$c['country']}: ERROR {$c['error']} (rows left untouched)";
        continue;
    }
    $lines[] = sprintf(
        "  %s: %d shops (%d new, %d updated, %d removed), %d with an email",
        $c['country'], $c['shops'], $c['inserted'], $c['updated'],
        $c['deleted'], $c['withEmail']
    );
}

$s = $summary['subscriptions'];
if ($s['error'] !== null) {
    $hadError = true;
    $lines[] = "  subscriptions: ERROR {$s['error']} (rows left untouched)";
} else {
    $lines[] = sprintf(
        "  subscriptions: %d active (%d by relation, %d by name, %d unmatched), %d removed",
        $s['subscriptions'], $s['matched']['relation'],
        $s['matched']['name'], $s['matched']['none'], $s['deleted']
    );
    // A rising name/none count means Monday's relation column is being left
    // unset and somebody should look at the board.
    if ($s['matched']['name'] > 0 || $s['matched']['none'] > 0) {
        $lines[] = "  NOTE: some subscriptions did not resolve via the Monday relation column";
    }
}

// microtime(true) is already in seconds -- do not divide by 1000.
$elapsed = round(microtime(true) - $started, 1);
$stamp   = date('Y-m-d H:i:s');
$status  = $hadError ? 'with errors' : 'ok';

$output = "[{$stamp} +07] ud mirror sync {$status} ({$elapsed}s)\n"
        . implode("\n", $lines) . "\n";
echo $output;

// Keep a rolling log so an overnight failure is still visible in the morning.
$logDir = __DIR__ . '/../assets/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0700, true);
}
@file_put_contents($logDir . '/mirror-sync.log', $output, FILE_APPEND | LOCK_EX);

exit($hadError ? 1 : 0);
```

- [ ] **Step 2: Check the file parses**

Run: `php -l modules/upgrade-downgrade/sync/syncMondayMirror.php`
Expected: `No syntax errors detected`.

- [ ] **Step 3: Run a single-country sync for real**

Thailand is the smallest board, so it is the cheapest first real run.

Run: `php modules/upgrade-downgrade/sync/syncMondayMirror.php TH`

Expected: output shaped like

```text
[2026-10-05 14:22:01 +07] ud mirror sync ok (18.4s)
  TH: 214 shops (214 new, 0 updated, 0 removed), 198 with an email
  subscriptions: 1532 active (1532 by relation, 0 by name, 0 unmatched), 0 removed
```

Exit 0. The shop count must be non-zero and `with an email` must be a large
fraction of it — if `with an email` is 0, the contact batch query is not
returning `contact_email` and the lookup will never match anything.

- [ ] **Step 4: Verify the mirror has usable rows**

Run:

```bash
php -r 'require "modules/upgrade-downgrade/assets/db/db.php"; require "modules/upgrade-downgrade/assets/db/initDB.php"; $r=$db->query("SELECT country, COUNT(*) c, SUM(email<>\"\") e FROM l4u_ud_shops GROUP BY country")->fetchAll(); foreach($r as $x) echo "{$x["country"]}: {$x["c"]} shops, {$x["e"]} with email\n"; $s=$db->query("SELECT matched_by, COUNT(*) c FROM l4u_ud_subscriptions GROUP BY matched_by")->fetchAll(); foreach($s as $x) echo "{$x["matched_by"]}: {$x["c"]}\n";'
```

Expected: a `TH` row with a non-zero email count, and a `relation` count that
dominates `name` and `none`.

- [ ] **Step 5: Run the full sync**

Run: `php modules/upgrade-downgrade/sync/syncMondayMirror.php`
Expected: a line per country, all without `ERROR`, exit 0. This takes several
minutes — AU alone is ~1300 projects over 13 pages.

- [ ] **Step 6: Commit**

```bash
git add modules/upgrade-downgrade/sync/syncMondayMirror.php
git commit -m "feat(upgrade-downgrade): add mirror sync cron entry point

CLI-only unless a sync_token is configured, following the QRCodeDynamicLink
cron pattern, and writes a rolling log so an overnight failure is visible the
next morning.

The summary reports the matched_by breakdown, so the Monday relation column
going unset shows up as a number instead of silently degrading the join.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 7: Lookup query

**Files:**

- Create: `modules/upgrade-downgrade/models/shopLookup.php`
- Create: `modules/upgrade-downgrade/tests/test_shop_lookup.php`

**Interfaces:**

- Consumes: `$db`, `udmNormalizeEmail` (Task 2).
- Produces: `udmLookupShopByEmail($db, string $country, string $email): array` — `['found' => false]`, or `['found' => true, 'shop' => [...], 'subscriptions' => [...], 'syncedAt' => string, 'stale' => bool, 'multiple' => bool]`.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_shop_lookup.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';
require_once __DIR__ . '/../models/shopLookup.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

function cleanup($db) {
    $db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
    $db->query("DELETE FROM l4u_ud_shops WHERE country IN ('ZZ','ZY')");
}
cleanup($db);

$fresh = date('Y-m-d H:i:s');
$old   = date('Y-m-d H:i:s', time() - 60 * 60 * 72);  // 72h ago, past the 48h threshold

// Shop with three subscriptions, matching the real pattern we sampled.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_A', 'country' => 'ZZ', 'email' => 'owner@testshop.com',
    'shop_name' => 'Test Shop A', 'shop_type' => 'Thai Restaurants',
    'owner_name' => 'Somchai Test', 'phone' => '+66812345678',
    'board_id' => '1943203205',
], $fresh);

foreach ([
    ['TEST_SUB_1', 'Yelp Ad Spend',             '300.00', 'USD'],
    ['TEST_SUB_2', 'Yelp Enhanced Profile',     '120.00', 'USD'],
    ['TEST_SUB_3', 'Local Starter Bundle',     '3099.60', 'USD'],
] as [$id, $group, $price, $cur]) {
    udmUpsertSubscription($db, [
        'sub_id' => $id, 'shop_id' => 'TEST_SHOP_A',
        'raw_name' => "USD - {$price} SKU {$group}", 'product_group' => $group,
        'price' => $price, 'currency' => $cur,
        'account_name' => 'Test Shop A', 'matched_by' => 'relation',
    ], $fresh);
}

// Shop with no subscriptions at all.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_NOSUB', 'country' => 'ZZ', 'email' => 'nosub@testshop.com',
    'shop_name' => 'No Subs Shop', 'shop_type' => '', 'owner_name' => 'Nobody',
    'phone' => '', 'board_id' => '1943203205',
], $fresh);

// Same email in a different country, to prove country scoping.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_OTHERCOUNTRY', 'country' => 'ZY', 'email' => 'owner@testshop.com',
    'shop_name' => 'Wrong Country Shop', 'shop_type' => '', 'owner_name' => 'Other',
    'phone' => '', 'board_id' => '1943203246',
], $fresh);

echo "== a match ==\n";
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
test("found", $r['found'] === true);
test("returns the right shop", $r['shop']['shopId'] === 'TEST_SHOP_A');
test("returns the shop name", $r['shop']['shopName'] === 'Test Shop A');
test("returns the owner name", $r['shop']['ownerName'] === 'Somchai Test');
test("returns the phone", $r['shop']['phone'] === '+66812345678');
test("returns the board id", $r['shop']['boardId'] === '1943203205');
test("does not echo the email back", !isset($r['shop']['email']));
test("returns all three subscriptions", count($r['subscriptions']) === 3);
test("not stale", $r['stale'] === false);
test("not multiple", $r['multiple'] === false);
test("reports syncedAt", $r['syncedAt'] === $fresh);

$names = array_column($r['subscriptions'], 'name');
test("subscription name is the product group", in_array('Yelp Ad Spend', $names, true));
test("subscriptions carry the raw name too",
     isset($r['subscriptions'][0]['rawName']) && $r['subscriptions'][0]['rawName'] !== '');

// The customer-facing list shows product names only. price and currency stay in
// the mirror for internal reporting but must not reach the form.
test("does not return a price", !isset($r['subscriptions'][0]['price']));
test("does not return a currency", !isset($r['subscriptions'][0]['currency']));

echo "== the same product held several times collapses to one row ==\n";
// A real shop was found holding ten separate "Social Media Marketing Bundle"
// rows. Listing each would look like a broken form, so the lookup groups by
// product name.
foreach (['TEST_SUB_DUP_A', 'TEST_SUB_DUP_B', 'TEST_SUB_DUP_C'] as $dupId) {
    udmUpsertSubscription($db, [
        'sub_id' => $dupId, 'shop_id' => 'TEST_SHOP_A',
        'raw_name' => 'USD - 297 SMMB Social Media Marketing Bundle',
        'product_group' => 'Social Media Marketing Bundle',
        'price' => '297.00', 'currency' => 'USD',
        'account_name' => 'Test Shop A', 'matched_by' => 'relation',
    ], $fresh);
}
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
$names = array_column($r['subscriptions'], 'name');
test("three duplicates appear once", count(array_keys($names, 'Social Media Marketing Bundle')) === 1);
test("the other products are still listed", count($r['subscriptions']) === 4);
test("every returned name is distinct", count($names) === count(array_unique($names)));
$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_SUB\_DUP%'");

echo "== normalization ==\n";
test("uppercase email matches",
     udmLookupShopByEmail($db, 'ZZ', 'OWNER@TESTSHOP.COM')['found'] === true);
test("padded email matches",
     udmLookupShopByEmail($db, 'ZZ', '  owner@testshop.com  ')['found'] === true);
test("lowercase country code matches",
     udmLookupShopByEmail($db, 'zz', 'owner@testshop.com')['found'] === true);

echo "== country scoping ==\n";
$r = udmLookupShopByEmail($db, 'ZY', 'owner@testshop.com');
test("the same email in another country finds that country's shop",
     $r['found'] === true && $r['shop']['shopId'] === 'TEST_SHOP_OTHERCOUNTRY');
test("a country with no such email finds nothing",
     udmLookupShopByEmail($db, 'TH', 'owner@testshop.com')['found'] === false);

echo "== no match ==\n";
test("unknown email", udmLookupShopByEmail($db, 'ZZ', 'nobody@nowhere.com')['found'] === false);
test("empty email", udmLookupShopByEmail($db, 'ZZ', '')['found'] === false);
test("empty country", udmLookupShopByEmail($db, '', 'owner@testshop.com')['found'] === false);

echo "== shop with no subscriptions ==\n";
$r = udmLookupShopByEmail($db, 'ZZ', 'nosub@testshop.com');
test("the shop is still found", $r['found'] === true);
test("subscriptions is an empty array, not a row of nulls", $r['subscriptions'] === []);

echo "== duplicate email in one country ==\n";
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_DUP', 'country' => 'ZZ', 'email' => 'owner@testshop.com',
    'shop_name' => 'Aaa First Alphabetically', 'shop_type' => '',
    'owner_name' => 'Dup', 'phone' => '', 'board_id' => '1943203205',
], $fresh);
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
test("flags multiple", $r['multiple'] === true);
test("returns the first shop by name", $r['shop']['shopId'] === 'TEST_SHOP_DUP');
$db->query("DELETE FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_DUP');

echo "== staleness ==\n";
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_STALE', 'country' => 'ZZ', 'email' => 'stale@testshop.com',
    'shop_name' => 'Stale Shop', 'shop_type' => '', 'owner_name' => 'Old',
    'phone' => '', 'board_id' => '1943203205',
], $old);
$r = udmLookupShopByEmail($db, 'ZZ', 'stale@testshop.com');
test("still returns the data", $r['found'] === true);
test("flags it stale", $r['stale'] === true);

echo "== injection attempt is bound, not interpreted ==\n";
$r = udmLookupShopByEmail($db, 'ZZ', "' OR 1=1 --");
test("an injection string finds nothing", $r['found'] === false);
test("the shops table is intact",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE country = 'ZZ'")
              ->fetchArray()['c'] === 3);

cleanup($db);
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_shop_lookup.php`
Expected: FAIL — `Failed opening required .../models/shopLookup.php`.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/models/shopLookup.php`:

```php
<?php
/**
 * The customer-mode lookup: one shop and its products, by country and email.
 *
 * Read-only, and the only place that knows the mirror's schema. Everything
 * else -- the endpoint, the form -- deals in the array this returns.
 */

/** Past this age the mirror is reported stale but still served. */
const UDM_STALE_AFTER_HOURS = 48;

/**
 * Find a mirrored shop by country and email.
 *
 * The email is normalized the same way the sync normalizes it before storing,
 * so idx_country_email is used without a LOWER() that would defeat it.
 *
 * @return array{found: bool, shop?: array, subscriptions?: array,
 *               syncedAt?: string, stale?: bool, multiple?: bool}
 */
function udmLookupShopByEmail($db, string $country, string $email): array
{
    $country = strtoupper(trim($country));
    $email   = udmNormalizeEmail($email);

    if ($country === '' || $email === '') {
        return ['found' => false];
    }

    // Ordered by shop_name so a duplicated email returns the same shop every
    // time rather than whichever row the engine happens to hand back first.
    $shops = $db->query(
        "SELECT shop_id, shop_name, shop_type, owner_name, phone, board_id, synced_at
         FROM l4u_ud_shops
         WHERE country = ? AND email = ?
         ORDER BY shop_name ASC",
        $country, $email
    )->fetchAll();

    if (empty($shops)) {
        return ['found' => false];
    }

    $shop = $shops[0];

    // One row per distinct product name, not per Monday subscription row. A shop
    // can carry the same product many times over -- one sampled shop holds ten
    // separate "Social Media Marketing Bundle" rows -- and listing each would
    // look like a broken form. MIN(sub_id) just gives the group a stable id.
    //
    // price and currency are deliberately NOT selected: the customer-facing form
    // shows product names only. The columns stay populated in the mirror for
    // internal reporting.
    $subs = $db->query(
        "SELECT MIN(sub_id) AS sub_id, product_group, MIN(raw_name) AS raw_name
         FROM l4u_ud_subscriptions
         WHERE shop_id = ?
         GROUP BY product_group
         ORDER BY product_group ASC",
        (string) $shop['shop_id']
    )->fetchAll();

    $subscriptions = [];
    foreach ($subs as $s) {
        $subscriptions[] = [
            'id'      => (string) $s['sub_id'],
            // product_group is the customer-facing name; rawName carries the SKU.
            'name'    => (string) $s['product_group'],
            'rawName' => (string) $s['raw_name'],
        ];
    }

    $syncedAt = (string) $shop['synced_at'];
    $stale    = (strtotime($syncedAt) < time() - UDM_STALE_AFTER_HOURS * 3600);

    return [
        'found' => true,
        'shop'  => [
            'shopId'    => (string) $shop['shop_id'],
            'shopName'  => (string) $shop['shop_name'],
            'shopType'  => (string) $shop['shop_type'],
            'ownerName' => (string) $shop['owner_name'],
            'phone'     => (string) $shop['phone'],
            'boardId'   => (string) $shop['board_id'],
            // The email is deliberately not echoed back: the caller already
            // has it, and not returning it keeps this response from being a
            // confirmation oracle for an address someone merely guessed.
        ],
        'subscriptions' => $subscriptions,
        'syncedAt'      => $syncedAt,
        'stale'         => $stale,
        'multiple'      => count($shops) > 1,
    ];
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_shop_lookup.php`
Expected: all `PASS:`, final line `Passed: 29  Failed: 0`, exit 0.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/models/shopLookup.php \
        modules/upgrade-downgrade/tests/test_shop_lookup.php
git commit -m "feat(upgrade-downgrade): add shop lookup by country and email

One indexed query plus the subscription join. The email is normalized the same
way the sync stores it, so the composite index is used directly.

The response does not echo the email back, keeping it from confirming an
address that was merely guessed, and reports staleness so a stopped sync is
visible rather than silently serving old products.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 8: Lookup endpoint

**Files:**

- Create: `modules/upgrade-downgrade/assets/API/lookupByEmail.php`
- Create: `modules/upgrade-downgrade/tests/test_lookup_endpoint.php`

**Interfaces:**

- Consumes: `udmLookupShopByEmail` (Task 7).
- Produces: `GET assets/API/lookupByEmail.php?country=TH&email=a@b.com` returning the lookup JSON. Shape matches Task 7, plus `{"found": false, "reason": "..."}` for rejected input.

- [ ] **Step 1: Write the failing test**

The endpoint is exercised over HTTP via the local XAMPP server, because
argument handling and header behaviour are the point.

Create `modules/upgrade-downgrade/tests/test_lookup_endpoint.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

const ENDPOINT = 'http://localhost/masterPanel/modules/upgrade-downgrade/assets/API/lookupByEmail.php';

/** @return array{status:int, json:?array, body:string} */
function call(array $params): array
{
    $url = ENDPOINT . '?' . http_build_query($params);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode((string) $body, true), 'body' => (string) $body];
}

// Fixture
$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZZ'");
$now = date('Y-m-d H:i:s');
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_EP', 'country' => 'ZZ', 'email' => 'endpoint@testshop.com',
    'shop_name' => 'Endpoint Shop', 'shop_type' => 'Thai', 'owner_name' => 'Ep Owner',
    'phone' => '+66800000000', 'board_id' => '1943203205',
], $now);
udmUpsertSubscription($db, [
    'sub_id' => 'TEST_SUB_EP', 'shop_id' => 'TEST_SHOP_EP',
    'raw_name' => 'THB - 4900 LSS01M00 Local Starter Solo', 'product_group' => 'Local Starter Solo',
    'price' => '4900.00', 'currency' => 'THB', 'account_name' => 'Endpoint Shop',
    'matched_by' => 'relation',
], $now);

echo "== reachability ==\n";
$r = call(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']);
if ($r['status'] === 0) {
    echo "  FAIL: cannot reach {$r['body']} -- is Apache running and is the project at /masterPanel?\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
}
test("responds 200", $r['status'] === 200);
test("returns JSON", is_array($r['json']));

echo "== a match ==\n";
test("found", ($r['json']['found'] ?? null) === true);
test("returns the shop name", ($r['json']['shop']['shopName'] ?? '') === 'Endpoint Shop');
test("returns one subscription", count($r['json']['subscriptions'] ?? []) === 1);
test("subscription name is the product group",
     ($r['json']['subscriptions'][0]['name'] ?? '') === 'Local Starter Solo');
test("no price reaches the client",
     !isset($r['json']['subscriptions'][0]['price'])
     && !isset($r['json']['subscriptions'][0]['currency']));
test("does not echo the email back", !isset($r['json']['shop']['email']));

echo "== no match ==\n";
$r = call(['country' => 'ZZ', 'email' => 'nobody@nowhere.com']);
test("responds 200 for a miss, not 404", $r['status'] === 200);
test("found is false", ($r['json']['found'] ?? null) === false);

echo "== rejected input ==\n";
$r = call(['country' => 'ZZ']);
test("a missing email responds 200", $r['status'] === 200);
test("a missing email reports found false", ($r['json']['found'] ?? null) === false);
test("a missing email gives a reason", !empty($r['json']['reason']));

$r = call(['email' => 'endpoint@testshop.com']);
test("a missing country reports found false", ($r['json']['found'] ?? null) === false);

$r = call(['country' => 'ZZ', 'email' => 'not-an-email']);
test("a malformed email reports found false", ($r['json']['found'] ?? null) === false);
test("a malformed email gives a reason", !empty($r['json']['reason']));

$r = call(['country' => 'TOOLONG', 'email' => 'endpoint@testshop.com']);
test("an over-long country reports found false", ($r['json']['found'] ?? null) === false);

echo "== injection ==\n";
$r = call(['country' => 'ZZ', 'email' => "' OR 1=1 --"]);
test("an injection string reports found false", ($r['json']['found'] ?? null) === false);
test("the fixture row survives",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_EP')
              ->fetchArray()['c'] === 1);

echo "== method ==\n";
$ch = curl_init(ENDPOINT . '?' . http_build_query(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']));
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => '', CURLOPT_TIMEOUT => 15]);
$body = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
test("POST is rejected with 405", $status === 405);

$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZZ'");
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_lookup_endpoint.php`
Expected: FAIL — the reachability check fails, or a 404 body, because the
endpoint does not exist yet.

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/assets/API/lookupByEmail.php`:

```php
<?php
/**
 * Customer-mode shop lookup: GET ?country=TH&email=owner@shop.com
 *
 * Read-only. Validates input, calls the lookup, encodes JSON. No business
 * logic lives here.
 *
 * SECURITY: this endpoint is unauthenticated -- it sits behind a public
 * customer-facing form -- and it returns an owner name and phone number to
 * anyone who supplies a matching email. That is an email enumeration surface.
 * It is narrowed deliberately: only the fields the form fills are returned,
 * the email is never echoed back, a miss is indistinguishable from malformed
 * input in shape, and every query is logged with its outcome.
 */

date_default_timezone_set("Asia/Bangkok");

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// No Access-Control-Allow-Origin: this is called same-origin from the form,
// unlike the existing read-only board caches which are shared more widely.

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['found' => false, 'reason' => 'method not allowed']);
    exit;
}

$country = isset($_GET['country']) ? trim((string) $_GET['country']) : '';
$email   = isset($_GET['email'])   ? trim((string) $_GET['email'])   : '';

/** Log every call so enumeration attempts are visible after the fact. */
function udmLogLookup(string $country, string $email, string $outcome): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0700, true);
    }
    $line = sprintf(
        "[%s] %s country=%s email=%s outcome=%s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? '-',
        $country !== '' ? $country : '-',
        $email !== '' ? substr(hash('sha256', strtolower($email)), 0, 12) : '-',
        $outcome
    );
    @file_put_contents($logDir . '/lookup.log', $line, FILE_APPEND | LOCK_EX);
}

if ($country === '' || $email === '') {
    udmLogLookup($country, $email, 'missing-parameter');
    echo json_encode(['found' => false, 'reason' => 'country and email are both required']);
    exit;
}

if (strlen($country) !== 2) {
    udmLogLookup($country, $email, 'bad-country');
    echo json_encode(['found' => false, 'reason' => 'country must be a 2-letter code']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    udmLogLookup($country, $email, 'bad-email');
    echo json_encode(['found' => false, 'reason' => 'not a valid email address']);
    exit;
}

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/initDB.php';
require_once __DIR__ . '/../../sync/mirrorMatch.php';
require_once __DIR__ . '/../../models/shopLookup.php';

if (!isset($db)) {
    http_response_code(503);
    udmLogLookup($country, $email, 'db-unavailable');
    echo json_encode(['found' => false, 'reason' => 'lookup temporarily unavailable']);
    exit;
}

try {
    $result = udmLookupShopByEmail($db, $country, $email);
} catch (Throwable $e) {
    http_response_code(503);
    udmLogLookup($country, $email, 'error');
    echo json_encode(['found' => false, 'reason' => 'lookup temporarily unavailable']);
    exit;
}

udmLogLookup($country, $email, $result['found'] ? 'found' : 'not-found');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_lookup_endpoint.php`
Expected: all `PASS:`, final line `Passed: 18  Failed: 0`, exit 0.

Apache must be running and the project reachable at
`http://localhost/masterPanel/`. If the reachability check fails, fix the URL
in the test's `ENDPOINT` constant to match the local setup rather than changing
the endpoint.

- [ ] **Step 5: Confirm the log is being written**

Run: `tail -5 modules/upgrade-downgrade/assets/logs/lookup.log`
Expected: one line per call from the test run, with hashed emails (not plaintext)
and outcomes including `found`, `not-found`, `bad-email`.

- [ ] **Step 6: Commit**

```bash
git add modules/upgrade-downgrade/assets/API/lookupByEmail.php \
        modules/upgrade-downgrade/tests/test_lookup_endpoint.php
git commit -m "feat(upgrade-downgrade): add customer email lookup endpoint

Unauthenticated by necessity -- it serves a public form -- so the surface is
narrowed deliberately: only the fields the form fills are returned, the email
is never echoed back, and every call is logged with a hashed email so
enumeration attempts are visible after the fact.

A miss returns 200 with found:false rather than 404, because a miss is a normal
outcome the form handles, not an error.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 9: Form view scaffolding

**Files:**

- Modify: `modules/upgrade-downgrade/views/upgradeForm.php:155-175` (the customer-mode `originalProduct` select) and `:160` area (the email field at `:160`)

**Interfaces:**

- Consumes: nothing.
- Produces: DOM the JS in Task 10 depends on — `#emailLookupStatus`, `#customerProductList`, `#customerProductSelectWrap`, and the hidden `#originalProduct` input in customer mode.

- [ ] **Step 1: Add the lookup status line under the email field**

In `modules/upgrade-downgrade/views/upgradeForm.php`, find the email field:

```php
                            <div class="mb-3">
                                <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="e.g. owner@restaurant.com" required>
                                <div class="invalid-feedback">Please enter an email address.</div>
                            </div>
```

Replace it with:

```php
                            <div class="mb-3">
                                <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="e.g. owner@restaurant.com" required>
                                <div class="invalid-feedback">Please enter an email address.</div>
                                <?php if ($mode === 'customer'): ?>
                                <div class="form-text">We'll use this to find your shop and current products.</div>
                                <div id="emailLookupStatus" class="email-lookup-status" aria-live="polite"></div>
                                <?php endif; ?>
                            </div>
```

- [ ] **Step 2: Restructure the customer-mode product field**

Find the customer-mode branch of the current-product field:

```php
                                    <?php else: ?>
                                    <label for="originalProduct" class="form-label">Current Product</label>
                                    <select class="form-select" id="originalProduct" name="originalProduct">
```

Replace the whole `<?php else: ?>` branch (up to but not including the
`<?php endif; ?>` that closes it) with:

```php
                                    <?php else: ?>
                                    <label class="form-label">Current Product</label>

                                    <!-- Filled from the email lookup. Hidden until the lookup succeeds. -->
                                    <div id="customerProductList" class="subscription-list d-none"></div>
                                    <input type="hidden" id="originalProduct" name="originalProduct" value="">

                                    <!-- Manual fallback: shown whenever the lookup has not produced products. -->
                                    <div id="customerProductSelectWrap">
                                        <select class="form-select" id="originalProductSelect" name="originalProductManual">
                                            <option value="">-- Select Current Product --</option>
                                            <optgroup label="Pro Plans">
                                                <option value="pro_starter">Pro - Local Starter</option>
                                                <option value="pro_growth">Pro - Local Growth</option>
                                                <option value="pro_ultimate">Pro - Local Ultimate</option>
                                            </optgroup>
                                            <optgroup label="Solo Plans">
                                                <option value="solo_starter">Solo - Local Starter</option>
                                                <option value="solo_growth">Solo - Local Growth</option>
                                                <option value="solo_ultimate">Solo - Local Ultimate</option>
                                            </optgroup>
                                            <optgroup label="Bundle Plans">
                                                <option value="bundle_starter">Bundle - Local Starter</option>
                                                <option value="bundle_growth">Bundle - Local Growth</option>
                                                <option value="bundle_ultimate">Bundle - Local Ultimate</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <?php endif; ?>
```

Note what changed and why: `#originalProduct` is now a hidden input in customer
mode, exactly as it already is in staff mode, so both modes submit the same
field in the same shape. The visible `<select>` is renamed to
`#originalProductSelect` / `originalProductManual` and its value is copied into
the hidden input by the JS, so a customer who never triggers a lookup still
submits their choice under the name the webhook expects.

- [ ] **Step 2b: Add the status-line styles**

Append to `modules/upgrade-downgrade/assets/css/upgradeForm.css`:

```css
/* Email lookup status, customer mode */
.email-lookup-status { margin-top: .5rem; font-size: .875rem; }
.email-lookup-status:empty { display: none; }
.email-lookup-status .els-found { color: #198754; }
.email-lookup-status .els-neutral { color: #6c757d; }
.email-lookup-status .els-stale { color: #fd7e14; }
.email-lookup-status .els-revert { margin-left: .5rem; }
```

No new styles are needed for the product list itself: `.subscription-list`,
`.subscription-item` and `.sub-name` already exist in this file
at lines 55–96, because staff mode uses the same markup. Reusing them is why
Task 10 renders the customer product list with those exact class names.

Then bump the cache-buster in `views/upgradeForm.php:24` so browsers pick the
new CSS up — change `upgradeForm.css?v=2.0.0` to `upgradeForm.css?v=2.1.0`.

- [ ] **Step 3: Verify the view parses and renders**

Run: `php -l modules/upgrade-downgrade/views/upgradeForm.php`
Expected: `No syntax errors detected`.

Then open `http://localhost/masterPanel/modules/upgrade-downgrade/views/upgradeForm.php?mode=customer`
and confirm: the email field shows the new helper text, and "Current Product"
shows the manual `<select>` (it is the fallback, visible by default).

Then open the same URL with `?mode=staff` and confirm the staff view is
visually unchanged — it still shows the Monday project search and the
`#originalProductList` subscription box.

- [ ] **Step 4: Commit**

```bash
git add modules/upgrade-downgrade/views/upgradeForm.php
git commit -m "feat(upgrade-downgrade): prepare customer-mode product field for lookup

Customer mode now submits originalProduct as a hidden input, the same shape
staff mode already uses, with the hardcoded select kept as a visible fallback
under a separate id. A customer whose lookup never fires still submits their
choice under the field name the webhook expects.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 10: Form lookup behaviour

**Files:**

- Modify: `modules/upgrade-downgrade/controllers/upgradeForm.js` — add a new IIFE after the existing `Project Search Autocomplete` block (which ends around `:278`)

**Interfaces:**

- Consumes: `GET ../assets/API/lookupByEmail.php?country=&email=` (Task 8); the DOM ids from Task 9; the existing `IS_STAFF` global.
- Produces: no exports. Side effects on the form only.

- [ ] **Step 1: Confirm how IS_STAFF is defined**

Run: `grep -n "IS_STAFF" modules/upgrade-downgrade/controllers/upgradeForm.js modules/upgrade-downgrade/views/upgradeForm.php`

Expected: a definition reading the `data-mode` attribute on `<body>`, and the
existing guard `if (!IS_STAFF) return;` inside the project-search IIFE. The new
block uses the mirror-image guard `if (IS_STAFF) return;`.

- [ ] **Step 2: Add the customer lookup block**

Insert this immediately after the closing `})();` of the
`// ===== Project Search Autocomplete =====` IIFE in
`modules/upgrade-downgrade/controllers/upgradeForm.js`:

```javascript
// ===== Customer Email Lookup =====
// Customer mode has no Monday project id to search on, so the email is the
// only key the customer reliably knows. This fills the shop fields and the
// real product list from the mirrored Monday data.
//
// Every failure path leaves the form exactly as the customer left it: the
// lookup is an accelerator, never a gate. A wrong or unrecognised email is not
// an error condition -- CS briefs the customer directly.
(function () {
    const $email   = $('#emailAddress');
    const $status  = $('#emailLookupStatus');
    const $list    = $('#customerProductList');
    const $selWrap = $('#customerProductSelectWrap');
    const $select  = $('#originalProductSelect');
    const $hidden  = $('#originalProduct');

    let lookupTimer = null;
    let lastQueried = '';
    let inFlight    = null;

    if (IS_STAFF) return;

    function setStatus(html) {
        $status.html(html || '');
    }

    // With no lookup result, the hidden field that actually submits must track
    // the manual select -- otherwise a customer who picks a plan by hand
    // submits an empty originalProduct.
    function useManualSelect() {
        $list.addClass('d-none').empty();
        $selWrap.removeClass('d-none');
        $hidden.val($select.val() || '');
    }

    function useLookupProducts(subscriptions) {
        if (!subscriptions.length) {
            useManualSelect();
            return;
        }

        $selWrap.addClass('d-none');
        $hidden.val(subscriptions.map(s => s.name).join(', '));

        // Names only -- no prices in the customer-facing list. The endpoint does
        // not return them, so there is nothing to render even if this changed.
        const html = subscriptions.map(s => `
            <div class="subscription-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <div class="sub-name">${escapeHtml(s.name)}</div>
                </div>
            </div>`).join('');

        $list.html(html).removeClass('d-none');
    }

    // The shop name, owner name and product names come from Monday and are
    // rendered as HTML, so they are escaped here rather than trusted.
    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function fillShop(shop) {
        $('#shopName').val(shop.shopName || '');
        $('#ownerName').val(shop.ownerName || '');
        $('#phoneNumber').val(shop.phone || '');
        // These are hidden inputs in customer mode and have always submitted
        // empty; populating them means the webhook payload finally carries a
        // real project id for a customer submission.
        $('#mondayProjectId').val(shop.shopId || '');
        $('#boardId').val(shop.boardId || '');
    }

    function clearShopFields() {
        $('#shopName, #ownerName, #phoneNumber').val('');
        $('#mondayProjectId, #boardId').val('');
    }

    function revertToManual() {
        lastQueried = '';
        clearShopFields();
        useManualSelect();
        setStatus('');
    }

    function onFound(data) {
        fillShop(data.shop);
        useLookupProducts(data.subscriptions || []);

        const name = escapeHtml(data.shop.shopName);
        let msg = `<span class="els-found"><i class="bi bi-check-circle"></i> Found <strong>${name}</strong>.`;
        if (data.stale) {
            msg = `<span class="els-stale"><i class="bi bi-exclamation-circle"></i> Found <strong>${name}</strong>. These details may be out of date; our team will confirm them.`;
        }
        if (data.multiple) {
            msg += ' More than one shop uses this email, so our team will confirm which one.';
        }
        msg += ' You can edit anything below.</span>'
             + '<a href="#" class="els-revert" id="emailLookupRevert">Not my shop?</a>';
        setStatus(msg);
    }

    function onNotFound() {
        // Deliberately not alarming: an unrecognised email is expected, and CS
        // reconciles it. Nothing the customer already typed is touched.
        useManualSelect();
        setStatus('<span class="els-neutral">We couldn\'t prefill your details from this email. Please fill in the form and our team will confirm everything.</span>');
    }

    function runLookup() {
        const country = ($('#country').val() || '').trim();
        const email   = ($email.val() || '').trim();

        if (!country || !email || email.indexOf('@') === -1) return;

        const key = country + '|' + email.toLowerCase();
        if (key === lastQueried) return;
        lastQueried = key;

        if (inFlight) inFlight.abort();

        setStatus('<span class="els-neutral"><span class="spinner-border spinner-border-sm"></span> Looking up your shop...</span>');

        inFlight = $.getJSON('../assets/API/lookupByEmail.php', { country, email })
            .done(function (data) {
                if (data && data.found) onFound(data);
                else onNotFound();
            })
            .fail(function (jqXHR, textStatus) {
                if (textStatus === 'abort') return;
                // A failed lookup must be indistinguishable from no lookup.
                useManualSelect();
                setStatus('');
            })
            .always(function () { inFlight = null; });
    }

    $email.on('blur', runLookup);

    $email.on('input', function () {
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(runLookup, 400);
    });

    // A country change invalidates the result, mirroring how staff mode clears
    // its fields when the country changes.
    $('#country').on('change', function () {
        revertToManual();
        runLookup();
    });

    // Keep the submitted field in step with a manual choice.
    $select.on('change', function () {
        if ($selWrap.hasClass('d-none')) return;
        $hidden.val($(this).val() || '');
    });

    $status.on('click', '#emailLookupRevert', function (e) {
        e.preventDefault();
        revertToManual();
    });
})();
```

- [ ] **Step 3: Verify the file parses**

Run: `node --check modules/upgrade-downgrade/controllers/upgradeForm.js`
Expected: no output, exit 0.

- [ ] **Step 4: Test the happy path in a browser**

First get a real email from the mirror:

```bash
php -r 'require "modules/upgrade-downgrade/assets/db/db.php"; require "modules/upgrade-downgrade/assets/db/initDB.php"; $r=$db->query("SELECT s.country, s.email, s.shop_name, COUNT(b.sub_id) n FROM l4u_ud_shops s JOIN l4u_ud_subscriptions b ON b.shop_id = s.shop_id WHERE s.email <> \"\" GROUP BY s.shop_id HAVING n > 1 LIMIT 3")->fetchAll(); foreach($r as $x) echo "{$x["country"]}  {$x["email"]}  {$x["shop_name"]}  ({$x["n"]} subs)\n";'
```

Then open `http://localhost/masterPanel/modules/upgrade-downgrade/views/upgradeForm.php?mode=customer`
and check each of these:

1. Select the country from the query output, type the email, tab out. The shop
   name, owner and phone fill in; the status line names the shop; the product
   list shows the real products and the manual select is gone.
2. Open devtools and confirm `#originalProduct` holds the comma-joined product
   names and `#mondayProjectId` holds a numeric id.
3. Click "Not my shop?" — fields clear, the manual select returns.
4. Type a nonsense email and tab out — the neutral message appears, nothing
   the customer typed is cleared, the manual select stays.
5. Pick a plan from the manual select and confirm `#originalProduct` updates.
6. With a successful lookup showing, change the country — the result clears.
7. Submit the form and confirm it reaches the thank-you page.

- [ ] **Step 5: Confirm staff mode is untouched**

Open `?mode=staff` and confirm the project search still fills fields and loads
subscriptions, and that no lookup status line appears under the email field.
Check the browser console is free of errors.

- [ ] **Step 6: Run the full test suite**

```bash
for t in modules/upgrade-downgrade/tests/test_*.php; do echo "== $t"; php "$t" || echo "FAILED: $t"; done
```

Expected: every file ends `Failed: 0`. Also re-run the sibling module suites
that share the `db` class, to be sure nothing regressed:

```bash
for t in modules/googleReview/tests/test_*.php; do php "$t" >/dev/null || echo "FAILED: $t"; done
```

Expected: no `FAILED:` lines.

- [ ] **Step 7: Commit**

```bash
git add modules/upgrade-downgrade/controllers/upgradeForm.js
git commit -m "feat(upgrade-downgrade): prefill customer form from email lookup

On a match the form fills the shop fields and replaces the hardcoded nine-item
product select with the customer's real active products, all still editable,
with a 'Not my shop?' link back to manual entry.

Every failure path -- a miss, a timeout, a 503 -- leaves the form exactly as the
customer left it and keeps the manual select, because the lookup is an
accelerator and never a gate.

Monday-sourced values are escaped before rendering rather than trusted.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 11: Cron registration and documentation

**Files:**

- Create: `modules/upgrade-downgrade/sync/README.md`
- Modify: `modules/upgrade-downgrade/.gitignore` (add the logs directory)

**Interfaces:**

- Consumes: everything above.
- Produces: a scheduled sync and the runbook for it.

- [ ] **Step 1: Ignore the log directory**

Replace `modules/upgrade-downgrade/.gitignore` with:

```gitignore
assets/config.php
assets/logs/
```

- [ ] **Step 2: Write the runbook**

Create `modules/upgrade-downgrade/sync/README.md`:

```markdown
# Monday mirror sync

Mirrors Monday into `l4u_ud_shops` and `l4u_ud_subscriptions` so the
customer-mode upgrade form can find a shop by email in milliseconds instead of
the seconds a live Monday query takes.

## What reads this

`assets/API/lookupByEmail.php`, called by `controllers/upgradeForm.js` when a
customer enters their email in `views/upgradeForm.php?mode=customer`.

Nothing else. Staff mode still queries Monday live through
`assets/API/selectProjectCountry/live.php` and `assets/API/getSubscriptions.php`,
which this sync does not touch.

## Running it

    php modules/upgrade-downgrade/sync/syncMondayMirror.php            # all countries
    php modules/upgrade-downgrade/sync/syncMondayMirror.php TH AU      # some countries

Exit 0 means every country synced. Exit 1 means at least one country or the
subscriptions pass failed; the failing part keeps its previous rows.

## Schedule

Twice daily. A customer who signed up one to three days ago is not using the
upgrade form yet, so twelve-hour-old data is accurate enough for the people who
reach this form.

Windows Task Scheduler:

    schtasks /create /tn "L4U UD Mirror Sync" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\masterPanel\modules\upgrade-downgrade\sync\syncMondayMirror.php" /sc daily /st 06:00 /ri 720 /du 24:00

cron:

    0 6,18 * * * cd /path/to/masterPanel && php modules/upgrade-downgrade/sync/syncMondayMirror.php >> /dev/null 2>&1

The script keeps its own rolling log at `assets/logs/mirror-sync.log`, so
redirecting output to `/dev/null` loses nothing.

## Reading the summary

    [2026-10-05 06:00:04 +07] ud mirror sync ok (182.3s)
      TH: 214 shops (0 new, 214 updated, 1 removed), 198 with an email
      ...
      subscriptions: 1532 active (1530 by relation, 2 by name, 0 unmatched), 7 removed

- **`with an email`** well below the shop count means contacts are missing
  emails in Monday. Those shops can never be found by the form.
- **`by name`** or **`unmatched`** climbing means the `connect_boards`
  ("Projects") relation is being left unset on the Active Subscriptions board.
  The fallback is holding, but somebody should fix the board: unmatched
  subscriptions belong to no shop and will never appear in the form.
- **`ERROR`** on a country means that country kept its previous rows. Safe to
  leave until the next run; investigate if it repeats.

## If the sync stops

The form degrades, it does not break. Lookups keep answering from the last good
data, and past 48 hours the endpoint marks the response `stale` and the form
tells the customer the details may be out of date. A customer whose shop is not
found simply fills the form in by hand, which is how the form worked before this
existed.

## Configuration

`assets/config.php` (gitignored; copy from `assets/config.example.php`):

- `monday_token` — needs read access to the six project boards, Contacts &
  Accounts (1862866069), and Active Subscriptions (1881439330).
- `sync_token` — only needed to trigger the sync over HTTP. Leave empty to
  allow CLI invocation only.

## Known Monday coupling

Column ids are hardcoded in `mondayClient.php` and `mondayMirror.php` because
Monday's API addresses columns by opaque id, not by title. If someone deletes
and recreates a column in Monday, its id changes and this sync silently stops
reading that field. The summary line is the early warning: a field going blank
shows up as `with an email` collapsing or `unmatched` spiking.

The ids in use:

| Board | Column id | Meaning |
| --- | --- | --- |
| Project (per country) | `color1` | Customer / Industrial Type |
| Project | `connect_boards0` | relation to Contacts & Accounts |
| Contacts | `text0`, `text7` | first name, last name |
| Contacts | `contact_phone`, `dup__of_mobile` | mobile, alt phone |
| Contacts | `contact_email` | email — the lookup key |
| Active Subscriptions | `status` | Subscription Status |
| Active Subscriptions | `numbers`, `status0` | Amount, Currency |
| Active Subscriptions | `text9` | Account Name (fallback match only) |
| Active Subscriptions | `connect_boards` | relation to Projects — the join |
| Active Subscriptions | `lookup_mkwgjt31` | Product Group — customer-facing name |
```

- [ ] **Step 3: Register the scheduled task**

Run the `schtasks` command from the README (adjust paths if XAMPP is installed
elsewhere), then confirm:

Run: `schtasks /query /tn "L4U UD Mirror Sync"`
Expected: the task listed with its next run time.

- [ ] **Step 4: Verify the scheduled task actually runs**

Run: `schtasks /run /tn "L4U UD Mirror Sync"`

Wait for it to finish, then:

Run: `tail -20 modules/upgrade-downgrade/assets/logs/mirror-sync.log`
Expected: a fresh entry with a line per country and no `ERROR`. If the log has
no new entry, the task's working directory or PHP path is wrong — fix the task,
not the script.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/sync/README.md modules/upgrade-downgrade/.gitignore
git commit -m "docs(upgrade-downgrade): document the mirror sync and its schedule

Records how to read the summary line, what a rising by-name or unmatched count
means, and that the hardcoded Monday column ids are a silent-failure risk if a
column is ever recreated.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Deferred

Noted here so they are not mistaken for oversights:

- **The Monday token in `getSubscriptions.php:5` and `live.php:15`** stays
  hardcoded and committed. The spec rules out touching those two files; the new
  code reads the token from config instead so no third copy is created.
  Migrating the existing two is a separate change, and they should be rotated
  when it happens, since the current token is in git history.
- **Rate limiting `lookupByEmail.php`.** The spec names it; this plan logs every
  call with a hashed email so enumeration is detectable, but adds no limiter.
  Add one if the log shows probing.
- **Downgrade form.** No current-product field, so nothing to prefill.
- **`matched_by = 'none'` subscriptions** are stored with an empty `shop_id` and
  are invisible to the lookup. They are counted in the summary rather than
  dropped, so the board can be fixed in Monday.

## Self-Review

**Spec coverage:**

| Spec section | Task |
| --- | --- |
| Two-table schema | 1 |
| Config, token not hardcoded | 1, 4 |
| Decision 4: relation join + name fallback + `matched_by` | 2, 5 |
| Contacts collapsed into shops; email as its own field | 5 |
| Sync flow steps 1–8 | 5, 6 |
| Failed country skipped, not emptied | 5 (`udmSyncCountry`), 6 (summary) |
| Stale-row deletion | 3, 5 |
| Lookup query + response shape | 7 |
| `multiple` flag | 7 |
| Staleness 48h + `stale` flag | 7, 10 |
| UI fill, editable fields, "not my shop" | 9, 10 |
| Country change clears result | 10 |
| No-match and failure fall back silently | 10 |
| Error-handling table | 7, 8, 10 |
| Testing list | 2, 3, 4, 5, 7, 8, 10 |
| Security: enumeration surface | 8 |
| Security: token out of source | 1, 4 |
| Deployment steps 1–4 | 1, 6, 11 |

Spec's test list item "sync against a fixture Monday response: a failing country
leaving its existing rows intact" is covered by the pure extractor tests in
Task 5 plus the country-scoped deletion test in Task 3; `udmSyncCountry`'s
error branch returns before any write, so there is no write path to test
separately.

**Placeholder scan:** none. Every code step carries complete code; every run
step carries a command and an expected result.

**Type consistency checked:**
- `udmResolveShopId` returns `['shop_id', 'matched_by']` — used with those exact
  keys in Task 5.
- The subscription row keys `linked_shop_id` and `account_name` produced by
  `udmExtractSubscriptionRows` are exactly what `udmResolveShopId` reads.
- `udmUpsertShop`'s `$shop` keys match what `udmSyncCountry` passes.
- `udmUpsertSubscription`'s `$sub` keys match what `udmSyncSubscriptions` passes.
- The lookup's response keys (`shopId`, `shopName`, `ownerName`, `phone`,
  `boardId`; `name`, `rawName`, `price`, `currency`) match what the JS in Task
  10 reads.
- `udmNormalizeEmail` is required by both `mirrorRepo.php` (via Task 3) and
  `shopLookup.php` (via Task 8's includes), and `mirrorMatch.php` is included
  ahead of both everywhere.
