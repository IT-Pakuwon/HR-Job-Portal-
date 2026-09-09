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
    private const AGE_BUCKET_LABELS = ['<20', '20-25', '26-30', '31-35', '36-40', '41-45', '46-50', '50+'];

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
                ? $row->ktp_id . '|' . $row->date_of_birth
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
            $ageBuckets[self::ageBucket($age)]++;
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

        $totalRejected = (int) ($careerCounts->get('R', 0));
        $totalJoined = (int) ($careerCounts->get('C', 0));

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

        // ── Job posting status ────────────────────────────────────────────────
        $jobpostingQuery = Jobposting::query()
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->where('locationname', $location));
        $jobpostingCounts = (clone $jobpostingQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $postedCount = (int) ($jobpostingCounts->get('P', 0));
        $unpostedCount = (int) ($jobpostingCounts->get('U', 0));
        $closedCount = (int) ($jobpostingCounts->get('C', 0));
        $holdCount = (int) ($jobpostingCounts->get('H', 0));

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
            $prfToPostingBuckets[$bucket]++;
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
            'topPostingLabels' => $topPostings->pluck('job_title')->all(),
            'topPostingSeries' => $topPostings->pluck('total')->all(),
        ]);
    }
}
