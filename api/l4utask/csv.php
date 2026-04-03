<?php
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

header('Content-Type: application/json');

$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
if (!$myID) { echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']); exit; }

$act = $_POST['act'] ?? $_GET['act'] ?? '';

switch ($act) {
    case 'uploadCSV':
        $bID = intval($_POST['bID'] ?? 0);
        if (!$bID || !isset($_FILES['csvFiles'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid request']);
            exit;
        }

        $uploadedFiles = [];
        $errors = [];

        // Create uploads directory if it doesn't exist
        $uploadDir = '../../assets/uploads/excel/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle multiple file uploads
        foreach ($_FILES['csvFiles']['name'] as $key => $filename) {
            if ($_FILES['csvFiles']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading file: $filename (Error code: " . $_FILES['csvFiles']['error'][$key] . ")";
                continue;
            }

            $fileTmpPath = $_FILES['csvFiles']['tmp_name'][$key];
            $fileSize = $_FILES['csvFiles']['size'][$key];
            $fileType = $_FILES['csvFiles']['type'][$key];

            // Validate file type - Support both CSV and Excel files
            $allowedTypes = [
                'text/csv', 'application/csv', 'text/plain',  // CSV
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // Excel
            ];
            $allowedExtensions = ['csv', 'xls', 'xlsx'];
            $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($fileType, $allowedTypes) && !in_array($fileExt, $allowedExtensions)) {
                $errors[] = "Invalid file type: $filename. Only CSV and Excel files (.csv, .xls, .xlsx) are allowed.";
                continue;
            }

            // Generate unique filename
            $uniqueFileName = 'excel_' . $bID . '_' . $myID . '_' . time() . '_' . uniqid() . '.' . $fileExt;
            $destPath = $uploadDir . $uniqueFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Insert into database
                $db->query(
                    'INSERT INTO l4utask_csv_uploads (bID, sID, csvFileName, csvOriginalName, csvFilePath, csvSize, csvStatus) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    $bID, $myID, $uniqueFileName, $filename, $destPath, $fileSize, 'uploading'
                );
                $csvID = $db->lastInsertID();

                $uploadedFiles[] = [
                    'csvID' => $csvID,
                    'originalName' => $filename,
                    'size' => $fileSize,
                    'status' => 'uploading'
                ];

                // Start processing the Excel/CSV file in background
                processExcelFile($csvID, $destPath, $bID, $myID);
            } else {
                $errors[] = "Failed to move uploaded file: $filename";
            }
        }

        echo json_encode([
            'status' => 'success',
            'uploadedFiles' => $uploadedFiles,
            'errors' => $errors,
            'totalUploaded' => count($uploadedFiles),
            'totalErrors' => count($errors)
        ]);
        break;

    case 'getUploadStatus':
        $bID = intval($_GET['bID'] ?? 0);
        if (!$bID) {
            echo json_encode(['status' => 'error', 'msg' => 'Board ID required']);
            exit;
        }

        $uploads = $db->query(
            'SELECT * FROM l4utask_csv_uploads WHERE bID = ? ORDER BY csvCreatedAt DESC',
            $bID
        )->fetchAll();

        echo json_encode(['status' => 'success', 'data' => $uploads]);
        break;

    case 'deleteUpload':
        $csvID = intval($_POST['csvID'] ?? 0);
        if (!$csvID) {
            echo json_encode(['status' => 'error', 'msg' => 'Upload ID required']);
            exit;
        }

        $upload = $db->query('SELECT * FROM l4utask_csv_uploads WHERE csvID = ? AND sID = ?', $csvID, $myID)->fetchArray();
        if (!$upload) {
            echo json_encode(['status' => 'error', 'msg' => 'Upload not found']);
            exit;
        }

        // Delete file
        if (file_exists($upload['csvFilePath'])) {
            unlink($upload['csvFilePath']);
        }

        // Delete database record
        $db->query('DELETE FROM l4utask_csv_uploads WHERE csvID = ?', $csvID);

        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
}

function processExcelFile($csvID, $filePath, $bID, $myID) {
    // Update status to processing
    global $db;
    $db->query('UPDATE l4utask_csv_uploads SET csvStatus = ? WHERE csvID = ?', 'processing', $csvID);

    try {
        $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rows = [];
        $headers = [];

        if ($fileExt === 'csv') {
            // Handle CSV files
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new Exception('Cannot open CSV file');
            }

            $headerRow = fgetcsv($handle);
            if (!$headerRow) {
                throw new Exception('Cannot read CSV headers');
            }
            $headers = array_map(function($h) { return strtolower(trim($h)); }, $headerRow);

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        } else {
            // Handle Excel files using simple method (without PhpSpreadsheet for now)
            // Convert Excel to CSV using PHPExcel reader or simple method
            if (!function_exists('simplexml_load_file') && !class_exists('ZipArchive')) {
                throw new Exception('Excel support requires ZipArchive extension. Please upload CSV file instead.');
            }

            // For now, we'll use a simple approach - this is a basic Excel reader
            $rows = readSimpleExcel($filePath);
            if (empty($rows)) {
                throw new Exception('Cannot read Excel file or file is empty');
            }
            
            $headerRow = array_shift($rows);
            $headers = array_map(function($h) { return strtolower(trim($h)); }, $headerRow);
        }

        // Expected columns: title, description, list, priority, due_date, stage, assignee
        $requiredColumns = ['title'];
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $headers)) {
                throw new Exception("Missing required column: $col");
            }
        }

        $recordsProcessed = 0;
        $recordsTotal = count($rows);
        $errors = [];

        // Get or create lists
        $lists = $db->query('SELECT lID, lName FROM l4utask_lists WHERE bID = ? AND lDeletedAt IS NULL', $bID)->fetchAll();
        $listMap = [];
        foreach ($lists as $list) {
            $listMap[strtolower(trim($list['lName']))] = $list['lID'];
        }

        // Get staff members for assignee mapping
        $staffs = $db->query('SELECT sID, sNickName FROM staffs WHERE sStatus = 1')->fetchAll();
        $staffMap = [];
        foreach ($staffs as $staff) {
            $staffMap[strtolower(trim($staff['sNickName']))] = $staff['sID'];
        }

        // Process rows
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Excel row numbers start from 2 (after header)
            $data = array_combine($headers, $row);
            
            try {
                // Validate required fields
                if (empty(trim($data['title']))) {
                    $errors[] = "Row $rowNum: Title is required";
                    continue;
                }

                // Find or create list
                $listName = trim($data['list'] ?? 'Default');
                $listID = $listMap[strtolower($listName)] ?? null;
                
                if (!$listID) {
                    // Create new list
                    $maxPos = $db->query('SELECT IFNULL(MAX(lPosition),0)+1 AS pos FROM l4utask_lists WHERE bID = ?', $bID)->fetchArray();
                    $db->query(
                        'INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)',
                        $bID, $listName, $maxPos['pos']
                    );
                    $listID = $db->lastInsertID();
                    $listMap[strtolower($listName)] = $listID;
                }

                // Get max position for cards in this list
                $maxCardPos = $db->query('SELECT IFNULL(MAX(cPosition),0)+1 AS pos FROM l4utask_cards WHERE lID = ? AND cDeletedAt IS NULL', $listID)->fetchArray();

                // Parse priority
                $priorityMap = ['none' => 0, 'low' => 1, 'medium' => 2, 'high' => 3, 'urgent' => 4];
                $priority = $priorityMap[strtolower(trim($data['priority'] ?? 'none'))] ?? 0;

                // Parse due date
                $dueDate = null;
                if (!empty($data['due_date'])) {
                    $dueDate = date('Y-m-d', strtotime($data['due_date']));
                    if ($dueDate === false) $dueDate = null;
                }

                // Parse stage
                $stage = trim($data['stage'] ?? 'Draft');

                // Create card
                $db->query(
                    'INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPriority, cDueDate, cStage, cPosition, cCreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    $listID, $bID, trim($data['title']), trim($data['description'] ?? ''), $priority, $dueDate, $stage, $maxCardPos['pos'], $myID
                );
                $cID = $db->lastInsertID();

                // Handle assignee
                if (!empty($data['assignee'])) {
                    $assigneeName = strtolower(trim($data['assignee']));
                    if (isset($staffMap[$assigneeName])) {
                        $db->query('INSERT IGNORE INTO l4utask_card_members (cID, sID) VALUES (?, ?)', $cID, $staffMap[$assigneeName]);
                    }
                }

                $recordsProcessed++;
            } catch (Exception $e) {
                $errors[] = "Row $rowNum: " . $e->getMessage();
            }
        }

        // Update final status
        $errorText = count($errors) > 0 ? implode("\n", array_slice($errors, 0, 10)) : null;
        $finalStatus = (count($errors) > 0 && $recordsProcessed === 0) ? 'failed' : 'completed';
        
        $db->query(
            'UPDATE l4utask_csv_uploads SET csvStatus = ?, csvRecordsProcessed = ?, csvRecordsTotal = ?, csvErrors = ?, csvProcessedAt = NOW() WHERE csvID = ?',
            $finalStatus, $recordsProcessed, $recordsTotal, $errorText, $csvID
        );

    } catch (Exception $e) {
        $db->query(
            'UPDATE l4utask_csv_uploads SET csvStatus = ?, csvErrors = ?, csvProcessedAt = NOW() WHERE csvID = ?',
            'failed', $e->getMessage(), $csvID
        );
    }
}

// Simple Excel reader function (for XLSX files)
function readSimpleExcel($filePath) {
    $rows = [];
    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    if ($fileExt === 'xlsx') {
        // Read XLSX file
        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml) {
                $xml = simplexml_load_string($sheetXml);
                $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
                $sharedStrings = [];
                if ($sharedStringsXml) {
                    $sharedStringsXml = simplexml_load_string($sharedStringsXml);
                    foreach ($sharedStringsXml->si as $si) {
                        $sharedStrings[] = (string)$si->t;
                    }
                }
                
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    foreach ($row->c as $cell) {
                        $value = (string)$cell->v;
                        if (isset($cell['t']) && $cell['t'] == 's') {
                            $value = $sharedStrings[intval($value)] ?? '';
                        }
                        $rowData[] = $value;
                    }
                    if (!empty($rowData)) {
                        $rows[] = $rowData;
                    }
                }
            }
            $zip->close();
        }
    }
    
    return $rows;
}
