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

/**
 * One call-quality figure.
 *
 * A missing figure prints "?" rather than 0: "gcal sent us nothing for this
 * shop" and "this shop had no calls" are different facts, and showing both as
 * zero would quietly invent data.
 */
function arayaCallCell($v, string $extra = ''): string
{
    $cls = 'text-end' . ($extra === '' ? '' : ' ' . $extra);
    if ($v === null) {
        return '<td class="' . $cls . '"><span class="num mins-unknown" title="No figure from gcal">?</span></td>';
    }
    return '<td class="' . $cls . '"><span class="num">' . arayaH($v) . '</span></td>';
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
/* Araya Daily Brief — an operations console.
 *
 * This page is read once each morning by someone deciding which shops to ring,
 * so it is built around one question: what needs attention first? Urgency is
 * carried by a left border rail and a warm/cool split, never by decoration.
 * Numbers are the loudest thing here because they are what the eye is hunting.
 *
 * Everything is scoped under .araya-brief: this renders inside the admin
 * panel, and the panel's own chrome must not be touched.
 */

.araya-brief {
    /* A cool near-black rather than pure grey: easier to read at length. */
    --ink:        #15181d;
    --ink-2:      #454c57;
    --ink-3:      #7b8494;
    --line:       #e4e7ec;
    --line-2:     #f1f3f6;
    --surface:    #ffffff;
    --canvas:     #f6f7f9;

    /* One hue per state. Amber is the hinge: it means "soon", not "wrong". */
    --danger:     #c0362c;
    --danger-bg:  #fdf4f3;
    --danger-rail:#e0574b;
    --warn:       #9a6207;
    --warn-bg:    #fdf9f0;
    --ok:         #11705a;
    --ok-bg:      #f2f9f6;
    --accent:     #1f4ed8;

    --r:          10px;
    --shadow:     0 1px 2px rgba(21,24,29,.05), 0 1px 3px rgba(21,24,29,.04);
    --shadow-lift:0 2px 4px rgba(21,24,29,.05), 0 8px 20px -6px rgba(21,24,29,.10);

    /* The panel's own stack, via Bootstrap's variable, so this page matches
       every other report instead of announcing itself with a webfont. */
    font-family: var(--bs-font-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif);
    color: var(--ink);
    padding: 4px 0 56px;
}

.araya-brief *, .araya-brief *::before, .araya-brief *::after { box-sizing: border-box; }

/* ---- Page head ------------------------------------------------------- */
.araya-brief .page-head { display: flex; flex-wrap: wrap; align-items: flex-start;
    justify-content: space-between; gap: 16px; margin-bottom: 22px; }
.araya-brief .page-head h1 { font-weight: 700; font-size: 1.6rem; letter-spacing: -.02em; margin: 0; display: flex; align-items: center; gap: 10px; }
.araya-brief .page-head h1 i { color: var(--warn); font-size: 1.4rem; }
.araya-brief .page-sub { color: var(--ink-3); font-size: .8rem; margin-top: 3px;
    font-variant-numeric: tabular-nums; }

/* ---- Stat strip ------------------------------------------------------ */
/* One hairline-divided row, so the five counts read as a single statement
   rather than five cards competing for attention. */
.araya-brief .stats { display: grid; grid-template-columns: repeat(5, 1fr);
    background: var(--surface); border: 1px solid var(--line); border-radius: var(--r);
    box-shadow: var(--shadow); overflow: hidden; margin-bottom: 20px; }
.araya-brief .stat { padding: 16px 18px 15px; border-left: 1px solid var(--line-2); }
.araya-brief .stat:first-child { border-left: 0; }
.araya-brief .stat .n { font-size: 2rem; font-weight: 700; line-height: 1; letter-spacing: -.03em; font-variant-numeric: tabular-nums; }
.araya-brief .stat .l { font-size: .67rem; font-weight: 600; color: var(--ink-3);
    text-transform: uppercase; letter-spacing: .07em; margin-top: 7px; }
.araya-brief .stat.is-live   .n { color: var(--ok); }
.araya-brief .stat.is-cancel .n { color: var(--warn); }
.araya-brief .stat.is-setup  .n { color: var(--accent); }
.araya-brief .stat.is-gone   .n { color: var(--ink-3); }

/* ---- Banners --------------------------------------------------------- */
.araya-brief .gaps { border: 1px solid var(--line); border-left: 3px solid var(--danger-rail);
    border-radius: var(--r); background: var(--danger-bg); margin-bottom: 14px;
    box-shadow: var(--shadow); overflow: hidden; }
.araya-brief .gaps .head { display: flex; align-items: center; gap: 11px; padding: 13px 17px;
    cursor: pointer; width: 100%; background: none; border: 0; text-align: left;
    color: var(--danger); font-weight: 600; font-size: .9rem; transition: background .15s ease; }
.araya-brief .gaps .head:hover { background: rgba(192,54,44,.045); }
.araya-brief .gaps .count { background: var(--danger); color: #fff; border-radius: 999px;
    padding: 1px 10px; font-size: .78rem; font-weight: 700; font-variant-numeric: tabular-nums; }
.araya-brief .gaps .head .bi-chevron-down { transition: transform .2s ease; font-size: .8rem; opacity: .65; }
.araya-brief .gaps .head[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
.araya-brief .gaps.none     { border-left-color: var(--ok); background: var(--ok-bg); }
.araya-brief .gaps.none .head { color: var(--ok); cursor: default; }
.araya-brief .gaps.none .count { background: var(--ok); }
.araya-brief .gaps.expected { border-left-color: var(--ink-3); background: var(--surface); margin-bottom: 20px; }
.araya-brief .gaps.expected .head { color: var(--ink-2); font-weight: 500; }
.araya-brief .gaps.expected .count { background: var(--ink-3); }

/* ---- Panels ---------------------------------------------------------- */
.araya-brief .panel-card { background: var(--surface); border: 1px solid var(--line);
    border-radius: var(--r); box-shadow: var(--shadow); overflow: hidden;
    transition: box-shadow .18s ease; }
/* Panels size to their content. Stretching a two-row table to match an
   eight-row neighbour just adds white space under the short one. */
.araya-brief .row > [class*="col-"] { display: flex; }
.araya-brief .row > [class*="col-"] > .panel-card { width: 100%; align-self: flex-start; }
.araya-brief .panel-card:hover { box-shadow: var(--shadow-lift); }
.araya-brief .panel-card.urgent { border-left: 3px solid var(--danger-rail); }
.araya-brief .panel-card.soon   { border-left: 3px solid var(--warn); }

/* The call-quality table carries two windows side by side, so each window
   needs a visible edge -- without it the ten numeric columns read as one run
   and nobody can tell 7-day from 30-day at a glance. */
.araya-brief .calls th.grp { text-align: center; font-weight: 700; color: var(--ink-2); }
.araya-brief .calls .win { border-left: 1px solid var(--line); }
.araya-brief .panel-card h2 { font-size: .92rem; font-weight: 600; letter-spacing: -.005em;
    margin: 0; padding: 14px 17px 13px; border-bottom: 1px solid var(--line-2);
    display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
.araya-brief .panel-card .body { padding: 0; }

/* ---- Tables ---------------------------------------------------------- */
.araya-brief .table { margin: 0; font-size: .855rem; --bs-table-bg: transparent; }
.araya-brief .table th { font-size: .66rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: .07em; color: var(--ink-3); white-space: nowrap;
    border-bottom: 1px solid var(--line); padding: 9px 17px; background: transparent; }
.araya-brief .table td { padding: 11px 17px; border-bottom: 1px solid var(--line-2);
    vertical-align: middle; color: var(--ink-2); }
.araya-brief .table tbody tr:last-child td { border-bottom: 0; }
.araya-brief .table tbody tr { transition: background .12s ease; }
.araya-brief .table tbody tr:hover { background: var(--canvas); }
.araya-brief .table td.fw-semibold { color: var(--ink); font-weight: 600; }

/* Numbers in mono: columns line up, and a change in magnitude shows as a
   change in width before you have read the digits. */
.araya-brief .mins, .araya-brief .num { font-family: var(--bs-font-monospace, SFMono-Regular, Menlo, Consolas, monospace);
    font-weight: 600; font-variant-numeric: tabular-nums; font-size: .83rem; }
.araya-brief .mins-red     { color: var(--danger); }
.araya-brief .mins-amber   { color: var(--warn); }
.araya-brief .mins-green   { color: var(--ok); }
.araya-brief .mins-unknown { color: var(--ink-3); }

/* ---- Bits ------------------------------------------------------------ */
.araya-brief .note { color: var(--ink-3); font-size: .78rem; font-weight: 500; }
.araya-brief h2 .note { font-weight: 500; letter-spacing: 0; text-transform: none; }
.araya-brief .money { cursor: help; }
.araya-brief .badge.bg-danger { background: var(--danger) !important; font-weight: 600;
    font-size: .64rem; letter-spacing: .04em; text-transform: uppercase; padding: .26em .5em;
    vertical-align: middle; }
.araya-brief .empty { padding: 26px 17px; color: var(--ink-3); font-size: .84rem; }

.araya-brief .btn-primary { background: var(--accent); border-color: var(--accent);
    font-weight: 600; font-size: .82rem; }
.araya-brief .btn-primary:hover { background: #1a43b8; border-color: #1a43b8; }
.araya-brief .btn-outline-secondary { border-color: var(--line); color: var(--ink-2);
    font-weight: 600; font-size: .82rem; background: var(--surface); }
.araya-brief .btn-outline-secondary:hover { background: var(--canvas); color: var(--ink);
    border-color: var(--ink-3); }

.araya-brief pre.chat { white-space: pre-wrap; word-break: break-word; background: #15181d;
    color: #dfe3e9; border-radius: var(--r); padding: 20px; font-size: .8rem; margin: 0;
    font-family: var(--bs-font-monospace, SFMono-Regular, Menlo, Consolas, monospace); line-height: 1.65; }

.araya-brief .alert { border-radius: var(--r); border: 1px solid var(--line);
    border-left: 3px solid currentColor; font-size: .875rem; }

/* One quiet entrance, staggered, so the page assembles rather than flashes. */
@media (prefers-reduced-motion: no-preference) {
    .araya-brief .stats,
    .araya-brief .gaps,
    .araya-brief .panel-card { animation: arayaRise .4s cubic-bezier(.22,.61,.36,1) backwards; }
    .araya-brief .stats      { animation-delay: .02s; }
    .araya-brief .gaps       { animation-delay: .06s; }
    .araya-brief .panel-card { animation-delay: .10s; }
}
@keyframes arayaRise { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }

@media (max-width: 991px) {
    .araya-brief .stats { grid-template-columns: repeat(2, 1fr); }
    .araya-brief .stat:nth-child(odd) { border-left: 0; }
    .araya-brief .stat { border-top: 1px solid var(--line-2); }
    .araya-brief .stats .stat:nth-child(-n+2) { border-top: 0; }
}
@media (max-width: 575px) {
    .araya-brief .stats { grid-template-columns: 1fr; }
    .araya-brief .stat { border-left: 0; }
}
</style>

<div class="araya-brief container-fluid">

    <div class="page-head">
        <div>
            <h1><i class="bi bi-sunrise"></i>Araya Daily Brief</h1>
            <div class="page-sub">
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

    <?php // Said plainly, because an absent "Calls with errors" panel otherwise
          // reads as "there were none" -- the one wrong conclusion to draw. ?>
    <?php if (!empty($brief['call_errors_unavailable']) && empty($brief['gcal_unavailable'])) { ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> The failed-call list could not be fetched, so a missing
        &ldquo;Calls with errors&rdquo; panel does not mean there were none.
    </div>
    <?php } ?>

    <?php $o = $brief['overview']; ?>
    <?php // One hairline-divided strip rather than five floating cards: these
          // five numbers are a single statement about the fleet. Colour comes
          // from a class, not an inline style, so the palette lives in one place. ?>
    <div class="stats">
        <?php foreach ([
            ['Live', $o['live'], 'is-live'],
            ['Waiting to cancel', $o['pendingCancel'], 'is-cancel'],
            ['In setup', $o['setup'], 'is-setup'],
            ['Cancelled', $o['cancelled'], 'is-gone'],
            ['All clients', $o['total'], ''],
        ] as [$label, $n, $cls]) { ?>
        <div class="stat <?php echo $cls; ?>">
            <div class="n"><?php echo (int)$n; ?></div>
            <div class="l"><?php echo arayaH($label); ?></div>
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
                        <?php // Same Thai instruction the Chat message sends, so
                              // whoever reads either one is told the same thing. ?>
                        <td class="note"><?php echo arayaH(arayaChatGapDetail($g)); ?></td>
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

    <?php // The two lists someone acts on first: shops to ring today, and
          // shops that will run dry before their allowance refreshes. ?>
    <?php if ($brief['atRisk'] || $brief['runningOut']) { ?>
    <div class="row g-3 mb-4">
        <?php if ($brief['atRisk']) { ?>
        <div class="col-lg-6">
            <div class="panel-card urgent">
                <h2>&#128680; Needs a call today <span class="note fw-normal">(out of minutes, or <?php echo ARAYA_AT_RISK_MINUTES; ?> or fewer left)</span></h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Shop</th><th class="text-end">Minutes</th><th class="text-end">Refresh in</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['atRisk'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?>
                                    <?php if ($r['overdrawn']) { ?><span class="badge bg-danger ms-1">overdrawn</span><?php } ?></td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td class="text-end"><?php echo $r['days_until_refresh'] === null ? '-' : arayaH($r['days_until_refresh']) . ' d'; ?></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($brief['runningOut']) { ?>
        <div class="col-lg-6">
            <div class="panel-card soon">
                <h2>&#9203; Will run out before the refresh <span class="note fw-normal">(projected from this period's own usage)</span></h2>
                <div class="body table-responsive">
                    <table class="table align-middle">
                        <?php // Four columns, not six: in half the page width six
                              // forced every shop name to wrap. The rate is the
                              // evidence for "empty in", so they belong together. ?>
                        <thead><tr><th>Shop</th><th class="text-end">Minutes</th><th class="text-end">Empty in</th><th>Owner</th></tr></thead>
                        <tbody>
                        <?php foreach ($brief['runningOut'] as $r) { ?>
                            <tr>
                                <td class="fw-semibold"><?php echo arayaH($r['name']); ?>
                                    <div class="note">at <?php echo arayaH($r['per_day']); ?> mins/day &middot; refresh in <?php echo arayaH($r['days_until_refresh']); ?> d</div></td>
                                <td class="text-end"><?php echo arayaMinsCell($r['remaining_minutes']); ?></td>
                                <td class="text-end"><span class="num mins-amber"><?php echo $r['days_until_empty'] === null ? '-' : arayaH($r['days_until_empty']) . ' d'; ?></span></td>
                                <td><?php echo arayaOwner($r['po']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <?php // Failed calls sit with the urgent panels: a call that errored is a
          // customer who got nothing, and the recording link is what saves
          // someone hunting for it in the VAPI dashboard call by call. ?>
    <?php if (!empty($brief['callErrors'])) { ?>
    <div class="panel-card urgent mb-4" id="callErrors">
        <h2>&#128222; Calls with errors <span class="note fw-normal">(last 30 days, newest first; <?php echo count($brief['callErrors']); ?> shown)</span></h2>
        <div class="body table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Shop</th><th>When (Bangkok)</th><th>What went wrong</th><th class="text-end">Length</th><th>Recording</th></tr></thead>
                <tbody>
                <?php foreach ($brief['callErrors'] as $e) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH(arayaClean($e['shop_name'] ?? '')); ?></td>
                        <td><?php echo arayaH(arayaChatWhen($e['started_at'] ?? '')); ?></td>
                        <td class="note"><?php echo ($e['outcome'] ?? '') === '' ? '<span class="text-muted">not stated</span>' : arayaH($e['outcome']); ?></td>
                        <td class="text-end"><?php echo ($e['duration_sec'] ?? null) === null ? '<span class="num mins-unknown">?</span>' : '<span class="num">' . arayaH($e['duration_sec']) . ' s</span>'; ?></td>
                        <td><?php if (($e['recording_url'] ?? null) !== null) { ?><a href="<?php echo arayaH($e['recording_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-play-circle"></i> Listen</a><?php } else { ?><span class="text-muted">no recording</span><?php } ?></td>
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
                <?php if (!$brief['live']) { ?><tr><td colspan="5" class="empty">No live shops.</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php // Reference data, so it sits below the lists someone acts on. Both
          // windows in one table: 7 days alone cannot tell a bad week from a
          // bad month, and that is usually the question being asked. ?>
    <div class="panel-card mb-4" id="callQuality">
        <h2>Call quality <span class="note fw-normal">(<strong>?</strong> = gcal sent no figure, which is not the same as 0 &middot; short = under 15 s &middot; silent = caller said nothing and it timed out)</span></h2>
        <div class="body table-responsive">
            <table class="table table-hover align-middle calls">
                <thead>
                    <tr><th rowspan="2">Shop</th><th class="grp win" colspan="5">Last 7 days</th><th class="grp win" colspan="5">Last 30 days</th></tr>
                    <tr>
                        <?php foreach (ARAYA_CALL_WINDOWS as $ignored) { ?>
                        <th class="text-end win">Calls</th><th class="text-end">Forwarded</th><th class="text-end">Short</th><th class="text-end">Silent</th><th class="text-end">Minutes</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($brief['live'] as $r) { ?>
                    <tr>
                        <td class="fw-semibold"><?php echo arayaH($r['name']); ?></td>
                        <?php if (!is_array($r['calls'] ?? null)) { ?>
                        <td colspan="10" class="win note"><?php echo !empty($r['gcal_missing']) ? 'Not in gcal &ndash; no call data' : 'gcal sent no call data for this shop'; ?></td>
                        <?php } else { foreach (ARAYA_CALL_WINDOWS as $w) {
                            $win = $r['calls'][$w] ?? null;
                            if (!is_array($win)) { ?>
                        <td colspan="5" class="win note">no figures for this window</td>
                            <?php } else {
                                echo arayaCallCell($win['total'] ?? null, 'win');
                                foreach (['forwarded', 'short', 'silent', 'minutes'] as $k) {
                                    echo arayaCallCell($win[$k] ?? null);
                                }
                            }
                        } } ?>
                    </tr>
                <?php } ?>
                <?php if (!$brief['live']) { ?><tr><td colspan="11" class="empty">No live shops.</td></tr><?php } ?>
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
                        <?php if (!$brief['upsell']) { ?><tr><td colspan="4" class="empty">None today.</td></tr><?php } ?>
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
                <h2>&#128201; Live but barely using it</h2>
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
                        <?php if (!$brief['idleNew'] && !$brief['idleQuiet']) { ?><tr><td colspan="4" class="empty">None today.</td></tr><?php } ?>
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
                        <?php if (!$brief['setup']) { ?><tr><td colspan="4" class="empty">None.</td></tr><?php } ?>
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
                        <?php if (!$brief['cancelling']) { ?><tr><td colspan="4" class="empty">None.</td></tr><?php } ?>
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
