-- Warn before a full disk takes a site down.
INSERT INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`, `is_active`) VALUES
('disk_warn',
 'พื้นที่ใกล้เต็ม - เสี่ยงเว็บล่ม',
 'Domain : {domain}\ncPanel : {cpanelUser}\nใช้ไป : {diskUsed} / {diskLimit} ({diskPercent}%)\nTime : {time}', 0, 1)
ON DUPLICATE KEY UPDATE `tpl_key` = `tpl_key`;
