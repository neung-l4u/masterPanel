<?php

$staffName = isset($_POST['staffName']) ? $_POST['staffName'] : 'Noname';

$folderPath = "../upload/";
if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

if (isset($_FILES["file"])) {
    $filename = $_FILES['file']['name'];
    $fileExt = pathinfo($filename,PATHINFO_EXTENSION);
    $fileType = strtolower($fileExt);

    $staffName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $staffName);
    $newName = time() . "_" . $staffName . "." . $fileType;

    $location = $folderPath . $newName;
    $serverPath = "https://report.localforyou.com/modules/checkin/upload/" . $newName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $location)) {
        echo $serverPath;
    } else {
        echo "0";
    }
} else {
    echo "0";
}
?>