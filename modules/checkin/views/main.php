<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
require_once '../assets/php/pageNavigate.php';
global $db, $showPage;
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="../assets/css/main.css?v=1.0.0" rel="stylesheet">
    <style>
        .breadcrumb li a{
            text-decoration: none;
        }
        .active{

        }
        ul.navbar-nav li.nav-item a.active{

            text-decoration: underline !important;
            text-underline-offset: 5px;
        }
        footer{
            width: 100%;
            position: fixed;
            bottom: 1em;
            color: gray;
        }
    </style>
    <script src="../assets/js/main.js?v=1.0.0"></script>
    <title>L4U: Check-in/out working time</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="main.php">L4U: working time</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a id="menuHome" class="nav-link <?php echo ($showPage=="home.php")?'active':''; ?>" aria-current="page" href="main.php?p=home">Home</a>
                </li>
                <li class="nav-item">
                    <a id="menuTime" class="nav-link <?php echo ($showPage=="timestamp.php")?'active':''; ?>" href="main.php?p=time">Time stamp</a>
                </li>
                <li class="nav-item">
                    <a id="menuTime" class="nav-link <?php echo ($showPage=="timeOff.php")?'active':''; ?>" href="main.php?p=timeOff">Time Off</a>
                </li>
            </ul>
            <!--<span class="navbar-text">
                logout
            </span>-->
        </div>
    </div>
</nav>

<div class="container py-5">
    <?php include $showPage;?>
</div><!-- container-->
<?php include "footer.php";?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

<script src="../controllers/main.js?v=1.0.0"></script>

</body>
</html>