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
        return '<div class="v">' . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) . '</div>';
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
    /* Glassmorphism: a deep gradient-mesh ground with frosted panels floating
       above it. Glass only reads as glass when there is something coloured
       behind it, so the background does the heavy lifting. */
    :root {
        --brand: #1359cc;
        --brand-ink: #0b4bb8;
        --ink: #0f1b33;
        --muted: #4c5a78;
        --line: rgba(19, 48, 99, 0.13);
        --line-strong: rgba(19, 48, 99, 0.24);
        --bg: #eaf2fd;
        --accent: #b03806;
        --accent-2: #6f5bd6;
        --glass: rgba(255, 255, 255, 0.52);
        --glass-hi: rgba(255, 255, 255, 0.72);
        --radius: 18px;
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

    /* Gradient mesh: four wide colour pools that the glass samples from.
       Fixed so the panels appear to slide over a still surface. */
    body::before {
        content: "";
        position: fixed;
        inset: -20%;
        z-index: -2;
        background:
            radial-gradient(44% 40% at 16% 10%, rgba(96, 156, 255, 0.62), transparent 68%),
            radial-gradient(40% 44% at 86% 18%, rgba(126, 200, 255, 0.58), transparent 66%),
            radial-gradient(48% 42% at 76% 86%, rgba(150, 190, 255, 0.50), transparent 68%),
            radial-gradient(42% 46% at 20% 80%, rgba(178, 214, 255, 0.55), transparent 66%);
        filter: blur(20px);
    }

    /* Fine grain over the mesh so the gradients do not band. */
    body::after {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        opacity: 0.16;
        mix-blend-mode: multiply;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='180' height='180' filter='url(%23n)' opacity='0.55'/%3E%3C/svg%3E");
    }

    .wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px 72px; }

    /* One page-load reveal, staggered per card, rather than scattered
       micro-interactions. */
    @keyframes riseIn {
        from { opacity: 0; transform: translateY(18px) scale(.985); filter: blur(6px); }
        to   { opacity: 1; transform: none; filter: blur(0); }
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

    /* Masthead: a frosted bar the page scrolls beneath. */
    header.topbar {
        position: sticky;
        top: 0;
        z-index: 20;
        background: rgba(255, 255, 255, 0.62);
        backdrop-filter: blur(22px) saturate(165%);
        -webkit-backdrop-filter: blur(22px) saturate(165%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 1px 24px -8px rgba(21, 62, 130, 0.28);
    }
    /* A thin luminous seam along the bottom edge of the glass. */
    .topbar::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 1px;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(19, 89, 204, 0.42) 22%,
            rgba(111, 91, 214, 0.36) 55%,
            rgba(224, 86, 42, 0.32) 78%,
            transparent
        );
    }
    /* One line: a marker, the project name, and its metadata trailing
       behind it. Everything else lives in the cards below. */
    .topbar-inner {
        max-width: 1180px;
        margin: 0 auto;
        padding: 14px 28px;
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
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent);
        box-shadow: 0 0 0 3px rgba(176, 56, 6, 0.16);
        flex: 0 0 auto;
    }
    .eyebrow .no {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 10px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .topbar h1 {
        margin: 0;
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 19px;
        font-weight: 600;
        line-height: 1.25;
        /* Poppins is geometric and already wide; it needs less negative
           tracking than a grotesque at the same size. */
        letter-spacing: -0.005em;
        color: var(--ink);
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
        color: var(--muted);
    }
    .meta-cell { min-width: 0; }
    .meta-cell .val { letter-spacing: 0; }
    .meta-cell .accent { color: var(--accent); }

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

    .card {
        position: relative;
        background: var(--glass);
        backdrop-filter: blur(26px) saturate(160%);
        -webkit-backdrop-filter: blur(26px) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: var(--radius);
        padding: 0 20px 18px;
        /* Grid items default to min-content width; without this a long
           unbroken value (a URL, a path) stretches the whole track. */
        min-width: 0;
        overflow-wrap: anywhere;
        overflow: hidden;
        /* Outer depth plus an inner top highlight: the lit edge of a pane. */
        box-shadow:
            0 16px 38px -20px rgba(21, 62, 130, 0.42),
            0 2px 8px -4px rgba(21, 62, 130, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        transition: background .22s ease, border-color .22s ease,
                    box-shadow .22s ease, transform .22s ease;
    }
    /* A specular sheen sitting on the top-left of the pane. */
    .card::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.6) 0%,
            rgba(255, 255, 255, 0.14) 28%,
            transparent 56%
        );
    }
    .card:hover {
        background: var(--glass-hi);
        border-color: #ffffff;
        transform: translateY(-3px);
        box-shadow:
            0 26px 50px -22px rgba(21, 62, 130, 0.5),
            0 3px 10px -4px rgba(21, 62, 130, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 1);
    }
    /* Counter drives the index number printed on each card header. */
    .grid { counter-reset: card; }
    .card { counter-increment: card; }
    .card h2 {
        /* Header sits flush to the card edges and is separated by a heavy
           rule rather than a fill. The index number is printed at the right,
           the way a section is numbered in a technical document. */
        position: relative;
        display: flex;
        align-items: baseline;
        gap: 10px;
        margin: 0 -20px 16px;
        padding: 14px 20px 11px;
        /* A brighter pane of glass sitting on the panel it labels. */
        background: rgba(255, 255, 255, 0.45);
        border-bottom: 1px solid rgba(19, 48, 99, 0.09);
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--ink);
    }
    /* Coloured seam under the header, echoing the masthead. */
    .card h2::before {
        content: "";
        position: absolute;
        left: 20px;
        right: 20px;
        bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, var(--brand), transparent 72%);
        opacity: .7;
    }
    .card h2::after {
        content: counter(card, decimal-leading-zero);
        margin-left: auto;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0;
        color: var(--accent);
    }
    /* The anchor card gets the warm seam, so the eye starts there. */
    .card.b-project h2::before {
        background: linear-gradient(90deg, var(--accent), transparent 72%);
        opacity: .85;
    }

    /* The one card you act on rather than read. It sits on a tinted pane with
       a coloured rim so it separates from the reference panels above it. */
    .card.b-action {
        background:
            linear-gradient(180deg, rgba(232, 240, 255, 0.72), rgba(255, 255, 255, 0.6));
        border-color: rgba(19, 89, 204, 0.28);
        box-shadow:
            0 20px 44px -22px rgba(19, 60, 140, 0.5),
            0 2px 10px -4px rgba(19, 60, 140, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.95);
    }
    .card.b-action h2 {
        background: linear-gradient(90deg, rgba(19, 89, 204, 0.13), rgba(19, 89, 204, 0.03));
        color: var(--brand-ink);
    }
    .card.b-action h2::before {
        background: linear-gradient(90deg, var(--brand), var(--accent-2) 55%, transparent 88%);
        opacity: 1;
        height: 2px;
    }
    .card.b-action:hover {
        border-color: rgba(19, 89, 204, 0.4);
        transform: none; /* it holds a form; lifting it under the cursor is noise */
    }

    /* Label above value by default: in a one-column card a fixed label
       column squeezes the value into a narrow strip. */
    .row {
        display: flex;
        flex-direction: column;
        gap: 1px;
        padding: 6px 0;
        border-bottom: 1px solid rgba(19, 48, 99, 0.08);
    }
    .row:last-child { border-bottom: 0; }
    .k {
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 0.13em;
        text-transform: uppercase;
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

    /* The project name is the one serif moment on the page. */
    .card.b-project .row:first-child .v {
        font-family: "Newsreader", Georgia, serif;
        font-size: 18px;
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
        border: 1px solid rgba(19, 48, 99, 0.18);
        box-shadow:
            0 5px 14px -7px rgba(21, 62, 130, 0.5),
            inset 0 1px 0 rgba(255, 255, 255, 0.35);
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
        border: 1.5px solid rgba(19, 89, 204, 0.28);
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
        box-shadow: 0 2px 6px -3px rgba(19, 60, 140, 0.5);
        transition: background .16s ease, color .16s ease, border-color .16s ease,
                    box-shadow .16s ease, transform .14s ease;
    }
    .eye:hover {
        background: var(--brand);
        border-color: var(--brand);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 12px -6px rgba(19, 60, 140, 0.8);
    }
    .eye:active { transform: translateY(0); box-shadow: none; }
    .eye:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(19, 89, 204, 0.26);
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
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 500;
        letter-spacing: 0.13em;
        text-transform: uppercase;
        color: var(--brand-ink);
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
        border: 1.5px solid rgba(19, 89, 204, 0.22);
        border-radius: 10px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12.5px;
        background: #ffffff;
        color: var(--ink);
        box-shadow: inset 0 1px 3px rgba(19, 60, 140, 0.09);
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }
    .field input::placeholder { color: rgba(76, 90, 120, 0.55); }
    .field input:hover { border-color: rgba(19, 89, 204, 0.42); }
    .field input:focus {
        outline: none;
        border-color: var(--brand);
        transform: translateY(-1px);
        box-shadow:
            0 0 0 4px rgba(19, 89, 204, 0.16),
            0 8px 18px -10px rgba(19, 60, 140, 0.55),
            inset 0 1px 2px rgba(19, 60, 140, 0.05);
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
        background: rgba(224, 86, 42, 0.09);
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
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(19, 89, 204, 0.16);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
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
        background: rgba(19, 89, 204, 0.25);
        border-radius: 3px;
    }

    .btn {
        position: relative;
        overflow: hidden;
        padding: 13px 24px;
        border-radius: 999px;
        border: 1px solid transparent;
        background: linear-gradient(135deg, #1360d4, #4b46c9);
        color: #ffffff;
        font-family: "Poppins", Helvetica, sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        cursor: pointer;
        box-shadow:
            0 12px 26px -12px rgba(19, 60, 140, 0.85),
            0 2px 6px -2px rgba(19, 60, 140, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.28);
        transition: box-shadow .18s ease, transform .14s ease, filter .18s ease;
    }
    /* A sheen that sweeps across the button on hover. */
    .btn::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: -60%;
        width: 45%;
        background: linear-gradient(
            100deg,
            transparent,
            rgba(255, 255, 255, 0.42),
            transparent
        );
        transform: skewX(-18deg);
        transition: left .55s cubic-bezier(.25,.8,.35,1);
        pointer-events: none;
    }
    .btn:hover::after { left: 130%; }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow:
            0 18px 34px -12px rgba(19, 60, 140, 0.95),
            0 3px 8px -2px rgba(19, 60, 140, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.36);
        filter: brightness(1.05);
    }
    /* Press feedback: the button settles back onto the surface. */
    .btn:active {
        transform: translateY(0);
        box-shadow:
            0 4px 10px -6px rgba(19, 60, 140, 0.8),
            inset 0 2px 5px rgba(6, 26, 70, 0.3);
        filter: brightness(0.97);
    }
    .btn:focus-visible {
        outline: none;
        box-shadow:
            0 0 0 4px rgba(19, 89, 204, 0.3),
            0 12px 26px -12px rgba(19, 60, 140, 0.85);
    }

    .btn.ghost {
        padding: 11px 18px;
        background: rgba(255, 255, 255, 0.86);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1.5px solid rgba(19, 89, 204, 0.26);
        color: var(--brand-ink);
        font-weight: 600;
        box-shadow: 0 4px 12px -8px rgba(19, 60, 140, 0.5);
    }
    .btn.ghost::after { display: none; }
    .btn.ghost:hover {
        background: #ffffff;
        border-color: var(--brand);
        color: var(--brand);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -12px rgba(19, 60, 140, 0.7);
        filter: none;
    }
    .btn.ghost:active {
        transform: translateY(0);
        box-shadow: inset 0 2px 4px rgba(19, 60, 140, 0.14);
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
        background: var(--glass);
        backdrop-filter: blur(20px) saturate(150%);
        -webkit-backdrop-filter: blur(20px) saturate(150%);
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: var(--radius);
        padding: 13px 18px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 10.5px;
        font-weight: 500;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--ink);
        list-style: none;
        box-shadow:
            0 10px 26px -18px rgba(21, 62, 130, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        transition: background .18s ease;
    }
    details.raw > summary:hover {
        background: #ffffff;
        border-color: rgba(19, 89, 204, 0.3);
        color: var(--brand-ink);
    }
    details.raw > summary:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px rgba(19, 89, 204, 0.22);
    }
    details.raw > summary::-webkit-details-marker { display: none; }
    details.raw > summary::before { content: "▸ "; color: var(--brand); }
    details.raw[open] > summary::before { content: "▾ "; }
    details.raw[open] > summary { border-radius: var(--radius) var(--radius) 0 0; }
    details.raw pre {
        margin: 0;
        /* The raw payload keeps a dark surface - long JSON is easier to scan
           against one, and it reads as a distinct technical panel. */
        background: rgba(14, 26, 54, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-top: 0;
        color: #d5e2ff;
        padding: 16px 18px;
        border-radius: 0 0 var(--radius) var(--radius);
        overflow: auto;
        max-height: 460px;
        font-family: "Chivo Mono", ui-monospace, Consolas, monospace;
        font-size: 12px;
        line-height: 1.55;
    }

    @media (max-width: 640px) {
        .topbar-inner { padding: 12px 16px; gap: 10px; }
        .wrap { padding: 0 16px 44px; }
        .card { padding: 0 15px 15px; }
        .card h2 { margin: 0 -15px 13px; padding: 12px 15px 9px; }
        .card h2::before { left: 15px; right: 15px; }
        .topbar h1 { font-size: 16.5px; }
        /* The due date drops below the title rather than being squeezed. */
        .meta-strip { margin-left: 0; flex-basis: 100%; }
    }

    /* Where backdrop-filter is unavailable, fall back to opaque panels so
       nothing turns into unreadable low-contrast text. */
    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .card,
        details.raw > summary { background: rgba(255, 255, 255, 0.94); }
        .card.b-action { background: #eef4ff; }
        .card h2 { background: rgba(255, 255, 255, 0.6); }
        header.topbar { background: rgba(255, 255, 255, 0.96); }
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
