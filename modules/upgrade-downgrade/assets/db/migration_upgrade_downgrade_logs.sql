-- ============================================================
-- Migration: upgrade_downgrade_logs
-- Database : db_localforyou
-- ============================================================

CREATE TABLE IF NOT EXISTS `upgrade_downgrade_logs` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- Form meta
    `formType`        ENUM('upgrade','downgrade') NOT NULL,
    `formVersion`     VARCHAR(20)     DEFAULT NULL,
    `formMode`        VARCHAR(20)     NOT NULL DEFAULT 'customer',
    `testMode`        TINYINT(1)      NOT NULL DEFAULT 0,
    `status`          ENUM('pending','processing','completed','cancelled')
                                      NOT NULL DEFAULT 'pending',

    -- Key identifiers (denormalised for fast lookup)
    `stripeId`        VARCHAR(255)    DEFAULT NULL,
    `mondayProjectId` VARCHAR(100)    DEFAULT NULL,
    `shopName`        VARCHAR(255)    DEFAULT NULL,
    `emailAddress`    VARCHAR(255)    DEFAULT NULL,
    `country`         VARCHAR(10)     DEFAULT NULL,
    `requestType`     VARCHAR(255)    DEFAULT NULL,

    -- Full payload
    `payload`         LONGTEXT        NOT NULL,

    -- Timestamps
    `submittedAt`     DATETIME        DEFAULT NULL,
    `createdAt`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updatedAt`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_formType`        (`formType`),
    INDEX `idx_stripeId`        (`stripeId`),
    INDEX `idx_mondayProjectId` (`mondayProjectId`),
    INDEX `idx_shopName`        (`shopName`),
    INDEX `idx_emailAddress`    (`emailAddress`),
    INDEX `idx_createdAt`       (`createdAt`),
    INDEX `idx_testMode`        (`testMode`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores all upgrade and downgrade form submissions';
