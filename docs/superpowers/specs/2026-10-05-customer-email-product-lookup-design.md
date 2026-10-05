# Customer-Mode Product Lookup by Email

**Date:** 2026-10-05
**Module:** `modules/upgrade-downgrade`
**Status:** Design — approved for planning

## Problem

In the upgrade form's customer mode (`upgradeForm.php?mode=customer`), the
customer types every field by hand, and picks their current product from a
hardcoded `<select>` of nine plan names. That list does not reflect what they
actually bought: it has no add-ons, no Yelp products, no bundles-with-booking,
and no prices. Customers routinely pick the wrong entry, and CS has to
reconstruct the real subscription from Monday afterwards.

Staff mode already solves this — pick a country, search the project, and the
form fills shop name, owner, phone, and the real active subscriptions from
Monday. Customers can't use that flow because they don't know their Monday
project ID.

**Goal:** a customer enters their email, and the form fills in their shop
details and their real active products.

## Scope

In scope:

- Upgrade form, customer mode, lookup by email within the selected country.
- A local MySQL mirror of the Monday data, refreshed by cron, so the lookup
  is fast enough to sit in a customer's typing flow.

Out of scope:

- Downgrade form. It has no "current product" field — the customer picks a
  *target* product from a radio list and describes their situation in prose.
  Nothing there needs this lookup.
- Staff mode. It keeps its existing live project search unchanged.
- Verifying the email the customer typed. Per the request: a mismatch is not
  an error condition. CS briefs the customer directly. A miss leaves the form
  in its current manual state.

## Decisions

These were settled during brainstorming and are recorded here because each one
closed off an alternative:

1. **Country-scoped lookup, not global.** The customer selects a country first
   (the field already exists and is already required), and the lookup queries
   only that country. A global sweep would be friendlier but needs all six
   countries resident and fresh; scoping keeps the query on a two-column index.
   A wrong country selection means no match, which degrades to manual entry.

2. **Local MySQL mirror, refreshed twice a day.** Monday's API takes 1–3
   seconds per call and `live.php` needs up to 20 paginated requests for a
   large board (AU is ~1300 projects). That is acceptable for staff, not for a
   customer waiting on a form. A customer who signed up 1–3 days ago will not
   be using the upgrade form yet, so a 12-hour-stale mirror is accurate enough
   for the people who actually reach this form.

3. **Two tables, not one.** Real data shows one shop carries one to three or
   more subscriptions — one sampled shop has Yelp Ad Spend, Yelp Enhanced
   Profile, and a Local Starter Bundle + Booking simultaneously. Flattening
   into one table would mean either duplicating shop columns per subscription
   (making the sync's upsert and any shop-level count error-prone) or stuffing
   a JSON array into one column (which forecloses "which shops use product X"
   reporting). Two tables joined on an indexed key costs nothing at query time.

4. **Join subscriptions to shops by Monday item id, not by shop name.** The
   current `getSubscriptions.php` matches `text9` ("Account Name") with
   `contains_text`, which is a loose string match. Probing the Active
   Subscriptions board (1881439330) found `connect_boards`, a board-relation
   column titled "Projects", that links each subscription directly to its
   project item. In a 100-row sample every row had it set, and every linked id
   resolved to one of the project boards `live.php` already queries
   (1943203246 AU, 1943203305 UK, 1940392927 US). That id is the same
   `shopId` the project sync records, so the join is exact.

   The sync still falls back to normalized `text9` matching when
   `connect_boards` is empty, and counts those rows, because 100 sampled rows
   do not prove the whole board.

5. **Contacts collapse into the shops table.** One project links to one
   contact (`connect_boards0` → Contacts board 1862866069), so owner, phone,
   and email are columns on the shop row rather than a third table.

## Architecture

```text
                    ┌──────────────────────────────┐
  cron, 2x daily →  │ sync/syncMondayMirror.php    │
                    │  - 6 project boards          │
                    │  - contacts, batched by id   │
                    │  - Active Subscriptions board│
                    └──────────────┬───────────────┘
                                   │ upsert
                    ┌──────────────▼───────────────┐
                    │ MySQL: db_localforyou        │
                    │  l4u_ud_shops                │
                    │  l4u_ud_subscriptions        │
                    └──────────────┬───────────────┘
                                   │ one indexed join, ~5ms
                    ┌──────────────▼───────────────┐
  customer types →  │ API/lookupByEmail.php        │
  email, blurs      │  ?country=TH&email=...       │
                    └──────────────┬───────────────┘
                                   │ JSON
                    ┌──────────────▼───────────────┐
                    │ upgradeForm.js               │
                    │  fills shop fields + products│
                    └──────────────────────────────┘
```

The sync writes; the lookup only reads. They share no code path, so a sync
failure cannot break the form, and a form change cannot corrupt the mirror.

### Why these boundaries

- **`MondayMirror` (sync model)** — owns all Monday API access and all writes
  to the two tables. Depends on: Monday API, MySQL. Used by: the cron entry
  point only. Testable by pointing it at a fixture JSON instead of the API.
- **`ShopLookup` (read model)** — owns the single query. Depends on: MySQL.
  Takes a country code and an email, returns a shop with its subscriptions or
  null. Nothing else in the module needs to know the schema.
- **`lookupByEmail.php` (HTTP endpoint)** — validates input, calls
  `ShopLookup`, encodes JSON. No business logic.
- **`upgradeForm.js` customer-lookup block** — one self-contained IIFE
  alongside the existing staff `Project Search Autocomplete` block, guarded by
  `if (IS_STAFF) return;` as the mirror image of the existing guard.

## Schema

```sql
CREATE TABLE l4u_ud_shops (
  shop_id     VARCHAR(20)  NOT NULL,          -- Monday project item id
  country     CHAR(2)      NOT NULL,
  email       VARCHAR(255) NOT NULL DEFAULT '',
  shop_name   VARCHAR(255) NOT NULL DEFAULT '',
  shop_type   VARCHAR(100) NOT NULL DEFAULT '',
  owner_name  VARCHAR(255) NOT NULL DEFAULT '',
  phone       VARCHAR(50)  NOT NULL DEFAULT '',
  board_id    VARCHAR(20)  NOT NULL DEFAULT '',
  synced_at   DATETIME     NOT NULL,
  PRIMARY KEY (shop_id),
  KEY idx_country_email (country, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE l4u_ud_subscriptions (
  sub_id        VARCHAR(20)  NOT NULL,        -- Monday subscription item id
  shop_id       VARCHAR(20)  NOT NULL DEFAULT '',
  raw_name      VARCHAR(255) NOT NULL DEFAULT '',  -- item name, e.g. "USD - $300 YEUS03A02 Yelp Ad Spend"
  product_group VARCHAR(255) NOT NULL DEFAULT '',  -- mirror lookup_mkwgjt31, e.g. "Yelp Ad Spend"
  price         DECIMAL(12,2) NULL,
  currency      CHAR(3)      NOT NULL DEFAULT '',
  account_name  VARCHAR(255) NOT NULL DEFAULT '',  -- text9, kept for fallback matching and debugging
  matched_by    ENUM('relation','name','none') NOT NULL DEFAULT 'none',
  synced_at     DATETIME     NOT NULL,
  PRIMARY KEY (sub_id),
  KEY idx_shop (shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Notes on specific columns:

- `email` is stored lowercased and trimmed, and the lookup lowercases its input,
  so matching needs no `LOWER()` call that would defeat the index.
- `product_group` comes from the mirror column `lookup_mkwgjt31` and is what
  the customer sees: "Pro Online Ordering System" rather than
  "PO01M00 Pro Online Ordering System". `raw_name` is kept because staff mode's
  existing display uses it and because it carries the SKU.
- `matched_by` makes the fallback measurable. A rising `name` or `none` count
  means the Monday relation is being left unset and someone should look.

## Data flow

### Sync (cron, twice daily)

1. For each of the six countries, page the project board (existing `live.php`
   logic, 100 items per page, up to 20 pages, with the existing retry and
   backoff). Collect `shopId`, `shopName`, `shopType`, `boardId`, and the
   linked contact id from `connect_boards0`.
2. Batch-fetch contacts 100 ids at a time; take first name (`text0`), last name
   (`text7`), phone (`contact_phone`, falling back to `dup__of_mobile`), and
   **email (`contact_email`) as its own field** — today `live.php` reads
   `contact_email` only as a last-resort substitute for a missing phone and then
   discards it, which is why no email is available to match on.
3. Upsert shop rows.
4. Page the Active Subscriptions board, requesting `status`, `numbers`,
   `status0`, `text9`, `connect_boards`, and `lookup_mkwgjt31`. Keep rows whose
   `status` contains "active", matching the existing filter.
5. For each kept row, resolve `shop_id`: use `connect_boards[0]` when present
   (`matched_by='relation'`); otherwise normalize `text9` (trim, collapse
   whitespace, casefold) against shop names within the same country
   (`matched_by='name'`); otherwise leave empty (`matched_by='none'`).
6. Upsert subscription rows.
7. Delete rows whose `synced_at` predates this run, so cancelled subscriptions
   and removed projects disappear instead of lingering.
8. Print a one-line summary: per-country shop counts, subscription counts, and
   the `matched_by` breakdown.

A country whose Monday fetch fails is **skipped, not emptied** — the same
reasoning as `live.php`'s existing guard against caching an error as `[]`. A
failed country keeps its previous rows and is reported in the summary.

### Lookup (customer, in-form)

Trigger: `blur` on `#emailAddress`, plus a 400 ms debounced `input`, and only
when a country is selected and the value looks like an email.

```sql
SELECT s.shop_id, s.shop_name, s.shop_type, s.owner_name, s.phone, s.board_id,
       b.sub_id, b.raw_name, b.product_group, b.price, b.currency
FROM l4u_ud_shops s
LEFT JOIN l4u_ud_subscriptions b ON b.shop_id = s.shop_id
WHERE s.country = ? AND s.email = ?
```

Response shape:

```json
{
  "found": true,
  "shop": { "shopId": "...", "shopName": "...", "shopType": "...",
            "ownerName": "...", "phone": "...", "boardId": "..." },
  "subscriptions": [
    { "id": "...", "name": "Pro Online Ordering System",
      "rawName": "PO01M00 Pro Online Ordering System",
      "price": "49.00", "currency": "AUD" }
  ],
  "syncedAt": "2026-10-05 06:00:00"
}
```

`{"found": false}` when nothing matches. More than one shop on the same email
in the same country returns the first by `shop_name` and sets
`"multiple": true`; the UI mentions it so CS can sort it out.

## UI behaviour (customer mode)

On a match:

- Fill `#shopName`, `#ownerName`, `#phoneNumber`, and the hidden
  `#mondayProjectId` / `#boardId` (which already exist as hidden inputs in
  customer mode and currently always submit empty — populating them means the
  webhook payload carries a real project id for the first time).
- Replace the hardcoded `#originalProduct` `<select>` with the real product
  list, rendered as the same read-only `.subscription-list` markup staff mode
  uses, and set the hidden `#originalProduct` value to the comma-joined product
  names — matching exactly what staff mode submits today.
- Show a quiet confirmation naming the shop, with a "not my shop?" link that
  reverts to manual entry.

All filled fields stay editable. The customer can correct anything.

On no match, or on a request failure: leave every field as the customer left
it, keep the hardcoded `<select>` in place, and say nothing alarming — a short
neutral note that details could not be prefilled and the team will confirm.
The form must remain fully submittable with zero lookup involvement.

A country change clears the lookup result and restores the manual `<select>`,
mirroring how staff mode clears its fields on country change.

## Error handling

| Failure | Behaviour |
| --- | --- |
| Lookup HTTP error or timeout | Silent fallback to manual entry; form unaffected |
| Email matches nothing | `{"found": false}`; neutral note; manual entry |
| Email matches several shops | First shop, `"multiple": true`, UI notes it |
| Shop found, no subscriptions | Shop fields fill; product list says none found, as staff mode already does |
| MySQL unreachable | Endpoint returns 503; JS treats it as a lookup failure |
| One country's Monday sync fails | That country keeps previous rows; summary reports it |
| Whole sync fails | Mirror goes stale; lookups keep serving the last good data |

The guiding rule: **the lookup is an accelerator, never a gate.** Every failure
path ends with the form working the way it works today.

**Staleness.** A stopped cron fails silently — the mirror keeps answering with
old data and nothing looks broken. The endpoint returns `syncedAt` (the oldest
`synced_at` for that country) so the problem is at least visible, and when it
exceeds 48 hours the response carries `"stale": true`. The form still prefills,
because 48-hour-old subscription data is better than a hardcoded nine-item
list, but the confirmation line says the details may be out of date and the
team will verify. The sync's own summary line is the primary signal; this is
the backstop for when nobody is reading it.

## Testing

- `ShopLookup` against a seeded fixture database: exact match, case and
  whitespace variations, wrong country, unknown email, shop with zero
  subscriptions, shop with three, duplicate email in one country.
- Sync `resolveShopId()` in isolation: relation present; relation empty with a
  `text9` that normalizes to a known shop; relation empty with an unmatchable
  `text9`; `text9` differing only by case and padding.
- Sync against a fixture Monday response: correct row counts, correct
  `matched_by` distribution, stale rows deleted, a failing country leaving its
  existing rows intact.
- `lookupByEmail.php`: missing parameters, malformed email, SQL injection
  attempt in the email parameter (the query is parameterized via the existing
  `db` class), 503 when the database is down.
- Manual: a real customer email end to end in customer mode; an unknown email;
  a country change after a successful lookup; confirming staff mode is
  untouched.

## Security

- `lookupByEmail.php` is an **unauthenticated endpoint that returns shop
  owner name and phone number when given a matching email.** That is an email
  enumeration surface. Mitigations: return only the fields the form fills,
  never the email back; rate-limit by IP; log queries with their outcome. The
  form is already a public customer-facing page, so this is the same class of
  exposure the form itself carries, but it should be a deliberate choice rather
  than an accident.
- The Monday API token is currently hardcoded in `getSubscriptions.php:5` and
  `live.php:15`, both tracked in git. The sync script must read it from
  configuration instead (the repo already has `.env` and `credentials/`).
  Migrating the two existing files is adjacent cleanup, not part of this work,
  but the new script must not add a third copy.
- All queries use the existing `db` class's prepared statements.

## Files

New:

- `modules/upgrade-downgrade/assets/API/lookupByEmail.php` — read endpoint
- `modules/upgrade-downgrade/models/ShopLookup.php` — the single read query
- `modules/upgrade-downgrade/sync/MondayMirror.php` — sync logic
- `modules/upgrade-downgrade/sync/syncMondayMirror.php` — cron entry point,
  CLI-only with a token-guarded web fallback, following
  `modules/QRCodeDynamicLink/public/cron.php`
- `modules/upgrade-downgrade/sync/schema.sql` — the two `CREATE TABLE`s

Modified:

- `modules/upgrade-downgrade/controllers/upgradeForm.js` — add the customer
  lookup block
- `modules/upgrade-downgrade/views/upgradeForm.php` — container for the
  customer-mode product list and the lookup status line

Untouched: `getSubscriptions.php`, `live.php`, the staff flow, both downgrade
files.

## Deployment

1. Apply `schema.sql`.
2. Run the sync once by hand; check the summary line.
3. Register cron twice daily, following the QRCodeDynamicLink pattern:
   `php modules/upgrade-downgrade/sync/syncMondayMirror.php`
4. Deploy the form changes.

Steps 1–3 are inert on their own: until the form changes ship, the mirror is
just a populated pair of tables that nothing reads.
