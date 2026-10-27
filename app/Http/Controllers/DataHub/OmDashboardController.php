<?php

namespace App\Http\Controllers\DataHub;

use App\Http\Controllers\Controller;
use App\Services\DataHub\Om\OmDashboardData;
use App\Services\DataHub\Om\OmFilters;
use App\Services\DataHub\Om\OmMalls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OM Dashboard: the page shell plus one JSON endpoint per section. Access (mall/company)
 * is enforced in OmFilters::fromRequest -> OmMalls::resolve; the queries live in
 * App\Services\DataHub\Om\Queries.
 */
class OmDashboardController extends Controller
{
    public function __construct(
        private OmMalls $malls,
        private OmDashboardData $data,
    ) {}

    public function index(Request $request)
    {
        // The address bar stays a plain /om-dashboard. Old links that still carry ?mall=&tab=… are
        // absorbed into the session, then redirected to the clean URL.
        if ($request->query()) {
            OmFilters::remember($request, OmFilters::sanitize($request->query(), $this->malls));

            return redirect()->route('datahub-dashboard.om.index');
        }

        $f = OmFilters::fromSession($request, $this->malls);
        $initial = $this->data->initial($f);
        $onOverview = $f->tab === 'overview';

        return view('pages.datahub_dashboard.om.detail', [
            'overviewInitial' => $initial,
            'mall' => $f->mall,
            'mallName' => $this->malls->names()[$f->mall],
            'malls' => $this->malls->options(),
            'canSwitchAllMalls' => $this->malls->canSwitchAll(),
            'startDate' => $f->startDate,
            'endDate' => $f->endDate,
            'activeTab' => $f->tab,
            'trafficGranularity' => $f->granularity,
            'tenantMovementType' => $f->movementType,

            'trafficSummary' => $initial['traffic']['trafficSummary'] ?? [],
            'trafficPattern' => $initial['chart']['trafficPattern'] ?? [],
            'parkingSummary' => $initial['chart']['parkingSummary'] ?? [],
            'valetSummary' => $initial['chart']['valetSummary'] ?? [],
            'valetTrend' => $initial['chart']['valetTrend'] ?? [],
            'issueSummary' => $initial['issue']['issueSummary'] ?? [],
            'issueRiskMonitor' => $initial['issue']['issueRiskMonitor'] ?? [],
            'fitOutList' => $onOverview ? ($initial['tenancy']['fitOutList'] ?? []) : [],
            'tenantMovement' => $onOverview ? ($initial['tenancy']['tenantMovement'] ?? []) : [],
            'eventPipeline' => $onOverview ? ($initial['tenancy']['eventPipeline'] ?? []) : [],
            'budgetSpending' => $onOverview ? ($initial['budget']['budgetSpending'] ?? []) : [],
            'workDetailDaily' => $onOverview ? ($initial['work']['workDetailDaily'] ?? ['all' => [], 'departments' => []]) : ['all' => [], 'departments' => []],

            // Department + Kaizen tabs are filled by their AJAX endpoints; these are the empty shells the view expects.
            'tenantUpdate' => [],
            'consumableUsage' => [],
            'equipmentOpenClose' => [],
            'equipmentStatus' => ['total_monitored' => 0, 'below_normal' => 0, 'normal' => 0, 'latest_date' => null, 'items' => []],
            'assetInventory' => ['total_good' => 0, 'total_bad' => 0, 'total_qty' => 0, 'latest_date' => null, 'departments' => [], 'items' => []],
            'manpowerFulfillment' => ['overall_percentage' => 0, 'total_schedule' => 0, 'total_actual' => 0, 'lowest_department' => '-', 'lowest_percentage' => 0, 'departments' => []],
            'incidentByDepartment' => ['departments' => [], 'total_incident' => 0, 'highest_department' => '-', 'highest_case' => 0],
            'kaizenSummary' => [],
            'kaizenPeakHour' => [],
            'kaizenHotspot' => [],
            'kaizenDurationDept' => [],
            'kaizenOverdueDept' => [],
            'kaizenCasePerStaff' => [],
            'kaizenDurationPerStaff' => [],
            'kaizenRepeatIncident' => [],
            'kaizenDurationItem' => [],
            'kaizenOverdueCases' => [],
        ]);
    }

    /** Saves a user's selection (company, dates, tab, toggles) to their session; the URL never shows it. */
    public function updateState(Request $request)
    {
        OmFilters::remember($request, OmFilters::sanitize($request->input(), $this->malls));

        return $request->expectsJson()
            ? response()->noContent()
            : redirect()->route('datahub-dashboard.om.index');
    }

    // ── Overview sections ───────────────────────────────────────────

    public function overviewTraffic(Request $request): JsonResponse
    {
        return response()->json($this->data->trafficSection($this->filters($request)));
    }

    public function overviewChart(Request $request): JsonResponse
    {
        return response()->json($this->data->chartSection($this->filters($request)));
    }

    public function overviewIssue(Request $request): JsonResponse
    {
        return response()->json($this->data->issueSection($this->filters($request)));
    }

    public function overviewDetail(Request $request): JsonResponse
    {
        return response()->json($this->data->tenancySection($this->filters($request)));
    }

    public function overviewBudget(Request $request): JsonResponse
    {
        return response()->json($this->data->budgetSection($this->filters($request)));
    }

    public function overviewWorkDetail(Request $request): JsonResponse
    {
        return response()->json($this->data->workSection($this->filters($request)));
    }

    // ── Department sections ─────────────────────────────────────────

    public function departmentAll(Request $request): JsonResponse
    {
        return response()->json($this->data->departmentAll($this->filters($request)));
    }

    public function departmentMain(Request $request): JsonResponse
    {
        return response()->json($this->data->departmentMain($this->filters($request)));
    }

    public function departmentEquipment(Request $request): JsonResponse
    {
        return response()->json($this->data->departmentEquipment($this->filters($request)));
    }

    public function departmentInventory(Request $request): JsonResponse
    {
        return response()->json($this->data->departmentInventory($this->filters($request)));
    }

    // ── Kaizen section ──────────────────────────────────────────────

    public function kaizenMain(Request $request): JsonResponse
    {
        return response()->json($this->data->kaizenSection($this->filters($request)));
    }

    private function filters(Request $request): OmFilters
    {
        return OmFilters::fromRequest($request, $this->malls);
    }
}
