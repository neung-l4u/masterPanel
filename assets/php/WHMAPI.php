<?php
/**
 * WHM API v1 Helper Class
 *
 * Credentials are read per-server from the `L4UServers` table
 * (svWHMURL, svWHMUser, svWHMApiToken).
 *
 * API Documentation: https://api.docs.cpanel.net/openapi/whm/
 */

class WHMAPI {

    /** Landing page for a session that should open phpMyAdmin */
    const PMA_URI = '/3rdparty/phpMyAdmin/index.php';

    private $whmUrl;
    private $whmUser;
    private $apiToken;
    private $timeout = 60;

    /**
     * Constructor
     * @param string $whmUrl   e.g. https://host.example.com:2087/
     * @param string $whmUser  WHM (reseller) username
     * @param string $apiToken WHM API token
     */
    public function __construct($whmUrl, $whmUser, $apiToken) {
        $this->whmUrl   = rtrim(trim($whmUrl), '/');
        $this->whmUser  = trim($whmUser);
        $this->apiToken = trim($apiToken);
    }

    /**
     * Build a WHMAPI instance from a row of the L4UServers table.
     * @param object $db  Application db wrapper
     * @param int    $svID
     * @return WHMAPI|null null when the server has no WHM credentials stored
     */
    public static function fromServer($db, $svID) {
        $row = $db->query(
            'SELECT svWHMURL, svWHMUser, svWHMApiToken FROM L4UServers WHERE svID = ?;',
            $svID
        )->fetchArray();

        if (empty($row) || empty($row['svWHMURL']) || empty($row['svWHMUser']) || empty($row['svWHMApiToken'])) {
            return null;
        }

        return new self($row['svWHMURL'], $row['svWHMUser'], $row['svWHMApiToken']);
    }

    /**
     * Call a WHM API v1 function.
     * @param string $function WHM API function name, e.g. createacct
     * @param array  $params   Query parameters
     * @return array ['success' => bool, 'message' => string, 'data' => array|null, 'httpCode' => int]
     */
    public function call($function, $params = []) {
        $params['api.version'] = 1;
        $url = $this->whmUrl . '/json-api/' . $function . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: whm ' . $this->whmUser . ':' . $this->apiToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success'  => false,
                'message'  => 'cURL error: ' . $curlErr,
                'data'     => null,
                'httpCode' => $httpCode
            ];
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return [
                'success'  => false,
                'message'  => 'Invalid WHM response (HTTP ' . $httpCode . ')',
                'data'     => null,
                'httpCode' => $httpCode
            ];
        }

        // WHM API v1 wraps the status in metadata.result (1 = ok)
        $result = isset($decoded['metadata']['result']) ? (int)$decoded['metadata']['result'] : null;
        $reason = isset($decoded['metadata']['reason']) ? $decoded['metadata']['reason'] : '';

        if ($result === null) {
            // Some errors come back as a bare {"error": "..."} payload
            $reason = isset($decoded['error']) ? $decoded['error'] : 'Unknown WHM response';
            return [
                'success'  => false,
                'message'  => $reason,
                'data'     => $decoded,
                'httpCode' => $httpCode
            ];
        }

        return [
            'success'  => $result === 1,
            'message'  => $reason,
            'data'     => $decoded,
            'httpCode' => $httpCode
        ];
    }

    /**
     * Create a cPanel account.
     *
     * When $plan is empty the account is created with custom resource limits
     * (quota in MB) instead of an existing WHM package.
     *
     * @param array $opts domain, username, password, contactemail, quota, plan
     * @return array Same shape as call()
     */
    public function createAccount($opts) {
        $params = [
            'username'     => $opts['username'],
            'domain'       => $opts['domain'],
            'password'     => $opts['password'],
            'contactemail' => isset($opts['contactemail']) ? $opts['contactemail'] : '',
        ];

        if (!empty($opts['plan'])) {
            $params['plan'] = $opts['plan'];
        } else {
            // Custom resource options instead of a package
            $params['quota']       = isset($opts['quota']) ? (int)$opts['quota'] : 1000; // MB
            $params['bwlimit']     = isset($opts['bwlimit']) ? $opts['bwlimit'] : 'unlimited';
            $params['maxsql']      = 'unlimited';
            $params['maxpop']      = 'unlimited';
            $params['maxftp']      = 'unlimited';
            $params['maxsub']      = 'unlimited';
            $params['maxpark']     = 'unlimited';
            $params['maxaddon']    = 'unlimited';
            $params['hasshell']    = 0;
            $params['cgi']         = 1;
        }

        return $this->call('createacct', $params);
    }

    /**
     * List the WHM packages available to this reseller.
     * @return array Same shape as call()
     */
    public function listPackages() {
        return $this->call('listpkgs');
    }

    /**
     * Create a one-time login URL into a cPanel account.
     *
     * The returned URL is single use and expires quickly, so it must be opened
     * straight away rather than stored.
     *
     * @param string $username cPanel account username
     * @param string $service  cpaneld (cPanel) or webmaild (Webmail)
     * @param string $gotoUri  Optional page to land on, e.g. PMA_URI
     * @return array Same shape as call(), plus 'url' on success
     */
    public function createUserSession($username, $service = 'cpaneld', $gotoUri = '') {
        $call = $this->call('create_user_session', [
            'user'    => $username,
            'service' => $service,
        ]);

        $call['url'] = isset($call['data']['data']['url']) ? $call['data']['data']['url'] : '';

        if ($call['success'] && empty($call['url'])) {
            $call['success'] = false;
            $call['message'] = 'WHM returned no session URL.';
        }

        // cPanel redirects to goto_uri once the session is established
        if ($call['success'] && $gotoUri !== '') {
            $call['url'] .= '&goto_uri=' . rawurlencode($gotoUri);
        }

        return $call;
    }

    /**
     * Derive a valid cPanel username from a domain name.
     * cPanel usernames: max 16 chars, start with a letter, lowercase alphanumeric.
     * @param string $domain
     * @return string
     */
    public static function usernameFromDomain($domain) {
        $domain = preg_replace('#^https?://#i', '', trim($domain));
        $domain = preg_replace('#^www\.#i', '', $domain);
        $domain = strtok($domain, '/');

        $parts = explode('.', $domain);
        $base  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $parts[0]));

        if ($base === '' || !ctype_alpha(substr($base, 0, 1))) {
            $base = 'l4u' . $base;
        }

        return substr($base, 0, 16);
    }

    /**
     * Normalise a domain for WHM (strip scheme, www and any path).
     * @param string $domain
     * @return string
     */
    public static function normaliseDomain($domain) {
        $domain = preg_replace('#^https?://#i', '', trim($domain));
        $domain = preg_replace('#^www\.#i', '', $domain);
        $domain = strtok($domain, '/');

        return strtolower(rtrim($domain, '.'));
    }
}
