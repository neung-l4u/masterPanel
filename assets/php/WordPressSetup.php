<?php
/**
 * WordPress post-install setup.
 *
 * Softaculous leaves a bare WordPress behind; everything the checklist asks
 * for afterwards - locale, timezone, admin email, tagline, ping services,
 * search engine visibility, and the two standing user accounts - lives inside
 * WordPress itself and cannot be reached from here.
 *
 * The approach mirrors DomainUpdater: a short-lived PHP helper is uploaded
 * into the account's document root, called once over HTTPS with a one-time
 * token, and deleted again. Unlike DomainUpdater the helper loads wp-load.php
 * and works through WordPress's own functions, so no database credentials
 * ever leave the server and WordPress applies its usual validation.
 *
 * Every step is idempotent. Running the setup a second time overwrites the
 * options with the same values and updates the existing users instead of
 * failing on a duplicate email, so the button is safe to press again.
 */

class WordPressSetup {

    const HELPER_PREFIX = 'l4u-setup-';

    /** One-click login helpers; each one is used once and deletes itself. */
    const LOGIN_HELPER_PREFIX = 'l4u-login-';

    /** Seconds a login link stays valid once it has been written. */
    const LOGIN_TTL = 60;

    /** Settings > General, admin email for every site we build. */
    const ADMIN_EMAIL = 'administrator@localforyou.com';

    /** Admin Access (L4U) - the same on every site. */
    const L4U_FIRST_NAME = 'Admin';
    const L4U_LAST_NAME  = 'Localforyou';
    const L4U_LOGIN      = 'AdminL4U';
    const L4U_EMAIL      = 'admin@localforyou.com';
    const L4U_PASSWORD   = 'L4U=New@min';

    /** Shop owner access - only the email changes per site. */
    const OWNER_FIRST_NAME = 'Shop';
    const OWNER_LAST_NAME  = 'Owner';
    const OWNER_LOGIN      = 'shopowner';
    const OWNER_PASSWORD   = 'Localbooking';

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
     * Locale and timezone offered in the form, keyed by the value posted back.
     * @return array
     */
    public static function locales() {
        return [
            'en_GB' => ['label' => 'English (UK)',          'locale' => 'en_GB', 'timezone' => 'Europe/London'],
            'en_US' => ['label' => 'English (US)',           'locale' => 'en_US', 'timezone' => 'America/New_York'],
            'th'    => ['label' => 'Thai',                   'locale' => 'th',    'timezone' => 'Asia/Bangkok'],
            'de_DE' => ['label' => 'German',                 'locale' => 'de_DE', 'timezone' => 'Europe/Berlin'],
            'fr_FR' => ['label' => 'French',                 'locale' => 'fr_FR', 'timezone' => 'Europe/Paris'],
            'es_ES' => ['label' => 'Spanish (Spain)',        'locale' => 'es_ES', 'timezone' => 'Europe/Madrid'],
            'it_IT' => ['label' => 'Italian',                'locale' => 'it_IT', 'timezone' => 'Europe/Rome'],
            'nl_NL' => ['label' => 'Dutch',                  'locale' => 'nl_NL', 'timezone' => 'Europe/Amsterdam'],
            'pt_PT' => ['label' => 'Portuguese (Portugal)',  'locale' => 'pt_PT', 'timezone' => 'Europe/Lisbon'],
            'en_AU' => ['label' => 'English (Australia)',    'locale' => 'en_AU', 'timezone' => 'Australia/Sydney'],
            'en_NZ' => ['label' => 'English (New Zealand)',  'locale' => 'en_NZ', 'timezone' => 'Pacific/Auckland'],
            'en_CA' => ['label' => 'English (Canada)',       'locale' => 'en_CA', 'timezone' => 'America/Toronto'],
        ];
    }

    /**
     * Resolve a posted locale key to its locale and timezone.
     * Anything unrecognised falls back to UK, which is where most sites sit.
     * @param string $key
     * @return array ['locale' => string, 'timezone' => string]
     */
    public static function resolveLocale($key) {
        $locales = self::locales();
        $entry   = isset($locales[$key]) ? $locales[$key] : $locales['en_GB'];

        return ['locale' => $entry['locale'], 'timezone' => $entry['timezone']];
    }

    /**
     * Ping Update Services written into Settings > Writing.
     * @return array
     */
    public static function pingServices() {
        return [
            'http://rpc.pingomatic.com',
            'http://rpc.twingly.com',
            'http://api.feedster.com/ping',
            'http://api.moreover.com/RPC2',
            'http://api.moreover.com/ping',
            'http://www.blogdigger.com/RPC2',
            'http://ping.blo.gs/',
            'http://blogsearch.google.com/ping/RPC2',
            'http://ping.feedburner.com',
            'http://topicexchange.com/RPC2',
            'http://www.weblogalot.com/ping',
            'http://rpc.weblogs.com/RPC2',
            'http://ping.blogs.yandex.ru/RPC2',
            'http://xping.pubsub.com/ping/',
        ];
    }

    /**
     * Plugins installed and activated on every site, by wordpress.org slug.
     *
     * The slug is the last part of the plugin's wordpress.org address, so
     * https://wordpress.org/plugins/elementor/ is simply 'elementor'.
     *
     * Only plugins in the public directory can be fetched this way. Anything
     * paid - Elementor Pro and the like - needs its own zip and licence and
     * is still installed by hand.
     *
     * @return array
     */
    public static function plugins() {
        return [
            // Elementor comes first so the header and footer add-on finds it
            'elementor',
            'header-footer-elementor',
            'astra-sites',
            'wordpress-seo',
            'contact-form-7',
            'insert-headers-and-footers',
            'updraftplus',
            'broken-link-checker',
            'ays-popup-box',
            'akismet',
        ];
    }

    /**
     * Contact Form 7 forms created on every site.
     *
     * Two forms are wanted: one that reaches the shop, and one that reaches
     * the customer. They differ in recipient, subject and body, so each is
     * described in full rather than derived from the other.
     *
     * Placeholders filled in when the form is created:
     *   {shop_email}    the shop's own address, from the website record
     *   {shop_address}  the shop's location, as one HTML block
     *   {admin_email}   administrator@localforyou.com
     *   {site_title}    the site's name
     *   {domain}        the domain serving the site
     *
     * Everything else is Contact Form 7's own mail-tag syntax and is passed
     * through untouched, so [your-name] and [_site_title] keep working. The
     * shop's name is left to [_site_title] on purpose: renaming the site then
     * renames it in the mail as well.
     *
     * @return array
     */
    public static function contactForms() {
        // The address block opens with the shop's own name, so the signature
        // does not repeat it. A site with no address stored falls back to the
        // site title alone rather than signing off with nothing.
        $signature = "Best Regards,<br><br>\n"
                   . '{shop_address}';

        return [
            [
                'title' => 'Contact form 1',
                'form'  => "<label> Your Name\n"
                         . "    [text* your-name autocomplete:name] </label>\n\n"
                         . "<label> Phone Number\n"
                         . "    [tel* PhoneNumber] </label>\n\n"
                         . "<label> Your Email\n"
                         . "    [email* your-email autocomplete:email] </label>\n\n"
                         . "<label> Your Message (optional)\n"
                         . "    [textarea your-message] </label>\n\n"
                         . '[submit "Submit"]',

                // Reaches the shop
                'mail' => [
                    'to'      => '{shop_email}',
                    'subject' => 'New Contact - [your-name]',
                    'from'    => '[_site_title] <wordpress@{domain}>',
                    // So a reply from the shop goes back to the enquirer
                    'headers' => 'Reply-To: [your-email]',
                    'body'    => "Congratulations, you have a new lead!<br><br>\n"
                               . "Name: [your-name]<br>\n"
                               . "Email: [your-email]<br>\n"
                               . "Phone: [PhoneNumber]<br><br>\n"
                               . "Message Body:<br>\n"
                               . "[your-message]<br><br>\n"
                               . $signature,
                ],

                // Reaches the person who filled the form in
                'mail_2' => [
                    'to'      => '[your-email]',
                    'subject' => '[_site_title] Thank you',
                    'from'    => '[_site_title] <wordpress@{domain}>',
                    'headers' => 'Reply-To: {shop_email}',
                    'body'    => "Dear Customer,<br><br>\n"
                               . "Thank you for filling our contact form, one of [_site_title]'s team members will contact you ASAP!<br><br>\n"
                               . $signature,
                ],
            ],
        ];
    }

    /**
     * WPCode snippets added to every site.
     *
     * Each entry needs a title, the code itself, and where it belongs.
     * Location is WPCode's own name for the insertion point, e.g.
     * 'site_wide_header', 'site_wide_footer', 'site_wide_body_open'.
     *
     * Type is 'php', 'html', 'js' or 'css'. PHP snippets are stored without
     * their opening tag, which is how WPCode expects them, and one that hooks
     * an action runs wherever that action fires rather than at a location, so
     * 'site_wide_header' is only a placeholder for those.
     *
     * @return array
     */
    public static function codeSnippets() {
        // Nowdoc: the snippet keeps its own dollars and tags untouched
        $bookingLink = <<<'SNIPPET'
add_action('wp_footer', 'change_booking_link_on_home');
function change_booking_link_on_home() {
    if ( is_front_page() || is_home() ) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var buttons = document.querySelectorAll('a[href*="/home/#booking"]');
                buttons.forEach(function(button) {
                    button.href = button.href.replace('/home/#booking', '#booking');
                });
            });
        </script>
        <?php
    }
}
SNIPPET;

        return [
            [
                'title'    => 'Booking link on home',
                'code'     => $bookingLink,
                // The snippet hooks wp_footer itself, so the location only
                // decides that it is loaded, not where its output lands
                'location' => 'site_wide_header',
                'type'     => 'php',
            ],
        ];
    }

    /**
     * Turn a stored address into the one HTML block the mail bodies expect.
     *
     * The Location field is a textarea, so its line breaks have to become
     * <br> for a mail that is sent as HTML.
     *
     * @param string $address
     * @param string $default Used when nothing is stored
     * @return string
     */
    public static function addressToHtml($address, $default = '') {
        $address = trim((string) $address);

        if ($address === '') {
            return $default;
        }

        $lines = preg_split('/\r\n|\r|\n/', $address);
        $lines = array_filter(array_map('trim', $lines), 'strlen');

        return implode("<br>\n", $lines);
    }

    /**
     * Replace the {placeholders} in every string of a nested array.
     *
     * Contact Form 7's own mail tags use square brackets, so braces are used
     * here and the two never collide.
     *
     * @param array $data
     * @param array $replacements placeholder => value
     * @return array
     */
    public static function fillPlaceholders($data, $replacements) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::fillPlaceholders($value, $replacements);
            } elseif (is_string($value)) {
                $data[$key] = strtr($value, $replacements);
            }
        }

        return $data;
    }

    /**
     * Apply the whole checklist to the site on $domain.
     *
     * @param string $domain Domain currently serving the site
     * @param array  $opts   siteTitle, localeKey, tagline, ownerEmail, docRoot,
     *                       config (licence keys and storage credentials)
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
        $locale     = self::resolveLocale(isset($opts['localeKey']) ? $opts['localeKey'] : '');
        $config     = isset($opts['config']) && is_array($opts['config']) ? $opts['config'] : [];
        $siteTitle  = isset($opts['siteTitle']) ? $opts['siteTitle'] : '';
        $shopEmail  = isset($opts['ownerEmail']) ? $opts['ownerEmail'] : '';

        // The forms are written once here rather than inside the helper, so
        // the placeholders never have to travel separately
        $forms = self::fillPlaceholders(self::contactForms(), [
            '{shop_email}'   => $shopEmail !== '' ? $shopEmail : self::ADMIN_EMAIL,
            // Falls back to the site's own name so the sign-off is never blank
            '{shop_address}' => self::addressToHtml(isset($opts['address']) ? $opts['address'] : '', '[_site_title]'),
            '{admin_email}'  => self::ADMIN_EMAIL,
            '{site_title}'   => $siteTitle !== '' ? $siteTitle : $domain,
            '{domain}'       => $domain,
        ]);

        $call = self::callHelper($domain, $helperName, [
            'token'       => $token,
            'locale'      => $locale['locale'],
            'timezone'    => $locale['timezone'],
            'admin_email' => self::ADMIN_EMAIL,
            'site_title'  => isset($opts['siteTitle']) ? $opts['siteTitle'] : '',
            // Blank unless the client actually has a tagline for the business
            'tagline'     => isset($opts['tagline']) ? $opts['tagline'] : '',
            'ping_sites'  => implode("\n", self::pingServices()),
            'plugins'     => implode("\n", self::plugins()),
            // Structured settings travel as JSON so the shapes survive intact
            'forms'       => json_encode($forms),
            'snippets'    => json_encode(self::codeSnippets()),
            'akismet_key' => isset($config['akismet_api_key']) ? $config['akismet_api_key'] : '',
            's3_key'      => isset($config['updraft_s3_key']) ? $config['updraft_s3_key'] : '',
            's3_secret'   => isset($config['updraft_s3_secret']) ? $config['updraft_s3_secret'] : '',
            's3_bucket'   => isset($config['updraft_s3_bucket']) ? $config['updraft_s3_bucket'] : '',
            's3_region'   => isset($config['updraft_s3_region']) ? $config['updraft_s3_region'] : '',
            'owner_email' => isset($opts['ownerEmail']) ? $opts['ownerEmail'] : '',
            'l4u_user'    => self::L4U_LOGIN,
            'l4u_email'   => self::L4U_EMAIL,
            'l4u_pass'    => self::L4U_PASSWORD,
            'l4u_first'   => self::L4U_FIRST_NAME,
            'l4u_last'    => self::L4U_LAST_NAME,
            'owner_user'  => self::OWNER_LOGIN,
            'owner_pass'  => self::OWNER_PASSWORD,
            'owner_first' => self::OWNER_FIRST_NAME,
            'owner_last'  => self::OWNER_LAST_NAME,
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
        $result['message'] = $call['message'] !== '' ? $call['message'] : 'WordPress setup applied.';

        if (!$delete['success']) {
            $result['message'] .= ' Warning: the helper script could not be removed: ' . $helperPath;
        }

        return $result;
    }

    /**
     * Prepare a one-click login into wp-admin.
     *
     * A self-deleting helper carrying a one-time token is written into the
     * document root and its URL handed back for the browser to open. The
     * helper sets the auth cookie for the L4U admin account, removes itself,
     * and forwards to wp-admin. It refuses anything after LOGIN_TTL seconds
     * so a link that was never opened cannot be used later, and any helper
     * left behind by an earlier attempt is swept away before a new one is
     * written.
     *
     * @param string $domain  Domain currently serving the site
     * @param string $docRoot Directory under the account home, default public_html
     * @return array ['success' => bool, 'message' => string, 'url' => string]
     */
    public function loginLink($domain, $docRoot = 'public_html') {
        $result = ['success' => false, 'message' => '', 'url' => ''];

        $session = $this->whm->createUserSession($this->username);
        if (!$session['success']) {
            $result['message'] = 'Could not open a cPanel session: ' . $session['message'];
            return $result;
        }

        $cpanel = new CpanelAPI($session['url'], $this->username);
        $root   = $cpanel->homeDir() . '/' . trim($docRoot, '/');

        // Leftovers from links that were never opened
        $listing = $cpanel->listFiles($root);
        if ($listing['success']) {
            foreach ($listing['files'] as $file) {
                if (strpos($file, self::LOGIN_HELPER_PREFIX) === 0) {
                    $cpanel->deleteFile($root . '/' . $file);
                }
            }
        }

        $token      = bin2hex(random_bytes(16));
        $helperName = self::LOGIN_HELPER_PREFIX . bin2hex(random_bytes(8)) . '.php';
        $expires    = time() + self::LOGIN_TTL;

        $write = $cpanel->writeFile($root, $helperName, self::loginHelperSource($token, $expires));
        if (!$write['success']) {
            $result['message'] = 'Could not upload the login helper: ' . $write['message'];
            return $result;
        }

        $result['success'] = true;
        $result['message'] = 'Login link ready.';
        $result['url']     = 'https://' . $domain . '/' . $helperName . '?token=' . $token;

        return $result;
    }

    /**
     * Source of the self-deleting login helper.
     *
     * Whatever happens on the first request - good token, bad token, or a
     * link past its expiry - the file removes itself before answering, so it
     * can only ever be used once.
     *
     * @param string $token
     * @param int    $expires Unix timestamp after which the link is refused
     * @return string
     */
    private static function loginHelperSource($token, $expires) {
        $token   = var_export($token, true);
        $expires = (int) $expires;
        $login   = var_export(self::L4U_LOGIN, true);

        return <<<PHP
<?php
// Temporary one-click login written by masterPanel. Safe to delete.

\$expected = {$token};
\$expires  = {$expires};
\$login    = {$login};

// Single use: gone before anything else happens
@unlink(__FILE__);

function l4u_refuse(\$message) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo \$message;
    exit;
}

if (time() > \$expires) {
    l4u_refuse('This login link has expired. Open it again from masterPanel.');
}

if (empty(\$_GET['token']) || !hash_equals(\$expected, (string) \$_GET['token'])) {
    l4u_refuse('Invalid login link.');
}

\$wpLoad = __DIR__ . '/wp-load.php';
if (!file_exists(\$wpLoad)) {
    l4u_refuse('wp-load.php was not found next to the login helper.');
}

define('WP_USE_THEMES', false);
require_once \$wpLoad;

\$user = get_user_by('login', \$login);

// Fall back to the first administrator when the L4U account is missing
if (!\$user) {
    \$admins = get_users(['role' => 'administrator', 'orderby' => 'ID', 'order' => 'ASC', 'number' => 1]);
    \$user   = !empty(\$admins) ? \$admins[0] : null;
}

if (!\$user) {
    l4u_refuse('No administrator account was found on this site.');
}

wp_set_current_user(\$user->ID, \$user->user_login);
wp_set_auth_cookie(\$user->ID, true, is_ssl());
do_action('wp_login', \$user->user_login, \$user);

wp_safe_redirect(admin_url());
exit;
PHP;
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
            // A language pack and a handful of plugins all download inside
            // this one request, so it is given room to finish
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
        ]);

        $body    = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
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
     * and works through its own API so options and users end up exactly as
     * they would if they had been set by hand. It holds no credentials of its
     * own beyond the token, so a leftover copy is useless once the token is
     * forgotten.
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

// WordPress sits next to this file; loading it gives us the full API
\$wpLoad = __DIR__ . '/wp-load.php';
if (!file_exists(\$wpLoad)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'wp-load.php was not found next to the helper script.']);
    exit;
}

// Downloading a language pack and several plugins takes longer than the
// default limit on most shared hosting
@set_time_limit(600);

define('WP_USE_THEMES', false);
require_once \$wpLoad;
require_once ABSPATH . 'wp-admin/includes/user.php';
require_once ABSPATH . 'wp-admin/includes/translation-install.php';
// Everything the plugin installer needs: the API client, the upgrader
// classes, and the filesystem abstraction they run through
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/misc.php';

\$steps = [];

function l4u_step(&\$steps, \$name, \$ok, \$detail = '') {
    \$steps[] = ['step' => \$name, 'success' => (bool) \$ok, 'detail' => \$detail];
}

/**
 * Create the user, or bring an existing one back in line.
 * Running the setup twice must not fail on a duplicate login or email.
 */
function l4u_ensure_user(\$login, \$email, \$pass, \$first, \$last, \$role) {
    if (!is_email(\$email)) {
        return ['success' => false, 'detail' => 'Invalid email address: ' . \$email];
    }

    \$existing = get_user_by('login', \$login);
    if (!\$existing) {
        \$existing = get_user_by('email', \$email);
    }

    \$data = [
        'user_login'   => \$login,
        'user_email'   => \$email,
        'user_pass'    => \$pass,
        'first_name'   => \$first,
        'last_name'    => \$last,
        'nickname'     => trim(\$first . ' ' . \$last),
        'display_name' => trim(\$first . ' ' . \$last),
        'role'         => \$role,
    ];

    if (\$existing) {
        \$data['ID'] = \$existing->ID;
        unset(\$data['user_login']);  // WordPress will not rename a login
        \$id   = wp_update_user(\$data);
        \$verb = 'updated';
    } else {
        \$id   = wp_insert_user(\$data);
        \$verb = 'created';
    }

    if (is_wp_error(\$id)) {
        return ['success' => false, 'detail' => \$id->get_error_message()];
    }

    return ['success' => true, 'detail' => \$login . ' (' . \$role . ') ' . \$verb . '.'];
}

/**
 * Find the plugin file a slug installed to, e.g. 'elementor' becomes
 * 'elementor/elementor.php'. The file rarely matches the slug exactly, so
 * the installed list is searched rather than guessed at.
 */
function l4u_plugin_file(\$slug) {
    foreach (get_plugins() as \$file => \$plugin) {
        if (strpos(\$file, \$slug . '/') === 0) {
            return \$file;
        }
    }

    // Single-file plugins live directly in wp-content/plugins
    return file_exists(WP_PLUGIN_DIR . '/' . \$slug . '.php') ? \$slug . '.php' : '';
}

/**
 * Install a plugin from the wordpress.org directory and activate it.
 *
 * Already-installed plugins are left as they are and only activated, so
 * running the setup again neither reinstalls nor downgrades anything.
 */
function l4u_ensure_plugin(\$slug) {
    \$file = l4u_plugin_file(\$slug);

    if (\$file === '') {
        \$api = plugins_api('plugin_information', [
            'slug'   => \$slug,
            'fields' => ['sections' => false],
        ]);

        if (is_wp_error(\$api)) {
            return ['success' => false, 'detail' => \$api->get_error_message()];
        }

        // Skip_Plugin_Installation is off, so this really does write to disk
        \$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        \$installed = \$upgrader->install(\$api->download_link);

        if (is_wp_error(\$installed)) {
            return ['success' => false, 'detail' => \$installed->get_error_message()];
        }

        if (\$installed !== true) {
            return ['success' => false, 'detail' => 'The download or unpacking failed.'];
        }

        // get_plugins caches, so the fresh directory needs a second look
        wp_clean_plugins_cache();
        \$file = l4u_plugin_file(\$slug);

        if (\$file === '') {
            return ['success' => false, 'detail' => 'Installed, but the plugin file could not be found.'];
        }

        \$verb = 'installed';
    } else {
        \$verb = 'already installed';
    }

    if (is_plugin_active(\$file)) {
        return ['success' => true, 'detail' => \$slug . ' ' . \$verb . ' and already active.'];
    }

    \$activated = activate_plugin(\$file);

    if (is_wp_error(\$activated)) {
        return ['success' => false, 'detail' => \$slug . ' ' . \$verb . ', but activation failed: ' . \$activated->get_error_message()];
    }

    return ['success' => true, 'detail' => \$slug . ' ' . \$verb . ' and activated.'];
}

/**
 * Build one of Contact Form 7's mail templates.
 *
 * Every key the plugin expects is filled in, because a template missing one
 * of them shows up blank on the Mail tab.
 */
function l4u_cf7_mail(\$mail, \$active = true) {
    return [
        'active'             => \$active,
        'subject'            => isset(\$mail['subject']) ? \$mail['subject'] : '',
        'sender'             => isset(\$mail['from']) ? \$mail['from'] : '',
        'recipient'          => isset(\$mail['to']) ? \$mail['to'] : '',
        'body'               => isset(\$mail['body']) ? \$mail['body'] : '',
        'additional_headers' => isset(\$mail['headers']) ? \$mail['headers'] : '',
        'attachments'        => isset(\$mail['attachments']) ? \$mail['attachments'] : '',
        // The bodies carry <br>, so they are sent as HTML
        'use_html'           => isset(\$mail['html']) ? (bool) \$mail['html'] : true,
        'exclude_blank'      => !empty(\$mail['exclude_blank']),
    ];
}

/**
 * Create a Contact Form 7 form, or bring an existing one back in line.
 *
 * Forms are matched by title, so running the setup again rewrites the same
 * form rather than leaving a second copy behind.
 */
function l4u_ensure_contact_form(\$form) {
    \$title = \$form['title'];

    \$existing = get_posts([
        'post_type'      => 'wpcf7_contact_form',
        'title'          => \$title,
        'post_status'    => 'any',
        'posts_per_page' => 1,
    ]);

    if (!empty(\$existing)) {
        \$id   = \$existing[0]->ID;
        \$verb = 'updated';
        wp_update_post(['ID' => \$id, 'post_title' => \$title]);
    } else {
        \$id = wp_insert_post([
            'post_type'   => 'wpcf7_contact_form',
            'post_title'  => \$title,
            'post_status' => 'publish',
        ]);
        \$verb = 'created';
    }

    if (is_wp_error(\$id) || !\$id) {
        return ['success' => false, 'detail' => is_wp_error(\$id) ? \$id->get_error_message() : 'The form could not be saved.'];
    }

    // Contact Form 7 keeps the form body and the mail templates in postmeta
    update_post_meta(\$id, '_form', isset(\$form['form']) ? \$form['form'] : '');

    // The notification to the shop
    update_post_meta(\$id, '_mail', l4u_cf7_mail(isset(\$form['mail']) ? \$form['mail'] : []));

    // The acknowledgement to the person who filled the form in. Contact Form 7
    // calls it "Mail (2)" and leaves it switched off unless asked for.
    if (!empty(\$form['mail_2'])) {
        update_post_meta(\$id, '_mail_2', l4u_cf7_mail(\$form['mail_2']));
    } else {
        update_post_meta(\$id, '_mail_2', l4u_cf7_mail([], false));
    }

    if (!empty(\$form['messages']) && is_array(\$form['messages'])) {
        update_post_meta(\$id, '_messages', \$form['messages']);
    }

    \$shortcode = '[contact-form-7 id="' . \$id . '" title="' . esc_attr(\$title) . '"]';

    return ['success' => true, 'detail' => \$verb . '. Shortcode: ' . \$shortcode];
}

/**
 * Create a WPCode snippet, or bring an existing one back in line.
 *
 * Snippets are matched by title for the same reason forms are.
 */
function l4u_ensure_snippet(\$snippet) {
    \$title = \$snippet['title'];

    \$existing = get_posts([
        'post_type'      => 'wpcode',
        'title'          => \$title,
        'post_status'    => 'any',
        'posts_per_page' => 1,
    ]);

    if (!empty(\$existing)) {
        \$id = \$existing[0]->ID;
        wp_update_post([
            'ID'           => \$id,
            'post_content' => isset(\$snippet['code']) ? \$snippet['code'] : '',
        ]);
        \$verb = 'updated';
    } else {
        \$id = wp_insert_post([
            'post_type'    => 'wpcode',
            'post_title'   => \$title,
            'post_content' => isset(\$snippet['code']) ? \$snippet['code'] : '',
            'post_status'  => 'publish',
        ]);
        \$verb = 'created';
    }

    if (is_wp_error(\$id) || !\$id) {
        return ['success' => false, 'detail' => is_wp_error(\$id) ? \$id->get_error_message() : 'The snippet could not be saved.'];
    }

    \$type = isset(\$snippet['type']) ? \$snippet['type'] : 'html';

    // A PHP snippet is loaded rather than printed somewhere, so it runs
    // everywhere and does its own hooking
    \$location = \$type === 'php'
        ? 'everywhere'
        : (isset(\$snippet['location']) ? \$snippet['location'] : 'site_wide_header');

    update_post_meta(\$id, '_wpcode_code_type', \$type);
    update_post_meta(\$id, '_wpcode_location', \$location);
    update_post_meta(\$id, '_wpcode_auto_insert', 1);
    update_post_meta(\$id, '_wpcode_active', 1);
    update_post_meta(\$id, '_wpcode_priority', 10);

    // WPCode keeps its own index of active snippets and reads that rather
    // than querying the posts, so a snippet added here stays invisible to it
    // until the cache is dropped
    wp_cache_delete('wpcode_snippets', 'wpcode');
    delete_transient('wpcode_snippets');

    return ['success' => true, 'detail' => \$verb . ' as ' . \$type . ', ' . \$location . '.'];
}

// --- Settings > General -------------------------------------------------

\$locale   = isset(\$_POST['locale']) ? \$_POST['locale'] : 'en_GB';
\$timezone = isset(\$_POST['timezone']) ? \$_POST['timezone'] : 'Europe/London';

// en_US ships with WordPress and has no language pack to install
if (\$locale !== 'en_US') {
    \$installed = wp_download_language_pack(\$locale);
    if (\$installed === false) {
        // Not fatal: the site still works, it just stays in English
        l4u_step(\$steps, 'Language pack', false, 'Could not download the language pack for ' . \$locale . '.');
        \$locale = 'en_US';
    } else {
        l4u_step(\$steps, 'Language pack', true, \$locale);
    }
} else {
    l4u_step(\$steps, 'Language pack', true, 'en_US is built in.');
}

update_option('WPLANG', \$locale === 'en_US' ? '' : \$locale);
l4u_step(\$steps, 'Site language', true, \$locale);

// A named timezone keeps daylight saving correct by itself
update_option('timezone_string', \$timezone);
update_option('gmt_offset', '');
l4u_step(\$steps, 'Timezone', true, \$timezone);

// update_option writes straight through; the admin screen would instead send
// a confirmation email and hold the address in new_admin_email until clicked
if (!empty(\$_POST['admin_email']) && is_email(\$_POST['admin_email'])) {
    update_option('admin_email', \$_POST['admin_email']);
    delete_option('new_admin_email');
    l4u_step(\$steps, 'Admin email', true, \$_POST['admin_email']);
} else {
    l4u_step(\$steps, 'Admin email', false, 'No valid admin email supplied.');
}

if (isset(\$_POST['site_title']) && \$_POST['site_title'] !== '') {
    update_option('blogname', \$_POST['site_title']);
    l4u_step(\$steps, 'Site title', true, \$_POST['site_title']);
}

// Blank unless the client has a tagline of their own
\$tagline = isset(\$_POST['tagline']) ? trim(\$_POST['tagline']) : '';
update_option('blogdescription', \$tagline);
l4u_step(\$steps, 'Tagline', true, \$tagline === '' ? 'Removed.' : \$tagline);

// --- Settings > Writing -------------------------------------------------

if (isset(\$_POST['ping_sites'])) {
    update_option('ping_sites', \$_POST['ping_sites']);
    \$count = count(array_filter(array_map('trim', explode("\\n", \$_POST['ping_sites']))));
    l4u_step(\$steps, 'Ping services', true, \$count . ' services.');
}

// --- Settings > Reading -------------------------------------------------

// 0 asks search engines not to index the site
update_option('blog_public', 0);
l4u_step(\$steps, 'Search engine visibility', true, 'Discouraged.');

// --- Users --------------------------------------------------------------

\$l4u = l4u_ensure_user(
    \$_POST['l4u_user'], \$_POST['l4u_email'], \$_POST['l4u_pass'],
    \$_POST['l4u_first'], \$_POST['l4u_last'], 'administrator'
);
l4u_step(\$steps, 'Admin user', \$l4u['success'], \$l4u['detail']);

if (!empty(\$_POST['owner_email'])) {
    \$owner = l4u_ensure_user(
        \$_POST['owner_user'], \$_POST['owner_email'], \$_POST['owner_pass'],
        \$_POST['owner_first'], \$_POST['owner_last'], 'editor'
    );
    l4u_step(\$steps, 'Shop owner user', \$owner['success'], \$owner['detail']);
} else {
    l4u_step(\$steps, 'Shop owner user', false, 'No shop owner email supplied - skipped.');
}

// --- Plugins ------------------------------------------------------------

\$slugs = isset(\$_POST['plugins'])
    ? array_filter(array_map('trim', explode("\\n", \$_POST['plugins'])))
    : [];

foreach (\$slugs as \$slug) {
    // The slug forms part of a directory name, so nothing else is accepted
    if (!preg_match('/^[a-z0-9-]+\$/', \$slug)) {
        l4u_step(\$steps, 'Plugin: ' . \$slug, false, 'Not a valid plugin slug.');
        continue;
    }

    \$plugin = l4u_ensure_plugin(\$slug);
    l4u_step(\$steps, 'Plugin: ' . \$slug, \$plugin['success'], \$plugin['detail']);
}

// --- Plugin settings ----------------------------------------------------

// Yoast SEO
if (is_plugin_active(l4u_plugin_file('wordpress-seo'))) {
    // Dismissing the wizard keeps the setup notice off the dashboard
    \$titles = get_option('wpseo_titles', []);
    if (!is_array(\$titles)) \$titles = [];
    \$titles['title-home-wpseo']    = '%%sitename%% %%page%% %%sep%% %%sitedesc%%';
    \$titles['breadcrumbs-enable']  = true;
    // Thin archives add nothing for a single-location business site
    \$titles['noindex-archive-wpseo'] = true;
    \$titles['noindex-author-wpseo']  = true;
    \$titles['noindex-tax-post_tag']  = true;
    \$titles['disable-author']        = true;
    \$titles['disable-date']          = true;
    update_option('wpseo_titles', \$titles);

    \$wpseo = get_option('wpseo', []);
    if (!is_array(\$wpseo)) \$wpseo = [];
    \$wpseo['show_onboarding_notice']    = false;
    \$wpseo['first_activated_on']        = time();
    \$wpseo['dismiss_configuration_workout_notice'] = true;
    update_option('wpseo', \$wpseo);

    l4u_step(\$steps, 'Yoast SEO settings', true, 'Wizard dismissed, archives noindexed, breadcrumbs on.');
} else {
    l4u_step(\$steps, 'Yoast SEO settings', true, 'Yoast is not active - skipped.');
}

// Akismet
if (is_plugin_active(l4u_plugin_file('akismet'))) {
    \$akismetKey = isset(\$_POST['akismet_key']) ? trim(\$_POST['akismet_key']) : '';

    if (\$akismetKey === '') {
        l4u_step(\$steps, 'Akismet key', true, 'No key configured - skipped.');
    } else {
        update_option('wordpress_api_key', \$akismetKey);
        // Comments Akismet is sure about never reach the queue
        update_option('akismet_strictness', '1');
        update_option('akismet_show_user_comments_approved', '0');
        l4u_step(\$steps, 'Akismet key', true, 'Key applied.');
    }
} else {
    l4u_step(\$steps, 'Akismet key', true, 'Akismet is not active - skipped.');
}

// UpdraftPlus
if (is_plugin_active(l4u_plugin_file('updraftplus'))) {
    \$s3Key    = isset(\$_POST['s3_key']) ? trim(\$_POST['s3_key']) : '';
    \$s3Secret = isset(\$_POST['s3_secret']) ? trim(\$_POST['s3_secret']) : '';
    \$s3Bucket = isset(\$_POST['s3_bucket']) ? trim(\$_POST['s3_bucket']) : '';
    \$s3Region = isset(\$_POST['s3_region']) ? trim(\$_POST['s3_region']) : '';

    // A weekly schedule is worth setting even with no remote storage
    update_option('updraft_interval', 'weekly');
    update_option('updraft_interval_database', 'weekly');
    update_option('updraft_retain', 4);
    update_option('updraft_retain_db', 4);

    if (\$s3Key === '' || \$s3Secret === '' || \$s3Bucket === '') {
        l4u_step(\$steps, 'UpdraftPlus', true, 'Weekly schedule set. No S3 credentials configured, so storage was left alone.');
    } else {
        update_option('updraft_service', ['s3']);
        // UpdraftPlus keys each storage entry by an instance id
        update_option('updraft_s3', [
            'version'  => 1,
            'settings' => [
                'l4u' => [
                    'accesskey' => \$s3Key,
                    'secretkey' => \$s3Secret,
                    'path'      => \$s3Bucket,
                    'rrs'       => 0,
                    'server_side_encryption' => 0,
                    'region'    => \$s3Region,
                ],
            ],
        ]);
        l4u_step(\$steps, 'UpdraftPlus', true, 'Weekly schedule and S3 storage set.');
    }
} else {
    l4u_step(\$steps, 'UpdraftPlus', true, 'UpdraftPlus is not active - skipped.');
}

// Contact Form 7
\$forms = isset(\$_POST['forms']) ? json_decode(\$_POST['forms'], true) : [];
if (!is_array(\$forms)) \$forms = [];

if (\$forms === []) {
    l4u_step(\$steps, 'Contact forms', true, 'No forms configured - skipped.');
} elseif (!class_exists('WPCF7_ContactForm')) {
    l4u_step(\$steps, 'Contact forms', false, 'Contact Form 7 is not active.');
} else {
    foreach (\$forms as \$form) {
        \$title = isset(\$form['title']) ? \$form['title'] : '';

        if (\$title === '') {
            l4u_step(\$steps, 'Contact form', false, 'A form was configured without a title.');
            continue;
        }

        \$result = l4u_ensure_contact_form(\$form);
        l4u_step(\$steps, 'Contact form: ' . \$title, \$result['success'], \$result['detail']);
    }
}

// WPCode
\$snippets = isset(\$_POST['snippets']) ? json_decode(\$_POST['snippets'], true) : [];
if (!is_array(\$snippets)) \$snippets = [];

if (\$snippets === []) {
    l4u_step(\$steps, 'Code snippets', true, 'No snippets configured - skipped.');
} elseif (!post_type_exists('wpcode')) {
    l4u_step(\$steps, 'Code snippets', false, 'WPCode is not active.');
} else {
    foreach (\$snippets as \$snippet) {
        \$title = isset(\$snippet['title']) ? \$snippet['title'] : '';

        if (\$title === '') {
            l4u_step(\$steps, 'Code snippet', false, 'A snippet was configured without a title.');
            continue;
        }

        \$result = l4u_ensure_snippet(\$snippet);
        l4u_step(\$steps, 'Snippet: ' . \$title, \$result['success'], \$result['detail']);
    }
}

// --- Report back --------------------------------------------------------

\$failed = [];
foreach (\$steps as \$s) {
    if (!\$s['success']) \$failed[] = \$s['step'];
}

header('Content-Type: application/json');
echo json_encode([
    // The run only counts as a failure if a step actually broke
    'success' => empty(\$failed),
    'message' => empty(\$failed)
        ? 'All setup steps applied.'
        : 'Some steps did not apply: ' . implode(', ', \$failed) . '.',
    'steps'   => \$steps,
]);
PHP;
    }
}
