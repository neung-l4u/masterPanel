-- Amelia and Stripe keys for the Amelia setup step.
--
-- The Stripe keys here are the shared test keys, so a freshly built site can
-- take a test booking end to end. Live keys belong to the shop and are put in
-- by the team that hands the site over, per site, inside Amelia itself.
--
-- As with the other rows, the values are left blank on purpose: fill them in
-- once per environment and the matching step starts running.
INSERT INTO `l4uConfig` (`cfKey`, `cfValue`, `cfRemark`) VALUES
    ('amelia_licence_key', '', 'Amelia licence key, used to activate the plugin.'),
    ('stripe_pk_test',     '', 'Stripe publishable test key. Live keys are set per shop by the team.'),
    ('stripe_sk_test',     '', 'Stripe secret test key. Live keys are set per shop by the team.')
ON DUPLICATE KEY UPDATE `cfRemark` = VALUES(`cfRemark`);
