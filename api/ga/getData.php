<?php
session_start();
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;

header('Content-Type: application/json; charset=utf-8');

$credPath = __DIR__ . '/../../credentials/a-service-account.json';
$propertyId = '369289543';

if (!file_exists($credPath)) {
    echo json_encode(['error' => 'Service account credentials not found.']);
    exit;
}

$period = $_GET['period'] ?? '7days';
switch ($period) {
    case '7days':   $startDate = '7daysAgo';  $endDate = 'today'; break;
    case '14days':  $startDate = '14daysAgo'; $endDate = 'today'; break;
    case '30days':  $startDate = '30daysAgo'; $endDate = 'today'; break;
    case '90days':  $startDate = '90daysAgo'; $endDate = 'today'; break;
    default:        $startDate = '7daysAgo';  $endDate = 'today'; break;
}

$prop = 'properties/' . $propertyId;
$dateRange = new DateRange(['start_date' => $startDate, 'end_date' => $endDate]);

try {
    $client = new BetaAnalyticsDataClient(['credentials' => $credPath]);

    // ── 1. Summary metrics ──
    $req = new RunReportRequest();
    $req->setProperty($prop);
    $req->setDateRanges([$dateRange]);
    $req->setMetrics([
        new Metric(['name' => 'activeUsers']),
        new Metric(['name' => 'sessions']),
        new Metric(['name' => 'screenPageViews']),
        new Metric(['name' => 'averageSessionDuration']),
        new Metric(['name' => 'bounceRate']),
        new Metric(['name' => 'newUsers']),
    ]);
    $summaryResponse = $client->runReport($req);
    $summaryRow = $summaryResponse->getRows()[0] ?? null;
    $summary = [
        'activeUsers'   => $summaryRow ? $summaryRow->getMetricValues()[0]->getValue() : 0,
        'sessions'      => $summaryRow ? $summaryRow->getMetricValues()[1]->getValue() : 0,
        'pageViews'     => $summaryRow ? $summaryRow->getMetricValues()[2]->getValue() : 0,
        'avgDuration'   => $summaryRow ? round((float)$summaryRow->getMetricValues()[3]->getValue()) : 0,
        'bounceRate'    => $summaryRow ? round((float)$summaryRow->getMetricValues()[4]->getValue() * 100, 1) : 0,
        'newUsers'      => $summaryRow ? $summaryRow->getMetricValues()[5]->getValue() : 0,
    ];

    // ── 2. Users by country (top 10) ──
    $req2 = new RunReportRequest();
    $req2->setProperty($prop);
    $req2->setDateRanges([$dateRange]);
    $req2->setDimensions([new Dimension(['name' => 'country'])]);
    $req2->setMetrics([new Metric(['name' => 'activeUsers'])]);
    $req2->setOrderBys([new OrderBy([
        'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
        'desc' => true
    ])]);
    $req2->setLimit(10);
    $countryResponse = $client->runReport($req2);
    $byCountry = [];
    foreach ($countryResponse->getRows() as $row) {
        $byCountry[] = [
            'country' => $row->getDimensionValues()[0]->getValue(),
            'users'   => (int)$row->getMetricValues()[0]->getValue(),
        ];
    }

    // ── 3. Top pages (top 10) ──
    $req3 = new RunReportRequest();
    $req3->setProperty($prop);
    $req3->setDateRanges([$dateRange]);
    $req3->setDimensions([new Dimension(['name' => 'pagePath'])]);
    $req3->setMetrics([
        new Metric(['name' => 'screenPageViews']),
        new Metric(['name' => 'activeUsers']),
    ]);
    $req3->setOrderBys([new OrderBy([
        'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
        'desc' => true
    ])]);
    $req3->setLimit(10);
    $pageResponse = $client->runReport($req3);
    $topPages = [];
    foreach ($pageResponse->getRows() as $row) {
        $topPages[] = [
            'page'      => $row->getDimensionValues()[0]->getValue(),
            'pageViews' => (int)$row->getMetricValues()[0]->getValue(),
            'users'     => (int)$row->getMetricValues()[1]->getValue(),
        ];
    }

    // ── 4. Traffic sources (top 10) ──
    $req4 = new RunReportRequest();
    $req4->setProperty($prop);
    $req4->setDateRanges([$dateRange]);
    $req4->setDimensions([new Dimension(['name' => 'sessionDefaultChannelGroup'])]);
    $req4->setMetrics([new Metric(['name' => 'sessions'])]);
    $req4->setOrderBys([new OrderBy([
        'metric' => new MetricOrderBy(['metric_name' => 'sessions']),
        'desc' => true
    ])]);
    $req4->setLimit(10);
    $sourceResponse = $client->runReport($req4);
    $trafficSources = [];
    foreach ($sourceResponse->getRows() as $row) {
        $trafficSources[] = [
            'source'   => $row->getDimensionValues()[0]->getValue(),
            'sessions' => (int)$row->getMetricValues()[0]->getValue(),
        ];
    }

    // ── 5. Users over time (daily) ──
    $req5 = new RunReportRequest();
    $req5->setProperty($prop);
    $req5->setDateRanges([$dateRange]);
    $req5->setDimensions([new Dimension(['name' => 'date'])]);
    $req5->setMetrics([
        new Metric(['name' => 'activeUsers']),
        new Metric(['name' => 'sessions']),
    ]);
    $req5->setOrderBys([new OrderBy([
        'dimension' => new DimensionOrderBy(['dimension_name' => 'date'])
    ])]);
    $dailyResponse = $client->runReport($req5);
    $daily = [];
    foreach ($dailyResponse->getRows() as $row) {
        $d = $row->getDimensionValues()[0]->getValue(); // YYYYMMDD
        $daily[] = [
            'date'     => substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2),
            'users'    => (int)$row->getMetricValues()[0]->getValue(),
            'sessions' => (int)$row->getMetricValues()[1]->getValue(),
        ];
    }

    // ── 6. Device category ──
    $req6 = new RunReportRequest();
    $req6->setProperty($prop);
    $req6->setDateRanges([$dateRange]);
    $req6->setDimensions([new Dimension(['name' => 'deviceCategory'])]);
    $req6->setMetrics([new Metric(['name' => 'activeUsers'])]);
    $req6->setOrderBys([new OrderBy([
        'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
        'desc' => true
    ])]);
    $deviceResponse = $client->runReport($req6);
    $devices = [];
    foreach ($deviceResponse->getRows() as $row) {
        $devices[] = [
            'device' => $row->getDimensionValues()[0]->getValue(),
            'users'  => (int)$row->getMetricValues()[0]->getValue(),
        ];
    }

    echo json_encode([
        'summary'        => $summary,
        'byCountry'      => $byCountry,
        'topPages'       => $topPages,
        'trafficSources' => $trafficSources,
        'daily'          => $daily,
        'devices'        => $devices,
        'period'         => $period,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
