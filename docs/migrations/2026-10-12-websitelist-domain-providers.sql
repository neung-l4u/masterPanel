-- websiteList.wDomainProvidersID: set each site's domain provider to its actual registrar.
-- Source: Dreamscape reseller API + RDAP/auDA WHOIS lookups, 2026-10-12. Review list: domain_provider_update_2026-10-12.csv
-- Rows whose domain is our own, not registered anywhere, or could not be looked up are not touched.
-- Safe to run more than once. Rollback: 2026-10-12-websitelist-domain-providers.rollback.sql

START TRANSACTION;

-- 1. New providers (only if missing)
INSERT INTO `DomainProviders` (`name`, `status`) SELECT 'Synergy Wholesale', 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `DomainProviders` WHERE `name` = 'Synergy Wholesale');
INSERT INTO `DomainProviders` (`name`, `status`) SELECT 'Tucows / OpenSRS', 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `DomainProviders` WHERE `name` = 'Tucows / OpenSRS');
INSERT INTO `DomainProviders` (`name`, `status`) SELECT 'Instra', 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `DomainProviders` WHERE `name` = 'Instra');

-- 2. Point each site at its actual registrar
-- Crazy Domains: 7 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Crazy Domains' LIMIT 1)
  WHERE `wID` IN (156, 165, 276, 322, 332, 448, 488);
-- Dreamscape: 113 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Dreamscape' LIMIT 1)
  WHERE `wID` IN (8, 12, 35, 58, 60, 65, 73, 97, 113, 114, 124, 126, 128, 129, 130, 131, 135, 136, 139, 144, 149, 155, 166, 167, 168, 169, 171, 174, 177, 184, 187, 188, 192, 194, 195, 196, 197, 201, 202, 204, 205, 206, 207, 208, 211, 215, 217, 219, 222, 229, 231, 235, 238, 248, 258, 262, 264, 265, 267, 268, 270, 275, 278, 287, 288, 289, 290, 291, 292, 297, 299, 303, 310, 311, 319, 320, 337, 338, 342, 346, 352, 357, 364, 379, 384, 387, 392, 393, 396, 408, 410, 428, 429, 431, 434, 454, 456, 469, 472, 489, 491, 495, 496, 507, 511, 512, 516, 518, 520, 521, 522, 528, 543);
-- Godaddy: 24 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Godaddy' LIMIT 1)
  WHERE `wID` IN (90, 122, 283, 293, 304, 312, 316, 340, 349, 353, 385, 386, 417, 424, 436, 453, 502, 508, 510, 515, 519, 536, 537, 546);
-- HostInger: 2 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'HostInger' LIMIT 1)
  WHERE `wID` IN (173, 347);
-- IONOS: 1 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'IONOS' LIMIT 1)
  WHERE `wID` IN (420);
-- Instra: 5 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Instra' LIMIT 1)
  WHERE `wID` IN (134, 243, 326, 473, 482);
-- Other: 15 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Other' LIMIT 1)
  WHERE `wID` IN (22, 23, 40, 48, 117, 176, 191, 240, 245, 249, 251, 294, 348, 455, 501);
-- Synergy Wholesale: 8 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Synergy Wholesale' LIMIT 1)
  WHERE `wID` IN (31, 43, 200, 241, 277, 406, 438, 547);
-- Tucows / OpenSRS: 8 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Tucows / OpenSRS' LIMIT 1)
  WHERE `wID` IN (44, 45, 46, 47, 236, 305, 390, 497);
-- Wix: 1 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'Wix' LIMIT 1)
  WHERE `wID` IN (433);
-- namecheap: 1 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'namecheap' LIMIT 1)
  WHERE `wID` IN (506);
-- register.com: 1 sites
UPDATE `websiteList` SET `wDomainProvidersID` = (SELECT `id` FROM `DomainProviders` WHERE `name` = 'register.com' LIMIT 1)
  WHERE `wID` IN (405);

COMMIT;

-- Check:
-- SELECT dp.name, COUNT(*) FROM websiteList w LEFT JOIN DomainProviders dp ON dp.id = w.wDomainProvidersID WHERE w.delete_at IS NULL GROUP BY dp.name;
