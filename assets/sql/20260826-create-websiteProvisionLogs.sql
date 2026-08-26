-- Audit log for provisioning steps triggered from the Website List form
-- (cPanel account creation on WHM, and later WordPress installs).

CREATE TABLE IF NOT EXISTS `websiteProvisionLogs` (
    `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `wID`         int(11) DEFAULT NULL COMMENT 'websiteList.wID',
    `svID`        tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'L4UServers.svID',
    `step`        varchar(50) NOT NULL COMMENT 'e.g. cpanel_createacct',
    `status`      enum('success','failed','skipped') NOT NULL,
    `cpanelUser`  varchar(255) DEFAULT NULL,
    `domain`      varchar(255) DEFAULT NULL,
    `httpCode`    smallint(6) DEFAULT NULL,
    `message`     text,
    `response`    longtext COMMENT 'Raw WHM response, secrets stripped',
    `create_by`   int(11) DEFAULT NULL COMMENT 'staffs.sID',
    `create_at`   datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wID` (`wID`),
    KEY `idx_step_status` (`step`, `status`),
    KEY `idx_create_at` (`create_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
