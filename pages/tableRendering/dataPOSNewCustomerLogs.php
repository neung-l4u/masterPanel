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

function prettyJson($json) {
    if (empty($json)) return '-';
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) return htmlspecialchars($json, ENT_QUOTES);

    $items = [];
    foreach ($decoded as $key => $value) {
        if ($value === null || $value === '') continue;
        $label = ucwords(trim(preg_replace('/(?<!^)[A-Z]/', ' $0', (string)$key)));
        if (is_array($value)) {
            $value = implode(', ', array_filter($value, fn($v) => $v !== null && $v !== ''));
        }
        $items[] = '<div><strong>' . htmlspecialchars($label, ENT_QUOTES) . ':</strong> ' . htmlspecialchars((string)$value, ENT_QUOTES) . '</div>';
    }
    return $items ? implode('', $items) : '-';
}

$sql = 'SELECT *
          FROM `POSandNewOnlineOrder`
         WHERE `status` = ?
         ORDER BY `submissionId` DESC';

$rows = $db->query($sql, 'New Client')->fetchAll();
$data = ['data' => []];

foreach ($rows as $r) {
    $documents = json_decode($r['documents'] ?? '', true);
    if (!is_array($documents)) $documents = [];

    $data['data'][] = [
        htmlspecialchars($r['dateThai'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shop_name'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shopEmail'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['shopPhone'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['managerName'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['country'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['currency'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['tradingName'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['tradingAddress'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['terminalDeliveryAddress'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['serviceProvided'] ?? '', ENT_QUOTES),
        prettyJson($r['openingHours'] ?? ''),
        htmlspecialchars($r['eftposModel'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['eftposQty'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['hasOwnWebsite'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['thirdPartyPlatforms'] ?? '', ENT_QUOTES),
        prettyJson($r['restaurantAddress'] ?? ''),
        htmlspecialchars($r['deliveryServiceNeed'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['deliverBy'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['servicedArea'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['minimumOrder'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['deliveryFee'] ?? '', ENT_QUOTES),
        prettyJson($r['inhouseDelivery'] ?? ''),
        htmlspecialchars($r['logoStatus'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['gmbAccess'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['facebookPageAccess'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['domainHosting'] ?? '', ENT_QUOTES),
        docIcon($r['logoMenuPictures'] ?? ''),
        docIcon($documents['businessRegistration'] ?? ''),
        docIcon($documents['bankStatement'] ?? ''),
        docIcon($documents['directorId'] ?? ''),
        htmlspecialchars($r['adyenAgree'] ?? '', ENT_QUOTES),
        htmlspecialchars($r['status'] ?? '', ENT_QUOTES),
    ];
}

echo json_encode($data);
