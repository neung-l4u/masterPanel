-- websiteList.wAdminSupportPass: รหัสผ่านของบัญชี admin support (admin@localforyou.com, user เดิม)
-- แยกจาก wWordpressUser/wWordpressPass เพราะคอลัมน์นั้นเก็บรหัสแอดมินเฉพาะของแต่ละเว็บอยู่แล้ว
-- ต่อจากนี้ฟอร์ม New Item จะ auto-gen รหัสนี้ให้อัตโนมัติ (ดู genAdminSupportPass ใน pages/websiteList.php)
-- ต้องรันไฟล์นี้บน production ก่อนอัปโหลดโค้ดที่อ่าน/เขียน wAdminSupportPass
-- Rollback: 2026-10-26-websitelist-admin-support.rollback.sql

ALTER TABLE `websiteList` ADD COLUMN `wAdminSupportPass` VARCHAR(100) NULL DEFAULT NULL AFTER `wWordpressPass`;

-- Backfill wAdminSupportPass (510 เว็บ) ไม่เก็บใน repo เพราะ repo เป็น public
-- ไฟล์ backfill ตัวเต็มเก็บแยกไว้นอก repo ขอได้จากทีม IT
