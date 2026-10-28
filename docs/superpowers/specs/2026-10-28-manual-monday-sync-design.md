# Manual Monday Mirror Sync — design

**Date:** 2026-10-28
**Module:** `modules/upgrade-downgrade` + `pages/`
**Status:** approved, ready for implementation plan

## Problem

Staff edit a record in Monday — a customer's email address, or their active
subscriptions — and the upgrade/downgrade form keeps showing the old values
with no way to force a refresh.

The cause is not a cache bug. The customer-mode email lookup does not read
Monday at all: it reads a local mirror in `l4u_up_down_shops` /
`l4u_up_down_subscriptions`, populated by `sync/syncMondayMirror.php`. That
script is sound and tested, but `sync/README.md` records that its schedule was
never registered:

> **NOT YET REGISTERED.** No scheduled task exists for this.

So the mirror holds whatever the last manual CLI run wrote. Past 48 hours the
lookup marks itself stale and the form warns the customer, but nothing lets
staff bring the data up to date without shell access to the server.

## Scope

This feature syncs **the mirror only** — the data behind customer-mode email
lookup. That is where both reported symptoms (stale subscriptions, stale email)
originate.

Staff-mode reads are deliberately out of scope. They query Monday live through
`assets/API/selectProjectCountry/live.php` (1-hour file cache) and
`assets/API/getSubscriptions.php` (15-minute file cache). Both already accept
`?bust=1`, both expire on their own, and neither touches the mirror. A second
button for them can be added later if staff ask; bundling it now would widen
this page for a problem nobody has reported.

## Approach

A new admin page calls the existing sync script over HTTP with the token the
script already supports. **No sync logic changes.**

```
pages/mondaySync.php            UI: country checkboxes, buttons, last-sync status
  └─ POST per country ──→ pages/api/mondaySyncRun.php      server-side proxy
                            └─ syncMondayMirror.php?token=…&countries=TH&format=json
                                 └─ upDownRunSync($db, ['TH'])      unchanged
```

### Why one request per country

A full six-country run takes about 183 seconds. The script sets no
`set_time_limit`, so over HTTP it inherits PHP's default and a long country
would be killed mid-run.

The page therefore issues one request per country, sequentially, and renders
each result as it lands. "Sync all" is six browser-driven requests, not one
three-minute request. This gives real progress for free and keeps one country's
failure from taking down the rest — matching the script's existing per-country
error isolation.

### Changes to `syncMondayMirror.php`

Two additions, both confined to the HTTP path. The CLI path and its output are
untouched, so the documented cron command keeps working byte for byte.

1. **`set_time_limit(300)` on the HTTP path.** Mirrors the CLI reality that a
   country can take minutes. Without it the largest board dies partway.

2. **`?format=json`.** Returns the `$summary` array as JSON instead of the
   formatted text block. The array already exists and already carries every
   field the text summary prints — this only skips the text formatting, so the
   page can render a table instead of parsing prose. Default stays text.

The log line is still written in both formats. An HTTP-triggered sync must
leave the same audit trail in `assets/logs/mirror-sync.log` as a cron run.

### Token handling

`sync_token` lives in `modules/upgrade-downgrade/assets/config.php` (gitignored;
the key already exists in `config.example.php`, currently empty).

The token is read server-side by `pages/api/mondaySyncRun.php` and attached
there. **It is never sent to the browser.** Emitting it into JavaScript would
put it in view-source, letting anyone who loaded the page trigger unlimited
three-minute Monday syncs.

Deployment needs `sync_token` set once. While it is empty the script returns 403
by design, and the page surfaces that as a readable setup message rather than a
bare failure.

## Authentication

`pages/mondaySync.php` is reached as `main.php?p=mondaySync`, so it inherits the
session guard in `main.php:4` that redirects anonymous visitors to the login
page. Registering the route in `assets/php/page_navigate.php` is what makes this
apply; a page placed outside that flow would be unprotected.

The proxy endpoint repeats the session check itself. It is directly reachable by
URL, and inheriting a guard only works for code that actually runs behind it.

## UI

```
Monday Mirror Sync
Last sync: 2026-10-05 06:00   ⚠ 23 days ago (stale)

[✓] TH   30 shops, 14 with email
[ ] AU   1323 shops
[ ] NZ   104          [ ] UK   251
[ ] US   771          [ ] CA   3

[ Sync selected ]   [ Sync all ]

─ Results ─────────────────────────
TH   ✓ 30 shops (0 new, 30 updated), 14 with email    18.2s
AU   ⟳ syncing…
```

- Per-country checkboxes; "Sync all" iterates all six in sequence.
- Both buttons disable while a run is in flight, preventing concurrent syncs of
  the same country.
- Per-country shop counts come from the mirror tables, so the page shows what is
  stored now, not what Monday holds.
- "Last sync" is `MAX(synced_at)` from `l4u_up_down_shops`, flagged stale past
  48 hours to match `UP_DOWN_STALE_AFTER_HOURS` in `models/shopLookup.php`.
  Reusing that constant keeps the page's warning and the customer-facing stale
  banner from drifting apart.

## Error handling

The script's existing contract is preserved: a country that errors keeps its
previous rows rather than being emptied. The page renders that country's row in
red with the error text and continues to the next country.

Three failure modes get distinct messages, because the fixes are different:

| Condition | Shown as |
| --- | --- |
| `sync_token` empty or mismatched (403) | "Sync is not configured — set `sync_token` in config.php" |
| Country-level error from Monday | The error, plus "previous data kept" |
| Request timeout / network failure | "Sync may still be running on the server — check the log before retrying" |

The last one matters: a browser-side timeout does not stop the server-side run,
and a staff member who retries immediately would start a second concurrent sync
of the same country.

## Testing

Extends the module's existing PHP test files.

- `?format=json` returns the documented shape; without it, output is unchanged
  text (guards the cron contract).
- Empty `sync_token` returns 403; wrong token returns 403; correct token
  proceeds (guards the existing security boundary).
- An invalid country filter still fatals as before.
- `MAX(synced_at)` staleness calculation flips at the 48-hour boundary.

Not tested: a real sync against Monday's API. It needs live credentials and
network access, the existing suite does not attempt it either, and the code
path below `upDownRunSync()` is unchanged by this work.

## Follow-up, not in this scope

The cron schedule is still unregistered. This page relieves the symptom but
does not replace the schedule — without cron, the mirror only ever updates when
somebody remembers to press the button. The crontab line is already written in
`sync/README.md` and should be installed at deploy; the button is for when staff
need data sooner than the next scheduled run.
