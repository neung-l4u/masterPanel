-- thCustomer.shopName: ชื่อร้านแบบที่ PO เห็นบน monday (ชื่อ item บนบอร์ด Projects | TH ซึ่งตรงกับชื่อบน Past billing (Receipt TH))
-- เพราะ thCustomer.name คือ Trading Name (ชื่อบริษัท/ผู้ออกใบ) ทำให้ PO หาร้านในหน้า Receipt Thailand ไม่เจอ
-- ต่อจากนี้ createSubTH.php เขียนค่านี้ทุกครั้งที่ออกบิล (จาก pulse_name) และ signup ใส่ชื่อร้านเริ่มต้นไว้
-- ต้องรันไฟล์นี้บน production ก่อนอัปโหลดโค้ดที่อ่าน/เขียน shopName
-- Rollback: 2026-10-25-thcustomer-shopname.rollback.sql

ALTER TABLE `thCustomer` ADD COLUMN `shopName` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

-- Backfill ลูกค้าเดิม: ชื่อ item บนบอร์ด Projects | TH (1943203205) ดึงผ่าน monday API 2026-10-25
-- จับคู่ thCustomer.id กับ project ด้วยอีเมล + เลขผู้เสียภาษี (สถานีแจ่วฮ้อน จับด้วยชื่อ เพราะไม่มีอีเมลใน thCustomer)
-- ไม่พบบน monday: id 22 (Beam test), id 62 X5FBDGKU (Mootacup & Thaifood) → คงว่างไว้ เติมเองเมื่อออกบิลหรือแก้มือ
START TRANSACTION;
UPDATE `thCustomer` SET `shopName` = 'สถานีแจ่วฮ้อน - BR.Lom Sak' WHERE `id` = 3;
UPDATE `thCustomer` SET `shopName` = 'ภูกาษาวาเล่ย์ - Restaurant - TH' WHERE `id` = 4;
UPDATE `thCustomer` SET `shopName` = 'B''Leo Beauty & Wellness Phuket  - TH' WHERE `id` = 5;
UPDATE `thCustomer` SET `shopName` = 'Tanya’s Cafe and Resort - BR.Buriram' WHERE `id` = 6;
UPDATE `thCustomer` SET `shopName` = 'Guaytiew Rue Khun Pa - BR.Jaran29' WHERE `id` = 8;
UPDATE `thCustomer` SET `shopName` = 'Thai London Health Massage Pattaya - TH' WHERE `id` = 12;
UPDATE `thCustomer` SET `shopName` = 'sukkaya massage สุขกายา นวดเพื่อสุขภาพ - TH' WHERE `id` = 13;
UPDATE `thCustomer` SET `shopName` = 'ครัวในสวน  สวนรถไฟ - TH' WHERE `id` = 14;
UPDATE `thCustomer` SET `shopName` = 'ร้านครัวนายเหมือง ราชบุรี - TH' WHERE `id` = 15;
UPDATE `thCustomer` SET `shopName` = 'Catteleya Health Massage  - BR.Pattaya' WHERE `id` = 16;
UPDATE `thCustomer` SET `shopName` = 'Family Hair & Spa - BR.Ban Chang' WHERE `id` = 17;
UPDATE `thCustomer` SET `shopName` = 'Face Holistic House  - BR.Banglane' WHERE `id` = 18;
UPDATE `thCustomer` SET `shopName` = 'Zira Spa - TH' WHERE `id` = 19;
UPDATE `thCustomer` SET `shopName` = 'เนื้อแคมป์ไฟ - TH' WHERE `id` = 20;
UPDATE `thCustomer` SET `shopName` = 'Great Cafe Brunch Aonang Krabi - BR.Ao Nang' WHERE `id` = 21;
UPDATE `thCustomer` SET `shopName` = 'Duangnet Thai massage ดวงเนตรนวดแผนไทยเพื่อสุขภาพ&สปา - BR.Huai Khwang' WHERE `id` = 25;
UPDATE `thCustomer` SET `shopName` = 'The Story ร้านอาหารริมน้ำกาญจนบุรี - BR.River Kwai Bridge' WHERE `id` = 41;
UPDATE `thCustomer` SET `shopName` = 'Test by dev Res1 - TH' WHERE `id` = 48;
UPDATE `thCustomer` SET `shopName` = 'Test first signup billing Thailand - TH' WHERE `id` = 53;
UPDATE `thCustomer` SET `shopName` = 'แซ่บนัวเฮ้าส์ - TH' WHERE `id` = 54;
UPDATE `thCustomer` SET `shopName` = 'GROOVE BAR - BR.Ratchaphruek' WHERE `id` = 55;
UPDATE `thCustomer` SET `shopName` = 'ร้านตะไคร้หอม - BR.Number 6' WHERE `id` = 56;
UPDATE `thCustomer` SET `shopName` = 'Mark Test' WHERE `id` = 57;
UPDATE `thCustomer` SET `shopName` = 'Test first signup billing Thailand - TH' WHERE `id` = 58;
UPDATE `thCustomer` SET `shopName` = 'ร้านแอทโฮม at home- food cafe & playground, Nonthaburi - BR.Bang Bua Thong' WHERE `id` = 59;
UPDATE `thCustomer` SET `shopName` = 'ครัวแพรหมอ - BR.Mueang Samut Prakan' WHERE `id` = 60;
UPDATE `thCustomer` SET `shopName` = '27%CAFE - BR.Mae Sai' WHERE `id` = 61;
UPDATE `thCustomer` SET `shopName` = 'Arno''s Silom Serene Hotel - BR.Bang Rak' WHERE `id` = 63;
COMMIT;
