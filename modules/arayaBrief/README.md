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
  *live usage* (minutes, activity). If gcal is down the brief is still produced
  from Monday alone and flagged `gcal_unavailable`; gap noise is suppressed.

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

Check the server timezone; the script sets Asia/Bangkok for its own date, but
cron's `0 7` is in the server's clock. The script is CLI only (HTTP gets 403) and
safe to run twice: a date with a `sent` row is skipped. A `failed` row does not
block, so re-running after a fix posts the brief. Failures exit 1.

## Tests

```
for f in modules/arayaBrief/tests/test_*.php; do php "$f" || break; done
```
Each file must end `Failed: 0`.

## Dashboard parameters

- `?fixture=sample` - render canned sample data (no network).
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
