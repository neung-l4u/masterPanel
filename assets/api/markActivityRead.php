<?php
session_start();
if (isset($_POST['latestId'])) {
    require_once __DIR__ . '/../db/db.php';
    require_once __DIR__ . '/../db/initDB.php';
    global $db;
    
    $userID = $_SESSION['id'];
    $latestId = $_POST['latestId'];
    
    try {
        // Mark all activities up to latestId as read for this user
        $result = $db->query('UPDATE CoinLogs SET is_read = 1 WHERE ownerID = ? AND id <= ?', 
            $userID, $latestId);

        // Team notifications track read state per person in their own table, so
        // opening the bell marks this user's copy of them read too.
        $teamID = $_SESSION['teamID'] ?? 0;
        if (!empty($teamID)) {
            $db->query(
                'INSERT IGNORE INTO notification_reads (notificationID, staffID, readAt)
                 SELECT n.id, ?, NOW() FROM notifications n WHERE n.teamID = ?',
                $userID, $teamID
            );
        }

        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing latestId']);
}
