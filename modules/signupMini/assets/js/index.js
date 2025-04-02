
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

function validateForm() {
    let isValid = true;

    // Clear errors
    $(".text-danger").remove();

    let firstName = $("#first_name");
    let lastName = $("#last_name");
    let email = $("#email");
    let mobile = $("#mobile");
    let company = $("#company");
    let country = $("#country");

    // Error message
    function showError(input, message) {
        let error = $("<small class='text-danger'></small>").text(message);
        input.parent().append(error);
        isValid = false;
    }

    if ($.trim(firstName.val()) === "") {
        showError(firstName, "First Name is required.");
    }

    if ($.trim(lastName.val()) === "") {
        showError(lastName, "Last Name is required.");
    }

    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test($.trim(email.val()))) {
        showError(email, "Please enter a valid email.");
    }

    let phonePattern = /^\+?[0-9\s\-]{8,15}$/;
    if (!phonePattern.test($.trim(mobile.val()))) {
        showError(mobile, "Please enter a valid mobile number.");
    }

    if ($.trim(company.val()) === "") {
        showError(company, "Restaurant Name is required.");
    }

    if (!country.val()) {
        showError(country, "Please select your country.");
    }

    if (!isValid) {
        event.preventDefault(); // Prevent form submission if validation fails
    }

    return isValid; // Return validation status
}