test_select.php<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
?>
<style>
    table.users {
       border: 1px solid #DDDDDD;
        width: 50%;
        margin: auto;
        border-collapse: collapse;
        border-radius: 10px;
    }
    table.users tr th, table.users tr td{
        border: 1px solid #EEEEEE;
        padding: 5px;
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
