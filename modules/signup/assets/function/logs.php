<?php
date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$fileName = 'Logs#'.$date.'.txt';
$filePath = '../../logs/'.$fileName;

$result["result"] = "";
$result["msg"] = "";

$json = $_REQUEST["allData"];

$message = "----- $fileName -> $timestamp -----

";
$message .= json_encode($json, JSON_PRETTY_PRINT);
$message .= "

----- END -----";

if (file_put_contents($filePath,  PHP_EOL . $message,FILE_APPEND) !== false) {
    $result["result"] = "success";
    $result["msg"] = "File created successfully!";
} else {
    $result["result"] = "fail";
    $result["msg"] = "Error creating fail!";
}
echo json_encode($result);