<?php

namespace App\Http\Controllers;

use App\Exports\TrainingReportExport;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndTrainingDetail;
use App\Models\MsLndTrainingFeedback;
use App\Models\MsLndTrainingSchedule;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Training Report (L&D)
|--------------------------------------------------------------------------
| GM-style analytics dashboard (stat cards + charts + a sortable/searchable
| table) over Learning & Development training activity: attendance,
| training hours delivered, participant satisfaction (from feedback
| ratings), department/level distribution of participants, and the most
| attended trainings.
|
| "Attended" throughout this controller means: approval status Approved,
| never waitlisted/offered/cancelled (status_registration is null), and
| actually checked in (completed_at set) — the same definition
| TrainingAttendanceController's Training Report tab uses.
|
| Access is entirely DB-driven via sys_access_right (screen_id
| REPORTTRAINING, access_name VIEW) — see the `access:REPORTTRAINING,VIEW`
| middleware on the whole route group in routes/web.php.
*/
class TrainingReportController extends Controller
{
    protected function parseFilters(Request $request): array
    {
        $y = date('Y');

        return [
            'dateFrom' => $request->input('date_from') ?: "{$y}-01-01",
            'dateTo' => $request->input('date_to') ?: "{$y}-12-31",
            'cpnyId' => $request->input('cpny_id') ?: null,
        ];
    }

    protected function baseAttendedQuery(string $dateFrom, string $dateTo, ?string $cpnyId)
    {
        $q = TrLndTrainingRegistration::where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNull('status_registration')
            ->whereNotNull('completed_at')
            ->whereBetween('schedule_date', [$dateFrom, $dateTo]);

        if ($cpnyId) {
            $q->where('cpny_id', strtoupper(trim($cpnyId)));
        }

        return $q;
    }

    /**
     * One row per attended registration, decorated with everything the
     * charts/table need: training/company/department names, the
     * participant's level (resolved from the batch's job_level the same
     * way TrainingAttendanceController::events() does), the session's
     * duration in hours, attendance+feedback stars, and the participant's
     * own average Rating-type feedback score ("satisfaction"), if they
     * submitted one.
     */
    protected function gatherAttendedRows(Request $request): array
    {
        $filters = $this->parseFilters($request);

        $attended = $this->baseAttendedQuery($filters['dateFrom'], $filters['dateTo'], $filters['cpnyId'])->get();

        $scheduleIds = $attended->pluck('schedule_id')->filter()->unique();
        $schedules = $scheduleIds->isEmpty()
            ? collect()
            : MsLndTrainingSchedule::whereIn('schedule_id', $scheduleIds)
                ->get(['schedule_id', 'schedule_start_time', 'schedule_end_time'])
                ->keyBy('schedule_id');

        $detailIds = $attended->pluck('training_detail_id')->filter()->unique();
        $jobLevelByDetail = $detailIds->isEmpty()
            ? collect()
            : MsLndTrainingDetail::whereIn('training_detail_id', $detailIds)->pluck('job_level', 'training_detail_id');
        $levelLabels = StoGrading::labelsFor($jobLevelByDetail->values());

        $trainingIds = $attended->pluck('training_id')->filter()->unique();
        $trainingNames = $trainingIds->isEmpty()
            ? collect()
            : MsTrainingEvent::whereIn('training_id', $trainingIds)->pluck('training_name', 'training_id');

        $cpnyNames = MsCompany::whereIn('cpny_id', $attended->pluck('cpny_id')->filter()->unique())->pluck('cpny_name', 'cpny_id');
        $deptNames = MsDepartment::whereIn('department_id', $attended->pluck('department_id')->filter()->unique())->pluck('department_name', 'department_id');

        $registIds = $attended->pluck('training_regist_id')->filter()->unique();
        $satisfactionByRegist = $registIds->isEmpty()
            ? collect()
            : TrLndTrainingFeedbackAnswer::whereIn('training_regist_id', $registIds)
                ->where('question_type', MsLndTrainingFeedback::TYPE_RATING)
                ->whereNotNull('answer_number')
                ->get()
                ->groupBy('training_regist_id')
                ->map(fn ($rows) => round($rows->avg('answer_number'), 2));

        $rows = $attended->map(function ($r) use ($schedules, $jobLevelByDetail, $levelLabels, $trainingNames, $cpnyNames, $deptNames, $satisfactionByRegist) {
            $schedule = $schedules->get($r->schedule_id);
            $durationHours = 0.0;

            if ($schedule && $schedule->schedule_start_time && $schedule->schedule_end_time) {
                $start = Carbon::parse($schedule->schedule_start_time);
                $end = Carbon::parse($schedule->schedule_end_time);
                $durationHours = abs($start->diffInMinutes($end)) / 60;
            }

            $jobLevel = $jobLevelByDetail[$r->training_detail_id] ?? null;

            return [
                'training_regist_id' => $r->training_regist_id,
                'schedule_id' => $r->schedule_id,
                'training_id' => $r->training_id,
                'training_name' => $trainingNames[$r->training_id] ?? $r->training_id,
                'schedule_date' => optional($r->schedule_date)->format('Y-m-d'),
                'cpny_id' => $r->cpny_id,
                'cpny_name' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
                'department_id' => $r->department_id,
                'department_name' => $deptNames[$r->department_id] ?? ($r->department_id ?: 'Unassigned'),
                'level_name' => $jobLevel ? ($levelLabels[$jobLevel] ?? $jobLevel) : 'Unspecified',
                'duration_hours' => round($durationHours, 2),
                'stars' => $r->stars,
                'satisfaction' => $satisfactionByRegist[$r->training_regist_id] ?? null,
            ];
        })->values();

        return ['rows' => $rows, 'filters' => $filters];
    }

    // ── API: Company filter options ─────────────────────────────────────────────

    public function companies()
    {
        $list = TrLndTrainingRegistration::whereNotNull('cpny_id')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id');

        return response()->json([
            'data' => $list,
            'locked' => false,
            'single' => null,
        ]);
    }

    // ── API: Stat cards ──────────────────────────────────────────────────────────

    public function summaryJson(Request $request)
    {
        $g = $this->gatherAttendedRows($request);
        $rows = $g['rows'];
        $filters = $g['filters'];

        $totalAttendance = $rows->count();
        $totalSessions = $rows->pluck('schedule_id')->filter()->unique()->count();
        $totalHours = round($rows->sum('duration_hours'), 1);
        $avgStars = $totalAttendance ? round($rows->avg('stars'), 2) : 0;

        $withSatisfaction = $rows->filter(fn ($r) => $r['satisfaction'] !== null);
        $avgSatisfaction = $withSatisfaction->count() ? round($withSatisfaction->avg('satisfaction'), 2) : null;

        $registered = TrLndTrainingRegistration::where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNull('status_registration')
            ->whereBetween('schedule_date', [$filters['dateFrom'], $filters['dateTo']])
            ->when($filters['cpnyId'], fn ($q, $c) => $q->where('cpny_id', strtoupper(trim($c))))
            ->count();
        $completionRate = $registered > 0 ? round(($totalAttendance / $registered) * 100) : 0;

        return response()->json(['data' => [
            'total_attendance' => $totalAttendance,
            'total_sessions' => $totalSessions,
            'total_training_hours' => $totalHours,
            'avg_satisfaction' => $avgSatisfaction,
            'avg_stars' => $avgStars,
            'completion_rate' => $completionRate,
        ]]);
    }

    // ── API: Charts ──────────────────────────────────────────────────────────────

    public function byDepartmentJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('department_name')->map->count()->sortDesc()->take(10);

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
        ]]);
    }

    public function byLevelJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('level_name')->map->count()->sortDesc();

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
        ]]);
    }

    public function topTrainingsJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('training_name')->map->count()->sortDesc()->take(10);

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
        ]]);
    }

    /**
     * Monthly trend of attendance volume vs. average satisfaction, so L&D
     * can see whether satisfaction is holding up as training volume
     * changes month to month.
     */
    public function trendJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $byMonth = $rows->groupBy(fn ($r) => substr($r['schedule_date'] ?? '', 0, 7))
            ->filter(fn ($g, $key) => $key !== '')
            ->sortKeys();

        $categories = [];
        $attendance = [];
        $satisfaction = [];

        foreach ($byMonth as $month => $group) {
            $categories[] = Carbon::createFromFormat('Y-m', $month)->format('M Y');
            $attendance[] = $group->count();

            $withSat = $group->filter(fn ($r) => $r['satisfaction'] !== null);
            $satisfaction[] = $withSat->count() ? round($withSat->avg('satisfaction'), 2) : null;
        }

        return response()->json(['data' => [
            'categories' => $categories,
            'attendance' => $attendance,
            'satisfaction' => $satisfaction,
        ]]);
    }

    // ── API: Session list table ───────────────────────────────────────────────────

    protected function sessionRows($rows)
    {
        return $rows->groupBy('schedule_id')->map(function ($group) {
            $first = $group->first();
            $withSat = $group->filter(fn ($r) => $r['satisfaction'] !== null);

            return [
                'schedule_id' => $first['schedule_id'],
                'date' => $first['schedule_date'],
                'training_name' => $first['training_name'],
                'level_name' => $first['level_name'],
                'attendees' => $group->count(),
                'avg_stars' => round($group->avg('stars'), 2),
                'avg_satisfaction' => $withSat->count() ? round($withSat->avg('satisfaction'), 2) : null,
                'total_hours' => round($group->sum('duration_hours'), 1),
            ];
        })->values()->sortByDesc('date')->values();
    }

    public function tableJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        return response()->json(['data' => $this->sessionRows($rows)]);
    }

    // ── Export ───────────────────────────────────────────────────────────────────

    protected function gatherExportData(Request $request): array
    {
        $g = $this->gatherAttendedRows($request);
        $rows = $g['rows'];

        $totalAttendance = $rows->count();
        $withSatisfaction = $rows->filter(fn ($r) => $r['satisfaction'] !== null);

        return [
            'dateFrom' => $g['filters']['dateFrom'],
            'dateTo' => $g['filters']['dateTo'],
            'cpnyId' => $g['filters']['cpnyId'],
            'summary' => [
                'total_attendance' => $totalAttendance,
                'total_sessions' => $rows->pluck('schedule_id')->filter()->unique()->count(),
                'total_training_hours' => round($rows->sum('duration_hours'), 1),
                'avg_satisfaction' => $withSatisfaction->count() ? round($withSatisfaction->avg('satisfaction'), 2) : null,
                'avg_stars' => $totalAttendance ? round($rows->avg('stars'), 2) : 0,
            ],
            'byDepartment' => $rows->groupBy('department_name')->map->count()->sortDesc(),
            'byLevel' => $rows->groupBy('level_name')->map->count()->sortDesc(),
            'sessionRows' => $this->sessionRows($rows),
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->gatherExportData($request);
        $pdf = Pdf::loadView('pages.training-report.export-pdf', $data)
            ->setPaper('a4', 'landscape');
        $filename = 'training-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.pdf';

        return $pdf->download($filename);
    }

    public function exportXlsx(Request $request)
    {
        $data = $this->gatherExportData($request);
        $filename = 'training-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.xlsx';

        return Excel::download(new TrainingReportExport($data), $filename);
    }

    // ── Page ─────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.training-report.dashboard', [
            'user' => $user,
        ]);
    }
}
