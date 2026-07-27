<?php
/**
 * Send an agreement to DocuSign for signature.
 * POST /api/docusign/sendAgreement.php
 *
 * Accepts either a JSON body or a normal form POST.
 *
 * Fields:
 *   agreementType      pushpos | marketing            (required)
 *   signerEmail        customer email                  (required)
 *   customerFullName   signer name                     (required)
 *   deliveryMode       email | embedded                (default: email)
 *   ShopName, legalEntity, registrationNumber,
 *   State, Country, contractPeriod                     (passed to the template)
 *
 * Response (email mode):
 *   { "success": true, "envelopeId": "...", "status": "sent", "deliveryMode": "email" }
 *
 * Response (embedded mode):
 *   { "success": true, "envelopeId": "...", "signingUrl": "https://...", "deliveryMode": "embedded" }
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/DocuSignClient.php';
require_once __DIR__ . '/ContractPdf.php';

date_default_timezone_set('Asia/Bangkok');

$configPath = __DIR__ . '/docusign_config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'DocuSign is not configured. Copy docusign_config.sample.php to docusign_config.php.',
    ]);
    exit();
}

// Accept JSON bodies as well as classic form posts.
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $input = json_decode((string) $raw, true) ?? [];
}

$agreementType = trim((string) ($input['agreementType'] ?? 'pushpos'));
$signerEmail   = trim((string) ($input['signerEmail'] ?? $input['email'] ?? ''));
$signerName    = trim((string) ($input['customerFullName'] ?? ''));
$deliveryMode  = trim((string) ($input['deliveryMode'] ?? 'email'));

$errors = [];

if (!in_array($agreementType, ContractPdf::supportedTypes(), true)) {
    $errors[] = 'agreementType must be one of: ' . implode(', ', ContractPdf::supportedTypes());
}
if ($signerName === '') {
    $errors[] = 'customerFullName is required';
}
if ($signerEmail === '' || !filter_var($signerEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid signerEmail is required';
}
if (!in_array($deliveryMode, ['email', 'embedded'], true)) {
    $errors[] = "deliveryMode must be 'email' or 'embedded'";
}

if ($errors) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode('; ', $errors)]);
    exit();
}

// Only the fields the agreement templates actually read.
$templateFields = [
    'customerFullName'   => $signerName,
    'ShopName'           => trim((string) ($input['ShopName'] ?? '')),
    'legalEntity'        => trim((string) ($input['legalEntity'] ?? '')),
    'registrationNumber' => trim((string) ($input['registrationNumber'] ?? '')),
    'State'              => trim((string) ($input['State'] ?? '')),
    'Country'            => trim((string) ($input['Country'] ?? 'AU')),
    'contractPeriod'     => trim((string) ($input['contractPeriod'] ?? '0')),
];

try {
    $config = require $configPath;

    $renderer = new ContractPdf();
    $pdfBase64 = $renderer->renderBase64($agreementType, $templateFields);

    $shopLabel    = $templateFields['ShopName'] !== '' ? $templateFields['ShopName'] : $signerName;
    $documentName = ($agreementType === 'pushpos' ? 'Push POS Customer Agreement' : 'Marketing Service Agreement')
        . ' - ' . $shopLabel . '.pdf';

    $client = new DocuSignClient($config);
    $result = $client->createEnvelope([
        'pdfBase64'    => $pdfBase64,
        'documentName' => $documentName,
        'signerName'   => $signerName,
        'signerEmail'  => $signerEmail,
        'emailSubject' => 'Please sign: ' . rtrim($documentName, '.pdf'),
        'embedded'     => $deliveryMode === 'embedded',
        'clientUserId' => $signerEmail,
    ]);

    echo json_encode([
        'success'      => true,
        'envelopeId'   => $result['envelopeId'],
        'status'       => $result['status'],
        'deliveryMode' => $deliveryMode,
        'signingUrl'   => $result['signingUrl'],
    ]);
} catch (Throwable $e) {
    error_log('[DocuSign] sendAgreement failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
