# Downgrade Form Revisions Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the thirteen CS revisions to the downgrade / unsubscription form, and give that form the email lookup the conditional revisions depend on.

**Architecture:** The customer email lookup already built for the upgrade form is extracted into a shared, configuration-driven `emailLookup.js` and wired into both forms, so the four hard-won failure-path fixes live in one file. A new pure PHP function `udmClassifyProduct()` turns a Monday `product_group` string into a list of tags (`araya`, `plan_bundle`, `plan_solo`, `addon`) via an explicit map in `config.php` with a keyword fallback; its JavaScript twin drives the form's conditional sections. Every conditional section is recomputed wholesale by a single `recomputeConditionalState()` reading the currently checked product boxes, never by incremental add/remove.

**Tech Stack:** PHP 8 (no framework, `mysqli` wrapper at `assets/db/db.php`), jQuery 3.7.1, Bootstrap 5.3.3, Bootstrap Icons 1.11.3. Tests are standalone PHP scripts run from the CLI with a hand-rolled `test()` helper. No build step; no package manager for front-end code.

## Global Constraints

- **Working tree:** all paths are relative to `C:\xampp\htdocs\masterPanel-ud-lookup`, branch `mark/feature/ud-email-lookup-wt`. The module root is `modules/upgrade-downgrade/`.
- **Line endings:** the module's PHP and JS files use CRLF. Preserve them — a whole-file rewrite that switches to LF produces a diff touching every line and will be rejected at review.
- **The lookup is an accelerator, never a gate.** Every lookup failure path must leave the form submittable by hand, with nothing the customer typed cleared.
- **Hiding a `required` field must also remove its `required` attribute**, and validation must stop requiring it. A hidden required field fails validation with nothing visible to fix, making the form permanently unsubmittable. This is the single most likely way to ship these revisions broken.
- **No prices anywhere in this plan.** CS sanctioned shipping without them: "ถ้าทำไม่ได้ เอาแบบไม่มีราคาก่อนได้เลยค่า". Deferred to its own spec.
- **PHP function prefix** is `udm`, matching `udmLookupShopByEmail`, `udmNormalizeEmail`, `udmUpsertShop`.
- **`assets/config.php` is gitignored.** Every new config key ships in `assets/config.example.php` and must be copied into the real `config.php` at deploy.
- **Staff mode is detected as `$mode === 'staff'`** in `views/downgradeForm.php` (not `!== 'customer'`, which the sidebar uses). Keep each existing comparison as it is; do not normalise them in this work.
- **Araya classification cannot rely on the keyword fallback.** Verified against the live mirror: of 66 distinct `product_group` values, the keyword rule ("AI Receptionist" or "Araya") matches exactly **1**. The spec's own worked example, `AI + Local Starter Bundle`, contains neither string and is caught **only** by the explicit map. The map is load-bearing, not a convenience.
- **Make.com open question (approved to build, flagged):** dropping `downgradeTarget` from the webhook payload and adding `cancelledProducts` / `keepProducts` changes the contract with a Make.com scenario this repo cannot inspect. Build it as designed; the deviation is recorded in Task 9 and must be confirmed with the scenario owner before this reaches production.

---

## File Structure

New files:

| Path | Responsibility |
| --- | --- |
| `modules/upgrade-downgrade/assets/productClassify.php` | `udmClassifyProduct()` — pure, map-then-keyword product tagging. No DB, no I/O. |
| `modules/upgrade-downgrade/assets/js/emailLookup.js` | The shared email lookup, extracted from `controllers/upgradeForm.js`. Configuration-driven so both forms can use it. |
| `modules/upgrade-downgrade/assets/API/classifyProducts.php` | Thin endpoint returning tags per product name, so the browser classifies with the same map PHP uses. |
| `modules/upgrade-downgrade/tests/test_product_classify.php` | Tests for `udmClassifyProduct()` against all real mirror values. |
| `modules/upgrade-downgrade/tests/test_downgrade_conditionals.md` | Manual browser test script for `recomputeConditionalState()` (no JS test runner exists in this repo). |

Modified files:

| Path | Change |
| --- | --- |
| `assets/config.example.php` | Adds `product_classification` map and `araya_feedback_url`. |
| `controllers/upgradeForm.js` | Lookup block (lines 300–581) removed, replaced by a call into the shared file. |
| `views/upgradeForm.php` | Script tag for `emailLookup.js` added before `upgradeForm.js`. |
| `views/downgradeForm.php` | Field reorder, Type of Request rework, product checkbox list, Araya conditionals, policy section, staff variant, Araya block in the info modal, lookup markup. |
| `controllers/downgradeForm.js` | `FORM_MODE` constant, lookup wiring, `recomputeConditionalState()`, validation changes, payload changes, test autofill update. |
| `models/downgradeForm.php` | Araya admin email on submit. |

**Deviation from the spec, deliberate:** the spec placed the Araya admin email and the feedback redirect in `views/thankyou.php`. That file is static HTML reached by `window.location.replace("thankyou.php")` with no query string — it receives no submission data at all, so it cannot know whether an Araya product was cancelled. `models/downgradeForm.php` already receives the full payload server-side, runs before the redirect, and already isolates its own failures. The email goes there (Task 10). The redirect stays client-side in `controllers/downgradeForm.js`, where the cancelled products and the shop details are already in hand (Task 11).

---

## Task 1: Product classification map and `udmClassifyProduct()`

**Files:**
- Create: `modules/upgrade-downgrade/assets/productClassify.php`
- Modify: `modules/upgrade-downgrade/assets/config.example.php`
- Test: `modules/upgrade-downgrade/tests/test_product_classify.php`

**Interfaces:**
- Consumes: nothing from earlier tasks. Reads an optional `product_classification` key from the array returned by `assets/config.php`.
- Produces:
  - `udmClassifyProduct(string $productGroup, array $map = []): array` — returns a list of tag strings, a subset of `['araya', 'plan_bundle', 'plan_solo', 'addon']`, never empty.
  - `udmClassificationMap(): array` — loads the map from `config.php`, returning `[]` when the file or key is absent.
  - Config key `product_classification`: `array<string, string[]>` keyed by the exact `product_group` string.

- [ ] **Step 1: Write the failing test**

Create `modules/upgrade-downgrade/tests/test_product_classify.php`:

```php
<?php
require_once __DIR__ . '/../assets/productClassify.php';

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

// The map used throughout these tests. Mirrors what ships in config.example.php
// so the tests fail if the two drift apart.
$map = [
    'AI Receptionist'                        => ['araya'],
    'AI + Local Starter Bundle'              => ['araya', 'plan_bundle'],
    'Social Media Marketing Bundle'          => ['addon'],
    'Social Media Marketing Bundle (Incl Website Hosting and Booking System)' => ['addon'],
];

echo "\n== The map is authoritative ==\n";

test("AI Receptionist is araya",
     udmClassifyProduct('AI Receptionist', $map) === ['araya']);

// The spec's worked example, and the reason the map exists at all: this name
// contains neither "AI Receptionist" nor "Araya", so the keyword fallback
// cannot see it. Only the map can.
test("AI + Local Starter Bundle is both araya and plan_bundle",
     udmClassifyProduct('AI + Local Starter Bundle', $map) === ['araya', 'plan_bundle']);

test("Social Media Marketing Bundle is an addon, not a plan bundle",
     udmClassifyProduct('Social Media Marketing Bundle', $map) === ['addon']);

test("the map beats the keyword rule it contradicts",
     !in_array('plan_bundle', udmClassifyProduct('Social Media Marketing Bundle (Incl Website Hosting and Booking System)', $map), true));

echo "\n== Keyword fallback, for names absent from the map ==\n";

test("Local Starter Bundle is a plan bundle",
     udmClassifyProduct('Local Starter Bundle', $map) === ['plan_bundle']);

test("Local Growth Bundle is a plan bundle",
     udmClassifyProduct('Local Growth Bundle', $map) === ['plan_bundle']);

test("Local Starter Solo is a plan solo",
     udmClassifyProduct('Local Starter Solo', $map) === ['plan_solo']);

test("Araya by name is araya",
     udmClassifyProduct('Araya Receptionist Add-on', $map) === ['araya']);

test("matching is case insensitive",
     udmClassifyProduct('local starter bundle', $map) === ['plan_bundle']);

echo "\n== Solo and Bundle without Local are add-ons ==\n";

// "Solo" and "Bundle" are used for add-on product families too. Requiring
// "Local" is what keeps the keep-list from firing on a social media add-on.
test("Mega Marketing Solo is an addon",
     udmClassifyProduct('Mega Marketing Solo', $map) === ['addon']);

test("Visibility Boost Solo - No Contract is an addon",
     udmClassifyProduct('Visibility Boost Solo - No Contract', $map) === ['addon']);

test("Social Media Marketing Solo is an addon",
     udmClassifyProduct('Social Media Marketing Solo', $map) === ['addon']);

test("Direct Marketing Bundle is an addon",
     udmClassifyProduct('Direct Marketing Bundle', $map) === ['addon']);

echo "\n== The addon default ==\n";

test("an unrecognised name is an addon",
     udmClassifyProduct('Pro Online Ordering System', $map) === ['addon']);

test("a name we have never seen is an addon",
     udmClassifyProduct('Some Brand New Product 2027', $map) === ['addon']);

test("empty string is an addon",
     udmClassifyProduct('', $map) === ['addon']);

test("whitespace only is an addon",
     udmClassifyProduct('   ', $map) === ['addon']);

test("a result is never empty",
     count(udmClassifyProduct('anything at all', $map)) > 0);

echo "\n== Comma-joined composite names ==\n";

// 13 of the 66 real values are several products joined by commas, written into
// one Monday column. Classifying the whole string is correct: cancelling that
// subscription cancels everything in it.
test("a composite containing a plan bundle is a plan bundle",
     udmClassifyProduct('Social Media Ad Spend Credit (Original), Local Starter Bundle - No Contract', $map) === ['plan_bundle']);

test("a composite containing a solo plan is a plan solo",
     udmClassifyProduct('GBP - 29.00 WHT03M00 Website Hosting + Email included, GBP - 149 LSS02M00 Local Starter Solo - No Contract', $map) === ['plan_solo']);

test("a composite of add-ons only is an addon",
     udmClassifyProduct('Yelp Ad Spend, Yelp Enhanced Profile', $map) === ['addon']);

echo "\n== Tag order is stable ==\n";

// Callers compare arrays, and the JS twin must agree, so order is part of the
// contract: araya, plan_bundle, plan_solo, addon.
test("tags come back in declaration order",
     udmClassifyProduct('AI + Local Starter Bundle', $map) === ['araya', 'plan_bundle']);

echo "\n== An absent map still works ==\n";

test("no map falls back to keywords",
     udmClassifyProduct('Local Starter Bundle') === ['plan_bundle']);

test("no map leaves AI + Local Starter Bundle as a plan bundle only",
     udmClassifyProduct('AI + Local Starter Bundle') === ['plan_bundle']);

echo "\n== udmClassificationMap() ==\n";

test("returns an array even with no config.php",
     is_array(udmClassificationMap()));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php modules/upgrade-downgrade/tests/test_product_classify.php`

Expected: FAIL — `Failed to open stream: No such file or directory .../assets/productClassify.php`

- [ ] **Step 3: Write the implementation**

Create `modules/upgrade-downgrade/assets/productClassify.php`:

```php
<?php
/**
 * Product classification for the upgrade/downgrade forms.
 *
 * Almost every conditional item on the downgrade form keys off what kind of
 * product is being cancelled, so this turns a Monday `product_group` string
 * into a list of tags.
 *
 * A list, not a single type: "AI + Local Starter Bundle" is a combined product
 * where the AI receptionist ships alongside a plan, so it must trigger both the
 * Araya flows and the plan-bundle keep-list.
 *
 * Resolution is map first, then keywords, then `addon`. The map is
 * authoritative because it is the only thing that can see a combined product:
 * of the 66 distinct product_group values in the mirror, the keyword rule
 * matches "AI Receptionist" or "Araya" in exactly one, and "AI + Local Starter
 * Bundle" is not it. A keyword rule is also invisible until it is wrong, which
 * is how getPlanType() silently broke the Solo-to-Bundle gate on the upgrade
 * form for every real product name.
 */

/**
 * The classification map from config.php, or [] when it is not configured.
 *
 * config.php is gitignored, so a deployment that has not copied the new key
 * across must still work: the keyword fallback covers the plan names, and the
 * only loss is the combined-product tagging.
 */
function udmClassificationMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }

    $map  = [];
    $path = __DIR__ . '/config.php';
    if (is_file($path)) {
        $config = require $path;
        if (is_array($config) && isset($config['product_classification']) && is_array($config['product_classification'])) {
            $map = $config['product_classification'];
        }
    }

    return $map;
}

/**
 * Tags for one product_group string.
 *
 * @param string $productGroup The Monday product_group value, verbatim.
 * @param array  $map          Exact-match overrides. Defaults to config.php's.
 * @return string[] A non-empty subset of araya, plan_bundle, plan_solo, addon,
 *                  always in that order.
 */
function udmClassifyProduct(string $productGroup, array $map = []): array
{
    $name = trim($productGroup);

    if ($map === []) {
        $map = udmClassificationMap();
    }

    // Exact match wins outright: this is where a human corrects a
    // misclassification, so a keyword rule must not be able to add to it.
    if (isset($map[$name]) && is_array($map[$name]) && $map[$name] !== []) {
        return array_values($map[$name]);
    }

    $hasLocal  = stripos($name, 'Local')  !== false;
    $hasBundle = stripos($name, 'Bundle') !== false;
    $hasSolo   = stripos($name, 'Solo')   !== false;

    $tags = [];

    if (stripos($name, 'AI Receptionist') !== false || stripos($name, 'Araya') !== false) {
        $tags[] = 'araya';
    }

    // "Local" is required alongside "Bundle" and "Solo" because both words are
    // used for add-on families: "Social Media Marketing Bundle" and "Mega
    // Marketing Solo" are add-ons, and firing the plan keep-list on them would
    // ask a customer cancelling a social media add-on what plan to keep.
    if ($hasLocal && $hasBundle) {
        $tags[] = 'plan_bundle';
    }

    if ($hasLocal && $hasSolo) {
        $tags[] = 'plan_solo';
    }

    // The safe default: an unrecognised product must not trigger a conditional
    // flow, so it gets the tag that triggers nothing.
    if ($tags === []) {
        $tags[] = 'addon';
    }

    return $tags;
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php modules/upgrade-downgrade/tests/test_product_classify.php`

Expected: PASS — `Passed: 24  Failed: 0`

- [ ] **Step 5: Add the config keys**

Replace the closing lines of `modules/upgrade-downgrade/assets/config.example.php`. The file currently ends:

```php
    // Shared secret for triggering sync/syncMondayMirror.php over HTTP.
    // Leave empty to allow CLI invocation only, which is the safe default.
    'sync_token' => '',
];
```

Change it to:

```php
    // Shared secret for triggering sync/syncMondayMirror.php over HTTP.
    // Leave empty to allow CLI invocation only, which is the safe default.
    'sync_token' => '',

    // Where the downgrade form sends a customer who cancelled an AI
    // Receptionist product, so they can give feedback on it.
    //
    // Empty disables the redirect: the admin notification email still sends,
    // and the customer lands on the normal thank-you page. CS has not supplied
    // the URL yet ("จะแจ้งรายละเอียดให้ทีหลัง"), so filling this in later
    // switches the behaviour on with no code change.
    'araya_feedback_url' => '',

    // Exact-match product classification, keyed by the Monday `product_group`
    // string verbatim. See assets/productClassify.php.
    //
    // Tags: araya, plan_bundle, plan_solo, addon. A product may carry several:
    // "AI + Local Starter Bundle" is an AI receptionist sold alongside a plan,
    // so it triggers both the Araya flows and the plan keep-list.
    //
    // Only list names the keyword fallback gets wrong or cannot see. The
    // fallback already handles "Local ... Bundle" and "Local ... Solo", so the
    // plain plan names are deliberately absent from this map.
    'product_classification' => [
        // Araya. The keyword rule finds only the first of these; the rest are
        // invisible to it, which is the whole reason this map exists.
        'AI Receptionist'           => ['araya'],
        'AI + Local Starter Bundle' => ['araya', 'plan_bundle'],

        // Add-ons whose names contain "Bundle" or "Solo" but which are not
        // plans. Without these the keep-list would fire on an add-on
        // cancellation. The keyword rule happens to get these right today by
        // requiring "Local" as well, but they are pinned here because the
        // consequence of a future rule change is a confusing question put to a
        // customer who is already cancelling.
        'Social Media Marketing Bundle' => ['addon'],
        'Social Media Marketing Bundle (Incl Website Hosting and Booking System)' => ['addon'],
        'Direct Marketing Bundle'       => ['addon'],
        'Mega Marketing Solo'           => ['addon'],
        'Social Media Marketing Solo'   => ['addon'],
        'Visibility Boost Solo - No Contract' => ['addon'],
    ],
];
```

- [ ] **Step 6: Re-run the test**

Run: `php modules/upgrade-downgrade/tests/test_product_classify.php`

Expected: PASS — `Passed: 24  Failed: 0` (unchanged; `config.php` is gitignored and absent, so the map still comes from the test)

- [ ] **Step 7: Check the classification against every real product name**

Run:

```bash
php -r '
require "modules/upgrade-downgrade/assets/db/db.php";
require "modules/upgrade-downgrade/assets/db/initDB.php";
require "modules/upgrade-downgrade/assets/productClassify.php";
global $db;
$cfg = require "modules/upgrade-downgrade/assets/config.example.php";
$map = $cfg["product_classification"];
$db->query("SELECT DISTINCT product_group FROM l4u_ud_subscriptions");
$counts = [];
foreach ($db->fetchAll() as $r) {
    foreach (udmClassifyProduct($r["product_group"], $map) as $t) {
        $counts[$t] = ($counts[$t] ?? 0) + 1;
    }
}
ksort($counts);
print_r($counts);
'
```

Expected, against the mirror as of 2026-10-13 (66 distinct names):

```
Array
(
    [addon] => 49
    [araya] => 2
    [plan_bundle] => 9
    [plan_solo] => 6
)
```

The counts are a tripwire, not a specification: the mirror changes as the sync runs. If they differ, print the names per tag and confirm each one is tagged sensibly before moving on. What must hold is that `araya` contains `AI Receptionist` and `AI + Local Starter Bundle` and nothing else, and that no `Social Media Marketing` name carries `plan_bundle`.

- [ ] **Step 8: Commit**

```bash
git add modules/upgrade-downgrade/assets/productClassify.php \
        modules/upgrade-downgrade/assets/config.example.php \
        modules/upgrade-downgrade/tests/test_product_classify.php
git commit -m "feat(upgrade-downgrade): classify a product into tags

Almost every conditional item on the revised downgrade form keys off what kind
of product is being cancelled, and the real data rules out doing this by name
or by keyword alone.

udmClassifyProduct returns a list of tags rather than one type, because
\"AI + Local Starter Bundle\" is an AI receptionist sold alongside a plan and
has to trigger both the Araya flows and the plan keep-list.

The map in config is authoritative rather than a convenience. Of the 66
distinct product_group values in the mirror, the keyword rule for Araya matches
exactly one, and the combined product is not it. Requiring \"Local\" alongside
\"Bundle\" is what stops the keep-list firing on \"Social Media Marketing
Bundle\", which is an add-on.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 2: Classification endpoint

The browser needs the same tags PHP computes, and the map lives in `config.php`, which JavaScript cannot read. A thin endpoint keeps one map rather than two that drift.

**Files:**
- Create: `modules/upgrade-downgrade/assets/API/classifyProducts.php`
- Test: manual, over HTTP (this repo has no HTTP test harness; `tests/test_lookup_endpoint.php` tests its endpoint's helpers directly, and there is nothing comparable to reuse here)

**Interfaces:**
- Consumes: `udmClassifyProduct()` from Task 1.
- Produces: `GET assets/API/classifyProducts.php?names[]=A&names[]=B` → `{"tags": {"A": ["araya"], "B": ["addon"]}}`. Task 6's `recomputeConditionalState()` consumes this.

- [ ] **Step 1: Write the endpoint**

Create `modules/upgrade-downgrade/assets/API/classifyProducts.php`:

```php
<?php
/**
 * Product classification for the browser: GET ?names[]=AI+Receptionist
 *
 * The classification map lives in config.php, which the browser cannot read,
 * and duplicating it into JavaScript would guarantee the two drift. The
 * downgrade form asks here instead.
 *
 * Returns {"tags": {"<name>": ["<tag>", ...], ...}} for every name asked about.
 * There is nothing to authenticate and nothing to leak: the caller supplies the
 * names and gets back a category per name, which is the same information the
 * form already shows.
 */

require_once __DIR__ . '/../productClassify.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$names = $_GET['names'] ?? [];

// A single ?names=X is a string, and array_filter on a string is a fatal.
if (is_string($names)) {
    $names = [$names];
}

if (!is_array($names)) {
    $names = [];
}

$map  = udmClassificationMap();
$tags = [];

foreach ($names as $name) {
    if (!is_string($name)) {
        continue;
    }
    // Keyed by the name exactly as asked, so the caller can look its own
    // strings back up without having to re-trim them.
    $tags[$name] = udmClassifyProduct($name, $map);
}

$encoded = json_encode(['tags' => $tags], JSON_UNESCAPED_UNICODE);

// json_encode returns false on malformed UTF-8, and echoing false prints
// nothing at all, which the browser sees as a parse error rather than a
// failure. Mirrors the guard in lookupByEmail.php.
if ($encoded === false) {
    http_response_code(500);
    echo '{"tags":{}}';
    return;
}

echo $encoded;
```

- [ ] **Step 2: Verify it classifies**

Run:

```bash
php -r '
$_GET["names"] = ["AI + Local Starter Bundle", "Local Starter Solo", "Pro Online Ordering System"];
require "modules/upgrade-downgrade/assets/API/classifyProducts.php";
'
```

Expected (with `config.php` absent, so the keyword fallback alone applies):

```json
{"tags":{"AI + Local Starter Bundle":["plan_bundle"],"Local Starter Solo":["plan_solo"],"Pro Online Ordering System":["addon"]}}
```

- [ ] **Step 3: Verify the string and empty cases do not fatal**

Run:

```bash
php -r '$_GET["names"] = "AI Receptionist"; require "modules/upgrade-downgrade/assets/API/classifyProducts.php";'
echo
php -r 'require "modules/upgrade-downgrade/assets/API/classifyProducts.php";'
```

Expected: `{"tags":{"AI Receptionist":["araya"]}}` then `{"tags":{}}`, with no warnings or fatals.

- [ ] **Step 4: Commit**

```bash
git add modules/upgrade-downgrade/assets/API/classifyProducts.php
git commit -m "feat(upgrade-downgrade): serve product classification to the browser

The classification map lives in config.php, which the browser cannot read.
Duplicating it into JavaScript would guarantee the two drift, so the downgrade
form asks the server instead.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 3: Extract the shared email lookup

The riskiest task in the plan, and the reason it comes before anything on the downgrade form. It is a pure refactor: the upgrade form's behaviour must be identical afterwards.

The block at `controllers/upgradeForm.js:300-581` carries four fixes that were expensive to find — clearing a stale Monday project id when the email stops matching, clearing only the fields the lookup itself wrote, allowing a retry after a 503, and moving focus when the revert link removes itself. Copying it into a second file guarantees the next fix lands in one and not the other.

It is coupled to the upgrade form in four ways, and the extraction is about turning each into configuration: it calls `updateSectionVisibility()` directly; it fills `#ownerName` and `#phoneNumber`, which the downgrade form calls `#contactPerson` and `#mobileNumber`; it drives a `#originalProductSelect` / `#originalProduct` pair the downgrade form does not have; and it reads the module-level `IS_STAFF`.

**Files:**
- Create: `modules/upgrade-downgrade/assets/js/emailLookup.js`
- Modify: `modules/upgrade-downgrade/controllers/upgradeForm.js:300-581` (the whole `// ===== Customer Email Lookup =====` IIFE)
- Modify: `modules/upgrade-downgrade/views/upgradeForm.php:694`
- Test: manual, in the browser — see Step 6

**Interfaces:**
- Consumes: nothing from earlier tasks. jQuery must already be loaded.
- Produces: a global `udmInitEmailLookup(options)`. Every option is optional unless marked required.

```javascript
udmInitEmailLookup({
    // Required. jQuery selectors.
    email:        '#emailAddress',   // the field whose blur/input triggers a lookup
    country:      '#country',        // changing it invalidates the result
    status:       '#emailLookupStatus',
    branchPicker: '#branchPicker',
    branchList:   '#branchPickerList',

    // Shop fields this lookup writes, and may therefore clear.
    // Keys are properties of the endpoint's shop object.
    shopFields: { shopName: '#shopName', ownerName: '#ownerName', phone: '#phoneNumber' },

    // Identity fields, cleared on every failure path so a stale project id can
    // never be submitted under a new email address.
    identityFields: { shopId: '#mondayProjectId', boardId: '#boardId' },

    // Optional. The upgrade form's product list and manual-select fallback.
    // Omitted entirely by the downgrade form, which renders checkboxes instead.
    productList:   '#customerProductList',
    productSelect: '#originalProductSelect',
    productHidden: '#originalProduct',
    productSelectWrap: '#customerProductSelectWrap',

    // Optional. Called after every state change, with the shop's subscriptions
    // (an array, empty when there are none) and the shop object or null.
    // This is where a form hangs its own rendering.
    onProducts: function (subscriptions, shop) {},

    // Optional. Called after any change that could affect which sections or
    // required fields apply. The upgrade form passes updateSectionVisibility.
    onChange: function () {},

    // Optional, default false. True disables the lookup entirely.
    disabled: false,
});
```

- [ ] **Step 1: Create the shared file**

Create `modules/upgrade-downgrade/assets/js/emailLookup.js`. This is the block from `controllers/upgradeForm.js:300-581` with its four couplings turned into options; every comment explaining a failure-path fix is carried over verbatim, because those comments are the record of why the code is shaped this way.

```javascript
/* ============================================================
   Customer Email Lookup - shared by the upgrade and downgrade forms
   ============================================================ */

// Customer mode has no Monday project id to search on, so the email is the
// only key the customer reliably knows. This fills the shop fields and the
// real product list from the mirrored Monday data.
//
// Every failure path leaves the form exactly as the customer left it: the
// lookup is an accelerator, never a gate. A wrong or unrecognised email is not
// an error condition -- CS briefs the customer directly.
//
// Shared rather than copied: the four fixes below (a stale project id cleared
// when the email stops matching, only lookup-written fields cleared, a retry
// allowed after a 503, focus moved when the revert link removes itself) were
// expensive to find, and a copy guarantees the next one lands in only one file.
function udmInitEmailLookup(options) {
    const opts = options || {};

    const $email        = $(opts.email);
    const $country      = $(opts.country);
    const $status       = $(opts.status);
    const $branchPicker = $(opts.branchPicker);
    const $branchList   = $(opts.branchList);

    // Optional: only the upgrade form has a manual product select to fall back
    // to. The downgrade form renders checkboxes from onProducts instead.
    const $list    = opts.productList       ? $(opts.productList)       : $();
    const $selWrap = opts.productSelectWrap ? $(opts.productSelectWrap) : $();
    const $select  = opts.productSelect     ? $(opts.productSelect)     : $();
    const $hidden  = opts.productHidden     ? $(opts.productHidden)     : $();

    const shopFields     = opts.shopFields     || {};
    const identityFields = opts.identityFields || {};
    const onProducts     = opts.onProducts     || function () {};
    const onChange       = opts.onChange       || function () {};

    let lookupTimer    = null;
    let lastQueried    = '';
    let inFlight       = null;
    // True only while the shop fields hold values this lookup wrote, so a
    // later clear can leave anything the customer typed by hand alone.
    let filledByLookup = false;
    // The shops a multi-branch match returned, awaiting the customer's choice.
    let matchedShops   = [];

    if (opts.disabled || !$email.length) return;

    function setStatus(html) {
        $status.html(html || '');
    }

    // Every selector this lookup is allowed to clear, as one jQuery set.
    function $shopFieldSet() {
        const sel = Object.keys(shopFields).map(k => shopFields[k]).filter(Boolean).join(', ');
        return sel ? $(sel) : $();
    }

    function $identityFieldSet() {
        const sel = Object.keys(identityFields).map(k => identityFields[k]).filter(Boolean).join(', ');
        return sel ? $(sel) : $();
    }

    // With no lookup result, the hidden field that actually submits must track
    // the manual select -- otherwise a customer who picks a plan by hand
    // submits an empty originalProduct.
    //
    // onChange runs after every change to the hidden field: on the upgrade form
    // it drives the Solo-to-Bundle gate and the comparison card, so without
    // this a section or card from a previous selection stays on screen while
    // validateForm has already stopped requiring its fields.
    function useManualSelect() {
        $list.addClass('d-none').empty();
        $selWrap.removeClass('d-none');
        $hidden.val($select.val() || '');
        onProducts([], null);
        onChange();
    }

    function useLookupProducts(subscriptions, shop) {
        if (!subscriptions.length) {
            useManualSelect();
            return;
        }

        $selWrap.addClass('d-none');
        $hidden.val(subscriptions.map(s => s.name).join(', '));
        onChange();

        // Names only -- no prices in the customer-facing list. The endpoint does
        // not return them, so there is nothing to render even if this changed.
        if ($list.length) {
            const html = subscriptions.map(s => `
            <div class="subscription-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <div class="sub-name">${escapeHtml(s.name)}</div>
                </div>
            </div>`).join('');

            $list.html(html).removeClass('d-none');
        }

        onProducts(subscriptions, shop);
    }

    // The shop name, owner name and product names come from Monday and are
    // rendered as HTML, so they are escaped here rather than trusted.
    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function fillShop(shop) {
        Object.keys(shopFields).forEach(function (key) {
            $(shopFields[key]).val(shop[key] || '');
        });
        // These are hidden inputs in customer mode and have always submitted
        // empty; populating them means the webhook payload finally carries a
        // real project id for a customer submission.
        Object.keys(identityFields).forEach(function (key) {
            $(identityFields[key]).val(shop[key] || '');
        });
        filledByLookup = true;
    }

    // Clears only what a lookup actually wrote. Clearing unconditionally would
    // discard a shop name, owner and phone the customer typed by hand -- the
    // lookup is an accelerator, so it may not destroy their own input.
    function clearShopFields() {
        if (!filledByLookup) return;
        $shopFieldSet().val('');
        $identityFieldSet().val('');
        filledByLookup = false;
    }

    // The Monday project id is the dangerous one to leave behind: a stale id
    // from a previous email would attach this request to the wrong shop in
    // Monday while the form shows the customer's new address.
    //
    // The flag is lowered too: the shop name, owner and phone are left on screen
    // for the customer to correct, which makes them theirs to keep. Leaving the
    // flag raised would let a later country change wipe fields they had since
    // edited by hand.
    function clearShopIdentity() {
        if (!filledByLookup) return;
        $identityFieldSet().val('');
        filledByLookup = false;
    }

    function revertToManual() {
        lastQueried = '';
        clearShopFields();
        hideBranchPicker();
        useManualSelect();
        setStatus('');
    }

    function hideBranchPicker() {
        $branchPicker.addClass('d-none');
        $branchList.empty();
        matchedShops = [];
    }

    // One owner often runs several branches under one email: 69 emails match
    // more than one shop, and in 15 of those the first by name has no products
    // while a sibling does. Picking one silently showed "Found <shop>" with an
    // empty product list and, worse, submitted that shop's Monday project id.
    function showBranchPicker(shops) {
        matchedShops = shops;

        const html = shops.map(function (s, i) {
            const n     = s.subscriptions ? s.subscriptions.length : 0;
            const count = n === 1 ? '1 current product' : n + ' current products';
            return `
                <label class="branch-option">
                    <input type="radio" name="branchChoice" value="${i}">
                    <span class="branch-option-body">
                        <span class="branch-option-name">${escapeHtml(s.shopName)}</span>
                        <span class="branch-option-meta">${count}</span>
                    </span>
                </label>`;
        }).join('');

        $branchList.html(html);
        $branchPicker.removeClass('d-none');
    }

    function applyShop(shop, stale) {
        fillShop(shop);
        useLookupProducts(shop.subscriptions || [], shop);

        const name = escapeHtml(shop.shopName);
        let msg = stale
            ? `<span class="els-stale"><i class="bi bi-exclamation-circle"></i> Using <strong>${name}</strong>. These details may be out of date; our team will confirm them.`
            : `<span class="els-found"><i class="bi bi-check-circle"></i> Using <strong>${name}</strong>.`;

        // A found shop with no products must say so. Silently showing the
        // hardcoded select is the "did the lookup work?" ambiguity this feature
        // exists to remove.
        if (!shop.subscriptions || shop.subscriptions.length === 0) {
            msg += ' We found no active products on it, so please choose below.';
        }

        msg += ' You can edit anything below.</span>'
             + '<a href="#" class="els-revert" id="emailLookupRevert">Not my shop?</a>';
        setStatus(msg);
    }

    function onFound(data) {
        const shops = (data.shops && data.shops.length) ? data.shops : [data.shop];

        if (shops.length > 1) {
            // Fill nothing yet: which branch is theirs decides the project id.
            showBranchPicker(shops);
            useManualSelect();
            setStatus('<span class="els-neutral"><i class="bi bi-diagram-3"></i> This email is used by '
                + shops.length + ' shops. Please choose which one is yours.</span>'
                + '<a href="#" class="els-revert" id="emailLookupRevert">None of these?</a>');
            return;
        }

        hideBranchPicker();
        applyShop(shops[0], data.stale);
    }

    function onNotFound() {
        // Deliberately not alarming: an unrecognised email is expected, and CS
        // reconciles it. Nothing the customer typed by hand is touched.
        //
        // The project id must go, though: this email matched no shop, so a stale
        // id from a previous match would attach the request to the wrong shop.
        clearShopIdentity();
        useManualSelect();
        setStatus('<span class="els-neutral">We couldn\'t prefill your details from this email. Please fill in the form and our team will confirm everything.</span>');
    }

    function runLookup() {
        const country = ($country.val() || '').trim();
        const email   = ($email.val() || '').trim();

        if (!country || !email || email.indexOf('@') === -1) {
            // Mid-edit, so no lookup can be in flight for what is on screen now.
            // Abort it and drop the project id: leaving either would let an older
            // response land, or submit a previous shop's id under a new address.
            if (inFlight) inFlight.abort();
            clearShopIdentity();
            return;
        }

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
                // Forget the key so the same address can be retried: a 503 or a
                // timeout should not kill the lookup until the email changes.
                lastQueried = '';
                // A failed lookup must be indistinguishable from no lookup, but
                // a previous match's project id cannot be left to submit.
                clearShopIdentity();
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
    $country.on('change', function () {
        revertToManual();
        runLookup();
    });

    // The select -> hidden mirroring itself lives in the form's DOM-ready block,
    // because the form depends on it with or without a lookup. This only has to
    // stop a stale manual choice overwriting a successful lookup result.
    $select.on('change', function () {
        if ($selWrap.hasClass('d-none')) {
            // A lookup is showing; its product list owns the hidden field.
            $(this).val('');
        }
    });

    // Choosing a branch is what fills the form, so the project id can only ever
    // be the shop the customer confirmed.
    $branchList.on('change', 'input[name="branchChoice"]', function () {
        const shop = matchedShops[parseInt($(this).val(), 10)];
        if (!shop) return;
        applyShop(shop, false);
    });

    $status.on('click', '#emailLookupRevert', function (e) {
        e.preventDefault();
        revertToManual();
        // This link lives inside the status area, which the revert empties, so
        // activating it destroys the focused element and focus falls to <body>.
        // Send it to the email field, which is what the customer edits next.
        $email.trigger('focus');
    });
}
```

- [ ] **Step 2: Replace the block in the upgrade controller**

In `modules/upgrade-downgrade/controllers/upgradeForm.js`, delete lines 300–581 — everything from the `// ===== Customer Email Lookup =====` comment through the `})();` that closes that IIFE, inclusive — and put this in their place:

```javascript
// ===== Customer Email Lookup =====
// The lookup itself lives in assets/js/emailLookup.js, shared with the
// downgrade form. What stays here is this form's wiring: which fields the
// lookup may fill and clear, and that every state change has to re-run
// updateSectionVisibility(), because the hidden #originalProduct field drives
// the Solo-to-Bundle gate and the comparison card.
udmInitEmailLookup({
    email:        '#emailAddress',
    country:      '#country',
    status:       '#emailLookupStatus',
    branchPicker: '#branchPicker',
    branchList:   '#branchPickerList',

    shopFields: {
        shopName:  '#shopName',
        ownerName: '#ownerName',
        phone:     '#phoneNumber',
    },

    identityFields: {
        shopId:  '#mondayProjectId',
        boardId: '#boardId',
    },

    productList:       '#customerProductList',
    productSelectWrap: '#customerProductSelectWrap',
    productSelect:     '#originalProductSelect',
    productHidden:     '#originalProduct',

    onChange: updateSectionVisibility,

    // Staff mode searches Monday by project id instead, and must not have its
    // fields overwritten by an email lookup.
    disabled: IS_STAFF,
});
```

Note the `disabled: IS_STAFF` option replaces the old `if (IS_STAFF) return;` guard that sat inside the IIFE at line 328.

- [ ] **Step 3: Load the shared file in the upgrade view**

In `modules/upgrade-downgrade/views/upgradeForm.php`, line 694 currently reads:

```html
<script src="../controllers/upgradeForm.js?v=2.0.0"></script>
```

Insert the shared file before it, and bump the controller's cache-busting version so a browser holding the old copy does not run a controller that calls a function it has not loaded:

```html
<script src="../assets/js/emailLookup.js?v=1.0.0"></script>
<script src="../controllers/upgradeForm.js?v=2.1.0"></script>
```

- [ ] **Step 4: Check the syntax of both files**

Run:

```bash
node --check modules/upgrade-downgrade/assets/js/emailLookup.js
node --check modules/upgrade-downgrade/controllers/upgradeForm.js
```

Expected: no output from either, exit code 0. If `node` is unavailable, load the upgrade form in a browser and confirm the console is free of `SyntaxError` and `udmInitEmailLookup is not defined`.

- [ ] **Step 5: Confirm the old block is entirely gone**

Run:

```bash
grep -c 'lookupByEmail.php' modules/upgrade-downgrade/controllers/upgradeForm.js
grep -c 'emailLookupRevert' modules/upgrade-downgrade/controllers/upgradeForm.js
```

Expected: `0` from both. A non-zero count means part of the IIFE survived the delete and there are now two lookups racing each other on the same fields.

- [ ] **Step 6: Verify the upgrade form behaves identically**

This is a refactor, so the gate is "nothing changed". In a browser at `views/upgradeForm.php?mode=customer`, with the mirror populated, confirm each of the following. Every one corresponds to a fix the extracted code carries, so a regression here is a regression of something already paid for.

| Check | Expected |
| --- | --- |
| Pick a country, type a known single-shop email, blur | Shop name, contact person and phone fill; status reads "Using **<shop>**"; the product list shows that shop's products; the manual select is hidden |
| Type a known multi-branch email | Branch picker appears, nothing is filled yet, status says how many shops; choosing a branch fills the form |
| Type an unknown email | Neutral "We couldn't prefill" message; **nothing you typed by hand is cleared**; the manual select is showing |
| Type a known email, then change the email to an unknown one | `#mondayProjectId` and `#boardId` are empty (check in devtools); shop name is left on screen to correct |
| Type a shop name by hand, with no lookup ever succeeding, then clear the email | Your typed shop name is **still there** |
| Click "Not my shop?" | Fields clear, status empties, and focus lands on the email field (not `<body>`) |
| With devtools throttled to offline, trigger a lookup, restore the network, blur the same email again | A second request goes out — the retry is not blocked by the remembered key |
| Change the country after a successful lookup | The result is discarded and a fresh lookup runs for the new country |
| Choose a plan from the manual select with no lookup | `#originalProduct` holds that value; the comparison card and any gated section update |
| Load `?mode=staff` | No lookup fires on email blur; the Monday project search still works |

- [ ] **Step 7: Commit**

```bash
git add modules/upgrade-downgrade/assets/js/emailLookup.js \
        modules/upgrade-downgrade/controllers/upgradeForm.js \
        modules/upgrade-downgrade/views/upgradeForm.php
git commit -m "refactor(upgrade-downgrade): share the email lookup between both forms

The downgrade form needs the same customer email lookup, and the block carries
four fixes that were expensive to find: clearing a stale Monday project id when
the email stops matching, clearing only the fields the lookup itself wrote,
allowing a retry after a 503, and moving focus when the revert link removes
itself. Copying it would guarantee the next fix lands in one file and not the
other.

Extracted rather than copied, with its four couplings to the upgrade form
turned into options: the field names it fills, the identity fields it may
clear, the optional product select it falls back to, and an onChange callback
where updateSectionVisibility used to be called directly.

No behaviour change intended. The upgrade form's lookup was re-verified against
all ten of its failure paths before this landed.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 4: Reorder the Business Information fields (CS item 1)

More than a reorder: the fields CS wants in one ordered list are split across two sections, and `country` exists only in staff mode while the lookup needs it in both.

**Files:**
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php:106-172` (the Business Information and Contact Information sections)
- Test: manual, over HTTP — see Step 3

**Interfaces:**
- Consumes: nothing.
- Produces: `#country` present in both modes; `#businessType` added; the markup ids `#emailLookupStatus`, `#branchPicker`, `#branchPickerList` that Task 5 wires up. Field ids `#shopName`, `#tradingName`, `#contactPerson`, `#emailAddress`, `#mobileNumber`, `#address` are unchanged, so nothing downstream breaks.

- [ ] **Step 1: Replace both sections**

In `modules/upgrade-downgrade/views/downgradeForm.php`, replace lines 106–172 — from the `<!-- ========== SECTION: Business Information ========== -->` comment through the `</div>` that closes the Contact Information section — with the following. The Contact Information section disappears; its fields are absorbed above.

```php
                        <!-- ========== SECTION: Business Information ========== -->
                        <!-- One ordered list per CS item 1. Country is in both
                             modes now, not staff only: the email lookup keys on
                             country + email, so a customer with no country
                             cannot be looked up at all. -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-building"></i> Business Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="">-- Select Country --</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="TH">Thailand</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your country.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="businessType" class="form-label">Business Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="businessType" name="businessType" required>
                                        <option value="">-- Select Business Type --</option>
                                        <option value="Restaurant">Restaurant</option>
                                        <option value="Massage">Massage</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your business type.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shopName" class="form-label">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="shopName" name="shopName" placeholder="e.g. The Green Kitchen" required>
                                    <div class="invalid-feedback">Please enter the business name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tradingName" class="form-label">Trading Name</label>
                                    <input type="text" class="form-control" id="tradingName" name="tradingName" placeholder="e.g. Green Kitchen LLC">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="contactPerson" class="form-label">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactPerson" name="contactPerson" placeholder="e.g. Jane Smith" required>
                                <div class="invalid-feedback">Please enter the contact person name.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="e.g. jane@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                    <!-- The lookup writes its result here. -->
                                    <div id="emailLookupStatus" class="email-lookup-status" aria-live="polite"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mobileNumber" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="mobileNumber" name="mobileNumber" placeholder="e.g. +1 (555) 123-4567" required>
                                    <div class="invalid-feedback">Please enter the phone number.</div>
                                </div>
                            </div>

                            <!-- One email can belong to several branches, so the
                                 customer picks which shop is theirs before
                                 anything is filled. -->
                            <div id="branchPicker" class="branch-picker mb-3 d-none">
                                <label class="form-label">Which of these is your shop?</label>
                                <div id="branchPickerList" class="branch-picker-list" role="radiogroup" aria-label="Choose your shop"></div>
                            </div>

                            <!-- CS did not ask for address to go, and it is
                                 currently required, so it stays, after the
                                 ordered list. -->
                            <div class="mb-3">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="e.g. 123 Main St, Los Angeles, CA 90001" required>
                                <div class="invalid-feedback">Please enter the business address.</div>
                            </div>

                            <?php if ($mode === 'staff'): ?>
                            <!-- Staff keeps the project search and gains the
                                 lookup. A staff member taking a cancellation
                                 call may have the shop ID to hand rather than
                                 the email, so removing this would make their
                                 job harder, not easier. -->
                            <div class="mb-3 position-relative">
                                <label for="mondayProjectId" class="form-label">Monday Project / Shop ID <small class="text-muted">(Staff Reference)</small></label>
                                <input type="text" class="form-control" id="mondayProjectId" name="mondayProjectId" placeholder="Type shop name or ID to search..." autocomplete="off">
                                <input type="hidden" id="boardId" name="boardId" value="">
                                <div id="projectSearchDropdown" class="project-search-dropdown"></div>
                            </div>
                            <?php else: ?>
                            <!-- Customer mode has no search, but the lookup
                                 fills these, and the webhook payload reads
                                 them. Without these inputs a customer
                                 submission carries no project id at all. -->
                            <input type="hidden" id="mondayProjectId" name="mondayProjectId" value="">
                            <input type="hidden" id="boardId" name="boardId" value="">
                            <?php endif; ?>
                        </div>
```

- [ ] **Step 2: Check the PHP parses**

Run: `php -l modules/upgrade-downgrade/views/downgradeForm.php`

Expected: `No syntax errors detected in modules/upgrade-downgrade/views/downgradeForm.php`

- [ ] **Step 3: Verify both modes render**

Load `views/downgradeForm.php?mode=customer` and `views/downgradeForm.php?mode=staff` in a browser.

| Check | Expected |
| --- | --- |
| Customer mode field order | Country, Business Type, Business Name, Trading Name, Contact Person, Email Address, Phone Number, then Address |
| Customer mode | No "Contact Information" heading anywhere |
| Customer mode | `#mondayProjectId` and `#boardId` exist as hidden inputs (check in devtools) |
| Staff mode | Same order, plus the Monday Project / Shop ID search after Address |
| Both modes | The Country select is present and marked required |
| Both modes | `#emailLookupStatus` and `#branchPicker` exist, the latter carrying `d-none` |

- [ ] **Step 4: Confirm the staff project search still works**

In staff mode, pick a country and type part of a known shop name into the Monday Project / Shop ID field. The dropdown must appear and selecting a result must fill the field. The search's `#country` change handler is in `controllers/downgradeForm.js:154`, and it still finds the select — which has moved but kept its id.

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/views/downgradeForm.php
git commit -m "feat(upgrade-downgrade): one ordered Business Information section

CS item 1. The fields they want in one list were split across Business
Information and Contact Information, so Contact Information is absorbed and
disappears.

Two fields change availability rather than position. Country was staff-only and
is now in both modes, because the email lookup keys on country plus email and a
customer with no country cannot be looked up at all. Business Type is new,
matching the upgrade form, and item 2c's keep-list is chosen by it.

Customer mode gains hidden mondayProjectId and boardId inputs for the lookup to
fill, so a customer submission can finally carry a real project id.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 5: Wire the email lookup into the downgrade form

**Files:**
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js:1-3` (add the mode constants) and `:182-210` (the DOM-ready block)
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php:565-567` (script tags)
- Test: manual, in the browser — see Step 5

**Interfaces:**
- Consumes: `udmInitEmailLookup(options)` from Task 3.
- Produces:
  - `FORM_MODE` and `IS_STAFF` constants, matching `controllers/upgradeForm.js:8-9`.
  - `udmLookupProducts` — a module-level array of `{name, ...}` subscription objects from the last successful lookup, or `[]`. Task 6 renders the checkboxes from it.
  - `udmRenderProductList()` — a hook Task 6 replaces with the real renderer. A stub here, so this task is testable on its own.

- [ ] **Step 1: Add the mode constants**

At the top of `modules/upgrade-downgrade/controllers/downgradeForm.js`, after the existing header comment block (lines 1–3), insert:

```javascript
// ===== Form Mode =====
// 'customer' hides the staff-only fields (Monday project search, internal
// details, submitted-by), so those must not be validated as required.
const FORM_MODE = document.body.dataset.mode || 'customer';
const IS_STAFF = (FORM_MODE === 'staff');
```

`<body data-mode="...">` is already emitted at `views/downgradeForm.php:31`, so there is nothing to add in the view for this.

- [ ] **Step 2: Add the lookup state and wiring**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, immediately before the `$(function () {` DOM-ready block at line 182, insert:

```javascript
// ===== Customer Email Lookup =====
// The lookup lives in assets/js/emailLookup.js, shared with the upgrade form.
//
// Both modes get it. CS asked for both sides to work the same way, which means
// "staff should not have to type what the email can fill" -- not "remove the
// project search". Staff keeps both, and with both active they can disagree:
// staff searches shop X, then an email fills shop Y. Last action wins, and the
// status line says which shop is currently attached, so what will be submitted
// is never ambiguous.

// The products from the last successful lookup. The product checkbox list is
// rendered from this, and it is the empty array whenever no lookup has
// succeeded -- which is what makes the "enter your email first" prompt
// distinguishable from "this shop has no products".
let udmLookupProducts = [];

// The shop from the last successful lookup, or null. The post-submit Araya
// redirect reads its name.
let udmLookupShop = null;

udmInitEmailLookup({
    email:        '#emailAddress',
    country:      '#country',
    status:       '#emailLookupStatus',
    branchPicker: '#branchPicker',
    branchList:   '#branchPickerList',

    // The downgrade form's field names differ from the upgrade form's: contact
    // person rather than owner name, mobile number rather than phone number.
    shopFields: {
        shopName:  '#shopName',
        ownerName: '#contactPerson',
        phone:     '#mobileNumber',
    },

    identityFields: {
        shopId:  '#mondayProjectId',
        boardId: '#boardId',
    },

    // No productList/productSelect options: this form has no manual product
    // select to fall back to. It renders checkboxes from onProducts instead.
    onProducts: function (subscriptions, shop) {
        udmLookupProducts = subscriptions || [];
        udmLookupShop     = shop || null;
        udmRenderProductList();
    },

    onChange: updateSectionVisibility,
});
```

- [ ] **Step 3: Add the renderer stub**

Immediately after the block from Step 2, insert the stub Task 6 replaces. It exists now so this task can be verified on its own, and so `onProducts` is not calling an undefined function.

```javascript
// Replaced in full by Task 6. The lookup calls this after every state change,
// so it has to exist before the product list is built.
function udmRenderProductList() {
    // Task 6 renders the checkboxes here.
}
```

- [ ] **Step 4: Load the shared file in the view**

In `modules/upgrade-downgrade/views/downgradeForm.php`, the script tags at lines 565–567 currently read:

```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/downgradeForm.js?v=1.0.0"></script>
```

Change to:

```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/js/emailLookup.js?v=1.0.0"></script>
<script src="../controllers/downgradeForm.js?v=1.1.0"></script>
```

- [ ] **Step 5: Verify the lookup runs on the downgrade form**

Run `node --check modules/upgrade-downgrade/controllers/downgradeForm.js` first; expect no output.

Then, in a browser at `views/downgradeForm.php?mode=customer`:

| Check | Expected |
| --- | --- |
| Pick a country, type a known single-shop email, blur | Business Name, Contact Person and Phone Number fill; the status line reads "Using **<shop>**" |
| In the console, `udmLookupProducts` | An array of that shop's products |
| A known multi-branch email | The branch picker appears; choosing one fills the form |
| An unknown email | Neutral message; nothing you typed is cleared; `udmLookupProducts` is `[]` |
| A known email, then change it to an unknown one | `#mondayProjectId` is empty in devtools |
| `?mode=staff`: type a known email and blur | The lookup fills the fields **and** the project search still works |
| `?mode=staff`: search a shop, then type a different shop's email | The email's shop wins, and the status line names it |

- [ ] **Step 6: Commit**

```bash
git add modules/upgrade-downgrade/controllers/downgradeForm.js \
        modules/upgrade-downgrade/views/downgradeForm.php
git commit -m "feat(upgrade-downgrade): look the customer up by email on the downgrade form

The form had no lookup at all: it asked the customer to type their shop name
and offered a fixed three-option radio group unrelated to what they bought. The
conditional revisions CS asked for all key off which product is being
cancelled, so they are impossible without this.

Both modes get it. CS asked for both sides to work the same way, which means
staff should not have to type what the email can fill -- not that the project
search should go. With both active they can disagree, so last action wins and
the status line names the shop currently attached.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 6: Type of Request and the product checkbox list (CS items 2, 2a)

**Files:**
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php:174-210` (the Type of Request section)
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js:43-54` (`updateSectionVisibility`) and the `udmRenderProductList` stub from Task 5
- Test: manual, in the browser — see Step 6

**Interfaces:**
- Consumes: `udmLookupProducts` and `udmRenderProductList()` from Task 5; `GET assets/API/classifyProducts.php` from Task 2.
- Produces:
  - Markup: `#productSelectGroup`, `#productCheckboxList`, `#productListPrompt`, `#productListError`, and checkboxes `name="cancelledProducts[]"`.
  - `udmProductTags` — an object mapping a product name to its tag array, populated from the endpoint.
  - `udmCheckedTags()` — returns the set of tags across all checked products, as an object `{araya: true, ...}`. Tasks 7 and 8 read it.
  - `recomputeConditionalState()` — the single entry point every conditional section is driven from. Tasks 7 and 8 extend it.

- [ ] **Step 1: Replace the Type of Request section**

In `modules/upgrade-downgrade/views/downgradeForm.php`, replace lines 174–210 — the whole `<!-- ========== SECTION: Type of Request ========== -->` section, including the `#downgradeTargetGroup` block — with:

```php
                        <!-- ========== SECTION: Type of Request ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-arrow-down-circle"></i> Type of Request <span class="text-danger">*</span></h5>
                            <div class="mb-3">
                                <div class="radio-card-group-vertical">
                                    <div class="radio-card-v">
                                        <input type="radio" name="requestType" id="reqOtherProduct" value="Unsubscribe specific product" required>
                                        <label for="reqOtherProduct"><i class="bi bi-box-arrow-down"></i> Unsubscribe specific product</label>
                                    </div>
                                    <div class="radio-card-v radio-card-v-danger">
                                        <input type="radio" name="requestType" id="reqUnsubscribe" value="Unsubscribe all services">
                                        <label for="reqUnsubscribe"><i class="bi bi-x-octagon"></i> Unsubscribe all services</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="requestTypeError" style="display:none!important;"></div>
                            </div>

                            <!-- The shop's real products, from the email lookup.
                                 This replaces a fixed three-option radio group
                                 ("website only" / "ordering only" / "marketing
                                 only") that bore no relation to what the
                                 customer actually bought. -->
                            <div class="mb-3" id="productSelectGroup" style="display:none;">
                                <label class="form-label">Which products would you like to unsubscribe from? <span class="text-danger">*</span></label>

                                <!-- Shown until a lookup succeeds. An empty box
                                     here would read as "you have no products",
                                     which is a different and alarming claim. -->
                                <div id="productListPrompt" class="product-list-prompt">
                                    <i class="bi bi-envelope-at"></i>
                                    Enter your email address above and we will show the products on your account.
                                </div>

                                <div id="productCheckboxList" class="product-checkbox-list"></div>
                                <div class="invalid-feedback d-block" id="productListError" style="display:none!important;"></div>
                            </div>
                        </div>
```

- [ ] **Step 2: Add the list styles**

Append to `modules/upgrade-downgrade/assets/css/downgradeForm.css`:

```css
/* ===== Product checkbox list (CS item 2a) ===== */
.product-list-prompt {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    border: 1px dashed #c9ced6;
    border-radius: 10px;
    background: #f8f9fb;
    color: #5a6372;
    font-size: 0.9rem;
}

.product-checkbox-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-checkbox-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border: 1px solid #e3e6ec;
    border-radius: 10px;
    background: #fff;
    transition: border-color 0.2s ease, background 0.2s ease;
}

.product-checkbox-item:hover {
    border-color: #0d6efd;
    background: #f7faff;
}

.product-checkbox-item input[type="checkbox"] {
    margin-top: 3px;
    flex-shrink: 0;
}

.product-checkbox-item label {
    margin: 0;
    cursor: pointer;
    font-size: 0.93rem;
}

.product-list-empty {
    padding: 14px 16px;
    border: 1px solid #ffe2b8;
    border-radius: 10px;
    background: #fff8ec;
    color: #8a5a00;
    font-size: 0.9rem;
}
```

- [ ] **Step 3: Replace `updateSectionVisibility` and the renderer stub**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, replace the `updateSectionVisibility` function at lines 43–54 with:

```javascript
// ===== Section Visibility =====
// The product list appears only for "Unsubscribe specific product". Unchecking
// everything on the way out matters: a stale checked box would otherwise keep
// an Araya section on screen after the customer switched to cancelling
// everything.
function updateSectionVisibility() {
    const requestType  = $('input[name="requestType"]:checked').val();
    const isSpecific   = (requestType === 'Unsubscribe specific product');

    if (isSpecific) {
        $('#productSelectGroup').slideDown(200);
    } else {
        $('#productSelectGroup').slideUp(200);
        $('#productCheckboxList input[name="cancelledProducts[]"]').prop('checked', false);
    }

    recomputeConditionalState();
}
```

Then replace the `udmRenderProductList` stub from Task 5 with the real implementation, and add the state helpers alongside it:

```javascript
// Tags per product name, from assets/API/classifyProducts.php. The map that
// produces them lives in config.php, which the browser cannot read, so this is
// asked for rather than computed here -- two copies of the rules would drift.
let udmProductTags = {};

// The lookup calls this after every state change: a successful match, a branch
// choice, a revert, a failure. It renders from udmLookupProducts wholesale
// rather than patching what is on screen, so there is no path on which a
// previous shop's products survive.
function udmRenderProductList() {
    const $list   = $('#productCheckboxList');
    const $prompt = $('#productListPrompt');

    if (!udmLookupProducts.length) {
        // No lookup has succeeded, so say what to do rather than showing an
        // empty box, which reads as "you have no products".
        $list.empty();
        $prompt.show();
        recomputeConditionalState();
        return;
    }

    $prompt.hide();

    const html = udmLookupProducts.map(function (product, i) {
        const id   = 'cancelProduct' + i;
        const name = udmEscapeHtml(product.name);
        return `
            <div class="product-checkbox-item">
                <input type="checkbox" id="${id}" name="cancelledProducts[]" value="${name}">
                <label for="${id}">${name}</label>
            </div>`;
    }).join('');

    $list.html(html);

    udmFetchProductTags(udmLookupProducts.map(p => p.name));
    recomputeConditionalState();
}

// Product names come from Monday and are rendered as HTML, so they are escaped
// rather than trusted. Same guard as the shared lookup's.
function udmEscapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

// Classification drives which conditional sections exist, so a failure here
// must fail safe: no tags means no conditional section fires, which is the same
// state as a shop whose products are all add-ons. The customer can still
// submit.
function udmFetchProductTags(names) {
    if (!names.length) {
        udmProductTags = {};
        recomputeConditionalState();
        return;
    }

    $.getJSON('../assets/API/classifyProducts.php', { names: names })
        .done(function (data) {
            udmProductTags = (data && data.tags) ? data.tags : {};
        })
        .fail(function () {
            udmProductTags = {};
        })
        .always(function () {
            recomputeConditionalState();
        });
}

// The tags across every currently checked product, as a set.
function udmCheckedTags() {
    const tags = {};

    $('#productCheckboxList input[name="cancelledProducts[]"]:checked').each(function () {
        const name = $(this).val();
        (udmProductTags[name] || []).forEach(function (tag) {
            tags[tag] = true;
        });
    });

    return tags;
}

// ===== Conditional State =====
// Every conditional section is recomputed from the currently checked boxes,
// wholesale, on every change. Never incremental add/remove: ticking and then
// unticking a product must not leave a section behind, which is exactly the
// failure the upgrade form hit when updateSectionVisibility was not called on
// every change to the field that drives it.
//
// Tasks 7 and 8 extend this. It is the only place that reads udmCheckedTags().
function recomputeConditionalState() {
    const tags = udmCheckedTags();
    const hasAraya      = tags.araya      === true;
    const hasPlanBundle = tags.plan_bundle === true;

    // Task 7 adds the Araya reasons, warning, conditions block and the
    // feedback-field hiding here.
    // Task 8 adds the plan-bundle keep-list here.

    // Referenced so the linter and the next reader both see that these are the
    // two signals this function exists to compute.
    void hasAraya;
    void hasPlanBundle;
}
```

- [ ] **Step 4: Recompute when a product is ticked**

In the DOM-ready block at `controllers/downgradeForm.js:182`, after the existing `$('input[name="requestType"]').on('change', updateSectionVisibility);` line, add:

```javascript
    // Delegated: the checkboxes are rendered after a lookup, so they do not
    // exist when this binds.
    $('#productCheckboxList').on('change', 'input[name="cancelledProducts[]"]', recomputeConditionalState);
```

- [ ] **Step 5: Check the syntax**

Run:

```bash
node --check modules/upgrade-downgrade/controllers/downgradeForm.js
php -l modules/upgrade-downgrade/views/downgradeForm.php
```

Expected: no output from the first; `No syntax errors detected` from the second.

- [ ] **Step 6: Verify the product list**

In a browser at `views/downgradeForm.php?mode=customer`:

| Check | Expected |
| --- | --- |
| Choose "Unsubscribe specific product" before any lookup | The section opens showing the "Enter your email address above" prompt — **not** an empty box |
| Then look up a known shop with products | The prompt disappears; one checkbox per product, labelled with the real product name |
| In the console, `udmProductTags` | An object mapping each product name to a tag array |
| Choose "Unsubscribe all services" | The product section closes and every checkbox is unchecked (verify in devtools) |
| Switch back to "Unsubscribe specific product" | The checkboxes are all clear, none remembered |
| Look up shop A, tick a product, then look up shop B | Shop B's products replace A's and nothing is ticked |
| A shop with a product name containing `<` or `&` | The name renders as text, with no broken markup |
| Both radio labels | "Unsubscribe specific product" and "Unsubscribe all services"; the old "Downgrade to other product" wording is gone |
| The old second-level radio group | Absent — no "website service only" / "online ordering only" / "marketing only" |

- [ ] **Step 7: Commit**

```bash
git add modules/upgrade-downgrade/views/downgradeForm.php \
        modules/upgrade-downgrade/controllers/downgradeForm.js \
        modules/upgrade-downgrade/assets/css/downgradeForm.css
git commit -m "feat(upgrade-downgrade): offer the shop's real products to unsubscribe

CS items 2 and 2a. \"Downgrade to other product\" becomes \"Unsubscribe
specific product\", and the fixed three-option radio group it revealed is
replaced by the shop's actual products from the email lookup. The old options
bore no relation to what the customer had bought.

Before a lookup has run the list shows a prompt to enter the email address, not
an empty box: an empty box reads as \"you have no products\", which is a
different and alarming claim.

recomputeConditionalState is introduced here as the single entry point every
conditional section will be driven from, recomputing from the checked boxes
wholesale. Incremental add/remove on each click is how the upgrade form left a
stale section on screen.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 7: Araya conditionals (CS items 2b, 3, 3a, 4, 5)

Five CS items, one signal: an Araya product is checked. They ship together because they are all driven by the same line in `recomputeConditionalState()`, and splitting them would mean touching that function five times.

Item 5 is where this task can ship broken: the two fields it hides are `required`, and hiding a required field without dropping the attribute makes the form permanently unsubmittable.

**Files:**
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php` — the Reason section (lines 272–322), the effective-date section (lines 239–270), the Additional Details section (lines 340–386), and the `#modalImportantInfo` modal (lines 490–523)
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js` — `recomputeConditionalState()`
- Test: manual, in the browser — see Step 7

**Interfaces:**
- Consumes: `recomputeConditionalState()` and `udmCheckedTags()` from Task 6.
- Produces: markup ids `#arayaReasons`, `#arayaImmediateWarning`, `#arayaConditions`, `#improvementSuggestionGroup`, `#overallRatingGroup`. Task 9's validation reads the same visibility state.

- [ ] **Step 1: Rename the Immediately option and add the Araya warning (items 3, 3a)**

In `modules/upgrade-downgrade/views/downgradeForm.php`, the effective-date radio at lines 243–246 currently reads:

```php
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effImmediate" value="Immediately" required>
                                        <label for="effImmediate"><i class="bi bi-lightning-charge"></i> Immediately</label>
                                    </div>
```

Replace with:

```php
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effImmediate" value="Stop the service now" required>
                                        <label for="effImmediate"><i class="bi bi-lightning-charge"></i> Stop the service now &nbsp;<small class="text-muted">(billing continues until the end of your notice period)</small></label>
                                    </div>
```

Then, immediately after the `#customDateGroup` block that closes at line 267, insert the Araya warning:

```php
                                <!-- Item 3a: only when stopping now AND an AI
                                     Receptionist product is being cancelled. -->
                                <div class="alert alert-warning mt-3 d-none" id="arayaImmediateWarning">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Your AI Receptionist will stop answering calls right away and any remaining minutes will be forfeited. Billing continues until the end of your 30-day notice period.
                                </div>
```

- [ ] **Step 2: Add the five Araya reasons (item 2b)**

In the Reason for Downgrade/Unsubscription section, immediately after the `#reasonOther` checkbox block that closes at line 315 (the `</div>` ending the checkbox group), insert:

```php
                            <!-- Item 2b: appended only when an AI Receptionist
                                 product is checked. These five are specific to
                                 it and would be noise otherwise. -->
                            <div class="d-none" id="arayaReasons">
                                <div class="checkbox-card-group">
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="reasonArayaExpensive" name="reasons[]" value="Too expensive for the value we get">
                                        <label for="reasonArayaExpensive"><i class="bi bi-cash-stack"></i> Too expensive for the value we get</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="reasonArayaCalls" name="reasons[]" value="Not enough calls to justify the cost">
                                        <label for="reasonArayaCalls"><i class="bi bi-telephone-x"></i> Not enough calls to justify the cost</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="reasonArayaPerform" name="reasons[]" value="The service didn't perform as we expected">
                                        <label for="reasonArayaPerform"><i class="bi bi-graph-down-arrow"></i> The service didn't perform as we expected</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="reasonArayaBooking" name="reasons[]" value="Problems with the booking system">
                                        <label for="reasonArayaBooking"><i class="bi bi-calendar-x"></i> Problems with the booking system</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="reasonArayaStaff" name="reasons[]" value="We have staff answering calls now">
                                        <label for="reasonArayaStaff"><i class="bi bi-people-fill"></i> We have staff answering calls now</label>
                                    </div>
                                </div>
                            </div>
```

Note these use `name="reasons[]"`, the same name as the existing reasons, so they need no payload change and the existing "at least one reason" validation already counts them.

- [ ] **Step 3: Wrap the two feedback fields so they can be hidden (item 5)**

In the Additional Details section, the improvement textarea at lines 343–346 currently reads:

```php
                            <div class="mb-3">
                                <label for="improvementSuggestion" class="form-label">What could we improve to keep you with us?</label>
                                <textarea class="form-control" id="improvementSuggestion" name="improvementSuggestion" rows="3" placeholder="Your feedback helps us improve our services..."></textarea>
                            </div>
```

Give it an id so it can be hidden as a unit:

```php
                            <!-- Item 5: hidden when an AI Receptionist product
                                 is being cancelled, because the Araya feedback
                                 form asks this too. -->
                            <div class="mb-3" id="improvementSuggestionGroup">
                                <label for="improvementSuggestion" class="form-label">What could we improve to keep you with us?</label>
                                <textarea class="form-control" id="improvementSuggestion" name="improvementSuggestion" rows="3" placeholder="Your feedback helps us improve our services..."></textarea>
                            </div>
```

Then the star rating block at lines 373–383 currently reads:

```php
                            <div class="mb-3">
                                <label class="form-label">Rate your overall experience with Local For You: (1–5 stars)</label>
                                <div class="star-rating-widget" id="overallRatingWidget">
```

Wrap it the same way — replace that opening `<div class="mb-3">` with:

```php
                            <!-- Item 5, and removed outright in staff mode:
                                 a staff member must not rate the customer's
                                 experience on their behalf. -->
                            <?php if ($mode === 'customer'): ?>
                            <div class="mb-3" id="overallRatingGroup">
                                <label class="form-label">Rate your overall experience with Local For You: (1–5 stars)</label>
                                <div class="star-rating-widget" id="overallRatingWidget">
```

and add `<?php endif; ?>` immediately after the `</div>` that closes that block (after the `<input type="hidden" name="overallRating" id="overallRating" value="">` line at 383 and its closing `</div>` at 384).

Note: neither field carries a `required` attribute in the current markup — verified at lines 345 and 383. The *contact preference* radio group at line 352 does, and Task 9 handles it. So item 5's "hidden required field" trap is live for `contactPreference`, not for these two. Hide these two anyway, as CS asked, and keep the required-attribute discipline where it actually bites.

- [ ] **Step 4: Add the Araya conditions to the info modal (item 4)**

In `modules/upgrade-downgrade/views/downgradeForm.php`, inside the `#modalImportantInfo` modal body (which begins after line 494), insert the Araya block as the first child of the modal body, so it reads first when it is visible:

```php
                <!-- Item 4: shown only when an AI Receptionist product is
                     being cancelled. -->
                <div class="info-card info-downgrade mb-3 d-none" id="arayaConditions">
                    <h6><i class="bi bi-robot"></i> AI Receptionist — Cancellation Conditions</h6>
                    <ul class="mb-0">
                        <li>Cancellation takes effect at the end of your 30-day notice period.</li>
                        <li>Your included minute allowance stays available until that date.</li>
                        <li>Any remaining balance is not refunded or carried over.</li>
                        <li>Overage minutes used before that date appear on your final invoice.</li>
                        <li>Your other services are unaffected by cancelling the AI Receptionist.</li>
                    </ul>
                </div>
```

- [ ] **Step 5: Drive all five from `recomputeConditionalState()`**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, replace the body of `recomputeConditionalState()` from Task 6 with:

```javascript
function recomputeConditionalState() {
    const tags = udmCheckedTags();
    const hasAraya      = tags.araya       === true;
    const hasPlanBundle = tags.plan_bundle === true;

    // --- Item 2b: five Araya-specific reasons ---
    // Unchecking them on the way out is what keeps the payload honest: a reason
    // specific to the AI Receptionist must not be submitted by someone who has
    // since switched to cancelling something else.
    $('#arayaReasons').toggleClass('d-none', !hasAraya);
    if (!hasAraya) {
        $('#arayaReasons input[name="reasons[]"]').prop('checked', false);
    }

    // --- Item 3a: the stop-now warning, only when both conditions hold ---
    const stoppingNow = $('#effImmediate').is(':checked');
    $('#arayaImmediateWarning').toggleClass('d-none', !(hasAraya && stoppingNow));

    // --- Item 4: the Araya conditions block in the sidebar modal ---
    $('#arayaConditions').toggleClass('d-none', !hasAraya);

    // --- Item 5: hide the two feedback questions the Araya form also asks ---
    //
    // Hiding alone is not enough where a field is required: a hidden required
    // field fails validation with nothing visible for the customer to fix,
    // which makes the form permanently unsubmittable. Neither of these two
    // carries required today, but the attribute is dropped and restored
    // explicitly so that adding required to the markup later cannot
    // reintroduce that trap silently.
    $('#improvementSuggestionGroup').toggleClass('d-none', hasAraya);
    $('#overallRatingGroup').toggleClass('d-none', hasAraya);

    if (hasAraya) {
        $('#improvementSuggestion').removeAttr('required').val('');
        $('#overallRating').removeAttr('required').val('');
        udmResetStars('overallRatingWidget', 'overallRatingLabel');
    }

    // Task 8 adds the plan-bundle keep-list here.
    void hasPlanBundle;
}

// Clearing #overallRating leaves the stars lit, because the widget's state
// lives in CSS classes rather than in the input. Hiding the group makes that
// invisible, but unchecking the Araya product brings the group back still
// showing a rating the field no longer holds.
function udmResetStars(widgetId, labelId) {
    const widget = document.getElementById(widgetId);
    if (!widget) return;

    widget.querySelectorAll('.star').forEach(function (s) {
        s.classList.remove('active', 'selected');
    });

    const labelEl = labelId ? document.getElementById(labelId) : null;
    if (labelEl) labelEl.textContent = '';
}
```

- [ ] **Step 6: Recompute when the effective date changes**

Item 3a depends on the effective-date choice as well as the checked products, so that radio group has to drive the recompute too. In the DOM-ready block, the existing effective-date handler at lines 188–194 reads:

```javascript
    $('input[name="effectiveDate"]').on('change', function () {
        if ($('#effCustomDate').is(':checked')) {
            $('#customDateGroup').slideDown(200);
        } else {
            $('#customDateGroup').slideUp(200).find('input').val('');
        }
    });
```

Add the recompute call at the end of that handler:

```javascript
    $('input[name="effectiveDate"]').on('change', function () {
        if ($('#effCustomDate').is(':checked')) {
            $('#customDateGroup').slideDown(200);
        } else {
            $('#customDateGroup').slideUp(200).find('input').val('');
        }
        // Item 3a's warning depends on this choice as well as on the checked
        // products, so it has to be recomputed here too.
        recomputeConditionalState();
    });
```

- [ ] **Step 7: Verify every Araya conditional**

Run `node --check modules/upgrade-downgrade/controllers/downgradeForm.js` and `php -l modules/upgrade-downgrade/views/downgradeForm.php` first.

The mirror holds only two Araya rows, so a real shop is unlikely to be available to test with. Force the state from the console instead:

```javascript
udmProductTags['SOME PRODUCT NAME'] = ['araya'];
$('#productCheckboxList input[name="cancelledProducts[]"]').first().prop('checked', true);
recomputeConditionalState();
```

| Check | Expected |
| --- | --- |
| With an Araya product checked | The five Araya reasons appear in the Reason section |
| Uncheck it | The five disappear **and** any of them that were ticked are unchecked (verify in devtools) |
| Araya checked, "Stop the service now" selected | `#arayaImmediateWarning` is visible |
| Araya checked, "End of current billing cycle" selected | The warning is hidden |
| No Araya, "Stop the service now" selected | The warning is hidden |
| Araya checked, open the Information modal from the sidebar | The "AI Receptionist — Cancellation Conditions" card is there, with all five bullets |
| No Araya, open the modal | That card is absent |
| Araya checked | "What could we improve..." and the 1–5 rating are both hidden |
| Set a 4-star rating, then check an Araya product, then uncheck it | The rating group comes back with **no stars lit** and `#overallRating` empty |
| Araya checked, then submit | The form submits — it is not blocked by a hidden field |
| The Immediately option's label | Reads "Stop the service now", with the billing helper text |

- [ ] **Step 8: Commit**

```bash
git add modules/upgrade-downgrade/views/downgradeForm.php \
        modules/upgrade-downgrade/controllers/downgradeForm.js
git commit -m "feat(upgrade-downgrade): condition the form on cancelling the AI Receptionist

CS items 2b, 3, 3a, 4 and 5. Five items, one signal -- an Araya product is
checked -- so they are driven from one line of recomputeConditionalState rather
than five separate toggles that could disagree.

Item 5 hides the two feedback questions the Araya feedback form asks again.
Neither carries required today, but the attribute is dropped and restored
explicitly anyway: a hidden required field fails validation with nothing
visible to fix, so adding required to that markup later must not be able to
make the form unsubmittable silently.

Unchecking an Araya product also unchecks any Araya-specific reason that was
ticked, and resets the star widget, whose state lives in CSS classes rather
than in the input it writes.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 8: The plan-bundle keep-list (CS item 2c)

**Files:**
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php` — the "Useful Features to Keep Activated" section at lines 324–338
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js` — `recomputeConditionalState()`
- Test: manual, in the browser — see Step 4

**Interfaces:**
- Consumes: `recomputeConditionalState()` from Task 6; `#businessType` from Task 4.
- Produces: markup `#keepProductsGroup`, `#keepProductsList`, checkboxes `name="keepProducts[]"`. Task 9's payload reads them.

- [ ] **Step 1: Replace the features-to-keep section**

The existing section hardcodes one option, "Araya (Massage) $199/month", which is both a price and the wrong question. Replace lines 324–338 of `modules/upgrade-downgrade/views/downgradeForm.php` with:

```php
                        <!-- ========== SECTION: Products to Keep ========== -->
                        <!-- Item 2c. Only asked when a plan bundle is being
                             cancelled: someone cancelling a social media add-on
                             has no plan to keep anything from, and asking them
                             would be a confusing question put to a customer who
                             is already cancelling.
                             Without prices for now -- CS sanctioned shipping
                             without them, and resolving them needs three
                             answers CS has not given. -->
                        <div class="form-section d-none" id="keepProductsGroup">
                            <h5 class="section-title"><i class="bi bi-bookmark-heart"></i> Would you like to keep any of these?</h5>
                            <p class="text-muted small mb-3">You are cancelling a plan. These can stay active on their own if you would like to keep them.</p>
                            <div class="checkbox-card-group" id="keepProductsList"></div>
                        </div>
```

- [ ] **Step 2: Render and drive it**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, add the keep-list options and renderer next to the other product helpers:

```javascript
// Item 2c's options are chosen by business type, per CS. "Other" gets the
// union: we do not know which applies, and offering too much is recoverable
// where offering nothing is not.
const UDM_KEEP_OPTIONS = {
    Restaurant: ['Pro Plan', 'Website Hosting'],
    Massage:    ['Booking System', 'Website Hosting'],
    Other:      ['Pro Plan', 'Booking System', 'Website Hosting'],
};

// Rendered from scratch on every call, like the product list: the business type
// can change after a plan bundle is already checked, and a stale option from
// the previous type must not survive it.
function udmRenderKeepList() {
    const $list   = $('#keepProductsList');
    const type    = $('#businessType').val() || 'Other';
    const options = UDM_KEEP_OPTIONS[type] || UDM_KEEP_OPTIONS.Other;

    // What was ticked before, so a re-render for an unrelated reason does not
    // silently discard the customer's answer.
    const wasChecked = {};
    $list.find('input[name="keepProducts[]"]:checked').each(function () {
        wasChecked[$(this).val()] = true;
    });

    const html = options.map(function (name, i) {
        const id      = 'keepProduct' + i;
        const checked = wasChecked[name] ? ' checked' : '';
        return `
            <div class="checkbox-card">
                <input type="checkbox" id="${id}" name="keepProducts[]" value="${udmEscapeHtml(name)}"${checked}>
                <label for="${id}"><i class="bi bi-bookmark-check"></i> ${udmEscapeHtml(name)}</label>
            </div>`;
    }).join('');

    $list.html(html);
}
```

Then replace the `// Task 8 adds the plan-bundle keep-list here.` / `void hasPlanBundle;` lines at the end of `recomputeConditionalState()` with:

```javascript
    // --- Item 2c: what to keep, when a plan bundle is being cancelled ---
    //
    // Gated on plan_bundle specifically, not on the word "bundle": "Social
    // Media Marketing Bundle" is an add-on, and asking someone cancelling it
    // what plan they want to keep makes no sense.
    $('#keepProductsGroup').toggleClass('d-none', !hasPlanBundle);

    if (hasPlanBundle) {
        udmRenderKeepList();
    } else {
        // Clear it: an answer about keeping a plan must not be submitted by
        // someone who has since unchecked the plan.
        $('#keepProductsList').empty();
    }
```

- [ ] **Step 3: Recompute when the business type changes**

In the DOM-ready block, after the product checkbox handler added in Task 6, add:

```javascript
    // The keep-list options are chosen by business type, so changing it has to
    // re-render them.
    $('#businessType').on('change', recomputeConditionalState);
```

- [ ] **Step 4: Verify the keep-list**

Run `node --check modules/upgrade-downgrade/controllers/downgradeForm.js` and `php -l modules/upgrade-downgrade/views/downgradeForm.php` first.

Force the tags from the console as in Task 7, using `['plan_bundle']` and `['addon']`.

| Check | Expected |
| --- | --- |
| Business Type = Restaurant, a `plan_bundle` product checked | The section appears offering Pro Plan and Website Hosting |
| Business Type = Massage, same | Booking System and Website Hosting |
| Business Type = Other, same | All three |
| Change Business Type while the section is open | The options change to match, and anything still offered stays ticked |
| Uncheck the plan bundle product | The section hides and `#keepProductsList` is empty in devtools |
| A product tagged `addon` checked — e.g. `udmProductTags[name] = ['addon']` | The section does **not** appear |
| A product named "Social Media Marketing Bundle" | The section does **not** appear — this is the specific failure item 2c must avoid |
| A product tagged `['araya', 'plan_bundle']` | **Both** the Araya conditionals from Task 7 and this keep-list appear |
| Anywhere in the section | No prices |
| The old "Araya (Massage) $199/month" checkbox | Gone |

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/views/downgradeForm.php \
        modules/upgrade-downgrade/controllers/downgradeForm.js
git commit -m "feat(upgrade-downgrade): ask what to keep when a plan bundle is cancelled

CS item 2c. The options are chosen by business type: Restaurant gets Pro Plan
and Website Hosting, Massage gets Booking System and Website Hosting.

Gated on the plan_bundle tag rather than on the word \"bundle\", because
\"Social Media Marketing Bundle\" is an add-on and asking someone cancelling it
what plan they would like to keep makes no sense.

Replaces a hardcoded \"Araya (Massage) \$199/month\" checkbox, which was both a
price and the wrong question. No prices here: CS sanctioned shipping without
them.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 9: Validation and the webhook payload

**Files:**
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js:256-300` (`sendData`) and `:325-393` (`validateForm`)
- Test: manual, in the browser, reading the request in devtools — see Step 4

**Interfaces:**
- Consumes: `cancelledProducts[]` and `keepProducts[]` from Tasks 6 and 8; `IS_STAFF` from Task 5.
- Produces: the webhook payload. `downgradeTarget` is gone; `cancelledProducts`, `keepProducts` and `businessType` are new.

**Open question, approved to build:** the Make.com scenario this posts to cannot be inspected from here. `changeType` currently carries `formData.downgradeTarget || formData.requestType`, so with the radio group gone it would silently become `requestType` for every submission — a change in meaning, not just in value. The design keeps `changeType` as the request type and sends the products in their own fields. **Confirm with the scenario owner before this reaches production.** The same open question already applies to the upgrade form's `originalProductManual` key.

- [ ] **Step 1: Update the payload**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, replace the `jsonData` assignment inside `sendData` (lines 264–285) with:

```javascript
    const cancelledProducts = formData.cancelledProducts || [];
    const keepProducts      = formData.keepProducts || [];

    const jsonData = Object.assign({}, formData, {
        date: formattedDate,
        // ---- Normalized fields (shared upgrade/downgrade webhook schema) ----
        formType: 'downgrade',
        // changeType carries the request type. It used to carry the
        // second-level downgrade target when there was one, but that radio
        // group is gone: the products being cancelled are real product names
        // now, and they travel in cancelledProducts.
        //
        // downgradeTarget is dropped from the payload rather than sent as a
        // permanently empty string, because an always-empty field invites a
        // consumer to keep reading it. CONFIRM WITH THE MAKE.COM SCENARIO
        // OWNER before this reaches production.
        changeType: formData.requestType || '',
        requestType: formData.requestType || '',
        cancelledProducts: cancelledProducts,
        // A flat string too: a Make.com scenario reading a single cell is far
        // more likely than one iterating an array.
        cancelledProductsText: Array.isArray(cancelledProducts) ? cancelledProducts.join(', ') : cancelledProducts,
        keepProducts: keepProducts,
        keepProductsText: Array.isArray(keepProducts) ? keepProducts.join(', ') : keepProducts,
        email: formData.emailAddress || '',
        shopName: formData.shopName || '',
        businessType: formData.businessType || '',
        mondayProjectId: formData.mondayProjectId || '',
        country: formData.country || '',
        countryBoardId: COUNTRY_BOARD_IDS[country] || '',
        originalProduct: formData.originalProduct || '',
        newProduct: formData.newProduct || '',
        contractPeriod: formData.contractPeriod || '',
        billingDate: formData.billingDate || '',
        staffName: formData.accountManager || formData.submittedBy || '',
        submittedBy: formData.submittedBy || '',
        reason: reasonText || '',
        note: formData.staffDowngradeNote || '',
    });
```

- [ ] **Step 2: Update validation**

Replace `validateForm()` (lines 325–393) with:

```javascript
// ===== Validation =====
function validateForm() {
    let isValid = true;
    const requestType = $('input[name="requestType"]:checked').val();
    const isSpecific  = (requestType === 'Unsubscribe specific product');

    // Always required text fields. Country and business type are new, and
    // country especially: without it no lookup can run at all.
    const requiredFields = ['country', 'businessType', 'shopName', 'address', 'contactPerson', 'emailAddress', 'mobileNumber'];

    // Staff names themselves at the end of the form.
    if (IS_STAFF) requiredFields.push('submittedBy');

    requiredFields.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) {
            el.classList.add('is-invalid');
            isValid = false;
        } else if (el) {
            el.classList.remove('is-invalid');
        }
    });

    // Request type
    if (!requestType) {
        const errEl = document.getElementById('requestTypeError');
        if (errEl) { errEl.style.display = ''; errEl.textContent = 'Please select a type of request.'; }
        isValid = false;
    } else {
        const errEl = document.getElementById('requestTypeError');
        if (errEl) errEl.style.display = 'none';
    }

    // At least one product, when unsubscribing from specific products.
    //
    // The message distinguishes "you have not ticked anything" from "we have
    // nothing to show you": the second is not the customer's fault and ticking
    // harder will not fix it.
    const prodErrEl = document.getElementById('productListError');
    if (isSpecific && $('#productCheckboxList input[name="cancelledProducts[]"]:checked').length === 0) {
        if (prodErrEl) {
            prodErrEl.style.display = '';
            prodErrEl.textContent = $('#productCheckboxList input[name="cancelledProducts[]"]').length
                ? 'Please select at least one product to unsubscribe from.'
                : 'We could not find any products for this email address. Please choose "Unsubscribe all services", or contact our team and we will help.';
        }
        isValid = false;
    } else if (prodErrEl) {
        prodErrEl.style.display = 'none';
    }

    // Effective date
    if (!$('input[name="effectiveDate"]:checked').val()) {
        isValid = false;
    }

    // Custom date required when custom option selected
    if ($('#effCustomDate').is(':checked') && !$('#customDate').val()) {
        document.getElementById('customDate').classList.add('is-invalid');
        isValid = false;
    }

    // At least one reason
    if ($('input[name="reasons[]"]:checked').length === 0) {
        isValid = false;
    }

    // Contact preference. Customer mode only: staff must not answer on the
    // customer's behalf, and the field is not rendered for them at all. This is
    // the one genuinely required field the staff variant removes, so validating
    // it unconditionally would make the staff form unsubmittable -- the exact
    // hidden-required-field trap, arrived at from the other direction.
    if (!IS_STAFF) {
        if (!$('input[name="contactPreference"]:checked').val()) {
            const errEl = document.getElementById('contactPreferenceError');
            if (errEl) { errEl.style.display = ''; errEl.textContent = 'Please select a contact preference.'; }
            isValid = false;
        } else {
            const errEl = document.getElementById('contactPreferenceError');
            if (errEl) errEl.style.display = 'none';
        }

        // The policy acknowledgement gates submission, customer mode only.
        const $ack = $('#policyAcknowledge');
        if ($ack.length && !$ack.is(':checked')) {
            $ack.addClass('is-invalid');
            const errEl = document.getElementById('policyAcknowledgeError');
            if (errEl) { errEl.style.display = ''; errEl.textContent = 'Please confirm you understand the policy.'; }
            isValid = false;
        } else {
            $ack.removeClass('is-invalid');
            const errEl = document.getElementById('policyAcknowledgeError');
            if (errEl) errEl.style.display = 'none';
        }
    }

    if (!isValid) {
        $('#result').html('<div class="alert alert-danger">Please fill in all required fields.</div>');
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return isValid;
}
```

The `#policyAcknowledge` and `#submittedBy` fields this references are created in Task 10. Until then those branches are inert: `$('#policyAcknowledge').length` is 0, and `document.getElementById('submittedBy')` is null, which the `if (el && ...)` guard already handles.

- [ ] **Step 3: Update the test-mode autofill**

`applyTestAutofill()` (lines 418–463) still fills removed fields. Replace its Business Info and Request Type blocks:

```javascript
function applyTestAutofill() {
    // Business Info
    $('#country').val('TH');
    $('#businessType').val('Massage').trigger('change');
    $('#shopName').val('Test Shop');
    $('#tradingName').val('Test Shop LLC');
    $('#address').val('123 Test St, Los Angeles, CA 90001');
    $('#contactPerson').val('Test Person');
    $('#emailAddress').val('test@example.com');
    $('#mobileNumber').val('+1 (555) 010-0000');

    // Staff fields
    $('#mondayProjectId').val('99999');
    $('#boardId').val('1943203205'); // TH project board

    // Request Type. The old second-level target radio is gone, and the product
    // checkboxes come from a lookup, so there is nothing to tick here: a test
    // submission uses "all services", which needs no product selection.
    $('#reqUnsubscribe').prop('checked', true).trigger('change');
```

Then delete the `$('#billingDate')` line, and leave the rest of the function as it is. Append the two new fields before its closing brace:

```javascript
    // New in these revisions
    $('#submittedBy').val('Test Staff');
    $('#policyAcknowledge').prop('checked', true);
}
```

- [ ] **Step 4: Verify the payload and validation**

Run `node --check modules/upgrade-downgrade/controllers/downgradeForm.js`.

In a browser, with the Network tab open and filtered to `hook.us1.make.com`:

| Check | Expected |
| --- | --- |
| Submit with "Unsubscribe specific product" and two products ticked | The request body has `cancelledProducts` as a 2-element array and `cancelledProductsText` as the two names comma-joined |
| The same request body | **No `downgradeTarget` key at all** |
| The same request body | `changeType` equals `"Unsubscribe specific product"` |
| Submit with a plan bundle and a keep-list answer | `keepProducts` and `keepProductsText` carry the ticked options |
| The same request body | `businessType` carries the selection |
| Submit "Unsubscribe specific product" with nothing ticked | Blocked, with "Please select at least one product" |
| The same, with the list empty because the lookup found nothing | Blocked, with the "could not find any products ... contact our team" wording instead |
| Submit with Country empty | Blocked, with Country marked invalid |
| Submit with Business Type empty | Blocked |
| `?mode=staff`, submit a complete form | Submits — not blocked by the contact preference it does not render |
| `?mode=customer&testMode=true` | Autofills with no console error, and no attempt to fill `#billingDate` |

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/controllers/downgradeForm.js
git commit -m "feat(upgrade-downgrade): send the cancelled products, drop downgradeTarget

Removing the second-level radio group changes what is submitted, so this is
deliberate rather than incidental. changeType used to carry the chosen
downgrade target and fall back to the request type; with the group gone it
would have silently become requestType for every submission, which is a change
in meaning and not just in value.

changeType now carries the request type, and the products travel in their own
cancelledProducts and keepProducts fields, each with a comma-joined text twin
because a Make.com scenario reading one cell is likelier than one iterating an
array. downgradeTarget is dropped rather than sent permanently empty: an
always-empty field invites a consumer to keep reading it.

NEEDS CONFIRMING with whoever owns the Make.com scenario before this reaches
production. The same open question already applies to the upgrade form's
originalProductManual key.

Validation gains country and businessType, and stops requiring the contact
preference in staff mode, where the field is not rendered -- the hidden
required field trap arrived at from the other direction.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 10: Policy section and the staff variant (CS item 6)

**Files:**
- Modify: `modules/upgrade-downgrade/views/downgradeForm.php` — the Additional Details section (the contact-preference block) and before the submit button
- Test: manual, in the browser — see Step 4

**Interfaces:**
- Consumes: the validation branches from Task 9, which already read `#policyAcknowledge` and `#submittedBy`.
- Produces: `#policyAcknowledge`, `#policyAcknowledgeError`, `#submittedBy`.

- [ ] **Step 1: Make the contact-preference question customer-only**

The contact-preference block at `views/downgradeForm.php:348-361` asks whether to call the customer back. A staff member taking the call must not answer it for them, so wrap it. Replace its opening `<div class="mb-3">` at line 348 with:

```php
                            <?php if ($mode === 'customer'): ?>
                            <div class="mb-3">
```

and add `<?php endif; ?>` immediately after the `</div>` that closes it (after `#contactPreferenceError` at line 360 and its wrapper's close at 361).

Note the `required` attribute on `#contactYes` at line 352 stays exactly as it is. The field is now simply absent in staff mode, and Task 9's validation already skips it there. Leaving `required` on the markup and gating the whole block is safer than stripping the attribute, because an attribute on an element that does not exist cannot block anything.

- [ ] **Step 2: Add the policy section**

In `modules/upgrade-downgrade/views/downgradeForm.php`, immediately before the submit button block at line 396 (the `<div>` containing `#cmdSubmit`), insert:

```php
                        <?php if ($mode === 'customer'): ?>
                        <!-- ========== SECTION: Policy ========== -->
                        <!-- Item 6. Customer mode only: a staff member cannot
                             acknowledge a policy on the customer's behalf. -->
                        <div class="form-section" id="policySection">
                            <h5 class="section-title"><i class="bi bi-file-earmark-text"></i> Cancellation Policy</h5>

                            <div class="policy-body">
                                <p>All cancellations are subject to a <strong>30-day notice period</strong>. Your service remains active and billable until that period ends.</p>
                                <p>You are responsible for removing any marketing material, printed or online, that still advertises a service you have cancelled. We cannot remove material that is not under our control.</p>
                                <p>If you cancel all of your services, or your website hosting specifically, <strong>your website will be discontinued</strong> and will no longer be reachable.</p>
                            </div>

                            <div class="checkbox-card mt-3">
                                <input type="checkbox" id="policyAcknowledge" name="policyAcknowledge" value="Yes, I understand." required>
                                <label for="policyAcknowledge"><i class="bi bi-check2-square"></i> Yes, I understand.</label>
                            </div>
                            <div class="invalid-feedback d-block" id="policyAcknowledgeError" style="display:none!important;"></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($mode === 'staff'): ?>
                        <!-- Who processed this request. Staff only, and the one
                             field the staff variant adds. -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-person-badge"></i> Submitted By <span class="text-danger">*</span></h5>
                            <div class="mb-3">
                                <label for="submittedBy" class="form-label">Your name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="submittedBy" name="submittedBy" placeholder="e.g. Jane from Customer Success" required>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>
                        </div>
                        <?php endif; ?>
```

- [ ] **Step 3: Add the policy styles**

Append to `modules/upgrade-downgrade/assets/css/downgradeForm.css`:

```css
/* ===== Cancellation policy (CS item 6) ===== */
.policy-body {
    padding: 16px 18px;
    border-left: 3px solid #0d6efd;
    border-radius: 0 10px 10px 0;
    background: #f7faff;
    font-size: 0.91rem;
    line-height: 1.65;
    color: #3d4451;
}

.policy-body p:last-child {
    margin-bottom: 0;
}
```

- [ ] **Step 4: Verify both variants**

Run `php -l modules/upgrade-downgrade/views/downgradeForm.php`.

| Check | Expected |
| --- | --- |
| `?mode=customer` | The Cancellation Policy section is there, with all three paragraphs and the "Yes, I understand." checkbox |
| `?mode=customer`, submit without ticking it | Blocked, with "Please confirm you understand the policy" |
| `?mode=customer`, tick it and submit | Submits |
| `?mode=customer` | No "Submitted By" field |
| `?mode=staff` | No policy section and no acknowledgement checkbox anywhere |
| `?mode=staff` | A required "Submitted By" field at the end |
| `?mode=staff`, submit with it empty | Blocked, with the field marked invalid |
| `?mode=staff` | No "Would you like us to contact you..." question |
| `?mode=staff` | No 1–5 experience rating (removed in Task 7, Step 3) |
| `?mode=staff`, submit a complete form | Submits — nothing hidden is blocking it |
| `?mode=staff`, check the payload | `submittedBy` carries the name, and `staffName` falls back to it |

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/views/downgradeForm.php \
        modules/upgrade-downgrade/assets/css/downgradeForm.css
git commit -m "feat(upgrade-downgrade): add the cancellation policy and the staff variant

CS item 6, plus the staff/customer split CS described. The policy carries their
three paragraphs -- the 30-day notice, the customer's responsibility for stale
marketing material, and the website being discontinued if all services or
hosting are cancelled -- gated by a required acknowledgement.

Staff differs from customer by exactly: no feedback questions, no policy
acknowledgement, plus a required Submitted By field. The contact-preference
question is gated rather than having its required attribute stripped, because
an attribute on an element that does not exist cannot block a submission.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 11: Post-submit Araya flow (CS item 7)

**Files:**
- Modify: `modules/upgrade-downgrade/models/downgradeForm.php`
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js` — `sendData`
- Test: manual — see Step 4

**Interfaces:**
- Consumes: `udmClassifyProduct()` from Task 1; `cancelledProducts` from Task 9; `araya_feedback_url` from Task 1's config.
- Produces: an admin notification email, and a gated redirect.

**Deviation from the spec, restated:** the spec put both in `views/thankyou.php`. That file is static HTML reached by `window.location.replace("thankyou.php")` with no query string, so it receives no submission data and cannot know an Araya product was cancelled. The email goes in `models/downgradeForm.php`, which already receives the full payload server-side. The redirect stays in `sendData`, where the products and shop details are already in hand.

**Flagged for CS, not a build item:** item 7 promises the customer "30 นาทีฟรี" for completing the feedback form. Nothing in this system grants minutes. That is a business commitment rendered as form copy, and CS must be able to honour it operationally before that text ships. No such text is added by this task.

- [ ] **Step 1: Send the admin email**

In `modules/upgrade-downgrade/models/downgradeForm.php`, insert before the `$result['result'] = 'success';` line at line 38:

```php
// ----- AI Receptionist cancellation notice (CS item 7) -----
//
// Here rather than on the thank-you page: that page is static HTML reached by a
// redirect with no query string, so it never learns what was cancelled. This
// already has the whole payload.
//
// A failed notification must not lose a cancellation, so everything below is
// wrapped: the insert above has already succeeded by this point, and the
// customer's submission is safe regardless of what the mail server does.
try {
    require_once __DIR__ . '/../assets/productClassify.php';

    $cancelled = $data['cancelledProducts'] ?? [];
    if (is_string($cancelled)) {
        $cancelled = [$cancelled];
    }

    $arayaProducts = [];
    if (is_array($cancelled)) {
        foreach ($cancelled as $productName) {
            if (!is_string($productName)) {
                continue;
            }
            if (in_array('araya', udmClassifyProduct($productName), true)) {
                $arayaProducts[] = $productName;
            }
        }
    }

    if ($arayaProducts !== []) {
        $adminEmail = 'admin@localforyou.com';
        $subject    = 'AI Receptionist cancellation: ' . ($shopName ?: 'unknown shop');

        $rows = [
            'Shop'            => $shopName ?: '-',
            'Email'           => $emailAddress ?: '-',
            'Country'         => $country ?: '-',
            'Monday Project'  => $mondayId ?: '-',
            'Request type'    => $requestType ?: '-',
            'Products'        => implode(', ', $arayaProducts),
            'All cancelled'   => is_array($cancelled) ? implode(', ', $cancelled) : (string) $cancelled,
            'Effective'       => $data['effectiveDate'] ?? '-',
            'Submitted'       => $submittedAt ?: date('d-m-Y H:i'),
            'Mode'            => $formMode,
        ];

        $body = '<h2 style="font-family:sans-serif">AI Receptionist cancellation</h2>'
              . '<table cellpadding="6" style="font-family:sans-serif;border-collapse:collapse">';
        foreach ($rows as $label => $value) {
            $body .= '<tr>'
                   . '<td style="border:1px solid #ddd;background:#f6f6f6"><strong>' . htmlspecialchars($label) . '</strong></td>'
                   . '<td style="border:1px solid #ddd">' . htmlspecialchars((string) $value) . '</td>'
                   . '</tr>';
        }
        $body .= '</table>';

        // Same header shape as modules/printer/assets/php/sendPrinterMail.php,
        // which is the pattern this repo already sends mail with.
        $headers = [
            'From'         => 'Local For You <' . $adminEmail . '>',
            'Reply-To'     => $adminEmail,
            'X-Mailer'     => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
        ];

        // A test submission must not page anyone.
        if (!$testMode) {
            @mail($adminEmail, $subject, $body, $headers);
        }
    }
} catch (Throwable $e) {
    // Swallowed deliberately. Losing a notification must not lose a
    // cancellation, and the row is already committed.
    error_log('downgradeForm araya notice failed: ' . $e->getMessage());
}
```

- [ ] **Step 2: Expose the feedback URL to the browser**

The redirect target lives in `config.php`, which the browser cannot read. Add it to the page. In `modules/upgrade-downgrade/views/downgradeForm.php`, the PHP header block at lines 1–10 currently ends:

```php
$stripeID = $_GET['stripeID'] ?? '';
$mode = $_GET['mode'] ?? 'customer';
$testMode = $_GET['testMode'] ?? 'false';
?>
```

Change to:

```php
$stripeID = $_GET['stripeID'] ?? '';
$mode = $_GET['mode'] ?? 'customer';
$testMode = $_GET['testMode'] ?? 'false';

// Where a customer who cancelled an AI Receptionist product is sent to give
// feedback on it. Empty until CS supplies the URL, at which point the redirect
// starts working with no code change. config.php is gitignored, so an absent
// file must simply mean "no redirect".
$arayaFeedbackUrl = '';
$udmConfigPath = __DIR__ . '/../assets/config.php';
if (is_file($udmConfigPath)) {
    $udmConfig = require $udmConfigPath;
    if (is_array($udmConfig)) {
        $arayaFeedbackUrl = (string) ($udmConfig['araya_feedback_url'] ?? '');
    }
}
?>
```

Then add the data attribute to the `<body>` tag at line 31:

```php
<body data-mode="<?php echo htmlspecialchars($mode); ?>" data-araya-feedback-url="<?php echo htmlspecialchars($arayaFeedbackUrl); ?>">
```

- [ ] **Step 3: Redirect on success**

In `modules/upgrade-downgrade/controllers/downgradeForm.js`, replace the `success` handler inside `sendData`'s `$.ajax` call:

```javascript
        success: () => {
            console.log("Downgrade webhook sent.");
            window.location.replace(udmPostSubmitUrl(cancelledProducts, formData));
        },
```

and add the helper after `sendData`:

```javascript
// Where to send the customer after a successful submission.
//
// A customer who cancelled an AI Receptionist product goes to the feedback form
// for it, with their shop name and email carried over so they do not retype
// what they just typed. Everyone else goes to the normal thank-you page.
//
// The URL comes from config.php and is empty until CS supplies it, so this
// returns the thank-you page until then and starts redirecting with no code
// change afterwards.
function udmPostSubmitUrl(cancelledProducts, formData) {
    const feedbackUrl = document.body.dataset.arayaFeedbackUrl || '';
    if (!feedbackUrl) return 'thankyou.php';

    const names = Array.isArray(cancelledProducts) ? cancelledProducts : [cancelledProducts];
    const hasAraya = names.some(function (name) {
        return (udmProductTags[name] || []).indexOf('araya') !== -1;
    });

    if (!hasAraya) return 'thankyou.php';

    const sep = feedbackUrl.indexOf('?') === -1 ? '?' : '&';
    return feedbackUrl + sep + $.param({
        shopName: formData.shopName || '',
        email:    formData.emailAddress || '',
    });
}
```

- [ ] **Step 4: Verify the flow**

Run `php -l modules/upgrade-downgrade/models/downgradeForm.php`, `php -l modules/upgrade-downgrade/views/downgradeForm.php` and `node --check modules/upgrade-downgrade/controllers/downgradeForm.js`.

| Check | Expected |
| --- | --- |
| Submit with no Araya product and no `araya_feedback_url` set | Lands on `thankyou.php`; no email |
| Submit with an Araya product, `araya_feedback_url` still unset | Lands on `thankyou.php`, **and** the admin email is attempted — the redirect being unconfigured must not suppress the notification |
| Set `araya_feedback_url` in `config.php`, submit with an Araya product | Redirected to that URL with `shopName` and `email` in the query string |
| The same, with a `?` already in the configured URL | The parameters are appended with `&`, not a second `?` |
| Submit with `testMode=true` and an Araya product | No email sent |
| Break the mail config deliberately, then submit | The row is still written and the customer still reaches the next page |
| Check `error_log` after a mail failure | A `downgradeForm araya notice failed` line, or nothing if `mail()` returned false without throwing |

To verify the email body without a working mail server, stub it:

```bash
php -r '
$data = [
  "cancelledProducts" => ["AI Receptionist", "Website Hosting"],
  "shopName" => "Test Shop", "emailAddress" => "owner@test.com",
  "country" => "TH", "requestType" => "Unsubscribe specific product",
  "effectiveDate" => "Stop the service now",
];
require "modules/upgrade-downgrade/assets/productClassify.php";
foreach ($data["cancelledProducts"] as $p) {
    printf("%-20s => %s\n", $p, implode(",", udmClassifyProduct($p)));
}
'
```

Expected: `AI Receptionist` tagged `araya`, `Website Hosting` tagged `addon` — so the email fires, and lists only the first under "Products".

- [ ] **Step 5: Commit**

```bash
git add modules/upgrade-downgrade/models/downgradeForm.php \
        modules/upgrade-downgrade/views/downgradeForm.php \
        modules/upgrade-downgrade/controllers/downgradeForm.js
git commit -m "feat(upgrade-downgrade): notify admin and redirect on an Araya cancellation

CS item 7. Two independent halves, so one failing cannot take the other with
it: the admin notification sends whether or not the feedback URL is configured,
and losing a notification must never lose a cancellation.

Placed in the model rather than on the thank-you page, which the spec named.
That page is static HTML reached by a redirect with no query string, so it
never learns what was cancelled; the model already has the whole payload.

araya_feedback_url is empty until CS supplies it, so the redirect is inert and
starts working with no code change once the key is filled in.

Not built, and flagged for CS: item 7 promises the customer 30 free minutes for
completing the feedback form. Nothing in this system grants minutes, so that
text is a business commitment CS must be able to honour operationally before it
ships.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 12: Record the manual test script and the open questions

There is no JavaScript test runner in this repo, so the conditional-state behaviour is verified by hand. Writing the script down is what makes that repeatable rather than a one-off.

**Files:**
- Create: `modules/upgrade-downgrade/tests/test_downgrade_conditionals.md`
- Modify: `modules/upgrade-downgrade/README.md` (create the section if the file has none)

- [ ] **Step 1: Write the manual test script**

Create `modules/upgrade-downgrade/tests/test_downgrade_conditionals.md`:

```markdown
# Downgrade form conditionals — manual test script

The form's conditional sections are driven by `recomputeConditionalState()` in
`controllers/downgradeForm.js`. There is no JavaScript test runner in this
repo, so they are verified by hand. Run this whole script after any change to
that function or to the markup it toggles.

`udmClassifyProduct()` itself is covered automatically:

    php tests/test_product_classify.php

## Setting up a state

Only two rows in the mirror classify as `araya`, so a real Araya shop is
unlikely to be available. Force the state from the browser console instead,
after a lookup has rendered at least one product checkbox:

```javascript
// Tag the first rendered product however the test needs
const name = $('#productCheckboxList input').first().val();
udmProductTags[name] = ['araya'];            // or ['plan_bundle'], ['addon'],
                                             // or ['araya','plan_bundle']
$('#productCheckboxList input').first().prop('checked', true);
recomputeConditionalState();
```

## 1. Product list (items 2, 2a)

| Step | Expected |
| --- | --- |
| Choose "Unsubscribe specific product" before any lookup | The "Enter your email address above" prompt, not an empty box |
| Look up a shop with products | One checkbox per product, real names, prompt gone |
| Choose "Unsubscribe all services" | Section closes, every checkbox unchecked |
| Back to "Unsubscribe specific product" | Checkboxes clear, nothing remembered |
| Look up shop A, tick a product, look up shop B | B's products replace A's, nothing ticked |

## 2. Araya conditionals (items 2b, 3, 3a, 4, 5)

Tag a product `['araya']` and check it.

| Step | Expected |
| --- | --- |
| Araya checked | Five extra reasons appear |
| Uncheck it | They disappear, and any that were ticked are unchecked |
| Araya + "Stop the service now" | The forfeited-minutes warning shows |
| Araya + "End of current billing cycle" | No warning |
| No Araya + "Stop the service now" | No warning |
| Araya checked, open the Information modal | The "AI Receptionist — Cancellation Conditions" card, five bullets |
| No Araya, open the modal | That card absent |
| Araya checked | "What could we improve..." and the 1–5 rating both hidden |
| Rate 4 stars, check Araya, uncheck it | Rating group returns with no stars lit, `#overallRating` empty |
| Araya checked, submit | **Submits.** A hidden required field would block it with nothing to fix |

## 3. Plan bundle keep-list (item 2c)

Tag a product `['plan_bundle']` and check it.

| Step | Expected |
| --- | --- |
| Business Type = Restaurant | Pro Plan, Website Hosting |
| Business Type = Massage | Booking System, Website Hosting |
| Business Type = Other | All three |
| Change Business Type with the section open | Options change; anything still offered stays ticked |
| Uncheck the product | Section hides, `#keepProductsList` empty |
| A product tagged `['addon']` | Section does **not** appear |
| A product named "Social Media Marketing Bundle" | Section does **not** appear |
| A product tagged `['araya','plan_bundle']` | **Both** the Araya conditionals and this keep-list appear |
| Anywhere in the section | No prices |

## 4. Both modes

| Step | Expected |
| --- | --- |
| `?mode=customer` field order | Country, Business Type, Business Name, Trading Name, Contact Person, Email, Phone, Address |
| `?mode=customer` | No "Contact Information" heading |
| `?mode=customer` | Policy section with the acknowledgement; no Submitted By |
| `?mode=staff` | Same order plus the Monday project search |
| `?mode=staff` | Required Submitted By; no policy section, no contact-preference question, no star rating |
| `?mode=staff` | The project search still works, and an email lookup also fills the fields |
| `?mode=staff`, search shop X then enter shop Y's email | Y wins; the status line names it |
| `?mode=staff`, submit a complete form | **Submits** |

## 5. Payload

With the Network tab filtered to `hook.us1.make.com`:

| Step | Expected |
| --- | --- |
| Two products ticked | `cancelledProducts` is a 2-element array; `cancelledProductsText` is them comma-joined |
| Any submission | **No `downgradeTarget` key** |
| Any submission | `changeType` equals the request type |
| A keep-list answer | `keepProducts` and `keepProductsText` carry it |
| Any submission | `businessType` carries the selection |

## 6. Lookup failure paths

All of these must leave the form submittable by hand, with nothing the customer
typed cleared.

| Step | Expected |
| --- | --- |
| Unknown email | Neutral message; typed values intact; `udmLookupProducts` is `[]` |
| Known email, then change it to unknown | `#mondayProjectId` empty in devtools |
| Devtools offline, trigger a lookup, restore, blur again | A second request goes out |
| Country changed after a match | Result discarded, fresh lookup runs |
| `classifyProducts.php` returning 500 (block it in devtools) | Checkboxes still render; no conditional section fires; the form still submits |
```

- [ ] **Step 2: Record the open questions**

Append to `modules/upgrade-downgrade/README.md`:

```markdown
## Open questions

### The Make.com payload contract

Both forms post to Make.com scenarios this project cannot inspect, and three
keys have changed or are in doubt:

- **`downgradeTarget` is gone** from the downgrade payload. It used to carry a
  second-level radio choice; the products being cancelled are real product
  names now and travel in `cancelledProducts` / `cancelledProductsText`.
- **`changeType`** used to carry `downgradeTarget || requestType` on the
  downgrade form. It now always carries the request type.
- **`originalProductManual`** on the upgrade form, and `originalProduct` now
  carrying product names rather than slugs.

Confirm all of these with whoever owns the scenarios before this reaches
production.

### Deferred to their own specs

- **Prices** (CS item 2c, and the upgrade form's package list). The data exists
  and is the right shape — see the design spec's "Prices: deferred, not absent"
  — but the resolver needs three answers from CS: which billing period to
  display, why Solo variants do not match by name, and how to present combined
  products. CS sanctioned shipping without them.
- **The AI Araya feedback form** (item 7's redirect destination). The hook is
  built and gated on `araya_feedback_url`; the form itself does not exist yet.

### Flagged for CS, not a build item

Item 7 promises the customer "30 นาทีฟรี" for completing the feedback form.
Nothing in this system grants minutes. That is a business commitment rendered as
form copy, and CS must be able to honour it operationally before the text ships.
No such text is in the form today.
```

- [ ] **Step 3: Run the automated test one more time**

Run: `php modules/upgrade-downgrade/tests/test_product_classify.php`

Expected: `Passed: 24  Failed: 0`

- [ ] **Step 4: Commit**

```bash
git add modules/upgrade-downgrade/tests/test_downgrade_conditionals.md \
        modules/upgrade-downgrade/README.md
git commit -m "docs(upgrade-downgrade): record the conditional test script and open questions

The form's conditional sections have no automated coverage: there is no
JavaScript test runner here, and the two Araya rows in the mirror cannot
exercise the Araya flows anyway. Writing the manual script down is what makes
it repeatable rather than a one-off, including how to force each state from the
console.

The open questions are recorded in the README rather than left in a commit
message, because the Make.com payload change needs confirming by someone who is
not reading this branch's history.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Deployment

No migration: the mirror and the lookup endpoint already exist and are populated.

One required step, and skipping it degrades the form silently rather than breaking it:

1. Copy the two new keys from `assets/config.example.php` into the real `assets/config.php`, which is gitignored:
   - `product_classification` — **without it the Araya flows never fire**, because `AI + Local Starter Bundle` is invisible to the keyword fallback. The form still works; it just stops asking the Araya questions.
   - `araya_feedback_url` — leave empty until CS supplies the URL. The admin notification sends regardless.

2. The script tags carry bumped versions (`emailLookup.js?v=1.0.0`, `upgradeForm.js?v=2.1.0`, `downgradeForm.js?v=1.1.0`), so browsers holding the old controllers will fetch the new ones.

## Self-review notes

Checked against the spec, section by section. Covered: field reordering (Task 4), the email lookup in both modes (Tasks 3, 5), Type of Request and the product list (Task 6), the `downgradeTarget` payload replacement (Task 9), the Araya conditionals (Task 7), the keep-list (Task 8), the policy section and staff variant (Task 10), the post-submit Araya flow (Task 11), product classification (Task 1), and the error-handling table (distributed across Tasks 3, 5, 6, 9, 11 and consolidated as a test script in Task 12).

Three deliberate departures from the spec, each stated where it occurs:

1. **The Araya email and redirect are not in `views/thankyou.php`.** That file is static HTML reached with no query string and cannot know what was cancelled. The email is in `models/downgradeForm.php`, the redirect in `sendData`. (Task 11)
2. **The classification map is load-bearing, not a convenience.** Verified against the live mirror: the keyword fallback matches Araya in 1 of 66 names, and not in `AI + Local Starter Bundle` — the spec's own worked example. The spec presented the map as preferable; it is in fact required. (Task 1, and the Global Constraints)
3. **66 distinct product names, not 83.** Counted from the mirror on 2026-10-13.

Two things the spec asked for that turned out not to need doing, both recorded in place: `#improvementSuggestion` and `#overallRating` carry no `required` attribute today, so item 5's unsubmittable-form trap does not bite there — it bites on `contactPreference` in the staff variant instead, which Task 9 handles. And a classification endpoint (Task 2) is not in the spec's file list at all; it is needed because the map lives in a file the browser cannot read.
