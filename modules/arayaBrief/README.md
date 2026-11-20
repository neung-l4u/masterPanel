# Araya Daily Brief

Reports the fleet status of the "AI Araya" voice-receptionist product. Two
outputs from one data path (`models/loadBrief.php`):

- **Dashboard** in the admin panel (`modules/arayaBrief/index.php`).
- **Google Chat message** posted to the "IT in house" space at 07:00
  Asia/Bangkok by `api/arayaBrief/sendChat.php` (cron, CLI only).

## Sources: who is authoritative for what

- **Monday** (board 2032016220) is authoritative for *who is a customer*. Without
  it there is no report; the brief fails rather than post an empty one.
- **gcal** (`gcal-booking-api`, `/api/admin/araya-summary`) is authoritative for
  *live usage* (minutes, activity, call quality). If gcal is down the brief is
  still produced from Monday alone and flagged `gcal_unavailable`; gap noise is
  suppressed.

## Call quality and conversion

Per shop, for the last 7 and 30 days: calls, forwarded, short (under 15 s),
silent, booked, bookings that failed, and minutes. Three rates are derived from
those counts, and four findings from the rates. All findings are judged on one
window (`ARAYA_RATE_WINDOW`, currently `d7`), so nothing in a section is a
7-day rate sitting next to a 30-day one.

| Finding | Rule | Judged against |
|---|---|---|
| Bookings that failed | at least `ARAYA_BOOKING_FAILED_ALERT` failed attempts | nothing: a failure is a bug, not a rate |
| Conversion below the fleet | `ARAYA_CONVERSION_ALERT_MARGIN_PTS` points or more under | the fleet |
| Callers hanging up early | `ARAYA_SHORT_ALERT_PCT`% or more of calls under 15 s | a fixed threshold |
| Forwarding above the fleet | `ARAYA_RATE_ALERT_MARGIN_PTS` points or more over | the fleet |

Three rules that are easy to get wrong, and are pinned by
`tests/test_call_rates.php`:

- **A rate off a small sample is not shown at all.** Under
  `ARAYA_RATE_MIN_CALLS` calls, the rate is null and the shop is never flagged:
  one forwarded call out of three is 33% and would outrank a shop with a real
  problem. The dashboard prints `–` for it.
- **Conversion is a share of the calls that could have booked**, not of every
  call, and calls whose booking outcome gcal does not know (`booked_unknown`)
  leave the denominator. Counting misdials as lost bookings, or counting calls
  from before the booking instrumentation as failures, would report the whole
  fleet as collapsing.
- **Short hang-ups use a fixed threshold, the other two use the fleet.** The
  fleet's own short-call rate is high enough that comparing with it would
  excuse every shop.

The fleet baseline comes from `fleet_calls` in the gcal response, summed over
exactly the shops in that response. A gcal that does not send it (an older
deploy) yields no baseline: the two fleet-relative findings are then simply not
produced, and the dashboard says so rather than comparing against zeros. The
same applies during a gcal outage, when no shop has call figures at all.

The booking figures require `scripts/add_booking_outcome_tracking.py` to have
been run on the gcal side. Calls recorded before that have no known booking
outcome and count towards `booked_unknown`, not towards failures.

## Setup

1. `cp modules/arayaBrief/assets/config.example.php modules/arayaBrief/assets/config.php`
   (gitignored, never commit it). Keys, all required non-empty strings:
   - `gcal_base_url` - base URL of the gcal Heroku app, no trailing slash.
   - `gcal_admin_key` - **must equal `ARAYA_ADMIN_KEY` in the gcal Heroku app's
     config vars** (`heroku config:get ARAYA_ADMIN_KEY`). A mismatch shows up as
     `gcal fetch failed: ... HTTP 401/403` in the cron log (the brief still posts, flagged gcal unavailable).
   - `monday_token` - Monday API token with read access to the board.
   - `chat_webhook_url` - incoming webhook of the Chat space. It is a
     credential: the script hands it to curl only and never logs it.
2. DB credentials come from the shared, gitignored `assets/db/initDB.php`.
3. **Production: apply the schema by hand first.**
   `mysql <db> < modules/arayaBrief/assets/db/create_brief_logs.sql`
   (creates `l4u_araya_brief_logs`). Without it the script cannot check or log.

## Cron (Linux/cPanel; `schtasks` does not exist there)

```
0 7 * * * /usr/bin/php /home/<user>/public_html/api/arayaBrief/sendChat.php >> /home/<user>/logs/araya-brief.log 2>&1
```

The script sets Asia/Bangkok for its own date, but cron's `0 7` is the SERVER
clock. Check it with `date`. If the server is not on Bangkok time, either add
`CRON_TZ=Asia/Bangkok` on the line above the schedule (Vixie cron and most
cPanel builds support it) or shift the hour by the offset.

`/usr/bin/php` is often wrong on cPanel -- it may be `/usr/local/bin/php` or an
`ea-phpNN` path. Confirm with `which php`.

The script is CLI only (HTTP gets 403) and safe to run twice: a date with a
`sent` row is skipped, and a MySQL named lock stops two overlapping runs from
both posting. A `failed` row does not block, so re-running after a fix posts
the brief. Failures exit 1, so cron will mail you.

To re-run by hand after a failure, use the same command as the cron line.
## Tests

```
for f in modules/arayaBrief/tests/test_*.php; do php "$f" || break; done
```
Each file must end `Failed: 0`.

## Dashboard parameters

- `?fixture=sample` - render canned sample data (no network). The fixture is
  built so that all four call-quality findings have at least one shop in them;
  `tests/test_outage_noise.php` pins that, since a finding with no example in
  the sample data is one nobody would notice had stopped rendering.
- `?nocache=1` - bypass the 5-minute cache. The cron always reads live.

## Diagnosing a bad or silent morning

```
tail /home/<user>/logs/araya-brief.log
SELECT id, brief_date, status, LEFT(message,80) FROM l4u_araya_brief_logs ORDER BY id DESC LIMIT 10;
```
No row for the date at all = cron did not run or DB was unreachable.

| Failure | Message in log / `message` column |
|---|---|
| Not configured | `Missing config: ...` or `Config ... is invalid (missing: <keys>)` - fix `config.php` |
| Monday down | `Monday request failed (curl error N)`, `Monday returned HTTP N`, `Monday returned a GraphQL error` - no brief is posted; retry later |
| gcal down | Does **not** fail the run: the brief posts from Monday only and says gcal was unavailable. The cause appears only in the cron log as `gcal fetch failed: ...`; HTTP 401/403 there means `gcal_admin_key` does not match `ARAYA_ADMIN_KEY`. |
| Chat webhook | `Chat webhook returned HTTP N`. HTTP 0 means the request never completed and carries a curl errno: 6 = DNS, 7 = refused, 28 = timeout, 35/60 = TLS. A 404 usually means the webhook was revoked in the space |
| Database | `Failed to connect to MySQL`, or `aborted before completing` with exit 1. No row is written, so the log table shows nothing for that date. Check credentials in `assets/db/initDB.php` and that `l4u_araya_brief_logs` exists |
