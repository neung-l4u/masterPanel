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
    setPeriodSelectBox(contractPeriod);//0 = No contract ,3 = 3 months = ,12 = 12 months
    const loadingAjax = $("#loadingAjax"); 
    const loadGif = "<img alt='Loading' src='assets/img/loading.gif'>";
    loadingAjax.html(loadGif);//เอา gif ไปโหลด
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
    });//เลือดหาไฟล์ Json Product ,env = prod ,country = AU ,US ,NZ ,UK ,TH ,period = 0 ,3 ,12
    /////

    reqProductList.done(function(res) {
        let jsonData = res['data'][formCountry];//data ของ country นั้นๆ
        let jsonAddons = jsonData[formTypeJsonKey]['Addons'];//เอา json data มาเคย addon แล้วเช็คว่าร้านนวดหรือร้านอาหาร
        let jsonAll = jsonData['All'];//เอาสินค้าที่อยู่ใน All
        let jsonSetupFee = jsonData['All']['Special']['SetupFee'];// เอาสินค้าที่อยู่ใน All Special SetupFee

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

        let jsonMainProduct = jsonData[formTypeJsonKey]['Products'][contractPeriodKeyIndex]['items'].concat(jsonAll['Products'][0]['items']);//เอาข้อมูล Product ตาม contract ที่เลือกรวมกัน
        let jsonAddonsSubscriptions = jsonAddons['Subscriptions'][0]['items'].concat(jsonAll['Addons']['Subscriptions'][0]['items']);//เอาข้อมูล Addons ที่เป็น Subscriptions ตาม contract ที่เลือกรวมกัน
        let jsonAddonsOnetime = jsonAddons['Onetime'][0]['items'].concat(jsonAll['Addons']['Onetime'][0]['items']);//เอาข้อมูล Addons ที่เป็น one time ตาม contract ที่เลือกรวมกัน
        let jsonAddonsMaterials = jsonAddons['Materials'][0]['items'].concat(jsonAll['Addons']['Materials'][0]['items']);//เอาข้อมูล Materials ที่เป็น one time ตาม contract ที่เลือกรวมกัน
        let jsonAddonsOthers = jsonAddons['Others'][0]['items'].concat(jsonAll['Addons']['Others'][0]['items']);//เอาข้อมูล Addons Other ตาม contract ที่เลือกรวมกัน
        let checkIsOptionWebHosting = "";
        let bundleHeader = false;

        readMainProduct = jsonMainProduct;
        readAddonProduct = jsonAddonsMaterials.concat(jsonAddonsSubscriptions).concat(jsonAddonsOnetime).concat(jsonAddonsOthers);//เอา addon[sub,onetime,other] มารวมกัน

        let brBundle = {
            "type" : "",
            "status" : false
        }

        let headText = "<div class='text-warning mt-4'>Bundle</div>";//หัวข้อ Bundle
        if(readMainProduct.length > 0){//ถ้าเลือก product
            $("#products2").empty();//เคลียร์เนื้อหาใน id products2
            let productRadio2 = readMainProduct.map((item) => {
                let ran = Math.random();//สุ่มเลข
                let name = "";
                let special = (item.gst)?" + GST ":"";
                let price = 0;
                let currency = "";
                name = `${item.name}`;//ชื่อสินค้า
                price = addDotToPrice(item.amount);//ทศนิยม 2 จุด
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
                }//ตั้งค่าสัญลักษณ์เงินให้ตรงกับสกุล (เช่น THB → ฿, GBP → £, อื่น ๆ → $)

                lap++;
                if(brBundle.type!==item.type){
                    br = (brBundle.status)?"<div class='text-warning mt-2'>Solo</div>":"";
                    brBundle.type = item.type;
                    brBundle.status = true;
                }//ถ้าสินค้าใหม่มี type ต่างจากสินค้าเดิมจะเพิ่มหัวข้อ “Solo” ก่อนหน้า เพื่อแยกหมวด เช่น “Bundle” กับ “Solo”

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
            });//return สร้างค่าใน input กับ label

            productRadio2.map((item) => {
                if(!bundleHeader) {
                    $("<div class='text-warning mt-2'>Bundle</div>").appendTo("#products2");
                    bundleHeader = true;
                }
                $(item).appendTo( "#products2" );
            });//หลังจากได้ array ของ HTML แล้ว (productRadio2)จะเพิ่มหัวข้อ "Bundle" ก่อนรายการแรก จากนั้น append แต่ละ item ลงใน #products2
        }else{
            let textMainProduct = `<small class="text-danger">Sorry we don't have any Package in this currency yet !!</small>`;
            $("#products2").html(textMainProduct);
        }//ถ้าไม่มีสินค้าเลย

        if(jsonSetupFee.length > 0){
            $("#setUpFeeList").empty();
            let allSetupFeeIds = jsonSetupFee.map(item => item.price_id);
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
                            onclick="updateSetupFee('${name}', '${price}', '${amount}', '${special}', '${product_id}', [${allSetupFeeIds.map(id => `'${id}'`).join(',')}]);"
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

                /*const boxName = {
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
                }*/

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
                        name="${item.form_name}"
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
                        name="${item.form_name}"
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
                        name="${item.form_name}"
                        value="${name} - ${formData.formCurrency.charAt(0)}฿ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} ${currencySign}${price} ${special}</b>
                        ${discountText}
                    </label>
                </div>`;
                }else if(formCountry==="AU"){
                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType} ${checkIsOptionWebHosting}" 
                        type="checkbox" 
                        name="${item.form_name}"
                        id="addon-${product_id}" 
                        value="${name} - ${formData.formCurrency.charAt(0)}฿ ${price} ${special}"
                        onclick="addAddonCart('${name}', '${realPrice}', '${cartPrice}', '${special}', '${product_id}', 'addon-${product_id}', '${classType}');"
                    >
                    <label class="form-check-label" for="addon-${product_id}" >
                        ${name} <b class="text-primary"> -${leadDiscountText} ${currencySign}${price} ${special}</b>
                        ${discountText}
                    </label>
                </div>`;
                }else if(formCountry==="NZ"){
                    return `${addText}<div class="form-check">
                    <input 
                        class="form-check-input ${classType} ${checkIsOptionWebHosting}" 
                        type="checkbox" 
                        name="${item.form_name}"
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

/*const sendMail = () => {

    let sendMailPayload = {
        "mode" : "confirm",
        "shopName" : $("#shopName").val(),
        "fullName" : formData.owner.firstName+" "+formData.owner.lastName,
        "acceptAutoPilot" : $("input[name=acknowledgeAI]:checked").val(),
        "email" : $("#email").val()
    }

    const ajaxMailToCustomer = $.ajax({
        url: "https://hook.us1.make.com/2nm9tihm27otcavx7ftvafpmjlmasigo",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: sendMailPayload
    });

    ajaxMailToCustomer.done(function(res) {
        console.log(res);
        return true;
    });

    ajaxMailToCustomer.fail(function(xhr, status, error) {
        console.log("ajax Send Mail to Customer fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}*/
//sendMail

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
    }

    let shopAgent = $("#byAgent").val();
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    let cuisineSelected = [];

    $("input:checkbox[name='cuisinesOther']:checked").each(function(){
        cuisineSelected.push($(this).val());
    });
    let txtCuisine = cuisineSelected.join();

    let checkProduct = $("input[name='product']:checked").val();
    let toTeam = "";

    if(checkProduct.includes("Bundle")){
        toTeam = "All";
    }else if(checkProduct.includes("Solo") || checkProduct.includes("Yelp")){
        toTeam = "AM";
    }else if(checkProduct.includes("System")){
        toTeam = "CS";
    }else{
        toTeam = "All";
    }

    console.log(toTeam);


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
        formSalesAgent: shopAgent,
        formContractPeriod: $("#ContractPeriod").val(),
        formRefPerson: $("#byPerson").val(),
        formRefPartner: $("#byPartner").val(),
        formCoupon: $("#couponCode").val(),
        formRefShop: $("#byRestaurant").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formPaymentMethod: $("#paymentMethod").val(),

        toTeam: toTeam,

        addonFlyer: $("input:checkbox[name='addonFlyers']:checked").val(),
        addonFridgeMagnet: $("input:checkbox[name='addonFridgeMagnet']:checked").val(),
        addonDigitalMenu: $("input:checkbox[name='addonPricingDesign']:checked").val(),
        addonDineInDual: $("input:checkbox[name='addonDineInDual']:checked").val(),
        addonAdvPromo: $("input:checkbox[name='addonAdvPromo']:checked").val(),
        addonMobApp: $("input:checkbox[name='addonMobApp']:checked").val(),
        addonWebsiteHosting: $("input:checkbox[name='addonWebsiteHosting']:checked").val(),
        addonSocialMedia: $("input:checkbox[name='addonSocialMedia']:checked").val(),
        addonWebsiteMakeoverTemplate: $("input:checkbox[name='addWebsiteMakeoverTemplate']:checked").val(),
        addonWebsiteMakeoverFully: $("input:checkbox[name='addWebsiteMakeoverFully']:checked").val(),
        addonInfluencer: $("input:checkbox[name='addonInfluencer']:checked").val(),
        addonGoogleReview: $("input:checkbox[name='addonGoogleReview']:checked").val(),
        addonPOS: $("input:checkbox[name='addonPOS']:checked").val(),
        addonYelpAdSpend: $("input:checkbox[name='addonYelpAdSpend']:checked").val(),
        addonSocialMediaSetup: $("input:checkbox[name='addonSocialMediaSetup']:checked").val(),



        formCustomerType: $("#formType option:selected").text(),
        formShopName: $("#shopName").val(),
        formCountry: $("#formCountry option:selected").text(),
        ShippingAddress: $("#shipAddress1").val(),
        formFullName: $("#first_name").val().trim() + " " + $("#last_name").val().trim(),
        formEmail: $("#email").val().toLowerCase(),
        formMobile: $("#ownerMobile").val(),
        formBestTime: $("#bestTimeContact").val(),
        formNote: $("#additionComment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val(),
        formPOSUsing: $("#posSystem").val(),
        formPOSUsingOther: $("#posOtherDate").val(),
        formNoPOSProvider: $("input[id='noPOSProvider']:checked").val(),
        formYesPOSProvider: $("#endDatePOS").val(),
        acceptAutoPilotAI: $("input[name='acknowledgeAI']:checked").val(),


        //NEW//
        formShopNumber: $("#businessNumber").val(),
        formTradingName: $("#company").val(),
        formShopPhoneNumber: $("#shopPhoneFormatted").val(),
        formShopWebsite: $("#webURL").val(),
        formOwnerFirstLanguage: $("input[name='supportLanguage']:checked").val(),


        ///Cuisine///
        cuisinesOther: txtCuisine,
        formCuisineOther: $("#cuisinesOther").val(),
        formSetupFee: $("input[name='setup']:checked").val(),


        //Booking System//
        formLoginEmailBookingSystem: $("#emailBooking").val(),
        formPasswordBookingSystem: $("#passwordBooking").val(),

        //Online Ordering System//
        formLoginEmailOnlineOrderingSystem: $("#emailShoppingCart").val(),
        formPasswordOnlineOrderingSystem: $("#passwordShoppingCart").val(),

        //Services//
        formPinkUp: $("input[id='pickup']:checked").val(),
        formTableReservation: $("input[id='tableReservation']:checked").val(),

        formDineInTableOrdering: $("input[id='DineIn']:checked").val(),
        dineInTable: $("#tableNumber").val(),
        dineInSize: $("#sizeOption").val(),

        delivery: $("input[id='delivery']:checked").val(),
        deliveryYourOwn: $("input[id='ownDriver']:checked").val(),
        deliverySystemDriver: $("input[id='systemDriver']:checked").val(),
        ihdEmail: $("#ref_IHD_Email").val(),
        ihdPw: $("#ref_IHD_Password").val(),
        ihdToken: $("#ref_IHD_Token").val(),

        //Payment Options//
        cash: $("input[id='cash']:checked").val(),
        cardCounter: $("input[id='cardCounter']:checked").val(),
        callBack: $("input[id='callBack']:checked").val(),
        payOnline: $("input[id='payOnline']:checked").val(),

        //Social Networks//
        facebook: $("#box_Facebook").val(),
        tiktok: $("#box_TikTok").val(),
        instagram: $("#box_Instagram").val(),
        yelp: $("#box_Yelp").val(),

        //Domain Name//
        ///Old///
        websiteDomainName: $("#websiteDomainName").val(),
        keepWebsite: $("input[id='keepWebsite']:checked").val(),
        ownDomain: $("input[id='ownDomain']:checked").val(),

        ///New///
        websiteNewDomain: $("#newDomain").val(),

        ///Domain Name Login info///
        loginInfoU: $("#ref_Domain_U").val(),
        loginInfoP: $("#ref_Domain_P").val(),
        loginInfoComments: $("#ref_Domain_Comments").val(),
        loginInfoRegistered: $("#ref_Domain_Name_Registered").val(),

        ///1st Order Discount///
        firstOrderDiscount: $("input:checkbox[name='discount']:checked").val(),
        firstOrderDiscountOther: $("input[id='othersDiscount']:checked").val(),
        firstOrderDiscountOtherValue: $("#discountOther").val(),

        ///POS Check Box///
        posCheck: $("#posCheck").val(),
        renovationTakePOS: $("#renovationTakePOS").val(),
        necessaryPermitsPOS: $("#necessaryPermitsPOS").val(),

        //END NEW//
        testMail: CheckedBoxTestmailValue,
        token: Math.random()
    };



    // TODO: Send Email To Staff
    const ajaxSendL4UMail = $.ajax({
        url: "https://hook.us1.make.com/7r536tvdcr50jd77tvw5vo41yk61kygx",
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: payload
    });

    ajaxSendL4UMail.done(function(res) {
        console.log(res);
        return true;
    });

    ajaxSendL4UMail.fail(function(xhr, status, error) {
        console.log("ajax Send L4U Mail alert fail!!");
        console.log(status + ': ' + error);
        return false;
    });

}//sendMail

/**
 * คำนวณราคาก่อน VAT จากราคาที่รวม VAT แล้ว (สำหรับ VAT 7%)
 * @param {number} priceInclVAT - ราคาที่รวม VAT 7% แล้ว
 * @returns {number} - ราคาที่ไม่รวม VAT (ทศนิยม 2 ตำแหน่ง)
 */
function getPriceBeforeVAT(priceInclVAT) {
    const gstMultiply = 0.07; // VAT 7%
    if (priceInclVAT === 0 || isNaN(priceInclVAT)) {
        return 0;
    }
    // คำนวณราคาก่อน VAT จากราคาที่รวม VAT
    let priceBeforeVAT = priceInclVAT / (1 + gstMultiply);
    return parseFloat(priceBeforeVAT.toFixed(2));
}
function calculateVAT(price) {
    // 1. ราคาก่อน VAT
    let a = price;

    // 2. คำนวณ VAT 7%
    let b = parseFloat((a * 0.07).toFixed(2));

    // 3. ราคาหลังรวม VAT
    let c = parseFloat((a - b).toFixed(2));

    return { a, b, c };
}

/*
const saveTaxToDB = (stripePayload, stripeRes) => {
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
    let invoiceID = "VCDSG290-0002";
    let selectedInputId = $("input[name='product']:checked").attr("id")
    let product = $("label[for='" + selectedInputId + "']").text().trim().replace(/\n/g, '');
    let setupFee = $("input[name='setup']:checked").val();
    let addonSelect = $("input[name='addonSocialMediaSetup']:checked").val();
    let shopAgent = $("#byAgent").val();
    let taxWithheld = (grandTotal * 0.015).toFixed(2)
    let finalPrice = parseFloat((grandTotal - taxWithheld).toFixed(2));
    let finalPriceFormatted = finalPrice.toFixed(2);
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    ////product/////
    // แยกด้วย " - "
    let partsPro = product.split(" - ");
    // ชื่อสินค้า
    let nameProduct = partsPro[0];  // "Social Media Marketing Solo"
    // ราคา
    let priceProduct = partsPro[1]; // "฿4900.00 /Month"
    // ถ้าต้องการเอาแค่ตัวเลข
    let priceOnly = priceProduct.match(/[\d,.]+/)[0]; // "4900.00"

    let priceProductReal = parseFloat((priceOnly - taxWithheld).toFixed(2))

    let productResult = calculateVAT(priceProductReal);

    ////product/////

    ////setupFee/////
    // แยกด้วย " - "
    let partsSetupFee = setupFee.split(" - ");
    // ชื่อสินค้า
    let nameSetup = partsSetupFee[0];  // "Setup Fee (No Contract)"
    // ราคา
    let priceSetup = partsSetupFee[1]; // "฿0.00 /Month"
    // ถ้าต้องการเอาแค่ตัวเลข
    let priceSetupOnly = priceSetup.match(/[\d,.]+/)[0]; // "0.00"

    let setupFeeResult = calculateVAT(priceSetupOnly);
    ////setupFee/////

    ////addon////
    // แยกด้วย " - "
    let partsAddon = addonSelect.split(" - T฿ ");
    // ชื่อสินค้า
    let nameAddon = partsAddon[0]; // "Social Media Set Up"
    // ราคา
    let priceAddon = partsAddon[1]; // "1999.00"
    // ถ้าต้องการเอาแค่ตัวเลข
    let priceAddonOnly = priceAddon.match(/[\d,.]+/)[0];   // "1999.00"

    let addonResult = calculateVAT(priceAddonOnly);

    ////addon/////

    let today = new Date();

// ดึงวัน เดือน ปี
    let day = String(today.getDate()).padStart(2, '0');
    let month = String(today.getMonth() + 1).padStart(2, '0'); // เดือนเริ่มจาก 0
    let year = today.getFullYear();

// รวมเป็นฟอร์แมต dd/mm/yyyy
    let currentDate = `${day}/${month}/${year}`;

    if(taxType === "นิติบุคคล"){
        realNameQuotation = shopNameQuotation;
    }else{
        realNameQuotation = shopNameQuotation + " โดย " + nameQuotation;
    }

    let subtotalReal = parseFloat((productResult.c + setupFeeResult.c + addonResult.c).toFixed(2));


    let productQuotation = {
        "table": [
            { "product": nameProduct, "qyt": 1, "amount": productResult.c},
            { "setupfee": nameSetup, "qyt": 1, "amount": setupFeeResult.c},
            { "addon": nameAddon, "qyt": 1, "amount": addonResult.c}
        ],
        "summary": {
            "subtotal": subtotalReal,
            "tax": tax,
            "grandtotal": subTotal
        }
    };

    payload = {
        "checkBoxWantTAX" : checkBoxWantTAX,
        "taxType" : taxType,
        "nameQuotation" : nameQuotation,
        "shopNameQuotation" : shopNameQuotation,
        "phoneQuotation" : phoneQuotation,
        "emailQuotation" : emailQuotation,
        "addressQuotation" : addressQuotation,
        "taxNumberQuotation" : taxNumberQuotation,
        "subTotal" : subtotalReal,
        "tax" : tax,
        "grandTotal" : subTotal,
        "realNameQuotation" : realNameQuotation,
        "invoiceID" : invoiceID,
        "productQuotation" : productQuotation,
        "shopAgent" : shopAgent,
        "date" : currentDate
    }


    console.log(payload);
    if (taxType === "นิติบุคคล") {
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : checkBoxWantTAX,
                "taxType" : taxType,
                "nameQuotation" : shopNameQuotation,
                "phoneQuotation" : phoneQuotation,
                "emailQuotation" : emailQuotation,
                "addressQuotation" : addressQuotation,
                "taxNumberQuotation" : taxNumberQuotation,
                "productQuotation" : productQuotation,
                "finalPrice" : subTotal,
                "shopAgent" : shopAgent,
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }else if(taxType === "บุคคลธรรมดา"){
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : checkBoxWantTAX,
                "taxType" : taxType,
                "nameQuotation" : realNameQuotation,
                "phoneQuotation" : phoneQuotation,
                "emailQuotation" : emailQuotation,
                "addressQuotation" : addressQuotation,
                "taxNumberQuotation" : taxNumberQuotation,
                "productQuotation" : productQuotation,
                "finalPrice" : subTotal,
                "shopAgent" : shopAgent,
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }else{
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : "no",
                "taxType" : "ไม่ต้องการ",
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }


}
*/

const saveTaxToDB = (stripePayload, stripeRes) => {
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
    let invoiceID = "VCDSG290-0002";
    let selectedInputId = $("input[name='product']:checked").attr("id")
    let product = $("label[for='" + selectedInputId + "']").text().trim().replace(/\n/g, '');
    let setupFee = $("input[name='setup']:checked").val();
    let addonSelect = $("input[name='addonSocialMediaSetup']:checked").val();
    let shopAgent = $("#byAgent").val();
    let taxWithheld = (grandTotal * 0.015).toFixed(2)
    let finalPrice = parseFloat((grandTotal - taxWithheld).toFixed(2));
    let finalPriceFormatted = finalPrice.toFixed(2);
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    ////product/////
    // แยกด้วย " - "
    let partsPro = product.split(" - ");
    let nameProduct = partsPro[0];
    let priceProduct = partsPro[1] || "฿0.00";
    let priceOnly = parseFloat(priceProduct.match(/[\d,.]+/)[0].replace(/,/g, ''));
    let productAmountBeforeVAT = getPriceBeforeVAT(priceOnly);

    ////product/////

    ////setupFee/////
    let nameSetup = "Setup Fee (No Contract)";
    let setupFeeAmountBeforeVAT = 0;
    if (setupFee && setupFee.trim() !== "") {
        let partsSetupFee = setupFee.split(" - ");
        nameSetup = partsSetupFee[0];
        let priceSetup = partsSetupFee[1] || "฿0.00";
        let priceSetupOnly = parseFloat(priceSetup.match(/[\d,.]+/)[0].replace(/,/g, ''));
        setupFeeAmountBeforeVAT = getPriceBeforeVAT(priceSetupOnly);
    }
    ////setupFee/////

    ////addon////
    let nameAddon = "No Addon";
    let addonAmountBeforeVAT = 0;
    if (addonSelect && addonSelect.trim() !== "") {
        let partsAddon = addonSelect.split(" - T฿ ");
        nameAddon = partsAddon[0];
        let priceAddon = partsAddon[1] || "0.00";
        let priceAddonOnly = parseFloat(priceAddon.match(/[\d,.]+/)[0].replace(/,/g, ''));
        addonAmountBeforeVAT = getPriceBeforeVAT(priceAddonOnly);
    }
    ////addon/////

    let today = new Date();

// ดึงวัน เดือน ปี
    let day = String(today.getDate()).padStart(2, '0');
    let month = String(today.getMonth() + 1).padStart(2, '0'); // เดือนเริ่มจาก 0
    let year = today.getFullYear();

// รวมเป็นฟอร์แมต dd/mm/yyyy
    let currentDate = `${day}/${month}/${year}`;

    if(taxType === "นิติบุคคล"){
        realNameQuotation = shopNameQuotation;
    }else{
        realNameQuotation = shopNameQuotation + " โดย " + nameQuotation;
    }


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

    payload = {
        "checkBoxWantTAX" : checkBoxWantTAX,
        "taxType" : taxType,
        "nameQuotation" : nameQuotation,
        "shopNameQuotation" : shopNameQuotation,
        "phoneQuotation" : phoneQuotation,
        "emailQuotation" : emailQuotation,
        "addressQuotation" : addressQuotation,
        "taxNumberQuotation" : taxNumberQuotation,
        "subTotal" : subTotal,
        "tax" : tax,
        "grandTotal" : grandTotal,
        "realNameQuotation" : realNameQuotation,
        "invoiceID" : invoiceID,
        "productQuotation" : productQuotation,
        "shopAgent" : shopAgent,
        "date" : currentDate
    }


    console.log(payload);
    if (taxType === "นิติบุคคล") {
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : checkBoxWantTAX,
                "taxType" : taxType,
                "nameQuotation" : shopNameQuotation,
                "phoneQuotation" : phoneQuotation,
                "emailQuotation" : emailQuotation,
                "addressQuotation" : addressQuotation,
                "taxNumberQuotation" : taxNumberQuotation,
                "productQuotation" : productQuotation,
                "finalPrice" : subTotal,
                "shopAgent" : shopAgent,
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }else if(taxType === "บุคคลธรรมดา"){
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : checkBoxWantTAX,
                "taxType" : taxType,
                "nameQuotation" : realNameQuotation,
                "phoneQuotation" : phoneQuotation,
                "emailQuotation" : emailQuotation,
                "addressQuotation" : addressQuotation,
                "taxNumberQuotation" : taxNumberQuotation,
                "productQuotation" : productQuotation,
                "finalPrice" : subTotal,
                "shopAgent" : shopAgent,
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }else{
        const ajaxSaveQuotationToDB = $.ajax({
            url: settings.url_saveQuestionToDB,
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {
                "act": "add",
                "checkBoxWantTAX" : "no",
                "taxType" : "ไม่ต้องการ",
                "date" : currentDate
            }
        });

        ajaxSaveQuotationToDB.done(function(res) {
            console.log(res);
            $("#quotationID").val(res.quotationID);
            return true;
        });

        ajaxSaveQuotationToDB.fail(function(xhr, status, error) {
            console.log("Save to DB fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }


}


const invoiceIDToDB = (stripeRes) => {
    let insertIDToInvoice = $("#quotationID").val()

    const ajaxInvoiceIDToDB = $.ajax({
        url: settings.url_saveQuestionToDB,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "act": "update",
            "quotationID": insertIDToInvoice,
            "invoiceID": stripeRes
        }
    });

    ajaxInvoiceIDToDB.done(function(res) {
        console.log(res);
        callWebhookInvoice()
        return true;
    });

    ajaxInvoiceIDToDB.fail(function(xhr, status, error) {
        console.log("Save Stripe Response to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}


const callWebhookInvoice = (stripeRes) => {
    let webhook = "https://hook.us1.make.com/ilpkidd9ve4cflfxoym5fka8fwdozhxt";
    let idinvoice = $("#quotationID").val()


    const callWebhookInvoice = $.ajax({
        url: webhook,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "act": "callWebhookInvoice",
            "id": idinvoice
        }
    });

    callWebhookInvoice.done(function(res) {
        console.log(res);
        return true;
    });

    callWebhookInvoice.fail(function(xhr, status, error) {
        console.log("Save Stripe Response to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}

// TODO : Build Logs File to DB by Mark
const saveToDB = (stripePayload, stripeRes) => {
    genLinkPDF();
    const agreementGenerated = $("#agreementGenerated");
    let contractURL = agreementGenerated.val();

    let cuisineSelected = [];

    $("input:checkbox[name='cuisinesOther']:checked").each(function(){
        cuisineSelected.push($(this).val());
    });
    let txtCuisine = cuisineSelected.join();

    let yourArray = [];
    $("input:checkbox[name='serviceCheck']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtServices = yourArray.join();

    yourArray = [];
    $("input:checkbox[name='payCheck']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtPayment = yourArray.join();

    let domainUser = $("#ref_Domain_U");
    let domainPass = $("#ref_Domain_P");
    let domainComment = $("#ref_Domain_Comments");
    let domainRegister = $("#ref_Domain_Name_Registered");
    let Country = formData.formCountry;

    let shopAgent = $("#byAgent").val();
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    let CheckedBoxTestmailValue = $("#CheckedBoxTestmail").is(':checked') ? $("#CheckedBoxTestmail").val() : 0;

    let payload = {
        Country: formData.formCountry,
        CustomerType: formData.formType,
        FirstName: formData.owner.firstName.trim(),
        LastName: formData.owner.lastName.trim(),
        Mobile: $("#ownerMobile").val(),
        Email: $("#email").val().trim().toLowerCase(),
        BestTimeToContact: $("#bestTimeContact").val(),
        ShopName: $("#shopName").val().trim(),
        ABN: $("#businessNumber").val(),
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
        Delivery: $("input[name='serviceCheck']:checked").val(),
        TableNumber: $("#tableNumber").val(),
        TableSize: $("#sizeOption").val(),
        Payment: txtPayment,
        Facebook: $("#box_Facebook").val().trim(),
        TikTok: $("#box_TikTok").val().trim(),
        Instagram: $("#box_Instagram").val().trim(),
        Yelp: $("#box_Yelp").val().trim(),
        WebsiteURL: $("#websiteDomainName").val().trim(),
        NewDomain: $("input:checkbox[name='keepWebsite']:checked").val(),
        KeepWebsite: $("input:checkbox[name='ownDomain']:checked").val(),
        OwnDomain: $("#newDomain").val().trim(),
        domainUser: domainUser.val().trim(),
        domainPass: domainPass.val().trim(),
        domainComment: domainComment.val().trim(),
        domainRegister: domainRegister.val(),
        Flyer: $("input:checkbox[name='addonFlyers']:checked").val(),
        FridgeMagnet: $("input:checkbox[name='addonFridgeMagnet']:checked").val(),
        AddOn1: $("input:checkbox[name='addonAdvPromo']:checked").val(),
        AddOn2: $("input:checkbox[name='addonSocialMedia']:checked").val(),
        AddOn3: $("input:checkbox[name='addonInfluencer']:checked").val(),
        AddOn4: $("input:checkbox[name='addonDineInDual']:checked").val(),
        AddOn5: $("input:checkbox[name='addonPricingDesign']:checked").val(),
        AddOn6: $("input:checkbox[name='addonWebsiteMakeover']:checked").val(),
        AddOn7: $("input:checkbox[name='addonWebsiteHosting']:checked").val(),
        AddOn8: $("input:checkbox[name='addonMobApp']:checked").val(),
        AddOn9: $("input:checkbox[name='addonGoogleReview']:checked").val(),
        AddOn10: $("input:checkbox[name='addonPOS']:checked").val(),
        AddOn11: $("input:checkbox[name='addonYelpAdSpend']:checked").val(),
        AddOn12: $("input:checkbox[name='addonSocialMediaSetup']:checked").val(),
        OrderDiscount: $("input[name='discount']:checked").val(),
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
        ShopAgent: shopAgent,
        ReferredByPerson: $("#byPerson").val().trim(),
        formRefPartner: $("#byPartner").val(),
        ReferredByShop: $("#byRestaurant").val().trim(),
        CustomerStripeID: $("#customerStripeID").val(),
        formProduct: $("#currentlyPackage option:selected").text(),
        formInitialProductOffering: $("#initialProductOffering").val(),
        formSalesAgent: shopAgent,
        formContractPeriod: $("#ContractPeriod").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val().trim(),
        formPOSUsing: $("#posSystem").val(),
        formPOSUsingOther: $("#posOtherDate").val(),
        formNoPOSProvider: $("input[id='noPOSProvider']:checked").val(),
        formYesPOSProvider: $("#endDatePOS").val(),
    };

    // TODO : Save To Database
    const ajaxSaveToDB = $.ajax({
        url: settings.url_saveToDB,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "act": "add",
            "stripePayload" : stripePayload,
            "payload" : payload,
            "country" : Country,
            "contractURL" : contractURL,
            "testMail" : CheckedBoxTestmailValue,
        }
    });

    ajaxSaveToDB.done(function(res) {
        console.log(res);
        $("#logID").val(res.logID);
        return true;
    });

    ajaxSaveToDB.fail(function(xhr, status, error) {
        console.log("Save to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}

const stripeResToDB = (stripeRes) => {
    let logID = $("#logID").val();
    const ajaxStripeResToDB = $.ajax({
        url: settings.url_saveToDB,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "act": "update",
            "logID": logID,
            "stripeRes": stripeRes
        }
    });

    ajaxStripeResToDB.done(function(res) {
        console.log(res);
        return true;
    });

    ajaxStripeResToDB.fail(function(xhr, status, error) {
        console.log("Save Stripe Response to DB fail!!");
        console.log(status + ': ' + error);
        return false;
    });
}

const createLogs = (stripePayload) => {
    let cuisineSelected = [];

    $("input:checkbox[name='cuisinesOther']:checked").each(function(){
        cuisineSelected.push($(this).val());
    });
    let txtCuisine = cuisineSelected.join();

    let yourArray = [];
    $("input:checkbox[name='serviceCheck']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtServices = yourArray.join();

    yourArray = [];
    $("input:checkbox[name='payCheck']:checked").each(function(){
        yourArray.push($(this).val());
    });
    let txtPayment = yourArray.join();

    let domainUser = $("#ref_Domain_U");
    let domainPass = $("#ref_Domain_P");
    let domainComment = $("#ref_Domain_Comments");
    let domainRegister = $("#ref_Domain_Name_Registered");

    let shopAgent = $("#byAgent").val();
    if (shopAgent === "Other") {
        shopAgent = $("#otherAgent").val();
    }

    let tempData = {
        Country: formData.formCountry,
        CustomerType: formData.formType,
        FirstName: formData.owner.firstName.trim(),
        LastName: formData.owner.lastName.trim(),
        Mobile: $("#ownerMobile").val(),
        Email: $("#email").val().trim().toLowerCase(),
        BestTimeToContact: $("#bestTimeContact").val(),
        ShopName: $("#shopName").val().trim(),
        ABN: $("#businessNumber").val(),
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
        Delivery: $("input[name='serviceCheck']:checked").val(),
        TableNumber: $("#tableNumber").val(),
        TableSize: $("#sizeOption").val(),
        Payment: txtPayment,
        Facebook: $("#box_Facebook").val().trim(),
        TikTok: $("#box_TikTok").val().trim(),
        Instagram: $("#box_Instagram").val().trim(),
        Yelp: $("#box_Yelp").val().trim(),
        WebsiteURL: $("#websiteDomainName").val().trim(),
        NewDomain: $("input:checkbox[name='keepWebsite']:checked").val(),
        KeepWebsite: $("input:checkbox[name='ownDomain']:checked").val(),
        OwnDomain: $("#newDomain").val().trim(),
        domainUser: domainUser.val().trim(),
        domainPass: domainPass.val().trim(),
        domainComment: domainComment.val().trim(),
        domainRegister: domainRegister.val(),
        Flyer: $("input:checkbox[name='addonFlyers']:checked").val(),
        FridgeMagnet: $("input:checkbox[name='addonFridgeMagnet']:checked").val(),
        AddOn1: $("input:checkbox[name='addonAdvPromo']:checked").val(),
        AddOn2: $("input:checkbox[name='addonSocialMedia']:checked").val(),
        AddOn3: $("input:checkbox[name='addonInfluencer']:checked").val(),
        AddOn4: $("input:checkbox[name='addonDineInDual']:checked").val(),
        AddOn5: $("input:checkbox[name='addonPricingDesign']:checked").val(),
        AddOn6: $("input:checkbox[name='addonWebsiteMakeover']:checked").val(),
        AddOn7: $("input:checkbox[name='addonWebsiteHosting']:checked").val(),
        AddOn8: $("input:checkbox[name='addonMobApp']:checked").val(),
        AddOn9: $("input:checkbox[name='addonGoogleReview']:checked").val(),
        AddOn10: $("input:checkbox[name='addonPOS']:checked").val(),
        AddOn11: $("input:checkbox[name='addonYelpAdSpend']:checked").val(),
        AddOn12: $("input:checkbox[name='addonSocialMediaSetup']:checked").val(),
        OrderDiscount: $("input[name='discount']:checked").val(),
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
        ShopAgent: shopAgent,
        ReferredByPerson: $("#byPerson").val().trim(),
        formRefPartner: $("#byPartner").val(),
        ReferredByShop: $("#byRestaurant").val().trim(),
        CustomerStripeID: $("#customerStripeID").val(),
        formProduct: $("#currentlyPackage option:selected").text(),
        formInitialProductOffering: $("#initialProductOffering").val(),
        formSalesAgent: shopAgent,
        formContractPeriod: $("#ContractPeriod").val(),
        formFirstTimePayment: $("#firstTimePayment").val(),
        formstartProjectAs: $("input[id='startProjectAs']:checked").val(),
        formstartProjectOther: $("#dateproject").val(),
        formstartprojectNote: $("#startprojectNote").val().trim(),
        formPOSUsing: $("#posSystem").val(),
        formPOSUsingOther: $("#posOtherDate").val(),
        formNoPOSProvider: $("input[id='noPOSProvider']:checked").val(),
        formYesPOSProvider: $("#endDatePOS").val()
    };
// TODO : Build Logs File
    const ajaxSendLog = $.ajax({
        url: settings.url_logs,
        method: 'POST',
        async: false,
        cache: false,
        dataType: 'json',
        data: {
            "allData" : tempData
        }
    });

    ajaxSendLog.done(function(res) {
        console.log(res);
        return true;
    });

    ajaxSendLog.fail(function(xhr, status, error) {
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

/*const readForm = () => {
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
}*/

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

$('#formCountry').on('change', function () {
    const selectedCountryValue = $(this).val();
    if (selectedCountryValue === "TH") {
        $('#policyNoThai').hide();
    }else{
        $('#policyNoThai').show();
    }
})


