<?php
/**
 * Feedback module configuration.
 *
 * Copy this file to config.php and fill in the values. config.php is
 * gitignored: the feedback credit token must never be committed.
 */

return [
    // Base URL of the gcal booking API, no trailing slash.
    // e.g. 'https://araya-reception.localforyou.com'
    'gcal_base_url' => '',

    // Shared secret for POST /api/feedback-credit. Must match
    // FEEDBACK_CREDIT_TOKEN in the gcal environment.
    //
    // Empty disables the credit call entirely: feedback is still recorded and
    // the customer is still thanked. That is the safe default, because a
    // half-configured deployment must not look like a working one.
    'feedback_credit_token' => '',
];
