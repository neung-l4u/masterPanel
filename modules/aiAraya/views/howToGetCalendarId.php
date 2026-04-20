<?php
session_start();
?>
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
    <title>How-To: Setting up Google Calendar & Getting Calendar ID</title>
    <style>
        * { font-family: 'Montserrat', sans-serif; color: #1a1a2e; }

        .howto-header {
            background: linear-gradient(135deg, #0d6efd 0%, #4f8cfd 50%, #80b1ff 100%);
            padding: 48px 40px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .howto-header::before {
            content: '';
            position: absolute;
            top: -40%; right: -20%;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .howto-header h1 {
            font-weight: 700;
            color: #fff;
            font-size: 1.6rem;
            position: relative;
            z-index: 1;
        }
        .howto-header p {
            color: rgba(255,255,255,0.85);
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
            margin-bottom: 0;
        }

        .howto-container {
            max-width: 800px;
            margin: 40px auto 60px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .howto-body {
            padding: 40px 44px 48px;
        }

        /* Video section */
        .video-section {
            background: #f8f9fb;
            border: 1.5px solid #eef0f4;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 36px;
        }
        .video-section h5 {
            font-weight: 700;
            font-size: 1rem;
            color: #0d6efd;
            margin-bottom: 12px;
        }
        .video-section p {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 16px;
        }
        .video-section .btn-video {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 10px 24px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .video-section .btn-video:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(13,110,253,0.3);
            color: #fff;
        }

        /* Steps */
        .step-section {
            margin-bottom: 32px;
            padding-bottom: 28px;
            border-bottom: 1px solid #eef0f4;
        }
        .step-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #0d6efd;
            color: #fff;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .step-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .step-title h5 {
            font-weight: 700;
            font-size: 1rem;
            color: #1a1a2e;
            margin: 0;
        }

        .step-content {
            padding-left: 48px;
            font-size: 0.88rem;
            line-height: 1.7;
            color: #444;
        }
        .step-content p {
            margin-bottom: 10px;
            color: #444;
        }

        .email-box {
            background: #f0f4ff;
            border: 1.5px solid #d6e2ff;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 12px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.78rem;
            color: #0d6efd;
            word-break: break-all;
            line-height: 1.8;
        }

        .permission-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eef3ff;
            color: #0d6efd;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 50px;
        }

        .info-callout {
            background: #fff8e6;
            border-left: 4px solid #ffc107;
            border-radius: 0 10px 10px 0;
            padding: 14px 18px;
            margin: 14px 0;
            font-size: 0.84rem;
            color: #665500;
        }
        .info-callout i { color: #ffc107; }

        .success-callout {
            background: #eafaf1;
            border-left: 4px solid #28a745;
            border-radius: 0 10px 10px 0;
            padding: 14px 18px;
            margin: 14px 0;
            font-size: 0.84rem;
            color: #1a5c2e;
        }
        .success-callout i { color: #28a745; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #0d6efd;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .back-link:hover { text-decoration: underline; }

        .sub-step {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
        }
        .sub-step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: #eef3ff;
            color: #0d6efd;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.72rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            .howto-container { margin: 20px 12px 40px; border-radius: 16px; }
            .howto-header { padding: 32px 24px 28px; }
            .howto-header h1 { font-size: 1.2rem; }
            .howto-body { padding: 28px 22px 32px; }
            .step-content { padding-left: 0; }
            .email-box { font-size: 0.7rem; }
        }
    </style>
</head>
<body style="background: #f4f6fa;">

<div class="container">
    <main>
        <div class="howto-container">

            <!-- Header -->
            <div class="howto-header">
                <h1><i class="bi bi-calendar3"></i> How-To: Setting up Google Calendar & Getting Calendar ID</h1>
                <p>Follow the steps below to set up your Google Calendar for AI Araya integration.</p>
            </div>

            <div class="howto-body">

                <a href="javascript:history.back()" class="back-link"><i class="bi bi-arrow-left"></i> Back to Form</a>

                <!-- Video Section -->
                <div class="video-section">
                    <h5><i class="bi bi-play-circle"></i> Video Guide</h5>
                    <p>Watch this video walkthrough to help you set up Google Calendar step by step. We recommend screen sharing with our team during this process.</p>
                    <video controls style="width: 100%; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.10);">
                        <source src="../assets/img/google-calendar-step/set-up-Google-Calendar-guide.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>

                <!-- Step 1 -->
                <div class="step-section">
                    <div class="step-title">
                        <span class="step-number">1</span>
                        <h5>Navigate to Google Calendar Settings</h5>
                    </div>
                    <div class="step-content">
                        <p>Open <a href="https://calendar.google.com" target="_blank">Google Calendar</a> in your browser and click the <strong>gear icon (⚙️)</strong> at the top right, then select <strong>"Settings"</strong>.</p>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step1.png" alt="Step 1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <div class="info-callout">
                            <i class="bi bi-info-circle"></i> We recommend asking the customer to <strong>share their screen</strong> during this process for easier guidance.
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-section">
                    <div class="step-title">
                        <span class="step-number">2</span>
                        <h5>Verify Time Zone</h5>
                    </div>
                    <div class="step-content">
                        <p>In the <strong>General</strong> settings, check that the <strong>Time zone</strong> matches the timezone you'll use in the AI Prompt configuration.</p>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step2.png" alt="Step 2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <div class="info-callout">
                            <i class="bi bi-info-circle"></i> Mismatched timezones can cause booking conflicts. Double-check this before proceeding.
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step-section">
                    <div class="step-title">
                        <span class="step-number">3</span>
                        <h5>Share Calendar with Required Accounts</h5>
                    </div>
                    <div class="step-content">
                        <p>In the left sidebar under <strong>"Settings for my calendars"</strong>, click on the <strong>shop's main calendar name</strong>.</p>
                        <p>Scroll down to the <strong>"Share with specific people or groups"</strong> section and click <strong>"+ Add people and groups"</strong>.</p>
                        <p>Add each of the following email addresses with the permission <span class="permission-badge"><i class="bi bi-pencil"></i> Make changes to events</span>:</p>
                        <div class="email-box">
                            calendar-api@lfy-ai-gcal-463906-b1.iam.gserviceaccount.com<br>
                            calendar-bot@gcalbookingapi.iam.gserviceaccount.com<br>
                            ailogins@localforyou.com
                        </div>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step3-1.png" alt="Step 3-1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <div style="margin-top: 8px;"><img src="../assets/img/google-calendar-step/step3-2.png" alt="Step 3-2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="step-section">
                    <div class="step-title">
                        <span class="step-number">4</span>
                        <h5>Get the Calendar ID</h5>
                    </div>
                    <div class="step-content">
                        <p>Still in the same calendar settings, scroll down to the <strong>"Integrate calendar"</strong> section.</p>
                        <p>You'll see a field labeled <strong>"Calendar ID"</strong> — copy this value.</p>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step4-1.png" alt="Step 4-1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <p style="margin-top: 12px;">Paste the Calendar ID into the <strong>Google Calendar ID</strong> field in the customer details form (or the Edit Shop section in the admin dashboard).</p>
                        <div style="margin-top: 8px;"><img src="../assets/img/google-calendar-step/step4-2.png" alt="Step 4-2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <div class="success-callout">
                            <i class="bi bi-check-circle"></i> The Calendar ID typically looks like: <strong>abc123xyz@group.calendar.google.com</strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
