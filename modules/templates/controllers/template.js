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

function readPage(param){
    let readStatus = localStorage.getItem(key);
    return JSON.parse(readStatus);
}//readPage

function setPage(param){
    let currentPage = localStorage.getItem(key);
    let findPage = pages.includes(param);
    if(!findPage){
        pages.push(param);
        let val = JSON.stringify(pages); //arr to text
        localStorage.setItem(key,val);
    }
    return true;
}//setPage

function clearKey(){
    localStorage.clear();
    return true;
}//clearKey

function checkPage(param){
    let currentPage = localStorage.getItem(key);
    let currentPageArr =  JSON.parse(currentPage);
    let findPage = false;

    if (currentPageArr != null){
        findPage = currentPageArr.includes(param);
    }
    return findPage;
}//checkPage

function setAllPageStatus(){
    if(checkPage('home')){
        infoTextHome.removeClass( "text-danger" ).addClass( "text-success" );
        infoTextHome.empty().text("Send !!");
    }

    if(checkPage('about')){
        infoTextAbout.removeClass( "text-danger" ).addClass( "text-success" );
        infoTextAbout.empty().text("Send !!");
    }

    if(checkPage('contact')){
        infoTextContact.removeClass( "text-danger" ).addClass( "text-success" );
        infoTextContact.empty().text("Send !!");
    }

    if(checkPage('service')){
        infoTextService.removeClass( "text-danger" ).addClass( "text-success" );
        infoTextService.empty().text("Send !!");
    }
}//setAllPageStatus

function saveToDB() {
    let answer = confirm("Are you sure you want to save this page?");

    if (answer) {
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
        setPage(page);
        setAllPageStatus();
    }//if
}//saveToDB
