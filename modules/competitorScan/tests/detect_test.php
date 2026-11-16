<?php
/**
 * Run: php modules/competitorScan/tests/detect_test.php
 * Fixtures are trimmed from the two shops checked by hand (atchongkho.com.au, ninathaimassage.com).
 */
require_once __DIR__ . '/../models/scan.php';

$fail = 0;
function check($label, $got, $want) {
    global $fail;
    if ($got === $want) { echo "ok   $label\n"; return; }
    $fail++;
    echo "FAIL $label\n     got:  " . json_encode($got, JSON_UNESCAPED_UNICODE) . "\n     want: " . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n";
}

// ---- detection
$hiddenWp = '<meta name="generator" content="Drupal 11 (https://www.drupal.org)"><link href="/core/modules/39d1/style.css">'
    . '<div class="wp-block-group"><div class="elementor-widget"></div></div><script>gtag("config","AW-16623554470");</script>'
    . '<script>(function(w,d,s,l,i){})(window,document,"script","dataLayer","GTM-KTKX676B");</script>'
    . '<p>© 2024 atchongkho.com.au All rights reserved | Designed and Developed by cantydigital.com.au </p>';
check('hidden WordPress is still WordPress', cs_is_wordpress($hiddenWp), true);
check('Wix page is not WordPress', cs_is_wordpress('<img src="https://static.wixstatic.com/a.png">'), false);
check('Google Ads + GTM ids', cs_tracking_ids($hiddenWp), ['google_ads' => ['AW-16623554470'], 'meta_pixel' => [], 'gtm' => ['GTM-KTKX676B']]);
check('agency credit with "Designed and Developed by"', cs_agency_credits($hiddenWp), [['name' => 'cantydigital.com.au', 'url' => '']]);
check('credit pointing at a builder is not an agency', cs_agency_credits('<footer>Website by <a href="https://www.wix.com">Wix</a></footer>'), []);
check('credit inside a script is ignored', cs_agency_credits('<script>var t="Powered by Acme Digital";</script>'), []);
check('credit link url captured', cs_agency_credits('<footer>Site by <a href="https://acme.com.au/">Acme Digital</a> | ©</footer>'),
    [['name' => 'Acme Digital', 'url' => 'https://acme.com.au/']]);

$nina = "<script>fbq('init', '1252027042430701'); fbq('track', 'PageView');</script>"
    . '<img src="https://static.wixstatic.com/media/x.jpg"><a href="https://www.fresha.com/a/nina-thai-massage-richmond-299-bridge-road-jn73bjt0">Book now</a>'
    . '<p>Copyright 2017-2025 &copy;Nina Thai Massage</p>';
check('Meta pixel id', cs_tracking_ids($nina)['meta_pixel'], ['1252027042430701']);
check('copyright line is not an agency', cs_agency_credits($nina), []);

// Phukasa (TH): ReadyPlanet site, takes bookings through LINE, no agency credit in the text
$phukasa = '<script src="https://rwidget.readyplanet.com/widget/widget.min.js"></script><a class="banner-section-link" href="https://lin.ee/wulhyZ7s"><img src="x.png"></a>'
    . '<a href="https://www.readyplanet.com/16544025/r-web"><img alt="Powered by ReadyPlanet"></a>';
$r = cs_classify(['id' => '9', 'cc' => 'TH', 'name' => 'Phukasa', 'domain' => 'phukasa.com', 'scope' => ['marketing' => true, 'system' => true]],
    [[200, 'https://www.phukasa.com/', $phukasa]], []);
check('ReadyPlanet builder', $r['builder'], ['ReadyPlanet']);
check('LINE as the booking channel', [$r['system']['status'], $r['system']['providers']], ['Likely', ['LINE (chat)']]);
check('ReadyPlanet sells marketing → agency Likely, not Confirmed', [$r['agency']['status'], $r['agency']['name']], ['Likely', 'ReadyPlanet (platform + marketing)']);

// 86 Bangkok (AU): Next.js site, "ORDER ONLINE" is #order-online, the mobi-order link is only in a script chunk
$e86 = '<script src="/_next/static/chunks/webpack-1.js" async></script><script src="/_next/static/chunks/app/page-cd59.js" async></script>'
    . '<a href="#order-online">ORDER ONLINE</a><a href="https://pub-c7fd.r2.dev/86Bangkok-Menu.pdf">VIEW OUR MENU</a>'
    . '<li><a href="#" class="hover:underline">maintained by GORGOEY</a></li>';
check('app scripts: page chunk picked, runtime skipped', cs_app_scripts($e86, 'https://eightysixbangkok.com.au/'),
    ['https://eightysixbangkok.com.au/_next/static/chunks/app/page-cd59.js']);
check('plain WordPress page: no scripts fetched', cs_app_scripts($hiddenWp . '<script src="/x.js"></script>', 'https://a.com/'), []);
$r = cs_classify(['id' => '8', 'cc' => 'AU', 'name' => '86', 'domain' => 'eightysixbangkok.com.au', 'scope' => ['marketing' => true, 'system' => true]],
    [[200, 'https://eightysixbangkok.com.au/', $e86]], [], ['window.x={order:"https://86bangkokbirtinya.mobi-order.com/"}']);
check('system found in the script chunk', [$r['system']['status'], $r['system']['providers']], ['Confirmed', ['MobiPOS']]);
check('menu PDF on file storage is not a provider', in_array('pub-c7fd.r2.dev', $r['system']['providers'], true), false);
check('"maintained by" credit', [$r['agency']['status'], $r['agency']['name']], ['Confirmed', 'GORGOEY']);

// Links from the Google listing (cache/links.json) when the shop's own site cannot be checked
$dead = cs_classify(['id' => '7', 'cc' => 'US', 'name' => 'Bangkok@12 Thai - BR.Sacramento', 'domain' => 'bangkok12restaurant.com',
    'scope' => ['marketing' => true, 'system' => true]], [[403, 'https://bangkok12restaurant.com/', 'Just a moment...']], []);
$r = cs_apply_links($dead, ['https://bkkat12ca.smiledining.com/' => [200, 'https://bkkat12ca.smiledining.com/', '<h1>Bangkok At 12 Thai Restaurant, CA</h1>(916) 443-5588 | 900 12th St']], '+1 916-443-5588');
check('listing link + phone on the page → Confirmed', [$r['system']['status'], $r['system']['providers']], ['Confirmed', ['Smile Dining']]);
$r = cs_apply_links($dead, ['https://bangkokbay.mobi-order.com/' => [0, '', '']], '');
check('listing link that could not be opened → Likely', [$r['system']['status'], $r['system']['providers']], ['Likely', ['MobiPOS']]);
$r = cs_apply_links($dead, ['https://www.yisik.com/en/restaurants/x' => [200, 'https://www.yisik.com/en/restaurants/x', 'Bangkok@12 Thai']], '');
check('a directory page is not a provider', $r['system']['status'], 'Unknown');

check('search links: own provider pages kept, area listings and other shops dropped',
    cs_links_from_search("Let's Relax Massage - BR.Charmhaven", ['https://www.fresha.com/a/let-s-relax-massage-charmhaven-11-13-arizona-road-lsp9ikzd',
        'https://www.fresha.com/lp/en/tt/thai-massages/in/au-cairns/manunda', 'https://calso.com.au/directory/x']),
    ['https://www.fresha.com/a/let-s-relax-massage-charmhaven-11-13-arizona-road-lsp9ikzd']);
check('search links: short names match joined ("Cha Ba" → chaba)', cs_links_from_search('Cha Ba Thai - BR.Portland',
    ['https://www.doordash.com/store/cha-ba-thai-portland-552017/']), ['https://www.doordash.com/store/cha-ba-thai-portland-552017/']);
check('search links: L4U cart kept without the name', cs_links_from_search('Jungle King Street Thai & Bowls - BR.Burleigh Waters',
    ['https://www.localforyoucart.com/api/fb/nwg_r4', 'https://lash-brow-by-shelly.square.site/']), ['https://www.localforyoucart.com/api/fb/nwg_r4']);

$lfyWp = '<div class="wp-block-group woocommerce-mini-cart"></div><p>Website Maintained by Local For You</p>';
$r = cs_classify(['id' => '6', 'cc' => 'AU', 'name' => 'Gang Thai', 'type' => 'Thai Restaurants & Takeaways', 'domain' => 'gangthaimiranda.com.au',
    'scope' => ['marketing' => true, 'system' => true]], [[200, 'https://gangthaimiranda.com.au/', $lfyWp]], []);
check('WooCommerce in L4U\'s own template is not a competitor', [$r['system']['status'], $r['notes']], ['Not Detected', ['Website still credits Local For You']]);
$r = cs_classify(['id' => '6', 'cc' => 'AU', 'name' => 'Spa', 'type' => 'Thai Massage', 'domain' => 'spa.com.au', 'scope' => ['marketing' => true, 'system' => true]],
    [[200, 'https://spa.com.au/', '<div class="wc-block-cart">Gift voucher</div>']], []);
check('WooCommerce on a massage site (vouchers) is not a booking system', $r['system']['status'], 'Not Detected');
check('area listing pages dropped even when the shop name has the suburb', cs_links_from_search('Selly Oak Thai therapy - BR.Selly oak',
    ['https://www.fresha.com/lp/en/tt/thai-massages/in/gb-birmingham/weoley-and-selly-oak', 'https://booksy.com/en-gb/s/thai-massage/1294242_selly-oak']), []);
$stillLfy = ['name' => 'Sticky Rice - BR.Weston Creek', 'evidence' => [], 'notes' => [], 'system' => ['status' => 'Confirmed', 'providers' => ['CloudWaitress (L4U) — still LFY']]];
$r = cs_apply_links($stillLfy, ['https://stickyricethailao.werafoods.com/' => [200, 'https://stickyricethailao.werafoods.com/', 'Sticky Rice Thai Lao']], '');
check('listing competitor beats an old site that still shows LFY', [$r['system']['status'], $r['system']['providers']], ['Confirmed', ['Wera Foods']]);

// ---- add-on names
check('add-on name from signup form', cs_addon_name('Website Hosting + Email included - U$ 39.00 '), 'Website Hosting + Email included');
check('add-on name from price board', cs_addon_name('USD - $199 MEN03M00 Menu / Massage Pricing Design'), 'Menu / Massage Pricing Design');
check('set-up fee is not an add-on', cs_addon_name('AUD - $0 STF03M12 Set up fee - 12 months'), '');
check('labels from names + price codes', cs_addon_labels(['TH|phukasa' => ['names' => ['Website Hosting'], 'codes' => ['p1', 'p2', 'unknown']]],
    ['p1' => 'Menu / Massage Pricing Design', 'p2' => '']), ['TH|phukasa' => 'Add-on Only (Website Hosting, Menu / Massage Pricing Design)']);

// ---- package rules (company rule: Solo = marketing, Bundle = marketing + system, the rest add-on)
check('Solo', cs_package_kinds('SS02M03 Social Media Marketing Solo - 3 Months'), ['marketing']);
check('Bundle', cs_package_kinds('Local Starter Bundle'), ['marketing', 'system']);
check('Bundle mentioning hosting is still a bundle', cs_package_kinds('Social Media Marketing Bundle (Incl Website Hosting and Booking System)'), ['marketing', 'system']);
check('Ordering only', cs_package_kinds('PO01M00 Pro Online Ordering System'), ['system']);
check('Booking only', cs_package_kinds('BO02M00 Localforyou Booking System'), ['system']);
check('Hosting is add-on', cs_package_kinds('WHT03M00 Website Hosting + Email included'), ['addon']);
check('Yelp is add-on', cs_package_kinds('USD - $300 YEUS03A02 Yelp Ad Spend'), ['addon']);
check('Add-on Only', cs_package_kinds('Add-on Only - $0.00'), ['addon']);
check('AI Receptionist', cs_package_kinds('AI Receptionist - $249.00 + GST /Month'), ['araya']);

// ---- classification
$shop = ['id' => '1', 'cc' => 'AU', 'name' => 'Nina', 'domain' => 'ninathaimassage.com', 'scope' => ['marketing' => true, 'system' => true]];
$r = cs_classify($shop, [[200, 'https://www.ninathaimassage.com/', $nina]], []);
check('Nina: Fresha confirmed', [$r['system']['status'], $r['system']['providers']], ['Confirmed', ['Fresha']]);
check('Nina: Meta likely', [$r['marketing']['status'], $r['marketing']['channels']], ['Likely', ['Meta (Facebook / Instagram)']]);
check('Nina: agency unknown, builder Wix', [$r['agency']['status'], $r['builder']], ['Unknown', ['Wix']]);
$r = cs_classify($shop, [[200, 'https://www.ninathaimassage.com/', $nina]], ['1252027042430701']);
check('pixel that is L4U\'s own does not count', $r['marketing']['status'], 'Not Detected');
$r = cs_classify(['domain' => 'amarinthaiwarrawong.com.au'] + $shop, [[200, 'https://atchongkho.com.au/', $hiddenWp]], []);
check('redirect to another domain is noted', $r['notes'], ['Domain redirects to atchongkho.com.au']);
check('hidden WP builder', $r['builder'], ['WordPress']);
$moved = '<a href="https://danthaikitchenandbar.com.au/order">Order online</a>';
$r = cs_classify(['domain' => 'danthaikitchenandbar.com.au'] + $shop, [[200, 'https://danthaikitchen.com.au/', $moved]], []);
check('link back to the shop\'s own old domain is not a provider', $r['system']['status'], 'Not Detected');
$r = cs_classify($shop, [[0, '', '']], []);
check('unreachable site is Unknown, not Not Detected', [$r['site_status'], $r['system']['status'], $r['marketing']['status']],
    ['Not reachable (DNS / timeout)', 'Unknown', 'Unknown']);
$r = cs_classify(['scope' => ['marketing' => true, 'system' => false]] + $shop, [[200, 'https://x/', $nina]], []);
check('competitor found outside the package still shows', $r['system']['status'], 'Confirmed');
$r = cs_classify(['scope' => ['marketing' => true, 'system' => false]] + $shop, [[200, 'https://x/', '<p>hello</p>']], []);
check('system outside the package', $r['system']['status'], 'Not in package');

// ---- agency hints
$child = "/*\n Theme Name: Astra Child\n Author: Spice Digital\n Author URI: https://spicedigital.com.au\n Template: astra\n*/";
check('child theme author', cs_theme_author($child), ['name' => 'Spice Digital', 'url' => 'https://spicedigital.com.au']);
check('stock theme (no Template:) is not an agency', cs_theme_author("/*\nTheme Name: Astra\nAuthor: Brainstorm Force\n*/"), null);
check('child theme by a theme vendor is not an agency', cs_theme_author("/*\nAuthor: Brainstorm Force\nTemplate: astra\n*/"), null);
check('meta author', cs_meta_author('<meta content="Acme Web" name="author">'), 'Acme Web');
$r = cs_classify($shop, [[200, 'https://x/', '<meta name="author" content="Acme Web"><p>hi</p>']], [], [], [$child]);
check('theme author beats meta author', [$r['agency']['status'], $r['agency']['name']], ['Likely', 'Spice Digital']);
$r = cs_classify($shop, [[200, 'https://x/', '<meta name="author" content="Nina Thai Massage"><p>hi</p>']], []);
check('meta author = the shop itself is not an agency', $r['agency']['status'], 'Unknown');
$r = cs_classify(['name' => 'Mango Thai Restaurant (Colac) - BR.Colac'] + $shop, [[200, 'https://x/', '<meta name="author" content="Mango Colac Colac">']], []);
check('meta author with the shop\'s branch name is not an agency', $r['agency']['status'], 'Unknown');
$r = cs_classify($shop, [[200, 'https://x/', '<title>SITUS TOTO WIN4D Bandar Toto</title><meta name="author" content="SLOT GACOR">']], []);
check('gambling spam on the old domain', [$r['site_status'], $r['agency']['status']], ['Gambling spam on domain', 'Unknown']);
check('Toast-hosted CNAME maps to Toast', systems_in('sites.toasttab.com')['ordering'] ?? [], ['Toast']);
$mk = fn($d, $ids) => ['domain' => $d, 'agency' => ['status' => 'Unknown', 'name' => '', 'url' => ''], 'marketing' => ['ids' => ['gtm' => $ids]]];
$g = cs_shared_ids([$mk('a.com.au', ['GTM-AAAA1']), $mk('b.com', ['GTM-AAAA1']), $mk('x.tuckerfox.com.au', ['GTM-PLAT1']),
    $mk('y.tuckerfox.com.au', ['GTM-PLAT1']), $mk('c.com', ['GTM-XXXXXX']), $mk('d.com', ['GTM-XXXXXX'])]);
check('shared GTM → agency Likely', [$g[0]['agency']['status'], $g[0]['agency']['shared_with']], ['Likely', ['b.com']]);
$x = cs_shared_ids([['cc' => 'AU'] + $mk('a.com.au', ['GTM-CC1']), ['cc' => 'US'] + $mk('b.com', ['GTM-CC1'])]);
check('same id across countries is not an agency', $x[0]['agency']['status'], 'Unknown');
check('platform-wide id and placeholder ignored', [$g[2]['agency']['status'], $g[4]['agency']['status']], ['Unknown', 'Unknown']);
require_once __DIR__ . '/../models/places.php';
check('places: utm_source kept', cs_places_entry(['id' => '1', 'cc' => 'AU', 'name' => 'Bangkok Bay'], ['name' => 'Bangkok Bay', 'website' => 'https://bangkokbay.com.au/?utm_source=acmeweb', 'phone' => ''])['utm'] ?? '', 'utm_source=acmeweb');

// ---- selection rules
$P = fn($id, $name, $stage, $extra = []) => ['id' => $id, 'cc' => 'AU', 'name' => $name, 'group' => '', 'v' => $extra + ['stage3' => $stage, 'color1' => 'Thai Massage']];
$S = fn($id, $pid, $group, $pg, $date = '2026-03-01') => ['id' => $id, 'cc' => '', 'name' => $pg, 'group' => $group,
    'v' => ['status' => $group === 'Active Subscriptions' ? 'Active Subscription' : 'Cancelled', 'lookup_mkwgjt31' => $pg, 'date' => $date, 'connect_boards' => [$pid]]];
$projects = [
    $P('10', 'Alpha Thai - BR.A', 'Cancelled', ['cancel_date' => '2026-03-01', 'domain_name' => 'alpha.com']),
    $P('11', 'Beta Thai', 'Cancelled'),                                                      // live sub → not lost
    $P('12', 'Gamma Thai', 'Completed', ['date_mm26kg00' => '2026-02-01']),                 // partial: marketing
    $P('13', 'Delta Thai', 'Cancelled', ['color_mks76fr6' => 'Cancel before live']),
    $P('14', 'Echo Thai', 'Cancelled'), $P('15', 'Echo Thai', 'Completed'),                  // re-signed as a new project
    $P('16', 'Fox Thai', 'Cancelled', ['cancel_date' => '2021-01-01']), $P('17', 'Fox Thai', 'Cancelled', ['cancel_date' => '2025-05-01']),
    $P('18', 'Golf Thai', 'Test Data'),
    $P('19', 'Hotel Thai', 'Cancelled', ['text' => 'Promotion - Social Media Marketing Solo - $199.00 /Month']),
    $P('20', 'India Thai', 'Completed', ['date_mm26kg00' => '2026-02-01', 'color_mks7j57k' => 'Upgrade to Social Media Bundle']),   // plan change
    $P('21', 'Juliet Thai', 'Completed', ['date_mm26kg00' => '2026-02-01', 'color_mks7j57k' => 'Downgrade / Do by themselve',
        'text_mks7rcqh' => 'Running ads in-house now', 'live_date' => '2025-02-01', 'date_mkzs3896' => '2024-02-02']),   // real loss
];
$projects[] = $P('22', 'Kilo Thai', 'Completed', ['date_mm26kg00' => '2026-02-01']);   // upgrade reason only on the subscription
$projects[] = $P('23', 'Lima Spa - TH', 'Cancelled', ['text' => 'Add-on Only - ฿0.00']);
$projects[] = $P('24', 'Mike Noodle House - BR.X', 'Cancelled', ['cancel_date' => '2026-05-10', 'email' => 'Owner@Mike.com']);   // form found by email
$projects[] = $P('25', 'DeSPresso Thai and Brasserie - BR.Darlinghurst', 'Cancelled', ['cancel_date' => '2025-05-25']);       // form found by name words
$projects[] = $P('26', 'Star Thai Massage - BR.Cardiff', 'Cancelled', ['cancel_date' => '2025-05-25']);                       // one distinctive word: no guess
$subs = [$S('k', '22', 'Cancelled Subscription', 'Social Media Marketing Solo'), $S('a', '10', 'Cancelled Subscription', 'Social Media Marketing Solo'), $S('b', '10', 'Cancelled Subscription', 'Pro Online Ordering System'),
    $S('c', '10', 'Cancelled Subscription', 'Website Hosting', '2023-01-01'), $S('d', '11', 'Active Subscriptions', 'Booking System')];
$subs[0]['v']['text_mks62r3g'] = 'New Package 2026';
$subs[1]['v'] += ['numbers' => '199', 'status0' => 'aud']; $subs[2]['v'] += ['numbers' => '149.5', 'status0' => 'aud']; $subs[3]['v'] += ['numbers' => '50', 'status0' => 'aud'];
$rejoins = [['id' => 'r', 'cc' => '', 'name' => 'Alpha Thai : Rejoin', 'group' => '', 'v' => ['connect_boards_mkkskqcf' => ['10']]],
    ['id' => 'r2', 'cc' => '', 'name' => 'Fox : Rejoin Domain is due for renewal', 'group' => '', 'v' => ['x' => ['16']]]];
$form = [['county' => 'AU', 'shopname' => 'Alpha Thai', 'reason' => 'Found something better', 'other' => 'We do it ourselves', 'lastdate' => '', 'timestamp' => '2026-03-02'],
    ['county' => 'AU', 'shopname' => 'mike noodles', 'email' => 'owner@mike.com ', 'reason' => 'other', 'other' => 'Moving overseas', 'lastdate' => '', 'timestamp' => '2026-04-01'],
    ['county' => 'AU', 'shopname' => 'DESPRESSO THAI&BRASSERIE ', 'reason' => 'Cost too much', 'other' => 'NULL', 'lastdate' => '', 'timestamp' => '2025-05-23'],
    ['county' => 'AU', 'shopname' => 'Star Massage & Spa', 'reason' => 'Cost too much', 'other' => '', 'lastdate' => '', 'timestamp' => '2025-05-20']];
$shops = [];
foreach (cs_build_shops($projects, $subs, $rejoins, $form, ['AU|limaspa' => 'Add-on Only (Website Hosting)']) as $s) $shops[$s['id']] = $s;
check('lost shops (upgrade excluded, downgrade kept)', array_map('strval', array_keys($shops)), ['10', '12', '17', '19', '21', '23', '24', '25', '26']);
check('latest cancelled subs only (old hosting dropped)', $shops['10']['package'], 'Social Media Marketing Solo / Pro Online Ordering System');
check('solo + ordering → both scanned', $shops['10']['scope'], ['marketing' => true, 'system' => true]);
check('reason from cancellation form', [$shops['10']['reason'], $shops['10']['reason_src']], ['Found something better — We do it ourselves', 'form']);
check('rejoin flag', [$shops['10']['rejoined'], $shops['17']['rejoined']], [true, false]);
check('domain from monday', $shops['10']['domain'], 'alpha.com');
check('partial cancel scope', [$shops['12']['partial'], $shops['12']['scope']], ['Marketing', ['marketing' => true, 'system' => false]]);
check('duplicate keeps latest', [$shops['17']['cancel_date'], $shops['17']['dup_rows']], ['2025-05-01', 2]);
check('unknown package scans both', [$shops['17']['package'], $shops['17']['scope']], ['Unknown', ['marketing' => true, 'system' => true]]);
check('package from project text column', [$shops['19']['package'], $shops['19']['scope']],
    ['Promotion - Social Media Marketing Solo - $199.00 /Month', ['marketing' => true, 'system' => false]]);
check('reason details win over the reason label', $shops['21']['reason'], 'Running ads in-house now');
check('lifespan from the earlier live date', [$shops['21']['live_date'], $shops['21']['lifespan']], ['2024-02-02', 730]);
check('balance = last cancelled subs only', $shops['10']['balance'], ['AUD' => 348.5]);

check('form by email; "other" shows the typed answer', [$shops['24']['reason'], $shops['24']['reason_src']], ['Moving overseas', 'form']);
check('form by shop name words + date', $shops['25']['reason'], 'Cost too much');
check('one shared word is not a match', $shops['26']['reason'], '');

check('add-on only gets its add-ons named', [$shops['23']['package'], $shops['23']['addon_only']], ['Add-on Only (Website Hosting)', true]);

// ---- Google Places result → links.json entry
require_once __DIR__ . '/../models/places.php';
$sh = ['id' => '1', 'cc' => 'AU', 'name' => 'Bangkok Bay Thai Restaurant - BR.Toronto'];
check('places: provider site → link to check', cs_places_entry($sh, ['name' => 'Bangkok Bay Thai', 'website' => 'https://bangkokbay.mobi-order.com/?utm=g', 'phone' => '+61 2 1'])['urls'], ['https://bangkokbay.mobi-order.com/']);
check('places: own site → new domain', cs_places_entry($sh, ['name' => 'Bangkok Bay', 'website' => 'https://www.bangkokbay.com.au/', 'phone' => ''])['website'], 'bangkokbay.com.au');
check('places: facebook is not a website', cs_places_entry($sh, ['name' => 'Bangkok Bay', 'website' => 'https://facebook.com/bb', 'phone' => ''])['website'], '');
check('places: other shop on top is ignored', cs_places_entry($sh, ['name' => 'Siam Orchid Thai', 'website' => 'https://siam.com/', 'phone' => '+61'])['match'], false);

echo $fail ? "\n$fail failed\n" : "\nall passed\n";
exit($fail ? 1 : 0);
