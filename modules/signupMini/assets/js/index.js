
function setMoney() {
    let country = $("#country").val();
    let currencyBox = $("#currency");

    if (country === "Australia"){
        currencyBox.val("AUD");
    }else if (country === "Canada"){
        currencyBox.val("CAD");
    }else if (country === "New Zealand"){
        currencyBox.val("NZD");
    }else if(country === "United Kingdom"){
        currencyBox.val("GBP");
    }else if(country === "USA"){
        currencyBox.val("USD");
    }else if(country === "Thailand"){
        currencyBox.val("THB");
    }
}//setMoney

function shortCountry() {
    let country = $("#country").val();
    if (country === "Australia"){
        return "AU";
    }else if (country === "Canada"){
        return "CA";
    }else if (country === "New Zealand"){
        return "NZ";
    }else if(country === "United Kingdom"){
        return "GB";
    }else if(country === "United States"){
        return "US";
    }else if(country === "Thailand"){
        return "TH";
    }
}

function shopTypeForLeadManagement() {
    let shopType = $("#shopType").val();
    if (shopType === "Thai Restaurants &amp; Takeaways"){
        return "Thai Restaurant";
    }else if (shopType === "Thai Massage"){
        return "Thai Massage";
    }else if (shopType === "Restaurants &amp; Takeaways"){
        return "Restaurant";
    }
}

function getPayload(form) {
    return {
        first_name: form.find("[name='first_name']").val(),
        last_name: form.find("[name='last_name']").val(),
        email: form.find("[name='email']").val(),
        mobile: form.find("[name='mobile']").val(),
        contactTime: form.find("[name='contactTime']").val(),
        company: form.find("[name='company']").val(),
        shopName: form.find("[name='shopName']").val(),
        country: form.find("[name='country']").val(),
        countryCode: shortCountry(),
        shopType: form.find("[name='shopType']").val(),
        shopTypeForLeadManagement: shopTypeForLeadManagement(),
        url: form.find("[name='url']").val(),
        city: form.find("[name='city']").val(),
        currency: form.find("[name='currency']").val(),
        interest: form.find("[name='interest']").val(),
        comments: form.find("[name='comments']").val(),
        SignupFormVersion: form.find("[name='SignupFormVersion']").val(),
        formType: form.find("[name='formType']").val(),
        leadSource: form.find("[name='leadSource']").val(),
        leadRecordType: form.find("[name='leadRecordType']").val()
    };
}

function sendPayload(payload) {
    console.log("🚀 Sending Payload:", payload);
    $.ajax({
        url: "https://hook.us1.make.com/47ue45ij7fhm7sol8rldp6dxpag2ldjl",
        method: "POST",
        dataType: "json",
        data: payload,
        success: function (response) {
            console.log("✅ Webhook Success:", response);
            if (response.result === "Leads to Monday successfully") {
                $("#successMessage").show();
                setTimeout(() => {
                    window.location.href = "https://localforyou.com/thank-you/";
                }, 1500);
            }
        },
        error: function (xhr, status, error) {
            console.error("❌ Webhook Failed:", status, error);
        }
    });
}

function validateForm(form) {
    let isValid = true;
    form.find(".text-danger").remove(); // Clear previous errors

    const firstName = form.find('[name="first_name"]');
    const email     = form.find('[name="email"]');
    const mobile    = form.find('[name="mobile"]');
    const company   = form.find('[name="company"]');
    const country   = form.find('[name="country"]');

    function showError(input, message) {
        const error = $('<small class="text-danger d-block mt-1"></small>').text(message);
        input.after(error);
        isValid = false;
    }

    if (!firstName.val().trim()) showError(firstName, "First name is required.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.val())) showError(email, "Invalid email.");
    if (!/^\+?[0-9\s\-]{8,15}$/.test(mobile.val())) showError(mobile, "Invalid mobile number.");
    if (!company.val()) showError(company, "Please select a business type.");
    if (!country.val()) showError(country, "Please select a country.");

    return isValid;
}
