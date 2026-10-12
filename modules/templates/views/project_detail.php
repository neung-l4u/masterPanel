<?php
require_once "../assets/db/db.php";
require_once "../assets/db/initDB.php";
require_once "../assets/php/share_function.php";
require_once "../assets/php/brief.php";
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
    t.name AS typeName,
    c.name AS countryName,
    s.sNickName
    FROM tb_project p
    JOIN tb_shopType t ON p.shopTypeID = t.id
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

$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$isCustom = isCustomTemplate($row['selectedTemplate']);
$isOther = (int)$row['shopTypeID'] === SHOP_TYPE_OTHER;
$briefReady = !$isCustom || briefTableExists($db);
$brief = $isCustom ? loadBrief($db, $id) : [];
$colorMode = $brief['colorMode'] ?? 'pick';
$refLinks = briefRefLinks($brief) ?: [['url' => '', 'note' => '', 'forColor' => 0]];
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
                    <?php if ($isCustom): ?>
                        <span class="badge bg-warning text-dark">Fully custom</span>
                    <?php else: ?>
                        <span class="shopType"><?php echo $e($row['typeName']); ?></span> Template No. <?php echo $row['selectedTemplate']; ?>
                    <?php endif; ?>
                </li>
                <?php if ($isOther): ?>
                <li>
                    <span class="fw-bold">Business:</span>
                    <?php echo nl2br($e($brief['businessNote'] ?? '')); ?> <small class="text-muted">(edit in Project list)</small>
                </li>
                <?php endif; ?>
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

<?php if (!$briefReady): ?>
<div class="alert alert-danger">Fully custom brief is not available yet: run <code>docs/migrations/2026-10-06-templates-fully-custom.sql</code> on this database.</div>
<?php endif; ?>

<!-- Template Type -->
<?php if (!$isCustom): ?>
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
<?php endif; ?>
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
                <?php if ($isCustom): ?>
                    <div class="pr-3">
                        <?php foreach (BRIEF_COLOR_MODES as $mode => $label): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="colorMode" id="colorMode_<?= $mode ?>" value="<?= $mode ?>" <?= $colorMode === $mode ? 'checked' : '' ?>>
                                <label class="form-check-label" for="colorMode_<?= $mode ?>"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                        <small class="text-muted d-block colorModeHint" data-mode="reference">Tick "use for colors" on at least one link in Design brief below.</small>
                    </div>
                <?php endif; ?>
                <div id="colorPickers">
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
                </div><!--colorPickers-->
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

        <?php if ($isCustom): ?>
            <div class="row">
                <div class="col-6">
                    <label for="systemName" class="form-label">Booking / ordering system<span style="color:red; margin-left: 5px"> *</span></label>
                    <input type="text" class="form-control" id="systemName" maxlength="255" placeholder="e.g. Setmore, GloriaFood, or None" value="<?= $e($brief['systemName'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label for="systemURL" class="form-label">System URL</label>
                    <input type="text" class="form-control" id="systemURL" maxlength="255" placeholder="https://..." value="<?= $e($brief['systemURL'] ?? '') ?>">
                    <small class="text-muted">One system only, the link customers will actually use.</small>
                </div>
            </div>
        <?php else: ?>
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
        <?php endif; ?>
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


<?php if ($isCustom): ?>
<!-- Design brief (Fully custom only) -->
<div class="border rounded mb-4 px-5 py-3" id="designBrief">
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">Design brief.</h5>
            <small class="text-muted">Fields marked <span style="color:red;">*</span> must be filled before the project can be sent.</small>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-6 mb-3">
            <label for="briefGoal" class="form-label">Main goal of the website<span style="color:red; margin-left: 5px"> *</span></label>
            <select id="briefGoal" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach (BRIEF_GOALS as $k => $label): ?>
                    <option value="<?= $k ?>" <?= ($brief['goal'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">The main button on every page.</small>
        </div>
        <div class="col-6 mb-3">
            <label for="briefDeadline" class="form-label">Deadline</label>
            <input type="date" class="form-control" id="briefDeadline" value="<?= $e($brief['deadline'] ?? '') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-6 mb-3">
            <label for="briefPages" class="form-label">Pages<span style="color:red; margin-left: 5px"> *</span> <small class="text-muted" id="briefPageCount"></small></label>
            <textarea class="form-control" id="briefPages" rows="6" placeholder="One page per line&#10;Home&#10;About&#10;Services & prices&#10;Contact"><?= $e($brief['pages'] ?? '') ?></textarea>
        </div>
        <div class="col-6 mb-3">
            <label for="briefOldSite" class="form-label">Current website</label>
            <input type="text" class="form-control" id="briefOldSite" maxlength="255" placeholder="https://... (leave empty if none)" value="<?= $e($brief['oldSiteURL'] ?? '') ?>">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="briefKeepURLs" <?= !empty($brief['keepURLs']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="briefKeepURLs">Keep the old page URLs (used by ads / Google)</label>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Reference websites<span style="color:red; margin-left: 5px"> *</span> <small class="text-muted">at least 1</small></label>
        <div id="refLinks">
            <?php foreach ($refLinks as $link): ?>
                <div class="row g-2 mb-2 refLink">
                    <div class="col-5"><input type="text" class="form-control refUrl" maxlength="255" placeholder="https://..." value="<?= $e($link['url'] ?? '') ?>"></div>
                    <div class="col-5"><input type="text" class="form-control refNote" maxlength="255" placeholder="What do you like about it?" value="<?= $e($link['note'] ?? '') ?>"></div>
                    <div class="col-2 d-flex align-items-center gap-2">
                        <div class="form-check refForColorBox">
                            <input class="form-check-input refForColor" type="checkbox" <?= !empty($link['forColor']) ? 'checked' : '' ?>>
                            <label class="form-check-label"><small>use for colors</small></label>
                        </div>
                        <a href="#" class="text-muted refRemove" title="Remove link"><i class="bi bi-x-lg"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="#" id="refAdd" class="text-muted small text-decoration-none">+ Add link</a>
    </div>

    <div class="row">
        <div class="col-6 mb-3">
            <label for="briefMood" class="form-label">Mood / style<span style="color:red; margin-left: 5px"> *</span></label>
            <input type="text" class="form-control" id="briefMood" maxlength="255" placeholder="e.g. dark and calm, traditional Thai, modern minimal" value="<?= $e($brief['mood'] ?? '') ?>">
        </div>
        <div class="col-6 mb-3">
            <label for="briefContent" class="form-label">Where does the content come from?<span style="color:red; margin-left: 5px"> *</span></label>
            <select id="briefContent" class="form-select">
                <option value="">-- Select --</option>
                <?php foreach (BRIEF_CONTENT as $k => $label): ?>
                    <option value="<?= $k ?>" <?= ($brief['contentSource'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Files we already have</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="briefVectorLogo" <?= !empty($brief['hasVectorLogo']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="briefVectorLogo">Vector logo (SVG / AI / EPS)</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="briefHiResPhotos" <?= !empty($brief['hasHiResPhotos']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="briefHiResPhotos">Photos at least 2000 px wide</label>
        </div>
    </div>

    <!-- Photos: uploaded one by one through models/upload.php (prefix "Brief"), listed straight from the folder -->
    <?php $briefFolder = '../upload/' . $row['projectID'] . '-' . $folderName . '/'; ?>
    <div class="mb-3">
        <label class="form-label">Photos <small class="text-muted">(JPG, PNG, WEBP, HEIC — select many at once)</small></label>
        <div class="briefPhoto briefPhotoTpl position-relative d-none">
            <a href="#" target="_blank"><img src="" alt="" style="width:110px;height:110px;object-fit:cover;" class="rounded border"></a>
            <a href="#" class="briefPhotoDel position-absolute top-0 end-0 badge bg-dark text-decoration-none" title="Remove photo">&times;</a>
        </div>
        <div id="briefPhotos" class="d-flex flex-wrap gap-2 mb-2">
            <?php foreach (briefPhotos($briefFolder) as $photo): ?>
                <div class="briefPhoto position-relative" data-file="<?= $e($photo) ?>">
                    <a href="<?= $e($briefFolder . $photo) ?>" target="_blank"><img src="<?= $e($briefFolder . $photo) ?>" alt="" style="width:110px;height:110px;object-fit:cover;" class="rounded border"></a>
                    <a href="#" class="briefPhotoDel position-absolute top-0 end-0 badge bg-dark text-decoration-none" title="Remove photo">&times;</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <input type="file" id="briefPhotoInput" accept=".jpg,.jpeg,.png,.webp,.heic,.svg" multiple>
            <button type="button" class="btn btn-sm btn-outline-primary" id="briefPhotoUpload">Upload</button>
            <small class="text-muted" id="briefPhotoStatus"></small>
        </div>
    </div>

    <div class="mb-3">
        <label for="briefAssetLinks" class="form-label">Links to photos / files <small class="text-muted">Google Drive, Dropbox, etc. — one per line</small></label>
        <textarea class="form-control" id="briefAssetLinks" rows="3" placeholder="https://drive.google.com/drive/folders/...&#10;Make sure the link is shared as &quot;Anyone with the link&quot;"><?= $e($brief['assetLinks'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <div class="col-6 mb-3">
            <label for="briefFeatures" class="form-label">Special features</label>
            <textarea class="form-control" id="briefFeatures" rows="3" placeholder="e.g. blog, gallery, price list, 2 languages, contact form"><?= $e($brief['features'] ?? '') ?></textarea>
        </div>
        <div class="col-6 mb-3">
            <label for="briefTracking" class="form-label">Existing tracking</label>
            <textarea class="form-control" id="briefTracking" rows="3" placeholder="e.g. GTM-XXXX, GA4, Search Console verification code"><?= $e($brief['tracking'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="mb-3">
        <label for="briefNotes" class="form-label">Notes / things NOT to do</label>
        <textarea class="form-control" id="briefNotes" rows="3"><?= $e($brief['notes'] ?? '') ?></textarea>
    </div>
</div>
<?php endif; ?>

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
        <?php if ($isCustom): // no template designer: the brief above is the last step ?>
            <?php $missing = briefMissing($brief, $isOther); ?>
            <div class="small <?= $missing ? 'text-danger' : 'text-success' ?>" id="briefStatus">
                <?= $missing ? 'Still needed before sending: ' . $e(implode(', ', $missing)) : 'Brief complete. Send it from the Project list.' ?>
            </div>
            <button id="nextBtn" class="btn btn-secondary" onclick="nextForm('main.php?m=project')" disabled>Back to Projects</button>
        <?php else: ?>
        <button id="nextBtn" class="btn btn-secondary" onclick="nextForm('<?php echo $url; ?>')" disabled>Next</button>
        <?php endif; ?>
    </div>
</div>

<input type="hidden" id="projectID" value="<?php echo $id; ?>">
<input type="hidden" id="isCustom" value="<?php echo $isCustom ? 1 : 0; ?>">
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

    const isCustom = $("#isCustom").val() === "1";

    $(()=>{ //ready
        showSystem();
        if (isCustom) initBrief();
    }); //ready

    // Fully custom: color mode radio, reference link rows, page counter.
    const initBrief = () => {
        const applyColorMode = () => {
            const mode = $("input[name=colorMode]:checked").val() || "pick";
            $("#colorPickers").toggle(mode === "pick");
            $(".colorModeHint").toggle(mode === "reference");
            $(".refForColorBox").toggle(mode === "reference");
        };
        $("input[name=colorMode]").on("change", applyColorMode);
        applyColorMode();

        $("#refAdd").on("click", (ev) => {
            ev.preventDefault();
            const row = $(".refLink").first().clone();
            row.find("input[type=text]").val("");
            row.find(".refForColor").prop("checked", false);
            $("#refLinks").append(row);
            applyColorMode();
        });
        $("#refLinks").on("click", ".refRemove", function (ev) {
            ev.preventDefault();
            const rows = $(".refLink");
            if (rows.length > 1) $(this).closest(".refLink").remove();
            else rows.find("input[type=text]").val(""); // keep one row to type into
        });

        // Upload one file at a time: models/upload.php takes a single "file" per request.
        $("#briefPhotoUpload").on("click", async () => {
            const files = [...$("#briefPhotoInput")[0].files];
            if (!files.length) { alert("Please select photos."); return; }
            const status = $("#briefPhotoStatus");
            const failed = [];
            for (const [i, file] of files.entries()) {
                status.text(`Uploading ${i + 1}/${files.length}...`);
                const fd = new FormData();
                fd.append("file", file);
                fd.append("projectId", projectID);
                fd.append("prefixId", "Brief");
                const res = (await $.ajax({ url: "../models/upload.php", type: "post", data: fd, contentType: false, processData: false })
                    .catch(() => "") || "").toString().trim();
                if (!/[\/\\]/.test(res)) { failed.push(file.name); continue; }
                const name = res.split("/").pop();
                const tile = $(".briefPhotoTpl").first().clone().removeClass("briefPhotoTpl d-none").attr("data-file", name);
                tile.find("a").first().attr("href", res).find("img").attr("src", res);
                $("#briefPhotos").append(tile);
            }
            $("#briefPhotoInput").val("");
            status.text(failed.length ? `Not uploaded: ${failed.join(", ")}` : "Done.");
        });
        $("#briefPhotos").on("click", ".briefPhotoDel", function (ev) {
            ev.preventDefault();
            const tile = $(this).closest(".briefPhoto");
            if (!confirm("Remove this photo?")) return;
            $.post("../models/upload.php", { act: "deleteBrief", projectId: projectID, file: tile.data("file") }, (res) => {
                if ($.trim(res) === "1") tile.remove(); else alert("Could not remove the photo.");
            });
        });

        const countPages = () => {
            const n = $("#briefPages").val().split("\n").filter(l => l.trim() !== "").length;
            $("#briefPageCount").text(n ? `(${n} page${n > 1 ? "s" : ""})` : "");
        };
        $("#briefPages").on("input", countPages);
        countPages();
    };

    const briefPayload = () => ({
        colorMode: $("input[name=colorMode]:checked").val() || "pick",
        systemName: $("#systemName").val(),
        systemURL: $("#systemURL").val(),
        goal: $("#briefGoal").val(),
        pages: $("#briefPages").val(),
        oldSiteURL: $("#briefOldSite").val(),
        keepURLs: $("#briefKeepURLs").prop("checked") ? 1 : 0,
        refLinks: JSON.stringify($(".refLink").map(function () {
            return {
                url: $(this).find(".refUrl").val().trim(),
                note: $(this).find(".refNote").val().trim(),
                forColor: $(this).find(".refForColor").prop("checked") ? 1 : 0
            };
        }).get().filter(l => l.url !== "")),
        mood: $("#briefMood").val(),
        contentSource: $("#briefContent").val(),
        hasVectorLogo: $("#briefVectorLogo").prop("checked") ? 1 : 0,
        hasHiResPhotos: $("#briefHiResPhotos").prop("checked") ? 1 : 0,
        features: $("#briefFeatures").val(),
        tracking: $("#briefTracking").val(),
        assetLinks: $("#briefAssetLinks").val(),
        deadline: $("#briefDeadline").val(),
        notes: $("#briefNotes").val()
    });

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
            isCustom: isCustom ? 1 : 0,
            token: Math.random()
        };
        if (isCustom) Object.assign(payload, briefPayload());

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
            if (res.result === "fail") { alert(res.msg); return false; }
            if (isCustom) location.reload(); // refresh the "still needed" list from the server
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
