<?php
/**
 * Renders the arayaBuildBrief() output as the plain-text Google Chat message.
 *
 * Plain text, not a card: long cards get truncated by Chat and card text cannot be
 * copied out. Deferred sections (revenue, per-shop call quality, Respond.io
 * ghosting, Stripe status, contract expiry) emit no heading at all.
 */
require_once __DIR__ . '/buildBrief.php';

/** Minutes keep their sign; null means gcal had no figure. */
function arayaChatMins($v): string
{
    if ($v === null) {
        return '? mins';
    }
    return (is_float($v) ? rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.') : (string)$v) . ' mins';
}

/** "4 ด. 19 วัน", or the unrecorded marker when there is no usable live date. */
function arayaChatLifespan($lifespan): string
{
    if (!is_array($lifespan)) {
        return 'ยังไม่ได้บันทึก live date';
    }
    return (int)$lifespan['months'] . ' ด. ' . (int)$lifespan['days'] . ' วัน';
}

function arayaChatOwner($po): string
{
    $po = arayaClean($po);
    return $po === '' ? '' : ' · @' . $po;
}

function arayaChatName($row): string
{
    $n = arayaClean($row['name'] ?? '');
    return $n === '' ? '(unnamed shop)' : $n;
}

const ARAYA_CHAT_SEP = '━━━━━━━━━━━━━━━━━━━━━━━━';

/** "2026-10-20" -> "20 Oct 2026"; anything unparseable is shown as given. */
function arayaChatDate(string $ymd): string
{
    $d = DateTime::createFromFormat('!Y-m-d', $ymd);
    return ($d && $d->format('Y-m-d') === $ymd) ? $d->format('j M Y') : $ymd;
}

/** Thai gap sentences by kind; an unknown kind falls back to the English detail. */
function arayaChatGapDetail($g): string
{
    static $th = [
        'unmatched_monday'     => 'อยู่ใน Monday แต่หาร้านที่ตรงกันใน gcal ไม่พบ จึงไม่ทราบนาทีคงเหลือ',
        'unmatched_gcal'       => 'มีอยู่ใน gcal แต่ไม่มี item ใน Monday board',
        'no_live_date'         => 'ยังไม่มีวัน live จึงคำนวณอายุการใช้งานและเช็กกฎร้านเงียบไม่ได้',
        'no_billing_day'       => 'ยังไม่ได้ตั้งวันบิล จึงเช็กกฎอัพเซลล์ไม่ได้',
        'no_allocation'        => 'ไม่ทราบจำนวนนาทีที่ได้รับ หรือนาทีคงเหลือมากกว่าที่ได้รับ จึงใช้เปอร์เซ็นต์การใช้งานไม่ได้',
        'no_register_date'     => 'ยังไม่มีวันลงทะเบียน จึงคำนวณจำนวนวันที่ค้างไม่ได้',
        'blank_status'         => 'ช่อง Live Status ใน Monday ว่างอยู่',
        'unknown_status'       => 'ช่อง Live Status มีค่าที่ระบบไม่รู้จัก',
        'no_remaining_minutes' => 'gcal ไม่มีข้อมูลนาทีคงเหลือ จึงเช็กอัพเซลล์และร้านเงียบไม่ได้',
    ];
    $kind = arayaClean($g['kind'] ?? '');
    return $th[$kind] ?? arayaClean($g['detail'] ?? '');
}

function arayaRenderChat(array $brief): string
{
    $L = [];
    $S = []; // sections, joined by separator rules
    $date = arayaChatDate(arayaClean($brief['generated_for'] ?? ''));

    if (!empty($brief['gcal_unavailable'])) {
        $L[] = '⚠️ ดึงข้อมูลนาทีไม่ได้ — รายงานนี้มีแต่ข้อมูลจาก Monday';
        $L[] = '';
    }

    $L[] = '☀️ ARAYA DAILY BRIEF — ' . $date . ' (07:00)';
    $L[] = ARAYA_CHAT_SEP;
    $L[] = '';

    // Overview
    $o = $brief['overview'] ?? [];
    $S[] = [
        '📊 OVERVIEW',
        'LIVE: ' . (int)($o['live'] ?? 0) . ' ร้าน',
        'แจ้งยกเลิก (pending): ' . (int)($o['pendingCancel'] ?? 0) . ' ร้าน',
        'Setup ค้างอยู่: ' . (int)($o['setup'] ?? 0) . ' ร้าน',
        'ยกเลิกแล้ว: ' . (int)($o['cancelled'] ?? 0) . ' ร้าน (Cancel Before LIVE '
            . (int)($o['cancelBeforeLive'] ?? 0) . ' · Canceled ' . (int)($o['canceledAfterLive'] ?? 0) . ')',
        'รวมลูกค้าทั้งหมด: ' . (int)($o['total'] ?? 0) . ' ร้าน',
    ];

    // Live shops
    if (!empty($brief['live'])) {
        $B = ['✅ ร้านที่ LIVE (นาทีคงเหลือ · อยู่กับเรามานาน)'];
        foreach ($brief['live'] as $r) {
            if (!empty($r['gcal_missing'])) {
                $B[] = arayaChatName($r) . ' (ไม่พบใน gcal ⚠️)';
                continue;
            }
            $B[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
                . ' · ' . arayaChatLifespan($r['lifespan'] ?? null) . ')';
        }
        $S[] = $B;
    }

    // Upsell: always printed
    $B = ['💰 โอกาสอัพเซลล์ (นาที <70 และรีเฟรชอีก ≤15 วัน)'];
    if (empty($brief['upsell'])) {
        $B[] = 'ไม่มีวันนี้ ✅';
    } else {
        foreach ($brief['upsell'] as $r) {
            $B[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
                . ' · รีเฟรชอีก ' . (int)($r['days_until_refresh'] ?? 0) . ' วัน)' . arayaChatOwner($r['po'] ?? '');
        }
    }
    foreach ($brief['upsellExcluded'] ?? [] as $r) {
        $B[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
            . ' · รีเฟรชอีก ' . (int)($r['days_until_refresh'] ?? 0) . ' วัน) — ไม่นับ: '
            . arayaClean($r['reason'] ?? '');
    }
    $S[] = $B;

    // Idle, new
    if (!empty($brief['idleNew'])) {
        $B = ['📉 ไลฟ์แล้วแต่ไม่มีการใช้งาน'];
        foreach ($brief['idleNew'] as $r) {
            $B[] = arayaChatName($r) . ' (ใช้ ' . $r['used_pct'] . '% · live มา ' . (int)$r['days_live']
                . ' วัน · เหลือ ' . arayaChatMins($r['remaining_minutes'] ?? null) . ')' . arayaChatOwner($r['po'] ?? '');
        }
        $S[] = $B;
    }

    // Idle, quiet
    if (!empty($brief['idleQuiet'])) {
        $B = ['📉 เคยใช้แล้วเงียบไป'];
        foreach ($brief['idleQuiet'] as $r) {
            $B[] = arayaChatName($r) . ' (รอบล่าสุดใช้ ' . $r['latest_used'] . ' นาที · เฉลี่ยก่อนหน้า '
                . round($r['prior_mean'], 1) . ')' . arayaChatOwner($r['po'] ?? '');
        }
        $S[] = $B;
    }

    // Setup
    if (!empty($brief['setup'])) {
        $B = ['🚧 ทำไมยังไม่ไลฟ์ (Setup ค้าง)'];
        foreach ($brief['setup'] as $r) {
            $status = arayaClean($r['setup_status'] ?? '');
            if ($status === '') {
                $status = arayaClean($r['live_status'] ?? '');
            }
            $stalled = $r['days_stalled'] === null ? 'ไม่ทราบวันที่ลงทะเบียน' : 'ค้าง ' . (int)$r['days_stalled'] . ' วัน';
            $B[] = arayaChatName($r) . ' — ' . $status . ' · ' . $stalled . arayaChatOwner($r['po'] ?? '');
            $note = arayaClean($r['note'] ?? '');
            if ($note !== '') {
                $B[] = '   ' . $note;
            }
        }
        $S[] = $B;
    }

    // Cancelling
    if (!empty($brief['cancelling'])) {
        $B = ['❌ แจ้งยกเลิก'];
        foreach ($brief['cancelling'] as $r) {
            $reason = arayaClean($r['reason'] ?? '');
            $B[] = arayaChatName($r) . ' — Waiting to cancel · ' . arayaChatLifespan($r['lifespan'] ?? null)
                . arayaChatOwner($r['po'] ?? '');
            $B[] = '   เหตุผล: ' . ($reason === '' ? 'ยังไม่ได้บันทึก' : $reason);
        }
        $S[] = $B;
    }

    // Data gaps
    if (!empty($brief['gaps'])) {
        $B = ['⚠️ ข้อมูลไม่ครบ'];
        foreach ($brief['gaps'] as $g) {
            $B[] = '• ' . arayaChatName($g) . ' [' . arayaClean($g['kind'] ?? '') . '] ' . arayaChatGapDetail($g);
        }
        $S[] = $B;
    }

    foreach ($S as $i => $B) {
        if ($i > 0) {
            $L[] = '';
            $L[] = ARAYA_CHAT_SEP;
            $L[] = '';
        }
        foreach ($B as $line) {
            $L[] = $line;
        }
    }

    return implode("\n", $L) . "\n";
}
