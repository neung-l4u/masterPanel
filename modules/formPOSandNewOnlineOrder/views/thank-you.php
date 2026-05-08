<?php
?>
<!doctype html>
<html lang="en">
<head>
    <title>Thank You - Local For You</title>
    <?php include "form_header.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #eef6ff 0%, #ffffff 55%, #f4fff8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .thank-you-card {
            width: 100%;
            max-width: 680px;
            padding: 44px 34px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 24px 70px rgba(23, 43, 77, 0.12);
            text-align: center;
            border: 1px solid rgba(13, 110, 253, 0.1);
        }
        .thank-you-icon {
            width: 92px;
            height: 92px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #198754;
            background: rgba(25, 135, 84, 0.1);
            font-size: 48px;
        }
        .thank-you-card h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #172b4d;
            margin-bottom: 12px;
        }
        .thank-you-card p {
            color: #5f6b7a;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 0;
        }
        .thank-you-meta {
            margin-top: 24px;
            padding: 16px;
            border-radius: 14px;
            background: #f7faff;
            color: #42526e;
            font-size: 0.92rem;
        }
        .brand-link {
            display: inline-block;
            margin-top: 28px;
            color: #0d6efd;
            font-weight: 600;
            text-decoration: none;
        }
        .brand-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main class="thank-you-card">
        <div class="thank-you-icon">
            <i class="bi bi-check2-circle"></i>
        </div>
        <h1>Thank You!</h1>
        <p>Your POS &amp; Online Order onboarding form has been submitted successfully.</p>
        <p>Our team will review your information and contact you if anything else is required.</p>
        <div class="thank-you-meta">
            You may now close this page.
        </div>
        <a class="brand-link" href="https://www.localforyou.com" target="_blank">Local For You</a>
    </main>
</body>
</html>
