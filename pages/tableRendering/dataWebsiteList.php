<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

// Our own domains, shared by many shops' sites.
const PLATFORM_DOMAINS = ['localforyou.com', 'localforyou.net', 'localforyoubooking.com'];

$params = [
    "shopType" => !empty($_POST['shopType']) ? $_POST['shopType'] : '',
    "system"   => !empty($_POST['system'])   ? $_POST['system']   : '',
    "liveStatus"   => !empty($_POST['liveStatus'])  ? $_POST['liveStatus']  : '',
    "template" => !empty($_POST['template']) ? $_POST['template'] : '',
    "country"  => !empty($_POST['country'])  ? $_POST['country']  : '',
    "server"   => !empty($_POST['server'])   ? $_POST['server']   : '',
    "domainProvider" => !empty($_POST['domainProvider']) ? $_POST['domainProvider'] : '',
];

$qb = new QueryBuilder();
$qb->eq('w.wIndustry', $params["shopType"]);

if ($params["system"] === "AM")      $qb->raw("AND w.wSystemAmelia = 1");
elseif ($params["system"] === "GF")  $qb->raw("AND w.wSystemGloriaFood = 1");
elseif ($params["system"] === "VC")  $qb->raw("AND w.wSystemVoucher = 1");

$qb->eq('w.wLiveStatus', $params["liveStatus"])
   ->eq('w.wTemplateUsed', $params["template"])
   ->eq('w.countryID', $params["country"])
   ->eq('sv.svID', $params["server"]);

// "none" = sites with no domain provider recorded (NULL or 0)
if ($params["domainProvider"] === "none") $qb->raw("AND (w.wDomainProvidersID IS NULL OR w.wDomainProvidersID = 0)");
else $qb->eq('w.wDomainProvidersID', $params["domainProvider"]);

$baseSql = "SELECT * FROM websiteList w LEFT JOIN L4UServers sv ON w.svID = sv.svID WHERE delete_at IS NULL";
$result = $qb->execute($db, $baseSql, 'ORDER BY w.wID DESC')->fetchAll();

// Build one table row (6 visible cells) for a websiteList record.
$cells = function ($row) {
    $statusWebsite = !empty($row["wLiveStatus"]) ? $row["wLiveStatus"] : '-';
    $siteUrl = ensureAbsoluteUrl(stripWpAdmin($row["wWordpressURL"]));
    $shown = preg_replace('#^https?://#i', '', rtrim($siteUrl, '/'));   // the address without the scheme reads cleaner
    $link = !empty($siteUrl) ? '<a href="'.escUrl($siteUrl).'" target="_blank" class="siteLink" title="'.esc($siteUrl).'">'.esc($shown).'</a>' : '-';
    // The URL column shows the WordPress address; when the shop's domain is different (booking sites on
    // *.localforyoubooking.com, drafts on preview) show it underneath so it can be seen and found by Search.
    $domain = hostOnly($row["wDomain"]);
    if ($domain !== '' && $domain !== hostOnly($siteUrl)) {
        $link .= '<br><small class="text-muted">Domain: ' . esc($domain) . '</small>';
    }
    $wpAdminUrl = ensureAbsoluteUrl($row["wWordpressURL"]);
    $btn = '<a href="'.escUrl($wpAdminUrl).'" target="_blank" title="WP-Admin"><i class="bi bi-box-arrow-up-right"></i></a> '
         . '<a href="#" onclick="setEdit('.intval($row["wID"]).')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a> '
         . '<a href="#" onclick="setDel('.intval($row["wID"]).')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';
    return [
        intval($row["wID"]),
        '<a href="#" onclick="viewDetail('.intval($row["wID"]).')" title="Detail" class="linkDetail">'.esc(dash($row["wProject"])).'</a>',
        $link,
        esc($row['svName'] ?? '-'),
        esc($statusWebsite),
        $btn,
    ];
};

// Group branch sites under their main site: bankstown.thaiparagon.com.au under thaiparagon.com.au, and a
// booking site on *.localforyoubooking.com under the shop domain it belongs to (wDomain).
// Groups are built from every record, so a branch that matches the filters still appears under its main
// row even when the main row itself does not match (e.g. Status = Subdomain while the main site is Live).
$all = array_filter($params) ? (new QueryBuilder())->execute($db, $baseSql, 'ORDER BY w.wID DESC')->fetchAll() : $result;
$matched = array_flip(array_column($result, 'wID'));

$groups = [];
foreach ($all as $row) {
    $groups[registrable(shopHost($row))][] = $row;
}
// A booking site (*.localforyoubooking.com) belongs under its shop's main site when the shop has one
// (Malai Wellness); when it has none, it goes under the "Local Booking" record, if that record exists.
$isMain = fn($m, $key) => siteHost($m) === $key;
$hasMain = fn($key) => (bool) array_filter($groups[$key] ?? [], fn($m) => $isMain($m, $key));
if ($hasMain('localforyoubooking.com')) {
    foreach ($groups as $key => $members) {
        if (in_array($key, PLATFORM_DOMAINS, true) || $hasMain($key)) continue;
        foreach ($members as $k => $m) {
            if (!str_ends_with(siteHost($m), '.localforyoubooking.com')) continue;
            $groups['localforyoubooking.com'][] = $m;
            unset($groups[$key][$k]);
        }
        if (!$groups[$key]) unset($groups[$key]);
    }
}
// Our own platform domains (localforyoubooking.com, localforyou.com) hold many shops. When the platform
// domain itself has a record (e.g. "Local Booking") its subdomains sit under it; when it has none, split
// the group per shop so unrelated shops are not lumped together.
foreach ($groups as $key => $members) {
    if (!in_array($key, PLATFORM_DOMAINS, true)) continue;
    foreach ($members as $m) if (siteHost($m) === $key) continue 2;
    unset($groups[$key]);
    foreach ($members as $m) $groups[platformShopKey($m)][] = $m;
}

$single = fn($row, &$i) => array_merge([$i++], array_slice($cells($row), 1), ['', [], false]);
$data = array("data" => array());
$i = 1;
foreach ($groups as $key => $members) {
    $hits = array_values(array_filter($members, fn($m) => isset($matched[$m['wID']])));
    if (!$hits) continue;   // nothing in this group matches the filters
    // main = the record whose own site sits on the group's domain
    $main = null;
    foreach ($members as $m) if (siteHost($m) === $key) { $main = $m; break; }
    // Only a shop with no main record at all on our platform (Thai Body Works) gets a header row of its own;
    // other groups without a main record are just ordinary rows.
    $platformShop = preg_match('/\.localforyou(booking)?\.(com|net)$/', $key);
    $children = array_values(array_filter($hits, fn($m) => $main === null || $m['wID'] !== $main['wID']));
    if (count($members) === 1 || ($main === null && !$platformShop) || !$children) {
        foreach ($hits as $m) $data["data"][] = $single($m, $i);
        continue;
    }
    $head = $main ? $cells($main) : [0, '<span class="text-muted">' . esc($key) . '</span>', '-', '-', '-', ''];
    $searchText = array_map(fn($m) => $m['wProject'] . ' ' . $m['wDomain'] . ' ' . $m['wWordpressURL'], $children);
    $toggle = '<button type="button" class="grp-toggle btn btn-sm btn-light border py-0 px-1 me-2" title="Show / hide sites under this domain"><i class="bi bi-chevron-right"></i></button>';
    $head[1] = $toggle . $head[1] . ' <span class="badge rounded-pill text-bg-light border grp-count ms-1">' . count($children) . (count($children) === 1 ? ' site' : ' sites') . '</span>';
    // open the group straight away when only its branches matched the filters
    $autoOpen = $main === null ? (bool) array_filter($params) : !isset($matched[$main['wID']]);
    $data["data"][] = array_merge([$i++], array_slice($head, 1), [esc(implode(' ', $searchText)), array_map($cells, $children), $autoOpen]);
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//

// Host of the site we host for a record: the booking host when it is on *.localforyoubooking.com, else wDomain.
function siteHost($row): string
{
    $wp = hostOnly($row["wWordpressURL"]);
    return str_ends_with($wp, '.localforyoubooking.com') ? $wp : (hostOnly($row["wDomain"]) ?: $wp);
}

function registrable(string $host): string
{
    $p = explode('.', $host);
    $n = count($p);
    return ($n > 2 && in_array($p[$n - 2], ['com', 'net', 'org', 'co', 'ac', 'gov', 'edu', 'id', 'asn'], true))
        ? implode('.', array_slice($p, -3)) : implode('.', array_slice($p, -2));
}

// The shop's own domain for a record (wDomain), falling back to the site we host.
function shopHost($row): string
{
    return hostOnly($row["wDomain"]) ?: siteHost($row);
}

// On a platform domain with no record of its own, a site groups only with others under the same shop
// label (the branches of thaibodyworks.localforyoubooking.com); otherwise it stands alone.
function platformShopKey($row): string
{
    $host = shopHost($row);
    $reg = registrable($host);
    $labels = explode('.', substr($host, 0, -strlen($reg) - 1));
    return count($labels) >= 2 ? end($labels) . '.' . $reg : 'single:' . $row["wID"];
}

function hostOnly($url): string
{
    $host = preg_replace('#^[a-z]+://#i', '', trim((string) $url));
    return strtolower(preg_replace('/^www\./i', '', preg_split('#[/?\#:]#', $host)[0]));
}

function dash($param){
    if (empty($param)) { return "-"; }
    else {
        return $param;
    }
}

function dashAndShort($param): string
{
    if (empty($param)) { return "-"; }
    else {
        $location = mb_substr($param, 0, 15).'...';
        return '<abbr title="'.esc($param).'">'.esc($location).'</abbr>';
    }
}
