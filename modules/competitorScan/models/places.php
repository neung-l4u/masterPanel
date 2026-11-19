<?php
/**
 * Google Places API (New) lookup for shops whose own site can't be checked: the Google listing's website
 * (an ordering / booking page, or a new own domain) and phone go into cache/links.json, which every scan reads.
 *
 * Billing: websiteUri / phone are "Text Search Enterprise" fields — 1,000 free requests a month, then $35 / 1,000.
 * CS_PLACES_MONTHLY keeps us inside the free tier (also set a quota cap in the Cloud console).
 * The key lives in config/competitor_scan.php (gitignored): <?php return ['places_key' => '...'];
 */
require_once __DIR__ . '/scan.php';

const CS_PLACES_MONTHLY = 1000;  // requests per calendar month = the 1,000 free (Enterprise SKU)
const CS_PLACES_DAILY = 40;
const CS_COUNTRY = ['AU' => 'Australia', 'NZ' => 'New Zealand', 'UK' => 'United Kingdom', 'US' => 'USA', 'TH' => 'Thailand', 'CA' => 'Canada'];

function cs_places_key() {
    $f = __DIR__ . '/../../../config/competitor_scan.php';
    $c = is_file($f) ? include $f : [];
    return trim($c['places_key'] ?? '');
}

/** Top Places result for the shop, or null. Throws on HTTP / API errors (bad key, quota). */
function cs_places_search(array $shop, $key) {
    $base = trim(preg_split('/\s+[-–|]\s+BR\.|\s*\(/u', $shop['name'])[0]);
    $branch = preg_match('/BR\.\s*(.+)$/u', $shop['name'], $m) ? trim($m[1]) : '';
    $q = trim("$base $branch " . (CS_COUNTRY[$shop['cc']] ?? ''));
    $ch = curl_init('https://places.googleapis.com/v1/places:searchText');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', "X-Goog-Api-Key: $key",
            'X-Goog-FieldMask: places.displayName,places.websiteUri,places.internationalPhoneNumber'],
        CURLOPT_POSTFIELDS => json_encode(['textQuery' => $q, 'pageSize' => 1,
            'regionCode' => strtolower($shop['cc'] === 'UK' ? 'GB' : $shop['cc'])])]);
    $body = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode((string) $body, true);
    if ($code !== 200) throw new RuntimeException("Places HTTP $code: " . ($j['error']['message'] ?? substr((string) $body, 0, 200)));
    $p = $j['places'][0] ?? null;
    if (!$p) return null;
    return ['name' => $p['displayName']['text'] ?? '', 'website' => $p['websiteUri'] ?? '', 'phone' => $p['internationalPhoneNumber'] ?? ''];
}

/** The top result is the same shop when its name carries one of the shop's distinctive words. */
function cs_places_same_shop($shopName, $placeName) {
    $base = strtolower(preg_split('/\s+[-–|]\s+BR\.|\s*\(/u', $shopName)[0]);
    $words = array_filter(preg_split('/[^a-z0-9]+/', $base), fn($w) => strlen($w) >= 4
        && !in_array($w, ['thai', 'restaurant', 'massage', 'kitchen', 'cuisine', 'street', 'food', 'beauty', 'therapy', 'spa', 'cafe', 'takeaway'], true));
    $n = cs_norm($placeName);
    return (bool) array_filter($words, fn($w) => str_contains($n, $w));
}

/**
 * links.json entry from a Places result. A provider page (fresha, mobi-order…) goes to urls for cs_apply_links;
 * any other site is the shop's current own domain, scanned in place of the dead / missing one.
 */
function cs_places_entry(array $shop, ?array $p) {
    $e = ['urls' => [], 'phone' => '', 'website' => '', 'source' => 'places', 'found_at' => date('Y-m-d')];
    if (!$p || !cs_places_same_shop($shop['name'], $p['name'])) return $e + ['match' => false];
    $e['phone'] = $p['phone'];
    if ($p['website'] !== '') {
        parse_str((string) parse_url($p['website'], PHP_URL_QUERY), $q);   // utm_source=agencyname on the listing link
        if (!empty($q['utm_source']) && !preg_match('/^(google|gmb|gbp|maps|local|organic|yext)/i', $q['utm_source'])) $e['utm'] = 'utm_source=' . $q['utm_source'];
        $sys = systems_in(strtolower(host_of($p['website'])));
        if (!empty($sys['ordering']) || !empty($sys['booking']) || !empty($sys['ours'])) $e['urls'] = [preg_replace('/[?#].*$/', '', $p['website'])];
        elseif (!preg_match('/facebook|instagram|google|tiktok|yelp|localforyou\.com$|linktr\.ee|ozfoodhunter/', host_of($p['website']))) $e['website'] = host_of($p['website']);
    }
    return $e + ['match' => true];
}
