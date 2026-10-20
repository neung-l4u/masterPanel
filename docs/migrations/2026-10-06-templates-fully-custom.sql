-- Template submission: Fully custom website orders + Shop type "Other".
--
-- Needed by:
--   modules/templates/models/project.php         (save Other description)
--   modules/templates/models/project_detail.php  (save design brief)
--   modules/templates/views/project_detail.php   (brief form)
--   modules/templates/models/tableProject.php    (Send button for custom projects)
--   pages/tpSubmittedDetails.php                 (brief shown to IT)
--
-- Without this file: template projects keep working (code checks the table exists first), but
-- "Fully custom" projects cannot be saved and the "Other" shop type does not appear.
-- Custom projects are tb_project rows with selectedTemplate = 0; tb_project itself is unchanged.

-- 1. "Other" already exists (id 6) but was disabled on 2025-05-09.
UPDATE `tb_shopType` SET `status` = 1 WHERE `id` = 6 AND `name` = 'Other';

-- 2. One brief per custom project.
CREATE TABLE IF NOT EXISTS `tb_projectBrief` (
  `projectID` int(10) UNSIGNED NOT NULL COMMENT 'tb_project.projectID',
  `businessNote` text DEFAULT NULL COMMENT 'Shop type Other: what the business does',
  `colorMode` varchar(10) NOT NULL DEFAULT 'pick' COMMENT 'pick | logo | reference',
  `systemName` varchar(255) DEFAULT NULL COMMENT 'Booking/ordering system, or None',
  `systemURL` varchar(255) DEFAULT NULL,
  `goal` varchar(10) DEFAULT NULL COMMENT 'book | call | order | visit',
  `pages` text DEFAULT NULL COMMENT 'One page name per line',
  `oldSiteURL` varchar(255) DEFAULT NULL,
  `keepURLs` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Old URLs used by ads/SEO must not change',
  `refLinks` text DEFAULT NULL COMMENT 'JSON [{"url":"","note":"","forColor":0|1}]',
  `mood` varchar(255) DEFAULT NULL,
  `contentSource` varchar(10) DEFAULT NULL COMMENT 'oldsite | customer | we',
  `hasVectorLogo` tinyint(1) NOT NULL DEFAULT 0,
  `hasHiResPhotos` tinyint(1) NOT NULL DEFAULT 0,
  `features` text DEFAULT NULL,
  `tracking` text DEFAULT NULL COMMENT 'Existing GTM / GA4 / Search Console',
  `assetLinks` text DEFAULT NULL COMMENT 'Google Drive / Dropbox links to photos and files',
  `deadline` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updateAt` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `updateBy` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`projectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Safe to re-run on a database that already ran an earlier copy of step 2.
ALTER TABLE `tb_projectBrief` ADD COLUMN IF NOT EXISTS `assetLinks` text DEFAULT NULL
  COMMENT 'Google Drive / Dropbox links to photos and files' AFTER `tracking`;

-- Brief photos are not in the database: they are files named Brief_* in
-- modules/templates/upload/<projectID>-<projectName>/ (same folder as the logo).

-- Check:
--   SELECT * FROM tb_shopType WHERE id = 6;
--   SHOW TABLES LIKE 'tb_projectBrief';
--   SHOW COLUMNS FROM tb_projectBrief LIKE 'assetLinks';
