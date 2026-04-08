<?php
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

header('Content-Type: application/json');

$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
if (!$myID) { echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']); exit; }

$act = $_POST['act'] ?? $_GET['act'] ?? '';

// Helper: log activity
function logActivity($db, $sID, $cID, $siID, $type, $field, $oldVal, $newVal) {
    $db->query(
        'INSERT INTO l4utask_card_activities (cID, siID, sID, caType, caField, caOldValue, caNewValue) VALUES (?,?,?,?,?,?,?)',
        $cID, $siID, $sID, $type, $field, $oldVal, $newVal
    );
}

switch ($act) {

    case 'getCard':
        $cID = intval($_POST['cID'] ?? 0);
        $card = $db->query(
            'SELECT c.*, s.sNickName AS creatorName, s.sPic AS creatorPic, l.lName
             FROM l4utask_cards c
             LEFT JOIN staffs s ON s.sID = c.cCreatedBy
             LEFT JOIN l4utask_lists l ON l.lID = c.lID
             WHERE c.cID = ? AND c.cDeletedAt IS NULL', $cID
        )->fetchArray();

        // Get members
        $card['members'] = $db->query(
            'SELECT cm.cmID, cm.sID, s.sNickName, s.sPic
             FROM l4utask_card_members cm
             LEFT JOIN staffs s ON s.sID = cm.sID
             WHERE cm.cID = ?', $cID
        )->fetchAll();

        // Get comments
        $card['comments'] = $db->query(
            'SELECT cc.*, s.sNickName, s.sPic
             FROM l4utask_card_comments cc
             LEFT JOIN staffs s ON s.sID = cc.sID
             WHERE cc.cID = ? AND cc.ccDeletedAt IS NULL
             ORDER BY cc.ccCreatedAt ASC', $cID
        )->fetchAll();

        // Get subitems - filter in PHP to avoid MySQL strict mode zero-date issues
        $allSubs = $db->query(
            'SELECT si.*, s.sNickName AS assigneeName, s.sPic AS assigneePic
             FROM l4utask_card_subitems si
             LEFT JOIN staffs s ON s.sID = si.siAssignee
             WHERE si.cID = ?
             ORDER BY si.siPosition ASC', $cID
        )->fetchAll();
        $card['subitems'] = array_values(array_filter($allSubs, function($si) {
            $d = $si['siDeletedAt'] ?? null;
            return empty($d) || $d === '0000-00-00 00:00:00' || $d === '0000-00-00' || strtotime($d) === false || strtotime($d) < 86400;
        }));

        echo json_encode(['status' => 'success', 'data' => $card]);
        break;

    case 'createCard':
        $lID   = intval($_POST['lID'] ?? 0);
        $bID   = intval($_POST['bID'] ?? 0);
        $title = trim($_POST['cTitle'] ?? '');
        if (!$title) { echo json_encode(['status' => 'error', 'msg' => 'Card title is required']); exit; }

        $maxPos = $db->query('SELECT IFNULL(MAX(cPosition),0)+1 AS pos FROM l4utask_cards WHERE lID = ? AND cDeletedAt IS NULL', $lID)->fetchArray();
        $db->query(
            'INSERT INTO l4utask_cards (lID, bID, cTitle, cPosition, cCreatedBy) VALUES (?, ?, ?, ?, ?)',
            $lID, $bID, $title, $maxPos['pos'], $myID
        );
        $newID = $db->lastInsertID();
        // Get list name for activity
        $listInfo = $db->query('SELECT lName FROM l4utask_lists WHERE lID = ?', $lID)->fetchArray();
        logActivity($db, $myID, $newID, null, 'created', 'Card', null, $listInfo['lName'] ?? '');
        echo json_encode(['status' => 'success', 'cID' => $newID]);
        break;

    case 'updateCard':
        $cID   = intval($_POST['cID'] ?? 0);
        // Fetch old values for activity log
        $old = $db->query('SELECT * FROM l4utask_cards WHERE cID = ?', $cID)->fetchArray();
        $priMap = ['0'=>'None','1'=>'Low','2'=>'Medium','3'=>'High','4'=>'Urgent'];

        $title = trim($_POST['cTitle'] ?? '');
        $desc  = $_POST['cDescription'] ?? null;
        $color = $_POST['cColor'] ?? null;
        $priority = isset($_POST['cPriority']) ? intval($_POST['cPriority']) : null;
        $dueDate  = $_POST['cDueDate'] ?? null;

        // Build dynamic update
        $fields = []; $params = [];
        if ($title)            { $fields[] = 'cTitle=?';       $params[] = $title; }
        if ($desc !== null)    { $fields[] = 'cDescription=?'; $params[] = $desc; }
        if ($color !== null)   { $fields[] = 'cColor=?';       $params[] = $color; }
        if ($priority !== null){ $fields[] = 'cPriority=?';    $params[] = $priority; }
        if ($dueDate !== null) { $fields[] = 'cDueDate=?';     $params[] = ($dueDate === '' ? null : $dueDate); }
        if (isset($_POST['cStage']))       { $fields[] = 'cStage=?';       $params[] = trim($_POST['cStage']); }
        if (isset($_POST['cCompletedAt'])) { $fields[] = 'cCompletedAt=?'; $params[] = ($_POST['cCompletedAt'] === '' ? null : $_POST['cCompletedAt']); }

        if (!empty($fields)) {
            $params[] = $cID;
            $db->query('UPDATE l4utask_cards SET ' . implode(',', $fields) . ' WHERE cID=?', ...$params);
        }

        // Log activities for changed fields
        if ($old) {
            if ($title && $title !== ($old['cTitle'] ?? ''))
                logActivity($db, $myID, $cID, null, 'field_change', 'Title', $old['cTitle'], $title);
            if (isset($_POST['cStage']) && trim($_POST['cStage']) !== ($old['cStage'] ?? ''))
                logActivity($db, $myID, $cID, null, 'field_change', 'Stage', $old['cStage'] ?? '', trim($_POST['cStage']));
            if ($priority !== null && $priority !== intval($old['cPriority'] ?? 0))
                logActivity($db, $myID, $cID, null, 'field_change', 'Priority', $priMap[$old['cPriority'] ?? '0'] ?? '', $priMap[$priority] ?? '');
            if ($dueDate !== null && ($dueDate === '' ? null : $dueDate) !== ($old['cDueDate'] ?? null))
                logActivity($db, $myID, $cID, null, 'field_change', 'Due Date', $old['cDueDate'] ?? '', $dueDate);
            if (isset($_POST['cCompletedAt']) && ($_POST['cCompletedAt'] === '' ? null : $_POST['cCompletedAt']) !== ($old['cCompletedAt'] ?? null))
                logActivity($db, $myID, $cID, null, 'field_change', 'Completed', $old['cCompletedAt'] ?? '', $_POST['cCompletedAt']);
            if ($desc !== null && $desc !== ($old['cDescription'] ?? ''))
                logActivity($db, $myID, $cID, null, 'field_change', 'Description', '', 'Updated');
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'moveCard':
        $cID     = intval($_POST['cID'] ?? 0);
        $newLID  = intval($_POST['lID'] ?? 0);
        $newPos  = intval($_POST['cPosition'] ?? 0);
        $db->query('UPDATE l4utask_cards SET lID=?, cPosition=? WHERE cID=?', $newLID, $newPos, $cID);
        echo json_encode(['status' => 'success']);
        break;

    case 'reorderCards':
        $positions = json_decode($_POST['positions'] ?? '[]', true);
        if (is_array($positions)) {
            foreach ($positions as $pos) {
                $db->query('UPDATE l4utask_cards SET lID=?, cPosition=? WHERE cID=?',
                    intval($pos['lID']), intval($pos['position']), intval($pos['cID']));
            }
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'deleteCard':
        $cID = intval($_POST['cID'] ?? 0);
        $db->query('UPDATE l4utask_cards SET cDeletedAt=NOW(), cDeletedBy=? WHERE cID=?', $myID, $cID);
        echo json_encode(['status' => 'success']);
        break;

    // ===================== MEMBERS =====================
    case 'assignMember':
        $cID = intval($_POST['cID'] ?? 0);
        $sID = intval($_POST['sID'] ?? 0);
        $db->query('INSERT IGNORE INTO l4utask_card_members (cID, sID) VALUES (?, ?)', $cID, $sID);
        $staff = $db->query('SELECT sNickName FROM staffs WHERE sID = ?', $sID)->fetchArray();
        logActivity($db, $myID, $cID, null, 'member_added', 'Owner', null, $staff['sNickName'] ?? '');
        echo json_encode(['status' => 'success']);
        break;

    case 'removeMember':
        $cID = intval($_POST['cID'] ?? 0);
        $sID = intval($_POST['sID'] ?? 0);
        $staff = $db->query('SELECT sNickName FROM staffs WHERE sID = ?', $sID)->fetchArray();
        $db->query('DELETE FROM l4utask_card_members WHERE cID=? AND sID=?', $cID, $sID);
        logActivity($db, $myID, $cID, null, 'member_removed', 'Owner', $staff['sNickName'] ?? '', null);
        echo json_encode(['status' => 'success']);
        break;

    // ===================== COMMENTS =====================
    case 'addComment':
        $cID  = intval($_POST['cID'] ?? 0);
        $text = trim($_POST['ccText'] ?? '');
        if (!$text) { echo json_encode(['status' => 'error', 'msg' => 'Comment is required']); exit; }
        $db->query('INSERT INTO l4utask_card_comments (cID, sID, ccText) VALUES (?, ?, ?)', $cID, $myID, $text);
        $ccID = $db->lastInsertID();
        echo json_encode(['status' => 'success', 'ccID' => $ccID]);
        break;

    case 'deleteComment':
        $ccID = intval($_POST['ccID'] ?? 0);
        $db->query('UPDATE l4utask_card_comments SET ccDeletedAt=NOW() WHERE ccID=?', $ccID);
        echo json_encode(['status' => 'success']);
        break;

    // ===================== SUBITEMS =====================
    case 'addSubitem':
        $cID   = intval($_POST['cID'] ?? 0);
        $title = trim($_POST['siTitle'] ?? '');
        if (!$title) { echo json_encode(['status' => 'error', 'msg' => 'Subitem title is required']); exit; }
        $maxPos = $db->query('SELECT IFNULL(MAX(siPosition),0)+1 AS pos FROM l4utask_card_subitems WHERE cID = ?', $cID)->fetchArray();
        $db->query(
            'INSERT INTO l4utask_card_subitems (cID, siTitle, siPosition, siCreatedBy) VALUES (?, ?, ?, ?)',
            $cID, $title, $maxPos['pos'], $myID
        );
        echo json_encode(['status' => 'success', 'siID' => $db->lastInsertID()]);
        break;

    case 'updateSubitem':
        $siID = intval($_POST['siID'] ?? 0);
        $fields = []; $params = [];
        if (isset($_POST['siTitle']))       { $fields[] = 'siTitle=?';       $params[] = trim($_POST['siTitle']); }
        if (isset($_POST['siStatus']))      { $fields[] = 'siStatus=?';      $params[] = trim($_POST['siStatus']); }
        if (isset($_POST['siPriority']))    { $fields[] = 'siPriority=?';    $params[] = trim($_POST['siPriority']); }
        if (isset($_POST['siDueDate']))     { $fields[] = 'siDueDate=?';     $params[] = ($_POST['siDueDate'] === '' ? null : $_POST['siDueDate']); }
        if (isset($_POST['siCompletedAt'])){ $fields[] = 'siCompletedAt=?'; $params[] = ($_POST['siCompletedAt'] === '' ? null : $_POST['siCompletedAt']); }
        if (isset($_POST['siAssignee']))    { $fields[] = 'siAssignee=?';    $params[] = (intval($_POST['siAssignee']) ?: null); }
        if (!empty($fields)) {
            $params[] = $siID;
            $db->query('UPDATE l4utask_card_subitems SET ' . implode(',', $fields) . ' WHERE siID=?', ...$params);
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'deleteSubitem':
        $siID = intval($_POST['siID'] ?? 0);
        $db->query('UPDATE l4utask_card_subitems SET siDeletedAt=NOW() WHERE siID=?', $siID);
        echo json_encode(['status' => 'success']);
        break;

    case 'toggleSubitem':
        $siID = intval($_POST['siID'] ?? 0);
        $status = trim($_POST['siStatus'] ?? 'Pending');
        $completedAt = ($status === 'Done') ? date('Y-m-d') : null;
        $oldSi = $db->query('SELECT * FROM l4utask_card_subitems WHERE siID = ?', $siID)->fetchArray();
        $db->query('UPDATE l4utask_card_subitems SET siStatus=?, siCompletedAt=? WHERE siID=?', $status, $completedAt, $siID);
        if ($oldSi) logActivity($db, $myID, $oldSi['cID'], $siID, 'field_change', 'Status', $oldSi['siStatus'] ?? '', $status);
        echo json_encode(['status' => 'success']);
        break;

    // ===================== ACTIVITY LOG =====================
    case 'getActivities':
        $cID  = intval($_POST['cID'] ?? 0);
        $siID = intval($_POST['siID'] ?? 0);
        $where = $siID ? 'a.siID = ?' : 'a.cID = ? AND a.siID IS NULL';
        $param = $siID ?: $cID;
        $activities = $db->query(
            'SELECT a.*, s.sNickName, s.sPic
             FROM l4utask_card_activities a
             LEFT JOIN staffs s ON s.sID = a.sID
             WHERE ' . $where . '
             ORDER BY a.caCreatedAt DESC
             LIMIT 100',
            $param
        )->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $activities]);
        break;

    // ===================== GET SUBITEM DETAIL =====================
    case 'getSubitem':
        $siID = intval($_POST['siID'] ?? 0);
        $si = $db->query(
            'SELECT si.*, s.sNickName AS assigneeName, s.sPic AS assigneePic,
                    cr.sNickName AS creatorName, cr.sPic AS creatorPic
             FROM l4utask_card_subitems si
             LEFT JOIN staffs s ON s.sID = si.siAssignee
             LEFT JOIN staffs cr ON cr.sID = si.siCreatedBy
             WHERE si.siID = ?',
            $siID
        )->fetchArray();
        // Check if subitem is deleted
        if ($si) {
            $d = $si['siDeletedAt'] ?? null;
            if (!empty($d) && $d !== '0000-00-00 00:00:00' && $d !== '0000-00-00' && strtotime($d) !== false && strtotime($d) >= 86400) {
                $si = null;
            }
        }
        if (!$si) { echo json_encode(['status' => 'error', 'msg' => 'Subitem not found']); exit; }

        // Get card title for context
        $card = $db->query('SELECT cTitle FROM l4utask_cards WHERE cID = ?', $si['cID'])->fetchArray();
        $si['cardTitle'] = $card['cTitle'] ?? '';

        // Get activities for this subitem
        $si['activities'] = $db->query(
            'SELECT a.*, s.sNickName, s.sPic
             FROM l4utask_card_activities a
             LEFT JOIN staffs s ON s.sID = a.sID
             WHERE a.siID = ?
             ORDER BY a.caCreatedAt DESC
             LIMIT 50',
            $siID
        )->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $si]);
        break;

    // ===================== STAFF LIST (for assign dropdown) =====================
    case 'getStaffs':
        $staffs = $db->query('SELECT sID, sNickName, sPic FROM staffs WHERE sStatus = 1 ORDER BY sNickName ASC')->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $staffs]);
        break;

    case 'duplicateCard':
        $cID = intval($_POST['cID'] ?? 0);
        if (!$cID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid card ID']);
            break;
        }
        
        // Get original card
        $card = $db->query('SELECT * FROM l4utask_cards WHERE cID = ?', $cID)->fetchArray();
        if (!$card) {
            echo json_encode(['status' => 'error', 'msg' => 'Card not found']);
            break;
        }
        
        // Get max position for new card
        $maxPos = $db->query('SELECT IFNULL(MAX(cPosition),0)+1 AS pos FROM l4utask_cards WHERE lID = ?', $card['lID'])->fetchArray();
        
        // Create new card
        $db->query('INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPosition, cPriority, cDueDate, cCreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            $card['lID'], $card['bID'], $card['cTitle'] . ' (Copy)', $card['cDescription'], $maxPos['pos'], $card['cPriority'], $card['cDueDate'], $card['cCreatedBy']
        );
        $newCardID = $db->lastInsertID();
        
        // Duplicate subitems
        $subitems = $db->query('SELECT * FROM l4utask_card_subitems WHERE cID = ?', $cID)->fetchAll();
        foreach ($subitems as $subitem) {
            $db->query('INSERT INTO l4utask_card_subitems (cID, siTitle, siStatus, siDueDate, siPosition, siCreatedBy) VALUES (?, ?, ?, ?, ?, ?)',
                $newCardID, $subitem['siTitle'], $subitem['siStatus'], $subitem['siDueDate'], $subitem['siPosition'], $subitem['siCreatedBy']
            );
        }
        
        echo json_encode(['status' => 'success', 'newCardID' => $newCardID]);
        break;

    case 'moveCardToTop':
        $cID = intval($_POST['cID'] ?? 0);
        if (!$cID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid card ID']);
            break;
        }
        
        // Get card's current list
        $card = $db->query('SELECT lID FROM l4utask_cards WHERE cID = ?', $cID)->fetchArray();
        if (!$card) {
            echo json_encode(['status' => 'error', 'msg' => 'Card not found']);
            break;
        }
        
        // Shift all other cards down by 1
        $db->query('UPDATE l4utask_cards SET cPosition = cPosition + 1 WHERE lID = ? AND cID != ?', $card['lID'], $cID);
        
        // Set this card to position 0
        $db->query('UPDATE l4utask_cards SET cPosition = 0 WHERE cID = ?', $cID);
        
        echo json_encode(['status' => 'success']);
        break;

    case 'moveCard':
        $cID = intval($_POST['cID'] ?? 0);
        $newLID = intval($_POST['lID'] ?? 0);
        if (!$cID || !$newLID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid card ID or list ID']);
            break;
        }
        
        // Get max position in new list
        $maxPos = $db->query('SELECT IFNULL(MAX(cPosition),0)+1 AS pos FROM l4utask_cards WHERE lID = ?', $newLID)->fetchArray();
        
        // Update card list and position
        $db->query('UPDATE l4utask_cards SET lID = ?, cPosition = ? WHERE cID = ?', $newLID, $maxPos['pos'], $cID);
        
        echo json_encode(['status' => 'success']);
        break;

    case 'deleteCard':
        $cID = intval($_POST['cID'] ?? 0);
        if (!$cID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid card ID']);
            break;
        }
        
        // Delete subitems first
        $db->query('DELETE FROM l4utask_card_subitems WHERE cID = ?', $cID);
        
        // Delete card
        $db->query('DELETE FROM l4utask_cards WHERE cID = ?', $cID);
        
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
}
