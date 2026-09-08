-- Columns needed by the Auto Setup WordPress button on the website form.
--
-- wShopEmail  the shop's own email address, used to create the WordPress
--             "Shop Owner" editor account. Kept apart from wOwnerEmail, which
--             is the person we deal with rather than the shop itself.
-- wWPLocale   which language and timezone the site was set up with, so the
--             form comes back with the same choice and the button can be
--             pressed again without picking it a second time.

ALTER TABLE `websiteList`
    ADD COLUMN `wShopEmail` VARCHAR(255) NULL DEFAULT NULL AFTER `wOwnerEmail`,
    ADD COLUMN `wWPLocale`  VARCHAR(10)  NULL DEFAULT NULL AFTER `wWordpressURL`;
