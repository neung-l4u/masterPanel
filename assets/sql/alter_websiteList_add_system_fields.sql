ALTER TABLE `websiteList`
    ADD COLUMN `wSystemCallShop` tinyint(3) unsigned NOT NULL DEFAULT 0,
    ADD COLUMN `wSystemCallShopText` text,
    ADD COLUMN `wSystemCloudflare` tinyint(3) unsigned NOT NULL DEFAULT 0,
    ADD COLUMN `wSystemCloudflareText` text,
    ADD COLUMN `wSystemOther` tinyint(3) unsigned NOT NULL DEFAULT 0,
    ADD COLUMN `wSystemOtherText` text;
