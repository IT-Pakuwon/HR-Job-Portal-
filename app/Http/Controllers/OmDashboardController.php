<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\BigQueryService;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OmDashboardController extends Controller
{
    private array $mallNames = [
        'GC' => 'Gandaria City',
        'KK' => 'Kota Kasablanka',
        'PBM' => 'Plaza Blok M',
        'PMB' => 'Pakuwon Mall Bekasi',
    ];

    public function menu()
    {
        return view('pages.data_hub.index', [
            'malls' => $this->allowedMalls(),
        ]);
    }

    private function allowedMalls(): array
    {
        $companyByMall = ['GC' => 'AW', 'KK' => 'EP', 'PBM' => 'PSA', 'PMB' => 'GPS'];
        $companies = array_map('strtoupper', auth()->user()->scopedCompanyIds());

        return collect($this->mallNames)
            ->filter(fn ($name, $code) => in_array($companyByMall[$code], $companies, true))
            ->all();
    }

    private function abortIfNoOmMallAccess(string $mall): void
    {
        // Snapshot refresh runs only from Artisan; browser requests always use
        // the normal user/company access check below.
        if (app()->runningInConsole()) {
            return;
        }

        abort_unless(array_key_exists(strtoupper($mall), $this->allowedMalls()), 403);
    }

    private function getBudgetMallCode(string $mall): string
{
    return match ($mall) {
        'GC' => 'AW',
        'KK' => 'EP',
        'PBM' => 'PSA',
        'PMB' => 'GPS',
        default => $mall,
    };
}

private function budgetExcludedDepartmentsSql(): string
{
    $departments = [
        'LOYALTY',
        'WAREHOUSE',
        'HCGA',
        'LEGAL',
        'IT',
        'MANAGEMENT',
        'TREASURY',
        'FINANCE&ACCOUNTING',
        'PROJECT',
        'TAX',
        'FIXEDASSET',
        'PURCHASING',
        'LAND&PERMIT',
        'LAND & PERMIT',
        'LANDPERMIT',
    ];

    return "'" . implode("','", array_map(function ($department) {
        return str_replace("'", "\\'", $department);
    }, $departments)) . "'";
}

private function formatDepartmentName(string $department): string
{
    $raw = strtoupper(trim($department));

    return match ($raw) {
        'BUILDINGSERVICE', 'BUILDING SERVICE' => 'Building Svc',
        'CUSTOMERSERVICE', 'CUSTOMER SERVICE' => 'Customer Svc',
        'TENANTRELATION', 'TENANT RELATION' => 'Tenant Relation',
        'MANAGEMENT' => 'Management',
        'ENGINEERING' => 'Engineering',
        'HOUSEKEEPING' => 'Housekeeping',
        'SAFETY' => 'Safety',
        'PARKING' => 'Parking',
        'SECURITY' => 'Security',
        'IT' => 'IT',
        'HCGA' => 'HCGA',
        'TAX' => 'Tax',
        'WAREHOUSE' => 'Warehouse',
        default => ucwords(strtolower($department)),
    };
}

public function detail(Request $request, string $mall)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(120);
        $mall = strtoupper($mall);

        abort_if(!array_key_exists($mall, $this->mallNames), 404);

        $this->abortIfNoOmMallAccess($mall);

        $mallName = $this->mallNames[$mall];
        
        $defaultDate = now()->subDay()->toDateString();

        $startDate = $request->input('start_date', $defaultDate);
        $endDate = $request->input('end_date', $defaultDate);
        // $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        // $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $trafficGranularity = $request->input('traffic_granularity', 'monthly');
        if (!in_array($trafficGranularity, ['monthly', 'weekly', 'daily'])) {
            $trafficGranularity = 'monthly';
            }

        $activeTab = $request->input('tab', 'overview');
        if (!in_array($activeTab, ['overview', 'department', 'kaizen'])) {
        $activeTab = 'overview';
            }

        $tenantMovementType = $request->input('tenant_movement_type', 'all');

        if (!in_array($tenantMovementType, ['all', 'new', 'pullout'])) {
            $tenantMovementType = 'all';
            }   
            
        $trafficSummary = [];
        $trafficPattern = [];
        $parkingSummary = [];
        $valetSummary = [];
        $valetTrend = [];
        $issueSummary = [];
        $issueRiskMonitor = [];
        $fitOutList = [];
        $tenantMovement = [];
        $tenantUpdate = [];
        $eventPipeline = [];
        $budgetSpending = [];
        $workDetailDaily = [
                'all' => [],
                'departments' => [],
            ];

        $consumableUsage = [];
        $equipmentOpenClose = [];
        $equipmentStatus = [
                'total_monitored' => 0,
                'below_normal' => 0,
                'normal' => 0,
                'latest_date' => null,
                'items' => [],
            ];

        $assetInventory = [
                'total_good' => 0,
                'total_bad' => 0,
                'total_qty' => 0,
                'latest_date' => null,
                'departments' => [],
                'items' => [],
            ];

        $manpowerFulfillment = [
                'overall_percentage' => 0,
                'total_schedule' => 0,
                'total_actual' => 0,
                'lowest_department' => '-',
                'lowest_percentage' => 0,
                'departments' => [],
            ];

        $incidentByDepartment = [
                'departments' => [],
                'total_incident' => 0,
                'highest_department' => '-',
                'highest_case' => 0,
            ];

        $kaizenSummary = [];
        $kaizenPeakHour = [];
        $kaizenHotspot = [];
        $kaizenDurationDept = [];
        $kaizenOverdueDept = [];
        $kaizenCasePerStaff = [];
        $kaizenDurationPerStaff = [];
        $kaizenRepeatIncident = [];
        $kaizenDurationItem = [];
        $kaizenOverdueCases = [];
            
       if ($activeTab === 'overview') {
    //    $trafficSummary = $this->getTrafficSummary($mall, $startDate, $endDate);

    //    $dropOffSummary = $this->getDropOffSummary($mall, $startDate, $endDate);
    //    $trafficSummary = array_merge($trafficSummary, $dropOffSummary);

    //    $trafficPeaks = $this->getTrafficPeakHours($mall, $startDate, $endDate);
    //    $trafficSummary = array_merge($trafficSummary, $trafficPeaks);

    //    $trafficPattern = $this->getTrafficPattern($mall, $startDate, $endDate, $trafficGranularity);
    //    $parkingSummary = $this->getParkingSummary($mall, $startDate, $endDate);

    //    $valetSummary = $this->getValetSummary($mall, $startDate, $endDate);
    //    $valetTrend = $this->getValetTrend($mall, $startDate, $endDate);

    //    $issueSummary = $this->getIssueSummary($mall, $startDate, $endDate);
    //    $issueRiskMonitor = $this->getIssueRiskMonitor($mall, $startDate, $endDate);

    //    $fitOutList = $this->getFitOutList($mall, $startDate, $endDate);
    //    $tenantMovement = $this->getTenantMovement($mall, $startDate, $endDate, $tenantMovementType);
    //    $tenantUpdate = $this->getTenantUpdate($mall, $startDate, $endDate);
    //    $eventPipeline = $this->getEventPipeline($mall, $startDate, $endDate);

    //    $budgetSpending = $this->getBudgetSpending($mall, $startDate, $endDate);
    //   $workDetailDaily = $this->getWorkDetailDaily($mall, $startDate, $endDate);
     }

       if ($activeTab === 'department') {
   // Data Department Report diload via AJAX:
   // department-main
   // department-equipment
   // department-inventory

   // $manpowerFulfillment = $this->getManpowerFulfillment($mall, $startDate, $endDate);
   // $incidentByDepartment = $this->getIncidentByDepartment($mall, $startDate, $endDate);

   // $consumableUsage = $this->getConsumableUsage($mall, $startDate, $endDate);
   // $equipmentStatus = $this->getEquipmentStatus($mall, $startDate, $endDate);
   // $equipmentOpenClose = $this->getEquipmentOpenClose($mall, $startDate, $endDate);
   // $assetInventory = $this->getAssetInventory($mall, $startDate, $endDate);
}

       // Kaizen data is loaded by the kaizen-main AJAX endpoint.
        

        return view('dashboards.om-detail', [
            'mall' => $mall,
            'mallName' => $mallName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeTab' => $activeTab,
            'trafficSummary' => $trafficSummary,
            'trafficPattern' => $trafficPattern,
            'trafficGranularity' => $trafficGranularity,
            'parkingSummary' => $parkingSummary,
            'valetSummary' => $valetSummary,
            'valetTrend' => $valetTrend,
            'issueSummary' => $issueSummary,
            'issueRiskMonitor' => $issueRiskMonitor,
            'fitOutList' => $fitOutList,
            'tenantMovement' => $tenantMovement,
            'tenantUpdate' => $tenantUpdate,
            'eventPipeline' => $eventPipeline,
            'tenantMovementType' => $tenantMovementType,
            'budgetSpending' => $budgetSpending,
            'workDetailDaily' => $workDetailDaily,
            'consumableUsage' => $consumableUsage,
            'equipmentStatus' => $equipmentStatus,
            'equipmentOpenClose' => $equipmentOpenClose,
            'assetInventory' => $assetInventory,
            'kaizenSummary' => $kaizenSummary,
            'kaizenPeakHour' => $kaizenPeakHour,
            'kaizenHotspot' => $kaizenHotspot,
            'kaizenDurationDept' => $kaizenDurationDept,
            'kaizenOverdueDept' => $kaizenOverdueDept,
            'kaizenCasePerStaff' => $kaizenCasePerStaff,
            'kaizenDurationPerStaff' => $kaizenDurationPerStaff,
            'kaizenOverdueCases' => $kaizenOverdueCases,
            'kaizenRepeatIncident' => $kaizenRepeatIncident,
            'kaizenDurationItem' => $kaizenDurationItem,
            'manpowerFulfillment' => $manpowerFulfillment,
            'incidentByDepartment' => $incidentByDepartment,
            // 'kaizenDurationItem' => $kaizenDurationItem,
            // 'kaizenRepeatIncident' => $kaizenRepeatIncident,
            // 'kaizenOverdueCases' => $kaizenOverdueCases,
            'kaizenSummary' => $kaizenSummary,
        ]);
    }

    public function overviewTraffic(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $trafficGranularity = $request->input('traffic_granularity', 'monthly');

    if (!in_array($trafficGranularity, ['monthly', 'weekly', 'daily'])) {
        $trafficGranularity = 'monthly';
    }

    $trafficSummary = $this->getTrafficSummary($mall, $startDate, $endDate);

    $trafficSummary = array_merge($trafficSummary, [
    'drop_off_count' => 0,
    'drop_off_peak_hour' => '-',
    'drop_off_lw_percent' => 0,
    'drop_off_lm_percent' => 0,
    'drop_off_ly_percent' => 0,
]);

    // $dropOffSummary = $this->getDropOffSummary($mall, $startDate, $endDate);
    // $trafficSummary = array_merge($trafficSummary, $dropOffSummary);

    // $trafficPeaks = $this->getTrafficPeakHours($mall, $startDate, $endDate);
    // $trafficSummary = array_merge($trafficSummary, $trafficPeaks);

    // $trafficPattern = $this->getTrafficPattern($mall, $startDate, $endDate, $trafficGranularity);
    // $parkingSummary = $this->getParkingSummary($mall, $startDate, $endDate);

    // $valetSummary = $this->getValetSummary($mall, $startDate, $endDate);
    // $valetTrend = $this->getValetTrend($mall, $startDate, $endDate);

    return response()->json([
        'trafficSummary' => $trafficSummary,
        // 'trafficPattern' => $trafficPattern,
        // 'parkingSummary' => $parkingSummary,
        // 'valetSummary' => $valetSummary,
        // 'valetTrend' => $valetTrend,
    ]);
}

public function overviewIssue(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $issueSummary = $this->getIssueSummary($mall, $startDate, $endDate);
    $issueRiskMonitor = $this->getIssueRiskMonitor($mall, $startDate, $endDate);

    return response()->json([
        'issueSummary' => $issueSummary,
        'issueRiskMonitor' => $issueRiskMonitor,
    ]);
}

public function overviewChart(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $trafficGranularity = $request->input('traffic_granularity', 'monthly');

    if (!in_array($trafficGranularity, ['monthly', 'weekly', 'daily'])) {
        $trafficGranularity = 'monthly';
    }

    $sql = "
        SELECT
            summary_date,
            car_count,
            motorcycle_count,
            car_income,
            motorcycle_income,
            avg_parking_duration_minutes,
            peak_parking_hour,
            valet_served,
            valet_avg_wait_minutes,
            valet_income
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        ORDER BY summary_date
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $trafficGroups = [];
    $valetGroups = [];

    $parkingCarCount = 0;
    $parkingMotorCount = 0;
    $parkingDurationTotal = 0;
    $parkingDurationCount = 0;

    $peakCounts = [];

    $valetServed = 0;
    $valetWaitTotal = 0;
    $valetWaitCount = 0;
    $valetIncomeTotal = 0;

    foreach ($rows as $row) {
        $date = Carbon::parse($row['summary_date']);

        if ($trafficGranularity === 'daily') {
            $periodLabel = $date->format('d M');
            $periodKey = $date->format('Y-m-d');
        } elseif ($trafficGranularity === 'weekly') {
            $weekStart = $date->copy()->startOfWeek();
            $periodLabel = $weekStart->format('d M');
            $periodKey = $weekStart->format('Y-m-d');
        } else {
            $periodLabel = $date->format('M');
            $periodKey = $date->format('Y-m');
        }

        if (!isset($trafficGroups[$periodKey])) {
            $trafficGroups[$periodKey] = [
                'period_label' => $periodLabel,
                'period_key' => $periodKey,
                'car_count' => 0,
                'motorcycle_count' => 0,
                'car_income' => 0,
                'motorcycle_income' => 0,
                'total_vehicle_count' => 0,
                'total_income' => 0,
            ];
        }

        $carCount = $this->toFloat($row['car_count'] ?? 0);
        $motorCount = $this->toFloat($row['motorcycle_count'] ?? 0);
        $carIncome = $this->toFloat($row['car_income'] ?? 0);
        $motorIncome = $this->toFloat($row['motorcycle_income'] ?? 0);
        $valetIncome = $this->toFloat($row['valet_income'] ?? 0);

        $trafficGroups[$periodKey]['car_count'] += $carCount;
        $trafficGroups[$periodKey]['motorcycle_count'] += $motorCount;
        $trafficGroups[$periodKey]['car_income'] += $carIncome;
        $trafficGroups[$periodKey]['motorcycle_income'] += $motorIncome;
        $trafficGroups[$periodKey]['total_vehicle_count'] += $carCount + $motorCount;
        $trafficGroups[$periodKey]['total_income'] += $carIncome + $motorIncome;

        if (!isset($valetGroups[$periodKey])) {
            $valetGroups[$periodKey] = [
                'period_label' => $periodLabel,
                'period_key' => $periodKey,
                'valet_income' => 0,
            ];
        }

        $valetGroups[$periodKey]['valet_income'] += $valetIncome;

        $parkingCarCount += $carCount;
        $parkingMotorCount += $motorCount;

        $duration = $this->toFloat($row['avg_parking_duration_minutes'] ?? 0);

        if ($duration > 0) {
            $parkingDurationTotal += $duration;
            $parkingDurationCount++;
        }

        $peakHour = trim((string) ($row['peak_parking_hour'] ?? ''));

        if ($peakHour !== '') {
            if (!isset($peakCounts[$peakHour])) {
                $peakCounts[$peakHour] = 0;
            }

            $peakCounts[$peakHour]++;
        }

        $valetServed += $this->toFloat($row['valet_served'] ?? 0);

        $valetWait = $this->toFloat($row['valet_avg_wait_minutes'] ?? 0);

        if ($valetWait > 0) {
            $valetWaitTotal += $valetWait;
            $valetWaitCount++;
        }

        $valetIncomeTotal += $valetIncome;
    }

    ksort($trafficGroups);
    ksort($valetGroups);

    arsort($peakCounts);
    $peakParkingHour = array_key_first($peakCounts) ?? '-';

    $parkingSummary = [
        'car_count' => $parkingCarCount,
        'motorcycle_count' => $parkingMotorCount,
        'avg_parking_duration_minutes' => $parkingDurationCount > 0
            ? round($parkingDurationTotal / $parkingDurationCount, 2)
            : 0,
        'peak_parking_hour' => $peakParkingHour,
    ];

    $valetSummary = [
        'valet_served' => $valetServed,
        'valet_avg_wait_minutes' => $valetWaitCount > 0
            ? round($valetWaitTotal / $valetWaitCount, 2)
            : 0,
        'valet_income' => $valetIncomeTotal,
    ];

    return response()->json([
        'trafficPattern' => array_values($trafficGroups),
        'parkingSummary' => $parkingSummary,
        'valetSummary' => $valetSummary,
        'valetTrend' => array_values($valetGroups),
    ]);
}

    public function overviewDetail(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $trafficGranularity = $request->input('traffic_granularity', 'monthly');

    if (!in_array($trafficGranularity, ['monthly', 'weekly', 'daily'])) {
        $trafficGranularity = 'monthly';
    }

    $tenantMovementType = $request->input('tenant_movement_type', 'all');

    if (!in_array($tenantMovementType, ['all', 'new', 'pullout'])) {
        $tenantMovementType = 'all';
    }

    // $trafficSummary = $this->getTrafficSummary($mall, $startDate, $endDate);

    // $dropOffSummary = $this->getDropOffSummary($mall, $startDate, $endDate);
    // $trafficSummary = array_merge($trafficSummary, $dropOffSummary);

    // $trafficPeaks = $this->getTrafficPeakHours($mall, $startDate, $endDate);
    // $trafficSummary = array_merge($trafficSummary, $trafficPeaks);

    // $trafficPattern = $this->getTrafficPattern($mall, $startDate, $endDate, $trafficGranularity);
    // $parkingSummary = $this->getParkingSummary($mall, $startDate, $endDate);

    // $valetSummary = $this->getValetSummary($mall, $startDate, $endDate);
    // $valetTrend = $this->getValetTrend($mall, $startDate, $endDate);

    // $issueSummary = $this->getIssueSummary($mall, $startDate, $endDate);
    // $issueRiskMonitor = $this->getIssueRiskMonitor($mall, $startDate, $endDate);

    $fitOutAndTenantMovement = $this->getFitOutAndTenantMovement($mall, $startDate, $endDate, $tenantMovementType);
    $tenantUpdate = $this->getTenantUpdate($mall, $startDate, $endDate);
    $eventPipeline = $this->getEventPipeline($mall, $startDate, $endDate);

    return response()->json([
        // 'trafficSummary' => $trafficSummary,
        // 'trafficPattern' => $trafficPattern,
        // 'parkingSummary' => $parkingSummary,
        // 'valetSummary' => $valetSummary,
        // 'valetTrend' => $valetTrend,
        // 'issueSummary' => $issueSummary,
        // 'issueRiskMonitor' => $issueRiskMonitor,
        'fitOutList' => $fitOutAndTenantMovement['fitOutList'],
        'tenantMovement' => $fitOutAndTenantMovement['tenantMovement'],
        'tenantUpdate' => $tenantUpdate,
        'eventPipeline' => $eventPipeline,
    ]);
}

    public function overviewBudget(Request $request, string $mall)
    {
        $mall = strtoupper($mall);
        abort_if(!array_key_exists($mall, $this->mallNames), 404);
        $this->abortIfNoOmMallAccess($mall);

        $defaultDate = now()->subDay()->toDateString();

        return response()->json([
            'budgetSpending' => $this->getBudgetSpending(
                $mall,
                $request->input('start_date', $defaultDate),
                $request->input('end_date', $defaultDate),
            ),
        ]);
    }

    public function overviewWorkDetail(Request $request, string $mall)
    {
        $mall = strtoupper($mall);
        abort_if(!array_key_exists($mall, $this->mallNames), 404);
        $this->abortIfNoOmMallAccess($mall);

        $defaultDate = now()->subDay()->toDateString();

        return response()->json([
            'workDetailDaily' => $this->getWorkDetailDaily(
                $mall,
                $request->input('start_date', $defaultDate),
                $request->input('end_date', $defaultDate),
            ),
        ]);
    }

public function departmentMain(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $rows = $this->getDepartmentRows(['ManpowerFulfillment', 'IncidentByDepartment'], $mall, $startDate, $endDate);

    $manpowerFulfillment = $this->getManpowerFulfillment($mall, $startDate, $endDate, $rows['ManpowerFulfillment']);
    $incidentByDepartment = $this->getIncidentByDepartment($mall, $startDate, $endDate, $rows['IncidentByDepartment']);

    return response()->json([
        'manpowerFulfillment' => $manpowerFulfillment,
        'incidentByDepartment' => $incidentByDepartment,
    ]);
}

/**
 * Loads the Department Report in one BigQuery job.  The previous screen made
 * three HTTP requests which PHP's local development server handled in a
 * queue, leaving the lower widgets blank while the first request finished.
 */
public function departmentAll(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();
    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $rows = $this->getDepartmentRows([
        'ManpowerFulfillment',
        'IncidentByDepartment',
        'EquipmentStatus',
        'EquipmentOpenClose',
        'ConsumableUsage',
        'AssetInventory',
        'TenantUpdate',
    ], $mall, $startDate, $endDate);

    return response()->json([
        'manpowerFulfillment' => $this->getManpowerFulfillment($mall, $startDate, $endDate, $rows['ManpowerFulfillment']),
        'incidentByDepartment' => $this->getIncidentByDepartment($mall, $startDate, $endDate, $rows['IncidentByDepartment']),
        'equipmentStatus' => $this->getEquipmentStatus($mall, $startDate, $endDate, $rows['EquipmentStatus']),
        'equipmentOpenClose' => $this->getEquipmentOpenClose($mall, $startDate, $endDate, $rows['EquipmentOpenClose']),
        'consumableUsage' => $this->getConsumableUsage($mall, $startDate, $endDate, $rows['ConsumableUsage']),
        'assetInventory' => $this->getAssetInventory($mall, $startDate, $endDate, $rows['AssetInventory']),
        'tenantUpdate' => $this->getTenantUpdate($mall, $startDate, $endDate, $rows['TenantUpdate']),
    ]);
}

public function departmentEquipment(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $rows = $this->getDepartmentRows(['EquipmentStatus', 'EquipmentOpenClose'], $mall, $startDate, $endDate);

    $equipmentStatus = $this->getEquipmentStatus($mall, $startDate, $endDate, $rows['EquipmentStatus']);
    $equipmentOpenClose = $this->getEquipmentOpenClose($mall, $startDate, $endDate, $rows['EquipmentOpenClose']);

    return response()->json([
        'equipmentStatus' => $equipmentStatus,
        'equipmentOpenClose' => $equipmentOpenClose,
    ]);
}

public function departmentInventory(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $rows = $this->getDepartmentRows(['ConsumableUsage', 'AssetInventory', 'TenantUpdate'], $mall, $startDate, $endDate);

    $consumableUsage = $this->getConsumableUsage($mall, $startDate, $endDate, $rows['ConsumableUsage']);
    $assetInventory = $this->getAssetInventory($mall, $startDate, $endDate, $rows['AssetInventory']);
    $tenantUpdate = $this->getTenantUpdate($mall, $startDate, $endDate, $rows['TenantUpdate']);

    return response()->json([
        'consumableUsage' => $consumableUsage,
        'assetInventory' => $assetInventory,
        'tenantUpdate' => $tenantUpdate,
    ]);
}

public function kaizenMain(Request $request, string $mall)
{
    $mall = strtoupper($mall);

    abort_if(!array_key_exists($mall, $this->mallNames), 404);
    $this->abortIfNoOmMallAccess($mall);

    $defaultDate = now()->subDay()->toDateString();

    $startDate = $request->input('start_date', $defaultDate);
    $endDate = $request->input('end_date', $defaultDate);

    $rows = $this->getKaizenRows($mall, $startDate, $endDate);

    $kaizenSummary = $this->getKaizenSummary($rows['getKaizenSummary']);
    $kaizenPeakHour = $this->getKaizenPeakHour($rows['getKaizenPeakHour']);
    $kaizenHotspot = $this->getKaizenHotspot($rows['getKaizenHotspot']);
    $kaizenDurationDept = $this->getKaizenDurationDept($rows['getKaizenDurationDept']);
    $kaizenOverdueDept = $this->getKaizenOverdueDept($rows['getKaizenOverdueDept']);
    $kaizenCasePerStaff = $this->getKaizenCasePerStaff($rows['getKaizenCasePerStaff']);
    $kaizenDurationPerStaff = $this->getKaizenDurationPerStaff($rows['getKaizenDurationPerStaff']);
    $kaizenRepeatIncident = $this->getKaizenRepeatIncident($rows['getKaizenRepeatIncident']);
    $kaizenDurationItem = $this->getKaizenDurationItem($rows['getKaizenDurationItem']);
    $kaizenOverdueCases = $this->getKaizenOverdueCases($rows['getKaizenOverdueCases']);

    return response()->json([
        'kaizenSummary' => $kaizenSummary,
        'kaizenPeakHour' => $kaizenPeakHour,
        'kaizenHotspot' => $kaizenHotspot,
        'kaizenDurationDept' => $kaizenDurationDept,
        'kaizenOverdueDept' => $kaizenOverdueDept,
        'kaizenCasePerStaff' => $kaizenCasePerStaff,
        'kaizenDurationPerStaff' => $kaizenDurationPerStaff,
        'kaizenRepeatIncident' => $kaizenRepeatIncident,
        'kaizenDurationItem' => $kaizenDurationItem,
        'kaizenOverdueCases' => $kaizenOverdueCases,
    ]);
}

    private function bigQuery(): BigQueryClient
{
    return app(BigQueryService::class)->getClient();
}

private function runQuery(string $sql, array $params = []): array
{
    $cacheKey = 'om_bq_' . sha1($sql . '|' . json_encode($params));

    return Cache::remember($cacheKey, 86400, function () use ($sql, $params) {
        $attempts = 3;
        $lastException = null;

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $bigQuery = $this->bigQuery();

                $query = $bigQuery->query($sql);

                if (!empty($params)) {
                    $query->parameters($params);
                }

                $startTime = microtime(true);

                $rows = iterator_to_array($bigQuery->runQuery($query));

                $duration = round(microtime(true) - $startTime, 2);

                \Log::info('OM BigQuery duration', [
                    'duration_seconds' => $duration,
                    'params' => $params,
                    'sql_preview' => substr(preg_replace('/\s+/', ' ', trim($sql)), 0, 180),
                ]);

                return array_map(function ($row) {
                    return $this->normalizeBigQueryValue($row);
                }, $rows);

            } catch (\Throwable $e) {
                $lastException = $e;

                $message = $e->getMessage();

                $canRetry =
                    str_contains($message, 'cURL error 56') ||
                    str_contains($message, 'SSL_read') ||
                    str_contains($message, 'unexpected eof') ||
                    str_contains($message, 'Maximum execution time') ||
                    str_contains($message, 'timed out') ||
                    str_contains($message, 'ServiceException');

                if (!$canRetry || $i === $attempts) {
                    throw $e;
                }

                sleep(1);
            }
        }

        throw $lastException;
    });
}

  private function getTrafficSummary(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        WITH ranges AS (
            SELECT 'current' AS period, DATE(@start_date) AS start_date, DATE(@end_date) AS end_date

            UNION ALL
            SELECT
                'lw',
                DATE_SUB(DATE(@start_date), INTERVAL DATE_DIFF(DATE(@end_date), DATE(@start_date), DAY) + 1 DAY),
                DATE_SUB(DATE(@start_date), INTERVAL 1 DAY)

            UNION ALL
            SELECT
                'lm',
                DATE_SUB(DATE(@start_date), INTERVAL 1 MONTH),
                DATE_SUB(DATE(@end_date), INTERVAL 1 MONTH)

            UNION ALL
            SELECT
                'ly',
                DATE_SUB(DATE(@start_date), INTERVAL 1 YEAR),
                DATE_SUB(DATE(@end_date), INTERVAL 1 YEAR)
        ),

        base AS (
            SELECT
                r.period,
                d.summary_date,
                d.head_count,
                d.car_count,
                d.motorcycle_count,
                d.taxi_count,
                d.head_peak_hour,
                d.taxi_peak_hour,
                d.peak_parking_hour
            FROM ranges r
            LEFT JOIN `ifca-pkwjakarta.dashboard_summary.gm_operation_daily` d
              ON d.mall = @mall
             AND d.summary_date BETWEEN r.start_date AND r.end_date
        ),

        totals AS (
            SELECT
                period,
                SUM(head_count) AS head_count,
                SUM(car_count) AS car_count,
                SUM(motorcycle_count) AS motorcycle_count,
                SUM(taxi_count) AS taxi_count
            FROM base
            GROUP BY period
        ),

        head_peak AS (
            SELECT head_peak_hour
            FROM base
            WHERE period = 'current'
              AND head_peak_hour IS NOT NULL
              AND head_peak_hour != ''
            GROUP BY head_peak_hour
            ORDER BY COUNT(*) DESC, head_peak_hour ASC
            LIMIT 1
        ),

        taxi_peak AS (
            SELECT taxi_peak_hour
            FROM base
            WHERE period = 'current'
              AND taxi_peak_hour IS NOT NULL
              AND taxi_peak_hour != ''
            GROUP BY taxi_peak_hour
            ORDER BY COUNT(*) DESC, taxi_peak_hour ASC
            LIMIT 1
        ),

        parking_peak AS (
            SELECT peak_parking_hour
            FROM base
            WHERE period = 'current'
              AND peak_parking_hour IS NOT NULL
              AND peak_parking_hour != ''
            GROUP BY peak_parking_hour
            ORDER BY COUNT(*) DESC, peak_parking_hour ASC
            LIMIT 1
        )

        SELECT
            COALESCE(MAX(IF(period = 'current', head_count, NULL)), 0) AS head_count,
            COALESCE(MAX(IF(period = 'current', car_count, NULL)), 0) AS car_count,
            COALESCE(MAX(IF(period = 'current', motorcycle_count, NULL)), 0) AS motorcycle_count,
            COALESCE(MAX(IF(period = 'current', taxi_count, NULL)), 0) AS taxi_count,

            COALESCE(MAX(IF(period = 'lw', head_count, NULL)), 0) AS head_lw,
            COALESCE(MAX(IF(period = 'lm', head_count, NULL)), 0) AS head_lm,
            COALESCE(MAX(IF(period = 'ly', head_count, NULL)), 0) AS head_ly,

            COALESCE(MAX(IF(period = 'lw', car_count, NULL)), 0) AS car_lw,
            COALESCE(MAX(IF(period = 'lm', car_count, NULL)), 0) AS car_lm,
            COALESCE(MAX(IF(period = 'ly', car_count, NULL)), 0) AS car_ly,

            COALESCE(MAX(IF(period = 'lw', motorcycle_count, NULL)), 0) AS motor_lw,
            COALESCE(MAX(IF(period = 'lm', motorcycle_count, NULL)), 0) AS motor_lm,
            COALESCE(MAX(IF(period = 'ly', motorcycle_count, NULL)), 0) AS motor_ly,

            COALESCE(MAX(IF(period = 'lw', taxi_count, NULL)), 0) AS taxi_lw,
            COALESCE(MAX(IF(period = 'lm', taxi_count, NULL)), 0) AS taxi_lm,
            COALESCE(MAX(IF(period = 'ly', taxi_count, NULL)), 0) AS taxi_ly,

            (SELECT head_peak_hour FROM head_peak) AS head_peak_hour,
            (SELECT taxi_peak_hour FROM taxi_peak) AS taxi_peak_hour,
            (SELECT peak_parking_hour FROM parking_peak) AS peak_parking_hour
        FROM totals
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];

    $head = $this->toFloat($row['head_count'] ?? 0);
    $car = $this->toFloat($row['car_count'] ?? 0);
    $motor = $this->toFloat($row['motorcycle_count'] ?? 0);
    $taxi = $this->toFloat($row['taxi_count'] ?? 0);

    return [
        'head_count' => $head,
        'car_count' => $car,
        'motor_count' => $motor,

        'head_lw_percent' => $this->percentChange($head, $this->toFloat($row['head_lw'] ?? 0)),
        'head_lm_percent' => $this->percentChange($head, $this->toFloat($row['head_lm'] ?? 0)),
        'head_ly_percent' => $this->percentChange($head, $this->toFloat($row['head_ly'] ?? 0)),

        'car_lw_percent' => $this->percentChange($car, $this->toFloat($row['car_lw'] ?? 0)),
        'car_lm_percent' => $this->percentChange($car, $this->toFloat($row['car_lm'] ?? 0)),
        'car_ly_percent' => $this->percentChange($car, $this->toFloat($row['car_ly'] ?? 0)),

        'motor_lw_percent' => $this->percentChange($motor, $this->toFloat($row['motor_lw'] ?? 0)),
        'motor_lm_percent' => $this->percentChange($motor, $this->toFloat($row['motor_lm'] ?? 0)),
        'motor_ly_percent' => $this->percentChange($motor, $this->toFloat($row['motor_ly'] ?? 0)),

        'blue_bird' => $taxi,
        'blue_bird_lw_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_lw'] ?? 0)),
        'blue_bird_lm_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_lm'] ?? 0)),
        'blue_bird_ly_percent' => $this->percentChange($taxi, $this->toFloat($row['taxi_ly'] ?? 0)),

        'head_peak_hour' => $row['head_peak_hour'] ?? '-',
        'taxi_peak_hour' => $row['taxi_peak_hour'] ?? '-',
        'blue_bird_peak_hour' => $row['taxi_peak_hour'] ?? '-',
        'parking_peak_hour' => $row['peak_parking_hour'] ?? '-',

        'drop_off' => 0,
        'drop_off_lw_percent' => 0,
        'drop_off_lm_percent' => 0,
        'drop_off_ly_percent' => 0,
    ];
}

private function getTrafficPattern(string $mall, string $startDate, string $endDate, string $granularity = 'monthly'): array
{
    if ($granularity === 'weekly') {
        $periodSelect = "
            FORMAT_DATE('%d %b', DATE_TRUNC(summary_date, WEEK(MONDAY))) AS period_label,
            FORMAT_DATE('%Y-%m-%d', DATE_TRUNC(summary_date, WEEK(MONDAY))) AS period_key
        ";
        $groupBy = "period_label, period_key";
    } elseif ($granularity === 'daily') {
        $periodSelect = "
            FORMAT_DATE('%d %b', summary_date) AS period_label,
            FORMAT_DATE('%Y-%m-%d', summary_date) AS period_key
        ";
        $groupBy = "period_label, period_key";
    } else {
        $periodSelect = "
            FORMAT_DATE('%b', summary_date) AS period_label,
            FORMAT_DATE('%Y-%m', summary_date) AS period_key
        ";
        $groupBy = "period_label, period_key";
    }

    $sql = "
        SELECT
            {$periodSelect},

            SUM(car_count) AS car_count,
            SUM(motorcycle_count) AS motorcycle_count,

            SUM(car_income) AS car_income,
            SUM(motorcycle_income) AS motorcycle_income,

            SUM(car_count + motorcycle_count) AS total_vehicle_count,
            SUM(car_income + motorcycle_income) AS total_income
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY {$groupBy}
        ORDER BY period_key
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'period_label' => $row['period_label'] ?? '-',
            'period_key' => $row['period_key'] ?? '-',

            'car_count' => $this->toFloat($row['car_count'] ?? 0),
            'motorcycle_count' => $this->toFloat($row['motorcycle_count'] ?? 0),

            'car_income' => $this->toFloat($row['car_income'] ?? 0),
            'motorcycle_income' => $this->toFloat($row['motorcycle_income'] ?? 0),

            'total_vehicle_count' => $this->toFloat($row['total_vehicle_count'] ?? 0),
            'total_income' => $this->toFloat($row['total_income'] ?? 0),
        ];
    }, $rows);
}

private function getParkingSummary(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            SUM(car_count) AS car_count,
            SUM(motorcycle_count) AS motorcycle_count,
            CAST(ROUND(AVG(avg_parking_duration_minutes), 2) AS NUMERIC) AS avg_parking_duration_minutes,
            ARRAY_AGG(
                peak_parking_hour
                IGNORE NULLS
                ORDER BY total_vehicle DESC
                LIMIT 1
            )[SAFE_OFFSET(0)] AS peak_parking_hour
        FROM (
            SELECT
                summary_date,
                car_count,
                motorcycle_count,
                avg_parking_duration_minutes,
                peak_parking_hour,
                COALESCE(car_count, 0) + COALESCE(motorcycle_count, 0) AS total_vehicle
            FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
        )
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];

    return [
        'car_count' => $this->toFloat($row['car_count'] ?? 0),
        'motorcycle_count' => $this->toFloat($row['motorcycle_count'] ?? 0),
        'avg_parking_duration_minutes' => $this->toFloat($row['avg_parking_duration_minutes'] ?? 0),
        'peak_parking_hour' => $row['peak_parking_hour'] ?? '-',
    ];
}

private function getValetSummary(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            SUM(valet_served) AS valet_served,
            CAST(ROUND(AVG(valet_avg_wait_minutes), 2) AS NUMERIC) AS valet_avg_wait_minutes,
            SUM(valet_income) AS valet_income
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];

    return [
        'valet_served' => $this->toFloat($row['valet_served'] ?? 0),
        'valet_avg_wait_minutes' => $this->toFloat($row['valet_avg_wait_minutes'] ?? 0),
        'valet_income' => $this->toFloat($row['valet_income'] ?? 0),
    ];
}

private function getValetTrend(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            FORMAT_DATE('%b', summary_date) AS period_label,
            FORMAT_DATE('%Y-%m', summary_date) AS period_key,
            SUM(valet_income) AS valet_income
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY period_label, period_key
        ORDER BY period_key
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'period_label' => $row['period_label'] ?? '-',
            'period_key' => $row['period_key'] ?? '-',
            'valet_income' => $this->toFloat($row['valet_income'] ?? 0),
        ];
    }, $rows);
}

private function getIssueSummary(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            SUM(total_issue) AS total_issue,
            SUM(open_issue) AS open_issue,
            SUM(in_progress_issue) AS in_progress_issue,
            SUM(closed_issue) AS closed_issue,
            MAX(oldest_issue_days) AS oldest_issue_days,

            ARRAY_AGG(
                STRUCT(
                    top_issue_department,
                    top_issue_area,
                    top_issue_item,
                    total_issue
                )
                IGNORE NULLS
                ORDER BY total_issue DESC
                LIMIT 1
            )[SAFE_OFFSET(0)] AS top_issue
        FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];
    $topIssue = $row['top_issue'] ?? null;

    return [
        'total_issue' => $this->toFloat($row['total_issue'] ?? 0),
        'open_issue' => $this->toFloat($row['open_issue'] ?? 0),
        'in_progress_issue' => $this->toFloat($row['in_progress_issue'] ?? 0),
        'closed_issue' => $this->toFloat($row['closed_issue'] ?? 0),
        'oldest_issue_days' => $this->toFloat($row['oldest_issue_days'] ?? 0),

        'top_issue_department' => is_array($topIssue) ? ($topIssue['top_issue_department'] ?? '-') : '-',
        'top_issue_area' => is_array($topIssue) ? ($topIssue['top_issue_area'] ?? '-') : '-',
        'top_issue_item' => is_array($topIssue) ? ($topIssue['top_issue_item'] ?? '-') : '-',
    ];
}

private function getIssueRiskMonitor(string $mall, string $startDate, string $endDate): array
{
    $sql = "
    WITH base AS (
        SELECT
            kaizen_id,
            location_name AS area,
            area_name AS location,
            item AS issue,
            kaizen_department AS department,
            CAST(status AS STRING) AS status,
            issue_date,
            solved_date,
            DATE_DIFF(
                COALESCE(DATE(solved_date), CURRENT_DATE('Asia/Jakarta')),
                DATE(issue_date),
                DAY
            ) AS days,
            ROW_NUMBER() OVER (
                PARTITION BY kaizen_id
                ORDER BY issue_date DESC
            ) AS rn
        FROM `ifca-pkwjakarta.isort.vw_detail_kaizen`
        WHERE site = @mall
          AND DATE(issue_date) BETWEEN @start_date AND @end_date
    )

    SELECT
        area,
        location,
        issue,
        department,
        status,
        days
    FROM base
    WHERE rn = 1
    ORDER BY days DESC
    LIMIT 10
";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
    return [
        'area' => $row['area'] ?? '-',
        'location' => $row['location'] ?? '-',
        'issue' => $row['issue'] ?? '-',
        'department' => $row['department'] ?? '-',
        'status' => $row['status'] ?? '-',
        'days' => $this->toFloat($row['days'] ?? 0),
    ];
}, $rows);
}

private function getFitOutList(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            tenant_name AS tenant,
            CAST(0 AS NUMERIC) AS area_sqm,
            CONCAT(
                COALESCE(CAST(floor AS STRING), '-'),
                CASE
                    WHEN unit_type IS NOT NULL AND CAST(unit_type AS STRING) <> '' THEN CONCAT(' ', CAST(unit_type AS STRING))
                    ELSE ''
                END
            ) AS location
        FROM `ifca-pkwjakarta.fitoutme.fitoutme_list_fitout_type_src`
        WHERE ho_date IS NOT NULL
          AND DATE(ho_date) BETWEEN @start_date AND @end_date
          AND (
            CASE
              WHEN mall = 'Gandaria City' THEN 'GC'
              WHEN mall = 'Kota Kasablanka' THEN 'KK'
              WHEN mall = 'Plaza Blok M' THEN 'PBM'
              WHEN mall = 'Pakuwon Mall Bekasi' THEN 'PMB'
              ELSE mall
            END
          ) = @mall
        ORDER BY DATE(ho_date) DESC, tenant_name
        LIMIT 10
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'tenant' => $row['tenant'] ?? '-',
            'area_sqm' => $this->toFloat($row['area_sqm'] ?? 0),
            'location' => $row['location'] ?? '-',
        ];
    }, $rows);
}

private function getTenantMovement(string $mall, string $startDate, string $endDate, string $movementType = 'all'): array
{
    $movementFilter = "";

    if ($movementType === 'new') {
        $movementFilter = "
            AND LOWER(COALESCE(fo_type, '')) LIKE '%new%'
        ";
    } elseif ($movementType === 'pullout') {
        $movementFilter = "
            AND (
                LOWER(COALESCE(fo_type, '')) LIKE '%pull%'
                OR LOWER(COALESCE(pull_out_type, '')) LIKE '%pull%'
            )
        ";
    }

    $sql = "
        SELECT
            tenant_name AS tenant,
            COALESCE(CAST(unit_type AS STRING), '-') AS category,

            CASE
                WHEN LOWER(COALESCE(fo_type, '')) LIKE '%pull%'
                  OR LOWER(COALESCE(pull_out_type, '')) LIKE '%pull%'
                    THEN 'Out'

                WHEN LOWER(COALESCE(fo_type, '')) LIKE '%new%'
                    THEN 'New'

                ELSE COALESCE(CAST(fo_type AS STRING), '-')
            END AS movement_type,

            DATE(ho_date) AS ho_date
        FROM `ifca-pkwjakarta.fitoutme.fitoutme_list_fitout_type_src`
        WHERE ho_date IS NOT NULL
          AND DATE(ho_date) BETWEEN @start_date AND @end_date
          AND (
            CASE
              WHEN mall = 'Gandaria City' THEN 'GC'
              WHEN mall = 'Kota Kasablanka' THEN 'KK'
              WHEN mall = 'Plaza Blok M' THEN 'PBM'
              WHEN mall = 'Pakuwon Mall Bekasi' THEN 'PMB'
              ELSE mall
            END
          ) = @mall
          {$movementFilter}
        ORDER BY ho_date DESC, tenant_name
        LIMIT 10
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'tenant' => $row['tenant'] ?? '-',
            'category' => $row['category'] ?? '-',
            'type' => $row['movement_type'] ?? '-',
        ];
    }, $rows);
}
private function getFitOutAndTenantMovement(string $mall, string $startDate, string $endDate, string $movementType): array
{
    $sql = "
        WITH base AS (
            SELECT tenant_name, floor, unit_type, fo_type, pull_out_type, DATE(ho_date) AS ho_date
            FROM `ifca-pkwjakarta.fitoutme.fitoutme_list_fitout_type_src`
            WHERE ho_date IS NOT NULL
              AND DATE(ho_date) BETWEEN @start_date AND @end_date
              AND (CASE
                    WHEN mall = 'Gandaria City' THEN 'GC'
                    WHEN mall = 'Kota Kasablanka' THEN 'KK'
                    WHEN mall = 'Plaza Blok M' THEN 'PBM'
                    WHEN mall = 'Pakuwon Mall Bekasi' THEN 'PMB'
                    ELSE mall
                  END) = @mall
        ),
        fit_out AS (
            SELECT 'fit_out' AS result_type, tenant_name, floor, unit_type, fo_type, pull_out_type, ho_date
            FROM base ORDER BY ho_date DESC, tenant_name LIMIT 10
        ),
        movement AS (
            SELECT 'movement' AS result_type, tenant_name, floor, unit_type, fo_type, pull_out_type, ho_date
            FROM base
            WHERE @movement_type = 'all'
               OR (@movement_type = 'new' AND LOWER(COALESCE(fo_type, '')) LIKE '%new%')
               OR (@movement_type = 'pullout' AND (
                    LOWER(COALESCE(fo_type, '')) LIKE '%pull%'
                    OR LOWER(COALESCE(pull_out_type, '')) LIKE '%pull%'
               ))
            ORDER BY ho_date DESC, tenant_name LIMIT 10
        )
        SELECT * FROM fit_out
        UNION ALL
        SELECT * FROM movement
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'movement_type' => $movementType,
    ]);

    $fitOutList = [];
    $tenantMovement = [];

    foreach ($rows as $row) {
        if (($row['result_type'] ?? '') === 'fit_out') {
            $floor = $row['floor'] ?? '-';
            $unitType = (string) ($row['unit_type'] ?? '');
            $fitOutList[] = [
                'tenant' => $row['tenant_name'] ?? '-',
                'area_sqm' => 0,
                'location' => $floor . ($unitType !== '' ? ' ' . $unitType : ''),
            ];
            continue;
        }

        $foType = strtolower((string) ($row['fo_type'] ?? ''));
        $pullOutType = strtolower((string) ($row['pull_out_type'] ?? ''));
        $type = str_contains($foType, 'pull') || str_contains($pullOutType, 'pull')
            ? 'Out'
            : (str_contains($foType, 'new') ? 'New' : ($row['fo_type'] ?? '-'));

        $tenantMovement[] = [
            'tenant' => $row['tenant_name'] ?? '-',
            'category' => $row['unit_type'] ?? '-',
            'type' => $type,
        ];
    }

    return compact('fitOutList', 'tenantMovement');
}

private function getBudgetSpending(string $mall, string $startDate, string $endDate): array
{
    $excludedDepartments = $this->budgetExcludedDepartmentsSql();

    $sql = "
        WITH base AS (
            SELECT
                department,
                item,
                SUM(COALESCE(budget, 0)) AS budget,
                SUM(COALESCE(spending, 0)) AS spending,
                SUM(COALESCE(unbudgeted, 0)) AS unbudgeted
            FROM `ifca-pkwjakarta.dashboard_summary.om_budget_spending_item_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
              AND UPPER(TRIM(department)) NOT IN ({$excludedDepartments})
            GROUP BY department, item
        ),

        dept_summary AS (
            SELECT
                department,
                SUM(IF(budget > 0, budget, 0)) AS total_budget,
                SUM(IF(budget > 0, spending, 0)) AS budgeted_spending,
                SUM(IF(budget = 0, spending, 0)) AS unbudgeted_spending,
                SUM(spending) AS total_spending,
                ROUND(
                    SAFE_DIVIDE(
                        SUM(IF(budget > 0, spending, 0)),
                        NULLIF(SUM(IF(budget > 0, budget, 0)), 0)
                    ) * 100,
                    2
                ) AS usage_percent
            FROM base
            GROUP BY department
        )

        SELECT
            b.department,
            b.item,
            b.budget,
            b.spending,
            b.unbudgeted,
            CASE
                WHEN b.budget > 0 THEN ROUND(SAFE_DIVIDE(b.spending, b.budget) * 100, 2)
                ELSE NULL
            END AS item_usage_percent,
            d.total_budget,
            d.budgeted_spending,
            d.unbudgeted_spending,
            d.total_spending,
            d.usage_percent
        FROM base b
        LEFT JOIN dept_summary d
          ON b.department = d.department
        ORDER BY d.usage_percent DESC, b.department, b.spending DESC
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $departments = [];

    foreach ($rows as $row) {
        $deptRaw = trim((string) ($row['department'] ?? '-'));
        $deptKey = strtoupper($deptRaw);

        if (!isset($departments[$deptKey])) {
            $departments[$deptKey] = [
                'department' => $this->formatDepartmentName($deptRaw),
                'total_budget' => $this->toFloat($row['total_budget'] ?? 0),
                'budgeted_spending' => $this->toFloat($row['budgeted_spending'] ?? 0),
                'unbudgeted_spending' => $this->toFloat($row['unbudgeted_spending'] ?? 0),
                'total_spending' => $this->toFloat($row['total_spending'] ?? 0),
                'usage_percent' => $this->toFloat($row['usage_percent'] ?? 0),
                'items' => [],
            ];
        }

        $budget = $this->toFloat($row['budget'] ?? 0);
        $spending = $this->toFloat($row['spending'] ?? 0);
        $unbudgeted = $this->toFloat($row['unbudgeted'] ?? 0);

        $departments[$deptKey]['items'][] = [
            'item' => $row['item'] ?? '-',
            'budget' => $budget,
            'spending' => $spending,
            'unbudgeted' => $unbudgeted,
            'usage_percent' => $row['item_usage_percent'] === null
                ? null
                : $this->toFloat($row['item_usage_percent']),
            'is_unbudgeted' => $budget == 0,
        ];
    }

    return array_values($departments);
}

// One BigQuery job for all widgets; each array preserves its query's ordering and limits.
private function getKaizenRows(string $mall, string $startDate, string $endDate): array
{
    $queries = [
        'getKaizenSummary' => "
        SELECT AS STRUCT
            SUM(total_case) AS total_case,
            SUM(total_open) AS total_open,
            SUM(total_closed) AS total_closed,
            SUM(total_overdue) AS total_overdue,
            SUM(solved_duration_hour_sum) AS solved_duration_hour_sum,
            SUM(solved_case_count) AS solved_case_count
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
    ",
        'getKaizenHotspot' => "
        SELECT AS STRUCT
            location_name,
            SUM(total) AS total
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_hotspot_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY location_name
        ORDER BY total DESC
        LIMIT 10
    ",
        'getKaizenPeakHour' => "
        SELECT AS STRUCT
            day_no,
            hour_of_day,
            SUM(total_case) AS total_case
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_peak_hour_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY day_no, hour_of_day
        ORDER BY day_no, hour_of_day
    ",
        'getKaizenDurationDept' => "
        SELECT AS STRUCT
            kaizen_department,
            SUM(solved_duration_hour_sum) AS solved_duration_hour_sum,
            SUM(solved_case_count) AS solved_case_count,
            SAFE_DIVIDE(
                SUM(solved_duration_hour_sum),
                NULLIF(SUM(solved_case_count), 0)
            ) / 24 AS avg_duration_day
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
          AND solved_case_count > 0
        GROUP BY kaizen_department
        ORDER BY avg_duration_day ASC
        LIMIT 10
    ",
        'getKaizenOverdueDept' => "
        SELECT AS STRUCT
            kaizen_department,
            SUM(total_case) AS total_case,
            SUM(total_overdue) AS total_overdue,
            SAFE_DIVIDE(SUM(total_overdue), NULLIF(SUM(total_case), 0)) * 100 AS overdue_rate
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_summary_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY kaizen_department
        HAVING total_case > 0
        ORDER BY overdue_rate DESC
        LIMIT 10
    ",
        'getKaizenCasePerStaff' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            COUNT(DISTINCT kaizen_id) AS total_case,
            COUNT(DISTINCT NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '')) AS total_staff,
            SAFE_DIVIDE(
                COUNT(DISTINCT kaizen_id),
                NULLIF(COUNT(DISTINCT NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '')), 0)
            ) AS case_per_staff
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_analytics_base`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY case_per_staff DESC
        LIMIT 10
    ",
        'getKaizenDurationPerStaff' => "
        WITH staff_avg AS (
            SELECT
                kaizen_department AS department,
                NULLIF(TRIM(CAST(kaizen_close_by AS STRING)), '') AS staff_name,
                COUNT(DISTINCT kaizen_id) AS total_case,
                AVG(COALESCE(solved_duration_minute, 0)) / 60 / 24 AS avg_duration_day
            FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_analytics_base`
            WHERE site = @mall
              AND issue_dt BETWEEN @start_date AND @end_date
              AND kaizen_close_by IS NOT NULL
              AND solved_duration_minute IS NOT NULL
            GROUP BY department, staff_name
        )

        SELECT AS STRUCT
            department,
            COUNT(DISTINCT staff_name) AS total_staff,
            SUM(total_case) AS total_case,
            AVG(avg_duration_day) AS duration_per_staff_day
        FROM staff_avg
        WHERE staff_name IS NOT NULL
        GROUP BY department
        HAVING total_staff > 0
        ORDER BY duration_per_staff_day DESC
        LIMIT 10
    ",
        'getKaizenOverdueCases' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            COUNT(DISTINCT case_no) AS total_case,
            MAX(duration_hour) / 24 AS max_duration_day,
            AVG(duration_hour) / 24 AS avg_duration_day,

            CASE
                WHEN MAX(duration_hour) / 24 >= 14 THEN 'Overdue'
                ELSE 'At risk'
            END AS case_status

        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_overdue_case_base`
        WHERE site = @mall
          AND issue_dt <= @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY max_duration_day DESC
        LIMIT 10
    ",
        'getKaizenRepeatIncident' => "
        SELECT AS STRUCT
            kaizen_department AS department,
            CONCAT(
                COALESCE(item, '-'),
                CASE
                    WHEN subitem IS NOT NULL AND TRIM(subitem) <> ''
                        THEN CONCAT(' / ', subitem)
                    ELSE ''
                END
            ) AS item_name,
            COALESCE(location_name, '-') AS area,
            SUM(repeat_total) AS total_case
        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_repeat_incident_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY
            department,
            item_name,
            area
        HAVING total_case > 0
        ORDER BY total_case DESC
        LIMIT 10
    ",
        'getKaizenDurationItem' => "
        SELECT AS STRUCT
            CONCAT(
                COALESCE(item, '-'),
                CASE
                    WHEN subitem IS NOT NULL AND TRIM(subitem) <> ''
                        THEN CONCAT(' / ', subitem)
                    ELSE ''
                END
            ) AS item_name,

            SUM(total_kaizen) AS total_case,
            SAFE_DIVIDE(
                SUM(total_duration_minute),
                NULLIF(SUM(total_kaizen), 0)
            ) / 60 / 24 AS avg_duration_day

        FROM `ifca-pkwjakarta.isort.tb_kaizen_dashboard_duration_item_daily`
        WHERE site = @mall
          AND issue_dt BETWEEN @start_date AND @end_date
        GROUP BY item_name
        HAVING total_case > 0
        ORDER BY avg_duration_day DESC
        LIMIT 10
    ",
    ];
    $columns = [];
    foreach ($queries as $name => $sql) {
        $columns[] = "TO_JSON_STRING(ARRAY($sql)) AS $name";
    }
    $result = $this->runQuery('SELECT ' . implode(",\n", $columns), [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $rows = [];
    foreach ($queries as $name => $sql) {
        $rows[$name] = json_decode($result[0][$name], true, 512, JSON_THROW_ON_ERROR);
    }
    return $rows;
}

private function getKaizenSummary(array $rows): array
{

    $row = $rows[0] ?? [];

    $totalCase = $this->toFloat($row['total_case'] ?? 0);
    $totalOpen = $this->toFloat($row['total_open'] ?? 0);
    $totalClosed = $this->toFloat($row['total_closed'] ?? 0);
    $totalOverdue = $this->toFloat($row['total_overdue'] ?? 0);
    $durationHourSum = $this->toFloat($row['solved_duration_hour_sum'] ?? 0);
    $solvedCaseCount = $this->toFloat($row['solved_case_count'] ?? 0);

    $avgDurationHour = $solvedCaseCount > 0
        ? $durationHourSum / $solvedCaseCount
        : 0;

    return [
        'total_case' => $totalCase,
        'total_open' => $totalOpen,
        'total_closed' => $totalClosed,
        'total_overdue' => $totalOverdue,

        'open_rate' => $totalCase > 0 ? round(($totalOpen / $totalCase) * 100, 1) : 0,
        'closed_rate' => $totalCase > 0 ? round(($totalClosed / $totalCase) * 100, 1) : 0,
        'overdue_rate' => $totalCase > 0 ? round(($totalOverdue / $totalCase) * 100, 1) : 0,

        'avg_duration_hour' => round($avgDurationHour, 1),
        'avg_duration_day' => round($avgDurationHour / 24, 1),
    ];
}

private function getKaizenHotspot(array $rows): array
{

    return array_map(function ($row) {
        return [
            'location_name' => $row['location_name'] ?? '-',
            'total' => $this->toFloat($row['total'] ?? 0),
        ];
    }, $rows);
}

private function getKaizenPeakHour(array $rows): array
{

    return array_map(function ($row) {
        return [
            'day_no' => (int) ($row['day_no'] ?? 0),
            'hour_of_day' => (int) ($row['hour_of_day'] ?? 0),
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
        ];
    }, $rows);
}

private function getKaizenDurationDept(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['kaizen_department'] ?? '-',
            'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
        ];
    }, $rows);
}

private function getKaizenOverdueDept(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['kaizen_department'] ?? '-',
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'total_overdue' => $this->toFloat($row['total_overdue'] ?? 0),
            'overdue_rate' => round($this->toFloat($row['overdue_rate'] ?? 0), 1),
        ];
    }, $rows);
}

private function getKaizenCasePerStaff(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['department'] ?? '-',
            'total_staff' => $this->toFloat($row['total_staff'] ?? 0),
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'case_per_staff' => round($this->toFloat($row['case_per_staff'] ?? 0), 1),
        ];
    }, $rows);
}

private function getKaizenDurationPerStaff(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['department'] ?? '-',
            'total_staff' => $this->toFloat($row['total_staff'] ?? 0),
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'duration_per_staff_day' => round($this->toFloat($row['duration_per_staff_day'] ?? 0), 1),
        ];
    }, $rows);
}

private function getKaizenOverdueCases(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['department'] ?? '-',
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'status' => $row['case_status'] ?? '-',
            'duration_day' => round($this->toFloat($row['max_duration_day'] ?? 0), 0),
            'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
        ];
    }, $rows);
}

private function getKaizenRepeatIncident(array $rows): array
{

    return array_map(function ($row) {
        return [
            'department' => $row['department'] ?? '-',
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'item' => $row['item_name'] ?? '-',
            'area' => $row['area'] ?? '-',
        ];
    }, $rows);
}

private function getKaizenDurationItem(array $rows): array
{

    return array_map(function ($row) {
        return [
            'item' => $row['item_name'] ?? '-',
            'total_case' => $this->toFloat($row['total_case'] ?? 0),
            'avg_duration_day' => round($this->toFloat($row['avg_duration_day'] ?? 0), 1),
        ];
    }, $rows);
}

private function departmentQueries(): array
{
    return [
        'ManpowerFulfillment' => "
        SELECT
            category,
            department_name,
            SUM(COALESCE(total_actual, actual, 0)) AS total_actual,
            SUM(COALESCE(total_target, schedule, 0)) AS total_target,
            SUM(COALESCE(variance, 0)) AS variance,
            CASE
                WHEN category = 'Inhouse' THEN NULL
                WHEN SUM(COALESCE(total_actual, actual, 0)) >= SUM(COALESCE(total_target, schedule, 0))
                    THEN 'Achieved'
                ELSE 'Below Target'
            END AS status,
            CASE
                WHEN category = 'Inhouse' THEN NULL
                ELSE SAFE_CAST(
                    ROUND(
                        SAFE_DIVIDE(
                            SUM(COALESCE(total_actual, actual, 0)),
                            NULLIF(SUM(COALESCE(total_target, schedule, 0)), 0)
                        ) * 100,
                        2
                    ) AS NUMERIC
                )
            END AS percentage
        FROM `ifca-pkwjakarta.dashboard_summary.om_manpower_fulfillment_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
          AND category IN ('Inhouse', 'Outsource')
        GROUP BY
            category,
            department_name
        ORDER BY
            category,
            department_name
    ",
        'IncidentByDepartment' => "
        SELECT
            department,
            SUM(total) AS total_case
        FROM `ifca-pkwjakarta.dashboard_summary.om_incident_department_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY department
        HAVING total_case > 0
        ORDER BY total_case DESC
    ",
        'EquipmentStatus' => "
        WITH latest_date AS (
            SELECT
                MAX(summary_date) AS max_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_status_daily`
            WHERE mall = @mall
              AND summary_date <= @end_date
        ),

        latest_data AS (
            SELECT
                a.*
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_status_daily` a
            CROSS JOIN latest_date l
            WHERE a.mall = @mall
              AND a.summary_date = l.max_date
        ),

        summary AS (
            SELECT
                COUNT(*) AS total_monitored,
                COUNTIF(final_status = 'Below Normal') AS below_normal,
                COUNTIF(final_status = 'Normal') AS normal,
                MAX(summary_date) AS latest_date
            FROM latest_data
        ),

        below_list AS (
            SELECT
                department_name,
                COALESCE(equipment_name, item_name) AS item_parameter,
                final_status
            FROM latest_data
            WHERE final_status = 'Below Normal'
            ORDER BY department_name, item_parameter
            LIMIT 10
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_parameter,
            NULL AS final_status,
            total_monitored,
            below_normal,
            normal,
            latest_date
        FROM summary

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_parameter,
            final_status,
            NULL AS total_monitored,
            NULL AS below_normal,
            NULL AS normal,
            NULL AS latest_date
        FROM below_list
    ",
        'EquipmentOpenClose' => "
        WITH summary AS (
            SELECT
                SUM(total_on) AS total_on,
                SUM(total_off) AS total_off,
                SUM(total_unit) AS total_unit
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_open_close_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
        ),

        off_list AS (
            SELECT
                department_name,
                item_name,
                SUM(total_off) AS qty_off
            FROM `ifca-pkwjakarta.dashboard_summary.om_equipment_open_close_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
            GROUP BY
                department_name,
                item_name
            HAVING qty_off > 0
            ORDER BY qty_off DESC, item_name
            LIMIT 10
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_name,
            NULL AS qty_off,
            total_on,
            total_off,
            total_unit
        FROM summary

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_name,
            qty_off,
            NULL AS total_on,
            NULL AS total_off,
            NULL AS total_unit
        FROM off_list
    ",
        'ConsumableUsage' => "
        SELECT
            item_id,
            COALESCE(item_name, CONCAT('Item ', CAST(item_id AS STRING))) AS item_name,
            SUM(total) AS total_usage
        FROM `ifca-pkwjakarta.dashboard_summary.om_consumable_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY item_id, item_name
        HAVING total_usage > 0
        ORDER BY total_usage DESC
        LIMIT 6
    ",
        'AssetInventory' => "
        WITH latest_date AS (
            SELECT
                MAX(summary_date) AS max_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_asset_inventory_daily`
            WHERE mall = @mall
              AND summary_date <= @end_date
        ),

        latest_data AS (
            SELECT
                a.*
            FROM `ifca-pkwjakarta.dashboard_summary.om_asset_inventory_daily` a
            CROSS JOIN latest_date l
            WHERE a.mall = @mall
              AND a.summary_date = l.max_date
        ),

        summary AS (
            SELECT
                SUM(good) AS total_good,
                SUM(bad) AS total_bad,
                SUM(qty) AS total_qty,
                MAX(summary_date) AS latest_date
            FROM latest_data
        ),

        dept_list AS (
            SELECT
                department_name,
                SUM(good) AS dept_good,
                SUM(bad) AS dept_bad,
                SUM(qty) AS dept_qty
            FROM latest_data
            GROUP BY department_name
            ORDER BY department_name
        ),

        bad_list AS (
            SELECT
                department_name,
                item_name,
                SUM(bad) AS qty_bad
            FROM latest_data
            GROUP BY
                department_name,
                item_name
            HAVING qty_bad > 0
            ORDER BY qty_bad DESC, item_name
            LIMIT 20
        )

        SELECT
            'summary' AS row_type,
            NULL AS department_name,
            NULL AS item_name,
            NULL AS qty_bad,
            total_good,
            total_bad,
            total_qty,
            latest_date
        FROM summary

        UNION ALL

        SELECT
            'dept' AS row_type,
            department_name,
            NULL AS item_name,
            NULL AS qty_bad,
            dept_good AS total_good,
            dept_bad AS total_bad,
            dept_qty AS total_qty,
            NULL AS latest_date
        FROM dept_list

        UNION ALL

        SELECT
            'detail' AS row_type,
            department_name,
            item_name,
            qty_bad,
            NULL AS total_good,
            NULL AS total_bad,
            NULL AS total_qty,
            NULL AS latest_date
        FROM bad_list
    ",
        'TenantUpdate' => "
        SELECT
            tenant_name,
            unit,
            type,
            status,
            summary_date,
            ho_date,
            actual_ho_date,
            open_date,
            actual_open_date
        FROM `ifca-pkwjakarta.dashboard_summary.om_tenant_update_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        ORDER BY summary_date DESC, tenant_name
        LIMIT 10
    ",
    ];
}

private function getDepartmentRows(array $names, string $mall, string $startDate, string $endDate): array
{
    $queries = $this->departmentQueries();
    $columns = [];
    foreach ($names as $name) {
        $sql = $queries[$name];
        // UNION queries already return summary/detail rows; wrap their complete result.
        // Keep ORDER BY at the array level for the other widget queries.
        $sql = str_contains($sql, 'UNION ALL')
            ? "SELECT AS STRUCT * FROM ($sql)"
            : preg_replace('/^(\s*)SELECT\b/', '$1SELECT AS STRUCT', $sql, 1);
        $columns[] = "TO_JSON_STRING(ARRAY($sql)) AS $name";
    }
    $result = $this->runQuery('SELECT ' . implode(",\n", $columns), [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);
    $rows = [];
    foreach ($names as $name) {
        $rows[$name] = json_decode($result[0][$name], true, 512, JSON_THROW_ON_ERROR);
    }
    return $rows;
}

private function getManpowerFulfillment(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['ManpowerFulfillment'], [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $result = [
        'inhouse' => [
            'overall' => 0,
            'total_actual' => 0,
            'total_target' => 0,
            'lowest_department' => '-',
            'lowest_percentage' => null,
            'departments' => [],
        ],
        'outsource' => [
            'overall' => 0,
            'total_actual' => 0,
            'total_target' => 0,
            'lowest_department' => '-',
            'lowest_percentage' => 0,
            'departments' => [],
        ],
    ];

    foreach ($rows as $row) {
        $category = strtolower($row['category'] ?? '');

        if (!in_array($category, ['inhouse', 'outsource'], true)) {
            continue;
        }

        $department = $row['department_name'] ?? '-';
        $actual = (float) ($row['total_actual'] ?? 0);
        $target = (float) ($row['total_target'] ?? 0);
        $percentage = $row['percentage'] === null ? null : (float) $row['percentage'];

        $result[$category]['departments'][] = [
            'department_name' => $department,
            'actual' => $actual,
            'target' => $target,
            'percentage' => $percentage,
            'variance' => (float) ($row['variance'] ?? 0),
            'status' => $row['status'] ?? null,
        ];

        $result[$category]['total_actual'] += $actual;
        $result[$category]['total_target'] += $target;
    }

    // Inhouse: tidak ada target, jadi overall tampil total activity/person count
    $result['inhouse']['overall'] = $result['inhouse']['total_actual'];

    // Outsource: ada target, jadi overall percentage
    if ($result['outsource']['total_target'] > 0) {
        $result['outsource']['overall'] = round(
            ($result['outsource']['total_actual'] / $result['outsource']['total_target']) * 100,
            2
        );
    }

    // Lowest outsource fulfillment
    $outsourceWithTarget = array_filter(
        $result['outsource']['departments'],
        fn ($item) => $item['percentage'] !== null
    );

    if (!empty($outsourceWithTarget)) {
        usort($outsourceWithTarget, fn ($a, $b) => $a['percentage'] <=> $b['percentage']);

        $result['outsource']['lowest_department'] = $outsourceWithTarget[0]['department_name'];
        $result['outsource']['lowest_percentage'] = $outsourceWithTarget[0]['percentage'];
    }

    // Untuk inhouse, lowest tidak dipakai karena tidak ada target
    $result['inhouse']['lowest_department'] = '-';
    $result['inhouse']['lowest_percentage'] = null;

    return $result;
}

private function getTrafficPeakHours(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        WITH base AS (
            SELECT
                head_peak_hour,
                taxi_peak_hour
            FROM `ifca-pkwjakarta.dashboard_summary.gm_operation_daily`
            WHERE mall = @mall
              AND summary_date BETWEEN @start_date AND @end_date
        ),

        head_peak AS (
            SELECT
                head_peak_hour,
                COUNT(*) AS total_days
            FROM base
            WHERE head_peak_hour IS NOT NULL
              AND head_peak_hour != ''
            GROUP BY head_peak_hour
            ORDER BY total_days DESC, head_peak_hour ASC
            LIMIT 1
        ),

        taxi_peak AS (
            SELECT
                taxi_peak_hour,
                COUNT(*) AS total_days
            FROM base
            WHERE taxi_peak_hour IS NOT NULL
              AND taxi_peak_hour != ''
            GROUP BY taxi_peak_hour
            ORDER BY total_days DESC, taxi_peak_hour ASC
            LIMIT 1
        )

        SELECT
            (SELECT head_peak_hour FROM head_peak) AS head_peak_hour,
            (SELECT taxi_peak_hour FROM taxi_peak) AS taxi_peak_hour
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];

    return [
        'head_peak_hour' => $row['head_peak_hour'] ?? '-',
        'taxi_peak_hour' => $row['taxi_peak_hour'] ?? '-',
        'blue_bird_peak_hour' => $row['taxi_peak_hour'] ?? '-',
    ];
}

private function getWorkDetailDaily(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        SELECT
            department,
            name_category,
            SUM(activity_count) AS total_activity
        FROM `ifca-pkwjakarta.dashboard_summary.om_work_detail_daily`
        WHERE mall = @mall
          AND summary_date BETWEEN @start_date AND @end_date
        GROUP BY department, name_category
        ORDER BY total_activity DESC
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $departments = [];
    $allCategories = [];

    foreach ($rows as $row) {
        $department = trim((string) ($row['department'] ?? '-'));
        $category = trim((string) ($row['name_category'] ?? '-'));
        $total = (int) ($row['total_activity'] ?? 0);

        if (!isset($departments[$department])) {
            $departments[$department] = [
                'department' => $department,
                'categories' => [],
                'total' => 0,
            ];
        }

        $departments[$department]['categories'][] = [
            'category' => $category,
            'total_activity' => $total,
        ];

        $departments[$department]['total'] += $total;

        if (!isset($allCategories[$category])) {
            $allCategories[$category] = 0;
        }

        $allCategories[$category] += $total;
    }

    arsort($allCategories);

    $all = [];
    foreach ($allCategories as $category => $total) {
        $all[] = [
            'category' => $category,
            'total_activity' => $total,
        ];
    }

    return [
        'all' => $all,
        'departments' => array_values($departments),
    ];
}

private function getDropOffSummary(string $mall, string $startDate, string $endDate): array
{
    $sql = "
        WITH ranges AS (
            SELECT 'current' AS period, DATE(@start_date) AS start_date, DATE(@end_date) AS end_date

            UNION ALL
            SELECT
                'lw',
                DATE_SUB(DATE(@start_date), INTERVAL DATE_DIFF(DATE(@end_date), DATE(@start_date), DAY) + 1 DAY),
                DATE_SUB(DATE(@start_date), INTERVAL 1 DAY)

            UNION ALL
            SELECT
                'lm',
                DATE_SUB(DATE(@start_date), INTERVAL 1 MONTH),
                DATE_SUB(DATE(@end_date), INTERVAL 1 MONTH)

            UNION ALL
            SELECT
                'ly',
                DATE_SUB(DATE(@start_date), INTERVAL 1 YEAR),
                DATE_SUB(DATE(@end_date), INTERVAL 1 YEAR)
        ),

        base AS (
            SELECT
                r.period,
                d.hour_label,
                SUM(d.drop_count) AS total_dropoff
            FROM ranges r
            LEFT JOIN `ifca-pkwjakarta.dashboard_summary.om_dropoff_hourly` d
              ON d.summary_date BETWEEN r.start_date AND r.end_date
             AND d.mall = @mall
            GROUP BY r.period, d.hour_label
        ),

        total_by_period AS (
            SELECT
                period,
                SUM(total_dropoff) AS drop_off_count
            FROM base
            GROUP BY period
        ),

        peak_current AS (
            SELECT
                hour_label
            FROM base
            WHERE period = 'current'
              AND hour_label IS NOT NULL
              AND SAFE.PARSE_TIME('%H:%M', hour_label) BETWEEN TIME '10:00:00' AND TIME '22:00:00'
            ORDER BY total_dropoff DESC, hour_label ASC
            LIMIT 1
        )

        SELECT
            COALESCE(MAX(IF(period = 'current', drop_off_count, NULL)), 0) AS current_count,
            COALESCE(MAX(IF(period = 'lw', drop_off_count, NULL)), 0) AS lw_count,
            COALESCE(MAX(IF(period = 'lm', drop_off_count, NULL)), 0) AS lm_count,
            COALESCE(MAX(IF(period = 'ly', drop_off_count, NULL)), 0) AS ly_count,
            (SELECT hour_label FROM peak_current) AS drop_off_peak_hour
        FROM total_by_period
    ";

    $rows = $this->runQuery($sql, [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $row = $rows[0] ?? [];

    $current = $this->toFloat($row['current_count'] ?? 0);
    $lw = $this->toFloat($row['lw_count'] ?? 0);
    $lm = $this->toFloat($row['lm_count'] ?? 0);
    $ly = $this->toFloat($row['ly_count'] ?? 0);

    return [
        'drop_off_count' => $current,
        'drop_off_peak_hour' => $row['drop_off_peak_hour'] ?? '-',
        'drop_off_lw_percent' => $this->percentChange($current, $lw),
        'drop_off_lm_percent' => $this->percentChange($current, $lm),
        'drop_off_ly_percent' => $this->percentChange($current, $ly),
    ];
}

private function getIncidentByDepartment(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['IncidentByDepartment'], [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $departments = [];
    $totalIncident = 0;

    foreach ($rows as $row) {
        $department = trim((string) ($row['department'] ?? '-'));
        $totalCase = (int) ($row['total_case'] ?? 0);

        $departments[] = [
            'department' => $this->formatDepartmentName($department),
            'total_case' => $totalCase,
        ];

        $totalIncident += $totalCase;
    }

    $highest = $departments[0] ?? [
        'department' => '-',
        'total_case' => 0,
    ];

    return [
        'departments' => $departments,
        'total_incident' => $totalIncident,
        'highest_department' => $highest['department'],
        'highest_case' => $highest['total_case'],
    ];
}

private function getConsumableUsage(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['ConsumableUsage'], [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'item_id' => (int) ($row['item_id'] ?? 0),
            'name' => $row['item_name'] ?? '-',
            'uom' => '',
            'total' => $this->toFloat($row['total_usage'] ?? 0),
        ];
    }, $rows);
}

private function getTenantUpdate(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['TenantUpdate'], [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    return array_map(function ($row) {
        return [
            'tenant' => $row['tenant_name'] ?? '-',
            'unit' => $row['unit'] ?? '-',
            'type' => $row['type'] ?? '-',
            'status' => $row['status'] ?? '-',
            'summary_date' => $row['summary_date'] ?? null,
        ];
    }, $rows);
}

private function getEquipmentOpenClose(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['EquipmentOpenClose'], [
        'mall' => $mall,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    $summary = [
        'total_on' => 0,
        'total_off' => 0,
        'total_unit' => 0,
        'items' => [],
    ];

    foreach ($rows as $row) {
        if (($row['row_type'] ?? '') === 'summary') {
            $summary['total_on'] = $this->toFloat($row['total_on'] ?? 0);
            $summary['total_off'] = $this->toFloat($row['total_off'] ?? 0);
            $summary['total_unit'] = $this->toFloat($row['total_unit'] ?? 0);
            continue;
        }

        $summary['items'][] = [
            'department' => $this->formatDepartmentName($row['department_name'] ?? '-'),
            'item' => $row['item_name'] ?? '-',
            'qty' => $this->toFloat($row['qty_off'] ?? 0),
        ];
    }

    return $summary;
}

private function getAssetInventory(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['AssetInventory'], [
        'mall' => $mall,
        'end_date' => $endDate,
    ]);

    $result = [
        'total_good' => 0,
        'total_bad' => 0,
        'total_qty' => 0,
        'latest_date' => null,
        'departments' => [],
        'items' => [],
    ];

    foreach ($rows as $row) {
        $rowType = $row['row_type'] ?? '';

        if ($rowType === 'summary') {
            $result['total_good'] = $this->toFloat($row['total_good'] ?? 0);
            $result['total_bad'] = $this->toFloat($row['total_bad'] ?? 0);
            $result['total_qty'] = $this->toFloat($row['total_qty'] ?? 0);
            $result['latest_date'] = $row['latest_date'] ?? null;
            continue;
        }

        if ($rowType === 'dept') {
    $department = $row['department_name'] ?? '-';

    $result['departments'][] = [
        'department' => $this->formatDepartmentName($department),
        'department_raw' => $department,
        'total_good' => $this->toFloat($row['total_good'] ?? 0),
        'total_bad' => $this->toFloat($row['total_bad'] ?? 0),
        'total_qty' => $this->toFloat($row['total_qty'] ?? 0),
    ];

    continue;
}

        if ($rowType === 'detail') {
            $department = $row['department_name'] ?? '-';

            $result['items'][] = [
                'department' => $this->formatDepartmentName($department),
                'department_raw' => $department,
                'item' => $row['item_name'] ?? '-',
                'qty' => $this->toFloat($row['qty_bad'] ?? 0),
            ];
        }
    }

    return $result;
}

private function getEventPipeline(string $mall, string $startDate, string $endDate): array
{
    $cacheKey = "om_event_pipeline_{$mall}_{$startDate}_{$endDate}";

    return $this->cachedQuery($cacheKey, function () use ($mall, $startDate, $endDate) {
        $sql = "
            SELECT
                event_location_name AS location,
                event_name AS event,
                event_start_date,
                event_end_date
            FROM `ifca-pkwjakarta.dashboard_summary.om_event_pipeline_daily`
            WHERE mall = @mall
              AND event_start_date <= @end_date
              AND event_end_date >= @start_date
            ORDER BY event_start_date ASC, event_name
            LIMIT 10
        ";

        $rows = $this->runQuery($sql, [
            'mall' => $mall,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return array_map(function ($row) {
            $start = $row['event_start_date'] ?? null;
            $end = $row['event_end_date'] ?? null;

            $period = '-';

            if ($start && $end) {
    $startDate = $start instanceof \DateTimeInterface
        ? \Carbon\Carbon::instance($start)
        : \Carbon\Carbon::parse((string) $start);

    $endDate = $end instanceof \DateTimeInterface
        ? \Carbon\Carbon::instance($end)
        : \Carbon\Carbon::parse((string) $end);

    if ($startDate->format('M Y') === $endDate->format('M Y')) {
        $period = $startDate->format('j') . '–' . $endDate->format('j M');
    } else {
        $period = $startDate->format('j M') . '–' . $endDate->format('j M');
    }
}

            return [
                'location' => $row['location'] ?? '-',
                'event' => $row['event'] ?? '-',
                'period' => $period,
                'event_start_date' => $start,
                'event_end_date' => $end,
            ];
        }, $rows);
    }, 3600);
}

private function getEquipmentStatus(string $mall, string $startDate, string $endDate, ?array $rows = null): array
{
    $rows ??= $this->runQuery($this->departmentQueries()['EquipmentStatus'], [
        'mall' => $mall,
        'end_date' => $endDate,
    ]);

    $result = [
        'total_monitored' => 0,
        'below_normal' => 0,
        'normal' => 0,
        'latest_date' => null,
        'items' => [],
    ];

    foreach ($rows as $row) {
        if (($row['row_type'] ?? '') === 'summary') {
            $result['total_monitored'] = $this->toFloat($row['total_monitored'] ?? 0);
            $result['below_normal'] = $this->toFloat($row['below_normal'] ?? 0);
            $result['normal'] = $this->toFloat($row['normal'] ?? 0);
            $result['latest_date'] = $row['latest_date'] ?? null;
            continue;
        }

        $result['items'][] = [
            'department' => $this->formatDepartmentName($row['department_name'] ?? '-'),
            'item_parameter' => $row['item_parameter'] ?? '-',
            'status' => $row['final_status'] ?? '-',
        ];
    }

    return $result;
}

private function getComparisonRanges(string $startDate, string $endDate): array
{
    $start = \Carbon\Carbon::parse($startDate);
    $end = \Carbon\Carbon::parse($endDate);

    $days = $start->diffInDays($end) + 1;

    $lwEnd = $start->copy()->subDay();
    $lwStart = $lwEnd->copy()->subDays($days - 1);

    $lmStart = $start->copy()->subMonthNoOverflow();
    $lmEnd = $end->copy()->subMonthNoOverflow();

    $lyStart = $start->copy()->subYearNoOverflow();
    $lyEnd = $end->copy()->subYearNoOverflow();

    return [
        'lw' => [
            'start' => $lwStart->toDateString(),
            'end' => $lwEnd->toDateString(),
        ],
        'lm' => [
            'start' => $lmStart->toDateString(),
            'end' => $lmEnd->toDateString(),
        ],
        'ly' => [
            'start' => $lyStart->toDateString(),
            'end' => $lyEnd->toDateString(),
        ],
    ];
}

private function percentChange(float|int $current, float|int $previous): float
{
    if ($previous == 0) {
        return 0;
    }

    return round((($current - $previous) / $previous) * 100, 1);
}

private function normalizeBigQueryValue($value)
{
    if (is_array($value)) {
        $result = [];

        foreach ($value as $key => $item) {
            $result[$key] = $this->normalizeBigQueryValue($item);
        }

        return $result;
    }

    if ($value instanceof \DateTimeInterface) {
        return $value->format('Y-m-d H:i:s');
    }

    if (is_object($value)) {
        if (method_exists($value, 'get')) {
            return $this->normalizeBigQueryValue($value->get());
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }
    }

    return $value;
}

private function toFloat($value): float
{
    if ($value === null) {
        return 0;
    }

    if (is_object($value) && method_exists($value, 'get')) {
        return (float) $value->get();
    }

    return (float) $value;
}

private function cachedQuery(string $key, callable $callback, int $seconds = 3600): mixed
{
    return \Illuminate\Support\Facades\Cache::remember($key, $seconds, $callback);
}

}
