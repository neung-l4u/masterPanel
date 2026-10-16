-- ============================================================
-- Read-only check: which up_down tables exist, and do they hold anything?
-- Database : db_localforyou
--
-- Run this before and after a deployment. It writes nothing.
--
-- What you want to see when the deployment is finished:
--
--   l4u_up_down_logs            present, rows > 0   (the submissions, renamed)
--   l4u_up_down_shops           present, rows > 0   (filled by the sync)
--   l4u_up_down_subscriptions   present, rows > 0   (filled by the sync)
--   upgrade_downgrade_logs      MISSING             (renamed away)
--   l4u_ud_shops                MISSING             (renamed away, or never made)
--   l4u_ud_subscriptions        MISSING
--
-- A new table showing 0 rows is not an error on its own: the two mirror tables
-- are empty until syncMondayMirror.php has run once, and an empty mirror is
-- exactly what makes lookupByEmail answer found:false for every address.
-- ============================================================

SELECT
    want.name                                    AS table_name,
    IF(t.TABLE_NAME IS NULL, 'MISSING', 'present') AS state,
    COALESCE(t.TABLE_ROWS, 0)                    AS approx_rows,
    want.note                                    AS expected
FROM (
          SELECT 'l4u_up_down_logs'          AS name, 'new  - must exist and keep its rows' AS note
    UNION SELECT 'l4u_up_down_shops'              , 'new  - filled by the sync'
    UNION SELECT 'l4u_up_down_subscriptions'      , 'new  - filled by the sync'
    UNION SELECT 'upgrade_downgrade_logs'         , 'old  - should be MISSING after the rename'
    UNION SELECT 'l4u_ud_shops'                   , 'old  - should be MISSING'
    UNION SELECT 'l4u_ud_subscriptions'           , 'old  - should be MISSING'
) AS want
LEFT JOIN information_schema.TABLES t
       ON t.TABLE_SCHEMA = DATABASE()
      AND t.TABLE_NAME   = want.name
ORDER BY want.note, want.name;

-- Exact counts for the tables that exist. TABLE_ROWS above is an estimate on
-- InnoDB, which is fine for "is it empty?" but not for reconciling a rename.
SELECT 'l4u_up_down_logs' AS table_name, COUNT(*) AS exact_rows FROM `l4u_up_down_logs`;
