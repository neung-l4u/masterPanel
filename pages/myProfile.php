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
$timestamp = time();
$coins["l4u"] = $_SESSION['L4UCoin'];
$coins["ceo"] = $_SESSION['CEOCoin'];
$loginID = $_SESSION['id'];
?>

<!-- ===== Content Header ===== -->
<div class="content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="profile-hero rounded-3 mb-3 p-4 text-white d-flex align-items-center gap-3">
                    <div class="avatar-wrap me-3 position-relative" style="cursor:pointer;" onclick="document.getElementById('profilePicInput').click();">
                        <img id="profilePicPreview" class="profile-user-img img-fluid rounded-circle shadow mr-3 profile-pic-update"
                             src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>"
                             alt="User profile picture"
                             style="width:84px;height:84px;object-fit:cover;">
                        <div class="profile-pic-overlay position-absolute d-flex align-items-center justify-content-center" 
                             style="top:0;left:0;width:84px;height:84px;border-radius:50%;background:rgba(0,0,0,0.5);opacity:0;transition:opacity 0.3s;">
                            <i class="fas fa-camera text-white"></i>
                        </div>
                        <input type="file" id="profilePicInput" accept="image/*" style="display:none;" onchange="uploadProfilePic(this);">
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="m-0 fw-semibold"><?php echo $_SESSION['name']; ?></h3>
                        <div class="text-white-50 small"><?php echo $_SESSION['levelName']; ?></div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
              <span class="coin-chip mr-1">
                <i class="fas fa-coins me-1"></i> L4U:
                <b><?php echo number_format($coins['l4u'],2); ?></b>
              </span>
                            <span class="coin-chip">
                <i class="fas fa-coins me-1"></i> CEO:
                <b><?php echo number_format($coins['ceo'],2); ?></b>
              </span>
                        </div>
                    </div>
                    <div class="ms-auto d-none d-md-block">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a class="text-white" href="main.php?p=home">Home</a></li>
                            <li class="breadcrumb-item text-white-50 active" aria-current="page">My Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Main content ===== -->
<div class="content">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left -->
                <div class="col-lg-4 col-xl-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-3 mr-2 profile-pic-update"
                                     src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>"
                                     alt="User profile picture"
                                     style="width:56px;height:56px;object-fit:cover;">
                                <div>
                                    <div class="fw-semibold"><?php echo $_SESSION['name']; ?></div>
                                    <div class="text-muted small"><?php echo $_SESSION['levelName']; ?></div>
                                </div>
                            </div>

                            <hr>

                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <span class="text-muted small d-block"><i class="fas fa-tag me-1"></i> Name</span>
                                    <span><?php echo $_SESSION['name']; ?></span>
                                </li>
                                <li class="mb-2">
                                    <span class="text-muted small d-block"><i class="fas fa-mobile me-1"></i> Mobile</span>
                                    <span><?php echo $_SESSION['phone']; ?></span>
                                </li>
                                <li class="mb-2">
                                    <span class="text-muted small d-block"><i class="fas fa-star me-1"></i> Level</span>
                                    <span class="badge bg-primary-subtle text-primary"><?php echo $_SESSION['levelName']; ?></span>
                                </li>
                            </ul>

                            <div class="mt-3 p-3 rounded-3 bg-warning-subtle">
                                <?php
                                $salt = "L4U";
                                $passwordAddSalt = $salt . $_SESSION['password'];
                                $data["passwordHash"] = md5($passwordAddSalt);
                                $isDefault = in_array($data["passwordHash"], ["e30d60a4848903ed23c42a8d45eccdba","35d3f3a0f752f01118028849afdf3c08"]);
                                ?>
                                <strong class="d-flex align-items-center gap-2">
                                    <i class="far fa-file-alt"></i>
                                    Notes
                                    <?php if($isDefault){ ?>
                                        <span class="badge bg-warning text-dark">Important</span>
                                    <?php } ?>
                                </strong>
                                <p class="mb-0 mt-1 small">
                                    <?php if($isDefault){ ?>
                                        <span class="text-danger">Your password is still the standard password. Please change it as soon as possible.</span>
                                    <?php } else { ?>
                                        <span class="text-success">You have changed your password. Your account is secure.</span>
                                    <?php } ?>
                                </p>
                            </div>

                            <a href="#" class="btn btn-primary w-100 mt-3 disabled">
                                <i class="fas fa-exchange-alt me-1"></i> Convert Coin (soon)
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.Left -->

                <!-- Right -->
                <div class="col-lg-8 col-xl-9">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <?php
                            $logs = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`ownerID`, CL.`amount`, ST.`sNickName` AS "nick",ST.`sName` AS "from", ST.`sPic` AS "pic", CL.`reason`, CL.`giveOn`, CL.`lastUpdate`, CL.`activityID`, CA.`aName` 
                                    FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT, `CoinActivities` CA 
                                    WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id` AND CL.`activityID` = CA.`aID`
                                    ORDER BY CL.`giveOn` DESC;', $loginID, 1)->fetchAll();
                            ?>
                            <ul class="nav nav-pills" id="profileTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#activity" data-toggle="tab">
                                        <i class="fas fa-list me-1"></i> Activity (<?php echo number_format(count($logs)); ?>)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings" data-toggle="tab">
                                        <i class="fas fa-user-cog me-1"></i> Settings
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Activity -->
                                <div class="tab-pane fade show active" id="activity">
                                    <div class="activity-scroll pe-1" style="max-height:680px;overflow:auto;">
                                        <?php if(count($logs)>=1){ $i=count($logs); foreach($logs as $row){ ?>
                                            <div class="activity-item d-flex align-items-start py-3 border-bottom">
                                                <img class="rounded-circle me-3 profile-pic-update"
                                                     src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>"
                                                     alt="user"
                                                     style="width:40px;height:40px;object-fit:cover;">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <a class="fw-semibold text-decoration-none" href="#">
                                                            <?php echo htmlspecialchars($row['aName']); ?>
                                                        </a>
                                                        <small class="text-muted">#<?php echo $i; ?></small>
                                                    </div>
                                                    <div class="text-muted small mb-1">
                                                        by <?php echo htmlspecialchars($row['nick']); ?> · <?php echo showDate($row['giveOn']); ?>
                                                    </div>
                                                    <div class="text-secondary">
                                                        <?php echo nl2br(htmlspecialchars($row['reason'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $i--; } } else { ?>
                                            <div class="text-center text-muted py-5">
                                                <i class="far fa-folder-open fa-2x d-block mb-2"></i>
                                                No activity yet.
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!-- /.Activity -->

                                <!-- Settings -->
                                <div class="tab-pane fade" id="settings">
                                    <form class="form-horizontal" onsubmit="return false;">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label class="form-label">Name</label>
                                                <div class="form-control-plaintext"><?php echo $_SESSION['name']; ?></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label">Email</label>
                                                <div class="form-control-plaintext"><?php echo $_SESSION['email']; ?></div>
                                            </div>

                                            <div class="col-12">
                                                <hr class="my-2">
                                                <div class="text-muted small mb-2">Change Password</div>
                                            </div>

                                            <!-- Current Password (แสดงจริง + toggle ได้) -->
                                            <div class="col-12">
                                                <label for="currentPassword" class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <input class="form-control pass" id="currentPassword" type="password" disabled
                                                           value="<?php echo $_SESSION['password'];?>">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            data-toggle="password" data-target="#currentPassword"
                                                            aria-pressed="false" aria-label="Toggle password visibility">
                                                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="inputNewPassword" class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" autocomplete="new-password" class="form-control pass" id="inputNewPassword" placeholder="New Password">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            data-toggle="password" data-target="#inputNewPassword"
                                                            aria-pressed="false" aria-label="Toggle password visibility">
                                                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text" id="pwStrength" aria-live="polite"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="inputRetype" class="form-label">Retype New Password</label>
                                                <div class="input-group">
                                                    <input type="password" autocomplete="new-password" class="form-control pass" id="inputRetype" placeholder="Retype password">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            data-toggle="password" data-target="#inputRetype"
                                                            aria-pressed="false" aria-label="Toggle password visibility">
                                                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="ackConfirm">
                                                    <label class="form-check-label" for="ackConfirm">
                                                        I agree my password is encrypted. If I forget it, I can only reset to a new one.
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="button" class="btn btn-danger" onclick="cmdSubmit();" id="btnSave">Save Change</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.Settings -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.Right -->
            </div>
        </div>
    </section>
</div>
<!-- /.content -->

<style>
    .profile-hero{
        background-color:#0d6efd;
    }
    .coin-chip{
        display:inline-flex; align-items:center; gap:.25rem;
        background:#ffffff22; color:#fff;
        border:1px solid rgba(255,255,255,.25);
        padding:.25rem .6rem; border-radius:999px; font-size:.9rem;
    }
    .activity-item:last-child{ border-bottom:0; }
    .activity-scroll{ scrollbar-width:thin; }
    .activity-scroll::-webkit-scrollbar{ height:8px; width:8px; }
    .activity-scroll::-webkit-scrollbar-thumb{ background:#d0d5dd; border-radius:8px; }
    .avatar-wrap:hover .profile-pic-overlay{ opacity:1 !important; }
</style>

<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-toggle="password"]');
        if (!btn) return;

        const targetSelector = btn.getAttribute('data-target');
        if (!targetSelector) return;

        const input = document.querySelector(targetSelector);
        if (!input) return;

        // สลับ type
        input.type = (input.type === 'password') ? 'text' : 'password';

        // สลับไอคอน (รองรับทั้ง fa และ fas)
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            icon.classList.toggle('fas'); // เผื่อหน้าใช้ fas
            icon.classList.add('fa');     // เผื่อหน้าใช้ fa
        }

        // ARIA state
        const pressed = btn.getAttribute('aria-pressed') === 'true';
        btn.setAttribute('aria-pressed', (!pressed).toString());
    });
</script>
<!-- ===== Scripts ===== -->
<script>
    // Upload profile picture
    const uploadProfilePic = (input) => {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            toast('File too large. Max 5MB.', 'warning');
            return;
        }
        
        // Preview image immediately
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update all profile images on page using class
            document.querySelectorAll('.profile-pic-update').forEach(img => {
                img.src = e.target.result;
            });
        };
        reader.readAsDataURL(file);
        
        // Upload to server
        const formData = new FormData();
        formData.append('act', 'uploadProfilePic');
        formData.append('profilePic', file);
        
        $.ajax({
            url: 'assets/php/actionStaffs.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function(res) {
            if (res.status === 'success') {
                toast('Profile picture updated!', 'success');
            } else {
                toast(res.message || 'Upload failed.', 'danger');
            }
        }).fail(function() {
            toast('Upload failed. Please try again.', 'danger');
        });
    };

    // เบา ๆ: password strength hint
    $('#inputNewPassword').on('input', function(){
        const v = $(this).val();
        let score = 0;
        if(v.length >= 8) score++;
        if(/[A-Z]/.test(v)) score++;
        if(/[a-z]/.test(v)) score++;
        if(/[0-9]/.test(v)) score++;
        if(/[^A-Za-z0-9]/.test(v)) score++;
        const map = ['Very weak','Weak','Okay','Good','Strong','Excellent'];
        $('#pwStrength').text(v ? 'Strength: ' + map[score] : '');
    });

    // Submit + validations
    const cmdSubmit = () => {
        const newPassword = $('#inputNewPassword').val().trim();
        const retypePassword = $('#inputRetype').val().trim();
        const ack = $('#ackConfirm').is(':checked');

        if(!newPassword || !retypePassword){
            return toast('Please fill both password fields.','warning');
        }
        if(newPassword !== retypePassword){
            return toast('Passwords do not match.','danger');
        }
        if(newPassword.length < 8){
            return toast('Password must be at least 8 characters.','warning');
        }
        if(!ack){
            return toast('Please confirm the acknowledgement.','info');
        }

        const payload = { act: "changePassword", password: newPassword, token: Math.random() };

        $.ajax({
            url: "assets/php/actionStaffs.php",
            method: "POST",
            dataType: "json",
            data: payload,
        }).done(function(res){
            toast('Password changed successfully.','success');
            setTimeout(()=>location.reload(), 700);
        }).fail(function(xhr, status, error){
            console.log(status + ": " + error);
            toast('Request failed. Please try again.','danger');
        });
    };

    // Simple toast
    const toast = (msg,type='primary')=>{
        const id = 'to_'+Math.random().toString(36).slice(2);
        const $t = $(`
      <div id="${id}" class="position-fixed top-0 end-0 p-3" style="z-index:1080;">
        <div class="toast align-items-center text-bg-${type} border-0 show" role="alert">
          <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>
      </div>
    `);
        $('body').append($t);
        setTimeout(()=>{$t.remove();}, 2600);
    };
</script>

<?php
function showDate($data){
    return date("d/m/Y (H:i)", strtotime($data));
}
?>