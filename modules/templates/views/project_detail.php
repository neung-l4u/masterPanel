<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");
global $db, $date;
$id=$_REQUEST['id'];

$row = $db->query('SELECT  p.*, IF(p.shopTypeID=1, "Restaurant", "Massage") as "typeName", p.countryID, c.name AS "countryName", s.sNickName 
        FROM tb_project p, staffs s, Countries c 
        WHERE p.projectOwner = s.sID AND p.countryID = c.id AND p.projectID = ?;', $id)->fetchArray();

// set default value
$row['colorTheme1'] = !empty($row['colorTheme1']) ? $row['colorTheme1'] : '#000000';
$row['colorTheme2'] = !empty($row['colorTheme2']) ? $row['colorTheme2'] : '#FFFFFF';
$row['colorTheme3'] = !empty($row['colorTheme3']) ? $row['colorTheme3'] : '#FFFFFF';

?>
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
                <li>
                    <span class="fw-bold">Template:</span>
                    <span class="shopType"><?php echo ($row['shopTypeID']==1 ? "Restaurant" : "Massage"); ?></span> Template No. <?php echo $row['selectedTemplate']; ?>
                </li>
            </ul>
        </div>
    </div>

    <input type="hidden" class="form-control" id="projectOwner" value="<?php echo $row['sNickName']; ?>">
    <input type="hidden" class="form-control" id="ShopType" value="<?php echo $row['typeName']; ?>">
    <label for="TemplateSelect"></label>
    <select class="form-select" id="TemplateSelect" style="display: none;">
        <option value="1" <?php echo ($row['selectedTemplate']==1) ? "selected":""; ?>>Template 1</option>
        <option value="2" <?php echo ($row['selectedTemplate']==2) ? "selected":""; ?>>Template 2</option>
        <option value="3" <?php echo ($row['selectedTemplate']==3) ? "selected":""; ?>>Template 3</option>
    </select>
</div>

<!-- Template Type -->
<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <div class="mb-2">
                <h5 class="text-secondary">Template Example.</h5>
            </div>
            <?php
            $picName = ($row['shopTypeID']==1 ? "Res" : "Mas").$row['selectedTemplate']."Home.png";
            ?>
            <img src="../assets/img/<?php echo $picName; ?>" alt="<?php echo $picName; ?>" style="width: 100%;">
        </div>
    </div>
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
                        <label for="bsName" class="form-label">Business Name:</label>
                        <?php echo $row['projectName']; ?> <small class="text-muted">(not allow to edit)</small>
                        <input type="hidden" class="form-control" id="bsName" placeholder="" value="<?php echo $row['projectName']; ?>" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="bsEmail" class="input-group-text" id="basic-addon3">Email</label>
                                <input type="email" class="form-control" id="bsEmail" maxlength="100" placeholder="admin@localforyou.com" value="<?php echo $row['email']; ?>">
                            </div>
                        </div>
                        <div class="col">
                                <div class="input-group mb-3">
                                    <label for="bsPhone" class="input-group-text" id="basic-addon3">Phone</label>
                                    <input type="tel" class="form-control" id="bsPhone" placeholder="+6112345678" maxlength="15" value="<?php echo $row['phone']; ?>">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="bsAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="bsAddress" rows="3" ><?php echo $row['address']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="bsOpen" class="form-label">Opening hour</label>
                        <textarea class="form-control" id="bsOpen" rows="7"><?php echo $row['openingHours']; ?></textarea>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <input class="form-check-input" type="checkbox" id="chPickup" name="chPickup" value="1" onclick="chShowtextPickup();">
                        <label for="bsPickup" class="form-label">Set Default time</label>
                        <textarea class="form-control" id="bsPickup" rows="7"><?php echo $row['pickupAndDelivery']; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-5">
            <div class="row">
                <div class="col py-3 px-3">
                    <label for="formLogo" class="form-label">Logo</label>
                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formLogo">
                        <img class="preview" src="../assets/img/default.png" alt="place">
                        <input class="picname" type="hidden" value="">
                        <div class="row">
                            <input type="file" class="file-input col-8" />
                            <button type="button" class="button col" onclick="handleFormSubmit(this)">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mt-3 border rounded py-3 pl-3">
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme1">Color Theme #1</label>
                    <input type="color" onchange="setHex(this.value,1);" id="theme1" value="<?php echo $row['colorTheme1']; ?>">
                    <span id="theme1Hex" class="codeHex"><?php echo $row['colorTheme1']; ?></span>
                </div>
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme2">Color Theme #2</label>
                    <input type="color" onchange="setHex(this.value,2);" id="theme2" value="<?php echo $row['colorTheme2']; ?>">
                    <span id="theme2Hex" class="codeHex"><?php echo $row['colorTheme2']; ?></span>
                </div>
                <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                    <label for="theme3">Color Theme #3</label>
                    <input type="color" onchange="setHex(this.value,3);"  id="theme3" value="<?php echo $row['colorTheme3']; ?>">
                    <span id="theme3Hex" class="codeHex"><?php echo $row['colorTheme3']; ?></span>
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
            <div class="mb-3">
                <label for="domainName" class="form-label">Domain Name</label>
                <input type="text" class="form-control" id="domainName" placeholder="domain.com" value="<?php echo $row['domainName']; ?>">
            </div>
        </div>
        <div class="col-6">
            <div class="mb-3">
                <label for="hostingName" class="form-label">Web Hosting Name</label>
                <input type="text" class="form-control" id="hostingName" placeholder="hosting.com" value="<?php echo $row['hostingName']; ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <label for="domainHave">Domain Log-in</label>
            <div class="form-check ml-1">
                <input class="form-check-input domainHave" type="checkbox" value="" id="domainHave" <?php echo $row['domainHave'] == 1 ? 'checked' : ''; ?>>
                <label for="domainHave">Yes, we got it.</label>
                <div class="domainbox" id="domainBox">
                    <div class="mb-4 row">
                        <label for="domainProvider" class="col-sm-2 col-form-label">Provider</label>
                        <div class="col-sm-10">
                            <select id="domainProvider" class="custom-select">
                                <option value="0" selected>-- None --</option>
                                <?php
                                $domains = $db->query('SELECT `id`, `name` FROM `DomainProviders` WHERE status=1 AND id<>11 ORDER BY `name`;')->fetchAll();
                                foreach ($domains as $val){
                                    ?>
                                    <option value="<?php echo $val['id']; ?>" <?php echo $row['domainProvidersID'] == $val['id'] ? 'selected' : ''; ?> >
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php }//foreach ?>
                                <option value="11">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                            <div class="input-group mb-3">
                                <label for="domainUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control domainuser" id="domainUser" aria-describedby="basic-addon3" placeholder="" value="<?php echo $row['domainUser']; ?>">
                            </div>
                        
                            <div class="input-group mb-3">
                                <label for="domainPass" class="input-group-text" id="basic-addon3">Pass</label>
                                <input type="text" class="form-control domainpass" id="domainPass" aria-describedby="basic-addon3" placeholder="" value="<?php echo $row['domainPass']; ?>">
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Hosting -->
        <div class="col-6">
            <label for="hostingHave">Hosting Log-in</label>
            <div class="form-check ml-1">
                <input class="form-check-input hostingHave" type="checkbox" value="" id="hostingHave" <?php echo $row['hostingHave'] == 1 ? 'checked' : ''; ?>>
                <label for="hostingHave">Yes, we got it.</label>
                <div class="hostingbox" id="hostingBox">
                    <div class="mb-4 row">
                        <label for="hostingProvider" class="col-sm-2 col-form-label">Provider</label>
                        <div class="col-sm-10">
                            <select id="hostingProvider" class="custom-select">
                                <option value="0" selected>-- None --</option>
                                <?php
                                $hosting = $db->query('SELECT `id`, `name` FROM `HostingProviders` WHERE status=1 AND id<>11 ORDER BY `name`;')->fetchAll();
                                foreach ($hosting as $val){
                                    ?>
                                    <option value="<?php echo $val['id']; ?>"  <?php echo $row['hostingProvidersID'] == $val['id'] ? 'selected' : ''; ?> >
                                        <?php echo $val['name']; ?>
                                    </option>
                                <?php }//foreach ?>
                                <option value="11">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                            <div class="input-group mb-3">
                                <label for="hostingUser" class="input-group-text" id="basic-addon3">User</label>
                                <input type="text" class="form-control hostingUser" id="hostingUser" aria-describedby="basic-addon3" placeholder="" value="<?php echo $row['hostingUser']; ?>">
                            </div>
                            <div class="input-group mb-3">
                                <label for="hostingPass" class="input-group-text" id="basic-addon3">Pass</label>
                                <input type="text" class="form-control hostingPass" id="hostingPass" aria-describedby="basic-addon3" placeholder="" value="<?php echo $row['hostingPass']; ?>">
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
                    <input class="form-check-input" type="checkbox" value="" id="noSystem">
                    <label for="noSystem">None</label>
                </div>
            
                <div>
                    <input class="form-check-input gloriahave" type="checkbox" value="" id="gloriaHave" <?php echo $row['gloriaHave'] == 1 ? 'checked' : ''; ?>>
                    <label for="gloriaHave">Gloria Food</label>
                </div>

                <div>
                    <div class="gloriabox">
                        <label for="orderURL">Order Online</label>
                        <input type="text" class="form-control" id="orderURL" placeholder="-- URL --" value="<?php echo $row['orderURL']; ?>">
                    </div>
                    <div class="gloriabox">
                        <label for="tableURL">Table Reservation</label>
                        <input type="text" class="form-control" id="tableURL" placeholder="-- URL --" value="<?php echo $row['tableURL']; ?>">
                    </div>
                </div>

                <div>
                    <input class="form-check-input orderOther" type="checkbox" value="" id="orderOther" <?php echo $row['orderOther'] == 1 ? 'checked' : ''; ?>>
                    <label for="orderOther">Other Ordering System</label>
                    <div class="resOtherSystem">
                        <label for="resOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="resOtherSystem" placeholder="System Name" value="<?php echo $row['resOtherSystem']; ?>">
                    </div>
                </div>

            </div>

        <!-- Massage System -->

            <div id="masSystem" class="form-check">
                <div>
                    <input class="form-check-input" type="checkbox" value="" id="noSystem" >
                    <label for="noSystem">None</label>
                </div>
                <div>
                    <input class="form-check-input amelia" type="checkbox" value="" id="amelia" <?php echo $row['amelia'] == 1 ? 'checked' : ''; ?>>
                    <label for="amelia">Amelia</label>
                </div>

                <div>
                    <input class="form-check-input voucher" type="checkbox" value="" id="voucher" <?php echo $row['	voucher'] == 1 ? 'checked' : ''; ?>>
                    <label for="voucher">Voucher</label>
                </div>

                <div>
                    <input class="form-check-input bookOther" type="checkbox" value="" id="bookOther" <?php echo $row['bookOther'] == 1 ? 'checked' : ''; ?>>
                    <label for="bookOther">Other Booking System</label>
                    <div class="masOtherSystem">
                        <label for="masOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="masOtherSystem" placeholder="System Name" value="<?php echo $row['masOtherSystem']; ?>">
                    </div>
                </div>
            </div>

        <div class="form-check">
            <input class="form-check-input orderOther" type="checkbox" value="1" id="needEmail" <?php echo $row['needEmail'] == 1 ? 'checked' : ''; ?>>
            <label for="needEmail">Need email inbox under shop domain name.</label>
            <small class="text-muted">e.g., info@hoonhaymassage.co.nz</small>
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
                <input type="text" class="form-control" id="facebookURL" placeholder="URL" value="<?php echo $row['facebookURL']; ?>">
            </div>
        </div>
        <div class="col">
            <div class="input-group mb-3">
                <label for="instagramURL" class="input-group-text">Instagram</label>
                <input type="text" class="form-control" id="instagramURL" placeholder="URL" value="<?php echo $row['instagramURL']; ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="input-group mb-3">
                <label for="youtubeURL" class="input-group-text">Youtube</label>
                <input type="text" class="form-control" id="youtubeURL" placeholder="URL" value="<?php echo $row['youtubeURL']; ?>">
            </div>
        </div>
        <div class="col">
            <div class="input-group mb-3">
                <label for="tiktokURL" class="input-group-text">Tiktok</label>
                <input type="text" class="form-control" id="tiktokURL" placeholder="URL" value="<?php echo $row['tiktokURL']; ?>">
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col">
        <input id="cmdSubmit" class="btn btn-primary" type="button" value="Save">
    </div>
    <div class="col text-right">
        <?php
        $url = "main.php?";
        $url .= "m=".($row['shopTypeID']==1 ? "res" : "mas").$row['selectedTemplate'];
        $url .= "&id=".$id;
        ?>
        <small id="infoText" class="text-warning">Please save before proceeding.</small>
        <button id="nextBtn" class="btn btn-secondary" onclick="nextForm('<?php echo $url; ?>')" disabled>Next</button>
    </div>
</div>

<input type="hidden" id="projectID" value="<?php echo $id; ?>">
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="../controllers/project_detail.js"></script>
<script>
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
    const domainName = $("#domainName");
    const chkDomainHave = $("#domainHave");
    const selDomainProvider = $("#domainProvider");
    const inputDomainUser = $("#domainUser");
    const inputDomainPass = $("#domainPass");
    const hostingName = $("#hostingName");
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
    const domainBox = $("#domainBox");
    const hostingBox = $("#hostingBox");


    $(()=>{ //ready
        loadProjectData();
        showSystem();
        $("#bsPickup").hide();
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
            res.domainHave === 1 ? domainBox.show() :  domainBox.hide();
            res.hostingHave === 1 ? hostingBox.show() :  hostingBox.hide();
            res.masOtherSystem === 1 ? inputMasOtherSystem.show() :  inputMasOtherSystem.hide();
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
            projectName: inputName.val(),
            email: inputEmail.val(),
            phone: inputPhone.val(),
            address: inputAddress.val(),
            openingHours: inputOpen.val(),
            pickupAndDelivery: inputPickup.val(),
            logo: hiddenPicLogo.val(),
            colorTheme1: selTheme1Hex.val(),
            colorTheme2: selTheme2Hex.val(),
            colorTheme3: selTheme3Hex.val(),
            domainName: domainName.val(),
            hostingName: hostingName.val(),
            domainHave: !!chkDomainHave.prop("checked"),
            domainProvidersID: selDomainProvider.val(),
            domainUser: inputDomainUser.val(),
            domainPass: inputDomainPass.val(),
            hostingHave: !!chkHostingHave.prop("checked"),
            hostingProvidersID: selHostingProvider.val(),
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

    const showSystem = () => {
      const shopType = $("#ShopType").val();
      console.log("shopType",shopType);
      if (shopType === "Restaurant") {
        $("#resSystem").show();
        $("#masSystem").hide();
      } else if (shopType === "Massage") {
        $("#resSystem").hide();
        $("#masSystem").show();
      }
    }//showSystem
</script>
