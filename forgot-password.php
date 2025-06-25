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
    <style>
        #linkViewPassword{
            text-decoration: none;
        }
        #formUser, #formPassword{
            font-size: 0.8em;
        }
    </style>
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
                                        <h5 class="fw-normal mb-3 pb-3 text-uppercase" style="letter-spacing: 1px;">
                                            Email :
                                        </h5>

                                        <div class="form-outline mb-2" id="formPassword">
                                            <label class="form-label" for="formPassword">New Password</label>
                                            <div class="input-group mb-3">
                                                <input type="password" id="formPassword"
                                                       class="form-control form-control-lg"
                                                       placeholder="New Password"
                                                       autocomplete="off"
                                                       value="">
                                                <span class="input-group-text" id="basic-addon2">
                                                    <a href="#" id="linkViewPassword" onclick="showPass()"><i class="fa-solid fa-eye"></i></a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="form-outline mb-2" id="formPassword">
                                            <label class="form-label" for="formPassword">Confirm New Password*</label>
                                            <div class="input-group mb-3">
                                                <input type="password" id="formPassword"
                                                       class="form-control form-control-lg"
                                                       placeholder="Confirm New Password"
                                                       autocomplete="off"
                                                       value="">
                                                <span class="input-group-text" id="basic-addon2">
                                                    <a href="#" id="linkViewPassword" onclick="showPass()"><i class="fa-solid fa-eye"></i></a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-muted btn-lg btn-block w-10 rounded-pill" type="button" id="cmdLogin">
                                                <span class="d-flex justify-content-between align-items-center pr-5">
                                                    <span class="w-10 text-right">
                                                        Cancel
                                                    </span>
                                                </span>
                                            </button>
                                            <button class="btn btn-secondary btn-lg btn-block w-10 rounded-pill" type="button" id="cmdLogin">
                                                <span class="d-flex justify-content-between align-items-center pr-5">
                                                    <span class="w-10 text-right">
                                                        Change Password
                                                    </span>
                                                </span>
                                            </button>

                                        </div>

                                        <div class="pt-1" id="resultText">
                                            <small>&nbsp;</small>
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
    Version 1.2.3 (16.06.2025)<br>
    © 2017 Localforyou.com #1 Marketing Agency for Thai Restaurant & Thai Massage
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<script src="assets/js/settings.js?v=1.0.0"></script>
<script src="assets/js/date_format.js?v=1.0.0"></script>
<script src="assets/js/getUserAgent.js?v=1.0.0"></script>
<script src="assets/js/ajaxFunction.js?v=1.0.0"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="https://unpkg.com/@popperjs/core@2.11.5/dist/umd/popper.min.js"></script>
<script src="assets/js/authentication.js?v=1.0.0"></script>
<script>



    function showPass() {
        let input = $("#formPassword");
        input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password');
    }


     function checkEmail (){
        alert("check email");

         const email = $("#inputForgotEmail").val();
         const action = "checkEmail";

         $.ajax({
             url: "api/masterpanel/login/actionCheckPassword.php",
             method: "POST",
             dataType: "json",
             data: {
                 act: action,
                 email: email
             }
         })
             .done(function(res) {
                 console.log("Response:", res);
                 if (res.status === "found") {
                     $("#noEmail").hide();
                     $("#resultText").html(`<small class="text-success">Email Correct: ${res.sEmail}</small>`);


                     $Email = res.sEmail;

                 } else {
                     $("#noEmail").show();
                 }



             })
             .fail(function(xhr, status, error) {
                 alert("Failed to send email.");
                 console.log("AJAX Error", status, error);
             });
     }


</script>
</body>
</html>