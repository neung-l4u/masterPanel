-- Google Chat alert message templates for the monitor system.
-- Run on production before deploying pages/managerWebhook.php.
CREATE TABLE IF NOT EXISTS `monitor_templates` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `tpl_key`   varchar(50)  NOT NULL,
  `title`     varchar(255) NOT NULL,
  `body`      text         NOT NULL,
  `mention_all` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1)   NOT NULL DEFAULT 1,
  `update_at` datetime     DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tpl_key` (`tpl_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`) VALUES
('down',      '[DOWN] {name} is unreachable',
 'Website: {name}\nURL: {url}\nStatus: DOWN\nHTTP: {httpCode}\nError: {errorMsg}\nTime: {time}', 1),
('recovered', '[RECOVERED] {name} is back online',
 'Website: {name}\nURL: {url}\nStatus: RECOVERED\nResponse: {responseMs}ms\nTime: {time}', 0),
('ssl',       '[SSL WARNING] {name} - {sslDaysLeft} days left',
 'Website: {name}\nURL: {url}\nSSL Expiry: {sslExpiry}\nDays Left: {sslDaysLeft}\nTime: {time}', 0);
