<?php
$testMode = false;
include("assets/function/testMode.php");
?>
<!doctype html>
<html lang="en">
<head>
    <?php require_once "../../assets/api/googleAnalytics.php";?>
    <title>L4U - Services</title>
    <?php include "form_header.php"; ?>
</head>
<body>

<header class="intro">
    <div class="d-flex flex-row align-items-center justify-content-center">
        <img src="assets/img/newL4U-logo-100x100.png" alt="L4U Logo" style="width: 100px; height: 100px;">
        <div class="d-flex flex-column">
            <h1>Local For You Services</h1>
            <p>Self-Registering.</p>
        </div>
    </div>
</header>

<main style="min-height: 68vh;">
    <?php include("modalRespond.php"); ?>
    <?php include("modalTerms.php"); ?>

    <article>
        <div class="container d-flex justify-content-center" style="min-width:720px!important">
            <div class="col-11 col-offset-2">
                <?php include "progress_bar.php"; ?>
                <form id="myForm" action="https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8"
                      method="POST">
                    <input type=hidden name="oid" value="00D2v0000012UyV">
                    <input type=hidden id="redirectURL" name="retURL"
                           value="https://signup.localforyou.com/mini/thankyou.php">
                    <input type=hidden name="recordType" value="012Mq000000ZPun">
                    <input type=hidden name="lead_source" value="Self-Registering">
                    <input type="hidden" id="leadStage" name="00N2u000000mQgE" value="New">
                    <input type=hidden name="currency" value="AUD">
                    <!--
                    <input type="hidden" name="debug" value=1>
                    <input type="hidden" name="debugEmail" value="neung@localforyou.com">
                    -->

                    <!-- All Form -->
                    <div class="card mt-3">
                        <div class="card-header font-weight-bold">Online Subscription Form</div>
                        <!-- Step 1-->
                        <div id="mainSetup" class="card-body p-4 step">
                            <div class="text-center">
                                <h5 class="card-title font-weight-bold pb-2">Shop type and Location</h5>
                            </div>
                            <div class="text-center firstStepFormLoading">
                                <small class="text-secondary">The form will be ready in seconds. ... <img alt='Loading' src='assets/img/loading.gif'></small>
                            </div>
                            <div class="form-group row firstStepForm" style="display: none;">
                                <div class="col-2">
                                        Country <b class="red">*</b>
                                </div>
                                <div class="col-6">
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check country_code" name="country_code" id="country_code_AU" value="AU" checked>
                                        <label class="btn btn-outline-primary" for="country_code_AU">Australia</label>

                                        <input type="radio" class="btn-check country_code" name="country_code" id="country_code_NZ" value="NZ">
                                        <label class="btn btn-outline-primary" for="country_code_NZ">New Zealand</label>

                                        <input type="radio" class="btn-check country_code" name="country_code" id="country_code_US" value="US">
                                        <label class="btn btn-outline-primary" for="country_code_US">United States</label>
                                    </div>

                                    <input type="hidden" name="00N2v00000IyVqF" id="countryTextOnly" value="Australia">
                                </div>
                            </div>
                            <div class="form-group row pt-2 firstStepForm">
                                <div class="col-2">
                                        industrial Type <b class="red">*</b>
                                </div>
                                <div class="col-6">

                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check formType" name="00N9s000000QRyY" id="formType_rest" value="Thai Restaurants &amp; Takeaways" checked onclick="setCountry();">
                                        <label class="btn btn-outline-primary" for="formType_rest">Thai Restaurants & Takeaways</label>

                                        <input type="radio" class="btn-check formType" name="00N9s000000QRyY" id="formType_mas" value="Thai Massage" onclick="setCountry();">
                                        <label class="btn btn-outline-primary" for="formType_mas">Thai Massage</label>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 step" style="display: none">

                            <div class="container">
                                <div class="text-center mt-4">
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
                                                        placeholder="Number only e.g.0897117227"
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
                                                placeholder="mail@gmail.com"
                                                value="<?php echo $test["email"]; ?>"
                                        />
                                        <small id="emailHelp" class="form-text text-muted">
                                            e.g. mail@gmail.com
                                        </small>
                                    </div>
                                    <div class="col-1 d-flex flex-row pb-4">
                                        <button class="btn text-primary" type="button" onclick="addGoogle('mainEmail');"
                                                tabindex="-1">
                                            <i class="fa-brands fa-google"></i>
                                        </button>
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
                                    <div class="col-8">
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
                                </div>

                                <div class="form-group row pt-2">
                                    <label for="webURL" class="col-2 control-label col-form-label">Shop Website</label>
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
                                                    placeholder="If you don't have a website Leave it blank."
                                                    value="<?php echo $test["website"]; ?>"
                                            />
                                        </span>
                                        <small id="timeHelp" class="form-text text-muted">
                                            e.g. www.localforyou.com
                                        </small>
                                    </div>
                                </div>

                                <hr class="row mt-4">

                            </div>
                        </div>

                        <!-- Step 3-->
                        <div class="card-body p-3 step" style="display: none">
                            <div class="container">
                                <div class="pt-4">
                                    <h5 class="card-title font-weight-bold text-center">Product Details</h5>
                                    <div class="row p-4">
                                        <div class="card col">
                                            <div class="card-body">
                                                <div class="card-text">
                                                    <div id="products2" class="text-start">
                                                        product 2 will show here
                                                    </div>
                                                    <div id="productsDescription" class="text-start">
                                                        product Description show here
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center mt-3">
                                            <h5 class="card-title font-weight-bold text-center">Other product offers</h5>
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="card-text d-flex flex-column justify-content-start">
                                                        <div class="form-check text-left">
                                                            <input name="00NMq000000EulV" class="form-check-input" type="checkbox" value="true" id="interestedOnlineMarketing">
                                                            <label class="form-check-label" for="interestedOnlineMarketing">
                                                                I'm interested in promoting my store through online marketing.
                                                            </label>
                                                        </div>
                                                        <div class="form-check text-left">
                                                            <input name="00NMq000000EuK6" class="form-check-input" type="checkbox" value="true" id="interestedWebsite">
                                                            <label class="form-check-label" for="interestedWebsite">
                                                                I'm interested in creating a new website or website makeover.
                                                            </label>
                                                        </div>
                                                        <div class="form-check text-left">
                                                            <input name="00NMq000000Eun7" class="form-check-input" type="checkbox" value="true" id="interestedMarketingMaterials">
                                                            <label class="form-check-label" for="interestedMarketingMaterials">
                                                                I am interested in making marketing materials such as flyers, stickers, fridge magnets.
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="row mt-4">
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
                                        <ul class="list-group mt-2" id="showDiscountAmount">
                                            <li class="list-group-item">
                                                <small class="text-secondary">Discount: </small>
                                                <small class="currency couponCurrency" style="display: none;">A</small><small class="couponCurrency" style="display: none;">$</small>
                                                <small class="" id="discountAmount">no coupon apply</small>
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
                                            <div class="row mb-4">
                                                <div class="col-4">
                                                    &nbsp;
                                                    <label for="couponCode" class="control-label col-form-label" style="display:none;">
                                                        Main Coupon</label>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-row align-items-center">
                                                        &nbsp;
                                                        <span class="input-group pe-2" style="display:none;">
                                                         <span class="input-group-text" style="display:none;">#</span>
                                                         <input
                                                                 type="text"
                                                                 class="form-control"
                                                                 id="couponCode"
                                                                 name="00N2u000000mNZ0"
                                                                 maxlength="30"
                                                                 autocomplete="off"
                                                                 placeholder="1trial"
                                                                 value="1TRIAL"
                                                                 onkeyup="applyCoupon();"
                                                                 style="display:none;"
                                                         />
                                                         </span>
                                                        <a href="coupon_code.php" target="_blank" tabindex="-1" style="display:none;">
                                                            <i class="fa-solid fa-magnifying-glass"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-4">
                                                    &nbsp;
                                                    <label for="couponCode2" class="control-label col-form-label" style="display:none;">
                                                        Addon Coupon</label>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-row align-items-center">
                                                        &nbsp;
                                                        <span class="input-group pe-2" style="display:none;">
                                                         <span class="input-group-text" style="display:none;">#</span>
                                                         <input
                                                                 type="text"
                                                                 class="form-control"
                                                                 id="couponCode2"
                                                                 name="couponCode2"
                                                                 maxlength="30"
                                                                 autocomplete="off"
                                                                 placeholder="freeweb"
                                                                 onkeyup="applyCoupon2();"
                                                                 style="display:none;"
                                                         />
                                                         </span>
                                                        <a href="coupon_code.php" target="_blank" tabindex="-1" style="display:none;">
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
                                                        <small>$</small><small class="subTotalText">0.00</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between text mb-2">
                                                    <small class="fw-bold textGST">GST</small>
                                                    <div class="d-flex justify-content-end text">
                                                        <small>$</small><small class="gstText">0.00</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-4">
                                                    <small class="fw-bold">Total</small>
                                                    <div class="d-flex justify-content-end text">
                                                        <small>$</small><small class="amountText">0.00</small>
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
                                                <input type="hidden" name="00N2v00000IyVq7" id="paymentMethod"
                                                       value="Credit Card"> <!--payment method value in Salesforce-->
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
                                        <div style="display: none;">
                                            <label for="byAgent" class="form-label">
                                                <i class="fa-solid fa-person"></i> &nbsp;
                                                Marketing Agent
                                            </label>
                                            <select class="form-select" name="00N2u000000mNZG" id="byAgent">
                                                <option value="" selected>--None--</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer">
                            <input type="hidden" id="firstTimePayment" name="00N2u000000mNZ5">
                            <input type="hidden" id="customerStripeEmail" class="Customer Stripe Email"
                                   name="00N9s000000PDD3" value="">
                            <input type="hidden" id="customerStripeID" class="customerStripeID" name="00N9s000000QYtB"
                                   value="">
                            <input type="hidden" id="customerStripeIDUSA" class="customerStripeIDUSA" name="00N9s000000VWOl"
                                   value="">
                            <input type="hidden" id="myIP">
                            <input type="hidden" id="agent">
                            <input type="hidden" name="00N9s000000QQl5" id="selectedPackages">
                            <input type="hidden" id="usageMainDiscountCode">
                            <input type="hidden" id="usageAddonDiscountCode">

                            <select style="display: none;"  id="currentlyPackage" name="00N9s000000QgXl" title="Hidden Currently Package">
                                <option value="Pro Online Ordering System">Pro Online Ordering System</option>
                                <option value="Social Media Bundle">Social Media Bundle</option>
                                <option value="Direct Marketing Bundle">Direct Marketing Bundle</option>
                                <option value="Social Media Marketing Solo">Social Media Marketing Solo</option>
                                <option value="Direct Marketing Solo">Direct Marketing Solo</option>
                                <option value="Mega Marketing Solo">Mega Marketing Solo</option>
                                <option value="Website Make Over">Website Make Over</option>
                            </select>
                            <select style="display: none;" id="ihdDirectDelivery" name="00N9s000000QgpB" title="Hidden IHD - Direct Delivery">
                                <option value="">--None--</option>
                                <option value="Requested">Requested</option>
                                <option value="Setting up">Setting up</option>
                                <option value="Live">Live</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <select style="display: none;" id="shopsOwnDriver" name="00N9s000000QgpG" title="Hidden Shops Own Driver">
                                <option value="">--None--</option>
                                <option value="Requested">Requested</option>
                                <option value="Setting up">Setting up</option>
                                <option value="Live">Live</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <select style="display: none;" id="referredSupplier" name="00N9s000000QhJa" title="Referred Supplier">
                                <option value="">--None--</option>
                                <option value="Smile Dinning">Smile Dinning</option>
                                <option value="Wawio">Wawio</option>
                            </select>
                            <input type="hidden" id="signupFormVersion" maxlength="80" name="00N9s000000VWbf" value="1.5.0" />
                            <input type="hidden" id="paymentRequestTimestamp" maxlength="100" name="00N9s000000QhRe" />
                            <input type="hidden" name="00N9s000000Qlfw" id="initPackage" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000Qlg1" id="initDineInTableOrdering" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QlgB" id="initAddOnPrintedFlyers" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QlgG" id="initAddOnFridgeMagnet" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000Qlgf" id="initAddOnAdvPromo" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QlgV" id="initAddOnSocialMediaPosts" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000Qlga" id="initAddOnInfluencer" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QlgL" id="initAddOnDineInSystem" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QlgQ" id="initAddOnDigitalMenuDesign" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000Qlgk" id="initAddOnWebsiteMakeOver" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000Qlgp" id="initAddOnWebHosting" class="initProject" maxlength="255">
                            <input type="hidden" name="00N9s000000QuiM" id="FirstOnlineOrderDiscount" maxlength="50" value="10%" />
                            <select style="display: none;" name="00N9s000000Qlgz"
                                    id="initIHDDirectDelivery" class="initProject">
                                <option value="">--None--</option>
                                <option value="Requested">Requested</option>
                                <option value="Setting up">Setting up</option>
                                <option value="Live">Live</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <select style="display: none;" name="00N9s000000Qlgu"
                                    id="initShopsOwnDriver" class="initProject">
                                <option value="">--None--</option>
                                <option value="Requested">Requested</option>
                                <option value="Setting up">Setting up</option>
                                <option value="Live">Live</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <input style="display: none;" type="checkbox" name="00N9s000000Qlg6" id="chkInitPickup" class="initProject" value="Pickup">
                            <input style="display: none;" type="checkbox" name="00N9s000000Qlg6" id="chkInitDineIn" class="initProject" value="Dine In">
                            <input style="display: none;" type="checkbox" name="00N9s000000Qlg6" id="chkInitTableReservations" class="initProject" value="Table Reservations">
                            <input style="display: none;" type="checkbox" name="00N9s000000Qlg6" id="chkInitOwnerDriver" class="initProject initDriver" value="Delivery by own driver">
                            <input style="display: none;" type="checkbox" name="00N9s000000Qlg6" id="chkInitSystemDriver" class="initProject initDriver" value="Delivery with delivery system">
                            <select style="display: none;" name="00N9s000000QnH3" id="initialProductOffering">
                                <option value="">--None--</option>
                                <option value="Promotions - Pro + $1 Trial">Promotions - Pro + $1 Trial</option>
                                <option value="Promotions - Booking System + Freeweb">Promotions - Booking System + Freeweb</option>
                            </select>
                            <div class="btn-row">
                                <button type="button" class="action back btn btn-sm btn-outline-warning"
                                        style="display: none">Back
                                </button>
                                <button type="button" class="action next btn btn-sm btn-outline-danger float-end"
                                        disabled>Next
                                </button>
                                <div class="float-end">
                                    <span class="paymentResult"></span>
                                    <button type="submit" class="action submit btn btn-sm btn-outline-danger"
                                            id="cmdSubmit" style="display: none" disabled>Submit your application
                                    </button>
                                </div>
                            </div>
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
</body>
</html>