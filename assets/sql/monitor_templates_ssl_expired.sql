-- Adds the "certificate already expired" alert and retunes the SSL warning wording.
-- Run AFTER assets/sql/monitor_templates_reasons.sql on production.
INSERT INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`, `is_active`) VALUES
('ssl_expired',
 'SSL หมดอายุแล้ว - เว็บเข้าไม่ได้',
 'Domain : {domain}\nSSL หมดอายุเมื่อ : {sslExpiry}\nTime : {time}', 1, 1)
ON DUPLICATE KEY UPDATE `tpl_key` = `tpl_key`;

-- The near-expiry warning is now a 1-day alert, so say so and tag the team.
UPDATE `monitor_templates`
   SET `title` = 'SSL กำลังจะหมดอายุ (เหลือ {sslDaysLeft} วัน)',
       `mention_all` = 1
 WHERE `tpl_key` = 'ssl';
