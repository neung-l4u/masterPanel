<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT *, IF(shopTypeID=1, "Restaurant", "Massage") as "typeName" FROM `tb_project` WHERE projectID = ?;',$id)->fetchArray();
$projectID = $id;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template submission : Massage 2</title>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<link rel="stylesheet" href="../assets/css/template.css">

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate"><?php echo $row['typeName']; ?> <?php echo $row['selectedTemplate']; ?></li>
    </ol>
</nav>

<!-- Template Massage 2 -->
<div id="Temres1" class="container-fluid">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-res1Services" data-bs-toggle="tab" data-bs-target="#res1Services" type="button" role="tab" aria-selected="false">Services</button>
                    <button class="nav-item nav-link" id="tab-res1Contact" data-bs-toggle="tab" data-bs-target="#res1Contact" type="button" role="tab" aria-selected="false">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane show active p-3" id="res1Home" role="tabpanel" aria-labelledby="nav-home-tab">

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h2>Home Page</h2>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->
                </div><!--end tab-Home-->

                <!-- fixed Structure -->
                <div class="tab-pane p-3" id="res1About" role="tabpanel" aria-labelledby="nav-about-tab">

                <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h2>About Page</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->
                </div><!--end tab-About-->

                <div class="tab-pane p-3" id="res1Services" role="tabpanel" aria-labelledby="nav-contact-tab">

                <div class="row mb-5"><!--Section 1-->
                    <div class="col-6">
                        <div class="row">
                            <div class="col">
                                <small class="text-info">Form Section—1</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <h2>Services Page</h2>
                            </div>
                        </div>
                        
                    </div><!--end col-6-->
                    <div class="col">
                        <div class="row">
                            <div class="col">
                                <small class="text-info">Example Section—1</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <a href="../assets/img/Res1/new/Res1Contact-1.jpg" target="_blank" title="click to zoom">
                                    <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                </div><!--end tab-Services-->

                <div class="tab-pane p-3" id="res1Contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <h2>Contact Page</h2>
                                </div>
                            </div>
                            
                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!--end tab-Contact-->

            </div> <!-- End tab-content-->
        </div>
    </div>
    <div class="row mb-5" id="changepage">
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-res1Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-res1About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-res1Services');" type="button" class="btn btn-primary" id="bottomTabServices">Services</button>
                    <button onclick="changeTab('tab-res1Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
        </div>
    </div>
</div>
<!-- End Template Massage 2 -->

<script src="../controllers/template.js"></script>
<script>

$(function() {
        setAllPageStatus(); //in template.js
    });//ready

    const submitHome = () => {
        page = "home";
        payload = {
            //TEMPLATE_R1_PAGE_HOME
            "loginID": loginID,
            "A1-01-Slogan": $('#inputSlogan').val(),
            "A1-02-Intro": $('#inputIntroduction1').val(),
            "A1-03-BgHeader": $('#formBGHeader .picname').val(),
            "A1-04-IntroSubHead1": $('#tdR1IntroductionSubHeadline1').val(),
            "A1-05-IntroMainHead": $('#tdR1IntroductionMainHeadline').val(),
            "A1-06-IntroSubhead2": $('#tdR1IntroductionSubHeadline2').val(),
            "A1-07-IntroBody": $('#tdR1IntroductionBody').val(),
            "A1-08-01-Intro1": $('#formIntroductionImage1 .picname').val(),
            "A1-08-02-Intro2": $('#formIntroductionImage2 .picname').val(),
            "A1-08-03-Intro3": $('#formIntroductionImage3 .picname').val(),
            "A1-08-04-Intro4": $('#formIntroductionImage4 .picname').val(),
            "A2-09-Dish1": $('#formDish1 .picname').val(),
            "A2-09-DishText1": $('#textFeaturedDish1').val(),
            "A2-10-Dish2": $('#formDish2 .picname').val(),
            "A2-10-DishText2": $('#textFeaturedDish2').val(),
            "A2-11-Dish3": $('#formDish3 .picname').val(),
            "A2-11-DishText3": $('#textFeaturedDish3').val(),
            "A2-12-Dish4": $('#formDish4 .picname').val(),
            "A2-12-DishText4": $('#textFeaturedDish4').val(),
            "A2-13-PicUser1": $('#formTestimonial1 .picname').val(),
            "A2-13-Review1": $('#textareaTrstimonial1').val(),
            "A2-13-NameUser1": $('#textTrstimonial1').val(),
            "A2-14-PicUser2": $('#formTestimonial2 .picname').val(),
            "A2-14-Review2": $('#textareaTrstimonial2').val(),
            "A2-14-NameUser2": $('#textTrstimonial2').val(),
            "A2-15-PicUser3": $('#formTestimonial3 .picname').val(),
            "A2-15-Review": $('#textareaTrstimonial3').val(),
            "A2-15-NameUser3": $('#textTrstimonial3').val(),
            "A2-16-PicUser4": $('#formTestimonial4 .picname').val(),
            "A2-16-Review4": $('#textareaTrstimonial4').val(),
            "A2-16-NameUser4": $('#textTrstimonial4').val(),
            "A2-17-LinkReview": $('#ggLinkReview').val(),
            "A2-18-LinkWrite": $('#ggLinkWrite').val(),
            "A3-19-DeliveryMapImg": $('#formDeliveryMap .picname').val(),
            "A3-20-DeliveryDetail": $('#deliveryDetail').val(),
            "A3-21-RateDetail": $('#deliveryRateDetail').val(),
            "A3-22-PromoImg": $('#formPromotionArea .picname').val(),
            "A3-23-PromoHeadline": $('#promotionHeadline').val(),
            "A3-24-PromoSunHeadline": $('#promotionSubHeadline').val(),
            "A4-25-01-Carousel1": $('#formCarousel1 .picname').val(),
            "A4-25-02-Carousel2": $('#formCarousel2 .picname').val(),
            "A4-25-03-Carousel3": $('#formCarousel3 .picname').val(),
            "A4-25-04-Carousel4": $('#formCarousel4 .picname').val()
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            //TEMPLATE_R1_PAGE_ABOUT
            "loginID": loginID,  
            "B1-1-HeadBG": $('#formAboutbg1 .picname').val(),
            "B1-2-Body": $('#aboutBody').val(),
            "B2-3-PromoHeadline": $('#aboutPromotionHeadline').val(),
            "B2-4-PromoBody": $('#aboutPromotionBody').val(),
            "B2-5-Prom": $('#formAboutPromotion .picname').val(),
            "B2-6-BGImg": $('#formbgAboutBackground .picname').val(),
            "B2-7-Callout1": $('#aboutCallout1').val(),
            "B2-8-Callout2": $('#aboutCallout2').val(),
            "B2-9-Callout3": $('#aboutCallout3').val(),
            "B2-10-Callout4": $('#aboutCallout4').val(),
            "B3-11-Dish1": $('#formAboutFeaturedDishImg1 .picname').val(),
            "B3-11-Dish2": $('#formAboutFeaturedDishImg2 .picname').val(),
            "B3-11-Dish3": $('#formAboutFeaturedDishImg3 .picname').val(),
            "B3-11-Dish4": $('#formAboutFeaturedDishImg4 .picname').val(),
            "B3-11-Dish5": $('#formAboutFeaturedDishImg5 .picname').val(),
            "B3-11-Dish6": $('#formAboutFeaturedDishImg6 .picname').val(),
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            //TEMPLATE_R1_PAGE_CONTACT    
            "loginID": loginID,    
            "C1-1-HeadBG": $('#formbgContactHeadBackground .picname').val(),
            "C1-2-FormBG" : $('#formbgContactBackground .picname').val(),
            "C1-3-SubHead1" : $('#contactSubHead1').val(),
            "C1-4-SubHead2" : $('#contactSubHead2').val(),
            "C1-5-PromoHeadline" : $('#contactPromotionHeadline').val(),
            "C1-6-PromoSubHeadline" : $('#contactPromotionSubHeadline').val(),
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitContact

</script>