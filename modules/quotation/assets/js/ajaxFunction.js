const typeJsonKey = (txt) => {
    return txt === "Thai Massage" ? "Massage" : "Restaurant";
}



function getProductList(country) {

    if (formData.formCountry === ""){
        $("#warn_form_country").show();
        return false;
    }else { $("#warn_form_country").hide(); }//ถ้าไม่เลือก span (Please select !!)จะโผล่

    if (formData.formType === ""){
        $("#warn_form_type").show();
        return false;
    }else { $("#warn_form_type").hide(); }//ถ้าไม่เลือก span (Please select !!)จะโผล่
    //////////////////

    let price_id = undefined;
    let product_id = undefined;
    let formType = formData.formType;//รับค่าจากไฟล์ global_data.js Restaurant ,Massage
    let formCountry = formData.formCountry;//รับค่าจากไฟล์ global_data.js AU ,US ,NZ ,UK ,CA ,TH
    let formTypeJsonKey = typeJsonKey(formType);// เอามา return กลับว่าคำว่า Thai Massage ไหมถ้าใช่จะ return Massage ถ้าไม่ return Restaurant
    let contractPeriod = $("input[name='contractPeriod']:checked").val();//รับค่า 0 ,3 ,12
    setPeriodSelectBox(contractPeriod);//0 = No contract ,3 =
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
                }else if (name.search("Yelp Ad Spend")>-1){
                    classType = "isYelpAdSpend";
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
                    'A6 Flyers x 1,000 pcs' : 'addonFlyers',
                    'A6 Flyers x 2,000 pcs' : 'addonFlyers',
                    'A6 Flyers x 5,000 pcs' : 'addonFlyers',
                    'A6 Flyers x 10,000 pcs' : 'addonFlyers',
                    'Flyers A6 (US 5` x 7`) x 1,000' : 'addonFlyers',
                    'Flyers A6 (US 5` x 7`) x 2,000' : 'addonFlyers',
                    'Flyers A6 (US 5` x 7`) x 5,000' : 'addonFlyers',
                    'Flyers A6 (US 5` x 7`) x 10,000' : 'addonFlyers',
                    'Flyers 5` x 7` x 1,000 pcs' : 'addonFlyers',
                    'Flyers 5` x 7` x 2,000 pcs' : 'addonFlyers',
                    'Flyers 5` x 7` x 5,000 pcs' : 'addonFlyers',
                    'Flyers 5` x 7` x 10,000 pcs' : 'addonFlyers',
                    'Fridge Magnet x 500 pcs' : 'addonFridgeMagnet',
                    'Fridge Magnet x 1,000 pcs' : 'addonFridgeMagnet',
                    'Fridge Magnet x 2,000 pcs' : 'addonFridgeMagnet',
                    'Fridge Magnet x 4,000 pcs' : 'addonFridgeMagnet',
                    'Yelp Ad Spend $10' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $20' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $30' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $40' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $50' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $100' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $200' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $300' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $400' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $500' : 'addonYelpAdSpend',
                    'Yelp Ad Spend $1000' : 'addonYelpAdSpend',
                    'POS System' : 'addonPOS',
                    'Menu / Massage Pricing Design' : 'addonPricingDesign',
                    'Dine-In Dual Online Ordering System' : 'addonDineInDual',
                    'Promotions Add-on' : 'addonAdvPromo',
                    'Mob App' : 'addonMobApp',
                    'Website Hosting + Email included' : 'addonWebsiteHosting',
                    'Social Media Management' : 'addonSocialMedia',
                    'Website Makeover/ Build template customize' : 'addWebsiteMakeoverTemplate',
                    'Website Makeover/ Build fully customize' : 'addWebsiteMakeoverFully',
                    'Google Review Respond' : 'addonGoogleReview',
                    'Influencer Package' : 'addonInfluencer',
                    'Social Media Set Up' : 'addonSocialMediaSetup'
                }

                // ถ้าเป็น Website Hosting ให้ใส่ class ไว้ จะเอาไว้เลือก Auto จาก package อื่น
                if (name.includes("Website Hosting")){
                    checkIsOptionWebHosting = "optionWebHosting";
                }else{
                    checkIsOptionWebHosting = "";
                }
                /////////


                if(formCountry==="US"){
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
                }else if(formCountry==="UK"){
                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType} ${checkIsOptionWebHosting}" 
                        type="checkbox" 
                        name=${boxName[name]} 
                        id="addon-${product_id}" 
                        value="${name} - ${formData.formCurrency.charAt(0)}£ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} ${currencySign}${price} ${special}</b>
                        ${discountText}
                    </label>
                </div>`;
                }else if(formCountry==="TH"){
                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType} ${checkIsOptionWebHosting}" 
                        type="checkbox" 
                        name=${boxName[name]} 
                        id="addon-${product_id}" 
                        value="${name} - ${formData.formCurrency.charAt(0)}฿ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} ${currencySign}${price} ${special}</b>
                        ${discountText}
                    </label>
                </div>`;
                }

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
        const chkIsYelpAdSpend = document.querySelector(".isYelpAdSpend");

        //set initial product value
        const initAddOnAdvPromo = $("#initAddOnAdvPromo");
        const initAddOnSocialMediaPosts = $("#initAddOnSocialMediaPosts");
        const initAddOnInfluencer = $("#initAddOnInfluencer");
        const initAddOnDineInSystem = $("#initAddOnDineInSystem");
        const initAddOnDigitalMenuDesign = $("#initAddOnDigitalMenuDesign");
        const initAddOnWebsiteMakeOver = $("#initAddOnWebsiteMakeOver");
        const initAddOnWebHosting = $("#initAddOnWebHosting");
        const initAddOnYelpAdSpend = $("#initAddOnYelpAdSpend");

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
        if (typeof(chkIsYelpAdSpend) != 'undefined' && chkIsYelpAdSpend != null) {
            chkIsYelpAdSpend.addEventListener("change", () => {
                if (chkIsYelpAdSpend.checked) {
                    initAddOnYelpAdSpend.val(chkIsYelpAdSpend.value);
                } else {
                    initAddOnYelpAdSpend.val("");
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
    const restaurant_name = $("#shopName");
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

    if (typeof Payment_Coupon_Obj !== "undefined" && Payment_Coupon_Obj !== null) { // check if Payment_Coupon_Obj exists
        console.log("เจอส่วนลดแล้ว");

        let pid = cloneCart["subscription"][0]; // read main product ID
        let cid = Payment_Coupon_Obj.code ?? ""; // use empty string if null or undefined

        codeDiscount = { [pid]: cid }; // set the main coupon code
    } else {
        let pid = cloneCart["subscription"][0]; // read main product ID
        let cid = ""; // force empty string if Payment_Coupon_Obj is undefined or null

        codeDiscount = { [pid]: cid }; // set the main coupon code
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
        "ip_address": "58.8.159.115",
        "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
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
    if(formCountry === "TH"){saveTaxToDB(stripePayload)}
    createLogs(stripePayload);
    clonePayload = stripePayload;

    setTimeout(function (){
    }, 1000);

    console.log("stripePayload = ",stripePayload);

    modalRespondAction('open','success');

    if(CheckedBoxMakeChargeValue) { //ถ้าเลือกโหมดจ่ายเงิน ให้คิดเงินผ่าน Stripe
        // TODO :  ชาร์จเงินผ่าน stripe
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

            let stripeRes = JSON.stringify(res);

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

                stripeResToDB(stripeRes);
                if(formCountry === "TH"){invoiceIDToDB(stripeRes)}


                setTimeout(function () {
                    genLinkPDF();
                    modalRespondAction('open', 'success');
                    sendMailToL4UTeam();
                }, 1000);
            } else {
                result.empty();
                let fail = `<span class="badge bg-danger">Payment Fail!!</span>`;
                $(fail).appendTo(".paymentResult");
                res.message = "Payment step is fail"
                alert("Payment step is fail");
                let stripeRes = "Ajax Fail : " + stripeRes;
                stripeResToDB(stripeRes);
                if(formCountry === "TH"){invoiceIDToDB(stripeRes)}
            }
            cmdSubmit.removeClass("btn-outline-danger").addClass("btn-outline-success").prop("disabled", false); //enable submit button

            return res.message;
        });

        reqPay.fail(function (xhr, status, error) {
            //console.log(xhr.responseText);
            console.log("ajax request Payment fail!!");
            console.log(status + ': ' + error);
            modalRespondAction('open', 'fail');
            result.html("");
            $("#cmdSubmit").removeClass("btn-outline-info").addClass("btn-outline-success").prop("disabled", false);
            $("#paymentSubmit").prop('disabled', false);

            let stripeRes = xhr.responseText;

            stripeResToDB(stripeRes);
            if(formCountry === "TH"){invoiceIDToDB(stripeRes)}
            return res.message;
        });
    }else{ //submit without a charge
        let stripeRes = "Test Mode - No Charge";
        invoiceIDToDB(stripeRes)
        stripeResToDB(stripeRes);
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


const submitToCRM = () => {
    const first_name = $("#first_name");
    const last_name = $("#last_name");
    let cap_first_name = capitalize(first_name.val());
    let cap_last_name = capitalize(last_name.val());

    first_name.val(cap_first_name);
    last_name.val(cap_last_name);
    //ถ้า Product ที่เลือกเป็นตัวที่บังคับเป็น 1 ปอนด์ให้แก้ราคาเป็น 1 ปอนด์
    /*if(clonePayload.products.subscription[0]==="UK1TRIAL"){
        $("#firstTimePayment").val("GBP 1.00");
    }*/

    applicationForm.submit();
}



const openConfirm = () => {
    modalRespondAction('open','success');
}

$('#formType').on('change', function () {
    const selectedValue = $(this).val();
    if (selectedValue === "Thai Restaurants & Takeaways" || selectedValue === "Restaurants & Takeaways") {
        $('#contentPOS').show();
    } else {
        $('#contentPOS').hide();
    }
});

/*function calculateVAT(price) {
    // 1. ราคาก่อน VAT
    let a = price;

    // 2. คำนวณ VAT 7%
    let b = parseFloat((a * 0.07).toFixed(2));

    // 3. ราคาหลังรวม VAT
    let c = parseFloat((a - b).toFixed(2));

    return { a, b, c };
}*/

function getPriceBeforeVAT(priceWithVAT) {
    if (isNaN(priceWithVAT) || priceWithVAT === 0) {
        return 0;
    }
    // หารด้วย 1.07 เพื่อหาราคาตั้งต้น (ราคาก่อน VAT)
    return parseFloat((priceWithVAT / 1.07).toFixed(2));
}
$("#cmdSubmit").click(function(e){
    e.preventDefault();

    // --- ส่วนของการดึงข้อมูลจากฟอร์ม (เหมือนเดิม) ---
    let checkBoxWantTAX = $("input:checkbox[id='quotationYes']:checked").val();
    let taxType = $("input:radio[name='taxType']:checked").val();
    let nameQuotation = $("#quotationName").val();
    let shopNameQuotation = $("#quotationShopName").val();
    let phoneQuotation = $("#shopPhoneQuotationFormatted").val();
    let emailQuotation = $("#quotationEmail").val();
    let addressQuotation = $("#quotationAddress").val();
    let taxNumberQuotation = $("#quotationTaxNumber").val();
    let subTotal = $("#subTotal").val();
    let tax = $("#GST").val();
    let grandTotal = $("#grandTotal").val();
    let realNameQuotation = "";
    let selectedInputId = $("input[name='product']:checked").attr("id");
    let product = $("label[for='" + selectedInputId + "']").text().trim().replace(/\n/g, '');
    let setupFee = $("input[name='setup']:checked").val();
    let addonSelect = $("input[name='addonSocialMediaSetup']:checked").val();
    let shopAgent = $("#byAgent").val();
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    // --- ส่วนของการคำนวณราคาที่ถูกต้อง (ที่คุณแก้ไขมาแล้ว) ---
    //// product /////
    let partsPro = product.split(" - ");
    let nameProduct = partsPro[0];
    let priceProduct = partsPro[1] || "฿0.00";
    let priceOnly = parseFloat(priceProduct.match(/[\d,.]+/)[0].replace(/,/g, ''));
    let productAmountBeforeVAT = getPriceBeforeVAT(priceOnly);

    //// setupFee /////
    let nameSetup = "Setup Fee (No Contract)";
    let setupFeeAmountBeforeVAT = 0;
    if (setupFee && setupFee.trim() !== "") {
        let partsSetupFee = setupFee.split(" - ");
        nameSetup = partsSetupFee[0];
        let priceSetup = partsSetupFee[1] || "฿0.00";
        let priceSetupOnly = parseFloat(priceSetup.match(/[\d,.]+/)[0].replace(/,/g, ''));
        setupFeeAmountBeforeVAT = getPriceBeforeVAT(priceSetupOnly);
    }

    //// addon /////
    let nameAddon = "No Addon";
    let addonAmountBeforeVAT = 0;
    if (addonSelect && addonSelect.trim() !== "") {
        let partsAddon = addonSelect.split(" - T฿ ");
        nameAddon = partsAddon[0];
        let priceAddon = partsAddon[1] || "0.00";
        let priceAddonOnly = parseFloat(priceAddon.match(/[\d,.]+/)[0].replace(/,/g, ''));
        addonAmountBeforeVAT = getPriceBeforeVAT(priceAddonOnly);
    }

    // --- ส่วนของการสร้างข้อมูลสำหรับส่งไปเซิร์ฟเวอร์ (แก้ไขแล้ว) ---
    let today = new Date();
    let day = String(today.getDate()).padStart(2, '0');
    let month = String(today.getMonth() + 1).padStart(2, '0');
    let year = today.getFullYear();
    let currentDate = `${day}/${month}/${year}`;

    if(taxType === "นิติบุคคล"){
        realNameQuotation = shopNameQuotation;
    }else{
        realNameQuotation = shopNameQuotation + " โดย " + nameQuotation;
    }

    // ✅ 1. เปิดใช้งานบรรทัดนี้ และลบโค้ดชุดเก่าที่ซ้ำซ้อนทิ้งไป
    let tableData = [];

    tableData.push({
        "product": nameProduct,
        "qyt": 1,
        "amount": productAmountBeforeVAT
    });

    if (setupFeeAmountBeforeVAT > 0) {
        tableData.push({ "setupfee": nameSetup, "qyt": 1, "amount": setupFeeAmountBeforeVAT });
    }

    if (addonAmountBeforeVAT > 0) {
        tableData.push({ "addon": nameAddon, "qyt": 1, "amount": addonAmountBeforeVAT });
    }

    // ✅ 2. สร้าง object หลักด้วย tableData ที่ถูกต้องเพียงชุดเดียว
    let productQuotation = {
        "quotation": [
            {
                "date": currentDate,
                "detail": [
                    {
                        "company": realNameQuotation,
                        "address": addressQuotation,
                        "tax_id": taxNumberQuotation,
                        "email": emailQuotation,
                        "phone": phoneQuotation
                    }
                ]
            }
        ],
        "table": tableData,
        "summary": {
            "subtotal": subTotal,
            "tax": tax,
            "grandtotal": grandTotal
        }
    };



    $.ajax({
        url: "assets/function/detailQuotation.php",
        type: "POST",
        data: {
            shopAgent: shopAgent,
            act: "add",
            price: productQuotation,
            finalPrice: grandTotal
        },
        success: function(result) {
            console.log("Raw result:", result);

            let res = (typeof result === "object") ? result : JSON.parse(result);

            if (res.result === "success") {
                let quotationID = res.quotationID;
                $("#quotationID").val(quotationID);

                // ✅ ขั้นตอนที่ 2: อัปเดตเลขที่ใบเสนอราคา
                $.ajax({
                    url: "assets/function/detailQuotation.php",
                    type: "POST",
                    data: {
                        act: "update",
                        quotationID: quotationID,
                        shopAgent: shopAgent
                    },
                    success: function(updateRes) {
                        console.log("Update quotation done:", updateRes);

                        // ✅ ขั้นตอนที่ 3: สร้าง PDF + เปิดไฟล์ในแท็บใหม่
                        let pdfUrl = "Monday/pdfQuotation.php?quotationID=" + quotationID;

                        $.ajax({
                            url: pdfUrl,
                            type: "GET",
                            data: {
                                quotationID: quotationID,
                            },
                            success: function(pdfRes) {
                                console.log("PDF created successfully:");

                                // 🔥 เปิดไฟล์ PDF ในแท็บใหม่
                                window.open(pdfUrl, "_blank");
                            },
                            error: function(xhr, status, error) {
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating quotation:", error);
                        alert("เกิดข้อผิดพลาดตอนอัปเดตใบเสนอราคา");
                    }
                });

            } else {
                alert("ไม่สามารถบันทึกข้อมูลได้: " + res.msg);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error in first AJAX:", error);
            alert("เกิดข้อผิดพลาดตอนบันทึกใบเสนอราคา");
        }
    });





});




