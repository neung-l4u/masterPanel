<?php
/**
 * Authoritative status of a DocuSign envelope.
 * GET /api/docusign/envelopeStatus.php?envelopeId=...
 *
 * Response:
 *   { "success": true, "envelopeId": "...", "status": "completed",
 *     "sentDateTime": "...", "completedDateTime": "..." }
 *
 * Statuses: created, sent, delivered, signed, completed, declined, voided.
 */

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/DocuSignClient.php';

$envelopeId = trim((string) ($_GET['envelopeId'] ?? ''));

if ($envelopeId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'envelopeId is required']);
    exit();
}

$configPath = __DIR__ . '/docusign_config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DocuSign is not configured']);
    exit();
}

try {
    $client   = new DocuSignClient(require $configPath);
    $envelope = $client->getEnvelope($envelopeId);

    echo json_encode([
        'success'           => true,
        'envelopeId'        => $envelope['envelopeId'] ?? $envelopeId,
        'status'            => $envelope['status'] ?? 'unknown',
        'sentDateTime'      => $envelope['sentDateTime'] ?? null,
        'completedDateTime' => $envelope['completedDateTime'] ?? null,
    ]);
} catch (Throwable $e) {
    error_log('[DocuSign] envelopeStatus failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
