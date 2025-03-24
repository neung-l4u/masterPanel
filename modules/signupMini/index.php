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
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    <script src="assets/js/index.js"></script>
</head>

<body>
    <div class="container">
        <form class="p-5" action="#" method="POST">

            <div class="card">
                <div class="card-header">
                    <h1>Free Thai Demo</h1>
                    <small>Enter your details below and we’ll show you how our Online Ordering, Booking & Marketing will work in your Thai Restaurant & Massage and Spa!</small>
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
                                <label for="contactTime" class="font-weight-bold">Best time to Contact</label>
                                <input id="contactTime" class="form-control" name="bestTimeToContact" placeholder="10:00am" type="text">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="trading" class="font-weight-bold">Trading name</label>
                                <input type="text" id="trading" class="form-control" name="trading">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="shopName" class="font-weight-bold">Shop Name <span class="text-danger">*</span></label>
                                <input id="shopName" class="form-control" placeholder="your shop name" name="shopName" type="text" />
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="shopType" class="font-weight-bold">Shop Type <span class="text-danger">*</span> </label>
                                <select id="shopType" class="form-control" name="shopType" title="Customer_Type">
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
                                <label for="city" class="font-weight-bold">City</label>
                                <input id="city" class="form-control" name="city" placeholder="Queenland" type="text">
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="country" class="font-weight-bold">Country</label>
                                <select id="country" class="form-control" name="country" onchange="setMoney();">
                                    <option value="Australia">Australia</option>
                                    <option value="Canada">Canada</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="USA">United States</option>
                                    <option value="Thailand">Thailand</option>
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
                                    <option value="CAD">CAD - Canadian Dollar</option>
                                    <option value="NZD">NZD - New Zealand Dollar</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="USD">USD - U.S. Dollar</option>
                                    <option value="THB">THB - Thai Baht</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="interest" class="font-weight-bold">Interesting in</label>
                                <select id="interest" class="form-control" name="interest" title="Lead Interesting in">
                                    <option value="Not specified" selected>-- Not specified --</option>
                                    <option value="Online Ordering System">Online Ordering System</option>
                                    <option value="Booking System">Booking System</option>
                                    <option value="Massage &amp; Spa">Massage & Spa</option>
                                    <option value="Pro Shopping Cart">Pro Shopping Cart</option>
                                    <option value="Social Media Marketing">Social Media Marketing</option>
                                    <option value="Social Media Bundle">Social Media Bundle</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="comment" class="font-weight-bold">Comments</label>
                                <textarea id="comment" class="form-control" name="comment" rows="3" type="text" wrap="soft"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
<!--                            <button type="submit" class="btn btn-primary mb-1" name="submit">Submit</button>-->
                            <input id="cmdSubmit" class="btn btn-primary" type="button" value="Submit">
                        </div>
                    </div>

                    <input type="hidden" id="RestaurantMarketingAgent" name="agent" value="Other">
                    <input type="hidden" id="SignupFormVersion" name="version" value="L4U Website 1.0" />

                </div>
            </div>

            <input type="hidden" id="leadSource" name="leadSource" value="Landing Page" />
            <input type="hidden" id="leadRecordType" name="leadRecordType" value="Ads" />
        </form>
    </div>
<script>

    $("#cmdSubmit").click(function () {
        setMoney();
        let countryCode = shortCountry();

        let payload = {
            "first_name": $("#first_name").val(),
            "last_name": $("#last_name").val(),
            "email": $('#email').val(),
            "mobile": $("#mobile").val(),
            "contactTime": $("#contactTime").val(),
            "trading": $("#trading").val(),
            "shopName": $("#shopName").val(),
            "shopType": $("#shopType").val(),
            "url": $("#url").val(),
            "city": $("#city").val(),
            "country": $("#country").val(),
            "currency": $("#currency").val(),
            "interest": $("#interest").val(),
            "comments": $("#comment").val(),
            "SignupFormVersion": $("#SignupFormVersion").val(),
            "countryCode" : countryCode,
            "leadSource": $("#leadSource").val(),
            "leadRecordType": $("#leadRecordType").val()
        };

        const callAjax = $.ajax({
            url: "models/index.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "payload": payload
            }
        });
        
        callAjax.done(function(res) {
            console.log('return',res);
            return true;
        });
        
        callAjax.fail(function(xhr, status, error) {
            console.log("ajax fail!!");
            console.log(status + ': ' + error);
            return false;
        });

        const sendWebhooks = $.ajax({
            url: "https://hook.us1.make.com/47ue45ij7fhm7sol8rldp6dxpag2ldjl",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

    });//cmdSubmit.click

</script>
</body>

</html>