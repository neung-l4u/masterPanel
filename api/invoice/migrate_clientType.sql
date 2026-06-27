-- Run this in phpMyAdmin (localfor_reports database)
-- เพิ่ม column clientType ใน thCustomer

ALTER TABLE `thCustomer`
  ADD COLUMN `clientType` ENUM('first_time','subscription') NOT NULL DEFAULT 'first_time'
  AFTER `sale`;
