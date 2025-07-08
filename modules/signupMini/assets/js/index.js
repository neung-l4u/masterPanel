
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
  if (!company.val().trim()) showError(company, "Restaurant name required.");
  if (!country.val()) showError(country, "Please select a country.");

  return isValid;
}

