<?php

namespace Tests\Feature;

use App\Http\Controllers\OmDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class DataHubOverviewCacheTest extends TestCase
{
    public function test_overview_sections_return_cached_payloads_without_bigquery(): void
    {
        config(['cache.default' => 'array']);
        $controller = new OmDashboardController;
        $key = new ReflectionMethod($controller, 'overviewCacheKey');
        $payloads = [
            'traffic' => ['overviewTraffic', ['trafficSummary' => ['head_count' => 1234]]],
            'chart' => ['overviewChart', ['trafficPattern' => [], 'parkingSummary' => ['car_count' => 42], 'valetSummary' => [], 'valetTrend' => []]],
            'issue' => ['overviewIssue', ['issueSummary' => ['total_issue' => 1], 'issueRiskMonitor' => []]],
            'tenancy' => ['overviewDetail', ['fitOutList' => [], 'tenantMovement' => [], 'eventPipeline' => [['event' => 'Expo']]]],
            'budget' => ['overviewBudget', ['budgetSpending' => [['department' => 'Engineering']]]],
            'work' => ['overviewWorkDetail', ['workDetailDaily' => ['all' => [], 'departments' => []]]],
        ];
        $request = Request::create('/', 'GET', ['start_date' => '2026-10-01', 'end_date' => '2026-10-06']);
        foreach ($payloads as $section => [$method, $payload]) {
            Cache::put($key->invoke($controller, $section, 'GC', '2026-10-01', '2026-10-06', $section === 'chart' ? 'monthly' : 'all'), $payload, 3600);
            $this->assertSame($payload, $controller->{$method}($request, 'GC')->getData(true));
        }
        $this->assertNotSame(
            $key->invoke($controller, 'chart', 'GC', '2026-10-01', '2026-10-06', 'monthly'),
            $key->invoke($controller, 'chart', 'GC', '2026-10-01', '2026-10-06', 'daily')
        );
        $this->assertNotSame(
            $key->invoke($controller, 'tenancy', 'GC', '2026-10-01', '2026-10-06'),
            $key->invoke($controller, 'tenancy', 'KK', '2026-10-01', '2026-10-06')
        );
        $this->assertNotSame(
            $key->invoke($controller, 'tenancy', 'GC', '2026-10-01', '2026-10-06'),
            $key->invoke($controller, 'tenancy', 'GC', '2026-10-01', '2026-10-06', 'pullout')
        );
    }
}
