<?php
/**
 * Copy to config.php and fill in. config.php is gitignored — never commit it.
 */
return [
    // Base URL of gcal-booking-api, no trailing slash.
    'gcal_base_url'    => 'https://your-gcal-host',
    // Must equal ARAYA_ADMIN_KEY in the gcal app's environment.
    'gcal_admin_key'   => '',
    // Monday API token with read access to board 2032016220.
    'monday_token'     => '',
    // Google Chat incoming webhook for the "IT in house" space.
    'chat_webhook_url' => '',
];
