<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>L4U - Available Coupon</title>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css'>
    <link rel="stylesheet" href="assets/css/defaultForm.css">
    <script src="https://kit.fontawesome.com/9c38e6ba4e.js" crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
    <div class="d-flex flex-column align-items-center justify-content-center" style="height: 100vh;">
        <h4 >Available Coupon</h4>
        <small class="mb-3 text-secondary">(Click for copy to clipboard)</small>
            <div class="accordion w-75" id="accordionPanel">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Australia
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionPanel">
                        <div class="accordion-body" style="background-color: #FAFAFA;">
                            <div class="AU-Coupon list-group px-4 py-3">
                                <span class="text-danger">No Available Coupon.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            New Zealand
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionPanel">
                        <div class="accordion-body" style="background-color: #FAFAFA;">
                            <div class="NZ-Coupon list-group px-4 py-3">
                                <span class="text-danger">No Available Coupon.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Thailand
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionPanel">
                        <div class="accordion-body" style="background-color: #FAFAFA;">
                            <div class="TH-Coupon list-group px-4 py-3">
                                <span class="text-danger">No Available Coupon.</span>
                            </div>
                        </div>
                    </div>
                </div>-->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            United States
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionPanel">
                        <div class="accordion-body" style="background-color: #FAFAFA;">
                            <div class="US-Coupon list-group px-4 py-3">
                                <span class="text-danger">No Available Coupon.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="d-flex flex-row align-items-start justify-content-start w-75 mt-2">
            <span>Status : </span>
            <span id="txtSuccess" class="success text-success ms-2">Code copied to clipboard!!</span>
        </div>
    </div>
</div>
<script src="assets/js/settings.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<script src="assets/js/genCoupon.js"></script>
</body>
</html>