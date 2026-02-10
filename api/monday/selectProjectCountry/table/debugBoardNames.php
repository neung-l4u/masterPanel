<?php
// Debug: แสดงชื่อ board ทั้งหมดใน JSON

$jsonDir = __DIR__ . '/../file/ALL/';
$files = glob($jsonDir . 'ALL_monday_data*.json');

if (empty($files)) {
    die("ไม่พบไฟล์ JSON");
}

usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
$jsonData = json_decode(file_get_contents($latestFile), true);

echo "<h3>ไฟล์: " . basename($latestFile) . "</h3>";
echo "<h3>ชื่อ Board ทั้งหมดใน JSON:</h3>";
echo "<ul>";
foreach ($jsonData['data']['boards'] as $board) {
    echo "<li><b>" . $board['name'] . "</b></li>";
}
echo "</ul>";
?>
