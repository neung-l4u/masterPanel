<?php
/**
 * DocuSign configuration — SAMPLE
 *
 * Copy this file to docusign_config.php and fill in the real values.
 * docusign_config.php is gitignored and must never be committed.
 *
 * Where to find each value:
 *   - integrationKey / userId / accountId : DocuSign Admin > Apps and Keys
 *   - privateKeyPath : the RSA private key generated alongside the integration key
 *
 * One-time consent (required before the first JWT call):
 *   https://account-d.docusign.com/oauth/auth
 *     ?response_type=code
 *     &scope=signature%20impersonation
 *     &client_id=<integrationKey>
 *     &redirect_uri=<any redirect URI registered on the app>
 *   Open that URL in a browser, log in as the impersonated user, click Accept.
 *   Use account.docusign.com instead of account-d for production.
 */

return [
    // --- OAuth / JWT ---
    'integrationKey' => '5a967ba4-06f2-41e3-8ce3-0f77e9838fdc',
    'userId'         => 'c198b268-89f1-4189-9aaa-6b719512beb5', // the user being impersonated
    'accountId'      => '5eefb16b-2be1-4dbc-894b-71fe0a7ee5b7',

    // Absolute path to the RSA private key (PEM). Keep it outside the web root.
    'privateKeyPath' => 'C:/xampp/credentials/docusign_private.key',

    // 'demo' for the sandbox, 'production' for live accounts.
    'environment'    => 'demo',

    // --- Signing behaviour ---
    // Where DocuSign returns the signer after an embedded signing session.
    'returnUrl'      => 'https://report.localforyou.com/api/docusign/signing_complete.php',

    // Sender name/email shown on the envelope emails.
    'senderName'     => 'Local For You Pty Ltd',

    // Company countersigner. Leave email empty to skip the countersignature step.
    'counterSignerName'  => '',
    'counterSignerEmail' => '',

    // --- Logging ---
    'logFile'        => __DIR__ . '/logs/docusign.log',
];