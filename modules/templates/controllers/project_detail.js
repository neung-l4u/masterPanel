const projectID = $("#projectID").val();
const saveKey = `saveStatus_${projectID}`;
const saveButton = document.getElementById("cmdSubmit");
const nextButton = document.getElementById("nextBtn");
const infoText = document.getElementById("infoText");

const isSaved = localStorage.getItem(saveKey); //อ่าน key ของ project ที่เลือกมา

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


if (isSaved === "true") {
    // ถ้าเคย Save แล้ว: ปลดล็อก Next และแก้ข้อความแจ้งเตือน
    btnNextStatus(true);
    $("#infoText").removeClass("text-warning").addClass("text-success").empty();
    infoText.textContent = "You have already saved. You can proceed.";
}


saveButton.addEventListener("click", () => { // เมื่อกดปุ่ม Save
    // บันทึกสถานะว่า Save เรียบร้อย
    localStorage.setItem(saveKey, "true");

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

$(".domainbox").hide();
$(".hostingbox").hide();
$(".gloriabox").hide();
$(".resOtherSystem").hide();
$(".masOtherSystem").hide();
$("#masSystem").hide();
$("#resSystem").hide();
$(".emailbox").hide();

function toggleBox(checkbox, box) {
    $(checkbox).on("change", function () {
        if ($(this).is(":checked")) { 
            $(box).show(300);
        } else {
            $(box).hide(200);
        }
    });
}

toggleBox(".domainHave", ".domainbox");
toggleBox(".hostingHave", ".hostingbox");
toggleBox(".gloriahave", ".gloriabox");
toggleBox(".orderOther", ".resOtherSystem");
toggleBox(".bookOther", ".masOtherSystem");
toggleBox(".needEmail", ".emailbox");

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
                success: function(response) {
                    if (response !== "0") {
                        const splitPath = response.split("/");
                        const newName = splitPath[splitPath.length - 1];
                        $preview.attr("src", response);
                        $form.find(".picname").val(newName);
                    } else {
                        alert("File not uploaded");
                    }
                },
            error: function () {
                alert("An error occurred while uploading the file.");
            }
        });
    } else {
        alert("Please select a file.");
    }
};
