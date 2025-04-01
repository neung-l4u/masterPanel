<?php
session_start();
global $title, $view;
require_once '../assets/php/pageNavigate.php';
?>
<!DOCTYPE html>
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
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">
    <link rel="stylesheet" href="../assets/css/main.css">

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
    <script src="../controllers/main.js"></script>
</head>
<body>
    <?php include ('topNav.php');?>
    <div class="container pt-5">
        <div class="row">
            <div class="col">
                <h6 class="text-danger-emphasis">Current page : <?php echo $title; ?></h6>
            </div>
        </div>
        <?php include($view);?>
    </div>
</body>
</html>
