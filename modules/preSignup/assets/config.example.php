<?php
/**
 * Pre sign-up module configuration.
 *
 * Copy this file to config.php and fill in the values. config.php is
 * gitignored: the Monday API token must never be committed.
 */

return [
    // Monday API. The token needs read access to the Lead Management board;
    // the module only ever reads from Monday, never writes.
    'monday_api_url' => 'https://api.monday.com/v2',
    'monday_token'   => '',

    // New - Lead Management V1. Dropdown choices on the pre sign-up form are
    // read from this board's columns so submitted values always match the
    // labels that actually exist in Monday.
    'monday_board_id' => '5031995646',

    // Status/dropdown columns the form mirrors. Keys are the form field names.
    'monday_columns' => [
        'country'        => 'numbers_mkn4adf8',
        'shopType'       => 'status_mkn4hemw',
        'customerType'   => 'color_mm7r5k6v',
        'leadSource'     => 'lead_source',
        'contactChannel' => 'color_mm897hc7',
    ],

    // Columns read when matching a pre sign-up to its Lead item.
    'monday_item_columns' => [
        // Monday's Opportunity ID, copied into a plain text column by a Monday
        // automation. The auto_number column itself cannot be read over the
        // API, so this mirror is the only way to see the number.
        'opportunityId'  => 'text_mm8q2zp1',
        'conversationLink' => 'link_mm4xqfx5',
        'mobNumber'      => 'phone_mkn4xxyp',
        'shopNumber'     => 'phone_mknfd49c',
        'email'          => 'email_mkn4kr59',
        'streetAddress'  => 'text_mm8qtmmw',
        'citySuburb'     => 'text_mm8qc42h',
        'state'          => 'text_mm8q8n4a',
        'postalCode'     => 'text_mm8qnwcd',
    ],

    // Group new Lead items live in, for any future write-back.
    'monday_group_id' => 'group_mkq2knzr',

    // Public base for the link handed to the customer, no trailing slash.
    'public_base_url' => 'https://report.localforyou.com',

    // Where a resolved pre-signup link sends the customer. The pre-filled
    // values are appended as query parameters.
    //
    // The signup module does not read these parameters yet: it only looks at
    // testMode and invoice. Until it does, a customer following the link
    // lands on a blank form and the pre-filled values are ignored.
    'signup_form_url' => 'https://report.localforyou.com/modules/signup',

    // How long Monday dropdown labels are cached, in seconds. Labels change
    // rarely; a stale cache is served if Monday is unreachable.
    'options_cache_ttl' => 3600,
];
