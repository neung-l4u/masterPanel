<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT * FROM `tb_project` WHERE projectID = ?;',$id)->fetchArray();
$projectID = $id;

$folderName = "upload/". $projectID . "-" . sanitizeFolderName($row["projectName"]).'/';

$pageDetail = $db->query('SELECT * FROM `templatepagedetails` WHERE `projectID` = ?;', $id)->fetchArray();
?>

<link rel="stylesheet" href="../assets/css/template.css">
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/res1.css">
<link rel="stylesheet" href="dist/css/newStyle.css">
<script src="../controllers/res1.js"></script>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mb-5">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo ($row['shopTypeID']==1)?"Restaurant":"Massage"; ?> <?php echo $row['selectedTemplate']; ?>
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

                    <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultHome">
                            <label for="defaultHome"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
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
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formBGHeader">
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
                                <div class="col">
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
                                <div class="col">
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—2</h5>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="row mb-3">
                                    <div class="col">
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
                                    <div class="col">
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
                                                            <textarea class="form-control" id="textareaTrstimonial1" rows="3" placeholder="Detail..."></textarea>
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
                                                            <textarea class="form-control" id="textareaTrstimonial2" rows="3" placeholder="Detail..."></textarea>
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
                                                            <textarea class="form-control" id="textareaTrstimonial3" rows="3" placeholder="Detail..."></textarea>
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
                                                            <textarea class="form-control" id="textareaTrstimonial4" rows="3" placeholder="Detail..."></textarea>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—3</h5>
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
                                        <label for="deliveryRateDetail" class="form-label">21. Delivery Rates with Locations</label>
                                        <textarea class="form-control" id="deliveryRateDetail" rows="5" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotionArea">
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—3</h5>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—4</h5>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                            <label for="bg1" class="form-label">25. Carousel Images</label>
                                <div class="row mb-3">
                                    <div class="col">
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
                                    <div class="col">
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—4</h5>
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

                        <div class="row mb-5"><!--Notes-->
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label for="notes">Notes / Additional Comments</label>
                                        <textarea class="form-control" name="notes" id="notesHome" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div><!--End Notes-->

                    </div><!--end Section 4-->

                    <div class="row">
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="cmdHomeSubmit" onclick="submitHome();">Save Home Page Info.</button>
                            <?php if ($pageDetail["home"] == null) {  ?>
                                <small class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextHome" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                </div><!--end tab-Home-->

                <!-- fixed Structure -->
                <div class="tab-pane p-3" id="res1About" role="tabpanel" aria-labelledby="nav-about-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultAbout">
                            <label for="defaultAbout"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutbg1">
                                        <div class="d-flex flex-column mb-3">
                                            <label for="bg1">1. About Page Header Background Image</label>
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
                                        <label for="aboutBody" class="form-label">2. About Us Body</label>
                                        <textarea class="form-control" id="aboutBody" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionHeadline" class="form-label">3. Promotion Headline</label>
                                        <input type="text" class="form-control" id="aboutPromotionHeadline" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionBody" class="form-label">4. Promotion Body</label>
                                        <textarea class="form-control" id="aboutPromotionBody" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotion">
                                        <div class="d-flex flex-column">
                                            <label for="textAboutPromotion">5. Promotion Image</label>
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
                                            <label for="textbgAboutBackground">6. Background Image</label>
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
                                        <label for="aboutCallout1" class="form-label">7. Callout#1</label>
                                        <input type="text" class="form-control" id="aboutCallout1" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout2" class="form-label">8. Callout#2</label>
                                        <input type="text" class="form-control" id="aboutCallout2" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout3" class="form-label">9. Callout#3</label>
                                        <input type="text" class="form-control" id="aboutCallout3" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutCallout4" class="form-label">10. Callout#4</label>
                                        <input type="text" class="form-control" id="aboutCallout4" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—3</h5>
                                </div>
                            </div>
                            <div class="col border rounded p-3">
                                <label for="bg1">11. Featured Dish Image</label>
                                <div class="mb-3 border rounded p-3 multiUpload">
                                <!-- This area will show the uploaded files -->
                                <div class="row">
                                    <div id="uploaded_images">
                                    </div>
                                </div>
                                <br>
                                <br>
                                <div id="select_file">
                                    <div class="form-group">
                                    
                                    <input id="fileupload" type="file" name="files" accept="image/x-png, image/gif, image/jpeg" >
                                        <small id="warnMaxText" class="text-info">Limit </small>
                                        <br>
                                        <br>
                                        <!-- The global progress bar -->
                                        <div id="progress" class="progress">
                                            <div class="progress-bar progress-bar-success"></div>
                                        </div>
                                        <!-- The container for the uploaded files -->
                                        <div id="files" class="files"></div>
                                        <label for="uploaded_file_name"></label>
                                        <input type="text" name="uploaded_file_name" id="uploaded_file_name" hidden>
                                    </div>
                                </div>
                                <small id="warnMaxFile" class="text-danger">You have uploaded the maximum number of files.</small>
                            </div>    
                        </div>
        
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—3</h5>
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

                        <div class="row mt-3 mb-5"><!--Notes-->
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label for="notesAbout">Notes / Additional Comments</label>
                                        <textarea class="form-control" name="notes" id="notesAbout" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div><!--End Notes-->

                    </div><!--end Section 3-->

                    <div class="row">
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="cmdAboutSubmit" onclick="submitAbout();">Save About Page Info.</button>
                            <?php if ($pageDetail["about"] == null) {  ?>
                                <small class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextAbout" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                </div><!--end tab-About-->

                <div class="tab-pane p-3" id="res1Contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultContact">
                            <label for="defaultContact"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-3"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formbgContactHeadBackground">
                                        <div class="d-flex flex-column">
                                            <label for="formbgContactHeadBackground">1. Contact Page Header Background Image</label>
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
                                            <label for="formbgContactBackground">2. Contact Page Background Image</label>
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
                                        <label for="contactSubHead1" class="form-label">3. Contact Us Sub Headline #1</label>
                                        <input type="text" class="form-control" id="contactSubHead1" placeholder="Detail...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactSubHead2" class="form-label">4. Contact Us Sub Headline #2</label>
                                        <textarea class="form-control" id="contactSubHead2" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionHeadline" class="form-label">5. Promotion Headline</label>
                                        <textarea class="form-control" id="contactPromotionHeadline" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionSubHeadline" class="form-label">6. Promotion Sub Headline</label>
                                        <textarea class="form-control" id="contactPromotionSubHeadline" rows="4" placeholder="Detail..."></textarea>
                                    </div>
                                </div>
                            </div>
                            
                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
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
                    </div><!--end Section 1-->

                    <div class="row mb-5"><!--Notes-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <label for="notesAbout">Notes / Additional Comments</label>
                                    <textarea class="form-control" name="notes" id="notesAbout" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div><!--End Notes-->

                    <div class="row">
                        <div class="mt-3">
                            <button type="button" class="btn btn-success" id="cmdContactSubmit" onclick="submitContact();">Save Contact Page Info.</button>
                            <?php if ($pageDetail["contact"] == null) {  ?>
                                <small class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextContact" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
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
<script src="dist/assets/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.fileupload.js"></script>
<script src="../controllers/template.js"></script>
<script>
        const max_uploads = 20;
    const multiUploadPrefix = 'album';
    let album_files = [];

    $(function() {
        setAllPageStatus(); //in template.js
        $('#warnMaxFile').hide();
        $('#warnMaxText').text('You can upload up to ' + max_uploads + ' files.');

        'use strict';

        // Change this to the location of your server-side upload handler:
        const url = '../multiUpload.php?projectID=<?php echo $id; ?>&folderPath=<?php echo $folderName; ?>&prefix=' + multiUploadPrefix;

        $('#fileupload').fileupload({
            url: url,
            dataType: 'html',
            done: function (e, data) {

                if(data['result'] === 'FAILED'){
                    alert('Invalid File');
                }else{
                    $('#uploaded_file_name').val(data['result']);
                    $('#uploaded_images').append('<div class="uploaded_image"> <input type="text" value="'+data['result']+'" name="uploaded_image_name[]" id="uploaded_image_name" hidden> <img src="../<?php echo $folderName; ?>'+data['result']+'" /> <a href="#uploaded_images" class="img_rmv btn btn-danger"><i class="fa fa-times-circle" style="font-size:48px;color:red"></i></a> </div>');
                    album_files.push(data['result']);

                    if($('.uploaded_image').length >= max_uploads){
                        $('#select_file').hide();
                        $('#warnMaxFile').show();
                    }else{
                        $('#warnMaxFile').hide();
                        $('#select_file').show();
                    }
                }

                $('#progress .progress-bar').css(
                    'width',
                    0 + '%'
                );

                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo('#files');
                });

            },
            progressall: function (e, data) {
                let progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });//ready

    $( "#uploaded_images" ).on( "click", ".img_rmv", function() {
        $(this).parent().remove();
        if($('.uploaded_image').length >= max_uploads){
            $('#select_file').hide();
            $('#warnMaxFile').show();
        }else{
            $('#select_file').show();
            $('#warnMaxFile').hide();
        }
    });


$(function() {
        setAllPageStatus(); //in template.js
    });//ready

    const submitHome = () => {
        page = "home";
        payload = {
            //TEMPLATE_R1_PAGE_HOME
            "loginID": loginID,
            "page" : "Home",
            "defaultTemplate": ($('#defaultHome').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesHome').val(),
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
        saveToDB(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            //TEMPLATE_R1_PAGE_ABOUT
            "loginID": loginID,
            "page" : "About",
            "defaultTemplate": ($('#defaultAbout').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesAbout').val(),
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
            "B2-11-FeaturedDishImg" :JSON.stringify(album_files),
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            //TEMPLATE_R1_PAGE_CONTACT    
            "loginID": loginID,
            "page" : "Contact",
            "defaultTemplate": ($('#defaultContact').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesContact').val(),
            "C1-1-HeadBG": $('#formbgContactHeadBackground .picname').val(),
            "C1-2-FormBG" : $('#formbgContactBackground .picname').val(),
            "C1-3-SubHead1" : $('#contactSubHead1').val(),
            "C1-4-SubHead2" : $('#contactSubHead2').val(),
            "C1-5-PromoHeadline" : $('#contactPromotionHeadline').val(),
            "C1-6-PromoSubHeadline" : $('#contactPromotionSubHeadline').val(),
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitContact

</script>