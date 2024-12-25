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

<!-- Template Massage 3 -->
<div id="TemMas3">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-mas3Home" data-bs-toggle="tab" data-bs-target="#mas3Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-mas3Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-mas3About" data-bs-toggle="tab" data-bs-target="#mas3About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3About');">About</button>
                        <button class="nav-item nav-link" id="nav-mas3Services" data-bs-toggle="tab" data-bs-target="#mas3Services" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3Services');">Services</button>
                        <button class="nav-item nav-link" id="nav-mas3Contact" data-bs-toggle="tab" data-bs-target="#mas3Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="mas3Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Business Logo - Header</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Business Phone</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">3. Business Name</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Business Slogan</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">5. Order Online URL</label>
                                <input type="text" class="form-control" id="" placeholder="">

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3About" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. About Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' alt="image upload 1"/>
                                <img id='img-upload-2' alt="image upload 2"/>
                                <img id='img-upload-3' alt="image upload 3"/>
                                <img id='img-upload-4' alt="image upload 4"/>
                                <img id='img-upload-5' alt="image upload 5"/>
                                <img id='img-upload-6' alt="image upload 6"/>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3Services" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Services Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3Contact" role="tabpanel" aria-labelledby="contact-tab">


                        <div class="row">
                            <div class="col">

                                <label for="">1. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">3. Contact Us Sub Headline #1</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">3. Contact Us Sub Headline #2</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">5. Promotion Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">6. Promotion Sub Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

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
                <img id="mas3Img" src="../assets/img/Mas3Home.png" class="img-fluid" alt="Massage3">
            </div>
        </div>
    </div>

</div>
<!-- End Template Massage 3 -->


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
            url: "../models/ajaxMas3.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

    });
</script>