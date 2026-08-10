<?php
require_once "../assets/db/db.php";
require_once "../assets/db/initDB.php";
require_once "../assets/php/share_function.php";
global $db;
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Project ID is required.");
}

$icon = '<svg xmlns="http://www.w3.org/2000/svg" style="width: 0.8em;" viewBox="0 0 321.74 321.74">
<path d="M139.7,86.37h133.21c26.88,0,48.83,21.95,48.83,48.83v137.71c0,26.88-21.95,48.83-48.83,48.83h-133.21c-26.88,0-48.83-21.95-48.83-48.83v-137.71c0-26.88,21.94-48.83,48.83-48.83ZM0,186.54V48.83C0,21.98,21.97,0,48.83,0h133.21v22.46H48.83c-14.51,0-26.37,11.85-26.37,26.37v137.71H0ZM45.43,229.73V92.02c0-26.85,21.97-48.83,48.83-48.83h133.21v22.46H94.26c-14.51,0-26.37,11.86-26.37,26.37v137.71h-22.46ZM272.9,108.84h-133.21c-14.48,0-26.37,11.89-26.37,26.37v137.71c0,14.48,11.89,26.37,26.37,26.37h133.21c14.48,0,26.37-11.89,26.37-26.37v-137.71c0-14.48-11.89-26.37-26.37-26.37Z"/>
</svg>';

$row = $db->query('
    SELECT p.*,
    IF(p.shopTypeID=1, "Restaurant", "Massage") AS typeName,
    c.name AS countryName,
    s.sNickName
    FROM tb_project p
    JOIN staffs s ON p.projectOwner = s.sID
    JOIN Countries c ON p.countryID = c.id
    WHERE p.projectID = ?
    ', $id)->fetchArray();
if (!$row) {
    die("Project not found.");
}

$logoName = $row["logo"] ?: "";
// สร้าง logo path ที่ถูกต้อง: ../upload/projectID-projectName/logoFilename
$folderName = sanitizeFolderName($row['projectName']);
$logoPath = !empty($logoName) ? '../upload/' . $row['projectID'] . '-' . $folderName . '/' . $logoName : '../assets/img/default.png';

$opening = $row['openingCustom'] == 0 ? explode('__', $row['openingHours']) : $row['openingHours'];
$delivery = $row['deliveryCustom'] == 0 ? explode('__', $row['pickupAndDelivery']) : $row['pickupAndDelivery'];

$row['colorTheme1'] = $row['colorTheme1'] ?: '#000000';
$row['colorTheme2'] = $row['colorTheme2'] ?: '#FFFFFF';
$row['colorTheme3'] = $row['colorTheme3'] ?: '#FFFFFF';

$row['orderURL'] = htmlspecialchars($row['orderURL'] ?? '', ENT_QUOTES, 'UTF-8'); 
$row['tableURL'] = htmlspecialchars($row['tableURL'] ?? '', ENT_QUOTES, 'UTF-8');  
?>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
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
                                <label for="bsEmail" class="input-group-text" id="basic-addon3">Email<span style="color:red; margin-left: 5px"> *</span></label>
                                <input type="email" class="form-control" id="bsEmail" maxlength="100" placeholder="admin@localforyou.com" value="<?php echo $row['email']; ?>">
                            </div>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <label for="bsPhone" class="input-group-text" id="basic-addon3">Phone<span style="color:red; margin-left: 5px"> *</span></label>
                                <input type="tel" class="form-control" id="bsPhone" placeholder="+6112345678" maxlength="15" value="<?php echo $row['phone']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label for="bsAddress" class="form-label">Address<span style="color:red; margin-left: 5px"> *</span></label>
                        <textarea class="form-control" id="bsAddress" rows="3" ><?php echo $row['address']; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <div class="row">
                        <div class="col-7">
                            <label for="bsOpen" class="form-label">Opening hours</label>
                        </div>
                        <div class="col text-right">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inputOpeningChk" id="7dayOpenChk" value="openChkDay" checked>
                                <label class="form-check-label" for="7dayOpenChk"><small>7 day</small></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inputOpeningChk" id="customOpenChk" value="openChkBox">
                                <label class="form-check-label" for="customOpenChk"><small>Custom</small></label>
                            </div>
                        </div>
                    </div>
                    <div id="openingBox">
                        <textarea class="form-control" id="openingCustom" rows="3"><?php echo $opening; ?></textarea>
                    </div>
                    <?php
                        $days = ['sunday' => 'Sun', 'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat',];
                    ?>
                    <form id="openingForm" class="border rounded px-4 py-3">
                        <div class="mb-3 px-3">
                            <div class="days-list">
                                <?php foreach ($days as $key => $label): ?>
                                    <?php $i = array_search($key, array_keys($days)); ?>
                                    <div class="mb-3 row align-items-center" data-day="<?= $key ?>">
                                        <div class="col-1">
                                            <input type="checkbox" 
                                                class="form-check-input day-toggle" 
                                                id="<?= $key ?>-open-chk"
                                                <?= !empty($opening[$i]) ? 'checked' : '' ?>>
                                            <label for="<?= $key ?>-open-chk" class="form-check-label"><?= $label ?></label>
                                        </div>
                                        <div class="col">
                                            <input type="text" 
                                                class="mb-0 form-control time-input opening" 
                                                id="<?= $key ?>-open" 
                                                placeholder="Opening hours (e.g., 08:00-17:00)" 
                                                value="<?= !empty($opening[$i]) ? $opening[$i] : '' ?>"
                                                <?= !empty($opening[$i]) ? '' : 'style="display:none;"' ?>>
                                        </div>
                                        <div class="col-1">
                                            <span class="copy-link" data-copy-from="<?= $key ?>-open"><?= $icon ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="row mb-2 ml-0">
                        <div class="col-8 form-check form-check-inline">
                            <input class="mr-2 form-check-input chkPickup" type="checkbox" id="chkPickup" name="chkPickup">
                            <label for="chkPickup" class="form-check-label">Delivery time does not match Opening hours.</label>
                        </div>
                        <div class="col text-right">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inputDeliveryChk" id="7dayDeliChk" value="deliChkDay" disabled>
                                <label class="form-check-label" for="7dayDeliChk"><small>7 day</small></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inputDeliveryChk" id="customDeliChk" value="deliChkBox" disabled>
                                <label class="form-check-label" for="customDeliChk"><small>Custom</small></label>
                            </div>
                        </div>
                    </div>
                    <div id="pickupAndDelivery">
                        <div id="deliveryBox">
                            <textarea class="form-control" id="deliveryCustom" rows="3"><?php echo $delivery; ?></textarea>
                        </div>
                        <form id="deliveryForm" class="border rounded px-4 py-3">
                            <div class="mb-3 px-3">
                                <div class="days-list">
                                    <?php foreach ($days as $key => $label): ?>
                                        <?php $i = array_search($key, array_keys($days)); ?>
                                        <div class="mb-3 row align-items-center" data-day="<?= $key ?>">
                                            <div class="col-1">
                                                <input type="checkbox" 
                                                    class="form-check-input day-toggle" 
                                                    id="<?= $key ?>-deli-chk"
                                                    <?= !empty($delivery[$i]) ? 'checked' : '' ?>>
                                                <label for="<?= $key ?>-deli-chk" class="form-check-label"><?= $label ?></label>
                                            </div>
                                            <div class="col">
                                                <input type="text" 
                                                    class="mb-0 form-control deli-input opening" 
                                                    id="<?= $key ?>-deli" 
                                                    placeholder="e.g., 08:00-17:00" 
                                                    value="<?= !empty($delivery[$i]) ? $delivery[$i] : '' ?>"
                                                    <?= !empty($delivery[$i]) ? '' : 'style="display:none;"' ?>>
                                            </div>
                                            <div class="col-1">
                                                <span class="copy-link" data-copy-from="<?= $key ?>-deli"><?= $icon ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-5">
            <div class="mt-3 border rounded py-3 pl-3">
                <div class="col py-3 px-3">
                    <label for="formLogo" class="form-label">Logo</label>
                    <form method="post" enctype="multipart/form-data" class="uploadForm" id="formLogo">
                        <div class="row">
                            <div class="d-flex flex-column col-6 gap-2">
                                <img style="aspect-ratio: 1/1;" class="preview" src="<?php echo $logoPath; ?>" alt="place">
                                <input class="picname" type="hidden" id="picname" value="<?php echo $logoName; ?>">
                            </div>
                            <div class="d-flex flex-column col-6 gap-2 justify-content-end">
                                <div class="d-flex flex-column gap-2">
                                    <input type="file" class="file-input">
                                    <button type="button" class="button" onclick="handleFormSubmit(this)">Upload</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php
            $themes = [
                1 => $row['colorTheme1'],
                2 => $row['colorTheme2'],
                3 => $row['colorTheme3'],
            ];
            ?>

            <div class="mt-3 border rounded py-3 pl-3">
                <?php foreach ($themes as $i => $color): ?>
                    <div class="row">
                        <div class="col-8">
                            <div class="mt-3 d-flex flex-row gap-3 align-items-center">
                                <label for="theme<?= $i ?>">Color Theme #<?= $i ?></label>
                                <input type="color"
                                    onchange="setHex(this.value, <?= $i ?>);"
                                    id="theme<?= $i ?>"
                                    value="<?= $color ?>">
                                <span id="theme<?= $i ?>Hex" class="codeHex"><?= $color ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mt-3 pr-3 d-flex flex-row gap-3 align-items-center">
                                <select class="form-select" id="theme<?= $i ?>Select">
                                    <option selected value="<?= $color ?>">None</option>
                                    <option value="#theme<?= $i ?>Gold">Gold</option>
                                    <option value="#theme<?= $i ?>Silver">Silver</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                    <input class="form-check-input amelia" type="checkbox" name="amelia" value="1" id="amelia" <?php echo $row['amelia'] == 1 ? 'checked' : ''; ?>>
                    <label for="amelia">Amelia</label>
                </div>

                <div>
                    <input class="form-check-input voucher" type="checkbox" name="voucher" value="1" id="voucher" <?php echo $row['voucher'] == 1 ? 'checked' : ''; ?>>
                    <label for="voucher">Voucher</label>
                </div>

                <div>
                    <input class="form-check-input bookOther" type="checkbox" name="bookOther" value="1" id="bookOther" <?php echo $row['bookOther'] == 1 ? 'checked' : ''; ?>>
                    <label for="bookOther">Other Booking System</label>
                    <div class="masOtherSystem">
                        <label for="masOtherSystem">System Name</label>
                        <input type="text" class="form-control" id="masOtherSystem" placeholder="System Name" value="<?php echo $row['masOtherSystem']; ?>">
                    </div>
                </div>
            </div>
</div>

<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <div class="form-check">
                <input class="form-check-input needEmail" type="checkbox" value="1" id="needEmail" <?php echo $row['needEmail'] == 1 ? 'checked' : ''; ?>>
                <label for="needEmail">Need email inbox under shop domain name.</label>
                <small class="text-muted">e.g., info@hoonhaymassage.co.nz</small><br>
            </div>
        </div>
    </div>
</div>

<!-- Social Media -->
<?php
$socials = [
    'facebookURL'  => 'Facebook',
    'instagramURL' => 'Instagram',
    'youtubeURL'   => 'Youtube',
    'tiktokURL'    => 'Tiktok',
];
?>

<div class="border rounded mb-4 px-5 py-3">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Social Media.</h5>
        </div>
    </div>

    <div class="row">
        <?php foreach ($socials as $socialID => $label): ?>
            <div class="col">
                <div class="input-group mb-3">
                    <label for="<?= $socialID ?>" class="input-group-text"><?= $label ?></label>
                    <input type="text"
                           class="form-control"
                           id="<?= $socialID ?>"
                           placeholder="URL"
                           value="<?= $row[$socialID] ?>">
                </div>
            </div>
            <?php if ($label === 'Instagram' || $label === 'Tiktok'): ?>
                </div><div class="row">
            <?php endif; ?>
        <?php endforeach; ?>
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
        <small id="infoText" class="text-warning"><?php echo $row['saveFlag']; ?></small>
        <button id="nextBtn" class="btn btn-secondary" onclick="nextForm('<?php echo $url; ?>')" disabled>Next</button>
    </div>
</div>

<input type="hidden" id="projectID" value="<?php echo $id; ?>">
<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">

<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="../controllers/project_detail.js?v=1.0.0"></script>
<script>
    const hiddenShopType = $("#ShopType");
    const inputName = $("#bsName");
    const inputEmail = $("#bsEmail");
    const inputPhone = $("#bsPhone");
    const inputAddress = $("#bsAddress");
    const dayOpenChk = $("#7dayOpenChk");
    const customOpenChk = $("#customOpenChk");
    const inputSunOpen = $("#sunday-open");
    const inputMonOpen = $("#monday-open");
    const inputTueOpen = $("#tuesday-open");
    const inputWedOpen = $("#wednesday-open");
    const inputThuOpen = $("#thursday-open");
    const inputFriOpen = $("#friday-open");
    const inputSatOpen = $("#saturday-open");
    const inputOpenCus = $("#openingCustom")
    const dayDeliChk = $("#7dayDeliChk");
    const customDeliChk = $("#customDeliChk");
    const inputSunDeli = $("#sunday-deli");
    const inputMonDeli = $("#monday-deli");
    const inputTueDeli = $("#tuesday-deli");
    const inputWedDeli = $("#wednesday-deli");
    const inputThuDeli = $("#thursday-deli");
    const inputFriDeli = $("#friday-deli");
    const inputSatDeli = $("#saturday-deli");
    const inputDeliCus = $("#deliveryCustom")
    const hiddenPicLogo = $("#picname");
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
        showSystem();
    }); //ready

    $("#cmdSubmit").click(function () {
        // ✅ Debug: ตรวจสอบค่า checkbox
        console.log("DEBUG - chkAmelia checked:", chkAmelia.prop("checked"));
        console.log("DEBUG - chkVoucher checked:", chkVoucher.prop("checked"));
        console.log("DEBUG - chkBookOther checked:", chkBookOther.prop("checked"));
        console.log("DEBUG - chkAmelia is:", chkAmelia);
        console.log("DEBUG - chkVoucher is:", chkVoucher);
        
        let payload = {
            mode : "save",
            projectName: inputName.val(),
            email: inputEmail.val(),
            phone: inputPhone.val(),
            address: inputAddress.val(),
            dayOpenChk: dayOpenChk.prop("checked"),
            customOpenChk: customOpenChk.prop("checked"),
            sunOpen: inputSunOpen.val(),
            monOpen: inputMonOpen.val(),
            tueOpen: inputTueOpen.val(),
            wedOpen: inputWedOpen.val(),
            thuOpen: inputThuOpen.val(),
            friOpen: inputFriOpen.val(),
            satOpen: inputSatOpen.val(),
            cusOpen: inputOpenCus.val(),
            dayDeliChk: dayDeliChk.prop("checked"),
            customDeliChk: customDeliChk.prop("checked"),
            sunDeli: inputSunDeli.val(),
            monDeli: inputMonDeli.val(),
            tueDeli: inputTueDeli.val(),
            wedDeli: inputWedDeli.val(),
            thuDeli: inputThuDeli.val(),
            friDeli: inputFriDeli.val(),
            satDeli: inputSatDeli.val(),
            cusDeli: inputDeliCus.val(),
            logo: hiddenPicLogo.val(),
            colorTheme1: selTheme1Hex.val(),
            colorTheme2: selTheme2Hex.val(),
            colorTheme3: selTheme3Hex.val(),
            domainName: domainName.val(),
            hostingName: hostingName.val(),
            domainHave: chkDomainHave.prop("checked") ? 1 : 0,
            domainProvidersID: selDomainProvider.val(),
            domainUser: inputDomainUser.val(),
            domainPass: inputDomainPass.val(),
            hostingHave: chkHostingHave.prop("checked") ? 1 : 0,
            hostingProvidersID: selHostingProvider.val(),
            hostingUser: inputHostingUser.val(),
            hostingPass: inputHostingPass.val(),
            gloriaHave: chkGloriaHave.prop("checked") ? 1 : 0,
            orderURL: inputOrderURL.val(),
            tableURL: inputTableURL.val(),
            orderOther: chkOrderOther.prop("checked") ? 1 : 0,
            resOtherSystem: inputResOtherSystem.val(),
            amelia: chkAmelia.prop("checked") ? 1 : 0,
            voucher: chkVoucher.prop("checked") ? 1 : 0,
            bookOther: chkBookOther.prop("checked") ? 1 : 0,
            needEmail: chkNeedEmail.prop("checked") ? 1 : 0,
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
        console.log("Payload amelia:", payload.amelia);
        console.log("Payload voucher:", payload.voucher);
        console.log("Payload bookOther:", payload.bookOther);

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
