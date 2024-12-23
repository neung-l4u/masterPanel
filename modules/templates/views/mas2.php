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

<link rel="stylesheet" href="../assets/css/project_detail.css">

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
                                <img id='img-upload-1' alt="image upload 1"/>
                                <img id='img-upload-2' alt="image upload 2"/>
                                <img id='img-upload-3' alt="image upload 3"/>
                                <img id='img-upload-4' alt="image upload 4"/>
                                <img id='img-upload-5' alt="image upload 5"/>
                                <img id='img-upload-6' alt="image upload 6"/>

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
                                <img id='img-upload-1' alt="image upload 1"/>
                                <img id='img-upload-2' alt="image upload 2"/>
                                <img id='img-upload-3' alt="image upload 3"/>
                                <img id='img-upload-4' alt="image upload 4"/>
                                <img id='img-upload-5' alt="image upload 5"/>
                                <img id='img-upload-6' alt="image upload 6"/>

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
                <img id="mas2Img" src="../assets/img/Mas2Home.png" class="img-fluid" alt="Massage 2">
            </div>
        </div>
    </div>

<script>
    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",

//TEMPLATE_M2_PAGE_HOME
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_ABOUT
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_SERVICE
            //tdR2HeadContactImg: $('#tdR2HeadContactImg').val(),

        //TEMPLATE_M2_PAGE_CONTACT
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

    });
</script>