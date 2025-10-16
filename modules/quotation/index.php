<?php
global $settings;
date_default_timezone_set("Asia/Bangkok");
include ("form_settings.php");
global $test;
$testMode = !empty($_GET['testMode']) ? $_GET['testMode'] : false;

$QuotationMode = !empty($_GET['Quotation']) ? $_GET['Quotation'] : true;
$currentDate = date('d/m/Y');
$dateProject = date('Y-m-d', strtotime('+14 day', strtotime(date('Y/m/d'))));
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <title>Quotation</title>
    <?php include "form_header.php"; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        .backupButton{
            width: 100%;
            border: none;
            color: white;
            padding: 10px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
</head>
<body class="pt-3" id="topForm">
<?php include ("navBar.php");?>
<br>
<!-- Begin page content -->
<main class="d-flex flex-column">
    <div class="container">
        <?php include("modalRespond.php"); ?>
        <?php include("modalTerms.php"); ?>
        <?php include("modalSecretSetup.php"); ?>
        <article>

            <div id="mySidebar" class="sidebar">
                <a href="javascript:void(0)" class="btnClose toolElements mt-5" onclick="toggleLeftNav();">×</a>
                <div class="d-flex flex-column px-4 h-100 pb-4 justify-content-between mt-3">
                    <div class="d-flex flex-column">
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
                    <div>
                        <small class="text-white fw-bold">Initial Payment</small>
                        <button type="button" id="backupFormAU" class="btn btn-secondary backupButton" onclick="backupPayment('AU');" >AU</button>
                        <button type="button" id="backupFormNZ" class="btn btn-secondary backupButton" onclick="backupPayment('NZ');" >NZ</button>
                        <button type="button" id="backupFormUK" class="btn btn-secondary backupButton" onclick="backupPayment('UK');" >UK</button>
                        <button type="button" id="backupFormUS" class="btn btn-secondary backupButton" onclick="backupPayment('US');" >US</button>
                        <button type="button" id="backupFormCA" class="btn btn-secondary backupButton" onclick="backupPayment('CA');" >CA</button>
                        <button type="button" id="backupFormTH" class="btn btn-secondary backupButton" onclick="backupPayment('TH');" >TH</button>
                    </div>
                </div>
            </div>

            <div class="container d-flex justify-content-center" style="min-width:720px!important">
                <div class="col-11 col-offset-2">
                    <?php include "progress_bar.php"; ?>
                    <form id="myForm" action="<?php echo $settings["formAction"]; ?>"
                          method="POST">


                        <!-- All Form -->
                        <div class="card mt-3">
                            <div class="card-header font-weight-bold">Quotation Form</div>
                            <!-- Step 1-->
                            <div id="mainSetup" class="card-body p-4 step">
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

                                            <option  value="" >Please select Country</option>
<!--                                            <option value="AU">Australia</option>
                                            <option value="CA">Canada</option>
                                            <option value="NZ">New Zealand</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="US">United States</option>-->
                                            <option value="TH" >Thailand</option>
                                        </select>
                                        <input type="hidden" name="countryTextOnly" id="countryTextOnly">
                                    </div>
                                    <div class="col-2 d-flex align-items-center">
                                        <span id="loadingAjax"></span>
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
                                        <select id="formType" class="form-select" name="formType">
                                            <option selected value="" disabled>--None--</option>
                                            <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants &amp; Takeaways</option>
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
                            <div class="card-body p-5 step" style="display: none">
                                <div class="container">
                                    <div class="text-center">
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

                                        <div class="row p-4">
                                            <div class="card col">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-semibold">Initial service</h6>
                                                    <div class="card-text mt-5">
                                                        <div class="d-flex">
                                                            <h6 class="fw-semibold text-warning">Setup Fee</h6>
                                                        </div>
                                                        <div id="setUpFeeList" class="text-start">
                                                            set up fee will show here
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
                                </div>
                            </div>


                            <!-- Step 3-->
                            <div class="card-body px-2 py-4 step" style="display: none">
                                <div class="container d-flex flex-column gap-4">
                                    <div id="summaryTop" class="d-flex flex-row justify-content-evenly align-items-start">
                                        <div id="selectedList" class="d-flex flex-column" style="width: 45%;">
                                            <h5 class="text-center card-title font-weight-bold">Cart Items</h5>
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
                                            <!--<ul class="list-group mt-2" id="showSetupFeeAmount">
                                                <li class="list-group-item">
                                                    <small class="text-secondary">Setup-Fee: </small>
                                                    <small class="currency SetupFeeCurrency" style="display: none;">A</small><small class="SetupFeeCurrency" style="display: none;">$</small>
                                                    <small class="SetupFeeAmount" id="SetupFeeAmount">$0</small>
                                                </li>
                                            </ul>-->
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
                                                                     name="couponCode"
                                                                     maxlength="30"
                                                                     autocomplete="off"
                                                                     placeholder="1trial"
                                                                     onkeyup="applyCoupon();"
                                                             />
                                                             </span>
                                                            <!--                                                            <a href="coupon_code.php" target="_blank" tabindex="-1">-->
                                                            <!--                                                                <i class="fa-solid fa-magnifying-glass"></i>-->
                                                            <!--                                                            </a>-->
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
                                                            <!--                                                            <a href="coupon_code.php" target="_blank" tabindex="-1">-->
                                                            <!--                                                                <i class="fa-solid fa-magnifying-glass"></i>-->
                                                            <!--                                                            </a>-->
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

                                    <div class="quotationDetail">
                                        <hr class="row mt-2">
                                        <div class="row align-items-center justify-content-center">
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="yes"
                                                                   id="quotationYes" onclick="wantTax();" checked>
                                                            <label class="form-check-label quotationYes" for="quotationYes">
                                                                ต้องการใบเสนอราคา
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="quotationContact">

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input type="radio" id="individual" class="form-check-input" name="taxType" value="นิติบุคคล" onclick="quolegalEntity();">
                                                                <label class="form-check-label" for="individual" >นิติบุคคล</label>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input type="radio" id="legalEntity" class="form-check-input" name="taxType" value="บุคคลธรรมดา" onclick="quolegalEntity();">
                                                                <label class="form-check-label" for="legalEntity">บุคคลธรรมดา</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="forIndividual">
                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label for="quotationShopName">
                                                                    <span>ชื่อบริษัท</span>
                                                                </label>
                                                                <input
                                                                        type="text"
                                                                        id="quotationShopName"
                                                                        class="quotationShopName form-control"
                                                                        name="quotationShopName"
                                                                        placeholder="ร้าน แซ่บตำนัว"
                                                                >
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2" id="nameQuotation">
                                                            <div class="col">
                                                                <label for="quotationName">
                                                                    <span>ชื่อ</span>
                                                                </label>
                                                                <input
                                                                        type="text"
                                                                        id="quotationName"
                                                                        class="quotationName form-control"
                                                                        name="quotationName"
                                                                        placeholder="สมศัก นามสมมุติ"
                                                                >
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label for="quotationPhone">
                                                                    <span>เบอร์โทร</span>
                                                                </label>
                                                                <input
                                                                        type="text"
                                                                        id="quotationPhone"
                                                                        class="form-control"
                                                                        name="quotationPhone"
                                                                        maxlength="12"
                                                                        onkeyup="formatMobile(this.value,'quotationPhoneFormatted');"
                                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                                                        placeholder="เฉพาะตัวเลขเท่านั้น ตัวอย่าง 0895117447"
                                                                >
                                                                <small id="quotationNumberHelp" class="form-text text-muted">หมายเลขที่จัดรูปแบบจะอยู่ตรงนี้ <span class="fakeQuotationNumber">0508084722</span> ||
                                                                </small>
                                                                <small class="form-text text-primary quotationPhoneFormatted">Formatted number will
                                                                    show here.</small>
                                                                <input type="hidden" name="phone" id="shopPhoneQuotationFormatted" class="quotationPhoneFormatted quotationPhone">
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label for="quotationEmail">
                                                                    <span>อีเมล</span>
                                                                </label>
                                                                <input
                                                                        type="email"
                                                                        id="quotationEmail"
                                                                        class="quotationEmail form-control"
                                                                        name="quotationEmail"
                                                                        maxlength="80"
                                                                        onchange="ownerEmail(this.value);"
                                                                        onkeyup="setEmailShoppingCart(this.value);"
                                                                        onblur="checkEmailUsed(this.value);"
                                                                        autocomplete="off"
                                                                        placeholder="mail@localforyou.com"
                                                                >
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label for="quotationAddress">
                                                                    <span>ที่อยู่</span>
                                                                </label>
                                                                <textarea
                                                                        class="form-control w-100"
                                                                        id="quotationAddress"
                                                                        rows="3"
                                                                        name="quotationAddress"

                                                                ></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-2">
                                                            <div class="col">
                                                                <label for="quotationTaxNumber">
                                                                    <span>เลขประจำตัวผู้เสียภาษี</span>
                                                                </label>
                                                                <input
                                                                        type="text"
                                                                        id="quotationTaxNumber"
                                                                        class="quotationTaxNumber form-control"
                                                                        name="quotationTaxNumber"
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="row mt-4">

                                    <div class="w-50 px-2">
                                        <label for="byAgent" class="form-label">
                                            <i class="fa-solid fa-person"></i> &nbsp; Sales Agent
                                        </label>
                                        <div class="col d-flex justify-content-between">
                                            <select class="form-select" name="byAgent" id="byAgent" style="min-width: 50%">
                                                <option value="">--None--</option>
                                                <option value="ปิยะกร จ้อยเอม">Boom Piyakorn</option>
                                                <option value="อนิรุตมิ์ จิราสิรินันทชัย">Ball Anirut</option>
                                                <option value="วิมล ปลื้มกมล">Pluem Pluemkamol</option>
                                                <option value="นิธิพันธ์ ธรรมพุฒ">Honey Tummaput</option>
                                                <option value="ชมภูนุช จุลไกรอานิสงส์">Nan Chompunuch</option>
                                                <option value="พฤกษ์ ปฏิพัทธศิลปกิจ">Pruek Patipatsinlapakit</option>
                                                <option value="พรนภา กันทาทำ">Aon Pornnapa</option>
                                                <option value="สุชานันท์ ราชเจริญ">Ploy Suchanan</option>
                                                <option value="วรรษชล ธรรมจะดี">Fern Piyawan</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <input
                                                    class="form-control mb-3"
                                                    id="otherAgent"
                                                    maxlength="200"
                                                    name="otherAgent"
                                                    type="text"
                                                    autocomplete="off"
                                                    placeholder="Enter Other Name"
                                                    style="display: none; width: 48%;"
                                            />
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer">
                                <input type="hidden" id="setupFeeCharge" value="0">
                                <?php include "formButtons.php";?>
                                <div>
                                    <!--blank space-->
                                </div>
                            </div>
                            <!-- Step 4-->

                        </div>
                    </form>
                </div>
            </div>
        </article>
    </div>
</main>
<?php include "form_footer.php"; ?>
<?php
if($QuotationMode){ ?>
    <script>
        //alert("Quotation Mode = <?php echo $QuotationMode; ?>");
        $(".formQuotation").show();
    </script>
<?php } ?>

</body>
</html>