<?php

$email = !empty($_GET['email']) ? $_GET['email'] : "noemail@localforyou.com;";
$token = !empty($_GET['code']) ? $_GET['code'] : "No Encode;";

$decode = decode_safe($token);

if(empty($decode)){ exit("Invalid Code"); }
?>

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
    <title>Reset Password</title>
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

        <!-- Left Panel: Form -->
        <div class="md:w-1/2 bg-[#F2F2F2] flex items-center justify-center p-8 md:p-12 order-2 md:order-1">
            <div class="w-full max-w-sm">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h2>
                <p class="text-sm text-gray-600 mb-8"><?php echo $email;?></p>

                <form>
                    <input type="hidden" id="idUser" value="<?php echo $decode;?>">

                    <!-- New Password -->
                    <div class="mb-5" id="newPassword">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5" for="inputNewPassword">New Password</label>
                        <div class="relative">
                            <input type="password" id="inputNewPassword"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00BCF4] focus:border-transparent transition"
                                   placeholder="Enter new password"
                                   autocomplete="off"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                        </div>
                        <small id="smallNewPassword" class="text-red-500 text-xs mt-1 hidden">Please enter a password.</small>
                        <small id="smallNewPasswordNotStrong" class="text-red-500 text-xs mt-1 hidden">Password must be at least 5 characters.</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6" id="confirmPassword">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5" for="inputConfirmPassword">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="inputConfirmPassword"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00BCF4] focus:border-transparent transition"
                                   placeholder="Confirm new password"
                                   autocomplete="off"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                        </div>
                        <small id="smallConfirmPasswordNull" class="text-red-500 text-xs mt-1 hidden">Please confirm your password.</small>
                        <small id="smallConfirmPasswordNotMatch" class="text-red-500 text-xs mt-1 hidden">Passwords do not match.</small>
                    </div>

                    <!-- Buttons -->
                    <div class="space-y-3">
                        <button type="button" id="resetPassword" onclick="updatePassword();" data-ga="click_reset_password" data-ga-label="Change Password"
                                class="w-full bg-gradient-to-r from-[#0619B6] to-[#00BCF4] hover:from-[#0514a0] hover:to-[#009dd4] text-white font-semibold py-2.5 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                            <span>Change Password</span>
                            <i class="fa-solid fa-key text-sm"></i>
                        </button>
                        <button type="button" id="backToLogin" onclick="login();" data-ga="click_back_to_login" data-ga-label="Back to Login"
                                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 rounded-lg transition-all duration-200">
                            Back to Login
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Panel: Image -->
        <div class="relative md:w-1/2 min-h-[200px] md:min-h-[540px] order-1 md:order-2">
            <img src="assets/img/forgot-password.jpg"
                 alt="reset password visual"
                 class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-b from-[#0619B6]/70 via-[#0361D1]/50 to-[#00BCF4]/60"></div>
            <div class="relative z-10 flex flex-col justify-center items-center h-full p-8 text-white text-center">
                <i class="fa-solid fa-shield-halved text-6xl mb-4 opacity-90"></i>
                <h2 class="text-3xl md:text-4xl font-bold leading-tight mb-2">Secure Reset</h2>
                <p class="text-sm text-white/70 max-w-xs leading-relaxed">
                    Create a strong password to keep your account safe and secure.
                </p>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="fixed bottom-3 text-center w-full text-xs text-white/40">
        Version 1.3.0 (26.06.2025)<br>
        &copy; 2017 Localforyou.com #1 Marketing Agency for Thai Restaurant &amp; Thai Massage
    </div>


<script src="assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="assets/js/settings.js?v=1.0.0"></script>
<script src="assets/js/date_format.js?v=1.0.0"></script>
<script src="assets/js/getUserAgent.js?v=1.0.0"></script>
<script src="assets/js/ajaxFunction.js?v=1.0.0"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="assets/js/authentication.js?v=1.0.0"></script>
<script>

    function login(){
        location.replace("https://report.localforyou.com/")
    }

    function updatePassword(){

        let inputNewPassword = $("#inputNewPassword").val();
        let inputConfirmNull = $("#inputConfirmPassword").val();
        let idUser = $("#idUser").val();

        // Hide all error messages
        $("#smallNewPassword, #smallNewPasswordNotStrong, #smallConfirmPasswordNull, #smallConfirmPasswordNotMatch").addClass("hidden");

        if(inputNewPassword === ""){
            $("#smallNewPassword").removeClass("hidden");
            $("#inputNewPassword").focus();
        }else if (inputNewPassword.length < 5){
            $("#smallNewPasswordNotStrong").removeClass("hidden");
            $("#inputNewPassword").focus();
        }else if (inputConfirmNull === ""){
            $("#smallConfirmPasswordNull").removeClass("hidden");
            $("#inputConfirmPassword").focus();
        }else if (inputNewPassword !== inputConfirmNull){
            $("#smallConfirmPasswordNotMatch").removeClass("hidden");
            $("#inputConfirmPassword").focus();
        }else{

            $.ajax({
                        url: "assets/php/actionResetPassword.php",
                        method: "POST",
                        dataType: "json",
                        data: {
                            mode: "resetPassword",
                            id: idUser,
                            password: inputNewPassword
                        }
                    })
                        .done(function(res) {
                            login();
                        })
                        .fail(function(xhr, status, error) {
                            alert("Failed to reset password.");
                            console.log("AJAX Error", status, error);
                        });

        }
    }

</script>
</body>
</html>

<?php
function encode_safe($id): string
{
    return base64_encode($id . '|L4U');
}

function decode_safe($encoded) {
    $decoded = base64_decode($encoded);
    list($id, $secret) = explode('|', $decoded);
    if ($secret === 'L4U') {
        return $id;
    }
    return false;
}

?>