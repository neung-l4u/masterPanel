<?php
global $db, $date;
$timestamp = time();
$coins["l4u"] = $_SESSION['L4UCoin'];
$coins["ceo"] = $_SESSION['CEOCoin'];
$loginID = $_SESSION['id'];

?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path d="M96 0C60.7 0 32 28.7 32 64l0 384c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-384c0-35.3-28.7-64-64-64L96 0zM208 288l64 0c44.2 0 80 35.8 80 80c0 8.8-7.2 16-16 16l-192 0c-8.8 0-16-7.2-16-16c0-44.2 35.8-80 80-80zm-32-96a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM512 80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64zM496 192c-8.8 0-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64c0-8.8-7.2-16-16-16zm16 144c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64z" /></svg>
                    My Profile
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><a href="main.php?p=myProfile">My Profile</a></li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>"
                                     alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $_SESSION['name']; ?></h3>

                            <p class="text-muted text-center"><?php echo $_SESSION['levelName']; ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-coins"></i> L4U</b> <a class="float-right"><?php echo number_format($coins['l4u'],2); ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-coins"></i> CEO</b> <a class="float-right"><?php echo number_format($coins['ceo'],2); ?></a>
                                </li>
                            </ul>

                            <a href="#" class="btn btn-primary btn-block disabled"><b>Convert Coin</b> (soon)</a>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <!-- About Me Box -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Me</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <strong><i class="fas fa-tag mr-1"></i> Name</strong>

                            <p class="text-muted">
                                <?php echo $_SESSION['name']; ?>
                            </p>

                            <hr>

                            <strong><i class="fas fa-mobile mr-1"></i> Mobile</strong>

                            <p class="text-muted">
                                <?php echo $_SESSION['phone']; ?>
                            </p>

                            <hr>

                            <strong><i class="fas fa-star mr-1"></i> Level</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">
                                    <?php echo $_SESSION['levelName']; ?>
                                </span>
                            </p>

                            <hr>
                            <?php
                            $salt = "L4U";
                            $passwordAddSalt = $salt . $_SESSION['password'];
                            $data["passwordHash"] = md5($passwordAddSalt);
                            ?>
                            <strong><i class="far fa-file-alt mr-1"></i> Notes <span class="badge badge-warning">Important</span> </strong>

                            <p class="text-muted">
                                <?php if(($data["passwordHash"] == "e30d60a4848903ed23c42a8d45eccdba") or ($data["passwordHash"] == "35d3f3a0f752f01118028849afdf3c08")){ ?>
                                    <span class="text-danger">Your password is still the standard password set by the system. For security reasons, please change your password as soon as possible.</span>
                                <?php }else{ ?>
                                    <span class="text-success">You have changed your password. Your account is secure.</span>
                                <?php }//else ?>
                            </p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <?php
                            $logs = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`ownerID`, CL.`amount`, ST.`sNickName` AS "nick",ST.`sName` AS "from", ST.`sPic` AS "pic", CL.`reason`, CL.`giveOn`, CL.`lastUpdate`, CL.`activityID`, CA.`aName` 
                                                        FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT, `CoinActivities` CA 
                                                        WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id` AND CL.`activityID` = CA.`aID`
                                                        ORDER BY CL.`giveOn` DESC;'
                                , $loginID, 1)->fetchAll();
                            ?>
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#activity" data-toggle="tab">
                                        <i class="fas fa-list"></i> Activity (<?php echo number_format(count($logs)); ?> items)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings" data-toggle="tab">
                                        <i class="fas fa-edit"></i> Settings
                                    </a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="activity" style="max-height: 700px; overflow-y: scroll;">
                                    <?php
                                    if (count($logs)>=1) {
                                        $i = count($logs);
                                        foreach ($logs as $row) {
                                            ?>
                                    <!-- Post -->
                                    <div class="post">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm" src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" alt="user image">
                                            <span class="username">
                                              <a href="#">item <?php echo $i; ?>: <?php echo $row['aName']; ?></a>
                                              <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                                            </span>
                                            <span class="description">by <?php echo $row['nick']; ?> - <?php echo showDate($row['giveOn']); ?></span>
                                        </div>
                                        <!-- /.user-block -->
                                        <p class="text-muted">
                                            <?php echo $row['reason']; ?>
                                        </p>

                                    </div>
                                    <!-- /.post -->
                                            <?php
                                            $i--;
                                        }//foreach
                                    }//if
                                    ?>

                                </div>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="settings">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                            <div class="col-sm-10">
                                                <?php echo $_SESSION['name']; ?>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <?php echo $_SESSION['email']; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group row">
                                            <label for="currentPassword" class="col-sm-2 col-form-label">Current Password</label>
                                            <div class="col-sm-10 input-group" id="show_hide_password">
                                                <input class="form-control pass" id="currentPassword" type="password" disabled value="<?php echo $_SESSION['password'];?>">
                                                <div class="input-group-append">
                                                    <i onclick="showHidePassword();" class="eyeIcon input-group-text fa fa-eye-slash" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputNewPassword" class="col-sm-2 col-form-label">New Password</label>
                                            <div class="col-sm-10 input-group">
                                                <input type="password" autocomplete="new-password" class="form-control pass" id="inputNewPassword" placeholder="New Password">
                                                <div class="input-group-append">
                                                    <i onclick="showHidePassword();" class="eyeIcon input-group-text fa fa-eye-slash" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputRetype" class="col-sm-2 col-form-label">Retype</label>
                                            <div class="col-sm-10 input-group">
                                                <input type="password" autocomplete="new-password" class="form-control pass" id="inputRetype" placeholder="Retype password">
                                                <div class="input-group-append">
                                                    <i onclick="showHidePassword();" class="eyeIcon input-group-text fa fa-eye-slash" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"> I agree, my password is encrypted. If I forget my password, I can only reset it to a new one.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="button" class="btn btn-danger" onclick="cmdSubmit();">Save Change</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
</div>
<!-- /.content -->
<script>
    const showHidePassword = () => {
        if($('#currentPassword').attr("type") === "text"){
            $('.pass').attr('type', 'password');
            $('.eyeIcon').addClass( "fa-eye-slash" );
            $('.eyeIcon').removeClass( "fa-eye" );
        }else if($('#currentPassword').attr("type") === "password"){
            $('.pass').attr('type', 'text');
            $('.eyeIcon').removeClass( "fa-eye-slash" );
            $('.eyeIcon').addClass( "fa-eye" );
        }
    };

    const cmdSubmit = () => {
        const newPassword = $('#inputNewPassword').val();
        const retypePassword = $('#inputRetype').val();

        if(newPassword !== retypePassword){
            alert('Passwords do not match.');
        }else {
            let payload = {
                act: "changePassword",
                password: newPassword,
                token: Math.random()
            };

            console.log(payload);

            const reqAjax = $.ajax({
                url: "assets/php/actionStaffs.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: payload,
            });

            reqAjax.done(function (res) {
                console.log(res);
                location.reload();
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });
        }//else
    }//cmdSubmit
</script>
<?php
function showDate($data){
    return date( "d/m/Y (H:i)", strtotime($data));
}
?>