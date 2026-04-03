<?php
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

header('Content-Type: application/json');

$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
if (!$myID) { echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']); exit; }

$act = $_POST['act'] ?? $_GET['act'] ?? '';

switch ($act) {

    // ===================== BOARDS =====================
    case 'getBoards':
        $rows = $db->query(
            'SELECT b.*, s.sNickName AS creatorName,
                    (SELECT COUNT(*) FROM l4utask_lists WHERE bID = b.bID AND lStatus = 1) AS listCount,
                    (SELECT COUNT(*) FROM l4utask_cards WHERE bID = b.bID AND cStatus = 1 AND cDeletedAt IS NULL) AS cardCount
             FROM l4utask_boards b
             LEFT JOIN staffs s ON s.sID = b.bCreatedBy
             WHERE b.bStatus = 1 AND b.bDeletedAt IS NULL
             ORDER BY b.bCreatedAt DESC'
        )->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $rows]);
        break;

    case 'getBoard':
        $bID = intval($_POST['bID'] ?? 0);
        $board = $db->query(
            'SELECT b.*, s.sNickName AS creatorName
             FROM l4utask_boards b
             LEFT JOIN staffs s ON s.sID = b.bCreatedBy
             WHERE b.bID = ? AND b.bDeletedAt IS NULL', $bID
        )->fetchArray();
        // Get board members
        $board['members'] = $db->query(
            'SELECT bm.*, s.sNickName, s.sPic
             FROM l4utask_board_members bm
             LEFT JOIN staffs s ON s.sID = bm.sID
             WHERE bm.bID = ?', $bID
        )->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $board]);
        break;

    case 'createBoard':
        $name  = trim($_POST['bName'] ?? '');
        $desc  = trim($_POST['bDescription'] ?? '');
        $color = trim($_POST['bColor'] ?? '#0079BF');
        if (!$name) { echo json_encode(['status' => 'error', 'msg' => 'Board name is required']); exit; }

        $db->query(
            'INSERT INTO l4utask_boards (bName, bDescription, bColor, bCreatedBy) VALUES (?, ?, ?, ?)',
            $name, $desc, $color, $myID
        );
        $newID = $db->lastInsertID();
        // Add creator as admin member
        $db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, 1)', $newID, $myID);
        // Save additional members from permission form
        $members = json_decode($_POST['members'] ?? '[]', true);
        if (is_array($members)) {
            foreach ($members as $m) {
                $sID = intval($m['sID'] ?? 0);
                $role = intval($m['role'] ?? 2);
                if ($sID && $sID != $myID) {
                    $db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, ?)', $newID, $sID, $role);
                }
            }
        }
        // Create default lists
        try {
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 0)', $newID, "To Do");
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 1)', $newID, "In Progress");
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 2)', $newID, "Done");
        } catch (Exception $e) {
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 0)', $newID, "To Do");
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 1)', $newID, "In Progress");
            $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 2)', $newID, "Done");
        }

        echo json_encode(['status' => 'success', 'bID' => $newID]);
        break;

    case 'updateBoard':
        $bID   = intval($_POST['bID'] ?? 0);
        $name  = trim($_POST['bName'] ?? '');
        $desc  = trim($_POST['bDescription'] ?? '');
        $color = trim($_POST['bColor'] ?? '#0079BF');
        $db->query('UPDATE l4utask_boards SET bName=?, bDescription=?, bColor=? WHERE bID=?', $name, $desc, $color, $bID);
        // Sync board members
        if (isset($_POST['members'])) {
            $members = json_decode($_POST['members'], true);
            if (is_array($members)) {
                $db->query('DELETE FROM l4utask_board_members WHERE bID = ?', $bID);
                foreach ($members as $m) {
                    $sID = intval($m['sID'] ?? 0);
                    $role = intval($m['role'] ?? 2);
                    if ($sID) {
                        $db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, ?)', $bID, $sID, $role);
                    }
                }
            }
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'deleteBoard':
        $bID = intval($_POST['bID'] ?? 0);
        $db->query('UPDATE l4utask_boards SET bDeletedAt=NOW(), bDeletedBy=? WHERE bID=?', $myID, $bID);
        echo json_encode(['status' => 'success']);
        break;

    // ===================== STAFF + TEAMS =====================
    case 'getStaffsWithTeam':
        // AM team IDs to combine: AM AU(2), AM USA(8), AM NZ(10), AM UK(11)
        $amTeamIDs = [2, 8, 10, 11];

        $staffs = $db->query(
            'SELECT s.sID, s.sNickName, s.sPic, s.teamID, t.name AS teamName, t.fullName AS teamFullName
             FROM staffs s
             LEFT JOIN Team t ON t.id = s.teamID
             WHERE s.sStatus = 1 AND s.sDeleteAt IS NULL
             ORDER BY t.idx ASC, s.sNickName ASC'
        )->fetchAll();

        // Group by team, combining AM teams
        $teams = [];
        foreach ($staffs as $s) {
            $tid = intval($s['teamID']);
            if (in_array($tid, $amTeamIDs)) {
                $groupKey = 'AM';
                $groupName = 'Account Manager (AM)';
            } else {
                $groupKey = $s['teamName'] ?: 'OT';
                $groupName = $s['teamFullName'] ?: 'Other';
            }
            if (!isset($teams[$groupKey])) {
                $teams[$groupKey] = ['key' => $groupKey, 'name' => $groupName, 'members' => []];
            }
            $teams[$groupKey]['members'][] = [
                'sID' => intval($s['sID']),
                'sNickName' => $s['sNickName'],
                'sPic' => $s['sPic'],
                'teamID' => $tid
            ];
        }
        echo json_encode(['status' => 'success', 'data' => array_values($teams)]);
        break;

    // ===================== IMPORT FROM CSV / XLSX =====================
    case 'importCSV':
        if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'msg' => 'No file uploaded or upload error']);
            exit;
        }

        $file = $_FILES['csvFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Only CSV and Excel (.xlsx, .xls) files are supported']);
            exit;
        }

        $boardName  = trim($_POST['bName'] ?? 'Imported Board');
        $boardColor = trim($_POST['bColor'] ?? '#0079BF');
        $boardDesc  = trim($_POST['bDescription'] ?? '');
        $membersRaw = json_decode($_POST['members'] ?? '[]', true);

        // Create board
        $db->query(
            'INSERT INTO l4utask_boards (bName, bDescription, bColor, bCreatedBy) VALUES (?, ?, ?, ?)',
            $boardName, $boardDesc, $boardColor, $myID
        );
        $boardID = $db->lastInsertID();
        $db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, 1)', $boardID, $myID);
        if (is_array($membersRaw)) {
            foreach ($membersRaw as $m) {
                $sID = intval($m['sID'] ?? 0);
                $role = intval($m['role'] ?? 2);
                if ($sID && $sID != $myID) {
                    $db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, ?)', $boardID, $sID, $role);
                }
            }
        }

        // Parse file (CSV or XLSX)
        $allRows = [];
        if ($ext === 'csv') {
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                echo json_encode(['status' => 'error', 'msg' => 'Cannot read CSV file']);
                exit;
            }
            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                $allRows[] = $row;
            }
            fclose($handle);
        } else {
            // Read XLSX/XLS file
            $allRows = readExcelFile($file['tmp_name']);
            if (empty($allRows)) {
                // Rollback: delete the board we just created
                $db->query('DELETE FROM l4utask_board_members WHERE bID = ?', $boardID);
                $db->query('DELETE FROM l4utask_boards WHERE bID = ?', $boardID);
                echo json_encode(['status' => 'error', 'msg' => 'Cannot read Excel file. Make sure the file is a valid .xlsx file.']);
                exit;
            }
        }

        $sectionNames = ['Templates', 'New Request', 'Tasks Queue', 'Completed Tasks'];
        $lists = [];
        $currentSection = null;
        $headerMap = [];
        $subHeaderMap = [];
        $inSubitems = false;
        $currentCardID = null;
        $cardCount = 0;
        $subitemCount = 0;
        $listPosition = 0;

        $priorityMap = ['critical'=>4,'urgent'=>4,'high'=>3,'medium'=>2,'normal'=>2,'low'=>1];

        $i = 0;
        $totalRows = count($allRows);

        while ($i < $totalRows) {
            $row = $allRows[$i];
            $col0 = trim($row[0] ?? '');

            // Detect section header
            if (in_array($col0, $sectionNames)) {
                $currentSection = $col0;
                $inSubitems = false;
                if (!isset($lists[$currentSection])) {
                    $sectionName = mb_convert_encoding($currentSection, 'UTF-8', 'UTF-8');
                    try {
                        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), ?)', $boardID, $sectionName, $listPosition++);
                    } catch (Exception $e) {
                        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)', $boardID, $sectionName, $listPosition++);
                    }
                    $lists[$currentSection] = $db->lastInsertID();
                }
                $i++;
                if ($i < $totalRows && trim($allRows[$i][0] ?? '') === 'Name') {
                    $headerMap = [];
                    foreach ($allRows[$i] as $ci => $cn) $headerMap[trim($cn)] = $ci;
                    $i++;
                }
                continue;
            }

            // Detect generic header (first row with "Name" as first column when no section yet)
            if ($col0 === 'Name' && !$currentSection) {
                $currentSection = 'General';
                if (!isset($lists[$currentSection])) {
                    try {
                        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), ?)', $boardID, $currentSection, $listPosition++);
                    } catch (Exception $e) {
                        $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)', $boardID, $currentSection, $listPosition++);
                    }
                    $lists[$currentSection] = $db->lastInsertID();
                }
                $headerMap = [];
                foreach ($row as $ci => $cn) $headerMap[trim($cn)] = $ci;
                $i++;
                continue;
            }

            // Subitems header - detect when we find "Subitems" in first column
            if ($col0 === 'Subitems') {
                error_log("DEBUG: Subitems header found, entering subitems mode");
                $inSubitems = true;
                $subHeaderMap = [];
                foreach ($row as $ci => $cn) $subHeaderMap[trim($cn)] = $ci;
                error_log("DEBUG: SubHeaderMap: " . json_encode($subHeaderMap));
                $i++;
                continue;
            }

            // Skip empty/metadata rows (only when not in subitems mode)
            if ($col0 === '' && !$inSubitems) { $inSubitems = false; $i++; continue; }
            if (!$currentSection || !isset($lists[$currentSection])) { $i++; continue; }

            // Subitem row - detect rows with empty first column when in subitems mode
            if ($inSubitems && $currentCardID && $col0 === '') {
                $siName = trim($row[$subHeaderMap['Name'] ?? 1] ?? '');
                
                // Debug logging
                error_log("DEBUG: Subitem row found - Name: '$siName', CardID: $currentCardID");
                
                // Simple validation - if we have a name, import it as subitem
                if (!empty($siName) && $siName !== 'Name') {
                    $siStatus = strtolower(trim($row[$subHeaderMap['Status'] ?? 8] ?? ''));
                    $siDue = trim($row[$subHeaderMap['Due Date'] ?? 2] ?? '');
                    $mappedStatus = ($siStatus === 'done') ? 'Done' : 'Pending';
                    
                    error_log("DEBUG: Importing subitem: '$siName' with status: '$mappedStatus'");
                    
                    // Convert to UTF-8 to fix collation issues
                    $siName = mb_convert_encoding($siName, 'UTF-8', 'UTF-8');
                    
                    try {
                        $db->query(
                            'INSERT INTO l4utask_card_subitems (cID, siTitle, siStatus, siDueDate, siPosition, siCreatedBy) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), ?, ?, ?, ?)',
                            $currentCardID, $siName, $mappedStatus,
                            (preg_match('/^\d{4}-\d{2}-\d{2}$/', $siDue) ? $siDue : null),
                            $subitemCount++, $myID
                        );
                        error_log("DEBUG: Successfully inserted subitem");
                    } catch (Exception $e) {
                        error_log("DEBUG: Failed to insert subitem: " . $e->getMessage());
                        continue;
                    }
                } else {
                    // Exit subitems mode if we hit empty row or header
                    error_log("DEBUG: Exiting subitems mode - empty name or header");
                    $inSubitems = false;
                    $currentCardID = null;
                }
                $i++;
                continue;
            }

            // Main task row - reset subitems mode when we find a new card
            $inSubitems = false;
            $name = trim($row[$headerMap['Name'] ?? 0] ?? '');
            if (!$name || strlen($name) < 3 || $name === 'Name' || in_array($name, $sectionNames) ||
                $name === 'Website Tasks' || strpos($name, 'all task about') === 0) {
                $i++;
                continue;
            }

            $description = trim($row[$headerMap['Description'] ?? 11] ?? '');
            $dueDate     = trim($row[$headerMap['Due Date'] ?? 2] ?? '');
            $priority    = strtolower(trim($row[$headerMap['Priority'] ?? 8] ?? ''));
            $priNum      = $priorityMap[$priority] ?? 0;
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) $dueDate = null;

            $lID = $lists[$currentSection];
            
            // Convert to UTF-8 to fix collation issues
            $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
            $description = $description ? mb_convert_encoding($description, 'UTF-8', 'UTF-8') : null;
            
            try {
                $db->query(
                    'INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPosition, cPriority, cDueDate, cCreatedBy) VALUES (?, ?, CAST(? AS CHAR CHARACTER SET utf8mb4), CAST(? AS CHAR CHARACTER SET utf8mb4), ?, ?, ?, ?)',
                    $lID, $boardID, $name, $description, $cardCount, $priNum, $dueDate, $myID
                );
            } catch (Exception $e) {
                // If still fails, try without description
                $db->query(
                    'INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPosition, cPriority, cDueDate, cCreatedBy) VALUES (?, ?, CAST(? AS CHAR CHARACTER SET utf8mb4), NULL, ?, ?, ?, ?)',
                    $lID, $boardID, $name, $cardCount, $priNum, $dueDate, $myID
                );
            }
            $currentCardID = $db->lastInsertID();
            error_log("DEBUG: Created main task - Name: '$name', CardID: $currentCardID");
            $cardCount++;
            $i++;
        }

        // If no lists were created (flat CSV), create a default list
        if (empty($lists)) {
            try {
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 0)', $boardID, "To Do");
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 1)', $boardID, "In Progress");
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, CAST(? AS CHAR CHARACTER SET utf8mb4), 2)', $boardID, "Done");
            } catch (Exception $e) {
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 0)', $boardID, "To Do");
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 1)', $boardID, "In Progress");
                $db->query('INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, 2)', $boardID, "Done");
            }
        }

        echo json_encode([
            'status' => 'success',
            'bID' => $boardID,
            'stats' => ['lists' => count($lists), 'cards' => $cardCount, 'subitems' => $subitemCount]
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
}

// ===================== Helper Functions =====================
function isSubitemRow($row, $subHeaderMap) {
    // Check if this row looks like a subitem based on content characteristics
    $name = trim($row[$subHeaderMap['Name'] ?? 1] ?? '');
    $status = trim($row[$subHeaderMap['Status'] ?? 8] ?? '');
    $ticketId = trim($row[$subHeaderMap['Ticket ID'] ?? 3] ?? '');
    
    // For Monday.com exports, subitems typically have:
    // 1. Empty first column (already validated outside)
    // 2. Valid ticket ID (starts with TICKET-)
    // 3. Status is Done, Review, New Request, etc.
    // 4. Name is not too long (allow up to 100 chars for Monday.com)
    
    if (empty($name) || $name === 'Name') return false;
    
    // Check if this has a ticket ID (strong indicator of subitem)
    if (!empty($ticketId) && strpos($ticketId, 'TICKET-') === 0) {
        return true;
    }
    
    // Check if status is subitem-like
    $validStatuses = ['done', 'pending', 'working', 'stuck', 'review', 'new request', ''];
    if (!in_array(strtolower($status), $validStatuses)) return false;
    
    // Check if name looks like a task (not a section or metadata)
    $invalidPatterns = [
        '/^all task about/i',
        '/^website tasks$/i',
        '/^templates$/i',
        '/^new request$/i',  // This is a section name, not subitem
        '/^tasks queue$/i',
        '/^completed tasks$/i',
        '/^WEB-\d+/i'  // Main tasks start with WEB-
    ];
    
    foreach ($invalidPatterns as $pattern) {
        if (preg_match($pattern, $name)) return false;
    }
    
    // Allow longer names for Monday.com subitems (up to 100 chars)
    if (strlen($name) > 100) return false;
    
    return true;
}

// ===================== XLSX Reader =====================
function readExcelFile($filePath) {
    $rows = [];

    if (!class_exists('ZipArchive')) {
        return $rows;
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== TRUE) {
        return $rows;
    }

    // Read shared strings
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml) {
        $ssDoc = simplexml_load_string($ssXml);
        if ($ssDoc) {
            foreach ($ssDoc->si as $si) {
                // Handle both simple <t> and rich text <r><t>
                $text = '';
                if (isset($si->t)) {
                    $text = (string)$si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }
    }

    // Read sheet1
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheetXml) {
        $zip->close();
        return $rows;
    }

    $sheetDoc = simplexml_load_string($sheetXml);
    if (!$sheetDoc || !isset($sheetDoc->sheetData)) {
        $zip->close();
        return $rows;
    }

    foreach ($sheetDoc->sheetData->row as $row) {
        $rowData = [];
        $maxCol = 0;

        foreach ($row->c as $cell) {
            // Parse cell reference (e.g. "A1", "B2", "AA3") to get column index
            $ref = (string)$cell['r'];
            $colLetters = preg_replace('/[0-9]/', '', $ref);
            $colIndex = 0;
            $len = strlen($colLetters);
            for ($j = 0; $j < $len; $j++) {
                $colIndex = $colIndex * 26 + (ord(strtoupper($colLetters[$j])) - 64);
            }
            $colIndex--; // 0-based

            // Fill gaps with empty strings
            while (count($rowData) < $colIndex) {
                $rowData[] = '';
            }

            // Get cell value
            $value = '';
            $type = (string)($cell['t'] ?? '');

            if ($type === 's') {
                // Shared string
                $idx = intval((string)$cell->v);
                $value = $sharedStrings[$idx] ?? '';
            } elseif ($type === 'inlineStr') {
                // Inline string
                $value = (string)($cell->is->t ?? '');
            } elseif (isset($cell->v)) {
                $value = (string)$cell->v;
            }

            $rowData[$colIndex] = $value;
            if ($colIndex > $maxCol) $maxCol = $colIndex;
        }

        // Pad row to consistent length
        while (count($rowData) <= $maxCol) {
            $rowData[] = '';
        }

        if (!empty(array_filter($rowData, function($v){ return $v !== ''; }))) {
            $rows[] = $rowData;
        }
    }

    $zip->close();
    return $rows;
}
