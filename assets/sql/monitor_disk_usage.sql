-- Latest disk usage per cPanel account, refreshed by assets/php/check_disk.php.
-- Kept in its own table so the monitor page can sort and filter on it without
-- calling WHM on every page load.
CREATE TABLE IF NOT EXISTS `disk_usage` (
  `cpanel_user` varchar(64)  NOT NULL,
  `svID`        int(11)      DEFAULT NULL,
  `used`        varchar(32)  DEFAULT NULL,
  `quota`       varchar(32)  DEFAULT NULL,
  `percent`     decimal(6,2) DEFAULT NULL,
  `checked_at`  datetime     DEFAULT current_timestamp(),
  PRIMARY KEY (`cpanel_user`),
  KEY `idx_percent` (`percent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
