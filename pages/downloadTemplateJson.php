<?php
/**
 * Serves the Elementor template JSON of a project with every `$__token__!`
 * placeholder replaced by the project data.
 *
 *   ?projectID=194              -> ZIP with every page of the template
 *   ?projectID=194&file=X.json  -> that single rendered JSON file
 */
global $db;
session_start();

require_once "../assets/db/db.php";
require_once "../assets/db/initDB.php";
require_once "../assets/php/shareFunction.php";
require_once "../assets/php/ElementorTemplateRenderer.php";
require_once "../assets/php/SimpleZipWriter.php";

date_default_timezone_set("Asia/Bangkok");

function fail($message, $status = 400)
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

// Accept both a plain GET link and the POST of the edit form.
$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

$projectID = isset($input['projectID']) ? (int)$input['projectID'] : 0;
$requestedFile = isset($input['file']) ? basename($input['file']) : '';

if ($projectID <= 0) {
    fail('Missing or invalid projectID.');
}

$project = $db->query(
    'SELECT pj.`projectName`, st.name AS "shopType", pj.`selectedTemplate`, ct.name AS "country",
            pj.`email`, pj.`phone`, pj.`address`, pj.`city`, pj.`openingCustom`, pj.`openingHours`,
            pj.`orderURL`, pj.`tableURL`, pj.`facebookURL`, pj.`instagramURL`
       FROM `tb_project` pj, `Countries` ct, `tb_shopType` st
      WHERE pj.`projectID` = ? AND pj.`countryID` = ct.`id` AND pj.`shopTypeID` = st.id',
    $projectID
)->fetchArray();

if (empty($project)) {
    fail('Project not found.', 404);
}

// Same opening-hours formatting as the submission details page.
if ((int)$project['openingCustom'] === 1) {
    $openingHours = $project['openingHours'];
} else {
    $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $parts = explode('__', (string)$project['openingHours']);
    $lines = array();
    foreach ($days as $index => $day) {
        $value = isset($parts[$index]) && $parts[$index] !== '' ? $parts[$index] : '-';
        $lines[] = $day . ' : ' . $value;
    }
    $openingHours = implode('<br>', $lines);
}

$pageRow = $db->query(
    'SELECT `home`, `about`, `services`, `contact` FROM `templatepagedetails` WHERE projectID = ?',
    $projectID
)->fetchArray();

$pageDetails = array(
    'home'     => json_decode(isset($pageRow['home']) ? $pageRow['home'] : '', true),
    'about'    => json_decode(isset($pageRow['about']) ? $pageRow['about'] : '', true),
    'services' => json_decode(isset($pageRow['services']) ? $pageRow['services'] : '', true),
    'contact'  => json_decode(isset($pageRow['contact']) ? $pageRow['contact'] : '', true),
);

$renderer = new ElementorTemplateRenderer();
$renderer->setProjectData($project, $pageDetails, $openingHours);

// The edit form posts a `values` map. Anything present there wins over the
// value derived from the project, so the download can be tweaked without
// touching the database. Only known placeholder names are accepted.
if (isset($input['values']) && is_array($input['values'])) {
    $allowed = array_keys($renderer->getValues());
    foreach ($input['values'] as $name => $value) {
        if (in_array($name, $allowed, true) && !is_array($value)) {
            $renderer->setValue($name, trim((string)$value));
        }
    }
}

$folder = $renderer->resolveTemplateFolder($project['shopType'], $project['selectedTemplate']);
if ($folder === null) {
    fail('No Elementor template folder for ' . $project['shopType']
        . ' template ' . $project['selectedTemplate'] . '.', 404);
}

$prefix = $projectID . '-' . sanitizeFolderName($project['projectName']);

// Single file download
if ($requestedFile !== '') {
    $content = $renderer->renderFile($folder, $requestedFile);
    if ($content === null) {
        fail('Template file not found.', 404);
    }

    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $prefix . ' ' . $requestedFile . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

// Whole template as a ZIP
$rendered = $renderer->renderAll($folder);
if (empty($rendered)) {
    fail('Template folder is empty.', 404);
}

$zip = new SimpleZipWriter();
foreach ($rendered as $name => $content) {
    $zip->addFile($name, $content);
}
$bytes = $zip->build();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $prefix . ' ' . $folder . '.zip"');
header('Content-Length: ' . strlen($bytes));
echo $bytes;
