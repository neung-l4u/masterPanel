<?php

$shopName = isset($_POST['shopName']) ? $_POST['shopName'] : 'Noname';

$shopName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $shopName);
$folderName = date("Ymd") . "_" . $shopName;
$folderPath = "../upload/"."$folderName"."/";
if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

if (isset($_FILES["file"])) {
    $filename = $_FILES['file']['name'];
    $fileExt = pathinfo($filename,PATHINFO_EXTENSION);
    $fileType = strtolower($fileExt);

    $newName = time() . "_" . $shopName . "." . $fileType;

    $location = $folderPath . $newName;
    $serverPath = "/upload/" . $folderName . "/" . $newName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $location)) {
        echo $serverPath;
    } else {
        echo "0";
    }
} else {
    echo "0";
}
?>