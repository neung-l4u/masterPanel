<?php
/*
 * Thumbnail rendering diagnostics.
 *
 * Reports why automatic thumbnail generation is or is not available on this
 * host. Intended to be opened in a browser on the server that is failing:
 *
 *     /modules/templates/models/thumbnailDiagnostics.php
 *
 * Read-only apart from an optional render test, which writes to the system
 * temp directory and cleans up after itself.
 */

session_start();
require_once __DIR__ . '/templateCatalogue.php';

header('Content-Type: text/plain; charset=utf-8');

// Same session rule as the rest of the module.
if (empty($_COOKIE['id'])) {
    http_response_code(401);
    echo "Not logged in. Open the templates page first, then reload this URL.\n";
    exit;
}

function diagLine(string $label, $value): void
{
    printf("%-28s %s\n", $label . ':', $value);
}

function diagBool(string $label, bool $ok, string $yes = 'yes', string $no = 'NO'): void
{
    diagLine($label, ($ok ? '[ OK ] ' : '[FAIL] ') . ($ok ? $yes : $no));
}

echo "Thumbnail generation diagnostics\n";
echo str_repeat('=', 60) . "\n\n";

echo "PHP\n" . str_repeat('-', 60) . "\n";
diagLine('php version', PHP_VERSION);
diagLine('os', PHP_OS_FAMILY . ' / ' . php_uname('s'));
diagLine('sapi', PHP_SAPI);

$disabled = (string)ini_get('disable_functions');
diagLine('disable_functions', $disabled === '' ? '(none)' : $disabled);

$hasProcOpen = function_exists('proc_open');
diagBool('proc_open', $hasProcOpen, 'available', 'DISABLED - cannot launch a browser');

$hasGd = function_exists('imagecreatefrompng');
diagBool('GD imagecreatefrompng', $hasGd, 'available', 'missing - screenshot cannot be resized');

echo "\nBrowser\n" . str_repeat('-', 60) . "\n";
$envPath = getenv('TPL_CHROME_PATH');
diagLine('TPL_CHROME_PATH env', $envPath === false || $envPath === '' ? '(not set)' : $envPath);

$searched = [
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
];
foreach ($searched as $candidate) {
    diagLine('  ' . $candidate, is_file($candidate) ? 'FOUND' : 'not found');
}

$browser = tplFindBrowser();
diagBool('browser resolved', $browser !== false, (string)$browser, 'none of the paths above exist');

echo "\nOverall\n" . str_repeat('-', 60) . "\n";
$canAuto = tplCanAutoThumbnail();
diagBool('auto thumbnail available', $canAuto, 'yes - the checkbox is shown', 'no - the checkbox is hidden');

echo "\nDirectories\n" . str_repeat('-', 60) . "\n";
diagLine('thumbnail dir', TPL_THUMB_DIR);
diagBool('  exists', is_dir(TPL_THUMB_DIR), 'yes', 'missing');
diagBool('  writable', is_writable(TPL_THUMB_DIR), 'yes', 'NOT writable by PHP');
diagLine('template dir', TPL_HTML_DIR);
diagBool('  exists', is_dir(TPL_HTML_DIR), 'yes', 'missing');
diagBool('  writable', is_writable(TPL_HTML_DIR), 'yes', 'NOT writable by PHP');
diagLine('temp dir', sys_get_temp_dir());
diagBool('  writable', is_writable(sys_get_temp_dir()), 'yes', 'NOT writable by PHP');

echo "\nCatalogue vs disk\n" . str_repeat('-', 60) . "\n";
$rows = tplReadCatalogue();
$missing = [];
$noThumb = [];
foreach ($rows as $row) {
    $id = isset($row['id']) ? $row['id'] : '?';
    if (empty($row['thumbnail'])) {
        $noThumb[] = $id;
        continue;
    }
    if (!is_file(TPL_THUMB_DIR . '/' . $row['thumbnail'])) {
        $missing[] = $id . '  ->  ' . $row['thumbnail'];
    }
}
diagLine('templates in catalogue', count($rows));
diagLine('no thumbnail recorded', count($noThumb) . ($noThumb ? '  (' . implode(', ', $noThumb) . ')' : ''));
diagLine('thumbnail file MISSING', count($missing));
foreach ($missing as $line) {
    echo "    " . $line . "\n";
}
if (count($missing) > 0) {
    echo "\n  These rows point at a thumbnail that is not on this server.\n";
    echo "  The card shows the placeholder icon for each of them.\n";
    echo "  Most likely the image files were not deployed. Copy\n";
    echo "  assets/img/custom-template/ from the machine that generated them,\n";
    echo "  or re-render them with the Edit button on each card.\n";
}

// Optional live render test: append ?render=1
if (!empty($_GET['render'])) {
    echo "\nRender test\n" . str_repeat('-', 60) . "\n";
    if (!$canAuto) {
        echo "Skipped: auto thumbnail is not available on this host.\n";
    } else {
        $sample = null;
        foreach ($rows as $row) {
            if (!empty($row['file']) && is_file(TPL_HTML_DIR . '/' . $row['file'])) {
                $sample = TPL_HTML_DIR . '/' . $row['file'];
                break;
            }
        }
        if ($sample === null) {
            echo "Skipped: no template file found to render.\n";
        } else {
            diagLine('source', basename($sample));
            $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tpl_diag_' . bin2hex(random_bytes(4)) . '.png';
            $started = microtime(true);
            $result = tplGenerateThumbnail($sample, $target);
            $elapsed = round(microtime(true) - $started, 1);
            diagLine('elapsed', $elapsed . 's');
            if (is_string($result) && is_file($result)) {
                $size = getimagesize($result);
                diagLine('result', 'OK  ' . $size[0] . 'x' . $size[1] . '  ' . round(filesize($result) / 1024) . ' KB');
                @unlink($result);
            } else {
                diagLine('result', 'FAILED  ' . $result);
            }
        }
    }
}

echo "\nDone. Add ?render=1 to this URL to attempt a live render.\n";
