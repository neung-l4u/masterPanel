-- Send log for the AI Araya daily brief (modules/arayaBrief).
-- Safe to apply more than once. brief_date is indexed but deliberately not
-- unique: a failed attempt followed by a successful one must both be recorded.

CREATE TABLE IF NOT EXISTS `l4u_araya_brief_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `brief_date` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL COMMENT 'sent / failed / skipped',
  `message` MEDIUMTEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_brief_date` (`brief_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
