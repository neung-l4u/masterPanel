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
    }else{
        currencyBox.val("");
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
    }else{
        return "";
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
        first_name: decodeURIComponent(form.find("[name='first_name']").val()),
        last_name: decodeURIComponent(form.find("[name='last_name']").val()),
        email: decodeURIComponent(form.find("[name='email']").val()),
        mobile: decodeURIComponent(form.find("[name='mobile']").val()),
        contactTime: decodeURIComponent(form.find("[name='contactTime']").val()),
        company: decodeURIComponent(form.find("[name='company']").val()),
        shopName: decodeURIComponent(form.find("[name='shopName']").val()),
        country: decodeURIComponent(form.find("[name='country']").val()),
        countryCode: shortCountry(),
        shopType: decodeURIComponent(form.find("[name='shopType']").val()),
        shopTypeForLeadManagement: shopTypeForLeadManagement(),
        url: decodeURIComponent(form.find("[name='url']").val()),
        city: decodeURIComponent(form.find("[name='city']").val()),
        currency: decodeURIComponent(form.find("[name='currency']").val()),
        interest: decodeURIComponent(form.find("[name='interest']").val()),
        comments: decodeURIComponent(form.find("[name='comments']").val()),
        SignupFormVersion: decodeURIComponent(form.find("[name='SignupFormVersion']").val()),
        formType: decodeURIComponent(form.find("[name='formType']").val()),
        leadSource: decodeURIComponent(form.find("[name='leadSource']").val()),
        leadRecordType: decodeURIComponent(form.find("[name='leadRecordType']").val())
    };
}

function validateForm(form) {
    let isValid = true;
    form.find(".field-error").remove(); // Clear previous errors
    form.find(".border-red-400").removeClass("border-red-400");

    const firstName = form.find('[name="first_name"]');
    const email     = form.find('[name="email"]');
    const mobile    = form.find('[name="mobile"]');
    const lastName  = form.find('[name="last_name"]');
    const shopName  = form.find('[name="shopName"]');
    const shopType  = form.find('[name="shopType"]');
    const country   = form.find('[name="country"]');
    const currency  = form.find('[name="currency"]');
    const interest  = form.find('[name="interest"]');

    function showError(input, message) {
        const error = $('<p class="field-error text-red-500 text-xs mt-1"></p>').text(message);
        input.addClass("border-red-400");
        input.closest('.form-group').append(error);
        isValid = false;
    }

    if (!firstName.val() || !firstName.val().trim()) showError(firstName, "First name is required.");
    if (!lastName.val() || !lastName.val().trim()) showError(lastName, "Last name is required.");
    if (!email.val() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.val())) showError(email, "Invalid email.");
    if (!mobile.val() || !/^\+?[0-9\s\-]{8,15}$/.test(mobile.val())) showError(mobile, "Invalid mobile number.");
    if (!shopName.val() || !shopName.val().trim()) showError(shopName, "Shop name is required.");
    if (!shopType.val() || shopType.val() === "") showError(shopType, "Please select a business type.");
    if (!country.val() || country.val() === "") showError(country, "Please select a country.");
    if (!currency.val() || currency.val() === "") showError(currency, "Please select a currency.");
    if (!interest.val() || interest.val() === "") showError(interest, "Please select your interest.");

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
