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

class RecruitmentDashboardController extends Controller
{
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

    private const FUNNEL_STAGES = [
        'apply_step' => [
            1 => 'Sourced',
            2 => 'Screened',
            3 => 'Interviewed',
            4 => 'Offered',
            5 => 'Hired',
        ],
    ];

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

        $isGroupLocked = !empty($userGroupCpny);

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
        $departmentNames = $departments->pluck('department_name', 'department_id');

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

        $selfBase = $conn->table('viewselfregister')
            ->when($from, fn ($q) => $q->whereDate('viewselfregister.apply_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('viewselfregister.apply_date', '<=', $to))
            ->when($department, fn ($q) => $q->where('viewselfregister.departementid', $department))
            ->when($isCareerMode, fn ($q) => $q->whereRaw('1 = 0'));

        // ── Pipeline counts (used early for Talent Pool) ───────────────────────
        $careerCounts = (clone $careerBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── KPI: Total Talent Pool ─────────────────────────────────────────────
        $selfCandidates = (clone $selfBase)
            ->leftJoin('hr_ms_applicant as a', 'viewselfregister.applicant_id', '=', 'a.applicant_id')
            ->select(
                'a.gender as gender',
                'a.date_of_birth as date_of_birth',
                'viewselfregister.education_type as education_type',
                'a.upload_cv as upload_cv',
                DB::raw("COALESCE(NULLIF(TRIM(LOWER(a.email_address)), ''), NULLIF(TRIM(a.mobile_phone), ''), NULLIF(a.ktp_id, ''), a.applicant_id) as identity_key")
            )
            ->get()
            ->unique('identity_key')
            ->values();

        $totalSelfApplicant = $selfCandidates->count();

        $careerCandidates = (clone $careerBase)
            ->leftJoin('hr_ms_applicant as a', 'viewtrxcareer.applicant_id', '=', 'a.applicant_id')
            ->select('a.gender as gender', 'a.date_of_birth as date_of_birth', 'a.ktp_id as ktp_id', 'viewtrxcareer.education_type as education_type', 'viewtrxcareer.applicant_id as applicant_id')
            ->get()
            ->unique(fn ($row) => $row->ktp_id && $row->date_of_birth
                ? $row->ktp_id . '|' . $row->date_of_birth
                : $row->applicant_id
            )
            ->values();

        $totalTalentPool = $careerCandidates->count() + $totalSelfApplicant;

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
            $ageBuckets[self::ageBucket($age)]++;
        }

        // ── Education ─────────────────────────────────────────────────────────
        $educationCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $educationCounts = $educationCandidates->groupBy(fn ($row) => $row->education_type ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        // ── Job level ─────────────────────────────────────────────────────────
        $jobLevelCounts = (clone $careerBase)
            ->select(DB::raw("COALESCE(NULLIF(job_level, ''), 'Unknown') as job_level"), DB::raw('COUNT(*) as total'))
            ->groupBy('job_level')
            ->orderByDesc('total')
            ->pluck('total', 'job_level');

        // ── Division ──────────────────────────────────────────────────────────
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

        $divisionTotals = [];
        foreach ($careerDeptCounts->keys()->merge($selfDeptCounts->keys())->unique() as $deptId) {
            $divisionId = $departmentToDivision->get($deptId, $deptId);
            $divisionTotals[$divisionId] = ($divisionTotals[$divisionId] ?? 0)
                + (int) $careerDeptCounts->get($deptId, 0) + (int) $selfDeptCounts->get($deptId, 0);
        }

        $divisionCounts = collect($divisionTotals)
            ->map(fn ($total, $id) => ['label' => $divisionNames->get($id, $id), 'total' => $total])
            ->sortByDesc('total')
            ->take(10)
            ->sortBy('total')
            ->values();

        $totalApplied = (int) $careerCounts->sum();
        $totalRejected = (int) ($careerCounts->get('R', 0));
        $totalJoined = (int) ($careerCounts->get('C', 0));
        $totalCandidate = (int) ($careerCounts->get('P', 0));
        $totalNoCandidate = (int) ($careerCounts->get('H', 0));

        // ── Average Time-to-Hire (TTH) ────────────────────────────────────────
        $hireRows = (clone $careerBase)
            ->where('status', 'C')
            ->whereNotNull('completed_at')
            ->whereNotNull('apply_date')
            ->whereNotNull('departementid')
            ->select('apply_date', 'completed_at', 'departementid')
            ->get();

        $avgTimeToHire = $hireRows->isNotEmpty()
            ? (int) round($hireRows->avg(fn ($row) => Carbon::parse($row->apply_date)->diffInDays(Carbon::parse($row->completed_at))))
            : null;

        $tthByDept = $hireRows->groupBy('departementid')->map(function ($rows, $deptId) use ($departmentNames) {
            $avg = (int) round($rows->avg(fn ($r) => Carbon::parse($r->apply_date)->diffInDays(Carbon::parse($r->completed_at))));
            return ['dept' => $departmentNames->get($deptId, $deptId), 'days' => $avg, 'hires' => $rows->count()];
        })->sortBy('days')->values();

        // ── Time-in-stage bottlenecks ─────────────────────────────────────────
        $stageTimings = (clone $careerBase)
            ->whereNotNull('apply_date')
            ->whereIn('status', ['P', 'C'])
            ->select('docid', 'apply_date', 'apply_step', 'completed_at', 'status')
            ->get();

        $timeToStage = [];
        foreach ($stageTimings as $row) {
            $stage = (int) ($row->apply_step ?? 0);
            if ($stage > 0 && $row->apply_date) {
                $stageName = self::FUNNEL_STAGES['apply_step'][$stage] ?? "Stage $stage";
                $refDate = $row->status === 'C' && $row->completed_at ? $row->completed_at : now();
                $days = Carbon::parse($row->apply_date)->diffInDays(Carbon::parse($refDate));
                if (!isset($timeToStage[$stageName])) {
                    $timeToStage[$stageName] = [];
                }
                $timeToStage[$stageName][] = $days;
            }
        }

        $stageVelocity = collect($timeToStage)->map(fn ($days, $stage) => [
            'stage' => $stage,
            'avgDays' => count($days) > 0 ? (int) round(array_sum($days) / count($days)) : 0,
            'candidates' => count($days),
        ])->values();

        // ── Funnel conversion rates ───────────────────────────────────────────
        $stageCounts = [];
        if ($totalApplied > 0) {
            $stageCounts['Applied'] = $totalApplied;
            $stageCounts['No Candidate'] = $totalNoCandidate;
            $stageCounts['Candidate'] = $totalCandidate;
            $stageCounts['Joined'] = $totalJoined;
            $stageCounts['Rejected'] = $totalRejected;
        }

        $funnelConversion = [];
        $stages = ['Applied', 'No Candidate', 'Candidate', 'Joined'];
        for ($i = 0; $i < count($stages) - 1; $i++) {
            $current = $stages[$i];
            $next = $stages[$i + 1];
            $fromCount = $stageCounts[$current] ?? 0;
            $toCount = $stageCounts[$next] ?? 0;
            $funnelConversion[] = [
                'stage' => $current,
                'applicants' => $fromCount,
                'converted' => $toCount,
                'rate' => $fromCount > 0 ? round(($toCount / $fromCount) * 100, 1) : 0,
                'dropRate' => $fromCount > 0 ? round((($fromCount - $toCount) / $fromCount) * 100, 1) : 0,
            ];
        }

        // ── Sourcing efficiency ───────────────────────────────────────────────
        $careerSourced = (clone $careerBase)->count();
        $selfSourced = (clone $selfBase)->count();
        $totalSourced = $careerSourced + $selfSourced;

        $careerJoined = (clone $careerBase)->where('status', 'C')->count();
        $selfJoined = 0;

        $sourceEfficiency = [
            ['source' => 'Job Portal (Career)', 'applied' => $careerSourced, 'hired' => $careerJoined, 'conversion' => $careerSourced > 0 ? round(($careerJoined / $careerSourced) * 100, 1) : 0],
            ['source' => 'Self Registration', 'applied' => $selfSourced, 'hired' => $selfJoined, 'conversion' => $selfSourced > 0 ? round(($selfJoined / $selfSourced) * 100, 1) : 0],
        ];

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

        $trendMoM = [];
        $prev = null;
        foreach ($monthKeys as $i => $ym) {
            $val = (int) $careerByMonth->get($ym, 0) + (int) $selfByMonth->get($ym, 0);
            if ($prev !== null && $prev > 0) {
                $trendMoM[] = round((($val - $prev) / $prev) * 100, 1);
            } else {
                $trendMoM[] = 0;
            }
            $prev = $val;
        }

        // ── Top postings ──────────────────────────────────────────────────────
        $topPostings = (clone $careerBase)
            ->whereNotNull('docidposting')
            ->select('docidposting', DB::raw('MIN(job_title) as job_title'), DB::raw('COUNT(*) as total'))
            ->groupBy('docidposting')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->sortBy('total')
            ->values();

        // ── Posting status ────────────────────────────────────────────────────
        $jobpostingQuery = Jobposting::query()
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->where('locationname', $location));
        $jobpostingCounts = (clone $jobpostingQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalJobposting = (int) $jobpostingCounts->sum();
        $postedCount = (int) ($jobpostingCounts->get('P', 0));
        $unpostedCount = (int) ($jobpostingCounts->get('U', 0));

        // ── PRF ───────────────────────────────────────────────────────────────
        $completedPrfIds = DB::connection('pgsql3')->table('hr_trx_prf')
            ->where('status', 'C')
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
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
                'posting_status' => $posting->posting_status ?? null,
                'posting_date' => $posting->posting_date ?? null,
            ];
        });

        $totalPrfPosted = $prfWithPosting->filter(fn ($r) => $r->posting_status === 'P')->count();
        $totalPrfUnposted = $prfWithPosting->filter(fn ($r) => $r->posting_status === 'U' || $r->posting_status === null)->count();

        $prfAgingLabels = [];
        $prfAgingSeries = [];
        $agingBuckets = ['< 7 days' => 0, '7–14 days' => 0, '15–30 days' => 0, '31–60 days' => 0, '> 60 days' => 0];
        foreach ($prfWithPosting as $r) {
            $isUnposted = $r->posting_status === 'U' || $r->posting_status === null;
            if (!$isUnposted || !$r->prf_date) {
                continue;
            }
            $age = Carbon::parse($r->prf_date)->diffInDays(now());
            $bucket = match (true) {
                $age <= 7 => '< 7 days',
                $age <= 14 => '7–14 days',
                $age <= 30 => '15–30 days',
                $age <= 60 => '31–60 days',
                default => '> 60 days',
            };
            $agingBuckets[$bucket]++;
        }
        $prfAgingLabels = array_keys($agingBuckets);
        $prfAgingSeries = array_values($agingBuckets);

        // ── Offer Acceptance Rate ────────────────────────────────────────────
        $totalOffered = (int) (clone $careerBase)->whereIn('apply_step', [4, 5])->count();
        $offerAcceptanceRate = $totalOffered > 0 ? round(($totalJoined / $totalOffered) * 100, 1) : 0;
        $offerDeclineRate = $totalOffered > 0 ? round((($totalOffered - $totalJoined) / $totalOffered) * 100, 1) : 0;

        // ── Area ──────────────────────────────────────────────────────────────
        $cpnyAreaMap = MsCompany::where('status', 'A')
            ->whereNotNull('area_id')
            ->where('area_id', '!=', '')
            ->pluck('area_id', 'cpny_id');

        $careerByCpny = (clone $careerBase)
            ->whereNotNull('cpnyid')
            ->select('cpnyid', DB::raw('COUNT(*) as total'))
            ->groupBy('cpnyid')
            ->pluck('total', 'cpnyid');

        $areaCounts = [];
        foreach ($careerByCpny as $cpnyId => $count) {
            $area = $cpnyAreaMap->get($cpnyId, 'Unknown');
            $areaCounts[$area] = ($areaCounts[$area] ?? 0) + $count;
        }
        arsort($areaCounts);
        $areaLabels = array_keys($areaCounts);
        $areaSeries = array_values($areaCounts);

        // ── Candidates by posting status ──────────────────────────────────────
        $candidatesByPostingStatusCounts = (clone $careerBase)
            ->join('hr_trx_jobposting as jp', 'viewtrxcareer.docidposting', '=', 'jp.docid')
            ->select('jp.status as status', DB::raw('COUNT(*) as total'))
            ->groupBy('jp.status')
            ->pluck('total', 'status');

        $candidatesByPostingStatus = collect(self::JOBPOSTING_STATUS_LABELS)
            ->map(fn ($label, $code) => [
                'code' => $code,
                'label' => $label,
                'count' => (int) ($candidatesByPostingStatusCounts->get($code, 0)),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();

        // ── Active Requisitions ──────────────────────────────────────────────
        $activeRequisitions = (clone $jobpostingQuery)->whereIn('status', ['P', 'U'])->count();

        // ── Sourcing channel donut ────────────────────────────────────────────
        $sourcingLabels = ['Job Portal', 'Self Registration'];
        $sourcingSeries = [$careerSourced, $selfSourced];

        // ── Self Applicant Talent Pool Analytics ─────────────────────────────
        $talentPoolGrowthLabels = [];
        $talentPoolGrowthSeries = [];
        $profileCompletenessRate = 0;
        $totalUnassignedCandidates = 0;
        $totalMatchedCandidates = 0;
        $selfByDeptLabels = [];
        $selfByDeptSeries = [];

        if ($isSelfMode || !$isCareerMode) {
            $selfGrowth = (clone $selfBase)
                ->select(DB::raw("DATE_FORMAT(apply_date, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('apply_date')
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $growthKeys = $selfGrowth->keys()->sort()->values();
            $talentPoolGrowthLabels = $growthKeys->map(fn ($ym) => Carbon::createFromFormat('Y-m', $ym)->format('M Y'))->all();
            $talentPoolGrowthSeries = $growthKeys->map(fn ($ym) => (int) $selfGrowth->get($ym, 0))->all();

            $totalSelf = $selfCandidates->count();
            $completedProfile = $selfCandidates->filter(fn ($r) => !empty($r->upload_cv))->count();
            $profileCompletenessRate = $totalSelf > 0 ? round(($completedProfile / $totalSelf) * 100, 1) : 0;

            $selfDeptData = (clone $selfBase)
                ->whereNotNull('departementid')
                ->select('departementid', DB::raw('COUNT(*) as total'))
                ->groupBy('departementid')
                ->pluck('total', 'departementid')
                ->sortDesc()
                ->take(10);

            $selfByDeptLabels = $selfDeptData->keys()->map(fn ($id) => $departmentNames->get($id, $id))->all();
            $selfByDeptSeries = $selfDeptData->values()->all();

            $selfDocids = (clone $selfBase)->pluck('docid');
            $totalUnassignedCandidates = $conn->table('hr_trx_job_apply')
                ->whereIn('docid', $selfDocids)
                ->where('status', '!=', 'X')
                ->distinct('docid')
                ->count();
            $totalUnassignedCandidates = $totalSelf - $totalUnassignedCandidates;
            if ($totalUnassignedCandidates < 0) {
                $totalUnassignedCandidates = $totalSelf;
            }
            $totalMatchedCandidates = $totalSelf - $totalUnassignedCandidates;
        }

        // ── Departmental KPI ──────────────────────────────────────────────────
        $topDeptApplicants = (clone $careerBase)
            ->whereNotNull('departementid')
            ->select('departementid', DB::raw('COUNT(*) as total'))
            ->groupBy('departementid')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $departmentNames->get($row->departementid, $row->departementid),
                'total' => (int) $row->total,
            ])
            ->values();

        $deptApplicantLabels = $topDeptApplicants->pluck('label')->all();
        $deptApplicantSeries = $topDeptApplicants->pluck('total')->all();

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

            // KPI metrics
            'totalTalentPool' => $totalTalentPool,
            'totalSelfApplicant' => $totalSelfApplicant,
            'profileCompletenessRate' => $profileCompletenessRate,
            'totalUnassignedCandidates' => $totalUnassignedCandidates,
            'totalMatchedCandidates' => $totalMatchedCandidates,
            'talentPoolGrowthLabels' => $talentPoolGrowthLabels,
            'talentPoolGrowthSeries' => $talentPoolGrowthSeries,
            'selfByDeptLabels' => $selfByDeptLabels,
            'selfByDeptSeries' => $selfByDeptSeries,
            'totalApplied' => $totalApplied,
            'totalRejected' => $totalRejected,
            'totalJoined' => $totalJoined,
            'totalCandidate' => $totalCandidate,
            'totalNoCandidate' => $totalNoCandidate,
            'avgTimeToHire' => $avgTimeToHire,
            'activeRequisitions' => $activeRequisitions,
            'offerAcceptanceRate' => $offerAcceptanceRate,
            'offerDeclineRate' => $offerDeclineRate,
            'totalOffered' => $totalOffered,
            'totalJobposting' => $totalJobposting,
            'postedCount' => $postedCount,
            'unpostedCount' => $unpostedCount,
            'totalPrf' => $totalPrf,
            'totalPrfPosted' => $totalPrfPosted,
            'totalPrfUnposted' => $totalPrfUnposted,

            // Demographics
            'genderLabels' => $genderCounts->keys()->values()->all(),
            'genderSeries' => $genderCounts->values()->all(),
            'ageLabels' => array_keys($ageBuckets),
            'ageSeries' => array_values($ageBuckets),
            'educationLabels' => $educationCounts->keys()->values()->all(),
            'educationSeries' => $educationCounts->values()->all(),
            'jobLevelLabels' => $jobLevelCounts->keys()->values()->all(),
            'jobLevelSeries' => $jobLevelCounts->values()->all(),

            // Divisions & Departments
            'divisionLabels' => $divisionCounts->pluck('label')->all(),
            'divisionSeries' => $divisionCounts->pluck('total')->all(),
            'deptApplicantLabels' => $deptApplicantLabels,
            'deptApplicantSeries' => $deptApplicantSeries,

            // Trends
            'trendLabels' => $trendLabels->all(),
            'trendSeries' => $trendSeries->all(),
            'trendMoM' => $trendMoM,

            // Velocity
            'tthByDept' => $tthByDept,
            'stageVelocity' => $stageVelocity,

            // PRF Aging
            'prfAgingLabels' => $prfAgingLabels,
            'prfAgingSeries' => $prfAgingSeries,

            // Top postings
            'topPostingLabels' => $topPostings->pluck('job_title')->all(),
            'topPostingSeries' => $topPostings->pluck('total')->all(),

            // Area
            'areaLabels' => $areaLabels,
            'areaSeries' => $areaSeries,

            // Funnel
            'funnelSeries' => [
                ['x' => 'Applied', 'y' => $totalApplied],
                ['x' => 'No Candidate', 'y' => $totalNoCandidate],
                ['x' => 'Candidate', 'y' => $totalCandidate],
                ['x' => 'Joined', 'y' => $totalJoined],
            ],
            'funnelConversion' => $funnelConversion,

            // Sourcing
            'sourceEfficiency' => $sourceEfficiency,
            'sourcingLabels' => $sourcingLabels,
            'sourcingSeries' => $sourcingSeries,

            // Posting status
            'jobpostingLabels' => $candidatesByPostingStatus->pluck('label')->all(),
            'jobpostingSeries' => $candidatesByPostingStatus->pluck('count')->all(),
        ]);
    }
}
