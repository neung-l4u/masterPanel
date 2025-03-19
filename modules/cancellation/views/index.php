<?php
$token = !empty($_GET['token']) ? $_GET['token'] : '';
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
        <img src="assets/img/newL4U-logo-100x100.png" alt="L4U Logo">
        <div class="d-flex flex-column">
            <h1>Local For You Services</h1>
            <p>Online Unsubscribe Form.</p>
        </div>
    </div>
</header>

<main style="min-height: 60vh">
    <div class="container d-flex justify-content-center align-content-center">
        <?php
        if (empty($_GET['token'])){ ?>
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
                                <div class="form-group">
                                    <label for="formCountry">
                                        <span>Country <b class="red">*</b> </span><small class="text-danger" id="smallCountry">Please Select County.</small>
                                    </label>
                                    <select id="formCountry" class="form-select" name="country">
                                        <option selected value="">Please select Country</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="US">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="TH">Thailand</option>
                                    </select>

                                </div>

                                <div class="form-group pt-2">
                                    <label for="shopName">
                                        Shop name
                                        <b class="red">*</b>
                                    </label>
                                    <small class="text-danger" id="smallShopName">Please check the message.</small>
                                    <div>
                                        <input
                                                type="text"
                                                id="shopName"
                                                name="shopName"
                                                class="form-control"
                                                placeholder="Chaba Thai Bistro"
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
                                        <select id="state" class="form-select optionState">
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
                                    <input
                                            type="text"
                                            id="first_name"
                                            name="first_name"
                                            class="form-control"
                                            autocomplete="off"
                                            required
                                            placeholder="John"
                                    />
                                </div>

                                <div class="pt-2">
                                    <label for="last_name">
                                        <span>Last name</span>
                                        <b class="red">*</b>
                                    </label>
                                    <input
                                            type="text"
                                            id="last_name"
                                            name="last_name"
                                            class="form-control"
                                            autocomplete="off"
                                            placeholder="Doe"
                                    />
                                </div>

                                <div class="pt-2">
                                    <label for="mobile" class="control-label">
                                        Mobile
                                        <b class="red">*</b>
                                        <small class="form-text text-muted">
                                            ( without country's code )
                                        </small>
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
                                        <small id="MobileHelp" class="form-text text-muted">
                                            e.g. 0408084722
                                        </small><br>
                                        <small class="form-text text-primary mobileFormatted">Formatted number will show
                                            here.</small>
                                        <input type="hidden" id="ownerMobile" name="ownerMobile" class="mobileFormatted">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <label for="email" class="control-label">
                                        Email
                                        <b class="red">*</b>
                                    </label>
                                    <div>
                                        <input
                                                type="email"
                                                class="form-control mainEmail text-lowercase"
                                                id="email"
                                                name="email"
                                                maxlength="80"
                                                autocomplete="off"
                                                placeholder="mail@localforyou.com"
                                        />
                                        <small id="emailHelp" class="form-text text-muted">
                                            e.g. mail@localforyou.com
                                        </small>
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
                                            <small class="text-danger" id="smallReason">Please Select One.</small>
                                        </label>
                                        <select id="formReason" class="form-select" name="reason" onchange="superbas()">
                                            <option selected value=""> --- Please select your reason --- </option>
                                            <option value="Closing down the Business">Closing down the Business</option>
                                            <option value="Changing Business Owner">Changing Business Owner</option>
                                            <option value="Not getting enough orders">Not getting enough orders</option>
                                            <option value="NO longer want online ordering">NO longer want online ordering</option>
                                            <option value="Cost too much">Cost too much</option>
                                            <option value="Found something better">Found something better</option>
                                            <option value="COVID-19 (Closed the Shop, will not be re-opening) ">COVID-19 (Closed the Shop, will not be re-opening) </option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 mt-2 w-100" id="other">
                                    <label for="additionComment" class="form-label">
                                        <i class="fa-solid fa-note-sticky"></i>&nbsp;
                                        Other
                                    </label><small class="text-danger" id="smallOther">Please check the message.</small>
                                    <textarea
                                            class="form-control w-100"
                                            id="boxother"
                                            name="other"
                                            rows="3"
                                            placeholder="-- Comment --"
                                    ></textarea>
                                </div>

                                <div class="mt-3">
                                    <div class="col d-flex flex-column">
                                        <label for="lastDate">
                                            Last Date you want the system Live? <b class="red">*</b><small class="text-danger" id="smallDate">Please Please select a date.</small>
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
                                        <div id="paymentAgreement"
                                             class="d-flex flex-column align-items-center gap-2 justify-content-evenly">
                                            <div id="agreementTop"
                                                 class="pb-4 d-flex flex-column align-items-start">

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
                                                    <input class="form-check-input" type="checkbox" value="true"
                                                           id="acceptAgreement">
                                                    <label class="form-check-label acceptAgreement" for="acceptAgreement">
                                                        Yes I understand this
                                                    </label>
                                                </div>

                                                <div class="mb-3 mt-5 w-100">
                                                    <label for="additionComment" class="form-label">
                                                        <i class="fa-solid fa-note-sticky"></i>&nbsp;
                                                        Additional Information or feedback you wish to share with us
                                                    </label>
                                                    <textarea
                                                            class="form-control w-100"
                                                            id="additionComment"
                                                            name="note"
                                                            rows="3"
                                                            placeholder="Any other information you would like us to know."
                                                    ></textarea>
                                                    <small id="subAdditionCommentHelp" class="form-text text-muted">
                                                        We would love to hear your thoughts!
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="float-end">
                                <span id="loadingAjax" class="text-success">Form Submited</span>

                                <button
                                        type="button"
                                        class="btn btn-success"
                                        id="cancelBtn"
                                >
                                    Confirm
                                </button>
                            </div>
                            <div>
                                <!--blank space-->
                                <input type="hidden" id="myIP" name="myIP">
                                <input type="hidden" id="agent" name="agent">

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php }//else ?>
    </div>
</main>
<footer class="credit">
    Author: IT Team - Distributed By:
    <a
            title="Awesome Online Shopping Cart Application"
            href="https://www.localforyou.com"
            target="_blank"
    >
        Local For You
    </a>
</footer>
<script src="assets/js/jquery.3.6.0.min.js"></script>
<script src="assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script src="assets/js/global_data.js"></script>
<script src="assets/js/date_format.js"></script>
<script src="assets/js/popper.2.11.5.min.js"></script>
<script>
    $( document ).ready(function() {
        $("#other").hide()
        $("#loadingAjax").hide()
        $("#smallCountry").hide()
        $("#smallShopName").hide()
        $("#smallReason").hide()
        $("#smallDate").hide()
        $("#smallOther").hide()
    });//ready

    function validateForm(){
        let country = $("#formCountry").val();
        let shopName = $("#shopName").val();
        let reason = $("#formReason").val();
        let lastDate = $("#lastDate").val();
        let other = $("#boxother").val();

        if
    }


    const cancelFrm = {};
    const SfOAut = {};

    const SOBJECT_API_NAME = "Opportunity";
    const RECORD_ID = "<?php echo $token; ?>";

    const sfParam = {
        "Completed_By__c": "Admin",
        "StageName": "Cancellation requested"
    }




    function formatMobile(param,place) {
        let mobileFormatted = $("."+place);
        const shopNumber = $(".shopNumber");
        if (param.length>=1){
            let newNum = countryCode[formData.formCountry]+parseInt(param, 10);
            mobileFormatted.html(newNum);
            mobileFormatted.val(newNum);
            if (place==="mobileFormatted"){
                formData.owner.mobile = newNum;
            }else if(place==="shopNumberFormatted"){
                shopNumber.val(newNum);
            }
        }else {
            mobileFormatted.html("Formatted number will show here.");
        }
    }

    $('#formCountry').change(function() {
        formData.formCountry = $(this).val();
        const countryValue = $(".countryValue");
        const countryName = $(".countryName");
        const selectState = $(".selectState");
        const zipLabel = $(".zipLabel");
        const countryTextOnly = $("#selectedCountry");
        countryValue.val($(this).val());

        switch ($(this).val()) {
            case "AU":
                countryName.html("Australia");
                selectState.show();
                zipLabel.html("Postcode");
                countryTextOnly.html("Australia");
                break;
            case "NZ":
                countryName.html("New Zealand");
                selectState.show();
                zipLabel.html("Postcode");
                countryTextOnly.html("New Zealand");
                break;
            case "TH":
                countryName.html("Thailand");
                selectState.show();
                zipLabel.html("รหัสไปรษณีย์");
                countryTextOnly.html("Thailand");
                break;
            case "US":
                countryName.html("United States");
                selectState.show();
                zipLabel.html("Zip Code");
                countryTextOnly.html("United States");
                break;
            case "UK":
                countryName.html("United Kingdom");
                selectState.hide();
                zipLabel.html("Zip Code");
                countryTextOnly.html("United Kingdom");
                break;
            case "CA":
                countryName.html("Canada");
                selectState.show();
                zipLabel.html("Zip Code");
                countryTextOnly.html("Canada");
                break;
            default:
                countryName.html("please select country");
                zipLabel.html("Postal Code");
                countryTextOnly.html("Australia");
                selectState.show();
        }
        optionState();
    });

    function optionState(){
        let shopCountry = $("#formCountry").val();
        let optionState = $('.optionState');
        let options = [
            {
                country: "AU",
                label: "Australia",
                state: [
                    { "code": "CT", "text": "Australian Capital Territory" },
                    { "code": "NS", "text": "New South Wales" },
                    { "code": "NT", "text": "Northern Territory" },
                    { "code": "QL", "text": "Queensland" },
                    { "code": "SA", "text": "South Australia" },
                    { "code": "TS", "text": "Tasmania" },
                    { "code": "VI", "text": "Victoria" },
                    { "code": "WA", "text": "Western Australia" }
                ]
            },
            {
                country: "NZ",
                label: "New Zealand",
                state: [
                    { "code": "NZ-BOP", "text": "Bay of Plenty" },
                    { "code": "NZ-AUK", "text": "Auckland" },
                    { "code": "NZ-GIS", "text": "Gisborne" },
                    { "code": "NZ-CAN", "text": "Canterbury" },
                    { "code": "NZ-MBH", "text": "Marlborough" },
                    { "code": "NZ-HKB", "text": "Hawke's Bay" },
                    { "code": "NZ-NSN", "text": "Nelson" },
                    { "code": "NZ-MWT", "text": "Manawatu-Wanganui" },
                    { "code": "NZ-OTA", "text": "Otago" },
                    { "code": "NZ-NTL", "text": "Northland" },
                    { "code": "NZ-TAS", "text": "Tasman" },
                    { "code": "NZ-STL", "text": "Southland" },
                    { "code": "NZ-WKO", "text": "Waikato" },
                    { "code": "NZ-TKI", "text": "Taranaki" },
                    { "code": "NZ-WTC", "text": "West Coast" },
                    { "code": "NZ-WGN", "text": "Wellington" }
                ]
            },
            {
                country: "US",
                label: "United States",
                state: [
                    { "code": "AL", "text": "Alabama" },
                    { "code": "AK", "text": "Alaska" },
                    { "code": "AZ", "text": "Arizona" },
                    { "code": "AR", "text": "Arkansas" },
                    { "code": "CA", "text": "California" },
                    { "code": "CO", "text": "Colorado" },
                    { "code": "CT", "text": "Connecticut" },
                    { "code": "DE", "text": "Delaware" },
                    { "code": "DC", "text": "District of Columbia" },
                    { "code": "Florida", "text": "Florida" },
                    { "code": "GA", "text": "Georgia" },
                    { "code": "HI", "text": "Hawaii" },
                    { "code": "ID", "text": "Idaho" },
                    { "code": "IL", "text": "Illinois" },
                    { "code": "IN", "text": "Indiana" },
                    { "code": "IA", "text": "Iowa" },
                    { "code": "KS", "text": "Kansas" },
                    { "code": "KY", "text": "Kentucky" },
                    { "code": "LA", "text": "Louisiana" },
                    { "code": "MD", "text": "Maryland" },
                    { "code": "MA", "text": "Massachusetts" },
                    { "code": "ME", "text": "Maine" },
                    { "code": "MI", "text": "Michigan" },
                    { "code": "MS", "text": "Mississippi" },
                    { "code": "MO", "text": "Missouri" },
                    { "code": "MN", "text": "Minnesota" },
                    { "code": "MT", "text": "Montana" },
                    { "code": "NE", "text": "Nebraska" },
                    { "code": "NV", "text": "Nevada" },
                    { "code": "NH", "text": "New Hampshire" },
                    { "code": "NJ", "text": "New Jersey" },
                    { "code": "NM", "text": "New Mexico" },
                    { "code": "NY", "text": "New York" },
                    { "code": "NC", "text": "North Carolina" },
                    { "code": "ND", "text": "North Dakota" },
                    { "code": "OH", "text": "Ohio" },
                    { "code": "OK", "text": "Oklahoma" },
                    { "code": "OR", "text": "Oregon" },
                    { "code": "PA", "text": "Pennsylvania" },
                    { "code": "RI", "text": "Rhode Island" },
                    { "code": "SC", "text": "South Carolina" },
                    { "code": "SD", "text": "South Dakota" },
                    { "code": "TN", "text": "Tennessee" },
                    { "code": "TX", "text": "Texas" },
                    { "code": "UT", "text": "Utah" },
                    { "code": "VA", "text": "Virginia" },
                    { "code": "VT", "text": "Vermont" },
                    { "code": "WA", "text": "Washington" },
                    { "code": "WV", "text": "West Virginia" },
                    { "code": "WI", "text": "Wisconsin" },
                    { "code": "WY", "text": "Wyoming" }
                ]
            },
            {
                country: "CA",
                label: "Canada",
                state:[
                    {
                        "code": "ON",
                        "text": "Ontario"
                    },
                    {
                        "code": "QC",
                        "text": "Quebec"
                    },
                    {
                        "code": "NS",
                        "text": "Nova Scotia"
                    },
                    {
                        "code": "NB",
                        "text": "New Brunswick"
                    },
                    {
                        "code": "MB",
                        "text": "Manitoba"
                    },
                    {
                        "code": "BC",
                        "text": "British Columbia"
                    },
                    {
                        "code": "PE",
                        "text": "Prince Edward Island"
                    },
                    {
                        "code": "SK",
                        "text": "Saskatchewan"
                    },
                    {
                        "code": "AB",
                        "text": "Alberta"
                    },
                    {
                        "code": "NL",
                        "text": "Newfoundland and Labrador "
                    }
                ]
            },
            {
                country: "TH",
                label: "Thailand",
                state:[
                    { "code": "ACR", "text": "Amnat Charoen" },
                    { "code": "ATG", "text": "Ang Thong" },
                    { "code": "AYA", "text": "Phra Nakhon Si Ayutthaya" },
                    { "code": "BKK", "text": "Bangkok" },
                    { "code": "BKN", "text": "Bueng Kan" },
                    { "code": "BRM", "text": "Buri Ram" },
                    { "code": "CBI", "text": "Chonburi" },
                    { "code": "CCO", "text": "Chachoengsao" },
                    { "code": "CMI", "text": "Chiang Mai" },
                    { "code": "CNT", "text": "Chai Nat" },
                    { "code": "CPM", "text": "Chaiyaphum" },
                    { "code": "CPN", "text": "Chumphon" },
                    { "code": "CRI", "text": "Chiang Rai" },
                    { "code": "CTI", "text": "Chanthaburi" },
                    { "code": "KKN", "text": "Khon Kaen" },
                    { "code": "KPT", "text": "Kamphaeng Phet" },
                    { "code": "KRI", "text": "Kanchanaburi" },
                    { "code": "KSN", "text": "Kalasin" },
                    { "code": "LEI", "text": "Loei" },
                    { "code": "LPG", "text": "Lampang" },
                    { "code": "LPN", "text": "Lamphun" },
                    { "code": "LRI", "text": "Lopburi" },
                    { "code": "MDH", "text": "Mukdahan" },
                    { "code": "MKM", "text": "Maha Sarakham" },
                    { "code": "MSN", "text": "Mae Hong Son" },
                    { "code": "NAN", "text": "Nan" },
                    { "code": "NBP", "text": "Nong Bua Lamphu" },
                    { "code": "NBI", "text": "Nonthaburi" },
                    { "code": "NKI", "text": "Nong Khai" },
                    { "code": "NMA", "text": "Nakhon Ratchasima" },
                    { "code": "NPM", "text": "Nakhon Phanom" },
                    { "code": "NPT", "text": "Nakhon Pathom" },
                    { "code": "NSN", "text": "Nakhon Sawan" },
                    { "code": "NST", "text": "Nakhon Si Thammarat" },
                    { "code": "NYK", "text": "Nakhon Nayok" },
                    { "code": "NWT", "text": "Narathiwat" },
                    { "code": "PBI", "text": "Phetchaburi" },
                    { "code": "PCT", "text": "Phichit" },
                    { "code": "PKN", "text": "Prachuap Khiri Khan" },
                    { "code": "PKT", "text": "Phuket" },
                    { "code": "PLG", "text": "Phatthalung" },
                    { "code": "PLK", "text": "Phitsanulok" },
                    { "code": "PNB", "text": "Phetchabun" },
                    { "code": "PNA", "text": "Phang Nga" },
                    { "code": "PRE", "text": "Phrae" },
                    { "code": "PTE", "text": "Pathum Thani" },
                    { "code": "PYO", "text": "Phayao" },
                    { "code": "RBR", "text": "Ratchaburi" },
                    { "code": "RET", "text": "Roi Et" },
                    { "code": "RNG", "text": "Ranong" },
                    { "code": "RYG", "text": "Rayong" },
                    { "code": "SKA", "text": "Songkhla" },
                    { "code": "SKM", "text": "Samut Songkhram" },
                    { "code": "SKN", "text": "Samut Sakhon" },
                    { "code": "SKW", "text": "Sa Kaeo" },
                    { "code": "SNI", "text": "Surat Thani" },
                    { "code": "SNK", "text": "Sakon Nakhon" },
                    { "code": "SRI", "text": "Saraburi" },
                    { "code": "SPB", "text": "Suphan Buri" },
                    { "code": "SPK", "text": "Samut Prakan" },
                    { "code": "SSK", "text": "Si Sa Ket" },
                    { "code": "STI", "text": "Sukhothai" },
                    { "code": "STN", "text": "Satun" },
                    { "code": "UDN", "text": "Udon Thani" },
                    { "code": "UBN", "text": "Ubon Ratchathani" },
                    { "code": "UTI", "text": "Uthai Thani" },
                    { "code": "UTT", "text": "Uttaradit" },
                    { "code": "YST", "text": "Yasothon" },
                    { "code": "YLA", "text": "Yala" }
                ]
            }
        ];
        optionState.empty().show();

        switch (shopCountry) {
            case "AU":
                jQuery.each( options[0].state, function( i, val ) {
                    optionState.append("<option value='"+val.code+"'>"+val.text+"</option>");
                });
                break;
            case "NZ":
                jQuery.each( options[1].state, function( i, val ) {
                    optionState.append("<option value='"+val.code+"'>"+val.text+"</option>");
                });
                break;
            case "TH":
                jQuery.each( options[4].state, function( i, val ) {
                    optionState.append("<option value='"+val.code+"'>"+val.text+"</option>");
                });
                break;
            case "US":
                jQuery.each( options[2].state, function( i, val ) {
                    optionState.append("<option value='"+val.code+"'>"+val.text+"</option>");
                });
                break;
            case "CA":
                jQuery.each( options[3].state, function( i, val ) {
                    optionState.append("<option value='"+val.code+"'>"+val.text+"</option>");
                });
                break;
            default:
                optionState.append("<option value='0'>Please select country</option>");
        }
    }

    function delay(time) {
        return new Promise(resolve => setTimeout(resolve, time));
    }


    function superbas(){
        let boxother = $("#formReason").val();
        // alert (boxother);
        if(boxother === "other"){
            $("#other").show();
        }else{
            $("#other").hide();
        }
    }//Other Box

    let payload = {};

    $('#cancelBtn').on('click', function () {

        // อ่านค่าจาก input, select, textarea ทั้งหมดในฟอร์ม
        $('form').find('input, select, textarea').each(function () {
            let name = $(this).attr('name');
            let value = $(this).val();

            // ตรวจสอบว่ามี name ไหม (ป้องกันค่า undefined)
            if (name) {
                // เช็คว่าเป็น radio และถูกเลือกหรือไม่
                if ($(this).is(':radio')) {
                    if ($(this).is(':checked')) {
                        payload[name] = value;
                    }
                }
                // เช็คว่าเป็น checkbox หรือไม่
                else if ($(this).is(':checkbox')) {
                    payload[name] = $(this).is(':checked');
                }
                // ค่าอื่นๆ (text, select, textarea)
                else {
                    payload[name] = value;
                }
            }

        });


        // แสดง payload ใน console
        console.log(payload);



        let ans = confirm("Are you sure??");

        if (payload.country === "AU"){
            payload.country = "Australia";
        }else if (payload.country === "NZ"){
            payload.country = "New Zealand";
        }else if (payload.country === "TH"){
            payload.country = "Thailand";
        }else if (payload.country === "US"){
            payload.country = "United States";
        }else if (payload.country === "CA"){
            payload.country = "Canada";
        }else if (payload.country === "UK"){
            payload.country = "United Kingdom";
        }else{
            payload.country = "Please select country";
        }

        if(ans){
            callWebhook();
        }else{ console.log("no no no"); }

    });


    function callWebhook() {

        console.log("we call webhook");
        const callAjax = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'json',
            url: "https://hook.us1.make.com/a3or9ct3u3m84lqckivl1rt7891ky1h2",
            data: payload

        });
        callAjax.done(function (res) {

            $("#loadingAjax").fadeIn(100);
            reSubmit();
            alert("Send Successful");

        });
        callAjax.fail(function(xhr, status, error) {
            console.log("ajax webhook fail!!");
            console.log(status + ': ' + error);
            alert("Send fail!!");

        });

        const ajaxSaveDB = $.ajax({
                url: "activeAjax.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload,
            }
        );

        ajaxSaveDB.done(function(res) {
            console.log(payload);
            // alert("Send Data To Database successful");
            return true;
        });

        ajaxSaveDB.fail(function(xhr, status, error) {
            console.log("ajax Send Date to Database fail!!");
            console.log(status + ': ' + error);
            // alert("Send Data To Database fail!!");
            return false;
        });




    }//Call Webhook


    function reSubmit(){

        // alert ("Done")
        location.replace("https://localforyou.com/")
    }

</script>

</body>
</html>