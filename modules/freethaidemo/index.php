<?php
date_default_timezone_set("Asia/Bangkok");
$currentDate = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
    <link href="/css/css.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <form class="p-5" action="https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8" method="POST">
             <!--<input type="hidden" name="debug" value="1">
             <input type="hidden" name="debugEmail" value="neung@localforyou.com">-->

            <input type="hidden" name="oid" value="00D2v0000012UyV">
            <input type="hidden" name="retURL" value="https://localforyou.com/thank-you/">
            <input type="hidden" name="lead_source" value="Website - Free Demo">
            <input type="hidden" name="recordType" value="012Mq000000iNgr">
            <input type="hidden" name="00N2u000000mQgE" value="New">
            <input type="hidden" name="title" value="Lead form Website L4U 2024">
            <input type="hidden" name="00N2v00000JvZge" id="00N2v00000JvZge" value="<?php echo $currentDate; ?>">

            <div class="card">
                <div class="card-header">
                    <h1>Free Thai Demo</h1>
                    <small>Enter your details below and we’ll show you how our online ordering & marketing system will work in your Thai restaurant!</small>
                </div>


                <div class="card-body p-4">

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="font-weight-bold">Sign Up Date : <?php echo $currentDate; ?></label>

                            </div>
                        </div> <!-- col -->
                    </div> <!-- row -->

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="first_name" class="font-weight-bold">First Name <span class="text-danger">*</span> </label>
                                <input type="text" id="first_name" class="form-control" maxlength="40" name="first_name" placeholder="First name">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="last_name" class="font-weight-bold">Last Name <span class="text-danger">*</span> </label>
                                <input type="text" id="last_name" class="form-control" maxlength="80" name="last_name" placeholder="Last name">
                            </div>
                        </div>
                    </div>




                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span> </label>
                                <input type="text" id="email" class="form-control" name="email" placeholder="email@website.com">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="mobile" class="font-weight-bold">Mobile <span class="text-danger">*</span> </label>
                                <input type="text" id="mobile" class="form-control" name="mobile" placeholder="012345678">
                            </div>
                        </div>
                    </div>

                    

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="00N9s000000Nl1G" class="font-weight-bold">Contact Time</label>
                                <input id="00N9s000000Nl1G" class="form-control" name="00N9s000000Nl1G" placeholder="10:00am" type="text">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="company" class="font-weight-bold">Company</label>
                                <input type="text" id="company" class="form-control" name="company">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="00N2v00000IyVqB" class="font-weight-bold">Shop Name <span class="text-danger">*</span></label>
                                <input id="00N2v00000IyVqB" class="form-control" placeholder="your shop name" name="00N2v00000IyVqB" type="text" />
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="00N9s000000QRyY" class="font-weight-bold">Shop Type <span class="text-danger">*</span> </label>
                                <select id="00N9s000000QRyY" class="form-control" name="00N9s000000QRyY" title="Customer_Type">
                                    <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants &amp; Takeaways</option>
                                    <option value="Thai Massage">Thai Massage</option>
                                    <option value="Restaurants &amp; Takeaways">Restaurants &amp; Takeaways</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="url" class="font-weight-bold">Website or Social media</label>
                                <input type="text" id="url" class="form-control" name="url" placeholder="www.localforyou.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <lable for="00NMq000000R4sL" class="font-weight-bold">City</lable>
                                <input id="00NMq000000R4sL" class="form-control" name="00NMq000000R4sL" placeholder="Queenland" type="text">
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="country" class="font-weight-bold">Country</label>
                                <select id="country" class="form-control" name="00N2v00000IyVqF" onchange="setMoney();">
                                    <option value="Australia">Australia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="USA">United States</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row" style="display:none;">
                        <div class="col">
                            <div class="form-group">
                                <label for="currency" class="font-weight-bold">
                                    Currency
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="currency" class="form-control" name="currency">
                                    <option value="AUD" selected="selected">AUD - Australian Dollar</option>
                                    <!-- <option value="GBP">GBP - British Pound</option> -->
                                    <option value="NZD">NZD - New Zealand Dollar</option>
                                    <option value="USD">USD - U.S. Dollar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <lable for="00NMq000000R4yn" class="font-weight-bold">Interesting in</lable>
                                <select multiple id="00NMq000000R4yn" class="form-control" name="00NMq000000R4yn" title="Lead Interesting in">
                                    <option value="Not specified" selected>-- Not specified --</option>
                                    <option value="Online Ordering System">• Online Ordering System</option>
                                    <option value="Booking System">• Booking System</option>
                                    <option value="Website">• Website</option>
                                    <option value="Marketing">• Marketing</option>
                                    <option value="Marketing materials">• Marketing materials</option>
                                    <option value="Other">Other</option>
                                </select>
                                <small class="text-muted">select multiple choice by press shift or ctrl</small>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <lable for="00N2v00000IyVpq" class="font-weight-bold">Comments</lable>
                                <textarea id="00N2v00000IyVpq" class="form-control" name="00N2v00000IyVpq" rows="3" type="text" wrap="soft"></textarea>
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
    function setMoney() {
        let country = $("#country").val();
        let currencyBox = $("#currency");

        if (country === "Australia"){
            currencyBox.val("AUD");
        }else if (country === "New Zealand"){
            currencyBox.val("NZD");
        }else if(country === "USA"){
            currencyBox.val("USD");
        }

    }//function
</script>
</body>

</html>