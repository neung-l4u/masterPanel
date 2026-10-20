-- บันทึกว่าใครกดดูรหัสผ่านลูกค้าจากหน้า Payment Password
-- รันบน localfor_reports (ฐานของ masterPanel) ทั้ง local และ production
CREATE TABLE IF NOT EXISTS `password_view_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `staffID` INT(11) NOT NULL,
  `staffName` VARCHAR(255) DEFAULT NULL,
  `customerEmail` VARCHAR(255) NOT NULL,
  `viewedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ipAddress` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_staff` (`staffID`),
  KEY `idx_customer` (`customerEmail`),
  KEY `idx_viewed` (`viewedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
