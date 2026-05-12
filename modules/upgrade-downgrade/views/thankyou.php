<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Thank you - Araya AI</title>
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Montserrat', sans-serif;
            color: #1a1a2e;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .thankyou-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .thankyou-card {
            max-width: 600px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
            text-align: center;
        }

        .thankyou-header {
            background: linear-gradient(135deg, #0d6efd 0%, #4f8cfd 50%, #80b1ff 100%);
            padding: 44px 40px 36px;
            position: relative;
            overflow: hidden;
        }

        .thankyou-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 260px;
            height: 260px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .thankyou-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        .thankyou-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(4px);
            border: 2px solid rgba(255,255,255,0.3);
        }

        .thankyou-icon i {
            font-size: 2.4rem;
            color: #fff;
        }

        .thankyou-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            position: relative;
            z-index: 1;
            letter-spacing: 0.5px;
        }

        .thankyou-body {
            padding: 36px 40px 40px;
        }

        .thankyou-body p {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.75;
            margin-bottom: 12px;
        }

        .thankyou-body p:last-of-type {
            margin-bottom: 0;
        }

        .thankyou-body strong {
            color: #0d6efd;
            font-weight: 600;
        }

        .highlight-box {
            background: #eef3ff;
            border: 1.5px solid #d4e0fd;
            border-radius: 12px;
            padding: 18px 22px;
            margin: 24px 0;
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
        }

        .highlight-box i {
            font-size: 1.6rem;
            color: #0d6efd;
            flex-shrink: 0;
        }

        .highlight-box span {
            font-size: 0.88rem;
            color: #333;
            line-height: 1.6;
        }

        .btn-back {
            background: linear-gradient(135deg, #0d6efd, #4f8cfd);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 36px;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(13,110,253,0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 28px;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #0b5ed7, #3d7cf0);
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(13,110,253,0.4);
            color: #fff;
        }

        .btn-back:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .thankyou-header {
                padding: 32px 24px 28px;
            }
            .thankyou-body {
                padding: 28px 22px 32px;
            }
            .thankyou-header h1 {
                font-size: 1.6rem;
            }
            .highlight-box {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>

</head>
<body>

<div class="thankyou-wrapper">
    <div class="thankyou-card">
        <div class="thankyou-header">
            <div class="thankyou-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1>Thank You!</h1>
        </div>
        <div class="thankyou-body">
            <p>Your information has been successfully received! Our team is now beginning the setup of your <strong>Araya AI Receptionist</strong> right away.</p>

            <div class="highlight-box">
                <i class="bi bi-clock-history"></i>
                <span>The system will be ready within <strong>48 hours</strong>. We will contact you to confirm once the system is fully online and operational.</span>
            </div>

            <p>Thank you for trusting and choosing <strong>Araya AI</strong> services.</p>

            <a href="customerDetailsForm.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Form</a>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>

</body>
</html>