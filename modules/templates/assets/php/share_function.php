<?php
/*
----- function getRandomPic ------
parameters
    $shopType = restaurant, massage
    $picType = folder name in assets/img/sample_image/shopType/ e.g., bg, logo, other, review, service, food
    $fullPath = true, false (default false)

  example 1:
    $getPic = getRandomPic("restaurant", "logo");
    will return >> bg5.webp

  example 2:
    $getPic = getRandomPic("massage", "bg", true);
    will return >> ../assets/img/sample_image/massages/bg/bg5.webp
*/

function getRandomPic($shopType, $picType, $fullPath=false)
{
    $mainDir = "";
    if (strtolower($shopType)=="restaurant"){
        $mainDir = "restaurant";
    }else if(strtolower($shopType)=="massage"){
        $mainDir = "massages";
    }
    $path = '../assets/img/sample_image/'.$mainDir."/".$picType;


    $allFile = array();
    foreach (new DirectoryIterator($path) as $file) {
        if ($file->isFile()) {
            $allFile[] = $file->getFilename();
        }//if
    }//foreach

    $rand_keys = array_rand($allFile,1);

    if ($fullPath){
        return $path."/".$allFile[$rand_keys];
    }else{
        return($allFile[$rand_keys]);
    }
}//getRandomPic

?>