function getProductList(country) {
    let price_id = undefined;
    let product_id = undefined;
    let formType = formData.formType;
    let formCountry = formData.formCountry;
    const loadingAjax = $("#loadingAjax");
    loadingAjax.html("<img alt='Loading' src='assets/img/loading.gif'>");
    //console.log(formData);
    const reqProductList = $.ajax({
        url: settings.url_getProductList,
        method: 'POST',
        async: true,
        dataType: 'json',
        crossDomain: true,
        data: {
            "env": "'"+settings.env_mode+"'",
            "country": formCountry,
            "products": {
                "0": "prod_NLcT8DjKXyWaUW",
                "1": "prod_NLcVGPTTaxEBaS",
                "2": "prod_NLcWZ1JSU2xrzY",
                "3": "prod_NLcc9qneu7PI8P",
                "4": "prod_NLcd1970S04wyc",
                "5": "prod_NLcehl1x4jDo4a",
                "6": "prod_NLcf0VrwPCsBHq",
                "7": "prod_NLd4OuUr9ltuTG",
                "8": "prod_NLd4aMdBayHQUh",
                "9": "prod_NLd4j8pnuBwjCw",
                "10": "prod_NLd5mn5LpYaGuU",
                "11": "prod_NLd6vtR8RfyTZd",
                "12": "prod_NLd6Hg6FE0UBEA",
                "13": "prod_NLd6cEMeLzfjqW",
                "14": "prod_NLd7AJ6DzKUsj3",
                "15": "prod_NLd7kFrwQ7cMcQ",
                "16": "prod_NLd75rW0mB5QAH",
                "17": "prod_NLd8yr9uChiaZ9",
                "18": "prod_NLd8wtttQ24hbs",
                "19": "prod_NLd8iG72ZvX4P5",
                "20": "prod_NSAmAOLRTeZMbH",
                "21": "prod_NSAoJC5S6j23nq",
                "22": "prod_NTL2evgYqPotm5",
                "23": "prod_NTL23OGnBDljjo",
                "24": "prod_NTL2iYsJKIXyI8",
                "25": "prod_NTL3fsaAg9tgBI",

            }
        }
    });
    /////

    reqProductList.done(function(res) {
        mainProduct2 = res.data[0];
        addonProduct2 = res.data[1];

        readMainProduct = mainProduct2.filter((item) => {
            return item.currency === formData.formCurrency.toLowerCase();
        })

        readAddonProduct = addonProduct2.filter((item) => {
            return item.currency === formData.formCurrency.toLowerCase();
        })
        if(readMainProduct.length > 0){
            //console.log("Read from Stripe found - ",readMainProduct.length," Main items = ",readMainProduct);
            $("#products2").empty();
            let productRadio2 = readMainProduct.map((item) => {
                let ran = Math.random();
                let name = "";
                let special = (formData.formCurrency)==="AUD"?" + GST ":"";
                let price = 0;
                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                amount = item.amount;
                let product_id = item.price_id;

                //ถ้าเป็นร้านนวดไม่ต้องแสดง product บางตัว
                let findSomeProduct  = name.search("Pro Online Ordering System|Social Media Bundle|Direct Marketing Bundle");
                if ((formType==="Thai Massage") && ((findSomeProduct>-1))){
                    return false;
                }else{
                    return `<div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="product" 
                            id="product${ran}" 
                            value="${name} - $${price} ${special}/Month"
                            onclick="addMainCart('${name}', '${price}', '${amount}', '${special}', '${product_id}');"
                        >
                        <label class="form-check-label" for="product${ran}" >
                            ${name} <b class="text-primary"> - $${price} ${special}/Month</b>
                        </label>
                    </div>`;
                }
            });

            productRadio2.map((item) => {
                $(item).appendTo( "#products2" );
            });
        }else{
            let textMainProduct = `<small class="text-danger">Sorry we don't have any Package in this currency yet !!</small>`;
            $("#products2").html(textMainProduct);
        }

        if(readAddonProduct.length > 0){
            //console.log("Read from Stripe found - ",readAddonProduct.length," Add on items = ",readAddonProduct);
            $("#addon2").empty();
            $("#addon3").empty();
            let didItFlyer = false;
            let didItFridge = false;
            let newLine = false;
            let addonCheck2 = readAddonProduct.map((item) => {
                let name = "";
                let special = (formData.formCurrency)==="AUD"?" + GST ":"";
                let price = 0;
                let addText = "";
                let discountValue = Math.round((item.amount*15)/100)/100;
                let cartPrice = (item.amount-Math.round((item.amount*15)/100));
                let realPrice = cartPrice/100;

                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                amount = item.amount;
                let product_id = item.price_id;
                let position = name.search("A6|Fridge");
                let position2 = name.search("Fridge");
                let findInfluencer  = name.search("Influencer|Digital");
                let discountText = "";
                let leadDiscountText = "";
                let classType = "";

                if (name.search("US 5")>-1){
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
                }else if (name.search("Website Make Over")>-1){
                    classType = "isWebsiteMakeOver";
                }else if (name.search("Web Hosting")>-1){
                    classType = "isWebHosting";
                }else { classType = ""; }

                if((position>-1) && (!didItFlyer)){
                    if(formCountry==="US"){
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
                    if(formCountry==="US"){
                        discountText = ``;
                        leadDiscountText = "";
                    }
                    else {
                        discountText = `<b class="text-success">(will save $${discountValue.toFixed(2)})</b>
                                = <b class="text-primary">$${realPrice.toFixed(2)}${special}<b/>`;
                        leadDiscountText = " From ";
                    }
                }else {
                    if(findInfluencer>-1){
                        discountText = "";
                    }else { discountText = `<b class="text-primary">/Month</b>`; }
                    leadDiscountText = "";
                    if(!newLine) {
                        addText = "<div class='text-warning mt-4'>Others</div>";
                    }
                    newLine = true;
                    realPrice = price;
                    cartPrice = amount;
                }

                const boxName = {
                    'A6 Flyers x 1,000 pcs - One time Purchase' : '00N9s000000QQaH',
                    'A6 Flyers x 2,000 pcs - One time Purchase' : '00N9s000000QQaH',
                    'A6 Flyers x 5,000 pcs - One time Purchase' : '00N9s000000QQaH',
                    'A6 Flyers x 10,000 pcs - One time Purchase' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 1,000 - One time Purchase' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 2,000 - One time Purchase' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 5,000 - One time Purchase' : '00N9s000000QQaH',
                    'Flyers A6 (US 5` x 7`) x 10,000 - One time Purchase' : '00N9s000000QQaH',
                    'Fridge Magnet x 500 - One time Purchase' : '00N9s000000QQav',
                    'Fridge Magnet x 1,000 - One time Purchase' : '00N9s000000QQav',
                    'Fridge Magnet x 2,000 - One time Purchase' : '00N9s000000QQav',
                    'Fridge Magnet x 4,000 - One time Purchase' : '00N9s000000QQav',
                    'Adv Promo - Setup' : '00N9s000000QgTt',
                    'Social Media Management - Setup' : '00N9s000000QQcI',
                    'Influencer Package - Setup' : '00N9s000000QgU3',
                    'Dine-in Dual Online Ordering System - Setup' : '00N9s000000QgU8',
                    'Digital Menu Design - Setup' : '00N9s000000QQbP',
                    'Website Make Over - Setup' : '00N9s000000Qn1j',
                    'Add-on Web Hosting (Free Website Setup)' : '00N9s000000Qn1t'
                }

                //ถ้าเป็นร้านนวดจะไม่แสดง Add-on บางตัว
                let findSomeAddOn  = name.search("Adv Promo|Influencer Package|Dine-in Dual Online Ordering System");
                if ((formType==="Thai Massage") && ((findSomeAddOn>-1))){
                    return `${addText}`;
                }else {
                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType}" 
                        type="checkbox" 
                        name=${boxName[name]} 
                        id="addon-${product_id}" 
                        value="${name} - ${formData.formCurrency.charAt(0)}$ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} $${price} ${special}</b>
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
let cloneCart = {};
function requestToPay() {
    let paymentURL = settings.url_payment.card;
    let paymentMethod = "card";
    let cardDetail = {};

    const currentPaymentMethod = $("#paymentMethod").val();
    const customerStripeEmail = $("#customerStripeEmail");
    const customerStripeID = $("#customerStripeID");
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
    let customerEmail = shopOwnerEmail.val();
    let bsb_number = "000000";
    let account_number = "000123456";
    let addOnDiscountCode = "suhgy7Fb";

    let single = creditExpireDate.split("/");
    let month = parseInt(single[0],10);
    let year = parseInt(single[1],10)+2000;
    let formCountry = formData.formCountry;

    let res = {
        "message": "Dummy message",
        "customer_id": null,
        "error": ""
    }

    //add on discount not for USA
    if (formCountry==="US"){
        addOnDiscountCode = "";
    }else {
        addOnDiscountCode = "suhgy7Fb";
    }
    //

    // action before sent
    const result = $(".paymentResult");
    result.html("Payment request submitted, loading... <img alt='Loading' src='assets/img/loading.gif'>");
    $("#cmdSubmit").removeClass("btn-outline-success").addClass("btn-outline-info").prop("disabled", true);
    $("#paymentSubmit").prop('disabled', true);
    paymentTimestamp();
    /////

    let textDiscount = couponCode.val().trim().toUpperCase();
    let Payment_Coupon_Obj = settings.Payment_Detail.coupon_Code[formCountry];
    let codeDiscount = "";

    if (typeof Payment_Coupon_Obj[textDiscount] !== "undefined") { //check this coupon code is exist in setting list
        codeDiscount = Payment_Coupon_Obj[textDiscount].code; //read stripe coupon code
    }

    usageMainDiscountCode.val(codeDiscount);
    usageAddonDiscountCode.val(addOnDiscountCode);

    if (requestedTime<=0){
        cloneCart = cart;
        cloneCart.subscription = cloneCart.subscription.reduce(function(acc, cur, i) { //convert array to object
            acc[i] = cur;
            return acc;
        }, {});
        cloneCart.add_on = cloneCart.add_on.reduce(function(acc, cur, i) { //convert array to object
            acc[i] = cur;
            return acc;
        }, {});
    }

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
            break;
        case "Direct Debit":
            paymentMethod = "direct";
            paymentURL = settings.url_payment.card;
            bsb_number = $("#bsbDirectDebit").val();
            account_number = $("#acnDirectDebit").val();
            break;
        case "Stripe":
            paymentMethod = "stripe";
            paymentURL = settings.url_payment.card;
            break;
        case "QR":
            paymentMethod = "qr";
            paymentURL = settings.url_payment.card;
            break;
        case "Invoice":
            paymentMethod = "invoice";
            paymentURL = settings.url_payment.invoice;
            cardDetail = {};
            customerEmail = $("#emailInvoiceOther").val();
            break;
        default:
            paymentMethod = "card";
            paymentURL = settings.url_payment.card;
    }

    requestedTime++;

    let stripePayload = {
        "env": settings.env_mode,
        "country": formCountry,
        "ip_address": myIP.val(),
        "user_agent": agent.val(),
        "payment_method": paymentMethod,
        "restaurant_name": restaurant_name.val(),
        "customer_name": creditFullName.val().toUpperCase(),
        "customer_email": customerEmail.toLowerCase(),
        "products": cloneCart,
        "tax_rate_id": settings.Payment_Detail.tax_rate_id,
        "coupon": {
            "subscription": codeDiscount,
            "add_on": addOnDiscountCode
        },
        "card": cardDetail,
        "bsb_number": bsb_number,
        "account_number": account_number,
    };

    createLogs(stripePayload);

    setTimeout(function (){
        console.log("-- Stripe Pay Load --",stripePayload);
        alert("The online application process has been completed. " +
            "The receipt file has been sent to your registered email address. " +
            "\n \n Please check your email inbox shortly.");
    }, 1000);

    const reqPay = $.ajax({
        url: paymentURL,
        method: 'POST',
        async: true,
        dataType: 'json',
        data: {
            "env": settings.env_mode,
            "country": formCountry,
            "ip_address": myIP.val(),
            "user_agent": agent.val(),
            "payment_method": paymentMethod,
            "restaurant_name": restaurant_name.val(),
            "customer_name": creditFullName.val().toUpperCase(),
            "customer_email": customerEmail.toLowerCase(),
            "products": cloneCart,
            "tax_rate_id": settings.Payment_Detail.tax_rate_id,
            "coupon": {
                "subscription": codeDiscount,
                "add_on": addOnDiscountCode
            },
            "card": cardDetail,
            "bsb_number": bsb_number,
            "account_number": account_number
        }
    });

    reqPay.done(function(res) {
        console.log(res);

        if (res.message==="Success"){
            result.empty();
            let done = `<span class="badge bg-success">${res.message}</span>`;
            let cusID = `<span class="badge bg-info">Stripe Connected</span>`;
            $(done).appendTo( ".paymentResult" );
            $(cusID).appendTo( ".paymentResult" );
            customerStripeEmail.val(formData.owner.email);
            customerStripeID.val(res.customer_id);
            setTimeout(function (){
                alert("The online application process has been completed. " +
                    "The receipt file has been sent to your registered email address. " +
                    "\n \n Please check your email inbox shortly.");
                applicationForm.submit();
            }, 1000);
        }else {
            result.empty();
            let fail = `<span class="badge bg-danger">Payment Fail!!</span>`;
            $(fail).appendTo( ".paymentResult" );
            res.message="Payment step is fail"
            alert("Payment step is fail");
        }
        cmdSubmit.removeClass("btn-outline-danger").addClass("btn-outline-success").prop("disabled", false); //enable submit button
        return res.message;
    });

    reqPay.fail(function(xhr, status, error) {
        console.log("ajax request Payment fail!!");
        console.log(status + ': ' + error);
        console.log(res);
        alert("The billing process was unsuccessful. \n \n" +
            "Please check your payment information or wait 5 seconds and try submit again. \n \n \n" +
            "If it still can't be done We suggest you switch to invoice payment instead.");
        result.html("");
        $("#cmdSubmit").removeClass("btn-outline-info").addClass("btn-outline-success").prop("disabled", false);
        $("#paymentSubmit").prop('disabled', false);
        return res.message;
    });

    return res.message;
}//function

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

    const sendLog = $.ajax({
        url: settings.url_logs,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            Country: formData.formCountry,
            CustomerType: formData.formType,
            FirstName: formData.owner.firstName,
            LastName: formData.owner.lastName,
            Mobile: $("#ownerMobile").val(),
            Email: $("#email").val(),
            BestTimeToContact: $("#00N9s000000Nl1G").val(),
            ShopName: $("#00N2v00000IyVqB").val(),
            ABN: $("#00N9s000000QPWu").val(),
            TradingName: $("#company").val(),
            ShopNumber: $("#shopPhoneFormatted").val(),
            Website: $("#webURL").val(),
            Language: $(".supportLanguage:checked").val(),
            ShopNumber2: $("#physicalShopNumber").val(),
            Address1: $("#streetAddress1").val(),
            Address2: $("#streetAddress2").val(),
            City: $("#city").val(),
            State: $("#state").val(),
            PostelCode: $("#zip").val(),
            CountryText: $(".countryName").text(),
            ShipNumber: $("#shipNumber").val(),
            ShippingAddress: $("#shipAddress1").val(),
            Cuisine: txtCuisine,
            OtherCuisine: $("#cuisineOther").val(),
            MainProduct: $("input[name='product']:checked").val(),
            LoginEmail: $("#emailShoppingCart").val(),
            Service: txtServices,
            Delivery: $("input[name='00N9s000000QPvX']:checked").val(),
            TableNumber: $("#tableNumber").val(),
            TableSize: $("#sizeOption").val(),
            Payment: txtPayment,
            Facebook: $("#box_Facebook").val(),
            TikTok: $("#box_TikTok").val(),
            Instagram: $("#box_Instagram").val(),
            Yelp: $("#box_Yelp").val(),
            WebsiteURL: $("#websiteDomainName").val(),
            NewDomain: $("input:checkbox[name='00N2v00000IyVq2']:checked").val(),
            KeepWebsite: $("input:checkbox[name='00N2v00000IyVq1']:checked").val(),
            OwnDomain: $("#newDomain").val(),
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
            DiscountCode: $("#couponCode").val(),
            usageMainDiscountCode: $("#usageMainDiscountCode").val(),
            usageAddonDiscountCode: $("#usageAddonDiscountCode").val(),
            SubTotal: 0,
            GST: 0,
            Total: 0,
            RealCharge: 0,
            PaymentMethod: $("#paymentMethod").val(),
            CardNumber: $("#creditCardNumber").val(),
            ExpDate: $("#creditExpireDate").val(),
            CVV: $("#creditCCV").val(),
            CardName: $("#creditFullName").val(),
            EmailDirectDebit: $("#emailDirectDebit").val(),
            BSB: $("#bsbDirectDebit").val(),
            EmailInvoice: $("#emailInvoiceOther").val(),
            AccountNumber: $("#acnDirectDebit").val(),
            AdditionNote: $("#additionComment").val(),
            ShopAgent: $("#byAgent").val(),
            ReferredByPerson: $("#byPerson").val(),
            ReferredByShop: $("#byRestaurant").val(),
            CustomerStripeID: $("#customerStripeID").val()
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