<?php
/**
 * cPanel UAPI / API2 Helper Class
 *
 * Runs against a one-time cPanel session created by WHM
 * (see WHMAPI::createUserSession).
 *
 * API Documentation: https://api.docs.cpanel.net/cpanel/introduction/
 */

class CpanelAPI {

    private $sessionUrl;
    private $username;
    private $baseUrl = '';   // https://host:2083/cpsessNNNN
    private $cookieJar;
    private $timeout = 90;
    private $connected = false;

    /**
     * @param string $sessionUrl One-time cPanel login URL from WHM
     * @param string $username   cPanel account name (needed by API2)
     */
    public function __construct($sessionUrl, $username) {
        $this->sessionUrl = $sessionUrl;
        $this->username   = $username;
        $this->cookieJar  = tempnam(sys_get_temp_dir(), 'cpa');
    }

    public function __destruct() {
        if (!empty($this->cookieJar) && file_exists($this->cookieJar)) {
            @unlink($this->cookieJar);
        }
    }

    /**
     * Follow the one-time login URL so the session cookie is stored.
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

        $this->baseUrl   = $m[1];
        $this->connected = true;

        return ['success' => true, 'message' => ''];
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
     * Call a UAPI function.
     * @param string     $module e.g. Fileman
     * @param string     $func   e.g. get_file_content
     * @param array      $args   Query arguments
     * @param array|null $post   POST body, when the call needs one
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function uapi($module, $func, $args = [], $post = null) {
        $connect = $this->connect();
        if (!$connect['success']) {
            return ['success' => false, 'message' => $connect['message'], 'data' => null];
        }

        $url = $this->baseUrl . '/execute/' . $module . '/' . $func;
        if (!empty($args)) {
            $url .= '?' . http_build_query($args);
        }

        $res     = $this->request($url, $post);
        $decoded = json_decode($res['body'], true);

        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Invalid cPanel response (HTTP ' . $res['httpCode'] . ').', 'data' => null];
        }

        $ok      = isset($decoded['status']) && (int)$decoded['status'] === 1;
        $message = '';

        if (!$ok && !empty($decoded['errors'])) {
            $errors  = is_array($decoded['errors']) ? $decoded['errors'] : [$decoded['errors']];
            $message = trim(implode(' ', $errors));
        }

        return [
            'success' => $ok,
            'message' => $message,
            'data'    => isset($decoded['data']) ? $decoded['data'] : null,
        ];
    }

    /**
     * Call an API2 function. Some file operations only exist here.
     * @param string $module
     * @param string $func
     * @param array  $args
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function api2($module, $func, $args = []) {
        $connect = $this->connect();
        if (!$connect['success']) {
            return ['success' => false, 'message' => $connect['message'], 'data' => null];
        }

        $query = array_merge([
            'cpanel_jsonapi_user'       => $this->username,
            'cpanel_jsonapi_apiversion' => 2,
            'cpanel_jsonapi_module'     => $module,
            'cpanel_jsonapi_func'       => $func,
        ], $args);

        $res     = $this->request($this->baseUrl . '/json-api/cpanel?' . http_build_query($query));
        $decoded = json_decode($res['body'], true);

        if (!is_array($decoded) || !isset($decoded['cpanelresult'])) {
            return ['success' => false, 'message' => 'Invalid cPanel API2 response (HTTP ' . $res['httpCode'] . ').', 'data' => null];
        }

        $result = $decoded['cpanelresult'];

        if (!empty($result['error'])) {
            return ['success' => false, 'message' => $result['error'], 'data' => null];
        }

        return [
            'success' => true,
            'message' => '',
            'data'    => isset($result['data']) ? $result['data'] : null,
        ];
    }

    /**
     * Read a file from the account.
     * @param string $dir  Absolute directory
     * @param string $file File name
     * @return array ['success' => bool, 'message' => string, 'content' => string]
     */
    public function readFile($dir, $file) {
        $res = $this->uapi('Fileman', 'get_file_content', ['dir' => $dir, 'file' => $file]);

        return [
            'success' => $res['success'],
            'message' => $res['message'],
            'content' => isset($res['data']['content']) ? $res['data']['content'] : '',
        ];
    }

    /**
     * Write a file into the account, overwriting any existing one.
     * @param string $dir
     * @param string $file
     * @param string $content
     * @return array ['success' => bool, 'message' => string]
     */
    public function writeFile($dir, $file, $content) {
        $res = $this->uapi('Fileman', 'save_file_content', [], [
            'dir'     => $dir,
            'file'    => $file,
            'content' => $content,
        ]);

        return ['success' => $res['success'], 'message' => $res['message']];
    }

    /**
     * List the entries directly inside a directory.
     * @param string $dir Absolute directory
     * @return array ['success' => bool, 'message' => string, 'files' => string[]] file names only
     */
    public function listFiles($dir) {
        $res = $this->uapi('Fileman', 'list_files', ['dir' => $dir, 'include_mime' => 0]);

        $files = [];
        if ($res['success'] && is_array($res['data'])) {
            foreach ($res['data'] as $entry) {
                if (isset($entry['file']) && isset($entry['type']) && $entry['type'] === 'file') {
                    $files[] = $entry['file'];
                }
            }
        }

        return ['success' => $res['success'], 'message' => $res['message'], 'files' => $files];
    }

    /**
     * Delete a file. Only API2 exposes this operation.
     * @param string $path Absolute path
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteFile($path) {
        $res = $this->api2('Fileman', 'fileop', [
            'op'           => 'unlink',
            'sourcefiles'  => $path,
            'doubledecode' => 0,
        ]);

        return ['success' => $res['success'], 'message' => $res['message']];
    }

    /**
     * Create a mailbox, or reset the password of one that already exists.
     *
     * cPanel refuses a second mailbox on the same address, so an address
     * that is already there has its password set instead. The result says
     * which of the two happened.
     *
     * @param string $email   Full address, e.g. info@example.com
     * @param string $password
     * @param int    $quotaMb Mailbox size in MB, 0 for unlimited
     * @return array ['success' => bool, 'message' => string, 'created' => bool]
     */
    public function addEmailAccount($email, $password, $quotaMb = 1024) {
        $result = ['success' => false, 'message' => '', 'created' => false];

        $parts = explode('@', $email, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            $result['message'] = 'Not a full email address: ' . $email;
            return $result;
        }

        list($user, $domain) = $parts;

        $add = $this->uapi('Email', 'add_pop', [], [
            'email'    => $user,
            'password' => $password,
            'quota'    => (int) $quotaMb,
            'domain'   => $domain,
            // Without this cPanel sends the mailbox owner a welcome message
            'skip_update_db' => 0,
        ]);

        if ($add['success']) {
            $result['success'] = true;
            $result['created'] = true;
            $result['message'] = 'Mailbox created.';
            return $result;
        }

        // "already exists" is not a failure here: the address is wanted, and
        // it is there. Its password is brought in line instead.
        if (stripos($add['message'], 'already exists') === false) {
            $result['message'] = $add['message'] !== '' ? $add['message'] : 'The mailbox could not be created.';
            return $result;
        }

        $passwd = $this->uapi('Email', 'passwd_pop', [], [
            'email'    => $user,
            'password' => $password,
            'domain'   => $domain,
        ]);

        if (!$passwd['success']) {
            $result['message'] = 'The mailbox exists, but its password could not be set: ' . $passwd['message'];
            return $result;
        }

        $result['success'] = true;
        $result['message'] = 'Mailbox already existed; its password was reset.';

        return $result;
    }

    /**
     * The mailboxes on this account.
     * @return array ['success' => bool, 'message' => string, 'accounts' => array]
     */
    public function listEmailAccounts() {
        $res = $this->uapi('Email', 'list_pops');

        return [
            'success'  => $res['success'],
            'message'  => $res['message'],
            'accounts' => is_array($res['data']) ? $res['data'] : [],
        ];
    }

    /**
     * The mail client settings for one mailbox: server names, ports and
     * whether each of them is encrypted.
     *
     * @param string $email Full address
     * @return array ['success' => bool, 'message' => string, 'settings' => array]
     */
    public function emailClientSettings($email) {
        $parts = explode('@', $email, 2);

        if (count($parts) !== 2) {
            return ['success' => false, 'message' => 'Not a full email address: ' . $email, 'settings' => []];
        }

        $res = $this->uapi('Email', 'get_client_settings', [
            'account' => $parts[0],
            'domain'  => $parts[1],
        ]);

        return [
            'success'  => $res['success'],
            'message'  => $res['message'],
            'settings' => is_array($res['data']) ? $res['data'] : [],
        ];
    }

    /**
     * The account's home directory.
     * @return string
     */
    public function homeDir() {
        return '/home/' . $this->username;
    }
}
