-- Table: POSandNewOnlineOrder
-- Source: derived from Google Form CSV (POS & New online order Onboarding new Client)
-- DB: localfor_reports

CREATE TABLE IF NOT EXISTS `POSandNewOnlineOrder` (
  `id`                          INT(11)      NOT NULL AUTO_INCREMENT,
  `submissionId`                CHAR(32)     NOT NULL,                    -- token (bin2hex random_bytes(16))

  -- Page 1 : Onboarding new Client
  `shopEmail`                   VARCHAR(120) DEFAULT NULL,
  `shopPhone`                   VARCHAR(40)  DEFAULT NULL,
  `managerName`                 VARCHAR(120) DEFAULT NULL,
  `country`                     VARCHAR(60)  DEFAULT NULL,                -- Australia / USA / UK / New Zealand
  `currency`                    VARCHAR(10)  DEFAULT NULL,                -- AUD / USD / ...
  `tradingName`                 VARCHAR(160) DEFAULT NULL,
  `tradingAddress`              VARCHAR(255) DEFAULT NULL,
  `terminalDeliveryAddress`     VARCHAR(255) DEFAULT NULL,
  `serviceProvided`             VARCHAR(255) DEFAULT NULL,                -- comma list: Pickup,Delivery,Table Reservation,Dine-in

  -- Page 2 : Opening Hour (JSON)
  -- e.g. {"mon":"Open","tue":"09:00-21:00","wed":"Closed",...}
  `openingHours`                JSON         DEFAULT NULL,

  -- Page 3 : POS set up
  `eftposModel`                 VARCHAR(60)  DEFAULT NULL,                -- Portable / Standard / None
  `eftposQty`                   VARCHAR(10)  DEFAULT NULL,                -- 1 / 2 / 3 / 4 / 5 / None
  `hasOwnWebsite`               VARCHAR(10)  DEFAULT NULL,                -- Yes / No
  `thirdPartyPlatforms`         VARCHAR(255) DEFAULT NULL,                -- comma list

  -- Page 4 : Restaurant Address / Cuisine (JSON)
  -- e.g. {"countryCode":"+61","streetAddress":"...","city":"...","stateRegion":"...","cuisineSelector":"Thai"}
  `restaurantAddress`           JSON         DEFAULT NULL,

  -- Page 5 : Own Delivery
  `deliveryServiceNeed`         VARCHAR(60)  DEFAULT NULL,                -- Inhouse Delivery / Third Party / No
  `deliverBy`                   VARCHAR(60)  DEFAULT NULL,
  `servicedArea`                VARCHAR(255) DEFAULT NULL,
  `minimumOrder`                DECIMAL(10,2) DEFAULT NULL,
  `deliveryFee`                 DECIMAL(10,2) DEFAULT NULL,

  -- Page 6 : Inhouse Delivery (JSON)
  -- e.g. {"price0to3km":3,"price4km":4,"price5km":5,"price6km":6,"minimumOrder":7}
  `inhouseDelivery`             JSON         DEFAULT NULL,

  -- Page 7 : For Local for you team
  `logoStatus`                  VARCHAR(60)  DEFAULT NULL,                -- Request / Received / Other (or custom text)
  `logoMenuPictures`            VARCHAR(500) DEFAULT NULL,                -- uploaded file URL(s) comma-separated
  `gmbAccess`                   VARCHAR(60)  DEFAULT NULL,                -- Request / Received / Granted / Waiting
  `facebookPageAccess`          VARCHAR(60)  DEFAULT NULL,
  `domainHosting`               VARCHAR(120) DEFAULT NULL,
  `marketingTricksOptIn`        VARCHAR(10)  DEFAULT NULL,                -- YES / null

  -- Documents (JSON)
  -- e.g. {"businessRegistration":"https://...","bankStatement":"https://...","directorId":"https://..."}
  `documents`                   JSON         DEFAULT NULL,

  -- Meta
  `status`                      VARCHAR(40)  DEFAULT 'New Client',
  `dateThai`                    VARCHAR(40)  DEFAULT NULL,
  `createdAt`                   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updatedAt`                   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_submission` (`submissionId`),
  KEY `idx_email`  (`shopEmail`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
