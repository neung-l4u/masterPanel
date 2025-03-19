<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB_server.php";


$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "No Action";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "9999";

 $row = $db->query('SELECT * FROM `Cancellation` WHERE id = ?;',$params ["id"])->fetchArray();

?>



    <table class="table table-striped">
        <tbody>
            <tr>
                <th scope="row">Shop Name</th>
                <td><?php echo $row["shopname"];?></td>
            </tr>
            <tr>
                <th scope="row">Address</th>
                <td><?php echo $row["address"].", ".$row["city"].", ".$row["state"].", ".$row["zip"].", ".$row["county"];?></td>
            </tr>
            <tr>
                <th scope="row">Name</th>
                <td><?php echo $row["firstname"]." ".$row["lastdate"];?></td>
            </tr>
            <tr>
                <th scope="row">Email</th>
                <td><?php echo $row["email"];?></td>
            </tr>
            <tr>
                <th scope="row">Moblile</th>
                <td><?php echo $row["mobile"];?></td>
            </tr>
            <tr>
                <th scope="row">FeedBack</th>
                <td><?php echo $row["feedback"];?></td>
            </tr>
        </tbody>
    </table>


