# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`modules/aiAraya` is one module of the **masterPanel** PHP app (repo root: `C:\xampp\htdocs\masterPanel`, an AdminLTE-based admin panel). It collects onboarding details from customers who bought the "AI Araya" AI-receptionist product, stores them, and forwards them to Make.com.

There is **no build step, no test suite, and no package manager** for this module. It is plain PHP + jQuery served directly by Apache. Edit files, reload the page.

- Local: `http://localhost/masterPanel/modules/aiAraya/` (XAMPP, `C:\xampp\htdocs`)
- Production: `https://report.localforyou.com/modules/aiAraya/`

## Layout convention (MVC-ish, used by every masterPanel module)

```
index.php        -> JS redirect to views/index.php
views/           -> full HTML pages (each starts session + requires db)
controllers/     -> browser-side JS loaded by the matching view
models/          -> AJAX endpoints; echo JSON, never HTML
assets/db/       -> db.php (mysqli wrapper class) + initDB.php (credentials)
assets/libs/     -> vendored Bootstrap 5.3.3, DataTables, jQuery 3.7.1
layout/footer.php
```

Views and models both use **relative includes from their own directory** (`'../assets/db/db.php'`). A file moved between directories will break its includes — this has bitten the codebase before. Same for asset URLs (`../assets/...`) and AJAX URLs (`../models/...`).

## Database access

`assets/db/initDB.php` is **gitignored** (`**/initDB.php` in root `.gitignore`) and must exist locally. Switch environments by copying `initDB_local.php` or `initDB_server.php` over `initDB.php`.

Query via the `db` class in `assets/db/db.php` — a prepared-statement mysqli wrapper. Always pass bound params; never interpolate:

```php
global $db;
$row  = $db->query('SELECT startedAt FROM order_tracking WHERE stripeID = ? LIMIT 1', [$stripeID])->fetchArray();
$rows = $db->query('SELECT * FROM ai_araya ORDER BY id DESC')->fetchAll();
```

`fetchArray()` = single row, `fetchAll()` = all rows. Params may be passed as an array or as varargs. `$db->error()` calls `exit()`, so a bad query kills the response mid-JSON.

Two tables matter:

- **`order_tracking`** — one row per purchase. Columns used: `shopName`, `stripeID`, `customerEmail`, `statusUser` (`pending` → `completed`), `reminderStep` (0/1), `startedAt`. Created by `api/aiAraya/aiFirstStep.php` (called from the Stripe webhook), not by this module.
- **`ai_araya`** — one row per submitted form. `customerDetailsLogs` holds the whole form as a JSON blob, plus `stripeID`, `createAt`. Legacy rows used a `dataLogs` column — `models/entriesTable.php` falls back to it.

Adding a form field means only adding an `<input name="...">`; it lands in the JSON blob automatically. To make it visible you must also add it to the `fields` array in `views/entries.php` (detail modal) and to the explicit `jsonData` map in `controllers/customerDetailsForm.js` (Make.com payload) — that map is a whitelist, unlisted fields are silently dropped.

## The submission flow (the one thing worth understanding)

1. Stripe webhook → `api/aiAraya/aiFirstStep.php` inserts an `order_tracking` row with `statusUser='pending'`.
2. Customer opens `views/customerDetailsForm.php?stripeID=<id>`; the ID is echoed into a hidden input.
3. On submit, `controllers/customerDetailsForm.js` serializes the form and POSTs it to `models/customerDetailsForm.php`, which:
   - computes `qualify` = `"Yes (done 24 hr)"` if `now - order_tracking.startedAt < 24h`, else `"No (over 24 hrs)"`;
   - injects `qualify` into the JSON, inserts into `ai_araya`;
   - sets `order_tracking.statusUser='completed'`, `reminderStep=1`.
4. On success the JS calls `sendData()`, which POSTs a flat JSON object to the **Make.com webhook hardcoded in `controllers/customerDetailsForm.js`**, then redirects to `views/thankyou.php`.
5. `views/entries.php` shows two DataTables: unsubmitted (`models/unsubmitTable.php`, pending `order_tracking` rows) and submitted (`models/entriesTable.php`, `ai_araya` rows).

Reminder emails are external: Make.com polls `api/aiAraya/check_reminders.php` (pending, `reminderStep=0`, older than `REMINDER_AFTER_HOURS = 21`), sends mail, then calls `api/aiAraya/mark_reminded.php`.

Note `controllers/customerDetailsForm.js` has `modulePath` local/server lines where one is commented out — check which is active before shipping.

## Conventions to keep

- Bump the `?v=x.y.z` query string on `<link>`/`<script>` tags for changed CSS/JS; the shop-facing pages are cached aggressively.
- Validation is duplicated: HTML `required` attributes plus the `requiredFields`/`requiredRadios` arrays in `controllers/customerDetailsForm.js`. Update both.
- `?testMode=true` on `customerDetailsForm.php` auto-fills the form with sample data (inline script at the bottom of the view) **and** skips both DB writes — submit only fires the Make.com webhook, tagged `testMode: "yes"`. Add new fields to the autofill block too or test submissions will be incomplete.
- Escape everything rendered from the DB with `htmlspecialchars()` — model files build table cells as raw HTML strings.
- These pages are **public** (customers reach them by link); unlike other masterPanel pages they deliberately do not include the root `chkLogin.php`. Don't add panel-only chrome to them.
