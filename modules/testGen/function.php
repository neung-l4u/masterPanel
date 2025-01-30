<?php
include_once 'db/db.php';
include_once 'db/initDB.php';

// ฟังก์ชันสำหรับสร้าง Ticket Code
function generateTicketCode($projectId)
{
    $dateToday = date('Y-m-d');
    $formattedDate = date('Ymd');

    // ค้นหา daily_index ล่าสุดของวันนั้น (รวมทุกโปรเจกต์)
    $tickets = $db->query('SELECT daily_index FROM tickets WHERE date_created = ? ORDER BY daily_index DESC LIMIT 1',$dateToday )->fetchAll();


    if (count($tickets) > 0) {
        // หากมี ticket ในวันนั้นแล้ว ให้เพิ่ม daily_index
        $dailyIndex = count($tickets) + 1;
    } else {
        // หากยังไม่มี ticket ให้เริ่ม daily_index เป็น 1
        $dailyIndex = 1;
    }

    // แปลง daily_index เป็น 2 หลัก
    $formattedIndex = str_pad($dailyIndex, 2, "0", STR_PAD_LEFT);

    // สร้าง ticket code
    $ticketCode = "$projectId-$formattedDate-$formattedIndex";

    // บันทึกลงฐานข้อมูล
    $insertQuery = $db->query('INSERT INTO tickets (project_id, ticket_code, date_created, daily_index) VALUES (?, ?, ?, ?)'
        , $projectId, $ticketCode, $dateToday, $dailyIndex);

    return $ticketCode;
}
