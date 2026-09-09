<?php
/**
 * Amelia booking plugin setup.
 *
 * Amelia keeps almost everything in one option, `amelia_settings`, holding a
 * JSON document with a section per settings screen: company, notifications,
 * payments, activation, general, and so on. The licence sits in there too,
 * under `activation.licence`, rather than in an option of its own. The email
 * templates are the exception and live in their own table,
 * `wp_amelia_notifications`.
 *
 * Both are reachable from inside WordPress, so the same approach as
 * WordPressSetup is used: a short-lived helper is uploaded into the document
 * root, called once over HTTPS with a one-time token, and deleted again. The
 * helper merges our values into whatever Amelia already has rather than
 * replacing the document, so settings we say nothing about are left alone and
 * running the setup twice is harmless.
 *
 * Live payment keys are deliberately not handled here. The shared test keys
 * let a fresh site take a test booking end to end; the shop's own live keys
 * are entered per site by the team that hands the site over.
 */

class AmeliaSetup {

    const HELPER_PREFIX = 'l4u-amelia-';

    /** Slug of the free plugin on wordpress.org. */
    const LITE_SLUG = 'ameliabooking';

    /**
     * Currency per country, keyed by the country code in the countries table.
     * Amelia wants the ISO code; the symbol it derives itself.
     * @return array
     */
    public static function currencies() {
        return [
            'AU'  => 'AUD',
            'NZ'  => 'NZD',
            'USA' => 'USD',
            'US'  => 'USD',
            'TH'  => 'THB',
            'UK'  => 'GBP',
            'GB'  => 'GBP',
            'CA'  => 'CAD',
        ];
    }

    /**
     * The currency for a country code, falling back to AUD.
     * @param string $code
     * @return string
     */
    public static function currencyFor($code) {
        $currencies = self::currencies();
        $code       = strtoupper(trim($code));

        return isset($currencies[$code]) ? $currencies[$code] : 'AUD';
    }

    /**
     * Common SMTP hosts, so a mailbox on the site's own domain does not have
     * to be configured by hand. cPanel serves mail on the domain itself.
     * @param string $domain
     * @return array ['host' => string, 'port' => int, 'secure' => string]
     */
    public static function smtpDefaults($domain) {
        return [
            'host'   => 'mail.' . $domain,
            'port'   => 465,
            'secure' => 'ssl',
        ];
    }

    private $whm;
    private $username;

    /**
     * @param WHMAPI $whm
     * @param string $username cPanel account name
     */
    public function __construct($whm, $username) {
        $this->whm      = $whm;
        $this->username = $username;
    }

    /**
     * Apply the Amelia settings to the site on $domain.
     *
     * @param string $domain Domain currently serving the site
     * @param array  $opts   companyName, companyAddress, companyPhone,
     *                       companyWebsite, currency, adminEmail, smtpUser,
     *                       smtpPass, docRoot, config
     * @return array ['success' => bool, 'message' => string, 'steps' => array, 'raw' => array|null]
     */
    public function apply($domain, $opts) {
        $result = ['success' => false, 'message' => '', 'steps' => [], 'raw' => null];

        $session = $this->whm->createUserSession($this->username);
        if (!$session['success']) {
            $result['message'] = 'Could not open a cPanel session: ' . $session['message'];
            return $result;
        }

        $cpanel  = new CpanelAPI($session['url'], $this->username);
        $docRoot = !empty($opts['docRoot']) ? $opts['docRoot'] : 'public_html';
        $root    = $cpanel->homeDir() . '/' . trim($docRoot, '/');

        $token      = bin2hex(random_bytes(16));
        $helperName = self::HELPER_PREFIX . bin2hex(random_bytes(8)) . '.php';

        $write = $cpanel->writeFile($root, $helperName, self::helperSource($token));
        if (!$write['success']) {
            $result['message'] = 'Could not upload the helper script: ' . $write['message'];
            return $result;
        }

        $helperPath = $root . '/' . $helperName;
        $config     = isset($opts['config']) && is_array($opts['config']) ? $opts['config'] : [];
        $smtp       = self::smtpDefaults($domain);

        $call = self::callHelper($domain, $helperName, [
            'token'            => $token,
            'plugin_slug'      => self::LITE_SLUG,
            'licence_key'      => isset($config['amelia_licence_key']) ? $config['amelia_licence_key'] : '',
            'company_name'     => isset($opts['companyName']) ? $opts['companyName'] : '',
            'company_address'  => isset($opts['companyAddress']) ? $opts['companyAddress'] : '',
            'company_phone'    => isset($opts['companyPhone']) ? $opts['companyPhone'] : '',
            'company_website'  => 'https://' . $domain,
            'company_email'    => isset($opts['adminEmail']) ? $opts['adminEmail'] : '',
            'currency'         => isset($opts['currency']) ? $opts['currency'] : 'AUD',
            // A mailbox on the site's own domain, so notifications are sent
            // by the shop rather than by the hosting account
            'smtp_host'        => $smtp['host'],
            'smtp_port'        => $smtp['port'],
            'smtp_secure'      => $smtp['secure'],
            'smtp_user'        => isset($opts['smtpUser']) ? $opts['smtpUser'] : '',
            'smtp_pass'        => isset($opts['smtpPass']) ? $opts['smtpPass'] : '',
            'stripe_pk'        => isset($config['stripe_pk_test']) ? $config['stripe_pk_test'] : '',
            'stripe_sk'        => isset($config['stripe_sk_test']) ? $config['stripe_sk_test'] : '',
        ]);

        // Always remove the helper, whatever the outcome
        $delete = $cpanel->deleteFile($helperPath);

        $result['steps'] = $call['steps'];
        $result['raw']   = $call['raw'];

        if (!$call['success']) {
            $result['message'] = $call['message'];
            if (!$delete['success']) {
                $result['message'] .= ' (the helper script could not be removed: ' . $helperPath . ')';
            }
            return $result;
        }

        $result['success'] = true;
        $result['message'] = $call['message'] !== '' ? $call['message'] : 'Amelia setup applied.';

        if (!$delete['success']) {
            $result['message'] .= ' Warning: the helper script could not be removed: ' . $helperPath;
        }

        return $result;
    }

    /**
     * Request the uploaded helper over HTTPS.
     * @param string $domain
     * @param string $helperName
     * @param array  $post
     * @return array ['success' => bool, 'message' => string, 'steps' => array, 'raw' => array|null]
     */
    private static function callHelper($domain, $helperName, $post) {
        $out = ['success' => false, 'message' => '', 'steps' => [], 'raw' => null];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://' . $domain . '/' . $helperName,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            // Installing the plugin happens inside this one request
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            $out['message'] = 'Could not reach the helper script: ' . $curlErr;
            return $out;
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            $out['message'] = 'The helper script returned an unexpected response (HTTP ' . $httpCode . ').';
            return $out;
        }

        $out['raw']   = $decoded;
        $out['steps'] = isset($decoded['steps']) && is_array($decoded['steps']) ? $decoded['steps'] : [];

        if (empty($decoded['success'])) {
            $out['message'] = !empty($decoded['message']) ? $decoded['message'] : 'The helper script reported a failure.';
            return $out;
        }

        $out['success'] = true;
        $out['message'] = isset($decoded['message']) ? $decoded['message'] : '';

        return $out;
    }

    /**
     * Source of the throwaway helper.
     *
     * It answers a single POST carrying the matching token, loads WordPress,
     * and merges our values into Amelia's own settings document. Nothing it
     * writes is destructive: sections Amelia already has keep every key we do
     * not name, so a site that has been tuned by hand only gains what we set.
     *
     * @param string $token
     * @return string
     */
    private static function helperSource($token) {
        $token = var_export($token, true);

        return <<<PHP
<?php
// Temporary helper written by masterPanel. Safe to delete.

\$expected = {$token};

if (empty(\$_POST['token']) || !hash_equals(\$expected, \$_POST['token'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid token.']);
    exit;
}

\$wpLoad = __DIR__ . '/wp-load.php';
if (!file_exists(\$wpLoad)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'wp-load.php was not found next to the helper script.']);
    exit;
}

// Installing a plugin takes longer than the default limit on shared hosting
@set_time_limit(600);

define('WP_USE_THEMES', false);
require_once \$wpLoad;
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

\$steps = [];

function l4u_step(&\$steps, \$name, \$ok, \$detail = '') {
    \$steps[] = ['name' => \$name, 'success' => (bool) \$ok, 'detail' => \$detail];
}

/**
 * The plugin file of an installed plugin, by slug, e.g. 'ameliabooking'
 * gives back 'ameliabooking/ameliabooking.php'.
 */
function l4u_plugin_file(\$slug) {
    foreach (get_plugins() as \$file => \$data) {
        if (strpos(\$file, \$slug . '/') === 0) {
            return \$file;
        }
    }

    return '';
}

/**
 * Install and activate a plugin from wordpress.org, leaving one that is
 * already there alone beyond making sure it is switched on.
 */
function l4u_ensure_plugin(\$slug) {
    \$file = l4u_plugin_file(\$slug);

    if (\$file === '') {
        \$api = plugins_api('plugin_information', ['slug' => \$slug, 'fields' => ['sections' => false]]);

        if (is_wp_error(\$api)) {
            return ['success' => false, 'detail' => \$api->get_error_message()];
        }

        \$upgrader  = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        \$installed = \$upgrader->install(\$api->download_link);

        if (is_wp_error(\$installed)) {
            return ['success' => false, 'detail' => \$installed->get_error_message()];
        }

        if (!\$installed) {
            return ['success' => false, 'detail' => 'The plugin could not be installed.'];
        }

        wp_clean_plugins_cache();
        \$file = l4u_plugin_file(\$slug);

        if (\$file === '') {
            return ['success' => false, 'detail' => 'The plugin was installed but could not be found afterwards.'];
        }
    }

    if (is_plugin_active(\$file)) {
        return ['success' => true, 'detail' => 'Already active.'];
    }

    \$activated = activate_plugin(\$file);

    if (is_wp_error(\$activated)) {
        return ['success' => false, 'detail' => \$activated->get_error_message()];
    }

    return ['success' => true, 'detail' => 'Installed and activated.'];
}

// Amelia has to be there before any of its settings mean anything
\$plugin = l4u_ensure_plugin(\$_POST['plugin_slug']);
l4u_step(\$steps, 'Amelia plugin', \$plugin['success'], \$plugin['detail']);

if (!\$plugin['success']) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Amelia could not be installed, so no settings were applied.',
        'steps'   => \$steps,
    ]);
    exit;
}

// Amelia stores everything as one JSON document. Reading it first and
// writing back a merge keeps whatever the site already had.
\$raw      = get_option('amelia_settings');
\$settings = is_string(\$raw) ? json_decode(\$raw, true) : (is_array(\$raw) ? \$raw : []);

if (!is_array(\$settings)) {
    \$settings = [];
}

/**
 * Merge \$new into \$section of \$settings, leaving keys we do not name alone.
 */
function l4u_merge_section(&\$settings, \$section, \$new) {
    \$current = isset(\$settings[\$section]) && is_array(\$settings[\$section]) ? \$settings[\$section] : [];
    \$settings[\$section] = array_merge(\$current, \$new);
}

// Company - what the shop is called, where it is, how to reach it
\$company = [];

if (\$_POST['company_name'] !== '') {
    \$company['name'] = \$_POST['company_name'];
}

if (\$_POST['company_address'] !== '') {
    \$company['address'] = \$_POST['company_address'];
}

if (\$_POST['company_phone'] !== '') {
    \$company['phone'] = \$_POST['company_phone'];
}

\$company['website'] = \$_POST['company_website'];

if (!empty(\$company)) {
    l4u_merge_section(\$settings, 'company', \$company);
    l4u_step(\$steps, 'Shop details', true, \$_POST['company_name'] !== '' ? \$_POST['company_name'] : \$_POST['company_website']);
} else {
    l4u_step(\$steps, 'Shop details', true, 'Nothing supplied - skipped.');
}

// Payments - the currency the shop trades in, on-site always available, and
// Stripe switched on when test keys were configured in masterPanel
\$payments = [
    'currency' => \$_POST['currency'],
    'priceSeparator' => isset(\$settings['payments']['priceSeparator']) ? \$settings['payments']['priceSeparator'] : 1,
    'onSite'   => true,
];

\$stripePk = trim(\$_POST['stripe_pk']);
\$stripeSk = trim(\$_POST['stripe_sk']);

if (\$stripePk !== '' && \$stripeSk !== '') {
    \$currentStripe = isset(\$settings['payments']['stripe']) && is_array(\$settings['payments']['stripe'])
        ? \$settings['payments']['stripe']
        : [];

    // testMode stays on: these are test keys, and the shop's own live keys
    // are put in per site by the team that hands the site over
    \$payments['stripe'] = array_merge(\$currentStripe, [
        'enabled'       => true,
        'testMode'      => true,
        'testPublishableKey' => \$stripePk,
        'testSecretKey'      => \$stripeSk,
    ]);

    \$stripeDetail = 'Test keys applied, test mode on.';
} else {
    \$stripeDetail = 'No Stripe keys configured, so on-site payment only.';
}

l4u_merge_section(\$settings, 'payments', \$payments);
l4u_step(\$steps, 'Payments', true, \$_POST['currency'] . '. ' . \$stripeDetail);

// Notifications - who the emails come from, and over which mail server
\$notifications = [
    'senderName' => \$_POST['company_name'] !== '' ? \$_POST['company_name'] : \$_POST['company_website'],
];

\$smtpUser = trim(\$_POST['smtp_user']);
\$smtpPass = \$_POST['smtp_pass'];

if (\$smtpUser !== '' && \$smtpPass !== '') {
    \$notifications['mailService']  = 'smtp';
    \$notifications['smtpHost']     = \$_POST['smtp_host'];
    \$notifications['smtpPort']     = (int) \$_POST['smtp_port'];
    \$notifications['smtpSecure']   = \$_POST['smtp_secure'];
    \$notifications['smtpUsername'] = \$smtpUser;
    \$notifications['smtpPassword'] = \$smtpPass;
    \$notifications['senderEmail']  = \$smtpUser;

    // Amelia's own mailer honours this on every edition, but the settings
    // screen only offers SMTP from Starter upwards, so on Lite the value is
    // live while the dropdown shows it greyed out
    \$mailDetail = 'SMTP on ' . \$_POST['smtp_host'] . ' as ' . \$smtpUser
                . '. On Amelia Lite the settings screen greys SMTP out, but sending still uses it.';
} else {
    // Without a mailbox the plugin's own default is the safer choice: PHP
    // mail still sends, where half-configured SMTP would not
    \$mailDetail = 'No SMTP mailbox stored, so WordPress mail is left in place.';

    if (\$_POST['company_email'] !== '') {
        \$notifications['senderEmail'] = \$_POST['company_email'];
    }
}

l4u_merge_section(\$settings, 'notifications', \$notifications);
l4u_step(\$steps, 'Notifications', true, \$mailDetail);

// The licence lives inside the settings document too, under activation,
// spelled the British way. There is no separate option for it.
\$licence = trim(\$_POST['licence_key']);

if (\$licence !== '') {
    l4u_merge_section(\$settings, 'activation', ['licence' => \$licence]);
    l4u_step(\$steps, 'Amelia licence', true, 'Key stored.');
} else {
    l4u_step(\$steps, 'Amelia licence', true, 'No licence key configured - skipped.');
}

\$saved = update_option('amelia_settings', json_encode(\$settings));
l4u_step(\$steps, 'Amelia settings saved', true, \$saved ? 'Written.' : 'Already up to date.');


header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Amelia setup applied.',
    'steps'   => \$steps,
]);
PHP;
    }
}
