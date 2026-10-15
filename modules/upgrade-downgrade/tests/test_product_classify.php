<?php
require_once __DIR__ . '/../assets/productClassify.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

// The map used throughout these tests. It is a literal copy of entries from
// config.example.php; the "config.example.php" section below asserts each one is
// still present there, so the two cannot drift apart unnoticed.
$map = [
    'AI Receptionist'                        => ['araya'],
    'AI + Local Starter Bundle'              => ['araya', 'plan_bundle'],
    'Social Media Marketing Bundle'          => ['addon'],
    'Social Media Marketing Bundle (Incl Website Hosting and Booking System)' => ['addon'],
];

echo "\n== The map is authoritative ==\n";

test("AI Receptionist is araya",
     udmClassifyProduct('AI Receptionist', $map) === ['araya']);

// The spec's worked example, and the reason the map exists at all: this name
// contains neither "AI Receptionist" nor "Araya", so the keyword fallback
// cannot see it. Only the map can.
test("AI + Local Starter Bundle is both araya and plan_bundle",
     udmClassifyProduct('AI + Local Starter Bundle', $map) === ['araya', 'plan_bundle']);

test("Social Media Marketing Bundle is an addon, not a plan bundle",
     udmClassifyProduct('Social Media Marketing Bundle', $map) === ['addon']);

test("the map beats the keyword rule it contradicts",
     !in_array('plan_bundle', udmClassifyProduct('Social Media Marketing Bundle (Incl Website Hosting and Booking System)', $map), true));

test("the long Social Media Marketing Bundle name is exactly an addon",
     udmClassifyProduct('Social Media Marketing Bundle (Incl Website Hosting and Booking System)', $map) === ['addon']);

// The two tests above pass with no map at all, because neither name contains
// "Local". This one has a name the keyword rule tags plan_bundle, so only map
// precedence can produce the asserted result.
test("the map overrides a keyword match that disagrees with it",
     udmClassifyProduct('Local Starter Bundle', ['Local Starter Bundle' => ['addon']]) === ['addon']);

test("without that map entry the same name is a plan bundle",
     udmClassifyProduct('Local Starter Bundle', ['Unrelated' => ['araya']]) === ['plan_bundle']);

echo "
== Map entries are untrusted: normalised ==
";

// The map lives in a gitignored, hand-edited file, so a typo or a bad order is
// a realistic input. The JS twin compares these arrays element by element.
test("addon is dropped when another tag is present",
     udmClassifyProduct('X', ['X' => ['addon', 'araya']]) === ['araya']);

test("an out-of-order entry is returned in canonical order",
     udmClassifyProduct('X', ['X' => ['plan_bundle', 'araya']]) === ['araya', 'plan_bundle']);

test("an unknown tag is dropped but known tags survive",
     udmClassifyProduct('X', ['X' => ['arya', 'plan_solo']]) === ['plan_solo']);

test("duplicate tags collapse",
     udmClassifyProduct('X', ['X' => ['araya', 'araya']]) === ['araya']);

test("an entry with only a typo'd tag falls through to keywords",
     udmClassifyProduct('Local Starter Bundle', ['Local Starter Bundle' => ['arya']]) === ['plan_bundle']);

test("an empty-array entry falls through to keywords",
     udmClassifyProduct('Local Starter Bundle', ['Local Starter Bundle' => []]) === ['plan_bundle']);

test("a typo'd entry on an unrecognised name falls through to addon, never empty",
     udmClassifyProduct('Some Brand New Product 2027', ['Some Brand New Product 2027' => ['arya']]) === ['addon']);

echo "\n== Keyword fallback, for names absent from the map ==\n";

test("Local Starter Bundle is a plan bundle",
     udmClassifyProduct('Local Starter Bundle', $map) === ['plan_bundle']);

test("Local Growth Bundle is a plan bundle",
     udmClassifyProduct('Local Growth Bundle', $map) === ['plan_bundle']);

test("Local Starter Solo is a plan solo",
     udmClassifyProduct('Local Starter Solo', $map) === ['plan_solo']);

test("Araya by name is araya",
     udmClassifyProduct('Araya Receptionist Add-on', $map) === ['araya']);

test("matching is case insensitive",
     udmClassifyProduct('local starter bundle', $map) === ['plan_bundle']);

echo "\n== Solo and Bundle without Local are add-ons ==\n";

// "Solo" and "Bundle" are used for add-on product families too. Requiring
// "Local" is what keeps the keep-list from firing on a social media add-on.
test("Mega Marketing Solo is an addon",
     udmClassifyProduct('Mega Marketing Solo', $map) === ['addon']);

test("Visibility Boost Solo - No Contract is an addon",
     udmClassifyProduct('Visibility Boost Solo - No Contract', $map) === ['addon']);

test("Social Media Marketing Solo is an addon",
     udmClassifyProduct('Social Media Marketing Solo', $map) === ['addon']);

test("Direct Marketing Bundle is an addon",
     udmClassifyProduct('Direct Marketing Bundle', $map) === ['addon']);

echo "\n== The addon default ==\n";

test("an unrecognised name is an addon",
     udmClassifyProduct('Pro Online Ordering System', $map) === ['addon']);

test("a name we have never seen is an addon",
     udmClassifyProduct('Some Brand New Product 2027', $map) === ['addon']);

test("empty string is an addon",
     udmClassifyProduct('', $map) === ['addon']);

test("whitespace only is an addon",
     udmClassifyProduct('   ', $map) === ['addon']);

test("a result is never empty",
     count(udmClassifyProduct('anything at all', $map)) > 0);

echo "\n== Comma-joined composite names ==\n";

// 13 of the 66 real values are several products joined by commas, written into
// one Monday column. Classifying the whole string is correct: cancelling that
// subscription cancels everything in it.
test("a composite containing a plan bundle is a plan bundle",
     udmClassifyProduct('Social Media Ad Spend Credit (Original), Local Starter Bundle - No Contract', $map) === ['plan_bundle']);

test("a composite containing a solo plan is a plan solo",
     udmClassifyProduct('GBP - 29.00 WHT03M00 Website Hosting + Email included, GBP - 149 LSS02M00 Local Starter Solo - No Contract', $map) === ['plan_solo']);

test("a composite of add-ons only is an addon",
     udmClassifyProduct('Yelp Ad Spend, Yelp Enhanced Profile', $map) === ['addon']);

echo "\n== Tag order is stable ==\n";

// Callers compare arrays, and the JS twin must agree, so order is part of the
// contract: araya, plan_bundle, plan_solo, addon.
test("tags come back in declaration order",
     udmClassifyProduct('AI + Local Starter Bundle', $map) === ['araya', 'plan_bundle']);

echo "\n== An absent map still works ==\n";

test("no map falls back to keywords",
     udmClassifyProduct('Local Starter Bundle') === ['plan_bundle']);

// Asserted against the keyword rules explicitly rather than by omitting the
// argument. Omitting it reads the real config.php, so asserting ['plan_bundle']
// would pass only on a machine whose config.php lacks the product_classification
// key, and fail on every correctly deployed one -- the opposite of useful.
test("the keyword rules alone cannot see AI + Local Starter Bundle as araya",
     udmClassifyProduct('AI + Local Starter Bundle', ['Unrelated Name' => ['addon']]) === ['plan_bundle']);

echo "
== udmClassificationMap() ==
";

test("returns an array", is_array(udmClassificationMap()));

echo "
== The test map has not drifted from config.example.php ==
";

// config.example.php is the file a deployment copies the real map out of, so
// these are what stop the map at the top of this file from diverging from it.
$example = require __DIR__ . '/../assets/config.example.php';

test("config.example.php ships a non-empty product_classification map",
     is_array($example['product_classification'] ?? null) && $example['product_classification'] !== []);

test("config.example.php ships the araya_feedback_url key",
     array_key_exists('araya_feedback_url', $example));

foreach ($map as $exampleName => $exampleTags) {
    test("config.example.php still classifies {$exampleName} identically",
         ($example['product_classification'][$exampleName] ?? null) === $exampleTags);
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
