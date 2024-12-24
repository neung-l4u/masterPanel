<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT  p.projectName, p.shopTypeID, IF(p.shopTypeID=1, "Restaurant", "Massage") as "typeName", p.countryID, c.name AS "countryName", s.sNickName 
        FROM tb_project p, staffs s, Countries c 
        WHERE p.projectOwner = s.sID AND p.countryID = c.id AND p.projectID = ?;', $id)->fetchArray();

?>



<!--    <link rel="stylesheet" href="../assets/css/bootstrap.4.5.2.min.css">-->
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css" xmlns:input="http://www.w3.org/1999/html">
    <link rel="stylesheet" href="../assets/css/project_detail.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">


<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../views/main.php?m=project">Projects</a></li>
        <li class="breadcrumb-item active projectName" aria-current="page" id="projectName"><?php echo $row['projectName']; ?></li>
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
                    <span class="projectName"><?php echo $row['projectName']; ?></span>
                </li>
                <li>
                    <span class="fw-bold">Type: </span>
                    <span class="shopType"><?php echo $row['typeName']; ?></span>
                </li>
                <li>
                    <span class="fw-bold">PO: </span>
                    <span class="projectOwner"><?php echo $row['sNickName']; ?></span>
                </li>
                <li>
                    <span class="fw-bold">Country:</span>
                    <span class="projectCountry"><?php echo $row['countryName']; ?></span>
                </li>
            </ul>
        </div>
    </div>

    <input type="hidden" class="form-control" id="projectOwner" value="<?php echo $row['sNickName']; ?>">
    <input type="hidden" class="form-control" id="ShopType" value="<?php echo $row['typeName']; ?>">
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
                        <label for="bsName" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="bsName" placeholder="" value="<?php echo $row['projectName']; ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="bsEmail" class="input-group-text" id="basic-addon3">Email</label>
                                <input type="email" class="form-control" id="bsEmail" placeholder="admin@localforyou.com">
                            </div>
                        </div>
                        <div class="col">
                                <div class="input-group mb-3">
                                    <label for="bsPhone" class="input-group-text" id="basic-addon3">Phone</label>
                                    <input type="tel" class="form-control" id="bsPhone" placeholder="+6112345678">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="bsAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="bsAddress" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="bsOpen" class="form-label">Opening hour</label>
                        <textarea class="form-control" id="bsOpen" rows="7"></textarea>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="bsOpen" class="form-label">Pickup And Delivery</label>
                        <textarea class="form-control" id="bsPickup" rows="7"></textarea>
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
                            <input type="hidden" id="picNameLogo" value="">
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
                <input class="form-check-input domainHave" type="checkbox" value="1" id="domainHave">
                <label for="domainHave">Yes, we got it.</label>
                <div class="domainbox" id="domainBox">
                    <div class="mb-4 row">
                        <label for="domainProvider" class="col-sm-2 col-form-label">Provider</label>
                        <div class="col-sm-10">
                            <select id="domainProvider" class="custom-select">
                                <option value="0" selected>-- None --</option>
                                <?php
                                $domains = $db->query('SELECT `id`, `name` FROM `DomainProviders` WHERE status=1 AND id<>11 ORDER BY `name`;')->fetchAll();
                                foreach ($domains as $row){
                                    ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php }//foreach ?>
                                <option value="11">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                            <div class="input-group mb-3">
                                <label for="domainUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control domainuser" id="domainUser" aria-describedby="basic-addon3" placeholder="">
                            </div>
                        
                            <div class="input-group mb-3">
                                <label for="domainPass" class="input-group-text" id="basic-addon3">Password</label>
                                <input type="text" class="form-control domainpass" id="domainPass" aria-describedby="basic-addon3" placeholder="">
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
                    <div class="mb-4 row">
                        <label for="hostingProvider" class="col-sm-2 col-form-label">Provider</label>
                        <div class="col-sm-10">
                            <select id="hostingProvider" class="custom-select">
                                <option value="0" selected>-- None --</option>
                                <?php
                                $hostings = $db->query('SELECT `id`, `name` FROM `HostingProviders` WHERE status=1 AND id<>11 ORDER BY `name`;')->fetchAll();
                                foreach ($hostings as $row){
                                    ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php }//foreach ?>
                                <option value="11">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                            <div class="input-group mb-3">
                                <label for="hostingUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control hostingUser" id="hostingUser" aria-describedby="basic-addon3" placeholder="">
                            </div>
                            <div class="input-group mb-3">
                                <label for="hostingPass" class="input-group-text" id="basic-addon3">Password</label>
                                <input type="text" class="form-control hostingPass" id="hostingPass" aria-describedby="basic-addon3" placeholder="">
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Require Systems -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Require Systems.</h5>
        </div>
    </div>

        <!-- Restaurant System -->
            <div id="resSystem" class="form-check">
                <div>
                    <input class="form-check-input gloriahave" type="checkbox" value="" id="gloriaHave">
                    <label for="gloriaHave">Gloria Food</label>
                </div>

                <div>
                    <div class="gloriabox">
                        <label for="orderURL">Order Online</label>
                        <input type="text" class="form-control" id="orderURL" placeholder="-- URL --">
                    </div>
                    <div class="gloriabox">
                        <label for="tableURL">Table Reservation</label>
                        <input type="text" class="form-control" id="tableURL" placeholder="-- URL --">
                    </div>
                </div>

                <div>
                    <input class="form-check-input orderOther" type="checkbox" value="" id="orderOther">
                    <label for="orderOther">Other Ordering System</label>
                    <div class="resOtherSystem">
                        <label for="resOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="resOtherSystem" placeholder="System Name">
                    </div>
                </div>
            </div>

        <!-- Massage System -->

            <div id="masSystem" class="form-check">
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
                    <div class="masOtherSystem">
                        <label for="masOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="masOtherSystem" placeholder="System Name">
                    </div>
                </div>
            </div>

        <div class="form-check">
            <input class="form-check-input orderOther" type="checkbox" value="1" id="needEmail">
            <label for="needEmail">Need email inbox under shop domain name.</label>
            <small class="text-muted">e.g., info@hoonhaymassage.co.nz</small>
                <div class="masOtherSystem">
                        <label for="masOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="masOtherSystem" placeholder="System Name">
                    </div>
        </div>
</div>

<!-- Social Media -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Social Media.</h5>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="input-group mb-3">
                <label for="facebookURL" class="input-group-text">Facebook</label>
                <input type="text" class="form-control" id="facebookURL" placeholder="URL">
            </div>
        </div>
        <div class="col">
            <div class="input-group mb-3">
                <label for="instagramURL" class="input-group-text">Instagram</label>
                <input type="text" class="form-control" id="instagramURL" placeholder="URL">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="input-group mb-3">
                <label for="youtubeURL" class="input-group-text">Youtube</label>
                <input type="text" class="form-control" id="youtubeURL" placeholder="URL">
            </div>
        </div>
        <div class="col">
            <div class="input-group mb-3">
                <label for="tiktokURL" class="input-group-text">Tiktok</label>
                <input type="text" class="form-control" id="tiktokURL" placeholder="URL">
            </div>
        </div>
    </div>
</div>

<!-- Website Information -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Template Details.</h5>
        </div>
    </div>

    <!-- Template Type -->
    <div class="row">
        <div class="col-6 mb-4">
            <label for="TemplateSelect"><span class="shopType">Unknown</span> Template</label>
            <select class="form-select" id="TemplateSelect" onchange="setLayout();">
                <option selected disabled> -- Select Template -- </option>
                <option value="1">Template 1</option>
                <option value="2">Template 2</option>
                <option value="3">Template 3</option>
            </select>
        </div>
        <div class="col-6 placeholder bg-secondary text-light">
            template pic here
        </div>
    </div>
</div>

<div class="row mb-5">
    <div>
        <input id="cmdSubmit" class="btn btn-primary" type="button" value="Save">
    </div>
</div>

<input type="hidden" id="projectID" value="<?php echo $id; ?>">
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="../controllers/project_detail.js"></script>
<script>
    const projectID = $("#projectID").val();
    const hiddenShopType = $("#ShopType");
    const inputName = $("#bsName");
    const inputEmail = $("#bsEmail");
    const inputPhone = $("#bsPhone");
    const inputAddress = $("#bsAddress");
    const inputOpen = $("#bsOpen");
    const inputPickup = $("#bsPickup");
    const hiddenPicLogo = $("#picNameLogo");
    const selTheme1Hex = $("#theme1");
    const selTheme2Hex = $("#theme2");
    const selTheme3Hex = $("#theme3");
    const TemplateSelect = $("#TemplateSelect");
    const chkDomainHave = $("#domainHave");
    const selDomainProvider = $("#domainProvider");
    const inputDomainUser = $("#domainUser");
    const inputDomainPass = $("#domainPass");
    const chkNeedEmail = $("#needEmail");
    const chkHostingHave = $("#hostingHave");
    const selHostingProvider = $("#hostingProvider");
    const inputHostingUser = $("#hostingUser");
    const inputHostingPass = $("#hostingPass");
    const chkGloriaHave = $("#gloriaHave");
    const inputOrderURL = $("#orderURL");
    const inputTableURL = $("#tableURL");
    const chkOrderOther = $("#orderOther");
    const inputResOtherSystem = $("#resOtherSystem");
    const chkAmelia = $("#amelia");
    const chkVoucher = $("#voucher");
    const chkBookOther = $("#bookOther");
    const inputMasOtherSystem = $("#masOtherSystem");
    const inputFacebookURL = $("#facebookURL");
    const inputInstagramURL = $("#instagramURL");
    const inputYoutubeURL = $("#youtubeURL");
    const inputTiktokURL = $("#tiktokURL");
    const hiddenProjectOwner = $("#projectOwner");


    $(()=>{ //ready
        loadProjectData();
    }); //ready

    const loadProjectData = () => {
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
            console.log("loadProjectData = ",res)
            setLayout();
            selectPage();
            return true;
        });

        callAjax.fail(function(xhr, status, error) {
            console.log("ajax fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }//const loadProjectData

    $("#cmdSubmit").click(function () {
        let payload = {
            mode : "save",
            businessName: inputName.val(),
            businessEmail: inputEmail.val(),
            businessPhone: inputPhone.val(),
            businessAddress: inputAddress.val(),
            openingHours: inputOpen.val(),
            pickupAndDelivery: inputPickup.val(),
            logo: hiddenPicLogo.val(),
            colorTheme1: selTheme1Hex.val(),
            colorTheme2: selTheme2Hex.val(),
            colorTheme3: selTheme3Hex.val(),
            domainHave: !!chkDomainHave.prop("checked"),
            domainProvider: selDomainProvider.val(),
            domainUser: inputDomainUser.val(),
            domainPass: inputDomainPass.val(),
            hostingHave: !!chkHostingHave.prop("checked"),
            hostingUser: inputHostingUser.val(),
            hostingPass: inputHostingPass.val(),
            gloriaHave: !!chkGloriaHave.prop("checked"),
            orderURL: inputOrderURL.val(),
            tableURL: inputTableURL.val(),
            orderOther: !!chkOrderOther.prop("checked"),
            resOtherSystem: inputResOtherSystem.val(),
            amelia: !!chkAmelia.prop("checked"),
            voucher: !!chkVoucher.prop("checked"),
            bookOther: !!chkBookOther.prop("checked"),
            needEmail: !!chkNeedEmail.prop("checked"),
            masOtherSystem: inputMasOtherSystem.val(),
            facebookURL: inputFacebookURL.val(),
            instagramURL: inputInstagramURL.val(),
            youtubeURL: inputYoutubeURL.val(),
            tiktokURL: inputTiktokURL.val(),
            projectOwner: hiddenProjectOwner.val(),
            projectID: projectID,
            shopType: hiddenShopType.val(),
            TemplateSelect: TemplateSelect.val(),
            token: Math.random()
        };

        console.log("Payload", payload);

        const callAjax = $.ajax({
            url: "../models/project_detail.php",
            method: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: payload
        });

        callAjax.done(function(res) {
            console.log('return',res);
            return true;
        });

        callAjax.fail(function(xhr, status, error) {
            console.log("ajax fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    });//cmdSubmit.click


    const handleFormSubmit = (formId, imgId, picNameId, fileInputId, prefixId) => {
        $("#" + formId).on("submit", function (e) {
            e.preventDefault();
            let fd = new FormData();
            let files = $("#" + fileInputId)[0].files;

            if (files.length > 0) {
                fd.append('file', files[0]);
                fd.append('projectId', projectID);
                fd.append('prefixId', prefixId);

                $.ajax({
                    url: '../models/upload.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        if (response !== "0") {
                            const splitPath = response.split("/");
                            const newName = splitPath[3];
                            $("#" + imgId).attr("src", response);
                            $("#" + picNameId).val(newName);
                        }else { alert("File not uploaded"); }
                    }
                });//ajax
            } else { alert("Please select a file."); }
        });//on submitting
    }//function handleFormSubmit

    //Upload Group Preview
    handleFormSubmit("myFormLogo", "imgLogo", "picNameLogo", "fileLogo", "Logo");
    /* อันนี้น่าจะเป็นของหน้า template อื่น
    handleFormSubmit("myFormTdR1HeadHomeImg", "tdR1HeadHomeImg", "picNametdR1HeadHomeImg", "filetdR1HeadHomeImg", "HeadHomeImg");
    handleFormSubmit("myFormTdR1Featured1", "tdR1Featured1", "picNametdR1Featured1", "filetdR1Featured1", "Featured1");
    handleFormSubmit("myFormTdR1Featured2", "tdR1Featured2", "picNametdR1Featured2", "filetdR1Featured2", "Featured2");
    handleFormSubmit("myFormTdR1Featured3", "tdR1Featured3", "picNametdR1Featured3", "filetdR1Featured3", "Featured3");
    handleFormSubmit("myFormTdR1Featured4", "tdR1Featured4", "picNametdR1Featured4", "filetdR1Featured4", "Featured4");*/
    // End Upload Group Preview

</script>
