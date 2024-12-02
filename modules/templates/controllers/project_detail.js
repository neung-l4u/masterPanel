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

$(".domainbox, .hostingbox, .gloriabox, .resOtherSystem, .masOtherSystem").hide();

function toggleBox(checkbox, box) {
    $(checkbox).on("click", function () {
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
    console.log('ShopType: ' + ShopType);
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

function selectPage(page) {
    var imageMap = {
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

    var imgData = imageMap[page] || '#res1Img,../assets/img/Res1Home.png';
    var [imgSelector, imgSrc] = imgData.split(',');
    $(imgSelector).attr('src', imgSrc);
}

function nextPrev(step) {
    var $activeTab = $('.nav-tabs .nav-link.active');
    var $tabs = $('.nav-tabs .nav-link');
    var currentIndex = $tabs.index($activeTab);
    var newIndex = currentIndex + step;

    // Ensure the new index is within bounds
    if (newIndex >= 0 && newIndex < $tabs.length) {
        $tabs.eq(newIndex).tab('show');
        updateImage(newIndex);
    }
}

$('#prevPageBtn').click(function() {
    nextPrev(-1);
});

$('#nextPageBtn').click(function() {
    nextPrev(1);
});

$(function() {
    setLayout();
    selectPage();

});//ready

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
