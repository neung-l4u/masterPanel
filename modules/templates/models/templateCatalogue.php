<?php
/*
 * Shared helpers for the customized-template catalogue.
 *
 * The catalogue lives in data/templates.json. Uploaded template files live in
 * views/customized-template/ and their thumbnails in
 * assets/img/custom-template/.
 *
 * Deletes are soft: the row keeps its files on disk and is flagged
 * active = false so it can be restored.
 */

define('TPL_CATALOGUE_FILE', __DIR__ . '/../data/templates.json');
define('TPL_HTML_DIR', __DIR__ . '/../views/customized-template');
define('TPL_THUMB_DIR', __DIR__ . '/../assets/img/custom-template');

// Only these extensions may be uploaded.
define('TPL_HTML_EXT', ['html', 'htm']);
define('TPL_THUMB_EXT', ['png', 'jpg', 'jpeg', 'webp']);

define('TPL_MAX_HTML_BYTES', 5242880);   // 5 MB
define('TPL_MAX_THUMB_BYTES', 5242880);  // 5 MB

/*
 * Auto thumbnail settings. The template is rendered in a headless browser at
 * TPL_SHOT_WIDTH x TPL_SHOT_HEIGHT, then downscaled to TPL_THUMB_WIDTH so the
 * stored file stays small. The card crops to the top of the image, so a tall
 * viewport only wastes bytes.
 */
define('TPL_SHOT_WIDTH', 1280);
define('TPL_SHOT_HEIGHT', 800);
define('TPL_THUMB_WIDTH', 640);
define('TPL_SHOT_TIMEOUT', 25);     // seconds before the browser is abandoned
/*
 * How long the page may run before the screenshot is taken. Templates that
 * animate in or build content with JavaScript need longer; raise this with
 * the TPL_SHOT_SETTLE_MS environment variable when a thumbnail comes out
 * half-empty.
 */
define(
    'TPL_SHOT_SETTLE_MS',
    (int)(getenv('TPL_SHOT_SETTLE_MS') !== false ? getenv('TPL_SHOT_SETTLE_MS') : 1200)
);

function tplCategories(): array
{
    return [
        'restaurant' => 'Restaurant',
        'massage'    => 'Massage',
    ];
}

/*
 * Reads the catalogue. Returns a list of rows; a missing or unreadable file
 * yields an empty catalogue rather than a fatal error so the page still renders.
 */
function tplReadCatalogue(): array
{
    if (!is_file(TPL_CATALOGUE_FILE)) {
        return [];
    }
    $raw = file_get_contents(TPL_CATALOGUE_FILE);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['templates']) || !is_array($decoded['templates'])) {
        return [];
    }
    return $decoded['templates'];
}

/*
 * Writes the catalogue atomically: a temp file in the same directory is
 * renamed over the target, so a crash mid-write cannot leave truncated JSON.
 */
function tplWriteCatalogue(array $templates): bool
{
    $dir = dirname(TPL_CATALOGUE_FILE);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }
    $payload = json_encode(
        ['templates' => array_values($templates)],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
    if ($payload === false) {
        return false;
    }
    $tmp = tempnam($dir, 'tpl');
    if ($tmp === false) {
        return false;
    }
    if (file_put_contents($tmp, $payload, LOCK_EX) === false) {
        @unlink($tmp);
        return false;
    }
    if (!@rename($tmp, TPL_CATALOGUE_FILE)) {
        @unlink($tmp);
        return false;
    }
    @chmod(TPL_CATALOGUE_FILE, 0664);
    return true;
}

function tplFindIndexById(array $templates, string $id)
{
    foreach ($templates as $i => $row) {
        if (isset($row['id']) && (string)$row['id'] === $id) {
            return $i;
        }
    }
    return false;
}

/*
 * Template ids become part of a filename, so restrict them to characters that
 * cannot traverse directories or hide a second extension.
 */
function tplValidateId(string $id): bool
{
    return $id !== '' && strlen($id) <= 40 && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $id) === 1
        && strpos($id, '..') === false;
}

// A display name is free text, so cap it rather than restrict the characters.
define('TPL_MAX_NAME_LEN', 80);

/*
 * Normalises a display name. The name is shown on the card and never used as
 * a file name, so any character is allowed except control characters, which
 * are stripped along with excess whitespace. Returns '' when nothing is left,
 * which means "fall back to Template <id>".
 */
function tplCleanName(string $name): string
{
    // Strip control characters, including the newlines a paste can carry.
    $clean = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $name);
    if ($clean === null) {
        // Invalid UTF-8: fall back to a byte-safe strip.
        $clean = preg_replace('/[\x00-\x1F\x7F]/', ' ', $name);
    }
    $clean = trim(preg_replace('/\s+/u', ' ', $clean));
    if ($clean === '') {
        return '';
    }
    if (function_exists('mb_substr')) {
        return mb_substr($clean, 0, TPL_MAX_NAME_LEN, 'UTF-8');
    }
    return substr($clean, 0, TPL_MAX_NAME_LEN);
}

/*
 * Derives a template id from an uploaded file name, for bulk uploads where
 * the user does not type an id per file.
 *
 *   Template20.html -> 20        spa-luxe.html -> spa-luxe
 *   massage 2.html  -> massage_2
 *
 * Only the base name is used, so a path in the name cannot escape the
 * upload directory. Returns false when nothing usable is left.
 */
function tplIdFromFilename(string $filename)
{
    $base = basename(str_replace('\\', '/', $filename));
    $base = preg_replace('/\.[A-Za-z0-9]+$/', '', $base);
    $base = trim($base);

    // "Template20" is the house naming convention; keep just the number part.
    if (preg_match('/^Template[\s_-]*(.+)$/i', $base, $matches)) {
        $base = trim($matches[1]);
    }

    $base = str_replace(' ', '_', $base);
    // Drop anything the id rules would reject rather than failing outright.
    $base = preg_replace('/[^A-Za-z0-9._-]/', '', $base);
    $base = ltrim($base, '._-');
    $base = substr($base, 0, 40);

    return tplValidateId($base) ? $base : false;
}

function tplSafeExtension(string $filename, array $allowed)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed, true) ? $ext : false;
}

/*
 * Rejects an uploaded HTML file that is not actually text, and refuses any
 * payload carrying a PHP open tag so a mislabelled script cannot be stored.
 */
function tplHtmlUploadError(array $file)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed (error code ' . (isset($file['error']) ? $file['error'] : 'unknown') . ').';
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'Upload failed: not an uploaded file.';
    }
    if ($file['size'] <= 0 || $file['size'] > TPL_MAX_HTML_BYTES) {
        return 'HTML file must be between 1 byte and 5 MB.';
    }
    if (tplSafeExtension($file['name'], TPL_HTML_EXT) === false) {
        return 'Only .html or .htm files are allowed.';
    }
    $contents = file_get_contents($file['tmp_name']);
    if ($contents === false) {
        return 'Could not read the uploaded file.';
    }
    if (stripos($contents, '<?php') !== false || stripos($contents, '<?=') !== false) {
        return 'The file contains PHP code and was rejected.';
    }
    return null;
}

function tplThumbUploadError(array $file)
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'Thumbnail upload failed (error code ' . (isset($file['error']) ? $file['error'] : 'unknown') . ').';
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'Thumbnail upload failed: not an uploaded file.';
    }
    if ($file['size'] <= 0 || $file['size'] > TPL_MAX_THUMB_BYTES) {
        return 'Thumbnail must be between 1 byte and 5 MB.';
    }
    if (tplSafeExtension($file['name'], TPL_THUMB_EXT) === false) {
        return 'Thumbnail must be a .png, .jpg, .jpeg or .webp image.';
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return 'Thumbnail is not a valid image.';
    }
    return null;
}

/*
 * Guards the upload directory so Apache cannot execute anything stored there,
 * even if a future change lets a non-HTML file through.
 */
function tplEnsureUploadGuard(): void
{
    $guard = TPL_HTML_DIR . '/.htaccess';
    if (is_file($guard)) {
        return;
    }
    /*
     * php_admin_flag / php_admin_value are server-config only and make Apache
     * return HTTP 500 from a .htaccess file, so only .htaccess-legal
     * directives are used here. RemoveHandler/AddType strips any scripting
     * handler so a script is served as plain text instead of being run.
     */
    $rules = "# Generated by the templates module: never execute files in this directory.\n"
        . "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phps .cgi .pl .py .asp .aspx .jsp .shtml\n"
        . "AddType text/plain .php .phtml .php3 .php4 .php5 .php7 .php8 .phps .cgi .pl .py .asp .aspx .jsp .shtml\n"
        . "Options -ExecCGI -Includes\n"
        . "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n"
        . "<IfModule mod_php7.c>\n    php_flag engine off\n</IfModule>\n"
        . "<IfModule mod_php8.c>\n    php_flag engine off\n</IfModule>\n";
    @file_put_contents($guard, $rules);
}

/*
 * Locates a Chrome/Edge binary for rendering thumbnails. Set the
 * TPL_CHROME_PATH environment variable to override the search.
 */
function tplFindBrowser()
{
    $configured = getenv('TPL_CHROME_PATH');
    if (!empty($configured) && is_file($configured)) {
        return $configured;
    }
    $candidates = [
        'C:\Program Files\Google\Chrome\Application\chrome.exe',
        'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
        'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
        'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    return false;
}

/*
 * True when a thumbnail can be rendered on this host. The UI uses this to
 * decide whether to offer the auto-generate option.
 */
function tplCanAutoThumbnail(): bool
{
    return tplAutoThumbnailBlocker() === null;
}

/*
 * Explains why rendering is unavailable, or null when it works. The UI shows
 * this instead of silently hiding the buttons, so a server that cannot render
 * says so rather than looking like the feature was never deployed.
 */
function tplAutoThumbnailBlocker()
{
    if (!function_exists('proc_open')) {
        return 'PHP cannot start programs here (proc_open is disabled), so a browser cannot be launched.';
    }
    if (!function_exists('imagecreatefrompng')) {
        return 'The GD extension is missing, so a screenshot cannot be processed.';
    }
    if (tplFindBrowser() === false) {
        return 'No Chrome or Edge installation was found on this server.';
    }
    return null;
}

/*
 * Renders a local HTML file to a PNG thumbnail using headless Chrome.
 *
 * $htmlPath is a path on disk, NOT a URL: the browser loads it over file://
 * so the template never has to be reachable over HTTP. Returns the written
 * path on success, or an error string on failure.
 *
 * Chrome is given a throwaway profile directory and no network access flags
 * beyond the defaults; it is killed if it exceeds TPL_SHOT_TIMEOUT.
 */
function tplGenerateThumbnail(string $htmlPath, string $destPath)
{
    if (!is_file($htmlPath)) {
        return 'Template file not found for rendering.';
    }
    $browser = tplFindBrowser();
    if ($browser === false) {
        return 'No Chrome or Edge installation was found for rendering.';
    }
    if (!function_exists('proc_open')) {
        return 'proc_open is disabled, so a thumbnail cannot be rendered.';
    }

    $realHtml = realpath($htmlPath);
    if ($realHtml === false) {
        return 'Template file could not be resolved.';
    }

    // Chrome needs a writable profile dir and absolute output path.
    $workDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tplshot_' . bin2hex(random_bytes(6));
    if (!mkdir($workDir, 0700, true) && !is_dir($workDir)) {
        return 'Could not create a temporary directory for rendering.';
    }
    $shotPath = $workDir . DIRECTORY_SEPARATOR . 'shot.png';

    /*
     * Many templates hide content until it scrolls into view, using
     * IntersectionObserver with opacity:0. That never fires in a headless
     * screenshot, so the thumbnail comes out half empty. Render a patched
     * copy with animations disabled and hidden content forced visible.
     *
     * The copy sits next to the original so relative assets still resolve.
     */
    $renderPath = $realHtml;
    $patchedCopy = null;
    $sourceHtml = @file_get_contents($realHtml);
    if ($sourceHtml !== false && stripos($sourceHtml, '</head>') !== false) {
        /*
         * Reveal-on-scroll markup varies wildly (.reveal, .rv, [data-aos], ...),
         * so the settled state is applied with a script rather than guessed
         * from class names: only elements that are fully transparent or
         * clipped away are revealed. A blanket "opacity:1" would also expose
         * overlays and lightboxes that are meant to stay hidden.
         */
        $override = '<style id="tpl-shot-override">'
            . '*,*::before,*::after{'
            . 'animation-delay:0s !important;animation-duration:0s !important;'
            . 'transition-delay:0s !important;transition-duration:0s !important;'
            . '}'
            . '</style>'
            . '<script id="tpl-shot-reveal">'
            . 'window.addEventListener("load",function(){'
            . 'try{'
            . 'var all=document.body.querySelectorAll("*");'
            . 'for(var i=0;i<all.length;i++){'
            . 'var el=all[i],cs=getComputedStyle(el);'
            // Skip anything deliberately removed from the layout.
            . 'if(cs.display==="none"||cs.visibility==="hidden"){continue;}'
            // Skip things that cover the viewport: overlays, not content.
            . 'var r=el.getBoundingClientRect();'
            . 'if(r.width>=innerWidth*0.98&&r.height>=innerHeight*0.98){continue;}'
            /*
             * Reveal-on-scroll elements clip themselves away, usually with
             * inset(... 100%). Elements that are merely opacity:0 are often
             * meant to stay hidden (sticky-nav logos, hover peeks), so they
             * are only revealed when they also sit inside a reveal-looking
             * container.
             */
            . 'var clipped=cs.clipPath&&cs.clipPath.indexOf("100%")!==-1;'
            . 'var named=/(reveal|rv|fade|animate|aos|inview|in-view)/i.test(el.className||"");'
            . 'var hidden=parseFloat(cs.opacity)<0.05&&named;'
            . 'if(hidden||clipped){'
            . 'el.style.setProperty("opacity","1","important");'
            . 'el.style.setProperty("clip-path","none","important");'
            . 'el.style.setProperty("transform","none","important");'
            . '}}}catch(e){}'
            . '});'
            . '</script>';
        $patched = preg_replace('/<\/head>/i', $override . '</head>', $sourceHtml, 1);
        if ($patched !== null && $patched !== $sourceHtml) {
            $patchedCopy = dirname($realHtml) . DIRECTORY_SEPARATOR
                . '.tplshot_' . bin2hex(random_bytes(5)) . '.html';
            if (@file_put_contents($patchedCopy, $patched) !== false) {
                $renderPath = $patchedCopy;
            } else {
                $patchedCopy = null;
            }
        }
    }

    $fileUrl = 'file:///' . str_replace('\\', '/', $renderPath);

    $args = [
        $browser,
        '--headless',
        '--disable-gpu',
        '--no-sandbox',
        '--no-first-run',
        '--no-default-browser-check',
        '--disable-extensions',
        '--disable-sync',
        '--hide-scrollbars',
        '--force-device-scale-factor=1',
        '--user-data-dir=' . $workDir,
        '--virtual-time-budget=' . TPL_SHOT_SETTLE_MS,
        '--screenshot=' . $shotPath,
        '--window-size=' . TPL_SHOT_WIDTH . ',' . TPL_SHOT_HEIGHT,
        $fileUrl,
    ];

    $command = '';
    foreach ($args as $arg) {
        $command .= escapeshellarg($arg) . ' ';
    }

    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open($command, $descriptors, $pipes, $workDir);
    if (!is_resource($process)) {
        tplRemoveDirectory($workDir);
        if ($patchedCopy !== null) { @unlink($patchedCopy); $patchedCopy = null; }
        return 'Could not start the browser for rendering.';
    }
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    // Poll so a hung browser cannot block the request forever.
    $deadline = microtime(true) + TPL_SHOT_TIMEOUT;
    do {
        $status = proc_get_status($process);
        if (!$status['running']) {
            break;
        }
        usleep(200000);
    } while (microtime(true) < $deadline);

    $status = proc_get_status($process);
    if ($status['running']) {
        proc_terminate($process, 9);
    }
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    proc_close($process);

    if (!is_file($shotPath)) {
        tplRemoveDirectory($workDir);
        if ($patchedCopy !== null) { @unlink($patchedCopy); $patchedCopy = null; }
        return 'The browser did not produce a screenshot (the template may be too slow to load).';
    }

    $resized = tplResizePng($shotPath, $destPath, TPL_THUMB_WIDTH);
    tplRemoveDirectory($workDir);
    if ($patchedCopy !== null) {
        @unlink($patchedCopy);
        $patchedCopy = null;
    }
    if ($resized !== true) {
        return is_string($resized) ? $resized : 'Could not process the rendered screenshot.';
    }
    return $destPath;
}

/*
 * Downscales a PNG to $targetWidth, preserving aspect ratio and alpha.
 * Returns true, or an error string.
 */
function tplResizePng(string $src, string $dest, int $targetWidth)
{
    if (!function_exists('imagecreatefrompng')) {
        // No GD: keep the full-size screenshot rather than failing outright.
        return copy($src, $dest) ? true : 'Could not save the screenshot.';
    }
    $info = @getimagesize($src);
    if ($info === false) {
        return 'The rendered screenshot was not a readable image.';
    }
    $source = @imagecreatefrompng($src);
    if ($source === false) {
        return 'The rendered screenshot could not be opened.';
    }
    $srcW = imagesx($source);
    $srcH = imagesy($source);
    if ($srcW <= $targetWidth) {
        imagedestroy($source);
        return copy($src, $dest) ? true : 'Could not save the screenshot.';
    }
    $destW = $targetWidth;
    $destH = (int)round($srcH * ($targetWidth / $srcW));
    $canvas = imagecreatetruecolor($destW, $destH);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $destW, $destH, $srcW, $srcH);
    // Suppressed: a write failure is reported as a string, and a raw warning
    // would corrupt the JSON response this runs inside.
    $ok = @imagepng($canvas, $dest, 8);
    imagedestroy($canvas);
    imagedestroy($source);
    return $ok ? true : 'Could not write the thumbnail file.';
}

function tplRemoveDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = @scandir($dir);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            tplRemoveDirectory($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}
