<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

function docIcon($url) {
    if (empty($url)) return '-';
    $safeUrl = htmlspecialchars($url, ENT_QUOTES);
    return '<a href="' . $safeUrl . '" target="_blank" title="View file"><i class="bi bi-image" style="font-size:1.35rem;"></i></a>';
}

$sql = 'SELECT dateThai, shop_name, country, shopPhone, shopEmail, managerName, documents, adyenAgree
          FROM `POSandNewOnlineOrder`
         WHERE `status` = ?
         ORDER BY `submissionId` DESC';

$rows = $db->query($sql, 'Old Client')->fetchAll();
$data = ['data' => []];

foreach ($rows as $r) {
    $documents = json_decode($r['documents'] ?? '', true);
    if (!is_array($documents)) $documents = [];

    $data['data'][] = [
        htmlspecialchars($r['dateThai'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shop_name'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['country'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shopPhone'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shopEmail'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['managerName'] ?? '', ENT_QUOTES),
        docIcon($documents['businessRegistration'] ?? ''),
        docIcon($documents['bankStatement'] ?? ''),
        docIcon($documents['directorId'] ?? ''),
        htmlspecialchars($r['adyenAgree'] ?? '', ENT_QUOTES),
    ];
}

echo json_encode($data);
