let _crmCountdownTimer = null;

const enableCRMButton = () => {
    const isChecked = $("#enableCRM").is(':checked');
    const CRMButton = $("#CRMButton");
    const ballLoading = $("#ballLoading");
    const countdownText = $("#countdownText");

    // Always clear any existing timer first
    if (_crmCountdownTimer !== null) {
        clearInterval(_crmCountdownTimer);
        _crmCountdownTimer = null;
    }

    if (isChecked) {
        // กำลังเปิดการใช้งาน CRM
        CRMButton.hide().prop('disabled', true);
        ballLoading.show();
        countdownText.show();

        let countdown = 6;
        let showText = progressText(countdown);
        countdownText.text(`${showText} ... ${countdown} sec.`);

        _crmCountdownTimer = setInterval(() => {
            countdown--;
            showText = progressText(countdown);
            countdownText.text(`${showText} ... ${countdown} sec.`);
            if (countdown <= 0) {
                clearInterval(_crmCountdownTimer);
                _crmCountdownTimer = null;
                ballLoading.hide();
                countdownText.hide();
                CRMButton.prop('disabled', false).fadeIn("slow");
            }
        }, 1000);
    } else {
        // ปิดการใช้งาน CRM
        ballLoading.hide();
        countdownText.hide();
        CRMButton.hide().prop('disabled', true);
    }
};

function progressText(sec) {
    let txt = '';

    if(sec >= 5){ txt = 'Connect to Stripe ...'; }
    else if(sec >= 4){ txt = 'Create log file ...'; }
    else if(sec >= 3){ txt = 'Save to DB ...'; }
    else if(sec <=2 ){ txt = 'Sending Email ...'; }

    return txt;
}

// const enableCRMButton = () => {
//     const enableCRM = $("#enableCRM").is(':checked');;
//     const CRMButton = $("#CRMButton");
//
//
//     if (enableCRM){
//         CRMButton.prop('disabled', false);
//     }else{
//         CRMButton.prop('disabled', true);
//     }
// }