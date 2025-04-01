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
                <a href="chkLogin.php?act=logout" class="d-block"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="main.php?p=home" class="nav-link <?php echo $activeMenu["lv1"] == "home" ? "active":""; ?>">
<!--                        <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"  fill="--><?php //echo $activeMenu["lv1"] == "myDesk" ? "#FB8500":"#a7acb6"; ?><!--"/></svg>-->
                        <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" fill="<?php echo $activeMenu["lv1"] == "myDesk" ? "#FB8500":"#a7acb6"; ?>"/></svg>
                        <p>Home</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="main.php?p=myProfile" class="nav-link <?php echo $activeMenu["lv1"] == "myProfile" ? "active":""; ?>">
<!--                        <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512"><path d="M96 0C60.7 0 32 28.7 32 64l0 384c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-384c0-35.3-28.7-64-64-64L96 0zM208 288l64 0c44.2 0 80 35.8 80 80c0 8.8-7.2 16-16 16l-192 0c-8.8 0-16-7.2-16-16c0-44.2 35.8-80 80-80zm-32-96a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM512 80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64zM496 192c-8.8 0-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64c0-8.8-7.2-16-16-16zm16 144c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64z"  fill="--><?php //echo $activeMenu["lv1"] == "myProfile" ? "#FB8500":"#a7acb6"; ?><!--"/></svg>-->
                        <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"  fill="<?php echo $activeMenu["lv1"] == "myProfile" ? "#FB8500":"#a7acb6"; ?>"/></svg>
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
                        <svg class="nav-icon mr-2"  height="1em"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17l0 80c0 13.3 10.7 24 24 24l80 0c13.3 0 24-10.7 24-24l0-40 40 0c13.3 0 24-10.7 24-24l0-40 40 0c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z" fill="<?php echo $activeMenu["lv1"] == "l4uPassword" ? "#FB8500":"#a7acb6"; ?>" /></svg>
                        <p>Password</p>
                    </a>
                </li>
                <?php ?>

                <?php if($userLevel<=4){
                    ?>
                    <li class="nav-item <?php echo $activeMenu["lv1"] == "settings" ? "menu-is-opening menu-open":""; ?>">
                        <a href="#" class="nav-link <?php echo $activeMenu["lv1"] == "settings" ? "active":""; ?>">
                            <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" fill="<?php echo $activeMenu["lv1"] == "settings" ? "#FB8500":"#a7acb6"; ?>" /></svg>
                            <p>
                                Settings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item pl-2">
                                <a href="main.php?p=setStaff" class="nav-link <?php echo $activeMenu["lv2"] == "staffs" ? "active":""; ?>">
                                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="<?php echo $activeMenu["lv2"] == "staffs" ? "#FB8500":"#a7acb6"; ?>" /></svg>
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
                                <svg class="nav-icon mr-2" id="Layer_1" data-name="Layer 1" height="1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68.94 86.65"><path d="M55.01,26.09H13.93c-1.5,0-2.71,1.21-2.71,2.71s1.21,2.71,2.71,2.71h41.09c1.5,0,2.71-1.21,2.71-2.71s-1.21-2.71-2.71-2.71ZM55.01,40.62H13.93c-1.5,0-2.71,1.21-2.71,2.71s1.21,2.71,2.71,2.71h41.09c1.5,0,2.71-1.21,2.71-2.71s-1.21-2.71-2.71-2.71ZM37.72,55.14H13.93c-1.5,0-2.71,1.21-2.71,2.71s1.21,2.71,2.71,2.71h23.8c1.5,0,2.71-1.21,2.71-2.71s-1.21-2.71-2.71-2.71ZM63.52,65.75l-15.37,15.41v-13.78c.03-.89.75-1.61,1.64-1.63h13.73ZM61.85,5.46c.89.02,1.62.74,1.64,1.63l.03,53.2h-13.69c-3.89,0-7.05,3.12-7.13,7v13.88l-35.62-.03c-.89-.02-1.61-.74-1.64-1.63V7.1c.03-.89.75-1.61,1.64-1.63h54.76ZM61.81,0h0S7.13,0,7.13,0C3.24,0,.07,3.13,0,7.01v72.6c.07,3.88,3.23,7,7.12,7h0s0,0,0,0l42.07.04c.69,0,1.38-.27,1.91-.79l17.03-17.08c.56-.57.83-1.32.79-2.06V7.01C68.93,3.13,65.7,0,61.81,0h0Z" fill="<?php echo $activeMenu["lv1"] == "Form" ? "#FB8500":"#a7acb6"; ?>"/></svg>
                                <p>
                                    Form
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item pl-2">
                                    <a href="modules/signup/index.php" target="_blank" class="nav-link">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V285.7l-86.8 86.8c-10.3 10.3-17.5 23.1-21 37.2l-18.7 74.9c-2.3 9.2-1.8 18.8 1.3 27.5H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128zM549.8 235.7l14.4 14.4c15.6 15.6 15.6 40.9 0 56.6l-29.4 29.4-71-71 29.4-29.4c15.6-15.6 40.9-15.6 56.6 0zM311.9 417L441.1 287.8l71 71L382.9 487.9c-4.1 4.1-9.2 7-14.9 8.4l-60.1 15c-5.5 1.4-11.2-.2-15.2-4.2s-5.6-9.7-4.2-15.2l15-60.1c1.4-5.6 4.3-10.8 8.4-14.9z" fill="#A4CAE9" /></svg>
                                        <p>
                                            Sign up form
                                        </p>
                                    </a>
                                </li>
                                <?php if($teamID != 3){ ?>
                                <li class="nav-item pl-2">
                                    <a href="modules/cancellation/views/index.php?token=123" target="_blank" class="nav-link <?php echo $activeMenu["lv2"] == "unsubscribe" ? "active":""; ?>">
                                        <svg class="nav-icon" height="1em" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 76.41 95.13"><path d="M.63,17.92L17.97.59c.39-.39.9-.59,1.41-.59h41.63c2.39,0,4.57.98,6.14,2.55,1.23,1.23,2.1,2.83,2.42,4.61,1.65.36,3.12,1.2,4.28,2.35,1.57,1.57,2.55,3.75,2.55,6.14v70.77c0,2.39-.98,4.57-2.55,6.14-1.57,1.57-3.75,2.55-6.14,2.55H15.4c-2.39,0-4.57-.98-6.14-2.55-1.23-1.23-2.1-2.83-2.42-4.61-1.65-.36-3.12-1.2-4.28-2.35C.99,84.04,0,81.86,0,79.47v-2.96h4v2.96c0,1.29.53,2.46,1.38,3.32.85.85,2.03,1.38,3.32,1.38h52.31c1.29,0,2.46-.53,3.32-1.38.85-.85,1.38-2.03,1.38-3.32V8.7c0-1.29-.53-2.46-1.38-3.32-.85-.85-2.03-1.38-3.32-1.38H21.38v8.68c0,2.39-.98,4.57-2.55,6.14-1.57,1.57-3.75,2.55-6.14,2.55H4v42.42H0V19.39c0-.57.24-1.09.63-1.46h0ZM0,72.21v-4.11h4v4.11H0ZM69.71,11.42v68.05c0,2.39-.98,4.57-2.55,6.14-1.57,1.57-3.75,2.55-6.14,2.55H11.04c.24.6.6,1.13,1.05,1.58.85.85,2.03,1.38,3.32,1.38h52.31c1.29,0,2.46-.53,3.32-1.38.85-.85,1.38-2.03,1.38-3.32V15.67c0-1.29-.53-2.46-1.38-3.32-.38-.38-.82-.7-1.32-.93h0ZM6.83,17.39h5.85c1.29,0,2.46-.53,3.32-1.38.85-.85,1.38-2.03,1.38-3.32v-5.85l-10.55,10.55h0Z" fill="#A4CAE9"/><polygon points="0 63.8 0 77.71 4 77.71 4 61.77 0 63.8" fill="#A4CAE9"/><path d="M13.86,28.05c2.34-2.34,6.14-2.34,8.48,0l12.72,12.72,12.72-12.72c2.34-2.34,6.14-2.34,8.48,0s2.34,6.14,0,8.48l-12.72,12.72,12.72,12.72c2.34,2.34,2.34,6.14,0,8.48s-6.14,2.34-8.48,0l-12.72-12.72-12.72,12.72c-2.34,2.34-6.14,2.34-8.48,0s-2.34-6.14,0-8.48l12.72-12.72-12.72-12.72c-2.34-2.34-2.34-6.14,0-8.48Z" fill="#A4CAE9"/></svg>
                                        <p>
                                        Unsubscribe Form
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item pl-2">
                                    <a href="modules/templates/views/main.php" target="_blank" class="nav-link <?php echo $activeMenu["lv2"] == "unsubscribe" ? "active":""; ?>">
                                        <svg class="nav-icon" id="Layer_1" xmlns="http://www.w3.org/2000/svg" height="1em" version="1.1" viewBox="0 0 110 120.6"><path d="M92,13.8H26.4c-4.9,0-8.9,3.7-9.4,8.4-4.8.5-8.5,4.5-8.5,9.4v65.6c0,5.2,4.3,9.5,9.5,9.5h65.5c2.6,0,5-1,6.8-2.9,1.5-1.5,2.4-3.4,2.6-5.5,2.2-.2,4.2-1.2,5.8-2.8,1.8-1.8,2.7-4.2,2.7-6.7V23.3c0-5.2-4.3-9.5-9.5-9.5h0ZM26.4,16.8h65.5c3.6,0,6.5,2.9,6.5,6.5v3.6H19.9v-3.6c0-3.6,2.9-6.5,6.5-6.5h0ZM98.5,44.3H19.9v-14.4h78.6v14.4ZM26.4,95.4c-3.6,0-6.5-2.9-6.5-6.5v-41.6h27.6v48.1h-21ZM83.6,103.8H18c-3.6,0-6.5-2.9-6.5-6.5V31.6c0-3.3,2.3-5.9,5.4-6.4v63.7c0,5.2,4.3,9.5,9.5,9.5h63.5c-.2,1.2-.8,2.4-1.7,3.3-1.3,1.3-2.9,2-4.7,2h0ZM96.7,93.4c-1.2,1.3-2.9,2-4.7,2h-41.5v-48.1h48v41.6c0,1.7-.6,3.3-1.8,4.5h0Z" fill="#A4CAE9"/><path d="M26.4,22.5c0,.1.1.2.1.3.1.1.1.2.2.2.3.3.7.4,1.1.4h.3c.1,0,.2-.1.3-.1s.2-.1.3-.1c.1-.1.2-.1.2-.2q.1-.1.2-.2c0-.1.1-.2.1-.3s.1-.2.1-.3v-.6c0-.1-.1-.2-.1-.3s-.1-.2-.1-.3c-.1-.1-.1-.2-.2-.2-.5-.6-1.6-.6-2.1,0-.1.1-.1.2-.2.2,0,.1-.1.2-.1.3s-.1.2-.1.3v.6c0,0,0,.2,0,.3Z"/><path d="M36.9,23.4h.1c.8,0,1.5-.7,1.5-1.5s-.7-1.5-1.5-1.5h-.1c-.8,0-1.5.7-1.5,1.5s.7,1.5,1.5,1.5Z" fill="#A4CAE9"/><path d="M46,23.4h.3c.1,0,.2-.1.3-.1s.2-.1.3-.1c.1-.1.2-.1.2-.2.3-.3.4-.7.4-1.1s-.2-.8-.4-1.1q-.1-.1-.2-.2c-.1,0-.2-.1-.3-.1s-.2-.1-.3-.1c-.5-.1-1,.1-1.4.4-.3.3-.4.7-.4,1.1s.2.8.4,1.1c.3.2.7.4,1.1.4h0Z" fill="#A4CAE9"/></svg>
                                        <p>
                                        Template submissions
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
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"  x="0px" y="0px" viewBox="0 0 100 125" ><g><path d="M50,5C25.15,5,5,25.15,5,50s20.15,45,45,45s45-20.15,45-45S74.85,5,50,5z M63.82,71.62c0,0.67-0.54,1.21-1.21,1.21H35.39   c-0.67,0-1.21-0.54-1.21-1.21v-3.27c0-0.46,0.26-0.88,0.67-1.09c4.19-2.09,6.79-6.06,6.79-10.36c0-1.15-0.03-2.16-0.18-3.36h-5.61   c-0.67,0-1.21-0.55-1.21-1.22V48.2c0-0.67,0.54-1.21,1.21-1.21h4.66C40.23,45.11,40,43.14,40,41.14c0-8.23,5.67-13.97,13.77-13.97   c4.21,0,6.68,1.14,7.77,1.82c0.46,0.28,0.68,0.84,0.53,1.36l-1.24,4.32c-0.1,0.35-0.36,0.64-0.71,0.78   c-0.34,0.14-0.72,0.12-1.05-0.06c-0.77-0.43-2.5-1.15-5.3-1.15c-3.92,0-5.91,2.34-5.91,6.96c0,2.33,0.24,4.16,0.52,5.79h8.34   c0.67,0,1.21,0.54,1.21,1.21v4.12c0,0.67-0.54,1.22-1.21,1.22h-7.53c0.13,2.11,0.14,4.31-0.39,6.4c-0.53,2.1-1.51,4.01-2.91,5.69   h16.72c0.67,0,1.21,0.54,1.21,1.21V71.62z" fill="<?php echo $activeMenu["lv1"] == "coin" ? "#FB8500":"#a7acb6"; ?>" /></g></svg>
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
                            <svg id="Layer_1" class="nav-icon"  data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 83.35 83.36"><path d="M41.68,0h0s0,0,0,0C18.66,0,0,18.66,0,41.68s18.66,41.68,41.68,41.68,41.68-18.66,41.68-41.68S64.69,0,41.68,0h0ZM21.96,9.44c-.79,1.49-1.51,3.06-2.17,4.71-1-.31-1.96-.64-2.9-1,1.58-1.38,3.27-2.62,5.07-3.71h0ZM61.39,9.44c1.79,1.09,3.49,2.34,5.07,3.71h0c-.94.35-1.9.69-2.9,1-.67-1.65-1.39-3.23-2.18-4.71h0ZM43.62,3.94h0c3.89.2,7.63.98,11.13,2.27h0c1.9,2.49,3.62,5.52,5.07,9h0c-4.89,1.21-10.34,1.96-16.19,2.09V3.94ZM39.72,3.94v13.36s.01,0,.01,0c-5.85-.14-11.29-.88-16.19-2.1h0c1.45-3.48,3.16-6.5,5.06-8.99h0c3.5-1.3,7.22-2.08,11.11-2.28h0ZM13.86,16.11c1.48.61,3.02,1.16,4.61,1.67h0c-2.18,6.59-3.46,14.12-3.64,21.96H3.94c.46-9.11,4.14-17.34,9.91-23.62h0ZM22.18,18.84h0s0,0,0,0c5.46,1.39,11.41,2.21,17.56,2.36h0v18.54h-21.02c.18-7.8,1.45-14.88,3.46-20.9h0ZM61.17,18.84h0s0,0,0,0c2.02,6.02,3.28,13.1,3.46,20.9h-21.02v-18.53h0c6.14-.15,12.09-.97,17.56-2.36h0ZM69.49,16.11h0c5.78,6.28,9.46,14.53,9.92,23.62h0s-10.89,0-10.89,0c-.18-7.84-1.47-15.36-3.64-21.95h0c1.59-.51,3.13-1.07,4.61-1.67h0ZM39.73,43.63v18.54h0c-6.14.14-12.09.96-17.55,2.36h0c-2.02-6.02-3.28-13.09-3.46-20.89h0s21.02,0,21.02,0ZM64.63,43.62h0s0,0,0,0h0c-.18,7.79-1.45,14.87-3.46,20.89h0c-5.46-1.39-11.41-2.21-17.55-2.36h0v-18.53h21.01ZM14.83,43.63h0c.18,7.84,1.46,15.36,3.64,21.95h0c-1.59.51-3.13,1.07-4.61,1.67-5.78-6.29-9.45-14.53-9.91-23.62h10.89ZM79.41,43.62h0s0,0,0,0h0c-.46,9.09-4.14,17.34-9.92,23.62h0c-1.48-.61-3.02-1.16-4.61-1.67h0c2.17-6.59,3.46-14.11,3.64-21.95h0,10.89ZM19.79,69.2c.67,1.65,1.39,3.23,2.18,4.72h0c-1.8-1.1-3.5-2.35-5.08-3.72h0c.94-.36,1.9-.69,2.89-1h0ZM63.55,69.2h0c1,.31,1.96.65,2.9,1h0c-1.59,1.38-3.29,2.62-5.08,3.72h0c.79-1.5,1.51-3.07,2.18-4.73h0ZM43.62,66.05h0c5.85.13,11.3.88,16.2,2.09h0c-1.45,3.48-3.17,6.51-5.07,9h0c-3.49,1.29-7.23,2.07-11.12,2.27v-13.37ZM39.72,66.05h0s0,0,0,0v13.36c-3.89-.2-7.62-.98-11.12-2.27h0c-1.9-2.49-3.62-5.52-5.07-8.99h0c4.9-1.22,10.34-1.97,16.19-2.11h0Z" fill="<?php echo $activeMenu["lv1"] == "websiteTemplate" ? "#FB8500":"#a7acb6"; ?>"/></svg>
                            <p>
                                Website Template
                            </p>
                        </a>
                    </li>
                <?php

                    }// Team Sale
                    } //Super Admin Menu */?>

                <li class="nav-item mt-5">
                    <a href="https://localforyou.com/" target="_blank" class="nav-link">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M579.8 267.7c56.5-56.5 56.5-148 0-204.5c-50-50-128.8-56.5-186.3-15.4l-1.6 1.1c-14.4 10.3-17.7 30.3-7.4 44.6s30.3 17.7 44.6 7.4l1.6-1.1c32.1-22.9 76-19.3 103.8 8.6c31.5 31.5 31.5 82.5 0 114L422.3 334.8c-31.5 31.5-82.5 31.5-114 0c-27.9-27.9-31.5-71.8-8.6-103.8l1.1-1.6c10.3-14.4 6.9-34.4-7.4-44.6s-34.4-6.9-44.6 7.4l-1.1 1.6C206.5 251.2 213 330 263 380c56.5 56.5 148 56.5 204.5 0L579.8 267.7zM60.2 244.3c-56.5 56.5-56.5 148 0 204.5c50 50 128.8 56.5 186.3 15.4l1.6-1.1c14.4-10.3 17.7-30.3 7.4-44.6s-30.3-17.7-44.6-7.4l-1.6 1.1c-32.1 22.9-76 19.3-103.8-8.6C74 372 74 321 105.5 289.5L217.7 177.2c31.5-31.5 82.5-31.5 114 0c27.9 27.9 31.5 71.8 8.6 103.9l-1.1 1.6c-10.3 14.4-6.9 34.4 7.4 44.6s34.4 6.9 44.6-7.4l1.1-1.6C433.5 260.8 427 182 377 132c-56.5-56.5-148-56.5-204.5 0L60.2 244.3z" fill="#A4CAE9"/></svg>
                        <p>
                            L4U Website
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="https://local-for-you.monday.com/" target="_blank" class="nav-link">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48V96H384V80c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H432c-26.5 0-48-21.5-48-48V160H192v16c0 1.7-.1 3.4-.3 5L272 288h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H272c-26.5 0-48-21.5-48-48V336c0-1.7 .1-3.4 .3-5L144 224H48c-26.5 0-48-21.5-48-48V80z" fill="#A4CAE9" /></svg>
                        <p>
                            Monday
                        </p>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="modules/mondayReport/views/index.php?id=<?php echo $myID; ?>" target="_blank" class="nav-link bg-danger">
                    <svg class="nav-icon pr-1" xmlns="http://www.w3.org/2000/svg" height="1em"  viewBox="0 0 512 512"><path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480L40 480c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z" fill="#ffffff"/></svg>
                        <span>
                            Monday Report
                        </span>
                    </a>
                </li>


            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>