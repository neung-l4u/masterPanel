<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result = ['result' => '', 'msg' => ''];

$payload = !empty($_POST['payload']) ? $_POST['payload'] : 'no data';

// Parse payload to extract key fields for indexed columns
$data = json_decode($payload, true) ?: [];

$formType       = 'downgrade';
$formVersion    = $data['formVersion']      ?? null;
$formMode       = $data['formMode']         ?? 'customer';
$testMode       = ($data['testMode'] ?? 'false') === 'true' ? 1 : 0;
$stripeID       = $data['stripeID']         ?? '';
$mondayId       = $data['mondayProjectId']  ?? null;
$shopName       = $data['shopName']         ?? null;
$emailAddress   = $data['emailAddress']     ?? null;
$country        = $data['country']          ?? null;
$requestType    = $data['requestType']      ?? null;
$submittedAt    = $data['date']             ?? null;

$db->query(
    'INSERT INTO `upgrade_downgrade_logs`
        (`formType`, `formVersion`, `formMode`, `testMode`,
         `stripeId`, `mondayProjectId`, `shopName`, `emailAddress`,
         `country`, `requestType`, `payload`, `submittedAt`)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    $formType, $formVersion, $formMode, $testMode,
    $stripeID, $mondayId, $shopName, $emailAddress,
    $country, $requestType, $payload, $submittedAt
);

// ----- AI Receptionist cancellation notice (CS item 7) -----
//
// Here rather than on the thank-you page: that page is static HTML reached by a
// redirect with no query string, and it reads no request data at all, so it can
// never learn what was cancelled. This already has the whole payload.
//
// A failed notification must not lose a cancellation, so everything below is
// wrapped: the insert above has already succeeded by this point, and the
// customer's submission is safe regardless of what the mail server does.
try {
    require_once __DIR__ . '/../assets/productClassify.php';

    $cancelled = $data['cancelledProducts'] ?? [];
    if (is_string($cancelled)) {
        $cancelled = [$cancelled];
    }

    $arayaProducts = [];
    if (is_array($cancelled)) {
        foreach ($cancelled as $productName) {
            if (!is_string($productName)) {
                continue;
            }
            if (in_array('araya', upDownClassifyProduct($productName), true)) {
                $arayaProducts[] = $productName;
            }
        }
    }

    // A test submission must not page anyone.
    if ($arayaProducts !== [] && !$testMode) {
        $adminEmail = 'admin@localforyou.com';
        $subject    = 'AI Receptionist cancellation: ' . ($shopName ?: 'unknown shop');

        $rows = [
            'Shop'           => $shopName ?: '-',
            'Email'          => $emailAddress ?: '-',
            'Country'        => $country ?: '-',
            'Monday Project' => $mondayId ?: '-',
            'Request type'   => $requestType ?: '-',
            'Araya products' => implode(', ', $arayaProducts),
            'All cancelled'  => is_array($cancelled) ? implode(', ', $cancelled) : (string) $cancelled,
            'Keeping'        => $data['keepProductsText'] ?? '-',
            'Effective'      => $data['effectiveDate'] ?? '-',
            'Submitted'      => $submittedAt ?: date('d-m-Y H:i'),
            'Mode'           => $formMode,
        ];

        $body = '<h2 style="font-family:sans-serif">AI Receptionist cancellation</h2>'
              . '<table cellpadding="6" style="font-family:sans-serif;border-collapse:collapse">';
        foreach ($rows as $label => $value) {
            $body .= '<tr>'
                   . '<td style="border:1px solid #ddd;background:#f6f6f6"><strong>' . htmlspecialchars($label) . '</strong></td>'
                   . '<td style="border:1px solid #ddd">' . htmlspecialchars((string) $value) . '</td>'
                   . '</tr>';
        }
        $body .= '</table>';

        // Same header shape as modules/printer/assets/php/sendPrinterMail.php,
        // which is the pattern this repo already sends mail with.
        $headers = [
            'From'         => 'Local For You <' . $adminEmail . '>',
            'Reply-To'     => $adminEmail,
            'X-Mailer'     => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
        ];

        @mail($adminEmail, $subject, $body, $headers);
    }
} catch (Throwable $e) {
    // Swallowed deliberately. Losing a notification must not lose a
    // cancellation, and the row is already committed.
    error_log('downgradeForm araya notice failed: ' . $e->getMessage());
}

$result['result'] = 'success';
$result['msg']    = 'Saved to DB successfully.';

echo json_encode($result);
?>
