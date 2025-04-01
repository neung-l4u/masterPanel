<?php
require_once "../../../assets/api/googleAnalytics.php";
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
<link rel="stylesheet" href="../assets/css/template.css">
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/mas1.css">
<link rel="stylesheet" href="dist/css/newStyle.css">

<script src="../controllers/res3.js"></script>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item projectName" aria-current="page" id="projectName"><a href="../views/main.php?m=detail&id=<?php echo $projectID; ?>"><?php echo $row['projectName']; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" id="projectTemplate">
            <?php echo ($row['shopTypeID']==1)?"Restaurant":"Massage"; ?> <?php echo $row['selectedTemplate']; ?>
            (<a class="link-primary" href="https://massage3.localforyou.com/" target="_blank">Preview</a>)
        </li>
    </ol>
</nav>

<!-- Template Massage 1 -->
<div id="Temmas1" class="container-fluid">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-mas1Home" data-bs-toggle="tab" data-bs-target="#mas1Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-mas1About" data-bs-toggle="tab" data-bs-target="#mas1About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-mas1Services" data-bs-toggle="tab" data-bs-target="#mas1Services" type="button" role="tab" aria-selected="false">Service</button>
                    <button class="nav-item nav-link" id="tab-mas1Contact" data-bs-toggle="tab" data-bs-target="#mas1Contact" type="button" role="tab" aria-selected="false">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane show active p-3" id="mas1Home" role="tabpanel" aria-labelledby="nav-home-tab">

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
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomebg1">
                                        <div class="d-flex flex-column">
                                            <label for="bg1">1. Home Page Header BG Image</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="homeHeaderBG" src="../assets/img/default.png" alt="place">
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
                                        <label for="homeHeaderHeadline" class="form-label">2. Header Headline</label>
                                        <input type="text" class="form-control" id="homeHeaderHeadline" placeholder="Header Headline">
                                    </div>
                                </div>
                            </div>
                            <label for="homeFeature1" class="form-label">3. Feature Box #1</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeFeature1">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                                    <div class="col mt-2">
                                        <div class="">
                                            <input type="text" class="form-control" id="homeNameFeature1" placeholder="Services #1">
                                            <textarea class="form-control" name="notes" id="homeDetailFeature1" rows="8" placeholder="Services #1 Detail"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <label for="homeFeature1" class="form-label mt-3">4. Feature Box #2</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeFeature2">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                                    <div class="col mt-2">
                                        <div class="">
                                            <input type="text" class="form-control" id="homeNameFeature2" placeholder="Services #2">
                                            <textarea class="form-control" name="notes" id="homeDetailFeature2" rows="8" placeholder="Services #2 Detail"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <label for="homeFeature1" class="form-label  mt-3">5. Feature Box #3</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeFeature3">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                                    <div class="col mt-2">
                                        <div class="">
                                            <input type="text" class="form-control" id="homeNameFeature3" placeholder="Services #3">
                                            <textarea class="form-control" name="notes" id="homeDetailFeature3" rows="8" placeholder="Services #3 Detail"></textarea>
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
                                    <a href="../assets/img/Mas1/Mas1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                        <label for="homeWelcomeMessage" class="form-label">6. Welcome Message</label>
                                        <textarea class="form-control" id="homeWelcomeMessage" placeholder="Welcome Message" row="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formwelcomeimg1">
                                        <div class="d-flex flex-column">
                                            <label for="formwelcomeimg1">7. Welcome Image #1</label>
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formwelcomeimg2">
                                        <div class="d-flex flex-column">
                                            <label for="formwelcomeimg2">8. Welcome Image #2</label>
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
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/img/Mas1/Mas1Home-2.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Home-2.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                    <div class="mb-3">
                                        <label for="homeTestimonialsMessage" class="form-label">9. Testimonials Message</label>
                                        <textarea class="form-control" id="homeTestimonialsMessage" placeholder="Testimonials Message..." row="4"></textarea>
                                    </div>
                                </div>
                            </div>

                            <label for="homeTestimonial1" class="form-label">10. Testimonial #1</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                <div class="col mt-2">
                                        <div class="">
                                            <label for="">Name</label>
                                            <input type="text" class="form-control" id="homeTestimonialName1" placeholder="User #1">
                                            <label for="">Text</label>
                                            <textarea class="form-control" name="notes" id="homeTestimonialText1" rows="5" placeholder="Review #1 Detail"></textarea>
                                        </div>
                                    </div>
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeTestimonialimg1">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                            </div>

                            <label for="homeTestimonial2" class="form-label mt-3">11. Testimonial #2</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                <div class="col mt-2">
                                        <div class="">
                                            <label for="">Name</label>
                                            <input type="text" class="form-control" id="homeTestimonialName2" placeholder="User #2">
                                            <label for="">Text</label>
                                            <textarea class="form-control" name="notes" id="homeTestimonialText2" rows="5" placeholder="Review #2 Detail"></textarea>
                                        </div>
                                    </div>
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeTestimonialimg2">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                            </div>

                            <label for="homeTestimonial3" class="form-label mt-3">12. Testimonial #3</label>
                            <div class="col">
                                <div class="row border rounded p-2">
                                <div class="col mt-2">
                                        <div class="">
                                            <label for="">Name</label>
                                            <input type="text" class="form-control" id="homeTestimonialName3" placeholder="User #3">
                                            <label for="">Text</label>
                                            <textarea class="form-control" name="notes" id="homeTestimonialText3" rows="5" placeholder="Review #3 Detail"></textarea>
                                        </div>
                                    </div>
                                    <div class="col mt-2">
                                        <div class="">
                                            <form method="post" enctype="multipart/form-data" class="uploadForm" id="formhomeTestimonialimg3">
                                            <div class="d-flex flex-column gap-2 mb-2">
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
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homeGoogleReview" class="form-label">13. Link Google Review</label>
                                        <input type="text" id="homeGoogleReview" class="form-control" placeholder="https://g.co/kgs/LMNCXeu">
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
                                    <a href="../assets/img/Mas1/Mas1Home-3.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Home-3.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                        <label for="homeTeamHeadline" class="form-label">14. Our Team Headline</label>
                                        <input type="text" id="homeTeamHeadline" class="form-control" placeholder="Name Team">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homeOurTeamMessage" class="form-label">15. Our Team Message</label>
                                        <textarea class="form-control" id="homeOurTeamMessage" placeholder="Detail Team" row="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <label for="formwelcomeimg1">16. Our Team Images</label>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTeamImages1">
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTeamImages2">
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTeamImages3">
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
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formTeamImages4">
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
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homeAppointmentMessage" class="form-label">17. Make An Appointment Message</label>
                                        <textarea class="form-control" id="homeAppointmentMessage" placeholder="Appointment Message" row="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionHeadline" class="form-label">18. Promotion Headline</label>
                                        <textarea class="form-control" id="homePromotionHeadline" placeholder="Promotion Headline" row="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="homePromotionMessage" class="form-label">19. Promotion Message</label>
                                        <textarea class="form-control" id="homePromotionMessage" placeholder="Promotion Message" row="4"></textarea>
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
                                    <a href="../assets/img/Mas1/Mas1Home-4.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Home-4.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 4-->
                    <div class="row mb-5"><!--Notes-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <label for="notesContact">Notes / Additional Comments</label>
                                    <textarea class="form-control" name="notes" id="notesHome" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div><!--End Notes-->
                    <div class="col">
                        <button type="button" class="btn btn-success" id="submitHomeBtn" onclick="submitHome();">Save page Home info.</button>
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
                </div><!--end tab-Home-->

                <!-- fixed Structure -->
                <div class="tab-pane p-3" id="mas1About" role="tabpanel" aria-labelledby="nav-about-tab">

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
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formaboutbg1">
                                        <div class="d-flex flex-column">
                                            <label for="formaboutbg1">1. About Page Header BG Image</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
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
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col mt-2">
                                    <label for="aboutOurstoryMessage">2. Our Story Message</label>
                                    <div class="">
                                        <textarea class="form-control" name="notes" id="aboutOurstoryMessage" rows="3" placeholder="Detail Our Story Message"></textarea>
                                    </div>
                                </div>
                            </div>

                            <label for="formOurStory">3. Our Story Image</label>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formOurStory">
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

                            <div class="row">
                                <div class="col mt-2">
                                    <label for="aboutMessagefromBusiness">4. Message From Business Owner</label>
                                    <div class="">
                                        <textarea class="form-control" name="notes" id="aboutMessagefromBusiness" rows="3" placeholder="Detail Message From Business Owner"></textarea>
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
                                    <a href="../assets/img/Mas1/Mas1About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1About-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                            <label for="fileupload">5. Featured Dish Image</label>
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
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Mas1/Mas1About-2.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1About-2.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->
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
                    <div class="col">
                        <button type="button" class="btn btn-success" id="submitAboutBtn" onclick="submitAbout();">Save page Services info.</button>
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
                </div><!--end tab-About-->

                <div class="tab-pane p-3" id="mas1Services" role="tabpanel" aria-labelledby="nav-services-tab">

                <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultService">
                            <label for="defaultService"><u>Use Default Template.</u></label>
                        </div>
                    </div>

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Form Section—1</h5>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="fromservicesHeaderBG">
                                        <label for="fromservicesHeaderBG">1. Services Page Header BG Image</label>
                                        <div class="d-flex flex-column gap-2">
                                            <img class="preview" id="servicesHeaderBG" src="../assets/img/default.png" alt="place">
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
                                        <label for="inputServicesMessage" class="form-label">2. Services Message</label>
                                        <textarea class="form-control" id="inputServicesMessage" rows="3" placeholder="Services Message"></textarea>
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
                                    <a href="../assets/img/Mas1/Mas1Services-1.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Services-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                            <div class="row gap-2 mb-2">
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices1">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services1">3. Services #1</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services1" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices1" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices1" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices1" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices2">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services2">4. Services #2</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services2" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices2" rows="2" placeholder="Name Services #2"></textarea>
                                    <textarea class="form-control" id="priceServices2" rows="4" placeholder="Price Services #2"></textarea>
                                    <textarea class="form-control" id="delailServices2" rows="6" placeholder="Detail Services #2"></textarea>
                                </div>
                            </div>
                            <div class="row gap-2 mb-2">
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices3">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services2">5. Services #3</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services3" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices3" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices3" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices3" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices4">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services4">6. Services #4</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services4" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices4" rows="2" placeholder="Name Services #2"></textarea>
                                    <textarea class="form-control" id="priceServices4" rows="4" placeholder="Price Services #2"></textarea>
                                    <textarea class="form-control" id="delailServices4" rows="6" placeholder="Detail Services #2"></textarea>
                                </div>
                            </div>
                            <div class="row gap-2 mb-2">
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices5">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services5">7. Services #5</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services5" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices5" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices5" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices5" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices6">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services6">8. Services #6</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services6" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices6" rows="2" placeholder="Name Services #2"></textarea>
                                    <textarea class="form-control" id="priceServices6" rows="4" placeholder="Price Services #2"></textarea>
                                    <textarea class="form-control" id="delailServices6" rows="6" placeholder="Detail Services #2"></textarea>
                                </div>
                            </div>
                            <div class="row gap-2 mb-2">
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices7">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services6">9. Services #7</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services7" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices7" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices7" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices7" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices8">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services8">10. Services #8</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services8" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices8" rows="2" placeholder="Name Services #2"></textarea>
                                    <textarea class="form-control" id="priceServices8" rows="4" placeholder="Price Services #2"></textarea>
                                    <textarea class="form-control" id="delailServices8" rows="6" placeholder="Detail Services #2"></textarea>
                                </div>
                            </div>
                            <div class="row gap-2 mb-2">
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formservices9">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="services9">11. Services #9</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services9" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices9" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices9" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices9" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                                <div class="col border rounded p-3">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formserviceend">
                                        <div class="d-flex flex-column mb-4">
                                            <label for="formhomeTestmg3">12. Services #10</label>
                                            <div class="d-flex flex-column gap-2 p-2 border rounded">
                                                <div class="d-flex flex-column gap-2">
                                                    <img class="preview" id="services10" src="../assets/img/default.png" alt="place">
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
                                    <textarea class="form-control" id="nameServices10" rows="2" placeholder="Name Services #1"></textarea>
                                    <textarea class="form-control" id="priceServices10" rows="4" placeholder="Price Services #1"></textarea>
                                    <textarea class="form-control" id="delailServices10" rows="6" placeholder="Detail Services #1"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-3">
                                        <label for="inputPackagesDetail" class="form-label">13. Our Packages Detail</label>
                                        <textarea class="form-control" id="inputPackagesDetail" rows="3" placeholder="Our Packages Detail"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            
                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <h5 class="text-decoration-underline text-black font-weight-bold">Example Section—2</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Mas1/Mas1Services-2.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Services-2.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->
                    <div class="row mb-5"><!--Notes-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <label for="notesServices">Notes / Additional Comments</label>
                                    <textarea class="form-control" name="notes" id="notesServices" rows="3" placeholder="Notes / Additional Comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div><!--End Notes-->
                    <div class="col">
                        <button type="button" class="btn btn-success" id="submitServiceBtn" onclick="submitServices();">Save page Services info.</button>
                        <?php if ($pageDetail["services"] == null) {  ?>
                            <small id="infoTextServices" class="text-danger ml-3"3>
                                This page has never had a design template submitted.
                            </small>
                        <?php }else{ ?>
                            <small id="infoTextServices" class="text-success ml-3"3>
                                Saved.
                            </small>
                        <?php } ?>
                    </div>
                </div><!--end tab-Services-->

                <div class="tab-pane p-3" id="mas1Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                
                <div class="row pl-3 my-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="defaultContact">
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
                            <div class="row mb-3">
                                <div class="col">
                                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="fromcontactHeaderBG">
                                        <label for="fromcontactHeaderBG">1. Contact Page Header BG Image</label>
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
                                        <label for="inputContactUsMessage" class="form-label">2. Contact Us Message</label>
                                        <textarea class="form-control" id="inputContactUsMessage" rows="3" placeholder="Contact Message"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row"><!-- Contact BTN-->
                        
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
                                    <a href="../assets/img/Mas1/Mas1Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="mas1Img" src="../assets/img/Mas1/Mas1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
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
                    <div class="col">
                        <button type="button" class="btn btn-success" id="submitContactBtn" onclick="submitContact();">Save page Contact info.</button>
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
            </div> <!-- End tab-content-->
        </div>
        
    </div>
    <div class="row mb-5" id="changepage">
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-mas1Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-mas1About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-mas1Services');" type="button" class="btn btn-primary" id="bottomTabServices">Services</button>
                    <button onclick="changeTab('tab-mas1Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
                </div>
            </nav>
            <!-- end tab menu -->
            <input type="hidden" id="projectID" value="<?php echo $id; ?>">
            <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
        </div>
    </div>
</div>
<!-- End Template Massage 1 -->

<script src="../controllers/template.js"></script>
<!-- These are the necessary files for the image uploader -->
<script src="dist/assets/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script src="dist/assets/jquery-file-upload/js/jquery.fileupload.js"></script>
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
            "A1-01-HeadBG" : $('#formhomebg1 .picname').val(),
            "A1-02-HeadHline" : $('#homeHeaderHeadline').val(),
            "A1-03-01-Feature" : $('#formhomeFeature1 .picname').val(),
            "A1-03-02-ServiceName" : $('#homeNameFeature1').val(),
            "A1-03-03-ServiceDetail" : $('#homeDetailFeature1').val(),
            "A1-04-01-Feature" : $('#formhomeFeature2 .picname').val(),
            "A1-04-02-ServiceName" : $('#homeNameFeature2').val(),
            "A1-04-03-ServiceDetail" : $('#homeDetailFeature2').val(),
            "A1-05-01-Feature" : $('#formhomeFeature3 .picname').val(),
            "A1-05-02-ServiceName" : $('#homeNameFeature3').val(),
            "A1-05-03-ServiceDetail" : $('#homeDetailFeature3').val(),
            "A2-06-WelcomeMessage" : $('#homeWelcomeMessage').val(),
            "A2-07-WelcomeImg1" : $('#formwelcomeimg1 .picname').val(),
            "A2-08-WelcomeImg2" : $('#formwelcomeimg2 .picname').val(),
            "A3-09-TestimonialsMessage" : $('#homeTestimonialsMessage').val(),
            "A3-10-01-TestimonialsName1" : $('#homeTestimonialName1').val(),
            "A3-10-02-TestimonialsText1" : $('#homeTestimonialText1').val(),
            "A3-10-03-TestimonialsImg1" : $('#formhomeTestimonialimg1 .picname').val(),
            "A3-11-01-TestimonialsName2" : $('#homeTestimonialName2').val(),
            "A3-11-02-TestimonialsText2" : $('#homeTestimonialText2').val(),
            "A3-11-03-TestimonialsImg2" : $('#formhomeTestimonialimg2 .picname').val(),
            "A3-12-01-TestimonialsName3" : $('#homeTestimonialName3').val(),
            "A3-12-02-TestimonialsText3" : $('#homeTestimonialText3').val(),
            "A3-12-03-TestimonialsImg3" : $('#formhomeTestimonialimg3 .picname').val(),
            "A3-13-LinkGoogleReview" : $('#homeGoogleReview').val(),
            "A4-14-OurTeamHeadline" : $('#homeTeamHeadline').val(),
            "A4-15-OurTeamHeadMessage" : $('#homeOurTeamMessage').val(),
            "A4-16-01-OurTeamImg1" : $('#formTeamImages1 .picname').val(),
            "A4-16-02-OurTeamImg2" : $('#formTeamImages2 .picname').val(),
            "A4-16-03-OurTeamImg3" : $('#formTeamImages3 .picname').val(),
            "A4-16-04-OurTeamImg4" : $('#formTeamImages4 .picname').val(),
            "A4-17-AppointmentMessage" : $('#homeAppointmentMessage').val(),
            "A4-18-PromotionHeadline" : $('#homePromotionHeadline').val(),  
            "A4-19-PromotionMessage" : $('#homePromotionMessage').val(),  
            "HomeNote" : $('#notesHome').val()

        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            "loginID" : loginID,
            "page" : "About",
            "defaultTemplate": ($('#defaultAbout').prop('checked')) ? "Yes" : "No",  
            "B1-01-HeadBG" :$('#formaboutbg1 .picname').val(),
            "B1-02-OurStoryMessage" :$('#aboutOurstoryMessage').val(),
            "B1-03-OurStoryImg" :$('#formOurStory .picname').val(),
            "B1-04-MessageBusinees" :$('#aboutMessagefromBusiness').val(),
            "B2-05-FeaturedDish" :JSON.stringify(album_files),
            "AboutNote" :$('#notesAbout').val(),
        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitAbout

    
    const submitServices = () => {
        page = "services";
        payload = {
            "loginID": loginID,
            "page": "Services",
            "defaultTemplate": ($('#defaultService').prop('checked')) ? "Yes" : "No",
            "C1-01-HeadBG" : $('#fromservicesHeaderBG .picname').val(), 
            "C1-02-ServicesMessage" : $('#inputServicesMessage').val(), 

            "C2-03-01-ServicesImg1" : $('#formservices1 .picname').val(), 
            "C2-03-02-ServicesName1" : $('#nameServices1').val(), 
            "C2-03-03-ServicesPrice1" : $('#priceServices1').val(), 
            "C2-03-04-ServicesDetail1" : $('#delailServices1').val(), 

            "C2-04-01-ServicesImg2" : $('#formservices2 .picname').val(), 
            "C2-04-02-ServicesName2" : $('#nameServices2').val(), 
            "C2-04-03-ServicesPrice2" : $('#priceServices2').val(), 
            "C2-04-04-ServicesDetail2" : $('#delailServices2').val(), 

            "C2-05-01-ServicesImg3" : $('#formservices3 .picname').val(), 
            "C2-05-02-ServicesName3" : $('#nameServices3').val(), 
            "C2-05-03-ServicesPrice3" : $('#priceServices3').val(), 
            "C2-05-04-ServicesDetail3" : $('#delailServices3').val(), 

            "C2-06-01-ServicesImg4" : $('#formservices4 .picname').val(), 
            "C2-06-02-ServicesName4" : $('#nameServices4').val(), 
            "C2-06-03-ServicesPrice4" : $('#priceServices4').val(), 
            "C2-06-04-ServicesDetail4" : $('#delailServices4').val(), 

            "C2-07-01-ServicesImg5" : $('#formservices5 .picname').val(), 
            "C2-07-02-ServicesName5" : $('#nameServices5').val(), 
            "C2-07-03-ServicesPrice5" : $('#priceServices5').val(), 
            "C2-07-04-ServicesDetail5" : $('#delailServices5').val(), 

            "C2-08-01-ServicesImg5" : $('#formservices6 .picname').val(), 
            "C2-08-02-ServicesName5" : $('#nameServices6').val(), 
            "C2-08-03-ServicesPrice5" : $('#priceServices6').val(), 
            "C2-08-04-ServicesDetail5" : $('#delailServices6').val(), 

            "C2-09-01-ServicesImg6" : $('#formservices7 .picname').val(), 
            "C2-09-02-ServicesName6" : $('#nameServices7').val(), 
            "C2-09-03-ServicesPrice6" : $('#priceServices7').val(), 
            "C2-09-04-ServicesDetail6" : $('#delailServices7').val(), 

            "C2-10-01-ServicesImg7" : $('#formservices8 .picname').val(), 
            "C2-10-02-ServicesName7" : $('#nameServices8').val(), 
            "C2-10-03-ServicesPrice7" : $('#priceServices8').val(), 
            "C2-10-04-ServicesDetail7" : $('#delailServices8').val(), 

            "C2-11-01-ServicesImg8" : $('#formservices9 .picname').val(), 
            "C2-11-02-ServicesName8" : $('#nameServices9').val(), 
            "C2-11-03-ServicesPrice8" : $('#priceServices9').val(), 
            "C2-11-04-ServicesDetail8" : $('#delailServices9').val(), 

            "C2-12-01-ServicesImg9" : $('#formserviceend .picname').val(), 
            "C2-12-02-ServicesName9" : $('#nameServices10').val(), 
            "C2-12-03-ServicesPrice9" : $('#priceServices10').val(), 
            "C2-12-04-ServicesDetail9" : $('#delailServices10').val(), 

            "C2-13-OurPackagesDetail" :$('#inputPackagesDetail').val(),
            "ServicesNote" :$('#notesServices').val(),

        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitContact
    
    const submitContact = () => {
        page = "contact";
        payload = {
            "loginID": loginID,
            "page" : "Contact",
            "defaultTemplate": ($('#defaultContact').prop('checked')) ? "Yes" : "No",
            "D1-01-HeadBG" : $('#fromcontactHeaderBG .picname').val(), 
            "D1-02-ContactUsMessage" : $('#inputContactUsMessage').val(), 
            "ContactNote" :$('#notesContact').val(),

        }
        console.log("Payload", payload);
        saveToDB(); //in template.js
    }//submitContact
</script>