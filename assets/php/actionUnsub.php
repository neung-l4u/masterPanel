<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";


$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "No Action";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "9999";

 $row = $db->query('SELECT * FROM `Cancellation` WHERE id = ?;',$params ["id"])->fetchArray();
 $shopname = !empty($row["shopname"]) ? $row["shopname"] : "-";
 $feedback = !empty($row["feedback"]) ? $row["feedback"] : "-";
 $fullname = !empty($row["firstname"]." ".$row["lastname"]) ? $row["firstname"]." ".$row["lastname"] : "-";
 $email = !empty($row["email"]) ? $row["email"] : "-";
 $mobile = !empty($row["mobile"]) ? $row["mobile"] : "-";
 $address = !empty($row["address"]." ,".($row["city"])." ,".($row["state"])." ,".($row["zip"])." ,".($row["county"])) ? $row["address"]." ,".($row["city"])." ,".($row["state"])." ,".($row["zip"])." ,".($row["county"]) : "-";

?>

    <table class="table table-striped">
        <tbody>
            <tr>
                <th scope="row">Shop Name</th>
                <td><?php echo $shopname;?></td>
            </tr>
            <tr>
                <th scope="row">Address</th>
                <td><?php echo $address;?></td>
            </tr>
            <tr>
                <th scope="row">Name</th>
                <td><?php echo $fullname;?></td>
            </tr>
            <tr>
                <th scope="row">Email</th>
                <td><?php echo $email;?></td>
            </tr>
            <tr>
                <th scope="row">Mobile</th>
                <td><?php echo $mobile;?></td>
            </tr>
            <tr>
                <th scope="row">FeedBack</th>
                <td><?php echo $feedback;?></td>
            </tr>
        </tbody>
    </table>


