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

<!-- Template Massage 1 -->
<div id="Temres1" class="container-fluid">
    <div class="row">
        <div class="col">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                    <button class="nav-item nav-link active" id="tab-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true">Home</button>
                    <button class="nav-item nav-link" id="tab-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false">About</button>
                    <button class="nav-item nav-link" id="tab-res1Services" data-bs-toggle="tab" data-bs-target="#res1Services" type="button" role="tab" aria-selected="false">Service</button>
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
                                    <h2>Home Page</h2>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                    <h2>Home Page</h2>
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
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                    <h2>Home Page</h2>
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
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-5"><!--Section 4-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—4</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h2>Home Page</h2>
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
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 4-->

                    <div class="row mb-5"><!--Section 5-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—5</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h2>Home Page</h2>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—5</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 5-->

                    <div class="row mb-5"><!--Section 6-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—6</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <h2>Home Page</h2>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—6</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Home-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Home-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 6-->
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
                                    <h2>About Page</h2>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                                    <h2>About Page</h2>
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
                                    <a href="../assets/img/Res1/new/Res1About-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1About-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 2-->
                </div><!--end tab-Services-->

                <div class="tab-pane p-3" id="res1Services" role="tabpanel" aria-labelledby="nav-services-tab">

                    <div class="row mb-5"><!--Section 1-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—1</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <h2>Services Page</h2>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
                            <div class="row mb-3">
                                <div class="col">
                                    <h2>Services Page</h2>
                                </div>
                            </div>
                            
                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—2</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                    <div class="row mb-5"><!--Section 3-->
                        <div class="col-6">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Form Section—3</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <h2>Services Page</h2>
                                </div>
                            </div>
                            
                        </div><!--end col-6-->
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <small class="text-info">Example Section—3</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <a href="../assets/img/Res1/new/Res1Contact-1.jpg" target="_blank" title="click to zoom">
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--end Section 3-->

                </div><!--end tab-Services-->

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
                                    <h2>Contact Page</h2>
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
                                        <img id="res1Img" src="../assets/img/Res1/new/Res1Contact-1.jpg" class="img-fluid" alt="Massage 1 - Section 1">
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
        <div class="col d-flex justify-content-end">
            <!-- tab menu -->
            <nav>
                <div class="nav nav-pills d-flex justify-content-end gap-2" id="nav-tab2" role="tablist">
                    <button onclick="changeTab('tab-res1Home');" type="button" class="btn btn-primary" id="bottomTabHome">Home</button>
                    <button onclick="changeTab('tab-res1About');" type="button" class="btn btn-primary" id="bottomTabAbout">About</button>
                    <button onclick="changeTab('tab-res1Services');" type="button" class="btn btn-primary" id="bottomTabServices">Services</button>
                    <button onclick="changeTab('tab-res1Contact');" type="button" class="btn btn-primary" id="bottomTabContact">Contact</button>
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
<script>

$(function() {
        setAllPageStatus(); //in template.js
    });//ready

    const submitHome = () => {
        page = "home";
        payload = {
            //TEMPLATE_R1_PAGE_HOME
            "loginID": loginID,
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitHome

    const submitAbout = () => {
        page = "about";
        payload = {
            //TEMPLATE_R1_PAGE_ABOUT
            "loginID": loginID,  
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitAbout

    const submitContact = () => {
        page = "contact";
        payload = {
            //TEMPLATE_R1_PAGE_CONTACT    
            "loginID": loginID,    
        }
        console.log("Payload", payload);
        sendEmail(); //in template.js
    }//submitContact

</script>