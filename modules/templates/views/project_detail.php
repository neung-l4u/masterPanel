<?php
global $db, $date;
$id=$_REQUEST['id'];
require_once ("../assets/php/share_function.php");
require_once ("../assets/db/db.php");
?>
<style>

</style>

<!--    <link rel="stylesheet" href="../assets/css/bootstrap.4.5.2.min.css">-->
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <link rel="stylesheet" href="../assets/css/project_detail.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">
    <script src="../assets/js/jquery-3.7.1.min.js"></script>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php">Projects</a></li>
        <li class="breadcrumb-item active projectName" aria-current="page" id="projectName"></li>
    </ol>
</nav>

<!-- Project Information -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Project info.</h5>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <ul>
                <li>
                    <span class="fw-bold">Name: </span>
                    <span class="projectName">Unknown</span>
                </li>
                <li>
                    <span class="fw-bold">Type: </span>
                    <span class="shopType">Unknown</span>
                </li>
                <li>
                    <span class="fw-bold">PO: </span>
                    <span class="projectOwner">Unknown</span>
                </li>
                <li>
                    <span class="fw-bold">Country:</span>
                    <span class="projectCountry">Unknown</span>
                </li>
            </ul>
        </div>
    </div>

    <input type="hidden" class="form-control" id="projectOwner">
    <input type="hidden" class="form-control" id="ShopType">
</div>

<!-- Business Information -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Business info.</h5>
        </div>
    </div>

    <div class="row">
        <div class="col-7">
            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="businessName" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="businessName" placeholder="" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="businessEmail" class="input-group-text" id="basic-addon3">Email</label>
                                <input type="email" class="form-control" id="businessEmail" placeholder="admin@localforyou.com">
                            </div>
                        </div>
                        <div class="col">
                                <div class="input-group mb-3">
                                    <label for="businessPhone" class="input-group-text" id="basic-addon3">Phone</label>
                                    <input type="tel" class="form-control" id="businessPhone" placeholder="+6112345678">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="businessAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="businessAddress" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="openingHours" class="form-label">Opening hour</label>
                        <textarea class="form-control" id="openingHours" rows="7"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-5">
            <div class="row">
                <div class="col py-3 px-3">
                    <label for="logoImg" class="form-label">Logo</label>
                    <form method="post" action="" enctype="multipart/form-data" id="myFormLogo">
                            <img class="preview" src="../assets/img/default.png" id="imgLogo" alt="place">
                            <p id="picNameLogo" class="text-muted"> -- no pic --</p>
                            <div class="row">
                                <input type="file" class="file-input col-8" id="fileLogo" />
                                <button type="submit" class="button col" id="btnUpload">Upload</button>
                            </div>
                    </form>
                </div>
            </div>
            <div class="mt-3 border rounded py-3 pl-3">
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme1">Color Theme #1</label>
                    <input type="color" onchange="setHex(this.value,1);" id="theme1" value="#ffffff">
                    <span id="theme1Hex" class="codeHex">#ffffff</span>
                </div>
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme2">Color Theme #2</label>
                    <input type="color" onchange="setHex(this.value,2);" id="theme2" value="#ffffff">
                    <span id="theme2Hex" class="codeHex">#ffffff</span>
                </div>
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme3">Color Theme #3</label>
                    <input type="color" onchange="setHex(this.value,3);"  id="theme3" value="#ffffff">
                    <span id="theme3Hex" class="codeHex">#ffffff</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Domain -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Customer's Domain name & Hosting.</h5>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <label for="domainHave">Domain Log-in</label>
            <div class="form-check ml-1">
                <input class="form-check-input domainHave" type="checkbox" value="" id="domainHave">
                <label for="domainHave">Yes, we got it.</label>
                <div class="domainbox" id="domainBox">
                    <div class="mb-4">
                        <select id="domainProvider" class="custom-select">
                            <option value="0" selected>-- None --</option>
                            <?php
                            $domains = $db->query('SELECT `id`, `name` FROM `DomainProviders` ORDER BY `name`;')->fetchAll();
                            foreach ($domains as $row){
                                ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                            <?php }//foreach ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="domainUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control domainuser" id="domainUser" aria-describedby="basic-addon3" placeholder="">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="domainPass" class="input-group-text" id="basic-addon3">Password</label>
                                <input type="password" class="form-control domainpass" id="domainPass" aria-describedby="basic-addon3" placeholder="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hosting -->
        <div class="col-6">
            <label for="hostingHave">Hosting Log-in</label>
            <div class="form-check ml-1">
                <input class="form-check-input hostingHave" type="checkbox" value="" id="hostingHave">
                <label for="hostingHave">Yes, we got it.</label>

                <div class="hostingbox" id="hostingBox">
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="hostingUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control hostingUser" id="hostingUser" aria-describedby="basic-addon3" placeholder="">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="hostingPass" class="input-group-text" id="basic-addon3">Password</label>
                                <input type="password" class="form-control hostingPass" id="hostingPass" aria-describedby="basic-addon3" placeholder="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Shop Type -->
    <div class="row mb-3 p-3 border rounded">

<!-- Restaurant System -->
        <div class="col-6" id="resSystem">
            <div class="form-check ml-1">
                <div>
                    <input class="form-check-input gloriahave" type="checkbox" value="" id="gloriaHave">
                    <label for="gloriaHave">Gloria Food</label>
                </div>

                <div>
                    <input type="text" class="form-control gloriabox" id="orderURL" placeholder="Order Online URL">
                    <input type="text" class="form-control gloriabox" id="tableURL" placeholder="Table Reservation URL">
                </div>

                <div>
                    <input class="form-check-input orderOther" type="checkbox" value="" id="orderOther">
                    <label for="orderOther">Other Ordering System</label>

                    <input type="text" class="form-control resOtherSystem" id="resOtherSystem" placeholder="System Name">
                </div>
                <div>
                    <input class="form-check-input orderOther" type="checkbox" value="" id="needEmail">
                    <label for="needEmail">Need email inbox under shop domain name.</label><br>
                    <small class="text-muted">e.g. info@hoonhaymassage.co.nz</small>
                </div>


            </div>
        </div>

<!-- Massage System -->
        <div class="col-6" id="masSystem">
            <div class="form-check ml-1">
                <div>
                    <input class="form-check-input amelia" type="checkbox" value="" id="amelia">
                    <label for="amelia">Amelia</label>
                </div>

                <div>
                    <input class="form-check-input voucher" type="checkbox" value="" id="voucher">
                    <label for="voucher">Voucher</label>
                </div>

                <div>
                    <input class="form-check-input bookOther" type="checkbox" value="" id="bookOther">
                    <label for="bookOther">Other Booking System</label>
                
                    <input type="text" class="form-control masOtherSystem" id="masOtherSystem" placeholder="System Name">
                </div>
            </div>
        </div>

    </div>

<!-- Social Media -->
    <div class="row mb-3 p-3 border rounded">
        <label for="">Social Media</label>
        <div class="col">
            <input type="text" class="form-control" id="facebookURL" placeholder="Facebook URL">
            <input type="text" class="form-control" id="instagramURL" placeholder="Instagram URL">
        </div>
        <div class="col">
            <input type="text" class="form-control" id="youtubeURL" placeholder="Youtube URL">
            <input type="text" class="form-control" id="tiktokURL" placeholder="Tiktok URL">
        </div>
    </div>

    <div class="row">
        <div class="col">
            <input id="cmdSubmit" class="btn btn-primary" type="button" value="Save">
        </div>
    </div>


<!-- Website Infomation -->
    <div class="row">
        <h3>Template Details</h3>

<!-- Select Type -->
    <div class="row">

        <!-- Template Type -->
        <div class="col mb-4">
            <label for="">Template</label>
            <select class="form-select" aria-label="Template" id="TemplateType" onchange="setLayout();">
                <option disabled>Select Template</option>
                <option selected value="1">Template 1</option>
                <option value="2">Template 2</option>
                <option value="3">Template 3</option>
            </select>
        </div>
    </div>

<!-- Template Restaurant 1 -->
    <div id="TemRes1">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-res1Home" data-bs-toggle="tab" data-bs-target="#res1Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res1Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-res1About" data-bs-toggle="tab" data-bs-target="#res1About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1About');">About</button>
                        <button class="nav-item nav-link" id="nav-res1Contact" data-bs-toggle="tab" data-bs-target="#res1Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res1Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="res1Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row pt-3">
                            <div>
                                <input type="text" class="form-control" id="tdR1Slogan" placeholder="Business Slogan">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1HomePromotion1" placeholder="Information or Promotion">
                            </div>

                            <div class="mb-3">
                                <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1HeadHomeImg">
                                    <div class="col pt-3 pb-3 border rounded">
                                        <img class="preview" src="../assets/img/default.png" id="tdR1HeadHomeImg" alt="place">
                                        <p id="picNametdR1HeadHomeImg"></p>
                                        <input type="file" class="file-input" id="filetdR1HeadHomeImg" />
                                        <button type="submit" class="button" id="btnUpload">Upload</button>
                                    </div>
                                </form>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1SubHeadline1" placeholder="Home Page Introduction Sub Headline-1">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1MainHeadline1" placeholder="Home Page Introduction Main Headline">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1SubHeadline2" placeholder="Home Page Introduction Sub Headline-2">
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1HomeIntroduction" rows="3" placeholder="Home Page Introduction Body"></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="row mb-3">
                                    <div class="col">
                                        <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured1">
                                            <div class="col pt-3 pb-3 border rounded">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured1" alt="place">
                                                <p id="picNametdR1Featured1">Featured image 1</p>
                                                <input type="file" class="file-input" id="filetdR1Featured1" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col">
                                        <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured2">
                                            <div class="col pt-3 pb-3 border rounded">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured2" alt="place">
                                                <p id="picNametdR1Featured2">Featured image 2</p>
                                                <input type="file" class="file-input" id="filetdR1Featured2" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured3">
                                            <div class="col pt-3 pb-3 border rounded">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured3" alt="place">
                                                <p id="picNametdR1Featured3">Featured image 3</p>
                                                <input type="file" class="file-input" id="filetdR1Featured3" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col">
                                        <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1Featured4">
                                            <div class="col pt-3 pb-3 border rounded">
                                                <img class="preview" src="../assets/img/default.png" id="tdR1Featured4" alt="place">
                                                <!-- <p id="picNametdR1Featured4">Featured image 4</p> -->
                                                <input type="hidden" id="picNametdR1Featured4" value="abc">
                                                <input type="file" class="file-input" id="filetdR1Featured4" />
                                                <button type="submit" class="button" id="btnUpload">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <textarea class="form-control" id="tdR1TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                                    <input type="text" class="col-8 form-control" id="tdR1TestimonialName1" placeholder="Name">
                                </div>

                                <div class="col">
                                    <form method="post" action="" enctype="multipart/form-data" id="myFormTdR1TestimonialImg1">
                                        <div class="col">
                                            <img class="preview" src="../assets/img/default.png" id="tdR1TestimonialImg1" alt="place">
                                            <p id="picNametdR1TestimonialImg1">Testimonial image 1 </p>
                                            <input type="file" class="file-input" id="filetdR1TestimonialImg1" />
                                            <button type="submit" class="button" id="btnUpload">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR1TestimonialName2" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR1TestimonialImg2">
                                        <label class="custom-file-label" for="tdR1TestimonialImg2">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR1TestimonialName3" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR1TestimonialImg3">
                                        <label class="custom-file-label" for="tdR1TestimonialImg3">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1TestimonialText4" rows="3" placeholder="Testimonial #4"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR1TestimonialName4" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR1TestimonialImg4">
                                        <label class="custom-file-label" for="tdR1TestimonialImg4">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1GGReview" placeholder="Google Link for Review">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1DeliveryMapImg">
                                    <label class="custom-file-label" for="tdR1DeliveryMapImg">Delivery Map Image</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR1DeliveryRate" rows="3" placeholder="Delivery Rates with Locations"></textarea>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR1PromotionBgImg">
                                    <label class="custom-file-label" for="tdR1PromotionBgImg">Promotion Area Background Image</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1HomePromotion2" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR1HomePromotion2Sub" placeholder="Promotion Sub Headline">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
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

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">
                    <input id="cmdSubmit" class="btn btn-success" type="button" value="Save">
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="res1Img" src="../assets/img/Res1Home.png" class="img-fluid">
            </div>

        </div>
    </div>

<!-- Template Restaurant 2 -->
    <div id="TemRes2">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-res2Home" data-bs-toggle="tab" data-bs-target="#res2Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res2Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-res2About" data-bs-toggle="tab" data-bs-target="#res2About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res2About');">About</button>
                        <button class="nav-item nav-link" id="nav-res2Contact" data-bs-toggle="tab" data-bs-target="#res2Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res2Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="res2Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row pt-3">

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HomeSlider1">
                                    <label class="custom-file-label" for="tdR2HomeSlider1">Image Slider Set 1 1920x415 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HomeSlider2">
                                    <label class="custom-file-label" for="tdR2HomeSlider1">Image Slider Set 2 1920x415 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HomeSlider3">
                                    <label class="custom-file-label" for="tdR2HomeSlider3">Image Slider Set 3 1920x415 pixels</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2Delivery" rows="3" placeholder="Pickup & Delivery Service"></textarea>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion1" placeholder="Promotion Headline #1">
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2HomePromotion1Body" rows="3" placeholder="Promotion Message #1"></textarea>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2Carousel">
                                    <label class="custom-file-label" for="tdR2Carousel">Promotion Image Carousel 800x1000 pixels (4 Image)</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FeaturedImg">
                                    <label class="custom-file-label" for="tdR2FeaturedImg">Featured Menu Image #1 600x800 pixels (8 Image)</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2PromotionImg2">
                                    <label class="custom-file-label" for="tdR2PromotionImg2">Promotion Background Image Carousel 2201x1068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion2" placeholder="Promotion Headline #2">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2ReviewBg">
                                    <label class="custom-file-label" for="tdR2ReviewBg">Reviews Background Image 2201x1068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName1" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg1">
                                        <label class="custom-file-label" for="tdR2TestimonialImg1">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName2" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg2">
                                        <label class="custom-file-label" for="tdR2TestimonialImg2">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName3" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg3">
                                        <label class="custom-file-label" for="tdR2TestimonialImg3">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2PromotionImg3">
                                    <label class="custom-file-label" for="tdR2PromotionImg3">Promotion Image #3 952x952 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion3" placeholder="Promotion Headline #3">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion3Body" placeholder="Promotion Message #3">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg1">
                                    <label class="custom-file-label" for="tdR2FooterImg1">Footer Image #1 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg2">
                                    <label class="custom-file-label" for="tdR2FooterImg2">Footer Image #2 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg3">
                                    <label class="custom-file-label" for="tdR2FooterImg3">Footer Image #3 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg4">
                                    <label class="custom-file-label" for="tdR2FooterImg4">Footer Image #4 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion4" placeholder="Promotion Headline #4">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion4Body" placeholder="Promotion Message #4">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterBgImg">
                                    <label class="custom-file-label" for="tdR2FooterBgImg">Footer Background Image 2201x1068 pixels</label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade" id="res2About" role="tabpanel" aria-labelledby="nav-about-tab">
                        <div class="row pt-3">

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2HeadAboutImg">
                                        <label class="custom-file-label" for="tdR2HeadAboutImg">About Page Header Background Image 2201x11068 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <textarea class="form-control" id="tdR2AboutUSBody" rows="3" placeholder="About Us Body"></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <input type="text" class="form-control" id="tdR2AboutPromotion1" placeholder="Promotion Headline">
                                </div>

                                <div>
                                    <textarea class="form-control" id="tdR2AboutPromotion1Body" rows="3" placeholder="Promotion Body"></textarea>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2AboutPromotionImg">
                                        <label class="custom-file-label" for="tdR2AboutPromotionImg">Promotion Image Carousel 850x1275 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg1">
                                        <label class="custom-file-label" for="tdR2StaffImg1">Staff Image #1 600x900 pixels</label>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg2">
                                        <label class="custom-file-label" for="tdR2StaffImg2">Staff Image #2 600x900 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg3">
                                        <label class="custom-file-label" for="tdR2StaffImg3">Staff Image #3 600x900 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2AboutFeaturedImg">
                                        <label class="custom-file-label" for="tdR2AboutFeaturedImg">Featured Dish Image #1</label>
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
                    <div class="tab-pane fade" id="res2Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <div class="row pt-3">

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HeadContactImg">
                                    <label class="custom-file-label" for="tdR2HeadContactImg">Contact Page Header Background Image 2268x1512 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactHeadSub1" placeholder="Contact Us Sub Headline #1">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactHeadSub2" placeholder="Contact Us Sub Headline #2">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2ContactBgImg">
                                    <label class="custom-file-label" for="tdR2ContactBgImg">Contact Page Background Image 2268x1512 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactPromotion1" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactPromotion1sub" placeholder="Promotion Sub Headline">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">

                    <button type="button" class="btn btn-success" id="submitBtn">save</button>
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="res2Img" src="../assets/img/Res2Home.png" class="img-fluid">
            </div>

        </div>
    </div>

<!-- Template Restaurant 3 -->
    <div id="TemRes3">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-res3Home" data-bs-toggle="tab" data-bs-target="#res3Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-res3Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-res3About" data-bs-toggle="tab" data-bs-target="#res3About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res3About');">About</button>
                        <button class="nav-item nav-link" id="nav-res3Contact" data-bs-toggle="tab" data-bs-target="#res3Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-res3Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="res3Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row pt-3">

                            <div>
                                <input type="text" class="form-control" id="tdR3Slogan" placeholder="Business Slogan">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR3HomeIntroduction" placeholder="Business Introduction">
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2HomePromotion1Body" rows="3" placeholder="Promotion Message #1"></textarea>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2Carousel">
                                    <label class="custom-file-label" for="tdR2Carousel">Promotion Image Carousel 800x1000 pixels (4 Image)</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FeaturedImg">
                                    <label class="custom-file-label" for="tdR2FeaturedImg">Featured Menu Image #1 600x800 pixels (8 Image)</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2PromotionImg2">
                                    <label class="custom-file-label" for="tdR2PromotionImg2">Promotion Background Image Carousel 2201x1068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion2" placeholder="Promotion Headline #2">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2ReviewBg">
                                    <label class="custom-file-label" for="tdR2ReviewBg">Reviews Background Image 2201x1068 pixels</label>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText1" rows="3" placeholder="Testimonial #1"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName1" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg1">
                                        <label class="custom-file-label" for="tdR2TestimonialImg1">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText2" rows="3" placeholder="Testimonial #2"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName2" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg2">
                                        <label class="custom-file-label" for="tdR2TestimonialImg2">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <textarea class="form-control" id="tdR2TestimonialText3" rows="3" placeholder="Testimonial #3"></textarea>
                            
                                <div class="d-flex flex-row gap-1">
                                    <input type="text" class="col-8 form-control" id="tdR2TestimonialName3" placeholder="Name">
                                
                                    <div class="col-4 custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2TestimonialImg3">
                                        <label class="custom-file-label" for="tdR2TestimonialImg3">Photo</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2PromotionImg3">
                                    <label class="custom-file-label" for="tdR2PromotionImg3">Promotion Image #3 952x952 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion3" placeholder="Promotion Headline #3">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion3Body" placeholder="Promotion Message #3">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg1">
                                    <label class="custom-file-label" for="tdR2FooterImg1">Footer Image #1 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg2">
                                    <label class="custom-file-label" for="tdR2FooterImg2">Footer Image #2 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg3">
                                    <label class="custom-file-label" for="tdR2FooterImg3">Footer Image #3 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterImg4">
                                    <label class="custom-file-label" for="tdR2FooterImg4">Footer Image #4 600x600 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion4" placeholder="Promotion Headline #4">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2HomePromotion4Body" placeholder="Promotion Message #4">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2FooterBgImg">
                                    <label class="custom-file-label" for="tdR2FooterBgImg">Footer Background Image 2201x1068 pixels</label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade" id="res3About" role="tabpanel" aria-labelledby="nav-about-tab">
                        <div class="row pt-3">

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2HeadAboutImg">
                                        <label class="custom-file-label" for="tdR2HeadAboutImg">About Page Header Background Image 2201x11068 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <textarea class="form-control" id="tdR2AboutUSBody" rows="3" placeholder="About Us Body"></textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <input type="text" class="form-control" id="tdR2AboutPromotion1" placeholder="Promotion Headline">
                                </div>

                                <div>
                                    <textarea class="form-control" id="tdR2AboutPromotion1Body" rows="3" placeholder="Promotion Body"></textarea>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2AboutPromotionImg">
                                        <label class="custom-file-label" for="tdR2AboutPromotionImg">Promotion Image Carousel 850x1275 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg1">
                                        <label class="custom-file-label" for="tdR2StaffImg1">Staff Image #1 600x900 pixels</label>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg2">
                                        <label class="custom-file-label" for="tdR2StaffImg2">Staff Image #2 600x900 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2StaffImg3">
                                        <label class="custom-file-label" for="tdR2StaffImg3">Staff Image #3 600x900 pixels</label>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom-file  mb-3">
                                        <input type="file" class="custom-file-input" id="tdR2AboutFeaturedImg">
                                        <label class="custom-file-label" for="tdR2AboutFeaturedImg">Featured Dish Image #1</label>
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
                    <div class="tab-pane fade" id="res3Contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <div class="row pt-3">

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2HeadContactImg">
                                    <label class="custom-file-label" for="tdR2HeadContactImg">Contact Page Header Background Image 2268x1512 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactHeadSub1" placeholder="Contact Us Sub Headline #1">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactHeadSub2" placeholder="Contact Us Sub Headline #2">
                            </div>

                            <div>
                                <div class="custom-file  mb-3">
                                    <input type="file" class="custom-file-input" id="tdR2ContactBgImg">
                                    <label class="custom-file-label" for="tdR2ContactBgImg">Contact Page Background Image 2268x1512 pixels</label>
                                </div>
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactPromotion1" placeholder="Promotion Headline">
                            </div>

                            <div>
                                <input type="text" class="form-control" id="tdR2ContactPromotion1sub" placeholder="Promotion Sub Headline">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">

                    <button type="button" class="btn btn-success" id="submitBtn">save</button>
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="res3Img" src="../assets/img/Res3Home.png" class="img-fluid">
            </div>
        </div>
    </div>

<!-- Template Massage 1 -->
    <div id="TemMas1">
        
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-mas1Home" data-toggle="tab" data-bs-target="#mas1Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-mas1Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-mas1About" data-bs-toggle="tab" data-bs-target="#mas1About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas1About');">About</button>
                        <button class="nav-item nav-link" id="nav-mas1Services" data-bs-toggle="tab" data-bs-target="#mas1Services" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas1Services');">Services</button>
                        <button class="nav-item nav-link" id="nav-mas1Contact" data-bs-toggle="tab" data-bs-target="#mas1Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas1Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="mas1Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Business Logo - Header</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Business Phone</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">3. Business Name</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Business Slogan</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">5. Order Online URL</label>
                                <input type="text" class="form-control" id="" placeholder="">

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas1About" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. About Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas1Services" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Services Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas1Contact" role="tabpanel" aria-labelledby="contact-tab">


                        <div class="row">
                            <div class="col">

                                <label for="">1. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">3. Contact Us Sub Headline #1</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">3. Contact Us Sub Headline #2</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">5. Promotion Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">6. Promotion Sub Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">

                    <button type="button" class="btn btn-success" id="submitBtn">save</button>
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="mas1Img" src="../assets/img/Mas1Home.png" class="img-fluid">
            </div>
        </div>

    </div>

<!-- Template Massage 2 -->
    <div id="TemMas2">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-mas2Home" data-toggle="tab" data-bs-target="#mas2Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-mas2Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-mas2About" data-bs-toggle="tab" data-bs-target="#mas2About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas2About');">About</button>
                        <button class="nav-item nav-link" id="nav-mas2Services" data-bs-toggle="tab" data-bs-target="#mas2Services" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas2Services');">Services</button>
                        <button class="nav-item nav-link" id="nav-mas2Contact" data-bs-toggle="tab" data-bs-target="#mas2Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas2Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="mas2Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Business Logo - Header</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Business Phone</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">3. Business Name</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Business Slogan</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">5. Order Online URL</label>
                                <input type="text" class="form-control" id="" placeholder="">


                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas2About" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. About Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas2Services" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Services Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas2Contact" role="tabpanel" aria-labelledby="contact-tab">


                        <div class="row">
                            <div class="col">

                                <label for="">1. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">3. Contact Us Sub Headline #1</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">3. Contact Us Sub Headline #2</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">5. Promotion Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">6. Promotion Sub Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">

                    <button type="button" class="btn btn-success" id="submitBtn">save</button>
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="mas2Img" src="../assets/img/Mas2Home.png" class="img-fluid">
            </div>
        </div>
    </div>

<!-- Template Massage 3 -->
    <div id="TemMas3">
        <div class="row">
            <div class="col-6">
                <nav>
                    <div class="nav nav-tabs page-tabs" id="nav-tab" role="tablist">
                        <button class="nav-item nav-link active" id="nav-mas3Home" data-toggle="tab" data-bs-target="#mas3Home" type="button" role="tab" aria-selected="true" onclick="selectPage('tab-mas3Home');">Home</button>
                        <button class="nav-item nav-link" id="nav-mas3About" data-bs-toggle="tab" data-bs-target="#mas3About" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3About');">About</button>
                        <button class="nav-item nav-link" id="nav-mas3Services" data-bs-toggle="tab" data-bs-target="#mas3Services" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3Services');">Services</button>
                        <button class="nav-item nav-link" id="nav-mas3Contact" data-bs-toggle="tab" data-bs-target="#mas3Contact" type="button" role="tab" aria-selected="false" onclick="selectPage('tab-mas3Contact');">Contact</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="mas3Home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Business Logo - Header</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Business Phone</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">3. Business Name</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Business Slogan</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">5. Order Online URL</label>
                                <input type="text" class="form-control" id="" placeholder="">

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3About" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. About Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3Services" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                            <div class="col">

                                <label for="">1. Services Page Header Background Image 2201x11068 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. About Us Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">3. Promotion Headline</label>
                                <input type="text" class="form-control" id="" placeholder="">

                                <label for="">4. Promotion Body</label>
                                <textarea class="form-control" id="" rows="3"></textarea>

                                <label for="">5. Promotion Image 600x900 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">6. Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">7. Callout #1</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">8. Callout #2</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">9. Callout #3</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">10. Callout #4</label>
                                <input type="text" class="form-control homeno3" id="" placeholder="">

                                <label for="">11. Featured Dish Image #1 900x600 pixels </label>
                                <div class="input-group">
                                    <input type="file" class="form-control imginp btn btn-default btn-file" id="imgInp" multiple>
                                </div>
                                <img id='img-upload-1' />
                                <img id='img-upload-2' />
                                <img id='img-upload-3' />
                                <img id='img-upload-4' />
                                <img id='img-upload-5' />
                                <img id='img-upload-6' />

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mas3Contact" role="tabpanel" aria-labelledby="contact-tab">


                        <div class="row">
                            <div class="col">

                                <label for="">1. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">2. Contact Page Header Background Image 2268x1512 pixels</label>
                                <input type="file" class="form-control btn btn-default btn-file" id="">

                                <label for="">3. Contact Us Sub Headline #1</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">3. Contact Us Sub Headline #2</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">5. Promotion Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                                <label for="">6. Promotion Sub Headline</label>
                                <input type="text" class="form-control homeno3" id="HomeNo3" placeholder="">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="changepage" style="overflow:auto;">
                    <div class="col-6" style="text-align:left;">

                    <button type="button" class="btn btn-success" id="submitBtn">save</button>
                    </div>

                    <div class="col-6" style="text-align:right;">

                        <button type="button" class="btn btn-light" id="prevPageBtn" onclick="nextPrev(-1)">Back</button>
                        <button type="button" class="btn btn-primary" id="nextPageBtn" onclick="nextPagePrev(1)">Next</button>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <img id="mas3Img" src="../assets/img/Mas3Home.png" class="img-fluid">
            </div>
        </div>
    </div>

    </div>

<input type="hidden" id="projectID" value="<?php echo $id; ?>">

<!--<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="/assets/js/jquery3.5.1.min.js"></script>-->
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>-->
<!--<script src="../assets/js/bootstrap.bundle.min.js"></script>-->
<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="../controllers/project_detail.js"></script>
<script>



    //console.log("Form Submitted");

    $(document).ready(function(){

        const projectID = $("#projectID").val();

        let payload = {
            mode : "loadArray",
            projectID : projectID
        };

        const callAjax = $.ajax({
            url: "../models/loadArray.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        callAjax.done(function(res) {
            console.log("return = ",res);

            let allData = res.data.length;
            let row = res.data;

            const projectName = row.projectName;
            const shopType = row.shopTypeID;

            $('.projectName').text(projectName);
            $('#businessName').val(projectName);
            $('.businessName').val(projectName);
            $(".shopType").html(shopType === 1 ? "Restaurant" : "Massage");
            $("#ShopType").val(shopType === 1 ? "Restaurant" : "Massage");
            $(".projectOwner").html(row.sNickName);
            $("#projectOwner").val(row.sNickName);
            $(".projectCountry").html(row.countryName);

            return true;
        });

        callAjax.fail(function(xhr, status, error) {
            console.log("ajax fail!!");
            console.log(status + ': ' + error);
            return false;
        });

        setLayout();

        // Upload Group Preview
        function handleFormSubmit(formId, imgId, picNameId, fileInputId, prefixId) {
            $("#" + formId).on("submit", function (e) { // จะทำงานก็ต่อเมื่อกด submit ฟอร์ม
                e.preventDefault(); // ปิดการใช้งาน submit ปกติ เพื่อใช้งานผ่าน ajax

                let fd = new FormData(); // สร้างตัวแปรมาเตรียมไว้ที่เราจะเก็บข้อมูลในฟอร์ม (รูปที่เลือกมา)
                let files = $("#" + fileInputId)[0].files; //เป็นการดึงข้อมูลรูปภาพเพื่อเตรียมเช็คไฟล์ก่อนทำงานส่วน Ajax
                let projectId = projectID;

                if (files.length > 0) { // เช็คว่ามีไฟล์รูปภาพอยู่หรือไม่

                    fd.append('file', files[0]); //ดึงไฟล์รูปภาพไปใส่ตัวแปรที่เตรียมไว้
                    fd.append('projectId', projectId);
                    fd.append('prefixId', prefixId);

                    $.ajax({
                        url: '../models/upload.php',
                        type: 'post',
                        data: fd, //ข้อมูลไฟล์รูปภาพจาก input ที่อ่านมา
                        contentType: false,
                        processData: false,
                        success: function(response) { //พอทำงาน ajax สำเร็จ จะรับค่ามาจาก JSON เก็บในตัวแปร response

                            if (response !== "0") {
                                let fullPath = response;
                                const splitPath = fullPath.split("/");
                                const newName = splitPath[3];

                                $("#" + imgId).attr("src", response);
                                $("#" + picNameId).val(newName);
                            } else {
                                alert("File not uploaded");
                            }
                        }
                    });
                } else {
                    alert("Please select a file.");
                }
            });
        }

        //PATTERN -> handleFormSubmit(formId, imgId, picNameId,, fileInputId, prefixId(ตั้งอะไรก็ได้ตรงนี้เลยไม่ได้ดึงจากฟอร์ม))
        handleFormSubmit("myFormLogo", "imgLogo", "picNameLogo", "fileLogo", "Logo");
        handleFormSubmit("myFormTdR1HeadHomeImg", "tdR1HeadHomeImg", "picNametdR1HeadHomeImg", "filetdR1HeadHomeImg", "HeadHomeImg");
        handleFormSubmit("myFormTdR1Featured1", "tdR1Featured1", "picNametdR1Featured1", "filetdR1Featured1", "Featured1");
        handleFormSubmit("myFormTdR1Featured2", "tdR1Featured2", "picNametdR1Featured2", "filetdR1Featured2", "Featured2");
        handleFormSubmit("myFormTdR1Featured3", "tdR1Featured3", "picNametdR1Featured3", "filetdR1Featured3", "Featured3");
        handleFormSubmit("myFormTdR1Featured4", "tdR1Featured4", "picNametdR1Featured4", "filetdR1Featured4", "Featured4");

        // End Upload Group Preview
    
    });

    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

        //BUSINESS_DETAILS
            businessName: $('#businessName').val(),
            //businessLogo: $('#businessName').val(),
            businessEmail: $('#businessEmail').val(),
            businessPhone: $('#businessPhone').val(),
            businessAddress: $('#businessAddress').val(),
            openingHours: $('#openingHours').val(),
            colorInput: $('#colorInput').val(),
            domainHave: $("#domainHave").prop("checked") ? 1 : 0,
            domainType: $('#domainType').val(),
            domainUser: $('#domainUser').val(),
            domainPass: $('#domainPass').val(),
            hostingHave: $('#hostingHave').prop("checked") ? 1 : 0,
            //hostingType: $('#hostingType').prop("checked") ? 1 : 0,
            hostingUser: $('#hostingUser').val(),
            hostingPass: $('#hostingPass').val(),
            //resSystem: $('#resSystem').prop("checked") ? 1 : 0,
            gloriaHave: $('#gloriaHave').prop("checked") ? 1 : 0,
            orderURL: $('#orderURL').val(),
            tableURL: $('#tableURL').val(),
            orderOther: $('#orderOther').prop("checked") ? 1 : 0,
            resOtherSystem: $('#resOtherSystem').val(),
            //masSystem: $('#masSystem').prop("checked") ? 1 : 0,
            amelia: $('#amelia').prop("checked") ? 1 : 0,
            voucher: $('#voucher').prop("checked") ? 1 : 0,
            bookOther: $('#bookOther').prop("checked") ? 1 : 0,
            masOtherSystem: $('#masOtherSystem').val(),
            facebookURL: $('#facebookURL').val(),
            instagramURL: $('#instagramURL').val(),
            youtubeURL: $('#youtubeURL').val(),
            tiktokURL: $('#tiktokURL').val(),
            projectOwner: $('#projectOwner').val(),
            token: Math.random(),

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

        //TEMPLATE_R3_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_R3_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_R3_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M1_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M1_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M1_PAGE_SERVICE
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M1_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_SERVICE
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M3_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M3_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M3_PAGE_SERVICE
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M3_PAGE_CONTACT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),
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

        // callAjax.done(function(res) {
        //     console.log('return',res);
        //     return true;
        // });

        // callAjax.fail(function(xhr, status, error) {
        //     console.log("ajax fail!!");
        //     console.log(status + ': ' + error);
        //     return false;
        // });
    });

</script>
