<?php
global $menu;
?>
<link rel="stylesheet" href="../assets/css/topNav.css">
<link rel="stylesheet" href="../../../plugins/fontawesome-free/css/all.min.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="main.php">
            <img src="../assets/img/logo-login2.png" alt="logo"/>
            Template submission system
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                    <li class="nav-item <?php echo (empty($menu) or ($menu == 'home')) ? 'active':''; ?>">
                        <a class="nav-link" href="main.php?m=home">Home</a>
                    </li>
                    <li class="nav-item <?php echo (($menu == 'project') or ($menu == 'detail')) ? 'active':''; ?>">
                        <a class="nav-link" href="main.php?m=project">Projects</a>
                    </li>
                </ul>
            <form class="d-flex" role="search">
                <a class="nav-link text-white mr-3" href="../assets/API/chkLogin.php?act=logout"><i class="fas fa-sign-out-alt"></i></a>
            </form>
        </div>
    </div>
</nav>