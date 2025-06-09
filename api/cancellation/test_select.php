<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
?>
<style>
    table.users {
       border: 1px solid #999999;
        width: 50%;
        margin: auto;
    }
    table.users tr td{
        border: 1px solid #CCCCCC;
    }
</style>

<table class="users">
    <caption>Users</caption>
    <tr>
        <th width="50">#</th>
        <th>Name</th>
        <th width="150">type</th>
    </tr>
    <tr>
        <td>1</td>
        <td>John</td>
        <td>User</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Jane</td>
        <td>User</td>
    </tr>
    <tr>
        <td>3</td>
        <td>Tom</td>
        <td>Admin</td>
    </tr>
</table>
