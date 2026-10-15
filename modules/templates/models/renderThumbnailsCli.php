<?php
/*
 * Renders template thumbnails from the command line.
 *
 * For hosts that cannot render on their own (no Chrome, or proc_open
 * disabled): run this on a machine that has Chrome, then upload the
 * generated PNGs to the server's assets/img/custom-template/ directory.
 *
 * The server's catalogue is NOT modified by this script and does not need to
 * be. The view falls back to "thumbnail<id> (Custom).png" when a row has no
 * thumbnail recorded, so uploading the image alone is enough.
 *
 * Usage:
 *   php renderThumbnailsCli.php --base=https://example.com/modules/templates --id=01-foo,02-bar
 *   php renderThumbnailsCli.php --ids-from=ids.txt --base=...
 *   php renderThumbnailsCli.php --local            (render every local template)
 *   php renderThumbnailsCli.php --local --missing  (only those without an image)
 *
 * Options:
 *   --base=URL     Render over HTTP from this site instead of local files.
 *                  Point it at the templates module root.
 *   --id=LIST      Comma separated template ids.
 *   --ids-from=F   Read ids from a file, one per line.
 *   --local        Render from local files listed in data/templates.json.
 *   --missing      With --local, skip templates that already have an image.
 *   --out=DIR      Write to DIR instead of assets/img/custom-template.
 *   --force        Overwrite an existing image (default: skip it).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script runs from the command line only.\n");
}

require_once __DIR__ . '/templateCatalogue.php';

function cliOption(string $name, $default = null)
{
    foreach ($GLOBALS['argv'] as $arg) {
        if ($arg === '--' . $name) {
            return true;
        }
        if (strpos($arg, '--' . $name . '=') === 0) {
            return substr($arg, strlen($name) + 3);
        }
    }
    return $default;
}

$base = cliOption('base');
$idList = cliOption('id');
$idsFrom = cliOption('ids-from');
$useLocal = cliOption('local', false);
$onlyMissing = cliOption('missing', false);
$force = cliOption('force', false);
$outDir = cliOption('out', TPL_THUMB_DIR);

$blocker = tplAutoThumbnailBlocker();
if ($blocker !== null) {
    exit("Cannot render here: " . $blocker . "\n");
}

// Work out which ids to render.
$ids = [];
if ($idsFrom !== null) {
    if (!is_file($idsFrom)) {
        exit("ids file not found: $idsFrom\n");
    }
    foreach (file($idsFrom, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line !== '' && $line[0] !== '#') {
            $ids[] = $line;
        }
    }
} elseif ($idList !== null) {
    foreach (explode(',', $idList) as $part) {
        $part = trim($part);
        if ($part !== '') {
            $ids[] = $part;
        }
    }
} elseif ($useLocal) {
    foreach (tplReadCatalogue() as $row) {
        if (empty($row['id'])) {
            continue;
        }
        if ($onlyMissing) {
            $thumb = !empty($row['thumbnail']) ? $row['thumbnail'] : 'thumbnail' . $row['id'] . ' (Custom).png';
            if (is_file(TPL_THUMB_DIR . '/' . $thumb)) {
                continue;
            }
        }
        $ids[] = $row['id'];
    }
}

if (count($ids) === 0) {
    exit("No template ids given. Use --id=a,b  --ids-from=file  or --local\n");
}
if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    exit("Cannot create output directory: $outDir\n");
}

$catalogue = tplReadCatalogue();
printf("Rendering %d template(s) into %s\n", count($ids), $outDir);
printf("Source: %s\n\n", $base !== null ? rtrim($base, '/') : 'local files');

$ok = 0;
$skipped = 0;
$failed = 0;

foreach ($ids as $id) {
    $target = $outDir . '/thumbnail' . $id . ' (Custom).png';
    if (!$force && is_file($target)) {
        printf("  %-28s skipped (image already exists)\n", $id);
        $skipped++;
        continue;
    }

    // Resolve the template's HTML file name from the catalogue when possible.
    $fileName = 'Template' . $id . '.html';
    $index = tplFindIndexById($catalogue, $id);
    if ($index !== false && !empty($catalogue[$index]['file'])) {
        $fileName = $catalogue[$index]['file'];
    }

    $source = null;
    $tempCopy = null;

    if ($base !== null) {
        /*
         * Download the template, then render the local copy. Chrome is given
         * a file:// URL so the page cannot reach anything on this machine.
         */
        $url = rtrim($base, '/') . '/views/customized-template/' . rawurlencode($fileName);
        $html = @file_get_contents($url);
        if ($html === false || trim($html) === '') {
            printf("  %-28s FAILED (could not download %s)\n", $id, $url);
            $failed++;
            continue;
        }
        $tempCopy = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tplcli_' . bin2hex(random_bytes(5)) . '.html';
        // Give relative assets a chance to resolve against the live site.
        if (stripos($html, '<base ') === false) {
            $baseTag = '<base href="' . htmlspecialchars(rtrim($base, '/') . '/views/customized-template/', ENT_QUOTES) . '">';
            if (preg_match('/<head[^>]*>/i', $html, $m)) {
                $html = preg_replace('/(<head[^>]*>)/i', '$1' . $baseTag, $html, 1);
            } else {
                $html = $baseTag . $html;
            }
        }
        file_put_contents($tempCopy, $html);
        $source = $tempCopy;
    } else {
        $source = TPL_HTML_DIR . '/' . $fileName;
        if (!is_file($source)) {
            printf("  %-28s FAILED (local file not found: %s)\n", $id, $fileName);
            $failed++;
            continue;
        }
    }

    $started = microtime(true);
    $result = tplGenerateThumbnail($source, $target);
    $elapsed = round(microtime(true) - $started, 1);

    if ($tempCopy !== null) {
        @unlink($tempCopy);
    }

    if (is_string($result) && is_file($result)) {
        $size = getimagesize($result);
        printf("  %-28s OK  %dx%d  %d KB  (%.1fs)\n", $id, $size[0], $size[1], round(filesize($result) / 1024), $elapsed);
        $ok++;
    } else {
        printf("  %-28s FAILED  %s\n", $id, is_string($result) ? $result : 'unknown error');
        $failed++;
    }
}

printf("\nDone: %d rendered, %d skipped, %d failed.\n", $ok, $skipped, $failed);
if ($ok > 0) {
    printf("Upload the PNGs in %s to the server's assets/img/custom-template/ directory.\n", $outDir);
}
