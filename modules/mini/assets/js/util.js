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