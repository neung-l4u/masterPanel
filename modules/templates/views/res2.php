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

<link rel="stylesheet" href="../assets/css/template.css">

<!-- Template Restaurant 2 -->
<div id="TemRes2">
    <div class="row">
        <div class="col-6">
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="nav-res2Home" data-bs-toggle="tab" data-bs-target="#res2Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res2Home');">Home</button>
                    <button class="nav-item nav-link" id="nav-res2About" data-bs-toggle="tab" data-bs-target="#res2About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res2About');">About</button>
                    <button class="nav-item nav-link" id="nav-res2Contact" data-bs-toggle="tab" data-bs-target="#res2Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res2Contact');">Contact</button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="res2Home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row pt-3">

                        <div class="col py-3 px-3">
                            <label for="formLogo" class="form-label">Logo</label>
                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formLogo">
                                <img class="preview" src="../assets/img/default.png" alt="place">
                                <input class="picname" type="hidden" value="">
                                <div class="row">
                                    <input type="file" class="file-input col-8" />
                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                </div>
                            </form>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2HomeSlider2">
                                <label class="custom-file-label" for="tdR2HomeSlider1">Image Slider Set 2 1920x415 pixels</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2HomeSlider3">
                                <label class="custom-file-label" for="tdR2HomeSlider3">Image Slider Set 3 1920x415 pixels</label>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR2Delivery" rows="3" placeholder="Pickup & Delivery Service"></textarea>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion1" placeholder="Promotion Headline #1">
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR2HomePromotion1Body" rows="3" placeholder="Promotion Message #1"></textarea>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2Carousel">
                                <label class="custom-file-label" for="tdR2Carousel">Promotion Image Carousel 800x1000 pixels (4 Image)</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FeaturedImg">
                                <label class="custom-file-label" for="tdR2FeaturedImg">Featured Menu Image #1 600x800 pixels (8 Image)</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2PromotionImg2">
                                <label class="custom-file-label" for="tdR2PromotionImg2">Promotion Background Image Carousel 2201x1068 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion2" placeholder="Promotion Headline #2">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2ReviewBg">
                                <label class="custom-file-label" for="tdR2ReviewBg">Reviews Background Image 2201x1068 pixels</label>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR2TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR2TestimonialName1" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2TestimonialImg1">
                                    <label class="custom-file-label" for="tdR2TestimonialImg1">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR2TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR2TestimonialName2" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2TestimonialImg2">
                                    <label class="custom-file-label" for="tdR2TestimonialImg2">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <textarea class="form-control" id="tdR2TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                        
                            <div class="d-flex flex-row gap-1">
                                <input type="text" class="col-8 form-control" id="tdR2TestimonialName3" placeholder="Name">
                            
                                <div class="col-4 custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2TestimonialImg3">
                                    <label class="custom-file-label" for="tdR2TestimonialImg3">Photo</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2PromotionImg3">
                                <label class="custom-file-label" for="tdR2PromotionImg3">Promotion Image #3 952x952 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion3" placeholder="Promotion Headline #3">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion3Body" placeholder="Promotion Message #3">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FooterImg1">
                                <label class="custom-file-label" for="tdR2FooterImg1">Footer Image #1 600x600 pixels</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FooterImg2">
                                <label class="custom-file-label" for="tdR2FooterImg2">Footer Image #2 600x600 pixels</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FooterImg3">
                                <label class="custom-file-label" for="tdR2FooterImg3">Footer Image #3 600x600 pixels</label>
                            </div>
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FooterImg4">
                                <label class="custom-file-label" for="tdR2FooterImg4">Footer Image #4 600x600 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion4" placeholder="Promotion Headline #4">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2HomePromotion4Body" placeholder="Promotion Message #4">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2FooterBgImg">
                                <label class="custom-file-label" for="tdR2FooterBgImg">Footer Background Image 2201x1068 pixels</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="res2About" role="tabpanel" aria-labelledby="nav-about-tab">
                    <div class="row pt-3">

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HeadAboutImg">
                                    <label class="custom-file-label" for="tdR2HeadAboutImg">About Page Header Background Image 2201x11068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2AboutUSBody" rows="3" placeholder="About Us Body"></textarea>
                            </div>
                            
                            <div class="mt-3">
                                <input type="text" class="form-control" id="tdR2AboutPromotion1" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2AboutPromotion1Body" rows="3" placeholder="Promotion Body"></textarea>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2AboutPromotionImg">
                                    <label class="custom-file-label" for="tdR2AboutPromotionImg">Promotion Image Carousel 850x1275 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2StaffImg1">
                                    <label class="custom-file-label" for="tdR2StaffImg1">Staff Image #1 600x900 pixels</label>
                                </div>
                            </div>
                            
                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2StaffImg2">
                                    <label class="custom-file-label" for="tdR2StaffImg2">Staff Image #2 600x900 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2StaffImg3">
                                    <label class="custom-file-label" for="tdR2StaffImg3">Staff Image #3 600x900 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2AboutFeaturedImg">
                                    <label class="custom-file-label" for="tdR2AboutFeaturedImg">Featured Dish Image #1</label>
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
                <div class="tab-pane fade" id="res2Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    <div class="row pt-3">

                        <!-- Contact -->
                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2HeadContactImg">
                                <label class="custom-file-label" for="tdR2HeadContactImg">Contact Page Header Background Image 2268x1512 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2ContactHeadSub1" placeholder="Contact Us Sub Headline #1">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2ContactHeadSub2" placeholder="Contact Us Sub Headline #2">
                        </div>

                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR2ContactBgImg">
                                <label class="custom-file-label" for="tdR2ContactBgImg">Contact Page Background Image 2268x1512 pixels</label>
                            </div>
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2ContactPromotion1" placeholder="Promotion Headline">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR2ContactPromotion1sub" placeholder="Promotion Sub Headline">
                        </div>

                    </div>
                </div>
            </div>

            <div class="row" id="changepage" style="overflow:auto;">
                <div class="col-6" style="text-align:left;">

                <button type="button" class="btn btn-success" id="submitBtn">save</button>
                </div>

                <div class="col-6" style="text-align:right;">

                    <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                    <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                </div>
            </div>
        </div>

        <div class="col-6">
            <img id="res2Img" src="../assets/img/Res2Home.png" class="img-fluid" alt="Restaurant 2">
        </div>

    </div>
</div>
<!-- EndTemplate Restaurant 2 -->


<input type="hidden" id="projectID" value="<?php echo $id; ?>">
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

<script src="../controllers/template.js"></script>
<script>
    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //TEMPLATE_R2_PAGE_HOME
            tdSlogan: $('#tdSlogan').val(),
            //tdR2HomeSlider1: $('#tdR2HomeSlider1').val(),
            //tdR2HomeSlider2: $('#tdR2HomeSlider2').val(),
            //tdR2HomeSlider3: $('#tdR2HomeSlider3').val(),
            tdR2Delivery: $('#tdR2Delivery').val(),
            tdR2HomePromotion1: $('#tdR2HomePromotion1').val(),
            tdR2HomePromotion1Body: $('#tdR2HomePromotion1Body').val(),
            //tdR2Carousel: $('#tdR2Carousel').val(),
            //tdR2FeaturedImg: $('#tdR2FeaturedImg').val(),
            //tdR2PromotionImg2: $('#tdR2PromotionImg2').val(),
            tdR2HomePromotion2: $('#tdR2HomePromotion2').val(),
            //tdR2ReviewBg: $('#tdR2ReviewBg').val(),
            tdR2TestimonialText1: $('#tdR2TestimonialText1').val(),
            tdR2TestimonialName1: $('#tdR2TestimonialName1').val(),
            //tdR2TestimonialImg1: $('#tdR2TestimonialImg1').val(),
            tdR2TestimonialText2: $('#tdR2TestimonialText1').val(),
            tdR2TestimonialName2: $('#tdR2TestimonialName1').val(),
            //tdR2TestimonialImg2: $('#tdR2TestimonialImg1').val(),
            tdR2TestimonialText3: $('#tdR2TestimonialText1').val(),
            tdR2TestimonialName3: $('#tdR2TestimonialName1').val(),
            //tdR2TestimonialImg3: $('#tdR2TestimonialImg1').val(),
            //tdR2PromotionImg3: $('#tdR2PromotionImg3').val(),
            tdR2HomePromotion3: $('#tdR2HomePromotion3').val(),
            tdR2HomePromotion3Body: $('#tdR2HomePromotion3Body').val(),
            //tdR2FooterImg1: $('#tdR2FooterImg1').val(),
            //tdR2FooterImg2: $('#tdR2FooterImg2').val(),
            //tdR2FooterImg3: $('#tdR2FooterImg3').val(),
            //tdR2FooterImg4: $('#tdR2FooterImg4').val(),
            tdR2HomePromotion4: $('#tdR2HomePromotion4').val(),
            tdR2HomePromotion4Body: $('#tdR2HomePromotion4Body').val(),
            //tdR2FooterBgImg: $('#tdR2FooterBgImg').val(),

        //TEMPLATE_R2_PAGE_ABOUT
            //tdR2HeadAboutImg: $('#tdR2HeadAboutImg').val(),
            tdR2AboutUSBody: $('#tdR2AboutUSBody').val(),
            tdR2AboutPromotion1: $('#tdR2AboutPromotion1').val(),
            tdR2AboutPromotion1Body: $('#tdR2AboutPromotion1Body').val(),
            //tdR2AboutPromotionImg: $('#tdR2AboutPromotionImg').val(),
            //tdR2StaffImg1: $('#tdR2StaffImg1').val(),
            //tdR2StaffImg2: $('#tdR2StaffImg2').val(),
            //tdR2StaffImg3: $('#tdR2StaffImg3').val(),
            //tdR2AboutFeaturedImg: $('#tdR2AboutFeaturedImg').val(),
            
        //TEMPLATE_R2_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),
            tdR2ContactHeadSub1: $('#tdR2ContactHeadSub1').val(),
            tdR2ContactHeadSub2: $('#tdR2ContactHeadSub2').val(),
            //tdR2ContactBgImg: $('#tdR2ContactBgImg').val(),
            tdR2ContactPromotion1: $('#tdR2ContactPromotion1').val(),
            tdR2ContactPromotion1sub: $('#tdR2ContactPromotion1sub').val(),
            tdR2ContactHeadSub1: $('#tdR2ContactHeadSub1').val(),
            tdR2ContactHeadSub1: $('#tdR2ContactHeadSub1').val(),

        };

        console.log("Payload", payload);

        const callAjax = $.ajax({
            url: "../models/ajaxRes3.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

    });
</script>