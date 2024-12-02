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
        price = '$685 Inc GST + Free Shipping (Bluetooth)';
        printerFullName = 'Epson TM-M30 Bluetooth PSU Black Thermal Receipt Printer $685 + Free Shipping - NZ';
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

    formLabel.removeClass('text-danger');
    if(inputFirstName.val().length<1){
        formLabel.removeClass('text-danger');
        labelFirstName.addClass('text-danger');
        inputFirstName.focus();
    }else if(inputLastName.val().length<1){
        formLabel.removeClass('text-danger');
        labelLastName.addClass('text-danger');
        inputLastName.focus();
    }else if(inputEmail.val().length<1){
        formLabel.removeClass('text-danger');
        labelEmail.addClass('text-danger');
        inputEmail.focus();
    }else if(inputMobile.val().length<1){
        formLabel.removeClass('text-danger');
        labelMobile.addClass('text-danger');
        inputMobile.focus();
    }else if(inputShopName.val().length<1){
        formLabel.removeClass('text-danger');
        labelShopName.addClass('text-danger');
        inputShopName.focus();
    }else if(inputAddress.val().length<1){
        labelAddress.addClass('text-danger');
        formLabel.removeClass('text-danger');
        inputAddress.focus();
    }else {
        console.log(payload);

        const reqMail = $.ajax({
            url: 'assets/php/sendPrinterMail.php',
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        reqMail.done(function(res) {
            console.log(res);
            if (res.result===1){
                modalRespondAction('open','success');
            }else{
                modalRespondAction('open','fail');
            }
            return true;
        });

        reqMail.fail(function(xhr, status, error) {
            console.log("ajax Send Mail fail!!");
            console.log(status + ': ' + error);
            modalRespondAction('open','fail');
            return false;
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
    window.location.replace("https://www.localforyou.com");
}