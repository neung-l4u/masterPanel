<?php
// Set by the feedback form's redirect. This page only re-states what the
// server already decided; it never grants anything and makes no request, so
// a hand-typed ?credit=granted changes a sentence and nothing else.
$creditState = $_GET['credit'] ?? '';
$creditMins  = isset($_GET['minutes']) ? (int) $_GET['minutes'] : 0;
$creditBal   = isset($_GET['balance']) && $_GET['balance'] !== '' ? (int) $_GET['balance'] : null;
$creditOn    = trim($_GET['claimed'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/feedbackForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Thank you</title>
    <style>
        body {
            background: #f8f9fa;
        }
        .thankyou-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .thankyou-icon {
            font-size: 5rem;
            color: #28a745;
        }
        .thankyou-message h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .thankyou-message p {
            font-size: 1.1rem;
            color: #6c757d;
        }
    </style>

</head>
<body>
<div class="container py-5">
    <div class="thankyou-container mx-auto col-md-8 col-lg-6">
        <div class="thankyou-message">
            <div class="thankyou-icon mb-4">
                ✅
            </div>
            <h1>Thank You!</h1>
            <p>Your submission has been received. We will get back to you shortly.</p>
            <?php if ($creditState === 'granted'): ?>
            <div class="alert alert-success mt-4 text-start">
                <strong><?php echo $creditMins ?: 30; ?> free minutes added.</strong>
                <?php if ($creditBal !== null): ?>
                Your balance is now <strong><?php echo $creditBal; ?> minutes</strong>.
                <?php endif; ?>
                We have emailed you the details. These are goodwill minutes and do not
                change your billing or cancellation date.
            </div>
            <?php elseif ($creditState === 'already_claimed'): ?>
            <div class="alert alert-info mt-4 text-start">
                Thank you for the feedback. This shop has already received its free
                minutes<?php echo $creditOn !== '' ? ' on ' . htmlspecialchars($creditOn) : ''; ?>,
                and the offer is one per shop.
            </div>
            <?php elseif ($creditState !== ''): ?>
            <div class="alert alert-info mt-4 text-start">
                Thank you for the feedback. We could not confirm your free minutes
                automatically, so our team will check and apply them for you.
            </div>
            <?php endif; ?>

            <a href="feedbackForm.php" class="btn btn-success mt-4">Back to Home</a>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>

</body>
</html>