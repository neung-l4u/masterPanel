const settings = {
  env_mode: "prod",
  url_payment: {
    card: "https://payments.localforyou.com/api/dev/payment",
    invoice: "https://payments.localforyou.com/api/dev/invoice",
  },
  url_getProductList: "assets/API/priceModel23-08.json",
  url_getPrice: "assets/API/price.php",
  url_authentication: "assets/API/authentication.php",
  url_logs: "assets/function/logs.php",
  Payment_Module: {
    CreditCard: true,
    DirectDebit: false,
    Stripe: false,
    QR: false,
    Invoice: false,
  },
  PaymentFee: {
    AU: {
      "Restaurant": "1.75% + 30 cent",
      "Massage": "4% + 30 cent"
    },
    NZ: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    },
    US: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    }
  },
  socials: [
    {
      show: true,
      name: "Facebook",
      icon: "<i class='fa-brands fa-facebook'></i>",
      boxName: "00N2v00000IyVq6",
    },
    {
      show: true,
      name: "Instagram",
      icon: "<i class='fa-brands fa-instagram'></i>",
      boxName: "00N9s000000Qp8x",
    },
    {
      show: false,
      name: "YouTube",
      icon: "<i class='fa-brands fa-youtube'></i>",
      boxName: "00N9s000000Qp97",
    },
    {
      show: false,
      name: "WhatsApp",
      icon: "<i class='fa-brands fa-whatsapp'></i>",
      boxName: "",
    },
    {
      show: false,
      name: "WeChat",
      icon: "<i class='fa-brands fa-weixin'></i>",
      boxName: "",
    },
    {
      show: true,
      name: "TikTok",
      icon: "<i class='fa-brands fa-tiktok'></i>",
      boxName: "00N9s000000Qoxu",
    },
    {
      show: false,
      name: "Snapchat",
      icon: "<i class='fa-brands fa-snapchat'></i>",
      boxName: "",
    },
    {
      show: false,
      name: "Pinterest",
      icon: "<i class='fa-brands fa-pinterest'></i>",
      boxName: "",
    },
    {
      show: false,
      name: "Twitter",
      icon: "<i class='fa-brands fa-twitter'></i>",
      boxName: "00N9s000000Qp92",
    },
    {
      show: false,
      name: "LinkedIn",
      icon: "<i class='fa-brands fa-linkedin'></i>",
      boxName: "",
    },
    {
      show: true,
      name: "Yelp",
      icon: "<i class='fa-brands fa-yelp'></i>",
      boxName: "00N9s000000Qoxz",
    },
  ],
  Payment_Detail: {
    tax_rate_id: "txr_1FOBr7AVc0AdHeDfJlSDoENf",
    coupon_Code: {
      AU: {
        "1TRIAL": {
          code: "6gEtdC79",
          discount: "19800",
        },
        "FREEWEB": {
          code: "G8eiBBZp",
          discount: "49900",
        }
      },
      NZ: {
        "1TRIAL": {
          code: "TlzlydBN",
          discount: "19800",
        },
        "FREEWEB": {
          code: "DuXDCjDt",
          discount: "49900",
        },
      },
      TH: {},
      US: {
        "1TRIAL": {
          code: "QmU8MHVR",
          discount: "9600",
        },
        "1SMILE": {
          code: "UlA856pc",
          discount: "19800",
        },
        "WAWIO": {
          code: "Wawio",
          discount: "0",
        },
        "FREEWEB": {
          code: "lcIadxmG",
          discount: "39900",
        },
      },
    },
  },
  Products_Description: {
    "AU": {
      "Restaurant": "<ul id='description'>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Online Ordering System</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Delivery Driver Solution</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Dine-in QR Code + Printing</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>2x Promotion For Your Customer</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>2x Window Stickers</li>" +
                      "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
                    "</ul>",
      "Massage": "<ul id='description'>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Localforyou Booking System</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Website Hosting</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
                   "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
                 "</ul>"
    },
    "NZ": {
      "Restaurant": "<ul id='description'>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Online Ordering System</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Delivery System</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Dine-in QR Code + Printing</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>2x Promotion For Your Customer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>2x Window Stickers</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
          "</ul>",
      "Massage": "<ul id='description'>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Localforyou Booking System</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Website Hosting</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
          "</ul>"
    },
    "US": {
      "Restaurant": "<ul id='description'>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Online Ordering System</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Delivery Driver Solution</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Dine-in QR Code</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>2x Promotion For Your Customer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
          "</ul>",
      "Massage": "<ul id='description'>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Optimised Website</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Localforyou Booking System</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Website Hosting</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Customer Data</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Sales Analytics</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Corrected Digital Footprint</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Personalised Digital Flyer</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>DIY SMS Directly To Your Customer Base</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Strategy Session</li>" +
          "<li><img src='assets/img/checkmark-flat-16.png' alt='included'>Secure Online Payment</li>" +
          "</ul>"
    }
  }
};
