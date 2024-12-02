const settings = {
  env_mode: "prod",
  url_payment: {
    card: "https://payments.localforyou.com/api/payment",
    invoice: "https://payments.localforyou.com/api/invoice",
  },
  url_getProductList: "assets/API/latestPrice.json",
  url_getPrice: "assets/API/price.php",
  url_authentication: "assets/api/authentication.php",
  url_logs: "assets/function/logs.php",
  Payment_Module: {
    CreditCard: true,
    DirectDebit: true,
    Stripe: false,
    QR: false,
    Invoice: true,
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
        },
        "1SMILE": {
          code: "5EBRQJYT",
          discount: "19800",
        },
        "SMILETRIAL": {
          code: "48ijwQYU",
          discount: "19800",
        }
      },
      NZ: {
        "1TRIAL": {
          code: "TlzlydBN",
          discount: "19800",
        },
        "1SMILE": {
          code: "Jt2LJ9Tc",
          discount: "19800",
        },
        "SMILETRIAL": {
          code: "ErU8AVh4",
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
          code: "8TCNZSmU",
          discount: "9600",
        },
        "1SMILE": {
          code: "UlA856pc",
          discount: "19800",
        },
        "SMILETRIAL": {
          code: "8003x3Va",
          discount: "9700",
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
};
