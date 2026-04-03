<?php
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

header('Content-Type: application/json');

$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
if (!$myID) { echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']); exit; }

$act = $_POST['act'] ?? $_GET['act'] ?? '';

switch ($act) {

    case 'getLists':
        $bID = intval($_POST['bID'] ?? 0);
        $lists = $db->query(
            'SELECT * FROM l4utask_lists WHERE bID = ? AND lStatus = 1 ORDER BY lPosition ASC', $bID
        )->fetchAll();

        // For each list, get its cards
        foreach ($lists as &$list) {
            $list['cards'] = $db->query(
                'SELECT c.*, s.sNickName AS creatorName, s.sPic AS creatorPic
                 FROM l4utask_cards c
                 LEFT JOIN staffs s ON s.sID = c.cCreatedBy
                 WHERE c.lID = ? AND c.cStatus = 1 AND c.cDeletedAt IS NULL
                 ORDER BY c.cPosition ASC',
                $list['lID']
            )->fetchAll();

            // For each card, get assigned members + subitems
            foreach ($list['cards'] as &$card) {
                $card['members'] = $db->query(
                    'SELECT cm.cmID, cm.sID, s.sNickName, s.sPic
                     FROM l4utask_card_members cm
                     LEFT JOIN staffs s ON s.sID = cm.sID
                     WHERE cm.cID = ?',
                    $card['cID']
                )->fetchAll();

                $allSubs = $db->query(
                    'SELECT si.*, s.sNickName AS assigneeName, s.sPic AS assigneePic
                     FROM l4utask_card_subitems si
                     LEFT JOIN staffs s ON s.sID = si.siAssignee
                     WHERE si.cID = ?
                     ORDER BY si.siPosition ASC',
                    $card['cID']
                )->fetchAll();
                // Filter out deleted subitems in PHP to avoid MySQL strict mode zero-date issues
                $card['subitems'] = array_values(array_filter($allSubs, function($si) {
                    $d = $si['siDeletedAt'] ?? null;
                    return empty($d) || $d === '0000-00-00 00:00:00' || $d === '0000-00-00' || strtotime($d) === false || strtotime($d) < 86400;
                }));
            }
        }

        echo json_encode(['status' => 'success', 'data' => $lists]);
        break;

    case 'createList':
        $bID  = intval($_POST['bID'] ?? 0);
        $name = trim($_POST['lName'] ?? '');
        if (!$name) { echo json_encode(['status' => 'error', 'msg' => 'List name is required']); exit; }

        // Get max position
        $maxPos = $db->query('SELECT IFNULL(MAX(lPosition),0)+1 AS pos FROM l4utask_lists WHERE bID = ?', $bID)->fetchArray();
        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)', $bID, $name, $maxPos['pos']);
        $newID = $db->lastInsertID();
        echo json_encode(['status' => 'success', 'lID' => $newID]);
        break;

    case 'updateList':
        $lID  = intval($_POST['lID'] ?? 0);
        $name = trim($_POST['lName'] ?? '');
        $db->query('UPDATE l4utask_lists SET lName=? WHERE lID=?', $name, $lID);
        echo json_encode(['status' => 'success']);
        break;

    case 'archiveList':
        $lID = intval($_POST['lID'] ?? 0);
        $db->query('UPDATE l4utask_lists SET lStatus=0 WHERE lID=?', $lID);
        echo json_encode(['status' => 'success']);
        break;

    case 'reorderLists':
        $positions = json_decode($_POST['positions'] ?? '[]', true);
        if (is_array($positions)) {
            foreach ($positions as $pos) {
                $db->query('UPDATE l4utask_lists SET lPosition=? WHERE lID=?', intval($pos['position']), intval($pos['lID']));
            }
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'duplicateList':
        $lID = intval($_POST['lID'] ?? 0);
        if (!$lID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid list ID']);
            break;
        }
        
        // Get original list
        $list = $db->query('SELECT * FROM l4utask_lists WHERE lID = ?', $lID)->fetchArray();
        if (!$list) {
            echo json_encode(['status' => 'error', 'msg' => 'List not found']);
            break;
        }
        
        // Get max position for new list
        $maxPos = $db->query('SELECT IFNULL(MAX(lPosition),0)+1 AS pos FROM l4utask_lists WHERE bID = ?', $list['bID'])->fetchArray();
        
        // Create new list
        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)', 
            $list['bID'], $list['lName'] . ' (Copy)', $maxPos['pos']);
        $newListID = $db->lastInsertID();
        
        // Duplicate cards
        $cards = $db->query('SELECT * FROM l4utask_cards WHERE lID = ?', $lID)->fetchAll();
        foreach ($cards as $card) {
            $db->query('INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPosition, cPriority, cDueDate, cCreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                $newListID, $card['bID'], $card['cTitle'] . ' (Copy)', $card['cDescription'], $card['cPosition'], $card['cPriority'], $card['cDueDate'], $card['cCreatedBy']
            );
            $newCardID = $db->lastInsertID();
            
            // Duplicate subitems
            $subitems = $db->query('SELECT * FROM l4utask_card_subitems WHERE cID = ?', $card['cID'])->fetchAll();
            foreach ($subitems as $subitem) {
                $db->query('INSERT INTO l4utask_card_subitems (cID, siTitle, siStatus, siDueDate, siPosition, siCreatedBy) VALUES (?, ?, ?, ?, ?, ?)',
                    $newCardID, $subitem['siTitle'], $subitem['siStatus'], $subitem['siDueDate'], $subitem['siPosition'], $subitem['siCreatedBy']
                );
            }
        }
        
        echo json_encode(['status' => 'success', 'newListID' => $newListID]);
        break;

    case 'deleteList':
        $lID = intval($_POST['lID'] ?? 0);
        if (!$lID) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid list ID']);
            break;
        }
        
        // Get board ID for cleanup
        $list = $db->query('SELECT bID FROM l4utask_lists WHERE lID = ?', $lID)->fetchArray();
        if (!$list) {
            echo json_encode(['status' => 'error', 'msg' => 'List not found']);
            break;
        }
        
        // Delete subitems first
        $cards = $db->query('SELECT cID FROM l4utask_cards WHERE lID = ?', $lID)->fetchAll();
        foreach ($cards as $card) {
            $db->query('DELETE FROM l4utask_card_subitems WHERE cID = ?', $card['cID']);
        }
        
        // Delete cards
        $db->query('DELETE FROM l4utask_cards WHERE lID = ?', $lID);
        
        // Delete list
        $db->query('DELETE FROM l4utask_lists WHERE lID = ?', $lID);
        
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
}
