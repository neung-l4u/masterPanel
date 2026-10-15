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

    // Where the downgrade form sends a customer who cancelled an AI
    // Receptionist product, so they can give feedback on it.
    //
    // Empty disables the redirect: the admin notification email still sends,
    // and the customer lands on the normal thank-you page. CS has not supplied
    // the URL yet ("จะแจ้งรายละเอียดให้ทีหลัง"), so filling this in later
    // switches the behaviour on with no code change.
    'araya_feedback_url' => '',

    // Exact-match product classification, keyed by the Monday `product_group`
    // string verbatim. See assets/productClassify.php.
    //
    // Tags: araya, plan_bundle, plan_solo, addon. A product may carry several:
    // "AI + Local Starter Bundle" is an AI receptionist sold alongside a plan,
    // so it triggers both the Araya flows and the plan keep-list.
    //
    // Only list names the keyword fallback gets wrong or cannot see. The
    // fallback already handles "Local ... Bundle" and "Local ... Solo", so the
    // plain plan names are deliberately absent from this map.
    'product_classification' => [
        // Araya. The keyword rule finds only the first of these; the rest are
        // invisible to it, which is the whole reason this map exists.
        'AI Receptionist'           => ['araya'],
        'AI + Local Starter Bundle' => ['araya', 'plan_bundle'],

        // Add-ons whose names contain "Bundle" or "Solo" but which are not
        // plans. Without these the keep-list would fire on an add-on
        // cancellation. The keyword rule happens to get these right today by
        // requiring "Local" as well, but they are pinned here because the
        // consequence of a future rule change is a confusing question put to a
        // customer who is already cancelling.
        'Social Media Marketing Bundle' => ['addon'],
        'Social Media Marketing Bundle (Incl Website Hosting and Booking System)' => ['addon'],
        'Direct Marketing Bundle'       => ['addon'],
        'Mega Marketing Solo'           => ['addon'],
        'Social Media Marketing Solo'   => ['addon'],
        'Visibility Boost Solo - No Contract' => ['addon'],
    ],
];
