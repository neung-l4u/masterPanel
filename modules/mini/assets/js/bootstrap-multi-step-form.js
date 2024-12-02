const invoiceID = $("#invoiceID");

/// variable with default value
let step = 1;
let mainProduct = [];
let addonPrintedFlyer = [];
let addonMagnet = [];
let addonFlyerDesign = [];
let addonSocialMediaPost = [];
let allPrice = 0;
let mainProduct2 = [];
let addonProduct2 = [];
let readMainProduct = [];
let readAddonProduct = [];

const modalTerms = new bootstrap.Modal(document.getElementById("modalTerms"), {});
const modalResponse = new bootstrap.Modal(document.getElementById("modalResponse"), {});

let cart = {
  "subscription": [],
  "add_on": []
};
let productsForShow = [];
let addonForShow = [];
let leftNavOpen = true;
let offeringProduct = {
  "type" : "",
  "current" : "",
  "old" : "",
  "new" : "",
  "code" : "",
  "name" : "",
  "promotion" : ""
}; //เอาไว้เก็บว่าลูกค้าเลือกอะไรบ้างในตอนสมัครทีแรก

/// element selector
let classStep = $(".step");
const allStep = classStep.length;
const cmdSubmit = $("#cmdSubmit");
const applicationForm = $("#myForm");
const formQR = $(".formQR");
const formCreditCard = $(".formCreditCard");
const formDebit = $(".formDebit");
const formStripe = $(".formStripe");
const formInvoice = $(".formInvoice");
const methodDebit = $(".methodDebit");
const sectionCuisineSelector = $(".sectionCuisineSelector");
const input_first_name = $("#first_name");
const input_last_name = $("#last_name");
const input_credit_full_name = $("#creditFullName");
const optionDelivery = $(".optionDelivery");
const inputExpireDate = $("#creditExpireDate");
const inputFormType = $('#formType');
const inputFormTypeOption = $('#formType option');
const inputInitialProductOffering = $('#initialProductOffering');
const terms_permission = $("#terms_permission");
//////////////////

/////price calculation
let amount, allGST, total, stripeNum, newAmount, newAllGST, newTotal, newStripeNum;
amount = 0;
allGST = 0;
total = 0;
newAmount = 0;
newAllGST = 0;
newTotal = 0;
newStripeNum = 0;
testMode = true;
///////////

const setCountry = () => {
  let countrySelected = $("input[name='country_code']:checked").val();
  formData.formCountry = countrySelected;
  const inputBusinessNumber = $('#businessNumber');
  const countryValue = $(".countryValue");
  const countryName = $(".countryName");
  const selectState = $(".selectState");
  const currency = $(".currency");
  const inputCurrency = $('input[name="currency"]');
  const textGST = $(".textGST");
  const fakeNumber = $(".fakeNumber");
  const countryTextOnly = $("#countryTextOnly");
  countryValue.val(countrySelected);
  inputBusinessNumber.removeClass("is-invalid");
  resetForm();

  switch (countrySelected) {
    case "AU":
      inputBusinessNumber.attr('required', true);
      countryName.html("Australia");
      currency.html("AUD");
      formData.formCurrency = "AUD";
      inputCurrency.val("AUD");
      textGST.html("GST");
      fakeNumber.html("0408084722");
      countryTextOnly.val("Australia");
      getProductList("AU");
      terms_permission.html('I Give Permission to Manaexito T/as "Local For You" to withdraw monthly payments as agreed from this Credit Card.');
      break;
    case "NZ":
      countryName.html("New Zealand");
      currency.html("NZD");
      formData.formCurrency = "NZD";
      inputCurrency.val("NZD");
      textGST.html("GST");
      fakeNumber.html("31781138");
      countryTextOnly.val("New Zealand");
      getProductList("NZ");
      terms_permission.html('I Give Permission to Manaexito T/as "Local For You" to withdraw monthly payments as agreed from this Credit Card.');
      break;
    case "US":
      countryName.html("United States");
      currency.html("USD");
      formData.formCurrency = "USD";
      inputCurrency.val("USD");
      textGST.html("TAX");
      fakeNumber.html("2025550175");
      countryTextOnly.val("USA");
      terms_permission.html('I Give Permission to Manaexito T/as "Local For You LLC" to withdraw monthly payments as agreed from this Credit Card.');
      getProductList("US");
      break;
    default:
      currency.html("AUD");
      formData.formCurrency = "AUD";
      inputCurrency.val("AUD");
      textGST.html("GST");
      fakeNumber.html("0408084722");
      countryTextOnly.val("Australia");
      terms_permission.html('I Give Permission to Manaexito T/as "Local For You" to withdraw monthly payments as agreed from this Credit Card.');
  }
  setShopType();
}// setCountry()

$(document).ready(function () {
  stepProgress(step);
  $(".firstStepFormLoading").hide();
  $(".firstStepForm").show();
  setCountry();
  ////
});//ready

//on click Next button
$(".next").on("click", function () {
  let nextstep = true;
  if (step === 1){
    addCuisines();
    $("#firstName").focus();
  }

  if (nextstep === true) {
    if (step < classStep.length) {
      classStep.show();
      classStep
          .not(":eq(" + step++ + ")")
          .hide();
      stepProgress(step);
    }
    hideButtons(step);
  }
  jumpTop();
});

// ON CLICK BACK BUTTON
$(".back").on("click", function () {
  if (step > 1) {
    step = step - 2;
    $(".next").trigger("click");
  }
  hideButtons(step);
});

// check accept agreement to allow to submit button
function checkAcceptAgreement() {
  const acceptAgreement = $("#acceptAgreement").is(":checked");
  const acceptTerms = $("#acceptTerms").is(":checked");

  if (acceptAgreement && acceptTerms){
    cmdSubmit.removeClass("btn-outline-danger").addClass("btn-outline-success").prop("disabled", false);
  }else{
    cmdSubmit.removeClass("btn-outline-success").addClass("btn-outline-danger").prop("disabled", true);
  }
}

// ON CLICK Submit BUTTON
cmdSubmit.on("click", async function () {
  let paymentResponse = await requestToPay();
});

// CALCULATE PROGRESS BAR
stepProgress = function (currentStep) {
  let divide = 100 / $(".step").length;
  let percent = divide * (currentStep);
  percent = percent.toFixed();
  $(".progress-bar")
      .css("width", percent + "%")
      .html("Step" + currentStep + "/" + allStep);
};

// DISPLAY AND HIDE "NEXT", "BACK" AND "SUBMIT" BUTTONS
hideButtons = function (step) {
  let limit = parseInt($(".step").length);
  $(".action").hide();
  if (step < limit) {
    $(".next").show();
  }
  if (step > 1) {
    $(".back").show();
  }
  if (step === limit) {
    $(".next").hide();
    $(".submit").show();
  }
};

const copyToFirstOnlineOrderDiscount = (val) => {
  $("#FirstOnlineOrderDiscount").val(val);
}

// auto add slash to Expire date
inputExpireDate.bind('keyup','keydown', function(e){
  if(e.which !== 8) {
    let thisVal = inputExpireDate.val();
    let out = thisVal.replace(/\D/g, '');
    let numChars = out.length;

    if (numChars > 1 && numChars < 5) {
      out = out.substring(0, 2) + '/' + out.substring(2, 4);
    }
    inputExpireDate.val(out);
  }
});


//Set country value to hidden input and label when country change
$('.country_code').change(function() {
  setCountry();
});

//set selected Payment Method
function setMethod(arg) {
  const paymentMethod = $("#paymentMethod");
  paymentMethod.val(arg);
}

//add string @google.com
function addGoogle(name) {
  let box = $("."+name);
  let value = box.val();
  const mainOwnerEmail = $(".mainOwnerEmail");

  if (value.length<=0){
    return true;
  }else if(value.includes('@')){
    return true;
  }else if (/^([A-Za-z0-9_\-\.])+\@([gmail|GMAIL])+\.(com)$/.test(value)) {
    return true;
  }else {
    value = value+"@gmail.com";
    box.val(value);
    formData.owner.email = value;
    if (name==="mainEmail"){
      mainOwnerEmail.html(value);
    }
  }
}

//add clear box
function clearGoogle(name) {
  let box = $("."+name);
  box.val("");
  formData.owner.email = "";
}

const setShopType = () => {
  formData.formType = $("input[name='00N9s000000QRyY']:checked").val();

  getProductList(formData.formCountry);
  let findMassage = formData.formType.search("Massage");
  let tmpType = (findMassage>=0) ? "Massage" : "Restaurant"; //find exactly shop type
}

//Save form type to Master form data
$('.formType').change(function() {
  setShopType();
});

//Generate cuisines checkbox from array
function addCuisines(){
  let cuisinesSelector = $("#cuisinesSelector");
  cuisinesSelector.empty();
  jQuery.each( Cuisines, function( i, val ) {
    if (val!=="Other") {
      cuisinesSelector.append("<li><input class='form-check-input cuisines' onclick='chkCuisine();' type='checkbox' value='" + val + "' name='00N2v00000IyVpy' id='cuisine" + i + "'><label class='form-check-label' for='cuisine" + i + "'>" + val + "</label></li>");
    }else {
      cuisinesSelector.append("<li><input class='form-check-input cuisines cuisinesOther' onclick='chkCuisineOther();' type='checkbox' value='"+val+"' name='00N2v00000IyVpy' id='cuisine"+i+"'><label class='form-check-label' for='cuisine"+i+"'>"+val+"</label></li>");
    }

  });
}

//Generate cuisines checkbox from array
function chkCuisineOther(){
  const cuisines = $(".cuisines");
  const others = $("#others");
  const cuisinesOther = $(".cuisinesOther");
  const cuisinesOther2 = $("#cuisinesOther");

  cuisines.prop( "checked", false );
  cuisinesOther.prop( "checked", true );
  others.prop( "checked", true );
  cuisines.prop( "disabled", true );
  cuisinesOther2.prop( "disabled", false );
  allowOther()

}

//Allow max 3 cuisines checked
function chkCuisine(){
  let maxCuisines = 3;
  if ($('.cuisines:checked').length >= maxCuisines) {
    $(".cuisines").not(":checked").attr("disabled",true);
  }
  else {
    $(".cuisines").not(":checked").removeAttr('disabled');
  }
}

//Show and Hide shipping address
$('#sameShippingAddress').on('click',function(){
  let shippingForm = $(".shippingForm");
  if(this.checked){
    shippingForm.slideUp(500);
  }else{
    shippingForm.slideDown(1000);
  }
});

//Checkbox All for Payment option
function paymentOption(){
  const payAll = $("#payAll");
  const paymentOption = $(".paymentOption");
  if(payAll.is(":checked")){
    paymentOption.prop( "checked", true );
  }
  else if(payAll.is(":not(:checked)")){
    paymentOption.prop( "checked", false );
  }
}

//Checkbox All for Service option
function serviceOption(){
  const serviceAll = $("#serviceAll");
  const serviceOption = $(".serviceOption");
  if(serviceAll.is(":checked")){
    serviceOption.prop( "checked", true );
  }
  else if(serviceOption.is(":not(:checked)")){
    serviceOption.prop( "checked", false );
  }
}

//correct format of input mobile number
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

//set default setShipAddress
function setShipAddress(){
  const inputStreetAddress1 = $("#streetAddress1");
  const inputCity = $("#city");
  const inputZip = $("#zip");
  const shipAddress1 = $("#shipAddress1");

  let streetAddress1 = inputStreetAddress1.val().trim();
  let city = inputCity.val().trim();
  let state = $("#state").find(":selected").text();
  let zip = inputZip.val().trim();
  let country = $("#formCountry").find(":selected").text();

  let shipAddress = [streetAddress1, city, state, zip, country].filter(Boolean).join(" ,");
  shipAddress1.val(shipAddress);
}

const fillFacebook = () => {
  let inputFacebook = $("#facebookAddress");
  if (inputFacebook.val().length <= 0){
    inputFacebook.val("www.facebook.com/");
  }
}

//copy webURL value to Domain name value
function setDomainName(){
  const webURL = $("#webURL").val();
  const websiteDomainName = $("#websiteDomainName");
  websiteDomainName.val(webURL);
}

//Show delivery radio
function showDelivery() {
  const delivery = $("#delivery");
  let selectDelivery = $("#selectDelivery");
  if(delivery.is(":checked")){
    selectDelivery.fadeIn("slow");
  }else if(delivery.is(":not(:checked)")){
    selectDelivery.fadeOut("fast");
    $("#ihdDirectDelivery").val("");
    $("#shopsOwnDriver").val("");
    $("#ownDriver").prop( "checked", false );
    $("#systemDriver").prop( "checked", false );
    $("#IHDLogin").hide();
  }
}

//Show table number input
function showTable() {
  const DineIn = $("#DineIn");
  let tableNumber = $("#tableNumber");
  if (DineIn.is(":checked")) {
    tableNumber.fadeIn("slow");
  } else if (DineIn.is(":not(:checked)")) {
    tableNumber.fadeOut("fast");
  }
}

//price using for show in form only
const addDotToPrice = (price) => {
  return (parseFloat(price)/100).toFixed(2);
}

///add product to cart
function addMainCart(name, price, amount, special, product_id){
  let currentPriceID = "";
  let oldPrice = 0;

  //mirror selecting package to select box
  const inputCurrentlyPackage = $("#currentlyPackage");
  const initPackage = $("#initPackage");
  let currentProduct = name.split(" - ")[0];
  currentProduct = currentProduct.split(" (")[0];
  inputCurrentlyPackage.val(currentProduct);
  ///////////////

  //mirror to init value
  let packageFullName = `${name} - $${price} ${special}/Month`;
  initPackage.val(packageFullName);
  //ทดสอบ gen ชื่อ product
  offeringProduct.current = typeJsonKey(formData.formType) + " - " + currentProduct;
  offeringProduct.name = currentProduct;
  inputInitialProductOffering.val(offeringProduct.current);
  /////


  if(cart.subscription.length>0){
    currentPriceID = cart.subscription[0];
    let index = readMainProduct.map(function(e) { return e.price_id; }).indexOf(currentPriceID);
    if(index>=0){
      oldPrice = readMainProduct[index].amount;
      calPrice("minus", oldPrice);
    }
  }

  cart.subscription = [];
  productsForShow = [];

  let itemName = `${name} <b class="text-primary"> <br> <small>$${price} ${special}/Month</b></small>`;
  productsForShow.push(itemName);
  cart.subscription.push(product_id);

  calPrice("add", amount);
  listProductItems();
}

//function for search text in array and return first match array index
const textSearchInArray = (str, strArray) => {
  for (let j=0; j<strArray.length; j++) {
    if (strArray[j].match(str)) { return j; }
  }
  return -1;
}

//function for search text in array and return first match array index
const getAmountFromAddonList = (str, strArray) => {
  let i =-1;
  let discountPrice = 0;
  let amount = 0;
  //รายการ Add On ที่จะให้ส่วนลด 15%
  const addOnPriceIDGivenDiscount = [
    "price_1NYNzcAVc0AdHeDfRgV8Ob6z",
    "price_1NYO3gAVc0AdHeDfo3BmCdCv",
    "price_1NYO7AAVc0AdHeDf8m1WRjqO",
    "price_1NYOM0AVc0AdHeDfdhQ7rMhG",
    "price_1NYOOqAVc0AdHeDfJc9MwA6q",
    "price_1NYOVfAVc0AdHeDfFLKUYSyd",
    "price_1NYOY6AVc0AdHeDfAMnEOqHb",
    "price_1NYOafAVc0AdHeDfLHcpGNtv",
    "price_1NYO04AVc0AdHeDfYIE9sElv",
    "price_1NYO3wAVc0AdHeDf3KkPBVpV",
    "price_1NYO7VAVc0AdHeDfvsIf0vNj",
    "price_1NYOMGAVc0AdHeDfPCwmUcbo",
    "price_1NYOP5AVc0AdHeDfxX6brAla",
    "price_1NYOVtAVc0AdHeDfouFOlJpm",
    "price_1NYOYHAVc0AdHeDfk9qUKzOx",
    "price_1NYOasAVc0AdHeDfdr12he0O"
  ];
  for (let j=0; j<strArray.length; j++) {
    if (strArray[j]["price_id"].match(str)) {
      i = j;
      break;
    }
  }//for

  amount = readAddonProduct[i]["amount"];

  if (addOnPriceIDGivenDiscount.includes(str)){
    discountPrice = (amount-Math.round((amount*15)/100));
  }else{
    discountPrice = amount;
  }
  return discountPrice;
}

//delete selected addon item
function deleteAddOn(id) {
  let findIndexForShow = textSearchInArray(id,addonForShow);
  let findIndexAddonCart = textSearchInArray(id,cart.add_on);
  let findAmount = 0;
  if(findIndexForShow>-1){
    addonForShow.splice(findIndexForShow, 1);
    if(findIndexAddonCart>-1){
      cart.add_on.splice(findIndexAddonCart, 1);
    }
    findAmount = getAmountFromAddonList(id,readAddonProduct);
    calPrice("minus", findAmount);
    console.log("cal = -"+findAmount);
    listProductItems();
  }else {
    console.log("Not found item in array, nothing to delete!!");
  }
}

///add product to cart
function addAddonCart(name, price, amount, special, product_id, checkID, checkClass){

  let itemName = `${name} <b class="text-primary"> <br> <small>$${price} ${special}</b>
    &nbsp;<i class="fa-solid fa-circle-xmark clickAble text-danger" onclick="deleteAddOn('${product_id}');"></i>
</small>`;

  console.log(checkID);
  console.log(checkClass);

  // Add new item if it's not a duplicate
  let addNewItem = true;
  for (let key in addonForShow) {
    let existingItems = addonForShow[key];
    if (existingItems.includes(itemName)) {
      addNewItem = false;
      break;
    }
  }

  if (addNewItem) {
    // add to array
    addonForShow.push(itemName);
    cart.add_on.push(product_id);
    calPrice("add", amount);
  }else {
    addonForShow = addonForShow.filter(val => val !== itemName);
    calPrice("minus", amount);
    ///////
    // Remove duplicate entry
    let keysToRemove = [];
    let allItem = Object.keys(cart.add_on).length;
    let flagNotFound = true;

    setTimeout(() => {
      for (let key in cart.add_on) {
        if (cart.add_on[key] === product_id) { //ถ้าเจอว่ามีข้อมูลนั้นอยู่แล้ว
          flagNotFound = false;
          if(allItem<=1){ //ถ้าเหลือแค่ตัวเดียว ให้เซ็ตเป็นว่าง
            cart.add_on = []; //เซ็ตเป็นว่าง
          }else{ //ถ้ามีมากกว่า 1 ตัวให้ลบตัวที่เจอ
            let newArr = cart.add_on.filter(function (item) {
              return item !== product_id;
            });
            cart.add_on = newArr;
          }
        }

      }//for
      if(flagNotFound){ // ถ้าไม่เจอ
        cart.add_on.push(product_id); // ให้เพิ่มข้อมูลใหม่ลงไป
      }
    }, 100);
  }//else

  listProductItems();
}

const findNumberOnly = (val) => {
  let nums;
  let myArray = val.split(" ");
  myArray.reverse();
  nums = parseInt(myArray[0].replace(/[{($)}]/g, ''));//read only number
  return nums;
}

//set hidden select delevery value
const setDeliveryOption = (val) => {
  let inputDelivery = $("#delivery");
  let inputIhdDirectDelivery = $("#ihdDirectDelivery");
  let inputShopsOwnDriver = $("#shopsOwnDriver");
  let inputIHDLogin = $("#IHDLogin");

  if (inputDelivery.prop('checked')){
    if (val === "Delivery by own driver"){
      inputShopsOwnDriver.val("Requested");
      inputIhdDirectDelivery.val("");
      inputIHDLogin.fadeOut("fast");
    }else if (val === "Delivery with delivery system"){
      inputShopsOwnDriver.val("");
      inputIhdDirectDelivery.val("Requested");
      inputIHDLogin.fadeIn("fast");
    }else {
      inputShopsOwnDriver.val("");
      inputIhdDirectDelivery.val("");
      inputIHDLogin.fadeOut("fast");
    }
  }else {
    inputShopsOwnDriver.val("");
    inputIhdDirectDelivery.val("");
    inputIHDLogin.fadeOut("fast");
  }
}

///// jump to top of form
function jump(h) {
  let top = document.getElementById(h).offsetTop,
      left = document.getElementById(h).offsetLeft;
  let animator = createAnimator({
    start: [0,0],
    end: [left, top],
    duration: 1000
  }, function(vals){
    console.log(arguments);
    window.scrollTo(vals[0], vals[1]);
  });

  //run
  animator();
}

//set payment request timestamp
const paymentTimestamp = () => {
  let brisbaneDate = new Date().toLocaleString('en-AU', timezoneOptions);
  $("#paymentRequestTimestamp").val(brisbaneDate);
}

//Animator
function createAnimator(config, callback, done) {
  if (typeof config !== "object") throw new TypeError("Argument config expect an Object");

  let start = config.start,
      mid = $.extend({}, start), //clone object
      math = $.extend({}, start), //precalculate the math
      end = config.end,
      duration = config.duration || 1000,
      startTime, endTime;

  function precalculate(a, b, c, d) {
    return [(b - d) / (a - c), (a * d - b * c) / (a - c)];
  }

  function calculate(key, t) {
    return t * math[key][0] + math[key][1];
  }

  function step() {
    let t = Date.now();
    let val = end;
    if (t < endTime) {
      val = mid;
      for (let key in mid) {
        mid[key] = calculate(key, t);
      }
      callback(val);
      requestAnimationFrame(step);
    } else {
      callback(val);
      done && done();
    }
  }

  return function () {
    startTime = Date.now();
    endTime = startTime + duration;

    for (let key in math) {
      math[key] = precalculate(startTime, start[key], endTime, end[key]);
    }

    step();
  }
}

function listProductItems() {
  const mainSelectedPackage = $("#mainSelectedPackage");
  const mainSelectedAddOn = $("#mainSelectedAddOn");
  grandTotal();

  $(".subTotal").val(formatNumber(newAmount/100));
  $(".subTotalText").html(formatNumber(newAmount/100));
  $(".gst").val(newAllGST);
  $(".gstText").html(formatNumber(newAllGST));
  $(".amount").val(formatNumber(newTotal/100));
  $(".amountText").html(formatNumber(newTotal/100));
  $("#firstTimePayment").val(newTotal/100);

  applyCoupon();

  let ele;
/////////
  mainSelectedPackage.empty();

  productsForShow.forEach((item) => {
    $("#selectedPackages").val(removeTags(item));
    ele = `<li class="list-group-item">
                <small><span class="text-secondary">Package</span> : </small>
                <ol><li>${item}</li></ol>
          </li>`;
    $(ele).appendTo( "#mainSelectedPackage" );
  });

/////////
  mainSelectedAddOn.empty();
  if(addonForShow.length>0){
    ele = `<li class="list-group-item">
                <small><span class="text-secondary">Add On</span> : </small>
                <ol>`;
    addonForShow.forEach((item) => {
      ele += `<li>${item}</li>`;
    });
    ele += `</ol></li>`;
    $(ele).appendTo( "#mainSelectedAddOn" );
  }
////////
}

function applyCoupon() {

  const objCode = $("#couponCode");
  let inputCode = objCode.val().trim().toUpperCase();
  objCode.val(inputCode);
  const discountAmount = $("#discountAmount");
  const couponCurrency = $(".couponCurrency");
  const inputReferredSupplier = $("#referredSupplier");
  inputReferredSupplier.val("");

  let discountFlag = false;
  let cal = 0;
  let Settings_Coupon_Obj = settings.Payment_Detail.coupon_Code[formData.formCountry];
  let discountValue = 0;
  let gstDecimal = 0;

  //ถ้าไม่ได้กรอกอะไรมา
  if (inputCode.length<1){
    couponCurrency.hide();
    discountAmount.removeClass("text-danger").html("no coupon apply");
    inputInitialProductOffering.val(offeringProduct.current);
    discountFlag = false;
    $("#couponCode2").removeAttr('disabled');
    return false;
  }else{
    $("#couponCode2").attr('disabled', 'disabled');
  }

  if (typeof Settings_Coupon_Obj[inputCode] !== "undefined") { //check this coupon code is exist in setting list
    discountFlag = true;
    discountValue =  Settings_Coupon_Obj[inputCode].discount; //get discount value of this coupon
    let findSmile = inputCode.search("SMILE");

    if(findSmile===1){
      inputReferredSupplier.val("Smile Dinning");
    }else if(inputCode==="WAWIO"){
      inputReferredSupplier.val("Wawio");
    }else {
      inputReferredSupplier.val("");
    }
  }

  //// select initial product offering
  if(discountFlag){
    let productName = offeringProduct.name;
    if((productName==="Pro Online Ordering System") && (inputCode==="1TRIAL")){
      inputInitialProductOffering.val("Promotions - Pro + $1 Trial");
    }else if((productName==="Social Media Marketing Bundle") && (inputCode==="1TRIAL")){
      inputInitialProductOffering.val("Promotions - Social Bundle + $1 Trial");
    }else if((productName==="Direct Marketing Bundle") && (inputCode==="1TRIAL")){
      inputInitialProductOffering.val("Promotions - Direct Bundle + $1 Trial");
    }else if((productName==="Mega Marketing Bundle") && (inputCode==="1TRIAL")){
      inputInitialProductOffering.val("Promotions - Mega Bundle + $1 Trail");
    }else if((productName==="Social Media Marketing Solo") && (inputCode==="1SMILE")){
      inputInitialProductOffering.val("Promotions - Social + $1 Smile");
    }else if((productName==="Direct Marketing Solo") && (inputCode==="1SMILE")){
      inputInitialProductOffering.val("Promotions - Direct + $1 Smile");
    }else if((productName==="Mega Marketing Solo") && (inputCode==="1TRIAL")){
      inputInitialProductOffering.val("Promotions - Mega + $1 Trail");
    }else if((productName==="Localforyou Booking System") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Booking System + Freeweb");
    }else if((productName==="Social Media Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Social Bundle + Freeweb");
    }else if((productName==="Direct Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Direct Bundle + Freeweb");
    }else if((productName==="Mega Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Mega Bundle + Freeweb");
    }else if((productName==="Social Media Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Social + Freeweb");
    }else if((productName==="Direct Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Direct + Freeweb");
    }else if((productName==="Mega Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Mega + Freeweb");
    }else{
      inputInitialProductOffering.val(offeringProduct.current);
    }
  }
  //


  if (!discountFlag){
    allPrice = (parseFloat(newAmount))/100;
    allGST = parseFloat(newAllGST);
    discountAmount.removeClass("text-success").addClass("text-danger").html("does not match any coupon code");
  }else if(discountFlag){
    allPrice = (parseFloat(newAmount)-discountValue)/100;
    gstDecimal = discountValue/1000;
    allGST = parseFloat(newAllGST)-gstDecimal;
    cal = Math.round(discountValue/100).toFixed(2);
    couponCurrency.show();
    discountAmount.removeClass("text-danger").addClass("text-success").html(cal);
  }

  if(formData.formCountry !== "AU"){
    allGST = 0;
  }

  let calGST = parseFloat(allGST).toFixed(2);
  let showPrice = parseFloat(allPrice).toFixed(2);
  let total = (parseFloat(allPrice)+parseFloat(allGST)).toFixed(2);

  $(".subTotal").val(showPrice);
  $(".subTotalText").html(showPrice);
  $(".gst").val(calGST);
  $(".gstText").html(calGST);
  $(".amount").val(total);
  $(".amountText").html(total);
  $("#firstTimePayment").val(total);
}//function applyCoupon

function applyCoupon2() {

  const objCode = $("#couponCode2");
  let inputCode = objCode.val().trim().toUpperCase();
  objCode.val(inputCode);
  const discountAmount = $("#discountAmount");
  const couponCurrency = $(".couponCurrency");
  const inputReferredSupplier = $("#referredSupplier");
  inputReferredSupplier.val("");

  let discountFlag = false;
  let cal = 0;
  let Settings_Coupon_Obj = settings.Payment_Detail.coupon_Code[formData.formCountry];
  let discountValue = 0;
  let gstDecimal = 0;

  //ถ้าไม่ได้กรอกอะไรมา
  if (inputCode.length<1){
    couponCurrency.hide();
    discountAmount.removeClass("text-danger").html("no coupon apply");
    inputInitialProductOffering.val(offeringProduct.current);
    discountFlag = false;
    $("#couponCode").removeAttr('disabled');
    return false;
  }else{
    $("#couponCode").attr('disabled', 'disabled');
  }

  if (typeof Settings_Coupon_Obj[inputCode] !== "undefined") { //check this coupon code is exist in setting list
    discountFlag = true;
    discountValue =  Settings_Coupon_Obj[inputCode].discount; //get discount value of this coupon
    let findSmile = inputCode.search("SMILE");

    if(findSmile===1){
      inputReferredSupplier.val("Smile Dinning");
    }else if(inputCode==="WAWIO"){
      inputReferredSupplier.val("Wawio");
    }else {
      inputReferredSupplier.val("");
    }
  }

  //// select initial product offering
  if(discountFlag){
    let productName = offeringProduct.name;
    if((productName==="Localforyou Booking System") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Booking System + Freeweb");
    }else if((productName==="Social Media Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Social Bundle + Freeweb");
    }else if((productName==="Direct Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Direct Bundle + Freeweb");
    }else if((productName==="Mega Marketing Bundle") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Mega Bundle + Freeweb");
    }else if((productName==="Social Media Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Social + Freeweb");
    }else if((productName==="Direct Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Direct + Freeweb");
    }else if((productName==="Mega Marketing Solo") && (inputCode==="FREEWEB")){
      inputInitialProductOffering.val("Promotions - Mega + Freeweb");
    }else{
      inputInitialProductOffering.val(offeringProduct.current);
    }
  }
  //


  if (!discountFlag){
    allPrice = (parseFloat(newAmount))/100;
    allGST = parseFloat(newAllGST);
    discountAmount.removeClass("text-success").addClass("text-danger").html("does not match any coupon code");
  }else if(discountFlag){
    allPrice = (parseFloat(newAmount)-discountValue)/100;
    gstDecimal = discountValue/1000;
    allGST = parseFloat(newAllGST)-gstDecimal;
    cal = Math.round(discountValue/100).toFixed(2);
    couponCurrency.show();
    discountAmount.removeClass("text-danger").addClass("text-success").html(cal);
  }

  if(formData.formCountry !== "AU"){
    allGST = 0;
  }

  let calGST = parseFloat(allGST).toFixed(2);
  let showPrice = parseFloat(allPrice).toFixed(2);
  let total = (parseFloat(allPrice)+parseFloat(allGST)).toFixed(2);

  $(".subTotal").val(showPrice);
  $(".subTotalText").html(showPrice);
  $(".gst").val(calGST);
  $(".gstText").html(calGST);
  $(".amount").val(total);
  $(".amountText").html(total);
  $("#firstTimePayment").val(total);
}//function applyCoupon

// new price calculation
//add new value to main Amount
const calPrice = (method, value) => {
  switch (method) {
    case "add":
      amount = parseFloat(value);
      newAmount = parseFloat(value);
      newTotal = parseFloat(value);
      break;
    case "minus":
      amount = value;
      newAmount = parseFloat(value);
      newTotal = parseFloat(value);
      break;
    default:
      amount += 0;
      newAmount += 0;
  }

  newCalGST();
  return amount;
}

//show number format a thousand separate and 2 digits eg 1,668.70
const formatNumber = (value) => {
  let newNum = parseFloat(value).toFixed(2);
  return newNum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

//calculate all current GST and store in "allGST"
const calGST = (value) => {
  let gst = 0;
  if (value > 0) {
    gst = value * (10 / 100);
    allGST = parseFloat(gst).toFixed(2);
  } else {
    allGST = parseFloat(gst);
  }
  return gst;
}

//New calculate all current GST and store in "allGST"
const newCalGST = () => {
  let gst = 0;

  if(formData.formCountry === "AU"){
    if (newAmount > 0) {
      gst = parseFloat(newAmount)/1000;
      newAllGST = formatNumber(gst.toFixed(2));
      newTotal = parseFloat(newAmount)+(parseFloat(newAmount)*0.1);
    }
  }else {
    if (newAmount > 0) {
      gst = 0;
      newAllGST = formatNumber(gst.toFixed(2));
      newTotal = parseFloat(newAmount);
    }
  }
  return gst;
}

//summary price and GST then store in "total"
const grandTotal = () => {
  let sumAll;
  if (formData.formCountry!=="AU"){
    allGST = 0;
  }
  sumAll = parseFloat(amount) + parseFloat(allGST);
  total = sumAll;
}


//read total variable then remove dot and comma then store in "stripeNum" for send to Stripe
const noDot = () => {
  let regex = /[.,\s]/g;
  stripeNum = (total.toFixed(2)).toString().replace(regex, '');
}
////////////////////////

function resetForm(){
  const formControl = $('.form-control');
  formControl.removeClass("is-invalid");
}

//log global form variable
function showLog(){
  console.clear();
  getProductList("AU");
}

/// add ABN, NZBN to Business number
function setBusinessnumber(){
  const businessNumber = $("#00N9s000000QPWu");
  const abnField = $("#00N2v00000IyVpp");

  const myArray = businessNumber.val().split(":");
  myArray.reverse();

  let country = formData.formCountry;
  let newNumber = "";
  if (myArray[0].length>=1) {
    switch (country) {
      case "AU":
        newNumber = "ABN:" + myArray[0];
        abnField.val(myArray[0]);
        break;
      case "NZ":
        newNumber = "NZBN:" + myArray[0];
        break;
      case "US":
        newNumber = myArray[0];
        break;
      case "TH":
        newNumber = "DBD:" + myArray[0];
        break;
      default:
        newNumber = "ABN:" + myArray[0];
        break;
    }
  }
  businessNumber.val(newNumber);
}

/// set emailShoppingCart follow owner email
function setEmailShoppingCart(email){
  const emailShoppingCart = $(".mainEmail");
  const mainOwnerEmail = $(".mainOwnerEmail");
  const receiveEmail = $(".receiveEmail");
  emailShoppingCart.val(email);
  mainOwnerEmail.html(email);
  receiveEmail.html(email);
  let formTypeJsonKey = typeJsonKey(formData.formType); //"Massage" : "Restaurant"
  if (formTypeJsonKey==="Restaurant"){ $("#emailBooking").val(""); }
  if (formTypeJsonKey==="Massage"){ $("#emailShoppingCart").val(""); }
}

//////

/// jump to anchor
function jumpTop(){
  let url = location.href;
  location.href = "#topForm";
  history.replaceState(null,null,url);
}

//remove HTML tag from string
function removeTags(str) {
  if ((str===null) || (str===''))
    return false;
  else
    str = str.toString();
  return str.replace( /(<([^>]+)>)/ig, '');
}

///fill data
function ownerFirstName(val) {
  formData.owner.firstName = val;
  setCreditFullName();
}

function ownerLastName(val) {
  formData.owner.lastName = val;
  setCreditFullName();
}

function ownerEmail(val) {
  formData.owner.email = val;
  $(".customerStripeAccount").val(val);
}

function ownerBestTime(val) {
  formData.owner.bestTime = val;
}

function setRestaurantName(val) {
  $('input[name="company"]').val(val);
  formData.business.shopName = val;
  formData.business.company = val;
}

const setCreditFullName = () =>{
  let text = input_first_name.val()+" "+input_last_name.val();
  input_credit_full_name.val(text.trim());
}

//Toggle left sidebar
const toggleLeftNav = () => {
  leftNavOpen = !leftNavOpen;
  const toolElements = $(".toolElements");

  if (leftNavOpen){
    toolElements.fadeOut(100);
    setTimeout(() => {
      document.getElementById("mySidebar").style.width = "0";
    }, 100);
  }else if(!leftNavOpen){
    document.getElementById("mySidebar").style.width = "250px";
    setTimeout(() => {
      toolElements.fadeIn(500);
    }, 200);
  }
  return leftNavOpen;
}

//force to 10 digits
function fixDigitDirectDebit() {
  const inputAcnDirectDebit = $("#acnDirectDebit");
  let newValue = "";
  let currentValue = inputAcnDirectDebit.val().trim();
  newValue = currentValue.padStart(10, '0');
  inputAcnDirectDebit.val(newValue);
  return true;
}

//fixNumber to be decimal
function fixNumber(val) {
  let num = parseInt(val,10);
  let boxTable = $("#tableNumber");
  boxTable.val("");
  boxTable.val(num);
  initDineInTableOrdering.val(num);
}

//set Initial Product Offering Value
function setInitialProductOffering() {

  let product = $("input[name='product']:checked").val();
  alert(product);
}

//synchronize note in left sidebar and not in main form
const syncComment = (val) => {
  $("#additionComment").val(val);
  $("#stickyComment").val(val);
  return true;
}

// Hide Payment method
if(!settings.Payment_Module.CreditCard) { formCreditCard.hide(); }
if(!settings.Payment_Module.DirectDebit) { formDebit.hide(); }
if(!settings.Payment_Module.Stripe) { formStripe.hide(); }
if(!settings.Payment_Module.QR) { formQR.hide(); }
if(!settings.Payment_Module.Invoice) { formInvoice.hide(); }

//open url in new tab
function readAgreement() {
  //event.preventDefault();
  let inputAccept = $("#acceptTerms");
  inputAccept.prop("disabled", false);
  modalTermsAction("open");
}

const modalTermsAction = (action) => {
  if (action==="open"){ modalTerms.show();}
}

const modalRespondAction = (action,status) => {
  const respondSuccess = $(".respondSuccess");
  const respondFail = $(".respondFail");
  respondSuccess.hide();
  respondFail.hide();
  if (status==="success"){
    respondSuccess.show();
  }else if (status==="fail"){
    respondFail.show();
  }
  if (action==="open"){ modalResponse.show();}
}