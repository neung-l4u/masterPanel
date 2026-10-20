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

function arayaRenderChat(array $brief): string
{
    $L = [];
    $date = arayaClean($brief['generated_for'] ?? '');

    if (!empty($brief['gcal_unavailable'])) {
        $L[] = '⚠️ ดึงข้อมูลนาทีไม่ได้ — รายงานนี้มีแต่ข้อมูลจาก Monday';
        $L[] = '';
    }

    $L[] = '📋 รายงาน AI Araya ประจำวันที่ ' . $date;

    // Overview
    $o = $brief['overview'] ?? [];
    $L[] = '';
    $L[] = '📊 ภาพรวม';
    $L[] = 'LIVE: ' . (int)($o['live'] ?? 0);
    $L[] = 'Waiting to cancel: ' . (int)($o['pendingCancel'] ?? 0);
    $L[] = 'Setup: ' . (int)($o['setup'] ?? 0);
    $L[] = 'Cancelled: ' . (int)($o['cancelled'] ?? 0)
        . ' (Canceled ' . (int)($o['canceledAfterLive'] ?? 0)
        . ', Cancel Before LIVE ' . (int)($o['cancelBeforeLive'] ?? 0) . ')';
    $L[] = 'Total: ' . (int)($o['total'] ?? 0);

    // Live shops
    if (!empty($brief['live'])) {
        $L[] = '';
        $L[] = '🟢 ร้านที่ LIVE (น้อยสุดก่อน)';
        foreach ($brief['live'] as $r) {
            if (!empty($r['gcal_missing'])) {
                $L[] = arayaChatName($r) . ' (ไม่พบใน gcal ⚠️)';
                continue;
            }
            $L[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
                . ' · ' . arayaChatLifespan($r['lifespan'] ?? null) . ')';
        }
    }

    // Upsell: always printed
    $L[] = '';
    $L[] = '💰 โอกาส Upsell';
    if (empty($brief['upsell'])) {
        $L[] = 'ไม่มีวันนี้ ✅';
    } else {
        foreach ($brief['upsell'] as $r) {
            $L[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
                . ' · รีเฟรชอีก ' . (int)($r['days_until_refresh'] ?? 0) . ' วัน)' . arayaChatOwner($r['po'] ?? '');
        }
    }
    foreach ($brief['upsellExcluded'] ?? [] as $r) {
        $L[] = arayaChatName($r) . ' (' . arayaChatMins($r['remaining_minutes'] ?? null)
            . ' · รีเฟรชอีก ' . (int)($r['days_until_refresh'] ?? 0) . ' วัน) — ไม่นับ: '
            . arayaClean($r['reason'] ?? '');
    }

    // Idle, new
    if (!empty($brief['idleNew'])) {
        $L[] = '';
        $L[] = '😴 ร้านใหม่ที่ยังใช้น้อย';
        foreach ($brief['idleNew'] as $r) {
            $L[] = arayaChatName($r) . ' (ใช้ ' . $r['used_pct'] . '% · live มา ' . (int)$r['days_live']
                . ' วัน · เหลือ ' . arayaChatMins($r['remaining_minutes'] ?? null) . ')' . arayaChatOwner($r['po'] ?? '');
        }
    }

    // Idle, quiet
    if (!empty($brief['idleQuiet'])) {
        $L[] = '';
        $L[] = '📉 ร้านเก่าที่เงียบลง';
        foreach ($brief['idleQuiet'] as $r) {
            $L[] = arayaChatName($r) . ' (รอบล่าสุดใช้ ' . $r['latest_used'] . ' นาที · เฉลี่ยก่อนหน้า '
                . round($r['prior_mean'], 1) . ')' . arayaChatOwner($r['po'] ?? '');
        }
    }

    // Setup
    if (!empty($brief['setup'])) {
        $L[] = '';
        $L[] = '🛠 Setup ที่ค้างอยู่';
        foreach ($brief['setup'] as $r) {
            $status = arayaClean($r['setup_status'] ?? '');
            if ($status === '') {
                $status = arayaClean($r['live_status'] ?? '');
            }
            $stalled = $r['days_stalled'] === null ? 'ไม่ทราบวันที่ลงทะเบียน' : 'ค้าง ' . (int)$r['days_stalled'] . ' วัน';
            $L[] = arayaChatName($r) . ' — ' . $status . ' · ' . $stalled . arayaChatOwner($r['po'] ?? '');
            $note = arayaClean($r['note'] ?? '');
            if ($note !== '') {
                $L[] = '   ' . $note;
            }
        }
    }

    // Cancelling
    if (!empty($brief['cancelling'])) {
        $L[] = '';
        $L[] = '🚪 รอยกเลิก';
        foreach ($brief['cancelling'] as $r) {
            $reason = arayaClean($r['reason'] ?? '');
            $L[] = arayaChatName($r) . ' — Waiting to cancel · ' . arayaChatLifespan($r['lifespan'] ?? null)
                . arayaChatOwner($r['po'] ?? '');
            $L[] = '   เหตุผล: ' . ($reason === '' ? 'ยังไม่ได้บันทึก' : $reason);
        }
    }

    // Data gaps
    if (!empty($brief['gaps'])) {
        $L[] = '';
        $L[] = '⚠️ ข้อมูลที่ขาด (' . count($brief['gaps']) . ')';
        foreach ($brief['gaps'] as $g) {
            $L[] = '• ' . arayaChatName($g) . ' [' . arayaClean($g['kind'] ?? '') . '] ' . arayaClean($g['detail'] ?? '');
        }
    }

    return implode("\n", $L) . "\n";
}
