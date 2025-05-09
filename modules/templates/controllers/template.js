const projectID = $("#projectID").val();
const loginID = $("#loginID").val();

let pages = []; //for local storage
const ketTxt = "sendStatus";
const key = ketTxt+projectID;

let page = "";// for ajax
let payload = {};// for ajax

const infoTextHome = $("#infoTextHome");
const infoTextAbout = $("#infoTextAbout");
const infoTextContact = $("#infoTextContact");
const infoTextService = $("#infoTextService");

const imageMap = {
    'tab-res1Home':  '#res1Img,../assets/img/Res1Home.png',
    'tab-res1About': '#res1Img,../assets/img/Res1About.png',
    'tab-res1Contact': '#res1Img,../assets/img/Res1Contact.png',
    'tab-res2Home': '#res2Img,../assets/img/Res2Home.png',
    'tab-res2About': '#res2Img,../assets/img/Res2About.png',
    'tab-res2Contact': '#res2Img,../assets/img/Res2Contact.png',
    'tab-res3Home': '#res3Img,../assets/img/Res3Home.png',
    'tab-res3About': '#res3Img,../assets/img/Res3About.png',
    'tab-res3Contact': '#res3Img,../assets/img/Res3Contact.png',
    'tab-mas1Home': '#mas1Img,../assets/img/Mas1Home.png',
    'tab-mas1About': '#mas1Img,../assets/img/Mas1About.png',
    'tab-mas1Services': '#mas1Img,../assets/img/Mas1Service.png',
    'tab-mas1Contact': '#mas1Img,../assets/img/Mas1Contact.png',
    'tab-mas2Home': '#mas2Img,../assets/img/Mas2Home.png',
    'tab-mas2About': '#mas2Img,../assets/img/Mas2About.png',
    'tab-mas2Services': '#mas2Img,../assets/img/Mas2Service.png',
    'tab-mas2Contact': '#mas2Img,../assets/img/Mas2Contact.png',
    'tab-mas3Home': '#mas3Img,../assets/img/Mas3Home.png',
    'tab-mas3About': '#mas3Img,../assets/img/Mas3About.png',
    'tab-mas3Services': '#mas3Img,../assets/img/Mas3Service.png',
    'tab-mas3Contact': '#mas3Img,../assets/img/Mas3Contact.png'
};

$('.nav-tabs').on('shown.bs.tab', (e) => {
    const [selector, src] = (imageMap[e.target.id] || '').split(',');
    //if (selector) $(selector).attr('src', src);
});

const nextPrev = (step) => {
    const $tabs = $('.nav-tabs .nav-link');
    const idx = $tabs.index($('.nav-tabs .nav-link.active')) + step;
    if ($tabs[idx]) $tabs.eq(idx).tab('show');
};
$('#prevPageBtn').click(() => nextPrev(-1));
$('#nextPageBtn').click(() => nextPrev(1));

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

function saveToDB() {
    const callAjax = $.ajax({
        type: "POST",
        crossDomain: true,
        dataType: 'json',
        url: "../models/ajaxTemplate.php",
        data: {
            "loginID": loginID,
            "projectID": projectID,
            "page": page,
            "payload": payload
        }
    });
    callAjax.done(function (res) {
        saveStatus(page, 1);
    });
}//saveToDB


function saveStatus(page, status) {
    let infoTextHome = $("#infoTextHome");
    let infoTextAbout = $("#infoTextAbout");
    let infoTextContact = $("#infoTextContact");
    let infoTextServices = $("#infoTextServices");

    let statusText = (status === 1) ? "Saved !!" : "Not Saved";

    switch (page) {
        case "home":
            infoTextHome.text(statusText);
            if (status === 1) {
                infoTextHome.removeClass("text-danger").addClass("text-success");
            } else {
                infoTextHome.removeClass("text-success").addClass("text-danger");
            }
            break;
        case "about":
            infoTextAbout.text(statusText);
            if (status === 1) {
                infoTextAbout.removeClass("text-danger").addClass("text-success");
            } else {
                infoTextAbout.removeClass("text-success").addClass("text-danger");
            }
            break;
        case "contact":
            infoTextContact.text(statusText);
            if (status === 1) {
                infoTextContact.removeClass("text-danger").addClass("text-success");
            } else {
                infoTextContact.removeClass("text-success").addClass("text-danger");
            }
            break;
        case "services":
            infoTextServices.text(statusText);
            if (status === 1) {
                infoTextServices.removeClass("text-danger").addClass("text-success");
            } else {
                infoTextServices.removeClass("text-success").addClass("text-danger");
            }
            break;
    }
}
