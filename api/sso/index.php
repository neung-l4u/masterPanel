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
$data = json_decode($raw, true);

// Optional shared-secret gate. Backward compatible: requests that send NO
// "secret" field behave exactly as before (existing callers keep working).
// When a "secret" IS present it must match the configured value.
$expectedSecret = getenv('SSO_SHARED_SECRET') ?: '';
if (isset($data['secret']) && $data['secret'] !== '') {
    if ($expectedSecret === '' || !hash_equals($expectedSecret, (string)$data['secret'])) {
        echo json_encode(["status" => false, "msg" => "Unauthorized"]);
        exit;
    }
}

$user     = base64_decode($data['user']);
$pass     = base64_decode($data['pass']);
$system   = base64_decode($data['system']);
$requestAt = $data['requestAt'];

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

count($account);
if (count($account) !== 0) {
    $staffID = $account['sID'];
    $staffName = $account['sName'];

    $sql = "SELECT `$system` FROM isAdmin WHERE staffID = ?";
    $getRole = $db->query($sql, $staffID)->fetchArray();

    $role = $getRole[$system] == 1 ? "admin" : "user";

    $iss = md5($staffID);
    $currentTimestamp = time();
    $oneYearLaterTimestamp = strtotime('+1 year', $currentTimestamp);

    echo json_encode([
        "status" => true,
        "userID" => $staffID,
        "name"   => $staffName,
        "role"   => $role,
        "system" => $system,
        "iss"    => $iss,
        "iat"    => $currentTimestamp,
        "exp"    => $oneYearLaterTimestamp
    ]);
} else {
    echo json_encode([
        "status" => false,
        "msg" => "Not found"
    ]);
}
?>