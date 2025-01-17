<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT * FROM `tb_project` WHERE projectID = ?;',$id)->fetchArray();
$projectID = $id;

$folderName = "upload/". $projectID . "-" . sanitizeFolderName($row["projectName"]).'/';
?>

<link rel="stylesheet" href="../assets/css/template.css">
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/res3.css">
<link rel="stylesheet" href="dist/css/newStyle.css">

<script src="../controllers/res3.js"></script>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mb-5">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo ($row['shopTypeID']==1)?"Restaurant":"Massage"; ?> <?php echo $row['selectedTemplate']; ?>
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

                    <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultHome">
                            <label for="defaultHome"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-5"><!-- Home Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
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
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formbg1">
                                        <div class="d-flex flex-column">
                                            <label for="bg1">4. BG Image 2201x1068 px.</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="homeBGimg" src="../assets/img/default.png" alt="place">
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Home-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 1-->

                    <div class="row mb-5"><!-- Home Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—2</h5>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                    <h6>promotion1 #1</h6>
                                    <label for="textFeaturedDish4">6. Promotion Message #1</label>
                                    <textarea type="text" class="form-control" id="textFeaturedDish4" row="4" placeholder="promotion #1"></textarea>
                                </div>
                                <div class="col">
                                    <label for="textFeaturedDish4">7. Promotion Image #1</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formpromotion1">
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
                                                <label for="textFeaturedDish1">8.Featured Dish Image #1</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <div class="mt-2">
                                                                <input type="text" class="form-control" id="textFeaturedDish1" placeholder="Featured Dish Name #1">
                                                                <textarea class="form-control" name="notes" id="descFeaturedDish1" rows="3" placeholder="Featured Dish Description #1"></textarea>
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
                                                <label for="textFeaturedDish2">9.Featured Dish Image #2</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <div class="mt-2">
                                                                <input type="text" class="form-control" id="textFeaturedDish2" placeholder="Featured Dish Name #2">
                                                                <textarea class="form-control" name="notes" id="descFeaturedDish2" rows="3" placeholder="Featured Dish Description #2"></textarea>
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
                                                <label for="textFeaturedDish3">10.Featured Dish Image #3</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <div class="mt-2">
                                                                <input type="text" class="form-control" id="textFeaturedDish3" placeholder="Featured Dish Name #3">
                                                                <textarea class="form-control" name="notes" id="descFeaturedDish3" rows="3" placeholder="Featured Dish Description #3"></textarea>
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
                                                <label for="textFeaturedDish4">11.Featured Dish Image #4</label>
                                                <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                    <div class="d-flex flex-column gap-2">
                                                        <img class="preview" src="../assets/img/default.png" alt="place">
                                                        <input class="picname" type="hidden" value="">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-column gap-2">
                                                                <input type="file" class="file-input">
                                                                <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                                            </div>
                                                            <div class="mt-2">
                                                                <input type="text" class="form-control" id="textFeaturedDish4" placeholder="Featured Dish Name #4">
                                                                <textarea class="form-control" name="notes" id="descFeaturedDish4" rows="3" placeholder="Featured Dish Description #4"></textarea>
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-Home-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Home-2.jpg" class="img-fluid" alt="Restaurant 3 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->

                    <div class="row mb-5"><!-- Home Section 3-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—3</h5>
                                </div>
                            </div>
                            <div class="row mb-3 border rounded p-3">
                                <div class="col-6">
                                        <h6>12. Testimonial #1</h6>
                                        <label for="textNameTestimonial1">Name</label>
                                        <input type="text" class="form-control" id="textNameTestimonial1" placeholder="Testimonial #1">
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
                                    <h6>13. Testimonial #2</h6>
                                    <label for="textNameTestimonial2">Name</label>
                                    <input type="text" class="form-control" id="textNameTestimonial2" placeholder="Testimonial #2">
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
                                    <label for="textPromotion2">14. Promotion Image #2</label>
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
                                    <label for="textpicpng">15. Heading</label>
                                    <input type="text" class="form-control" id="textpicpng" placeholder="Testimonial #2">
                                    <label for="textbody" class="form-label">16. Detail</label>
                                    <textarea class="form-control" id="textbody" rows="3" placeholder="Testimonial #2"></textarea>
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
                                    <a href="../assets/img/Res3/Res3-Home-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Home-3.jpg" class="img-fluid" alt="Restaurant 3 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-3"><!-- Home Section 4-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—4</h5>
                                </div>
                            </div>

                            <div class="row mb-3 border rounded p-3">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="formFooter1">
                                            <div class="d-flex flex-column">
                                                <label for="picFooter1">17.Footer Image #1</label>
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
                                                <label for="picFooter2">18.Footer Image #2</label>
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
                                                <label for="picFooter3">19.Footer Image #3</label>
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
                                                <label for="picFooter4">20.Footer Image #4</label>
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
                                    <a href="../assets/img/Res3/Res3-Home-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Home-4.jpg" class="img-fluid" alt="Restaurant 3 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5"><!--Notes-->
                            <div class="col">
                                <div class="row">
                                    <div class="col">
                                        <label for="notesHome">Notes / Additional Comments</label>
                                        <textarea class="form-control" name="notes" id="notesHome" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div><!--End Notes-->
                    </div><!--end Section 4-->

                    <div class="row"><!-- Home BTN -->
                        <div class="col">
                            <button type="button" class="btn btn-success" id="submitHomeBtn" onclick="submitHome();">Submit page Home info.</button>
                            <small id="infoTextHome" class="text-danger ml-3">This page has never had a design template submitted.</small>
                        </div>
                    </div>

                </div><!--end tab-Home-->

                <!-- fixed Structure -->
                <div class="tab-pane p-3" id="res3About" role="tabpanel" aria-labelledby="nav-about-tab">

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

                            <div class="border rounded p-5">
                                <div class="row mt-3">
                                    <div class="col">
                                        <form method="post" enctype="multipart/form-data" class="uploadForm" id="fromaboutHeaderBG">
                                            <label for="bg1">1. About Page Header BG Image 2201x1068 px.</label>
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
                                            <label for="inputTagline" class="form-label">2. Tag Line</label>
                                            <input type="text" class="form-control" id="inputTagline" placeholder="Business Tagline">
                                        </div>
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
                                    <a href="../assets/img/Res3/Res3-About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-About-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5"><!--Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—2</h5>
                                </div>
                            </div>
                            <div class="border rounded mb-3 p-5">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputHighlightSlogan" class="form-label">3. Highlight Slogan</label>
                                            <input type="text" class="form-control" id="inputHighlightSlogan" placeholder="Highlight Slogan">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutSlogan" class="form-label">4. Slogan</label>
                                            <input type="text" class="form-control" id="inputAboutSlogan" placeholder="About Slogan">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutHeadline" class="form-label">5. About Us Headline</label>
                                            <textarea class="form-control" id="inputAboutHeadline" rows="3" placeholder="About Us Headline"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputAboutDetail" class="form-label">6. About Us Detail</label>
                                            <textarea class="form-control" id="inputAboutDetail" rows="3" placeholder="About Us Detail"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div><!--border rounded-->

                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-About-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-About-2.jpg" class="img-fluid" alt="Restaurant 3 - Section 2">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—3</h5>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="textPromotion2">7. Highlight dish</label>
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
                                        <label for="textHighlightTitle">8. Highlight Title</label>
                                        <input type="text" class="form-control" id="textHighlightTitle" placeholder="Highlight Title">
                                        <label for="textHighlightSubtitle" class="form-label">9. Highlight Sub</label>
                                        <textarea class="form-control" id="textHighlightSubtitle" rows="3" placeholder="Highlight Subtitle"></textarea>
                                        <label for="textHighlightDetail" class="form-label">10. Highlight Detail</label>
                                        <textarea class="form-control" id="textHighlightDetail" rows="3" placeholder="Highlight Detail"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—3</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-About-3.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-About-3.jpg" class="img-fluid" alt="Restaurant 3 - Section 3">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—4</h5>
                                </div>
                            </div>
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
                                    <label for="fileupload">11. Featured Dish Image</label>
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
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—4</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-About-4.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-About-4.jpg" class="img-fluid" alt="Restaurant 3 - Section 4">
                                    </a>
                                </div>
                            </div>
                        </div>

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
                    </div>

                    <div class="row"><!-- About BTN -->
                        <div class="col">
                            <button type="button" class="btn btn-success" id="submitAboutBtn" onclick="submitAbout();">Submit page About info.</button>
                            <small id="infoTextAbout" class="text-danger ml-3">This page has never had a design template submitted.</small>
                        </div>
                    </div>

                </div><!--end tab-About-->

                <div class="tab-pane p-3" id="res3Contact" role="tabpanel" aria-labelledby="nav-contact-tab">

                    <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultContact">
                            <label for="defaultContact"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-5"><!-- Contact Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputContactHeadline" class="form-label">1. Contact Us Headline #1</label>
                                            <input type="text" class="form-control" id="inputContactHeadline" placeholder="Headline #1">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputContactSub" class="form-label">2. Contact Us Sub Headline #1</label>
                                            <textarea class="form-control" id="inputContactSub" rows="3" placeholder="Sub Headline #1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputFormHeadline" class="form-label">3. Form Headline #2</label>
                                            <input type="text" class="form-control" id="inputFormHeadline" placeholder="Form Headline #2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputFormSub" class="form-label">4. Form Sub Headline #2</label>
                                            <textarea class="form-control" id="inputFormSub" rows="3" placeholder="Form Sub Headline #2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="col">
                                    <label for="textPromotion2">5. Background Image</label>
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formContactBackgroundImage">
                                        <div class="d-flex flex-column gap-2">
                                            <img class="preview" id="contactBGimg" src="../assets/img/default.png" alt="place">
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
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—1</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Contact-1.jpg" class="img-fluid" alt="Restaurant 3 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5"><!-- Contact Section 2-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—2</h5>
                                </div>
                            </div>
                            <div class="mb-3 border rounded p-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputLocationHead" class="form-label">6. Location Headline #3</label>
                                            <input type="text" class="form-control" id="inputLocationHead" placeholder="Location Headline #2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3">
                                            <label for="inputLocationSub" class="form-label">7. Location Sub Headline #3</label>
                                            <textarea class="form-control" id="inputLocationSub" rows="3" placeholder="Location Sub Headline #2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res3/Res3-Contact-2.jpg" target="_blank" title="click to zoom">
                                        <img id="res3Img" src="../assets/img/Res3/Res3-Contact-2.jpg" class="img-fluid" alt="Restaurant 2 - Section 1">
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
            <input type="hidden" id="folderPath" value="<?php echo $folderName; ?>">
        </div>
    </div>
</div>
<!-- End Template Restaurant 3 -->

<script src="../controllers/template.js"></script>
<!-- These are the necessary files for the image uploader -->
<script src="dist/assets/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.fileupload.js"></script>
<script>
    const max_uploads = 10;
    const multiUploadPrefix = 'album';
    let album_files = [];

    $(function() {
        setAllPageStatus(); //in template.js
        /////////
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
            ////////

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
            "defaultTemplate": ($('#defaultHome').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesHome').val(),
            "A1-01-color" : $('#bg1Hex').val(),
            "A1-02-Slogan" : $('#inputSlogan').val(),
            "A1-03-Intro" : $('#inputIntroduction1').val(),
            "A1-04-HeadBG" : $('#formbg1 .picname').val(),
            "A1-05-Intro" : $('#inputIntroduction2').val(),
            "A2-06-FeaturedDish" : $('#textFeaturedDish4').val(),
            "A2-07-Promopic" : $('#formpromotion1 .picname').val(),
            "A2-08-Dishpic1" : $('#formDish1 .picname').val(),
            "A2-09-Dishpic2" : $('#formDish2 .picname').val(),
            "A2-10-Dishpic3" : $('#formDish3 .picname').val(),
            "A2-11-Dishpic4" : $('#formDish4 .picname').val(),
            "A3-12-01-NameUser1" : $('#textNameTestimonial1').val(),
            "A3-12-02-Review1" : $('#textTestimonial1').val(),
            "A3-12-03-PicUser1" : $('#formTestimonial1 .picname').val(),
            "A3-13-01-Name2" : $('#textNameTestimonial2').val(),
            "A3-13-02-Review2" : $('#textTestimonial2').val(),
            "A3-13-03-PicUser2" : $('#formTestimonial2 .picname').val(),
            "A3-14-MenuPNG" : $('#formPromotion2 .picname').val(),
            "A3-15-Heading" : $('#textpicpng').val(),
            "A3-16-Body" : $('#textbody').val(),
            "A4-17-Footer1" : $('#formFooter1 .picname').val(),
            "A4-18-Footer2" : $('#formFooter2 .picname').val(),
            "A4-19-Footer3" : $('#formFooter3 .picname').val(),
            "A4-20-Footer4" : $('#formFooter4 .picname').val(),
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            "loginID" : loginID,
            "defaultTemplate": ($('#defaultAbout').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesAbout').val(),
            "B1-01-HeadBG" :$('#fromaboutHeaderBG .picname').val(),
            "B1-02-Tagline" :$('#inputTagline').val(),

            "B2-03-HLSlogan" :$('#inputHighlightSlogan').val(),
            "B2-04-Slogan" :$('#inputAboutSlogan').val(),
            "B2-05-Headline" :$('#inputAboutHeadline').val(),
            "B2-06-Detail" :$('#inputAboutDetail').val(),

            "B3-07-HLDish" :$('#formHighlightDish .picname').val(),
            "B3-08-HLTitle" :$('#textHighlightTitle').val(),
            "B3-09-HLSubTitle" :$('#textHighlightSubtitle').val(),
            "B3-10-HLDetail" :$('#textHighlightDetail').val(),

            "B4-11-Slogan" :JSON.stringify(album_files)
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            "loginID": loginID,
            "defaultTemplate": ($('#defaultContact').prop('checked')) ? "Yes" : "No",
            "notes": $('#notesContact').val(),
            "C1-01-Headline" :$('#inputContactHeadline').val(),
            "C1-02-SubHeadline" :$('#inputContactSub').val(),
            "C1-03-FormHeadline" :$('#inputFormHeadline').val(),
            "C1-04-FormSub" :$('#inputFormSub').val(),
            "C1-05-BGForm" :$('#formContactBackgroundImage .picname').val(),
            "C2-06-LocationHead" :$('#inputLocationHead').val(),
            "C2-07-LocationSub" :$('#inputLocationSub').val(),
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitContact
</script>