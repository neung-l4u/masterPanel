<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription unsubscribed</title>
    <style>
        /* --- CSS Styles --- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap'); 

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa; 
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #333;
        }

        .container {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            box-sizing: border-box; 
        }
        
                .containerg {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            box-sizing: border-box; 
            text-align: center;
        }

        .logo {
            display: block;
            margin: 0 auto 10px auto;
            width: 90px; 
        }


        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 0;
            margin-bottom: 16px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }

        p.support-email {
            font-size: 15px;
        }

        a {
            color: #0d6efd; 
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        hr {
            border: 0;
            height: 1px;
            background-color: #e0e0e0;
            margin: 32px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .info-table td {
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 15px;
        }

        .info-table td:first-child {
            font-weight: 700;
            background-color: #f9f9f9;
            width: 130px; 
            color: #333;
        }

        .shop-info p {
            font-size: 15px;
            margin: 6px 0;
        }

        .shop-info strong {
            color: #1a1a1a;
            min-width: 140px;
            display: inline-block; 
        }

        .cta-section {
            margin-top: 24px;
        }

        .cta-section h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        
        .btn-container {
            text-align: center; 
            margin: 24px 0;
        }

        .btn {
            background-color: #0d6efd;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            min-width: 200px; 
            box-sizing: border-box;
            text-align: center;
        }

        .btn:hover {
            background-color: #0b5ed7;
            text-decoration: none;
            color: #ffffff;
        }

        .closing {
            font-size: 15px;
            line-height: 1.5;
        }

        /* --- Footer Styles --- */
        .footer {
            text-align: center;
            margin-top: 30px;
            width: 100%;
            max-width: 600px;
        }

        .social-icons a {
            font-size: 20px;
            margin: 0 12px;
            color: #888;
            font-weight: 500;
        }

        .social-icons a:hover {
            color: #333;
            text-decoration: none;
        }

        .footer p {
            font-size: 13px;
            color: #888;
            margin-bottom: 8px;
        }

        .footer-links {
            font-size: 13px;
            color: #888;
        }

        .footer-links strong {
            color: #555;
        }
        
        .footer-links a {
            color: #555;
            margin: 0 5px;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <img src="https://report.localforyou.com/modules/signup/assets/img/newL4U-logo-100x100-2.png" alt="Local For You Logo" class="logo">

        <h1>You’ve unsubscribed.</h1>

        <p class="support-email">We're sorry to see you go, but we truly appreciate the time you've been with us.
            <br><br>
            If you have any questions regarding your unsubscribed or final schedule, please reach out to our support team anytime at <a href="mailto:admin@localforyou.com"><b>admin@localforyou.com</b></a>
        </p>

        <hr>

        <table class="info-table">
            <tbody>
                <tr>
                    <td>Name</td>
                    <td>{{1.first_name}} {{1.last_name}}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{1.email}}</td>
                </tr>
                <tr>
                    <td>Mobile</td>
                    <td>{{1.ownerMobile}}</td>
                </tr>
                <tr>
                    <td>Shop Name</td>
                    <td>{{1.shopName}}</td>
                </tr>
                <tr>
                    <td>Trading Name</td>
                    <td>{{1.tradingName}}</td>
                </tr>
                <tr>
                    <td>Country</td>
                    <td>{{1.country}}</td>
                </tr>
                <tr>
                    <td>Reason</td>
                    <td>{{ifempty(1.reason + 1.other; "-")}}</td>
                </tr>
                <tr>
                    <td>Last Service Date</td>
                    <td>{{1.lastDate}}</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <div class="cta-section">
            <h3>Changed your mind?</h3>
            <p>If this request was made by mistake or you’d like to rejoin our service, we’ll be happy to help you reactivate your account.</p>
        </div>

        <div class="btn-container">
            <a href="https://localforyou.com/en/contact-us-en" class="btn">Contact Support</a>
        </div>

        <p class="closing">
            Thank you once again for trusting Local For You.
            <br>
            We wish you and your team all the best in your next chapter.
        </p>

        <p class="closing">
            Warm regards,<br>
            Local For You Team
        </p>

    </div>
    
    <div class="containerg">
        <p><b>Need help?</b> Contact us</p>
        <div class="social-icons">
         <a href="https://facebook.com/localforyou/"><img src="https://cdn-icons-png.flaticon.com/512/733/733604.png" width="20px"></a>
         <a href="https://line.me/R/ti/p/%40238pwmel"><img src="https://cdn-icons-png.flaticon.com/512/2111/2111527.png" width="20px"></a>
         <a href="https://wa.me/447418313742"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384023.png" width="20 px"></a>
         <a href="https://www.instagram.com/local_for_you_/"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384031.png" width="20px"></a>
         <a href="https://www.youtube.com/channel/UC8ijHDLwbLg5LXFc9ucajBA/"><img src="https://cdn-icons-png.flaticon.com/512/1077/1077046.png" width="20px"></a>
         <a href="https://www.tiktok.com/@thaibizbuddies"><img src="https://cdn-icons-png.flaticon.com/512/3046/3046120.png" width="20px"></a>
        </div>
        
        <p class="footer-links">
            <a href="https://localforyou.com/en/privacy-policy-en/">privacy policy</a> | 
            <a href="https://localforyou.com/en/terms-and-conditions-en/">terms of service</a>
        </p>
    </div>

</body>
</html>


