<?php
/**
 * Helper: queue and send TH signup confirmed payload via thApoMonday table.
 *
 * Queue at invoice creation (createInvoiceTH.php / createSubTH.php),
 * send when thReceipt.status becomes confirmed (updateStatusInvoiceTH.php / reviewSlipTH.php).
 */

if (!function_exists('ensureThApoMondayTable')) {
    function ensureThApoMondayTable($db)
    {
        try {
            $db->query("
                CREATE TABLE IF NOT EXISTS `thApoMonday` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `invoice_id` INT UNSIGNED NOT NULL,
                    `customer_id` INT UNSIGNED DEFAULT NULL,
                    `payload` LONGTEXT NOT NULL,
                    `send` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    `response` TEXT DEFAULT NULL,
                    `sentAt` DATETIME DEFAULT NULL,
                    `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uk_invoice_id` (`invoice_id`),
                    KEY `idx_send` (`send`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Throwable $e) {
            error_log('[thApoMonday] ensure table error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('buildThApoMondayPayload')) {
    function buildThApoMondayPayload($db, $invoiceId, $signupPayload = [])
    {
        $invoiceId = (int)$invoiceId;
        if (!$invoiceId || !$db) {
            return null;
        }

        $invRows = $db->query(
            'SELECT i.`id`, i.`invoiceID`, i.`customer_id`, i.`product`, i.`amount`, i.`thBathIn`,
                    i.`billingSeq`, i.`billingDate`, i.`monday_item_id`,
                    c.`name`, c.`address`, c.`taxNumber`, c.`type`, c.`sale`,
                    c.`email` AS customerEmail, c.`phone` AS customerPhone,
                    c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName,
                    c.`clientType`
             FROM `thInvoice` i
             JOIN `thCustomer` c ON c.`id` = i.`customer_id`
             WHERE i.`id` = ? LIMIT 1',
            $invoiceId
        )->fetchAll();

        if (empty($invRows[0])) {
            error_log('[thApoMonday] invoice not found id=' . $invoiceId);
            return null;
        }

        $row = $invRows[0];

        // ยิงเฉพาะ invoice แรก (signup) billingSeq = 1
        if ((int)($row['billingSeq'] ?? 0) !== 1 || ($row['clientType'] ?? '') !== 'first_time') {
            return null;
        }

        $productArr = json_decode($row['product'] ?? '', true) ?? [];
        $summary    = $productArr['summary'] ?? [];

        $receiptRows = $db->query(
            'SELECT `slip`, `thBathRe`, `receiptID`, `sentAt` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
            $invoiceId
        )->fetchAll();
        $receipt   = $receiptRows[0] ?? [];
        $slipPath  = $receipt['slip'] ?? '';
        $slipUrl   = $slipPath ? 'https://report.localforyou.com/modules/signup/assets/uploads/' . $slipPath : '';
        $receiptID = $receipt['receiptID'] ?? $row['invoiceID'];

        $convertBahtPath = __DIR__ . '/convertToBahtText.php';
        if (!function_exists('convertToBahtText') && file_exists($convertBahtPath)) {
            require_once $convertBahtPath;
        }
        $thBathRe = $receipt['thBathRe'] ?? (function_exists('convertToBahtText') ? convertToBahtText((float)$row['amount']) : '');

        $detail      = $productArr['quotation'][0]['detail'][0] ?? [];
        $rawItems    = $productArr['table'] ?? [];
        $productName = '';
        $addonNames  = [];
        foreach ($rawItems as $item) {
            if ($productName === '' && !empty($item['product'])) {
                $productName = trim((string)$item['product']);
            }
            if (!empty($item['addon'])) {
                $addonNames[] = trim((string)$item['addon']);
            }
        }
        $nameParts = preg_split('/\s+/', trim((string)$row['name']), 2);
        $compatibilityPayload = [
            'lead_source'         => 'New Signup',
            'leadStage'           => 'New',
            'country_code'        => 'TH',
            'countryTextOnly'     => 'Thailand',
            'currency'            => 'THB',
            'formType'            => 'Thailand Signup',
            'first_name'          => $nameParts[0] ?? '',
            'last_name'           => $nameParts[1] ?? '',
            'mobile'              => $row['customerPhone'],
            'email'               => $row['customerEmail'],
            'phone'               => $row['customerPhone'],
            'company'             => $row['name'],
            'shopName'            => $row['name'],
            'tradingName'         => $row['name'],
            'businessNumber'      => $row['taxNumber'],
            'street'              => $row['address'],
            'shopCountry'         => 'TH',
            'shipNumber'          => $row['customerPhone'],
            'shipAddress1'        => $row['address'],
            'emailShoppingCart'   => $row['customerEmail'],
            'customerStripeEmail' => $row['customerEmail'],
            'paymentMethod'       => 'Invoice',
            'taxType'             => $row['type'],
            'quotationShopName'   => $row['name'],
            'quotationPhone'      => $row['customerPhone'],
            'quotationEmail'      => $row['customerEmail'],
            'quotationAddress'    => $row['address'],
            'quotationTaxNumber'  => $row['taxNumber'],
            'byAgent'             => $row['sale'],
            'firstTimePayment'    => (string)$row['amount'],
            'product'             => $productName,
            'productName'         => $productName,
            'addons'              => implode(', ', $addonNames),
            'addonsName'          => implode(', ', $addonNames),
        ];
        if (!is_array($signupPayload)) {
            $signupPayload = [];
        }

        $payload = [
            'event'          => 'th_signup_confirmed',
            'invoice_id'     => (int)$row['id'],
            'invoiceID'      => $row['invoiceID'],
            'customer_id'    => (int)$row['customer_id'],
            'monday_item_id' => $row['monday_item_id'] ?? null,
            'clientType'     => $row['clientType'] ?? 'first_time',
            'billingSeq'     => (int)$row['billingSeq'],
            'billingDate'    => $row['billingDate'],
            'status'         => 'confirmed',
            'name'           => $row['name'],
            'address'        => $row['address'],
            'taxNumber'      => $row['taxNumber'],
            'type'           => $row['type'],
            'sale'           => $row['sale'],
            'customerEmail'  => $row['customerEmail'],
            'customerPhone'  => $row['customerPhone'],
            'bankName'       => $row['bankName'],
            'bankThaiNumber' => $row['bankThaiNumber'],
            'bankThaiName'   => $row['bankThaiName'],
            'subtotal'       => $summary['subtotal']           ?? '',
            'vat'            => $summary['vat']                ?? '',
            'grandtotal'     => $summary['grandtotal_inc_vat'] ?? '',
            'withholdingTax' => $summary['withholdingTax']     ?? '',
            'net_payment'    => $summary['net_payment']        ?? $row['amount'],
            'amount'         => (float)$row['amount'],
            'currency'       => 'THB',
            'thBathIn'       => $row['thBathIn'] ?? '',
            'thBathRe'       => $thBathRe,
            'receiptID'      => $receiptID,
            'receipt_url'    => 'https://report.localforyou.com/pages/receiptTH.php?invoice_id=' . $invoiceId,
            'slip_url'       => $slipUrl,
            'confirmedAt'    => $receipt['sentAt'] ?? date('Y-m-d H:i:s'),
        ];

        return array_merge($compatibilityPayload, $signupPayload, $payload);
    }
}

if (!function_exists('queueThApoMondayPayload')) {
    function queueThApoMondayPayload($db, $invoiceId, $signupPayload = [])
    {
        $invoiceId = (int)$invoiceId;
        if (!$invoiceId || !$db) {
            return false;
        }

        ensureThApoMondayTable($db);

        $payload = buildThApoMondayPayload($db, $invoiceId, $signupPayload);
        if (!$payload) {
            return false;
        }

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $existing = $db->query(
                'SELECT `id` FROM `thApoMonday` WHERE `invoice_id` = ? LIMIT 1',
                $invoiceId
            )->fetchAll();

            if (empty($existing[0])) {
                $db->query(
                    'INSERT INTO `thApoMonday` (`invoice_id`, `customer_id`, `payload`, `send`, `createdAt`) VALUES (?,?,?,0,NOW())',
                    $invoiceId, (int)($payload['customer_id'] ?? 0), $payloadJson
                );
            } else {
                $db->query(
                    'UPDATE `thApoMonday` SET `payload` = ?, `customer_id` = ? WHERE `invoice_id` = ? AND `send` = 0',
                    $payloadJson, (int)($payload['customer_id'] ?? 0), $invoiceId
                );
            }
            return true;
        } catch (Throwable $e) {
            error_log('[thApoMonday] queue error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sendThApoMondayPayload')) {
    function sendThApoMondayPayload($db, $invoiceId, $webhookUrl = 'https://hook.us1.make.com/izna04q2cdj68bqylepknapxa4l0wkaz')
    {
        $invoiceId = (int)$invoiceId;
        if (!$invoiceId || !$webhookUrl || !$db) {
            return false;
        }

        ensureThApoMondayTable($db);

        $eligibilityRows = $db->query(
            'SELECT i.`billingSeq`, c.`clientType`
             FROM `thInvoice` i
             JOIN `thCustomer` c ON c.`id` = i.`customer_id`
             WHERE i.`id` = ? LIMIT 1',
            $invoiceId
        )->fetchAll();
        $eligibility = $eligibilityRows[0] ?? [];
        if ((int)($eligibility['billingSeq'] ?? 0) !== 1 || ($eligibility['clientType'] ?? '') !== 'first_time') {
            error_log('[thApoMonday] skipped non-first client invoice_id=' . $invoiceId);
            return true;
        }

        $rowRows = $db->query(
            'SELECT `payload`, `send` FROM `thApoMonday` WHERE `invoice_id` = ? LIMIT 1',
            $invoiceId
        )->fetchAll();

        $payload = null;
        if (!empty($rowRows[0]['payload'])) {
            $payload = json_decode($rowRows[0]['payload'], true) ?? [];
            if ((int)($rowRows[0]['send'] ?? 0) === 1) {
                error_log('[thApoMonday] already sent invoice_id=' . $invoiceId);
                return true;
            }
        }

        $compatiblePayload = buildThApoMondayPayload($db, $invoiceId);
        if ($payload && $compatiblePayload) {
            $payload = array_merge($compatiblePayload, $payload);
        } elseif (!$payload) {
            $payload = $compatiblePayload;
        }

        if (!$payload) {
            error_log('[thApoMonday] cannot build payload invoice_id=' . $invoiceId);
            return false;
        }
        if (($payload['clientType'] ?? '') !== 'first_time' || (int)($payload['billingSeq'] ?? 0) !== 1) {
            error_log('[thApoMonday] skipped non-first client invoice_id=' . $invoiceId);
            return true;
        }
        $payload['country_code'] = $payload['country_code'] ?? 'TH';
        $payload['countryTextOnly'] = $payload['countryTextOnly'] ?? 'Thailand';
        $payload['currency'] = strtoupper((string)($payload['currency'] ?? 'THB'));
        $payload['company'] = $payload['company'] ?? ($payload['name'] ?? 'Thailand Signup');
        $payload['shopName'] = $payload['shopName'] ?? $payload['company'];
        foreach (['phone', 'mobile', 'physicalShopNumber', 'shipNumber', 'quotationPhone', 'customerPhone'] as $phoneKey) {
            if (!empty($payload[$phoneKey]) && strpos((string)$payload[$phoneKey], ',') !== false) {
                $phoneValues = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$payload[$phoneKey])))));
                $payload[$phoneKey] = $phoneValues[0] ?? '';
            }
        }

        // Mark as sending (idempotency)
        $db->query(
            'UPDATE `thApoMonday` SET `send` = 1, `sentAt` = NOW() WHERE `invoice_id` = ? AND `send` = 0',
            $invoiceId
        );

        // Enrich with latest receipt data at send time
        $receiptRows = $db->query(
            'SELECT `slip`, `thBathRe`, `receiptID`, `sentAt` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
            $invoiceId
        )->fetchAll();
        $receipt   = $receiptRows[0] ?? [];
        $slipPath  = $receipt['slip'] ?? '';
        $slipUrl   = $slipPath ? 'https://report.localforyou.com/modules/signup/assets/uploads/' . $slipPath : '';
        $receiptID = $receipt['receiptID'] ?? ($payload['invoiceID'] ?? '');
        $thBathRe  = $receipt['thBathRe'] ?? ($payload['thBathRe'] ?? '');

        $payload['slip_url']    = $slipUrl;
        $payload['receiptID']   = $receiptID;
        $payload['thBathRe']    = $thBathRe;
        $payload['confirmedAt'] = $receipt['sentAt'] ?? date('Y-m-d H:i:s');
        $payload['status']      = 'confirmed';

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response  = curl_exec($ch);
        $httpCode  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $sent      = $response !== false && $httpCode >= 200 && $httpCode < 300;
        $responseText = $curlError !== '' ? $curlError : ($response === false ? 'false' : (string)$response);
        $payloadJson  = json_encode($payload, JSON_UNESCAPED_UNICODE);

        error_log('[thApoMonday] invoice_id=' . $invoiceId . ' webhook=' . $webhookUrl . ' http=' . $httpCode . ' resp=' . $responseText);

        try {
            $db->query(
                'UPDATE `thApoMonday` SET `payload` = ?, `send` = ?, `response` = ? WHERE `invoice_id` = ?',
                $payloadJson, $sent ? 1 : 0, $responseText, $invoiceId
            );
        } catch (Throwable $e) {
            error_log('[thApoMonday] response update error: ' . $e->getMessage());
        }

        return $sent;
    }
}
