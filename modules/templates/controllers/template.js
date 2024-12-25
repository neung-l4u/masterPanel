const projectID = $("#projectID").val();

function selectPage(page) {
    let imageMap = {
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

    let imgData = imageMap[page] || '#res1Img,../assets/img/Res1Home.png';
    let [imgSelector, imgSrc] = imgData.split(',');
    $(imgSelector).attr('src', imgSrc);
}

function nextPrev(step) {
    let $activeTab = $('.nav-tabs .nav-link.active');
    let $tabs = $('.nav-tabs .nav-link');
    let currentIndex = $tabs.index($activeTab);
    let newIndex = currentIndex + step;

    // Ensure the new index is within bounds
    if (newIndex >= 0 && newIndex < $tabs.length) {
        $tabs.eq(newIndex).tab('show');
        updateImage(newIndex);
    }
}
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