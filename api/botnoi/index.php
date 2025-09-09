<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$salt = "L4U";

$raw = file_get_contents("php://input");
// $raw = '{
//             "user":"bWFya0Bsb2NhbGZvcnlvdS5jb20=",
//             "pass":"bWFya2VhdHMjMjAyNA==",
//             "system":"bGVhcm5pbmdDZW50ZXI=",
//             "requestAt":1755486357
//         }';
// $raw = '{
//             "user":"dGVzdDE=",
//             "pass":"cGFzc3dvcmQ=",
//             "system":"QUkgQXV0byBkaWFs",
//             "requestAt":1182119280
//         }';
$data = json_decode($raw, true);

$user     = base64_decode($data['user']);
$pass     = base64_decode($data['pass']);
$system   = base64_decode($data['system']);
$requestAt = $data['requestAt'];

$request_id = 'req_' . $requestAt;

if (!$user || !$pass || !$system) {
    $respond = [
        "status" => [
            "code"       => "400",
            "message"    => "Bad Request",
            "request_id" => 'req_' . $request_id
        ],
        "data" => null
    ];
    echo json_encode($respond);
    exit();
}

$passwordAddSalt = $salt . $pass;
$passwordHash = md5($passwordAddSalt);

$account = $db->query('SELECT s.sID, s.sEmail, s.sMobile, s.sName, s.sLevel, l.lName, s.teamID ,s.sPic, s.sL4U, s.sCEO
                                     FROM `staffs` s , `userLevel` l
                                     WHERE s.sDeleteAt IS NULL 
                                     AND s.sStatus = ? 
                                     AND s.sPassword = ?
                                     AND ( s.sEmail = ? OR s.sMobile = ? )
                                     AND s.sLevel = l.lID;'
            ,1,$passwordHash,$user,$user
        )->fetchArray();

if ($account) {
    $staffID = $account['sID'];

    $iss = md5($staffID);
    $currentTimestamp = time();
    $oneYearLaterTimestamp = strtotime('+1 year', $currentTimestamp);

    $respond = [
        "status" => [
            "code"       => "200",
            "message"    => "OK",
            "request_id" => $request_id
        ],
        "data" => [
            "iss" => $iss,
            "iat" => $currentTimestamp,
            "exp" => $oneYearLaterTimestamp
        ]
    ];
} else {
    $respond = [
        "status" => [
            "code"       => "404",
            "message"    => "Not found",
            "request_id" => $request_id
        ],
        "data" => null
    ];
} 

echo json_encode($respond);