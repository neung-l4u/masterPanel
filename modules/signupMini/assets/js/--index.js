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
    }else if(country === "United States"){
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

function validateForm(form) {
    let isValid = true;
    form.find(".text-danger").remove(); // Clear previous errors

    const firstName = form.find('[name="first_name"]');
    const email     = form.find('[name="email"]');
    const mobile    = form.find('[name="mobile"]');
    const shopType   = form.find('[name="shopType"]');
    const country   = form.find('[name="country"]');

    function showError(input, message) {
        const error = $('<small class="text-danger d-block mt-1"></small>').text(message);
        input.after(error);
        isValid = false;
    }

    if (!firstName.val().trim()) showError(firstName, "First name is required.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.val())) showError(email, "Invalid email.");
    if (!/^\+?[0-9\s\-]{8,15}$/.test(mobile.val())) showError(mobile, "Invalid mobile number.");
    if (!shopType.val()) showError(shopType, "Please select a business type.");
    if (!country.val()) showError(country, "Please select a country.");

    return isValid;
}

function getUTMParams() {
    const params = new URLSearchParams(window.location.search);
    return {
        utm_source: params.get("utm_source") || "",
        utm_medium: params.get("utm_medium") || "",
        utm_campaign: params.get("utm_campaign") || "",
        utm_content: params.get("utm_content") || "",
        utm_term: params.get("utm_term") || ""
    };
}

sendPayload = function(payload) {
    console.log("🚀 Sending Payload with UTM:", payload);

    try {
        fbq("track", "Lead", {
            content_name: "Thai Demo Signup Form",
            currency: payload.currency || "THB",
            value: 0,
            utm_source: payload.utm_source,
            utm_medium: payload.utm_medium,
            utm_campaign: payload.utm_campaign
        });
    } catch (e) {
        console.warn("⚠️ FB Pixel not loaded yet:", e);
    }
};
