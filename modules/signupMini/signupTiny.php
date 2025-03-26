<!DOCTYPE html>
<html lang="en">
<head>

    <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>signupMini - Local For You</title>
    
    <style>
        header, .elementor-location-footer, .elementor-location-header
        {
        	display:block;
        }
        body{
            background-color: #ffffff !important;
        }
    </style>
     
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
             <!-- <div class="card bg-light mb-3 mt-3 shadow-lg">"card" class in replacement with "fieldset" -->
                
                <div class="card-body">
                    
                    <!--<div class="card-header"></div>-->
                    <form action="#" method="POST">
                        <input type=hidden name="oid" value="oid">
                        <input type=hidden name="retURL" value="https://localforyou.com/thank-you/">
            
                        <!--  ----------------------------------------------------------------------  -->
                        <!--  NOTE: These fields are optional debugging elements. Please uncomment    -->
                        <!--  these lines if you wish to test in debug mode.                          -->
                        <!--  <input type="hidden" name="debug" value=1>                              -->
                        <!--  <input type="hidden" name="debugEmail"                                  -->
                        <!--  value="belgarjobelle@gmail.com">                                        -->
                        <!--  ----------------------------------------------------------------------  -->
            
                        <!--<legend> FREE DEMO</legend>
                        <small>Enter your details below and we’ll show you how our online ordering system will work in your restaurant!</small>
                        -->

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input id="first_name" maxlength="40" name="first_name" type="text" class="form-control" placeholder="Enter your first name">
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" maxlength="40" name="last_name" type="text" class="form-control" placeholder="Enter your last name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
                        </div>

                        <div class="form-group">
                            <label for="phone">Mobile Phone</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="0123456789" pattern="\+?[0-9\s\-]+" maxlength="15">
                        </div>

                        <div class="form-group">
                            <label for="company">Restaurant Name</label>
                            <input type="text" class="form-control" id="company" name="company" placeholder="Enter your restaurant name">
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <select class="form-control" id="country" name="country">
                                <option value="" disabled selected>Select your country</option>
                                <option value="Australia">Australia</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="Thailand">Thailand</option>
                            </select>
                        </div>

                        <input type="hidden" id="shopType" name="shopType" value="Thai Restaurants &amp; Takeaways" />
                        <input type="hidden" id="formType" name="formType" value="tiny" />
                        <input type="hidden" id="leadSource" name="leadSource" value="Landing Page" />
                        <input type="hidden" id="leadRecordType" name="leadRecordType" value="Ads" />
                    
                        <input id="cmdSubmit" class="btn btn-sm btn-success btn-block" type="button" value="Yes! I want a FREE Demo.">
                        
                        <div id="successMessage" class="mt-3 border border-success py-1 px-2" style="display: none;">
                            <small class="text-success">Success! We will contact you shortly to schedule a demo.</small>
                        </div>
                    </form>
                            
                  

            </div>
        </div> <!--End of col-md-6 -->
    </div> <!--End of row-->
</div> <!--End of Container-->

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<!-- <script src="https://report.localforyou.com/modules/signupMini/assets/js/index.js"></script> -->
<script src="assets/js/index.js"></script>
<script>

    $("#cmdSubmit").click(function () {

        if (validateForm() == false) {
            return false;
        } else {
            let countryCode = shortCountry(); //function located in modules/signupMini/assets/js/index.js

            let payload = {
                "first_name": $("#first_name").val(),
                "last_name": $("#last_name").val(),
                "email": $('#email').val(),
                "mobile": $("#mobile").val(),
                "company": $("#company").val(),
                "country": $("#country").val(),
                "countryCode": countryCode,
                "shopType": $("#shopType").val(),
                "formType": $("#formType").val(),
                "leadSource": $("#leadSource").val(),
                "leadRecordType": $("#leadRecordType").val()
            };

            const callAjax = $.ajax({
                url: "https://report.localforyou.com/modules/signupMini/models/index.php",
                //url: "models/index.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'jsonp',
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
                data: payload,
                success: function(response) {
                    if (response.result === "Leads to Monday successfully") {
                        $("#successMessage").fadeIn();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + " - " + error);
                }
            });
        }

    }); //cmdSubmit.click

</script>
</body>
</html>