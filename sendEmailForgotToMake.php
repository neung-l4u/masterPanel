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
    <title>Master Panel</title>
    <link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css'>
    <link rel='stylesheet' href="assets/css/login_form.css?v=1.0.0">
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
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="assets/img/marissa-unsplash.jpg"
                                     alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;"/>
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form>
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img src="assets/img/logo-login2.png" alt="logo"/>
                                            <span class="h3 fw-light mb-0">L4U Master Panel</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3 text-uppercase" style="letter-spacing: 1px;">
                                            Forgot Password
                                        </h5>
                                        <div id="formForgotPassword">
                                            <div class="form-outline mb-4" id="formForgotEmail">
                                                <label class="form-label" for="inputForgotEmail">Email </label>
                                                <input type="email" id="inputForgotEmail"
                                                       class="form-control form-control-lg"
                                                       placeholder="mail@localforyou.com"
                                                       value=""
                                                />
                                            </div>
                                            <div class="form-outline mb-5 d-flex justify-content-between gap-2" >
                                                <div class="small" id="btnCheckEmail">
                                                    <button class="btn btn-primary btn-lg btn-block w-20 rounded-pill" type="button" id="sendEmail" onclick="checkEmail();">
                                                    <span class="d-flex justify-content-between align-items-between pr-5">
                                                        <span class="w-100 text-right">
                                                            Check Email
                                                        </span>
                                                    </span>
                                                    </button>
                                                </div>
                                                <div class="small" id="linkForgotPassword">
                                                    <a href="index.php" >
                                                        Back To Login
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="pt-1" id="dangerEmail" style="display: none;">
                                                <i class="fa-solid fa-x text-danger"></i> <small class="text-danger">undefined</small>
                                            </div>
                                            <div class="pt-1" id="invalidEmailFormat" style="display: none;">
                                                <i class="fa-solid fa-x text-danger"></i> <small class="text-danger">invalid Email Format</small>
                                            </div>
                                            <div class="pt-1" id="emailNotFound" style="display: none;">
                                                <i class="fa-solid fa-x text-danger"></i> <small class="text-danger">This user was not found.</small>
                                            </div>
                                        </div>
                                            <div class="card-body p-lg-5 text-black" id="afterCheckEmail" style="display: none;">
                                                <div class="d-flex flex-row align-items-center justify-content-center text-center gap-3 text-success" style="font-size: 1.5em; font-weight: bold;">
                                                    <i class="bi bi-envelope-check-fill"></i>
                                                    <h3> Please check your email.</h3>
                                                </div>
                                                <div class="d-flex flex-row align-items-center justify-content-center text-center gap-3 text-success" id="afterBackLogin">
                                                    <button class="btn btn-success btn-lg btn-block w-20 rounded-pill" type="button" id="sendEmail">
                                                        <a href="index.php" class="text-white">
                                                        <span class="d-flex justify-content-between align-items-between pr-5">
                                                            <span class="w-100 text-right">
                                                                Back to Login
                                                            </span>
                                                        </span>
                                                        </a>
                                                    </button>
                                                </div>
                                            </div>
                                        <img src="assets/img/loadingSpin.gif" alt="" id="loading" style="display: none;" width="100" height="100">


                                    </form>
                                </div>
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
<script>
/*
    $( document ).ready(function() {
        $("#dangerEmail").hide();
        $("#invalidEmailFormat").hide();
        $("#emailNotFound").hide();
        $("#afterCheckEmail").hide();
    });//ready
*/

     function checkEmail (){
         const email = $("#inputForgotEmail").val();
         const action = "checkEmail";
         const form = $("#formForgotPassword");
         const afterCheck = $("#afterCheckEmail");
         const loading = $("#loading");

         if (email !== "" && email !== null && email !== undefined){

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
                     form.hide();
                     loading.show();
                     setTimeout(function() {
                         loading.hide();
                         afterCheck.show();
                     }, 2000);


                     sendEncode(res);

                 }else if (res.status === "not_found"){
                     $("#emailNotFound").show();
                 }



             })
             .fail(function(xhr, status, error) {
                 alert("Failed to send email.");
                 console.log("AJAX Error", status, error);
             });
        }else{
             $("#dangerEmail").show();
         }
     }

    function sendEncode(res){
        // alert("Password");

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
             }
         );

        sendEncode.done(function(res) {
             console.log("ajax Send to Make Done");
         });

        sendEncode.fail(function(xhr, status, error) {
             console.log("ajax Send to Make fail!!");
             console.log(status + ': ' + error);
         });
     }






</script>
</body>
</html>