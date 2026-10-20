-- Rollback for 2026-10-12-websitelist-domain-providers.sql: put back the provider each site had before.

START TRANSACTION;
UPDATE `websiteList` SET `wDomainProvidersID` = 9 WHERE `wID` IN (35, 113, 114, 124, 126, 128, 129, 130, 131, 135, 136, 139, 144, 149, 155, 166, 167, 169, 171, 174, 176, 177, 184, 187, 188, 192, 196, 197, 200, 201, 202, 207, 208, 211, 217, 222, 229, 231, 235, 238, 243, 248, 258, 262, 264, 265, 267, 268, 270, 275, 277, 278, 287, 288, 289, 290, 291, 292, 297, 303, 310, 311, 319, 320, 352, 357, 364, 379, 384, 387, 392, 393, 408, 431, 434, 454, 516, 520, 521, 528);   -- Crazy Domains
UPDATE `websiteList` SET `wDomainProvidersID` = 1 WHERE `wID` IN (122, 134, 165, 173, 240, 245, 249, 251, 276, 322, 332, 340, 405, 417, 448, 453, 482, 502, 506, 508, 515, 519, 536);   -- Dreamscape
UPDATE `websiteList` SET `wDomainProvidersID` = 2 WHERE `wID` IN (48, 294, 346, 396, 472);   -- Godaddy
UPDATE `websiteList` SET `wDomainProvidersID` = 0 WHERE `wID` IN (8, 12, 22, 23, 31, 40, 43, 58, 60, 65, 73, 90, 97, 117, 168, 191, 194, 195, 204, 205, 206, 215, 299, 348, 349, 353, 410, 420, 429, 433, 455, 469, 495, 501, 512, 522, 543, 546);   -- None
UPDATE `websiteList` SET `wDomainProvidersID` = 11 WHERE `wID` IN (44, 45, 46, 47, 219, 236, 241, 283, 293, 304, 305, 312, 316, 326, 337, 338, 342, 347, 385, 386, 390, 406, 424, 436, 438, 456, 473, 488, 489, 491, 497, 510, 511, 518, 537, 547);   -- Other
UPDATE `websiteList` SET `wDomainProvidersID` = 13 WHERE `wID` IN (156);   -- SquareSpace
UPDATE `websiteList` SET `wDomainProvidersID` = 15 WHERE `wID` IN (428, 496, 507);   -- Wix
-- The new providers are left in DomainProviders (removing them is safe only when no row uses them):
-- DELETE FROM `DomainProviders` WHERE `name` = 'Synergy Wholesale' AND `id` NOT IN (SELECT `wDomainProvidersID` FROM `websiteList` WHERE `wDomainProvidersID` IS NOT NULL);
-- DELETE FROM `DomainProviders` WHERE `name` = 'Tucows / OpenSRS' AND `id` NOT IN (SELECT `wDomainProvidersID` FROM `websiteList` WHERE `wDomainProvidersID` IS NOT NULL);
-- DELETE FROM `DomainProviders` WHERE `name` = 'Instra' AND `id` NOT IN (SELECT `wDomainProvidersID` FROM `websiteList` WHERE `wDomainProvidersID` IS NOT NULL);
COMMIT;
