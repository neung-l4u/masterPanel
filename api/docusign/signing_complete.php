<?php
/**
 * Landing page DocuSign redirects an embedded signer to when the ceremony ends.
 *
 * DocuSign appends ?event=<outcome>; the envelopeId is carried through from the
 * returnUrl we build in DocuSignClient::createRecipientView().
 *
 * This page is informational only — never treat `event` as proof of signature.
 * The authoritative status comes from envelopeStatus.php / DocuSign Connect.
 */

$event      = $_GET['event']      ?? '';
$envelopeId = $_GET['envelopeId'] ?? '';

$messages = [
    'signing_complete' => ['Thank you — your agreement is signed.', 'A signed copy has been emailed to you.', '#198754'],
    'viewing_complete' => ['Document closed.', 'You can reopen the agreement from the email we sent.', '#0d6efd'],
    'cancel'           => ['Signing cancelled.', 'Nothing has been submitted. You can start again when ready.', '#6c757d'],
    'decline'          => ['Agreement declined.', 'Our team will be in touch shortly.', '#dc3545'],
    'session_timeout'  => ['Session timed out.', 'The signing link expired. Please request a new one.', '#fd7e14'],
    'ttl_expired'      => ['Link expired.', 'This signing link is single-use. Please request a new one.', '#fd7e14'],
];

[$heading, $detail, $colour] = $messages[$event]
    ?? ['Signing session ended.', 'If you completed signing, you will receive a confirmation email.', '#6c757d'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agreement Signing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <h1 class="h4 mb-3" style="color: <?php echo $colour; ?>">
                            <?php echo htmlspecialchars($heading, ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        <p class="text-muted mb-4">
                            <?php echo htmlspecialchars($detail, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <?php if ($envelopeId !== ''): ?>
                            <p class="small text-muted mb-0">
                                Reference: <code><?php echo htmlspecialchars($envelopeId, ENT_QUOTES, 'UTF-8'); ?></code>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
