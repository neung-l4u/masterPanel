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
    </script>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Password - Master Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Open Runde', 'sans-serif'] }
                }
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/9c38e6ba4e.js" crossorigin="anonymous"></script>
    <style>
        @font-face {
            font-family: 'Open Runde';
            src: url('assets/font/OpenRunde-Regular.woff2') format('woff2'),
                 url('assets/font/OpenRunde-Regular.woff') format('woff');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Open Runde';
            src: url('assets/font/OpenRunde-Medium.woff2') format('woff2'),
                 url('assets/font/OpenRunde-Medium.woff') format('woff');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Open Runde';
            src: url('assets/font/OpenRunde-Semibold.woff2') format('woff2'),
                 url('assets/font/OpenRunde-Semibold.woff') format('woff');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Open Runde';
            src: url('assets/font/OpenRunde-Bold.woff2') format('woff2'),
                 url('assets/font/OpenRunde-Bold.woff') format('woff');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        input::placeholder { opacity: 0.5; color: #9ca3af; font-size: 0.875rem; }

        .bg-animated { position: fixed; inset: 0; overflow: hidden; z-index: 0; }
        .bg-animated li {
            position: absolute;
            list-style: none;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(1px);
            animation: floatUp linear infinite;
            bottom: -160px;
        }
        .bg-animated li:nth-child(1)  { left:  3%;  width: 80px;  height: 80px;  animation-duration: 22s; animation-delay: 0s;   border-radius: 50%; }
        .bg-animated li:nth-child(2)  { left: 13%;  width: 30px;  height: 30px;  animation-duration: 14s; animation-delay: 2s;   border-radius: 4px; }
        .bg-animated li:nth-child(3)  { left: 23%;  width: 100px; height: 100px; animation-duration: 26s; animation-delay: 4s;   border-radius: 50%; }
        .bg-animated li:nth-child(4)  { left: 35%;  width: 50px;  height: 50px;  animation-duration: 18s; animation-delay: 0s;   border-radius: 8px; }
        .bg-animated li:nth-child(5)  { left: 48%;  width: 40px;  height: 40px;  animation-duration: 20s; animation-delay: 3s;   border-radius: 50%; }
        .bg-animated li:nth-child(6)  { left: 58%;  width: 110px; height: 110px; animation-duration: 28s; animation-delay: 7s;   border-radius: 50%; }
        .bg-animated li:nth-child(7)  { left: 68%;  width: 60px;  height: 60px;  animation-duration: 16s; animation-delay: 1s;   border-radius: 4px; }
        .bg-animated li:nth-child(8)  { left: 78%;  width: 25px;  height: 25px;  animation-duration: 24s; animation-delay: 5s;   border-radius: 50%; }
        .bg-animated li:nth-child(9)  { left: 87%;  width: 70px;  height: 70px;  animation-duration: 30s; animation-delay: 2s;   border-radius: 12px; }
        .bg-animated li:nth-child(10) { left: 95%;  width: 45px;  height: 45px;  animation-duration: 19s; animation-delay: 8s;   border-radius: 50%; }

        @keyframes floatUp {
            0%   { transform: translateY(0) rotate(0deg);   opacity: 0.5; }
            50%  { opacity: 0.3; }
            100% { transform: translateY(-110vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0619B6] via-[#0361D1] to-[#00BCF4] p-4">

    <!-- Animated Vector Background -->
    <ul class="bg-animated">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="relative z-10 w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex flex-col md:flex-row">

        <!-- Left Panel: Image -->
        <div class="relative md:w-1/2 min-h-[200px] md:min-h-[540px] hidden md:block">
            <img src="assets/img/forgot-password-new.jpg"
                 alt="forgot password visual"
                 class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-b from-[#0619B6]/70 via-[#0361D1]/50 to-[#00BCF4]/60"></div>
            <div class="relative z-10 flex flex-col justify-center items-center h-full p-8 text-white text-center">
                <i class="fa-solid fa-envelope text-6xl mb-4 opacity-90"></i>
                <h2 class="text-3xl md:text-4xl font-bold leading-tight mb-2">Forgot Password?</h2>
                <p class="text-sm text-white/70 max-w-xs leading-relaxed">
                    No worries! Enter your email and we'll send you reset instructions.
                </p>
            </div>
        </div>

        <!-- Right Panel: Form -->
        <div class="md:w-1/2 bg-[#F2F2F2] flex items-center justify-center p-8 md:p-12">
            <div class="w-full max-w-sm">

                <!-- Mobile Logo -->
                <div class="flex items-center gap-2 mb-4 md:hidden">
                    <img src="assets/img/logo-login2.png" alt="logo" class="w-7 h-7" />
                    <span class="text-base font-semibold text-gray-800">L4U Master Panels</span>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-2">Forgot Password</h2>
                <p class="text-sm text-gray-600 mb-8">Enter your email to receive reset instructions</p>

                <form>
                    <!-- Email Input Form -->
                    <div id="formForgotPassword">
                        <div class="mb-5" id="formForgotEmail">
                            <label class="block text-sm font-medium text-gray-600 mb-1.5" for="inputForgotEmail">Email Address</label>
                            <div class="relative">
                                <input type="email" id="inputForgotEmail"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00BCF4] focus:border-transparent transition"
                                       placeholder="mail@localforyou.com"
                                       autocomplete="off"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fa-solid fa-envelope text-sm"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        <div class="mb-4 space-y-2">
                            <div id="dangerEmail" class="hidden text-red-500 text-xs flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Please enter your email</span>
                            </div>
                            <div id="invalidEmailFormat" class="hidden text-red-500 text-xs flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Invalid email format</span>
                            </div>
                            <div id="emailNotFound" class="hidden text-red-500 text-xs flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>This user was not found</span>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="space-y-3">
                            <button type="button" id="sendEmail" onclick="checkEmail();"
                                    class="w-full bg-gradient-to-r from-[#0619B6] to-[#00BCF4] hover:from-[#0514a0] hover:to-[#009dd4] text-white font-semibold py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span>Check Email</span>
                                <i class="fa-solid fa-paper-plane text-sm"></i>
                            </button>
                            <a href="index.php" id="linkForgotPassword"
                               class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 rounded-lg transition-all duration-200 text-center">
                                Back to Login
                            </a>
                        </div>
                    </div>

                    <!-- Success Message (Hidden by default) -->
                    <div id="afterCheckEmail" class="hidden text-center">
                        <div class="mb-6">
                            <i class="fa-solid fa-circle-check text-6xl text-green-500 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Check Your Email</h3>
                            <p class="text-sm text-gray-600">We've sent password reset instructions to your email address.</p>
                        </div>
                        <a href="index.php" id="afterBackLogin"
                           class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 px-8 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                            Back to Login
                        </a>
                    </div>

                    <!-- Loading Spinner (Hidden by default) -->
                    <div id="loading" class="hidden flex justify-center items-center py-8">
                        <img src="assets/img/loadingSpin.gif" alt="Loading..." class="w-24 h-24" />
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="fixed bottom-3 text-center w-full text-xs text-white/40">
        Version 1.3.0 (26.06.2025)<br>
        &copy; 2017 Localforyou.com #1 Marketing Agency for Thai Restaurant &amp; Thai Massage
    </div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="assets/js/settings.js?v=1.0.0"></script>
<script src="assets/js/date_format.js?v=1.0.0"></script>
<script src="assets/js/getUserAgent.js?v=1.0.0"></script>
<script src="assets/js/ajaxFunction.js?v=1.0.0"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script>

    $(document).ready(function() {
        $("#sendEmail").prop("disabled", true);

        $("#inputForgotEmail").on("input", function() {
            const email = $(this).val().trim();
            const checkEmailButton = $("#sendEmail");

            $("#dangerEmail, #invalidEmailFormat, #emailNotFound").addClass("hidden");

            if (email.length > 0 && isValidEmail(email)) {
                checkEmailButton.prop("disabled", false);
            } else {
                checkEmailButton.prop("disabled", true);
            }
        });
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function checkEmail (){
        const email = $("#inputForgotEmail").val().trim();
        const action = "checkEmail";
        const form = $("#formForgotPassword");
        const afterCheck = $("#afterCheckEmail");
        const loading = $("#loading");

        $("#dangerEmail, #invalidEmailFormat, #emailNotFound").addClass("hidden");

        if (email === "" || email === null || email === undefined){
            $("#dangerEmail").removeClass("hidden");
            return;
        }

        if (!isValidEmail(email)) {
            $("#invalidEmailFormat").removeClass("hidden");
            return;
        }

        $.ajax({
            url: "assets/php/actionCheckPassword.php",
            method: "POST",
            dataType: "json",
            data: {
                act: action,
                email: email
            }
        })
        .done(function(res) {
            console.log("Response:", res);
            if (res.status === "Correct") {
                form.addClass("hidden");
                loading.removeClass("hidden");

                setTimeout(function() {
                    loading.addClass("hidden");
                    afterCheck.removeClass("hidden");
                }, 2000);

                sendEncode(res);

            }else if (res.status === "not_found"){
                $("#emailNotFound").removeClass("hidden");
            }
        })
        .fail(function(xhr, status, error) {
            alert("Failed to send email. Please try again.");
            console.log("AJAX Error", status, error);
        });
    }

    function sendEncode(res){
        $.ajax({
            url: "https://hook.us1.make.com/63snt17f21kzx9hapt9d5lrcdb54hqbi",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                email: res.email,
                encode: res.en,
            }
        })
        .done(function(res) {
            console.log("ajax Send to Make Done");
        })
        .fail(function(xhr, status, error) {
            console.log("ajax Send to Make fail!!");
            console.log(status + ': ' + error);
        });
    }

</script>
</body>
</html>