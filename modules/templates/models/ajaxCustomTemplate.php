<?php
/*
 * Add / edit / delete endpoint for the customized-template catalogue.
 *
 * Accepts multipart POSTs from views/custom-template.php. Only the HTML
 * template file and an optional thumbnail image are stored; both are
 * extension- and content-checked in models/templateCatalogue.php.
 */

session_start();
require_once __DIR__ . '/templateCatalogue.php';

header('Content-Type: application/json; charset=utf-8');

function tplRespond(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

/*
 * Same session rule as the rest of the module (see assets/API/heartbeat.php):
 * a logged-in user carries the id cookie.
 */
if (empty($_COOKIE['id'])) {
    tplRespond(['status' => 'error', 'message' => 'Your session has expired. Please log in again.'], 401);
}
$loginID = $_COOKIE['id'];

$mode = isset($_POST['mode']) ? trim($_POST['mode']) : '';
$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
// Optional display name. Unlike the id it is free text, never a file name.
$name = isset($_POST['name']) ? tplCleanName($_POST['name']) : '';

// Render a thumbnail from the template when the user did not upload one.
$autoThumb = !empty($_POST['autoThumb']) && tplCanAutoThumbnail();

$categories = tplCategories();
$templates = tplReadCatalogue();

switch ($mode) {

    case 'add':
        /*
         * Bulk uploads post one file per request without an id, so derive it
         * from the file name. A single add still sends an explicit id.
         */
        if ($id === '' && isset($_FILES['htmlFile']['name'])) {
            $derived = tplIdFromFilename($_FILES['htmlFile']['name']);
            if ($derived === false) {
                tplRespond([
                    'status'  => 'error',
                    'message' => 'Could not derive a template id from "' . basename($_FILES['htmlFile']['name'])
                        . '". Rename the file using letters and numbers.',
                ], 422);
            }
            $id = $derived;
        }
        if (!tplValidateId($id)) {
            tplRespond(['status' => 'error', 'message' => 'Template id may only contain letters, numbers, dot, dash or underscore.'], 422);
        }
        if (!isset($categories[$category])) {
            tplRespond(['status' => 'error', 'message' => 'Unknown category.'], 422);
        }
        if (tplFindIndexById($templates, $id) !== false) {
            tplRespond(['status' => 'error', 'message' => 'Template ' . $id . ' already exists.'], 409);
        }
        if (!isset($_FILES['htmlFile'])) {
            tplRespond(['status' => 'error', 'message' => 'An HTML file is required.'], 422);
        }
        $htmlError = tplHtmlUploadError($_FILES['htmlFile']);
        if ($htmlError !== null) {
            tplRespond(['status' => 'error', 'message' => $htmlError], 422);
        }

        $hasThumb = isset($_FILES['thumbFile']) && isset($_FILES['thumbFile']['error'])
            && $_FILES['thumbFile']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($hasThumb) {
            $thumbError = tplThumbUploadError($_FILES['thumbFile']);
            if ($thumbError !== null) {
                tplRespond(['status' => 'error', 'message' => $thumbError], 422);
            }
        }

        tplEnsureUploadGuard();
        if (!is_dir(TPL_HTML_DIR) && !mkdir(TPL_HTML_DIR, 0775, true) && !is_dir(TPL_HTML_DIR)) {
            tplRespond(['status' => 'error', 'message' => 'Could not create the template directory.'], 500);
        }

        $htmlExt = tplSafeExtension($_FILES['htmlFile']['name'], TPL_HTML_EXT);
        $htmlName = 'Template' . $id . '.' . $htmlExt;
        $htmlPath = TPL_HTML_DIR . '/' . $htmlName;
        if (is_file($htmlPath)) {
            tplRespond(['status' => 'error', 'message' => 'A file named ' . $htmlName . ' already exists on disk.'], 409);
        }
        if (!move_uploaded_file($_FILES['htmlFile']['tmp_name'], $htmlPath)) {
            tplRespond(['status' => 'error', 'message' => 'Could not save the HTML file.'], 500);
        }
        @chmod($htmlPath, 0664);

        $thumbName = null;
        $thumbNotice = null;
        if ($hasThumb) {
            if (!is_dir(TPL_THUMB_DIR) && !mkdir(TPL_THUMB_DIR, 0775, true) && !is_dir(TPL_THUMB_DIR)) {
                @unlink($htmlPath);
                tplRespond(['status' => 'error', 'message' => 'Could not create the thumbnail directory.'], 500);
            }
            $thumbExt = tplSafeExtension($_FILES['thumbFile']['name'], TPL_THUMB_EXT);
            $thumbName = 'thumbnail' . $id . ' (Custom).' . $thumbExt;
            if (!move_uploaded_file($_FILES['thumbFile']['tmp_name'], TPL_THUMB_DIR . '/' . $thumbName)) {
                @unlink($htmlPath);
                tplRespond(['status' => 'error', 'message' => 'Could not save the thumbnail.'], 500);
            }
            @chmod(TPL_THUMB_DIR . '/' . $thumbName, 0664);
        } elseif ($autoThumb) {
            /*
             * No thumbnail was uploaded and the user asked for one to be
             * rendered. A failure here is not fatal: the template is still
             * added and the card falls back to the placeholder icon.
             */
            if (!is_dir(TPL_THUMB_DIR) && !mkdir(TPL_THUMB_DIR, 0775, true) && !is_dir(TPL_THUMB_DIR)) {
                @unlink($htmlPath);
                tplRespond(['status' => 'error', 'message' => 'Could not create the thumbnail directory.'], 500);
            }
            $candidate = 'thumbnail' . $id . ' (Custom).png';
            $generated = tplGenerateThumbnail($htmlPath, TPL_THUMB_DIR . '/' . $candidate);
            if (is_string($generated) && is_file($generated)) {
                @chmod($generated, 0664);
                $thumbName = $candidate;
            } else {
                $thumbNotice = is_string($generated) ? $generated : 'The thumbnail could not be generated.';
            }
        }

        $templates[] = [
            'id'         => $id,
            'name'       => $name,
            'category'   => $category,
            'file'       => $htmlName,
            'thumbnail'  => $thumbName,
            'active'     => true,
            'createBy'   => $loginID,
            'createDate' => date('Y-m-d H:i:s'),
        ];

        if (!tplWriteCatalogue($templates)) {
            @unlink($htmlPath);
            if ($thumbName !== null) {
                @unlink(TPL_THUMB_DIR . '/' . $thumbName);
            }
            tplRespond(['status' => 'error', 'message' => 'Could not update the catalogue.'], 500);
        }
        tplRespond([
            'status'  => 'success',
            'message' => 'Template ' . $id . ' added.'
                . ($thumbNotice !== null ? ' Thumbnail skipped: ' . $thumbNotice : ''),
            'notice'  => $thumbNotice,
            'id'      => $id,
        ]);
        break;

    case 'edit':
        $index = tplFindIndexById($templates, $id);
        if ($index === false) {
            tplRespond(['status' => 'error', 'message' => 'Template not found.'], 404);
        }
        if (!isset($categories[$category])) {
            tplRespond(['status' => 'error', 'message' => 'Unknown category.'], 422);
        }

        $row = $templates[$index];
        $replacedHtml = null;
        $replacedThumb = null;
        $thumbNotice = null;

        // Replacing the HTML file is optional; the category alone may change.
        $hasHtml = isset($_FILES['htmlFile']) && isset($_FILES['htmlFile']['error'])
            && $_FILES['htmlFile']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($hasHtml) {
            $htmlError = tplHtmlUploadError($_FILES['htmlFile']);
            if ($htmlError !== null) {
                tplRespond(['status' => 'error', 'message' => $htmlError], 422);
            }
            tplEnsureUploadGuard();
            $htmlExt = tplSafeExtension($_FILES['htmlFile']['name'], TPL_HTML_EXT);
            $htmlName = 'Template' . $row['id'] . '.' . $htmlExt;
            $htmlPath = TPL_HTML_DIR . '/' . $htmlName;

            // Keep the previous file until the catalogue write succeeds.
            if (is_file($htmlPath)) {
                $replacedHtml = $htmlPath . '.bak-' . time();
                @rename($htmlPath, $replacedHtml);
            }
            if (!move_uploaded_file($_FILES['htmlFile']['tmp_name'], $htmlPath)) {
                if ($replacedHtml !== null) {
                    @rename($replacedHtml, $htmlPath);
                }
                tplRespond(['status' => 'error', 'message' => 'Could not save the HTML file.'], 500);
            }
            @chmod($htmlPath, 0664);

            // A different extension leaves the old file behind; remove it.
            $oldFile = isset($row['file']) ? $row['file'] : null;
            if ($oldFile !== null && $oldFile !== $htmlName && is_file(TPL_HTML_DIR . '/' . $oldFile)) {
                @unlink(TPL_HTML_DIR . '/' . $oldFile);
            }
            $row['file'] = $htmlName;
        }

        $hasThumb = isset($_FILES['thumbFile']) && isset($_FILES['thumbFile']['error'])
            && $_FILES['thumbFile']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($hasThumb) {
            $thumbError = tplThumbUploadError($_FILES['thumbFile']);
            if ($thumbError !== null) {
                tplRespond(['status' => 'error', 'message' => $thumbError], 422);
            }
            if (!is_dir(TPL_THUMB_DIR) && !mkdir(TPL_THUMB_DIR, 0775, true) && !is_dir(TPL_THUMB_DIR)) {
                tplRespond(['status' => 'error', 'message' => 'Could not create the thumbnail directory.'], 500);
            }
            $thumbExt = tplSafeExtension($_FILES['thumbFile']['name'], TPL_THUMB_EXT);
            $thumbName = 'thumbnail' . $row['id'] . ' (Custom).' . $thumbExt;
            $thumbPath = TPL_THUMB_DIR . '/' . $thumbName;
            if (is_file($thumbPath)) {
                $replacedThumb = $thumbPath . '.bak-' . time();
                @rename($thumbPath, $replacedThumb);
            }
            if (!move_uploaded_file($_FILES['thumbFile']['tmp_name'], $thumbPath)) {
                if ($replacedThumb !== null) {
                    @rename($replacedThumb, $thumbPath);
                }
                tplRespond(['status' => 'error', 'message' => 'Could not save the thumbnail.'], 500);
            }
            @chmod($thumbPath, 0664);

            $oldThumb = isset($row['thumbnail']) ? $row['thumbnail'] : null;
            if ($oldThumb !== null && $oldThumb !== $thumbName && is_file(TPL_THUMB_DIR . '/' . $oldThumb)) {
                @unlink(TPL_THUMB_DIR . '/' . $oldThumb);
            }
            $row['thumbnail'] = $thumbName;
        } elseif ($autoThumb) {
            /*
             * Re-render from whichever HTML file is current after the upload
             * above, so a replaced template gets a matching thumbnail.
             */
            if (!is_dir(TPL_THUMB_DIR) && !mkdir(TPL_THUMB_DIR, 0775, true) && !is_dir(TPL_THUMB_DIR)) {
                tplRespond(['status' => 'error', 'message' => 'Could not create the thumbnail directory.'], 500);
            }
            $candidate = 'thumbnail' . $row['id'] . ' (Custom).png';
            $sourceHtml = TPL_HTML_DIR . '/' . $row['file'];
            $generated = tplGenerateThumbnail($sourceHtml, TPL_THUMB_DIR . '/' . $candidate);
            if (is_string($generated) && is_file($generated)) {
                @chmod($generated, 0664);
                $oldThumb = isset($row['thumbnail']) ? $row['thumbnail'] : null;
                if ($oldThumb !== null && $oldThumb !== $candidate && is_file(TPL_THUMB_DIR . '/' . $oldThumb)) {
                    @unlink(TPL_THUMB_DIR . '/' . $oldThumb);
                }
                $row['thumbnail'] = $candidate;
            } else {
                $thumbNotice = is_string($generated) ? $generated : 'The thumbnail could not be generated.';
            }
        }

        $row['category'] = $category;
        // Only touch the name when the form actually sent the field, so a
        // caller that omits it does not silently clear an existing name.
        if (isset($_POST['name'])) {
            $row['name'] = $name;
        }
        $row['updateBy'] = $loginID;
        $row['updateDate'] = date('Y-m-d H:i:s');
        $templates[$index] = $row;

        if (!tplWriteCatalogue($templates)) {
            tplRespond(['status' => 'error', 'message' => 'Could not update the catalogue.'], 500);
        }

        // Catalogue is committed, so the superseded files are safe to drop.
        if ($replacedHtml !== null) {
            @unlink($replacedHtml);
        }
        if ($replacedThumb !== null) {
            @unlink($replacedThumb);
        }
        tplRespond([
            'status'  => 'success',
            'message' => 'Template ' . $row['id'] . ' updated.'
                . ($thumbNotice !== null ? ' Thumbnail skipped: ' . $thumbNotice : ''),
            'notice'  => $thumbNotice,
        ]);
        break;

    case 'delete':
    case 'restore':
        $index = tplFindIndexById($templates, $id);
        if ($index === false) {
            tplRespond(['status' => 'error', 'message' => 'Template not found.'], 404);
        }
        // Soft delete: files stay on disk so the row can be restored.
        $templates[$index]['active'] = ($mode === 'restore');
        $templates[$index]['updateBy'] = $loginID;
        $templates[$index]['updateDate'] = date('Y-m-d H:i:s');
        if (!tplWriteCatalogue($templates)) {
            tplRespond(['status' => 'error', 'message' => 'Could not update the catalogue.'], 500);
        }
        tplRespond([
            'status'  => 'success',
            'message' => 'Template ' . $id . ($mode === 'restore' ? ' restored.' : ' removed.'),
        ]);
        break;

    /*
     * Re-renders one template's thumbnail. The bulk UI calls this once per
     * template so a slow render cannot time out the whole run.
     */
    case 'regen':
        if (!tplCanAutoThumbnail()) {
            tplRespond([
                'status'  => 'error',
                'message' => 'Thumbnail rendering is not available on this server. '
                    . 'Open models/thumbnailDiagnostics.php for details.',
            ], 503);
        }
        $index = tplFindIndexById($templates, $id);
        if ($index === false) {
            tplRespond(['status' => 'error', 'message' => 'Template not found.'], 404);
        }
        $row = $templates[$index];
        $sourceHtml = TPL_HTML_DIR . '/' . (isset($row['file']) ? $row['file'] : '');
        if (empty($row['file']) || !is_file($sourceHtml)) {
            tplRespond([
                'status'  => 'error',
                'message' => 'The template file is missing on this server, so it cannot be rendered.',
            ], 404);
        }
        if (!is_dir(TPL_THUMB_DIR) && !mkdir(TPL_THUMB_DIR, 0775, true) && !is_dir(TPL_THUMB_DIR)) {
            tplRespond(['status' => 'error', 'message' => 'Could not create the thumbnail directory.'], 500);
        }

        $candidate = 'thumbnail' . $row['id'] . ' (Custom).png';
        $generated = tplGenerateThumbnail($sourceHtml, TPL_THUMB_DIR . '/' . $candidate);
        if (!is_string($generated) || !is_file($generated)) {
            tplRespond([
                'status'  => 'error',
                'message' => is_string($generated) ? $generated : 'The thumbnail could not be generated.',
            ], 500);
        }
        @chmod($generated, 0664);

        // Drop a superseded thumbnail that used a different extension.
        $oldThumb = isset($row['thumbnail']) ? $row['thumbnail'] : null;
        if ($oldThumb !== null && $oldThumb !== $candidate && is_file(TPL_THUMB_DIR . '/' . $oldThumb)) {
            @unlink(TPL_THUMB_DIR . '/' . $oldThumb);
        }

        $templates[$index]['thumbnail'] = $candidate;
        $templates[$index]['updateBy'] = $loginID;
        $templates[$index]['updateDate'] = date('Y-m-d H:i:s');
        if (!tplWriteCatalogue($templates)) {
            tplRespond(['status' => 'error', 'message' => 'Could not update the catalogue.'], 500);
        }
        tplRespond([
            'status'  => 'success',
            'message' => 'Thumbnail rendered for template ' . $row['id'] . '.',
            'id'      => $row['id'],
        ]);
        break;

    default:
        tplRespond(['status' => 'error', 'message' => 'Unknown mode.'], 400);
}
