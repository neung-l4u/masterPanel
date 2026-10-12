-- Monitor alerts: show the site's country and hosting server under the Domain line.
--
-- Needed by: assets/php/check_monitor.php (renderTemplate fills {country} and {server}).
-- Without this file: alerts keep working, they just don't show the two new lines.
-- Safe to re-run: templates that already mention {server} are skipped.

UPDATE `monitor_templates`
   SET `body` = REPLACE(REPLACE(`body`,
         'Domain : {domain}\n', 'Domain : {domain}\nประเทศ : {country}\nServer : {server}\n'),
         'Domain : {url}\n',    'Domain : {url}\nประเทศ : {country}\nServer : {server}\n')
 WHERE `body` NOT LIKE '%{server}%';
