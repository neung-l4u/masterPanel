<?php
require_once ("../assets/db/db.php");
require_once ("../assets/db/initDB.php");
require_once ("../assets/php/share_function.php");

if (isset($_FILES['file']['name'])) {

    $projectID = $_POST['projectId'];
    $prefix = isset($_POST['prefixId']) ? $_POST['prefixId'] : 'Section';

    if (!empty($projectID)) {
        $row = $db->query('SELECT projectName FROM `tb_project` WHERE projectID = ?;', $projectID)->fetchArray();
        $projectName = sanitizeFolderName($row["projectName"]);
    } else {
        $projectName = 'Noname';
        $projectID = 0;
    }

    $filename = $_FILES['file']['name'];
    $oldName = $filename;
    $filename = str_replace(' ', '_', $filename);

    $location = "../upload/" . $filename;
    $imageExt = pathinfo($location, PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageExt);

    $valid_extensions = array("jpg","jpeg","png","svg","webp","heic");

    $response = "0";
    if (in_array($imageFileType, $valid_extensions)) {

        $subfolder = "../upload/" . $projectID . "-" . $projectName;
        if (!is_dir($subfolder)) {
            mkdir($subfolder, 0777, true);
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $currentDate = date("ymdHis");
            $newName = $subfolder . "/" . $prefix . "_" . $projectID . "_" . $currentDate . "." . $imageExt;

            if (rename($location, $newName)) {
                $location = $newName;
            }
            $response = $location;
        }
    } else {
        // ❌ ก่อนหน้านี้ echo ตรงนี้แล้วไป echo ซ้ำข้างล่าง
        $response = "Invalid file extension.";
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo $response;
    exit;
}

echo "0";
