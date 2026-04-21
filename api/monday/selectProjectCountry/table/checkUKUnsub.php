<?php
require_once '../../../../assets/db/db.php';
require_once '../../../../assets/db/initDB.php';

$day = date('Y-m-d');
$obj_day = new DateTime($day);
$dateIndex = $obj_day->format('w');
$endDateDiff = 6 - $dateIndex;

$obj_startDate = new DateTime($day);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d 00:00:00');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->modify("+$endDateDiff days")->format('Y-m-d 23:59:59');

// ========== อ่าน JSON ==========
$latestFile = __DIR__ . '/../file/ALL/ALL_monday_data260206-1423.json';
$jsonData = json_decode(file_get_contents($latestFile), true);

// สร้าง lookup map จาก JSON: accountName(lowercase) => status
$mondayShopStatus = [];
if (isset($jsonData['data']['items'])) {
    foreach ($jsonData['data']['items'] as $item) {
        $accountName = '';
        $activeStatus = '';
        foreach ($item['column_values'] as $col) {
            if ($col['id'] === 'text9') $accountName = $col['text'] ?? '';
            if ($col['id'] === 'status') $activeStatus = $col['text'] ?? '';
        }
        if (!empty($accountName)) {
            $mondayShopStatus[strtolower($accountName)] = $activeStatus;
        }
    }
}

// ========== ดึง UK unsub จาก DB ==========
echo "<h2>UK Unsubscribe This Week - DB vs JSON</h2>";
echo "Period: $startDate - $endDate<br><br>";

$cancellations = $db->query('SELECT id, county, shopname, timestamp FROM Cancellation WHERE county = ? AND timestamp BETWEEN ? AND ?', 'UK', $startDate, $endDate)->fetchAll();

echo "Total UK unsub in DB: <b>" . count($cancellations) . "</b><br><br>";

if (count($cancellations) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>#</th><th>ID</th><th>DB Shop Name</th><th>Timestamp</th><th>JSON Status (text9 match)</th><th>Match?</th></tr>";
    $i = 1;
    $matchCount = 0;
    foreach ($cancellations as $row) {
        $shopnameLower = strtolower($row['shopname']);
        $jsonStatus = isset($mondayShopStatus[$shopnameLower]) ? $mondayShopStatus[$shopnameLower] : 'NOT FOUND';
        $isMatch = ($jsonStatus === 'Request Cancellation' || $jsonStatus === 'Cancelled' || $jsonStatus === 'Cancelled Subscription');
        if ($isMatch) $matchCount++;
        
        $bgColor = $isMatch ? '#90EE90' : ($jsonStatus === 'NOT FOUND' ? '#FFB3B3' : '#FFFFB3');
        
        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>$i</td>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['shopname']}</td>";
        echo "<td>{$row['timestamp']}</td>";
        echo "<td>$jsonStatus</td>";
        echo "<td>" . ($isMatch ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";
    echo "<br><b>Match count (= Unsubscribe): $matchCount / " . count($cancellations) . "</b>";
}

echo "<br><br><small>สีเขียว = match, สีเหลือง = พบใน JSON แต่ status ไม่ตรง, สีแดง = ไม่พบใน JSON</small>";
