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
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/res1.css">
<script src="../controllers/res1.js"></script>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mb-5">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo $row['typeName']; ?> <?php echo $row['selectedTemplate']; ?>
            (<a class="link-primary" href="https://restaurant1.localforyou.com/" target="_blank">Preview</a>)
        </li>
    </ol>
</nav>

<!-- Template Restaurant 1 -->
<div id="Temres1" class="container-fluid">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false">About</button>
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
                                    <div class="mb-3">
                                        <label for="inputSlogan" class="form-label">1. Business Slogan</label>
                                        <input type="text" class="form-control" id="inputSlogan" placeholder="Business Slogan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="inputIntroduction1" class="form-label">2. Information or Promotion</label>
                                        <textarea class="form-control" id="inputIntroduction1" rows="3" placeholder="Information or Promotion"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="bg1">
                                        <div class="d-flex flex-column mb-3">
                                            <label for="bg1">3. Header Background Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="tdR1IntroductionSubHeadline1" class="form-label">4. Home Page Introduction Sub Headline-1</label>
                                        <input type="text" class="form-control" id="tdR1IntroductionSubHeadline1" placeholder="Introduction Sub Headline">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="tdR1IntroductionMainHeadline" class="form-label">5. Home Page Introduction Main Headline</label>
                                        <input type="text" class="form-control" id="tdR1IntroductionMainHeadline" placeholder="Introduction Main Headline">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="tdR1IntroductionSubHeadline2" class="form-label">6. Home Page Introduction Sub Headline-2</label>
                                        <input type="text" class="form-control" id="tdR1IntroductionSubHeadline2" placeholder="Introduction Sub Headline">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="tdR1IntroductionBody" class="form-label">7. Home Page Introduction Body</label>
                                        <textarea class="form-control" id="tdR1IntroductionBody" rows="5" placeholder="Introduction Body"></textarea>
                                    </div>
                                </div>
                            </div>
                            <label for="bg1">8. Home Page Introduction Image</label>
                            <div class="row mb-3 ">
                                <div class="col-6">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formIntroductionImage1">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formIntroductionImage2">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formIntroductionImage3">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formIntroductionImage4">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Restaurant 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->

                    <div class="row mb-5"><!--Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—2</small>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish1">
                                            <div class="d-flex flex-column">
                                                <label for="textFeaturedDish1">9.Featured Dish Image #1</label>
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
                                                                <input type="text" class="form-control" id="textFeaturedDish1" placeholder="Detail...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish2">
                                            <div class="d-flex flex-column">
                                                <label for="textFeaturedDish2">10.Featured Dish Image #2</label>
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
                                                                <input type="text" class="form-control" id="textFeaturedDish2" placeholder="Detail...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish3">
                                            <div class="d-flex flex-column">
                                                <label for="textFeaturedDish3">11.Featured Dish Image #3</label>
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
                                                                <input type="text" class="form-control" id="textFeaturedDish3" placeholder="Detail...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDish4">
                                            <div class="d-flex flex-column">
                                                <label for="textFeaturedDish4">12.Featured Dish Image #4</label>
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
                                                                <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Detail...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3 border rounded p-3">
                                <div class="row mb-3">
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial1">
                                            <div class="d-flex flex-column">
                                                <label for="textTrstimonial1">13. Testimonial #1</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <textarea class="form-control" id="textTrstimonial1" rows="3" placeholder="Detail..."></textarea>
                                                            <div>
                                                                <input type="text" class="form-control" id="textTrstimonial1" placeholder="Menu...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial2">
                                            <div class="d-flex flex-column">
                                                <label for="textTrstimonial2">14. Testimonial #2</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <textarea class="form-control" id="textTrstimonial2" rows="3" placeholder="Detail..."></textarea>
                                                            <div>
                                                                <input type="text" class="form-control" id="textTrstimonial2" placeholder="Menu...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>  
                                <div class="row">
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial3">
                                            <div class="d-flex flex-column">
                                                <label for="textTrstimonial3">15. Testimonial #3</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <textarea class="form-control" id="textTrstimonial3" rows="3" placeholder="Detail..."></textarea>
                                                            <div>
                                                                <input type="text" class="form-control" id="textTrstimonial3" placeholder="Menu...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial4">
                                            <div class="d-flex flex-column">
                                                <label for="textTrstimonial4">16. Testimonial #4</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <textarea class="form-control" id="textTrstimonial4" rows="3" placeholder="Detail..."></textarea>
                                                            <div>
                                                                <input type="text" class="form-control" id="textTrstimonial4" placeholder="Menu...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>    
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="ggLinkReview" class="form-label">17. Google Link for Review</label>
                                        <input type="text" class="form-control" id="ggLinkReview" placeholder="https://maps.app.goo.gl/HLAwcpZZHeKtBFuR8">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="ggLinkWrite" class="form-label">18. Write Us A Review</label>
                                        <input type="text" class="form-control" id="ggLinkWrite" placeholder="https://g.co/kgs/65id4BT">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-2.jpg"" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-2.jpg" class="img-fluid" alt="Restaurant 1 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->

                    <div class="row mb-5"><!--Section 3-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—3</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formDeliveryMap">
                                        <div class="d-flex flex-column mb-3">
                                            <label for="">19. Delivery Map Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="deliveryDetail" class="form-label">20. Delivery Detail</label>
                                        <textarea class="form-control" id="deliveryDetail" rows="5" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="inputIntroduction1" class="form-label">21. Delivery Rates with Locations</label>
                                        <textarea class="form-control" id="inputIntroduction1" rows="5" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formRromotionArea">
                                        <div class="d-flex flex-column mb-3">
                                            <label for="bg1">22. Promotion Area Background Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="promotionHeadline" class="form-label">23. Promotion Headline</label>
                                        <input type="text" class="form-control" id="promotionHeadline" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="promotionSubHeadline" class="form-label">24. Promotion Sub Headline</label>
                                        <input type="text" class="form-control" id="promotionSubHeadline" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—3</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-3.jpg" class="img-fluid" alt="Restaurant 1 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-3"><!--Section 4-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—4</small>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                            <label for="bg1" class="form-label">25. Carousel Images</label>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel1">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel2">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel3">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formCarousel4">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-column gap-2">
                                                        <input type="file" class="file-input">
                                                        <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—4</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-4.jpg" class="img-fluid" alt="Restaurant 1 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 4-->
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
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutbg1">
                                        <div class="d-flex flex-column mb-3">
                                            <label for="bg1">26. About Page Header Background Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutBody" class="form-label">27. About Us Body</label>
                                        <textarea class="form-control" id="aboutBody" rows="4" placeholder="Detail..."></textarea>
                                    </div>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-1.jpg" class="img-fluid" alt="Restaurant 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->

                    <div class="row mb-5"><!--Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionHeadline" class="form-label">28. Promotion Headline</label>
                                        <input type="text" class="form-control" id="aboutPromotionHeadline" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionBody" class="form-label">29. Promotion Body</label>
                                        <textarea class="form-control" id="aboutPromotionBody" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotion">
                                        <div class="d-flex flex-column">
                                            <label for="textAboutPromotion">30. Promotion Image</label>
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formbgAboutBackground">
                                        <div class="d-flex flex-column">
                                            <label for="textbgAboutBackground">31. Background Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout1" class="form-label">32. Callout#1</label>
                                        <input type="text" class="form-control" id="aboutCallout1" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout2" class="form-label">33. Callout#2</label>
                                        <input type="text" class="form-control" id="aboutCallout2" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout3" class="form-label">34. Callout#3</label>
                                        <input type="text" class="form-control" id="aboutCallout3" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout4" class="form-label">35. Callout#4</label>
                                        <input type="text" class="form-control" id="aboutCallout4" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1About-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-2.jpg" class="img-fluid" alt="Restaurant 1 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->

                    <div class="row mb-5"><!--Section 3-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—3</small>
                                </div>
                            </div>
                            <label for="bg1">36. Featured Dish Image</label>
                            <div class="row mb-3 ">
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg1">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg2">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg3">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row mb-3 ">
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg4">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg5">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-4">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutFeaturedDishImg6">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <img class="preview" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
                                                    <input type="file" class="file-input">
                                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
        
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—3</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1About-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-3.jpg" class="img-fluid" alt="Restaurant 1 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->
                </div><!--end tab-About-->

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
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formbgContactHeadBackground">
                                        <div class="d-flex flex-column">
                                            <label for="formbgContactHeadBackground">37. Contact Page Header Background Image</label>
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formbgContactBackground">
                                        <div class="d-flex flex-column">
                                            <label for="formbgContactBackground">38. Contact Page Background Image</label>
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactSubHead1" class="form-label">39. Contact Us Sub Headline #1</label>
                                        <input type="text" class="form-control" id="contactSubHead1" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactSubHead2" class="form-label">40. Contact Us Sub Headline #2</label>
                                        <textarea class="form-control" id="contactSubHead2" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionHeadline" class="form-label">41. Promotion Headline</label>
                                        <textarea class="form-control" id="contactPromotionHeadline" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionSubHeadline" class="form-label">42. Promotion Sub Headline</label>
                                        <textarea class="form-control" id="contactPromotionSubHeadline" rows="4" placeholder="Detail..."></textarea>
                                    </div>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Restaurant 1 - Section 1">
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
        <div class="col-6" style="text-align:left;">
            <button type="button" class="btn btn-success" id="submitBtn">save</button>
        </div>
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-res1Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-res1About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-res1Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
        </div>
    </div>
</div>
<!-- End Template Restaurant 1 -->

<script src="../controllers/template.js"></script>
<script>
    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //TEMPLATE_R1_PAGE_HOME
            tdSlogan: $('#tdSlogan').val(),
            //tdR1HomeSlider1: $('#tdR1HomeSlider1').val(),
            //tdR1HomeSlider2: $('#tdR1HomeSlider2').val(),
            //tdR1HomeSlider3: $('#tdR1HomeSlider3').val(),
            tdR1Delivery: $('#tdR1Delivery').val(),
            tdR1HomePromotion1: $('#tdR1HomePromotion1').val(),
            tdR1HomePromotion1Body: $('#tdR1HomePromotion1Body').val(),
            //tdR1Carousel: $('#tdR1Carousel').val(),
            //tdR1FeaturedImg: $('#tdR1FeaturedImg').val(),
            //tdR1PromotionImg2: $('#tdR1PromotionImg2').val(),
            tdR1HomePromotion2: $('#tdR1HomePromotion2').val(),
            //tdR1ReviewBg: $('#tdR1ReviewBg').val(),
            tdR1TestimonialText1: $('#tdR1TestimonialText1').val(),
            tdR1TestimonialName1: $('#tdR1TestimonialName1').val(),
            //tdR1TestimonialImg1: $('#tdR1TestimonialImg1').val(),
            tdR1TestimonialText2: $('#tdR1TestimonialText1').val(),
            tdR1TestimonialName2: $('#tdR1TestimonialName1').val(),
            //tdR1TestimonialImg2: $('#tdR1TestimonialImg1').val(),
            tdR1TestimonialText3: $('#tdR1TestimonialText1').val(),
            tdR1TestimonialName3: $('#tdR1TestimonialName1').val(),
            //tdR1TestimonialImg3: $('#tdR1TestimonialImg1').val(),
            //tdR1PromotionImg3: $('#tdR1PromotionImg3').val(),
            tdR1HomePromotion3: $('#tdR1HomePromotion3').val(),
            tdR1HomePromotion3Body: $('#tdR1HomePromotion3Body').val(),
            //tdR1FooterImg1: $('#tdR1FooterImg1').val(),
            //tdR1FooterImg2: $('#tdR1FooterImg2').val(),
            //tdR1FooterImg3: $('#tdR1FooterImg3').val(),
            //tdR1FooterImg4: $('#tdR1FooterImg4').val(),
            tdR1HomePromotion4: $('#tdR1HomePromotion4').val(),
            tdR1HomePromotion4Body: $('#tdR1HomePromotion4Body').val(),
            //tdR1FooterBgImg: $('#tdR1FooterBgImg').val(),

        //TEMPLATE_R2_PAGE_ABOUT
            //tdR1HeadAboutImg: $('#tdR1HeadAboutImg').val(),
            tdR1AboutUSBody: $('#tdR1AboutUSBody').val(),
            tdR1AboutPromotion1: $('#tdR1AboutPromotion1').val(),
            tdR1AboutPromotion1Body: $('#tdR1AboutPromotion1Body').val(),
            //tdR1AboutPromotionImg: $('#tdR1AboutPromotionImg').val(),
            //tdR1StaffImg1: $('#tdR1StaffImg1').val(),
            //tdR1StaffImg2: $('#tdR1StaffImg2').val(),
            //tdR1StaffImg3: $('#tdR1StaffImg3').val(),
            //tdR1AboutFeaturedImg: $('#tdR1AboutFeaturedImg').val(),
            
        //TEMPLATE_R2_PAGE_CONTACT
            //tdR1HeadContactImg: $('#tdR1HeadContactImg').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val(),
            tdR1ContactHeadSub2: $('#tdR1ContactHeadSub2').val(),
            //tdR1ContactBgImg: $('#tdR1ContactBgImg').val(),
            tdR1ContactPromotion1: $('#tdR1ContactPromotion1').val(),
            tdR1ContactPromotion1sub: $('#tdR1ContactPromotion1sub').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val(),
            tdR1ContactHeadSub1: $('#tdR1ContactHeadSub1').val(),

        };

        console.log("Payload", payload);

        const callAjax = $.ajax({
            url: "../models/ajaxRes1.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

    });
</script>