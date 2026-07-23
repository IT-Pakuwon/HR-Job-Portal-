<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PgTrekDashboardController extends Controller
{
    /**
     * Extracted from "SPOTILY SECURITY 3.1.xlsx" → 'Detail Vendor Report (alternat)'.
     * Bobot (section weights) and per-aspect Agreement Points are fixed constants
     * in that workbook, not derived from any table — hardcoded here to match.
     */
    private const BOBOT = [
        'absent_discipline' => 0.10,
        'point_completion' => 0.30,
        'time_implement' => 0.30,
        'alert_point' => 0.30,
    ];

    private const BEACON_BUFFER = 0.10; // achievement >= 90% of target counts as 100%

    private const AGREEMENT_POINTS = [
        'Supportive Alert' => 0.2,
        'Technical Alert' => 0.2,
        'Neutral Alert' => 0.2,
        'Negligence Alert' => -5.0,
    ];

    private const PERFORMANCE_TIERS = [
        ['max' => 0.25, 'label' => 'Unacceptable Performance'],
        ['max' => 0.40, 'label' => 'Below Standard Performance'],
        ['max' => 0.70, 'label' => 'Minimum Standard Performance'],
        ['max' => 0.80, 'label' => 'Moderately Compliant with Standard'],
        ['max' => 0.90, 'label' => 'Substantially Compliant with Standard'],
        ['max' => null, 'label' => 'Fully Compliant with Standard'],
    ];

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.pgtrek.dashboard', [
            'user' => $user,
            'vendors' => $this->availableVendors(),
            'sites' => $this->availableSites(),
        ]);
    }

    public function beaconPerformanceJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $filters = $this->validatedFilters($request);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                ['period' => ['start' => $filters['start'], 'end' => $filters['end']], 'vendor' => $filters['vendor'], 'site' => $filters['site']],
                $this->getBeaconPerformance(...array_values($filters)),
            ),
        ]);
    }

    /**
     * "Duty" reason count in vwm_vendor_alert_reason_detail is a per-detection
     * tally (hundreds/period), not a substitute headcount — no reliable source
     * for "Substitute Used" was found, so it's intentionally left out here.
     */
    public function reportSummaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $filters = $this->validatedFilters($request);

        return response()->json([
            'success' => true,
            'data' => array_merge(
                ['period' => ['start' => $filters['start'], 'end' => $filters['end']], 'vendor' => $filters['vendor'], 'site' => $filters['site']],
                $this->getReportSummary(...array_values($filters)),
            ),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $beacon = $this->getBeaconPerformance(...array_values($filters));
        $summary = $this->getReportSummary(...array_values($filters));

        $alertRows = [];
        foreach ($summary['alert_point']['groups'] as $gi => $group) {
            $reasons = $group['reasons']->isEmpty() ? collect([['reason' => '—', 'qty' => 0]]) : $group['reasons'];
            foreach ($reasons as $ri => $reason) {
                $alertRows[] = [
                    'groupIndex' => $gi + 1,
                    'aspect' => $group['aspect'],
                    'groupQty' => $group['qty'],
                    'groupSpan' => $reasons->count(),
                    'isFirst' => $ri === 0,
                    'reason' => $reason['reason'],
                    'qty' => $reason['qty'],
                ];
            }
        }

        $sites = $this->availableSites();
        $siteName = $filters['site']
            ? optional($sites->firstWhere('site_code', $filters['site']))->site_name ?? $filters['site']
            : 'All Locations';

        $pdf = Pdf::loadView('pages.pgtrek.export-pdf', [
            'start' => $filters['start'],
            'end' => $filters['end'],
            'vendor' => $filters['vendor'] ?: 'All Vendors',
            'siteName' => $siteName,
            'pointCompletion' => $beacon['point_completion'],
            'timeImplement' => $beacon['time_implement'],
            'personnel' => $summary['personnel'],
            'absentDiscipline' => $summary['absent_discipline'],
            'alertPoint' => $summary['alert_point'],
            'alertRows' => $alertRows,
            'scoring' => $this->computeScoring($beacon, $summary),
        ])->setPaper('a4', 'portrait');

        $filename = 'pgtrek-report-'.$filters['start'].'-to-'.$filters['end'].'.pdf';

        return $pdf->download($filename);
    }

    private function validatedFilters(Request $request): array
    {
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'vendor' => 'nullable|string',
            'site' => 'nullable|string',
        ]);

        return [
            'start' => $data['start'],
            'end' => $data['end'],
            'vendor' => $data['vendor'] ?? null,
            'site' => $data['site'] ?? null,
        ];
    }

    private function availableVendors()
    {
        return DB::connection('pgsql6')
            ->table('pgtrek_point_time_daily')
            ->whereNotNull('vendor_name')
            ->distinct()
            ->orderBy('vendor_name')
            ->pluck('vendor_name');
    }

    private function availableSites()
    {
        $siteCodes = DB::connection('pgsql6')
            ->table('pgtrek_point_time_daily')
            ->whereNotNull('siteid')
            ->distinct()
            ->pluck('siteid');

        return DB::connection('pgsql6')
            ->table('ms_site')
            ->where('status', 'A')
            ->whereIn('site_code', $siteCodes)
            ->orderBy('site_name')
            ->get(['site_code', 'site_name']);
    }

    /**
     * Reads from pgtrek_point_time_daily — our own materialized view (see
     * PgTrekRefreshViews / database/pgtrek-views.sql), built directly from
     * ct_ms_track_sc + live_presence_log because the pre-existing vwm_/vw_
     * views for this had a duration-inflation bug (first-to-last-ping span
     * instead of merged presence sessions) and, separately, target counts
     * that double-counted gateway checkpoints across scheduled time-ranges.
     */
    private function getBeaconPerformance(string $start, string $end, ?string $vendor, ?string $site): array
    {
        $rows = DB::connection('pgsql6')
            ->table('pgtrek_point_time_daily')
            ->selectRaw('upper(trim(activity)) as activity_key')
            ->selectRaw('initcap(upper(trim(activity))) as activity')
            ->selectRaw('sum(target_total_activity) as target_count')
            ->selectRaw('sum(actual_total_activity) as actual_count')
            ->selectRaw('sum(target_duration_minutes) as target_minutes')
            ->selectRaw('sum(actual_duration_minutes) as actual_minutes')
            ->whereBetween('date_calendar', [$start, $end])
            ->when($vendor, fn ($q) => $q->where('vendor_name', $vendor))
            ->when($site, fn ($q) => $q->where('siteid', $site))
            ->groupBy('activity_key')
            ->orderBy('activity_key')
            ->get();

        $pointRows = $rows->map(fn ($r) => [
            'activity' => $r->activity,
            'target' => (int) $r->target_count,
            'actual' => (int) $r->actual_count,
            'pct' => $r->target_count > 0 ? round($r->actual_count / $r->target_count * 100, 2) : null,
        ]);

        $timeRows = $rows->map(fn ($r) => [
            'activity' => $r->activity,
            'target_minutes' => round((float) $r->target_minutes, 2),
            'actual_minutes' => round((float) $r->actual_minutes, 2),
            'pct' => $r->target_minutes > 0 ? round($r->actual_minutes / $r->target_minutes * 100, 2) : null,
        ]);

        $pointTargetSum = $pointRows->sum('target');
        $pointActualSum = $pointRows->sum('actual');
        $timeTargetSum = $timeRows->sum('target_minutes');
        $timeActualSum = $timeRows->sum('actual_minutes');

        $pointRawPct = $pointTargetSum > 0 ? $pointActualSum / $pointTargetSum : null;
        $timeRawPct = $timeTargetSum > 0 ? $timeActualSum / $timeTargetSum : null;

        return [
            'point_completion' => [
                'rows' => $pointRows,
                'raw_pct' => $pointRawPct !== null ? round($pointRawPct * 100, 2) : null,
                'overall_pct' => $pointRawPct !== null ? round($this->applyBuffer($pointRawPct) * 100, 2) : null,
            ],
            'time_implement' => [
                'rows' => $timeRows,
                'raw_pct' => $timeRawPct !== null ? round($timeRawPct * 100, 2) : null,
                'overall_pct' => $timeRawPct !== null ? round($this->applyBuffer($timeRawPct) * 100, 2) : null,
            ],
        ];
    }

    /**
     * "Buffer" tolerance from the workbook: achievement within BEACON_BUFFER of
     * target (i.e. >= 90%) is treated as fully met (100%) rather than penalized.
     */
    private function applyBuffer(float $rawRatio): float
    {
        return $rawRatio >= (1 - self::BEACON_BUFFER) ? 1.0 : $rawRatio;
    }

    private function computeScoring(array $beacon, array $summary): array
    {
        $absentPct = ($summary['absent_discipline']['work_days_pct'] ?? 0) / 100;
        $pointPct = ($beacon['point_completion']['overall_pct'] ?? 0) / 100;
        $timePct = ($beacon['time_implement']['overall_pct'] ?? 0) / 100;

        $alertContribution = 0;
        foreach ($summary['alert_point']['groups'] as $group) {
            $alertContribution += $group['qty'] * (self::AGREEMENT_POINTS[$group['aspect']] ?? 0);
        }
        $alertPct = min(1.0, 1.0 + $alertContribution / 100);

        $sections = [
            ['label' => 'Absent Dicipline', 'pct' => $absentPct, 'bobot' => self::BOBOT['absent_discipline']],
            ['label' => 'Beacon Tracking Performance - Track Point Activities Completion', 'pct' => $pointPct, 'bobot' => self::BOBOT['point_completion']],
            ['label' => 'Beacon Tracking Performance - Track Time Implement (Minutes)', 'pct' => $timePct, 'bobot' => self::BOBOT['time_implement']],
            ['label' => 'Alert Point', 'pct' => $alertPct, 'bobot' => self::BOBOT['alert_point']],
        ];

        $result = 0;
        foreach ($sections as &$s) {
            $s['contribution'] = $s['pct'] * $s['bobot'];
            $s['pct'] = round($s['pct'] * 100, 2);
            $s['bobot'] = round($s['bobot'] * 100, 2);
            $s['contribution'] = round($s['contribution'] * 100, 2);
            $result += $s['contribution'];
        }
        unset($s);

        $resultRatio = $result / 100;
        $performance = self::PERFORMANCE_TIERS[array_key_last(self::PERFORMANCE_TIERS)]['label'];
        foreach (self::PERFORMANCE_TIERS as $tier) {
            if ($tier['max'] !== null && $resultRatio <= $tier['max']) {
                $performance = $tier['label'];
                break;
            }
        }
        return [
            'sections' => $sections,
            'result' => round($result, 2),
            'performance' => $performance,
        ];
    }

    private function getReportSummary(string $start, string $end, ?string $vendor, ?string $site): array
    {
        $employees = DB::connection('pgsql6')
            ->table('pgtrek_personnel_daily')
            ->selectRaw('employeeid')
            ->selectRaw('count(*) as calendar_days')
            ->selectRaw('count(*) filter (where logged) as logged_days')
            ->whereBetween('date_calendar', [$start, $end])
            ->when($vendor, fn ($q) => $q->where('vendor_name', $vendor))
            ->when($site, fn ($q) => $q->where('siteid', $site))
            ->groupBy('employeeid')
            ->get();

        $totalBeaconUser = $employees->count();
        $activeTrackUser = $employees->filter(fn ($e) => $e->logged_days > 0)->count();
        $nonTrackUser = $totalBeaconUser - $activeTrackUser;

        $workDaysTarget = (int) $employees->sum('calendar_days');
        $workDaysActual = (int) $employees->sum('logged_days');

        $alertRows = DB::connection('pgsql6')
            ->table('pgtrek_alert_point_daily')
            ->selectRaw('sub_aspect')
            ->selectRaw('reason_name')
            ->selectRaw('sum(qty) as qty')
            ->whereBetween('date', [$start, $end])
            ->when($vendor, fn ($q) => $q->where('vendor_name', $vendor))
            ->when($site, fn ($q) => $q->where('siteid', $site))
            ->groupBy('sub_aspect', 'reason_name')
            ->havingRaw('sum(qty) > 0')
            ->get();

        // ct_alert_reason (and our own fallback classification for unreviewed
        // system alerts) uses upper-case sub_aspect labels — see
        // PgTrekRefreshViews / pgtrek_alert_point_daily.
        $aspectMap = [
            'SUPPORTIVE ALERT' => 'Supportive Alert',
            'TECHNICAL PROBLEM ALERT' => 'Technical Alert',
            'NEUTRAL ALERT' => 'Neutral Alert',
            'NEGLIGANCE ALERT' => 'Negligence Alert',
        ];

        $alertGroups = collect($aspectMap)->map(function ($label, $rawSubAspect) use ($alertRows) {
            $reasons = $alertRows
                ->filter(fn ($r) => $r->sub_aspect === $rawSubAspect)
                ->map(fn ($r) => ['reason' => $r->reason_name, 'qty' => (int) $r->qty])
                ->values();

            return [
                'aspect' => $label,
                'qty' => $reasons->sum('qty'),
                'reasons' => $reasons,
            ];
        })->values();

        return [
            'personnel' => [
                'active_track_user' => $activeTrackUser,
                'non_track_user' => $nonTrackUser,
                'total_beacon_user' => $totalBeaconUser,
            ],
            'absent_discipline' => [
                'work_days_target' => $workDaysTarget,
                'work_days_actual' => $workDaysActual,
                'work_days_pct' => $workDaysTarget > 0 ? round($workDaysActual / $workDaysTarget * 100, 2) : null,
            ],
            'alert_point' => [
                'groups' => $alertGroups,
                'total_raised' => $alertGroups->sum('qty'),
            ],
        ];
    }
}
