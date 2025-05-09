<?php
date_default_timezone_set("Asia/Bangkok");
global $test;
$testMode = !empty($_GET['testMode']) ? $_GET['testMode'] : false;
include("assets/function/testMode.php");
$invoiceMode = !empty($_GET['invoice']) ? $_GET['invoice'] : true;
$currentDate = date('d/m/Y');
?>
<!doctype html>
<html lang="en">
<head>
    <title>L4U - Services</title>
    <?php include "form_header.php"; ?>
</head>
<body>
<header class="intro">
    <div class="d-flex flex-row align-items-center justify-content-center">
        <img src="assets/img/newL4U-logo-100x100.png" alt="L4U Logo" style="width: 100px; height: 100px;">
        <div class="d-flex flex-column">
            <h1>Local For You Services</h1>
            <p>Online application.</p>
        </div>
    </div>
</header>

<main style="min-height: 68vh;">
    <?php include("modalRespond.php"); ?>
    <?php include("modalTerms.php"); ?>
    <article>
        <?php include "suggestion.php"; ?>
        <div id="mySidebar" class="sidebar">
            <a href="javascript:void(0)" class="btnClose toolElements" onclick="toggleLeftNav();">×</a>
            <div class="d-flex flex-column px-4 h-100 pb-4 justify-content-between">
                <div class="d-flex flex-column">
                    <!--<button type="button" class="btn btn-outline-light toolElements mb-5" onclick="openConfirm();">Confirm</button>-->
                    <button type="button" class="btn btn-outline-light toolElements mb-5" onclick="showPolicy();">Website Creation policy</button>
                    <button type="button" class="btn btn-outline-light toolElements mb-5" onclick="showFormGenAgreement();">Gen an agreement document</button>
                    <label class="form-label text-light toolElements" for="stickyComment" style="display: none;"><small>Note :</small></label>
                    <textarea
                            class="form-control toolElements"
                            id="stickyComment"
                            rows="8"
                            placeholder="Any other information you would like us to know."
                            onkeyup="syncComment(this.value);"
                            style="display: none;"
                    ></textarea>
                </div>

                <button type="button" id="saveForm" class="btn btn-secondary toolElements" disabled style="display: none;">Save Form Progress</button>
                <!--<button type="button" class="btn btn-secondary toolElements" onclick="calShowPrice();" >Cal</button>-->
            </div>
        </div>
        <button type="button" class="btnOpen" onclick="toggleLeftNav();" style="position: fixed; bottom: 30px; right: 30px;"><i class="fa-solid fa-note-sticky"></i></button>
        <div class="container d-flex justify-content-center" style="min-width:720px!important">
            <div class="col-11 col-offset-2">
                <?php include "progress_bar.php"; ?>
                <form id="myForm" action="https://monday.localforyou.com/devV2/monday_data.php"
                      method="POST">
                    <input type=hidden name="oid" value="00D2v0000012UyV">
                    <input type=hidden id="redirectURL" name="retURL"
                           value="https://signup.localforyou.com/thankyou.php">
                    <input type=hidden name="recordType" value="0129s0000004KiL">
                    <input type=hidden name="lead_source" value="New Signup">
                    <input type="hidden" id="leadStage" name="00N2u000000mQgE" value="New">
                    <input type=hidden name="currency" value="AUD">

                 <!--   <input type="hidden" name="debug" value=1>
                    <input type="hidden" name="debugEmail" value="neung@localforyou.com">-->


                    <!-- All Form -->
                    <div class="card mt-3">
                        <div class="card-header font-weight-bold">Online Subscription Form</div>
                        <!-- Step 1-->
                        <div id="mainSetup" class="card-body p-4 step">
                            <div class="text-center">
                                <h5 class="card-title font-weight-bold pb-2">Form set-up</h5>
                            </div>
                            <div class="text-center firstStepFormLoading">
                                <small class="text-secondary">The form will be ready in seconds. ... <img alt='Loading' src='assets/img/loading.gif'></small>
                            </div>
                            <div class="form-group row firstStepForm" style="display: none;">
                                <div class="col-2">
                                    <label for="formCountry">
                                        Country <b class="red">*</b>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <select id="formCountry" class="form-select" name="country_code">
                                        <option selected value="" disabled>Please select Country</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
<!--                                        <option value="TH">Thailand</option>-->
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                    </select>
                                    <input type="hidden" name="00N2v00000IyVqF" id="countryTextOnly">
                                </div>
                                <div class="col-2 d-flex align-items-center">
                                    <span id="loadingAjax">
                                         <!--<button  class="btn text-primary" type="button" onclick="setCountry();" tabindex="-1">
                                             <i class="fa-solid fa-play"></i>
                                        </button>-->
                                    </span>
                                    <span class="text-danger" id="warn_form_country" style="display: none;">
                                        Please select !!
                                    </span>
                                </div>
                            </div>
                            <div class="form-group row pt-2 firstStepForm" style="display: none;">
                                <div class="col-2">
                                    <label for="formType">
                                        industrial Type <b class="red">*</b>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <select
                                            id="formType"
                                            class="form-select"
                                            name="00N9s000000QRyY"
                                            aria-label="Default select"
                                    >
                                        <
                                        <option selected value="" disabled>--None--</option>
<!--                                        <option value="Thailand Restaurant">Thailand Restaurant (Located in Thailand)</option>-->
                                        <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants &amp;
                                            Takeaways
                                        </option>
                                        <option value="Thai Massage">Thai Massage</option>
                                        <option value="Restaurants &amp; Takeaways">Restaurants &amp; Takeaways</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <span id="warn_form_type" class="text-danger" style="display: none;">
                                        Please select !!
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2-->
                        <div id="userinfo" class="card-body step" style="display: none">
                            <div class="container">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold">Shop Owner information</h5>
                                </div>
                                <div class="form-group row pt-4">
                                    <div class="col-2"></div>
                                    <div class="col-4">
                                        <label for="first_name">First name <b class="red">*</b></label>
                                        <input
                                                type="text"
                                                id="first_name"
                                                class="form-control"
                                                name="first_name"
                                                onkeyup="ownerFirstName(this.value);"
                                                autocomplete="off"
                                                required
                                                placeholder="John"
                                                value="<?php echo $test["firstname"]; ?>"
                                        />
                                    </div>
                                    <div class="col-4">
                                        <label for="last_name">
                                            <span>Last name</span>
                                            <b class="red">*</b>
                                        </label>
                                        <input
                                                type="text"
                                                id="last_name"
                                                class="form-control"
                                                name="last_name"
                                                onkeyup="ownerLastName(this.value);"
                                                autocomplete="off"
                                                placeholder="Doe"
                                                value="<?php echo $test["lastname"]; ?>"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="mobile" class="col-2 text-end control-label col-form-label">
                                        Mobile
                                        <b class="red">*</b>
                                    </label>
                                    <div class="col-8">
                                            <span class="input-group">
                                                <label for="mobile" class="input-group-text">+</label>
                                                <input
                                                        type="tel"
                                                        class="form-control"
                                                        id="mobile"
                                                        maxlength="12"
                                                        onkeyup="formatMobile(this.value,'mobileFormatted');"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                        placeholder="Number only e.g.0895117447"
                                                        autocomplete="off"
                                                        value="<?php echo $test["mobile"]; ?>"
                                                />
                                            </span>
                                        <small id="MobileHelp" class="form-text text-muted">
                                            without country's code e.g. <span class="fakeNumber">0408084722</span> ||
                                        </small>
                                        <small class="form-text text-primary mobileFormatted">Formatted number will show
                                            here.</small>
                                        <input type="hidden" name="mobile" id="ownerMobile" class="mobileFormatted">
                                    </div>
                                </div>
                                <div class="form-group row pt-2">
                                    <label for="email" class="col-2 text-end control-label col-form-label">
                                        Email
                                        <b class="red">*</b>
                                    </label>
                                    <div class="col-7">
                                        <input
                                                type="email"
                                                class="form-control mainEmail text-lowercase"
                                                id="email"
                                                name="email"
                                                maxlength="80"
                                                onchange="ownerEmail(this.value);"
                                                onkeyup="setEmailShoppingCart(this.value);"
                                                autocomplete="off"
                                                placeholder="mail@localforyou.com"
                                                value="<?php echo $test["email"]; ?>"
                                        />
                                        <small id="emailHelp" class="form-text text-muted">
                                            e.g. mail@localforyou.com
                                        </small>
                                    </div>
                                    <div class="col-1 d-flex flex-row pb-4">
                                        <!--<button class="btn text-primary" type="button" onclick="addGoogle('mainEmail');"
                                                tabindex="-1">
                                            <i class="fa-brands fa-google"></i>
                                        </button>-->
                                        <button class="btn text-primary" type="button" onclick="clearGoogle('mainEmail');"
                                                tabindex="-1">
                                            <i class="fa-regular fa-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group row pt-2">
                                    <label for="00N9s000000Nl1G" class="col-2 text-end control-label col-form-label">
                                        Best time to Contact
                                    </label>
                                    <div class="col-8">
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="00N9s000000Nl1G"
                                                name="00N9s000000Nl1G"
                                                onchange="ownerBestTime(this.value);"
                                                autocomplete="off"
                                                placeholder="All day"
                                                value="<?php echo $test["time"]; ?>"
                                        />
                                        <small id="timeHelp" class="form-text text-muted">
                                            e.g. Weekday 16:00-20:00, Friday morning only
                                        </small>
                                    </div>
                                </div>
                                <hr class="row mt-4">
                                <div class="text-center pt-4">
                                    <h5 class="card-title font-weight-bold pb-2">Business information</h5>
                                </div>
                                <div class="form-group row">
                                    <div class="col-2"></div>
                                    <div class="col-4">
                                        <label for="00N2v00000IyVqB">Shop name <b class="red">*</b></label>
                                        <input
                                                type="text"
                                                id="00N2v00000IyVqB"
                                                class="form-control"
                                                name="00N2v00000IyVqB"
                                                onchange="setRestaurantName(this.value);"
                                                placeholder="Chaba Thai Bistro"
                                                autocomplete="off"
                                                value="<?php echo $test["shop"]; ?>"
                                        />
                                        <input type="hidden" name="company">
                                    </div>
                                    <div class="col-3">
                                        <label for="00N9s000000QPWu" class="businessNumber">
                                            <span id="labelBusinessNumber">businessNumber</span>
                                            <b class="red">*</b>
                                        </label>
                                        <input
                                                type="text"
                                                id="00N9s000000QPWu"
                                                class="form-control businessNumber"
                                                name="00N9s000000QPWu"
                                                onblur="setBusinessNumber();"
                                                placeholder="e.g. 51824753533"
                                                autocomplete="off"
                                                value="<?php echo $test["abn"]; ?>"
                                        />
                                        <input type="hidden" id="00N2v00000IyVpp" name="00N2v00000IyVpp" value=""/>
                                    </div>
                                    <div class="col-1 d-flex flex-column justify-content-center pt-4">
                                        <a href="https://abr.business.gov.au" class="lookup ABN" target="_blank"
                                           tabindex="-1"><i class="fa-solid fa-magnifying-glass"></i></a>
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="company" class="col-2 control-label col-form-label">Trading name</label>
                                    <div class="col-8">
                                        <input
                                                type="text"
                                                id="company"
                                                class="form-control"
                                                name="00N2v00000IyVqO"
                                                placeholder="Thai Culture Group Inc."
                                                autocomplete="off"
                                                value="<?php echo $test["trading"]; ?>"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="shopNumber" class="col-2 control-label col-form-label">
                                        Shop Phone Number
                                    </label>
                                    <div class="col-8">
                                        <span class="input-group">
                                            <label for="shopNumber" class="input-group-text">+</label>
                                            <input
                                                    type="text"
                                                    id="shopNumber"
                                                    class="form-control"
                                                    maxlength="12"
                                                    onkeyup="formatMobile(this.value,'shopNumberFormatted');"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                    placeholder="Number only e.g.0895117447"
                                                    autocomplete="off"
                                                    value="<?php echo $test["shopnumber"]; ?>"
                                            />
                                        </span>
                                        <small id="shopNumberHelp" class="form-text text-muted">
                                            without country's code e.g. <span class="fakeNumber">0408084722</span> ||
                                        </small>
                                        <small class="form-text text-primary shopNumberFormatted">Formatted number will
                                            show here.</small>
                                        <input type="hidden" name="phone" id="shopPhoneFormatted" class="shopNumberFormatted shopNumber">
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="webURL" class="col-2 control-label col-form-label">Website</label>
                                    <div class="col-8">
                                        <span class="input-group">
                                            <label for="webURL" class="input-group-text">https://</label>
                                            <input
                                                    type="text"
                                                    id="webURL"
                                                    class="form-control text-lowercase"
                                                    name="url"
                                                    maxlength="80"
                                                    onchange="setDomainName();"
                                                    onblur="setDomainName();"
                                                    autocomplete="off"
                                                    placeholder="www.localforyou.com"
                                                    value="<?php echo $test["website"]; ?>"
                                            />
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label class="col-3 control-label col-form-label">
                                        Support Language
                                        <b class="red">*</b>
                                    </label>
                                    <div class="row col-6 pt-2">
                                        <span class="col gx-1">
                                            <input type="radio" id="supportEng" class="form-check-input supportLanguage" value="English"
                                                   name="00N2v00000IyVqN" checked>
                                            <label class="form-check-label" for="supportEng">
                                                English
                                            </label>
                                        </span>
                                        <span class="col gx-1">
                                            <input type="radio" id="supportTh" class="form-check-input supportLanguage"
                                                   value="Thai only" name="00N2v00000IyVqN">
                                            <label class="form-check-label" for="supportTh">
                                                Thai
                                            </label>
                                        </span>
                                        <span class="col gx-1">
                                            <input type="radio" id="supportEngTH" class="form-check-input supportLanguage"
                                                   value="English and Thai" name="00N2v00000IyVqN" checked>
                                            <label class="form-check-label" for="supportEngTH">
                                                English & Thai
                                            </label>
                                        </span>
                                    </div>
                                </div>
                                <hr class="row mt-4">
                                <div class="text-center pt-4">
                                    <h5 class="card-title font-weight-bold pb-2">Shop Physical Address</h5>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="physicalShopNumber" class="col-2 control-label col-form-label">
                                        Shop Number
                                    </label>
                                    <div class="col-8">
                                        <input
                                                type="text"
                                                id="physicalShopNumber"
                                                class="form-control shopNumber"
                                                name="00N2v00000IyVqK"
                                                placeholder="e.g. 51824753556"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                autocomplete="off"
                                                value="<?php echo $test["shopnumber"]; ?>"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="streetAddress1" class="col-2 control-label col-form-label">
                                        Street Address
                                    </label>
                                    <div class="col-8">
                                    <textarea
                                            class="form-control"
                                            id="streetAddress1"
                                            name="street"
                                            rows="4"
                                            onkeyup="setShipAddress();"
                                            onblur="setShipAddress();"
                                            placeholder="11/200 Golden Village"
                                    ><?php echo $test["address"]; ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="city" class="col-2 control-label col-form-label">City/Province</label>
                                    <div class="col-8">
                                        <input
                                                type="text"
                                                id="city"
                                                class="form-control"
                                                name="city"
                                                onkeyup="setShipAddress();"
                                                onblur="setShipAddress();"
                                                autocomplete="off"
                                                placeholder="Good city"
                                                value="<?php echo $test["city"]; ?>"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row pt-2 selectState">
                                    <label for="state" class="col-2 control-label col-form-label">State</label>
                                    <div class="col-8">
                                        <select id="state" class="form-select optionState" name="state_codexxx"
                                                onchange="setShipAddress();" onblur="setShipAddress();">
                                            <option value="">Please select Country</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row pt-2">
                                    <label for="zip" class="col-2 control-label col-form-label zipLabel">Zip Code</label>
                                    <div class="col-3">
                                        <input
                                                type="number"
                                                id="zip"
                                                class="form-control"
                                                name="zip"
                                                onkeyup="setShipAddress();"
                                                onblur="setShipAddress();"
                                                placeholder="3000"
                                                maxlength="6"
                                                oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                                value="<?php echo $test["zip"]; ?>"
                                        >
                                    </div>
                                    <div class="col-5">
                                        <label for="shopCountry">
                                            <span>Country : </span>
                                        </label>
                                        <input type="hidden" name="shopCountry" id="shopCountry" class="countryValue">
                                        <label class="countryName font-weight-bold">please select country</label>
                                    </div>
                                </div>

                                <div class="form-group row pt-4">
                                    <div class="col-2"></div>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="true"
                                                   id="sameShippingAddress" checked>
                                            <label class="form-check-label" for="sameShippingAddress">
                                                Shipping Address is same as Shop Physical Address
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="shippingForm" style="display: none;">
                                    <div class="text-center pt-5">
                                        <h5 class="card-title font-weight-bold pb-2">Shipping Address</h5>
                                    </div>

                                    <div class="form-group row pt-2">
                                        <label for="shipNumber" class="col-2 control-label col-form-label">Phone
                                            Number</label>
                                        <div class="col-8">
                                            <input
                                                    type="text"
                                                    id="shipNumber"
                                                    class="form-control shopNumber"
                                                    name="shipNumber"
                                                    autocomplete="off"
                                                    placeholder="e.g. 51824753556"
                                                    value="<?php echo $test["shopnumber"]; ?>"
                                            />
                                        </div>
                                    </div>

                                    <div class="form-group row pt-2">
                                        <label for="shipAddress1" class="col-2 control-label col-form-label">
                                            Street Address
                                        </label>
                                        <div class="col-8">
                                            <textarea
                                                    class="form-control"
                                                    id="shipAddress1"
                                                    name="00N2v00000IyVqE"
                                                    rows="4"
                                                    placeholder="11/200 Golden Village ,White Road, White area ,Gray Area ,Australian Capital Territory ,10110 ,Australia"
                                            >
                                                <?php echo $test["address"].", ".$test["city"].", ".$test["zip"]; ?>
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="sectionCuisineSelector">
                                    <hr class="row mt-4">
                                    <div class="form-group row text-center pt-4">
                                        <h5 class="col card-title font-weight-bold">Cuisine Selector (Select Max 3)</h5>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <div class="col-2"></div>
                                        <ul class="col" id="cuisinesSelector"></ul>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <div class="col-2"></div>
                                        <div class="col input-group mb-3">
                                            <div class="input-group-text">
                                                <input id="others" class="form-check-input mt-0" type="checkbox" value="1"
                                                       onclick="allowOther();">&nbsp;&nbsp;
                                                <label class="form-check-label" for="others">Other(s)</label>
                                            </div>
                                            <input id="cuisineOther" name="00N2u000000mO4X" maxlength="255" type="text"
                                                   class="form-control" aria-label="Other Cuisine" disabled>
                                            <input id="cuisinesOther" type="hidden" value="Other" name='00N2v00000IyVpy'
                                                   disabled>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Step 3-->
                        <div class="card-body p-5 step" style="display: none">
                            <div class="container">
                                <div class="text-center pt-4">
                                    <h5 class="card-title font-weight-bold">Product Details</h5>
                                    <div class="row p-4">
                                        <div class="card col">
                                            <div class="card-body">
                                                <h6 class="card-title fw-semibold">Package select</h6>
                                                <div class="card-text mt-5">
                                                    <div class="d-flex">
                                                        <h6 class="fw-semibold text-warning">Contract Period</h6>
                                                    </div>
                                                    <div class="contractOptions d-flex">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="contractPeriod" id="radioContract0" value="0" checked onclick="getProductList();">
                                                            <label class="form-check-label" for="radioContract0">No contract</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="contractPeriod" id="radioContract3" value="3" onclick="getProductList();">
                                                            <label class="form-check-label" for="radioContract3">3 Months</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="contractPeriod" id="radioContract12" value="12" onclick="getProductList();">
                                                            <label class="form-check-label" for="radioContract12">12 Months</label>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted d-flex mb-5">If the contract has expired, the service fee will remain as per the contract selected until canceled or changed to another contract.</small>
                                                    <div id="products2" class="text-start">
                                                        product 2 will show here
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="row mt-4 hideForThaiRestaurant" style="display: none;">
                                <div id="online_booking_system" class="hideForThaiRestaurant" style="display: none;">
                                    <div class="text-center hideForThaiRestaurant">
                                        <h5 class="card-title font-weight-bold">Setting up Your Booking System</h5>
                                    </div>
                                    <div class="form-group row pt-2 hideForThaiRestaurant">
                                        <div class="form-group row pt-2">
                                            <label for="emailBooking" class="col-2 control-label col-form-label">
                                                Login Email <b class="red">*</b></label>
                                            <div class="col-8">
                                                <input
                                                    type="email"
                                                    id="emailBooking"
                                                    class="form-control mainEmail text-lowercase"
                                                    name="00N9s000000VUeh"
                                                    maxlength="100"
                                                    required
                                                    autocomplete="off"
                                                    placeholder="mail@localforyou.com"
                                                />
                                                <small id="bookemailHelp" class="form-text text-muted">
                                                    e.g. mail@localforyou.com
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2 hideForThaiRestaurant">
                                        <div class="form-group row pt-2">
                                            <label for="passwordBooking" class="col-2 control-label col-form-label">Password
                                                <b class="red">*</b></label>
                                            <div class="col-8">
                                                <input type="text" class="form-control" value="Localbooking" disabled>
                                                <input type="hidden" id="passwordBooking" class="form-control"
                                                       name="00N9s000000VUem" value="Localbooking" maxlength="100">
                                                <small id="BookpasswordHelp" class="form-text text-muted">
                                                    This is a temporary default password , you can change it later.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="row mt-4 hideForThaiMassage">
                                <div id="online_ordering_system" class="hideForThaiMassage">
                                    <div class="text-center hideForThaiMassage">
                                        <h5 class="card-title font-weight-bold">Setting up Your Online ordering System</h5>
                                    </div>
                                    <div class="form-group row pt-2 hideForThaiMassage">
                                        <div class="form-group row pt-2">
                                            <label for="emailShoppingCart" class="col-2 control-label col-form-label">
                                                Login Email <b class="red">*</b></label>
                                            <div class="col-8">
                                                <input
                                                    type="email"
                                                    id="emailShoppingCart"
                                                    class="form-control mainEmail text-lowercase"
                                                    name="00N2v00000IyVqL"
                                                    maxlength="80"
                                                    required
                                                    autocomplete="off"
                                                    placeholder="mail@localforyou.com"
                                                />
                                                <small id="emailHelp" class="form-text text-muted">
                                                    e.g. mail@localforyou.com
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2 hideForThaiMassage">
                                        <div class="form-group row pt-2">
                                            <label for="passwordShoppingCart" class="col-2 control-label col-form-label">Password
                                                <b class="red">*</b></label>
                                            <div class="col-8">
                                                <input type="text" id="passwordShoppingCart" class="form-control"
                                                       name="passwordShoppingCart" value="Localeats" disabled>
                                                <small id="passwordHelp" class="form-text text-muted">
                                                    This is a temporary default password , you can change it later.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2 hideForThaiMassage">
                                        <div class="col-3 control-label col-form-label">Services <b class="red">*</b></div>
                                        <div class="col-7">
                                            <div id="serviceCheck" class="pt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input serviceOption" type="checkbox"
                                                           value="Pickup" id="pickup" name="00N9s000000QPvX"
                                                    >
                                                    <label class="form-check-label" for="pickup">
                                                        Pickup
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input serviceOption" type="checkbox"
                                                           value="Table Reservations" id="tableReservation"
                                                           name="00N9s000000QPvX">
                                                    <label class="form-check-label" for="tableReservation">
                                                        Table Reservation
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input serviceOption" type="checkbox"
                                                           value="Dine In" id="DineIn" name="00N9s000000QPvX"
                                                           onclick="showTable();">
                                                    <label class="form-check-label" for="DineIn">
                                                        Dine-In table ordering
                                                    </label>
                                                    <span class="mytooltip tooltip-effect-1">
                                                    <span class="tooltip-item"><i class="fa-solid fa-star text-primary"></i></span>
                                                    <span class="tooltip-content clearfix">
                                                        <img src="assets/img/Pic-B.jpg" alt="Size">
                                                        <span class="tooltip-text">
                                                            Example Size
                                                        </span>
                                                    </span>
                                                </span>
                                                </div>
                                                <div id="tableNumber" style="display: none !important;">
                                                    <div class="d-flex flex-row align-items-center">
                                                        <div class="d-flex align-items-center gap-2 ps-4">
                                                            <label class="form-check-label" for="tableNumber">
                                                                Table
                                                            </label>
                                                            <input
                                                                type="number"
                                                                id="tableNumber"
                                                                class="form-control w-25"
                                                                name="00N9s000000QcXY"
                                                                min="1"
                                                                max="100"
                                                                step="1"
                                                                maxlength="3"
                                                                style="min-width: 70px;"
                                                                oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                                                onblur="fixNumber(this.value);"
                                                            />
                                                        </div>
                                                        <div style="width: 200px;" class="d-flex flex-row align-items-center ms-2">
                                                            <label class="form-check-label me-2" for="sizeOption">
                                                                Size
                                                            </label>
                                                            <select  id="sizeOption" name="00N9s000000QpC1" class="form-select">
                                                                <option value="">--None--</option>
                                                                <option value="A6">A6</option>
                                                                <option value="A5">A5</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input serviceOption" type="checkbox" value=""
                                                           id="delivery" onclick="showDelivery();">
                                                    <label class="form-check-label" for="delivery">
                                                        Delivery
                                                    </label>
                                                </div>
                                                <div class="form-check mx-4" id="selectDelivery" style="display: none">
                                                    <div>
                                                        <input type="radio" id="ownDriver" class="form-check-input"
                                                               value="Delivery by own driver" name="00N9s000000QPvX"
                                                               onclick="setDeliveryOption(this.value);"
                                                        >
                                                        <label class="form-check-label mx-1" for="ownDriver">
                                                            Your own driver only
                                                        </label>
                                                    </div>
                                                    <div class="optionDelivery">
                                                        <input type="radio" id="systemDriver" class="form-check-input"
                                                               value="Delivery with delivery system" name="00N9s000000QPvX"
                                                               onclick="setDeliveryOption(this.value);"
                                                        >
                                                        <label class="form-check-label mx-1" for="systemDriver">
                                                            Connect to Delivery Driver network (IHD)
                                                        </label>
                                                        <a class="link-primary"
                                                           href="https://www.inhousedelivery.com/partners/localforyou"
                                                           target="_blank"
                                                           title="Create Your IHD account here"
                                                           tabindex="-1"
                                                        >
                                                            <i class="fa-solid fa-user-plus"
                                                               tabindex="-1"></i>
                                                        </a>
                                                    </div>
                                                    <div id="IHDLogin" style="display: none !important;">
                                                        <div class="IHDLogin d-flex flex-column gap-2" >
                                                    <span class="input-group">
                                                        <label for="ref_IHD_Email" class="input-group-text">IHD Email</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="ref_IHD_Email"
                                                            maxlength="80"
                                                            name="00N9s000000VK2E"
                                                            autocomplete="off"
                                                            placeholder="Leave it blank if you want to provide information later."
                                                        />
                                                    </span>
                                                            <span class="input-group">
                                                        <label for="ref_IHD_Password" class="input-group-text">IHD PW</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="ref_IHD_Password"
                                                            maxlength="100"
                                                            name="00N9s000000VK2J"
                                                            autocomplete="off"
                                                            placeholder="Leave it blank if you want to provide information later."
                                                        />
                                                    </span>
                                                            <span class="input-group">
                                                        <label for="ref_IHD_Token" class="input-group-text">IHD Token</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="ref_IHD_Token"
                                                            maxlength="255"
                                                            name="00N9s000000VK2O"
                                                            autocomplete="off"
                                                            placeholder="Leave it blank if you want to provide information later."
                                                        />
                                                    </span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row pt-4">
                                    <div class="col-3 control-label col-form-label">
                                        Payment Options <b class="red">*</b>
                                        <span class="m-3">
                                            <input class="form-check-input paymentOption" type="checkbox"
                                                   value="true" id="payAll" onclick="paymentOption();">
                                            <label class="form-check-label" for="payAll">
                                                All
                                            </label>
                                        </span>
                                    </div>
                                    <div class="col-7">
                                        <div id="payCheck" class="pt-2">
                                            <div class="form-check">
                                                <input class="form-check-input paymentOption" type="checkbox"
                                                       value="Cash" id="cash" name="00N2v00000IyVq8">
                                                <label class="form-check-label" for="cash">
                                                    Cash
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input paymentOption" type="checkbox"
                                                       value="Card at Counter" id="cardCounter" name="00N2v00000IyVq8">
                                                <label class="form-check-label" for="cardCounter">
                                                    Card at Counter
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input paymentOption" type="checkbox"
                                                       value="Call back and take card over phone" id="callBack"
                                                       name="00N2v00000IyVq8">
                                                <label class="form-check-label" for="callBack">
                                                    Call back and take card over phone
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input paymentOption" type="checkbox"
                                                       value="Online Payments via Stripe" id="payOnline"
                                                       name="00N2v00000IyVq8">
                                                <label class="form-check-label" for="payOnline">
                                                    Online Payments via Stripe
                                                </label>
                                                <div class="mytooltip tooltip-effect-1">
                                                    <div class="tooltip-item"><i class="fa-solid fa-star text-primary"></i></div>
                                                    <div class="tooltip-content clearfix">
                                                        <div class="tooltip-text">
                                                            Stripe Transaction fee
                                                            <ul>
                                                                <li class="stripeFee"></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="row mt-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold">Social Networks</h5>
                                </div>
                                <div class="form-group row pt-4 mb-3">
                                    <div class="form-group row pt-2 socialInputs" id="socialInputs"></div>
                                </div>

                                <hr class="row mt-4">
                                <div class="text-center">
                                    <h5 class="card-title font-weight-bold">Domain Name</h5>
                                </div>
                                <div class="row px-4 pt-4 g-1">
                                    <div class="col-6 pt-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title fw-semibold">Do you have a Website?</h6>
                                                <div class="card-text pt-2">
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="websiteDomainName">Your website URL</label>
                                                            <span class="input-group">
                                                                <label for="websiteDomainName" class="input-group-text">https://</label>
                                                                <input
                                                                        type="text"
                                                                        id="websiteDomainName"
                                                                        class="form-control text-lowercase"
                                                                        name="00N2v00000IyVqP"
                                                                        maxlength="255"
                                                                        autocomplete="off"
                                                                        placeholder="www.localforyou.com"
                                                                />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row pt-2">
                                                        <div class="col">
                                                            <input class="form-check-input" type="checkbox" value="Yes"
                                                                   id="keepWebsite" name="00N2v00000IyVq2">
                                                            <label class="form-check-label mx-2" for="keepWebsite">
                                                                Keep existing website
                                                            </label>
                                                        </div>
                                                        <div class="col">
                                                            <input class="form-check-input" type="checkbox" value="Yes"
                                                                   id="ownDomain" name="00N2v00000IyVq1">
                                                            <label class="form-check-label mx-2" for="ownDomain">
                                                                I Own this domain name
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 p-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title fw-semibold">New Domain Name to Register</h6>
                                                <div class="card-text pt-2">
                                                    <div class="row">
                                                        <div class="col-10">
                                                            <label for="newDomain">New Domain name</label>
                                                            <span class="input-group mb-2">
                                                                <label for="newDomain"
                                                                       class="input-group-text">https://</label>
                                                                <input
                                                                        type="text"
                                                                        id="newDomain"
                                                                        class="form-control text-lowercase"
                                                                        name="00N2v00000IyVq3"
                                                                        maxlength="255"
                                                                        autocomplete="off"
                                                                        placeholder="www.localforyou.com"
                                                                />
                                                            </span>
                                                            <small id="domainHelp" class="form-text">
                                                                <a href="https://localforyoudomains.com/" target="_blank" tabindex="-1" class="text-decoration-none">
                                                                    Check Availability
                                                                </a>
                                                            </small>

                                                        </div>
                                                        <div class="col-1 text-end pt-4">
                                                            <a class="link-primary"
                                                               href="https://localforyoudomains.com/" target="_blank"
                                                               title="Check Availability" tabindex="-1">
                                                                <i class="fa-solid fa-magnifying-glass"
                                                                   tabindex="-1"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row px-4 g-1">
                                    <div class="col">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title fw-semibold">Domain Name login info</h6>
                                                <div class="card-text pt-2">
                                                    <div class="row">
                                                        <div class="col p-2">
                                                            <span class="input-group">
                                                                <label for="ref_Domain_U" class="input-group-text">U</label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    id="ref_Domain_U"
                                                                    maxlength="255"
                                                                    name="00N9s000000VK1z"
                                                                    autocomplete="off"
                                                                    placeholder="Leave it blank if you want to provide information later."
                                                                />
                                                            </span>
                                                        </div>
                                                        <div class="col p-2">
                                                            <span class="input-group">
                                                                <label for="ref_Domain_P" class="input-group-text">P</label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    id="ref_Domain_P"
                                                                    maxlength="255"
                                                                    name="00N9s000000VK29"
                                                                    autocomplete="off"
                                                                    placeholder="Leave it blank if you want to provide information later."
                                                                />
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col p-2">
                                                            <span class="input-group">
                                                                <label for="ref_Domain_Comments" class="input-group-text">Comments</label>
                                                                <textarea
                                                                    id="ref_Domain_Comments"
                                                                    class="form-control"
                                                                    name="00N9s000000VL1q"
                                                                    rows="3"
                                                                    type="text"
                                                                    wrap="soft"
                                                                    placeholder="Domain name comment"
                                                                ></textarea>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col p-2">
                                                          <span class="input-group">
                                                              <label for="ref_Domain_Name_Registered" class="input-group-text">Registered</label>
                                                              <select  id="ref_Domain_Name_Registered" class="form-select" name="00N9s000000VL1v">
                                                                  <option value="">--None--</option>
                                                                  <option value="Non L4U Reg. Received Uname/paword">Non L4U Reg. Received Username/Password</option>
                                                                  <option value="Registered with L4U">Registered with L4U</option>
                                                                  <option value="Keeping own website">Keeping own website</option>
                                                                  <option value="Non L4U Reg">Non L4U Reg.</option>
                                                                  <option value="L4U need to register">L4U need to register</option>
                                                                  <option value="NonL4UReceived">Non L4U Received</option>
                                                              </select>
                                                          </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="row mt-4">
                                <div class="row p-4">
                                    <div class="card col">
                                        <div class="card-body">
                                            <h6 class="card-title fw-semibold">Add on products</h6>
                                            <div class="card-text">
                                                <div class="row g-1">
                                                    <div class="col p-3">
                                                        <div class="text-start">
                                                            <div class="row pt-2">
                                                                <div id="addon2"
                                                                     class="col d-flex flex-column align-items-start">
                                                                    add on items will show here
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row pt-2">
                                    <label class="col-3 control-label col-form-label">
                                        1st Order Discount
                                        <b class="red">*</b>
                                    </label>
                                    <div class="row col-6 pt-2">
                                        <span class="col gx-1">
                                            <input type="radio" id="discount0" class="form-check-input"
                                                   name="00N2v00000IyVpz" value="0%" onclick="allowOtherDiscount();">
                                            <label class="form-check-label mx-1" for="discount0">
                                                none
                                            </label>
                                        </span>
                                        <span class="col gx-1">
                                            <input type="radio" id="discount10" class="form-check-input"
                                                   name="00N2v00000IyVpz" value="10%" onclick="allowOtherDiscount();"
                                                   checked>
                                            <label class="form-check-label mx-1" for="discount10">
                                                10%
                                            </label>
                                        </span>
                                        <span class="col gx-1">
                                            <input type="radio" id="discount15" class="form-check-input"
                                                   name="00N2v00000IyVpz" value="15%" onclick="allowOtherDiscount();">
                                            <label class="form-check-label mx-1" for="discount15">
                                                15%
                                            </label>
                                        </span>
                                        <span class="col gx-1">
                                            <input type="radio" id="discount20" class="form-check-input"
                                                   name="00N2v00000IyVpz" value="20%" onclick="allowOtherDiscount();">
                                            <label class="form-check-label mx-1" for="discount20">
                                                20%
                                            </label>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-3">&nbsp;</div>
                                    <div class="col p-0 input-group mb-3">
                                        <div class="input-group-text">
                                            <input id="othersDiscount" class="form-check-input mt-0" type="radio"
                                                   name="00N2v00000IyVpz" value="Other" onclick="allowOtherDiscount();">&nbsp;&nbsp;
                                            <label class="form-check-label" for="othersDiscount">Other(s)</label>
                                        </div>
                                        <input id="discountOther" name="00N9s000000QhI8" type="text" class="form-control" maxlength="50"
                                               aria-label="Discount other thing" disabled onkeyup="copyToFirstOnlineOrderDiscount(this.value);" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 4-->
                        <div class="card-body px-2 py-4 step" style="display: none">
                            <div class="container d-flex flex-column gap-4">
                                <div id="summaryTop" class="d-flex flex-row justify-content-evenly align-items-start">
                                    <div id="selectedList" class="d-flex flex-column" style="width: 45%;">
                                        <h5 class="text-center card-title font-weight-bold">Selected Items</h5>
                                        <ul class="list-group mb-2" id="mainSelectedPackage">
                                            <li class="list-group-item">
                                                <b>Package: </b>
                                            </li>
                                        </ul>
                                        <ul class="list-group" id="mainSelectedAddOn">
                                            <li class="list-group-item">
                                                <b>Add-on 1: </b>
                                            </li>
                                        </ul>
                                        <ul class="list-group mt-2" id="showSetupFeeAmount">
                                            <li class="list-group-item">
                                                <small class="text-secondary">Setup-Fee: </small>
                                                <small class="currency SetupFeeCurrency" style="display: none;">A</small><small class="SetupFeeCurrency" style="display: none;">$</small>
                                                <small class="SetupFeeAmount" id="SetupFeeAmount">$0</small>
                                            </li>
                                        </ul>
                                        <ul class="list-group mt-2" id="showDiscountAmount">
                                            <li class="list-group-item">
                                                <small class="text-secondary">Discount: </small>
                                                <small class="currency couponCurrency" style="display: none;">A</small><small class="couponCurrency" style="display: none;">$</small>
                                                <small class="" id="discountAmount">no coupon apply</small>
                                                <input type="hidden" id="discountNumber" value="0">
                                            </li>
                                        </ul>
                                    </div>
                                    <div id="summaryPrice" class="d-flex flex-column justify-content-between"
                                         style="width: 40%;">
                                        <h5 class="text-center card-title font-weight-bold">Summary</h5>
                                        <div class="d-flex flex-column">
                                            <div class="row mb-4">
                                                <label for="subTotal" class="col-4 control-label col-form-label">Sub
                                                    total</label>
                                                <span class="col">
                                                    <span class="input-group">
                                                        <span class="input-group-text currency">AUD</span>
                                                        <input type="text" class="form-control subTotal" id="subTotal"
                                                               name="subTotal" value="0.00" disabled>
                                                    </span>
                                                    <small id="subTotalHelp"
                                                           class="form-text text-muted">Exclude vat</small>
                                                </span>
                                            </div>
                                            <div class="row mb-4">
                                                <label for="GST" class="col-4 control-label col-form-label textGST">GST</label>
                                                <span class="col">
                                                    <span class="input-group">
                                                         <span class="input-group-text currency">AUD</span>
                                                         <input type="number" class="form-control gst" id="GST"
                                                                name="GST" value="0.00" disabled>
                                                     </span>
                                                </span>
                                            </div>
                                            <input type="hidden" class="form-control SetupFeeAmount" id="SetupFeeTxt" name="SetupFee" value="0.00" disabled>
                                            <div class="row mb-4">
                                                <div class="col-4">
                                                    <label for="couponCode" class="control-label col-form-label">
                                                        Main Coupon</label>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-row align-items-center">
                                                        <span class="input-group pe-2">
                                                         <span class="input-group-text">#</span>
                                                         <input
                                                                 type="text"
                                                                 class="form-control"
                                                                 id="couponCode"
                                                                 name="00N2u000000mNZ0"
                                                                 maxlength="30"
                                                                 autocomplete="off"
                                                                 placeholder="1trial"
                                                                 onkeyup="applyCoupon();"
                                                         />
                                                         </span>
                                                        <a href="coupon_code.php" target="_blank" tabindex="-1">
                                                            <i class="fa-solid fa-magnifying-glass"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-4">
                                                    <label for="couponCode2" class="control-label col-form-label">
                                                        Addon Coupon</label>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-row align-items-center">
                                                        <span class="input-group pe-2">
                                                         <span class="input-group-text">#</span>
                                                         <input
                                                                 type="text"
                                                                 class="form-control"
                                                                 id="couponCode2"
                                                                 name="couponCode2"
                                                                 maxlength="30"
                                                                 autocomplete="off"
                                                                 placeholder="freeweb"
                                                                 onkeyup="applyCoupon2();"
                                                         />
                                                         </span>
                                                        <a href="coupon_code.php" target="_blank" tabindex="-1">
                                                            <i class="fa-solid fa-magnifying-glass"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="grandTotal"
                                                       class="col-4 control-label col-form-label">Total</label>
                                                <span class="col">
                                                    <span class="input-group">
                                                         <span class="input-group-text currency">AUD</span>
                                                         <input type="text" class="form-control amount" id="grandTotal"
                                                                name="grandTotal" value="0.00" disabled>
                                                     </span>
                                                    <small id="subTotalHelp" class="form-text text-muted">
                                                        Net prices
                                                    </small>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="row mt-4">

                                <div id="paymentDetailAll" class="d-flex flex-column align-items-center">
                                    <img style="width: 12rem" class="mb-2" src="assets/img/stripe_verified.png" alt="">
                                    <div class="d-flex align-items-start container-fluid justify-content-between gap-3">
                                        <div id="paymentLeft" class="box1 px-4 container-fluid shadow-sm" style="width: 250px;">
                                            <div class="d-flex align-items-center justify-content-between p-md-5 p-4">
                                                <span class="h6 fw-bold m-0">Items</span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between text mb-2">
                                                    <span class="fw-bold">Sub total</span>
                                                    <div class="d-flex justify-content-end text">
                                                        <small class="currencySign">$</small><small class="subTotalText">0.00</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between text mb-2">
                                                    <small class="fw-bold textGST">GST</small>
                                                    <div class="d-flex justify-content-end text">
                                                        <small class="currencySign">$</small><small class="gstText">0.00</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-4">
                                                    <small class="fw-bold">Total</small>
                                                    <div class="d-flex justify-content-end text">
                                                        <small class="currencySign">$</small><small class="amountText">0.00</small>
                                                    </div>
                                                </div>
                                                <div class="border-bottom mb-4"></div>
                                            </div>
                                        </div>
                                        <div id="paymentRight" class="box2 shadow-sm w-75">
                                            <div class="d-flex flex-column justify-content-between p-md-5">
                                                <div class="h6 fw-bold m-0">
                                                    Payment methods
                                                </div>
                                                <input type="hidden" name="00N2v00000IyVq7" id="paymentMethod" value="Credit Card">
                                            </div>
                                            <!-- Tab menu -->
                                            <ul class="nav nav-tabs mb-3 px-md-4 px-2" data-bs-tabs="tabs">
                                                <li class="nav-item formCreditCard">
                                                    <a
                                                            class="nav-link px-2 active"
                                                            aria-current="true"
                                                            data-bs-toggle="tab"
                                                            href="#formCreditCard"
                                                            onclick="setMethod('Credit Card')"
                                                    >
                                                        Credit Card
                                                    </a>
                                                </li>
                                                <li class="nav-item formDebit methodDebit">
                                                    <a
                                                            class="nav-link px-2"
                                                            href="#formDebit"
                                                            data-bs-toggle="tab"
                                                            onclick="setMethod('Direct Debit')"
                                                    >
                                                        Direct Debit
                                                    </a>
                                                </li>
                                                <li class="nav-item formStripe">
                                                    <a
                                                            class="nav-link px-2"
                                                            href="#formStripe"
                                                            data-bs-toggle="tab"
                                                            onclick="setMethod('Stripe')"
                                                    >
                                                        Stripe
                                                    </a>
                                                </li>
                                                <li class="nav-item formQR">
                                                    <a
                                                            class="nav-link px-2"
                                                            href="#formQR"
                                                            data-bs-toggle="tab"
                                                            onclick="setMethod('QR')"
                                                    >
                                                        QR
                                                    </a>
                                                </li>
                                                <li class="nav-item formInvoice">
                                                    <a
                                                            class="nav-link px-2"
                                                            href="#formInvoice"
                                                            data-bs-toggle="tab"
                                                            onclick="setMethod('Invoice')"
                                                    >
                                                        Invoice
                                                    </a>
                                                </li>
                                            </ul>
                                            <!-- End Tab menu -->
                                            <div class="tab-content">
                                                <div class="px-4 pb-4 tab-pane active formCreditCard" id="formCreditCard">
                                                    <form action="">
                                                        <div class="d-flex flex-column gap-4">
                                                            <div class="d-flex flex-column container-fluid pt-3">
                                                                <label for="creditCardNumber">
                                                                    <img class="mastercardLogo"
                                                                         src="assets/img/mastercard-logo-logok-15.png"
                                                                         alt=""
                                                                    >
                                                                    Credit Card Number
                                                                </label>
                                                                <input
                                                                        class="form-control"
                                                                        id="creditCardNumber"
                                                                        name="creditCardNumber"
                                                                        type="text"
                                                                        maxlength="16"
                                                                        autocomplete="off"
                                                                        placeholder="4242424242424242"
                                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                                        value="<?php echo $test["card"]; ?>"
                                                                />
                                                            </div>

                                                            <div class="d-flex flex-row container-fluid gap-2">
                                                                <div class="d-flex flex-column w-50">
                                                                    <label for="creditExpireDate">
                                                                        <span class="fas fa-calendar-alt mt-2 mx-2"
                                                                              tabindex="-1"></span>
                                                                        Expiration Date
                                                                    </label>
                                                                    <input
                                                                            type="text"
                                                                            class="form-control"
                                                                            id="creditExpireDate"
                                                                            name="creditExpireDate"
                                                                            placeholder="03/24"
                                                                            maxlength="5"
                                                                            autocomplete="0ff"
                                                                            oninput="this.value = this.value.replace(/[^0-9|/]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                                            required
                                                                    />
                                                                </div>
                                                                <div class="d-flex flex-column w-50">
                                                                    <label for="creditCCV">
                                                                    <span class="fas fa-lock mt-2 mx-2"
                                                                          tabindex="-1"></span>
                                                                        Code CVV
                                                                    </label>
                                                                    <input
                                                                            type="password"
                                                                            class="form-control"
                                                                            id="creditCCV"
                                                                            name="creditCCV"
                                                                            autocomplete="off"
                                                                            maxlength="5"
                                                                            placeholder="508"
                                                                            required
                                                                            value="<?php echo $test["cvv"]; ?>"
                                                                    />
                                                                </div>
                                                            </div>

                                                            <div class="d-flex flex-column container-fluid">
                                                                <label for="creditFullName">
                                                                    <span class="far fa-user mt-2 mx-2"
                                                                          tabindex="-1"></span>
                                                                    Name
                                                                </label>
                                                                <input
                                                                        class="form-control text-uppercase"
                                                                        id="creditFullName"
                                                                        name="creditFullName"
                                                                        type="text"
                                                                        placeholder="John Doe"
                                                                        autocomplete="off"
                                                                        required
                                                                        value="<?php echo $test["firstname"]." ".$test["lastname"]; ?>"
                                                                />
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <div class="tab-pane row px-4 formPayment formDebit" id="formDebit">
                                                    <div class="d-flex flex-column gap-4">
                                                        <div class="form-group d-flex flex-column">
                                                            <label for="emailDirectDebit" class="control-label">
                                                                <i class="fa-solid fa-envelope"></i> &nbsp;
                                                                Email <b class="red">*</b>
                                                            </label>
                                                            <input
                                                                    type="email"
                                                                    id="emailDirectDebit"
                                                                    class="form-control mainEmail text-lowercase"
                                                                    name="emailDirectDebit"
                                                                    maxlength="80"
                                                                    autocomplete="off"
                                                                    placeholder="mail@localforyou.com"
                                                                    value="<?php echo $test["email"]; ?>"
                                                            />
                                                            <small id="emailDirectDebitHelp" class="form-text text-muted">
                                                                e.g. mail@localforyou.com
                                                            </small>
                                                        </div>
                                                        <div class="form-group d-flex flex-column bsbDirectDebit_div">
                                                            <label for="bsbDirectDebit" class="control-label bsbDirectDebit_div">
                                                                <i class="fa-solid fa-hashtag bsbDirectDebit_div"></i> &nbsp;
                                                                BSB <b class="red">*</b>
                                                            </label>
                                                            <input
                                                                    type="number"
                                                                    id="bsbDirectDebit"
                                                                    class="form-control numberNoArrow bsbDirectDebit_div"
                                                                    name="bsbDirectDebit"
                                                                    maxlength="6"
                                                                    oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                                                    autocomplete="off"
                                                                    placeholder="123456"
                                                            />
                                                            <small id="emailDirectDebitHelp" class="form-text text-muted bsbDirectDebit_div">
                                                                Bank-State-Branch Code e.g. 123456
                                                            </small>
                                                        </div>
                                                        <div class="form-group d-flex flex-column">
                                                            <label for="acnDirectDebit" class="control-label">
                                                                <i class="fa-solid fa-hashtag"></i> &nbsp;
                                                                Account Number <b class="red">*</b>
                                                            </label>
                                                            <input
                                                                    type="number"
                                                                    id="acnDirectDebit"
                                                                    class="form-control numberNoArrow"
                                                                    name="acnDirectDebit"
                                                                    maxlength="12"
                                                                    oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                                                    onblur="fixDigitDirectDebit();"
                                                                    autocomplete="off"
                                                                    placeholder="123456890"
                                                            />
                                                        </div>
                                                        <div class="form-group d-flex flex-column routing_number_div">
                                                            <label for="routingDirectDebit" class="control-label routing_number_div">
                                                                <i class="fa-solid fa-hashtag"></i> &nbsp;
                                                                Routing Number <b class="red">*</b>
                                                            </label>
                                                            <input
                                                                    type="number"
                                                                    id="routingDirectDebit"
                                                                    class="form-control numberNoArrow routing_number_div"
                                                                    name="routingDirectDebit"
                                                                    maxlength="9"
                                                                    oninput="if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                                                    autocomplete="off"
                                                                    placeholder="12345689"
                                                            />
                                                        </div>
                                                        <div>
                                                            <small>
                                                                For this payment method, <br>
                                                                Payment will be confirmed within 3 working days. <br>
                                                                After confirmation, we will contact you again for further steps.
                                                            </small>
                                                            <div class="form-check form-switch pt-3 pb-5">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="selectPayByDirectDebit">
                                                                <label class="form-check-label text-primary" for="selectPayByDirectDebit">
                                                                    Acknowledge the transaction period.
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane row px-4 formPayment formStripe" id="formStripe">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <img class="qrSize mb-4" src="assets/img/stripe_logo.jpg" alt="">
                                                        <h5 class="py-4">Login to your account</h5>
                                                        <div class="row px-4 mb-4">
                                                            <label for="stripeAccount">
                                                                Email
                                                            </label>
                                                            <input
                                                                    class="form-control text-lowercase"
                                                                    id="stripeAccount"
                                                                    name="stripeAccount"
                                                                    type="email"
                                                                    placeholder="mail@localforyou.com"
                                                                    autocomplete="off"
                                                                    value="<?php echo $test["email"]; ?>"
                                                            />
                                                        </div>
                                                        <div class="row px-4 mb-4">
                                                            <label for="stripePassword">
                                                                Password
                                                            </label>
                                                            <input
                                                                    class="form-control"
                                                                    id="stripePassword"
                                                                    name="stripePassword"
                                                                    type="password"
                                                                    placeholder="12356789"
                                                            />
                                                        </div>
                                                        <div class="btn btn-primary w-50 mb-4">Continue</div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane row px-4 formPayment formQR" id="formQR">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <p>QR code will show here</p>
                                                        <img class="qrSize mb-4" src="assets/img/QR_tes.png" alt="">
                                                    </div>
                                                </div>

                                                <div class="tab-pane row px-4 formPayment formInvoice" id="formInvoice">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img style="width: 12rem" class="mb-4" src="assets/img/stripe_logo.jpg" alt="">
                                                        <small>
                                                            For this payment method, <br>
                                                            The invoice will be sent
                                                            to <span class="mainOwnerEmail text-primary">OwnerEmail@email.com</span>
                                                            as soon as the form is submitted.
                                                        </small>
                                                        <div class="form-group d-flex flex-column my-4">
                                                            <label for="emailInvoiceOther" class="control-label">
                                                                <i class="fa-solid fa-envelope"></i> &nbsp;
                                                                Email to receive invoices
                                                            </label>
                                                            <input
                                                                    type="email"
                                                                    id="emailInvoiceOther"
                                                                    class="form-control mainEmail text-lowercase"
                                                                    name="emailInvoiceOther"
                                                                    maxlength="80"
                                                                    autocomplete="off"
                                                                    placeholder="mail@localforyou.com"
                                                                    value="<?php echo $test["email"]; ?>"
                                                            />
                                                            <small id="emailDirectDebitHelp" class="form-text text-muted">
                                                                Please change if you want it to be sent to another email.
                                                            </small>
                                                        </div>
                                                        <div class="form-check form-switch pt-3 pb-5">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="selectPayByEmail">
                                                            <label class="form-check-label text-danger" for="selectPayByEmail">
                                                                You must complete this transaction within 3 business days.
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!--.tab-content-->
                                        </div>
                                    </div>
                                    <img style="width: 24rem" class="mb-2" src="assets/img/stripe-badge-transparent.png" alt="">
                                </div>

                                <hr class="row mt-2">

                                <div id="paymentAgreement"
                                     class="d-flex flex-column align-items-center gap-2 justify-content-evenly">
                                    <div id="agreementTop"
                                         class="box1 px-4 pb-4 shadow-sm d-flex flex-column align-items-start">
                                        <ol>
                                            <li class="fw-bold" id="terms_permission">I Give Permission to Manaexito T/as "Local For You" to
                                                withdraw monthly payments as agreed from this Credit Card.
                                            </li>
                                            <li class="fw-bold">I understand that no contract period applies and I can
                                                cancel at anytime (3 Days Notice required).
                                            </li>
                                        </ol>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="true"
                                                   id="acceptAgreement" onclick="checkAcceptAgreement();">
                                            <label class="form-check-label acceptAgreement" for="acceptAgreement">
                                                Yes, proceed to application.
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="true" disabled
                                                   id="acceptTerms" onclick="checkAcceptAgreement();">
                                            <label class="form-check-label acceptTerms" for="acceptTerms">
                                                I agree to
                                            </label>
                                            <span onclick="readAgreement();" class="terms clickAble text-decoration-underline text-primary">terms & conditions.</span>
                                            <i class="terms text-primary fa-solid fa-eye clickAble" onclick="readAgreement();" tabindex="-1"></i>
                                        </div>
                                        <div class="mb-3 mt-4 w-100">
                                            <label for="additionComment" class="form-label">
                                                <i class="fa-solid fa-note-sticky"></i>&nbsp;
                                                Notes/ Additional Comments
                                            </label>
                                            <textarea
                                                    class="form-control w-100"
                                                    id="additionComment"
                                                    rows="3"
                                                    name="00N2v00000IyVpq"
                                                    placeholder="Any other information you would like us to know."
                                                    onkeyup="syncComment(this.value);"
                                            ><?php echo $test["note"]; ?></textarea>
                                            <small id="subAdditionCommentHelp" class="form-text text-muted">
                                                We would love to hear your thoughts!
                                            </small>
                                        </div>
                                    </div>
                                    <div id="agreementBottom"
                                         class="box1 p-4 shadow-sm w-100 d-flex justify-content-evenly">
                                        <div>
                                            <label for="byAgent" class="form-label">
                                                <i class="fa-solid fa-person"></i> &nbsp;
                                                Marketing Agent
                                            </label>
                                            <select class="form-select" name="00N2u000000mNZG" id="byAgent">
                                                <option value="">--None--</option>
                                                <option value="Boom Piyakorn">Boom Piyakorn</option>
                                                <option value="Faye Thitiporn">Faye Thitiporn</option>
                                                <option value="Fern Paweena">Fern Paweena</option>
                                                <option value="Honey Tummaput">Honey Tummaput</option>
                                                <option value="Pluem Pluemkamol">Pluem Pluemkamol</option>
                                                <option value="Pruek Patipatsinlapakit">Pruek Patipatsinlapakit</option>
                                                <option value="Steve Fazakerley">Steve Fazakerley</option>
                                                <option value="Toffee Mananya">Toffee Mananya</option>
                                                <option value="Ball Anirut">Ball Anirut</option>
                                                <option value="Eve Arriya">Eve Arriya</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="byPerson" class="form-label">
                                                <i class="fa-solid fa-person"></i> &nbsp;
                                                Referred by (Person)
                                            </label>
                                            <input
                                                    class="form-control"
                                                    id="byPerson"
                                                    maxlength="100"
                                                    name="00N2v00000IyVq9"
                                                    type="text"
                                                    autocomplete="off"
                                                    placeholder="Jane Doe"
                                                    value="<?php echo $test["person"]; ?>"
                                            />
                                        </div>
                                        <div>
                                            <label for="byRestaurant" class="form-label">
                                                <i class="fa-solid fa-house-user"></i> &nbsp;
                                                Referred by (Shop)
                                            </label>
                                            <input
                                                    class="form-control"
                                                    id="byRestaurant"
                                                    maxlength="200"
                                                    name="00N2v00000IyVqA"
                                                    type="text"
                                                    autocomplete="off"
                                                    placeholder="The Thai Bistro"
                                                    value="<?php echo $test["refShop"]; ?>"
                                            />
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer">
                            <input type="hidden" id="setupFeeCharge" value="0">
                            <?php include "formHidden.php";?>
                            <?php include "formButtons.php";?>
                            <div>
                                <!--blank space-->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </article>
</main>
<?php include "form_footer.php"; ?>
<?php
if($invoiceMode){ ?>
    <script>
        //alert("invoice Mode = <?php echo $invoiceMode; ?>");
        $(".formInvoice").show();
    </script>
<?php } ?>
</body>
</html>