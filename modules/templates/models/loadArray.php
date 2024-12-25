<?php
global $db;
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$params = array();

$mode = $_POST['mode'];
$token = $_POST['token'];
$projectID = $_POST['projectID'];


if ($mode == "loadArray"){
    $row = $db->query(
        'SELECT * 
        FROM tb_project
        WHERE projectID = ?;', $projectID)->fetchArray();

    $params["domainHave"] = $row["domainHave"];
    $params["hostingHave"] = $row["hostingHave"];
    $params["gloriaHave"] = $row["gloriaHave"];
    $params["orderOther"] = $row["orderOther"];
    $params["amelia"] = $row["amelia"];
    $params["voucher"] = $row["voucher"];
    $params["bookOther"] = $row["bookOther"];
    $params["needEmail"] = $row["needEmail"];

    $params['result'] = "found ".count($row)." data";
}

echo json_encode($params);