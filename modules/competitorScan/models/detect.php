<?php
/**
 * Pure detection helpers shared by modules/competitorScan and assets/php/competitor_scan.php.
 * Nothing here touches the network except fetch_all(), and nothing reads globals.
 */

// name => [group, pattern on the lower-cased HTML or on a URL written in monday]
const SYSTEMS = [
    // ours
    'GloriaFood (L4U)'      => ['ours', 'fbgcdn\.com|foodbooking\.com|data-glf-ruid'],
    'CloudWaitress (L4U)'   => ['ours', 'cloudwaitress\.com|localforyoucarts?\.com'],
    'Amelia (L4U)'          => ['ours', 'ameliabooking'],
    // online ordering / POS (restaurants)
    'Toast'                 => ['ordering', 'toasttab\.com'],
    'Square Online'         => ['ordering', 'square\.site|squareup\.com/(?:store|order)|order\.online|weeblysite\.com'],
    'Uber Eats'             => ['ordering', 'ubereats\.com'],
    'DoorDash'              => ['ordering', 'doordash\.com'],
    'Menulog'               => ['ordering', 'menulog\.com\.au'],
    'Deliveroo'             => ['ordering', 'deliveroo\.'],
    'Just Eat'              => ['ordering', 'just-eat\.|justeat\.'],
    'Foodhub'               => ['ordering', 'foodhub\.(?:co\.uk|com)'],
    'Grubhub'               => ['ordering', 'grubhub\.com'],
    'ChowNow'               => ['ordering', 'chownow\.com'],
    'Owner.com'             => ['ordering', 'owner\.com'],
    'Smile Dining'          => ['ordering', 'smiledining\.com'],
    'Chowbus'               => ['ordering', 'chowbus\.com'],
    'SpotOn'                => ['ordering', 'spoton\.com'],
    'Clover'                => ['ordering', 'clover\.com'],
    'me&u / Mr Yum'         => ['ordering', 'meandu\.|mryum\.'],
    'HungryHungry'          => ['ordering', 'hungryhungry\.com'],
    'Bopple'                => ['ordering', 'bopple\.com'],
    'Slice'                 => ['ordering', 'slicelife\.com'],
    'BeyondMenu'            => ['ordering', 'beyondmenu\.com'],
    'Menufy'                => ['ordering', 'menufy\.com'],
    'Blizzfull'             => ['ordering', 'blizzfull\.com'],
    'Easi'                  => ['ordering', 'easi\.com\.au'],
    'WooCommerce (self-hosted)' => ['ordering', 'wc-block-|woocommerce-mini-cart|wc-ajax='],
    'MobiPOS'               => ['ordering', 'mobi-order\.com|mobi-booking\.com|mobipos\.com'],
    'NextOrder'             => ['ordering', 'nextorder\.com'],
    'Wera Foods'            => ['ordering', 'werafoods\.com'],
    'Yumbo Jumbo'           => ['ordering', 'yumbojumbo\.com\.au'],
    'OnlineDining'          => ['ordering', 'onlinedining\.com\.au'],
    // booking (massage)
    'Fresha'                => ['booking', 'fresha\.com'],
    'Vagaro'                => ['booking', 'vagaro\.com'],
    'Square Appointments'   => ['booking', 'squareup\.com/appointments|book\.squareup\.com'],
    'Mindbody'              => ['booking', 'mindbodyonline\.com|mindbody\.io'],
    'Booksy'                => ['booking', 'booksy\.com'],
    'Timely'                => ['booking', 'gettimely\.com'],
    'Acuity'                => ['booking', 'acuityscheduling\.com'],
    'Setmore'               => ['booking', 'setmore\.com'],
    'GlossGenius'           => ['booking', 'glossgenius\.com'],
    'Mangomint'             => ['booking', 'mangomint\.com'],
    'Cliniko'               => ['booking', 'cliniko\.com'],
    'SimplyBook.me'         => ['booking', 'simplybook\.(?:me|it|asia)'],
    'Zenoti'                => ['booking', 'zenoti\.com'],
    'Phorest'               => ['booking', 'phorest\.com'],
    'Boulevard'             => ['booking', 'joinblvd\.com|blvd\.co'],
    'Schedulicity'          => ['booking', 'schedulicity\.com'],
    'StyleSeat'             => ['booking', 'styleseat\.com'],
    'Treatwell'             => ['booking', 'treatwell\.'],
    'Jane'                  => ['booking', 'janeapp\.com'],
    'Halaxy'                => ['booking', 'halaxy\.com'],
    'Nookal'                => ['booking', 'nookal\.com'],
    'MassageBook'           => ['booking', 'massagebook\.com'],
    'Booker'                => ['booking', 'secure-booker\.com|booker\.com'],
    'Genbook'               => ['booking', 'genbook\.com'],
    'Calendly'              => ['booking', 'calendly\.com'],
    'Receptioner'           => ['booking', 'receptionerapp\.com'],
    'Splose'                => ['booking', 'splose\.com'],
    'Wix Bookings'          => ['booking', '/book-online'],   // the Bookings app page; every Wix site mentions "bookings.Wix…" in its code
    // website builders
    'Wix'                   => ['builder', 'wixstatic\.com|wixsite\.com|_wixcss|wix\.com'],
    'Squarespace'           => ['builder', 'squarespace(?:-cdn)?\.com'],
    'GoDaddy Builder'       => ['builder', 'img1\.wsimg\.com|godaddysites\.com'],
    'Shopify'               => ['builder', 'cdn\.shopify\.com'],
    'Webflow'               => ['builder', 'website-files\.com|webflow\.(?:com|io)'],
    'Weebly'                => ['builder', 'editmysite\.com|weebly\.com'],
    'Duda'                  => ['builder', 'multiscreensite\.com|cdn-website\.com|dudaone'],
    'Hostinger Builder'     => ['builder', 'zyrosite\.com|userapp\.zyro'],
    'Google Sites'          => ['builder', 'sites\.google\.com'],
    'Jimdo'                 => ['builder', 'jimdo'],
    'ReadyPlanet'           => ['builder', 'readyplanet\.com'],
    // chat used to take orders / bookings (common in Thailand); only reported when no real system is found
    'LINE (chat)'           => ['chat', 'lin\.ee/|line\.me/(?:r/|ti/|R/)|page\.line\.me'],
];
const PARKED = '~domain (?:is )?for sale|buy this domain|parked free|sedoparking|this domain (?:has expired|is parked)|domain parking|hugedomains|dan\.com/|afternic|related searches|links to advertisements|website under redevelopment~';

function host_of($x) { return preg_replace('/^www\./', '', preg_split('#[/?\#:]#', preg_replace('#^[a-z]+://#', '', strtolower(trim((string) $x))))[0]); }
// Hosts that order / book / menu buttons point to, so competitors not in SYSTEMS still show up
// (e.g. "View Menu & Order" → onlineorder.enrichpos.com.au). Social, maps and the shop's own site are left out.
function action_hosts($html, $ownHost) {
    $out = [];
    if (!preg_match_all('#<a\b[^>]*href=["\']\s*(https?://[^"\'\s>]+)["\'][^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER)) return [];
    foreach ($m as [, $href, $label]) {
        $text = strtolower($href . ' ' . strip_tags($label));
        if (!preg_match('#order|book|menu|reserv|deliver|appointment|takeaway|take-away|pickup|schedule#', $text)) continue;
        // a menu PDF / image on file storage is not an ordering system
        if (preg_match('#\.(?:pdf|jpe?g|png|gif|webp|docx?)(?:\?|$)#i', $href)) continue;
        $h = host_of($href);
        if (preg_match('#(?:^|\.)(?:r2\.dev|amazonaws\.com|cloudfront\.net|googleusercontent\.com|drive\.google\.com|dropbox\.com|wixstatic\.com|squarespace-cdn\.com)$#', $h)) continue;
        if ($h === '' || $h === $ownHost || str_ends_with($h, '.' . $ownHost) || str_ends_with($ownHost, '.' . $h)) continue;
        if (preg_match('#(^|\.)(facebook|instagram|google|goo\.gl|youtube|tiktok|twitter|x\.com|yelp|tripadvisor|linkedin|pinterest|apple\.com|maps|wa\.me|whatsapp|wordpress\.org|elementor|w\.org|gravatar|schema\.org)#', $h)) continue;
        $out[$h] = 1;
    }
    return array_keys($out);
}

function systems_in($text) {
    $found = [];
    foreach (SYSTEMS as $name => [$group, $re]) if (preg_match("#$re#i", $text)) $found[$group][] = $name;
    // Wix Bookings is a Wix feature, Square Appointments also matches Square Online's domain: keep the specific one
    if (in_array('Wix Bookings', $found['booking'] ?? [], true)) $found['builder'] = array_unique(array_merge($found['builder'] ?? [], ['Wix']));
    return $found;
}

// fetch many URLs in parallel: url => [http, final_url, body]
function fetch_all(array $urls, $parallel = 15, $timeout = 20) {
    $out = []; $queue = array_values(array_unique($urls)); $mh = curl_multi_init(); $active = [];
    while ($queue || $active) {
        while ($queue && count($active) < $parallel) {
            $u = array_shift($queue); $ch = curl_init($u);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 5, CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => min(10, $timeout), CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124 Safari/537.36']);
            curl_multi_add_handle($mh, $ch); $active[(int) $ch] = [$ch, $u];
        }
        curl_multi_exec($mh, $running); curl_multi_select($mh, 1.0);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle']; [, $u] = $active[(int) $ch]; unset($active[(int) $ch]);
            $out[$u] = [curl_getinfo($ch, CURLINFO_HTTP_CODE), (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), substr((string) curl_multi_getcontent($ch), 0, 1500000)];
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
        }
    }
    curl_multi_close($mh);
    return $out;
}

// ---- competitor scan (modules/competitorScan) ------------------------------------------------------------

/**
 * WordPress even when a "hide WP" plugin renames /wp-content/ and fakes the generator tag
 * (seen live: atchongkho.com.au claims "Drupal 11" but is Astra + Elementor + WooCommerce).
 * Class names survive the renaming because the theme's CSS needs them.
 */
function cs_is_wordpress($html) {
    return (bool) preg_match('#/wp-content/|/wp-includes/|api\.w\.org|wp-block-|class="[^"]*\belementor-|woocommerce|\bast-container\b#i', $html);
}

/**
 * Same-site script files worth reading when the page is a JavaScript app (Next.js, React, Vue, Nuxt, Vite…):
 * there the "Order online" button is only "#order-online" in the HTML and the real link lives in a script
 * (seen live: eightysixbangkok.com.au → mobi-order.com only inside /_next/static/…/page-….js).
 * WordPress and plain sites return nothing, so they cost no extra requests.
 */
function cs_app_scripts($html, $pageUrl, $max = 8) {
    if (!preg_match('#/_next/static/|__NEXT_DATA__|/_nuxt/|id="(?:root|app|__next|___gatsby)"|/assets/index-[\w-]+\.js|/static/js/main\.#i', $html)) return [];
    $base = parse_url($pageUrl);
    if (empty($base['host'])) return [];
    $origin = ($base['scheme'] ?? 'https') . '://' . $base['host'];
    preg_match_all('#<script\b[^>]*\bsrc=["\']([^"\']+\.js(?:\?[^"\']*)?)["\']#i', $html, $m);
    $out = [];
    foreach ($m[1] as $src) {
        $src = html_entity_decode($src);
        $abs = str_starts_with($src, '//') ? 'https:' . $src : (preg_match('#^https?://#i', $src) ? $src : $origin . '/' . ltrim($src, '/'));
        if (host_of($abs) !== host_of($origin) || preg_match('#/wp-|polyfill|webpack|framework-|/main-app-|jquery#i', $abs)) continue;
        $out[$abs] = 1;
    }
    // the page's own chunk ("page-…js") is the one that carries its links: read those first
    $out = array_keys($out);
    usort($out, fn($a, $b) => (int) !str_contains($a, '/page') <=> (int) !str_contains($b, '/page'));
    return array_slice($out, 0, $max);
}

/** Ad / tracking IDs in the page. Pixel or tag present means the account exists, not that ads are running. */
function cs_tracking_ids($html) {
    $ids = ['google_ads' => [], 'meta_pixel' => [], 'gtm' => []];
    if (preg_match_all('/\bAW-\d{8,12}\b/', $html, $m)) $ids['google_ads'] = $m[0];
    if (preg_match_all('/fbq\(\s*[\'"]init[\'"]\s*,\s*[\'"](\d{10,20})/', $html, $m)) $ids['meta_pixel'] = $m[1];
    if (preg_match_all('#facebook\.com/tr\?id=(\d{10,20})#', $html, $m)) $ids['meta_pixel'] = array_merge($ids['meta_pixel'], $m[1]);
    if (preg_match_all('/\bGTM-[A-Z0-9]{5,9}\b/', $html, $m)) $ids['gtm'] = $m[0];
    return array_map(fn($a) => array_values(array_unique($a)), $ids);
}

// Builders, platforms and L4U itself are not the shop's agency.
const CS_NOT_AGENCY = '#wordpress|wix|squarespace|godaddy|elementor|shopify|weebly|astra|wpbakery|divi|duda|jimdo|webflow|hostinger|google|facebook|meta\b|fresha|gloria|foodbooking|localforyou|local for you|cloudwaitress|mindbody|vagaro|square|toast|clover|owner\.com|theme|plugin|^(the|our|us|me|you)$#i';

// Builders whose company also sells marketing / ads management. Being on one is a hint, not proof, of an agency.
const CS_AGENCY_PLATFORMS = ['ReadyPlanet'];

/**
 * "Website by X" / "Designed and Developed by X" credits. Returns [['name' => ..., 'url' => ...], ...].
 * Scripts and styles are dropped first so JSON blobs ("powered by" in a config) do not count.
 */
function cs_agency_credits($html) {
    $html = preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html);
    $out = [];
    $re = '~\b(?:website|site|web\s*design|designed|developed|built|created|managed|maintained|marketing|seo)(?:\s+(?:and|&|&amp;)\s+[a-z]+)?\s+by\b~i';
    if (!preg_match_all($re, $html, $m, PREG_OFFSET_CAPTURE)) return [];
    foreach ($m[0] as [$hit, $off]) {
        $seg = substr($html, $off + strlen($hit), 300);
        $url = preg_match('#href=["\'](https?://[^"\']+)#i', $seg, $h) ? $h[1] : '';
        // a tag boundary ends the name: "maintained by GORGOEY</a></li><li>Privacy Policy" must not run together
        $text = '';
        foreach (preg_split('/[|\n\r©•·]|\s{3,}/u', html_entity_decode(preg_replace('#<[^>]*>#', '|', $seg), ENT_QUOTES | ENT_HTML5, 'UTF-8')) as $part)
            if (($text = trim($part, " \t:-–.,")) !== '') break;
        $name = mb_substr($text, 0, 60) ?: host_of($url);
        if (mb_strlen($name) < 3 || preg_match(CS_NOT_AGENCY, $name . ' ' . $url)) continue;
        $out[strtolower($name)] = ['name' => $name, 'url' => $url];
    }
    return array_values($out);
}

/**
 * Which parts of a cancelled customer to scan, from one package / product name.
 * Company rule: Solo = marketing only, Bundle = marketing + system (ordering for restaurants, booking for
 * massage), Hosting / Makeover / Ad Credit / Yelp = add-on. Bundle is checked before add-on words because
 * "Social Media Marketing Bundle (Incl Website Hosting and Booking System)" mentions hosting.
 */
function cs_package_kinds($name) {
    $n = strtolower($name);
    if (preg_match('/add-?on only/', $n)) return ['addon'];
    $k = [];
    if (str_contains($n, 'bundle')) $k = ['marketing', 'system'];
    if (preg_match('/\bsolo\b|social media (?:marketing|management)|local (?:starter|growth|ultimate)|mega marketing|direct marketing|autopilot/', $n)) $k[] = 'marketing';
    if (preg_match('/online ordering|ordering system|booking system|\bpos\b/', $n)) $k[] = 'system';
    if (preg_match('/ai receptionist|araya/', $n)) $k[] = 'araya';
    if (!$k && preg_match('/hosting|makeover|website build|ad credit|ad spend|yelp|add-?on|promotion|menu design|flyer|sticker|email/', $n)) $k[] = 'addon';
    return array_values(array_unique($k));
}
