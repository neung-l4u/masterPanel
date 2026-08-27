<?php
/**
 * Browser view for the template submission details.
 *
 * tpSubmittedDetails.php still builds $topData as inline-styled tables because
 * that same string is used as the body of the notification email, where only
 * table markup renders reliably. This file is the on-screen version of the same
 * data: a compact dashboard layout that fits without endless scrolling.
 *
 * Expects (from the including page):
 *   $id, $project, $dueDate, $folderName, $openingHours, $pickupAndDelivery,
 *   $domainUser, $domainPass, $hostingUser, $hostingPass, $prettyJson,
 *   $renderer, $templateFolder, $placeholderLabels
 */

/** Escapes a value for HTML output, showing a dash when it is empty. */
function tpText($value, $fallback = '—')
{
    $value = trim((string)$value);

    return $value === '' ? $fallback : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Renders one label/value row of a card. */
function tpRow($label, $value, $mono = false)
{
    $class = $mono ? 'v mono' : 'v';

    return '<div class="row"><span class="k">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        . '</span><span class="' . $class . '">' . $value . '</span></div>';
}

/**
 * Renders a credential row masked behind a click-to-reveal toggle, so the
 * page can be shared on screen without exposing passwords by default.
 */
function tpSecretRow($label, $value)
{
    $value = trim((string)$value);
    if ($value === '' || $value === '-') {
        return tpRow($label, '—', true);
    }

    $safe = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    return '<div class="row"><span class="k">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
        . '<span class="v mono secret"><span class="dots">••••••••</span>'
        . '<span class="reveal" hidden>' . $safe . '</span>'
        . '<button type="button" class="eye" aria-label="Show ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">show</button>'
        . '</span></div>';
}

/**
 * Renders "Sunday : 11:30 - 15:00<br>Monday : ..." as aligned label/value rows.
 * Falls back to the raw text when it is not in the day-per-line shape (the
 * custom opening hours are free text).
 */
function tpSchedule($html)
{
    $text = trim((string)$html);
    if ($text === '') {
        return '<div class="muted-empty">No data</div>';
    }

    $lines = preg_split('/<br\s*\/?>/i', $text);
    $out = '';
    $matched = 0;

    foreach ($lines as $line) {
        $line = trim(strip_tags($line));
        if ($line === '') {
            continue;
        }
        // "Sunday : 11:30 - 15:00" -> label + value
        if (preg_match('/^([A-Za-z]+)\s*:\s*(.*)$/', $line, $m)) {
            $matched++;
            // An empty value means the day was left blank in the form, which
            // is not the same as the shop being closed that day.
            $value = trim($m[2]);
            $out .= '<div class="row"><span class="k">' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</span>'
                . '<span class="v">' . ($value === '' || $value === '-'
                    ? '<span class="muted-empty">No data</span>'
                    : htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) . '</span></div>';
        }
    }

    if ($matched === 0) {
        // Free-text hours: still wrap it in a row so it picks up the same
        // horizontal padding as the day-per-line version.
        return '<div class="row"><span class="v">'
            . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'))
            . '</span></div>';
    }

    return $out;
}

$shopType = tpText($project['shopType']);
$templateNo = 'Template No. 0' . tpText($project['selectedTemplate'], '?');
$socialLinks = array(
    'Facebook'  => $project['facebookURL'],
    'Instagram' => $project['instagramURL'],
    'Youtube'   => $project['youtubeURL'],
    'Tiktok'    => $project['tiktokURL'],
);
$hasSocial = false;
foreach ($socialLinks as $url) {
    if (!empty($url)) {
        $hasSocial = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo tpText($project['projectName'], 'Template Submission'); ?> · Template Submission</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Chivo+Mono:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&display=swap" rel="stylesheet">
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<style>
    /* Bento: solid white cards floating on a saturated blue wash. The colour
       lives entirely in the background - every panel stays white so the data
       inside it reads cleanly. */
    :root {
        --brand: #0a66d6;
        --brand-ink: #084ea8;
        --ink: #1c1c1e;
        --muted: #6b7180;
        --line: #e8e9ed;
        --line-strong: #d8dae0;
        --bg: #3d8bf0;
        --card: #ffffff;
        --card-tint: #f6f8fc;
        --accent: #c2410c;
        --accent-2: #6f5bd6;
        --radius: 22px;
        --radius-sm: 14px;
        /* Soft, wide, low-opacity: the shadow of something light resting on
           a coloured surface. */
        --shadow: 0 10px 34px -12px rgba(12, 48, 110, 0.34),
                  0 2px 8px -3px rgba(12, 48, 110, 0.16);
        --shadow-lift: 0 20px 48px -14px rgba(12, 48, 110, 0.44),
                       0 4px 12px -4px rgba(12, 48, 110, 0.2);
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        background: var(--bg);
        color: var(--ink);
        font-family: "Poppins", "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        font-feature-settings: "kern" 1;
        -webkit-font-smoothing: antialiased;
    }

    /* A single blue wash, brighter towards the top left, with a soft light
       source behind the content. Nothing textured: the cards supply all the
       structure and the background just holds them. */
    body::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(120% 90% at 18% 4%, #79b4fb 0%, transparent 58%),
            radial-gradient(110% 80% at 92% 22%, #4f97f4 0%, transparent 62%),
            radial-gradient(120% 100% at 50% 108%, #2f6fd8 0%, transparent 66%),
            linear-gradient(168deg, #4e9bf5 0%, #3d8bf0 46%, #2d76dd 100%);
    }

    .wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px 72px; }

    /* One page-load reveal, staggered per card, rather than scattered
       micro-interactions. */
    @keyframes riseIn {
        from { opacity: 0; transform: translateY(16px) scale(.98); }
        to   { opacity: 1; transform: none; }
    }
    @media (prefers-reduced-motion: no-preference) {
        .card {
            opacity: 0;
            animation: riseIn 0.5s cubic-bezier(.2,.7,.3,1) forwards;
        }
        .card:nth-child(1) { animation-delay: .04s; }
        .card:nth-child(2) { animation-delay: .09s; }
        .card:nth-child(3) { animation-delay: .14s; }
        .card:nth-child(4) { animation-delay: .19s; }
        .card:nth-child(5) { animation-delay: .24s; }
        .card:nth-child(6) { animation-delay: .29s; }
        .card:nth-child(7) { animation-delay: .34s; }
        .card:nth-child(8) { animation-delay: .39s; }
    }

    /* Masthead: the page title sits directly on the blue, no bar behind it,
       so the first white shape the eye meets is a card. */
    header.topbar {
        position: relative;
        z-index: 20;
        /* A faint scrim so white text clears contrast over the lightest part
           of the wash, without reading as a bar. */
        background: linear-gradient(180deg, rgba(10, 46, 105, 0.28), transparent);
        border: 0;
    }
    /* One line: a marker, the project name, and its metadata trailing
       behind it. Everything else lives in the cards below. */
    .topbar-inner {
        max-width: 1180px;
        margin: 0 auto;
        padding: 30px 28px 4px;
        display: flex;
        align-items: baseline;
        gap: 14px;
        flex-wrap: wrap;
    }
    .eyebrow {
        display: flex;
        align-items: center;
        gap: 9px;
        flex: 0 0 auto;
    }
    .eyebrow .mark {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ffffff;
        flex: 0 0 auto;
    }
    .eyebrow .no {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 10px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #ffffff;
    }

    /* Everything in the masthead sits on the blue, so it is white. */
    .topbar h1 {
        margin: 0;
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 22px;
        font-weight: 600;
        line-height: 1.25;
        /* Poppins is geometric and already wide; it needs less negative
           tracking than a grotesque at the same size. */
        letter-spacing: -0.005em;
        color: #ffffff;
        text-shadow: 0 1px 12px rgba(12, 48, 110, 0.28);
    }

    /* Metadata reads as quiet trailing text, not a table. */
    .meta-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0 14px;
        margin-left: auto;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 11px;
        color: #ffffff;
    }
    .meta-cell { min-width: 0; }
    .meta-cell .val { letter-spacing: 0; }
    .meta-cell .accent { color: #ffe0b8; }

    /* Bento grid: a fixed four-column track so cards can claim their own
       width and height. Cards stretch to fill the row rather than sitting at
       their natural height, which is what gives the layout its blocky look. */
    .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: minmax(128px, auto);
        gap: 16px;
        margin-top: 28px;
        align-items: stretch;
    }

    /* Bento spans. Project anchors the layout; Infrastructure sits directly
       beneath it so the left half stays a single column of wide cards. */
    .card.b-project  { grid-column: span 2; }
    .card.b-schedule { grid-column: span 2; }
    .card.b-wide     { grid-column: span 2; }
    .card.span2      { grid-column: 1 / -1; }

    /* Three columns: the spans still work, the rows just re-flow. */
    @media (max-width: 1080px) {
        .grid { grid-template-columns: repeat(3, 1fr); }
    }

    /* Two columns: Project keeps its emphasis, schedules go full width. */
    @media (max-width: 820px) {
        .grid { grid-template-columns: repeat(2, 1fr); }
        .card.b-schedule,
        .card.b-wide { grid-column: 1 / -1; }
    }

    /* Single column: every span collapses so nothing overflows. */
    @media (max-width: 560px) {
        .grid { grid-template-columns: 1fr; grid-auto-rows: auto; }
        .card.b-project,
        .card.b-schedule,
        .card.b-wide,
        .card.span2 { grid-column: auto; grid-row: auto; }

        /* Back to stacked rows once the card is narrow again. */
        .card.b-project .row,
        .card.b-wide .row,
        .card.b-schedule .row,
        .card.span2 .row { flex-direction: column; gap: 1px; }
        .card.b-project .k,
        .card.b-wide .k,
        .card.b-schedule .k,
        .card.span2 .k { flex: none; }
    }

    /* Solid white tiles with generous corners, resting on the blue. */
    .card {
        position: relative;
        background: var(--card);
        border: 0;
        border-radius: var(--radius);
        padding: 0 0 6px;
        /* Grid items default to min-content width; without this a long
           unbroken value (a URL, a path) stretches the whole track. */
        min-width: 0;
        overflow-wrap: anywhere;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: box-shadow .24s ease, transform .24s ease;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lift);
    }
    /* Counter drives the index number printed on each card header. */
    .grid { counter-reset: card; }
    .card { counter-increment: card; }
    /* Title bar: a rounded colour tile carrying the section number, the name
       beside it, and a hairline separating it from the rows below. */
    .card h2 {
        position: relative;
        display: flex;
        align-items: center;
        gap: 11px;
        margin: 0 0 4px;
        padding: 16px 18px 14px;
        background: linear-gradient(180deg, #d7e1ee, #e8f1fa);
        border-bottom: 1px solid var(--line);
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 15.5px;
        font-weight: 600;
        letter-spacing: -0.012em;
        text-transform: none;
        color: var(--ink);
    }
    .card h2::before {
        content: counter(card, decimal-leading-zero);
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: linear-gradient(160deg, #4f9bf5, #2f6fd8);
        color: #ffffff;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0;
        box-shadow: 0 4px 10px -4px rgba(24, 76, 160, 0.7);
    }
    /* A few tiles pick up their own hue, the way each app does in iCloud. */
    .card:nth-child(3) h2::before { background: linear-gradient(160deg, #ffd25e, #f0a72c); }
    .card:nth-child(4) h2::before { background: linear-gradient(160deg, #7fd3a0, #35a86a); }
    .card:nth-child(5) h2::before { background: linear-gradient(160deg, #9db4ff, #5a6ee0); }
    .card:nth-child(6) h2::before { background: linear-gradient(160deg, #ffa987, #ef6d3d); }
    .card:nth-child(7) h2::before { background: linear-gradient(160deg, #b79bff, #7d5ae0); }
    /* The anchor card leads with the accent tile. */
    .card.b-project h2::before {
        background: linear-gradient(160deg, #ff9f6b, #e2570f);
    }

    /* The card you act on takes a tinted body so it reads as the one
       interactive tile among the reference panels. */
    .card.b-action {
        background: linear-gradient(180deg, #f4f8ff 0%, #ffffff 40%);
    }
    .card.b-action h2 { color: var(--brand-ink); }
    .card.b-action h2::before {
        background: linear-gradient(160deg, #6aa8f7, #1f5fc4);
    }
    .card.b-action:hover {
        transform: none; /* it holds a form; lifting it under the cursor is noise */
        box-shadow: var(--shadow);
    }
    /* Cards carry no padding of their own, so the block-level content inside
       the action card supplies its own gutter. */
    .card > form,
    .card > .muted-empty { padding: 4px 18px 14px; }

    /* Rows are list items: hairline between them, indented so the rule stops
       short of the tile edge. */
    .row {
        display: flex;
        flex-direction: column;
        gap: 1px;
        margin: 0 18px;
        padding: 9px 0;
        border-bottom: 1px solid var(--line);
    }
    .row:last-child { border-bottom: 0; }
    .k {
        font-size: 11.5px;
        font-weight: 400;
        letter-spacing: 0;
        text-transform: none;
        color: var(--muted);
    }
    .v {
        min-width: 0;
        overflow-wrap: anywhere;
        font-size: 13.5px;
        /* Poppins runs heavier than a grotesque at the same weight, so plain
           values sit at 400 and only the project name is emphasised. */
        font-weight: 400;
        letter-spacing: 0;
    }

    /* Wide cards have room for the label beside the value again. */
    .card.b-project .row,
    .card.b-wide .row,
    .card.b-schedule .row,
    .card.span2 .row {
        flex-direction: row;
        gap: 16px;
        align-items: baseline;
    }
    .card.b-project .k,
    .card.b-wide .k,
    .card.b-schedule .k,
    .card.span2 .k {
        flex: 0 0 132px;
        padding-top: 2px;
    }
    .card.b-project .v,
    .card.b-wide .v,
    .card.b-schedule .v,
    .card.span2 .v { flex: 1 1 auto; }

    /* Days line up as a tabular column of times. */
    .card.b-schedule .v {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12.5px;
        font-weight: 400;
        font-variant-numeric: tabular-nums;
    }

    /* The owner is the one emphasised value in the anchor card. */
    .card.b-project .row:first-child .v {
        font-size: 15px;
        font-weight: 500;
        letter-spacing: -0.01em;
    }

    .v.mono {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12.5px;
        font-weight: 400;
    }
    .v a {
        color: var(--brand);
        text-decoration-color: color-mix(in srgb, var(--brand) 35%, transparent);
        text-underline-offset: 2px;
    }
    .v a:hover { color: var(--accent); text-decoration-color: currentColor; }

    .swatches { display: flex; gap: 14px; flex-wrap: wrap; }
    .swatch {
        display: flex;
        flex-direction: column;
        gap: 5px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 10px;
        letter-spacing: 0.04em;
        color: var(--muted);
    }
    /* Swatches read as paint chips rather than inline dots. */
    .dot {
        width: 54px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid var(--line-strong);
        box-shadow: 0 3px 8px -4px rgba(12, 48, 110, 0.4);
    }
    .sw-name { color: var(--ink); font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; }
    .sw-hex { margin-top: -3px; }

    .muted-empty {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 10.5px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        font-style: normal;
    }

    .secret { display: flex; align-items: center; gap: 9px; }
    .secret .dots {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        letter-spacing: 2px;
    }
    .eye {
        border: 1px solid var(--line-strong);
        background: #ffffff;
        color: var(--brand-ink);
        border-radius: 999px;
        padding: 3px 11px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        cursor: pointer;
        flex: 0 0 auto;
        box-shadow: none;
        transition: background .16s ease, color .16s ease, border-color .16s ease,
                    box-shadow .16s ease, transform .14s ease;
    }
    .eye:hover {
        background: var(--brand);
        border-color: var(--brand);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px -4px rgba(12, 48, 110, 0.45);
    }
    .eye:active { transform: translateY(0); box-shadow: none; }
    .eye:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(10, 102, 214, 0.24);
    }

    /* Download + edit panel */
    .fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
        gap: 10px 16px;
        margin-bottom: 14px;
    }
    .field label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: none;
        color: var(--ink);
        margin-bottom: 5px;
    }
    .field .token {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 400;
        color: var(--accent);
        margin-left: 5px;
        text-transform: none;
        letter-spacing: 0;
    }
    /* Editable fields announce themselves: solid white against the tinted
       card, a visible rim, and an inset shadow that reads as a well. */
    .field { position: relative; }
    .field input {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12.5px;
        background: #ffffff;
        color: var(--ink);
        box-shadow: none;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }
    .field input::placeholder { color: rgba(76, 90, 120, 0.55); }
    .field input:hover { border-color: #b9c0cc; }
    .field input:focus {
        outline: none;
        border-color: var(--brand);
        transform: translateY(-1px);
        box-shadow:
            0 0 0 4px rgba(10, 102, 214, 0.16),
            0 6px 16px -10px rgba(12, 48, 110, 0.4);
    }
    /* An underline that draws itself across the focused field. */
    .field::after {
        content: "";
        position: absolute;
        left: 13px;
        bottom: 0;
        width: 0;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, var(--brand), var(--accent-2));
        transition: width .24s cubic-bezier(.2,.7,.3,1);
        pointer-events: none;
    }
    .field:focus-within::after { width: calc(100% - 26px); }

    .note {
        border-left: 2px solid var(--accent);
        background: #fff4ec;
        color: var(--ink);
        border-radius: 0 10px 10px 0;
        padding: 8px 12px;
        font-size: 12.5px;
        margin-bottom: 16px;
    }

    /* The action bar is its own tray, not just a strip below the fields.
       Two stacked rows: the primary download, then the per-page buttons. */
    .actions {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
        margin-top: 18px;
        padding: 16px 18px;
        border-radius: 14px;
        background: var(--card-tint);
        border: 1px solid var(--line);
        box-shadow: none;
    }

    /* Per-page buttons stay on one line and scroll sideways when there are
       more of them than fit, rather than wrapping into ragged rows. */
    .actions-single {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        overflow-x: auto;
        /* overflow-x also clips vertically, which would cut the top of a
           button as it lifts on hover - so leave room on every side. */
        padding: 5px 5px 9px;
        margin: -5px -5px -5px;
        scrollbar-width: thin;
    }
    .actions-single > .btn { flex: 0 0 auto; }
    .actions-single::-webkit-scrollbar { height: 6px; }
    .actions-single::-webkit-scrollbar-thumb {
        background: var(--line-strong);
        border-radius: 3px;
    }

    .btn {
        position: relative;
        overflow: hidden;
        padding: 11px 20px;
        border-radius: 999px;
        border: 1px solid transparent;
        background: linear-gradient(160deg, #2f86f0, #1360d4);
        color: #ffffff;
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: none;
        cursor: pointer;
        box-shadow: 0 6px 16px -8px rgba(12, 48, 110, 0.7);
        transition: box-shadow .18s ease, transform .14s ease, filter .18s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -10px rgba(12, 48, 110, 0.8);
        filter: brightness(1.05);
    }
    /* Press feedback: the button settles back onto the surface. */
    .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px -4px rgba(12, 48, 110, 0.7);
        filter: brightness(0.95);
    }
    .btn:focus-visible {
        outline: none;
        box-shadow:
            0 0 0 4px rgba(10, 102, 214, 0.28),
            0 6px 16px -8px rgba(12, 48, 110, 0.7);
    }

    .btn.ghost {
        padding: 10px 16px;
        background: var(--card-tint);
        border: 1px solid var(--line-strong);
        color: var(--brand);
        font-weight: 500;
        box-shadow: none;
    }
    .btn.ghost:hover {
        background: #eef3fb;
        border-color: #c3cbd8;
        color: var(--brand-ink);
        transform: translateY(-1px);
        box-shadow: 0 6px 14px -10px rgba(12, 48, 110, 0.6);
        filter: none;
    }
    .btn.ghost:active {
        transform: translateY(0);
        box-shadow: none;
    }
    .btn.ghost:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px rgba(19, 89, 204, 0.24);
    }

    .actions-label {
        flex: 0 0 auto;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 9.5px;
        letter-spacing: 0.13em;
        text-transform: uppercase;
        color: var(--muted);
        margin-right: 2px;
        white-space: nowrap;
    }

    details.raw { margin-top: 16px; }
    details.raw > summary {
        cursor: pointer;
        background: var(--card);
        border: 0;
        border-radius: var(--radius);
        padding: 15px 18px;
        font-size: 13.5px;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: none;
        color: var(--ink);
        list-style: none;
        box-shadow: var(--shadow);
        transition: background .18s ease, color .18s ease;
    }
    details.raw > summary:hover {
        background: var(--card-tint);
        color: var(--brand);
    }
    details.raw > summary:focus-visible {
        outline: none;
        box-shadow: var(--shadow), 0 0 0 4px rgba(10, 102, 214, 0.24);
    }
    details.raw > summary::-webkit-details-marker { display: none; }
    details.raw > summary::before { content: "▸ "; color: var(--brand); }
    details.raw[open] > summary::before { content: "▾ "; }
    details.raw[open] > summary { border-radius: var(--radius) var(--radius) 0 0; }
    details.raw pre {
        margin: 0;
        /* The raw payload keeps a dark surface - long JSON is easier to scan
           against one, and it reads as a distinct technical panel. */
        background: #10182c;
        border: 0;
        color: #d5e2ff;
        padding: 18px;
        border-radius: 0 0 var(--radius) var(--radius);
        box-shadow: var(--shadow);
        overflow: auto;
        max-height: 460px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12px;
        line-height: 1.55;
    }

    @media (max-width: 640px) {
        .topbar-inner { padding: 20px 16px 4px; gap: 10px; }
        .wrap { padding: 0 16px 44px; }
        .card h2 { padding: 14px 15px 12px; font-size: 14.5px; }
        .row { margin: 0 15px; }
        .topbar h1 { font-size: 18px; }
        /* The due date drops below the title rather than being squeezed. */
        .meta-strip { margin-left: 0; flex-basis: 100%; }
    }
</style>
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">
        <div class="eyebrow">
            <span class="mark"></span>
            <span class="no">No. <?php echo tpText($id); ?></span>
        </div>
        <h1><?php echo tpText($project['projectName'], 'Untitled project'); ?></h1>
        <div class="meta-strip">
            <!-- Everything else is repeated in the Project card below. -->
            <div class="meta-cell">
                <span class="val">Due <span class="accent"><?php echo tpText($dueDate); ?></span></span>
            </div>
        </div>
    </div>
</header>

<div class="wrap">
    <div class="grid">

        <div class="card b-project">
            <h2>Project</h2>
            <?php
            echo tpRow('Owner', tpText($project['PO']));
            echo tpRow('Type', $shopType . ' · ' . $templateNo);
            echo tpRow('Country', tpText($project['country']));
            echo tpRow('Due date', tpText($dueDate));
            echo tpRow('Project code', tpText('WEB-' . date('ymd') . ' ' . $project['projectName']), true);
            echo tpRow('Resources', tpText($folderName), true);
            ?>
        </div>

        <div class="card">
            <h2>Contact</h2>
            <?php
            echo tpRow('Email', tpText($project['email']));
            echo tpRow('Phone', tpText($project['phone']));
            echo tpRow('City', tpText(isset($project['city']) ? $project['city'] : ''));
            echo tpRow('Location', tpText($project['address']));

            // Most projects carry one social link at most, so they sit here
            // rather than in a card of their own.
            if ($hasSocial) {
                foreach ($socialLinks as $label => $url) {
                    if (empty($url)) {
                        continue;
                    }
                    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    echo tpRow($label, '<a href="' . $safeUrl . '" target="_blank" rel="noopener">' . $safeUrl . '</a>');
                }
            }
            ?>
        </div>

        <div class="card">
            <h2>Theme</h2>
            <?php echo tpRow('Logo', tpText($project['logo']), true); ?>
            <div class="row">
                <span class="k">Colors</span>
                <span class="v">
                    <span class="swatches">
                    <?php
                    $colorLabels = array('colorTheme1' => 'Primary', 'colorTheme2' => 'Secondary', 'colorTheme3' => 'Accent');
                    $anyColor = false;
                    foreach ($colorLabels as $key => $label) {
                        $hex = trim((string)$project[$key]);
                        if ($hex === '') {
                            continue;
                        }
                        $anyColor = true;
                        echo '<span class="swatch"><span class="dot" style="background:'
                            . htmlspecialchars($hex, ENT_QUOTES, 'UTF-8') . '"></span>'
                            . '<span class="sw-name">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
                            . '<span class="sw-hex">' . htmlspecialchars(strtoupper($hex), ENT_QUOTES, 'UTF-8') . '</span>'
                            . '</span>';
                    }
                    if (!$anyColor) {
                        echo '<span class="muted-empty">No data</span>';
                    }
                    ?>
                    </span>
                </span>
            </div>
        </div>

        <div class="card b-wide">
            <h2>Infrastructure</h2>
            <?php
            // Domain and hosting share the same shape, and 39% of projects
            // carry credentials for neither, so they read better as one block.
            echo tpRow('Domain name', tpText($project['domainName']), true);
            echo tpRow('Domain provider', tpText($project['domainProvider']));
            if ($project['domainHave'] != 0) {
                echo tpRow('Domain username', tpText($domainUser), true);
                echo tpSecretRow('Domain password', $domainPass);
            }
            echo tpRow('Hosting provider', tpText($project['HostingProvider']));
            if ($project['hostingHave'] != 0) {
                echo tpRow('Hosting username', tpText($hostingUser), true);
                echo tpSecretRow('Hosting password', $hostingPass);
            }
            ?>
        </div>

        <div class="card b-schedule">
            <h2>Opening hours</h2>
            <?php echo tpSchedule($openingHours); ?>
        </div>

        <div class="card b-schedule">
            <h2>Pickup &amp; delivery</h2>
            <?php echo tpSchedule($pickupAndDelivery); ?>
        </div>

        <div class="card b-wide">
            <h2>Systems</h2>
            <?php
            $anySystem = false;
            if ($project['gloriaHave'] == 1) {
                $anySystem = true;
                echo tpRow('Gloria Food', 'Enabled');
                echo tpRow('Order URL', tpText($project['orderURL']), true);
                echo tpRow('Table URL', tpText($project['tableURL']), true);
            }
            if ($project['amelia'] == 1) {
                $anySystem = true;
                echo tpRow('Amelia', 'Enabled');
            }
            if (!$anySystem) {
                echo '<div class="muted-empty">No system selected</div>';
            }
            ?>
        </div>

        <div class="card span2 b-action" id="template">
            <h2>Elementor template — ready to import</h2>
            <?php if ($templateFolder === null): ?>
                <div class="muted-empty">
                    No Elementor template files for <?php echo $shopType . ' ' . $templateNo; ?>.
                </div>
            <?php else: ?>
                <form method="post" action="downloadTemplateJson.php">
                    <input type="hidden" name="projectID" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="note">
                        Edit any value to change what gets written into the JSON. Changes apply to the
                        download only and are not saved to the project.
                    </div>

                    <div class="fields">
                        <?php foreach ($renderer->getValues() as $name => $value):
                            $label = isset($placeholderLabels[$name]) ? $placeholderLabels[$name] : $name;
                            $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="field">
                                <label for="f_<?php echo $safeName; ?>">
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="token">$__<?php echo $safeName; ?>__!</span>
                                </label>
                                <input type="text" id="f_<?php echo $safeName; ?>"
                                       name="values[<?php echo $safeName; ?>]"
                                       value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="actions">
                        <button type="submit" name="file" value="" class="btn">
                            Download ZIP (<?php echo htmlspecialchars($templateFolder, ENT_QUOTES, 'UTF-8'); ?>)
                        </button>
                        <!-- Per-page buttons get their own row so they never wrap
                             apart from each other. -->
                        <div class="actions-single">
                            <span class="actions-label">or a single page:</span>
                            <?php foreach ($renderer->listTemplateFiles($templateFolder) as $fileName => $filePath):
                                // Shorten "Template Restaurant 1  Home Ver. 2.1.4.json" down to "Home".
                                $short = preg_replace('/\s*Ver\..*$/', '', $fileName);
                                $short = trim(preg_replace('/^Template\s+\w+\s+\d+\s*/', '', $short));
                                if ($short === '') {
                                    $short = $fileName;
                                }
                                ?>
                                <button type="submit" name="file"
                                        value="<?php echo htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8'); ?>"
                                        class="btn ghost"><?php echo htmlspecialchars($short, ENT_QUOTES, 'UTF-8'); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>

    </div>

    <details class="raw">
        <summary>Raw page details JSON</summary>
        <pre><?php echo $prettyJson; ?></pre>
    </details>
</div>

<script>
    // Click-to-reveal for the credential rows.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.eye');
        if (!btn) return;
        var box = btn.closest('.secret');
        var dots = box.querySelector('.dots');
        var real = box.querySelector('.reveal');
        var shown = !real.hidden;
        real.hidden = shown;
        dots.hidden = !shown;
        btn.textContent = shown ? 'show' : 'hide';
    });
</script>

</body>
</html>
