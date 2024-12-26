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

$('#prevPageBtn').click(function() {
    nextPrev(-1);
});

$('#nextPageBtn').click(function() {
    nextPrev(1);
});


// $('#nextPagePrev').click(function(e){
//   e.preventDefault();
//   var next_tab = $('.nav-tabs > .active').next('div').find('a');
//   if(next_tab.length>0){
//       next_tab.trigger('click');
//   }else{
//       $('.nav-tabs li:eq(0) a').trigger('Save');
//   }
// });

// $("#nextPageBtn").click(function () {
//   $('.nav-tabs').tab("show").scrollTop(300);
// });

// var currentTab = 0;
// showTab(currentTab);

// function showTab(n) {
//   $(".tab").eq(n).show();

//   if (n == 0) {
//       $("#prevBtn").hide();
//   } else {
//       $("#prevBtn").show();
//   }

//   if (n == $(".tab").length - 1) {
//       $("#nextBtn").text("Submit");
//   } else {
//       $("#nextBtn").text("Next");
//   }

//   fixStepIndicator(n);
// }

// function nextPrev(n) {
//   var $tabs = $(".tab");

//   // if (n == 1 && !validateForm()) return false;

//   $tabs.eq(currentTab).hide();

//   currentTab += n;

//   if (currentTab >= $tabs.length) {
//       $("#projectForm").submit();
//       return false;
//   }

//   showTab(currentTab);
// }

// function validateForm() {
//   var $tab = $(".tab").eq(currentTab);
//   var $inputs = $tab.find("input");
//   var valid = true;

//   $inputs.each(function() {
//     if ($(this).val() == "") {
//       $(this).addClass("invalid");
//       valid = false;
//     }
//   });

//   if (valid) {
//     $(".step").eq(currentTab).addClass("finish");
//   }

//   return valid;
// }

/*
----- function Upload Preview ------
    
เปลี่ยนเฉพาะ form id โดยตั้งชื่อ form นำหน้า ตามด้วย prefix

<label for="formLogo" class="form-label">Logo</label>
    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formLogo">
        <img class="preview" src="../assets/img/default.png" alt="place">
        <input class="picname" type="hidden" value="">
        <div class="row">
            <input type="file" class="file-input col-8" />
            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
        </div>
    </form>
*/

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
