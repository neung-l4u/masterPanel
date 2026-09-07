<?php
/**
 * Change Log — Localforyou Master Panel
 *
 * To add a new entry: prepend an element to $releases.
 *   version : version number shown in the left gutter
 *   date    : Y-m-d format (used for both sorting and display)
 *   title   : short headline for the release
 *   tags    : related modules, rendered as labels under the version
 *   changes : list of { type: feature|improvement|fix, text: '...' }
 *
 * Start each `text` with a verb (Added / Improved / Fixed / Moved / Enabled …)
 * so the renderer can bold it automatically.
 */

$releases = [
    [
        'version' => '2.12.01',
        'date'    => '2026-09-07',
        'title'   => 'Team notifications and unpaid signup tracking',
        'tags'    => ['Signup Form', 'Notifications', 'Master Panel'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added an internal team notification system that alerts the team automatically when a signup completes without a Stripe result, so it can be investigated straight away'],
            ['type' => 'feature',     'text' => 'Added a "First Paid" column to the Signup and View Logs pages showing the date of the customer\'s first payment'],
            ['type' => 'feature',     'text' => 'Added automatic storeID generation and slug handling to the signup process'],
            ['type' => 'improvement', 'text' => 'Improved the signup form to load the sales agent list dynamically, along with general interface refinements'],
            ['type' => 'fix',         'text' => 'Fixed incorrect state codes in states.json so they match the official data'],
        ],
    ],
    [
        'version' => '2.11.00',
        'date'    => '2026-08-12',
        'title'   => 'Invoice items editor and Data Customer page',
        'tags'    => ['Invoice', 'Master Panel', 'Templates'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added an invoice items editor that supports inline editing directly in the table, with no need to open a separate page'],
            ['type' => 'feature',     'text' => 'Added a Data Customer page that brings customer information together in one place'],
            ['type' => 'feature',     'text' => 'Added automatic content loading from the database into the template editor, with field mapping for the massage and restaurant templates'],
            ['type' => 'improvement', 'text' => 'Improved Thai character handling in the folder names used for slip uploads'],
            ['type' => 'fix',         'text' => 'Fixed the logo path on the Project Details page to point at the correct upload folder structure'],
            ['type' => 'fix',         'text' => 'Fixed boolean field handling on the Project Details page'],
        ],
    ],
    [
        'version' => '2.10.90',
        'date'    => '2026-08-07',
        'title'   => 'CRM and DocuSign integration, TH payment flow changes',
        'tags'    => ['Signup Form', 'TH Billing', 'Integrations'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added a DocuSign button to the signup modal for sending documents out for signature'],
            ['type' => 'feature',     'text' => 'Added a manual "save to CRM" button and enabled the CRM webhook for non-Thailand customers'],
            ['type' => 'feature',     'text' => 'Added the AMELIA coupon code'],
            ['type' => 'improvement', 'text' => 'Moved the thApoMonday webhook trigger from slip confirmation to receipt sending, so it matches the real order of work'],
            ['type' => 'improvement', 'text' => 'Added a wantGM flag to the invoice API and introduced a rejected receipt workflow that reports status back to Monday.com'],
        ],
    ],
    [
        'version' => '2.10.80',
        'date'    => '2026-07-28',
        'title'   => 'Monday.com queue system and image tooling',
        'tags'    => ['Integrations', 'TH Billing', 'Tools'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added the thApoMonday queue for Monday.com webhooks, reducing failed deliveries'],
            ['type' => 'feature',     'text' => 'Added an Image Resizer tool'],
            ['type' => 'feature',     'text' => 'Added TH receipt item creation on the Monday board, plus a payment expiry countdown'],
            ['type' => 'improvement', 'text' => 'Improved slip submission by turning it into a pending list view'],
            ['type' => 'fix',         'text' => 'Fixed a hardcoded sales representative name on receipts, and corrected the slip upload paths'],
        ],
    ],
    [
        'version' => '2.10.70',
        'date'    => '2026-07-18',
        'title'   => 'POS Devices add-on and Website List fields',
        'tags'    => ['Signup Form', 'Website List'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added the Zeller device option to the POS Devices add-on'],
            ['type' => 'feature',     'text' => 'Added Cloudwaitress and Other System fields to the Website List, with a character-limited textarea and double-click to edit'],
            ['type' => 'improvement', 'text' => 'Added Social Media Ads to the product list API'],
            ['type' => 'fix',         'text' => 'Fixed the misspelling "Duel", which now reads "Dual"'],
        ],
    ],
    [
        'version' => '2.10.60',
        'date'    => '2026-07-07',
        'title'   => 'New Thai invoice system with automatic tax calculation',
        'tags'    => ['TH Billing', 'Invoice', 'Master Panel'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Migrated the invoice system to the new Thai schema, built around customer, invoice and receipt tables'],
            ['type' => 'feature',     'text' => 'Added the Thai customer payment flow together with slip upload'],
            ['type' => 'feature',     'text' => 'Added automatic VAT and withholding tax calculation'],
            ['type' => 'feature',     'text' => 'Added the POS Devices add-on with sub-options, plus the AI Management and QR Generator pages'],
        ],
    ],
    [
        'version' => '2.10.50',
        'date'    => '2026-06-07',
        'title'   => 'L4U Booking improvements',
        'tags'    => ['L4U Booking'],
        'changes' => [
            ['type' => 'improvement', 'text' => 'Rebuilt the booking interface with Tailwind CSS and separated the logic into an MVC structure'],
            ['type' => 'improvement', 'text' => 'Added staff profile pictures and switched to Vanilla Calendar'],
            ['type' => 'fix',         'text' => 'Fixed the booking module redirect, which now points to bookingStep.php instead of index.php'],
        ],
    ],
    [
        'version' => '2.10.47',
        'date'    => '2026-05-19',
        'title'   => 'Domain Monitor for website and SSL checks',
        'tags'    => ['Monitor', 'Master Panel'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added Domain Monitor, which checks HTTP status and SSL certificate expiry automatically and sends notifications'],
            ['type' => 'feature',     'text' => 'Added the monitor.php page with statistics, a CRUD table, manual checks, log history and downtime periods'],
            ['type' => 'feature',     'text' => 'Added an adsBudget field to the signup form, carried through to email, the database and the logs'],
            ['type' => 'fix',         'text' => 'Fixed XSS vulnerabilities in the log and downtime modals'],
            ['type' => 'fix',         'text' => 'Fixed the SSL notification logic so it alerts once per day as intended'],
        ],
    ],
    [
        'version' => '2.10.30',
        'date'    => '2026-05-15',
        'title'   => 'Quotation PDF and upgrade / downgrade flow',
        'tags'    => ['Quotation', 'Upgrade / Downgrade'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added 3% withholding tax for corporate customers in the quotation PDF'],
            ['type' => 'feature',     'text' => 'Enabled the upgrade and downgrade forms in full'],
            ['type' => 'improvement', 'text' => 'Reorganised the quotation summary so the grand total appears first'],
            ['type' => 'fix',         'text' => 'Fixed page breaks in the PDF so footer content is no longer split across pages'],
        ],
    ],
    [
        'version' => '2.10.10',
        'date'    => '2026-05-14',
        'title'   => 'Faster Report Lifespan',
        'tags'    => ['Reports', 'Integrations'],
        'changes' => [
            ['type' => 'improvement', 'text' => 'Improved reportLifespan to fetch GraphQL data in parallel with curl_multi, making the report noticeably faster to load'],
            ['type' => 'improvement', 'text' => 'Split board fetching into metadata and parallel group queries, and added country_code to each item'],
            ['type' => 'fix',         'text' => 'Fixed the Monday API connection used by Make.com'],
        ],
    ],
    [
        'version' => '2.9.00',
        'date'    => '2026-04-30',
        'title'   => 'AI Araya form and Monday.com integration',
        'tags'    => ['AI Araya', 'Signup Form', 'Integrations'],
        'changes' => [
            ['type' => 'feature',     'text' => 'Added the AI Araya form and the ASAP form'],
            ['type' => 'feature',     'text' => 'Added add-on data forwarding from the signup form to Make.com and Monday.com'],
            ['type' => 'feature',     'text' => 'Added Google Tag Manager for usage tracking'],
            ['type' => 'improvement', 'text' => 'Improved the signup form for AU customers on POS products, which now accept three file uploads'],
            ['type' => 'improvement', 'text' => 'Added an existing-customer mode to the POS and New Online Order forms, with improved validation'],
        ],
    ],
];
/* ---------- Prepare data for rendering ---------- */
usort($releases, fn($a, $b) => strcmp($b['date'], $a['date']));

/**
 * The change type (feature / improvement / fix) is used for the counts only.
 * On screen we simply bold the leading verb that each entry already starts
 * with ("Added ...", "Fixed ..."), so no separate badge is needed.
 */

$counts = ['feature' => 0, 'improvement' => 0, 'fix' => 0];
foreach ($releases as $r) {
    foreach ($r['changes'] as $c) {
        if (isset($counts[$c['type']])) {
            $counts[$c['type']]++;
        }
    }
}

$latest     = $releases[0];
$totalItems = array_sum($counts);
function releaseDate(string $ymd): string
{
    return date('F j, Y', strtotime($ymd));
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * Render technical identifiers as monospace chips, the way docs pages do:
 * file names (.php/.json), camelCase, snake_case and Domain.com names.
 */
function richText(string $text): string
{
    $safe = e($text);
    $pattern = '/\b([A-Za-z][A-Za-z0-9_]*\.(?:php|json|js|css|md)|'
             . '[a-z]+[A-Z][A-Za-z0-9]*|'
             . '[a-z]+_[a-z_]+|'
             . 'Monday\.com|Make\.com|curl_multi)\b/u';

    $safe = preg_replace($pattern, '<code>$1</code>', $safe);

    // Bold the leading verb so the list is quick to scan
    $verbs = 'Added|Improved|Fixed|Moved|Migrated|Enabled|Rebuilt|Reorganised|Split|Removed|Changed|Updated';
    $safe = preg_replace('/^(' . $verbs . ')\b/', '<b>$1</b>', $safe);

    return $safe;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
        document.addEventListener('click', function(e) {
            var el = e.target.closest('[data-ga]');
            if (el) {
                gtag('event', el.getAttribute('data-ga'), {
                    event_category: el.getAttribute('data-ga-category') || 'button',
                    event_label: el.getAttribute('data-ga-label') || el.textContent.trim().substring(0, 50)
                });
            }
        });
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Log | Localforyou Master Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0f0f0e;
            --panel:    #141413;
            --line:     #2a2a27;
            --line-dim: #1f1f1d;
            --text:     #e8e6e1;
            --muted:    #a3a09a;
            --faint:    #6f6c66;
            --accent:   #c96442;
            --code-bg:  #22221f;
            --code-fg:  #d6d3cd;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            overflow-x: hidden;
            background: var(--bg);
            color: var(--text);
            font-family: "Prompt", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.75;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }

        code {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
            font-size: .855em;
            background: var(--code-bg);
            color: var(--code-fg);
            border-radius: 5px;
            padding: .12em .42em;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        /* ---------- Top bar ---------- */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 30px;
            background: rgba(15,15,14,.88);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line-dim);
        }
        .topbar img { height: 26px; width: auto; }
        .topbar .name { font-size: 15px; font-weight: 500; letter-spacing: -.01em; }
        .topbar .sep { color: var(--faint); font-weight: 300; }
        .topbar .section { color: var(--muted); font-size: 14px; }

        /* ---------- Layout ---------- */
        .wrap {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 210px;
            gap: 56px;
            max-width: 1180px;
            margin: 0 auto;
            padding: 52px 30px 120px;
            align-items: start;
        }

        /* ---------- Page header ---------- */
        .eyebrow {
            font-size: 12px;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 500;
            margin-bottom: 14px;
        }
        h1 {
            font-family: "Newsreader", Georgia, serif;
            font-size: 44px;
            font-weight: 400;
            line-height: 1.15;
            letter-spacing: -.015em;
            margin: 0 0 16px;
        }
        .lede { color: var(--muted); font-size: 16px; margin: 0 0 14px; max-width: 60ch; }
        .note { color: var(--muted); font-size: 15px; margin: 0 0 6px; max-width: 62ch; }

        .divider { height: 1px; background: var(--line-dim); margin: 40px 0 8px; }

        /* ---------- Release row ---------- */
        .release {
            display: grid;
            grid-template-columns: 168px minmax(0, 1fr);
            gap: 34px;
            padding: 30px 0;
            border-bottom: 1px solid var(--line-dim);
            scroll-margin-top: 80px;
        }
        .release:last-of-type { border-bottom: none; }

        .rel-meta { padding-top: 1px; }
        .rel-version {
            color: var(--accent);
            font-size: 15px;
            font-weight: 500;
            letter-spacing: .01em;
        }
        .rel-date { color: var(--faint); font-size: 13.5px; margin-top: 9px; }
        .rel-tags { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 5px; }
        .rel-tags span {
            font-size: 11.5px;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: 4px;
            padding: 1px 7px;
            line-height: 1.6;
        }

        .rel-body h2 {
            margin: 0 0 14px;
            overflow-wrap: break-word;
            font-size: 17px;
            font-weight: 500;
            letter-spacing: -.01em;
            color: var(--text);
        }

        ul.changes { list-style: none; margin: 0; padding: 0; }
        ul.changes li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 9px;
            color: var(--muted);
        }
        ul.changes li:last-child { margin-bottom: 0; }
        ul.changes li { overflow-wrap: break-word; }
        ul.changes li::before {
            content: "";
            position: absolute;
            left: 2px; top: .68em;
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--faint);
        }
        ul.changes li b { color: var(--text); font-weight: 500; }

        /* ---------- On this page ---------- */
        .toc { position: sticky; top: 84px; }
        .toc-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text);
            margin-bottom: 14px;
        }
        .toc-title svg { width: 14px; height: 14px; stroke: var(--muted); }
        .toc a {
            display: block;
            font-size: 13.5px;
            color: var(--faint);
            padding: 4.5px 0;
            transition: color .15s;
        }
        .toc a:hover { color: var(--text); }
        .toc a.active { color: var(--accent); }

        .toc-stats {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--line-dim);
            font-size: 12.5px;
            color: var(--faint);
            line-height: 2;
        }

        .foot {
            margin-top: 46px;
            padding-top: 20px;
            border-top: 1px solid var(--line-dim);
            color: var(--faint);
            font-size: 13.5px;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1000px) {
            .wrap { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .toc { display: none; }
        }
        @media (max-width: 680px) {
            .wrap { padding: 34px 20px 80px; }
            h1 { font-size: 32px; }
            .release { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 26px 0; }
            .rel-date { display: inline-block; margin-top: 0; margin-left: 12px; }
            .topbar { padding: 12px 20px; }
            .topbar .section { display: none; }
        }
    </style>
</head>
<body>

<header class="topbar">
    <img src="assets/img/img.png" alt="Localforyou">
    <span class="name">Master Panel</span>
    <span class="sep">/</span>
    <span class="section">Change Log</span>
</header>

<div class="wrap">
    <main>
        <div class="eyebrow">Getting started</div>
        <h1>Master Panel changelog</h1>
        <p class="lede">
            Release notes for the Localforyou Master Panel, including new features,
            improvements and bug fixes by version.
        </p>
        <p class="note">
            The signup form is currently on version <code><?= e($latest['version']) ?></code>,
            shown in the footer of the form itself.
        </p>

        <div class="divider"></div>

        <?php foreach ($releases as $r):
            $anchor = 'v' . str_replace('.', '-', $r['version']); ?>
            <section class="release" id="<?= e($anchor) ?>">
                <div class="rel-meta">
                    <div class="rel-version"><?= e($r['version']) ?></div>
                    <div class="rel-date"><?= e(releaseDate($r['date'])) ?></div>
                    <div class="rel-tags">
                        <?php foreach ($r['tags'] as $t): ?>
                            <span><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rel-body">
                    <h2><?= e($r['title']) ?></h2>
                    <ul class="changes">
                        <?php foreach ($r['changes'] as $c): ?>
                            <li><?= richText($c['text']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endforeach; ?>

        <p class="foot">
            Maintained by the IT team — if anything here looks wrong, or an update is
            missing, please let the development team know.
        </p>
    </main>

    <aside class="toc">
        <div class="toc-title">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
                <path d="M4 6h16M4 12h16M4 18h10"></path>
            </svg>
            On this page
        </div>
        <nav id="tocNav">
            <?php foreach ($releases as $i => $r):
                $anchor = 'v' . str_replace('.', '-', $r['version']); ?>
                <a href="#<?= e($anchor) ?>"<?= $i === 0 ? ' class="active"' : '' ?>><?= e($r['version']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="toc-stats">
            <?= count($releases) ?> versions · <?= $totalItems ?> changes<br>
            <?= $counts['feature'] ?> new · <?= $counts['improvement'] ?> improved · <?= $counts['fix'] ?> fixed
        </div>
    </aside>
</div>

<script>
(function () {
    var links    = Array.prototype.slice.call(document.querySelectorAll('#tocNav a'));
    var sections = links.map(function (a) {
        return document.getElementById(a.getAttribute('href').slice(1));
    });

    function onScroll() {
        var pos = window.scrollY + 120;
        var current = 0;
        sections.forEach(function (sec, i) {
            if (sec && sec.offsetTop <= pos) { current = i; }
        });
        links.forEach(function (a, i) {
            a.classList.toggle('active', i === current);
        });
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(function () { onScroll(); ticking = false; });
            ticking = true;
        }
    }, { passive: true });

    onScroll();
})();
</script>

</body>
</html>
