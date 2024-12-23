<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT projectName, shopTypeID FROM `tb_project` WHERE projectID = ?;',$id)->fetchArray();

$projectID = $id;
$projectName = $row["projectName"];
$shopTypeID = $row["shopTypeID"];
?>

<link rel="stylesheet" href="../assets/css/project_detail.css">

<!-- Template Restaurant 1 -->
<div id="TemRes1">
    <div class="row">
        <div class="col-6">
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="nav-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res1Home');">Home</button>
                    <button class="nav-item nav-link" id="nav-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1About');">About</button>
                    <button class="nav-item nav-link" id="nav-res1Contact" data-bs-toggle="tab" data-bs-target="#res1Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1Contact');">Contact</button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="res1Home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row pt-3">
                        <div>
                            <input type="text" class="form-control" id="tdR1Slogan" placeholder="Business Slogan">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion1" placeholder="Information or Promotion">
                        </div>

                        <div class="mb-3">
                            <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1HeadHomeImg">
                                <div class="col pt-3 pb-3 border rounded">
                                    <img class="preview" src="../assets/img/default.png" id="tdR1HeadHomeImg" alt="place">
                                    <p id="picNametdR1HeadHomeImg"></p>
                                    <input type="file" class="file-input" id="filetdR1HeadHomeImg" />
                                    <button type="submit" class="button" id="btnUpload">Upload</button>
                                </div>
                            </form>


                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1SubHeadline1" placeholder="Home Page Introduction Sub Headline-1">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1MainHeadline1" placeholder="Home Page Introduction Main Headline">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1SubHeadline2" placeholder="Home Page Introduction Sub Headline-2">
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR1HomeIntroduction" rows="3" placeholder="Home Page Introduction Body"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured1">
                                        <div class="col pt-3 pb-3 border rounded">
                                            <img class="preview" src="../assets/img/default.png" id="tdR1Featured1" alt="place">
                                            <p id="picNametdR1Featured1">Featured image 1</p>
                                            <input type="file" class="file-input" id="filetdR1Featured1" />
                                            <button type="submit" class="button" id="btnUpload">Upload</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="col">
                                    <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured2">
                                        <div class="col pt-3 pb-3 border rounded">
                                            <img class="preview" src="../assets/img/default.png" id="tdR1Featured2" alt="place">
                                            <p id="picNametdR1Featured2">Featured image 2</p>
                                            <input type="file" class="file-input" id="filetdR1Featured2" />
                                            <button type="submit" class="button" id="btnUpload">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured3">
                                        <div class="col pt-3 pb-3 border rounded">
                                            <img class="preview" src="../assets/img/default.png" id="tdR1Featured3" alt="place">
                                            <p id="picNametdR1Featured3">Featured image 3</p>
                                            <input type="file" class="file-input" id="filetdR1Featured3" />
                                            <button type="submit" class="button" id="btnUpload">Upload</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="col">
                                    <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured4">
                                        <div class="col pt-3 pb-3 border rounded">
                                            <img class="preview" src="../assets/img/default.png" id="tdR1Featured4" alt="place">
                                            <p id="picNametdR1Featured4">Featured image 4</p>
                                            <input type="hidden" id="picNametdR1Featured4" value="abc">
                                            <input type="file" class="file-input" id="filetdR1Featured4" />
                                            <button type="submit" class="button" id="btnUpload">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <textarea class="form-control" id="tdR1TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                                <input type="text" class="col-8 form-control" id="tdR1TestimonialName1" placeholder="Name">
                            </div>

                            <div class="col">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg1">
                                    <div class="col">
                                        <img class="preview" src="../assets/img/default.png" id="tdR1TestimonialImg1" alt="place">
                                        <p id="picNametdR1TestimonialImg1">Testimonial image 1 </p>
                                        <input type="file" class="file-input" id="filetdR1TestimonialImg1" />
                                        <button type="submit" class="button" id="btnUpload">Upload</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR1TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR1TestimonialName2" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1TestimonialImg2">
                                    <label class="custom-file-label" for="tdR1TestimonialImg2">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR1TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR1TestimonialName3" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1TestimonialImg3">
                                    <label class="custom-file-label" for="tdR1TestimonialImg3">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR1TestimonialText4" rows="3" placeholder="Testimonial #4"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR1TestimonialName4" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1TestimonialImg4">
                                    <label class="custom-file-label" for="tdR1TestimonialImg4">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1GGReview" placeholder="Google Link for Review">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1DeliveryMapImg">
                                <label class="custom-file-label" for="tdR1DeliveryMapImg">Delivery Map Image</label>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR1DeliveryRate" rows="3" placeholder="Delivery Rates with Locations"></textarea>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1PromotionBgImg">
                                <label class="custom-file-label" for="tdR1PromotionBgImg">Promotion Area Background Image</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2" placeholder="Promotion Headline">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2Sub" placeholder="Promotion Sub Headline">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1CarouselImg">
                                <label class="custom-file-label" for="tdR1CarouselImg">Carousel Images</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="res1About" role="tabpanel" aria-labelledby="nav-about-tab">
                    <div class="row pt-3">

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1HeadAboutImg">
                                    <label class="custom-file-label" for="tdR1HeadAboutImg">About Page Header Background Image 2201x11068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1AboutUSBody" rows="3" placeholder="About Us Body"></textarea>
                            </div>
                            
                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR1AboutPromotion1" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1AboutPromotion1Body" rows="3" placeholder="Promotion Body"></textarea>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1AboutPromotionImg">
                                    <label class="custom-file-label" for="tdR1AboutPromotionImg">Promotion Image</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1CalloutBgImg">
                                    <label class="custom-file-label" for="tdR1CalloutBgImg">Background Image</label>
                                </div>
                            </div>

                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR1Callout1" placeholder="Callout #1">
                            </div>

                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR1Callout2" placeholder="Callout #2">
                            </div>

                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR1Callout3" placeholder="Callout #3">
                            </div>

                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR1Callout4" placeholder="Callout #4">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1AboutFeaturedImg">
                                    <label class="custom-file-label" for="tdR1AboutFeaturedImg">Featured Dish Image #1</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="">
                                    <label class="custom-file-label" for="">Featured Dish Image #2</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="">
                                    <label class="custom-file-label" for="">Featured Dish Image #3</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="">
                                    <label class="custom-file-label" for="">Featured Dish Image #4</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="">
                                    <label class="custom-file-label" for="">Featured Dish Image #5</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="">
                                    <label class="custom-file-label" for="">Featured Dish Image #6</label>
                                </div>
                            </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="res1Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    <div class="row pt-3">

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1HeadContactImg">
                                <label class="custom-file-label" for="tdR1HeadContactImg">Contact Page Header Background Image 2268x1512 pixels</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1ContactBgImg">
                                <label class="custom-file-label" for="tdR1ContactBgImg">Contact Page Background Image 2268x1512 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1ContactHeadSub1" placeholder="Contact Us Sub Headline #1">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1ContactHeadSub2" placeholder="Contact Us Sub Headline #2">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1ContactPromotion1" placeholder="Promotion Headline">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR1ContactPromotion1sub" placeholder="Promotion Sub Headline">
                        </div>

                    </div>
                </div>
            </div>

            <div class="row" id="changepage" style="overflow:auto;">
                <div class="col-6" style="text-align:left;">
                <input id="cmdSubmit" class="btn btn-success" type="button" value="Save">
                </div>

                <div class="col-6" style="text-align:right;">

                    <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                    <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                </div>
            </div>
        </div>

        <div class="col-6">
            <img id="res1Img" src="../assets/img/Res1Home.png" class="img-fluid" alt="Restaurant 1">
        </div>

    </div>
</div>

<script src="../controllers/project_detail.js"></script>

<script>
    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //TEMPLATE_R1_PAGE_HOME
            tdR1Slogan: $('#tdSlogan').val(),
            tdR1HomePromotion1: $('#tdHomePromotion1').val(),
            //tdR1HeadHomeImg: $('#tdHeadHomeImg').val(),
            tdR1SubHeadline1: $('#tdSubHeadline1').val(),
            tdR1MainHeadline1: $('#tdMainHeadline1').val(),
            tdR1SubHeadline2: $('#tdSubHeadline2').val(),
            tdR1HomeIntroduction: $('#tdHomeIntroduction').val(),
            //tdR1HomeIntroductionImg: $('#tdHomeIntroductionImg').val(),
            tdR1Featured1: $('#tdFeatured1').val(),
            //tdR1Featured1Img: $('#tdFeatured1Img').val(),
            tdR1Featured2: $('#tdFeatured1').val(),
            //tdR1Featured2Img: $('#tdFeatured1Img').val(),
            tdR1Featured3: $('#tdFeatured1').val(),
            //tdR1Featured3Img: $('#tdFeatured1Img').val(),
            tdR1Featured4: $('#tdFeatured1').val(),
            //tdR1Featured4Img: $('#tdFeatured1Img').val(),
            tdR1TestimonialText1: $('#tdTestimonialText1').val(),
            tdR1TestimonialName1: $('#tdTestimonialName1').val(),
            //tdR1TestimonialImg1: $('#tdTestimonialImg1').val(),
            tdR1TestimonialText2: $('#tdTestimonialText1').val(),
            tdR1TestimonialName2: $('#tdTestimonialName1').val(),
            //tdR1TestimonialImg2: $('#tdTestimonialImg1').val(),
            tdR1TestimonialText3: $('#tdTestimonialText1').val(),
            tdR1TestimonialName3: $('#tdTestimonialName1').val(),
            //tdR1TestimonialImg3: $('#tdTestimonialImg1').val(),
            tdR1TestimonialText4: $('#tdTestimonialText1').val(),
            tdR1TestimonialName4: $('#tdTestimonialName1').val(),
            //tdR1TestimonialImg4: $('#tdTestimonialImg1').val(),
            tdR1GGReview: $('#tdGGReview').val(),
            //tdR1DeliveryMapImg: $('#tdDeliveryMapImg').val(),
            tdR1DeliveryRate: $('#tdDeliveryRate').val(),
            //tdR1PromotionBgImg: $('#tdPromotionBgImg').val(),
            tdR1HomePromotion2: $('#tdHomePromotion2').val(),
            tdR1HomePromotion2Sub: $('#tdHomePromotion2Sub').val(),
            tdR1CarouselImg: $('#tdCarouselImg').val(),

        //TEMPLATE_R1_PAGE_ABOUT
            //tdR1HeadAboutImg: $('#tdHeadAboutImg').val(),
            tdR1AboutUSBody: $('#tdAboutUSBody').val(),
            tdR1AboutPromotion1: $('#tdAboutPromotion1').val(),
            tdR1AboutPromotion1Body: $('#tdAboutPromotion1Body').val(),
            tdR1AboutPromotionImg: $('#tdAboutPromotionImg').val(),
            tdR1CalloutBgImg: $('#tdCalloutBgImg').val(),
            tdR1Callout1: $('#tdCallout1').val(),
            tdR1Callout2: $('#tdCallout2').val(),
            tdR1Callout3: $('#tdCallout3').val(),
            tdR1Callout4: $('#tdCallout4').val(),
            //tdR1AboutFeaturedImg: $('#tdAboutFeaturedImg').val(),
            
        //TEMPLATE_R1_PAGE_CONTACT
            //tdR1HeadContactImg: $('#tdHeadContactImg').val(),
            //tdR1ContactBgImg: $('#tdContactBgImg').val(),
            tdR1ContactHeadSub1: $('#tdContactHeadSub1').val(),
            tdR1ContactHeadSub2: $('#tdContactHeadSub2').val(),
            tdR1ContactPromotion1: $('#tdContactPromotion1').val(),
            tdR1ContactPromotion1sub: $('#tdContactPromotion1sub').val(),

        };

        console.log("Payload", payload);

        const callAjax = $.ajax({
            url: "../models/actionAjax.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

    });
</script>