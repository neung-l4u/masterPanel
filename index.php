<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Master Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css'>
    <link rel='stylesheet' href="assets/css/login_form.css">
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
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="assets/img/marissa-unsplash.jpg"
                                     alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;"/>
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form>
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img src="assets/img/logo-login.png" alt="logo"/>
                                            <span class="h2 fw-light mb-0">L4U Master Panel</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3 text-uppercase" style="letter-spacing: 1px;">
                                            Please Sign In
                                        </h5>
                                        <div class="form-outline mb-4">
                                            <label class="form-label" for="formUser">Email or Mobile</label>
                                            <input type="email" id="formUser"
                                                   class="form-control form-control-lg"
                                                   placeholder="mail@localforyou.com | 0891234567"
                                                   autocomplete="off"
                                                   value="<?php echo isset($_COOKIE['user'])?$_COOKIE['user']:''; ?>"
                                            />
                                        </div>
                                        <div class="form-outline mb-2">
                                            <label class="form-label" for="formPassword">Password</label>
                                            <input type="password" id="formPassword"
                                                   class="form-control form-control-lg"
                                                   placeholder="your password here"
                                                   autocomplete="off"
                                                   value="<?php echo isset($_COOKIE['pass'])?$_COOKIE['pass']:''; ?>"
                                            />
                                        </div>
                                        <div class="form-outline mb-5 d-flex justify-content-between gap-2">
                                            <div class="small">
                                                <input class="form-check-input" type="checkbox" value="true"
                                                       id="formRemember" checked/>
                                                <label class="form-check-label" for="formRemember"> Remember me </label>
                                            </div>
                                            <div class="small">
<!--                                                <a href="#">-->
<!--                                                    Forgot password-->
<!--                                                </a>-->
                                            </div>
                                        </div>
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-primary btn-lg btn-block w-100 rounded-pill" type="button" id="cmdLogin">
                                                <span class="d-flex justify-content-between align-items-center pr-5">
                                                    <span class="w-100 text-right">
                                                        Login
                                                    </span>
                                                    <i class="fa-solid fa-fingerprint"></i>
                                                </span>
                                            </button>
                                        </div>
                                        <div class="pt-1" id="resultText">
                                            <small>&nbsp;</small>
                                        </div>
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
    Version 1.2.2 (24.01.2025)<br>
    © 2017 Localforyou.com Online Ordering & Marketing FOR THAI RESTAURANTS
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<script src="assets/js/settings.js"></script>
<script src="assets/js/date_format.js"></script>
<script src="assets/js/getUserAgent.js"></script>
<script src="assets/js/ajaxFunction.js"></script>
<script src="https://api.ipify.org?format=jsonp&callback=getIP"></script>
<script src="https://unpkg.com/@popperjs/core@2.11.5/dist/umd/popper.min.js"></script>
<script src="assets/js/authentication.js"></script>
</body>
</html>