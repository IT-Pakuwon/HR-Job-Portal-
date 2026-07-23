<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Jobposting;
use App\Models\ViewCareer;
use Carbon\Carbon;
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

    public function dashboard()
    {
        $applicantConn = (new Applicant())->getConnectionName();
        $conn = DB::connection($applicantConn);

        // Distinct applicants = same person identified by KTP + Date of Birth together.
        // Rows missing either field can't be matched to a person and are excluded.
        $distinctApplicants = $conn->table('hr_ms_applicant')
            ->whereNotNull('ktp_id')
            ->where('ktp_id', '!=', '')
            ->whereNotNull('date_of_birth')
            ->select('ktp_id', 'date_of_birth', DB::raw('MIN(gender) as gender'))
            ->groupBy('ktp_id', 'date_of_birth')
            ->get();

        $totalApplicant = $distinctApplicants->count();
        $totalApplicantRecords = $conn->table('hr_ms_applicant')->count();

        $genderCounts = $distinctApplicants->groupBy(fn ($row) => $row->gender ?: 'Unknown')
            ->map->count();

        $ageBuckets = array_fill_keys(self::AGE_BUCKET_LABELS, 0);
        foreach ($distinctApplicants as $row) {
            $age = Carbon::parse($row->date_of_birth)->age;
            $ageBuckets[self::ageBucket($age)]++;
        }

        // Job posting status breakdown
        $jobpostingCounts = Jobposting::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalJobposting = (int) $jobpostingCounts->sum();
        $postedCount = (int) ($jobpostingCounts->get('P', 0));
        $unpostedCount = (int) ($jobpostingCounts->get('U', 0));

        $jobpostingBreakdown = collect(self::JOBPOSTING_STATUS_LABELS)
            ->map(fn ($label, $code) => [
                'code' => $code,
                'label' => $label,
                'count' => (int) ($jobpostingCounts->get($code, 0)),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();

        // Career / applicant pipeline (viewtrxcareer): H=No Candidate, P=Candidate, C=Joined, R=Rejected
        $careerCounts = ViewCareer::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalApplied = (int) $careerCounts->sum();
        $totalRejected = (int) ($careerCounts->get('R', 0));
        $totalJoined = (int) ($careerCounts->get('C', 0));
        $totalCandidate = (int) ($careerCounts->get('P', 0)) + $totalJoined;

        return view('pages.recruitment.dashboard', [
            'totalApplicant' => $totalApplicant,
            'totalApplicantRecords' => $totalApplicantRecords,
            'totalApplied' => $totalApplied,
            'totalRejected' => $totalRejected,
            'totalJoined' => $totalJoined,
            'totalJobposting' => $totalJobposting,
            'postedCount' => $postedCount,
            'unpostedCount' => $unpostedCount,
            'genderLabels' => $genderCounts->keys()->values()->all(),
            'genderSeries' => $genderCounts->values()->all(),
            'ageLabels' => array_keys($ageBuckets),
            'ageSeries' => array_values($ageBuckets),
            'jobpostingLabels' => $jobpostingBreakdown->pluck('label')->all(),
            'jobpostingSeries' => $jobpostingBreakdown->pluck('count')->all(),
            'funnelSeries' => [
                ['x' => 'Applied', 'y' => $totalApplied],
                ['x' => 'Candidate', 'y' => $totalCandidate],
                ['x' => 'Joined', 'y' => $totalJoined],
            ],
        ]);
    }
}
