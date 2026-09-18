<?php
// ทีมที่เปิดดูรหัสผ่านลูกค้าได้จากหน้า Payment Password
// 1=CS, 2=AM AU, 5=IT, 6=CEO, 8=AM USA, 10=AM NZ, 11=AM UK, 12=MK
// (ไม่รวม 3=Sales, 4=HR, 7=Other, 13=House Keeping)
// แก้ที่นี่ที่เดียว มีผลทั้งตอนวาดตาราง (dataPaymentAccount.php)
// และตอนขอค่ารหัสจริง (revealPassword.php)
$PASSWORD_VIEW_TEAMS = [1, 2, 5, 6, 8, 10, 11, 12];

function canViewCustomerPassword(): bool
{
    global $PASSWORD_VIEW_TEAMS;
    return in_array((int) ($_SESSION['teamID'] ?? 0), $PASSWORD_VIEW_TEAMS, true);
}
