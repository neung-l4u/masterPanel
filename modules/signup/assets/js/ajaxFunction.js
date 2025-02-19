const typeJsonKey = (txt) => {
    return txt === "Thai Massage" ? "Massage" : "Restaurant";
}

function getProductList(country) {

    if (formData.formCountry === ""){
        $("#warn_form_country").show();
        return false;
    }else { $("#warn_form_country").hide(); }

    if (formData.formType === ""){
        $("#warn_form_type").show();
        return false;
    }else { $("#warn_form_type").hide(); }
    //////////////////

    let price_id = undefined;
    let product_id = undefined;
    let formType = formData.formType;
    let formCountry = formData.formCountry;
    let formTypeJsonKey = typeJsonKey(formType);
    let contractPeriod = $("input[name='contractPeriod']:checked").val();
    setPeriodSelectBox(contractPeriod);
    const loadingAjax = $("#loadingAjax");
    const loadGif = "<img alt='Loading' src='assets/img/loading.gif'>";
    loadingAjax.html(loadGif);
    const reqProductList = $.ajax({
        url: settings.url_getProductList,
        method: 'POST',
        async: true,
        dataType: 'json',
        crossDomain: true,
        data: {
            "env": "'"+settings.env_mode+"'",
            "country": formCountry,
            "period": contractPeriod
        }
    });
    /////

    reqProductList.done(function(res) {
        let jsonData = res['data'][formCountry];
        let jsonAddons = jsonData[formTypeJsonKey]['Addons'];
        let jsonAll = jsonData['All'];
        let jsonSetupFee = jsonData['All']['Special']['SetupFee'];

        let contractPeriodKeyIndex = 0;
        let contractPeriodKeyIndexSub = 'all';
        let contractPeriodSelected = $("input[name='contractPeriod']:checked").val();

        switch (contractPeriodSelected){
            case "0":
                contractPeriodKeyIndex = 0;
                break;
            case "3":
                contractPeriodKeyIndex = 1;
                break;
            case "12":
                contractPeriodKeyIndex = 2;
                break;
            default:
                contractPeriodKeyIndex = 0;
        }

        let jsonMainProduct = jsonData[formTypeJsonKey]['Products'][contractPeriodKeyIndex]['items'].concat(jsonAll['Products'][0]['items']);
        let jsonAddonsSubscriptions = jsonAddons['Subscriptions'][0]['items'].concat(jsonAll['Addons']['Subscriptions'][0]['items']);
        let jsonAddonsOnetime = jsonAddons['Onetime'][0]['items'].concat(jsonAll['Addons']['Onetime'][0]['items']);
        let jsonAddonsMaterials = jsonAddons['Materials'][0]['items'].concat(jsonAll['Addons']['Materials'][0]['items']);
        let jsonAddonsOthers = jsonAddons['Others'][0]['items'].concat(jsonAll['Addons']['Others'][0]['items']);
        let checkIsOptionWebHosting = "";
        let bundleHeader = false;

        readMainProduct = jsonMainProduct;
        readAddonProduct = jsonAddonsMaterials.concat(jsonAddonsSubscriptions).concat(jsonAddonsOnetime).concat(jsonAddonsOthers);

        let brBundle = {
            "type" : "",
            "status" : false
        }

        let headText = "<div class='text-warning mt-4'>Bundle</div>";
        if(readMainProduct.length > 0){
            $("#products2").empty();
            let productRadio2 = readMainProduct.map((item) => {
                let ran = Math.random();
                let name = "";
                let special = (item.gst)?" + GST ":"";
                let price = 0;
                let currency = "";
                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                currency = item.currency;
                amount = item.amount;
                let product_id = item.price_id;
                let ext = item.ext;
                let br = "";

                let currencySign = "";
                let currencySignPlace = $(".currencySign");
                switch(item.currency) {
                    case "gbp":
                        currencySign = "£";
                        currencySignPlace.html("£");
                        break;
                    case "thb":
                        currencySign = "฿";
                        currencySignPlace.html("฿");
                        break;
                    default:
                        currencySign = "$";
                        currencySignPlace.html("$");
                }

                lap++;
                if(brBundle.type!==item.type){
                    br = (brBundle.status)?"<div class='text-warning mt-2'>Solo</div>":"";
                    brBundle.type = item.type;
                    brBundle.status = true;
                }

                    return `${br}<div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="product" 
                            id="product${ran}" 
                            value="${name} - ${currencySign}${price} ${special}${ext}"
                            onclick="addMainCart('${name}', '${price}', '${amount}', '${special}', '${product_id}', '');"
                        >
                        <label class="form-check-label" for="product${ran}" >
                            ${name} <b class="text-primary"> - ${currencySign}${price} ${special}${ext}</b>
                        </label>
                    </div>`;
            });//return

            productRadio2.map((item) => {
                if(!bundleHeader) {
                    $("<div class='text-warning mt-2'>Bundle</div>").appendTo("#products2");
                    bundleHeader = true;
                }
                $(item).appendTo( "#products2" );
            });
        }else{
            let textMainProduct = `<small class="text-danger">Sorry we don't have any Package in this currency yet !!</small>`;
            $("#products2").html(textMainProduct);
        }

        if(jsonSetupFee.length > 0){
            $("#setUpFeeList").empty();
            let setUpFeeList = jsonSetupFee.map((item) => {
                let ran = Math.random();
                let name = "";
                let special = (item.gst)?" + GST ":"";
                let price = 0;
                let currency = "";
                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                currency = item.currency;
                amount = item.amount;
                let product_id = item.price_id;
                let ext = item.ext;
                let br = "";

                let currencySign = "";
                let currencySignPlace = $(".currencySign");
                switch(item.currency) {
                    case "gbp":
                        currencySign = "£";
                        currencySignPlace.html("£");
                        break;
                    case "thb":
                        currencySign = "฿";
                        currencySignPlace.html("฿");
                        break;
                    default:
                        currencySign = "$";
                        currencySignPlace.html("$");
                }

                lap++;

                return `<div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="setup" 
                            id="setup${ran}" 
                            value="${name} - ${currencySign}${price} ${special}${ext}"
                            onclick="addAddonCart('${name}', '${price}', '${amount}', '${special}', '${product_id}', '', '');"
                        >
                        <label class="form-check-label" for="setup${ran}" >
                            ${name} <b class="text-primary"> - ${currencySign}${price} ${special}${ext}</b>
                        </label>
                    </div>`;
            });//return

            setUpFeeList.map((item) => {
                $(item).appendTo( "#setUpFeeList" );
            });
        }else{
            let textMainProduct = `<small class="text-danger">Sorry we don't have any Package in this currency yet !!</small>`;
            $("#setUpFeeList").html(textMainProduct);
        }

        if(readAddonProduct.length > 0){
            $("#addon2").empty();
            $("#addon3").empty();
            let didItFlyer = false;
            let didItFridge = false;
            let newLine = false;
            let addonCheck2 = readAddonProduct.map((item) => {
                let name = "";
                let special = (item.gst)?" + GST ":"";
                let price = 0;
                let addText = "";
                let discountValue = Math.round((item.amount*15)/100)/100;
                let cartPrice = (item.amount-Math.round((item.amount*15)/100));
                if(formCountry==="US"){
                    cartPrice = item.amount;
                }
                let realPrice = cartPrice/100;
                let ext = item.ext;
                //let setup_fee = item.setup_fee;

                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                amount = item.amount;
                let product_id = item.price_id;
                let position = name.search("A6|Fridge|Flyers 5");
                let position2 = name.search("Fridge");
                let findInfluencer  = name.search("Influencer|Digital");
                let discountText = "";
                let leadDiscountText = "";
                let classType = "";

                let currencySign = "";
                let currencySignPlace = $(".currencySign");
                switch(item.currency) {
                    case "gbp":
                        currencySign = "£";
                        currencySignPlace.html("£");
                        break;
                    case "thb":
                        currencySign = "฿";
                        currencySignPlace.html("฿");
                        break;
                    default:
                        currencySign = "$";
                        currencySignPlace.html("$");
                }

                if (name.search("Flyers 5")>-1){
                    classType = "isUSFlyer";
                }
                else if (name.search("A6")>-1){
                    classType = "isFlyer";
                }else if (name.search("Fridge")>-1){
                    classType = "isFridge";
                }else if (name.search("Adv Promo")>-1){
                    classType = "isAdvPromo";
                }else if (name.search("Social Media Management")>-1){
                    classType = "isSocialMedia";
                }else if (name.search("Influencer")>-1){
                    classType = "isInfluencer";
                }else if (name.search("Online Ordering System")>-1){
                    classType = "isDineIn";
                }else if (name.search("Digital")>-1){
                    classType = "isDigitalMenu";
                }else if (name.search("Website Makeover")>-1){
                    classType = "isWebsiteMakeOver";
                }else if (name.search("Web Hosting")>-1){
                    classType = "isWebHosting";
                }else { classType = ""; }

                if((position>-1) && (!didItFlyer)){
                    if((formCountry==="US") || (formCountry==="CA")){
                        addText = `<div class='text-warning'>
                                        Add-on Flyer
                                        <span class="mytooltip tooltip-effect-1">
                                            <span class="tooltip-item"><i class="fa-solid fa-star text-primary"></i></span>
                                            <span class="tooltip-content clearfix">
                                                <img src="assets/img/Pic-A.jpg" alt="Flyer">
                                                <span class="tooltip-text">
                                                    Example Flyer
                                                </span>
                                            </span>
                                        </span>
                                   </div>`;
                    }else{ addText = `<div class='text-warning'>
                                           Special Add-on Flyer buy now got 15% discount (Recommend)
                                           <span class="mytooltip tooltip-effect-1">
                                                <span class="tooltip-item"><i class="fa-solid fa-star text-primary"></i></span>
                                                <span class="tooltip-content clearfix">
                                                    <img src="assets/img/Pic-A.jpg" alt="Flyer">
                                                    <span class="tooltip-text">
                                                        Example Flyer
                                                    </span>
                                                </span>
                                           </span>
                                      </div>`; }
                    didItFlyer = true;
                }
                if((position2>-1) && (!didItFridge)){
                    addText = `<div class='text-warning mt-4'>
                                    Special Add-on Fridge Magnet buy now got 15% discount (Recommend)
                                    <span class="mytooltip tooltip-effect-1">
                                        <span class="tooltip-item"><i class="fa-solid fa-star text-primary"></i></span>
                                        <span class="tooltip-content clearfix">
                                            <img src="assets/img/Pic-C.jpg" alt="Magnet">
                                            <span class="tooltip-text">
                                                Example Fridge Magnet
                                            </span>
                                        </span>
                                    </span>
                               </div>`;
                    didItFridge = true;
                }

                if(position>-1){
                    if((formCountry==="US") || (formCountry==="CA")){
                        discountText = `<b class="text-danger">(excluded tax)</b>`;
                        leadDiscountText = "";
                    }
                    else {
                        discountText = `<b class="text-success">(will save ${currencySign}${discountValue.toFixed(2)})</b>
                                = <b class="text-primary">${currencySign}${realPrice.toFixed(2)}${special}<b/>`;
                        leadDiscountText = " From ";
                    }
                }else {
                    if(findInfluencer>-1){
                        discountText = "";
                    }else { discountText = `<b class="text-primary">${ext}</b>`; }
                    leadDiscountText = "";
                    if(!newLine) {
                        addText = "<div class='text-warning mt-4'>Others</div>";
                    }
                    newLine = true;
                    realPrice = price;
                    cartPrice = amount;
                }

                const boxName = {
                    'A6 Flyers x 1,000 pcs' : '00N9s000000QQaH',
                    'A6 Flyers x 2,000 pcs' : '00N9s000000QQaH',
                    'A6 Flyers x 5,000 pcs' : '00N9s000000QQaH',
                    'A6 Flyers x 10,000 pcs' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 1,000' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 2,000' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 5,000' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 10,000' : '00N9s000000QQaH',
                    'Fridge Magnet x 500' : '00N9s000000QQav',
                    'Fridge Magnet x 1,000' : '00N9s000000QQav',
                    'Fridge Magnet x 2,000' : '00N9s000000QQav',
                    'Fridge Magnet x 4,000' : '00N9s000000QQav',
                    'Adv Promo' : '00N9s000000QgTt',
                    'Social Media Management' : '00N9s000000QQcI',
                    'Influencer Package' : '00N9s000000QgU3',
                    'Dine-in Dual Online Ordering System' : '00N9s000000QgU8',
                    'Menu / Massage Pricing Design' : '00N9s000000QQbP',
                    'Website Makeover / Build' : '00N9s000000Qn1j',
                    'Website Hosting + Email included' : '00N9s000000Qn1t'
                }

                // ถ้าเป็น Website Hosting ให้ใส่ class ไว้ จะเอาไว้เลือก Auto จาก package อื่น
                if (name.includes("Website Hosting")){
                    checkIsOptionWebHosting = "optionWebHosting";
                }else{
                    checkIsOptionWebHosting = "";
                }
                /////////

                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType} ${checkIsOptionWebHosting}" 
                        type="checkbox" 
                        name=${boxName[name]} 
                        id="addon-${product_id}" 
                        value="${name} - ${formData.formCurrency.charAt(0)}$ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} ${currencySign}${price} ${special}</b>
                        ${discountText}
                    </label>
                </div>`;

            });

            addonCheck2.map((item) => {
                $(item).appendTo( "#addon2" );
            });
        }else{
            let textAddOn = `<small class="text-danger">Sorry we don't have any Add-on in this currency yet !!</small>`;
            $("#addon2").html(textAddOn);
        }
        ///
        //get add-on checkbox status and value
        const chkIsAdvPromo = document.querySelector(".isAdvPromo");
        const chkIsSocialMedia = document.querySelector(".isSocialMedia");
        const chkIsInfluencer = document.querySelector(".isInfluencer");
        const chkIsDineIn = document.querySelector(".isDineIn");
        const chkIsDigitalMenu = document.querySelector(".isDigitalMenu");
        const chkIsWebsiteMakeOver = document.querySelector(".isWebsiteMakeOver");
        const chkIsWebHosting = document.querySelector(".isWebHosting");

        //set initial product value
        const initAddOnAdvPromo = $("#initAddOnAdvPromo");
        const initAddOnSocialMediaPosts = $("#initAddOnSocialMediaPosts");
        const initAddOnInfluencer = $("#initAddOnInfluencer");
        const initAddOnDineInSystem = $("#initAddOnDineInSystem");
        const initAddOnDigitalMenuDesign = $("#initAddOnDigitalMenuDesign");
        const initAddOnWebsiteMakeOver = $("#initAddOnWebsiteMakeOver");
        const initAddOnWebHosting = $("#initAddOnWebHosting");

        //set value on checkbox is checked
        if (typeof(chkIsAdvPromo) != 'undefined' && chkIsAdvPromo != null) {
            chkIsAdvPromo.addEventListener("change", () => {
                if (chkIsAdvPromo.checked) {
                    initAddOnAdvPromo.val(chkIsAdvPromo.value);
                } else {
                    initAddOnAdvPromo.val("");
                }
            });
        }
        if (typeof(chkIsSocialMedia) != 'undefined' && chkIsSocialMedia != null) {
            chkIsSocialMedia.addEventListener("change", () => {
                if (chkIsSocialMedia.checked) {
                    initAddOnSocialMediaPosts.val(chkIsSocialMedia.value);
                } else {
                    initAddOnSocialMediaPosts.val("");
                }
            });
        }
        if (typeof(chkIsInfluencer) != 'undefined' && chkIsInfluencer != null) {
            chkIsInfluencer.addEventListener("change", () => {
                if (chkIsInfluencer.checked) {
                    initAddOnInfluencer.val(chkIsInfluencer.value);
                } else {
                    initAddOnInfluencer.val("");
                }
            });
        }
        if (typeof(chkIsDineIn) != 'undefined' && chkIsDineIn != null) {
            chkIsDineIn.addEventListener("change", () => {
                if (chkIsDineIn.checked) {
                    initAddOnDineInSystem.val(chkIsDineIn.value);
                } else {
                    initAddOnDineInSystem.val("");
                }
            });
        }
        if (typeof(chkIsDigitalMenu) != 'undefined' && chkIsDigitalMenu != null) {
            chkIsDigitalMenu.addEventListener("change", () => {
                if (chkIsDigitalMenu.checked) {
                    initAddOnDigitalMenuDesign.val(chkIsDigitalMenu.value);
                } else {
                    initAddOnDigitalMenuDesign.val("");
                }
            });
        }
        if (typeof(chkIsWebsiteMakeOver) != 'undefined' && chkIsWebsiteMakeOver != null) {
            chkIsWebsiteMakeOver.addEventListener("change", () => {
                if (chkIsWebsiteMakeOver.checked) {
                    initAddOnWebsiteMakeOver.val(chkIsWebsiteMakeOver.value);
                } else {
                    initAddOnWebsiteMakeOver.val("");
                }
            });
        }
        if (typeof(chkIsWebHosting) != 'undefined' && chkIsWebHosting != null) {
            chkIsWebHosting.addEventListener("change", () => {
                if (chkIsWebHosting.checked) {
                    initAddOnWebHosting.val(chkIsWebHosting.value);
                } else {
                    initAddOnWebHosting.val("");
                }
            });
        }
        //
        loadingAjax.html('<i class="fa-solid fa-check text-primary"></i>');
        $(".next").removeClass("btn-outline-danger").addClass("btn-outline-secondary").prop("disabled", false);
    });

    reqProductList.fail(function(xhr, status, error) {
        console.log("ajax get ProductList fail!!");
        loadingAjax.html('<i class="fa-solid fa-xmark text-danger"></i><button class="btn text-primary" type="button" onClick="getProductList();"><i class="fa-solid fa-rotate text-primary"></i></i></button>');
        $(".next").removeClass("btn-outline-secondary").addClass("btn-outline-danger").prop("disabled", true);
        console.log(status + ': ' + error);
    });

}//function

let requestedTime = 0;
let setupFee = 0;
let cloneCart = {};
let clonePayload = {};

function requestToPay() {
    let paymentURL = settings.url_payment.card;
    let paymentMethod = "card";
    let cardDetail = {};
    let selectEnv = settings.env_mode;

    ////////if checked on a submitted form will charge customer via stripe/////
    const CheckedBoxMakeCharge = $("#CheckedBoxMakeCharge");
    let CheckedBoxMakeChargeValue = 2;

    if(CheckedBoxMakeCharge.prop('checked') === true){
        CheckedBoxMakeChargeValue = CheckedBoxMakeCharge.attr('value');
    }else{
        CheckedBoxMakeChargeValue = 0;
    }
    ///////////////////////////////

    const currentPaymentMethod = $("#paymentMethod").val();
    const customerStripeEmail = $("#customerStripeEmail");
    const customerStripeID = $("#customerStripeID");
    const customerStripeIDUSA = $("#customerStripeIDUSA");
    const usageMainDiscountCode = $("#usageMainDiscountCode");
    const usageAddonDiscountCode = $("#usageAddonDiscountCode");
    const myIP = $("#myIP");
    const agent = $("#agent")
    const creditFullName = $("#creditFullName");
    const shopOwnerEmail = $("#email");
    const restaurant_name = $("#00N2v00000IyVqB");
    const creditCardNumber = $("#creditCardNumber");
    const creditExpireDate = $("#creditExpireDate").val();
    const creditCCV = $("#creditCCV");
    const couponCode = $("#couponCode");
    const couponCode2 = $("#couponCode2");
    let customerEmail = shopOwnerEmail.val();
    let bsb_number = "000000";
    let account_number = "000123456";
    let routingDirectDebit = "110000000";

    let single = creditExpireDate.split("/");
    let month = parseInt(single[0],10);
    let year = parseInt(single[1],10)+2000;
    let formCountry = formData.formCountry;
    cloneCart = cart;

    let res = {
        "message": "Dummy message",
        "customer_id": null,
        "error": ""
    }

    // action before sent
    const result = $(".paymentResult");
    result.html("Payment request submitted, loading... <img alt='Loading' src='assets/img/loading.gif'>");
    $("#cmdSubmit").removeClass("btn-outline-success").addClass("btn-outline-info").prop("disabled", true);
    $("#paymentSubmit").prop('disabled', true);
    paymentTimestamp();
    /////

    if (requestedTime<=0){
        cloneCart.subscription = cloneCart.subscription.reduce(function(acc, cur, i) { //convert an array to object
            acc[i] = cur;
            return acc;
        }, {});
        cloneCart.add_on = cloneCart.add_on.reduce(function(acc, cur, i) { //convert an array to object
            acc[i] = cur;
            return acc;
        }, {});
    }
    /////
    if(Object.keys(couponObjectList).length<=0){ loadCouponObject(); } //if empty couponObjectList then load it via ajax
    const objCode = $("#couponCode");
    let inputCode = objCode.val().trim().toUpperCase();
    let formTypeJsonKey = typeJsonKey(formData.formType); //"Massage" : "Restaurant"
    let textDiscount2 = couponCode2.val().trim().toUpperCase();
    let Payment_Coupon_Obj = {};
    let Payment_Coupon_Obj2 = {};

    try { //try to create discountList form JSON file *this will error if nothing filled in the main coupon code field
        let discountList = couponObjectList.Coupon[inputCode][formTypeJsonKey];
        let discountObject = discountList[formData.formCountry];
        let textDiscount = couponCode.val().trim().toUpperCase();
        Payment_Coupon_Obj = discountObject;
        Payment_Coupon_Obj2 = discountObject;
    } catch (error) {
        console.log("--This is catch case --");
        console.error('JSON parsing error:', error.message);
        console.log("--End catch case --");
    }

    let codeDiscount = {};

    if (typeof Payment_Coupon_Obj !== "undefined") { //check this coupon code is exist in a setting list
        console.log("เจอส่วนลดแล้ว");
        let pid = "";
            pid = cloneCart["subscription"][0]; //read main product ID
        let cid = "";
            cid = Payment_Coupon_Obj.code; //read stripe coupon code
        codeDiscount = { [pid] : cid } ; //set the main coupon code
    }else{
        let pid = "";
        pid = cloneCart["subscription"][0]; //read main product ID
        let cid = "";
        codeDiscount = { [pid] : cid } ; //set the main coupon code
    }

    let addOnDiscountCode = {};
    let materialDiscountCode = (formCountry==="US" || formCountry==="CA" || formCountry==="TH") ? "" : "suhgy7Fb";
    let freewebDiscountCode = "Freeweb";

    let applyAddonCode = "";
    if (textDiscount2 === "FREEWEB"){
        applyAddonCode = freewebDiscountCode;
    }else{
        applyAddonCode = materialDiscountCode;
    }

    let addonArray = cloneCart["add_on"];
    //----------------
    let allAddon = [];
    let data = [];

    for(let x in addonArray){
        let tpid = addonArray[x];
        let tcid = applyAddonCode;

        data.push( {[tpid]: tcid} );
    }

    allAddon = data.reduce(function(result, currentObject) {
        for(let key in currentObject) {
            if (currentObject.hasOwnProperty(key)) {
                result[key] = currentObject[key];
            }
        }
        return result;
    }, {});

    addOnDiscountCode = allAddon;

    usageMainDiscountCode.val(JSON.stringify(codeDiscount));
    usageAddonDiscountCode.val(JSON.stringify(addOnDiscountCode));

    cardDetail = {
        "number": creditCardNumber.val(),
        "exp_month": month,
        "exp_year": year,
        "cvc": creditCCV.val()
    };

    switch (currentPaymentMethod){
        case "Credit Card":
            paymentMethod = "card";
            paymentURL = settings.url_payment.card;
            selectEnv = settings.env_mode;
            break;
        case "Direct Debit":
            paymentMethod = (formCountry==="US") ? "us_bank_account" : "direct";
            selectEnv = settings.env_mode;
            paymentURL = settings.url_payment.card;
            bsb_number = $("#bsbDirectDebit").val();
            account_number = $("#acnDirectDebit").val();
            routingDirectDebit = $("#routingDirectDebit").val();
            break;
        case "Stripe":
            paymentMethod = "stripe";
            paymentURL = settings.url_payment.card;
            selectEnv = settings.env_mode;
            break;
        case "QR":
            paymentMethod = "qr";
            paymentURL = settings.url_payment.card;
            selectEnv = settings.env_mode;
            break;
        case "Invoice":
            paymentMethod = "invoice";
            paymentURL = settings.url_payment.invoice;
            selectEnv = settings.env_mode;
            cardDetail = {};
            customerEmail = $("#emailInvoiceOther").val();
            break;
        default:
            paymentMethod = "card";
            paymentURL = settings.url_payment.card;
            selectEnv = settings.env_mode;
    }

    requestedTime++;

    let setupFeeCharge = $("#setupFeeCharge").val();

    let newCountry = "";// เอาไว้แก้ที UK มีโค๊ดเป็น GB
    if(formCountry === "GB"){ newCountry= "UK"; }
    else { newCountry = formCountry; }

    let stripePayload = {
        "env": selectEnv,
        "country": newCountry,
        "ip_address": myIP.val(),
        "user_agent": agent.val(),
        "payment_method": paymentMethod,
        "restaurant_name": restaurant_name.val().trim(),
        "customer_name": creditFullName.val().trim().toUpperCase(),
        "customer_email": customerEmail.trim().toLowerCase(),
        "products": cloneCart,
        "tax_rate_id": settings.Payment_Detail.tax_rate_id,
        "coupon": {
            "subscription": codeDiscount,
            "add_on": addOnDiscountCode
        },
        "card": cardDetail,
        "bsb_number": bsb_number,
        "routing_number": routingDirectDebit,
        "account_number": account_number
    };
    saveToDB(stripePayload);
    createLogs(stripePayload);
    clonePayload = stripePayload;

    setTimeout(function (){
    }, 1000);

    console.log("stripePayload = ",stripePayload);

    modalRespondAction('open','success');

    //เทสส่งอีเมล sendMailToL4UTeam();

    if(CheckedBoxMakeChargeValue) { //ถ้าเลือกโหมดจ่ายเงิน ให้คิดเงินผ่าน Stripe
        const reqPay = $.ajax({
            url: paymentURL,
            method: 'POST',
            async: true,
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify(stripePayload)
        });

        reqPay.done(function (res) {
            console.log(res);

            if (res.message === "Success") {
                result.empty();
                let done = `<span class="badge bg-success">${res.message}</span>`;
                let cusID = `<span class="badge bg-info">Stripe Connected</span>`;
                $(done).appendTo(".paymentResult");
                $(cusID).appendTo(".paymentResult");
                if (formCountry === "US") {
                    customerStripeIDUSA.val(res.customer_id);
                } else {
                    customerStripeID.val(res.customer_id);
                }
                setTimeout(function () {
                    genLinkPDF();
                    modalRespondAction('open', 'success');
                    sendMail();
                    sendMailToL4UTeam();
                }, 1000);
            } else {
                result.empty();
                let fail = `<span class="badge bg-danger">Payment Fail!!</span>`;
                $(fail).appendTo(".paymentResult");
                res.message = "Payment step is fail"
                alert("Payment step is fail");
            }
            cmdSubmit.removeClass("btn-outline-danger").addClass("btn-outline-success").prop("disabled", false); //enable submit button
            return res.message;
        });

        reqPay.fail(function (xhr, status, error) {
            console.log("ajax request Payment fail!!");
            console.log(status + ': ' + error);
            modalRespondAction('open', 'fail');
            result.html("");
            $("#cmdSubmit").removeClass("btn-outline-info").addClass("btn-outline-success").prop("disabled", false);
            $("#paymentSubmit").prop('disabled', false);
            return res.message;
        });
    }else{ //submit without a charge
            result.empty();
            let done = `<span class="badge bg-success">No Charge</span>`;
            let cusID = `<span class="badge bg-info">No Stripe Connect</span>`;
            $(done).appendTo(".paymentResult");
            $(cusID).appendTo(".paymentResult");
            genLinkPDF();
            sendMailToL4UTeam();
            modalRespondAction('open', 'success');
            cmdSubmit.removeClass("btn-outline-danger").addClass("btn-outline-success").prop("disabled", false); //enable submit button
    }

    return res.message;

}//function

const sendMail = () => {

    let sendMailPayload = {
        "mode" : "confirm",
        "shopName" : $("#00N2v00000IyVqB").val(),
        "fullName" : formData.owner.firstName+" "+formData.owner.lastName,
        "acceptAutoPilotAI" : $("#acceptAutoPilotAI").val(),
        "email" : $("#email").val()
    }

    const sendLog = $.ajax({
        url: "email/sendMail.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: sendMailPayload
    });

    sendLog.done(function(res) {
        console.log(res);
        return true;
    });

    sendLog.fail(function(xhr, status, error) {
        console.log("ajax Send Mail fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}//sendMail

const sendMailToL4UTeam = () => {
    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = today.getFullYear();

    today = dd + '/' + mm + '/' + yyyy;

    ////////if checked on a submitted form will send test mail to IT only/////
    const CheckedBoxTestmail = $("#CheckedBoxTestmail");
    let CheckedBoxTestmailValue = 2;

    if ($(CheckedBoxTestmail).prop('checked')) {
        CheckedBoxTestmailValue = $(CheckedBoxTestmail).val();
    } else {
        CheckedBoxTestmailValue = 0;
    };

    ///////////////////////////////

    //formProduct: $("#currentlyPackage option:selected").text(), อันนี้เลิกใช้ ใช้ MainProduct แทน
    let payload = {
        mode : "alert",
        formDate: today,
        leadSource: 'Signup Form',
        formVersion: $("#signupFormVersion").val(),
        formMessage: 'Hi, Team <br>There are new sign-up customers coming in now. Below are brief details. You can check full information on CRM.',
        formProduct: $("#currentlyPackage option:selected").text(),
        MainProduct: $("input[name='product']:checked").val(),
        formInitialProductOffering: $("#initialProductOffering").val(),
        formSalesAgent: $("#byAgent option:selected").text(),
        formContractPeriod: $("#ContractPeriod").val(),
        formRefPerson: $("#byPerson").val(),
        formRefPartner: $("#byPartner").val(),
        formCoupon: $("#couponCode").val(),
        formRefShop: $("#byRestaurant").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formPaymentMethod: $("#paymentMethod").val(),
        formFlyer: $("#initAddOnPrintedFlyers").val(),
        formDineIn: $("#initAddOnDineInSystem").val(),
        formMagnet: $("#initAddOnFridgeMagnet").val(),
        formSocialMedia: $("#initAddOnSocialMediaPosts").val(),
        formMenuDesign: $("#initAddOnDigitalMenuDesign").val(),
        formWebsiteMakeOver: $("#initAddOnWebsiteMakeOver").val(),
        formADVPromo: $("#initAddOnAdvPromo").val(),
        formWebHosting: $("#initAddOnWebHosting").val(),
        formInfluencer: $("#initAddOnInfluencer").val(),
        formCustomerType: $("#formType option:selected").text(),
        formShopName: $("#00N2v00000IyVqB").val(),
        formCountry: $("#formCountry option:selected").text(),
        ShippingAddress: $("#shipAddress1").val(),
        formFullName: $("#first_name").val().trim() + " " + $("#last_name").val().trim(),
        formEmail: $("#email").val().toLowerCase(),
        formMobile: $("#mobile").val(),
        formBestTime: $("#00N9s000000Nl1G").val(),
        formNote: $("#additionComment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val(),
        acceptAutoPilotAI: $("#acceptAutoPilotAI").val(),
        testMail: CheckedBoxTestmailValue,
        token: Math.random()
    };

    const sendL4UMail = $.ajax({
        url: "email/L4UEmailAlert.php",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    sendL4UMail.done(function(res) {
        console.log(res);
        return true;
    });

    sendL4UMail.fail(function(xhr, status, error) {
        console.log("ajax Send L4U Mail alert fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}//sendMail

const saveToDB = (stripePayload) => {
    let cuisineSelected = [];

    $("input:checkbox[name='00N2v00000IyVpy']:checked").each(function(){
        cuisineSelected.push($(this).val());
    });
    let txtCuisine = cuisineSelected.join();

    let yourArray = [];
    $("input:checkbox[name='00N9s000000QPvX']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtServices = yourArray.join();

    yourArray = [];
    $("input:checkbox[name='00N2v00000IyVq8']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtPayment = yourArray.join();

    let domainUser = $("#ref_Domain_U");
    let domainPass = $("#ref_Domain_P");
    let domainComment = $("#ref_Domain_Comments");
    let domainRegister = $("#ref_Domain_Name_Registered");
    let Country = formData.formCountry;

    let payload = {
        Country: formData.formCountry,
        CustomerType: formData.formType,
        FirstName: formData.owner.firstName.trim(),
        LastName: formData.owner.lastName.trim(),
        Mobile: $("#ownerMobile").val(),
        Email: $("#email").val().trim().toLowerCase(),
        BestTimeToContact: $("#00N9s000000Nl1G").val(),
        ShopName: $("#00N2v00000IyVqB").val().trim(),
        ABN: $("#00N9s000000QPWu").val(),
        TradingName: $("#company").val().trim(),
        ShopNumber: $("#shopPhoneFormatted").val(),
        Website: $("#webURL").val().trim(),
        Language: $(".supportLanguage:checked").val(),
        ShopNumber2: $("#physicalShopNumber").val(),
        Address1: $("#streetAddress1").val().trim(),
        Address2: $("#streetAddress2").val(),
        City: $("#city").val().trim(),
        State: $("#state").val(),
        PostelCode: $("#zip").val().trim(),
        CountryText: $(".countryName").text(),
        ShipNumber: $("#shipNumber").val(),
        ShippingAddress: $("#shipAddress1").val(),
        Cuisine: txtCuisine,
        OtherCuisine: $("#cuisineOther").val(),
        MainProduct: $("input[name='product']:checked").val(),
        LoginEmail: $("#emailShoppingCart").val().trim().toLowerCase(),
        Service: txtServices,
        Delivery: $("input[name='00N9s000000QPvX']:checked").val(),
        TableNumber: $("#tableNumber").val(),
        TableSize: $("#sizeOption").val(),
        Payment: txtPayment,
        Facebook: $("#box_Facebook").val().trim(),
        TikTok: $("#box_TikTok").val().trim(),
        Instagram: $("#box_Instagram").val().trim(),
        Yelp: $("#box_Yelp").val().trim(),
        WebsiteURL: $("#websiteDomainName").val().trim(),
        NewDomain: $("input:checkbox[name='00N2v00000IyVq2']:checked").val(),
        KeepWebsite: $("input:checkbox[name='00N2v00000IyVq1']:checked").val(),
        OwnDomain: $("#newDomain").val().trim(),
        domainUser: domainUser.val().trim(),
        domainPass: domainPass.val().trim(),
        domainComment: domainComment.val().trim(),
        domainRegister: domainRegister.val(),
        Flyer: $("input:checkbox[name='00N9s000000QQaH']:checked").val(),
        FridgeMagnet: $("input:checkbox[name='00N9s000000QQav']:checked").val(),
        AddOn1: $("input:checkbox[name='00N9s000000QgTt']:checked").val(),
        AddOn2: $("input:checkbox[name='00N9s000000QQcI']:checked").val(),
        AddOn3: $("input:checkbox[name='00N9s000000QgU3']:checked").val(),
        AddOn4: $("input:checkbox[name='00N9s000000QgU8']:checked").val(),
        AddOn5: $("input:checkbox[name='00N9s000000QQbP']:checked").val(),
        AddOn6: $("input:checkbox[name='00N9s000000Qn1j']:checked").val(),
        AddOn7: $("input:checkbox[name='00N9s000000Qn1t']:checked").val(),
        OrderDiscount: $("input[name='00N2v00000IyVpz']:checked").val(),
        OtherDiscount: $("#discountOther").val(),
        mainDiscountCode: $("#couponCode").val(),
        addonDiscountCode: $("#couponCode2").val(),
        usageMainDiscountCode: JSON.stringify($("#usageMainDiscountCode").val()),
        usageAddonDiscountCode: JSON.stringify($("#usageAddonDiscountCode").val()),
        SubTotal: $("#subTotal").val(),
        GST: $("#GST").val(),
        Total: $("#grandTotal").val(),
        PaymentMethod: $("#paymentMethod").val(),
        CardNumber: $("#creditCardNumber").val(),
        ExpDate: $("#creditExpireDate").val(),
        CVV: $("#creditCCV").val(),
        CardName: $("#creditFullName").val(),
        EmailDirectDebit: $("#emailDirectDebit").val().trim().toLowerCase(),
        BSB: $("#bsbDirectDebit").val(),
        EmailInvoice: $("#emailInvoiceOther").val().trim().toLowerCase(),
        Routing_number: $("#routingDirectDebit").val(),
        AccountNumber: $("#acnDirectDebit").val(),
        acceptAutoPilotAI: $("#acceptAutoPilotAI").val(),
        AdditionNote: $("#additionComment").val().trim(),
        ShopAgent: $("#byAgent").val(),
        ReferredByPerson: $("#byPerson").val().trim(),
        formRefPartner: $("#byPartner").val(),
        ReferredByShop: $("#byRestaurant").val().trim(),
        CustomerStripeID: $("#customerStripeID").val(),
        formProduct: $("#currentlyPackage option:selected").text(),
        formInitialProductOffering: $("#initialProductOffering").val(),
        formSalesAgent: $("#byAgent option:selected").text(),
        formContractPeriod: $("#ContractPeriod").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val().trim()
    };

    const saveToDB = $.ajax({
        url: settings.url_saveToDB,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "stripePayload" : stripePayload,
            "payload" : payload,
            "country" : Country
        }
    });

    saveToDB.done(function(res) {
        console.log(res);
        return true;
    });

    saveToDB.fail(function(xhr, status, error) {
        console.log("Save to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}


const createLogs = (stripePayload) => {
    let cuisineSelected = [];

    $("input:checkbox[name='00N2v00000IyVpy']:checked").each(function(){
        cuisineSelected.push($(this).val());
    });
    let txtCuisine = cuisineSelected.join();

    let yourArray = [];
    $("input:checkbox[name='00N9s000000QPvX']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtServices = yourArray.join();

    yourArray = [];
    $("input:checkbox[name='00N2v00000IyVq8']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtPayment = yourArray.join();

    let domainUser = $("#ref_Domain_U");
    let domainPass = $("#ref_Domain_P");
    let domainComment = $("#ref_Domain_Comments");
    let domainRegister = $("#ref_Domain_Name_Registered");

    let tempData = {
        Country: formData.formCountry,
        CustomerType: formData.formType,
        FirstName: formData.owner.firstName.trim(),
        LastName: formData.owner.lastName.trim(),
        Mobile: $("#ownerMobile").val(),
        Email: $("#email").val().trim().toLowerCase(),
        BestTimeToContact: $("#00N9s000000Nl1G").val(),
        ShopName: $("#00N2v00000IyVqB").val().trim(),
        ABN: $("#00N9s000000QPWu").val(),
        TradingName: $("#company").val().trim(),
        ShopNumber: $("#shopPhoneFormatted").val(),
        Website: $("#webURL").val().trim(),
        Language: $(".supportLanguage:checked").val(),
        ShopNumber2: $("#physicalShopNumber").val(),
        Address1: $("#streetAddress1").val().trim(),
        Address2: $("#streetAddress2").val(),
        City: $("#city").val().trim(),
        State: $("#state").val(),
        PostelCode: $("#zip").val().trim(),
        CountryText: $(".countryName").text(),
        ShipNumber: $("#shipNumber").val(),
        ShippingAddress: $("#shipAddress1").val(),
        Cuisine: txtCuisine,
        OtherCuisine: $("#cuisineOther").val(),
        MainProduct: $("input[name='product']:checked").val(),
        LoginEmail: $("#emailShoppingCart").val().trim().toLowerCase(),
        Service: txtServices,
        Delivery: $("input[name='00N9s000000QPvX']:checked").val(),
        TableNumber: $("#tableNumber").val(),
        TableSize: $("#sizeOption").val(),
        Payment: txtPayment,
        Facebook: $("#box_Facebook").val().trim(),
        TikTok: $("#box_TikTok").val().trim(),
        Instagram: $("#box_Instagram").val().trim(),
        Yelp: $("#box_Yelp").val().trim(),
        WebsiteURL: $("#websiteDomainName").val().trim(),
        NewDomain: $("input:checkbox[name='00N2v00000IyVq2']:checked").val(),
        KeepWebsite: $("input:checkbox[name='00N2v00000IyVq1']:checked").val(),
        OwnDomain: $("#newDomain").val().trim(),
        domainUser: domainUser.val().trim(),
        domainPass: domainPass.val().trim(),
        domainComment: domainComment.val().trim(),
        domainRegister: domainRegister.val(),
        Flyer: $("input:checkbox[name='00N9s000000QQaH']:checked").val(),
        FridgeMagnet: $("input:checkbox[name='00N9s000000QQav']:checked").val(),
        AddOn1: $("input:checkbox[name='00N9s000000QgTt']:checked").val(),
        AddOn2: $("input:checkbox[name='00N9s000000QQcI']:checked").val(),
        AddOn3: $("input:checkbox[name='00N9s000000QgU3']:checked").val(),
        AddOn4: $("input:checkbox[name='00N9s000000QgU8']:checked").val(),
        AddOn5: $("input:checkbox[name='00N9s000000QQbP']:checked").val(),
        AddOn6: $("input:checkbox[name='00N9s000000Qn1j']:checked").val(),
        AddOn7: $("input:checkbox[name='00N9s000000Qn1t']:checked").val(),
        OrderDiscount: $("input[name='00N2v00000IyVpz']:checked").val(),
        OtherDiscount: $("#discountOther").val(),
        mainDiscountCode: $("#couponCode").val(),
        addonDiscountCode: $("#couponCode2").val(),
        usageMainDiscountCode: JSON.stringify($("#usageMainDiscountCode").val()),
        usageAddonDiscountCode: JSON.stringify($("#usageAddonDiscountCode").val()),
        SubTotal: $("#subTotal").val(),
        GST: $("#GST").val(),
        Total: $("#grandTotal").val(),
        PaymentMethod: $("#paymentMethod").val(),
        CardNumber: $("#creditCardNumber").val(),
        ExpDate: $("#creditExpireDate").val(),
        CVV: $("#creditCCV").val(),
        CardName: $("#creditFullName").val(),
        EmailDirectDebit: $("#emailDirectDebit").val().trim().toLowerCase(),
        BSB: $("#bsbDirectDebit").val(),
        EmailInvoice: $("#emailInvoiceOther").val().trim().toLowerCase(),
        Routing_number: $("#routingDirectDebit").val(),
        AccountNumber: $("#acnDirectDebit").val(),
        acceptAutoPilotAI: $("#acceptAutoPilotAI").val(),
        AdditionNote: $("#additionComment").val().trim(),
        ShopAgent: $("#byAgent").val(),
        ReferredByPerson: $("#byPerson").val().trim(),
        formRefPartner: $("#byPartner").val(),
        ReferredByShop: $("#byRestaurant").val().trim(),
        CustomerStripeID: $("#customerStripeID").val(),
        formProduct: $("#currentlyPackage option:selected").text(),
        formInitialProductOffering: $("#initialProductOffering").val(),
        formSalesAgent: $("#byAgent option:selected").text(),
        formContractPeriod: $("#ContractPeriod").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val().trim()
    };

    const sendLog = $.ajax({
        url: settings.url_logs,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "allData" : tempData
        }
    });

    sendLog.done(function(res) {
        console.log(res);
        return true;
    });

    sendLog.fail(function(xhr, status, error) {
        console.log("ajax Log file fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}

/// ไว้ Set ตัวแปร setup fee ที่เป็น global
const setSetupFee = (param) => {
  setupFee = param;
  let showSetupFeeAmount = (parseInt(param)*0.01);
  const SetupFeeAmount = $(".SetupFeeAmount");
    SetupFeeAmount.html(showSetupFeeAmount.toFixed(2)+" + GST");
    SetupFeeAmount.val(showSetupFeeAmount.toFixed(2));
    $("#setupFeeCharge").val(param);
}

/// ไว้เปลี่ยน option contract period ตาม radio button ที่เลือก
const setPeriodSelectBox = (month) => {
  const boxContractPeriod = $("#ContractPeriod");
  switch (month) {
      case "0" :
          boxContractPeriod.val('No contract');
          break;
      case "3" :
          boxContractPeriod.val('3 months');
          break;
      case "12" :
          boxContractPeriod.val('12 months');
          break;
      default :
          boxContractPeriod.val('');
  }
}//setPeriodSelectBox

const readForm = () => {
    cancelFrm.country = `${$("#formCountry").val()}`;
    cancelFrm.countryText = `${$("#formCountry option:selected").text()}`;
    cancelFrm.shopName = `${$("#shopName").val()}`;
    cancelFrm.tradingName = `${$("#company").val()}`;
    cancelFrm.address = `${$("#streetAddress1").val()}, ${$("#city").val()}, ${$("#state option:selected").text()} ${$("#zip").val()} ${cancelFrm.countryText}`;
    cancelFrm.fullName = `${$("#first_name").val()} ${$("#last_name").val()}`;
    cancelFrm.mobile = `${$("#ownerMobile").val()}`;
    cancelFrm.email = `${$("#email").val()}`;
    cancelFrm.reason = `${$("#formReason option:selected").text()}`;
    cancelFrm.lastDate = `${$("#lastDate").val()}`;
    cancelFrm.comment = `${$("#additionComment").val()}`;
    console.log(cancelFrm);

    sendMail();
}

const submitToCRM = () => {
    const first_name = $("#first_name");
    const last_name = $("#last_name");
    let cap_first_name = capitalize(first_name.val());
    let cap_last_name = capitalize(last_name.val());

    first_name.val(cap_first_name);
    last_name.val(cap_last_name);
    //ถ้า Product ที่เลือกเป็นตัวที่บังคับเป็น 1 ปอนด์ให้แก้ราคาเป็น 1 ปอนด์
    if(clonePayload.products.subscription[0]==="UK1TRIAL"){
        $("#firstTimePayment").val("GBP 1.00");
    }

    applicationForm.submit();
}

const openConfirm = () => {
    modalRespondAction('open','success');
}