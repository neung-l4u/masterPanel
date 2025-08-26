<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $date;

$password = "Localeats#".date("Y");
?>
<style>
    td > abbr{
        font-size:1em;
        line-height:1em;
        /*height:1em;*/
        width: 250px;
        /*border:3px solid #00ACEE;*/
        white-space: nowrap;
        overflow: hidden;
        text-overflow: '...?';
    }
    .colNo{
            width: 50px;
        }
    /* .colProName{

    } */
    .colLocation{
        width: 150px;
    }
    .colOwner{
        width: 150px;
    }
    .colEmail{
        width: 200px;
    }
    .colDetail{
        width: 80px;
    }
    .colInfo{
        width: 180px;
    }
    .modal-body {
        font-size: 0.9rem;
    }
    h5.text-info{
        font-size: 0.8rem;
    }
    small{
        font-size: 0.7rem;
    }
    .filterLabel{
        /*border: 1px solid black;*/
        width: 100px;
    }
    .filterSelect{
        width: 300px !important;
    }
    a.linkDetail:link {
        color: black;
        text-decoration: none;
    }

    a.linkDetail:visited {
        color: black;
    }

    a.linkDetail:hover {
        color: red;
        text-decoration: underline;
    }

    a.linkDetail:active {
        color: blue;
    }
    ::placeholder {
        color: #DDDDDD !important;
        opacity: 1; /* Firefox */
    }

    ::-ms-input-placeholder { /* Edge 12 -18 */
        color: #DDDDDD !important;
    }
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"  fill="#000000" /></svg>
                    Website Lists 
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Website Lists</a></li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="mt-3 mb-5">
                    <div class="row">
                        <div class="col d-flex align-items-center justify-content-between">
                            <h5>
                                <i class="nav-icon mr-3 bi bi-funnel"></i>
                                Filters
                            </h5>
                            <button id="btnModal" type="button" class="btn btn-primary" onclick="openFormModal()" data-toggle="modal" data-target="#formModal">
                                <i class="bi bi-plus"></i> New Item
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col d-flex align-items-center gap-5">
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterShopType" class="form-label filterLabel">Shop Type</label>
                                <select class="form-select filterSelect" id="filterShopType" onchange="filterChange()" aria-label="Default select example" disabled>
                                    <option value="" selected >All</option>
                                    <option value="1">Restaurant</option>
                                    <option value="2" >Massage</option>
                                    <option value="3">Grocery</option>
                                    <option value="4">Internal</option>
                                    <option value="5">Template</option>
                                    <option value="6">Other</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterSystem" class="form-label filterLabel">System</label>
                                <select class="form-select filterSelect" id="filterSystem" onchange="filterChange()"  aria-label="Default select example" disabled>
                                    <option value="" selected>All</option>
                                    <option value="GF">Gloria Food</option>
                                    <option value="AM">Amelia</option>
                                    <option value="VC">Voucher</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterStatus" class="form-label filterLabel">Status</label>
                                <select class="form-select filterSelect" id="filterStatus" onchange="filterChange()" aria-label="Default select example" disabled>
                                    <option value="" selected>All</option>
                                    <option value="Live">Live</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Transferred">Transferred</option>
                                    <option value="Pre Live">Pre Live</option>
                                    <option value="Subdomain">Subdomain</option>
                                    <option value="Redirect">Redirect</option>
                                    <option value="Unpublished">Unpublished</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col d-flex align-items-center gap-5">
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterTemplate" class="form-label filterLabel">Template</label>
                                <select class="form-select filterSelect" id="filterTemplate" onchange="filterChange()" aria-label="Default select example" disabled>
                                    <option value="" selected>All</option>
                                    <option value="1">Template 1</option>
                                    <option value="2">Template 2</option>
                                    <option value="3">Template 3</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterCountry" class="form-label filterLabel">Country</label>
                                <select class="form-select filterSelect" id="filterCountry" onchange="filterChange()" aria-label="Default select example" disabled>
                                    <option value="" selected>All</option>
                                    <option value="AU">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="USA">United States</option>
                                    <option value="TH">Thailand</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <label for="filterServer" class="form-label filterLabel">Server</label>
                                <select class="form-select filterSelect" id="filterServer" onchange="filterChange()" aria-label="Default select example" disabled>
                                    <option value="" selected>All</option>
                                    <option value="1">az1-tr102.supercp.com</option>
                                    <option value="2">mi3-tr104.supercp.com</option>
                                    <option value="3">nl1-cl9-atr1.supercp.com</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card p-3">
                        <div class="card-body">
                            <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                <tr>
                                    <th class="colNo">#</th>
                                    <th class="colProName">Project name</th>
                                    <th class="colDetail"></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel"><i class="bi bi-file-earmark-plus"></i> Form Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="newModalFormAction('close')">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container py-4">
                            <form>
                                <div class="form-group">
                                    <label for="inputProject">Project</label>
                                    <input type="text" class="form-control" id="inputProject" maxlength="255" placeholder="e.g. Hoon Hay">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputLocation">Location</label>
                                        <textarea class="form-control" id="inputLocation" rows="5" placeholder="e.g. Hoon Hay"> </textarea>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="inputOwner">Owner</label>
                                            <input type="text" class="form-control" id="inputOwner" maxlength="255" placeholder="e.g. John Doe">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputOwnerEmail">Owner Email</label>
                                            <input type="email" class="form-control" id="inputOwnerEmail" maxlength="255" placeholder="e.g. johndoe@gmail.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputDomain">Domain</label>
                                        <input type="text" class="form-control" id="inputDomain" maxlength="255" placeholder="e.g. www.hoonhaythaimassage.com">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputDomainProvider">Domain Provider</label>
                                        <select id="inputDomainProvider" class="form-control">
                                            <option value="0" selected>-- None --</option>
                                            <?php
                                            $dbDomainProviders = $db->query('SELECT id, name FROM DomainProviders WHERE status=1 ORDER BY id;')->fetchAll();
                                            foreach ($dbDomainProviders as $row){
                                                ?>
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                            <?php }//foreach ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputPublishedDate">Published Date</label>
                                        <input type="date" class="form-control" id="inputPublishedDate">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputLiveStatus">Live Status</label>
                                        <select id="inputLiveStatus" class="form-control">
                                            <option value="0" selected>-- None --</option>
                                            <option value="Live">Live</option>
                                            <option value="Draft">Draft</option>
                                            <option value="Transferred">Transferred</option>
                                            <option value="Pre Live">Pre Live</option>
                                            <option value="Subdomain">Subdomain</option>
                                            <option value="Redirect">Redirect</option>
                                            <option value="Unpublished">Unpublished</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputShopType">Shop Type</label>
                                        <select id="inputShopType" class="form-control">
                                            <option value="0" selected>-- None --</option>
                                            <?php
                                            $dbShoptype = $db->query('SELECT id, name FROM tb_shopType WHERE status=1 ORDER BY id;')->fetchAll();
                                            foreach ($dbShoptype as $row){
                                                ?>
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                            <?php }//foreach ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputTemplate">Template</label>
                                        <select id="inputTemplate" class="form-control">
                                            <option value="0" selected>-- None --</option>
                                            <?php
                                            $dbWebsiteTemplate = $db->query('SELECT id, template FROM WebsiteTemplate ORDER BY id;')->fetchAll();
                                            foreach ($dbWebsiteTemplate as $row){
                                                ?>
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['template']; ?></option>
                                            <?php }//foreach ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputServer">L4U Server</label>
                                    <select id="inputServer" class="form-control">
                                        <option value="0" selected>-- None --</option>
                                        <?php
                                        $dbl4uServers = $db->query('SELECT svID, svName FROM L4UServers ORDER BY svID;')->fetchAll();
                                        foreach ($dbl4uServers as $row){
                                            ?>
                                            <option value="<?php echo $row['svID']; ?>"><?php echo $row['svName']; ?></option>
                                        <?php }//foreach ?>
                                    </select>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputCPanelUser">CPanel User</label>
                                        <input type="text" class="form-control" id="inputCPanelUser" placeholder="Hoonhay">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputCPanelPass">CPanel Pass</label>
                                        <input type="text" class="form-control" id="inputCPanelPass" placeholder="bXWR8r&8Vb">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputWordpressUser">Wordpress User</label>
                                        <input type="text" class="form-control" id="inputWordpressUser" placeholder="Hoonhay">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputWordpressPass">Wordpress Pass</label>
                                        <input type="text" class="form-control" id="inputWordpressPass" placeholder="99U@VFe~Ypm+">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputWordpressURL">Wordpress URL</label>
                                    <input type="text" class="form-control" id="inputWordpressURL" placeholder="https://www.hoonhay.com/wp-admin/">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputSMTPUser">SMTP Email User</label>
                                        <input type="text" class="form-control" id="inputSMTPUser" placeholder="noreply@hoonhay.com">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputSMTPPass">SMTP Email Pass</label>
                                        <input type="text" class="form-control" id="inputSMTPPass" placeholder="L4U@Notifications">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputSMTPRemark">SMTP Remark</label>
                                    <input type="text" class="form-control" id="inputSMTPRemark" placeholder="e.g. SMTP for Hoonhay">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="inputContactEmailUser">Contact Email User</label>
                                        <input type="text" class="form-control" id="inputContactEmailUser" placeholder="info@hoonhay.com">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputContactEmailPass">Contact Email Pass</label>
                                        <input type="text" class="form-control" id="inputContactEmailPass" placeholder="hoonhay@123">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputContactEmailRemark">Contact Email Remark</label>
                                    <input type="text" class="form-control" id="inputContactEmailRemark" placeholder="e.g. Email for Hoonhay">
                                </div>

                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="inputGloriaFood">
                                        <label class="form-check-label" for="inputGloriaFood">Gloria Food</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="inputAmelia">
                                        <label class="form-check-label" for="inputAmelia">Amelia</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="inputVoucher">
                                        <label class="form-check-label" for="inputVoucher">Voucher</label>
                                    </div>
                                </div>

                                <input type="hidden" name="editID" id="editID" value="">
                                <input type="hidden" name="formAction" id="formAction" value="add">
                            </form>
                        </div>

                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="newModalFormAction('close')">Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Detail</h1>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-column">
                            <h5 class="text-info">Basic Information</h5>
                            <div class="row mb-1">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">Project</th>
                                                <td id="wProject"></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Location</th>
                                                <td><span id="wLocation"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">Owner</th>
                                                <td><span id="wOwner"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Email</th>
                                                <td><span id="wOwnerEmail"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Industry</th>
                                                <td><span class="mr-3" id="wIndustry"></span> <b>Template :</b> <span id="wTemplateUsed"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">System</th>
                                                <td><span id="wSystem"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h5 class="text-info">Domain Information</h5>
                            <div class="row mb-1">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">Domain Name</th>
                                                <td><span id="wDomain"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Domain Provider</th>
                                                <td><span id="wDomainProvidersID"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">Publish Date</th>
                                                <td><span id="wPublishedDate"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Live Status</th>
                                                <td><span id="wLiveStatus"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h5 class="text-info">Log-in Information</h5>
                            <div class="row mb-3">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">cPanel Log-in</th>
                                                <td><span id="cPanelURL"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">cPanel User</th>
                                                <td><span id="wCPanelUser"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">cPanel Pass</th>
                                                <td><span id="wCPanelPass"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">WP Log-in</th>
                                                <td><span id="wWordpressURL"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">WP User</th>
                                                <td><span id="wWordpressUser"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">WP Pass</th>
                                                <td><span id="wWordpressPass"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h5 class="text-info">SMTP Information</h5>
                            <div class="row mb-1">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">User</th>
                                                <td><span id="wSMTPEmailUser"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Pass</th>
                                                <td><span id="wSMTPEmailPass"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Remark</th>
                                                <td><span id="wSMTPRemark"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <table class="table border">
                                        <tbody>
                                            <tr>
                                                <th scope="row" class="colInfo">Contact User</th>
                                                <td><span id="wContactEmailUser"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Contact Pass</th>
                                                <td><span id="wContactEmailPass"></span></td>
                                            </tr>
                                            <tr>
                                                <th scope="row" class="colInfo">Contact Remark</th>
                                                <td><span id="wContactEmailRemark"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal -->

        <!-- /.modal -->
    </div><!-- /.container-fluid -->
    
    <div id="alert" style="
        display: block;
        right: 20px; 
        bottom: 30px; 
        position: fixed; 
        background-color: #007bff; 
        color: white; 
        padding: 15px; 
        border-radius: 5px;
        z-index: 1;
        box-shadow: 0 4px 4px 0 rgb(191 191 191 / 20%);
        ">
        Text Copied
    </div>
</div>
<!-- /.content -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script>
    function showCopy() {
        $("#alert").fadeIn(500);
        setTimeout(function () {
            $("#alert").fadeOut();
        }, 1000);
    }
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function() {
            showCopy();
        }).catch(function(err) {
            console.error("Error copying text: ", error);
        });
    }

    // Modal Form Action
    const inputProject = $("#inputProject");
    const inputLocation = $("#inputLocation");
    const inputOwner = $("#inputOwner");
    const inputOwnerEmail = $("#inputOwnerEmail");
    const inputDomain = $("#inputDomain");
    const inputDomainProvider = $("#inputDomainProvider");
    const inputPublishedDate = $("#inputPublishedDate");
    const inputLiveStatus = $("#inputLiveStatus");
    const inputShopType = $("#inputShopType");
    const inputTemplate = $("#inputTemplate");
    const inputServer = $("#inputServer");
    const inputCPanelUser = $("#inputCPanelUser");
    const inputCPanelPass = $("#inputCPanelPass");
    const inputWordpressUser = $("#inputWordpressUser");
    const inputWordpressPass = $("#inputWordpressPass");
    const inputWordpressURL = $("#inputWordpressURL");
    const inputSMTPUser = $("#inputSMTPUser");
    const inputSMTPPass = $("#inputSMTPPass");
    const inputSMTPRemark = $("#inputSMTPRemark");
    const inputContactEmailUser = $("#inputContactEmailUser");
    const inputContactEmailPass = $("#inputContactEmailPass");
    const inputContactEmailRemark = $("#inputContactEmailRemark");
    const inputGloriaFood = $("#inputGloriaFood");
    const inputAmelia = $("#inputAmelia");
    const inputVoucher = $("#inputVoucher");
    const editID = $("#editID");
    const formAction = $("#formAction");

    // Modal Form Detail
    const ProjectName = $("#wProject");
    const Location = $("#wLocation");
    const Owner = $("#wOwner");
    const OwnerEmail = $("#wOwnerEmail");
    const Industry = $("#wIndustry");
    const TemplateUsed = $("#wTemplateUsed");
    const System = $("#wSystem");
    const DomainName = $("#wDomain");
    const DomainProvidersID = $("#wDomainProvidersID");
    const PublishedDate = $("#wPublishedDate");
    const LiveStatus = $("#wLiveStatus");
    const cPanelURL = $("#cPanelURL");
    const CPanelUser = $("#wCPanelUser");
    const CPanelPass = $("#wCPanelPass");
    const WordpressURL = $("#wWordpressURL");
    const WordpressUser = $("#wWordpressUser");
    const WordpressPass = $("#wWordpressPass");
    const SMTPEmailUser = $("#wSMTPEmailUser");
    const SMTPEmailPass = $("#wSMTPEmailPass");
    const SMTPRemark = $("#wSMTPRemark");
    const ContactEmailUser = $("#wContactEmailUser");
    const ContactEmailPass = $("#wContactEmailPass");
    const ContactEmailRemark = $("#wContactEmailRemark");

    const filterShopType = $("#filterShopType");
    const filterSystem = $("#filterSystem");
    const filterStatus = $("#filterStatus");

    const newModalForm = new bootstrap.Modal(document.getElementById("formModal"), {});

    let shopType = filterShopType.val();
    let system = filterSystem.val();
    let fstatus = filterStatus.val();

    let txt = '';
    let txt2 = '';
    let urlTxt = '';

    let iconCopy = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg>';
    let iconLink = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg>';

    function openFormModal() {
        newModalForm.show();
    }

    const viewDetail = (id) => {
        $.ajax({
            url: "assets/php/actionWebsiteList.php",
            method: "POST",
            dataType: "json",
            data: {
                act: "viewDetail",
                id: id
            },
            success: function (res) {
                txt = `<a onclick="copyText('${res.wProject}')" href="#">${iconCopy}</a> ${res.wProject}`;
                ProjectName.html(txt);
                Location.text(res.wLocation);
                Owner.text(res.wOwner);
                OwnerEmail.text(res.wOwnerEmail);
                Industry.text(res.wIndustry);
                TemplateUsed.text(res.wTemplateUsed);

                if (res.wSystemGloriaFood === 1) {
                    System.text("Gloria Food");
                } else if (res.wSystemAmelia === 1 && res.wSystemVoucher === 1) {
                    System.text("Amelia, Voucher");
                } else if (res.wSystemAmelia === 1) {
                    System.text("Amelia");
                } else if (res.wSystemVoucher === 1) {
                    System.text("Voucher");
                }

                DomainName.text(res.wDomain);
                DomainProvidersID.text(res.domainProvidersName);
                PublishedDate.text(res.wPublishedDate);
                LiveStatus.text(res.wLiveStatus);

                cPanelLink = `<a href="${res.svCpanelURL}" target="_blank">${iconLink}</a> ${res.svCpanelURL}`;
                cPanelURL.html(cPanelLink);
                CPanelUser.text(res.wCPanelUser);
                CPanelPass.text(res.wCPanelPass);

                urlTxt = `<a href="${res.wWordpressURL}" target="_blank">${iconLink}</a> ${res.wWordpressURL}`;
                WordpressURL.html(urlTxt);

                txt = `<a onclick="copyText('admin@localforyou.com')" href="#">${iconCopy}</a> admin@localforyou.com`;
                txt2 = `<a onclick="copyText('L4U=New@min')" href="#">${iconCopy}</a> L4U=New@min`;

                WordpressUser.html(res.wWordpressUser);
                WordpressPass.html(res.wWordpressPass);

                SMTPEmailUser.text(res.wSMTPEmailUser);
                SMTPEmailPass.text(res.wSMTPEmailPass);
                SMTPRemark.text(res.wSMTPRemark);
                ContactEmailUser.text(res.wContactEmailUser);
                ContactEmailPass.text(res.wContactEmailPass);
                ContactEmailRemark.text(res.wContactEmailRemark);

                $("#detailModal").modal("show");
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    };

    const setEdit = (id) => {

        const reqAjax = $.ajax({
            url: "assets/php/actionWebsiteList.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "loadUpdate",
                id: id,
            },
        });

        
        reqAjax.done(function (res) {
            console.log(res);
            inputProject.val(res.wProject);

            inputDomain.val(res.wDomain);
            inputLocation.val(res.wLocation);
            inputOwner.val(res.wOwner);
            inputOwnerEmail.val(res.wOwnerEmail);
            inputDomainProvider.val(res.wDomainProvidersID).prop("selected", true);
            inputPublishedDate.val(res.wPublishedDate);
            inputLiveStatus.val(res.wLiveStatus);
            inputShopType.val(res.wIndustry).prop("selected", true);
            inputTemplate.val(res.wTemplateUsed).prop("selected", true);
            inputServer.val(res.wServerID);
            inputCPanelUser.val(res.wCPanelUser);
            inputCPanelPass.val(res.wCPanelPass);
            inputWordpressUser.val(res.wWordpressUser);
            inputWordpressPass.val(res.wWordpressPass);
            inputWordpressURL.val(res.wWordpressURL);
            inputSMTPUser.val(res.wSMTPEmailUser);
            inputSMTPPass.val(res.wSMTPEmailPass);
            inputSMTPRemark.val(res.wSMTPRemark);
            inputContactEmailUser.val(res.wContactEmailUser);
            inputContactEmailPass.val(res.wContactEmailPass);
            inputContactEmailRemark.val(res.wContactEmailRemark);
            inputGloriaFood.prop("checked", res.wSystemGloriaFood === 1 ? 1 : 0);
            inputAmelia.prop("checked", res.wSystemAmelia === 1 ? 1 : 0);
            inputVoucher.prop("checked", res.wSystemVoucher === 1 ? 1 : 0);
            editID.val(res.id);
            formAction.val("edit");
            newModalFormAction("open");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax loadUpdate fail!!");
            console.log(status + ": " + error);
        })
    }// setEdit

    const formSave = () => {
        let payload = {
                act: "save",
                inputProject: inputProject.val(),
                inputLocation: inputLocation.val(),
                inputOwner: inputOwner.val(),
                inputOwnerEmail: inputOwnerEmail.val(),
                inputDomain: inputDomain.val(),
                inputDomainProvider: inputDomainProvider.val(),
                inputPublishedDate: inputPublishedDate.val(),
                inputLiveStatus: inputLiveStatus.val(), 
                inputShopType: inputShopType.val(),
                inputTemplate: inputTemplate.val(),
                inputServer: inputServer.val(),
                inputCPanelUser: inputCPanelUser.val(),
                inputCPanelPass: inputCPanelPass.val(),
                inputWordpressUser: inputWordpressUser.val(),
                inputWordpressPass: inputWordpressPass.val(),
                inputWordpressURL: inputWordpressURL.val(),
                inputSMTPUser: inputSMTPUser.val(),
                inputSMTPPass: inputSMTPPass.val(),
                inputSMTPRemark: inputSMTPRemark.val(),
                inputContactEmailUser: inputContactEmailUser.val(),
                inputContactEmailPass: inputContactEmailPass.val(),
                inputContactEmailRemark: inputContactEmailRemark.val(),
                inputGloriaFood: inputGloriaFood.prop("checked") ? 1 : 0,
                inputAmelia: inputAmelia.prop("checked") ? 1 : 0,
                inputVoucher: inputVoucher.prop("checked") ? 1 : 0,
                editID: editID.val(),
                formAction: formAction.val(),
            };

            console.log("payload=",payload);
            
        const reqAjax = $.ajax({
            url: "assets/php/actionWebsiteList.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload
        });
            
        reqAjax.done(function (res) {
            
            console.log(res);
            reloadTable();
            resetForm();
            newModalFormAction("close");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax save fail!!");
            console.log(status + ": " + error);
        });
        
    }//formSave

    const resetForm = () => {
        inputProject.val('');
        inputLocation.val('');
        inputOwner.val('');
        inputOwnerEmail.val('');
        inputDomain.val('');
        inputDomainProvider.val('');
        inputPublishedDate.val('');
        inputLiveStatus.val('');
        inputShopType.val('');
        inputTemplate.val('');
        inputServer.val('');
        inputCPanelUser.val('');
        inputCPanelPass.val('');
        inputWordpressUser.val('');
        inputWordpressPass.val('');
        inputWordpressURL.val('');
        inputSMTPUser.val('');
        inputSMTPPass.val('');
        inputSMTPRemark.val('');
        inputContactEmailUser.val('');
        inputContactEmailPass.val('');
        inputContactEmailRemark.val('');
        inputGloriaFood.prop("checked", false);
        inputAmelia.prop("checked", false);
        inputVoucher.prop("checked", false);
        editID.val('');
        formAction.val('add');
    }//resetForm

    const setDel = (delID) => {

        let answer = confirm ("Are you sure to delete this Staff?");

        console.log (answer);
        if (answer === true){
            let payload = {
                act: "setDelete",
                id : delID
            };
            console.log("payload=",payload);

            const reqAjax = $.ajax({
                url: "assets/php/actionWebsiteList.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: payload
            });
                
            reqAjax.done(function (res) {
                modalFormAction("close");
                console.log(res);
                reloadTable();
                resetForm();
                $("#formModal").modal('hide');
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax setDel fail!!");
                console.log(status + ": " + error);
            });

        }//if
    }//setDel

    function newModalFormAction(action) {
    console.log("goNew = " + action);
    if (action === "open") {
        newModalForm.show();
    } else {
        newModalForm.hide();
        $(".modal-backdrop").hide();
    }
}

</script>