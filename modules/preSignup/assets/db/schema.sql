-- Pre sign-up records. One row = one opportunity a sales rep opened on a
-- customer's behalf, before that customer has filled in the signup form.
--
-- `id` is what the public link is built from (prelead-0000001), so rows are
-- never deleted: a link handed to a customer must keep resolving. Use
-- `status` to retire one instead.
CREATE TABLE IF NOT EXISTS `presignup` (
    `id`              INT(11) NOT NULL AUTO_INCREMENT,

    -- Shop
    `shopName`        VARCHAR(255) NOT NULL DEFAULT '',
    `country`         VARCHAR(16)  NOT NULL DEFAULT '',
    `shopType`        VARCHAR(100) NOT NULL DEFAULT '',
    `customerType`    VARCHAR(50)  NOT NULL DEFAULT '',
    `mobNumber`       VARCHAR(40)  NOT NULL DEFAULT '',
    `shopNumber`      VARCHAR(40)  NOT NULL DEFAULT '',
    `websiteLink`     VARCHAR(500) NOT NULL DEFAULT '',

    -- Contact person
    `nickname`        VARCHAR(100) NOT NULL DEFAULT '',
    `firstName`       VARCHAR(100) NOT NULL DEFAULT '',
    `lastName`        VARCHAR(100) NOT NULL DEFAULT '',
    `email`           VARCHAR(255) NOT NULL DEFAULT '',

    -- Address
    `streetAddress`   VARCHAR(255) NOT NULL DEFAULT '',
    `citySuburb`      VARCHAR(100) NOT NULL DEFAULT '',
    `state`           VARCHAR(100) NOT NULL DEFAULT '',
    `postalCode`      VARCHAR(30)  NOT NULL DEFAULT '',

    -- Tracking
    `leadSource`      VARCHAR(100) NOT NULL DEFAULT '',
    `contactChannel`  VARCHAR(20)  NOT NULL DEFAULT '',
    -- Link to the conversation this lead came from, whichever platform
    -- that is: Respond.io, Facebook, TikTok, Line. Not every platform
    -- produces one, so it is optional.
    `conversationLink` VARCHAR(500) NOT NULL DEFAULT '',
    `salesRep`        VARCHAR(100) NOT NULL DEFAULT '',

    -- Lifecycle. 'open' until the customer completes the signup form.
    `status`          VARCHAR(20)  NOT NULL DEFAULT 'open',
    `openedAt`        DATETIME     NULL DEFAULT NULL,
    `openCount`       INT(11)      NOT NULL DEFAULT 0,

    -- The Lead item this pre sign-up was matched to, and Monday's own
    -- Opportunity ID for it. The auto_number column cannot be read over the
    -- API at all, so `opportunityId` is filled from the text column a Monday
    -- automation mirrors it into, and stays empty until that has run.
    `mondayItemId`    VARCHAR(40)  NOT NULL DEFAULT '',
    `opportunityId`   VARCHAR(40)  NOT NULL DEFAULT '',

    -- Full submitted payload, for fields added to the form later.
    `dataLogs`        LONGTEXT     NULL DEFAULT NULL,

    `createAt`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_status` (`status`),
    KEY `idx_shopName` (`shopName`),
    KEY `idx_mondayItemId` (`mondayItemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
