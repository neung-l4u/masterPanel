<?php
/**
 * Domain change helper.
 *
 * Changing a site's domain touches two places:
 *   1. The cPanel account's primary domain, via WHM modifyacct.
 *   2. WordPress's `siteurl` and `home` options, which live in the site
 *      database on the server's own MySQL (bound to localhost, so it cannot
 *      be reached from here).
 *
 * For (2) a short-lived PHP helper is uploaded into the account's document
 * root, called once over HTTPS with a one-time token, and deleted again.
 * Nothing is left behind on the server.
 */

class DomainUpdater {

    const HELPER_PREFIX = 'l4u-domain-';

    private $db;
    private $whm;
    private $username;

    /**
     * @param object $db
     * @param WHMAPI $whm
     * @param string $username cPanel account name
     */
    public function __construct($db, $whm, $username) {
        $this->db       = $db;
        $this->whm      = $whm;
        $this->username = $username;
    }

    /**
     * Point the cPanel account at a new primary domain.
     * @param string $newDomain
     * @return array WHMAPI::call() shape
     */
    public function updateCpanelDomain($newDomain) {
        return $this->whm->call('modifyacct', [
            'user'   => $this->username,
            'domain' => $newDomain,
        ]);
    }

    /**
     * Rewrite WordPress's siteurl and home options.
     *
     * @param string $oldDomain Domain currently serving the site
     * @param string $newDomain Domain to switch to
     * @param string $docRoot   Document root, defaults to public_html
     * @return array ['success' => bool, 'message' => string, 'siteurl' => string, 'home' => string]
     */
    public function updateWordPressUrls($oldDomain, $newDomain, $docRoot = 'public_html') {
        $result = ['success' => false, 'message' => '', 'siteurl' => '', 'home' => ''];

        $session = $this->whm->createUserSession($this->username);
        if (!$session['success']) {
            $result['message'] = 'Could not open a cPanel session: ' . $session['message'];
            return $result;
        }

        $cpanel = new CpanelAPI($session['url'], $this->username);
        $home   = $cpanel->homeDir();
        $root   = $home . '/' . trim($docRoot, '/');

        // wp-config.php carries the credentials WordPress itself uses
        $config = $cpanel->readFile($root, 'wp-config.php');
        if (!$config['success'] || $config['content'] === '') {
            $result['message'] = 'Could not read wp-config.php: ' . $config['message'];
            return $result;
        }

        $creds = self::parseWpConfig($config['content']);
        if (empty($creds['DB_NAME']) || empty($creds['DB_USER'])) {
            $result['message'] = 'wp-config.php did not contain usable database credentials.';
            return $result;
        }

        // The helper runs on the server, where MySQL is reachable
        $token      = bin2hex(random_bytes(16));
        $helperName = self::HELPER_PREFIX . bin2hex(random_bytes(8)) . '.php';
        $helper     = self::helperSource($token);

        $write = $cpanel->writeFile($root, $helperName, $helper);
        if (!$write['success']) {
            $result['message'] = 'Could not upload the helper script: ' . $write['message'];
            return $result;
        }

        $helperPath = $root . '/' . $helperName;

        // Reach the helper on the domain that still serves the site
        $call = self::callHelper($oldDomain, $helperName, [
            'token'     => $token,
            'db_name'   => $creds['DB_NAME'],
            'db_user'   => $creds['DB_USER'],
            'db_pass'   => $creds['DB_PASSWORD'],
            'db_host'   => !empty($creds['DB_HOST']) ? $creds['DB_HOST'] : 'localhost',
            'prefix'    => !empty($creds['table_prefix']) ? $creds['table_prefix'] : 'wp_',
            'new_url'   => 'https://' . $newDomain,
        ]);

        // Always remove the helper, whatever the outcome
        $delete = $cpanel->deleteFile($helperPath);

        if (!$call['success']) {
            $result['message'] = $call['message'];
            if (!$delete['success']) {
                $result['message'] .= ' (the helper script could not be removed: ' . $helperPath . ')';
            }
            return $result;
        }

        $result['success'] = true;
        $result['message'] = 'WordPress URLs updated.';
        $result['siteurl'] = $call['siteurl'];
        $result['home']    = $call['home'];

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
     * @return array ['success' => bool, 'message' => string, 'siteurl' => string, 'home' => string]
     */
    private static function callHelper($domain, $helperName, $post) {
        $out = ['success' => false, 'message' => '', 'siteurl' => '', 'home' => ''];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://' . $domain . '/' . $helperName,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 60,
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

        if (empty($decoded['success'])) {
            $out['message'] = !empty($decoded['message']) ? $decoded['message'] : 'The helper script reported a failure.';
            return $out;
        }

        $out['success'] = true;
        $out['siteurl'] = isset($decoded['siteurl']) ? $decoded['siteurl'] : '';
        $out['home']    = isset($decoded['home']) ? $decoded['home'] : '';

        return $out;
    }

    /**
     * Pull the database constants out of a wp-config.php.
     * @param string $content
     * @return array
     */
    public static function parseWpConfig($content) {
        $creds = [];

        foreach (['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'] as $constant) {
            if (preg_match('/define\(\s*[\'"]' . $constant . '[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/s', $content, $m)) {
                $creds[$constant] = $m[1];
            }
        }

        if (preg_match('/\$table_prefix\s*=\s*[\'"](.*?)[\'"]/', $content, $m)) {
            $creds['table_prefix'] = $m[1];
        }

        return $creds;
    }

    /**
     * Source of the throwaway helper.
     *
     * It answers a single POST carrying the matching token, updates the two
     * options and reports what it wrote. It holds no credentials of its own -
     * they arrive with the request - so a leftover copy cannot be used to read
     * the database.
     *
     * @param string $token
     * @return string
     */
    private static function helperSource($token) {
        $token = var_export($token, true);

        return <<<PHP
<?php
// Temporary helper written by masterPanel. Safe to delete.
header('Content-Type: application/json');

\$expected = {$token};

function fail(\$message) {
    echo json_encode(['success' => false, 'message' => \$message]);
    exit;
}

if (empty(\$_POST['token']) || !hash_equals(\$expected, \$_POST['token'])) {
    http_response_code(403);
    fail('Invalid token.');
}

\$newUrl = isset(\$_POST['new_url']) ? rtrim(\$_POST['new_url'], '/') : '';
if (\$newUrl === '') {
    fail('No new URL supplied.');
}

\$prefix = isset(\$_POST['prefix']) ? \$_POST['prefix'] : 'wp_';
if (!preg_match('/^[A-Za-z0-9_]+\$/', \$prefix)) {
    fail('Unexpected table prefix.');
}

\$mysqli = @new mysqli(\$_POST['db_host'], \$_POST['db_user'], \$_POST['db_pass'], \$_POST['db_name']);
if (\$mysqli->connect_error) {
    fail('Database connection failed: ' . \$mysqli->connect_error);
}

\$table = \$prefix . 'options';
\$stmt  = \$mysqli->prepare("UPDATE `{\$table}` SET option_value = ? WHERE option_name IN ('siteurl','home')");
if (!\$stmt) {
    fail('Could not prepare the update: ' . \$mysqli->error);
}

\$stmt->bind_param('s', \$newUrl);
if (!\$stmt->execute()) {
    fail('Could not update the options: ' . \$stmt->error);
}
\$stmt->close();

// Read the values back so the caller sees what is actually stored
\$siteurl = '';
\$home    = '';
\$res = \$mysqli->query("SELECT option_name, option_value FROM `{\$table}` WHERE option_name IN ('siteurl','home')");
if (\$res) {
    while (\$row = \$res->fetch_assoc()) {
        if (\$row['option_name'] === 'siteurl') \$siteurl = \$row['option_value'];
        if (\$row['option_name'] === 'home')    \$home    = \$row['option_value'];
    }
}

\$mysqli->close();

echo json_encode([
    'success' => true,
    'siteurl' => \$siteurl,
    'home'    => \$home,
]);
PHP;
    }
}
