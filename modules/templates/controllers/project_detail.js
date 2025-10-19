const projectID = $("#projectID").val();
const saveKey = `saveStatus_${projectID}`;
const saveButton = document.getElementById("cmdSubmit");
const nextButton = document.getElementById("nextBtn");
const infoText = document.getElementById("infoText");

const btnNextStatus = (status) => { //ไว้เปิด|ปิด ปุ่ม Next
    if(status === true){ // ปลดล็อกปุ่ม Next
        nextButton.classList.remove("btn-secondary"); // ลบคลาส btn-secondary
        nextButton.classList.add("btn-primary"); // เพิ่มคลาส btn-primary
        nextButton.disabled = false;
        return true;
    }else{
        nextButton.classList.remove("btn-primary"); // ลบคลาส btn-secondary
        nextButton.classList.add("btn-secondary"); // เพิ่มคลาส btn-primary
        nextButton.disabled = true;
        return false;
    }//if
}//btnStatus


if (infoText.textContent === "1") {
    // ถ้าเคย Save แล้ว: ปลดล็อก Next และแก้ข้อความแจ้งเตือน
    btnNextStatus(true);
    $("#infoText").removeClass("text-warning").addClass("text-success").empty();
    infoText.textContent = "You have already saved. You can proceed.";
} else {
    // ถ้ายังไม่ Save: ปิด Next และแก้ข้อความแจ้งเตือน
    infoText.textContent = "Please save before proceeding.";
}


saveButton.addEventListener("click", () => { // เมื่อกดปุ่ม Save
    btnNextStatus(true);
    // อัปเดตข้อความแจ้งเตือน
    $("#infoText").removeClass("text-warning").addClass("text-success").text("Save completed! You can now click Next.");
    //alert("Save completed!");
});

const nextForm = (url) => {
  window.location.replace(url);
}

function save() {
    let payload = {
        /*BusinessName: $('#BusinessName').val(),
        BusinessMobile: $('#BusinessMobile').val(),
        BusinessEmail: $('#BusinessEmail').val(),
        BusinessAddress: $('#BusinessAddress').val(),
        OpeningHours: $('#OpeningHours').val(),
        ProjectOwner: $('#ProjectOwner').val(),
        DomainUser: $('#DomainUser').val(),
        DomainPass: $('#DomainPass').val(),
        HostingUser: $('#HostingUser').val(),
        HostingPass: $('#HostingPass').val()*/
    };
    console.log(payload);
}//function save

// Toggle Handler
// ---------------------------------------
// UI Config (แก้ตรงนี้ได้ ไม่ต้องไล่แก้ในโค้ดด้านล่าง)
// ---------------------------------------
const UI = {
  HIDE_AT_START: [
    ".domainbox",
    ".hostingbox",
    ".gloriabox",
    ".resOtherSystem",
    ".masOtherSystem",
    "#masSystem",
    "#resSystem",
    "#openingBox",
    "#deliveryBox",
    "#pickupAndDelivery"
  ],

  // mapping: checkbox -> target box
  TOGGLES: [
    { control: ".domainHave",  target: ".domainbox" },
    { control: ".hostingHave", target: ".hostingbox" },
    { control: ".gloriahave",  target: ".gloriabox" },
    { control: ".orderOther",  target: ".resOtherSystem" },
    { control: ".bookOther",   target: ".masOtherSystem" },
    { control: "#chkPickup",   target: "#pickupAndDelivery" },
  ],

  // radio groups: value -> { show, hide }
  OPENING_SWITCH: {
    openChkBox: { show: "#openingBox",  hide: "#openingForm" },
    openChkDay: { show: "#openingForm", hide: "#openingBox"  },
  },

  DELIVERY_SWITCH: {
    deliChkBox: { show: "#deliveryBox",  hide: "#deliveryForm" },
    deliChkDay: { show: "#deliveryForm", hide: "#deliveryBox"  },
  },

  // ตัวเลือกที่ต้อง enable/disable ตาม #chkPickup
  PICKUP_OPTIONS: ["#7dayDeliChk", "#customDeliChk"],

  // ความไวแอนิเมชัน (ms) — ปรับได้ตามชอบ
  ANIM: 300,
};

// ---------------------------------------
// Helpers
// ---------------------------------------
function hideAll(selectors = []) {
  if (!Array.isArray(selectors) || selectors.length === 0) return;
  $(selectors.join(",")).hide();
}

function slideToggleTo($el, show, duration = 0) {
  if (duration > 0) {
    $el.stop(true, true)[show ? "slideDown" : "slideUp"](duration);
  } else {
    $el.toggle(show);
  }
}

/**
 * bindToggle: ผูก checkbox กับกล่องปลายทาง + apply state ตอนโหลด
 * @param {string} controlSel - selector ของ checkbox
 * @param {string} targetSel  - selector ของกล่องที่จะแสดง/ซ่อน
 * @param {object} opts       - { invert?: boolean, duration?: number }
 */
function bindToggle(controlSel, targetSel, { invert = false, duration = 0 } = {}) {
  const apply = () => {
    const checked = $(controlSel).is(":checked");
    const shouldShow = invert ? !checked : checked;
    slideToggleTo($(targetSel), shouldShow, duration);
  };

  $(document).on("change", controlSel, apply);
  apply(); // init state
}

/**
 * bindSwapByValue: สำหรับ radio/select ที่แต่ละค่าแสดง/ซ่อนต่างกัน
 * @param {string} inputSel - selector ของ radio group (เช่น name$='inputOpeningChk')
 * @param {object} map      - value -> { show, hide }
 * @param {number} duration
 */
function bindSwapByValue(inputSel, map, duration = 0) {
  const apply = () => {
    // รองรับทั้ง radio (ใช้ :checked) และ select (ใช้ .val())
    const $inputs = $(inputSel);
    const val = $inputs.is(":radio") ? $(`${inputSel}:checked`).val() : $inputs.val();
    const cfg = map[val];
    if (!cfg) return;

    slideToggleTo($(cfg.show), true, duration);
    slideToggleTo($(cfg.hide), false, duration);
  };

  $(document).on("change", inputSel, apply);
  apply(); // init state
}

/**
 * bindPickup: เปิด/ปิดตัวเลือกจัดส่งตามสถานะ #chkPickup
 */
function bindPickup(chkSel, optionSels, duration = 0) {
  const apply = () => {
    const on = $(chkSel).is(":checked");
    const $options = $(optionSels.join(","));

    $options.prop("disabled", !on);

    if (!on) {
      $options.prop("checked", false);
    } else {
      // ถ้ายังไม่มีอันไหนถูกเลือก บังคับติ๊กตัวแรก
      const anyChecked = optionSels.some(sel => $(sel).is(":checked"));
      if (!anyChecked) $(optionSels[0]).prop("checked", true);
    }

    // กล่องใหญ่ของ pickup/delivery
    slideToggleTo($("#pickupAndDelivery"), on, duration);
  };

  $(document).on("change", chkSel, apply);
  apply(); // init state
}

// ---------------------------------------
// Boot (เรียกครั้งเดียวจบ)
// ---------------------------------------
$(function () {
  // ซ่อนของที่ไม่อยากให้โชว์ตั้งแต่ต้น
  hideAll(UI.HIDE_AT_START);

  // ผูก checkbox toggles ทั่วไป
  UI.TOGGLES.forEach(({ control, target }) =>
    bindToggle(control, target, { duration: UI.ANIM })
  );

  // เปิด/ปิดตัวเลือกได้ตามสถานะ pickup
  bindPickup("#chkPickup", UI.PICKUP_OPTIONS, UI.ANIM);

  // กลุ่ม Opening
  bindSwapByValue("input[name$='inputOpeningChk']", UI.OPENING_SWITCH, UI.ANIM);

  // กลุ่ม Delivery
  bindSwapByValue("input[name$='inputDeliveryChk']", UI.DELIVERY_SWITCH, UI.ANIM);
});


const setHex = (param,box) => { //for set text in span follow color picker
    const theme1Hex = $("#theme1Hex");
    const theme2Hex = $("#theme2Hex");
    const theme3Hex = $("#theme3Hex");
    if (box===1){
        theme1Hex.html(param);
    }else if (box===2){
        theme2Hex.html(param);
    }else if (box===3){
        theme3Hex.html(param);
    }
    return true;
}//const

const handleFormSubmit = (button) => {
    const $form = $(button).closest(".uploadForm");
    const $preview = $form.find(".preview");
    const $prefixId = $(button).closest(".uploadForm").attr("id");
    const $fileInput = $form.find(".file-input");

    let fd = new FormData();
    let files = $fileInput[0].files;
    let newPrefix = $prefixId.substring(4);

    if (files.length > 0) {
        fd.append('file', files[0]);
        fd.append('projectId', projectID);
        fd.append('prefixId', newPrefix);

            $.ajax({
              url: '../models/upload.php',
              type: 'post',
              data: fd,
              contentType: false,
              processData: false,
              success: function (response) {
                const res = (response || '').toString().trim();

                if (res === 'Invalid file extension.') {
                  alert('นามสกุลไฟล์ไม่ถูกต้อง กรุณาเลือกไฟล์ JPG, JPEG, PNG, หรือ SVG');
                  return;
                }

                if (res === '0' || res === '') {
                  alert('File not uploaded');
                  return;
                }

                // อนุญาตเฉพาะผลลัพธ์ที่ดูเป็นพาธไฟล์
                if (!/[\/\\]/.test(res)) {
                  alert('Unexpected response from server.');
                  return;
                }

                const splitPath = res.split('/');
                const newName = splitPath[splitPath.length - 1];
                $preview.attr('src', res);
                $form.find('.picname').val(newName);
              },
              error: function () {
                alert('An error occurred while uploading the file.');
              }
            });

    } else {
        alert("Please select a file.");
    }
};

$('.day-toggle').change(function () {
    const targetId = $(this).attr('id').replace('-open-chk', '-open').replace('-deli-chk', '-deli');
    $(`#${targetId}`).toggle($(this).is(':checked'));
});

$('.copy-link').click(function () {
    const value = $(`#${$(this).data('copy-from')}`).val();
    if (value) {
        $(this).closest('.days-list').find('.time-input:visible, .deli-input:visible').not(`#${$(this).data('copy-from')}`).val(value);
    }
});