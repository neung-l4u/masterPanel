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
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css" xmlns:input="http://www.w3.org/1999/html">
    <link rel="stylesheet" href="../assets/css/project_detail.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">

<!-- Template Restaurant 1 -->
<div id="TemRes1" class="row">
    <div class="d-flex">
        <div class="col-8">
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="nav-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res1Home');">Home</button>
                    <button class="nav-item nav-link" id="nav-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1About');">About</button>
                    <button class="nav-item nav-link" id="nav-res1Contact" data-bs-toggle="tab" data-bs-target="#res1Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1Contact');">Contact</button>
                </div>
            </nav>

            <!-- Contect Home Page -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="res1Home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row pt-3">
                        <div class="col">
                            <label for="tdR1Slogan">1.Business Slogan</label>
                            <input type="text" class="form-control" id="tdR1Slogan" placeholder="Business Slogan">
                        </div>
                        <div>
                            <label for="tdR1HomePromotion1">2.Information or Promotion</label>
                            <input type="text" class="form-control" id="tdR1HomePromotion1" placeholder="Information or Promotion">
                        </div>


                        <label for="">3.Header Background Image</label>
                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formggwp">
                                <div class="d-flex flex-column mb-3">
                                    <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                        <input class="picname" type="hidden" value="">
                                        <div class="d-flex flex-row gap-2">
                                            <input type="file" class="file-input col-8" />
                                            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </form>


                        <div>
                            <label for="tdR1SubHeadline1">4.Home Page Introduction Sub Headline-1</label>
                            <input type="text" class="form-control" id="tdR1SubHeadline1" placeholder="Home Page Introduction Sub Headline-1">
                        </div>

                        <div>
                            <label for="tdR1MainHeadline1">5.Home Page Introduction Main Headline</label>
                            <input type="text" class="form-control" id="tdR1MainHeadline1" placeholder="Home Page Introduction Main Headline">
                        </div>

                        <div>
                            <label for="tdR1SubHeadline2">6.Home Page Introduction Sub Headline-2</label>
                            <input type="text" class="form-control" id="tdR1SubHeadline2" placeholder="Home Page Introduction Sub Headline-2">
                        </div>

                        <div>
                        <label for="tdR1HomeIntroduction">7.Home Page Introduction Body</label>
                            <textarea class="form-control" id="tdR1HomeIntroduction" rows="3" placeholder="Home Page Introduction Body"></textarea>
                        </div>

                        <!-- 8.Featured Dish Image #1 & 9.Featured Dish Image #2-->
                        <div class="row mb-3">
                            <div class="col-6">
                                <!-- <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured1">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured1">8.Featured Dish Image #1</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured1" alt="place">
                                                <input type="hidden" id="dish1" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured1" />
                                                <button type="submit" class="button" id="btnUpload" onclick="handleFormSubmit(this)>Upload</button>
                                                <div>
                                                    <input type="text" class="form-control" id="tdR1Featuredname1" placeholder="Featured Dish Name #1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form> -->
                                <div class="d-flex flex-column mb-3">
                                    <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                        <input class="picname" type="hidden" value="">
                                        <div class="d-flex flex-row gap-2">
                                            <input type="file" class="file-input col-8" />
                                            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured2">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured2">9.Featured Dish Image #2</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured2" alt="place">
                                                <input type="hidden" id="dish2" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured2" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <div>
                                                    <input type="text" class="form-control" id="tdR1Featuredname2" placeholder="Featured Dish Name #2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- END-->

                        <!-- 10.Featured Dish Image #3 & 11.Featured Dish Image #4-->
                        <div class="row mb-5">
                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured3">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured3">10.Featured Dish Image #3</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured4" alt="place">
                                                <input type="hidden" id="dish3" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured3" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <div>
                                                    <input type="text" class="form-control" id="tdR1Featuredname3" placeholder="Featured Dish Name #3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured4">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured4">11.Featured Dish Image #4</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured4" alt="place">
                                                <input type="hidden" id="dish4" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured4"/>
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <div>
                                                    <input type="text" class="form-control" id="tdR1Featuredname4" placeholder="Featured Dish Name #4">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- END-->

                        <!-- Testimonial Box1-->

                        <div class="row mb-3">
                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg1">
                                    <div class="d-flex flex-column">
                                        <label for="tdR1TestimonialImg1">12. Testimonial image #1</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured1" alt="place">
                                                <input type="hidden" id="testimg1" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured1" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <textarea class="form-control" id="tdR1TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                                                <input type="text" class="form-control" id="tdR1TestimonialName1" placeholder="Name #1">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg2">
                                    <div class="d-flex flex-column">
                                        <label for="tdR1TestimonialImg2">13. Testimonial image #2</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured2" alt="place">
                                                <input type="hidden" id="testimg2" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured2" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <textarea class="form-control" id="tdR1TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                                                <input type="text" class="form-control" id="tdR1TestimonialName2" placeholder="Name #2">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Testimonial -->
                        
                        <!-- Testimonial Box2-->
                        <div class="row mb-5">
                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg3">
                                    <div class="d-flex flex-column">
                                        <label for="tdR1TestimonialImg3">14. Testimonial image #3</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured3" alt="place">
                                                <input type="hidden" id="testimg3" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured3" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <textarea class="form-control" id="tdR1TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                                                <input type="text" class="form-control" id="tdR1TestimonialName3" placeholder="Name #3">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg4">
                                    <div class="d-flex flex-column">
                                        <label for="tdR1TestimonialImg4">15. Testimonial image #4</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                            <div class="d-flex flex-column ">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured4" alt="place">
                                                <input type="hidden" id="testimg4" value="">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <input type="file" class="file-input" id="filetdR1Featured2" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                                <textarea class="form-control" id="tdR1TestimonialText4" rows="3" placeholder="Testimonial #4"></textarea>
                                                <input type="text" class="form-control" id="tdR1TestimonialName4" placeholder="Name #4">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Testimonial -->

                        <label for="tdR1GGReview">16.Google Link for Review</label>
                        <div>
                            <input type="text" class="form-control" id="tdR1GGReview" placeholder="Google Link for Review">
                        </div>

                        <label for="tdR1DeliveryMapImg">17.Delivery Map Image</label>
                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1DeliveryMapImg">
                                <label class="custom-file-label" for="tdR1DeliveryMapImg">Delivery Map Image</label>
                            </div>
                        </div>

                        
                        <label for="tdR1DeliveryRate">18.Delivery Rates with Locations</label>
                        <div>
                            <textarea class="form-control" id="tdR1DeliveryRate" rows="3" placeholder="Delivery Rates with Locations"></textarea>
                        </div>

                        <label for="tdR1DeliveryRate">19.Promotion Area Background Image</label>
                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1PromotionBgImg">
                                <label class="custom-file-label" for="tdR1PromotionBgImg">Promotion Area Background Image</label>
                            </div>
                        </div>
                        
                        <label for="tdR1HomePromotion2">20.Promotion Headline</label>
                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2" placeholder="Promotion Headline">
                        </div>

                        <label for="tdR1HomePromotion2Sub">21.Promotion Sub Headline</label>
                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2Sub" placeholder="Promotion Sub Headline">
                        </div>

                        <label for="tdR1HomePromotion2Sub">22.Carousel Images</label>
                        <div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1CarouselImg">
                                <label class="custom-file-label" for="tdR1CarouselImg">Carousel Images</label>
                            </div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1CarouselImg">
                                <label class="custom-file-label" for="tdR1CarouselImg">Carousel Images</label>
                            </div>
                            <div class="custom-file  mb-3">
                                <input type="file" class="custom-file-input" id="tdR1CarouselImg">
                                <label class="custom-file-label" for="tdR1CarouselImg">Carousel Images</label>
                            </div>
                            <div class="custom-file  mb-5">
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
            
        </div>  

                       
        

        <!-- images ex.restaurant 1 -->
        <div class="col">
            <img id="res1Img" src="../assets/img/Res1Home.png" class="img-fluid" alt="Restaurant 1">
        </div>
                <!-- images ex. end--> 

    </div>
            <!-- button back next 1 -->
            <div class="row" id="changepage" style="overflow:auto;">
            <div class="col-6" style="text-align:left;">
            <input id="cmdSubmit" class="btn btn-success" type="button" value="Save">
            </div>

            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

            <div class="col-6" style="text-align:right;">

                <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
            </div>

            
        </div>
</div>

<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
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

</script>