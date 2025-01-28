const settings = {
  env_mode: "prod",
  url_payment: {
    card: "https://payments.localforyou.com/api/payment",
    invoice: "https://payments.localforyou.com/api/invoice",
  },
  url_getProductList: "assets/API/B-Price-2501150754-updatethai.json",
  url_getPrice: "assets/API/price.php",
  url_getStates: "assets/statics/states.json",
  url_authentication: "assets/API/authentication.php",
  url_logs: "assets/function/logs.php",
  Payment_Module: {
    CreditCard: true,
    DirectDebit: true,
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
    UK: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    },
    CA: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    },
    US: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    },
    TH: {
      "Restaurant": "2.9% + 30 cent",
      "Massage": "4% + 30 cent"
    }
  },
  socials: [
    {
      show: true,
      name: "Facebook",
      icon: "<i class='fa-brands fa-facebook'></i>",
      boxName: "socialsFacebook",
    },
    {
      show: true,
      name: "Instagram",
      icon: "<i class='fa-brands fa-instagram'></i>",
      boxName: "socialsInstagram",
    },
    {
      show: false,
      name: "YouTube",
      icon: "<i class='fa-brands fa-youtube'></i>",
      boxName: "socialsYouTube",
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
      boxName: "socialTikTok",
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
      boxName: "socialTwitter",
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
      boxName: "socialYelp",
    },
  ],
  Payment_Detail: {
    tax_rate_id: "txr_1FOBr7AVc0AdHeDfJlSDoENf",
    coupon_Code: {
      AU: {
        "1TRIAL": {
          code: "198Demo1",
          discount: "19800",
        },
        "FREEWEB": {
          code: "FreeWeb1",
          discount: "39900",
        }
      },
      NZ: {
        "1TRIAL": {
          code: "198Demo2",
          discount: "19800",
        },
        "FREEWEB": {
          code: "FreeWeb1",
          discount: "39900",
        },
      },
      CA: {
        "1TRIAL": {
          code: "096Demo1",
          discount: "9600",
        },
        "1SMILE": {
          code: "Partner1",
          discount: "19800",
        },
        "1WAWIO": {
          code: "Partner2",
          discount: "19800",
        },
        "FREEWEB": {
          code: "FreeWeb1",
          discount: "39900",
        },
      },
      UK: {
        "1TRIAL": {
          code: "140Demo1",
          discount: "0",
        },
        "SPECIAL": {
          code: "299Demo1",
          discount: "0",
        },
        "FREEWEB": {
          code: "FreeWeb1",
          discount: "0",
        }
      },
      US: {
        "1TRIAL": {
          code: "096Demo1",
          discount: "9600",
        },
        "1SMILE": {
          code: "Partner1",
          discount: "19800",
        },
        "1WAWIO": {
          code: "Partner2",
          discount: "19800",
        },
        "FREEWEB": {
          code: "FreeWeb1",
          discount: "39900",
        },
      },
    },
  },
};
