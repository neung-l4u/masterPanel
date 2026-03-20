<?php
global$showPage, $activeMenu, $coins;
$coins["l4u"] = $_SESSION['L4UCoin'];
$coins["ceo"] = $_SESSION['CEOCoin'];
$userLevel = $_SESSION['level'];
$teamID = $_SESSION['teamID'];
include ('assets/api/checkSession.php');
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="main.php" class="brand-link">
        <img src="assets/img/logo-login2.png" alt="L4U Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Master Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar text-xs" style="display:flex;flex-direction:column;min-height:calc(100vh - 57px);">
        <!-- Sidebar user panel moved to navbar dropdown -->



        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Home -->
                <li class="nav-item">
                    <a href="main.php?p=home" class="nav-link <?php echo $activeMenu["lv1"] == "home" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-house"></i>
                        <p>Home</p>
                    </a>
                </li>

                <!-- My Profile -->
                <li class="nav-item">
                    <a href="main.php?p=myProfile" class="nav-link <?php echo $activeMenu["lv1"] == "myProfile" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-person"></i>
                        <p>My Profile</p>
                    </a>
                </li>

                <!-- Website Management -->
                <li class="nav-item <?php echo $activeMenu["lv1"] == "websiteMgmt" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "websiteMgmt" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-globe"></i>
                        <p>Website Management <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="https://report.localforyou.com/modules/websiteList/views/websiteList.php#" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-list-check"></i>
                                <p>Website Lists &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                        <?php if($userLevel<=4 && ($teamID == 3 || $teamID == 5)){ ?>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=websiteTemplate" class="nav-link <?php echo $activeMenu["lv2"] == "websiteTemplate" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-browser-chrome"></i>
                                <p>Website Template</p>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>

                <!-- System -->
                <li class="nav-item <?php echo $activeMenu["lv1"] == "system" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "system" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-hdd-stack"></i>
                        <p>System <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="modules/L4UBooking" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-bookmarks"></i>
                                <p>L4U Booking &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Form Management -->
                <?php if($userLevel<=4){ ?>
                <li class="nav-item <?php echo $activeMenu["lv1"] == "formMgmt" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "formMgmt" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-file-earmark-medical"></i>
                        <p>Form Management <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="modules/signup/index.php" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-file-earmark-person"></i>
                                <p>Signup Form &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                        <?php if($teamID != 3){ ?>
                        <li class="nav-item pl-2">
                            <a href="modules/unsub2/views/index.php?id=123" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-file-earmark-excel"></i>
                                <p>Unsubscribe Form &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="modules/templates/views/main.php" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-file-earmark-break"></i>
                                <p>Template Submissions &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <!-- Rewards & Coins -->
                <?php if($userLevel<=3){ ?>
                <li class="nav-item <?php echo $activeMenu["lv1"] == "rewardsCoin" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "rewardsCoin" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-gem"></i>
                        <p>Rewards & Coins <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="main.php?p=rewardCoin" class="nav-link <?php echo $activeMenu["lv2"] == "rewardCoin" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-gem"></i>
                                <p>Reward Coin</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=coin" class="nav-link <?php echo $activeMenu["lv2"] == "coin" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-coin"></i>
                                <p>L4U Coin</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="https://forms.monday.com/forms/da9ca9feccd4e43b4d264a3b45ba38ed?r=apse2" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-file-plus"></i>
                                <p>L4U Coin Request &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <!-- Reports & Analytics -->
                <?php if($userLevel<=3){ ?>
                <li class="nav-item <?php echo $activeMenu["lv1"] == "report" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "report" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-bar-chart-line"></i>
                        <p>Reports & Analytics <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="main.php?p=reportWeekly" class="nav-link <?php echo in_array($activeMenu["lv2"], ['reportWeekly','reportMonthly','reportYearly']) ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-clipboard-data"></i>
                                <p>Subscription Report</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=reportGA" class="nav-link <?php echo $activeMenu["lv2"] == "reportGA" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-google"></i>
                                <p>Google Analytics</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=reportRevenue" class="nav-link <?php echo $activeMenu["lv2"] == "reportRevenue" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-cash-stack"></i>
                                <p>Revenue</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="modules/mondayReport/views/index.php?id=<?php echo $_SESSION['id']; ?>" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-kanban"></i>
                                <p>Monday Report &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <!-- Logs -->
                <li class="nav-item <?php echo $activeMenu["lv1"] == "logs" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "logs" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-journal-text"></i>
                        <p>Logs <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item pl-2">
                            <a href="modules/changeLog/changelog.php" target="_blank" class="nav-link">
                                <i class="nav-icon mr-3 bi bi-clock-history"></i>
                                <p>Change Logs &nbsp; <i class="bi bi-box-arrow-up-right"></i></p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User Management -->
                <?php if($userLevel<=3){ ?>
                <li class="nav-item <?php echo $activeMenu["lv1"] == "userMgmt" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "userMgmt" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-people"></i>
                        <p>User Management <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if($userLevel<=2){ ?>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=setStaff" class="nav-link <?php echo $activeMenu["lv2"] == "staffs" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-person-gear"></i>
                                <p>Staffs</p>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=checkinLogs" class="nav-link <?php echo $activeMenu["lv2"] == "checkinLogs" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-clock-history"></i>
                                <p>Check-in Logs</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <!-- System Settings -->
                <li class="nav-item <?php echo $activeMenu["lv1"] == "sysSettings" ? "menu-is-opening menu-open":""; ?>">
                    <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "sysSettings" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-gear"></i>
                        <p>System Settings <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if($userLevel<=2){ ?>
                        <!-- <li class="nav-item pl-2">
                            <a href="main.php?p=settings" class="nav-link <?php echo $activeMenu["lv2"] == "settings" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-sliders"></i>
                                <p>Settings</p>
                            </a>
                        </li> -->
                        <?php } ?>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=tools" class="nav-link <?php echo $activeMenu["lv2"] == "tools" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-tools"></i>
                                <p>Tools</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=feedbackMonday" class="nav-link <?php echo $activeMenu["lv2"] == "feedbackMonday" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-chat-left-text"></i>
                                <p>Feedback Monday</p>
                            </a>
                        </li>
                        <li class="nav-item pl-2">
                            <a href="main.php?p=l4uPassword" class="nav-link <?php echo $activeMenu["lv2"] == "l4uPassword" ? "active":""; ?>">
                                <i class="nav-icon mr-3 bi bi-key"></i>
                                <p>Password</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
        <div class="text-center py-3" style="margin-top:auto;font-size:11px;color:#FFFFFF;border-top:1px solid rgba(255, 255, 255, 0.1);">
            Version 1.0.0
        </div>
    </div>
    <!-- /.sidebar -->
</aside>