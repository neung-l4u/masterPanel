<?php
/**
 * Send log for the daily brief.
 *
 * Lets the cron script check "already posted today?" before sending, and
 * leaves a record of every attempt so a silent morning can be diagnosed.
 * Every value is bound, never interpolated: the body holds shop names with
 * apostrophes and Thai text.
 */

/** The schema statement. Kept identical to assets/db/create_brief_logs.sql. */
function arayaBriefSchemaSql(): string
{
    return "CREATE TABLE IF NOT EXISTS `l4u_araya_brief_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `brief_date` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL COMMENT 'sent / failed / skipped',
  `message` MEDIUMTEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_brief_date` (`brief_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
}

/**
 * True when a 'sent' row exists for this date. 'failed' and 'skipped' rows do
 * not count, so a failed morning can be retried.
 */
function arayaBriefAlreadySentToday(object $db, string $ymd): bool
{
    $row = $db->query(
        "SELECT id FROM l4u_araya_brief_logs WHERE brief_date = ? AND status = ? LIMIT 1",
        [$ymd, 'sent']
    )->fetchArray();
    // The db wrapper returns an empty array, not null, when nothing matched.
    return !empty($row);
}

/** Record one attempt. Status is 'sent', 'failed' or 'skipped'. */
function arayaBriefLogSend(object $db, string $ymd, string $status, string $message): void
{
    $db->query(
        "INSERT INTO l4u_araya_brief_logs (brief_date, status, message, created_at) VALUES (?, ?, ?, NOW())",
        [$ymd, $status, $message]
    );
}
