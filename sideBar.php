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
    <div class="sidebar text-xs">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-between">
            <div class="d-flex">
                <div class="image">
                    <a href="main.php?p=myProfile"><img src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" class="img-circle elevation-2" alt="User Image"></a>
                </div>
                <div class="info">
                    <a href="main.php?p=myProfile" class="d-block"><?php echo $_SESSION['name']; ?></a>
                    <a href="main.php?p=myProfile" class="d-block"><?php echo $_SESSION['levelName']; ?></a>

                    <div class="d-block d-flex justify-content-between">
                        <span class="text-warning font-weight-bold">L4U : </span>
                        <span class="text-white"><?php echo number_format($coins['l4u'],2); ?></span>
                    </div>
                    <div class="d-block d-flex justify-content-between">
                        <span class="text-warning font-weight-bold">CEO : </span>
                        <span class="text-white"><?php echo number_format($coins['ceo'],2); ?></span>
                    </div>
                </div>
            </div>
            <div class="info">
                <a href="chkLogin.php?act=logout" class="d-block"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="main.php?p=home" class="nav-link <?php echo $activeMenu["lv1"] == "home" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-house"></i>
                        <p>Home</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="main.php?p=myProfile" class="nav-link <?php echo $activeMenu["lv1"] == "myProfile" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-person"></i>
                        <p>My Profile</p>
                    </a>
                </li>
                <?php /*if($userLevel<=3){ */?><!--
                <li class="nav-item">
                    <a href="main.php?p=dashboard" class="nav-link <?php /*echo $activeMenu["lv1"] == "dashboard" ? "active":""; */?>">
                        <svg class="nav-icon mr-2" height="1.5em" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 64 80" x="0px" y="0px"><path d="M12.669,52.23c5.03,4.807,11.837,7.77,19.328,7.77,15.105,0,27.447-12.025,27.975-27.004h-27.576L12.669,52.23Z" fill="<?php /*echo $activeMenu["lv1"] == "dashboard" ? "#FB8500":"#a7acb6"; */?>"/><path d="M32.989,4.025V30.996h26.982c-.52-14.646-12.335-26.457-26.982-26.971Z" fill="<?php /*echo $activeMenu["lv1"] == "dashboard" ? "#FB8500":"#a7acb6"; */?>"/><path d="M30.989,4.026C16.016,4.559,3.997,16.898,3.997,32c0,7.231,2.755,13.83,7.271,18.803L30.989,31.574V4.026Z" fill="<?php /*echo $activeMenu["lv1"] == "dashboard" ? "#FB8500":"#a7acb6"; */?>"/></svg>
                        <p>Dashboard</p>
                    </a>
                </li>
                --><?php /*} */?>
<!--                --><?php //if($userLevel<=3){ ?>
<!--                <li class="nav-item --><?php //echo $activeMenu["lv1"] == "revenueTracking" ? "menu-is-opening menu-open":""; ?><!--">-->
<!--                    <a href="#" class="nav-link --><?php //echo $activeMenu["lv1"] == "revenueTracking" ? "active":""; ?><!--">-->
<!--                        <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M64 144a48 48 0 1 0 0-96 48 48 0 1 0 0 96zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM64 464a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm48-208a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z" fill="--><?php //echo $activeMenu["lv1"] == "revenueTracking" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                        <p>-->
<!--                            Revenue Tracking-->
<!--                            <i class="right fas fa-angle-left"></i>-->
<!--                        </p>-->
<!--                    </a>-->
<!--                    <ul class="nav nav-treeview">-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revRestaurant" class="nav-link --><?php //echo $activeMenu["lv2"] == "restaurant" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M416 0C400 0 288 32 288 176V288c0 35.3 28.7 64 64 64h32V480c0 17.7 14.3 32 32 32s32-14.3 32-32V352 240 32c0-17.7-14.3-32-32-32zM64 16C64 7.8 57.9 1 49.7 .1S34.2 4.6 32.4 12.5L2.1 148.8C.7 155.1 0 161.5 0 167.9c0 45.9 35.1 83.6 80 87.7V480c0 17.7 14.3 32 32 32s32-14.3 32-32V255.6c44.9-4.1 80-41.8 80-87.7c0-6.4-.7-12.8-2.1-19.1L191.6 12.5c-1.8-8-9.3-13.3-17.4-12.4S160 7.8 160 16V150.2c0 5.4-4.4 9.8-9.8 9.8c-5.1 0-9.3-3.9-9.8-9L127.9 14.6C127.2 6.3 120.3 0 112 0s-15.2 6.3-15.9 14.6L83.7 151c-.5 5.1-4.7 9-9.8 9c-5.4 0-9.8-4.4-9.8-9.8V16zm48.3 152l-.3 0-.3 0 .3-.7 .3 .7z" fill="--><?php //echo $activeMenu["lv2"] == "restaurant" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Restaurant</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revMassage" class="nav-link --><?php //echo $activeMenu["lv2"] == "massage" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M183.1 235.3c33.7 20.7 62.9 48.1 85.8 80.5c7 9.9 13.4 20.3 19.1 31c5.7-10.8 12.1-21.1 19.1-31c22.9-32.4 52.1-59.8 85.8-80.5C437.6 207.8 490.1 192 546 192h9.9c11.1 0 20.1 9 20.1 20.1C576 360.1 456.1 480 308.1 480H288 267.9C119.9 480 0 360.1 0 212.1C0 201 9 192 20.1 192H30c55.9 0 108.4 15.8 153.1 43.3zM301.5 37.6c15.7 16.9 61.1 71.8 84.4 164.6c-38 21.6-71.4 50.8-97.9 85.6c-26.5-34.8-59.9-63.9-97.9-85.6c23.2-92.8 68.6-147.7 84.4-164.6C278 33.9 282.9 32 288 32s10 1.9 13.5 5.6z" fill="--><?php //echo $activeMenu["lv2"] == "massage" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Massage shop</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revIHD" class="nav-link --><?php //echo $activeMenu["lv2"] == "IHD" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM112 416a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm368-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" fill="--><?php //echo $activeMenu["lv2"] == "IHD" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>IHD</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revStreams" class="nav-link  --><?php //echo $activeMenu["lv2"] == "streams" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" fill="--><?php //echo $activeMenu["lv2"] == "streams" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Generic Rev Streams</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revStats" class="nav-link --><?php //echo $activeMenu["lv2"] == "stats" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M384 32H512c17.7 0 32 14.3 32 32s-14.3 32-32 32H398.4c-5.2 25.8-22.9 47.1-46.4 57.3V448H512c17.7 0 32 14.3 32 32s-14.3 32-32 32H320 128c-17.7 0-32-14.3-32-32s14.3-32 32-32H288V153.3c-23.5-10.3-41.2-31.6-46.4-57.3H128c-17.7 0-32-14.3-32-32s14.3-32 32-32H256c14.6-19.4 37.8-32 64-32s49.4 12.6 64 32zm55.6 288H584.4L512 195.8 439.6 320zM512 416c-62.9 0-115.2-34-126-78.9c-2.6-11 1-22.3 6.7-32.1l95.2-163.2c5-8.6 14.2-13.8 24.1-13.8s19.1 5.3 24.1 13.8l95.2 163.2c5.7 9.8 9.3 21.1 6.7 32.1C627.2 382 574.9 416 512 416zM126.8 195.8L54.4 320H199.3L126.8 195.8zM.9 337.1c-2.6-11 1-22.3 6.7-32.1l95.2-163.2c5-8.6 14.2-13.8 24.1-13.8s19.1 5.3 24.1 13.8l95.2 163.2c5.7 9.8 9.3 21.1 6.7 32.1C242 382 189.7 416 126.8 416S11.7 382 .9 337.1z" fill="--><?php //echo $activeMenu["lv2"] == "stats" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Stats to Measure</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revSubscription" class="nav-link --><?php //echo $activeMenu["lv2"] == "subscription" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM96 136c0-13.3 10.7-24 24-24c137 0 248 111 248 248c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-110.5-89.5-200-200-200c-13.3 0-24-10.7-24-24zm0 96c0-13.3 10.7-24 24-24c83.9 0 152 68.1 152 152c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-57.4-46.6-104-104-104c-13.3 0-24-10.7-24-24zm0 120a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" fill="--><?php //echo $activeMenu["lv2"] == "subscription" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Subscription Fees</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                        <li class="nav-item pl-2">-->
<!--                            <a href="main.php?p=revCompany" class="nav-link --><?php //echo $activeMenu["lv2"] == "company" ? "active":""; ?><!--">-->
<!--                                <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V240zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V240c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V240zM80 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V112zM272 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16z" fill="--><?php //echo $activeMenu["lv2"] == "company" ? "#FB8500":"#a7acb6"; ?><!--" /></svg>-->
<!--                                <p>Company Stats</p>-->
<!--                            </a>-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </li>-->
<!--                --><?php //} ?>

                <?php ?>
                <li class="nav-item">
                    <a href="main.php?p=l4uPassword" class="nav-link <?php echo $activeMenu["lv1"] == "l4uPassword" ? "active":""; ?>">
                        <i class="nav-icon mr-2 bi bi-key"></i>
                        <p>Password</p>
                    </a>
                </li>
                <?php ?>

                <?php if($userLevel<=2){
                    ?>
                    <li class="nav-item <?php echo $activeMenu["lv1"] == "settings" ? "menu-is-opening menu-open":""; ?>">
                        <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "settings" ? "active":""; ?>">
                            <i class="nav-icon mr-2 bi bi-gear"></i>
                            <p>
                                Settings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item pl-2">
                                <a href="main.php?p=setStaff" class="nav-link <?php echo $activeMenu["lv2"] == "staffs" ? "active":""; ?>">
                                    <i class="nav-icon mr-3 bi bi-person-gear"></i>
                                    <p>Staffs</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } //Admin Menu ?>

                <?php if($userLevel<=4){

                        ?>
                        <li class="nav-item <?php echo $activeMenu["lv1"] == "Form" ? "menu-is-opening menu-open":""; ?>">
                            <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "Form" ? "active":""; ?>">
                                <i class="nav-icon mr-2 bi bi-file-earmark-medical"></i>
                                <p>
                                    Form
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item pl-2">
                                    <a href="modules/signup/index.php" target="_blank" class="nav-link">
                                        <i class="nav-icon mr-3 bi bi-file-earmark-person"></i>
                                        <p>
                                            Signup Form &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                                        </p>
                                    </a>
                                </li>
                                <?php if($teamID != 3){ ?>
                                <li class="nav-item pl-2">
                                    <a href="modules/unsub2/views/index.php?id=123" target="_blank" class="nav-link">
                                        <i class="nav-icon mr-3 bi bi-file-earmark-excel"></i>
                                        <p>
                                        Unsubscribe Form &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item pl-2">
                                    <a href="modules/templates/views/main.php" target="_blank" class="nav-link">
                                        <i class="nav-icon mr-3 bi bi-file-earmark-break"></i>
                                        <p>
                                        Template submissions &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                                        </p>
                                    </a>
                                </li>
                        <?php }// Not Team Sale ?>
                            </ul>
                        </li>

                        <?php

                } //Super Admin Menu ?>

                <?php if($_SESSION['level']<=3){ ?>
                    <li class="nav-item mt-5">
                        <a href="main.php?p=coin" class="nav-link <?php echo $activeMenu["lv2"] == "coin" ? "active":""; ?>">
                            <i class="nav-icon mr-2 bi bi-coin"></i>
                            <p>
                                L4U Coin
                            </p>
                        </a>
                    </li>
                <?php } //Super Admin Menu ?>

                <?php /*if($_SESSION['level']>=4){ */?><!--
                    <li class="nav-item">
                        <a href="https://forms.monday.com/forms/da9ca9feccd4e43b4d264a3b45ba38ed?r=apse2" class="nav-link <?php /*echo $activeMenu["lv2"] == "coin" ? "active":""; */?>">
                            <svg id="Layer_1" class="nav-icon" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 7.45 6.72"><path d="M4.29,3.01s.04-.08.08-.08.08.04.08.09-.04.09-.08.09-.08-.04-.08-.09ZM3.78,1.38s-.04.08-.09.08-.08-.04-.08-.09c.01-.21.1-.39.24-.52.14-.13.33-.21.53-.21s.39.08.53.21c.14.13.23.32.24.52,0,.28-.18.46-.35.65-.16.17-.33.34-.33.59,0,.05-.04.08-.08.08s-.08-.04-.08-.08c0-.32.19-.52.38-.71.15-.16.31-.31.31-.53,0-.16-.08-.3-.19-.4-.11-.1-.25-.17-.42-.17s-.31.06-.42.17c-.11.1-.18.25-.19.41ZM5.7.55c-.34-.34-.81-.55-1.32-.55s-.98.21-1.32.55c-.34.34-.55.81-.55,1.32,0,.15.02.29.05.42.03.14.08.27.14.4,0,.02.01.04,0,.06l-.17.78.79-.09s.04,0,.05.01c.15.09.31.16.48.21.16.05.34.07.52.07.52,0,.98-.21,1.32-.55.34-.34.55-.81.55-1.32s-.21-.98-.55-1.32ZM6.4,4.56c-.09-.15-.21-.28-.36-.38-.15-.1-.32-.17-.51-.19-.14-.02-.28-.01-.41.02-.14.03-.27.08-.38.16-.1.06-.21.11-.31.14-.11.03-.22.05-.34.05h-1.16c-.08.02-.14.07-.18.13-.05.06-.08.14-.08.22s.03.16.08.22c.05.06.11.1.18.13h1.24s.08.04.08.08-.04.08-.08.08h-1.25s-.02,0-.03,0c-.11-.03-.21-.1-.28-.19-.07-.09-.11-.2-.11-.33,0-.02,0-.05,0-.07l-1.94-.58c-.06-.03-.13-.04-.2-.03-.07,0-.13.03-.19.07-.06.04-.1.1-.13.16-.03.06-.04.13-.03.2,0,.07.04.13.08.19.04.05.1.1.17.12l3.64,1.45c.07.03.14.05.21.06.07.01.14.02.22.02h2.08s0-1.73,0-1.73ZM7.45,3.97h-.89v2.75h.89v-2.75Z" fill="<?php /*echo $activeMenu["lv1"] == "coin" ? "#FB8500":"#a7acb6"; */?>"/></svg>
                            <p>
                                Coin Request
                            </p>
                        </a>
                    </li>
                --><?php /*} //Super Admin Menu */?>

                <?php if($userLevel<=4){
                    if($teamID == 3 OR $teamID == 5){
                    ?>
                    <li class="nav-item">
                        <a href="main.php?p=websiteTemplate" class="nav-link <?php echo $activeMenu["lv1"] == "websiteTemplate" ? "active":""; ?>">
                            <i class="nav-icon mr-2 bi bi-browser-chrome"></i>
                            <p>
                                Website Template
                            </p>
                        </a>
                    </li>
                <?php

                    }// Team Sale
                    } //Super Admin Menu */?>

                <li class="nav-item">
                    <a href="https://report.localforyou.com/modules/websiteList/views/websiteList.php#" target="_blank" class="nav-link">
                        <i class="nav-icon mr-2 bi bi-list-check"></i>
                        <p>
                            Website Lists &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="modules/L4UBooking" target="_blank" class="nav-link">
                        <i class="nav-icon mr-2 bi bi-bookmarks"></i>
                        <p>
                            L4U Booking &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item mt-5">
                    <a href="https://localforyou.com/" target="_blank" class="nav-link">
                        <i class="nav-icon mr-2 bi bi-browser-safari"></i>
                        <p>
                            L4U Website &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="https://local-for-you.monday.com/" target="_blank" class="nav-link">
                        <i class="nav-icon mr-2 bi bi-share"></i>
                        <p>
                            Monday &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="modules/mondayReport/views/index.php?id=<?php echo $myID; ?>" target="_blank" class="nav-link text-warning">
                        <i class="nav-icon mr-2 bi bi-exclamation-triangle-fill"></i>
                        <p>
                            Monday Report &nbsp; <i class="bi bi-box-arrow-up-right"></i>
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>