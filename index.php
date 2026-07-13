<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
        document.addEventListener('click', function(e) {
            var el = e.target.closest('[data-ga]');
            if (el) {
                gtag('event', el.getAttribute('data-ga'), {
                    event_category: el.getAttribute('data-ga-category') || 'button',
                    event_label: el.getAttribute('data-ga-label') || el.textContent.trim().substring(0, 50)
                });
            }
        });
    </script>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="assets/img/logo-login2.png?v=2">
    <link rel="icon" type="image/png" href="assets/img/logo-login2.png?v=2">
    <title>Master Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Montserrat', 'sans-serif'] }
                }
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/9c38e6ba4e.js" crossorigin="anonymous"></script>
    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-Light.ttf') format('truetype');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-Regular.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-Medium.ttf') format('truetype');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-SemiBold.ttf') format('truetype');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-Bold.ttf') format('truetype');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Montserrat';
            src: url('assets/font/Montserrat-ExtraBold.ttf') format('truetype');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }

        input::placeholder { opacity: 0.5; color: #9ca3af; font-size: 0.875rem; }

        
    </style>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5P9NGXT2');</script>
    <!-- End Google Tag Manager -->
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0619B6] via-[#0361D1] to-[#00BCF4] p-4">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5P9NGXT2"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Animated Vector Background -->


    <div class="relative z-10 w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex flex-col md:flex-row">

        <!-- Left Panel: Image + Welcome -->
        <div class="relative md:w-1/2 min-h-[200px] md:min-h-[540px] hidden md:block">
            <img src="assets/img/page-login.jpg"
                 alt="login visual"
                 class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-b from-[#0619B6]/70 via-[#0361D1]/50 to-[#00BCF4]/60"></div>
            <div class="relative z-10 flex flex-col justify-between h-full p-8 text-white">
                <div class="flex items-center gap-2">
                    <img src="assets/img/logo-login2.png" alt="logo" class="w-8 h-6" />
                    <span class="text-lg font-semibold tracking-wide">L4U Master Panels</span>
                </div>
                <div class="mb-8">
                    <h2 class="text-3xl md:text-4xl font-semibold leading-tight">Welcome!</h2>
                    <h3 class="text-xl md:text-2xl font-light mt-1">To Master Panel System.</h3>
                    <p class="text-sm text-white/70 mt-4 max-w-xs leading-relaxed">
                        Our back-office system handles it all, because simplicity is our priority.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Sign In Form -->
        <div class="md:w-1/2 bg-[#F2F2F2] flex items-center justify-center p-8 md:p-12">
            <div class="w-full max-w-sm">

                <!-- Mobile Logo -->
                <div class="flex items-center gap-2 mb-4 md:hidden">
                    <img src="assets/img/logo-login2.png" alt="logo" class="w-7 h-7" />
                    <span class="text-base font-semibold text-gray-800">L4U Master Panels</span>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-8">Sign In</h2>

                <form>
                    <!-- Email -->
                    <div class="mb-5" id="formEmail">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5" for="formUser">Email or Mobile</label>
                        <div class="relative">
                            <input type="email" id="formUser"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00BCF4] focus:border-transparent transition"
                                   placeholder="mail@localforyou.com | 0891234567"
                                   autocomplete="off"
                                   value="<?php echo htmlspecialchars($_COOKIE['user'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-envelope text-sm"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4" id="oldPassword">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5" for="formPassword">Password</label>
                        <div class="relative">
                            <input type="password" id="formPassword"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00BCF4] focus:border-transparent transition"
                                   placeholder="your password here"
                                   autocomplete="off"
                                   value="<?php echo htmlspecialchars($_COOKIE['pass'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            />
                            <a href="#" id="linkViewPassword" onclick="showPass(); return false;"
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition no-underline">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Remember me & Forgot -->
                    <div class="flex items-center justify-between mb-6 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer text-gray-600" id="checkBoxTik">
                            <input type="checkbox" value="true" id="formRemember"
                                   class="w-4 h-4 rounded border-gray-300 text-[#0619B6] focus:ring-[#00BCF4] accent-[#0619B6]" checked />
                            Remember me
                        </label>
                        <a href="sendEmailForgotToMake.php" id="linkForgotPassword"
                           class="text-[#0361D1] hover:text-[#00BCF4] font-medium transition">Forgot password?</a>
                    </div>

                    <!-- Sign In Button -->
                    <button type="button" id="cmdLogin" data-ga="click_login" data-ga-label="Sign In"
                            class="w-full bg-gradient-to-r from-[#0619B6] to-[#00BCF4] hover:from-[#0514a0] hover:to-[#009dd4] text-white font-semibold py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <span>Sign In</span>
                        <!-- <i class="fa-solid fa-fingerprint text-sm"></i> -->
                    </button>

                    <!-- Result Text -->
                    <div class="mt-4 text-center text-sm min-h-[24px]" id="resultText">
                        <small>&nbsp;</small>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="fixed bottom-3 text-center w-full text-xs text-white/40">
        Version 2.0.0 (10.03.2026)<br>
        &copy; 2026 Localforyou.com #1 Marketing Agency for Thai Restaurant &amp; Thai Massage
    </div>

<script src="assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="assets/js/settings.js?v=1.0.0"></script>
<script src="assets/js/date_format.js?v=1.0.0"></script>
<script src="assets/js/getUserAgent.js?v=1.0.0"></script>
<script src="assets/js/ajaxFunction.js?v=1.0.0"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="assets/js/authentication.js?v=1.0.0"></script>
<script>

    function showPass() {
        let input = $("#formPassword");
        input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password');
    }

</script>
</body>
</html>