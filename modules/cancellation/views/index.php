<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
date_default_timezone_set('Asia/Bangkok');

$id = !empty($_GET['id']) ? strtolower(trim($_GET['id'])): '';
$testMode = ($id == "test") ? 1 : 0;
$leadSource = "Unsubscribe Form";
$formVersion = "1.5.4";
$emailVersion = "1.0";
$timestamps = date("H:i D ,d M Y") . " (BKK)";

?>
<!doctype html>
<html lang="en">
<head>
    <title>L4U - Services</title>
    <?php include "form_header.php"; ?>
</head>
<body>

<header class="intro">
    <div class="d-flex align-items-center justify-content-center">
        <img src="../assets/img/newL4U-logo-100x100.png" alt="L4U Logo">
        <div class="d-flex flex-column">
            <h1>Local For You Services</h1>
            <p>Online Unsubscribe Form.</p>
        </div>
    </div>
</header>

<main style="min-height: 60vh">
    <?php if ($id == "test"){ echo "<div><h3 class='text-danger'>Test Mode On</h3></div>"; }  ?>
    <div class="container d-flex justify-content-center align-content-center">
        <?php
        if (empty($_GET['id'])){ ?>
            <i class="fa-solid fa-circle-exclamation text-danger"></i>
            <span class="text-danger py-5">Please contact the Local For You staff to submit a request to cancel your membership.</span>
        <?php }else{ ?>
            <div>
                <form id="myForm" action="#" method="POST">
                    <div class="card mt-3">
                        <div class="card-header font-weight-bold">Request to Cancel - Online Submission</div>
                        <div id="mainSetup" class="card-body p-4">

                            <div class="lineBusinessInfo my-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold py-4">Business information</h5>
                                </div>
                                <div class="form-group pt-2">
                                    <label for="FormIndustrial">
                                        <span>Industrial Type<b class="red">*</b><small class="text-danger warningText" id="smallIndustrial">Please Select Industrial.</small></span>
                                    </label>
                                    <select id="formIndustrial" class="form-select" name="industrial">
                                        <option selected value="" disabled>--None--</option>
                                        <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants &amp; Takeaways</option>
                                        <option value="Thai Massage">Thai Massage</option>
                                        <option value="Restaurants &amp; Takeaways">Restaurants &amp; Takeaways</option>
                                    </select>
                                </div>

                                <div class="form-group pt-2">
                                    <label for="formCountry">
                                        <span>Country <b class="red">*</b><small class="text-danger warningText" id="smallCountry">Please Select Country.</small></span>
                                    </label>
                                    <select id="formCountry" class="form-select" name="country">
                                        <option selected value="" disabled>Please select Country</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="US">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="TH">Thailand</option>
                                    </select>
                                </div>
                                <?php if ($id == "test"){ echo "<div><input type='hidden' name='testMode' id='testMode' value='1'></div>"; }  ?>

                                <div class="form-group pt-2">
                                    <label for="shopName">Shop name <b class="red">*</b> <small class="text-danger warningText" id="smallShopName">Please check the message.</small></label>

                                    <div>
                                        <input
                                                type="text"
                                                id="shopName"
                                                name="shopName"
                                                class="form-control"
                                                placeholder="Mali Thai Bistro"
                                                autocomplete="off"
                                        />
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label for="company" class="control-label">Trading name</label>
                                    <div>
                                        <input
                                                type="text"
                                                id="company"
                                                name="tradingName"
                                                class="form-control"
                                                placeholder="Thai Culture Group Inc."
                                                autocomplete="off"
                                        />
                                    </div>
                                </div>
                            </div>

                            <br class="my-4">
                            <hr>

                            <div class="lineAddress my-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold pb-2">Shop Physical Address</h5>
                                </div>

                                <div class="pt-2">
                                    <label for="streetAddress" class="control-label">
                                        Street Address
                                    </label>
                                    <div>
                                <textarea
                                        class="form-control"
                                        id="streetAddress"
                                        name="streetAddress"
                                        rows="2"
                                        placeholder="11/200 Golden Village"
                                ></textarea>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label for="city" class="control-label">City/Province</label>
                                    <div>
                                        <input
                                                type="text"
                                                id="city"
                                                name="city"
                                                class="form-control"
                                                autocomplete="off"
                                                placeholder="Good city"
                                        />
                                    </div>
                                </div>

                                <div class="pt-2 selectState">
                                    <label for="state" class="control-label">State</label>
                                    <div>
                                        <select id="state" class="form-select optionState" name="state">
                                            <option value="">Please select Country</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <label for="zip" class="control-label zipLabel">Zip Code</label>
                                    <div>
                                        <input
                                                type="text"
                                                id="zip"
                                                name="zip"
                                                class="form-control"
                                                placeholder="3000"
                                                maxlength="6"
                                                oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                        >
                                    </div>
                                    <div class="pt-2">
                                        <label>Country : </label>
                                        <span id="selectedCountry"> -- </span>
                                    </div>
                                </div>
                            </div>

                            <br class="my-4">
                            <hr>

                            <div class="lineOwnerInfo my-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold">Shop Owner information</h5>
                                </div>

                                <div class="pt-4">
                                    <label for="first_name">First name <b class="red">*</b></label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" autocomplete="off" placeholder="John"/>
                                </div>

                                <div class="pt-2">
                                    <label for="last_name">
                                        <span>Last name</span>
                                        <b class="red">*</b>
                                    </label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" autocomplete="off" placeholder="Doe"/>
                                </div>

                                <div class="pt-2">
                                    <label for="mobile" class="control-label">
                                        Mobile <b class="red">*</b>
                                        <small class="form-text text-muted">(without country's code )</small>
                                    </label>
                                    <div>
                                    <span class="input-group">
                                        <label for="mobile" class="input-group-text">+</label>
                                        <input
                                                type="tel"
                                                class="form-control"
                                                id="mobile"
                                                name="mobile"
                                                maxlength="12"
                                                onkeyup="formatMobile(this.value,'mobileFormatted');"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                placeholder="Number only e.g.0895117447"
                                                autocomplete="off"
                                        />
                                    </span>
                                        <small id="MobileHelp" class="form-text text-muted">e.g. 0408084722</small><br>
                                        <small class="form-text text-primary mobileFormatted">Formatted number will show here.</small>
                                        <input type="hidden" id="ownerMobile" name="ownerMobile" class="mobileFormatted">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label for="email" class="control-label">
                                        <span>Email <b class="red">*</b><small class="text-danger warningText" id="smallEmail">Please provide a valid email address.</small></span>
                                    </label>
                                    <div>
                                        <input type="email" class="form-control mainEmail text-lowercase" id="email" name="email" maxlength="80" autocomplete="off" placeholder="mail@localforyou.com"/>
                                        <small id="emailHelp" class="form-text text-muted">e.g. mail@localforyou.com</small>
                                    </div>
                                </div>
                            </div>

                            <br class="my-4">
                            <hr>

                            <div class="lineReason py-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold pb-2">Please complete the following</h5>
                                </div>
                                <div class="form-group">
                                    <div class="d-flex flex-column">
                                        <label for="formReason">
                                            What is the Main Reason you wish to cancel?
                                            <b class="red">*</b>
                                            <small class="text-danger warningText" id="smallReason">Please select a reason.</small>
                                        </label>
                                        <select id="formReason" class="form-select" name="reason" onchange="superbas()">
                                            <option selected value=""> --- Please select your reason --- </option>
                                            <option value="Closing down the Business">Closing down the Business</option>
                                            <option value="Changing Business Owner">Changing Business Owner</option>
                                            <option value="Not getting enough orders">Not getting enough orders</option>
                                            <option value="NO longer want online ordering">NO longer want online ordering</option>
                                            <option value="Cost too much">Cost too much</option>
                                            <option value="Found something better">Found something better</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 mt-2 w-100 warningText" id="other">
                                    <label for="boxother" class="form-label">
                                        <i class="fa-solid fa-note-sticky"></i>&nbsp;
                                        Other
                                        <small class="text-danger warningText" id="smallOther">Please check the message.</small>
                                    </label>
                                    <textarea class="form-control w-100" id="boxother" name="other" rows="3" placeholder="-- Comment --"></textarea>
                                </div>

                                <div class="mt-3">
                                    <div class="col d-flex flex-column">
                                        <label for="lastDate">
                                            Last Date you want the system Live? <b class="red">*</b><small class="text-danger warningText" id="smallDate">Please select a date.</small>
                                        </label>

                                        <div class="date" id="datepicker">
                                            <input type="date" class="form-control" id="lastDate" name="lastDate">
                                            <input type="hidden" id="mode" value="save" name="mode">
                                        </div>

                                        <small class="form-text text-muted">
                                            Online ordering system will be turned off on that date
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <br class="my-4">
                            <hr>

                            <div class="lineAgreement pt-4">
                                <div class="card-body px-2">
                                    <div class="container d-flex flex-column">

                                        <div class="text-center pt-4">
                                            <h5 class="card-title font-weight-bold pb-2">Cancellation Details</h5>
                                        </div>
                                        <div id="paymentAgreement" class="d-flex flex-column align-items-center gap-2 justify-content-evenly">
                                            <div id="agreementTop" class="pb-4 d-flex flex-column align-items-start">

                                                <figure>
                                                    <figcaption>NO CONTRACT - Min cancellation Policy</figcaption>
                                                    <ul>
                                                        <li class="mt-3">Your Online Shopping Cart has no min contract period we do <b class="text-danger">require a completed cancellation Notice at least 3 business day</b> before your next charge date.</li>
                                                        <li class="mt-3">If cancelled 2 days before or on the same day no refunds will be offered. <b class="text-danger">Your Subscription will be scheduled to be cancelled at the end of your next billing cycle.</b></li>
                                                        <li class="mt-3">It is your responsibility to remove any marketing material, promotions, or specials that <b class="text-danger">will no longer be active online once your subscription has been cancelled.</b></li>
                                                        <li class="mt-3"><b class="text-danger">Your Website (if you have one with us) will be discontinued</b> along with all other subscribed services.</li>
                                                    </ul>
                                                </figure>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="true" id="acceptAgreement">
                                                    <label class="form-check-label acceptAgreement" for="acceptAgreement"> Yes I understand this </label>
                                                </div>

                                                <div class="mb-3 mt-5 w-100">
                                                    <label for="additionComment" class="form-label">
                                                        <i class="fa-solid fa-note-sticky"></i>&nbsp; Additional Information or feedback you wish to share with us
                                                    </label>
                                                    <textarea class="form-control w-100" id="additionComment" name="note" rows="3" placeholder="Any other information you would like us to know."></textarea>
                                                    <small id="subAdditionCommentHelp" class="form-text text-muted"> We would love to hear your thoughts! </small>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="float-end">
                                <span id="loadingAjax" class="text-primary warningText"><img src="../assets/img/loadingSpin.gif" alt="" width="41"> Loading...</span>
                                <span id="doneForm" class="text-success warningText">Success</span>
                                <button type="button" class="btn btn-success" id="cancelBtn" onclick="validateForm()">Confirm</button>
                                <input type="hidden" id="testMode" name="testMode" value="<?php echo $testMode; ?>">
                                <input type="hidden" id="leadSource" name="leadSource" value="<?php echo $leadSource; ?>">
                                <input type="hidden" id="formVersion" name="formVersion" value="<?php echo $formVersion; ?>">
                                <input type="hidden" id="emailVersion" name="emailVersion" value="<?php echo $emailVersion; ?>">
                                <input type="hidden" id="timeStamps" name="timeStamps" value="<?php echo $timestamps; ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php }//else ?>
    </div>
</main>
<footer class="credit" style="display: flex; justify-content: space-around; ">
    <div>
        <a
                title="Local For You Website - Cancellation Policy"
                href="https://localforyou.com/en/cancellation-policy/"
                target="_blank"
        >
            Cancellation Policy
        </a>
    </div>
    <div>
     Version <?php echo $formVersion;?> Author: IT Team - Distributed By:
    <a
            title="Local For You Website"
            href="https://www.localforyou.com"
            target="_blank"
    >
        Local For You
    </a></div>

</footer>
<script src="../assets/js/jquery.3.6.0.min.js"></script>
<script src="../assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script src="../assets/js/global_data.js?v=1.5.4"></script>
<script src="../assets/js/date_format.js"></script>
<script src="../assets/js/popper.2.11.5.min.js"></script>
<script src="../assets/js/unsubData.js?v=1.5.3"></script>
<script>
    let payload = {};

    $( document ).ready(function() {
        $(".warningText").hide();
        // $("#other").hide()
        // $("#loadingAjax").hide()
        // $("#smallCountry").hide()
        // $("#smallShopName").hide()
        // $("#smallDate").hide()
        // $("#smallOther").hide()
        // $("#smallEmail").hide()
    });//ready

    function validateForm(){
        let country = $("#formCountry").val();
        let industrial = $("#formIndustrial").val();
        let shopName = $("#shopName").val();
        let lastDate = $("#lastDate").val();
        let email = $("#email").val();
        let reason = $("#formReason").val();

        if (industrial === "" || industrial === null){
            $("#smallIndustrial").show();
            $("#formIndustrial").focus();
        }else if (country === "" || country === null){
            $("#smallIndustrial").hide();
            $("#smallCountry").show();
            $("#formCountry").focus();
        }else if (shopName.length < 1){
            $("#smallCountry").hide();
            $("#smallShopName").show();
            $("#shopName").focus();
        }else if (reason === "" || reason === null){
            $("#smallShopName").hide();
            $("#smallReason").show();
            $("#formReason").focus();
        }else if (reason === "other" && $("#boxother").val().trim() === ""){
            $("#smallReason").hide();
            $("#smallOther").show();
            $("#boxother").focus();
        }else if (lastDate === ""){
            $("#smallReason").hide();
            $("#smallDate").show();
            $("#lastDate").focus();
        }else if (email === ""){
            $("#smallDate").hide();
            $("#smallEmail").text("Please provide a valid email address.").show();
            $("#email").focus();

        }else if (!validateEmail(email)){
            $("#smallDate").hide();
            $("#smallEmail").text("Email format is invalid. Please include '@' and domain, e.g. mail@localforyou.com").show();
            $("#email").focus();

        }else{
            $("#cancelBtn").hide();
            $("#loadingAjax").fadeIn(100);


            // read all input, select, textarea in form
            $('form').find('input, select, textarea').each(function () {
                let name = $(this).attr('name');
                let value = $(this).val();

                if (name) {
                    if ($(this).is(':radio')) { if ($(this).is(':checked')) { payload[name] = value; } } // เช็คว่าเป็น radio และถูกเลือกหรือไม่
                    else if ($(this).is(':checkbox')) { payload[name] = $(this).is(':checked'); } // เช็คว่าเป็น checkbox หรือไม่
                    else { payload[name] = value; } // ค่าอื่นๆ (text, select, textarea)
                }
            }); //read from

            const countryMap = {
                AU: "Australia",
                NZ: "New Zealand",
                TH: "Thailand",
                US: "United States",
                CA: "Canada",
                UK: "United Kingdom"
            };

            payload.country = countryMap[payload.country] || "Please select country";
            console.log(payload);

            saveDB();

                console.log("we call webhook");


                //TODO : ยิงไป webhook unsub ให้ส่งเมล
                const callAjax = $.ajax({
                    type: "POST",
                    crossDomain: true,
                    dataType: 'json',
                    url: "https://hook.us1.make.com/a3or9ct3u3m84lqckivl1rt7891ky1h2",
                    data: payload
                });

                callAjax.done(function (res) {
                    $("#loadingAjax").hide();
                     $("#doneForm").show();


                    console.log("Ajax done");
                    console.log("return = ",res);
                    location.replace("https://localforyou.com/thank-you/");
                });

                callAjax.fail(function(xhr, status, error) {
                    console.log("ajax webhook fail!!");
                    console.log(status + ': ' + error);
                    alert("Send fail!!");

                });
        }//Validate pass
    }//end validateForm()

    function saveDB(){

        //TODO : Save Database table Cancellation
        const ajaxSaveDB = $.ajax({
                url: "activeajax.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload,
            }
        );

        ajaxSaveDB.done(function(res) {
            console.log("ajax Send to Database Done");
            return true;
        });

        ajaxSaveDB.fail(function(xhr, status, error) {
            console.log("ajax Send to Database fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }//saveDB

    function validateEmail(email) {
        // ใช้ regex ตรวจรูปแบบอีเมล
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/i;
        return emailPattern.test(email);
    }

</script>

</body>
</html>