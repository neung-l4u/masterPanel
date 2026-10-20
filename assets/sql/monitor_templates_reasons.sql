-- Adds the per-reason Google Chat templates (Thai wording).
-- Run AFTER assets/sql/monitor_templates.sql on production.
INSERT INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`, `is_active`) VALUES
('wp_fatal',
 'Wordpress There has been a critical error on this website',
 'Domain : {domain}\nTime : {time}', 1, 1),
('unregistered',
 'Domain Live ใน Website list แต่ Down (ไม่ได้ถูกซื้อ)',
 'Domain : {domain}\nTime : {time}', 1, 1),
('expired',
 'Domain Live ใน Website list แต่ Down (แต่โดเมนหมดอายุ)',
 'Domain : {domain}\nTime : {time}', 1, 1)
ON DUPLICATE KEY UPDATE `tpl_key` = `tpl_key`;

-- Reword the generic down/recovered alerts to match the same plain-language style.
UPDATE `monitor_templates`
   SET `title` = 'Domain Live ใน Website list แต่ Down (แต่โดเมนไม่ได้หมดอายุ)',
       `body`  = 'Domain : {domain}\nTime : {time}'
 WHERE `tpl_key` = 'down';

UPDATE `monitor_templates`
   SET `title` = 'Website กลับมาใช้งานได้ปกติแล้ว',
       `body`  = 'Domain : {domain}\nTime : {time}'
 WHERE `tpl_key` = 'recovered';

UPDATE `monitor_templates`
   SET `title` = 'SSL ใกล้หมดอายุ (เหลือ {sslDaysLeft} วัน)',
       `body`  = 'Domain : {domain}\nSSL หมดอายุ : {sslExpiry}\nTime : {time}'
 WHERE `tpl_key` = 'ssl';
