<?php
$userLevel = $_SESSION['level'];
$myID = $_SESSION['id'];
$teamID = $_SESSION['teamID'];
$navNickName = $_SESSION['nickName'] ?? '';
$navFirstName = explode(' ', $_SESSION['name'])[0];
$navDisplayName = $navNickName ? $navNickName . ' ' . $navFirstName : $navFirstName;
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item pl-5">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
            <!-- <li class="nav-item d-none d-sm-inline-block">
                <a href="https://forms.monday.com/forms/da9ca9feccd4e43b4d264a3b45ba38ed?r=apse2" target="_blank" class="nav-link">Coin Request <i class="bi bi-box-arrow-up-right"></i></a>
            </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="https://app.respond.io/user/login" target="_blank" class="nav-link">Respond.io <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="https://app.trainual.com/local-for-you/users/sign_in" target="_blank" class="nav-link">Trainual <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="modules/changeLog/changelog.php" target="_blank" class="nav-link">Change Log <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->


    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <!-- Navbar Search -->
        <?php if ($teamID == 5){ ?>
        <li class="nav-item">
            <a class="nav-link" href="main.php?p=tools" role="button">
                <i class="bi bi-tools"></i>
            </a>
        </li>
        <?php } ?>


        <!-- Activity Dropdown -->
        <?php
        global $db;
        // Get all recent activities for display (max 5)
        $navActivities = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`amount`, ST.`sNickName` AS "nick", ST.`sPic` AS "pic", CL.`reason`, CL.`giveOn`, CA.`aName` 
                FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT, `CoinActivities` CA 
                WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id` AND CL.`activityID` = CA.`aID`
                ORDER BY CL.`giveOn` DESC LIMIT 5;', $myID, 1)->fetchAll();
        
        // Count only unread activities for badge (using is_read column)
        $unreadCount = $db->query('SELECT COUNT(*) as count FROM CoinLogs WHERE ownerID = ? AND is_read = 0', $myID)->fetchAll();
        $navActivityCount = $unreadCount[0]['count'];
        
        // Show badge if there are any unread activities
        $showBadge = ($navActivityCount > 0);
        $latestActivityId = count($navActivities) > 0 ? $navActivities[0]['id'] : 0;
        ?>
        <li class="nav-item dropdown" id="activityDropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" style="position:relative;" onclick="markActivityRead()">
                <i class="bi bi-bell" style="font-size:1.1rem;"></i>
                <?php if($showBadge){ ?>
                    <span id="activityBadge" class="badge badge-danger navbar-badge" style="font-size:0.6rem;padding:2px 5px;border-radius:10px;"><?php echo $navActivityCount; ?></span>
                <?php } ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="min-width:340px;max-width:380px;border:none;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:0;overflow:hidden;">
                <div style="padding:0.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-weight:700;font-size:0.9rem;color:#0f172a;">Activity</span>
                    <span style="font-size:0.7rem;color:#94a3b8;background:#f1f5f9;padding:0.15rem 0.5rem;border-radius:8px;"><?php echo $navActivityCount; ?> unread</span>
                </div>
                <div style="max-height:320px;overflow-y:auto;">
                    <?php if(count($navActivities) >= 1){ foreach($navActivities as $act){ ?>
                        <div class="d-flex align-items-start gap-2" style="padding:0.75rem 1.25rem;border-bottom:1px solid #f8fafc;transition:background 0.2s;cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <img src="dist/img/crews/<?php echo $act['pic']; ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;flex-shrink:0;margin-top:2px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:0.8rem;color:#0f172a;"><?php echo htmlspecialchars($act['aName']); ?></div>
                                <div style="font-size:0.72rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($act['reason']); ?>
                                </div>
                                <div style="font-size:0.68rem;color:#94a3b8;margin-top:2px;">
                                    <i class="fas fa-coins" style="color:#fbbf24;font-size:0.6rem;"></i>
                                    <?php echo $act['coin']; ?> · by <?php echo htmlspecialchars($act['nick']); ?> · <?php echo date("d M H:i", strtotime($act['giveOn'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php } } else { ?>
                        <div style="padding:2rem;text-align:center;color:#94a3b8;">
                            <i class="bi bi-bell-slash" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>
                            <span style="font-size:0.8rem;">No activity yet</span>
                        </div>
                    <?php } ?>
                </div>
                <a href="main.php?p=myProfile" style="display:block;padding:0.7rem;text-align:center;font-size:0.8rem;font-weight:600;color:#0619B6;border-top:1px solid #f1f5f9;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    View All Activity
                </a>
            </div>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li> -->
        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 px-3" data-toggle="dropdown" href="#" style="cursor:pointer;">
                <img src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" 
                     alt="User" 
                     class="rounded-circle" 
                     style="width:32px;height:32px;object-fit:cover;border:2px solid #e2e8f0;">
                <span class="d-none d-md-inline-block" style="font-weight:600;color:#0f172a;font-size:0.85rem;">
                    <?php echo $navDisplayName; ?>
                </span>
                <i class="fas fa-chevron-down" style="font-size:0.6rem;color:#94a3b8;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="min-width:260px;border:none;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:0;overflow:hidden;">
                <!-- User Info -->
                <div style="padding:1rem 1.25rem;background:linear-gradient(135deg,#0619B6,#0361D1);color:#fff;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" 
                             alt="User" class="rounded-circle" 
                             style="width:40px;height:40px;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;"><?php echo $_SESSION['name']; ?></div>
                            <div style="font-size:0.75rem;opacity:0.8;"><?php echo $_SESSION['levelName']; ?></div>
                        </div>
                    </div>
                    <!-- Coins -->
                    <div class="d-flex gap-2 mt-2">
                        <div style="background:rgba(255,255,255,0.15);border-radius:8px;padding:0.3rem 0.6rem;font-size:0.75rem;display:flex;align-items:center;gap:0.3rem;">
                            <i class="fas fa-coins" style="color:#fbbf24;"></i>
                            L4U: <b><?php echo number_format($_SESSION['L4UCoin'],2); ?></b>
                        </div>
                        <div style="background:rgba(255,255,255,0.15);border-radius:8px;padding:0.3rem 0.6rem;font-size:0.75rem;display:flex;align-items:center;gap:0.3rem;">
                            <i class="fas fa-coins" style="color:#fbbf24;"></i>
                            GM: <b><?php echo number_format($_SESSION['CEOCoin'],2); ?></b>
                        </div>
                    </div>
                </div>
                <!-- Menu Items -->
                <div style="padding:0.5rem 0;">
                    <a href="main.php?p=myProfile" class="dropdown-item d-flex align-items-center gap-2" style="padding:0.6rem 1.25rem;font-size:0.85rem;">
                        <i class="bi bi-person" style="font-size:1rem;color:#64748b;"></i>
                        My Profile
                    </a>
                    <div class="dropdown-divider" style="margin:0.25rem 0;"></div>
                    <a href="chkLogin.php?act=logout" class="dropdown-item d-flex align-items-center gap-2" style="padding:0.6rem 1.25rem;font-size:0.85rem;color:#ef4444;">
                        <i class="bi bi-box-arrow-right" style="font-size:1rem;"></i>
                        Sign Out
                    </a>
                </div>
            </div>
        </li>
    </ul>
</nav>
