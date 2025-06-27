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
    </script>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css'>
    <link rel='stylesheet' href="assets/css/login_form.css?v=1.0.1">
    <script src="https://kit.fontawesome.com/9c38e6ba4e.js" crossorigin="anonymous"></script>
</head>
<body>
<main>
    <section class="vh-75 py-5" style="background-color: white;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">

                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form>
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <span class="h3 fw-light mb-0">Reset New Password</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">
                                            <?php echo $email;?>
                                        </h5>

                                        <div class="form-outline mb-2" id="newPassword">
                                            <label class="form-label" for="inputNewPassword">New Password
                                                <small id="smallNewPassword" class="text-danger" style="display: none;">Please check the message.</small>
                                                <small id="smallNewPasswordNotStrong" class="text-danger" style="display: none;">Password not Strong.</small>

                                            </label>
                                            <div class="input-group mb-3">
                                                <input type="text" id="inputNewPassword"
                                                       class="form-control form-control-lg"
                                                       placeholder="New Password"
                                                       autocomplete="off"
                                                       value="">
                                                <input type="hidden" id="idUser" value="<?php echo $decode;?>">
                                            </div>
                                        </div>
                                        <div class="form-outline mb-2" id="confirmPassword">
                                            <label class="form-label" for="inputConfirmPassword">Confirm New Password*
                                                <small id="smallConfirmPasswordNull" class="text-danger" style="display: none;">Please check the Password.</small><!-- Null Massage-->
                                                <small id="smallConfirmPasswordNotMatch" class="text-danger" style="display: none;">Please check the Password no match.</small><!-- Not Match-->
                                            </label>
                                            <div class="input-group mb-3">
                                                <input type="text" id="inputConfirmPassword"
                                                       class="form-control form-control-lg"
                                                       placeholder="Confirm New Password"
                                                       autocomplete="off"
                                                       value="">
                                            </div>
                                        </div>
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-muted btn-lg btn-block w-10 rounded-pill" type="button" id="backToLogin" onclick="login();">
                                                <span class="d-flex justify-content-between align-items-center pr-5">
                                                    <span class="w-10 text-right">
                                                        Back to Login
                                                    </span>
                                                </span>
                                            </button>
                                            <button class="btn btn-secondary btn-lg btn-block w-10 rounded-pill" type="button" id="resetPassword" onclick="updatePassword();">
                                                <span class="d-flex justify-content-between align-items-center pr-5">
                                                    <span class="w-10 text-right">
                                                        Change Password
                                                    </span>
                                                </span>
                                            </button>

                                        </div>

                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="assets/img/forgot-password.jpg"
                                     alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<footer class="credit">
    Version 1.3.0 (26.06.2025)<br>
    © 2017 Localforyou.com #1 Marketing Agency for Thai Restaurant & Thai Massage
</footer>
<script src="assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="assets/js/settings.js?v=1.0.0"></script>
<script src="assets/js/date_format.js?v=1.0.0"></script>
<script src="assets/js/getUserAgent.js?v=1.0.0"></script>
<script src="assets/js/ajaxFunction.js?v=1.0.0"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="https://unpkg.com/@popperjs/core@2.11.5/dist/umd/popper.min.js"></script>
<script src="assets/js/authentication.js?v=1.0.0"></script>
<script>

    /*$( document ).ready(function() {
        $("#smallNewPassword").hide();
        $("#smallNewPasswordNotStrong").hide();
        $("#smallConfirmPasswordNull").hide();
        $("#smallConfirmPasswordNotMatch").hide();

    });//ready*/

    function login(){
        location.replace("https://report.localforyou.com/")
    }

    function updatePassword(){

        let inputNewPassword = $("#inputNewPassword").val();
        let inputConfirmNull = $("#inputConfirmPassword").val();
        let idUser = $("#idUser").val();


        if(inputNewPassword === ""){
            $("#smallNewPassword").show();
            $("#smallNewPasswordNotStrong").hide();
            $("#smallConfirmPasswordNull").hide();
            $("#smallConfirmPasswordNotMatch").hide();
            $("#inputNewPassword").focus();
        }else if (inputNewPassword.length < 5){
            $("#smallNewPassword").hide();
            $("#smallConfirmPasswordNull").hide();
            $("#smallConfirmPasswordNotMatch").hide();
            $("#smallNewPasswordNotStrong").show();
            $("#inputNewPassword").focus();
        }else if (inputConfirmNull === ""){
            $("#smallNewPassword").hide();
            $("#smallNewPasswordNotStrong").hide();
            $("#smallConfirmPasswordNull").show();
            $("#smallConfirmPasswordNotMatch").hide();
            $("#inputConfirmPassword").focus();
        }else if (inputNewPassword !== inputConfirmNull){
            $("#smallConfirmPasswordNotMatch").show();
            $("#smallConfirmPasswordNull").hide();
            $("#smallNewPassword").hide();
            $("#smallNewPasswordNotStrong").hide();
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
                                // alert("Password reset successfully. Please login with new password.");

                        })
                        .fail(function(xhr, status, error) {
                            alert("Failed to send email.");
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