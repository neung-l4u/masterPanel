<?php
/**
 * Product classification for the browser: GET ?names[]=AI+Receptionist
 *
 * The classification map lives in config.php, which the browser cannot read,
 * and duplicating it into JavaScript would guarantee the two drift. The
 * downgrade form asks here instead.
 *
 * Returns {"tags": {"<name>": ["<tag>", ...], ...}} for every name asked about.
 * There is nothing to authenticate and nothing to leak: the caller supplies the
 * names and gets back a category per name, which is the same information the
 * form already shows.
 */

require_once __DIR__ . '/../productClassify.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// The caller's own strings come back as object keys, so stop a browser
// sniffing this response as anything other than JSON.
header('X-Content-Type-Options: nosniff');

$names = $_GET['names'] ?? [];

// ?names=X (without the brackets) arrives as a plain string rather than an
// array, so it is wrapped to keep one code path below.
if (is_string($names)) {
    $names = [$names];
}

if (!is_array($names)) {
    $names = [];
}

$map  = udmClassificationMap();
$tags = [];

foreach ($names as $name) {
    if (!is_string($name)) {
        continue;
    }
    // Skip a name that is not valid UTF-8 rather than letting it reach
    // json_encode. Encoding is all-or-nothing: one malformed name would return
    // false for the whole response, so every other name in the same request
    // would come back untagged and no conditional section would fire -- the
    // customer would then submit a form missing the disclosures those sections
    // carry. Product names originate in Monday, so this is not ours to
    // guarantee. Dropping the one bad name leaves the rest correct.
    if (!preg_match('//u', $name)) {
        continue;
    }
    // Keyed by the name exactly as asked, so the caller can look its own
    // strings back up without having to re-trim them.
    $tags[$name] = udmClassifyProduct($name, $map);
}

// Cast to object so an empty result encodes as {} rather than [], and so a
// sequential numeric name such as "0" does not encode the map as a list.
$encoded = json_encode(['tags' => (object) $tags], JSON_UNESCAPED_UNICODE);

// A backstop now that invalid UTF-8 is filtered above: json_encode returns
// false on malformed UTF-8, and echoing false prints nothing at all, which the
// browser sees as a parse error rather than a failure. Mirrors the guard in
// lookupByEmail.php. The body stays success-shaped on purpose -- a caller that
// ignores the 500 then behaves as though no product classified, which fires no
// conditional section, and failing closed is the safe direction here.
if ($encoded === false) {
    http_response_code(500);
    echo '{"tags":{}}';
    return;
}

echo $encoded;
