CREATE TABLE `monday_advanced_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `staffID` INT NOT NULL,
  `board` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `detail` TEXT,
  `attachment` VARCHAR(500) DEFAULT NULL COMMENT 'path to uploaded picture',
  `screenshot_internet` VARCHAR(500) DEFAULT NULL COMMENT 'path to internet speed screenshot',
  `screenshot_computer` VARCHAR(500) DEFAULT NULL COMMENT 'path to computer info screenshot',
  `status` TINYINT DEFAULT 1 COMMENT '1=active, 0=resolved',
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_staffID` (`staffID`),
  INDEX `idx_createdAt` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
