const projectID = $("#projectID").val();
const loginID = $("#loginID").val();

// ✅ ดึง projectName จาก breadcrumb
const projectNameElement = $("#projectName").find('a').text();
const projectName = projectNameElement ? projectNameElement.trim() : 'unknown';

// ✅ ตรวจสอบว่า template ไหน (Massage 1/2/3 หรือ Restaurant 1/2/3)
// ใช้ tab ID เพื่อตรวจสอบ template type
const activeTab = $('.nav-tabs .nav-link.active');
const tabId = activeTab.attr('id') || '';
const templateType = tabId.includes('res') ? 'restaurant' : 'massage';
const templateNumber = tabId.match(/(res|mas)(\d)/)?.[2] || '1';

let pages = []; //for local storage
const ketTxt = "sendStatus";
const key = ketTxt+projectID;

let page = "";// for ajax
let payload = {};// for ajax

const infoTextHome = $("#infoTextHome");
const infoTextAbout = $("#infoTextAbout");
const infoTextContact = $("#infoTextContact");
const infoTextService = $("#infoTextService");

// ✅ Mapping JSON key กับ input id
const fieldMapping = {
    // Home Page
    'A1-01-HeadBG': '#homeHeaderBG',
    'A1-02-HeadHline': '#homeHeaderHeadline',
    'A1-03-01-Feature': '#homeFeatureImg1',
    'A1-03-02-ServiceName': '#homeNameFeature1',
    'A1-03-03-ServiceDetail': '#homeDetailFeature1',
    'A1-04-01-Feature': '#homeFeatureImg2',
    'A1-04-02-ServiceName': '#homeNameFeature2',
    'A1-04-03-ServiceDetail': '#homeDetailFeature2',
    'A1-05-01-Feature': '#homeFeatureImg3',
    'A1-05-02-ServiceName': '#homeNameFeature3',
    'A1-05-03-ServiceDetail': '#homeDetailFeature3',
    'A2-06-WelcomeMessage': '#homeWelcomeMessage',
    'A2-07-WelcomeImg1': '#homeWelcomeImg1',
    'A2-08-WelcomeImg2': '#homeWelcomeImg2',
    'A3-09-TestimonialsMessage': '#homeTestimonialsMessage',
    'A3-10-01-TestimonialsName1': '#homeTestimonialName1',
    'A3-10-02-TestimonialsText1': '#homeTestimonialText1',
    'A3-10-03-TestimonialsImg1': '#homeTestimonialImg1',
    'A3-11-01-TestimonialsName2': '#homeTestimonialName2',
    'A3-11-02-TestimonialsText2': '#homeTestimonialText2',
    'A3-11-03-TestimonialsImg2': '#homeTestimonialImg2',
    'A3-12-01-TestimonialsName3': '#homeTestimonialName3',
    'A3-12-02-TestimonialsText3': '#homeTestimonialText3',
    'A3-12-03-TestimonialsImg3': '#homeTestimonialImg3',
    'A3-13-LinkGoogleReview': '#homeGoogleReview',
    'A4-14-OurTeamHeadline': '#homeTeamHeadline',
    'A4-15-OurTeamHeadMessage': '#homeOurTeamMessage',
    'A4-16-01-OurTeamImg1': '#homeTeamImg1',
    'A4-16-02-OurTeamImg2': '#homeTeamImg2',
    'A4-16-03-OurTeamImg3': '#homeTeamImg3',
    'A4-16-04-OurTeamImg4': '#homeTeamImg4',
    'A4-17-AppointmentMessage': '#homeAppointmentMessage',
    'A4-18-PromotionHeadline': '#homePromotionHeadline',
    'A4-19-PromotionMessage': '#homePromotionMessage',
    'HomeNote': '#notesHome',
    
    // About Page
    'B1-01-HeadBG': '#aboutHeaderBG',
    'B1-02-OurStoryMessage': '#aboutOurstoryMessage',
    'B1-03-OurStoryImg': '#ourStoryImg',
    'B1-04-MessageBusinees': '#aboutMessagefromBusiness',
    'AboutNote': '#notesAbout',
    
    // Services Page
    'C1-01-HeadBG': '#servicesHeaderBG',
    'C1-02-ServicesMessage': '#inputServicesMessage',
    'C2-03-01-ServicesImg1': '#services1',
    'C2-03-02-ServicesName1': '#nameServices1',
    'C2-03-03-ServicesPrice1': '#priceServices1',
    'C2-03-04-ServicesDetail1': '#delailServices1',
    'C2-04-01-ServicesImg2': '#services2',
    'C2-04-02-ServicesName2': '#nameServices2',
    'C2-04-03-ServicesPrice2': '#priceServices2',
    'C2-04-04-ServicesDetail2': '#delailServices2',
    'C2-05-01-ServicesImg3': '#services3',
    'C2-05-02-ServicesName3': '#nameServices3',
    'C2-05-03-ServicesPrice3': '#priceServices3',
    'C2-05-04-ServicesDetail3': '#delailServices3',
    'C2-06-01-ServicesImg4': '#services4',
    'C2-06-02-ServicesName4': '#nameServices4',
    'C2-06-03-ServicesPrice4': '#priceServices4',
    'C2-06-04-ServicesDetail4': '#delailServices4',
    'C2-07-01-ServicesImg5': '#services5',
    'C2-07-02-ServicesName5': '#nameServices5',
    'C2-07-03-ServicesPrice5': '#priceServices5',
    'C2-07-04-ServicesDetail5': '#delailServices5',
    'C2-08-01-ServicesImg5': '#services6',
    'C2-08-02-ServicesName5': '#nameServices6',
    'C2-08-03-ServicesPrice5': '#priceServices6',
    'C2-08-04-ServicesDetail5': '#delailServices6',
    'C2-09-01-ServicesImg6': '#services7',
    'C2-09-02-ServicesName6': '#nameServices7',
    'C2-09-03-ServicesPrice6': '#priceServices7',
    'C2-09-04-ServicesDetail6': '#delailServices7',
    'C2-10-01-ServicesImg7': '#services8',
    'C2-10-02-ServicesName7': '#nameServices8',
    'C2-10-03-ServicesPrice7': '#priceServices8',
    'C2-10-04-ServicesDetail7': '#delailServices8',
    'C2-11-01-ServicesImg8': '#services9',
    'C2-11-02-ServicesName8': '#nameServices9',
    'C2-11-03-ServicesPrice8': '#priceServices9',
    'C2-11-04-ServicesDetail8': '#delailServices9',
    'C2-12-01-ServicesImg9': '#services10',
    'C2-12-02-ServicesName9': '#nameServices10',
    'C2-12-03-ServicesPrice9': '#priceServices10',
    'C2-12-04-ServicesDetail9': '#delailServices10',
    'ServicesNote': '#notesServices',
    
    // Contact Page
    'D1-01-HeadBG': '#contactHeaderBG',
    'D1-02-ContactUsMessage': '#inputContactUsMessage',
    'ContactNote': '#notesContact'
};

// ✅ Mapping สำหรับรูปภาพ (key -> image element id)
const imageMapping = {
    'A1-01-HeadBG': '#homeHeaderBG',
    'A1-03-01-Feature': '#homeFeatureImg1',
    'A1-04-01-Feature': '#homeFeatureImg2',
    'A1-05-01-Feature': '#homeFeatureImg3',
    'A2-07-WelcomeImg1': '#homeWelcomeImg1',
    'A2-08-WelcomeImg2': '#homeWelcomeImg2',
    'A3-10-03-TestimonialsImg1': '#homeTestimonialImg1',
    'A3-11-03-TestimonialsImg2': '#homeTestimonialImg2',
    'A3-12-03-TestimonialsImg3': '#homeTestimonialImg3'
};

// ✅ Mapping สำหรับ Restaurant Template
const restaurantFieldMapping = {
    // Home Page
    'A1-01-Slogan': '#inputSlogan',
    'A1-02-Intro': '#inputIntroduction1',
    'A1-03-BgHeader': '#homeHeaderBG',
    'A1-04-IntroSubHead1': '#tdR1IntroductionSubHeadline1',
    'A1-05-IntroMainHead': '#tdR1IntroductionMainHeadline',
    'A1-06-IntroSubhead2': '#tdR1IntroductionSubHeadline2',
    'A1-07-IntroBody': '#tdR1IntroductionBody',
    'A1-08-01-Intro1': '#homeIntroImg1',
    'A1-08-02-Intro2': '#homeIntroImg2',
    'A1-08-03-Intro3': '#homeIntroImg3',
    'A1-08-04-Intro4': '#homeIntroImg4',
    'A2-09-Dish1': '#homeDishImg1',
    'A2-09-DishText1': '#textFeaturedDish1',
    'A2-10-Dish2': '#homeDishImg2',
    'A2-10-DishText2': '#textFeaturedDish2',
    'A2-11-Dish3': '#homeDishImg3',
    'A2-11-DishText3': '#textFeaturedDish3',
    'A2-12-Dish4': '#homeDishImg4',
    'A2-12-DishText4': '#textFeaturedDish4',
    'A2-13-PicUser1': '#homeTestimonialImg1',
    'A2-13-Review1': '#textareaTrstimonial1',
    'A2-13-NameUser1': '#textTrstimonial1',
    'A2-14-PicUser2': '#homeTestimonialImg2',
    'A2-14-Review2': '#textareaTrstimonial2',
    'A2-14-NameUser2': '#textTrstimonial2',
    'A2-15-PicUser3': '#homeTestimonialImg3',
    'A2-15-Review': '#textareaTrstimonial3',
    'A2-15-NameUser3': '#textTrstimonial3',
    'A2-16-PicUser4': '#homeTestimonialImg4',
    'A2-16-Review4': '#textareaTrstimonial4',
    'A2-16-NameUser4': '#textTrstimonial4',
    'A2-17-LinkReview': '#ggLinkReview',
    'A2-18-LinkWrite': '#ggLinkWrite',
    'A3-19-DeliveryMapImg': '#homeDeliveryMapImg',
    'A3-20-DeliveryDetail': '#deliveryDetail',
    'A3-21-RateDetail': '#deliveryRateDetail',
    'A3-22-PromoImg': '#homePromotionImg',
    'A3-23-PromoHeadline': '#promotionHeadline',
    'A3-24-PromoSunHeadline': '#promotionSubHeadline',
    'A4-25-01-Carousel1': '#homeCarouselImg1',
    'A4-25-02-Carousel2': '#homeCarouselImg2',
    'A4-25-03-Carousel3': '#homeCarouselImg3',
    'A4-25-04-Carousel4': '#homeCarouselImg4',
    'notes': '#notesHome',
    
    // About Page
    'B1-1-HeadBG': '#aboutHeaderBG',
    'B1-2-Body': '#aboutBody',
    'B1-5-PromoImg1': '#aboutImg1',
    'B1-5-PromoImg2': '#aboutImg2',
    'B2-3-PromoHeadline': '#aboutPromotionHeadline',
    'B2-4-PromoBody': '#aboutPromotionBody',
    'B2-7-Callout1': '#aboutCallout1',
    'B2-8-Callout2': '#aboutCallout2',
    'B2-9-Callout3': '#aboutCallout3',
    'B2-10-Callout4': '#aboutCallout4',
    'notesAbout': '#notesAbout',
    
    // Contact Page
    'C1-1-HeadBG': '#contactHeaderBG',
    'C1-2-FormBG': '#contactImg1',
    'C1-3-SubHead1': '#contactSubHead1',
    'C1-4-SubHead2': '#contactSubHead2',
    'C1-5-PromoHeadline': '#contactPromotionHeadline',
    'C1-6-PromoSubHeadline': '#contactPromotionSubHeadline',
    'notesContact': '#notesContact'
};

// ✅ ฟังก์ชันดึงข้อมูล content จาก database
function loadContentFromDB(page) {
    return $.ajax({
        type: "POST",
        crossDomain: true,
        dataType: 'json',
        url: "../models/ajaxTemplate.php",
        data: {
            "loginID": loginID,
            "projectID": projectID,
            "page": page,
            "mode": "read"
        }
    });
}

// ✅ ฟังก์ชันแสดงข้อมูล content ใน editor
function displayContentInEditor(page, data) {
    console.log('displayContentInEditor called with page:', page, 'data:', data);
    console.log('Template type:', templateType, 'Template number:', templateNumber);
    if (!data) return;
    
    // ✅ เลือก mapping ตามประเภท template
    const currentMapping = templateType === 'restaurant' ? restaurantFieldMapping : fieldMapping;
    
    // ✅ แสดงค่าลงใน form fields ตามชื่อ key
    Object.keys(data).forEach(function(key) {
        let value = data[key];
        
        // ✅ แทนที่ "n/a" เป็นช่องว่าง
        if (value === 'n/a' || value === 'n\/a') {
            value = '';
        }
        
        // ✅ ใช้ fieldMapping เพื่อหา selector ที่ถูกต้อง
        let selector = currentMapping[key];
        let $element = null;
        
        if (selector) {
            $element = $(selector);
            console.log(`Using mapping: ${key} -> ${selector}:`, $element.length > 0 ? 'FOUND' : 'NOT FOUND');
        } else {
            // ถ้าไม่มี mapping ลองหา element ด้วย id ที่ตรงกับ key
            $element = $(`#${key}`);
            console.log(`Looking for #${key}:`, $element.length > 0 ? 'FOUND' : 'NOT FOUND');
            
            // ถ้าไม่เจอ ลองหา element ที่มี data-field attribute
            if ($element.length === 0) {
                $element = $(`[data-field="${key}"]`);
                console.log(`Looking for [data-field="${key}"]:`, $element.length > 0 ? 'FOUND' : 'NOT FOUND');
            }
        }
        
        // ถ้าเจอ element ให้ set ค่า
        if ($element && $element.length > 0) {
            console.log(`Setting ${key} = ${value}`);
            if ($element.is('input[type="text"], input[type="email"], input[type="tel"], textarea')) {
                $element.val(value);
            } else if ($element.is('input[type="checkbox"], input[type="radio"]')) {
                $element.prop('checked', value === '1' || value === 1 || value === true);
            } else if ($element.is('select')) {
                $element.val(value);
            } else if ($element.is('img')) {
                // ✅ สำหรับรูปภาพ ให้แสดง path ของรูปภาพ
                if (value) {
                    // ถ้า value เป็น path เต็ม ให้ใช้โดยตรง
                    // ถ้า value เป็นแค่ชื่อไฟล์ ให้สร้าง path ที่ถูกต้อง
                    let imagePath = value;
                    if (!value.startsWith('http') && !value.startsWith('../')) {
                        // ถ้าเป็นแค่ชื่อไฟล์ ให้สร้าง path ที่ถูกต้อง
                        // รูปภาพอัปโหลดไปที่ ../upload/projectID-projectName/filename
                        // ใช้ logic เดียวกับ PHP sanitizeFolderName()
                        // 1. แทนที่ space ด้วย underscore
                        // 2. ลบ special characters (เก็บแค่ a-zA-Z0-9_-)
                        let folderName = projectName.replace(/ /g, '_');  // แทนที่ space ด้วย underscore
                        folderName = folderName.replace(/[^a-zA-Z0-9_-]/g, '');  // ลบ special characters
                        imagePath = `../upload/${projectID}-${folderName}/${value}`;
                    }
                    $element.attr('src', imagePath);
                    console.log(`Setting image src: ${imagePath}`);
                }
            } else {
                $element.html(value);
            }
        } else {
            console.log(`⚠️ Element not found for key: ${key}`);
        }
    });
}

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

// ✅ ฟังก์ชันสำหรับโหลดข้อมูลตามชื่อหน้า
function loadPageContent(tabId) {
    const [selector, src] = (imageMap[tabId] || '').split(',');
    
    // ✅ ดึงชื่อหน้า (home, about, services, contact)
    let pageName = '';
    if (tabId.includes('Home')) pageName = 'home';
    else if (tabId.includes('About')) pageName = 'about';
    else if (tabId.includes('Services')) pageName = 'services';
    else if (tabId.includes('Contact')) pageName = 'contact';
    console.log('Page name detected:', pageName);
    
    // ✅ ดึงข้อมูลจาก database และแสดงใน editor
    if (pageName) {
        console.log('Loading content for page:', pageName);
        loadContentFromDB(pageName).done(function(res) {
            console.log('API Response:', res);
            console.log('API Result:', res.result);
            console.log('API Data:', res.data);
            if (res.result === 'success' && res.data) {
                console.log('Displaying content...');
                displayContentInEditor(pageName, res.data);
            } else {
                console.log('No data returned or result is not success');
            }
        }).fail(function(xhr, status, error) {
            console.log('Failed to load content for page: ' + pageName);
            console.log('Error:', error);
            console.log('Status:', status);
        });
    }
}

$('.nav-tabs').on('shown.bs.tab', (e) => {
    console.log('✅ Tab clicked! Event:', e);
    const tabId = e.target.id;
    console.log('Tab ID:', tabId);
    loadPageContent(tabId);
});

// ✅ โหลดข้อมูลเมื่อหน้า load เสร็จ (สำหรับ tab ที่ active แล้ว)
$(document).ready(function() {
    console.log('Document ready! Loading initial content...');
    const activeTab = $('.nav-tabs .nav-link.active');
    if (activeTab.length > 0) {
        const tabId = activeTab.attr('id');
        console.log('Active tab ID:', tabId);
        loadPageContent(tabId);
    }
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
