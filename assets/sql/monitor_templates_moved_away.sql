-- Alert for a client whose domain no longer points at any of our servers.
-- Run AFTER assets/sql/monitor_templates_ssl_expired.sql on production.
INSERT INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`, `is_active`) VALUES
('moved_away',
 'Domain ไม่ได้ชี้มาที่ Server เราแล้ว (อาจยกเลิกบริการ)',
 'Domain : {domain}\nIP ปลายทาง : {resolvedIP}\nTime : {time}', 0, 1)
ON DUPLICATE KEY UPDATE `tpl_key` = `tpl_key`;

-- Drop the "(อิงตามเวลาไทย)" suffix from every alert body.
UPDATE `monitor_templates` SET `body` = REPLACE(`body`, ' (อิงตามเวลาไทย)', '');
