<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Jobposting;
use App\Models\MsCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    // Display names for the same ranks in the "Avg Time to Stage" timeline —
    // rank 1 (JOAPHC) is HC's screening decision, not the apply moment itself,
    // so it needs a distinct label there (unlike the funnel chart above, where
    // "reached rank 1" ~= "has an application at all" and "Applied" fits fine).
    private const STAGE_TIMING_LABELS = [
        1 => 'HC Screening', 2 => 'HC Review', 3 => 'Interview',
        4 => 'Psycho Test', 5 => 'Offering', 6 => 'Hired',
    ];

    private static function formatLabel(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? mb_convert_case(mb_strtolower($value), MB_CASE_TITLE) : $value;
    }

    /**
     * Groups a free-text "city" field into counts, folding case-only
     * variants ("jakarta selatan" vs "Jakarta Selatan") and full-address
     * entries that merely *mention* a real city ("Kebayoran Lama Jakarta
     * Selatan") into that city instead of letting them fork into their own
     * one-off noise buckets.
     *
     * Pass 1: title-case every value and count exact matches — this finds
     * the clean, frequently-used city labels ("Jakarta Selatan" x56).
     * Pass 2: treat those clean labels (2+ occurrences, plausible city-name
     * length) as anchors, longest first, and fold any other value that
     * *contains* an anchor into it. Values matching no anchor are kept as-is.
     */
    private static function normalizeCityCounts(\Illuminate\Support\Collection $rawCities): \Illuminate\Support\Collection
    {
        return self::normalizeCityLabels($rawCities)->countBy()->sortDesc();
    }

    /**
     * Same folding as normalizeCityCounts(), but returns the per-row resolved
     * label (keys preserved) instead of collapsing to aggregate counts — used
     * where callers need to know which bucket a specific candidate landed in
     * (e.g. exporting the "Others" rows).
     */
    private static function normalizeCityLabels(\Illuminate\Support\Collection $rawCities): \Illuminate\Support\Collection
    {
        $titled = $rawCities->map(fn ($city) => self::formatLabel($city) ?: 'Unknown');

        $anchors = $titled->countBy()
            ->filter(fn ($count, $label) => $label !== 'Unknown' && $count >= 2 && mb_strlen($label) >= 4 && mb_strlen($label) <= 24)
            ->keys()
            ->sortByDesc(fn ($label) => mb_strlen($label))
            ->values();

        return $titled->map(function ($label) use ($anchors) {
            if ($anchors->contains($label)) {
                return $label;
            }
            $haystack = mb_strtolower($label);
            foreach ($anchors as $anchor) {
                if (str_contains($haystack, mb_strtolower($anchor))) {
                    return $anchor;
                }
            }

            return $label;
        });
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
        $division = $request->query('division');

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

        $divisions = $conn->table('hr_ms_division')
            ->where('status', 'A')
            ->orderBy('division_name')
            ->get(['division_id', 'division_name']);

        $divisionDepartmentIds = $division
            ? $departmentToDivision->filter(fn ($divId) => $divId === $division)->keys()->all()
            : null;

        $postingCompanyQuery = $conn->table('hr_trx_jobposting')->whereNotNull('cpnyid');
        if ($filterCompanyIds) {
            $postingCompanyQuery->whereIn('cpnyid', $filterCompanyIds);
        }
        $postingCompanyIds = $postingCompanyQuery->distinct()->pluck('cpnyid');
        // MsCompany (pgsql2) is the current, complete company master — the legacy
        // mysql2 `company` table (used here previously) is missing rows for some
        // active cpnyids (e.g. O88, PRB), which made their names fall back to the
        // raw ID in the filter dropdown.
        $companyNames = MsCompany::whereIn('cpny_id', $postingCompanyIds)->pluck('cpny_name', 'cpny_id');
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
            ->when($divisionDepartmentIds, fn ($q) => $q->whereIn('viewtrxcareer.departementid', $divisionDepartmentIds))
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
            ->when($divisionDepartmentIds, fn ($q) => $q->whereIn('viewselfregister.departementid', $divisionDepartmentIds))
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
                'a.source_information as source_information',
                DB::raw("COALESCE(NULLIF(TRIM(LOWER(a.email_address)), ''), NULLIF(TRIM(a.mobile_phone), ''), NULLIF(a.ktp_id, ''), a.applicant_id) as identity_key")
            )
            ->get()
            ->unique('identity_key')
            ->values();

        $careerCandidates = (clone $careerBase)
            ->leftJoin('hr_ms_applicant as a', 'viewtrxcareer.applicant_id', '=', 'a.applicant_id')
            ->select('a.gender as gender', 'a.date_of_birth as date_of_birth', 'a.ktp_id as ktp_id', 'viewtrxcareer.education_type as education_type', 'viewtrxcareer.applicant_id as applicant_id', 'a.domicile_city as domicile_city', 'a.source_information as source_information')
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
        // domicile_city is free text — case varies ("jakarta selatan" vs "Jakarta
        // Selatan") and some applicants typed a full address instead of just a
        // city ("Kebayoran Lama Jakarta Selatan"), which would otherwise fork
        // into their own noise bucket instead of counting toward the real city.
        $cityCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $rawCityCounts = self::normalizeCityCounts($cityCandidates->pluck('domicile_city'));

        $cityCounts = $rawCityCounts->take(10);
        $otherCityTotal = (int) $rawCityCounts->slice(10)->sum();
        if ($otherCityTotal > 0) {
            $cityCounts->put('Others', $otherCityTotal);
        }

        // ── Hiring source ("how did you hear about us") ───────────────────────
        // Mostly unfilled (a large "Unknown" share is expected) — the chart only
        // plots the identified channels so a handful of real answers aren't
        // dwarfed to invisibility next to the unrecorded bucket.
        $sourceCandidates = $isSelfMode ? $selfCandidates : $careerCandidates;
        $sourceCounts = $sourceCandidates->groupBy(fn ($row) => trim((string) ($row->source_information ?? '')) ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        $sourceTotal = $sourceCounts->sum();
        $unknownSourceCount = (int) $sourceCounts->get('Unknown', 0);
        $unknownSourcePct = $sourceTotal > 0 ? round($unknownSourceCount / $sourceTotal * 100, 1) : 0;

        $knownSourceCounts = $sourceCounts->except('Unknown');
        $topSourceLabel = $knownSourceCounts->keys()->first();
        $topSourceCount = (int) ($knownSourceCounts->first() ?? 0);

        // ── Demographics insight (for the section summary) ────────────────────
        $genderTotal = $genderCounts->sum();
        $topGenderLabel = $genderCounts->sortDesc()->keys()->first();
        $topGenderPct = $genderTotal > 0 ? round($genderCounts->get($topGenderLabel, 0) / $genderTotal * 100, 1) : 0;

        $ageTotal = array_sum($ageBuckets);
        $topAgeLabel = collect($ageBuckets)->sortDesc()->keys()->first();
        $topAgePct = $ageTotal > 0 ? round(($ageBuckets[$topAgeLabel] ?? 0) / $ageTotal * 100, 1) : 0;

        $educationTotal = $educationCounts->sum();
        $unknownEducationPct = $educationTotal > 0 ? round($educationCounts->get('Unknown', 0) / $educationTotal * 100, 1) : 0;

        $topCityLabel = $cityCounts->keys()->first();
        $topCityCount = (int) ($cityCounts->first() ?? 0);

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

        $topDivisionRow = $divisionRows->last();
        $topDivisionShare = $totalApplicantAll > 0 && $topDivisionRow
            ? round($topDivisionRow['total'] / $totalApplicantAll * 100, 1)
            : 0;

        $divisionLabels = $divisionRows->pluck('label')->all();
        $divisionCareerSeries = $divisionRows->pluck('career')->all();
        $divisionSelfSeries = $divisionRows->pluck('self')->all();

        // ── Total candidate applied per job (career/job-posting applicants only —
        //    self registrations aren't tied to a specific job until later mapped) ──
        $jobApplyCounts = (clone $careerBase)
            ->whereNotNull('viewtrxcareer.docidposting')
            ->select('viewtrxcareer.docidposting', 'viewtrxcareer.job_title', 'viewtrxcareer.departementid', DB::raw('COUNT(*) as total'))
            ->groupBy('viewtrxcareer.docidposting', 'viewtrxcareer.job_title', 'viewtrxcareer.departementid')
            ->get();

        $jobPostingMeta = $conn->table('hr_trx_jobposting')
            ->whereIn('docid', $jobApplyCounts->pluck('docidposting'))
            ->select('docid', 'status', 'job_level', 'cpnyid')
            ->get()
            ->keyBy('docid');

        $departmentNames = $departments->pluck('department_name', 'department_id');
        $jobStatusLabels = ['P' => 'Posted', 'U' => 'Unposted', 'C' => 'Closed', 'H' => 'Hold'];

        $totalJobApplied = (int) $jobApplyCounts->sum('total');

        $jobApplyRows = $jobApplyCounts->map(fn ($row) => [
            'job_title' => $row->job_title ?: '(Untitled)',
            'department' => self::formatLabel($departmentNames->get($row->departementid, '-')),
            'status' => $jobStatusLabels[$jobPostingStatusMap->get($row->docidposting)] ?? '-',
            'total' => (int) $row->total,
            'pct' => $totalJobApplied > 0 ? round($row->total / $totalJobApplied * 100, 1) : 0,
        ])
            ->sortByDesc('total')
            ->values();

        $topJobRow = $jobApplyRows->first();

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
            ->select('docid', 'step_id', 'status', 'aprvuserdate')
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
                    ++$funnelReached[$rank];
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

        // ── Avg time (days) from Applied to each funnel stage ─────────────────
        // For each applicant, take the latest completed_at per stage rank (a rank
        // can have several step_ids — e.g. HC vs User interview — that both
        // resolve the same stage), then chain: apply_date -> rank1 -> rank2 -> ...
        // Only consecutive, actually-reached checkpoints count toward a transition,
        // so a candidate stuck before reaching a stage doesn't skew its average.
        $applyDates = (clone $careerBase)->pluck('apply_date', 'docid');

        $stageReachedAt = [];
        foreach ($stepRows as $row) {
            if (!in_array($row->status, ['A', 'R'], true) || !$row->aprvuserdate) {
                continue;
            }
            $rank = self::STEP_STAGE_RANK[$row->step_id] ?? null;
            if (!$rank) {
                continue;
            }
            $ts = Carbon::parse($row->aprvuserdate);
            if (!isset($stageReachedAt[$row->docid][$rank]) || $ts->greaterThan($stageReachedAt[$row->docid][$rank])) {
                $stageReachedAt[$row->docid][$rank] = $ts;
            }
        }

        $stageTransitionDays = array_fill_keys(array_keys(self::FUNNEL_STAGE_LABELS), []);
        foreach ($stageReachedAt as $docid => $ranks) {
            $prevTs = isset($applyDates[$docid]) ? Carbon::parse($applyDates[$docid]) : null;
            foreach (array_keys(self::FUNNEL_STAGE_LABELS) as $rank) {
                if (!isset($ranks[$rank])) {
                    $prevTs = null;
                    continue;
                }
                if ($prevTs) {
                    $days = $prevTs->diffInDays($ranks[$rank], false);
                    if ($days >= 0) {
                        $stageTransitionDays[$rank][] = $days;
                    }
                }
                $prevTs = $ranks[$rank];
            }
        }

        // "Applied" is the true day-0 anchor (the raw apply_date) — it's charted
        // as its own point so the line visibly starts at 0, not at the first
        // stage's already-elapsed average. Every rank after it uses
        // STAGE_TIMING_LABELS, since rank 1 (JOAPHC) here means "HC screened
        // this application", a distinct event from the apply moment itself.
        $stageTimingLabels = ['Applied', ...array_values(self::STAGE_TIMING_LABELS)];
        $stageTimingCumulative = [0];
        $cumulative = 0;
        $bottleneckStage = null;
        $bottleneckDays = 0;
        $prevLabelForBottleneck = 'Applied';
        foreach (self::STAGE_TIMING_LABELS as $rank => $label) {
            $days = $stageTransitionDays[$rank];
            $avg = count($days) > 0 ? round(array_sum($days) / count($days), 1) : null;
            $cumulative += $avg ?? 0;
            $stageTimingCumulative[] = round($cumulative, 1);
            if (($avg ?? 0) > $bottleneckDays) {
                $bottleneckDays = $avg;
                $bottleneckStage = $prevLabelForBottleneck.' → '.$label;
            }
            $prevLabelForBottleneck = $label;
        }
        $totalHireDays = end($stageTimingCumulative) ?: null;

        // ── Job posting status ────────────────────────────────────────────────
        $jobpostingQuery = Jobposting::query()
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($divisionDepartmentIds, fn ($q) => $q->whereIn('departementid', $divisionDepartmentIds))
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
        $totalJobPostings = $postedCount + $unpostedCount + $closedCount + $holdCount;
        $postedSharePct = $totalJobPostings > 0 ? round($postedCount / $totalJobPostings * 100, 1) : 0;

        // ── PRF ───────────────────────────────────────────────────────────────
        $completedPrfIds = DB::connection('pgsql3')->table('hr_trx_prf')
            ->where('status', 'C')
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($divisionDepartmentIds, fn ($q) => $q->whereIn('departementid', $divisionDepartmentIds))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($filterCompanyIds, fn ($q) => $q->whereIn('cpnyid', $filterCompanyIds))
            ->when($location, fn ($q) => $q->where('locationname', $location))
            ->pluck('docid');

        $totalPrf = $completedPrfIds->count();

        $prfRows = DB::connection('pgsql3')->table('hr_trx_prf')
            ->whereIn('docid', $completedPrfIds)
            ->select('docid', 'date as prf_date', 'job_title', 'job_level', 'cpnyid')
            ->get();

        $postingStatuses = $conn->table('hr_trx_jobposting')
            ->whereIn('refid', $completedPrfIds)
            ->whereNotNull('refid')
            ->select('refid', 'status as posting_status', 'date as posting_date')
            ->get()
            ->keyBy('refid');

        $prfCompanyNames = MsCompany::whereIn('cpny_id', $prfRows->pluck('cpnyid')->filter()->unique())
            ->pluck('cpny_name', 'cpny_id');

        $prfWithPosting = $prfRows->map(function ($prf) use ($postingStatuses) {
            $posting = $postingStatuses->get($prf->docid);

            return (object) [
                'docid' => $prf->docid,
                'prf_date' => $prf->prf_date,
                'posting_date' => $posting->posting_date ?? null,
                'job_title' => $prf->job_title,
                'job_level' => $prf->job_level,
                'cpnyid' => $prf->cpnyid,
            ];
        });

        // ── PRF Completed → Job Status turnaround ────────────────────────────
        // NOTE: "posting_date" is when the resulting job posting was *created*
        // (hr_trx_jobposting.date), not a true closure date — hr_trx_jobposting
        // has a jobclosing_date column but it is unused/unpopulated across the
        // app today, so PRF-completed-to-posted is the closest turnaround signal
        // currently available.
        $prfToPostingDays = [];
        $prfTurnaroundRows = [];
        foreach ($prfWithPosting as $r) {
            if ($r->posting_date && $r->prf_date) {
                $days = Carbon::parse($r->prf_date)->diffInDays(Carbon::parse($r->posting_date));
                $prfToPostingDays[] = $days;
                $title = $r->job_title ?: '(Untitled)';
                $prfTurnaroundRows[] = [
                    'prf' => $r->docid,
                    'job_title' => $r->job_level ? $title.' - '.$r->job_level : $title,
                    'company' => $prfCompanyNames->get($r->cpnyid, $r->cpnyid),
                    'total' => $days,
                ];
            }
        }
        $avgPrfToPostingDays = count($prfToPostingDays) > 0
            ? round(array_sum($prfToPostingDays) / count($prfToPostingDays), 1)
            : null;
        usort($prfTurnaroundRows, fn ($a, $b) => $b['total'] <=> $a['total']);

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

        // ── Full-dashboard summary insight (one consolidated read of everything
        //    above, in the same {type, text} shape GM report's renderInsights()
        //    expects — type drives which icon/color the panel shows). ─────────
        $fullInsights = [];

        $fullInsights[] = [
            'type' => $unpostedCount > 0 ? 'warning' : 'info',
            'text' => $postedSharePct.'% of requisitions are already live on the career site (<b>'
                .number_format($postedCount).'</b> of '.number_format($totalJobPostings).')'
                .($unpostedCount > 0 ? ' — <b>'.number_format($unpostedCount).'</b> approved but not yet posted' : '').'.',
        ];

        $rejectedPct = $totalApplicantAll > 0 ? round($totalRejectedAll / $totalApplicantAll * 100, 1) : 0;
        $hiredPct = $totalApplicantAll > 0 ? round($totalJoined / $totalApplicantAll * 100, 1) : 0;
        $fullInsights[] = [
            'type' => $rejectedPct >= 60 ? 'warning' : 'info',
            'text' => number_format($totalApplicantAll).' candidates have applied so far — <b>'.$rejectedPct.'%</b> get rejected'
                .' and only <b>'.$hiredPct.'%</b> are ultimately hired'
                .($applicantType !== 'self' && $avgTimeToHire !== null ? ', averaging <b>'.$avgTimeToHire.' days</b> from apply to join' : '').'.',
        ];

        $fullInsights[] = [
            'type' => 'info',
            'text' => '<b>'.$topGenderPct.'% '.$topGenderLabel.'</b>, mostly aged <b>'.$topAgeLabel.'</b> ('.$topAgePct.'%), and <b>'
                .$topCityLabel.'</b> leads by location ('.number_format($topCityCount).' candidates).',
        ];

        $fullInsights[] = [
            'type' => 'warning',
            'text' => 'Education level is unrecorded for <b>'.$unknownEducationPct.'%</b> of applicants, and the hiring-source field for <b>'
                .$unknownSourcePct.'%</b>'
                .($topSourceLabel ? ' — of those recorded, <b>'.$topSourceLabel.'</b> leads with '.number_format($topSourceCount).' candidates' : '').'.',
        ];

        if ($applicantType !== 'self' && $topDivisionRow) {
            $fullInsights[] = [
                'type' => $topDivisionShare >= 50 ? 'warning' : 'info',
                'text' => '<b>'.$topDivisionRow['label'].'</b> draws the most interest (<b>'.$topDivisionShare.'%</b> of applicants) — postings typically close '
                    .($avgPrfToPostingDays ?? '—').' days after their PRF is completed.',
            ];
        }

        if ($applicantType !== 'self' && $topJobRow) {
            $fullInsights[] = [
                'type' => $topJobRow['status'] === 'Hold' ? 'critical' : 'info',
                'text' => '<b>'.$topJobRow['job_title'].'</b> alone draws <b>'.$topJobRow['pct'].'%</b> of all job applications ('
                    .number_format($topJobRow['total']).' candidates)'
                    .($topJobRow['status'] === 'Hold' ? ' — even though that posting is currently <b>on Hold</b>' : '').'.',
            ];
        }

        if ($applicantType !== 'self' && $bottleneckStage) {
            $fullInsights[] = [
                'type' => 'warning',
                'text' => '<b>'.$bottleneckStage.'</b> is the biggest bottleneck in the hiring funnel, adding <b>'.$bottleneckDays.' days</b> on average'
                    .($totalHireDays ? ' — the full apply-to-hire journey averages <b>'.$totalHireDays.' days</b>' : '').'.',
            ];
        }

        return view('pages.recruitment.dashboard', [
            'applicantType' => $applicantType,
            'filters' => [
                'from' => $from, 'to' => $to, 'department' => $department,
                'company' => $company, 'location' => $location, 'source' => $source,
                'group_cpny' => $groupCpnyFilter, 'area' => $areaFilter, 'division' => $division,
            ],
            'companyGroups' => $companyGroups,
            'areas' => $areas,
            'isGroupLocked' => $isGroupLocked,
            'userGroupCpny' => $userGroupCpny,
            'departments' => $departments,
            'divisions' => $divisions,
            'companies' => $companies,
            'locations' => $locations,

            // Row 1 — Requisition & Job Status
            'totalPrf' => $totalPrf,
            'postedCount' => $postedCount,
            'unpostedCount' => $unpostedCount,
            'closedCount' => $closedCount,
            'holdCount' => $holdCount,
            'totalJobPostings' => $totalJobPostings,
            'postedSharePct' => $postedSharePct,

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
            'topGenderLabel' => $topGenderLabel,
            'topGenderPct' => $topGenderPct,
            'topAgeLabel' => $topAgeLabel,
            'topAgePct' => $topAgePct,
            'unknownEducationPct' => $unknownEducationPct,
            'topCityLabel' => $topCityLabel,
            'topCityCount' => $topCityCount,
            'sourceLabels' => $knownSourceCounts->keys()->values()->all(),
            'sourceSeries' => $knownSourceCounts->values()->all(),
            'topSourceLabel' => $topSourceLabel,
            'topSourceCount' => $topSourceCount,
            'unknownSourcePct' => $unknownSourcePct,

            // Row 4 — Top 10 Division by candidate applied
            'divisionLabels' => $divisionLabels,
            'divisionCareerSeries' => $divisionCareerSeries,
            'divisionSelfSeries' => $divisionSelfSeries,
            'topDivisionRow' => $topDivisionRow,
            'topDivisionShare' => $topDivisionShare,

            // Total candidate applied per job
            'jobApplyRows' => $jobApplyRows->all(),
            'prfTurnaroundRows' => $prfTurnaroundRows,

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

            // Avg time to reach each funnel stage
            'stageTimingLabels' => $stageTimingLabels,
            'stageTimingCumulative' => $stageTimingCumulative,
            'bottleneckStage' => $bottleneckStage,
            'bottleneckDays' => $bottleneckDays,
            'totalHireDays' => $totalHireDays,

            // Full-dashboard summary insight
            'fullInsights' => $fullInsights,
            'lastUpdatedAt' => now()->format('d M Y, H:i'),
        ]);
    }
}
