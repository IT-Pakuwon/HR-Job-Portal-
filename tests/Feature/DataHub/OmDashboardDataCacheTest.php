<?php

namespace Tests\Feature\DataHub;

use App\Services\DataHub\Om\OmDashboardData;
use App\Services\DataHub\Om\OmFilters;
use App\Services\DataHub\Om\Queries\BudgetQueries;
use App\Services\DataHub\Om\Queries\DepartmentQueries;
use App\Services\DataHub\Om\Queries\IssueQueries;
use App\Services\DataHub\Om\Queries\KaizenQueries;
use App\Services\DataHub\Om\Queries\TenancyQueries;
use App\Services\DataHub\Om\Queries\TrafficQueries;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class OmDashboardDataCacheTest extends TestCase
{
    /** Query classes that fail the test if anything reaches BigQuery. */
    private function data(): OmDashboardData
    {
        return new OmDashboardData(
            Mockery::mock(TrafficQueries::class),
            Mockery::mock(IssueQueries::class),
            Mockery::mock(TenancyQueries::class),
            Mockery::mock(BudgetQueries::class),
            Mockery::mock(DepartmentQueries::class),
            Mockery::mock(KaizenQueries::class),
        );
    }

    /** Same formula as OmDashboardData::cacheKey (the page and the snapshot command share these keys). */
    private function key(string $section, OmFilters $f, string $variant): string
    {
        return 'om_overview_v1_'.sha1(json_encode([$section, $f->mall, $f->startDate, $f->endDate, $variant]));
    }

    public function test_overview_sections_come_from_the_cache_without_touching_bigquery(): void
    {
        config(['cache.default' => 'array']);
        $f = new OmFilters('GC', '2026-10-01', '2026-10-06', 'weekly', 'new');
        $data = $this->data();

        $cases = [
            ['trafficSection', 'traffic', 'all', ['trafficSummary' => ['head_count' => 1234]]],
            ['chartSection', 'chart', 'weekly', ['trafficPattern' => [], 'parkingSummary' => ['car_count' => 42], 'valetSummary' => [], 'valetTrend' => []]],
            ['issueSection', 'issue', 'all', ['issueSummary' => ['total_issue' => 1], 'issueRiskMonitor' => []]],
            ['tenancySection', 'tenancy', 'new', ['fitOutList' => [], 'tenantMovement' => [], 'eventPipeline' => [['event' => 'Expo']]]],
            ['budgetSection', 'budget', 'all', ['budgetSpending' => [['department' => 'Engineering']]]],
            ['workSection', 'work', 'all', ['workDetailDaily' => ['all' => [], 'departments' => []]]],
        ];

        foreach ($cases as [$method, $section, $variant, $payload]) {
            Cache::put($this->key($section, $f, $variant), $payload, 3600);
            $this->assertSame($payload, $data->{$method}($f), $method);
        }
    }

    public function test_initial_returns_only_what_is_already_cached(): void
    {
        config(['cache.default' => 'array']);
        $f = new OmFilters('KK', '2026-10-01', '2026-10-06');
        Cache::put($this->key('traffic', $f, 'all'), ['trafficSummary' => ['head_count' => 7]], 3600);

        $this->assertSame(['traffic' => ['trafficSummary' => ['head_count' => 7]]], $this->data()->initial($f));
    }

    public function test_cache_keys_separate_malls_dates_and_variants(): void
    {
        config(['cache.default' => 'array']);
        $gc = new OmFilters('GC', '2026-10-01', '2026-10-06');
        $kk = new OmFilters('KK', '2026-10-01', '2026-10-06');
        $later = new OmFilters('GC', '2026-10-02', '2026-10-06');

        Cache::put($this->key('tenancy', $gc, 'all'), ['fitOutList' => ['gc']], 3600);

        $this->assertSame(['tenancy' => ['fitOutList' => ['gc']]], $this->data()->initial($gc));
        $this->assertSame([], $this->data()->initial($kk), 'another mall must not see it');
        $this->assertSame([], $this->data()->initial($later), 'another date range must not see it');
        $this->assertSame([], $this->data()->initial(new OmFilters('GC', '2026-10-01', '2026-10-06', 'monthly', 'pullout')), 'another variant must not see it');
    }
}
