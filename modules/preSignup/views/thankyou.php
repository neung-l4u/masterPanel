<?php
/**
 * Confirmation shown to a sales rep after a pre sign-up is saved.
 *
 * The form itself shows the generated link inline, so this page exists for the
 * case where the rep lands here directly with ?ref= - from a bookmark, or a
 * refresh after submitting - and still needs the link in front of them.
 */

session_start();

$configPath = __DIR__ . '/../assets/config.php';
$config     = is_file($configPath) ? require $configPath : [];
$baseUrl    = rtrim((string) ($config['public_base_url'] ?? ''), '/');

$ref  = isset($_GET['ref']) ? trim((string) $_GET['ref']) : '';
$link = '';

// Only a reference we recognise becomes a link; anything else is shown as
// nothing rather than echoed back into the page.
if (preg_match('/^(?:prelead-)?0*(\d+)$/i', $ref, $matches)) {
    $ref  = 'prelead-' . str_pad((string) (int) $matches[1], 7, '0', STR_PAD_LEFT);
    $link = ($baseUrl !== '' ? $baseUrl : '') . '/' . $ref;
} else {
    $ref = '';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/preSignupForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/img/L4U-Site-Icon.png">
    <title>Pre Sign-up Saved</title>
</head>
<body style="min-height: 100vh;">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="../assets/img/L4U-Site-Icon.png" alt="logo" style="width: 50px;"/></a>
    </div>
</nav>

<div class="container py-5" style="max-width: 640px;">
    <div class="card">
        <div class="card-body p-4 p-md-5 text-center">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
            <h1 class="h4 fw-bold mt-3">Pre sign-up saved</h1>

            <?php if ($link !== ''): ?>
                <p class="text-muted">
                    Send this link to the customer. It opens the signup form with everything
                    you entered already filled in, and can be opened more than once.
                </p>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="preleadLink"
                           value="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    <button class="btn btn-outline-primary" type="button" id="cmdCopyLink">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <p class="text-muted small">
                    Reference <code><?php echo htmlspecialchars($ref, ENT_QUOTES, 'UTF-8'); ?></code>
                </p>
            <?php else: ?>
                <p class="text-muted">
                    The pre sign-up was recorded. Open the entries page to find its link.
                </p>
            <?php endif; ?>

            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="preSignupForm.php" class="btn btn-primary">Open another lead</a>
                <a href="entries.php" class="btn btn-outline-dark">View entries</a>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script>
$('#cmdCopyLink').on('click', function () {
    const button = this;
    const $input = $('#preleadLink');
    $input.trigger('select');

    const done = () => {
        $(button).html('<i class="bi bi-check2"></i> Copied');
        window.setTimeout(() => {
            $(button).html('<i class="bi bi-clipboard"></i> Copy');
        }, 2000);
    };

    // navigator.clipboard needs a secure context; this page may be served over
    // plain HTTP internally, so fall back to the old command.
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText($input.val()).then(done, () => {
            document.execCommand('copy');
            done();
        });
    } else {
        document.execCommand('copy');
        done();
    }
});
</script>
</body>
</html>
