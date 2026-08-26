<?php
/**
 * Softaculous (cPanel) API Helper Class
 *
 * Softaculous lives inside a cPanel account, so every call runs through a
 * one-time cPanel session created by WHM (see WHMAPI::createUserSession).
 *
 * API Documentation: https://www.softaculous.com/docs/api/
 */

class SoftaculousAPI {

    const SOFT_WORDPRESS = 26;

    private $sessionUrl;
    private $baseUrl = '';   // https://host:2083/cpsessNNNN
    private $cookieJar;
    private $theme = 'jupiter';
    private $timeout = 180;  // installs are slow
    private $connected = false;

    /**
     * @param string $sessionUrl One-time cPanel login URL from WHM
     */
    public function __construct($sessionUrl) {
        $this->sessionUrl = $sessionUrl;
        $this->cookieJar  = tempnam(sys_get_temp_dir(), 'sfc');
    }

    public function __destruct() {
        if (!empty($this->cookieJar) && file_exists($this->cookieJar)) {
            @unlink($this->cookieJar);
        }
    }

    /**
     * Follow the one-time login URL so the session cookie is stored,
     * then remember the cpsess base path used by every later call.
     * @return array ['success' => bool, 'message' => string]
     */
    public function connect() {
        if ($this->connected) {
            return ['success' => true, 'message' => ''];
        }

        if (empty($this->sessionUrl)) {
            return ['success' => false, 'message' => 'No cPanel session URL.'];
        }

        $res = $this->request($this->sessionUrl);

        if ($res['httpCode'] !== 200) {
            return ['success' => false, 'message' => 'cPanel login failed (HTTP ' . $res['httpCode'] . ').'];
        }

        if (!preg_match('~(https://[^/]+/cpsess\d+)~', $res['effectiveUrl'] ?: $this->sessionUrl, $m)) {
            return ['success' => false, 'message' => 'Could not determine the cPanel session path.'];
        }

        $this->baseUrl = $m[1];

        // The landing page tells us which theme directory to address
        if (preg_match('~/frontend/([^/]+)/~', $res['effectiveUrl'], $t)) {
            $this->theme = $t[1];
        }

        $this->connected = true;

        return ['success' => true, 'message' => ''];
    }

    /**
     * Endpoint for the Softaculous JSON API inside this session.
     * @param array $query
     * @return string
     */
    private function endpoint($query) {
        $query['api'] = 'json';
        return $this->baseUrl . '/frontend/' . $this->theme . '/softaculous/index.live.php?' . http_build_query($query);
    }

    /**
     * Perform an HTTP request on the cPanel session.
     * @param string     $url
     * @param array|null $post
     * @return array ['httpCode' => int, 'body' => string, 'effectiveUrl' => string, 'error' => string]
     */
    private function request($url, $post = null) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR      => $this->cookieJar,
            CURLOPT_COOKIEFILE     => $this->cookieJar,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        $body         = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error        = curl_error($ch);
        curl_close($ch);

        return [
            'httpCode'     => $httpCode,
            'body'         => $body === false ? '' : $body,
            'effectiveUrl' => $effectiveUrl,
            'error'        => $error,
        ];
    }

    /**
     * List the applications already installed in this account.
     * @param int|null $soft Limit to one software id, e.g. SOFT_WORDPRESS
     * @return array ['success' => bool, 'message' => string, 'installations' => array]
     */
    public function listInstallations($soft = null) {
        $connect = $this->connect();
        if (!$connect['success']) {
            return ['success' => false, 'message' => $connect['message'], 'installations' => []];
        }

        $res     = $this->request($this->endpoint(['act' => 'installations']));
        $decoded = json_decode($res['body'], true);

        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Invalid Softaculous response.', 'installations' => []];
        }

        $all = isset($decoded['installations']) && is_array($decoded['installations']) ? $decoded['installations'] : [];

        if ($soft !== null) {
            $all = isset($all[$soft]) ? $all[$soft] : [];
        }

        return ['success' => true, 'message' => '', 'installations' => $all];
    }

    /**
     * Install WordPress into this cPanel account.
     *
     * @param array $opts domain, directory, siteName, adminUser, adminPass,
     *                    adminEmail, dbName, protocol, language
     * @return array ['success' => bool, 'message' => string, 'adminUrl' => string, 'siteUrl' => string, 'raw' => array|null]
     */
    public function installWordPress($opts) {
        $result = [
            'success'  => false,
            'message'  => '',
            'adminUrl' => '',
            'siteUrl'  => '',
            'raw'      => null,
        ];

        $connect = $this->connect();
        if (!$connect['success']) {
            $result['message'] = $connect['message'];
            return $result;
        }

        $protocol  = isset($opts['protocol']) ? $opts['protocol'] : 'https://';
        $directory = isset($opts['directory']) ? trim($opts['directory'], '/') : '';

        $post = [
            'softsubmit'     => 1,
            'soft'           => self::SOFT_WORDPRESS,
            'softproto'      => $this->protocolId($protocol),
            'softdomain'     => $opts['domain'],
            'softdirectory'  => $directory,
            'softdb'         => $opts['dbName'],
            'admin_username' => $opts['adminUser'],
            'admin_pass'     => $opts['adminPass'],
            'admin_email'    => $opts['adminEmail'],
            'site_name'      => $opts['siteName'],
            'site_desc'      => isset($opts['siteDesc']) ? $opts['siteDesc'] : '',
            'language'       => isset($opts['language']) ? $opts['language'] : 'en',
            // Softaculous emails the customer by default; that is not wanted here
            'noemail'        => 1,
        ];

        $res     = $this->request($this->endpoint(['act' => 'software', 'soft' => self::SOFT_WORDPRESS]), $post);
        $decoded = json_decode($res['body'], true);

        if (!is_array($decoded)) {
            $result['message'] = 'Invalid Softaculous response (HTTP ' . $res['httpCode'] . ').';
            return $result;
        }

        $result['raw'] = $decoded;

        // Softaculous reports problems in `error`, which can be a string or a list
        if (!empty($decoded['error'])) {
            $errors = is_array($decoded['error']) ? $decoded['error'] : [$decoded['error']];
            $result['message'] = trim(strip_tags(implode(' ', $errors)));
            return $result;
        }

        if (empty($decoded['__settings']) && empty($decoded['done']) && empty($decoded['insid'])) {
            $result['message'] = 'Softaculous did not confirm the installation.';
            return $result;
        }

        $siteUrl = rtrim($protocol . $opts['domain'] . ($directory !== '' ? '/' . $directory : ''), '/');

        $result['success']  = true;
        $result['message']  = 'WordPress installed.';
        $result['siteUrl']  = $siteUrl;
        $result['adminUrl'] = $siteUrl . '/wp-admin/';

        return $result;
    }

    /**
     * Map a protocol string to the id Softaculous expects.
     * @param string $protocol
     * @return int
     */
    private function protocolId($protocol) {
        switch (strtolower(trim($protocol))) {
            case 'http://':      return 1;
            case 'http://www.':  return 2;
            case 'https://www.': return 4;
            default:             return 3; // https://
        }
    }

    /**
     * Build a database name that fits cPanel's limits.
     * cPanel prefixes databases with the account name, so only a short
     * suffix is passed to Softaculous.
     * @param string $seed
     * @return string
     */
    public static function dbNameFor($seed) {
        $seed = strtolower(preg_replace('/[^a-z0-9]/i', '', $seed));
        return 'wp' . substr($seed, 0, 5);
    }
}
