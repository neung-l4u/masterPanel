-- ============================================================
-- Rename: ud / upgrade_downgrade  ->  up_down
-- Database : db_localforyou
--
-- Run this ONCE per deployment, BEFORE uploading the renamed code. The code
-- reads the new names only, so a deployment that uploads first and renames
-- second leaves the forms broken in between.
--
-- Safe to run on a database that has already been renamed: each statement is
-- guarded by a check against information_schema, so a second run does nothing
-- rather than failing.
-- ============================================================

-- ------------------------------------------------------------
-- 1. upgrade_downgrade_logs -> l4u_up_down_logs
--
-- This one holds real customer submissions and cannot be regenerated, so it is
-- RENAMEd rather than recreated. A rename keeps the rows, the ids and the
-- AUTO_INCREMENT position.
-- ------------------------------------------------------------
SET @db := DATABASE();

SET @have_old := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'upgrade_downgrade_logs');
SET @have_new := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'l4u_up_down_logs');

SET @sql := IF(@have_old = 1 AND @have_new = 0,
               'RENAME TABLE `upgrade_downgrade_logs` TO `l4u_up_down_logs`',
               'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- 2. l4u_ud_shops -> l4u_up_down_shops
-- 3. l4u_ud_subscriptions -> l4u_up_down_subscriptions
--
-- These two are a mirror of Monday data and are rebuilt by the sync, so a
-- deployment that has never run the migration can skip straight to
-- migration_up_down_mirror.sql and sync. The renames below are only for a
-- database that already carries the old mirror tables.
-- ------------------------------------------------------------
SET @have_old := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'l4u_ud_shops');
SET @have_new := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'l4u_up_down_shops');

SET @sql := IF(@have_old = 1 AND @have_new = 0,
               'RENAME TABLE `l4u_ud_shops` TO `l4u_up_down_shops`',
               'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @have_old := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'l4u_ud_subscriptions');
SET @have_new := (SELECT COUNT(*) FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'l4u_up_down_subscriptions');

SET @sql := IF(@have_old = 1 AND @have_new = 0,
               'RENAME TABLE `l4u_ud_subscriptions` TO `l4u_up_down_subscriptions`',
               'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------
-- What you should see afterwards. The three new names present, and none of the
-- three old ones.
-- ------------------------------------------------------------
SELECT TABLE_NAME, TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('l4u_up_down_logs', 'l4u_up_down_shops', 'l4u_up_down_subscriptions',
                     'upgrade_downgrade_logs', 'l4u_ud_shops', 'l4u_ud_subscriptions')
ORDER BY TABLE_NAME;
