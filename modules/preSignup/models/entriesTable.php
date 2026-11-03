<?php
/**
 * Feeds the entries table on views/entries.php.
 *
 * Rows come back newest first: a rep looking at this page has almost always
 * just created the lead they are looking for.
 */

global $db;
session_start();
header('Content-Type: application/json; charset=utf-8');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$configPath = __DIR__ . '/../assets/config.php';
$config     = is_file($configPath) ? require $configPath : [];
$baseUrl    = rtrim((string) ($config['public_base_url'] ?? ''), '/');

$rows = $db->query(
    'SELECT `id`, `shopName`, `country`, `shopType`, `customerType`,
            `mobNumber`, `shopNumber`, `websiteLink`,
            `nickname`, `firstName`, `lastName`, `email`,
            `streetAddress`, `citySuburb`, `state`, `postalCode`,
            `leadSource`, `contactChannel`, `conversationLink`, `salesRep`,
            `status`, `openedAt`, `openCount`, `opportunityId`, `createAt`
       FROM `presignup`
      ORDER BY `id` DESC'
)->fetchAll();

$data = ['data' => []];

/** Escape a value for the HTML cells below. */
$esc = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

foreach ($rows as $row) {
    $ref  = 'prelead-' . str_pad((string) $row['id'], 7, '0', STR_PAD_LEFT);
    $link = ($baseUrl !== '' ? $baseUrl : '') . '/' . $ref;

    // The link is the reason this page exists, so it is one click to copy.
    $linkCell = '<div class="d-flex align-items-center gap-1">'
        . '<code class="small">' . $esc($ref) . '</code>'
        . '<button type="button" class="btn btn-sm btn-link p-0 copyLink" data-link="' . $esc($link) . '" title="Copy link">'
        . '<i class="bi bi-clipboard"></i></button>'
        . '<a href="' . $esc($link) . '" target="_blank" rel="noopener" class="btn btn-sm btn-link p-0" title="Open link">'
        . '<i class="bi bi-box-arrow-up-right"></i></a>'
        . '</div>';

    // The conversation link is shown whenever there is one, whatever platform
    // it points at and whichever channel was recorded.
    $channel = (string) $row['contactChannel'];
    $channelCell = $esc($channel !== '' ? $channel : '-');
    if (trim((string) $row['conversationLink']) !== '') {
        $channelCell .= ' <a href="' . $esc($row['conversationLink']) . '" target="_blank" rel="noopener" title="Open conversation">'
            . '<i class="bi bi-chat-dots"></i></a>';
    }

    // Whether the customer ever opened the link is the one thing a rep chases,
    // so it is a badge rather than a raw count.
    $openCount = (int) $row['openCount'];
    if ((string) $row['status'] === 'cancelled') {
        $statusCell = '<span class="badge text-bg-secondary">Cancelled</span>';
    } elseif ($openCount === 0) {
        $statusCell = '<span class="badge text-bg-warning">Not opened</span>';
    } else {
        $statusCell = '<span class="badge text-bg-success">Opened ' . $openCount . '&times;</span>';
    }

    $contactName = trim(implode(' ', array_filter([
        (string) $row['firstName'],
        (string) $row['lastName'],
    ])));
    if ($contactName === '') {
        $contactName = (string) $row['nickname'];
    }

    $data['data'][] = [
        $linkCell,
        $esc($row['shopName']),
        $esc($row['country']),
        $esc($row['shopType']),
        $esc($row['customerType']),
        $esc($contactName !== '' ? $contactName : '-'),
        $esc($row['email'] !== '' ? $row['email'] : '-'),
        $esc($row['mobNumber'] !== '' ? $row['mobNumber'] : ($row['shopNumber'] !== '' ? $row['shopNumber'] : '-')),
        $esc($row['leadSource'] !== '' ? $row['leadSource'] : '-'),
        $channelCell,
        $esc($row['salesRep'] !== '' ? $row['salesRep'] : '-'),
        $statusCell,
        $esc(date('d-m-y H:i', strtotime((string) $row['createAt']))),
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
