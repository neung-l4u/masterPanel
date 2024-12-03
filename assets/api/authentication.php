<?php
global $db;
session_start();
include '../db/db.php';
include "../db/initDB.php";
$salt = "L4U";

$data["action"] = !empty(trim($_POST["act"])) ? trim($_POST["act"]) : "No Action";
$data["user"] = !empty(trim($_POST["u"])) ? trim($_POST["u"]) : "No Username";
$data["password"] = !empty(trim($_POST["p"])) ? trim($_POST["p"]) : "No Password";
$data["remember"] = $_POST["remember"] ? : false;

$result["result"] = "";
$result["msg"] = "";

if (!empty(trim($_POST["p"]))){
    $passwordAddSalt = $salt . $data["password"];
    $data["passwordHash"] = md5($passwordAddSalt);
}

if(isset($_POST['act'])){
    if ($data["action"]=="login"){
        $account = $db->query('SELECT s.sID, s.sEmail, s.sMobile, s.sName, s.sLevel, l.lName, s.teamID ,s.sPic, s.sL4U, s.sCEO
                                     FROM `staffs` s , `userLevel` l
                                     WHERE s.sDeleteAt IS NULL 
                                     AND s.sStatus = ? 
                                     AND s.sPassword = ?
                                     AND ( s.sEmail = ? OR s.sMobile = ? )
                                     AND s.sLevel = l.lID;'
            ,1,$data["passwordHash"],$data["user"],$data["user"]
        )->fetchArray();

        if($account['sID'])
        {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $_SESSION['login'] = date('Y-m-d H:i:s', time());
            $_SESSION['id'] = $account['sID'];
            $_SESSION['email'] = $account['sEmail'];
            $_SESSION['phone'] = $account['sMobile'];
            $_SESSION['password'] = $data["password"];
            $_SESSION['teamID'] = $account["teamID"];
            $_SESSION['name'] = $account['sName'];
            $_SESSION['tName'] = $account['sTName'];
            $_SESSION['userPic'] = $account['sPic'];
            $_SESSION['L4UCoin'] = $account['sL4U'];
            $_SESSION['CEOCoin'] = $account['sCEO'];
            $_SESSION['level'] = $account['sLevel'];
            $_SESSION['levelName'] = $account['lName'];
            $_SESSION['token'] = $token;

            if ($data["remember"]){
                $cookieExpire = time() + (86400 * 365); //time + 1 year
                setcookie("user", $data["user"], $cookieExpire, "/");
                setcookie("pass", $data["password"], $cookieExpire, "/");
                setcookie("remember", $data["remember"], $cookieExpire, "/");
                setcookie("token", $token, $cookieExpire, "/");

        }else if ($data["remember"]===false){
                /*unset($_COOKIE["user"]);
                unset($_COOKIE["pass"]);
                unset($_COOKIE["remember"]);
                unset($_COOKIE["token"]);
                setcookie('user', false, -1, '/', $_SERVER["HTTP_HOST"]);
                setcookie('pass', false, -1, '/', $_SERVER["HTTP_HOST"]);
                setcookie('remember', false, -1, '/', $_SERVER["HTTP_HOST"]);
                setcookie('token', false, -1, '/', $_SERVER["HTTP_HOST"]);*/
                setcookie("user", "", time() - 3600, '/');
                setcookie("pass", "", time() - 3600, '/');
                setcookie("remember", "", time() - 3600, '/');
                setcookie("token", "", time() - 3600, '/');
            }

            $db->query('UPDATE `staffs` SET sLogin = NOW(),sToken = ? WHERE sID = ?', $token,$account['sID']);
            $data["result"] = "success";
            $result["result"] = "success";
            $result["msg"] = $token;
        }else{
            $data["result"] = "notFound";
            $result["result"] = "fail";
            $result["msg"] = "User not match!!";
        }
    }
}else{
    $data["result"] = "noAction";
    $result["result"] = "fail";
    $result["msg"] = "Unknown action";
}

echo json_encode($result);