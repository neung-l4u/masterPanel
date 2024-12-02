let lap = 0;
let formData = {
    formCountry: "",
    formType: "",
    formCurrency: "AUD",
    owner: {
        firstName: "",
        lastName: "",
        mobile: "",
        email: "",
        bestTime: ""
    },
    business: {
      shopName: "",
      tradingName: "",
      company: "",
      businessRegisteredNo: "",
      phone: "",
      shopNumber: "",
      website: "",
      facebook: "",
      supportLanguage: "",
      country: ""
    },
    package: {

    }
}

const Cuisines = ["Thai","American","Asian","Breakfast","Burger","Cafe","Caribbean","Chinese","Coffee","Fast Foods","Fish and Chips","French","Greek","Indian","Italian","Japanese","Kebab","Lebanese","Mexican","North Indian","Pasta","Pizza","Salads","Sandwiches","Seafood","Spanish","Sushi","Turkish","Vietnamese","Other"];

const countryCode = {
    "AU" : "+61",
    "NZ" : "+64",
    "US" : "+1",
    "TH" : "+66"
};

const timezoneOptions = {
    timeZone: 'Australia/Brisbane',
    hour12: false,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
};