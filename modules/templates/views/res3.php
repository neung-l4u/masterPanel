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
<link rel="stylesheet" href="../assets/css/res3.css">
<script src="../controllers/res3.js"></script>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mb-5">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo $row['typeName']; ?> <?php echo $row['selectedTemplate']; ?>
            (<a class="link-primary" href="https://restaurant3.localforyou.com/" target="_blank">Preview</a>)
        </li>
    </ol>
</nav>

<!-- Template Restaurant 3 -->
<div id="TemRes3" class="container-fluid">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res3Home" data-bs-toggle="tab" data-bs-target="#res3Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res3About" data-bs-toggle="tab" data-bs-target="#res3About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-res3Contact" data-bs-toggle="tab" data-bs-target="#res3Contact" type="button" role="tab" aria-selected="false">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane show active p-3" id="res3Home" role="tabpanel" aria-labelledby="nav-home-tab">

                    <div class="row mb-5"><!-- Home Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                                        <label for="bg1Hex">1. BG Color</label>
                                        <input type="color" onchange="setHex(this.value,'bg1HexCode');"  id="bg1Hex" value="#000000">
                                        <span id="bg1HexCode" class="codeHex">#000000</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="inputSlogan" class="form-label">2. Slogan</label>
                                        <input type="text" class="form-control" id="inputSlogan" placeholder="Business Slogan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="inputIntroduction1" class="form-label">3. Introduction #1</label>
                                        <textarea class="form-control" id="inputIntroduction1" rows="3" placeholder="Business Introduction #1"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="bg1">
                                        <div class="d-flex flex-column">
                                            <label for="bg1">4. BG Image 2201x1068 px.</label>
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
                                        <label for="inputIntroduction2" class="form-label">5. Introduction #2</label>
                                        <textarea class="form-control" id="inputIntroduction2" rows="3" placeholder="Business Introduction #2"></textarea>
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
                                    <a href="../assets/img/Res3Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Home-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->

                    <div class="row mb-5"><!-- Home Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—2</small>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                    <h6>promotion1 #1</h6>
                                    <label for="textTestimonial2" class="form-label">6. Opening Hours</label>
                                    <textarea class="form-control" id="textTestimonial2" rows="3" placeholder="promotion #1"></textarea>
                                    <label for="textFeaturedDish4">7. Promotion Message #1</label>
                                    <input type="text" class="form-control" id="textFeaturedDish4" placeholder="promotion #1">
                                </div>
                                <div class="col">
                                    <label for="textFeaturedDish4">8. Promotion Image #1</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="promotion1">
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
                                    </form>
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
                                                                <input type="text" class="form-control" id="textFeaturedDish1" placeholder="Featured Dish Name #1">
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
                                                                <input type="text" class="form-control" id="textFeaturedDish2" placeholder="Featured Dish Name #2">
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
                                                                <input type="text" class="form-control" id="textFeaturedDish3" placeholder="Featured Dish Name #3">
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
                                                                <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Featured Dish Name #4">
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
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3Home-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Home-2.jpg" class="img-fluid" alt="Restaurant 3 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->

                    <div class="row mb-5"><!-- Home Section 3-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—3</small>
                                </div>
                            </div>
                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                        <h6>13. Testimonial #1</h6>
                                        <label for="textFeaturedDish4">Name</label>
                                        <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Testimonial #1">
                                        <label for="textTestimonial1" class="form-label">Text</label>
                                        <textarea class="form-control" id="textTestimonial1" rows="3" placeholder="Testimonial #1"></textarea>
                                </div>
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial1">
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
                                    </form>
                                </div>
                            </div>
                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                    <h6>14. Testimonial #2</h6>
                                    <label for="textFeaturedDish4">Name</label>
                                    <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Testimonial #2">
                                    <label for="textTestimonial2" class="form-label">Text</label>
                                    <textarea class="form-control" id="textTestimonial2" rows="3" placeholder="Testimonial #2"></textarea>
                                </div>
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTestimonial2">
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
                                    </form>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                    <label for="textPromotion2">15. Promotion Image #2</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formPromotion2">
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
                                    </form>
                                </div>
                                <div class="col">
                                    <h6>Promotion #2</h6>
                                    <label for="textFeaturedDish4">16. Heading</label>
                                    <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Testimonial #2">
                                    <label for="textTestimonial2" class="form-label">17. Detail</label>
                                    <textarea class="form-control" id="textTestimonial2" rows="3" placeholder="Testimonial #2"></textarea>
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
                                    <a href="../assets/img/Res3Home-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Home-3.jpg" class="img-fluid" alt="Restaurant 3 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-3"><!-- Home Section 4-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—4</small>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooter1">
                                            <div class="d-flex flex-column">
                                                <label for="picFooter1">18.Footer Image #1</label>
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
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooter2">
                                            <div class="d-flex flex-column">
                                                <label for="picFooter2">19.Footer Image #2</label>
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
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooter3">
                                            <div class="d-flex flex-column">
                                                <label for="picFooter3">20.Footer Image #3</label>
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
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooter4">
                                            <div class="d-flex flex-column">
                                                <label for="picFooter4">21.Footer Image #4</label>
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
                                        <div class="mb-3">
                                            <label for="inputMapURL" class="form-label">22. MapURL</label>
                                            <textarea class="form-control" id="inputMapURL" rows="3" placeholder="Map URL"></textarea>
                                        </div>
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
                                    <a href="../assets/img/Res3Home-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Home-4.jpg" class="img-fluid" alt="Restaurant 3 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 4-->

                    <div class="row"><!-- Home BTN -->
                        <div class="col">
                            <button type="button" class="btn btn-success" id="submitContactBtn" onclick="submitHome();">Submit page Home info.</button>
                            <small id="infoTextHome" class="text-danger ml-3">This page has never had a design template submitted.</small>
                        </div>
                    </div>

                </div><!--end tab-Home-->

                <!-- fixed Structure -->
                <div class="tab-pane p-3" id="res3About" role="tabpanel" aria-labelledby="nav-about-tab">

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>

                            <div class="border rounded p-5">
                                <div class="row mt-3">
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="aboutHeaderBG">
                                            <label for="bg1">23. About Page Header BG Image 2201x1068 px.</label>
                                            <div class="d-flex flex-column gap-2">
                                                <img class="preview" id="aboutHeaderBG" src="../assets/img/default.png" alt="place">
                                                <input class="picname" type="hidden" value="">
                                                <div class="d-flex flex-column gap-2">
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
                                        <div class="mb-3">
                                            <label for="inputTagline" class="form-label">24. Tag Line</label>
                                            <input type="text" class="form-control" id="inputTagline" placeholder="Business Tagline">
                                        </div>
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
                                    <a href="../assets/img/Res3About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3About-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5"><!--Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—2</small>
                                </div>
                            </div>
                            <div class="border rounded mb-3 p-5">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputHighlightSlogan" class="form-label">25. Highlight Slogan</label>
                                            <input type="text" class="form-control" id="inputHighlightSlogan" placeholder="Highlight Slogan">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutSlogan" class="form-label">26. Slogan</label>
                                            <input type="text" class="form-control" id="inputAboutSlogan" placeholder="About Slogan">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutHeadline" class="form-label">27. About Us Headline</label>
                                            <textarea class="form-control" id="inputAboutHeadline" rows="3" placeholder="About Us Headline"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutDetail" class="form-label">28. About Us Detail</label>
                                            <textarea class="form-control" id="inputAboutDetail" rows="3" placeholder="About Us Detail"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div><!--border rounded-->

                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3About-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3About-2.jpg" class="img-fluid" alt="Restaurant 3 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—3</small>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="textPromotion2">29. Highlight dish</label>
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formHighlightDish">
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
                                        </form>
                                    </div>
                                    <div class="col">
                                        <h6>Text</h6>
                                        <label for="textHighlightTitle">7. Highlight Title</label>
                                        <input type="text" class="form-control" id="textHighlightTitle" placeholder="Highlight Title">
                                        <label for="textHighlightSubtitle" class="form-label">8. Highlight Sub</label>
                                        <textarea class="form-control" id="textHighlightSubtitle" rows="3" placeholder="Highlight Subtitle"></textarea>
                                        <label for="textHighlightDetail" class="form-label">9. Highlight Detail</label>
                                        <textarea class="form-control" id="textHighlightDetail" rows="3" placeholder="Highlight Detail"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—3</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3About-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3About-3.jpg" class="img-fluid" alt="Restaurant 3 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—4</small>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                multi upload here
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—4</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3About-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3About-4.jpg" class="img-fluid" alt="Restaurant 3 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row"><!-- About BTN -->
                        <div class="col">
                            <button type="button" class="btn btn-success" id="submitContactBtn" onclick="submitAbout();">Submit page About info.</button>
                            <small id="infoTextAbout" class="text-danger ml-3">This page has never had a design template submitted.</small>
                        </div>
                    </div>

                </div><!--end tab-About-->

                <div class="tab-pane p-3" id="res3Contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                    <div class="row mb-5"><!-- Contact Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputContactHeadline" class="form-label">34. Contact Us Headline #1</label>
                                            <input type="text" class="form-control" id="inputContactHeadline" placeholder="Headline #1">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputContactSub" class="form-label">35. Contact Us Sub Headline #1</label>
                                            <textarea class="form-control" id="inputContactSub" rows="3" placeholder="Sub Headline #1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputFormHeadline" class="form-label">36. Form Headline #2</label>
                                            <input type="text" class="form-control" id="inputFormHeadline" placeholder="Form Headline #2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputFormSub" class="form-label">37. Form Sub Headline #2</label>
                                            <textarea class="form-control" id="inputFormSub" rows="3" placeholder="Form Sub Headline #2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3 border rounded p-3">
                                <div class="col">
                                    <label for="textPromotion2">38. Background Image</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formContactBackgroundImage">
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
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—1</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Contact-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5"><!-- Contact Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—2</small>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputLocationHead" class="form-label">39. Location Headline #3</label>
                                            <input type="text" class="form-control" id="inputLocationHead" placeholder="Location Headline #2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputLocationSub" class="form-label">40. Location Sub Headline #3</label>
                                            <textarea class="form-control" id="inputLocationSub" rows="3" placeholder="Location Sub Headline #2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3Contact-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3Contact-2.jpg" class="img-fluid" alt="Restaurant 2 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row"><!-- Contact BTN-->
                        <div class="col">
                            <button type="button" class="btn btn-success" id="submitContactBtn" onclick="submitContact();">Submit page Contact info.</button>
                            <small id="infoTextContact" class="text-danger ml-3">This page has never had a design template submitted.</small>
                        </div>
                    </div>

                </div><!--end tab-Contact-->

            </div> <!-- End tab-content-->
        </div><!--col-->
    </div><!--row-->
    <div class="row mb-5" id="changepage">
        <div class="col-6" style="text-align:left;">

        </div>
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-res3Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-res3About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-res3Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
        </div>
    </div>
</div>
<!-- End Template Restaurant 3 -->

<script src="../controllers/template.js"></script>
<script>
    let page = "";// for ajax
    let payload = {};// for ajax

    $(function() {
        setAllPageStatus(); //in template.js
    });//ready

    const submitHome = () => {
        page = "home";
        payload = {
            "loginID": loginID
        }
        console.log("submitHome");
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            "loginID": loginID
        }
        console.log("submitAbout");
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            "loginID": loginID
        }
        console.log("submitContact");
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitContact


    $("#cmdSubmit").click(function () {
        let payload = {
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
    });

</script>