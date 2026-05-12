<?php
/**
 * checkAvailability.php
 * เช็คจำนวน booking ในวันหนึ่งๆ จาก Google Calendar public ICS feed
 * Input  (GET): date = YYYY-MM-DD
 * Output (JSON): { date, booked, max, available, bookedTimes[] }
 */
header('Content-Type: application/json');
date_default_timezone_set('Asia/Bangkok');

// ========== CONFIG ==========
$MAX_SLOTS  = 2;                      // จำนวนสล็อตสูงสุดต่อวัน (ตัวแปรปรับได้)
$ALLOW_MIN  = '2026-06-01';           // จองได้เฉพาะเดือน June 2026
$ALLOW_MAX  = '2026-06-30';
$HOUR_MIN   = 6;                      // 06:00 BKK
$HOUR_MAX   = 18;                     // 18:00 BKK
// calendar ต้องเปิด public share (Settings > Access permissions > Make available to public)
$ICS_URL    = 'https://calendar.google.com/calendar/ical/bookings%40localforyou.com/public/basic.ics';
$CACHE_FILE = sys_get_temp_dir() . '/l4u_formASAP_ics_cache.txt';
$CACHE_TTL  = 60; // วินาที
// ============================

$date = isset($_GET['date']) ? trim($_GET['date']) : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['error' => 'Invalid date format (expected YYYY-MM-DD)']);
    exit;
}
if ($date < $ALLOW_MIN || $date > $ALLOW_MAX) {
    echo json_encode(['error' => 'Booking available only between ' . $ALLOW_MIN . ' and ' . $ALLOW_MAX]);
    exit;
}

// ห้ามเลือกวันในอดีต (BKK)
$todayBkk = date('Y-m-d');
if ($date < $todayBkk) {
    echo json_encode(['error' => 'Cannot book past dates']);
    exit;
}

$debug = !empty($_GET['debug']);

// ---- Fetch ICS (พร้อม cache, ใช้ cURL) ----
$ics = null;
$fetchInfo = null;
$fromCache = false;
if (!$debug && file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE)) < $CACHE_TTL) {
    $ics = file_get_contents($CACHE_FILE);
    $fromCache = true;
} else {
    if (function_exists('curl_init')) {
        $ch = curl_init($ICS_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'L4U-FormASAP/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);
        $fetchInfo = ['method' => 'curl', 'httpCode' => $httpCode, 'error' => $curlErr, 'size' => is_string($body) ? strlen($body) : 0];
        if ($body !== false && $httpCode === 200 && strlen($body) > 0) {
            $ics = $body;
            @file_put_contents($CACHE_FILE, $ics);
        }
    } else {
        $ctx = stream_context_create([
            'http'  => ['timeout' => 10, 'header' => "User-Agent: L4U-FormASAP/1.0\r\n"],
            'https' => ['timeout' => 10, 'header' => "User-Agent: L4U-FormASAP/1.0\r\n"],
        ]);
        $body = @file_get_contents($ICS_URL, false, $ctx);
        $fetchInfo = ['method' => 'file_get_contents', 'size' => is_string($body) ? strlen($body) : 0, 'allowUrlFopen' => (bool)ini_get('allow_url_fopen')];
        if ($body !== false && strlen($body) > 0) {
            $ics = $body;
            @file_put_contents($CACHE_FILE, $ics);
        }
    }
}

if (!$ics) {
    echo json_encode([
        'error'   => 'Cannot fetch calendar. Ensure calendar is shared publicly (See all event details).',
        'date'    => $date,
        'booked'  => 0,
        'max'     => $MAX_SLOTS,
        'available' => true,
        'fetchInfo' => $fetchInfo,
        'icsUrl'    => $ICS_URL,
    ]);
    exit;
}

// ---- Unfold ICS lines (ตาม RFC 5545 บรรทัดต่อขึ้นต้นด้วย space/tab) ----
$ics = preg_replace("/\r?\n[ \t]/", '', $ics);

// ---- แยก VEVENT ----
preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $matches);
$events = $matches[1] ?? [];

$tzBkk  = new DateTimeZone('Asia/Bangkok');
$booked = 0;
$bookedTimes  = [];
$bookedRanges = []; // [{start:"HH:MM", end:"HH:MM"}] — ใช้ตรวจเวลาทับ

// helper: parse ICS datetime ให้เป็น DateTime (Asia/Bangkok)
$parseIcsDt = function($params, $rawDate) use ($tzBkk) {
    if (stripos($params, 'VALUE=DATE') !== false || preg_match('/^\d{8}$/', $rawDate)) {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $rawDate, $pm)) {
            $d = DateTime::createFromFormat('Ymd His', $pm[1].$pm[2].$pm[3].' 000000', $tzBkk);
            return ['dt' => $d, 'allDay' => true];
        }
        return null;
    }
    if (!preg_match('/^(\d{8})T(\d{6})(Z?)/', $rawDate, $pm)) return null;
    $isUtc = $pm[3] === 'Z';
    $tz = $tzBkk;
    if ($isUtc) $tz = new DateTimeZone('UTC');
    elseif (preg_match('/TZID=([^;:]+)/', $params, $tm)) {
        try { $tz = new DateTimeZone($tm[1]); } catch (Exception $e) {}
    }
    $d = DateTime::createFromFormat('Ymd His', $pm[1].' '.$pm[2], $tz);
    if (!$d) return null;
    $d->setTimezone($tzBkk);
    return ['dt' => $d, 'allDay' => false];
};

foreach ($events as $ev) {
    if (!preg_match('/DTSTART([^:\r\n]*):([^\r\n]+)/', $ev, $dtm)) continue;
    $startInfo = $parseIcsDt(trim($dtm[1]), trim($dtm[2]));
    if (!$startInfo) continue;

    // DTEND (optional) — ถ้าไม่มี ถือว่าเริ่ม + 1:30 ชม.
    $endInfo = null;
    if (preg_match('/DTEND([^:\r\n]*):([^\r\n]+)/', $ev, $dem)) {
        $endInfo = $parseIcsDt(trim($dem[1]), trim($dem[2]));
    }

    $startDt = $startInfo['dt'];
    $endDt   = $endInfo ? $endInfo['dt'] : (clone $startDt)->modify('+90 minutes');

    // all-day → จองทั้งวัน
    if ($startInfo['allDay']) {
        if ($startDt->format('Y-m-d') === $date) {
            $booked++;
            $bookedTimes[]  = 'all-day';
            $bookedRanges[] = ['start' => '00:00', 'end' => '23:59', 'allDay' => true];
        }
        continue;
    }

    // นับถ้าเริ่มในวันที่เลือก (Asia/Bangkok)
    if ($startDt->format('Y-m-d') === $date) {
        $booked++;
        $bookedTimes[]  = $startDt->format('H:i');
        $bookedRanges[] = [
            'start' => $startDt->format('H:i'),
            'end'   => $endDt->format('H:i'),
        ];
    }
}

$resp = [
    'date'         => $date,
    'booked'       => $booked,
    'max'          => $MAX_SLOTS,
    'available'    => $booked < $MAX_SLOTS,
    'bookedTimes'  => $bookedTimes,
    'bookedRanges' => $bookedRanges,
    'hourMin'      => $HOUR_MIN,
    'hourMax'      => $HOUR_MAX,
];

if ($debug) {
    $resp['debug'] = [
        'fromCache'    => $fromCache,
        'fetchInfo'    => $fetchInfo,
        'icsUrl'       => $ICS_URL,
        'icsSize'      => strlen($ics),
        'eventCount'   => count($events),
        'icsFirst500'  => substr($ics, 0, 500),
    ];
}

echo json_encode($resp);
