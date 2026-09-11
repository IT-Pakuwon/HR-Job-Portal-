<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Company;
use App\Models\Jobposting;
use App\Models\MsCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class RecruitmentDashboardController extends Controller
{
    protected ApprovalDashboardController $approvalDashboard;

    public function __construct(ApprovalDashboardController $approvalDashboard)
    {
        $this->approvalDashboard = $approvalDashboard;
    }

    private const JOBPOSTING_STATUS_LABELS = [
        'D' => 'Draft',
        'P' => 'Posted',
        'U' => 'Unposted',
        'C' => 'Closed',
        'R' => 'Rejected',
        'X' => 'Cancelled',
        'H' => 'Hold',
    ];

    private const AGE_BUCKET_LABELS = ['<20', '20-25', '26-30', '31-35', '36-40', '41-45', '46-50', '50+'];

    // hr_trx_job_apply_step.step_id -> funnel stage rank. Several granular
    // steps (e.g. HC vs User interview) are folded into one funnel stage.
    private const STEP_STAGE_RANK = [
        'JOAPHC' => 1, 'JOAPUS' => 2,
        'WIHC' => 3, 'IHC' => 3, 'WIU' => 3, 'IU' => 3,
        'WPT' => 4, 'PT' => 4,
        'OFF' => 5,
        'JOIN' => 6, 'MCU' => 6,
    ];
    private const FUNNEL_STAGE_LABELS = [
        1 => 'Applied', 2 => 'HC Review', 3 => 'Interview',
        4 => 'Psycho Test', 5 => 'Offering', 6 => 'Hired',
    ];

    private static function formatLabel(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? mb_convert_case(mb_strtolower($value), MB_CASE_TITLE) : $value;
    }

    private static function ageBucket(int $age): string
    {
        return match (true) {
            $age < 20 => '<20',
            $age <= 25 => '20-25',
            $age <= 30 => '26-30',
            $age <= 35 => '31-35',
            $age <= 40 => '36-40',
            $age <= 45 => '41-45',
            $age <= 50 => '46-50',
            default => '50+',
        };
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $applicantConn = (new Applicant())->getConnectionName();
        $conn = DB::connection($applicantConn);

        $from = $request->query('from');
        $to = $request->query('to');
        $department = $request->query('department');
        $company = $request->query('company');
        $location = $request->query('location');
        $source = $request->query('source');
        $groupCpnyFilter = $request->query('group_cpny');
        $areaFilter = $request->query('area');

        $userGroupCpny = $user->group_cpny_id ?? null;

        $companyGroups = MsCompany::where('status', 'A')
            ->whereNotNull('group_cpny_id')
            ->where('group_cpny_id', '!=', '')
            ->select('group_cpny_id')
            ->distinct()
            ->orderBy('group_cpny_id')
            ->pluck('group_cpny_id')
            ->values()
            ->toArray();

        $areas = MsCompany::where('status', 'A')
            ->whereNotNull('area_id')
            ->where('area_id', '!=', '')
            ->select('area_id')
            ->distinct()
            ->orderBy('area_id')
            ->pluck('area_id')
            ->values()
            ->toArray();

        // Admin sees every group_cpny_id (e.g. JKT and SBY) and can switch freely.
        // Everyone else (including RECACCALLDEPT) stays locked to their own group_cpny_id.
        $isGroupLocked = !$user->isPrimaryAdmin() && !empty($userGroupCpny);

        if (empty($groupCpnyFilter) && $isGroupLocked) {
            $groupCpnyFilter = $userGroupCpny;
        }

        $filterCompanyIds = null;
        if ($groupCpnyFilter || $areaFilter) {
            $msQuery = MsCompany::where('status', 'A');
            if ($groupCpnyFilter) {
                $msQuery->where('group_cpny_id', $groupCpnyFilter);
            }
            if ($areaFilter) {
                $msQuery->where('area_id', $areaFilter);
            }
            $filterCompanyIds = $msQuery->pluck('cpny_id')->toArray();
            if (empty($filterCompanyIds)) {
                $filterCompanyIds = [null];
            }
        }

        $departments = $conn->table('hr_ms_department')
            ->where('status', 'A')
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);

        $departmentToDivision = $conn->table('hr_ms_department')->pluck('division_id', 'department_id');
        $divisionNames = $conn->table('hr_ms_division')->where('status', 'A')->pluck('division_name', 'division_id');

        $postingCompanyQuery = $conn->table('hr_trx_jobposting')->whereNotNull('cpnyid');
        if ($filterCompanyIds) {
            $postingCompanyQuery->whereIn('cpnyid', $filterCompanyIds);
        }
        $postingCompanyIds = $postingCompanyQuery->distinct()->pluck('cpnyid');
        $companyNames = Company::whereIn('cpnyid', $postingCompanyIds)->pluck('cpnyname', 'cpnyid');
        $companies = $postingCompanyIds->map(fn ($id) => (object) ['cpnyid' => $id, 'cpnyname' => $companyNames->get($id, $id)])
            ->sortBy('cpnyname')
            ->values();

        $locations = $conn->table('hr_trx_jobposting')
            ->whereNotNull('locationname')
            ->where('locationname', '!=', '')
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->distinct()
            ->orderBy('locationname')
            ->pluck('locationname');

        $locationPostingIds = $location
            ? $conn->table('hr_trx_jobposting')
                ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
                ->where('locationname', $location)
                ->pluck('docid')
            : null;

        $isSelfMode = $source === 'self';
        $isCareerMode = $source === 'career';

        $careerBase = $conn->table('viewtrxcareer')
            ->when($from, fn ($q) => $q->whereDate('viewtrxcareer.apply_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('viewtrxcareer.apply_date', '<=', $to))
            ->when($department, fn ($q) => $q->where('viewtrxcareer.departementid', $department))
            ->when($company, fn ($q) => $q->where('viewtrxcareer.cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('viewtrxcareer.cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->whereIn('viewtrxcareer.docidposting', $locationPostingIds))
            ->when($isSelfMode, fn ($q) => $q->whereRaw('1 = 0'));

        // NOTE: self-registered applicants (viewselfregister / hr_trx_selfposting) are not
        // tied to a company/group_cpny_id at registration time — hr_trx_selfposting.group_cpny_id
        // exists in the schema but is empty for every row in this data. They only gain a
        // company association once mapped to a posting (hr_trx_job_apply -> hr_trx_jobposting.cpnyid),
        // so self-sourced figures below are portal-wide and do not respond to the group filter.
        $selfBase = $conn->table('viewselfregister')
            ->when($from, fn ($q) => $q->whereDate('viewselfregister.apply_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('viewselfregister.apply_date', '<=', $to))
            ->when($department, fn ($q) => $q->where('viewselfregister.departementid', $department))
            ->when($isCareerMode, fn ($q) => $q->whereRaw('1 = 0'));

        // ── Pipeline counts ──────────────────────────────────────────────────
        $careerCounts = (clone $careerBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $selfCandidates = (clone $selfBase)
            ->leftJoin('hr_ms_applicant as a', 'viewselfregister.applicant_id', '=', 'a.applicant_id')
            ->select(
                'a.gender as gender',
                'a.date_of_birth as date_of_birth',
                'viewselfregister.education_type as education_type',
                'a.domicile_city as domicile_city',
                DB::raw("COALESCE(NULLIF(TRIM(LOWER(a.email_address)), ''), NULLIF(TRIM(a.mobile_phone), ''), NULLIF(a.ktp_id, ''), a.applicant_id) as identity_key")
            )
            ->get()
            ->unique('identity_key')
            ->values();

        $careerCandidates = (clone $careerBase)
            ->leftJoin('hr_ms_applicant as a', 'viewtrxcareer.applicant_id', '=', 'a.applicant_id')
            ->select('a.gender as gender', 'a.date_of_birth as date_of_birth', 'a.ktp_id as ktp_id', 'viewtrxcareer.education_type as education_type', 'viewtrxcareer.applicant_id as applicant_id', 'a.domicile_city as domicile_city')
            ->get()
            ->unique(fn ($row) => $row->ktp_id && $row->date_of_birth
                ? $row->ktp_id.'|'.$row->date_of_birth
                : $row->applicant_id
            )
            ->values();

        // ── Gender ────────────────────────────────────────────────────────────
        $genderCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $genderCounts = $genderCandidates->groupBy(fn ($row) => $row->gender ?: 'Unknown')
            ->map->count();

        // ── Age ───────────────────────────────────────────────────────────────
        $ageCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $ageBuckets = array_fill_keys(self::AGE_BUCKET_LABELS, 0);
        foreach ($ageCandidates as $row) {
            if (!$row->date_of_birth) {
                continue;
            }
            $age = Carbon::parse($row->date_of_birth)->age;
            ++$ageBuckets[self::ageBucket($age)];
        }

        // ── Education ─────────────────────────────────────────────────────────
        $educationCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $educationCounts = $educationCandidates->groupBy(fn ($row) => $row->education_type ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        // ── Residential city ─────────────────────────────────────────────────
        $cityCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $rawCityCounts = $cityCandidates->groupBy(function ($row) {
            $city = trim((string) ($row->domicile_city ?? ''));

            return $city !== '' ? mb_convert_case(mb_strtolower($city), MB_CASE_TITLE) : 'Unknown';
        })->map->count()->sortDesc();

        $cityCounts = $rawCityCounts->take(6);
        $otherCityTotal = (int) $rawCityCounts->slice(6)->sum();
        if ($otherCityTotal > 0) {
            $cityCounts->put('Others', $otherCityTotal);
        }

        $totalRejected = (int) $careerCounts->get('R', 0);
        $totalJoined = (int) $careerCounts->get('C', 0);

        // ── Self-applicant rejected ──────────────────────────────────────────
        $selfRejected = (int) (clone $selfBase)->where('viewselfregister.status', 'R')->count();

        // ── Average Time-to-Hire (TTH) ────────────────────────────────────────
        $hireRows = (clone $careerBase)
            ->where('status', 'C')
            ->whereNotNull('completed_at')
            ->whereNotNull('apply_date')
            ->select('apply_date', 'completed_at')
            ->get();

        $avgTimeToHire = $hireRows->isNotEmpty()
            ? (int) round($hireRows->avg(fn ($row) => Carbon::parse($row->apply_date)->diffInDays(Carbon::parse($row->completed_at))))
            : null;

        // ── Applicant funnel totals (Job Applicant vs Self Applicant) ───────
        $careerSourced = (clone $careerBase)->count();
        $selfSourced = (clone $selfBase)->count();
        $totalApplicantAll = $careerSourced + $selfSourced;
        $totalRejectedAll = $totalRejected + $selfRejected;

        // ── Top 10 Division by candidate applied (Job Applicant vs Self Applicant) ──
        $careerDeptCounts = (clone $careerBase)
            ->whereNotNull('departementid')
            ->select('departementid', DB::raw('COUNT(*) as total'))
            ->groupBy('departementid')
            ->pluck('total', 'departementid');
        $selfDeptCounts = (clone $selfBase)
            ->whereNotNull('departementid')
            ->select('departementid', DB::raw('COUNT(*) as total'))
            ->groupBy('departementid')
            ->pluck('total', 'departementid');

        $divisionCareerTotals = [];
        $divisionSelfTotals = [];
        foreach ($careerDeptCounts as $deptId => $count) {
            $divisionId = $departmentToDivision->get($deptId, $deptId);
            $divisionCareerTotals[$divisionId] = ($divisionCareerTotals[$divisionId] ?? 0) + (int) $count;
        }
        foreach ($selfDeptCounts as $deptId => $count) {
            $divisionId = $departmentToDivision->get($deptId, $deptId);
            $divisionSelfTotals[$divisionId] = ($divisionSelfTotals[$divisionId] ?? 0) + (int) $count;
        }

        $divisionIds = collect(array_keys($divisionCareerTotals))->merge(array_keys($divisionSelfTotals))->unique();
        $divisionRows = $divisionIds->map(fn ($id) => [
            'label' => self::formatLabel($divisionNames->get($id, $id)),
            'career' => $divisionCareerTotals[$id] ?? 0,
            'self' => $divisionSelfTotals[$id] ?? 0,
            'total' => ($divisionCareerTotals[$id] ?? 0) + ($divisionSelfTotals[$id] ?? 0),
        ])
            ->sortByDesc('total')
            ->take(10)
            ->sortBy('total')
            ->values();

        $divisionLabels = $divisionRows->pluck('label')->all();
        $divisionCareerSeries = $divisionRows->pluck('career')->all();
        $divisionSelfSeries = $divisionRows->pluck('self')->all();

        // ── Applications over time ────────────────────────────────────────────
        $careerByMonth = (clone $careerBase)
            ->select(DB::raw("DATE_FORMAT(apply_date, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('apply_date')
            ->groupBy('ym')
            ->pluck('total', 'ym');
        $selfByMonth = (clone $selfBase)
            ->select(DB::raw("DATE_FORMAT(apply_date, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('apply_date')
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $monthKeys = $careerByMonth->keys()->merge($selfByMonth->keys())->unique()->sort()->values();
        $trendLabels = $monthKeys->map(fn ($ym) => Carbon::createFromFormat('Y-m', $ym)->format('M Y'));
        $trendSeries = $monthKeys->map(fn ($ym) => (int) $careerByMonth->get($ym, 0) + (int) $selfByMonth->get($ym, 0));

        // ── Hiring funnel (stage-by-stage drop-off) ───────────────────────────
        // Every applicant gets one hr_trx_job_apply_step row per master step,
        // inserted upfront as 'P'. So "reached stage N" is derived per applicant
        // as: the lowest still-pending stage (currently in progress there), or
        // the highest resolved (A/R) stage if nothing is pending anymore.
        $funnelDocids = (clone $careerBase)->pluck('docid');
        $stepRows = $conn->table('hr_trx_job_apply_step')
            ->whereIn('docid', $funnelDocids)
            ->whereIn('step_id', array_keys(self::STEP_STAGE_RANK))
            ->select('docid', 'step_id', 'status')
            ->get();

        $funnelReached = array_fill_keys(array_keys(self::FUNNEL_STAGE_LABELS), 0);
        foreach ($stepRows->groupBy('docid') as $rows) {
            $resolvedRanks = [];
            $pendingRanks = [];
            foreach ($rows as $row) {
                $rank = self::STEP_STAGE_RANK[$row->step_id] ?? null;
                if (!$rank) {
                    continue;
                }
                if ($row->status === 'A' || $row->status === 'R') {
                    $resolvedRanks[] = $rank;
                } elseif ($row->status === 'P') {
                    $pendingRanks[] = $rank;
                }
            }
            $furthest = $pendingRanks ? min($pendingRanks) : ($resolvedRanks ? max($resolvedRanks) : 0);
            $furthest = max($furthest, $resolvedRanks ? max($resolvedRanks) : 0);

            foreach (self::FUNNEL_STAGE_LABELS as $rank => $label) {
                if ($rank <= $furthest) {
                    $funnelReached[$rank]++;
                }
            }
        }

        $funnelSeries = [[
            'name' => 'Applicants',
            'data' => collect(self::FUNNEL_STAGE_LABELS)
                ->map(fn ($label, $rank) => ['x' => $label, 'y' => $funnelReached[$rank]])
                ->values()
                ->all(),
        ]];

        // ── Job posting status ────────────────────────────────────────────────
        $jobpostingQuery = Jobposting::query()
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->where('locationname', $location));
        $jobpostingCounts = (clone $jobpostingQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $postedCount = (int) $jobpostingCounts->get('P', 0);
        $unpostedCount = (int) $jobpostingCounts->get('U', 0);
        $closedCount = (int) $jobpostingCounts->get('C', 0);
        $holdCount = (int) $jobpostingCounts->get('H', 0);

        // ── PRF ───────────────────────────────────────────────────────────────
        $completedPrfIds = DB::connection('pgsql3')->table('hr_trx_prf')
            ->where('status', 'C')
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->where('locationname', $location))
            ->pluck('docid');

        $totalPrf = $completedPrfIds->count();

        $prfRows = DB::connection('pgsql3')->table('hr_trx_prf')
            ->whereIn('docid', $completedPrfIds)
            ->select('docid', 'date as prf_date')
            ->get();

        $postingStatuses = $conn->table('hr_trx_jobposting')
            ->whereIn('refid', $completedPrfIds)
            ->whereNotNull('refid')
            ->select('refid', 'status as posting_status', 'date as posting_date')
            ->get()
            ->keyBy('refid');

        $prfWithPosting = $prfRows->map(function ($prf) use ($postingStatuses) {
            $posting = $postingStatuses->get($prf->docid);

            return (object) [
                'docid' => $prf->docid,
                'prf_date' => $prf->prf_date,
                'posting_date' => $posting->posting_date ?? null,
            ];
        });

        // ── PRF Completed → Job Status turnaround ────────────────────────────
        $prfToPostingDays = [];
        foreach ($prfWithPosting as $r) {
            if ($r->posting_date && $r->prf_date) {
                $prfToPostingDays[] = Carbon::parse($r->prf_date)->diffInDays(Carbon::parse($r->posting_date));
            }
        }
        $avgPrfToPostingDays = count($prfToPostingDays) > 0
            ? round(array_sum($prfToPostingDays) / count($prfToPostingDays), 1)
            : null;

        $prfToPostingBuckets = ['<3 days' => 0, '3–7 days' => 0, '8–14 days' => 0, '15–30 days' => 0, '>30 days' => 0];
        foreach ($prfToPostingDays as $days) {
            $bucket = match (true) {
                $days < 3 => '<3 days',
                $days <= 7 => '3–7 days',
                $days <= 14 => '8–14 days',
                $days <= 30 => '15–30 days',
                default => '>30 days',
            };
            ++$prfToPostingBuckets[$bucket];
        }
        $prfToPostingLabels = array_keys($prfToPostingBuckets);
        $prfToPostingSeries = array_values($prfToPostingBuckets);

        // ── Offer Acceptance Rate ────────────────────────────────────────────
        $totalOffered = (int) (clone $careerBase)->whereIn('apply_step', [4, 5])->count();
        $offerAcceptanceRate = $totalOffered > 0 ? round(($totalJoined / $totalOffered) * 100, 1) : 0;
        $offerDeclineRate = $totalOffered > 0 ? round((($totalOffered - $totalJoined) / $totalOffered) * 100, 1) : 0;

        $applicantType = $source ?: 'all';

        return view('pages.recruitment.dashboard', [
            'applicantType' => $applicantType,
            'filters' => [
                'from' => $from, 'to' => $to, 'department' => $department,
                'company' => $company, 'location' => $location, 'source' => $source,
                'group_cpny' => $groupCpnyFilter, 'area' => $areaFilter,
            ],
            'companyGroups' => $companyGroups,
            'areas' => $areas,
            'isGroupLocked' => $isGroupLocked,
            'userGroupCpny' => $userGroupCpny,
            'departments' => $departments,
            'companies' => $companies,
            'locations' => $locations,

            // Row 1 — Requisition & Job Status
            'totalPrf' => $totalPrf,
            'postedCount' => $postedCount,
            'unpostedCount' => $unpostedCount,
            'closedCount' => $closedCount,
            'holdCount' => $holdCount,

            // Row 2 — Applicant funnel (Job Applicant vs Self Applicant)
            'totalApplicantAll' => $totalApplicantAll,
            'careerSourced' => $careerSourced,
            'selfSourced' => $selfSourced,
            'totalRejectedAll' => $totalRejectedAll,
            'totalRejected' => $totalRejected,
            'selfRejected' => $selfRejected,
            'totalJoined' => $totalJoined,

            // Row 3 — Demographics
            'genderLabels' => $genderCounts->keys()->values()->all(),
            'genderSeries' => $genderCounts->values()->all(),
            'ageLabels' => array_keys($ageBuckets),
            'ageSeries' => array_values($ageBuckets),
            'educationLabels' => $educationCounts->keys()->values()->all(),
            'educationSeries' => $educationCounts->values()->all(),
            'cityLabels' => $cityCounts->keys()->values()->all(),
            'citySeries' => $cityCounts->values()->all(),

            // Row 4 — Top 10 Division by candidate applied
            'divisionLabels' => $divisionLabels,
            'divisionCareerSeries' => $divisionCareerSeries,
            'divisionSelfSeries' => $divisionSelfSeries,

            // Suggested additions
            'avgTimeToHire' => $avgTimeToHire,
            'offerAcceptanceRate' => $offerAcceptanceRate,
            'offerDeclineRate' => $offerDeclineRate,
            'totalOffered' => $totalOffered,
            'avgPrfToPostingDays' => $avgPrfToPostingDays,
            'prfToPostingLabels' => $prfToPostingLabels,
            'prfToPostingSeries' => $prfToPostingSeries,
            'trendLabels' => $trendLabels->all(),
            'trendSeries' => $trendSeries->all(),
            'funnelSeries' => $funnelSeries,
        ]);
    }

    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waitingApproval = collect(
            $this->approvalDashboard
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $approvalHistory = collect(
            $this->approvalDashboard
                ->approveJson($request)
                ->getData(true)['data'] ?? []
        )->count();

        $uncheckedApplicant = DB::connection('mysql3')
            ->table('viewtrxcareer')
            ->where('status', '!=', 'X')
            ->where('is_read', 'N')
            ->count();

        $selfRegister = $this->uncheckedSelfRegisterQuery()->distinct()->count('vc.id');

        return response()->json([
            'success' => true,
            'data' => [
                'waiting_approval' => $waitingApproval,
                'approval_history' => $approvalHistory,
                'unchecked_applicant' => $uncheckedApplicant,
                'self_register' => $selfRegister,
            ],
        ]);
    }

    /**
     * "New" self applicants = unread self-posting rows (sp.is_read).
     */
    protected function uncheckedSelfRegisterQuery()
    {
        return DB::connection('mysql3')
            ->table('viewselfregister as vc')
            ->leftJoin('hr_trx_selfposting as sp', function ($join) {
                $join->on('vc.id', '=', 'sp.id')
                    ->on('vc.group_cpny_id', '=', 'sp.group_cpny_id');
            })
            ->where(function ($q) {
                $q->where('sp.is_read', 'N')->orWhereNull('sp.is_read');
            })
            ->whereNotIn('vc.status', ['R', 'X']);
    }

    public function widgetWaitingApprovalJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalDashboard->waitingJson($request);
    }

    public function widgetApprovalHistoryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return $this->approvalDashboard->approveJson($request);
    }

    public function widgetApplicantJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $rows = DB::connection('mysql3')
            ->table('viewtrxcareer')
            ->select(['id', 'docid', 'fullname', 'apply_date', 'job_title', 'cpnyid', 'apply_step', 'status', 'is_read'])
            ->where('status', '!=', 'X')
            ->where('is_read', 'N')
            ->orderByDesc('apply_date')
            ->get();

        $companyNames = MsCompany::whereIn('cpny_id', $rows->pluck('cpnyid')->filter()->unique())
            ->pluck('cpny_name', 'cpny_id');

        $stepNames = DB::connection('mysql3')
            ->table('hr_ms_job_step')
            ->whereIn('step_id', $rows->pluck('apply_step')->filter()->unique())
            ->pluck('step_descr', 'step_id');

        $rows = $rows
            ->map(fn ($row) => [
                'eid' => Hashids::encode($row->id),
                'docid' => $row->docid,
                'fullname' => $row->fullname,
                'apply_date' => $row->apply_date,
                'job_title' => $row->job_title,
                'cpnyid' => $companyNames->get($row->cpnyid, $row->cpnyid),
                'apply_step' => $stepNames->get($row->apply_step, $row->apply_step),
                'status' => $row->status,
                'url' => '/showcareers',
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function widgetSelfRegisterJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $rows = $this->uncheckedSelfRegisterQuery()
            ->leftJoin('hr_ms_division as div', 'vc.division_id', '=', 'div.division_id')
            ->leftJoin('hr_ms_department as dept', 'vc.departementid', '=', 'dept.department_id')
            ->select([
                'vc.id', 'vc.docid', 'vc.fullname', 'vc.apply_date', 'vc.job_title', 'vc.group_cpny_id as cpnyid', 'vc.status',
                'div.division_name', 'dept.department_name',
            ])
            ->distinct()
            ->orderByDesc('vc.apply_date')
            ->get()
            ->map(fn ($row) => [
                'eid' => Hashids::encode($row->id),
                'docid' => $row->docid,
                'fullname' => $row->fullname,
                'apply_date' => $row->apply_date,
                'job_title' => $row->job_title,
                'cpnyid' => $row->cpnyid,
                'division' => $row->division_name,
                'department' => $row->department_name,
                'status' => $row->status,
                'url' => '/showselfregister',
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function widgetApprovalDocTypes(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waiting = collect(
            $this->approvalDashboard
                ->waitingJson($request)
                ->getData(true)['data'] ?? []
        );

        $history = collect(
            $this->approvalDashboard
                ->approveJson($request)
                ->getData(true)['data'] ?? []
        );

        $doctypes = $waiting
            ->merge($history)
            ->pluck('docid')
            ->filter()
            ->map(function ($docid) {
                preg_match('/^[A-Z]+/', $docid, $match);

                return $match[0] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $rows = \App\Models\Autonbr::query()
            ->select(['doctype', 'doctype_descr'])
            ->whereIn('doctype', $doctypes)
            ->orderBy('doctype')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
