<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT * FROM `tb_project` WHERE `projectID` = ?;',$id)->fetchArray();
$projectID = $id;

$folderName = "upload/". $projectID . "-" . sanitizeFolderName($row["projectName"]).'/';

$pageDetail = $db->query('SELECT * FROM `templatepagedetails` WHERE `projectID` = ?;', $id)->fetchArray();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template submission : Restaurant 2</title>
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
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/res2.css">
<link rel="stylesheet" href="dist/css/newStyle.css">
<script src="../controllers/res2.js"></script>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mb-5">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo ($row['shopTypeID']==1)?"Restaurant":"Massage"; ?> <?php echo $row['selectedTemplate']; ?>
            (<a class="link-primary" href="https://restaurant2.localforyou.com/" target="_blank">Preview</a>)
        </li>
    </ol>
</nav>

<!-- Template Restaurant 2 -->
<div id="TemRes2">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res2Home" data-bs-toggle="tab" data-bs-target="#res2Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res2About" data-bs-toggle="tab" data-bs-target="#res2About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-res2Contact" data-bs-toggle="tab" data-bs-target="#res2Contact" type="button" role="tab" aria-selected="false">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane show active p-3" id="res2Home" role="tabpanel" aria-labelledby="nav-home-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-input-group form-check">
                            <input class="form-check-input mt-2" type="checkbox" value="1" id="defaultHome">
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
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeSliderSet1">1.Image Slider Set 1</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeSliderSet1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeSliderSet2">2.Image Slider Set 2</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeSliderSet2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeSliderSet3">3.Image Slider Set 3</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeSliderSet3">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                           
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionHeadline1">4.Promotion Headline #1</label>
                                        <input type="text" class="form-control" id="homePromotionHeadline1" placeholder="Promotion Headline #1">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionMessage1">5. Promotion Message #1</label>
                                        <input type="text" class="form-control" id="homePromotionMessage1" placeholder="Business Slogan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeIntroduceImg1">6. Introduce Image Carousel #1</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeIntroduceImg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeIntroduceImg2">Introduce Image Carousel #2</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeIntroduceImg2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomeIntroduceImg3">Introduce Image Carousel #3</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeIntroduceImg3">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>                                                    
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Home-1.jpg" class="img-fluid" alt="Restaurant 2 - Section 1">
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
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg1">7. Featured Menu Image #1</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg1">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg2">8. Featured Menu Image #2</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg2">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg3">9. Featured Menu Image #3</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg3">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg4">10. Featured Menu Image #4</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg4">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg5">11. Featured Menu Image #5</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg5">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg6">12. Featured Menu Image #6</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg6">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg7">13. Featured Menu Image #7</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg7">
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
                                    <div class="mb-3">
                                        <label for="formHomeFeaturedImg8">14. Featured Menu Image #8</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeFeaturedImg8">
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
                        </div>

                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2Home-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Home-2.jpg" class="img-fluid" alt="Restaurant 2 - Section 2">
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
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomePromotionBg1">15. Promotion Background Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomePromotionBg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomePromotionBg2">Promotion Background Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomePromotionBg2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formHomePromotionBg3">Promotion Background Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomePromotionBg3">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="promotionHeadline2">16. Promotion Headline #2</label>
                                        <input type="text" class="form-control" id="promotionHeadline2" placeholder="Promotion Headline #2">
                                    </div>
                                </div>
                            </div>
                            <!-- End Promotion -->

                            <!-- Reviews -->
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formHomeReviewsBg">17. Reviews Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeReviewsBg">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="reviewBgHome" src="../assets/img/default.png" alt="place">
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
                            <div class="row border rounded px-2 py-3 mb-3">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label for="testimonialText1">18. Testimonial #1</label>
                                                <textarea class="form-control" id="testimonialText1" rows="3" placeholder="Testimonial Text 1"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label for="testimonialName1">Name:</label>
                                                <input type="text" class="form-control" id="testimonialName1" placeholder="Testimonial Name 1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formHomeTestimonialImg1">Testimonial Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeTestimonialImg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row border rounded px-2 py-3 ">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label for="testimonialText1">19. Testimonial #2</label>
                                                <textarea class="form-control" id="testimonialText2" rows="3" placeholder="Testimonial Text 1"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="mb-3">
                                                <label for="testimonialName2">Name:</label>
                                                <input type="text" class="form-control" id="testimonialName2" placeholder="Testimonial Name 1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formHomeTestimonialImg1">Testimonial Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomeTestimonialImg2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—3</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2Home-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Home-3.jpg" class="img-fluid" alt="Restaurant 2 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-5"><!--Section 4-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—4</h5>
                                </div>
                            </div>
                    
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formHomePromotionImg2">20. Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHomePromotionImg2">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="promotionHeadline3">21. Promotion Headline #3</label>
                                        <input type="text" class="form-control" id="promotionHeadline3" placeholder="Testimonial Name 3">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="promotionMessage3">22. Promotion Message #3</label>
                                        <textarea class="form-control" id="promotionMessage3" rows="3" placeholder="Testimonial Text 3"></textarea>
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
                                    <a href="../assets/img/Res2Home-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Home-4.jpg" class="img-fluid" alt="Restaurant 2 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 4-->

                    <div class="row mb-5"><!--Section 5-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—5</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formFooterImg1">23. Footer Image #1</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooterImg1">
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
                                    <div class="mb-3">
                                        <label for="formFooterImg2">24. Footer Image #2</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooterImg2">
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
                                    <div class="mb-3">
                                        <label for="formFooterImg3">25. Footer Image #3</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooterImg3">
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
                                    <div class="mb-3">
                                        <label for="formFooterImg4">26. Footer Image #4</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooterImg4">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formFooterBg">27. Footer Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooterBg">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="footerBgImgHome" src="../assets/img/default.png" alt="place">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionHeadline4">28. Promotion Headline #4</label>
                                        <input type="text" class="form-control" id="homePromotionHeadline4" placeholder="Testimonial Name 4">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionMessage4">29. Promotion Message #4</label>
                                        <textarea class="form-control" id="homePromotionMessage4" rows="3" placeholder="Testimonial Text 4"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—5</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2Home-5.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Home-5.jpg" class="img-fluid" alt="Restaurant 2 - Section 5">
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

                    </div><!--end Section 5-->
                    
                    <div class="row">
                        <div class="col-6" style="text-align:left;">
                            <button type="button" class="btn btn-success" id="submitHomeBtn" onclick="submitHome();">Save Home Page Info.</button>
                            <?php if ($pageDetail["home"] == null) {  ?>
                                <small id="infoTextHome" class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextHome" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane p-3" id="res2About" role="tabpanel" aria-labelledby="nav-about-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-input-group form-check">
                            <input class="form-check-input mt-2" type="checkbox" value="1" id="defaultAbout">
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
                                    <div class="mb-3">
                                        <label for="formAboutHeaderImg">1. About Page Header Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutHeaderImg">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="aboutPageHeader" src="../assets/img/default.png" alt="place">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutUsBody">2. About Us Body</label>
                                        <textarea class="form-control" id="aboutUsBody" rows="3" placeholder=""></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionHeadline1">3. Promotion Headline #1</label>
                                        <input type="text" class="form-control" id="aboutPromotionHeadline1" placeholder="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="aboutPromotionMessage1">4. Promotion Message #1</label>
                                        <textarea class="form-control" id="aboutPromotionMessage1" rows="3" placeholder=""></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formAboutPromotionImg1">5. Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotionImg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="formAboutPromotionImg2">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotionImg2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formAboutPromotionImg3">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotionImg3">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formAboutPromotionImg4">Promotion Image Carousel</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutPromotionImg4">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2About-1.jpg" class="img-fluid" alt="Restaurant 2 - Section 1">
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
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formAboutStaffImg1">6. Staff Image #1</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutStaffImg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formAboutStaffImg2">7. Staff Image #2</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutStaffImg2">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="formAboutStaffImg3">8. Staff Image #3</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutStaffImg3">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formAboutReviewsBg">9. Reviews Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formAboutReviewsBg">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="aboutReviewBgImg" src="../assets/img/default.png" alt="place">
                                                    <input class="picname" type="hidden" value="">
                                                    <div class="d-flex flex-row gap-2">
                                                        <input type="file" class="file-input col-8" />
                                                    </div>
                                                    <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2About-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2About-2.jpg" class="img-fluid" alt="Restaurant 2 - Section 2">
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
                                <label for="bg1">10. Featured Dish Image</label>
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
                        </div>

                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—3</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res2About-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2About-3.jpg" class="img-fluid" alt="Restaurant 2 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5"><!--Notes-->
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label for="notes">Notes / Additional Comments</label>
                                        <textarea class="form-control" name="notes" id="notesAbout" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div><!--End Notes-->

                    </div><!--end Section 2-->

                    <div class="row">
                        <div class="col-6" style="text-align:left;">
                            <button type="button" class="btn btn-success" id="submitAboutBtn" onclick="submitAbout();">Save About Page Info.</button>
                            <?php if ($pageDetail["about"] == null) {  ?>
                                <small id="infoTextAbout" class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextAbout" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane p-3" id="res2Contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-input-group form-check">
                            <input class="form-check-input mt-2" type="checkbox" value="1" id="defaultContact">
                            <label for="defaultContact"><u>Use Default Template.</u></label>
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
                                        <label for="formContactHeaderImg">1. Contact Page Header Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formContactHeaderImg">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="contactPageHeader" src="../assets/img/default.png" alt="place">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactHeadline1">2. Contact Us Sub Headline #1</label>
                                        <input type="text" class="form-control" id="contactHeadline1" placeholder="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactMessage1">3. Contact Us Sub Headline #2</label>
                                        <textarea class="form-control" id="contactMessage1" rows="3" placeholder=""></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="formContactBackgroundImg1">4. Contact Page Background Image</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formContactBackgroundImg1">
                                            <div class="d-flex flex-column mb-3">
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">  
                                                    <img class="preview" id="contactPageBGImg" src="../assets/img/default.png" alt="place">
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
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionHeadline1">5. Promotion Headline</label>
                                        <input type="text" class="form-control" id="contactPromotionHeadline1" placeholder="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="contactPromotionMessage1">6. Promotion Sub Headline</label>
                                        <textarea class="form-control" id="contactPromotionMessage1" rows="3" placeholder=""></textarea>
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
                                    <a href="../assets/img/Res2Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res2Contact-1.jpg" class="img-fluid" alt="Restaurant 2 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5"><!--Notes-->
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label for="notesContact">Notes / Additional Comments</label>
                                        <textarea class="form-control" name="notes" id="notesContact" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div><!--End Notes-->

                    </div><!--end Section 1-->

                    <div class="row">
                        <div class="col-6" style="text-align:left;">
                            <button type="button" class="btn btn-success" id="submitContactBtn" onclick="submitContact();">Save Contact Page Info.</button>
                            <?php if ($pageDetail["contact"] == null) {  ?>
                                <small id="infoTextContact" class="text-danger ml-3"3>
                                    This page has never had a design template submitted.
                                </small>
                            <?php }else{ ?>
                                <small id="infoTextContact" class="text-success ml-3"3>
                                    Saved.
                                </small>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5" id="changepage">
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-res2Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-res2About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-res2Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
        </div>
    </div>
</div>
<!-- EndTemplate Restaurant 2 -->

<script src="dist/assets/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.fileupload.js"></script>
<script src="../controllers/template.js"></script>
<script>
    const max_uploads = 20;
    const multiUploadPrefix = 'album';
    let album_files = [];

    $(function() {
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

    const submitHome = () => {
        page = "home";
        payload = {
            "loginID": loginID,
            "page" : "Home",
            "defaultTemplate": ($('#defaultHome').prop('checked')) ? "Yes" : "No",
            "A1-01-slide1": $('#formHomeSliderSet1 .picname').val(),
            "A1-02-slide2": $('#formHomeSliderSet2 .picname').val(),
            "A1-03-slide3": $('#formHomeSliderSet3 .picname').val(),
            "A1-04-promoHead1": $('#homePromotionHeadline1').val(),
            "A1-05-promoMsg1": $('#homePromotionMessage1').val(),
            "A1-06-introImg1": $('#formHomeIntroduceImg1 .picname').val(),
            "A1-06-introImg2": $('#formHomeIntroduceImg2 .picname').val(),
            "A1-06-introImg3": $('#formHomeIntroduceImg3 .picname').val(),

            "A2-07-featureImg1": $('#formHomeFeaturedImg1 .picname').val(),
            "A2-08-featureImg2": $('#formHomeFeaturedImg2 .picname').val(),
            "A2-09-featureImg3": $('#formHomeFeaturedImg3 .picname').val(),
            "A2-10-featureImg4": $('#formHomeFeaturedImg4 .picname').val(),
            "A2-11-featureImg5": $('#formHomeFeaturedImg5 .picname').val(),
            "A2-12-featureImg6": $('#formHomeFeaturedImg6 .picname').val(),
            "A2-13-featureImg7": $('#formHomeFeaturedImg7 .picname').val(),
            "A2-14-featureImg8": $('#formHomeFeaturedImg8 .picname').val(),

            "A3-15-promoBg1": $('#formHomePromotionBg1 .picname').val(),
            "A3-15-promoBg2": $('#formHomePromotionBg2 .picname').val(),
            "A3-15-promoBg3": $('#formHomePromotionBg3 .picname').val(),
            "A3-16-promoHead2": $('#promotionHeadline2').val(),
            "A3-17-reviewsBg": $('#formHomeReviewsBg .picname').val(),
            "A3-18-tesTxt1": $('#testimonialText1').val(),
            "A3-18-tesName1": $('#testimonialName1').val(),
            "A3-18-tesImg1": $('#formHomeTestimonialImg1 .picname').val(),
            "A3-19-tesTxt2": $('#testimonialText2').val(),
            "A3-19-tesName2": $('#testimonialName2').val(),
            "A3-19-tesImg2": $('#formHomeTestimonialImg2 .picname').val(),

            "A4-20-promoImg2": $('#formHomePromotionImg2 .picname').val(),
            "A4-21-promoHead3": $('#promotionHeadline3').val(),
            "A4-22-promoMsg3": $('#promotionMessage3').val(),
            "A4-23-footImg1": $('#formFooterImg1 .picname').val(),
            "A4-24-footImg2": $('#formFooterImg2 .picname').val(),
            "A4-25-footImg3": $('#formFooterImg3 .picname').val(),
            "A4-26-footImg4": $('#formFooterImg4 .picname').val(),
            "A4-27-footBg": $('#formFooterBg .picname').val(),
            "A4-28-promoHead4": $('#homePromotionHeadline4').val(),
            "A4-29-promoMsg4": $('#homePromotionMessage4').val(),
            "notes": $('#notesHome').val(),
            "token": Math.random()
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            "loginID": loginID,
            "page" : "About",
            "defaultTemplate": ($('#defaultAbout').prop('checked')) ? "Yes" : "No",
            "B1-01-headImg": $('#formAboutHeaderImg .picname').val(),
            "B1-02-bodyTxt": $('#aboutUsBody').val(),
            "B1-03-promoHead1": $('#aboutPromotionHeadline1').val(),
            "B1-04-promoMsg1": $('#aboutPromotionMessage1').val(),
            "B1-05-promoImg1": $('#formAboutPromotionImg1 .picname').val(),
            "B1-05-promoImg2": $('#formAboutPromotionImg2 .picname').val(),
            "B1-05-promoImg3": $('#formAboutPromotionImg3 .picname').val(),
            "B1-05-promoImg4": $('#formAboutPromotionImg4 .picname').val(),

            "B2-06-staffImg1": $('#formAboutStaffImg1 .picname').val(),
            "B2-07-staffImg2": $('#formAboutStaffImg2 .picname').val(),
            "B2-08-staffImg3": $('#formAboutStaffImg3 .picname').val(),
            "B2-09-reviewBg": $('#formAboutReviewsBg .picname').val(),
            
            "B3-10-dishimg": JSON.stringify(album_files),
            "notes": $('#notesAbout').val(),

            token: Math.random()
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            "loginID": loginID,
            "page" : "Contact",
            "defaultTemplate": ($('#defaultContact').prop('checked')) ? "Yes" : "No",
            "C1-01-headImg": $('#formContactHeaderImg .picname').val(),
            "C1-02-head1": $('#contactHeadline1').val(),
            "C1-03-msg1": $('#contactMessage1').val(),
            "C1-04-bgImg1": $('#formContactBackgroundImg1 .picname').val(),
            "C1-05-promoHead1": $('#contactPromotionHeadline1').val(),
            "C1-06-promoMsg1": $('#contactPromotionMessage1').val(),
            "notes": $('#notesContact').val(),

            token: Math.random()
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitContact
</script>