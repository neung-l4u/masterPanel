<?php

if(isset($_FILES['file']['name'])){

    $projectID = $_POST['projectId'];
    $prefix = isset($_POST['prefixId']) ? $_POST['prefixId'] : 'Section';

    /* Getting file name */
    $filename = $_FILES['file']['name'];
    $oldName = $filename;

    // echo "Project ID: " . $projectID . "<br>";
    // echo "Form ID: " . $formId . "<br>";
    // echo "Prefix: " . $prefix . "<br>";
    // echo "Old name: " . $oldName . "<br>";
    
    $filename = str_replace(' ', '_', $filename);

    /* Location */
    $location = "upload/".$filename;
    $imageExt = pathinfo($location,PATHINFO_EXTENSION);
    $imageFileType = strtolower($imageExt);

    /* Valid extensions */
    $valid_extensions = array("jpg","jpeg","png","svg");

    $response = 0;
    /* Check a file extension */
    if(in_array(strtolower($imageFileType), $valid_extensions)) {

        $subfolder = "upload/" . $projectID;
        if (!is_dir($subfolder)) {
            mkdir($subfolder, 0777, true);
        }

        /* Upload file */
        if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
            /* Rename file */

            $currentDate = date("ymd_His");
            $newName = $subfolder . "/" . $prefix . "_" . $currentDate . "_" . $projectID . "." . $imageExt;
            //echo "New name: ";

            if(rename($location, $newName)) {
                $location = $newName;
            }
            $response = $location;
        }
    } else {
        echo "<script>alert('นามสกุลไฟล์ไม่ถูกต้อง กรุณาเลือกไฟล์ JPG, JPEG, PNG, หรือ SVG');</script>";
    }

    echo $response;
    exit;
}

echo 0;

?>