<?php
/**
 * The call-quality rates and the four findings built on them.
 *
 * What is pinned here is the handful of ways a percentage lies. A rate off
 * three calls, a rate whose numerator gcal never sent, a conversion rate that
 * counts misdials as lost bookings, a conversion rate that counts calls from
 * before the booking instrumentation as failures, and a shop flagged against a
 * baseline that was not there -- each of those turns the dashboard into a list
 * of shops that are not actually in trouble, which is the one failure mode
 * that makes the whole report useless.
 */
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/buildBrief.php';

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

const TODAY = '2026-10-20';

/** A call window with zeros for everything not named. */
function win(array $over = []): array {
    return array_merge([
        'total' => 0, 'forwarded' => 0, 'short' => 0, 'silent' => 0, 'errors' => 0,
        'minutes' => 0, 'booked' => 0, 'booking_failed' => 0, 'long' => 0,
        'booked_unknown' => 0,
    ], $over);
}

/** A matched LIVE pair carrying the given d7 window; d30 mirrors it. */
function shop(string $name, array $d7, array $monday = []): array {
    return [
        'monday' => array_merge([
            'name' => $name, 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-06-01',
            'reason_for_cancel' => '', 'note' => '',
            'calendar_id' => 'cal-' . $name, 'email' => $name . '@x.com',
        ], $monday),
        'gcal' => [
            'shop_id' => 'g-' . $name, 'shop_name' => $name, 'remaining_minutes' => 200,
            'allocation' => 250, 'billing_day' => 15, 'days_until_refresh' => 26,
            'currency' => 'AUD', 'closed_periods' => [],
            'calls' => ['d7' => $d7, 'd30' => $d7],
        ],
        'matched_by' => 'calendar',
    ];
}

function res(array $matched): array {
    return ['matched' => $matched, 'unmatchedMonday' => [], 'unmatchedGcal' => []];
}

/** A fleet block, both windows the same. */
function fleet(array $d7): array {
    return ['d7' => $d7, 'd30' => $d7];
}

echo "== arayaRate: a percentage only when it means something ==\n";
test("a plain rate", arayaRate(24, 100) === 24.0);
test("rounded to one decimal", arayaRate(1, 3, 1) === 33.3);
test("a zero numerator is 0%, not null", arayaRate(0, 100) === 0.0);
test("a zero denominator is null, not a division", arayaRate(5, 0) === null);
test("a negative denominator is null", arayaRate(5, -10) === null);
// The one that matters in practice: one forwarded call out of three is 33%,
// which would put a quiet shop above a shop with a real problem.
test("a denominator under the minimum is null", arayaRate(1, 3) === null);
test("a denominator exactly at the minimum counts",
     arayaRate(1, ARAYA_RATE_MIN_CALLS) !== null);
test("a null numerator is null, never 0%", arayaRate(null, 100) === null);
test("a null denominator is null", arayaRate(5, null) === null);
test("a non-numeric numerator is null", arayaRate('lots', 100) === null);
test("the minimum can be waived for the fleet figure", arayaRate(1, 3, 0) === 33.3);

echo "== arayaWindowRates: the three rates ==\n";
$r = arayaWindowRates(win(['total' => 200, 'forwarded' => 24, 'short' => 50,
                           'booked' => 60, 'long' => 100, 'booked_unknown' => 0]));
test("forwarded is a share of every call", $r['forwarded_pct'] === 12.0);
test("short is a share of every call", $r['short_pct'] === 25.0);
// 60 of the 100 calls long enough to have booked, not 60 of 200: dividing by
// every call would count misdials and wrong numbers as lost bookings.
test("conversion is a share of the long calls only", $r['conversion_pct'] === 60.0);
test("the conversion denominator is reported", $r['conversion_base'] === 100);

// Calls from before the booking instrumentation have booked NULL upstream and
// arrive here counted in booked_unknown. Left in the denominator they would
// report the whole pre-instrumentation history as a conversion collapse.
$r = arayaWindowRates(win(['total' => 200, 'booked' => 30, 'long' => 100, 'booked_unknown' => 50]));
test("unknown-outcome calls leave the conversion denominator", $r['conversion_base'] === 50);
test("conversion uses only the calls we know about", $r['conversion_pct'] === 60.0);

$r = arayaWindowRates(win(['total' => 200, 'booked' => 0, 'long' => 20, 'booked_unknown' => 20]));
test("every long call unknown gives no conversion rate, not 0%",
     $r['conversion_base'] === 0 && $r['conversion_pct'] === null);

// A gcal that predates the booking figures sends the original six only.
$r = arayaWindowRates(['total' => 200, 'forwarded' => 24, 'short' => 50, 'silent' => 0,
                       'errors' => 0, 'minutes' => 300]);
test("an older gcal still gets forwarded and short rates",
     $r['forwarded_pct'] === 12.0 && $r['short_pct'] === 25.0);
test("an older gcal gets no conversion rate rather than 0%",
     $r['conversion_pct'] === null && $r['conversion_base'] === null);

$r = arayaWindowRates(null);
test("a missing window gives all nulls",
     $r['forwarded_pct'] === null && $r['short_pct'] === null
     && $r['conversion_pct'] === null && $r['conversion_base'] === null);

// More unknowns than long calls should not produce a negative denominator.
$r = arayaWindowRates(win(['total' => 200, 'booked' => 5, 'long' => 10, 'booked_unknown' => 40]));
test("a nonsensical unknown count clamps at zero rather than going negative",
     $r['conversion_base'] === 0 && $r['conversion_pct'] === null);

echo "== arayaFleetRates ==\n";
$f = arayaFleetRates(fleet(win(['total' => 1000, 'forwarded' => 124, 'short' => 200,
                                'booked' => 300, 'long' => 500, 'booked_unknown' => 0])));
test("both windows come back", array_keys($f) === ['d7', 'd30']);
test("the fleet forwarded rate", $f['d7']['forwarded_pct'] === 12.4);
test("the fleet conversion rate", $f['d7']['conversion_pct'] === 60.0);
test("the fleet counts are kept beside the rates", $f['d7']['total'] === 1000);
test("no fleet block gives null, not zeros", arayaFleetRates(null) === null);
test("a malformed fleet block gives null", arayaFleetRates(['d7' => 'nonsense']) === null);
// The fleet denominator is every call the fleet took, so there is no small
// sample to guard against -- and a minimum would switch the comparison off
// entirely on a quiet fleet.
$f = arayaFleetRates(fleet(win(['total' => 4, 'forwarded' => 1])));
test("the fleet rate is computed however few calls it took", $f['d7']['forwarded_pct'] === 25.0);

echo "== the live row carries its rates ==\n";
$b = arayaBuildBrief(res([shop('A', win(['total' => 100, 'forwarded' => 10, 'short' => 5,
                                         'booked' => 30, 'long' => 50]))]), TODAY);
test("rates are present for both windows",
     array_keys($b['live'][0]['rates']) === ['d7', 'd30']);
test("the rate matches the counts beside it", $b['live'][0]['rates']['d7']['forwarded_pct'] === 10.0);
test("the counts are still passed through untouched", $b['live'][0]['calls']['d7']['total'] === 100);

$b = arayaBuildBrief(res([shop('A', win())]), TODAY);
test("a shop with no calls gets null rates, not 0%",
     $b['live'][0]['rates']['d7']['forwarded_pct'] === null);

echo "== forwarding above the fleet ==\n";
$fleet = fleet(win(['total' => 1000, 'forwarded' => 124]));   // fleet 12.4%
$b = arayaBuildBrief(res([
    shop('Way over', win(['total' => 100, 'forwarded' => 30])),   // 30.0%
    shop('Just over', win(['total' => 100, 'forwarded' => 18])),  // 18.0%, +5.6
    shop('Near fleet', win(['total' => 100, 'forwarded' => 14])), // 14.0%, +1.6
    shop('Under', win(['total' => 100, 'forwarded' => 5])),
]), TODAY, null, $fleet);
$names = array_column($b['forwardingHigh'], 'name');
test("a shop well over the fleet is flagged", in_array('Way over', $names, true));
test("a shop just past the margin is flagged", in_array('Just over', $names, true));
// A shop at 13.5% against a fleet at 12.4% is doing what everyone else does;
// flagging it would bury the shop sitting at twice the rate.
test("a shop within the margin is not flagged", !in_array('Near fleet', $names, true));
test("a shop under the fleet is not flagged", !in_array('Under', $names, true));
test("the worst shop leads", $b['forwardingHigh'][0]['name'] === 'Way over');
test("the finding carries the fleet figure it was judged against",
     $b['forwardingHigh'][0]['fleet_pct'] === 12.4);
test("the finding carries the counts behind the rate",
     $b['forwardingHigh'][0]['total'] === 100 && $b['forwardingHigh'][0]['forwarded'] === 30);

// Without a fleet figure there is nothing to compare with. An absolute
// threshold invented here would be a guess presented as a finding.
$b = arayaBuildBrief(res([shop('Way over', win(['total' => 100, 'forwarded' => 30]))]), TODAY);
test("nothing is flagged for forwarding without a fleet baseline", $b['forwardingHigh'] === []);
test("the fleet block is null in the output too", $b['fleetCalls'] === null);

// A shop with too few calls has no rate at all, so it cannot be flagged.
$b = arayaBuildBrief(res([shop('Tiny', win(['total' => 3, 'forwarded' => 3]))]), TODAY, null, $fleet);
test("a shop with too few calls is not flagged", $b['forwardingHigh'] === []);

echo "== callers hanging up early ==\n";
// Judged against a fixed threshold, not the fleet: the fleet average for this
// is itself high, so comparing with it would excuse every shop.
$highFleet = fleet(win(['total' => 1000, 'short' => 250]));   // fleet 25%
$b = arayaBuildBrief(res([
    shop('Worst', win(['total' => 100, 'short' => 31])),
    shop('Over', win(['total' => 100, 'short' => 21])),
    shop('At threshold', win(['total' => 100, 'short' => 20])),
    shop('Under', win(['total' => 100, 'short' => 19])),
]), TODAY, null, $highFleet);
$names = array_column($b['hangupHigh'], 'name');
test("a shop over the threshold is flagged", in_array('Over', $names, true));
test("the threshold is inclusive", in_array('At threshold', $names, true));
test("a shop under the threshold is not flagged", !in_array('Under', $names, true));
test("a shop below the fleet average is still flagged if it passes the threshold",
     in_array('Over', $names, true));
test("the worst leads", $b['hangupHigh'][0]['name'] === 'Worst');
test("the fleet figure is still reported for context", $b['hangupHigh'][0]['fleet_pct'] === 25.0);

// The absolute rule works with no fleet figure at all, which is the point of
// it being absolute.
$b = arayaBuildBrief(res([shop('Over', win(['total' => 100, 'short' => 30]))]), TODAY);
test("the hang-up rule works without a fleet baseline", count($b['hangupHigh']) === 1);
test("the fleet figure is null when there is none", $b['hangupHigh'][0]['fleet_pct'] === null);

echo "== conversion below the fleet ==\n";
$fleet = fleet(win(['total' => 1000, 'booked' => 300, 'long' => 500]));   // fleet 60%
$b = arayaBuildBrief(res([
    shop('Worst', win(['total' => 100, 'booked' => 9, 'long' => 100])),    // 9%
    shop('Low', win(['total' => 100, 'booked' => 40, 'long' => 100])),     // 40%
    shop('At margin', win(['total' => 100, 'booked' => 55, 'long' => 100])), // 55%, -5
    shop('Near fleet', win(['total' => 100, 'booked' => 58, 'long' => 100])),
    shop('Better', win(['total' => 100, 'booked' => 80, 'long' => 100])),
]), TODAY, null, $fleet);
$names = array_column($b['conversionLow'], 'name');
test("a shop well under the fleet is flagged", in_array('Worst', $names, true));
test("the margin is inclusive", in_array('At margin', $names, true));
test("a shop within the margin is not flagged", !in_array('Near fleet', $names, true));
test("a shop above the fleet is not flagged", !in_array('Better', $names, true));
test("the lowest conversion leads", $b['conversionLow'][0]['name'] === 'Worst');
test("the finding carries its denominator", $b['conversionLow'][0]['base'] === 100);
test("the finding carries the bookings it did make", $b['conversionLow'][0]['booked'] === 9);

$b = arayaBuildBrief(res([shop('Worst', win(['total' => 100, 'booked' => 9, 'long' => 100]))]), TODAY);
test("nothing is flagged for conversion without a fleet baseline", $b['conversionLow'] === []);

// Every long call unknown means we have no conversion figure for the shop, so
// it cannot be flagged -- the alternative is reporting an un-instrumented
// shop as having lost every booking.
$b = arayaBuildBrief(res([
    shop('No data', win(['total' => 100, 'booked' => 0, 'long' => 100, 'booked_unknown' => 100])),
]), TODAY, null, $fleet);
test("a shop with no known booking outcomes is not flagged", $b['conversionLow'] === []);

echo "== bookings that failed ==\n";
// Counted, not rated: two failures out of two calls and two out of fifty are
// the same bug, so neither the sample size nor the fleet gates this.
$b = arayaBuildBrief(res([
    shop('Many', win(['total' => 50, 'booking_failed' => 3, 'booked' => 10, 'long' => 40])),
    shop('One', win(['total' => 50, 'booking_failed' => 1, 'booked' => 20, 'long' => 40])),
    shop('None', win(['total' => 50, 'booking_failed' => 0, 'booked' => 30, 'long' => 40])),
    shop('Tiny but broken', win(['total' => 2, 'booking_failed' => 2])),
]), TODAY);
$names = array_column($b['bookingBroken'], 'name');
test("a shop with failures is flagged", in_array('Many', $names, true));
test("a single failure is enough", in_array('One', $names, true));
test("a shop with none is not flagged", !in_array('None', $names, true));
test("a shop with too few calls for a rate is still flagged",
     in_array('Tiny but broken', $names, true));
test("the most failures lead", $b['bookingBroken'][0]['name'] === 'Many');
test("the finding carries the failure count", $b['bookingBroken'][0]['failed'] === 3);
test("no fleet baseline is needed", count($b['bookingBroken']) === 3);

// An older gcal sends no booking_failed at all. Null is not zero, but it is
// also not a finding: there is nothing to report either way.
$b = arayaBuildBrief(res([shop('Old gcal', ['total' => 50, 'forwarded' => 1, 'short' => 1,
                                            'silent' => 0, 'errors' => 0, 'minutes' => 60])]), TODAY);
test("a gcal without the figure produces no finding", $b['bookingBroken'] === []);

echo "== sections are always present ==\n";
$b = arayaBuildBrief(res([]), TODAY);
foreach (['forwardingHigh', 'hangupHigh', 'conversionLow', 'bookingBroken'] as $k) {
    test("{$k} is an array even with no shops", is_array($b[$k]) && $b[$k] === []);
}
test("the rate window is reported", $b['rateWindow'] === ARAYA_RATE_WINDOW);

echo "== only LIVE shops are judged ==\n";
// A shop that has given notice, or is not live yet, has no business in a
// call-quality finding: nobody is going to act on it.
$fleet = fleet(win(['total' => 1000, 'forwarded' => 124]));
$b = arayaBuildBrief(res([
    shop('Leaving', win(['total' => 100, 'forwarded' => 40]), ['live_status' => 'Waiting to cancel']),
    shop('Not yet', win(['total' => 100, 'forwarded' => 40]), ['live_status' => 'Testing']),
]), TODAY, null, $fleet);
test("a pending-cancel shop is not flagged", $b['forwardingHigh'] === []);
test("a shop in setup is not flagged", $b['hangupHigh'] === []);

echo "== a gcal outage flags nothing ==\n";
// With gcal down every shop arrives with no calls block, so there are no
// rates and nothing to flag. Flagging on absent data would fill the report
// with findings on the one morning nobody can check them.
$b = arayaBuildBrief([
    'matched' => [],
    'unmatchedMonday' => [['name' => 'Ghost', 'live_status' => 'LIVE', 'live_date' => '2026-06-01']],
    'unmatchedGcal' => [],
], TODAY, null, null);
test("a shop with no gcal match is not flagged",
     $b['forwardingHigh'] === [] && $b['hangupHigh'] === []
     && $b['conversionLow'] === [] && $b['bookingBroken'] === []);
test("its rates are null rather than zero", $b['live'][0]['rates']['d7']['short_pct'] === null);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed === 0 ? 0 : 1);
