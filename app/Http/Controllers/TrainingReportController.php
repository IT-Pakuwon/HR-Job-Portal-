<?php

namespace App\Http\Controllers;

use App\Exports\TrainingReportExport;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndTrainingDetail;
use App\Models\MsLndTrainingFeedback;
use App\Models\MsLndTrainingQuota;
use App\Models\MsLndTrainingSchedule;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\StoSubGradingJobLevel;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use App\Models\User;
use App\Services\JobLevelResolver;
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
            'trainingId' => $request->input('training_id') ?: null,
            'scheduleId' => $request->input('schedule_id') ?: null,
            'level' => $request->input('level') ?: null,
        ];
    }

    /**
     * Batches whose TARGETED level includes the given label — job_level on
     * ms_lnd_training_detail is a (possibly multi-select) batch-level
     * setting, resolved via StoGrading the same way
     * TrainingAttendanceController::events() does. Quota is allocated per
     * batch, not per person, so this batch-level matching is what the quota
     * funnel's level filter uses; everywhere else in this report (the
     * charts/table of actual attendees) uses each person's own resolved
     * level via usernamesForLevel() instead.
     */
    protected function trainingDetailIdsForLevel(?string $level): array
    {
        if (!$level) {
            return [];
        }

        $jobLevelByDetail = MsLndTrainingDetail::pluck('job_level', 'training_detail_id');
        $labels = StoGrading::labelsFor($jobLevelByDetail->values());

        return $jobLevelByDetail
            ->filter(fn ($jobLevel) => ($jobLevel ? ($labels[$jobLevel] ?? $jobLevel) : 'Unspecified') === $level)
            ->keys()
            ->all();
    }

    /**
     * Usernames whose OWN current level (resolved via JobLevelResolver —
     * ms_user.npk -> Talenta job title -> hr_ms_sto_subgrading_joblevel —
     * same chain used to decorate each attended row's level_name) matches
     * the given label. Scoped to everyone who's ever registered for L&D
     * training, since that's the only population this report touches.
     */
    protected function usernamesForLevel(string $level): array
    {
        $usernames = TrLndTrainingRegistration::whereNotNull('user_registration')
            ->distinct()
            ->pluck('user_registration');

        if ($usernames->isEmpty()) {
            return [];
        }

        $users = User::whereIn('username', $usernames)->get(['username', 'npk', 'group_cpny_id']);

        return JobLevelResolver::forUsers($users)
            ->filter(fn ($resolved) => ($resolved ?: 'Unspecified') === $level)
            ->keys()
            ->all();
    }

    protected function baseAttendedQuery(array $filters)
    {
        $q = TrLndTrainingRegistration::where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNull('status_registration')
            ->whereNotNull('completed_at')
            ->whereBetween('schedule_date', [$filters['dateFrom'], $filters['dateTo']])
            ->when($filters['cpnyId'], fn ($q, $c) => $q->where('cpny_id', strtoupper(trim($c))))
            ->when($filters['trainingId'], fn ($q, $t) => $q->where('training_id', $t))
            ->when($filters['scheduleId'], fn ($q, $s) => $q->where('schedule_id', $s));

        if ($filters['level']) {
            $q->whereIn('user_registration', $this->usernamesForLevel($filters['level']));
        }

        return $q;
    }

    /**
     * One row per attended registration, decorated with everything the
     * charts/table need: training/company/department names, the
     * participant's OWN level (resolved via JobLevelResolver — their actual
     * current grade, not the batch's possibly-multi-select target
     * audience), the session's duration in hours, attendance+feedback
     * stars, and the participant's own average Rating-type feedback score
     * ("satisfaction"), if they submitted one.
     */
    protected function gatherAttendedRows(Request $request): array
    {
        $filters = $this->parseFilters($request);

        $attended = $this->baseAttendedQuery($filters)->get();

        $scheduleIds = $attended->pluck('schedule_id')->filter()->unique();
        $schedules = $scheduleIds->isEmpty()
            ? collect()
            : MsLndTrainingSchedule::whereIn('schedule_id', $scheduleIds)
                ->get(['schedule_id', 'schedule_start_time', 'schedule_end_time'])
                ->keyBy('schedule_id');

        $usernames = $attended->pluck('user_registration')->filter()->unique();
        $users = $usernames->isEmpty()
            ? collect()
            : User::whereIn('username', $usernames)->get(['username', 'npk', 'group_cpny_id']);
        $levelByUsername = JobLevelResolver::forUsers($users);

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

        $rows = $attended->map(function ($r) use ($schedules, $levelByUsername, $trainingNames, $cpnyNames, $deptNames, $satisfactionByRegist) {
            $schedule = $schedules->get($r->schedule_id);
            $durationHours = 0.0;

            if ($schedule && $schedule->schedule_start_time && $schedule->schedule_end_time) {
                $start = Carbon::parse($schedule->schedule_start_time);
                $end = Carbon::parse($schedule->schedule_end_time);
                $durationHours = abs($start->diffInMinutes($end)) / 60;
            }

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
                'level_name' => $levelByUsername[$r->user_registration] ?? 'Unspecified',
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

    /**
     * Dropdown options for the Training/Schedule/Level filters. Schedule is
     * narrowed to one training when `training_id` is passed, so picking a
     * training first shrinks the schedule list to just its own sessions.
     */
    public function filters(Request $request)
    {
        $trainings = MsTrainingEvent::orderBy('training_name')->pluck('training_name', 'training_id');

        $schedules = MsLndTrainingSchedule::whereIn('status', ['P', 'C'])
            ->when($request->filled('training_id'), fn ($q) => $q->where('training_id', $request->input('training_id')))
            ->orderByDesc('schedule_date')
            ->get(['schedule_id', 'training_id', 'schedule_date']);

        $scheduleOptions = $schedules->map(fn ($s) => [
            'id' => $s->schedule_id,
            'name' => ($trainings[$s->training_id] ?? $s->training_id).' — '.(optional($s->schedule_date)->format('d M Y') ?? '-'),
        ]);

        // The individual-level vocabulary JobLevelResolver can actually return
        // (not the batch's possibly-combined job_level), so picking one here
        // matches what gatherAttendedRows() resolves per attendee.
        $levels = StoSubGradingJobLevel::where('status', 'A')
            ->whereNotNull('group_job_level')
            ->distinct()
            ->pluck('group_job_level')
            ->filter()
            ->push('Unspecified')
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'trainings' => $trainings->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values(),
            'schedules' => $scheduleOptions->values(),
            'levels' => $levels,
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

        $registered = $this->registeredCount($filters);
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

    /**
     * Everyone holding an approved, non-waitlisted/offered/cancelled seat in
     * the period — regardless of whether they ever checked in. The
     * denominator for both Completion Rate and the quota funnel's fill rate.
     */
    protected function registeredCount(array $filters): int
    {
        return $this->registeredQuery($filters)->count();
    }

    protected function registeredQuery(array $filters)
    {
        $q = TrLndTrainingRegistration::where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNull('status_registration')
            ->whereBetween('schedule_date', [$filters['dateFrom'], $filters['dateTo']])
            ->when($filters['cpnyId'], fn ($q, $c) => $q->where('cpny_id', strtoupper(trim($c))))
            ->when($filters['trainingId'], fn ($q, $t) => $q->where('training_id', $t))
            ->when($filters['scheduleId'], fn ($q, $s) => $q->where('schedule_id', $s));

        if ($filters['level']) {
            $q->whereIn('user_registration', $this->usernamesForLevel($filters['level']));
        }

        return $q;
    }

    /**
     * Seats offered (ms_lnd_training_quota.quota_pax) vs. seats actually
     * registered vs. seats attended, for the sessions scheduled in this
     * period. Quota is set per company per schedule, so it's filtered the
     * same way as everything else (by the schedule's date and, if chosen,
     * company) rather than by the registration rows themselves.
     */
    public function quotaFunnelJson(Request $request)
    {
        $filters = $this->parseFilters($request);

        return response()->json(['data' => $this->gatherQuotaFunnelData($filters)]);
    }

    protected function gatherQuotaFunnelData(array $filters): array
    {
        $scheduleIds = MsLndTrainingSchedule::whereIn('status', ['P', 'C'])
            ->whereBetween('schedule_date', [$filters['dateFrom'], $filters['dateTo']])
            ->when($filters['trainingId'], fn ($q, $t) => $q->where('training_id', $t))
            ->when($filters['scheduleId'], fn ($q, $s) => $q->where('schedule_id', $s))
            ->pluck('schedule_id');

        $quotaQuery = MsLndTrainingQuota::where('status', 'A')
            ->whereIn('schedule_id', $scheduleIds)
            ->when($filters['cpnyId'], fn ($q, $c) => $q->where('cpny_id', strtoupper(trim($c))))
            ->when($filters['trainingId'], fn ($q, $t) => $q->where('training_id', $t));

        if ($filters['level']) {
            $quotaQuery->whereIn('training_detail_id', $this->trainingDetailIdsForLevel($filters['level']));
        }

        $quotaByCpny = $quotaQuery->selectRaw('cpny_id, sum(quota_pax) as total')
            ->groupBy('cpny_id')
            ->pluck('total', 'cpny_id');

        $registeredByCpny = $this->registeredQuery($filters)
            ->selectRaw('cpny_id, count(*) as total')
            ->groupBy('cpny_id')
            ->pluck('total', 'cpny_id');

        $attendedByCpny = $this->baseAttendedQuery($filters)
            ->selectRaw('cpny_id, count(*) as total')
            ->groupBy('cpny_id')
            ->pluck('total', 'cpny_id');

        $cpnyIds = $quotaByCpny->keys()->merge($registeredByCpny->keys())->merge($attendedByCpny->keys())->unique();
        $cpnyNames = MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $namedBreakdown = fn ($byCpny) => $cpnyIds
            ->mapWithKeys(fn ($id) => [($cpnyNames[$id] ?? $id) => (int) ($byCpny[$id] ?? 0)])
            ->filter()
            ->sortDesc();

        $totalQuota = (int) $quotaByCpny->sum();
        $registered = (int) $registeredByCpny->sum();
        $attended = (int) $attendedByCpny->sum();

        $fillRate = $totalQuota > 0 ? round(($registered / $totalQuota) * 100) : 0;
        $noShowRate = $registered > 0 ? round((($registered - $attended) / $registered) * 100) : 0;

        return [
            'quota' => $totalQuota,
            'registered' => $registered,
            'attended' => $attended,
            'fill_rate' => $fillRate,
            'no_show_rate' => $noShowRate,
            'breakdown' => [
                'Quota' => $namedBreakdown($quotaByCpny),
                'Registered' => $namedBreakdown($registeredByCpny),
                'Attended' => $namedBreakdown($attendedByCpny),
            ],
        ];
    }

    // ── API: Charts ──────────────────────────────────────────────────────────────

    /**
     * Per-category → {company_name: count} map, used to power the "which
     * companies make up this bar" tooltip on the department/level/top
     * trainings charts.
     */
    protected function companyBreakdown($rows, string $groupKey, $keys)
    {
        return $rows->groupBy($groupKey)
            ->only($keys->all())
            ->map(fn ($group) => $group->groupBy('cpny_name')->map->count()->sortDesc());
    }

    public function byDepartmentJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('department_name')->map->count()->sortDesc()->take(10);

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
            'breakdown' => $this->companyBreakdown($rows, 'department_name', $counts->keys()),
        ]]);
    }

    public function byLevelJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('level_name')->map->count()->sortDesc();

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
            'breakdown' => $this->companyBreakdown($rows, 'level_name', $counts->keys()),
        ]]);
    }

    public function topTrainingsJson(Request $request)
    {
        $rows = $this->gatherAttendedRows($request)['rows'];

        $counts = $rows->groupBy('training_name')->map->count()->sortDesc()->take(5);

        return response()->json(['data' => [
            'categories' => $counts->keys()->values(),
            'series' => [['name' => 'Attendance', 'data' => $counts->values()]],
            'breakdown' => $this->companyBreakdown($rows, 'training_name', $counts->keys()),
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
            'quotaFunnel' => $this->gatherQuotaFunnelData($g['filters']),
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
            'lastUpdatedAt' => now()->format('d M Y, H:i'),
        ]);
    }
}
