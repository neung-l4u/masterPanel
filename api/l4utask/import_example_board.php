<?php
/**
 * Import "Website Tasks" CSV data into an example L4U Task Board.
 * Run this script once via browser or CLI to create the example board.
 * 
 * Usage: php import_example_board.php
 *   OR visit: http://localhost/masterPanelDemo/api/l4utask/import_example_board.php
 */

session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

header('Content-Type: text/html; charset=utf-8');

$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 1); // fallback to ID 1

$csvFile = '/Users/peeraphatmalimongkhon/Downloads/Website_Tasks_1774433755/website tasks-Table 1.csv';
if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile");
}

echo "<h2>Importing Website Tasks Example Board...</h2><pre>";

// ===================== 1. CREATE BOARD =====================
$boardName = 'Website Tasks (Example)';
$boardColor = '#0079BF';

$db->query(
    'INSERT INTO l4utask_boards (bName, bDescription, bColor, bCreatedBy) VALUES (?, ?, ?, ?)',
    $boardName, 'Imported from Monday.com CSV data', $boardColor, $myID
);
$boardID = $db->lastInsertID();
echo "Created board: $boardName (ID: $boardID)\n";

// Add creator as admin
$db->query('INSERT IGNORE INTO l4utask_board_members (bID, sID, bmRole) VALUES (?, ?, 1)', $boardID, $myID);

// ===================== 2. PARSE CSV =====================
$handle = fopen($csvFile, 'r');
if (!$handle) die("Cannot open CSV file");

// Read all lines to handle multiline quoted fields properly
$allRows = [];
while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
    $allRows[] = $row;
}
fclose($handle);

echo "Total CSV rows: " . count($allRows) . "\n";

// ===================== 3. IDENTIFY SECTIONS =====================
// Sections are identified by rows where col[0] matches section names
// and the NEXT non-empty row is a header row starting with "Name"
$sections = [];
$currentSection = null;
$inSubitems = false;
$currentCardID = null;
$headerMap = [];
$subHeaderMap = [];

// Priority map from text to numeric
$priorityMap = [
    'critical' => 4,
    'urgent'   => 4,
    'high'     => 3,
    'medium'   => 2,
    'normal'   => 2,
    'low'      => 1,
    ''         => 0
];

// Status map from CSV status to cStatus (1=active)
$statusMap = [
    'done'           => 'Done',
    'draft'          => 'Draft',
    'working on it'  => 'Working',
    'wait for info'  => 'Wait for info',
    'review'         => 'Review',
    'queue to go live'=> 'Queue to go live',
    'need fix'       => 'Need Fix',
    'stuck'          => 'Stuck',
];

// Known section names
$sectionNames = ['Templates', 'New Request', 'Tasks Queue', 'Completed Tasks'];
$lists = []; // sectionName => lID
$cardCount = 0;
$subitemCount = 0;
$maxCardsPerSection = 15; // limit per section for manageable import
$sectionCardCounts = [];

// Create lists for known sections + extras
$listPosition = 0;
foreach ($sectionNames as $sName) {
    $db->query(
        'INSERT INTO l4utask_lists (bID, lName, lPosition) VALUES (?, ?, ?)',
        $boardID, $sName, $listPosition++
    );
    $lists[$sName] = $db->lastInsertID();
    $sectionCardCounts[$sName] = 0;
    echo "Created list: $sName (ID: {$lists[$sName]})\n";
}

// ===================== 4. PROCESS ROWS =====================
$i = 0;
$totalRows = count($allRows);

while ($i < $totalRows) {
    $row = $allRows[$i];
    $col0 = trim($row[0] ?? '');

    // Check if this is a section header
    if (in_array($col0, $sectionNames)) {
        $currentSection = $col0;
        $inSubitems = false;
        $i++;
        // Skip the column header row (starts with "Name")
        if ($i < $totalRows && trim($allRows[$i][0] ?? '') === 'Name') {
            // Build header map
            $headerMap = [];
            foreach ($allRows[$i] as $ci => $colName) {
                $headerMap[trim($colName)] = $ci;
            }
            $i++;
        }
        continue;
    }

    // Check if this is a Subitems header row
    if ($col0 === 'Subitems') {
        $inSubitems = true;
        // Build subitem header map
        $subHeaderMap = [];
        foreach ($row as $ci => $colName) {
            $subHeaderMap[trim($colName)] = $ci;
        }
        $i++;
        continue;
    }

    // Skip empty rows or metadata rows
    if ($col0 === '' && !$inSubitems) {
        // Could be a blank separator or description continuation
        $inSubitems = false;
        $i++;
        continue;
    }

    // Skip if no current section
    if (!$currentSection || !isset($lists[$currentSection])) {
        $i++;
        continue;
    }

    // ---- SUBITEM ROW ----
    if ($inSubitems && $currentCardID) {
        $siName = trim($row[$subHeaderMap['Name'] ?? 1] ?? '');
        if ($siName && $siName !== 'Name') {
            $siStatus = trim($row[$subHeaderMap['Status'] ?? 8] ?? 'Pending');
            $siDue = trim($row[$subHeaderMap['Due Date'] ?? 2] ?? '');
            $siCompleted = trim($row[$subHeaderMap['Completion Date'] ?? 3] ?? '');
            $siPriority = trim($row[$subHeaderMap['Priority'] ?? 5] ?? '');

            // Map status
            if (strtolower($siStatus) === 'done') $siStatus = 'Done';
            elseif ($siStatus === '') $siStatus = 'Pending';

            $db->query(
                'INSERT INTO l4utask_card_subitems (cID, siTitle, siStatus, siPriority, siDueDate, siCompletedAt, siPosition, siCreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                $currentCardID,
                $siName,
                $siStatus,
                $siPriority ?: null,
                $siDue ?: null,
                $siCompleted ?: null,
                $subitemCount,
                $myID
            );
            $subitemCount++;
        }
        $i++;
        continue;
    }

    // ---- MAIN TASK ROW ----
    $inSubitems = false;

    // Check card limit per section
    if ($sectionCardCounts[$currentSection] >= $maxCardsPerSection) {
        $i++;
        continue;
    }

    $name = trim($row[$headerMap['Name'] ?? 0] ?? '');
    if (!$name || $name === 'Name' || strlen($name) < 3) {
        $i++;
        continue;
    }

    // Skip if it looks like another section header or metadata
    if (in_array($name, $sectionNames) || $name === 'Website Tasks' || $name === 'all task about website will place here.') {
        $i++;
        continue;
    }

    $description = trim($row[$headerMap['Description'] ?? 11] ?? '');
    $dueDate     = trim($row[$headerMap['Due Date'] ?? 2] ?? '');
    $priority    = strtolower(trim($row[$headerMap['Priority'] ?? 8] ?? ''));
    $status      = trim($row[$headerMap['Status'] ?? 7] ?? '');

    $priNum = $priorityMap[$priority] ?? 0;

    // Validate date format
    if ($dueDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $dueDate = null;
    }

    $lID = $lists[$currentSection];
    $cardPosition = $sectionCardCounts[$currentSection];

    $db->query(
        'INSERT INTO l4utask_cards (lID, bID, cTitle, cDescription, cPosition, cPriority, cDueDate, cCreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        $lID, $boardID, $name, $description ?: null, $cardPosition, $priNum, $dueDate ?: null, $myID
    );
    $currentCardID = $db->lastInsertID();
    $cardCount++;
    $sectionCardCounts[$currentSection]++;

    $i++;
}

echo "\n--- Import Summary ---\n";
echo "Board ID: $boardID\n";
echo "Lists created: " . count($lists) . "\n";
echo "Cards created: $cardCount\n";
echo "Sub-items created: $subitemCount\n";
foreach ($sectionCardCounts as $sec => $cnt) {
    echo "  $sec: $cnt cards\n";
}
echo "\n</pre>";
echo '<p><a href="../../main.php?p=l4utaskBoard&bID=' . $boardID . '">Go to imported board &rarr;</a></p>';
