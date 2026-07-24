<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Company;
use App\Models\Jobposting;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $applicantConn = (new Applicant())->getConnectionName();
        $conn = DB::connection($applicantConn);

        $from = $request->query('from');
        $to = $request->query('to');
        $department = $request->query('department');
        $company = $request->query('company');
        $location = $request->query('location');
        $source = $request->query('source'); // '', 'career' (job applicant), or 'self' (self applicant)

        $departments = $conn->table('hr_ms_department')
            ->where('status', 'A')
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);
        $departmentNames = $departments->pluck('department_name', 'department_id');

        // Department → Division lookup, so per-department application counts can be
        // rolled up into "By Division" without needing a division_id on every row
        // (viewtrxcareer doesn't carry one).
        $departmentToDivision = $conn->table('hr_ms_department')->pluck('division_id', 'department_id');
        $divisionNames = $conn->table('hr_ms_division')->where('status', 'A')->pluck('division_name', 'division_id');

        // Company / location filter options come from the job posting table itself
        // (viewselfregister and viewtrxcareer don't carry a location, and cpnyid codes
        // are only human-readable via the separate Company master on mysql2).
        $postingCompanyIds = $conn->table('hr_trx_jobposting')->whereNotNull('cpnyid')->distinct()->pluck('cpnyid');
        $companyNames = Company::whereIn('cpnyid', $postingCompanyIds)->pluck('cpnyname', 'cpnyid');
        $companies = $postingCompanyIds->map(fn ($id) => (object) ['cpnyid' => $id, 'cpnyname' => $companyNames->get($id, $id)])
            ->sortBy('cpnyname')
            ->values();

        $locations = $conn->table('hr_trx_jobposting')
            ->whereNotNull('locationname')
            ->where('locationname', '!=', '')
            ->distinct()
            ->orderBy('locationname')
            ->pluck('locationname');

        // Location isn't a column on viewtrxcareer itself, so filtering by it resolves
        // to the set of posting docids at that location first — deliberately not a join,
        // since hr_trx_jobposting shares several unqualified column names with
        // viewtrxcareer (status, departementid, job_level, job_title) used further down.
        $locationPostingIds = $location
            ? $conn->table('hr_trx_jobposting')->where('locationname', $location)->pluck('docid')
            : null;

        // Filtered base queries for the two application flows. Every widget below is
        // built from clones of these so the whole dashboard reacts to the same filters.
        // The "source" filter (Job Applicant / Self Applicant) is applied by forcing a
        // false condition on the excluded flow, so every downstream widget naturally
        // zeroes out that half without needing its own branch.
        $careerBase = $conn->table('viewtrxcareer')
            ->when($from, fn ($q) => $q->whereDate('viewtrxcareer.apply_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('viewtrxcareer.apply_date', '<=', $to))
            ->when($department, fn ($q) => $q->where('viewtrxcareer.departementid', $department))
            ->when($company, fn ($q) => $q->where('viewtrxcareer.cpnyid', $company))
            ->when($location, fn ($q) => $q->whereIn('viewtrxcareer.docidposting', $locationPostingIds))
            ->when($source === 'self', fn ($q) => $q->whereRaw('1 = 0'));

        // Self-registration has no company/location attached (it isn't tied to a specific
        // posting yet), so only date and department narrow that half of the pipeline.
        $selfBase = $conn->table('viewselfregister')
            ->when($from, fn ($q) => $q->whereDate('viewselfregister.apply_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('viewselfregister.apply_date', '<=', $to))
            ->when($department, fn ($q) => $q->where('viewselfregister.departementid', $department))
            ->when($source === 'career', fn ($q) => $q->whereRaw('1 = 0'));

        // Self Applicant KPI: distinct people who came through self-registration.
        // Self-registrants rarely have a KTP on file yet, so KTP+DOB (used for the
        // main Total Applicant count) would undercount them to near zero. Identity
        // is instead resolved by email, then phone, then KTP, then applicant_id.
        $selfCandidates = (clone $selfBase)
            ->leftJoin('hr_ms_applicant as a', 'viewselfregister.applicant_id', '=', 'a.applicant_id')
            ->select('a.gender as gender', 'a.date_of_birth as date_of_birth', 'viewselfregister.education_type as education_type', DB::raw(
                "COALESCE(NULLIF(TRIM(LOWER(a.email_address)), ''), NULLIF(TRIM(a.mobile_phone), ''), NULLIF(a.ktp_id, ''), a.applicant_id) as identity_key"
            ))
            ->get()
            ->unique('identity_key')
            ->values();

        $totalSelfApplicant = $selfCandidates->count();

        // Candidate pool for Gender / Age / Education: one row per application (matching
        // how "Total Applied" itself is counted) plus the deduped self-registrants —
        // most applicants don't have a KTP on file until much later in the flow, so a
        // KTP-matched subset would badly undercount them.
        $careerCandidates = (clone $careerBase)
            ->leftJoin('hr_ms_applicant as a', 'viewtrxcareer.applicant_id', '=', 'a.applicant_id')
            ->select('a.gender as gender', 'a.date_of_birth as date_of_birth', 'viewtrxcareer.education_type as education_type')
            ->get();

        $allCandidates = $careerCandidates->concat($selfCandidates);

        $genderCounts = $allCandidates->groupBy(fn ($row) => $row->gender ?: 'Unknown')
            ->map->count();

        $ageBuckets = array_fill_keys(self::AGE_BUCKET_LABELS, 0);
        foreach ($allCandidates as $row) {
            if (!$row->date_of_birth) {
                continue;
            }
            $age = Carbon::parse($row->date_of_birth)->age;
            $ageBuckets[self::ageBucket($age)]++;
        }

        // Education level breakdown, scoped to the same candidate pool. Far better
        // filled-in than sourcing channel (which is mostly blank on real records).
        $educationCounts = $allCandidates->groupBy(fn ($row) => $row->education_type ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        // Job level breakdown (career applicants only — self-registration doesn't
        // capture a job level since it isn't tied to a specific posting).
        $jobLevelCounts = (clone $careerBase)
            ->select(DB::raw("COALESCE(NULLIF(job_level, ''), 'Unknown') as job_level"), DB::raw('COUNT(*) as total'))
            ->groupBy('job_level')
            ->orderByDesc('total')
            ->pluck('total', 'job_level');

        // Applicants by division: career + self-register applications combined, rolled
        // up from department to division via hr_ms_department.division_id.
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

        // Applications over time: monthly trend across both flows within the filtered range.
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

        // Average time-to-hire: days from application to completion, for candidates who joined.
        $hireDurations = (clone $careerBase)
            ->where('status', 'C')
            ->whereNotNull('completed_at')
            ->whereNotNull('apply_date')
            ->select('apply_date', 'completed_at')
            ->get();
        $avgTimeToHire = $hireDurations->isNotEmpty()
            ? (int) round($hireDurations->avg(fn ($row) => Carbon::parse($row->apply_date)->diffInDays(Carbon::parse($row->completed_at))))
            : null;

        // Top job postings by number of applicants.
        $topPostings = (clone $careerBase)
            ->whereNotNull('docidposting')
            ->select('docidposting', DB::raw('MIN(job_title) as job_title'), DB::raw('COUNT(*) as total'))
            ->groupBy('docidposting')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->sortBy('total')
            ->values();

        // Job posting status breakdown (posting inventory health) — department/company/
        // location only, since a posting doesn't have an "apply date" of its own.
        $jobpostingQuery = Jobposting::query()
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($location, fn ($q) => $q->where('locationname', $location));
        $jobpostingCounts = (clone $jobpostingQuery)->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalJobposting = (int) $jobpostingCounts->sum();
        $postedCount = (int) ($jobpostingCounts->get('P', 0));
        $unpostedCount = (int) ($jobpostingCounts->get('U', 0));

        // PRF (Personnel Requisition Form) lives on a separate Postgres connection —
        // hr_trx_jobposting.refid links back to the PRF's docid once HR creates a
        // posting from an approved PRF. Only Completed (C) PRFs count: On Progress,
        // Revise, and Reject aren't requisitions HR has actually approved yet. Posted/
        // Unposted below are strictly a subset of this set (matched by refid), not an
        // independent count off the posting table — some postings carry a refid that
        // doesn't resolve to an existing PRF row (stale/mismatched test data), and
        // those must not inflate these numbers past Total PRF.
        $completedPrfIds = DB::connection('pgsql3')->table('hr_trx_prf')
            ->where('status', 'C')
            ->when($department, fn ($q) => $q->where('departementid', $department))
            ->when($company, fn ($q) => $q->where('cpnyid', $company))
            ->when($location, fn ($q) => $q->where('locationname', $location))
            ->pluck('docid');

        $totalPrf = $completedPrfIds->count();

        $postingStatusByRefid = Jobposting::whereIn('refid', $completedPrfIds)->pluck('status', 'refid');
        $totalPrfPosted = $postingStatusByRefid->filter(fn ($status) => $status === 'P')->count();
        $totalPrfUnposted = $postingStatusByRefid->filter(fn ($status) => $status === 'U')->count();

        // Candidates by posting status: of everyone who applied, how many applied
        // to postings that are currently Posted / Closed / Hold / etc.
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

        // Career / applicant pipeline (viewtrxcareer): H=No Candidate, P=Candidate, C=Joined, R=Rejected
        $careerCounts = (clone $careerBase)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalApplied = (int) $careerCounts->sum();
        $totalRejected = (int) ($careerCounts->get('R', 0));
        $totalJoined = (int) ($careerCounts->get('C', 0));
        $totalCandidate = (int) ($careerCounts->get('P', 0)) + $totalJoined;

        return view('pages.recruitment.dashboard', [
            'filters' => ['from' => $from, 'to' => $to, 'department' => $department, 'company' => $company, 'location' => $location, 'source' => $source],
            'departments' => $departments,
            'companies' => $companies,
            'locations' => $locations,
            'totalSelfApplicant' => $totalSelfApplicant,
            'totalApplied' => $totalApplied,
            'totalRejected' => $totalRejected,
            'totalJoined' => $totalJoined,
            'totalJobposting' => $totalJobposting,
            'postedCount' => $postedCount,
            'unpostedCount' => $unpostedCount,
            'totalPrf' => $totalPrf,
            'totalPrfPosted' => $totalPrfPosted,
            'totalPrfUnposted' => $totalPrfUnposted,
            'avgTimeToHire' => $avgTimeToHire,
            'genderLabels' => $genderCounts->keys()->values()->all(),
            'genderSeries' => $genderCounts->values()->all(),
            'ageLabels' => array_keys($ageBuckets),
            'ageSeries' => array_values($ageBuckets),
            'jobpostingLabels' => $candidatesByPostingStatus->pluck('label')->all(),
            'jobpostingSeries' => $candidatesByPostingStatus->pluck('count')->all(),
            'educationLabels' => $educationCounts->keys()->values()->all(),
            'educationSeries' => $educationCounts->values()->all(),
            'jobLevelLabels' => $jobLevelCounts->keys()->values()->all(),
            'jobLevelSeries' => $jobLevelCounts->values()->all(),
            'divisionLabels' => $divisionCounts->pluck('label')->all(),
            'divisionSeries' => $divisionCounts->pluck('total')->all(),
            'trendLabels' => $trendLabels->all(),
            'trendSeries' => $trendSeries->all(),
            'topPostingLabels' => $topPostings->pluck('job_title')->all(),
            'topPostingSeries' => $topPostings->pluck('total')->all(),
            'funnelSeries' => [
                ['x' => 'Applied', 'y' => $totalApplied],
                ['x' => 'Candidate', 'y' => $totalCandidate],
                ['x' => 'Joined', 'y' => $totalJoined],
            ],
        ]);
    }
}
