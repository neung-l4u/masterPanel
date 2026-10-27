-- Rollback for 2026-10-26-websitelist-admin-support.sql
-- อัปโหลดโค้ดเวอร์ชันก่อนหน้า (ที่ไม่อ่าน/เขียน wAdminSupportPass) ขึ้นไปก่อน แล้วค่อยรันไฟล์นี้
-- ไม่อย่างนั้นฟอร์ม Website List จะ error เพราะหาคอลัมน์ไม่เจอ

ALTER TABLE `websiteList` DROP COLUMN `wAdminSupportPass`;
