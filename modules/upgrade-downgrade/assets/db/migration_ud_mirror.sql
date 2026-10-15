-- ============================================================
-- Migration: l4u_ud_shops, l4u_ud_subscriptions
-- Database : db_localforyou
-- Purpose  : local mirror of Monday projects/contacts/subscriptions so the
--            customer-mode upgrade form can look a shop up by email fast
--            enough to sit in a customer's typing flow.
-- ============================================================

CREATE TABLE IF NOT EXISTS `l4u_ud_shops` (
    `shop_id`     VARCHAR(20)  NOT NULL COMMENT 'Monday project item id',
    `country`     CHAR(2)      NOT NULL,
    `email`       VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'lowercased, trimmed',
    `shop_name`   VARCHAR(255) NOT NULL DEFAULT '',
    `shop_type`   VARCHAR(100) NOT NULL DEFAULT '',
    `owner_name`  VARCHAR(255) NOT NULL DEFAULT '',
    `phone`       VARCHAR(50)  NOT NULL DEFAULT '',
    `board_id`    VARCHAR(20)  NOT NULL DEFAULT '',
    `synced_at`   DATETIME     NOT NULL,

    PRIMARY KEY (`shop_id`),
    INDEX `idx_country_email` (`country`, `email`),
    INDEX `idx_country_name`  (`country`, `shop_name`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Mirror of Monday project items with their linked contact details';

CREATE TABLE IF NOT EXISTS `l4u_ud_subscriptions` (
    `sub_id`        VARCHAR(20)   NOT NULL COMMENT 'Monday subscription item id',
    `shop_id`       VARCHAR(20)   NOT NULL DEFAULT '' COMMENT 'FK to l4u_ud_shops.shop_id',
    `raw_name`      VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'item name incl. SKU and price',
    `product_group` VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'mirror lookup_mkwgjt31, customer-facing',
    `price`         DECIMAL(12,2) DEFAULT NULL,
    `currency`      CHAR(3)       NOT NULL DEFAULT '',
    `account_name`  VARCHAR(255)  NOT NULL DEFAULT '' COMMENT 'text9, kept for fallback matching',
    `matched_by`    ENUM('relation','name','none') NOT NULL DEFAULT 'none',
    `synced_at`     DATETIME      NOT NULL,

    PRIMARY KEY (`sub_id`),
    INDEX `idx_shop`       (`shop_id`),
    INDEX `idx_matched_by` (`matched_by`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Mirror of active Monday subscriptions joined to mirrored shops';
