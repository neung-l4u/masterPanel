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

<!-- Template Restaurant 3 -->
<div id="TemRes3">
    <div class="row">
        <div class="col-6">
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="nav-res3Home" data-bs-toggle="tab" data-bs-target="#res3Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res3Home');">Home</button>
                    <button class="nav-item nav-link" id="nav-res3About" data-bs-toggle="tab" data-bs-target="#res3About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res3About');">About</button>
                    <button class="nav-item nav-link" id="nav-res3Contact" data-bs-toggle="tab" data-bs-target="#res3Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res3Contact');">Contact</button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="res3Home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row pt-3">

                        <div>
                            <input type="text" class="form-control" id="tdR3Slogan" placeholder="Business Slogan">
                        </div>

                        <div>
                            <input type="text" class="form-control" id="tdR3HomeIntroduction" placeholder="Business Introduction">
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
                <div class="tab-pane fade" id="res3About" role="tabpanel" aria-labelledby="nav-about-tab">
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
                <div class="tab-pane fade" id="res3Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    <div class="row pt-3">

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
            <img id="res3Img" src="../assets/img/Res3Home.png" class="img-fluid" alt="Restaurant 3">
        </div>
    </div>
</div>

<script>
    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //TEMPLATE_R3_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_R3_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_R3_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

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