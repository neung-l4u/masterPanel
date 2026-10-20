<?php
// Araya Daily Brief, rendered inside the panel (main.php?p=arayaBrief).
//
// main.php has already started the session, enforced the login and emitted the
// chrome, so this file is a fragment: no <html>, no auth guard, no asset tags.
// The rules, matching and fetching all stay in modules/arayaBrief/models --
// this page only presents them, and the module keeps its own standalone view
// and tests.
// __DIR__, not a bare relative path: a plain '../models/...' resolves against
// the calling script's directory, which happens to work when Apache serves
// this file directly but breaks the moment anything includes it from
// elsewhere -- a CLI harness, or the 07:00 cron that is coming next.
require_once __DIR__ . '/../modules/arayaBrief/models/loadBrief.php';
require_once __DIR__ . '/../modules/arayaBrief/models/renderChat.php';

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

function arayaOwner($po): string
{
    $po = arayaClean($po);
    return $po === '' ? '<span class="text-muted">-</span>' : arayaH($po);
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
    // different fixes, and before first setup the first one is far likelier.
    // Blaming Monday for a missing file sends whoever reads this to the wrong
    // place entirely. The config loader throws a message naming the file, which
    // is what distinguishes the two.
    $notConfigured = str_contains($e->getMessage(), 'config.php');
    if ($notConfigured) {
        $error = 'This report is not configured yet.';
        $errorHint = 'Copy modules/arayaBrief/assets/config.example.php to '
            . 'config.php and fill in the Monday token, the gcal URL and the '
            . 'admin key. Until then, add ?fixture=sample to the URL to see the '
            . 'report with sample data.';
    } else {
        $error = 'The brief could not be built.';
        $errorHint = 'Monday is the source of truth for who is a customer, so '
            . 'without it there is no report to show. The details are in the '
            . 'PHP error log.';
    }
}

// Inside the panel every link must carry p=arayaBrief, or main.php routes the
// click back to the home page instead of reloading this report.
$refreshQuery = http_build_query(array_filter([
    'p' => 'arayaBrief',
    'fixture' => $useFixture ? 'sample' : null,
    'nocache' => 1,
]));

// Names under upsell, so the same shop reads as one finding on the live table.
$upsellNames = [];
$gapKindLabels = [
    'unmatched_monday' => 'On Monday, not in gcal', 'unmatched_gcal' => 'In gcal, not on Monday',
    'blank_status' => 'Blank status', 'unknown_status' => 'Unknown status',
    'no_live_date' => 'No live date', 'no_register_date' => 'No register date',
    'no_billing_day' => 'No billing day', 'no_allocation' => 'No allocation',
    'no_remaining_minutes' => 'No minutes figure',
    'name_mismatch' => 'Name differs between systems',
];
if ($brief) {
    foreach ($brief['upsell'] as $r) {
        $upsellNames[arayaClean($r['name'])] = true;
    }
}
?>
<style>
        .araya-brief { font-family: 'Montserrat', sans-serif; color: #1a1a2e; }
        .araya-brief { padding: 8px 0 48px; }
        .araya-brief .stat { background: #fff; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 3px rgba(0,0,0,.08); height: 100%; }
        .araya-brief .stat .n { font-size: 2rem; font-weight: 800; line-height: 1; }
        .araya-brief .stat .l { font-size: .78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .04em; margin-top: 6px; }
        .araya-brief .panel-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); height: 100%; }
        .araya-brief .panel-card h2 { font-size: 1rem; font-weight: 700; margin: 0; padding: 14px 18px; border-bottom: 1px solid #eef0f3; }
        .araya-brief .panel-card .body { padding: 0; }
        .araya-brief .table { margin: 0; font-size: .86rem; }
        .araya-brief .table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; white-space: nowrap; }
        .araya-brief .mins { font-weight: 700; font-variant-numeric: tabular-nums; }
        .araya-brief .mins-red { color: #dc3545; }
        .araya-brief .mins-amber { color: #b36b00; }
        .araya-brief .mins-green { color: #198754; }
        .araya-brief .mins-unknown { color: #6c757d; }
        .araya-brief .gaps { border: 2px solid #dc3545; border-radius: 12px; background: #fff5f5; }
        .araya-brief .gaps .head { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; width: 100%; background: none; border: 0; text-align: left; color: #842029; font-weight: 700; }
        .araya-brief .gaps .count { background: #dc3545; color: #fff; border-radius: 999px; padding: 2px 12px; font-size: .95rem; }
        .araya-brief .gaps.none { border-color: #198754; background: #f1faf4; }
        .araya-brief .gaps.expected { border-color: #ced4da; background: #f8f9fa; }
        .araya-brief .gaps.expected .head { color: #495057; }
        .araya-brief .gaps.expected .count { background: #adb5bd; }
        .araya-brief .gaps.none .head { color: #0f5132; cursor: default; }
        .araya-brief .gaps.none .count { background: #198754; }
        .araya-brief .money { cursor: help; }
        .araya-brief .note { color: #6c757d; font-size: .8rem; }
        .araya-brief pre.chat { white-space: pre-wrap; word-break: break-word; background: #1a1a2e; color: #e9ecef; border-radius: 12px; padding: 18px; font-size: .85rem; margin: 0; }
</style>

<div class="araya-brief container-fluid">

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
            <a class="btn btn-primary btn-sm" href="main.php?<?php echo arayaH($refreshQuery); ?>"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
        </div>
    </div>

<?php if ($error !== null) { ?>
    <div class="alert <?php echo $notConfigured ? 'alert-warning' : 'alert-danger'; ?>">
        <strong><?php echo arayaH($error); ?></strong>
        <div class="mt-1"><?php echo arayaH($errorHint); ?></div>
        <?php if ($notConfigured) { ?>
        <a class="btn btn-sm btn-primary mt-2" href="main.php?p=arayaBrief&amp;fixture=sample">
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

    <!-- Data gaps first: these are the shops most likely to be a real problem.
         Cancelled shops are absent from gcal because that is what cancelling
         means; on the real board they outnumber the genuine problems 25 to 3,
         so they are kept behind a second toggle rather than burying the rows
         someone has to act on. -->
    <?php
        $gapsAction = array_values(array_filter($brief['gaps'], fn($g) => empty($g['expected'])));
        $gapsExpected = array_values(array_filter($brief['gaps'], fn($g) => !empty($g['expected'])));
        $gapCount = count($gapsAction);
    ?>
    <div class="gaps mb-4 <?php echo $gapCount === 0 ? 'none' : ''; ?>" id="gaps">
        <?php if ($gapCount > 0) { ?>
        <button class="head" type="button" data-bs-toggle="collapse" data-bs-target="#gapList" aria-expanded="false" aria-controls="gapList">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span>Data gaps - needs a look</span>
            <span class="count"><?php echo $gapCount; ?></span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </button>
        <div class="collapse" id="gapList">
            <table class="table table-sm mb-0">
                <thead><tr><th>Shop</th><th>Problem</th><th>Detail</th></tr></thead>
                <tbody>
                <?php foreach ($gapsAction as $g) { ?>
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
        <div class="head"><i class="bi bi-check-circle-fill"></i><span>Data gaps - nothing to chase</span><span class="count">0</span></div>
        <?php } ?>
    </div>

    <?php if ($gapsExpected) { ?>
    <div class="gaps expected mb-4">
        <button class="head" type="button" data-bs-toggle="collapse" data-bs-target="#gapExpected" aria-expanded="false" aria-controls="gapExpected">
            <i class="bi bi-archive"></i>
            <span>Expected - cancelled shops with no gcal record</span>
            <span class="count"><?php echo count($gapsExpected); ?></span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </button>
        <div class="collapse" id="gapExpected">
            <div class="px-3 pb-2 note">Normal: cancelling a shop is what removes it from gcal. Listed so the count stays honest.</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Shop</th><th>Status on the board</th></tr></thead>
                <tbody>
                <?php foreach ($gapsExpected as $g) {
                    preg_match('/\(([^)]*)\)/', $g['detail'], $sm); ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH($g['name']); ?></td>
                        <td class="note"><?php echo arayaH($sm[1] ?? '-'); ?></td>
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
