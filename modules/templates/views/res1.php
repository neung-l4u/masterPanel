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
<link rel="stylesheet" href="../assets/css/project_detail.css">
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css" xmlns:input="http://www.w3.org/1999/html">
    <link rel="stylesheet" href="../assets/css/project_detail.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">

   <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
           <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
           <!--<li class="breadcrumb-item active projectName" aria-current="page" id="projectName"><?php echo $row['projectName']; ?></li>-->
       </ol>
   </nav>

<!-- Template Restaurant 1 -->
<div id="TemRes1" class="row">
    <div class="d-flex">
        <div class="col-6">
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
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish1">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured2">8.Featured Dish Image #1</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control" id="tdR1Featuredname1" placeholder="Featured Dish Name #1">
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish2">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured2">9.Featured Dish Image #2</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control" id="tdR1Featuredname2" placeholder="Featured Dish Name #2">
                                                    </div>
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
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish3">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured2">10.Featured Dish Image #3</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control" id="tdR1Featuredname3" placeholder="Featured Dish Name #3">
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish4">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Featured2">11.Featured Dish Image #4</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control" id="tdR1Featuredname4" placeholder="Featured Dish Name #4">
                                                    </div>
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
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formtestimonail1">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Testimonail1">12.Testimonial image #1</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2">
                                                        <textarea class="form-control" id="tdR1TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                                                        <input type="text" class="form-control" id="tdR1TestimonialName1" placeholder="Name #1">
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formtestimonail2">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Testimonail1">13.Testimonial image #2</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2">
                                                        <textarea class="form-control" id="tdR1TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                                                        <input type="text" class="form-control" id="tdR1TestimonialName2" placeholder="Name #1">
                                                    </div>
                                                </div>
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
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formtestimonail3">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Testimonail1">14.Testimonial image #3</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2">
                                                        <textarea class="form-control" id="tdR1TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                                                        <input type="text" class="form-control" id="tdR1TestimonialName3" placeholder="Name #3">
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formtestimonail4">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1Testimonail1">15.Testimonial image #4</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2">
                                                        <textarea class="form-control" id="tdR1TestimonialText4" rows="3" placeholder="Testimonial #4"></textarea>
                                                        <input type="text" class="form-control" id="tdR1TestimonialName4" placeholder="Name #4">
                                                    </div>
                                                </div>
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

                        <div class="row mb-5">
                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="fromtdR1DeliveryMapImg1">
                                    <div class="d-flex flex-column">
                                        <label for="filetdR1DeliveryMapImg1">17.Delivery Map Image</label>
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>

                            </div>
                            <div class="col-6 mb-4">
                                <div>
                                <label for="tdR1DeliveryRate">18.Delivery Rates with Locations</label>
                                <div>
                                    <textarea class="form-control" id="tdR1DeliveryRate" rows="11" placeholder="Delivery Rates with Locations"></textarea>
                                </div>
                            </div>

                        </div>


                        <label for="">19.Promotion Area Background Image</label>
                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formpromotionBgImg">
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

                        <label for="tdR1HomePromotion2">20.Promotion Headline</label>
                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2" placeholder="Promotion Headline">
                        </div>

                        <label for="tdR1HomePromotion2Sub">21.Promotion Sub Headline</label>
                        <div>
                            <input type="text" class="form-control" id="tdR1HomePromotion2Sub" placeholder="Promotion Sub Headline">
                        </div>

                        <label for="tdR1HomeCarousel">22.Carousel Images</label>
                        <div class="row mb-3">
                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel1">
                                    <div class="d-flex flex-column">
                                    
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel2">
                                    <div class="d-flex flex-column">
                                    
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel1">
                                    <div class="d-flex flex-column">
                                    
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                            <div class="col-6">
                                <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel2">
                                    <div class="d-flex flex-column">
                                    
                                        <div class="d-flex flex-column gap-2 p-2 border rounded"> 
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>    
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- End Contect Home Page -->
                
                <div class="tab-pane fade" id="res1About" role="tabpanel" aria-labelledby="nav-about-tab">
                    <div class="row pt-3">

                            <div>
                                <!-- <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1HeadAboutImg">
                                    <label class="custom-file-label" for="tdR1HeadAboutImg">About Page Header Background Image 2201x11068 pixels</label>
                                </div> -->

                            <label for="tdR1HeadAboutImg">23.About Page Header Background Image</label>
                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formR1HeadAboutImg">
                                <div class="d-flex flex-column mb-3">
                                    <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                        <input class="picname" type="hidden" value="">
                                        <div class="d-flex flex-row gap-2">
                                            <input type="file" class="file-input col-8" id="tdR1HeadAboutImg">
                                            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            </div>

                            <div>
                                <label for="tdR1AboutUSBody">24.About Us Body</label>
                                <textarea class="form-control" id="tdR1AboutUSBody" rows="3" placeholder="About Us Body"></textarea>
                            </div>
                            
                            <div>
                                <label for="tdR1AboutPromotion1">25.Promotion Headline</label>
                                <input type="text" class="form-control" id="tdR1AboutPromotion1" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <label for="tdR1AboutPromotion1Body">26.Promotion Body</label>
                                <textarea class="form-control" id="tdR1AboutPromotion1Body" rows="3" placeholder="Promotion Body"></textarea>
                            </div>

                            <label for="tdR1AboutPromotionImg">27.Promotion Image</label>
                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formR1AboutPromotionImg">
                                <div class="d-flex flex-column mb-3">
                                    <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                        <input class="picname" type="hidden" value="">
                                        <div class="d-flex flex-row gap-2">
                                            <input type="file" class="file-input col-8" id="tdR1AboutPromotionImg">
                                            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </form>



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
<script src="../controllers/template.js"></script>
<script>
    //const projectID = $("#projectID").val();

    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //TEMPLATE_R1_PAGE_HOME

            tdR1Slogan: $('#tdR1Slogan').val(),
            tdR1HomePromotion1: $('#tdR1HomePromotion1').val(),
            tdR1SubHeadline1: $('#tdR1SubHeadline1').val(),
            tdR1MainHeadline1: $('#tdR1MainHeadline1').val(),
            tdR1SubHeadline2: $('#tdR1SubHeadline2').val(),
            tdR1HomeIntroduction: $('#tdR1HomeIntroduction').val(),          
            tdR1Featuredname1: $('#tdR1Featuredname1').val(),
            tdR1Featuredname2: $('#tdR1Featuredname2').val(),
            tdR1Featuredname3: $('#tdR1Featuredname3').val(),
            tdR1Featuredname4: $('#tdR1Featuredname4').val(),
            tdR1TestimonialText1: $('#tdR1TestimonialText1').val(),
            tdR1TestimonialName1: $('#tdR1TestimonialName1').val(),
            tdR1TestimonialText2: $('#tdR1TestimonialText2').val(),
            tdR1TestimonialName2: $('#tdR1TestimonialName2').val(),
            tdR1TestimonialName3: $('#tdR1TestimonialName3').val(),
            tdR1TestimonialText3: $('#tdR1TestimonialText3').val(),
            tdR1TestimonialName4: $('#tdR1TestimonialName4').val(),
            tdR1TestimonialText4: $('#tdR1TestimonialText4').val(),
            tdR1GGReview: $('#tdR1GGReview').val(),
            tdR1DeliveryRate: $('#tdR1DeliveryRate').val(),
            tdR1HomePromotion2: $('#tdR1HomePromotion2').val(),
            tdR1HomePromotion2Sub: $('#tdR1HomePromotion2Sub').val(),


        //TEMPLATE_R1_PAGE_ABOUT
            //tdR1HeadAboutImg: $('#tdR1HeadAboutImg').val(),
            tdR1AboutUSBody: $('#tdR1AboutUSBody').val(),
            tdR1AboutPromotion1: $('#tdR1AboutPromotion1').val(),
            tdR1AboutPromotion1Body: $('#tdR1AboutPromotion1Body').val(),
            //tdR1AboutPromotionImg: $('#tdR1AboutPromotionImg').val(),
            //tdR1StaffImg1: $('#tdR1StaffImg1').val(),
            //tdR1StaffImg2: $('#tdR1StaffImg2').val(),
            //tdR1StaffImg3: $('#tdR1StaffImg3').val(),
            //tdR1AboutFeaturedImg: $('#tdR2AboutFeaturedImg').val(),
            
        //TEMPLATE_R1_PAGE_CONTACT
            //tdR1HeadContactImg: $('#tdR1HeadContactImg').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val(),
            tdR1ContactHeadSub2: $('#tdR1ContactHeadSub2').val(),
            //tdR1ContactBgImg: $('#tdR1ContactBgImg').val(),
            tdR1ContactPromotion1: $('#tdR1ContactPromotion1').val(),
            tdR1ContactPromotion1sub: $('#tdR1ContactPromotion1sub').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val()

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