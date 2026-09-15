-- Separate alerts for the different ways a site can fail, so the message says what
-- was actually observed instead of collapsing everything into a generic "Down".
INSERT INTO `monitor_templates` (`tpl_key`, `title`, `body`, `mention_all`, `is_active`) VALUES
('dns_fail',
 'DNS หาไม่เจอชั่วคราว (โดเมนยังไม่หมดอายุ)',
 'Domain : {domain}\nอาการ : {errorMsg}\nหมายเหตุ : โดเมนยังจดทะเบียนอยู่ปกติ มักเกิดจาก DNS/nameserver กระตุกชั่วคราว ถ้าเว็บกลับมาเองใน 1-2 รอบถือว่าไม่ต้องแก้\nTime : {time}', 0, 1),
('timeout',
 'เว็บตอบช้าเกินกำหนด (Timeout)',
 'Domain : {domain}\nอาการ : {errorMsg}\nรอนานสุด : 30 วินาที\nหมายเหตุ : เซิร์ฟเวอร์ยังตอบอยู่แต่ช้ามาก อาจโหลดหนักหรือพื้นที่ใกล้เต็ม\nTime : {time}', 1, 1),
('conn_refused',
 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้',
 'Domain : {domain}\nIP : {resolvedIP}\nอาการ : {errorMsg}\nหมายเหตุ : DNS ปกติแต่เซิร์ฟเวอร์ไม่รับการเชื่อมต่อ - ต้องตรวจว่า service ยังรันอยู่ไหม\nTime : {time}', 1, 1),
('ssl_error',
 'SSL/ใบรับรองมีปัญหา',
 'Domain : {domain}\nอาการ : {errorMsg}\nTime : {time}', 1, 1)
ON DUPLICATE KEY UPDATE `tpl_key` = `tpl_key`;

-- The generic "down" now only covers a server that answered with an error code.
UPDATE `monitor_templates`
   SET `title` = 'เว็บเปิดไม่ได้ (เซิร์ฟเวอร์ตอบ error)',
       `body`  = 'Domain : {domain}\nอาการ : {errorMsg}\nTime : {time}'
 WHERE `tpl_key` = 'down';
