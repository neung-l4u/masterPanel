<?php
// Staff-only. This page lists the whole client roster, so unlike modules/aiAraya
// (public by design) it must not render for an anonymous visitor.
//
// The root chkLogin.php is the login/logout handler -- it destroys the session
// and redirects -- so it cannot be include()d as a guard. The panel's guard is
// the session check main.php performs, repeated here before anything else runs.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['id'])) {
    header('Location: ../../../index.php');
    exit;
}

// A full client roster has no business sitting in a browser or proxy cache,
// least of all on a shared machine after someone logs out.
header('Cache-Control: no-store');

date_default_timezone_set('Asia/Bangkok');
// __DIR__, not a bare relative path: a plain '../models/...' resolves against
// the calling script's directory, which happens to work when Apache serves
// this file directly but breaks the moment anything includes it from
// elsewhere -- a CLI harness, or the 07:00 cron that is coming next.
require_once __DIR__ . '/../models/loadBrief.php';
require_once __DIR__ . '/../models/renderChat.php';

function arayaH($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Minutes keep their sign. Red below zero, amber under the upsell threshold, green otherwise. */
function arayaMinsCell($v): string
{
    if ($v === null) {
        return '<span class="mins mins-unknown">?</span>';
    }
    $class = $v < 0 ? 'mins-red' : ($v < ARAYA_UPSELL_MAX_MINUTES ? 'mins-amber' : 'mins-green');
    $text = is_float($v) ? rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.') : (string)$v;
    return '<span class="mins ' . $class . '">' . arayaH($text) . '</span>';
}

function arayaLifespanText($l): string
{
    return is_array($l) ? (int)$l['months'] . ' mo ' . (int)$l['days'] . ' d' : 'not recorded';
}

/** One call-quality figure. Null is "gcal sent nothing", never shown as 0. */
function arayaCallCell($v, string $extra = ''): string
{
    $cls = 'text-end' . ($extra === '' ? '' : ' ' . $extra);
    if ($v === null) {
        return '<td class="' . $cls . '"><span class="mins mins-unknown" title="No figure from gcal">?</span></td>';
    }
    return '<td class="' . $cls . '"><span class="mins">' . arayaH($v) . '</span></td>';
}

function arayaOwner($po): string
{
    $po = arayaClean($po);
    return $po === '' ? '<span class="text-muted">-</span>' : arayaH($po);
}

/**
 * One rate as a cell. Null is "not enough calls to say", never 0%.
 *
 * A shop with three calls has no meaningful percentage, and showing one would
 * put it at the top of a sorted column ahead of the shops that matter. The
 * title says which of the two reasons applies so the dash is not a mystery.
 *
 * $worseWhenHigh flips the colouring for conversion, where low is the problem.
 */
function arayaRateCell(?float $pct, ?float $fleetPct, bool $worseWhenHigh = true,
                       float $margin = ARAYA_RATE_ALERT_MARGIN_PTS): string
{
    if ($pct === null) {
        return '<td class="text-end"><span class="mins mins-unknown" '
            . 'title="Fewer than ' . ARAYA_RATE_MIN_CALLS . ' calls in this window, or no figure from gcal">&ndash;</span></td>';
    }
    $class = 'mins';
    if ($fleetPct !== null) {
        $off = $worseWhenHigh ? $pct - $fleetPct : $fleetPct - $pct;
        if ($off >= $margin) {
            $class .= ' mins-red';
        } elseif ($off > 0) {
            $class .= ' mins-amber';
        }
    }
    $title = $fleetPct === null
        ? 'No fleet figure to compare with'
        : 'Fleet ' . number_format($fleetPct, 1) . '%';
    return '<td class="text-end"><span class="' . $class . '" title="' . arayaH($title) . '">'
        . arayaH(number_format($pct, 1)) . '%</span></td>';
}

/** A percentage for prose, or a dash when there is none. */
function arayaPct(?float $pct): string
{
    return $pct === null ? '&ndash;' : arayaH(number_format($pct, 1)) . '%';
}

$useFixture = ($_GET['fixture'] ?? '') === 'sample';
$useCache = empty($_GET['nocache']);

$brief = null;
$error = null;      // Headline: what went wrong.
$errorHint = null;  // What the reader should do about it.
$notConfigured = false;
try {
    $brief = arayaLoadBrief($useFixture, $useCache);
} catch (Throwable $e) {
    // The detail goes to the log, not the page. Today's messages carry no
    // credentials by design, but a future one that quoted an upstream response
    // body would publish it to whoever opened the page. A failure also must not
    // answer 200, or monitoring reads a broken report as a healthy one.
    error_log('[arayaBrief] dashboard load failed: ' . $e->getMessage());
    http_response_code(502);

    // "No config file yet" and "Monday is down" are different problems with
    // different fixes, and before first setup the first is far likelier.
    // Blaming Monday for a missing file sends the reader to the wrong place.
    $notConfigured = str_contains($e->getMessage(), 'config.php');
    if ($notConfigured) {
        $error = 'This report is not configured yet.';
        $errorHint = 'Copy assets/config.example.php to assets/config.php and '
            . 'fill in the Monday token, the gcal URL and the admin key. Until '
            . 'then, add ?fixture=sample to the URL to see the report with '
            . 'sample data.';
    } else {
        $error = 'The brief could not be built.';
        $errorHint = 'Monday is the source of truth for who is a customer, so '
            . 'without it there is no report to show. The details are in the '
            . 'PHP error log.';
    }
}

$refreshQuery = http_build_query(array_filter(['fixture' => $useFixture ? 'sample' : null, 'nocache' => 1]));

// Names under upsell, so the same shop reads as one finding on the live table.
$upsellNames = [];
$gapKindLabels = [
    'unmatched_monday' => 'On Monday, not in gcal', 'unmatched_gcal' => 'In gcal, not on Monday',
    'blank_status' => 'Blank status', 'unknown_status' => 'Unknown status',
    'no_live_date' => 'No live date', 'no_register_date' => 'No register date',
    'no_billing_day' => 'No billing day', 'no_allocation' => 'No allocation',
    'no_remaining_minutes' => 'No minutes figure',
];
if ($brief) {
    foreach ($brief['upsell'] as $r) {
        $upsellNames[arayaClean($r['name'])] = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Araya Daily Brief - Local For You</title>
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #f0f2f5; color: #1a1a2e; }
        .page { max-width: 1280px; margin: 0 auto; padding: 24px 16px 64px; }
        .stat { background: #fff; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 3px rgba(0,0,0,.08); height: 100%; }
        .stat .n { font-size: 2rem; font-weight: 800; line-height: 1; }
        .stat .l { font-size: .78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; margin-top: 6px; }
        .panel-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); height: 100%; }
        .panel-card h2 { font-size: 1rem; font-weight: 700; margin: 0; padding: 14px 18px; border-bottom: 1px solid #eef0f3; }
        .panel-card .body { padding: 0; }
        .table { margin: 0; font-size: .86rem; }
        .table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; white-space: nowrap; }
        .mins { font-weight: 700; font-variant-numeric: tabular-nums; }
        .mins-red { color: #dc3545; }
        .mins-amber { color: #b36b00; }
        .mins-green { color: #198754; }
        .mins-unknown { color: #6c757d; }
        .urgent { border-left: 4px solid #dc3545; background: #fff5f5; }
        .urgent h2 { color: #842029; border-bottom-color: #f1c9cd; }
        .calls th.grp { text-align: center; }
        .calls .win { border-left: 2px solid #dee2e6; }
        .gaps { border: 2px solid #dc3545; border-radius: 12px; background: #fff5f5; }
        .gaps .head { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; width: 100%; background: none; border: 0; text-align: left; color: #842029; font-weight: 700; }
        .gaps .count { background: #dc3545; color: #fff; border-radius: 999px; padding: 2px 12px; font-size: .95rem; }
        .gaps.none { border-color: #198754; background: #f1faf4; }
        .gaps.none .head { color: #0f5132; cursor: default; }
        .gaps.none .count { background: #198754; }
        .money { cursor: help; }
        .note { color: #6c757d; font-size: .8rem; }
        pre.chat { white-space: pre-wrap; word-break: break-word; background: #1a1a2e; color: #e9ecef; border-radius: 12px; padding: 18px; font-size: .85rem; margin: 0; }
    </style>
</head>
<body>
<div class="page">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-0"><i class="bi bi-sunrise"></i> Araya Daily Brief</h1>
            <div class="note">
                <?php if ($brief) { ?>
                    For <?php echo arayaH($brief['generated_for']); ?> &middot; page rendered <?php echo arayaH(date('H:i')); ?> (Bangkok)
                    <?php if ($useFixture) { ?> &middot; <strong>sample data, no API calls</strong><?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?php if ($brief) { ?>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#chatPreview" aria-expanded="false" aria-controls="chatPreview">
                <i class="bi bi-chat-text"></i> Preview Chat message
            </button>
            <?php } ?>
            <a class="btn btn-primary btn-sm" href="?<?php echo arayaH($refreshQuery); ?>"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
        </div>
    </div>

<?php if ($error !== null) { ?>
    <div class="alert <?php echo $notConfigured ? 'alert-warning' : 'alert-danger'; ?>">
        <strong><?php echo arayaH($error); ?></strong>
        <div class="mt-1"><?php echo arayaH($errorHint); ?></div>
        <?php if ($notConfigured) { ?>
        <a class="btn btn-sm btn-primary mt-2" href="?fixture=sample">
            <i class="bi bi-eye"></i> View with sample data
        </a>
        <?php } ?>
    </div>
<?php } else { ?>

    <?php if (!empty($brief['gcal_unavailable'])) { ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Minute data from gcal could not be fetched. This view uses Monday only;
        do not read the minutes as current.
    </div>
    <?php } ?>

    <?php if (!empty($brief['call_errors_unavailable']) && empty($brief['gcal_unavailable'])) { ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> The failed-call list could not be fetched, so a missing
        "Calls with errors" panel does not mean there were none.
    </div>
    <?php } ?>

    <?php $o = $brief['overview']; ?>
    <div class="row g-3 mb-3">
        <?php foreach ([
            ['Live', $o['live'], '#198754'], ['Waiting to cancel', $o['pendingCancel'], '#b36b00'],
            ['In setup', $o['setup'], '#0d6efd'], ['Cancelled', $o['cancelled'], '#6c757d'],
            ['All clients', $o['total'], '#1a1a2e'],
        ] as [$label, $n, $color]) { ?>
        <div class="col-6 col-md">
            <div class="stat"><div class="n" style="color:<?php echo $color; ?>"><?php echo (int)$n; ?></div><div class="l"><?php echo arayaH($label); ?></div></div>
        </div>
        <?php } ?>
    </div>

    <!-- Data gaps first: these are the shops most likely to be a real problem. -->
    <?php $gapCount = count($brief['gaps']); ?>
    <div class="gaps mb-4 <?php echo $gapCount === 0 ? 'none' : ''; ?>" id="gaps">
        <?php if ($gapCount > 0) { ?>
        <button class="head" type="button" data-bs-toggle="collapse" data-bs-target="#gapList" aria-expanded="false" aria-controls="gapList">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span>Data gaps - shops a rule had to skip</span>
            <span class="count"><?php echo $gapCount; ?></span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </button>
        <div class="collapse" id="gapList">
            <table class="table table-sm mb-0">
                <thead><tr><th>Shop</th><th>Problem</th><th>Detail</th></tr></thead>
                <tbody>
                <?php foreach ($brief['gaps'] as $g) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH($g['name']); ?></td>
                        <td><?php echo arayaH($gapKindLabels[$g['kind']] ?? $g['kind']); ?></td>
                        <td class="note"><?php echo arayaH($g['detail']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="head"><i class="bi bi-check-circle-fill"></i><span>Data gaps</span><span class="count">0</span></div>
        <?php } ?>
    </div>

<?php
    // Call-quality findings, above the full table: the table is for looking a
    // shop up, these are for being told which shop to look at. Each panel
    // states the rule it applied and the baseline it used, so a reader can
    // disagree with the finding instead of having to trust it.
    $windowLabel = ($brief['rateWindow'] ?? 'd7') === 'd7' ? 'last 7 days' : 'last 30 days';
    $callFindings = count($brief['bookingBroken']) + count($brief['conversionLow'])
        + count($brief['forwardingHigh']) + count($brief['hangupHigh']);
?>
    <?php if ($callFindings > 0) { ?>
    <div class="row g-3 mb-4">
        <?php if (!empty($brief['bookingBroken'])) { ?>
        <!-- First, because it is the only one of the four that is a bug rather
             than a trend: the caller wanted to book and the system refused. -->
        <div class="col-lg-6">
            <div class="panel-card urgent">
                <h2>&#128296; Bookings that failed
                    <span class="note fw-normal">(<?php echo arayaH($windowLabel); ?>; a booking was attempted and did not go through)</span>
                </h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Failed</th><th class="text-end">Booked</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['bookingBroken'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td class="text-end"><span class="mins mins-red"><?php echo arayaH($r['failed']); ?></span></td>
                                <td class="text-end"><?php echo $r['booked'] === null ? '<span class="mins mins-unknown">?</span>' : '<span class="mins">' . arayaH($r['booked']) . '</span>'; ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 note border-top">
                    These are calls where a booking tool ran and returned a failure, so the
                    caller was told no. Worth checking the booking API log for these shops.
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (!empty($brief['conversionLow'])) { ?>
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#128201; Conversion below the fleet
                    <span class="note fw-normal">(<?php echo arayaH($windowLabel); ?>; at least <?php echo ARAYA_CONVERSION_ALERT_MARGIN_PTS; ?> points under)</span>
                </h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Conv. %</th><th class="text-end">Fleet</th><th class="text-end">Of calls</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['conversionLow'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td class="text-end"><span class="mins mins-red"><?php echo arayaPct($r['pct']); ?></span></td>
                                <td class="text-end note"><?php echo arayaPct($r['fleet_pct']); ?></td>
                                <!-- The denominator, so a rate off a thin base
                                     is visibly thin rather than quietly wrong. -->
                                <td class="text-end note"><?php echo arayaH($r['base']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 note border-top">
                    Counted over calls long enough to have booked, so misdials and hang-ups are
                    not held against the shop. Worth listening back to a few of these calls.
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (!empty($brief['hangupHigh'])) { ?>
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#9986; Callers hanging up early
                    <span class="note fw-normal">(<?php echo arayaH($windowLabel); ?>; <?php echo ARAYA_SHORT_ALERT_PCT; ?>% or more of calls under 15 s)</span>
                </h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Short %</th><th class="text-end">Fleet</th><th class="text-end">Of calls</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['hangupHigh'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td class="text-end"><span class="mins mins-red"><?php echo arayaPct($r['pct']); ?></span></td>
                                <td class="text-end note"><?php echo arayaPct($r['fleet_pct']); ?></td>
                                <td class="text-end note"><?php echo arayaH($r['total']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 note border-top">
                    Judged against a fixed threshold, not the fleet: the fleet average for this
                    is high enough that comparing with it would excuse every shop. Often the
                    greeting &mdash; worth checking the shop name is pronounced correctly.
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (!empty($brief['forwardingHigh'])) { ?>
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#128222; Forwarding above the fleet
                    <span class="note fw-normal">(<?php echo arayaH($windowLabel); ?>; at least <?php echo ARAYA_RATE_ALERT_MARGIN_PTS; ?> points over)</span>
                </h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Fwd %</th><th class="text-end">Fleet</th><th class="text-end">Of calls</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['forwardingHigh'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td class="text-end"><span class="mins mins-red"><?php echo arayaPct($r['pct']); ?></span></td>
                                <td class="text-end note"><?php echo arayaPct($r['fleet_pct']); ?></td>
                                <td class="text-end note"><?php echo arayaH($r['total']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 note border-top">
                    Every forwarded call is one Araya could not finish. Compared with the fleet
                    because what counts as normal differs by shop and by service.
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($brief['callErrors'])) { ?>
    <div class="panel-card urgent mb-4" id="callErrors">
        <h2>&#128222; Calls with errors <span class="note fw-normal">(last 30 days, newest first; <?php echo count($brief['callErrors']); ?> shown)</span></h2>
        <div class="body table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Shop</th><th>When (Bangkok)</th><th>What went wrong</th><th class="text-end">Length</th><th>Recording</th></tr></thead>
                <tbody>
                <?php foreach ($brief['callErrors'] as $e) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH(arayaChatName(['name' => $e['shop_name']])); ?></td>
                        <td><?php echo arayaH(arayaChatWhen($e['started_at'])); ?></td>
                        <td class="note"><?php echo $e['outcome'] === '' ? '<span class="text-muted">not stated</span>' : arayaH($e['outcome']); ?></td>
                        <td class="text-end"><?php echo $e['duration_sec'] === null ? '<span class="mins mins-unknown">?</span>' : '<span class="mins">' . arayaH($e['duration_sec']) . ' s</span>'; ?></td>
                        <td><?php if ($e['recording_url'] !== null) { ?><a href="<?php echo arayaH($e['recording_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-play-circle"></i> Listen</a><?php } else { ?><span class="text-muted">no recording</span><?php } ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>

    <div class="panel-card mb-4">
        <h2>Live shops <span class="note fw-normal">(least minutes first; <span class="money">&#128176;</span> = also an upsell candidate)</span></h2>
        <div class="body table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Shop</th><th class="text-end">Minutes</th><th>Time live</th><th class="text-end">Days to refresh</th><th>Owner</th></tr></thead>
                <tbody>
                <?php foreach ($brief['live'] as $r) { ?>
                    <tr>
                        <td class="fw-semibold">
                            <?php echo arayaH($r['name']); ?>
                            <?php if (isset($upsellNames[arayaClean($r['name'])])) { ?><span class="money" title="Also under upsell">&#128176;</span><?php } ?>
                            <?php if (!empty($r['gcal_missing'])) { ?><span class="badge text-bg-danger">not in gcal</span><?php } ?>
                        </td>
                        <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                        <td><?php echo arayaH(arayaLifespanText($r['lifespan'])); ?></td>
                        <td class="text-end"><?php echo $r['days_until_refresh'] === null ? '<span class="text-muted">?</span>' : arayaH($r['days_until_refresh']); ?></td>
                        <td><?php echo arayaOwner($r['po']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (!$brief['live']) { ?><tr><td colspan="5" class="text-muted">No live shops.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>

<?php
    // The three rates and the counts behind them, per window. Each rate sits
    // next to its own count rather than in a table of its own, so nobody
    // reads a percentage without seeing how many calls produced it.
    $fleetWin = is_array($brief['fleetCalls'] ?? null)
        ? ($brief['fleetCalls'][$brief['rateWindow']] ?? null) : null;
    $callColumns = 10;   // per window, for the colspans below
?>
    <div class="panel-card mb-4" id="callQuality">
        <h2>Call quality
            <span class="note fw-normal">
                (<strong>?</strong> = gcal sent no figure, which is not the same as 0.
                <strong>&ndash;</strong> = under <?php echo ARAYA_RATE_MIN_CALLS; ?> calls, too few for a percentage.
                Short = under 15 s; silent = caller said nothing and it timed out.
                Conv. % = of the calls long enough to have booked, the share that did.
                Hover a percentage for the fleet figure it is coloured against.)
            </span>
        </h2>
        <div class="body table-responsive">
            <table class="table table-hover align-middle calls">
                <thead>
                    <tr><th rowspan="2">Shop</th><th class="grp win" colspan="<?php echo $callColumns; ?>">Last 7 days</th><th class="grp win" colspan="<?php echo $callColumns; ?>">Last 30 days</th></tr>
                    <tr>
                        <?php foreach (ARAYA_CALL_WINDOWS as $w) { ?>
                        <th class="text-end win">Calls</th>
                        <th class="text-end">Fwd</th><th class="text-end">Fwd %</th>
                        <th class="text-end">Short</th><th class="text-end">Short %</th>
                        <th class="text-end">Silent</th>
                        <th class="text-end">Booked</th><th class="text-end">Conv. %</th>
                        <th class="text-end">Book fail</th>
                        <th class="text-end">Minutes</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                <?php if ($fleetWin !== null) { ?>
                    <tr class="table-light fw-semibold">
                        <td>Whole fleet<div class="note fw-normal">the baseline the shops below are coloured against</div></td>
                        <?php foreach (ARAYA_CALL_WINDOWS as $w) {
                            $fw = is_array($brief['fleetCalls'][$w] ?? null) ? $brief['fleetCalls'][$w] : null;
                            if ($fw === null) { ?>
                        <td colspan="<?php echo $callColumns; ?>" class="win text-muted">no fleet figures for this window</td>
                            <?php } else {
                                // The fleet row is never coloured: it is the
                                // thing being compared against, so colouring
                                // it against itself would say nothing.
                                echo arayaCallCell($fw['total'], 'win');
                                echo arayaCallCell($fw['forwarded']);
                                echo arayaRateCell($fw['forwarded_pct'], null);
                                echo arayaCallCell($fw['short']);
                                echo arayaRateCell($fw['short_pct'], null);
                                echo arayaCallCell($fw['silent']);
                                echo arayaCallCell($fw['booked']);
                                echo arayaRateCell($fw['conversion_pct'], null);
                                echo arayaCallCell($fw['booking_failed']);
                                echo arayaCallCell($fw['minutes']);
                            }
                        } ?>
                    </tr>
                <?php } ?>
                <?php foreach ($brief['live'] as $r) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                        <?php if (!is_array($r['calls'] ?? null)) { ?>
                        <td colspan="<?php echo $callColumns * 2; ?>" class="win text-muted"><?php echo !empty($r['gcal_missing']) ? 'Not in gcal - no call data' : 'gcal sent no call data for this shop'; ?></td>
                        <?php } else { foreach (ARAYA_CALL_WINDOWS as $w) {
                            $win = $r['calls'][$w] ?? null;
                            $rate = $r['rates'][$w] ?? null;
                            // The fleet row for THIS window, so a 30-day rate
                            // is never coloured against a 7-day baseline.
                            $fwin = is_array($brief['fleetCalls'][$w] ?? null) ? $brief['fleetCalls'][$w] : null;
                            if (!is_array($win)) { ?>
                        <td colspan="<?php echo $callColumns; ?>" class="win text-muted">no figures for this window</td>
                            <?php } else {
                                echo arayaCallCell($win['total'], 'win');
                                echo arayaCallCell($win['forwarded']);
                                echo arayaRateCell($rate['forwarded_pct'] ?? null, $fwin['forwarded_pct'] ?? null);
                                echo arayaCallCell($win['short']);
                                // Short hang-ups are judged against an absolute
                                // threshold, not the fleet: the fleet's own
                                // figure for this is high enough to excuse
                                // every shop, which is how a problem this size
                                // goes unnoticed.
                                echo arayaRateCell($rate['short_pct'] ?? null, ARAYA_SHORT_ALERT_PCT, true, 0.0);
                                echo arayaCallCell($win['silent']);
                                echo arayaCallCell($win['booked']);
                                echo arayaRateCell($rate['conversion_pct'] ?? null, $fwin['conversion_pct'] ?? null, false, ARAYA_CONVERSION_ALERT_MARGIN_PTS);
                                echo arayaCallCell($win['booking_failed']);
                                echo arayaCallCell($win['minutes']);
                            }
                        } } ?>
                    </tr>
                <?php } ?>
                <?php if (!$brief['live']) { ?><tr><td colspan="<?php echo $callColumns * 2 + 1; ?>" class="text-muted">No live shops.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($fleetWin === null && !empty($brief['live'])) { ?>
        <div class="p-3 note border-top">
            gcal sent no fleet totals, so nothing above is compared with a baseline and the only
            shops flagged below are the ones an absolute rule catches. This is what a gcal
            without fleet reporting looks like, not a fleet that took no calls.
        </div>
        <?php } ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#128176; Upsell opportunities <span class="note fw-normal">(under <?php echo ARAYA_UPSELL_MAX_MINUTES; ?> mins, refresh within <?php echo ARAYA_UPSELL_MAX_DAYS; ?> days)</span></h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Minutes</th><th class="text-end">Refresh in</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['upsell'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td class="text-end"><?php echo arayaH($r['days_until_refresh']); ?> d</td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$brief['upsell']) { ?><tr><td colspan="4" class="text-muted">None today.</td></tr><?php } ?>
                        <?php foreach ($brief['upsellExcluded'] as $r) { ?>
                            <tr class="text-muted">
                                <td><?php echo arayaH($r['name']); ?><div class="note">Not counted: <?php echo arayaH($r['reason']); ?></div></td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td class="text-end"><?php echo arayaH($r['days_until_refresh']); ?> d</td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#128201; Live but idle</h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th>Why</th><th class="text-end">Minutes left</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['idleNew'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td>Used <?php echo arayaH($r['used_pct']); ?>% in <?php echo (int)$r['days_live']; ?> days live</td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php foreach ($brief['idleQuiet'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td>Went quiet: <?php echo arayaH($r['latest_used']); ?> mins last period vs <?php echo arayaH(round($r['prior_mean'], 1)); ?> average before</td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$brief['idleNew'] && !$brief['idleQuiet']) { ?><tr><td colspan="4" class="text-muted">None today.</td></tr><?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#128679; Stuck in setup <span class="note fw-normal">(longest stalled first)</span></h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th>Status</th><th class="text-end">Stalled</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['setup'] as $r) {
                            $status = $r['setup_status'] !== '' ? $r['setup_status'] : $r['live_status']; ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?>
                                    <?php if ($r['note'] !== '') { ?><div class="note"><?php echo arayaH($r['note']); ?></div><?php } ?></td>
                                <td><?php echo arayaH($status); ?></td>
                                <td class="text-end"><?php echo $r['days_stalled'] === null ? '<span class="text-muted">?</span>' : (int)$r['days_stalled'] . ' d'; ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$brief['setup']) { ?><tr><td colspan="4" class="text-muted">None.</td></tr><?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card">
                <h2>&#10060; Waiting to cancel</h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th>Time live</th><th>Reason</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['cancelling'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                                <td><?php echo arayaH(arayaLifespanText($r['lifespan'])); ?></td>
                                <td><?php echo $r['reason'] === '' ? '<span class="text-danger">not recorded</span>' : arayaH($r['reason']); ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$brief['cancelling']) { ?><tr><td colspan="4" class="text-muted">None.</td></tr><?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- The exact message the 07:00 cron would send, from the same renderer. -->
    <div class="collapse" id="chatPreview">
        <h2 class="h6 fw-bold">Google Chat message preview</h2>
        <pre class="chat" id="chatText"><?php echo arayaH(arayaRenderChat($brief)); ?></pre>
    </div>

<?php } ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
