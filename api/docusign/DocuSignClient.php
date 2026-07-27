<?php
/**
 * DocuSignClient — minimal DocuSign eSignature REST client using JWT Grant.
 *
 * Only the calls this project needs are implemented: fetch an access token,
 * create an envelope, and open an embedded signing session.
 *
 * Usage:
 *   $client = new DocuSignClient(require 'docusign_config.php');
 *   $result = $client->createEnvelope([...]);
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

class DocuSignClient
{
    /** Access tokens are valid for 1 hour; refresh a little early. */
    private const TOKEN_TTL        = 3600;
    private const TOKEN_SAFETY_GAP = 300;

    private array $config;
    private string $authHost;
    private string $apiBase;
    private string $tokenCacheFile;

    public function __construct(array $config)
    {
        foreach (['integrationKey', 'userId', 'accountId', 'privateKeyPath'] as $required) {
            if (empty($config[$required])) {
                throw new RuntimeException("DocuSign config is missing '{$required}'");
            }
        }

        if (!is_readable($config['privateKeyPath'])) {
            throw new RuntimeException("DocuSign private key not readable at {$config['privateKeyPath']}");
        }

        $this->config = $config;

        $isProduction   = ($config['environment'] ?? 'demo') === 'production';
        $this->authHost = $isProduction ? 'account.docusign.com' : 'account-d.docusign.com';
        $this->apiBase  = $isProduction
            ? 'https://na3.docusign.net/restapi/v2.1'
            : 'https://demo.docusign.net/restapi/v2.1';

        $this->tokenCacheFile = sys_get_temp_dir() . '/docusign_token_' . md5($config['integrationKey'] . $config['userId']) . '.json';
    }

    // ------------------------------------------------------------------
    // Authentication
    // ------------------------------------------------------------------

    /**
     * Return a valid access token, reusing the cached one when possible.
     */
    public function getAccessToken(): string
    {
        $cached = $this->readCachedToken();
        if ($cached !== null) {
            return $cached;
        }

        $now = time();
        $assertion = JWT::encode([
            'iss'   => $this->config['integrationKey'],
            'sub'   => $this->config['userId'],
            'aud'   => $this->authHost,
            'iat'   => $now,
            'exp'   => $now + self::TOKEN_TTL,
            'scope' => 'signature impersonation',
        ], file_get_contents($this->config['privateKeyPath']), 'RS256');

        $response = $this->request(
            'POST',
            "https://{$this->authHost}/oauth/token",
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $assertion,
            ]),
            ['Content-Type: application/x-www-form-urlencoded'],
            false
        );

        if (empty($response['access_token'])) {
            // consent_required means the impersonated user has never granted consent.
            if (($response['error'] ?? '') === 'consent_required') {
                throw new RuntimeException(
                    'DocuSign consent required. Grant it once by opening: ' . $this->consentUrl()
                );
            }
            throw new RuntimeException('DocuSign token request failed: ' . json_encode($response));
        }

        $this->writeCachedToken($response['access_token'], (int) ($response['expires_in'] ?? self::TOKEN_TTL));

        return $response['access_token'];
    }

    /**
     * The URL an admin must visit once to authorise impersonation.
     */
    public function consentUrl(): string
    {
        return "https://{$this->authHost}/oauth/auth?" . http_build_query([
            'response_type' => 'code',
            'scope'         => 'signature impersonation',
            'client_id'     => $this->config['integrationKey'],
            'redirect_uri'  => $this->config['returnUrl'] ?? 'https://report.localforyou.com/',
        ]);
    }

    // ------------------------------------------------------------------
    // Envelopes
    // ------------------------------------------------------------------

    /**
     * Create and send an envelope containing a single PDF document.
     *
     * @param array $args {
     *   @type string $pdfBase64      Base64-encoded PDF bytes. Required.
     *   @type string $documentName   Shown in the DocuSign UI. Required.
     *   @type string $signerName     Required.
     *   @type string $signerEmail    Required for email flow; for embedded signing
     *                                a placeholder address is still needed by DocuSign.
     *   @type string $emailSubject   Defaults to the document name.
     *   @type bool   $embedded       true = return a signing URL instead of emailing.
     *   @type string $clientUserId   Identifies the embedded signer. Defaults to the email.
     * }
     * @return array{envelopeId:string, status:string, signingUrl:?string}
     */
    public function createEnvelope(array $args): array
    {
        foreach (['pdfBase64', 'documentName', 'signerName', 'signerEmail'] as $required) {
            if (empty($args[$required])) {
                throw new InvalidArgumentException("createEnvelope requires '{$required}'");
            }
        }

        $embedded     = !empty($args['embedded']);
        $clientUserId = $embedded ? ($args['clientUserId'] ?: $args['signerEmail']) : null;

        $signer = [
            'email'        => $args['signerEmail'],
            'name'         => $args['signerName'],
            'recipientId'  => '1',
            'routingOrder' => '1',
            'tabs'         => [
                // anchorString places the tab wherever this text appears in the PDF,
                // so the contract template controls signature placement.
                'signHereTabs' => [[
                    'anchorString'       => '/sig1/',
                    'anchorUnits'        => 'pixels',
                    'anchorXOffset'      => '0',
                    'anchorYOffset'      => '0',
                    'anchorIgnoreIfNotPresent' => 'false',
                ]],
                'dateSignedTabs' => [[
                    'anchorString'       => '/date1/',
                    'anchorUnits'        => 'pixels',
                    'anchorIgnoreIfNotPresent' => 'true',
                ]],
            ],
        ];

        if ($clientUserId !== null) {
            $signer['clientUserId'] = $clientUserId;
        }

        $signers = [$signer];

        // Optional company countersigner, always routed after the customer.
        if (!empty($this->config['counterSignerEmail'])) {
            $signers[] = [
                'email'        => $this->config['counterSignerEmail'],
                'name'         => $this->config['counterSignerName'] ?: $this->config['senderName'] ?? 'Local For You',
                'recipientId'  => '2',
                'routingOrder' => '2',
                'tabs'         => [
                    'signHereTabs' => [[
                        'anchorString'             => '/sig2/',
                        'anchorUnits'              => 'pixels',
                        'anchorIgnoreIfNotPresent' => 'true',
                    ]],
                ],
            ];
        }

        $envelope = [
            'emailSubject' => $args['emailSubject'] ?? $args['documentName'],
            'status'       => 'sent', // 'created' would leave it as a draft
            'documents'    => [[
                'documentBase64' => $args['pdfBase64'],
                'name'           => $args['documentName'],
                'fileExtension'  => 'pdf',
                'documentId'     => '1',
            ]],
            'recipients'   => ['signers' => $signers],
        ];

        $result = $this->apiRequest('POST', "/accounts/{$this->config['accountId']}/envelopes", $envelope);

        if (empty($result['envelopeId'])) {
            throw new RuntimeException('DocuSign envelope creation failed: ' . json_encode($result));
        }

        return [
            'envelopeId' => $result['envelopeId'],
            'status'     => $result['status'] ?? 'unknown',
            'signingUrl' => $embedded
                ? $this->createRecipientView($result['envelopeId'], $args['signerName'], $args['signerEmail'], $clientUserId)
                : null,
        ];
    }

    /**
     * Get the one-time URL that renders the DocuSign signing ceremony for an
     * embedded signer. The URL expires within a few minutes and is single-use.
     */
    public function createRecipientView(string $envelopeId, string $name, string $email, string $clientUserId): string
    {
        $result = $this->apiRequest(
            'POST',
            "/accounts/{$this->config['accountId']}/envelopes/{$envelopeId}/views/recipient",
            [
                'returnUrl'             => ($this->config['returnUrl'] ?? '') . '?envelopeId=' . urlencode($envelopeId),
                'authenticationMethod'  => 'none',
                'userName'              => $name,
                'email'                 => $email,
                'clientUserId'          => $clientUserId,
            ]
        );

        if (empty($result['url'])) {
            throw new RuntimeException('DocuSign recipient view failed: ' . json_encode($result));
        }

        return $result['url'];
    }

    /**
     * Current status of an envelope (sent, delivered, completed, declined, voided).
     */
    public function getEnvelope(string $envelopeId): array
    {
        return $this->apiRequest('GET', "/accounts/{$this->config['accountId']}/envelopes/{$envelopeId}");
    }

    // ------------------------------------------------------------------
    // HTTP plumbing
    // ------------------------------------------------------------------

    private function apiRequest(string $method, string $path, ?array $body = null): array
    {
        return $this->request(
            $method,
            $this->apiBase . $path,
            $body === null ? null : json_encode($body),
            [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json',
            ]
        );
    }

    /**
     * @param bool $throwOnHttpError false while fetching a token, so the caller can
     *                               inspect DocuSign's error payload (e.g. consent_required).
     */
    private function request(string $method, string $url, ?string $body, array $headers, bool $throwOnHttpError = true): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException("DocuSign request to {$url} failed: {$err}");
        }

        $decoded = json_decode($raw, true) ?? [];

        if ($throwOnHttpError && $status >= 400) {
            $this->log("HTTP {$status} from {$method} {$url}: {$raw}");
            throw new RuntimeException("DocuSign API returned HTTP {$status}: {$raw}");
        }

        return $decoded;
    }

    // ------------------------------------------------------------------
    // Token cache
    // ------------------------------------------------------------------

    private function readCachedToken(): ?string
    {
        if (!is_readable($this->tokenCacheFile)) {
            return null;
        }

        $cache = json_decode((string) file_get_contents($this->tokenCacheFile), true);

        if (empty($cache['token']) || empty($cache['expires_at'])) {
            return null;
        }

        return $cache['expires_at'] > time() + self::TOKEN_SAFETY_GAP ? $cache['token'] : null;
    }

    private function writeCachedToken(string $token, int $expiresIn): void
    {
        file_put_contents($this->tokenCacheFile, json_encode([
            'token'      => $token,
            'expires_at' => time() + $expiresIn,
        ]));
        @chmod($this->tokenCacheFile, 0600);
    }

    private function log(string $message): void
    {
        $logFile = $this->config['logFile'] ?? null;
        if (!$logFile) {
            return;
        }

        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] {$message}\n", FILE_APPEND);
    }
}
