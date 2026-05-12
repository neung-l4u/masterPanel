<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$result = $db->query('SELECT * FROM ai_araya ORDER BY id DESC')->fetchAll();

$data = array("data"=> array());

$i = 1;

foreach ($result as $row) {
    $date = date("d-m-y H:i", strtotime($row["createAt"]));
    $stripeID = htmlspecialchars($row["stripeID"] ?? "");

    // Try new customerDetailsLogs column first, fallback to old dataLogs
    $rawJson = !empty($row["customerDetailsLogs"]) ? $row["customerDetailsLogs"] : ($row["dataLogs"] ?? "");
    $json = json_decode($rawJson, true);

    if (!$json) {
        $i++;
        continue;
    }

    // New payload fields
    $businessName   = htmlspecialchars($json["businessName"] ?? "-");
    $shopEmail      = htmlspecialchars($json["shopEmail"] ?? "-");
    $phone          = htmlspecialchars($json["backupPhoneNumber"] ?? "-");
    $bookingSystem  = htmlspecialchars($json["currentBookingSystem"] ?? "-");
    $voiceType      = htmlspecialchars($json["voiceType"] ?? "-");
    $googleCal      = htmlspecialchars($json["googleCalendar"] ?? "-");
    $therapists     = htmlspecialchars($json["numberOfTherapists"] ?? "-");
    $formVersion    = htmlspecialchars($json["formVersion"] ?? "-");

    // Services summary (truncated)
    $servicesFull = htmlspecialchars($json["servicesOffered"] ?? "-");
    $servicesShort = shortText($servicesFull, 30);

    // Build detail JSON for modal
    $detailJson = htmlspecialchars($rawJson, ENT_QUOTES, 'UTF-8');

    $data["data"][] = array(
        $i,
        $businessName,
        $shopEmail,
        $phone,
        $date,
        $detailJson,
    );

    $i++;
}

echo json_encode($data);

function shortText($param, $len = 30): string
{
    if (mb_strlen($param) <= $len) return $param;
    $desc = mb_substr($param, 0, $len).'...';
    return "<span data-bs-toggle='tooltip' title='" . str_replace("'", "&#39;", $param) . "'>$desc</span>";
}