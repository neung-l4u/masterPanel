# Monday mirror sync

Mirrors Monday into `l4u_up_down_shops` and `l4u_up_down_subscriptions` (database
`db_localforyou`) so the customer-mode upgrade form can find a shop by email in
milliseconds instead of the seconds a live Monday query takes.

## What reads this

`assets/API/lookupByEmail.php` (query in `models/shopLookup.php`), called by
`controllers/upgradeForm.js` when a customer enters their email in
`views/upgradeForm.php?mode=customer`.

Nothing else. Staff mode still queries Monday live through
`assets/API/selectProjectCountry/live.php` and `assets/API/getSubscriptions.php`,
which this sync does not touch.

Files in this directory: `syncMondayMirror.php` (entry point), `mondayClient.php`
(Monday API calls), `mondayMirror.php` (extraction and per-country sync),
`mirrorMatch.php` (subscription-to-shop matching), `mirrorRepo.php` (database
writes).

## First-time setup

Three steps. Two of these files are gitignored, so a fresh checkout does not
have them and nothing works until all three are done.

**1. Create the database connection.** `assets/db/initDB.php` holds the password
and is gitignored, so only `initDB_example.php` and `initDB_server.php` ship.
Copy one and set the credentials for the target database:

    cp modules/upgrade-downgrade/assets/db/initDB_example.php \
       modules/upgrade-downgrade/assets/db/initDB.php

Without it the sync exits 1 with `FATAL: cannot initialise: Failed opening
required '.../initDB.php'`, written to the log, and the lookup endpoint returns
503 with a generic body.

**2. Apply the migrations.** Both are idempotent (`CREATE TABLE IF NOT
EXISTS`), and on a database that predates the up_down naming run
`rename_up_down_tables.sql` FIRST -- before uploading the renamed code, because
the code reads the new names only:

    mysql -u root -p db_localforyou < modules/upgrade-downgrade/assets/db/rename_up_down_tables.sql
    mysql -u root -p db_localforyou < modules/upgrade-downgrade/assets/db/migration_up_down_logs.sql

The rename is guarded per table, so running it twice does nothing. It RENAMEs
`upgrade_downgrade_logs` rather than recreating it, because that table holds
real customer submissions which cannot be regenerated. The two mirror tables
can be, so a fresh database may skip their rename and just create them below.

**2b. Create the mirror tables.** Idempotent
(`CREATE TABLE IF NOT EXISTS`):

    mysql -u root -p db_localforyou < modules/upgrade-downgrade/assets/db/migration_up_down_mirror.sql

**3. Create the module config,** also gitignored:

    cp modules/upgrade-downgrade/assets/config.example.php \
       modules/upgrade-downgrade/assets/config.php

Then set `monday_token` (see Configuration below). Leave `sync_token` empty
unless the sync has to be triggerable over HTTP.

Run the sync once by hand and read the summary before scheduling anything.

## Running it

    php modules/upgrade-downgrade/sync/syncMondayMirror.php            # all countries
    php modules/upgrade-downgrade/sync/syncMondayMirror.php TH AU      # some countries

A full run takes about 3 minutes (183s on the first run).

- Exit 0: every country and the subscriptions pass synced.
- Exit 1: at least one country or the subscriptions pass errored (the failing
  part keeps its previous rows), or the script hit a fatal (cannot reach the
  database, bad country filter).

It is CLI-only unless `sync_token` is set in `assets/config.php`. Over HTTP it
returns 403 while `sync_token` is empty, or when `?token=` does not match.

Output goes to stdout and to a rolling log, `assets/logs/mirror-sync.log`.

## Schedule

> **NOT YET REGISTERED.** No scheduled task exists for this. It must be set up
> when this branch is deployed. Until then the mirror only holds whatever the
> last manual run wrote, and after 48 hours lookups are marked stale.

Twice daily. A customer who signed up one to three days ago is not using the
upgrade form yet, so twelve-hour-old data is accurate enough for the people who
reach this form.

**Production is Linux (cPanel), so cron is the one that matters.** The script
resolves every path from `__DIR__`, so it needs no working directory and a bare
absolute path is enough:

    0 6,18 * * * /usr/local/bin/php /home/USER/public_html/modules/upgrade-downgrade/sync/syncMondayMirror.php >/dev/null 2>&1

Find both paths on the box rather than assuming them: shared hosts put PHP
somewhere different per account and often ship several versions.

    which php; php -v | head -1
    find ~ -maxdepth 4 -type d -name upgrade-downgrade

Install with `crontab -e`, confirm with `crontab -l`.

**The hours are server time, not Bangkok time.** The script sets `Asia/Bangkok`
for its own timestamps, so the log can read 06:00 while cron actually fired at
whatever the host's clock said. Check the offset with `date` and shift the hours
if 06:00 and 18:00 local genuinely matter; for a twice-daily mirror they usually
do not.

To prove it works without waiting for 06:00, run the exact command from the
crontab line by hand, then check the log picked up a fresh entry:

    tail -5 modules/upgrade-downgrade/assets/logs/mirror-sync.log

A cron that never logs is almost always the wrong PHP binary (a CGI build that
needs `-q`, or a version too old) or a path typo. Fix the crontab line, not the
script.

Windows Task Scheduler, for a local XAMPP checkout (two tasks; adjust the PHP
and project paths):

    schtasks /create /tn "L4U UD Mirror Sync AM" /sc daily /st 06:00 /tr "C:\xampp\php\php.exe C:\xampp\htdocs\masterPanel\modules\upgrade-downgrade\sync\syncMondayMirror.php"
    schtasks /create /tn "L4U UD Mirror Sync PM" /sc daily /st 18:00 /tr "C:\xampp\php\php.exe C:\xampp\htdocs\masterPanel\modules\upgrade-downgrade\sync\syncMondayMirror.php"

Check with `schtasks /query /tn "L4U UD Mirror Sync AM"`, test with
`schtasks /run /tn "L4U UD Mirror Sync AM"`.

The script keeps its own log, so discarding stdout loses nothing.

## Reading the summary

    [2026-10-05 06:00:04 +07] ud mirror sync ok (182.3s)
      TH: 30 shops (0 new, 30 updated, 0 removed), 14 with an email
      ...
      subscriptions: 915 active (908 by relation, 0 by name, 7 unmatched), 0 removed

This line is the monitoring signal. Read it after any run you care about, and
skim the log periodically.

Reference run (first real sync): CA 3 shops, AU 1323, NZ 104, UK 251, US 771,
TH 30. 2482 shops total, 1691 with an email, 915 active subscriptions, 183s.

- **`with an email`** well below the shop count means Monday contacts lack
  email addresses. Those shops can never be found by the form. TH is 14 of 30
  today; the other 16 also have no owner name or phone, because they have no
  linked contact at all. That is a Monday data gap, not a sync fault.
- **`by name`** or **`unmatched`** climbing means the `connect_boards`
  ("Projects") relation is being left unset on the Active Subscriptions board.
  The name fallback covers some of it, but unmatched subscriptions belong to no
  shop and never appear in the form.
  - Currently 7 are unmatched, all test records ("Test US Restaurant",
    "Test Product"). So the `NOTE: some subscriptions did not resolve via the
    Monday relation column` line fires on **every** run and has stopped being a
    signal. Judge by whether the numbers grow beyond the known 7, not by the
    NOTE line, until those test records are removed or linked.
- **`ERROR`** on a country means that country kept its previous rows, by design.
  Safe to leave until the next run; investigate if it repeats.

## Page cap

`UP_DOWN_MAX_PAGES` in `mondayMirror.php` is 50 pages of 100 = 5000 items. The
**Active Subscriptions board is the binding constraint**, not the per-country
project boards: it already holds 2865 items (915 of them active). The first
attempt failed at the old cap of 20 (2000 items).

The guard turns an overflow into a visible error (`page cap reached with more
pages pending; raise UP_DOWN_MAX_PAGES`) rather than silently truncating, so the
subscriptions pass keeps its old rows. If you ever see that message, raise the
constant. Do not assume the board shrank.

## If the sync stops

The form degrades, it does not break. Lookups keep answering from the last good
data, and past 48 hours the endpoint marks the response `stale` and the form
tells the customer the details may be out of date. A customer whose shop is not
found simply fills the form in by hand, which is how the form worked before this
existed.

## Configuration

`assets/config.php` (gitignored; copy from `assets/config.example.php`):

- `monday_token` - needs read access to the six project boards, Contacts &
  Accounts (1862866069), and Active Subscriptions (1881439330).
- `sync_token` - only needed to trigger the sync over HTTP. Leave empty (the
  default) to allow CLI invocation only.

## Logs

`assets/logs/` holds `mirror-sync.log` and `lookup.log` (every lookup, with a
hashed email and its outcome). The directory is gitignored.

- `assets/logs/.htaccess` denies web access to them. **It is Apache-only.** An
  nginx deployment ignores it and would serve the logs publicly, so it needs an
  equivalent `location` deny added separately.
- Because the directory is gitignored, the `.htaccess` had to be force-added
  (`git add -f modules/upgrade-downgrade/assets/logs/.htaccess`). Anyone
  recreating it must do the same, or the deny silently never deploys.

## Known gap: no rate limiting

`lookupByEmail.php` has no rate limiting. An attempt costs about 11ms
unthrottled, so the endpoint can be used to probe whether an address belongs to
a customer. This is a known, deliberate gap. The compensating control is
`lookup.log`, which records every call so probing is detectable. Add a limiter
if the log shows it happening.

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
| Contacts | `contact_email` | email - the lookup key |
| Active Subscriptions | `status` | Subscription Status |
| Active Subscriptions | `numbers`, `status0` | Amount, Currency |
| Active Subscriptions | `text9` | Account Name (fallback match only) |
| Active Subscriptions | `connect_boards` | relation to Projects - the join |
| Active Subscriptions | `lookup_mkwgjt31` | Product Group - customer-facing name |
