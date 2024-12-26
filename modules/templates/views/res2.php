<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT *, IF(shopTypeID=1, "Restaurant", "Massage") as "typeName" FROM `tb_project` WHERE projectID = ?;',$id)->fetchArray();
$projectID = $id;
?>

<link rel="stylesheet" href="../assets/css/template.css">

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate"><?php echo $row['typeName']; ?> <?php echo $row['selectedTemplate']; ?></li>
    </ol>
</nav>

<!-- Template Restaurant 2 -->
<div id="TemRes2">
    <div class="row">
        <div class="col-6">
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res2Home" data-bs-toggle="tab" data-bs-target="#res2Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res2About" data-bs-toggle="tab" data-bs-target="#res2About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-res2Contact" data-bs-toggle="tab" data-bs-target="#res2Contact" type="button" role="tab" aria-selected="false">Contact</button>
                </div>
            </nav>

            <!-- Content Page -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="res2Home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="row pt-3">
                        <div class="col">
                            <div>
                                <label for="formSliderSet1">1.Image Slider Set 1</label>
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formSliderSet1">
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
                            </div>
                            <div>
                                <label for="formSliderSet2">2.Image Slider Set 2</label>
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formSliderSet2">
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
                            </div>
                            <div>
                                <label for="formSliderSet3">3.Image Slider Set 3</label>
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formSliderSet3">
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
                            </div>

                            <div class="col">
                                <label for="tdR1Slogan">4.Promotion Headline #1</label>
                                <input type="text" class="form-control" id="r2PromotionHeadline1" placeholder="Promotion Headline #1">
                            </div>

                            <div class="col">
                                <label for="tdR1Slogan">5. Promotion Message #1</label>
                                <input type="text" class="form-control" id="r2PromotionMessage1" placeholder="Business Slogan">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div>
                                        <label for="formPromotionImg1">6. Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotionImg1">
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
                                    </div>
                                    <div>
                                        <label for="formPromotionImg2">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotionImg2">
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
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <label for="formPromotionImg3">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotionImg3">
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
                                    </div>
                                    <div>
                                        <label for="formPromotionImg4">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotionImg4">
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
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label for="formFeaturedImg1">7. Featured Menu Image #1</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg1">
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
                                </div>
                                <div class="col-6">
                                    <label for="formFeaturedImg2">8. Featured Menu Image #2</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg2">
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
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label for="formFeaturedImg1">9. Featured Menu Image #3</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg3">
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
                                </div>
                                <div class="col-6">
                                    <label for="formFeaturedImg1">10. Featured Menu Image #4</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg4">
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
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div>
                                        <label for="formFeaturedImg1">7. Featured Menu Image #5</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg5">
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
                                    </div>
                                    <div>
                                        <label for="formFeaturedImg2">8. Featured Menu Image #6</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg6">
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
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div>
                                        <label for="formFeaturedImg1">9. Featured Menu Image #7</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg7">
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
                                    </div>
                                    <div>
                                        <label for="formFeaturedImg1">10. Featured Menu Image #8</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFeaturedImg8">
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
                                    </div>
                                </div>
                            </div>

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
                </div> 
                <!-- End-Home-Tab -->
                <div class="tab-pane fade" id="res2About" role="tabpanel" aria-labelledby="nav-about-tab">
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
                <!-- End-About-Tab -->
                <div class="tab-pane fade" id="res2Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                    <div class="row pt-3">

                        <!-- Contact -->
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
                <!-- End-Contact-Tab -->
            </div>

            <div class="row" id="changepage" style="overflow:auto;">
                <div class="col-6" style="text-align:left;">

                <button type="button" class="btn btn-success" id="submitBtn">save</button>
                </div>

                <div class="col-6" style="text-align:right;">
                    <button class="btn btn-light" id="prevPageBtn">Back</button>
                    <button class="btn btn-primary" id="nextPageBtn">Next</button>
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