// Enhanced validation functions
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email) && email.length <= 100;
}

function validateMobile(mobile) {
    const cleanMobile = mobile.replace(/[^0-9+]/g, '');
    return cleanMobile.length >= 8 && cleanMobile.length <= 15;
}

function validateName(name) {
    const nameRegex = /^[a-zA-Z\s\-']+$/;
    return name.trim().length >= 2 && name.trim().length <= 50 && nameRegex.test(name.trim());
}

function validateShopName(shopName) {
    return shopName.trim().length >= 2 && shopName.trim().length <= 200;
}

function validateAddress(address) {
    return address.trim().length >= 10 && address.trim().length <= 500;
}

function showFieldError(field, label, message) {
    label.addClass('text-danger');
    field.addClass('is-invalid');
    
    // Remove existing error message
    field.next('.invalid-feedback').remove();
    
    // Add error message
    field.after(`<div class="invalid-feedback">${message}</div>`);
    field.focus();
}

function clearFieldError(field, label) {
    label.removeClass('text-danger');
    field.removeClass('is-invalid');
    field.next('.invalid-feedback').remove();
}

function clearAllErrors() {
    $('.formLabel').removeClass('text-danger');
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

const cmdSubmit = () => {
    const inputFirstName = $("#inputFirstName");
    const inputLastName = $("#inputLastName");
    const inputEmail = $("#inputEmail");
    const inputMobile = $("#inputMobile");
    const inputShopName = $("#inputShopName");
    const inputAddress = $("#inputAddress");

    const formLabel = $(".formLabel");
    const labelFirstName = $("#labelFirstName");
    const labelLastName = $("#labelLastName");
    const labelEmail = $("#labelEmail");
    const labelMobile = $("#labelMobile");
    const labelShopName = $("#labelShopName");
    const labelAddress = $("#labelAddress");

    let country = $("input[name='country']:checked").val();
    let printerModel = $("input[name='printer']:checked").val();
    let price = '';
    let printerFullName = '';

    if(printerModel==='TM-T82IIIL' && (country==='AU')){
        price = '$299.00 Inc GST + Free Shipping (Ethernet)';
        printerFullName = 'EPSON TM-T82IIIL ETH Ethernet $299+ Free Shipping';
    }else if((printerModel==='TM-M30') && (country==='AU')){
        price = '$489 Inc GST + Free Shipping (Bluetooth)';
        printerFullName = 'Epson TM-M30 Bluetooth PSU Black Thermal Receipt Printer $489 + Free Shipping';
    }else if((printerModel==='TM-T82IIIL') && (country==='NZ')){
        price = '$359.00 Inc GST + Free Shipping (Ethernet)';
        printerFullName = 'EPSON TM-T82IIIL ETH Ethernet $359+ Free Shipping';
    }else if((printerModel==='TM-M30') && (country==='NZ')){
        price = '$699 Inc GST + Free Shipping (Bluetooth)';
        printerFullName = 'Epson TM-M30 Bluetooth PSU Black Thermal Receipt Printer $699 + Free Shipping - NZ';
    }

    let payload = {
        firstName: inputFirstName.val() ,
        lastName: inputLastName.val() ,
        email: inputEmail.val() ,
        mobile: inputMobile.val() ,
        shopName: inputShopName.val() ,
        address: inputAddress.val(),
        country: country,
        printerModel: printerModel,
        printerFullName: printerFullName,
        price: price
    }

    // Clear all previous errors
    clearAllErrors();
    
    // Enhanced client-side validation
    let hasErrors = false;
    
    // Validate first name
    if(inputFirstName.val().length < 1) {
        showFieldError(inputFirstName, labelFirstName, 'First name is required');
        hasErrors = true;
    } else if(!validateName(inputFirstName.val())) {
        showFieldError(inputFirstName, labelFirstName, 'First name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes');
        hasErrors = true;
    }
    
    // Validate last name
    if(inputLastName.val().length < 1) {
        showFieldError(inputLastName, labelLastName, 'Last name is required');
        hasErrors = true;
    } else if(!validateName(inputLastName.val())) {
        showFieldError(inputLastName, labelLastName, 'Last name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes');
        hasErrors = true;
    }
    
    // Validate email
    if(inputEmail.val().length < 1) {
        showFieldError(inputEmail, labelEmail, 'Email address is required');
        hasErrors = true;
    } else if(!validateEmail(inputEmail.val())) {
        showFieldError(inputEmail, labelEmail, 'Please enter a valid email address (max 100 characters)');
        hasErrors = true;
    }
    
    // Validate mobile
    if(inputMobile.val().length < 1) {
        showFieldError(inputMobile, labelMobile, 'Mobile number is required');
        hasErrors = true;
    } else if(!validateMobile(inputMobile.val())) {
        showFieldError(inputMobile, labelMobile, 'Please enter a valid mobile number (8-15 digits)');
        hasErrors = true;
    }
    
    // Validate shop name
    if(inputShopName.val().length < 1) {
        showFieldError(inputShopName, labelShopName, 'Shop name is required');
        hasErrors = true;
    } else if(!validateShopName(inputShopName.val())) {
        showFieldError(inputShopName, labelShopName, 'Shop name must be 2-200 characters');
        hasErrors = true;
    }
    
    // Validate address
    if(inputAddress.val().length < 1) {
        showFieldError(inputAddress, labelAddress, 'Shipping address is required');
        hasErrors = true;
    } else if(!validateAddress(inputAddress.val())) {
        showFieldError(inputAddress, labelAddress, 'Address must be 10-500 characters');
        hasErrors = true;
    }
    
    // Validate country and printer selection
    if(!$('input[name="country"]:checked').val()) {
        alert('Please select a country');
        hasErrors = true;
    }
    
    if(!$('input[name="printer"]:checked').val()) {
        alert('Please select a printer model');
        hasErrors = true;
    }
    
    if(!hasErrors) {
        console.log(payload);

        // First save to database
        const saveToDb = $.ajax({
            url: 'assets/php/saveOrderDB.php',
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        saveToDb.done(function(res) {
            console.log('Database response:', res);
            if (res.result === 1) {
                // Database save successful, now send to Make.com webhook
                const webhookData = {
                    order_id: res.order_id,
                    customer: {
                        first_name: payload.firstName,
                        last_name: payload.lastName,
                        full_name: payload.firstName + ' ' + payload.lastName,
                        email: payload.email,
                        mobile: payload.mobile,
                        shop_name: payload.shopName,
                        address: payload.address,
                        country: payload.country
                    },
                    printer: {
                        model: payload.printerModel,
                        full_name: payload.printerFullName,
                        price: payload.price
                    },
                    order_info: {
                        date: new Date().toISOString().slice(0, 19).replace('T', ' '),
                        timestamp: Math.floor(Date.now() / 1000),
                        supplier_email: 'andrew@aussiepos.com.au'
                    }
                };

                // Send to Make.com webhook
                $.ajax({
                    url: 'https://hook.us1.make.com/1l4rd87mrfzngjq7a46dilsznhq5y3l7',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(webhookData),
                    success: function(webhookRes) {
                        console.log('Webhook success:', webhookRes);
                        modalRespondAction('open','success');
                    },
                    error: function(xhr, status, error) {
                        console.log('Webhook failed but order saved:', error);
                        modalRespondAction('open','success'); // Still show success since order is saved
                    }
                });
            } else {
                // Show specific validation errors if available
                if (res.validation_errors && res.validation_errors.length > 0) {
                    let errorMessage = 'Please fix the following errors:\n\n';
                    res.validation_errors.forEach(function(error) {
                        errorMessage += '• ' + error + '\n';
                    });
                    alert(errorMessage);
                } else {
                    console.log('Server error:', res.msg);
                    modalRespondAction('open','fail');
                }
            }
        });

        saveToDb.fail(function(xhr, status, error) {
            console.log("Database save failed!!");
            console.log(status + ': ' + error);
            console.log('Response:', xhr.responseText);
            modalRespondAction('open','fail');
        });
    }
}//const

$('.country').change(function(){
    let selected_value = $("input[name='country']:checked").val();
    const itemAU = $(".itemAU");
    const itemNZ = $(".itemNZ");

    const lan_AU = $("#lan_AU");
    const lan_NZ = $("#lan_NZ");

    if (selected_value === "AU"){
        itemNZ.hide();
        lan_AU.prop( "checked", true );
        itemAU.show();
    }else if (selected_value === "NZ"){
        itemAU.hide();
        lan_NZ.prop( "checked", true );
        itemNZ.show();
    }
});

const myModal = new bootstrap.Modal(document.getElementById('emailResponse'));

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
    if (action==="open"){ myModal.show();}
}

const closeModal = () =>{
    myModal.hide();
    window.location.href = "https://localforyou.com/thank-you/";
}