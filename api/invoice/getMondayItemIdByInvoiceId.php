<?php
/**
 * getMondayItemIdByInvoiceId.php
 * 
 * ค้นหา monday_item_id จาก invoiceID
 * 
 * POST/GET params:
 *   invoiceID  string  Invoice ID (e.g., "INV-2026-001")
 * 
 * Response:
 * {
 *   "success": true,
 *   "invoiceID": "INV-2026-001",
 *   "monday_item_id": "1234567890",
 *   "invoice_id": 123
 * }
 */

session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Get invoiceID from POST or GET
    $invoiceID = trim($_POST['invoiceID'] ?? $_GET['invoiceID'] ?? '');
    
    if (empty($invoiceID)) {
        throw new Exception('invoiceID is required');
    }
    
    // Query database
    $result = $db->query(
        "SELECT id, invoiceID, monday_item_id FROM thInvoice WHERE invoiceID = ? LIMIT 1",
        $invoiceID
    );
    
    $invoice = $result->fetch();
    
    if (!$invoice) {
        throw new Exception('Invoice not found');
    }
    
    echo json_encode([
        'success' => true,
        'invoiceID' => $invoice['invoiceID'],
        'monday_item_id' => $invoice['monday_item_id'],
        'invoice_id' => $invoice['id']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
