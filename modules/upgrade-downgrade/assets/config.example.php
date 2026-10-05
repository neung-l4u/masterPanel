<?php
/**
 * Upgrade/downgrade module configuration.
 *
 * Copy this file to config.php and fill in the values. config.php is
 * gitignored: the Monday token must never be committed.
 */

return [
    // Monday.com API token. Needs read access to the per-country project
    // boards, the Contacts & Accounts board, and the Active Subscriptions board.
    'monday_token' => '',

    // Shared secret for triggering sync/syncMondayMirror.php over HTTP.
    // Leave empty to allow CLI invocation only, which is the safe default.
    'sync_token' => '',
];
