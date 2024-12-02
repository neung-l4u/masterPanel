$(document).ready(function () {
    console.log("ready");
    $("#txtSuccess").hide();
    genCoupon();
});//ready

const copyToClipboard = (text) => {
    $("#txtSuccess").fadeIn(500).fadeOut(300);
    // window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
    if (window.clipboardData && window.clipboardData.setData) {
        // IE specific code path to prevent textarea being shown while dialog is visible.
        return clipboardData.setData("Text", text);

    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        let textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
        } catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}
const genCoupon = () => {
  const coupons = settings.Payment_Detail.coupon_Code;
  const AU = Object.keys(coupons.AU);
  const NZ = Object.keys(coupons.NZ);
  const TH = Object.keys(coupons.TH);
  const US = Object.keys(coupons.US);
  const AUBox = $(".AU-Coupon");
  const NZBox = $(".NZ-Coupon");
  const THBox = $(".TH-Coupon");
  const USBox = $(".US-Coupon");

  if(AU.length>0){
      AUBox.empty();
      AU.map( (item) => {
          let link = `<a href="#" onclick="copyToClipboard('${item}');" class="list-group-item list-group-item-action">${item}</a>`;
          AUBox.append(link)
      });
  }

    if(NZ.length>0){
        NZBox.empty();
        NZ.map( (item) => {
            let link = `<a href="#" onclick="copyToClipboard('${item}');" class="list-group-item list-group-item-action">${item}</a>`;
            NZBox.append(link)
        });
    }

    if(TH.length>0){
        THBox.empty();
        TH.map( (item) => {
            let link = `<a href="#" onclick="copyToClipboard('${item}');" class="list-group-item list-group-item-action">${item}</a>`;
            THBox.append(link)
        });
    }

    if(US.length>0){
        USBox.empty();
        US.map( (item) => {
            let link = `<a href="#" onclick="copyToClipboard('${item}');" class="list-group-item list-group-item-action">${item}</a>`;
            USBox.append(link)
        });
    }
};