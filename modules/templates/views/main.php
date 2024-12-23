<?php
session_start();
$menu = (!empty($_REQUEST['m'])) ? trim($_REQUEST['m']) : 'home'; //ดูว่าเลือกเมนูไหนอยู่
switch ($menu) {
    case 'home':
        $title = 'Home';
        $view = 'home.php';
        $menu = 'home';
        break;
    case 'project':
        $title = 'Project';
        $view = 'project.php';
        $menu = 'project';
        break;
    case 'profile':
        $title = 'ProFile';
        $view = 'profile.php';
        $menu = 'profile';
        break;
    case 'detail':
        $title = 'Project Detail';
        $view = 'project_detail.php';
        $menu = 'detail';
        break;
    default:
        $title = 'Home';
        $view = 'home.php';
        $menu = 'home';
    case 'detail-res1':
        $title = 'Template Restaurant 1';
        $view = 'res1.php';
        $menu = 'res1';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php include ('topNav.php');?>
<div class="container pt-5">
    <div class="row">
        <div class="col">
            <h4 class="text-secondary">Page : <?php echo $title; ?></h4>
        </div>
    </div>
    <?php include($view);?>
</div>

<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
<script src="../controllers/main.js"></script>

</body>
</html>
