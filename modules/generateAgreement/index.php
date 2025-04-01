<?php
date_default_timezone_set("Asia/Bangkok");
$currentDate = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "../../assets/api/googleAnalytics.php";?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-3.7.1.min.js"></script>
    
</head>

<body>
    <div class="container">
        <form class="p-5" action="https://report.localforyou.com/modules/signup/assets/docs/contract_2024_V02.php?" method="GET">

            <div class="card">
                <div class="card-header">
                    <h1>Generate Agreement</h1>
                </div>


                <div class="card-body p-4">

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="Country" class="font-weight-bold">
                                    County
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="Country" class="form-control" name="Country" onchange="txt();">
                                    <option value="AU" selected="selected">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="US">United States</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="State" class="font-weight-bold">State
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="State" class="form-control" name="State" placeholder="Queenland">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="registrationNumber" class="font-weight-bold" id="registrationNumber">ABN
                                    <span class="text-danger">*</span>
                                </label>
                                <input id="registrationNumber" class="form-control" name="registrationNumber" type="text" placeholder="12345678">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="customerFullName" class="font-weight-bold">Customer FullName<span class="text-danger">*</span> </label>
                                <input type="text" id="customerFullName" class="form-control" maxlength="40" name="customerFullName" placeholder="John Doe">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="ShopName" class="font-weight-bold">Shop Name<span class="text-danger">*</span> </label>
                                <input type="text" id="ShopName" class="form-control" name="ShopName" placeholder="Good Restaurant">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="contractPeriod" class="font-weight-bold">
                                    Period
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="contractPeriod" class="form-control" name="contractPeriod">
                                    <option value="0" selected="selected">No Contract</option>
                                    <option value="3">3 months</option>
                                    <!-- <option value="6">6 months</option> -->
                                    <option value="12">12 months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary mb-1" name="submit">Submit</button>
                            <!-- <input type="submit" name="submit"> -->
                        </div>
                    </div>

                    <input type="hidden" id="RestaurantMarketingAgent" name="00N2u000000mNZG" value="Other">
                    <input type="hidden" id="SignupFormVersion" name="00N9s000000VWbf" value="L4U Website 1.0" />

                </div>
            </div>
        </form>
    </div>
    <script>
        function txt() {

            let ct = $("#Country").val();

            if (ct === "NZ") {
                $("#registrationNumber").html('NZBN <span class="text-danger">*</span>')
            } else if (ct === "AU") {
                $("#registrationNumber").html('ABN <span class="text-danger">*</span>')
            } else if (ct === "UK") {
                $("#registrationNumber").html('CRN <span class="text-danger">*</span>')
            } else if (ct === "US") {
                $("#registrationNumber").html('EIN <span class="text-danger">*</span>')
            }

        }
    </script>
</body>

</html>