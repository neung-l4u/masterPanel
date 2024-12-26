<?php
$menu = (!empty($_REQUEST['m'])) ? trim($_REQUEST['m']) : 'home'; //ดูว่าเลือกเมนูไหนอยู่
switch ($menu) {
    case 'project':
        $title = 'Project';
        $view = 'project.php';
        $menu = 'project';
        break;
    case 'detail':
        $title = 'Project Detail';
        $view = 'project_detail.php';
        $menu = 'detail';
        break;
    case 'res1':
        $title = 'Template Restaurant 1';
        $view = 'res1.php';
        $menu = 'project';
        break;
    case 'res2':
        $title = 'Template Restaurant 2';
        $view = 'res2.php';
        $menu = 'project';
        break;
    case 'res3':
        $title = 'Template Restaurant 3';
        $view = 'res3.php';
        $menu = 'project';
        break;
    case 'mas1':
        $title = 'Template Massage 1';
        $view = 'mas1.php';
        $menu = 'project';
        break;
    case 'mas2':
        $title = 'Template Massage 2';
        $view = 'mas2.php';
        $menu = 'project';
        break;
    case 'mas3':
        $title = 'Template Massage 3';
        $view = 'mas3.php';
        $menu = 'project';
        break;
    default:
        $title = 'Home';
        $view = 'home.php';
        $menu = 'home';
        break;
}