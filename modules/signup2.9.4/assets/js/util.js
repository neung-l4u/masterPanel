//element selector
const inputPickup = document.querySelector("#pickup");
const inputTableReservation = document.querySelector("#tableReservation");
const inputDineIn = document.querySelector("#DineIn");
const inputTableNumber = document.querySelector("#tableNumber");
const inputDelivery = document.querySelector("#delivery");
const inputOwnDriver = document.querySelector("#ownDriver");
const inputSystemDriver = document.querySelector("#systemDriver");

const chkInitPickup = document.querySelector("#chkInitPickup");
const chkInitTableReservations = document.querySelector("#chkInitTableReservations");
const chkInitDineIn = document.querySelector("#chkInitDineIn");
const chkInitOwnerDriver = document.querySelector("#chkInitOwnerDriver");
const chkInitSystemDriver = document.querySelector("#chkInitSystemDriver");
const groupInitDriver = $(".initDriver");

const initPackage = $("#initPackage");
const initAddOnPrintedFlyers = $("#initAddOnPrintedFlyers");
const initAddOnFridgeMagnet = $("#initAddOnFridgeMagnet");
const initDineInTableOrdering = $("#initDineInTableOrdering");
const initIHDDirectDelivery = $("#initIHDDirectDelivery");
const initShopsOwnDriver = $("#initShopsOwnDriver");

if (typeof(inputPickup) != 'undefined' && inputPickup != null)
{
    inputPickup.addEventListener( "change", () => {
        chkInitPickup.checked = inputPickup.checked;
    });
}

if (typeof(inputTableReservation) != 'undefined' && inputTableReservation != null) {
    inputTableReservation.addEventListener("change", () => {
        chkInitTableReservations.checked = inputTableReservation.checked;
    });
}

if (typeof(inputDineIn) != 'undefined' && inputDineIn != null) {
    inputDineIn.addEventListener("change", () => {
        chkInitDineIn.checked = inputDineIn.checked;
    });
}

if (typeof(inputTableNumber) != 'undefined' && inputTableNumber != null) {
    inputTableNumber.addEventListener("change", () => {
        chkInitPickup.checked = inputTableNumber.checked;
    });
}

if (typeof(inputDelivery) != 'undefined' && inputDelivery != null) {
    inputDelivery.addEventListener("change", () => {
        if (!inputDelivery.checked) {
            groupInitDriver.prop("checked", false);
        }
        initIHDDirectDelivery.val("");
        initShopsOwnDriver.val("");
    });
}

if (typeof(inputOwnDriver) != 'undefined' && inputOwnDriver != null) {
    inputOwnDriver.addEventListener("change", () => {
        groupInitDriver.prop("checked", false);
        chkInitOwnerDriver.checked = !!inputOwnDriver.checked;
        initIHDDirectDelivery.val("");
        initShopsOwnDriver.val("Requested");
    });
}

if (typeof(inputSystemDriver) != 'undefined' && inputSystemDriver != null) {
    inputSystemDriver.addEventListener("change", () => {
        groupInitDriver.prop("checked", false);
        chkInitSystemDriver.checked = !!inputSystemDriver.checked;
        initShopsOwnDriver.val("");
        initIHDDirectDelivery.val("Requested");
    });
}

///

const showSocials = settings.socials.filter( (member) => {
    return member.show === true;
});

const checksSocial = showSocials.map( (social) => {
    let ranID = Math.random();
    return `<div class="d-flex flex-row align-items-center mb-3">
                <input
                  class="form-check-input checksSocial me-2"
                  type="checkbox"
                  value=""
                  name=""
                  id="chk-${social.name}"
                  onclick="allowSocial('box${social.name}');"
                />
                <label class="form-check-label socialsLabel me-2" for="chk-${social.name}">${social.name}</label>
                <div class="input-group boxSocial" id="box${social.name}">
                  <div class="input-group-prepend">
                    <span class="input-group-text">${social.icon} #</span>
                  </div>
                  <input 
                    type="text" 
                    class="form-control" 
                    placeholder="${social.name}"
                    name="${social.boxName}"
                    id="box_${social.name}"
                    maxlength="255" 
                    autocomplete="off"
                    >
                </div>
          </div>`;
});

checksSocial.map((item) => {
    document.getElementById("socialInputs").innerHTML += item;
    return true;
});

const allowSocial = (id) => {
    let boxTikTok = $("#boxTikTok");
    let boxInstagram = $("#boxInstagram");
    let boxFacebook = $("#boxFacebook");
    let boxYelp = $("#boxYelp");
    if (id==="boxTikTok"){
        $("#chk-TikTok").is(":checked") ? boxTikTok.show() : boxTikTok.hide();
    }else if (id==="boxFacebook"){
        $("#chk-Facebook").is(":checked") ? boxFacebook.show() : boxFacebook.hide();
    }else if (id==="boxYelp"){
        $("#chk-Yelp").is(":checked") ? boxYelp.show() : boxYelp.hide();
    }else if (id==="boxInstagram"){
        boxInstagram.show();
        $("#chk-Instagram").is(":checked") ? boxInstagram.show() : boxInstagram.hide();
    }
}

const capitalize = (str) => {
    // Split the string into an array of words
    let words = str.split(' ');

    // Capitalize the first letter of each word
    for (let i = 0; i < words.length; i++) {
        words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1).toLowerCase();
    }

    // Join the words back into a single string
    return words.join(' ');
}