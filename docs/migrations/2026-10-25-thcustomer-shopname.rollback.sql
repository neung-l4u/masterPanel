-- Rollback for 2026-10-25-thcustomer-shopname.sql
-- อัปโหลดโค้ดเวอร์ชันก่อนหน้า (ที่ไม่อ่าน/เขียน shopName) ขึ้นไปก่อน แล้วค่อยรันไฟล์นี้
-- ไม่อย่างนั้น signup, createSubTH และหน้า Receipt Thailand จะ error เพราะหาคอลัมน์ไม่เจอ

ALTER TABLE `thCustomer` DROP COLUMN `shopName`;
