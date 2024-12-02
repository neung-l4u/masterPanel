const typeJsonKey = (txt) => {
    return txt === "Thai Massage" ? "Massage" : "Restaurant";
}

/// ไว้ใช้คอยเลือก option web hosting auto แล้วเอาราคาไปใส่ตะกร้าให้ด้วย
const setHost = () => {
    let chk = $(".optionWebHosting");
    chk.prop("checked", !chk.prop("checked"));
    if (formData.formCountry === "AU") {
        addAddonCart('Website Hosting + Email included', '39.00', '3900', '', 'price_1NZazAAVc0AdHeDfzAyPdNxQ', 'addon-price_1NZazAAVc0AdHeDfzAyPdNxQ', '');
    }else if (formData.formCountry === "NZ") {
        addAddonCart('Website Hosting + Email included', '39.00', '3900', '', 'price_1NZazAAVc0AdHeDfwzpINo8r', 'addon-price_1NZazAAVc0AdHeDfwzpINo8r', '');
    }else if (formData.formCountry === "US") {
        addAddonCart('Website Hosting + Email included', '39.00', '3900', '', 'price_1NYKjII6vmxJT6OmcZkzHs9i', 'addon-price_1NYKjII6vmxJT6OmcZkzHs9i', '');
    }
}
////////////////////
function getProductList(country) {
    // ถ้ายังไม่เลือกประเทศหรือประเภทลูกค้า ให้ยังกดไปหน้าถัดไปไม่ได้

    let price_id = undefined;
    let product_id = undefined;
    let formType = formData.formType;
    let formCountry = formData.formCountry;
    const productsDescription = $("#productsDescription");
    let formTypeJsonKey = typeJsonKey(formType);
    const loadingAjax = $("#loadingAjax");
    loadingAjax.html("<img alt='Loading' src='assets/img/loading.gif'>");
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
                "2": "prod_NLcWZ1JSU2xrzY"
            }
        }
    });
    /////

    reqProductList.done(function(res) {
        let jsonData = res['data'][formCountry];
        let jsonAddons = jsonData[formTypeJsonKey]['Addons'];
        let jsonAll = jsonData['All'];

        let jsonMainProduct = jsonData[formTypeJsonKey]['Products'].concat(jsonAll['Products']);
        let jsonAddonsSubscriptions = jsonAddons['Subscriptions'].concat(jsonAll['Addons']['Subscriptions']);
        let jsonAddonsOnetime = jsonAddons['Onetime'].concat(jsonAll['Addons']['Onetime']);
        let jsonAddonsMaterials = jsonAddons['Materials'].concat(jsonAll['Addons']['Materials']);
        let jsonAddonsOthers = jsonAddons['Others'].concat(jsonAll['Addons']['Others']);
        let checkIsOptionWebHosting = "";
        let bundleHeader = false;

        readMainProduct = jsonMainProduct;

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
                name = `${item.name}`;
                price = addDotToPrice(item.amount);
                amount = item.amount;
                let product_id = item.price_id;
                let ext = item.ext;
                let br = "";
                lap++;
                addMainCart(name,price,amount,special,product_id);
                let trialPromotion = "";
                if (formTypeJsonKey==="Restaurant"){
                    applyCoupon();
                    trialPromotion = "";
                }else {
                    $("#couponCode").val("");
                    applyCoupon();
                }

                if (formTypeJsonKey==="Restaurant") {
                    return `${br}<div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="product" 
                            id="product${ran}" 
                            value="${name} - $${price} ${special}${ext}"
                            checked
                            onclick="addMainCart('${name}', '${price}', '${amount}', '${special}', '${product_id}');"
                        >
                        <label class="form-check-label" for="product${ran}" >
                            ${name} <b class="text-danger"> - <del>$${price}</b> <b class="text-success"></del> $1 Trial</b>
                        </label>
                    </div>`;
                }else{
                    return `${br}<div class="form-check">
                        <input 
                            class="form-check-input" 
                            type="radio" 
                            name="product" 
                            id="product${ran}" 
                            value="${name} - $${price} ${special}${ext}"
                            checked
                            onclick="addMainCart('${name}', '${price}', '${amount}', '${special}', '${product_id}');"
                        >
                        <label class="form-check-label" for="product${ran}" >
                            ${name} <b class="text-primary"> - $${price} ${special}${ext}</b>
                        </label>
                    </div>`;
                }
            });//return



            productRadio2.map((item) => {
                $(item).appendTo( "#products2" );
            });
        }else{
            let textMainProduct = `<small class="text-danger">Sorry we don't have any Package in this currency yet !!</small>`;
            $("#products2").html(textMainProduct);
        }

        ///

        productsDescription.html(settings.Products_Description[formCountry][formTypeJsonKey]);

        loadingAjax.html('<i class="fa-solid fa-check text-primary"></i>');
        $(".next").removeClass("btn-outline-danger").addClass("btn-outline-secondary").prop("disabled", false);
    });

    reqProductList.fail(function(xhr, status, error) {
        //console.log("ajax get ProductList fail!!");
        loadingAjax.html('<i class="fa-solid fa-xmark text-danger"></i><button class="btn text-primary" type="button" onClick="getProductList();"><i class="fa-solid fa-rotate text-primary"></i></i></button>');
        $(".next").removeClass("btn-outline-secondary").addClass("btn-outline-danger").prop("disabled", true);
        //console.log(status + ': ' + error);
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
        cloneCart.subscription = cloneCart.subscription.reduce(function(acc, cur, i) { //convert array to object
            acc[i] = cur;
            return acc;
        }, {});
        cloneCart.add_on = cloneCart.add_on.reduce(function(acc, cur, i) { //convert array to object
            acc[i] = cur;
            return acc;
        }, {});
    }

    /////

    let textDiscount = couponCode.val().trim().toUpperCase();
    let Payment_Coupon_Obj = settings.Payment_Detail.coupon_Code[formCountry];
    let textDiscount2 = couponCode2.val().trim().toUpperCase();
    let Payment_Coupon_Obj2 = settings.Payment_Detail.coupon_Code[formCountry];
    let codeDiscount = {};

    if (typeof Payment_Coupon_Obj[textDiscount] !== "undefined") { //check this coupon code is exist in setting list
        let pid = "";
            pid = cloneCart["subscription"][0]; //read main product ID
        let cid = "";
            cid = Payment_Coupon_Obj[textDiscount].code; //read stripe coupon code
        codeDiscount = { [pid] : cid } ; //set main coupon code
    }else{
        let pid = "";
        pid = cloneCart["subscription"][0]; //read main product ID
        let cid = "";
        codeDiscount = { [pid] : cid } ; //set main coupon code
    }

    //add on discount not for USA
    //let addOnDiscountCode = "suhgy7Fb";
    let addOnDiscountCode = {};
    let materialDiscountCode = (formCountry==="US") ? "" : "suhgy7Fb";
    let freewebDiscountCode = Payment_Coupon_Obj["FREEWEB"]["code"];

    // console.log("***** textDiscount2 = "+textDiscount2+" *****")
    let applyAddonCode = "";
    if (textDiscount2 === "FREEWEB"){
        applyAddonCode = freewebDiscountCode;
    }else{
        applyAddonCode = materialDiscountCode;
    }

    // console.log("Material code = ",materialDiscountCode);
    // console.log("FreeWeb code = ",freewebDiscountCode);

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
    //console.log("codeDiscount = ",codeDiscount);
    //console.log("addOnDiscountCode = ",addOnDiscountCode);

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
        "account_number": account_number
    };

    createLogs(stripePayload);

    setTimeout(function (){
        console.log("-- Stripe Pay Load --",stripePayload);
        //console.log("form submitted");
    }, 1000);

    /*let ans = confirm('Do you really want to submit the form?');
    if (ans){ applicationForm.submit(); }*/

    const reqPay = $.ajax({
        url: paymentURL,
        method: 'POST',
        async: true,
        contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify(stripePayload)
    });

    reqPay.done(function(res) {
        //console.log(res);

        if (res.message==="Success"){
            result.empty();
            let done = `<span class="badge bg-success">${res.message}</span>`;
            let cusID = `<span class="badge bg-info">Stripe Connected</span>`;
            $(done).appendTo( ".paymentResult" );
            $(cusID).appendTo( ".paymentResult" );
            customerStripeEmail.val(formData.owner.email);
            if (formCountry==="US"){
                customerStripeIDUSA.val(res.customer_id);
            }else {
                customerStripeID.val(res.customer_id);
            }
            setTimeout(function (){
                modalRespondAction("open","success");

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
        //console.log("ajax request Payment fail!!");
        //console.log(status + ': ' + error);
        //console.log(res);

        modalRespondAction("open","fail");

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

    let domainUser = $("#ref_Domain_U");
    let domainPass = $("#ref_Domain_P");
    let domainComment = $("#ref_Domain_Comments");
    let domainRegister = $("#ref_Domain_Name_Registered");

    let tempData = {
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
        domainUser: domainUser.val(),
        domainPass: domainPass.val(),
        domainComment: domainComment.val(),
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
        EmailDirectDebit: $("#emailDirectDebit").val(),
        BSB: $("#bsbDirectDebit").val(),
        EmailInvoice: $("#emailInvoiceOther").val(),
        AccountNumber: $("#acnDirectDebit").val(),
        AdditionNote: $("#additionComment").val(),
        ShopAgent: $("#byAgent").val(),
        ReferredByPerson: $("#byPerson").val(),
        ReferredByShop: $("#byRestaurant").val(),
        CustomerStripeID: $("#customerStripeID").val()
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
        //console.log(res);
        return true;
    });

    sendLog.fail(function(xhr, status, error) {
        //console.log("ajax Log file fail!!");
        //console.log(status + ': ' + error);
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
    //console.log(cancelFrm);

    sendMail();
}