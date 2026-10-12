<?php
/*
----- Fully custom website brief (tb_projectBrief) ------
A project is "Fully custom" when tb_project.selectedTemplate = 0 (template projects use 1-3, old rows may be NULL).
Shared by modules/templates and pages/tpSubmittedDetails.php so the Send rule lives in one place.
Migration: docs/migrations/2026-10-06-templates-fully-custom.sql
*/

const SHOP_TYPE_OTHER = 6;

const BRIEF_GOALS = ['book' => 'Book online', 'call' => 'Call', 'order' => 'Order food', 'visit' => 'Visit the shop'];
const BRIEF_CONTENT = ['oldsite' => 'From the old website', 'customer' => 'Customer will send', 'we' => 'We write it'];
const BRIEF_COLOR_MODES = ['pick' => 'Pick colors', 'logo' => 'Use logo colors', 'reference' => 'From reference link'];

function isCustomTemplate($selectedTemplate): bool
{
    return $selectedTemplate !== null && $selectedTemplate !== '' && (int)$selectedTemplate === 0;
}

// The db class exits on SQL errors, so check the table before querying it (production may not be migrated yet).
function briefTableExists($db): bool
{
    static $exists = null;
    return $exists ??= $db->query("SHOW TABLES LIKE 'tb_projectBrief'")->numRows() > 0;
}

function loadBrief($db, $projectID): array
{
    if (!briefTableExists($db)) return [];
    $row = $db->query('SELECT * FROM `tb_projectBrief` WHERE `projectID` = ?', $projectID)->fetchArray();
    return $row ?: [];
}

function briefRefLinks(array $brief): array
{
    $links = json_decode($brief['refLinks'] ?? '', true);
    return is_array($links) ? array_values(array_filter($links, fn($l) => trim($l['url'] ?? '') !== '')) : [];
}

/** Labels of required brief fields that are still empty; [] means the project can be sent. */
function briefMissing(array $brief, bool $isOther): array
{
    $links = briefRefLinks($brief);
    $blank = fn($k) => trim((string)($brief[$k] ?? '')) === '';
    $missing = [];
    if ($isOther && $blank('businessNote')) $missing[] = 'Business description';
    if ($blank('systemName')) $missing[] = 'Require system';
    if (!isset(BRIEF_GOALS[$brief['goal'] ?? ''])) $missing[] = 'Main goal';
    if ($blank('pages')) $missing[] = 'Pages';
    if (!$links) $missing[] = 'Reference link';
    if (($brief['colorMode'] ?? '') === 'reference' && !array_filter($links, fn($l) => !empty($l['forColor']))) {
        $missing[] = 'Reference link for colors';
    }
    if ($blank('mood')) $missing[] = 'Mood / style';
    if (!isset(BRIEF_CONTENT[$brief['contentSource'] ?? ''])) $missing[] = 'Content source';
    return $missing;
}

/** Design-brief photos are plain files (Brief_*) in the project's upload folder, uploaded via models/upload.php. */
function briefPhotos(string $folder): array
{
    $files = array_map('basename', glob(rtrim($folder, '/') . '/Brief_*') ?: []);
    sort($files);
    return $files;
}
