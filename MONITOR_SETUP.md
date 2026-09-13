# Website Monitor — deployment notes

Checks every active monitor, classifies *why* a site is down, and posts a Thai-language
alert to the Google Chat space "Website Down".

## Local (Docker)

Everything is declared in `docker-compose.yml` (service `web`), so it survives a rebuild:

    command: >
      bash -c "apt-get update -qq
      && apt-get install -y -qq whois netbase cron >/dev/null
      && install -m 0644 /var/www/html/assets/php/monitor_cron /etc/cron.d/monitor
      && cron
      && apache2-foreground"

**`docker-php-ext-install mysqli` must stay first in that command.** The
`php:8.4-apache` image does not ship `mysqli`, and adding a `command:` overrides
the image's own startup — drop it and every page dies with
`Fatal error: Class "mysqli" not found`, because `assets/db/db.php` is included
everywhere via `main.php`.

Apply with `docker compose up -d web`. Verify:

    docker exec masterPanel_web pgrep cron
    docker exec masterPanel_web tail -f /var/log/monitor_cron.log

## Production (report.localforyou.com)

1. Run the migrations, in order:
   - `assets/sql/monitor_templates.sql`
   - `assets/sql/monitor_templates_reasons.sql`
2. Install the WHOIS client: `whois` **and** `netbase`.
   Without `netbase` the whois lookup cannot resolve its service port and every
   domain falls back to the generic "down" wording (no crash, just less detail).
3. Add the cron entry (cPanel → Cron Jobs, every 5 minutes):

       */5 * * * * /usr/bin/php /home/<user>/public_html/assets/php/check_monitor.php >> ~/monitor_cron.log 2>&1

4. Put the Google Chat webhook URL in `assets/php/chat_webhook.txt`
   (gitignored) or set `MONITOR_CHAT_WEBHOOK` in the environment.

## Alert types

| tpl_key | When |
|---|---|
| `wp_fatal` | Page returns HTTP 200 but contains the WordPress critical-error text |
| `expired` | DNS fails and WHOIS shows a past expiry date or a hold status |
| `unregistered` | DNS fails and WHOIS has no record of the domain |
| `down` | DNS resolves but the server answers badly (404/500/timeout) |
| `recovered` | Site came back up |
| `ssl` | Certificate expires within 30 days (once per day) |

Wording is editable at `main.php?p=managerWebhook` — no code change needed.
Alerts only fire on a *status change*, so a site that stays down is not repeated.

## Log growth

`/var/log/monitor_cron.log` only receives PHP errors, so it stays near-empty in
normal operation. `monitor_logs` grows by roughly (active monitors × 288) rows per
day at a 5-minute interval; prune it if it ever becomes large:

    DELETE FROM monitor_logs WHERE checked_at < NOW() - INTERVAL 90 DAY;
