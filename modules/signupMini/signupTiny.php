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
        .btn-block {
            background-color: #00BCF4;
            border-color: #00BCF4;
        }
        .btn-block:hover {
            background-color: #273B91;
            border-color: #273B91;
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
                <form action="#" method="POST" id="tinyForm1">
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
                        <label for="first_name">Name</label>
                        <input id="first_name" maxlength="40" name="first_name" type="text" class="form-control" placeholder="Enter your name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
                    </div>

                    <div class="form-group">
                        <label for="mobile">Phone</label>
                        <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="0123456789" pattern="\+?[0-9\s\-]+" maxlength="15">
                    </div>

                    <div class="form-group">
                        <label for="shopType">Business Type</label>
                        <select class="form-control" id="shopType" name="shopType">
                            <option value="" disabled selected>Select your business</option>
                            <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants & Takeaways</option>
                            <option value="Thai Massage">Thai Massage</option>
                            <option value="Restaurants &amp; Takeaways">Restaurants & Takeaways</option>
                        </select>
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

                    <input type="hidden" id="formType" name="formType" value="tiny" />
                    <input type="hidden" id="leadSource" name="leadSource" value="Landing Page" />
                    <input type="hidden" id="leadRecordType" name="leadRecordType" value="Ads" />
                
                    <input id="cmdSubmit" class="btn btn-sm btn-success btn-block" type="submit" value="Get Started">
                    
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
const form1 = $("#tinyForm1");

form1.submit(function (e) {
    e.preventDefault();
    const isValid = validateForm(form1);
    if (!isValid) return false;

    const payload = getPayload(form1);
    sendPayload(payload);
});
</script>
</body>