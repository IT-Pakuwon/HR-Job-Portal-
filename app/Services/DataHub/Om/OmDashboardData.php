<?php

namespace App\Services\DataHub\Om;

use App\Services\DataHub\Om\Queries\BudgetQueries;
use App\Services\DataHub\Om\Queries\DepartmentQueries;
use App\Services\DataHub\Om\Queries\IssueQueries;
use App\Services\DataHub\Om\Queries\KaizenQueries;
use App\Services\DataHub\Om\Queries\TenancyQueries;
use App\Services\DataHub\Om\Queries\TrafficQueries;
use Illuminate\Support\Facades\Cache;

/**
 * One method per OM Dashboard AJAX section. Overview sections are cached for an hour
 * (BigQuery is slow) and the page itself pre-fills from that same cache via initial().
 */
class OmDashboardData
{
    private const OVERVIEW_TTL = 3600;

    public function __construct(
        private TrafficQueries $traffic,
        private IssueQueries $issue,
        private TenancyQueries $tenancy,
        private BudgetQueries $budget,
        private DepartmentQueries $department,
        private KaizenQueries $kaizen,
    ) {}

    // ── Overview tab ────────────────────────────────────────────────

    public function trafficSection(OmFilters $f): array
    {
        return $this->remember('traffic', $f, 'all', fn () => [
            'trafficSummary' => array_merge($this->traffic->getTrafficSummary($f->mall, $f->startDate, $f->endDate), [
                'drop_off_count' => 0,
                'drop_off_peak_hour' => '-',
                'drop_off_lw_percent' => 0,
                'drop_off_lm_percent' => 0,
                'drop_off_ly_percent' => 0,
            ]),
        ]);
    }

    public function issueSection(OmFilters $f): array
    {
        return $this->remember('issue', $f, 'all', fn () => [
            'issueSummary' => $this->issue->getIssueSummary($f->mall, $f->startDate, $f->endDate),
            'issueRiskMonitor' => $this->issue->getIssueRiskMonitor($f->mall, $f->startDate, $f->endDate),
        ]);
    }

    public function chartSection(OmFilters $f): array
    {
        return $this->remember('chart', $f, $f->granularity, fn () => $this->traffic->overviewChart($f->mall, $f->startDate, $f->endDate, $f->granularity));
    }

    public function tenancySection(OmFilters $f): array
    {
        return $this->remember('tenancy', $f, $f->movementType, function () use ($f) {
            $fitOutAndTenantMovement = $this->tenancy->getFitOutAndTenantMovement($f->mall, $f->startDate, $f->endDate, $f->movementType);

            return [
                'fitOutList' => $fitOutAndTenantMovement['fitOutList'],
                'tenantMovement' => $fitOutAndTenantMovement['tenantMovement'],
                'eventPipeline' => $this->tenancy->getEventPipeline($f->mall, $f->startDate, $f->endDate),
            ];
        });
    }

    public function budgetSection(OmFilters $f): array
    {
        return $this->remember('budget', $f, 'all', fn () => [
            'budgetSpending' => $this->budget->getBudgetSpending($f->mall, $f->startDate, $f->endDate),
        ]);
    }

    public function workSection(OmFilters $f): array
    {
        return $this->remember('work', $f, 'all', fn () => [
            'workDetailDaily' => $this->budget->getWorkDetailDaily($f->mall, $f->startDate, $f->endDate),
        ]);
    }

    // ── Department tab (not cached, as before) ──────────────────────

    public function departmentMain(OmFilters $f): array
    {
        $rows = $this->department->getDepartmentRows(['ManpowerFulfillment', 'IncidentByDepartment'], $f->mall, $f->startDate, $f->endDate);

        return [
            'manpowerFulfillment' => $this->department->getManpowerFulfillment($f->mall, $f->startDate, $f->endDate, $rows['ManpowerFulfillment']),
            'incidentByDepartment' => $this->department->getIncidentByDepartment($f->mall, $f->startDate, $f->endDate, $rows['IncidentByDepartment']),
        ];
    }

    public function departmentEquipment(OmFilters $f): array
    {
        $rows = $this->department->getDepartmentRows(['EquipmentStatus', 'EquipmentOpenClose'], $f->mall, $f->startDate, $f->endDate);

        return [
            'equipmentStatus' => $this->department->getEquipmentStatus($f->mall, $f->startDate, $f->endDate, $rows['EquipmentStatus']),
            'equipmentOpenClose' => $this->department->getEquipmentOpenClose($f->mall, $f->startDate, $f->endDate, $rows['EquipmentOpenClose']),
        ];
    }

    public function departmentInventory(OmFilters $f): array
    {
        $rows = $this->department->getDepartmentRows(['ConsumableUsage', 'AssetInventory', 'TenantUpdate'], $f->mall, $f->startDate, $f->endDate);

        return [
            'consumableUsage' => $this->department->getConsumableUsage($f->mall, $f->startDate, $f->endDate, $rows['ConsumableUsage']),
            'assetInventory' => $this->department->getAssetInventory($f->mall, $f->startDate, $f->endDate, $rows['AssetInventory']),
            'tenantUpdate' => $this->department->getTenantUpdate($f->mall, $f->startDate, $f->endDate, $rows['TenantUpdate']),
        ];
    }

    /**
     * The whole Department Report in one BigQuery job. Separate requests were queued by
     * PHP's local dev server, leaving the lower widgets blank while the first one ran.
     */
    public function departmentAll(OmFilters $f): array
    {
        $rows = $this->department->getDepartmentRows([
            'ManpowerFulfillment',
            'IncidentByDepartment',
            'EquipmentStatus',
            'EquipmentOpenClose',
            'ConsumableUsage',
            'AssetInventory',
            'TenantUpdate',
        ], $f->mall, $f->startDate, $f->endDate);

        return [
            'manpowerFulfillment' => $this->department->getManpowerFulfillment($f->mall, $f->startDate, $f->endDate, $rows['ManpowerFulfillment']),
            'incidentByDepartment' => $this->department->getIncidentByDepartment($f->mall, $f->startDate, $f->endDate, $rows['IncidentByDepartment']),
            'equipmentStatus' => $this->department->getEquipmentStatus($f->mall, $f->startDate, $f->endDate, $rows['EquipmentStatus']),
            'equipmentOpenClose' => $this->department->getEquipmentOpenClose($f->mall, $f->startDate, $f->endDate, $rows['EquipmentOpenClose']),
            'consumableUsage' => $this->department->getConsumableUsage($f->mall, $f->startDate, $f->endDate, $rows['ConsumableUsage']),
            'assetInventory' => $this->department->getAssetInventory($f->mall, $f->startDate, $f->endDate, $rows['AssetInventory']),
            'tenantUpdate' => $this->department->getTenantUpdate($f->mall, $f->startDate, $f->endDate, $rows['TenantUpdate']),
        ];
    }

    // ── Kaizen tab ──────────────────────────────────────────────────

    public function kaizenSection(OmFilters $f): array
    {
        $rows = $this->kaizen->getKaizenRows($f->mall, $f->startDate, $f->endDate);

        return [
            'kaizenSummary' => $this->kaizen->getKaizenSummary($rows['getKaizenSummary']),
            'kaizenPeakHour' => $this->kaizen->getKaizenPeakHour($rows['getKaizenPeakHour']),
            'kaizenHotspot' => $this->kaizen->getKaizenHotspot($rows['getKaizenHotspot']),
            'kaizenDurationDept' => $this->kaizen->getKaizenDurationDept($rows['getKaizenDurationDept']),
            'kaizenOverdueDept' => $this->kaizen->getKaizenOverdueDept($rows['getKaizenOverdueDept']),
            'kaizenCasePerStaff' => $this->kaizen->getKaizenCasePerStaff($rows['getKaizenCasePerStaff']),
            'kaizenDurationPerStaff' => $this->kaizen->getKaizenDurationPerStaff($rows['getKaizenDurationPerStaff']),
            'kaizenRepeatIncident' => $this->kaizen->getKaizenRepeatIncident($rows['getKaizenRepeatIncident']),
            'kaizenDurationItem' => $this->kaizen->getKaizenDurationItem($rows['getKaizenDurationItem']),
            'kaizenOverdueCases' => $this->kaizen->getKaizenOverdueCases($rows['getKaizenOverdueCases']),
        ];
    }

    // ── Page pre-fill ───────────────────────────────────────────────

    /**
     * Whatever the overview sections already have cached, keyed by section name. The page
     * renders these straight away and the AJAX calls fill in whatever is missing.
     */
    public function initial(OmFilters $f): array
    {
        $variants = ['chart' => $f->granularity, 'tenancy' => $f->movementType];
        $initial = [];

        foreach (['traffic', 'chart', 'issue', 'tenancy', 'budget', 'work'] as $section) {
            $payload = Cache::get($this->cacheKey($section, $f, $variants[$section] ?? 'all'));
            if ($payload !== null) {
                $initial[$section] = $payload;
            }
        }

        return $initial;
    }

    private function remember(string $section, OmFilters $f, string $variant, callable $callback): array
    {
        return Cache::remember($this->cacheKey($section, $f, $variant), self::OVERVIEW_TTL, $callback);
    }

    private function cacheKey(string $section, OmFilters $f, string $variant): string
    {
        return 'om_overview_v1_'.sha1(json_encode([$section, $f->mall, $f->startDate, $f->endDate, $variant]));
    }
}
