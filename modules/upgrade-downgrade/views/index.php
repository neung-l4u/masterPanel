<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Upgrade / Downgrade - Local For You</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
            min-height: 100vh;
            margin: 0;
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, #0d6efd 0%, #4f8cfd 55%, #80b1ff 100%);
            padding: 64px 24px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -15%;
            width: 360px;
            height: 360px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -35%;
            left: -10%;
            width: 260px;
            height: 260px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-logo {
            width: 88px;
            height: 88px;
            background: #fff;
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.18);
            margin: 0 auto 24px;
            position: relative;
            z-index: 1;
            display: block;
        }

        .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .hero-sub {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.82);
            max-width: 460px;
            margin: 0 auto;
            line-height: 1.65;
            position: relative;
            z-index: 1;
        }

        /* ── Cards ── */
        .cards-section {
            max-width: 720px;
            margin: -40px auto 48px;
            padding: 0 16px;
            position: relative;
            z-index: 2;
        }

        .action-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1.5px solid transparent;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 36px rgba(0,0,0,0.13);
            color: inherit;
        }

        .action-card.upgrade:hover { border-color: #0d6efd; }
        .action-card.downgrade:hover { border-color: #dc3545; }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .card-icon.upgrade  { background: #e8f0fe; color: #0d6efd; }
        .card-icon.downgrade { background: #fdecea; color: #dc3545; }

        .card-title {
            font-size: 1.18rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-desc {
            font-size: 0.84rem;
            color: #6c757d;
            line-height: 1.6;
            flex: 1;
            margin-bottom: 24px;
        }

        .card-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
            align-self: flex-start;
        }

        .card-btn:hover { opacity: 0.88; }
        .card-btn.upgrade  { background: #0d6efd; color: #fff; }
        .card-btn.downgrade { background: #dc3545; color: #fff; }

        /* ── Divider pill ── */
        .or-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 600;
            color: #adb5bd;
            gap: 12px;
            margin: 4px 0;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e9ecef;
        }

        /* ── Entries link ── */
        .entries-link {
            text-align: center;
            padding-bottom: 48px;
        }

        .entries-link a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .84rem;
            font-weight: 600;
            color: #6c757d;
            text-decoration: none;
            padding: 9px 20px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            background: #fff;
            transition: border-color .15s, color .15s;
        }

        .entries-link a:hover {
            border-color: #1a1a2e;
            color: #1a1a2e;
        }

        /* ── Mode badges ── */
        .mode-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .74rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            text-decoration: none;
            border: 1.5px solid;
            transition: background .15s, color .15s;
        }

        .mode-badge.customer {
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .mode-badge.customer:hover {
            background: #0d6efd;
            color: #fff;
        }

        .mode-badge.staff {
            color: #198754;
            border-color: #198754;
        }

        .mode-badge.staff:hover {
            background: #198754;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- Hero -->
<div class="hero">
    <img src="../assets/img/L4U-Site-Icon.png" alt="Local For You" class="hero-logo">
    <h1 class="hero-title">Upgrade &amp; Downgrade</h1>
    <p class="hero-sub">Manage your Local For You subscription — upgrade your plan, add on services, or submit a downgrade / unsubscription request.</p>
</div>

<!-- Cards -->
<div class="cards-section">
    <div class="row g-3">

        <!-- Upgrade -->
        <div class="col-md-6">
            <div class="action-card upgrade">
                <div class="card-icon upgrade"><i class="bi bi-arrow-up-circle-fill"></i></div>
                <div class="card-title">Upgrade / Add-on</div>
                <p class="card-desc">Upgrade your current package or add new services — social media, SMS marketing, ARAYA, and more.</p>
                <div class="mode-row mb-3">
                    <a href="upgradeForm.php?mode=customer" class="mode-badge customer"><i class="bi bi-person"></i> Customer</a>
                    <a href="upgradeForm.php?mode=staff" class="mode-badge staff"><i class="bi bi-headset"></i> Staff</a>
                </div>
                <a href="upgradeForm.php" class="card-btn upgrade"><i class="bi bi-box-arrow-up-right"></i> Open Form</a>
            </div>
        </div>

        <!-- Downgrade -->
        <div class="col-md-6">
            <div class="action-card downgrade">
                <div class="card-icon downgrade"><i class="bi bi-arrow-down-circle-fill"></i></div>
                <div class="card-title">Downgrade / Unsubscribe</div>
                <p class="card-desc">Request a plan downgrade, switch to a lower tier, or submit an unsubscription request for your services.</p>
                <div class="mode-row mb-3">
                    <a href="downgradeForm.php?mode=customer" class="mode-badge customer"><i class="bi bi-person"></i> Customer</a>
                    <a href="downgradeForm.php?mode=staff" class="mode-badge staff"><i class="bi bi-headset"></i> Staff</a>
                </div>
                <a href="downgradeForm.php" class="card-btn downgrade"><i class="bi bi-box-arrow-down-right"></i> Open Form</a>
            </div>
        </div>

    </div>

    <div class="or-divider mt-4">or</div>

    <div class="entries-link mt-4">
        <a href="entries.php"><i class="bi bi-table"></i> View Submission Entries</a>
    </div>
</div>

<?php include '../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
