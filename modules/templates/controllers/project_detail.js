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

function setLayout() {
    let ShopType = $('#ShopType').val();
    let TemplateType = $('#TemplateType').find(":selected").val();
    console.log('ShopType = ' + ShopType);
    console.log('TemplateType: ' + TemplateType);

    const resSystem = $('#resSystem');
    const masSystem = $('#masSystem');
    const TemRes1 = $('#TemRes1');
    const TemRes2 = $('#TemRes2');
    const TemRes3 = $('#TemRes3');
    const TemMas1 = $('#TemMas1');
    const TemMas2 = $('#TemMas2');
    const TemMas3 = $('#TemMas3');

    resSystem.hide();
    masSystem.hide();
    TemRes1.hide();
    TemRes2.hide();
    TemRes3.hide();
    TemMas1.hide();
    TemMas2.hide();
    TemMas3.hide();

    if (ShopType === "Restaurant") {
        resSystem.show();
        if (TemplateType === "1") { TemRes1.show(); }
        if (TemplateType === "2") { TemRes2.show(); }
        if (TemplateType === "3") { TemRes3.show(); }
    } else if (ShopType === "Massage") {
        masSystem.show();
        if (TemplateType === "1") { TemMas1.show(); }
        if (TemplateType === "2") { TemMas2.show(); }
        if (TemplateType === "3") { TemMas3.show(); }
    } else {
        console.log('OK');
    }
}


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
