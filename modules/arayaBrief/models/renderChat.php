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

/**
 * Why a shop that met the numeric upsell test is not being pitched, in Thai.
 *
 * buildBrief.php composes this reason in English and 100 tests pin those
 * strings, so the translation lives here instead -- the same split used for gap
 * details. This line is the one a person acts on, so it has to read in their
 * language; the board status stays in it verbatim, since that is what they will
 * look up on Monday.
 */
function arayaChatExcludedReason($row): string
{
    $reason = arayaClean($row['reason'] ?? '');
    $status = arayaClean($row['live_status'] ?? '');

    // Anchored to the whole known sentence on purpose. A partial match (any
    // quoted word) would state something untrue in Thai for a future reason such
    // as 'Plan "Enterprise" is excluded', and a confident untruth in a line
    // someone acts on is worse than leaving the English in place.
    if ($status === '' && preg_match('/^Status is "([^"]+)"; the shop has given notice\.$/u', $reason, $m) === 1) {
        $status = $m[1];
    }
    if ($status !== '') {
        return 'สถานะ "' . $status . '" — ร้านแจ้งยกเลิกแล้ว';
    }
    // Anything else degrades to the English reason rather than a guess.
    return $reason;
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

/**
 * Thai gap sentences by kind; an unknown kind falls back to the English detail.
 *
 * The values the team needs (which status, which numbers) live only in the
 * English detail that buildBrief.php composes, so they are lifted out with one
 * narrowly anchored pattern per kind. When a pattern misses, the English detail
 * is appended in parentheses rather than losing the value.
 */
function arayaChatGapDetail($g): string
{
    // Written for whoever has to act, not for whoever wrote the rules: name
    // the system, the field and the change. The reader owns the Monday board,
    // not this code, so "the matcher could not pair them" tells them nothing
    // they can do.
    static $th = [
        'unmatched_gcal'       => 'มีร้านนี้ใน gcal แต่ยังไม่มีใน Monday board → เพิ่ม item ในบอร์ด (ถ้าเป็นร้านเทสต์หรือเลิกใช้แล้ว ข้ามได้)',
        'no_billing_day'       => 'ยังไม่ได้ตั้ง billing day ใน gcal → ตั้งวันตัดรอบให้ร้านนี้ ไม่งั้นระบบเช็กโอกาสอัพเซลล์ไม่ได้',
        'no_register_date'     => 'ช่อง Register Date ใน Monday ว่าง → กรอกวันที่ลูกค้าสมัคร ไม่งั้นนับวันที่ค้าง setup ไม่ได้',
        'blank_status'         => 'ช่อง Live Status ใน Monday ว่าง → เลือกสถานะให้ร้านนี้ ไม่งั้นร้านจะไม่ถูกนับในสรุป',
        'no_remaining_minutes' => 'gcal ไม่มีตัวเลขนาทีคงเหลือของร้านนี้ → ฝั่ง IT ช่วยเช็ก ไม่งั้นเช็กอัพเซลล์และร้านเงียบไม่ได้',
    ];
    $kind = arayaClean($g['kind'] ?? '');
    $detail = arayaClean($g['detail'] ?? '');
    $keep = $detail === '' ? '' : ' (' . $detail . ')';

    switch ($kind) {
        case 'name_mismatch':
            if (preg_match('/same shop as "([^"]+)" in gcal/u', $detail, $m) === 1) {
                return 'ชื่อใน Monday ไม่ตรงกับใน gcal ("' . $m[1] . '") ระบบเลยจับคู่ไม่ได้ '
                     . 'และนาทีของร้านนี้ไม่ขึ้นในรายงาน → แก้ชื่อในบอร์ดให้ตรง '
                     . 'หรือใส่ Google Calendar ID ในบอร์ด แล้วชื่อจะไม่สำคัญอีก';
            }
            return 'ชื่อใน Monday ไม่ตรงกับใน gcal ระบบเลยจับคู่ไม่ได้ '
                 . 'และนาทีของร้านนี้ไม่ขึ้นในรายงาน → ใส่ Google Calendar ID ในบอร์ด' . $keep;
        case 'unmatched_monday':
            if (preg_match('/^On the Monday tracker \((.+?)\) but no matching shop in gcal-booking-api; minutes unknown\.$/u', $detail, $m) === 1) {
                $st = $m[1] === 'no status' ? 'ไม่มีสถานะ' : $m[1];
                return 'อยู่ใน Monday (สถานะ: ' . $st . ') แต่ไม่มีร้านนี้ใน gcal '
                     . 'เลยไม่รู้นาทีคงเหลือ → เช็กว่าร้านถูกตั้งใน gcal แล้วหรือยัง '
                     . 'ถ้าตั้งแล้วให้ใส่ Google Calendar ID ในบอร์ด';
            }
            return 'อยู่ใน Monday แต่ไม่มีร้านนี้ใน gcal เลยไม่รู้นาทีคงเหลือ '
                 . '→ เช็กว่าร้านถูกตั้งใน gcal แล้วหรือยัง' . $keep;
        case 'unknown_status':
            if (preg_match('/^Monday live_status "([^"]*)" is not a known label;/u', $detail, $m) === 1) {
                return 'ช่อง Live Status เป็น "' . $m[1] . '" ซึ่งไม่ใช่ตัวเลือกมาตรฐาน '
                     . '→ เปลี่ยนเป็นสถานะที่มีในลิสต์ ไม่งั้นร้านนี้จะไม่ถูกนับในสรุป';
            }
            return 'ช่อง Live Status เป็นค่าที่ไม่ใช่ตัวเลือกมาตรฐาน → เปลี่ยนเป็นสถานะที่มีในลิสต์' . $keep;
        case 'no_allocation':
            if (preg_match('/^Remaining minutes \((.+?)\) exceed the allocation \((.+?)\);/u', $detail, $m) === 1) {
                return 'นาทีคงเหลือ (' . $m[1] . ') มากกว่าโควตาที่ตั้งไว้ (' . $m[2] . ') '
                     . '→ ปกติเกิดตอนเพิ่งเติมนาที ถ้าไม่ได้เพิ่งเติมให้ฝั่ง IT เช็กตัวเลขใน gcal';
            }
            if (preg_match('/^No minute allocation in gcal;/u', $detail) === 1) {
                return 'ยังไม่ได้ตั้งโควตานาทีให้ร้านนี้ใน gcal → ตั้งโควตา ไม่งั้นเช็กกฎร้านไม่ใช้งานไม่ได้';
            }
            return 'โควตานาทีใน gcal ไม่ถูกต้อง → ฝั่ง IT ช่วยเช็ก' . $keep;
        case 'no_live_date':
            // A LIVE shop also loses the idle-rule check; a pending-cancel shop
            // never faces that rule, so only claim it when the detail says so.
            $base = 'ไม่มีวัน live ทั้งใน Monday และ gcal → กรอกช่อง Live Date ในบอร์ด '
                  . 'ไม่งั้นคำนวณว่าอยู่กับเรามานานเท่าไหร่ไม่ได้';
            return strpos($detail, 'idle rules') !== false ? $base . ' และเช็กกฎร้านไม่ใช้งานไม่ได้' : $base;
    }
    return $th[$kind] ?? $detail;
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

    // The two lists someone rings about. They lead, before upsell, because a
    // shop that is already out of minutes is losing calls right now while an
    // upsell is only an opportunity.
    if (!empty($brief['atRisk'])) {
        $B = ['🚨 ต้องโทรวันนี้ (นาทีหมดหรือใกล้หมด)'];
        foreach ($brief['atRisk'] as $r) {
            $B[] = '• ' . arayaChatName($r) . ' ' . arayaChatMins($r['remaining_minutes'] ?? null)
                 . ($r['overdrawn'] ? ' ⚠️ ใช้เกินโควตาแล้ว' : '')
                 . ($r['days_until_refresh'] === null ? '' : ' · รีเฟรชอีก ' . (int)$r['days_until_refresh'] . ' วัน')
                 . arayaChatOwner($r['po'] ?? '');
        }
        $S[] = $B;
    }

    if (!empty($brief['runningOut'])) {
        $B = ['⏳ นาทีจะหมดก่อนรอบรีเฟรช'];
        foreach ($brief['runningOut'] as $r) {
            $B[] = '• ' . arayaChatName($r) . ' ' . arayaChatMins($r['remaining_minutes'] ?? null)
                 . ' · ใช้ราว ' . $r['per_day'] . ' นาที/วัน'
                 . ($r['days_until_empty'] === null ? '' : ' → หมดในอีกราว ' . (int)$r['days_until_empty'] . ' วัน')
                 . ' (รีเฟรชอีก ' . (int)$r['days_until_refresh'] . ' วัน)'
                 . arayaChatOwner($r['po'] ?? '');
        }
        $S[] = $B;
    }

    // Upsell: always printed
    // The thresholds come from the constants the rule itself uses. Spelling
    // them out as literals here would let the heading keep announcing "<70"
    // after someone changed the rule, and this message is the artefact people
    // act on without opening the dashboard.
    $B = ['💰 โอกาสอัพเซลล์ (นาที <' . ARAYA_UPSELL_MAX_MINUTES
        . ' และรีเฟรชอีก ≤' . ARAYA_UPSELL_MAX_DAYS . ' วัน)'];
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
            . arayaChatExcludedReason($r);
    }
    $S[] = $B;

    // Idle, new
    if (!empty($brief['idleNew'])) {
        $B = ['📉 ไลฟ์แล้วแต่แทบไม่ได้ใช้'];
        foreach ($brief['idleNew'] as $r) {
            $B[] = arayaChatName($r) . ' (ใช้ ' . $r['used_pct'] . '% · live มา ' . (int)$r['days_live']
                . ' วัน · เหลือ ' . arayaChatMins($r['remaining_minutes'] ?? null) . ')' . arayaChatOwner($r['po'] ?? '');
        }
        $S[] = $B;
    }

    // Idle, quiet
    if (!empty($brief['idleQuiet'])) {
        $B = ['📉 เคยใช้แล้วหายไป'];
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

    // Data gaps. Only the ones someone has to act on are listed: a cancelled
    // shop is missing from gcal because cancelling is what removes it, and on
    // the real board those outnumber the genuine problems 25 to 3. Listing all
    // of them every morning would bury the real ones and teach the team to
    // scroll past the section. The expected ones are counted, not named, so
    // the number stays honest and the dashboard still shows them in full.
    $gapsAction = array_filter($brief['gaps'] ?? [], fn($g) => empty($g['expected']));
    $gapsExpected = count($brief['gaps'] ?? []) - count($gapsAction);
    if ($gapsAction) {
        $B = ['⚠️ ข้อมูลไม่ครบ'];
        foreach ($gapsAction as $g) {
            $B[] = '• ' . arayaChatName($g) . ' [' . arayaClean($g['kind'] ?? '') . '] ' . arayaChatGapDetail($g);
        }
        if ($gapsExpected > 0) {
            $B[] = '(อีก ' . $gapsExpected . ' รายการเป็นร้านที่ยกเลิกแล้ว ไม่ต้องทำอะไร)';
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
