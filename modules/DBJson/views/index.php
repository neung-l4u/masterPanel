<?php
global $db;

include '../assets/db/db.php';
include "../assets/db/initDB.php";
$param['act'] = 'read';


$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="content-type" content="no-cache">
    <meta http-equiv="refresh" content="no-cache">
    <meta http-equiv="refresh" content="0">
    <title>Test DB Json</title>
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.v4.6.2.css">

    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
</head>
<body>
<div class="container pt-5">
    <?php
    if ( $param['act'] == 'add' ) {

        $user1 = array("firstname" => "Sorasak", "lastname" => "Thanomsap", "position" => "Manager");
        $user2 = array("firstname" => "Peeraphat", "lastname" => "Milimoncon", "position" => "Developer");
        $user3 = array("firstname" => "Netipong", "lastname" => "Choosri", "position" => "Developer");
        $insert = $db->query('INSERT INTO `testJson` (`data`) values (?),(?),(?)', json_encode($user1), json_encode($user2), json_encode($user3));

        $return['result'] = 'success'; ?>
        <div class="row">
            <div class="col">
                <?php  echo json_encode($return); ?>
            </div>
        </div>
    <?php
    }elseif ( $param['act'] == 'read' ) {
        $users = $db->query('SELECT * FROM `testJson`')->fetchAll();
        $row = array(); ?>

    <div class="card" style="width: 25rem;">
        <ul class="list-group list-group-flush">

        <?php
        foreach ($users as $user) {
            $valueObject = json_decode($user['data']);
            $valueArray = json_decode($user['data'], true); ?>

            <li class="list-group-item">
                <?php
                echo '<b>'.$valueObject->position.'</b> : ';
                echo $valueObject->firstname . ' ' . $valueArray["lastname"];
                ?>
            </li>
    <?php }//foreach ?>
        </ul>
    </div>
    <?php }//else ?>
</div>
</body>
</html>