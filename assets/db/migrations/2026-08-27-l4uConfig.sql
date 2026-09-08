-- Shared configuration values that do not belong in the repository.
--
-- Licence keys and storage credentials are needed when a site is set up, but
-- committing them would put them in everyone's checkout. They live here
-- instead, so the code ships without them and each environment carries its
-- own.
--
-- Insert the values by hand once per environment; the setup reads whatever is
-- present and skips the steps whose keys are missing.

CREATE TABLE IF NOT EXISTS `l4uConfig` (
    `cfKey`     VARCHAR(64) NOT NULL,
    `cfValue`   TEXT        NULL DEFAULT NULL,
    `cfRemark`  VARCHAR(255) NULL DEFAULT NULL,
    `update_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `update_by` INT         NULL DEFAULT NULL,
    PRIMARY KEY (`cfKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The keys the WordPress setup looks for. Values are left blank on purpose:
-- fill them in per environment and the matching step starts running.
INSERT INTO `l4uConfig` (`cfKey`, `cfValue`, `cfRemark`) VALUES
    ('akismet_api_key',    '', 'Akismet licence key. Blank leaves Akismet unconfigured.'),
    ('updraft_s3_key',     '', 'Amazon S3 access key id for UpdraftPlus backups.'),
    ('updraft_s3_secret',  '', 'Amazon S3 secret access key.'),
    ('updraft_s3_bucket',  '', 'S3 bucket, optionally with a path, e.g. l4u-backups/sites.'),
    ('updraft_s3_region',  '', 'S3 region, e.g. ap-southeast-1. Blank uses the plugin default.')
ON DUPLICATE KEY UPDATE `cfRemark` = VALUES(`cfRemark`);
