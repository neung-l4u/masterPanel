-- L4U Task Management System (Trello-like)
-- Created: 2026-03-25

CREATE TABLE IF NOT EXISTS `l4utask_boards` (
    `bID`           INT AUTO_INCREMENT PRIMARY KEY,
    `bName`         VARCHAR(255) NOT NULL,
    `bDescription`  TEXT DEFAULT NULL,
    `bColor`        VARCHAR(20) DEFAULT '#0079BF',
    `bVisibility`   TINYINT DEFAULT 1,
    `bStatus`       TINYINT DEFAULT 1,
    `bCreatedBy`    INT NOT NULL,
    `bCreatedAt`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    `bUpdatedAt`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `bDeletedAt`    DATETIME DEFAULT NULL,
    `bDeletedBy`    INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_board_members` (
    `bmID`          INT AUTO_INCREMENT PRIMARY KEY,
    `bID`           INT NOT NULL,
    `sID`           INT NOT NULL,
    `bmRole`        TINYINT DEFAULT 2,
    `bmJoinedAt`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_board_member` (`bID`, `sID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_lists` (
    `lID`           INT AUTO_INCREMENT PRIMARY KEY,
    `bID`           INT NOT NULL,
    `lName`         VARCHAR(255) NOT NULL,
    `lPosition`     INT NOT NULL DEFAULT 0,
    `lStatus`       TINYINT DEFAULT 1,
    `lCreatedAt`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    `lUpdatedAt`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_cards` (
    `cID`           INT AUTO_INCREMENT PRIMARY KEY,
    `lID`           INT NOT NULL,
    `bID`           INT NOT NULL,
    `cTitle`        VARCHAR(500) NOT NULL,
    `cDescription`  TEXT DEFAULT NULL,
    `cPosition`     INT NOT NULL DEFAULT 0,
    `cColor`        VARCHAR(20) DEFAULT NULL,
    `cPriority`     TINYINT DEFAULT 0,
    `cDueDate`      DATE DEFAULT NULL,
    `cCompletedAt`  DATE DEFAULT NULL,
    `cStage`        VARCHAR(50) DEFAULT 'Draft',
    `cStatus`       TINYINT DEFAULT 1,
    `cCreatedBy`    INT NOT NULL,
    `cCreatedAt`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    `cUpdatedAt`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `cDeletedAt`    DATETIME DEFAULT NULL,
    `cDeletedBy`    INT DEFAULT NULL,
    INDEX `idx_list` (`lID`),
    INDEX `idx_board` (`bID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_card_members` (
    `cmID`          INT AUTO_INCREMENT PRIMARY KEY,
    `cID`           INT NOT NULL,
    `sID`           INT NOT NULL,
    `cmAssignedAt`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_card_member` (`cID`, `sID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_card_subitems` (
    `siID`          INT AUTO_INCREMENT PRIMARY KEY,
    `cID`           INT NOT NULL,
    `siTitle`       VARCHAR(500) NOT NULL,
    `siStatus`      VARCHAR(50) DEFAULT 'Pending',
    `siPriority`    VARCHAR(50) DEFAULT NULL,
    `siDueDate`     DATE DEFAULT NULL,
    `siCompletedAt` DATE DEFAULT NULL,
    `siAssignee`    INT DEFAULT NULL,
    `siPosition`    INT NOT NULL DEFAULT 0,
    `siCreatedBy`   INT NOT NULL,
    `siCreatedAt`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    `siUpdatedAt`   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `siDeletedAt`   DATETIME DEFAULT NULL,
    INDEX `idx_card_subitems` (`cID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_card_activities` (
    `caID`          INT AUTO_INCREMENT PRIMARY KEY,
    `cID`           INT DEFAULT NULL,
    `siID`          INT DEFAULT NULL,
    `sID`           INT NOT NULL,
    `caType`        VARCHAR(50) NOT NULL,
    `caField`       VARCHAR(50) DEFAULT NULL,
    `caOldValue`    TEXT DEFAULT NULL,
    `caNewValue`    TEXT DEFAULT NULL,
    `caCreatedAt`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_card_act` (`cID`),
    INDEX `idx_subitem_act` (`siID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_card_comments` (
    `ccID`          INT AUTO_INCREMENT PRIMARY KEY,
    `cID`           INT NOT NULL,
    `sID`           INT NOT NULL,
    `ccText`        TEXT NOT NULL,
    `ccCreatedAt`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ccUpdatedAt`   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `ccDeletedAt`   DATETIME DEFAULT NULL,
    INDEX `idx_card` (`cID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `l4utask_csv_uploads` (
    `csvID`         INT AUTO_INCREMENT PRIMARY KEY,
    `bID`           INT NOT NULL,
    `sID`           INT NOT NULL,
    `csvFileName`   VARCHAR(255) NOT NULL,
    `csvOriginalName` VARCHAR(255) NOT NULL,
    `csvFilePath`   VARCHAR(500) NOT NULL,
    `csvSize`       INT DEFAULT 0,
    `csvStatus`     ENUM('uploading','processing','completed','failed') DEFAULT 'uploading',
    `csvRecordsProcessed` INT DEFAULT 0,
    `csvRecordsTotal` INT DEFAULT 0,
    `csvErrors`     TEXT DEFAULT NULL,
    `csvCreatedAt`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    `csvProcessedAt` DATETIME DEFAULT NULL,
    INDEX `idx_board` (`bID`),
    INDEX `idx_user` (`sID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
