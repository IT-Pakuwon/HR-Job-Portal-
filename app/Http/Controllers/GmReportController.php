<?php

namespace App\Http\Controllers;

use App\Exports\GmReportExport;
use App\Models\BudgetDetail;
use App\Models\DepartmentFin;
use App\Models\MsEvent;
use App\Models\User;
use App\Services\BigQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class GmReportController extends Controller
{
    // ── PG Card constants ─────────────────────────────────────────────────────
    private const PGCARD_PROJECT = 'ifca-pkwjakarta';
    private const PGCARD_DATASET = 'pgcard';

    // ── Isort constants ───────────────────────────────────────────────────────
    private const ISORT_PROJECT     = 'ifca-pkwjakarta';
    private const ISORT_DATASET     = 'isort';
    private const ISORT_COMPANY_MAP = [
        'AW'  => 'GC',
        'EP'  => 'KK',
        'PSA' => 'PBM',
        'GPS' => 'PMB',
    ];

    // Maps HR company code → pgcard directory_code
    private const PGCARD_COMPANY_MAP = [
        'AW' => 'GC',
        'EP' => 'KK',
        'PSA' => 'PBM',
        'GPS' => 'PMB',
    ];

    // ── Valet Parking constants ─────────────────────────────────────────────────
    private const VALET_PROJECT = 'ifca-pkwjakarta';
    private const VALET_DATASET = 'valet_parking';

    // Maps HR company code → valet_parking places_id — 'PSA' (Blok M Plaza) is
    // intentionally absent, it has no valet service.
    private const VALET_PLACES_MAP = [
        'AW'  => 2,   // Gandaria City
        'EP'  => 1,   // Kota Kasablanka
        'GPS' => 6,   // Pakuwon Mall Bekasi
    ];

    private const VALET_PLACE_NAMES = [
        1 => 'Kota Kasablanka',
        2 => 'Gandaria City',
        6 => 'Pakuwon Mall Bekasi',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────────

    // BigQuery NUMERIC columns come back as Google\Cloud\BigQuery\Numeric
    // objects, not plain PHP numbers — casting one directly to float throws
    // a "could not be converted to float" warning and silently yields 1.
    private function bqFloat($val): float
    {
        return (float) (string) ($val ?? 0);
    }

    private function csvToArray($val): array
    {
        if ($val === null) {
            return [];
        }
        $s = trim((string) $val);
        if ($s === '') {
            return [];
        }

        return collect(explode(',', $s))
            ->map(fn ($x) => strtoupper(trim($x)))
            ->filter()->unique()->values()->all();
    }

    private function allowedCompanies(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $u = User::query()->where('username', $user->username)->first();

        return $this->csvToArray(optional($u)->cpny_id);
    }

    /**
     * Returns the pgcard directory_codes the current user may see.
     * null  = no restriction (show all malls)
     * []    = no access
     * ['GC', 'KK'] = filter to these codes only.
     */
    private function allowedPgcardMalls(?string $cpnyId): ?array
    {
        $map = self::PGCARD_COMPANY_MAP;
        $allowed = $this->allowedCompanies(); // HR company codes user is assigned to

        // Specific company filter requested
        if ($cpnyId && isset($map[$cpnyId])) {
            $requested = $map[$cpnyId];
            if (!empty($allowed) && !in_array($cpnyId, $allowed, true)) {
                return []; // user doesn't have access to this company
            }

            return [$requested];
        }

        // No specific filter — apply user's company restrictions
        if (empty($allowed)) {
            return null; // super-admin / no restriction, show all
        }

        return array_values(array_filter(
            array_map(fn ($code) => $map[$code] ?? null, $allowed)
        ));
    }

    private function allowedIsortSites(?string $cpnyId): ?array
    {
        $map     = self::ISORT_COMPANY_MAP;
        $allowed = $this->allowedCompanies();

        if ($cpnyId && isset($map[$cpnyId])) {
            if (!empty($allowed) && !in_array($cpnyId, $allowed, true)) {
                return [];
            }
            return [$map[$cpnyId]];
        }

        if (empty($allowed)) {
            return null; // no restriction — show all sites
        }

        return array_values(array_filter(
            array_map(fn ($code) => $map[$code] ?? null, $allowed)
        ));
    }

    /**
     * Returns the valet_parking places_id values the current user may see.
     * null = no restriction (show all sites with valet service)
     * []   = no access (includes the PSA case — Blok M Plaza has no valet)
     * [2]  = filtered to a single site
     *
     * Unlike allowedIsortSites()/allowedPgcardMalls(), an explicit $cpnyId not
     * present in the map returns [] immediately instead of falling through to
     * the "no restriction" branch — every HR company those two helpers know
     * about happens to exist in their maps, but PSA deliberately doesn't exist
     * in VALET_PLACES_MAP, so the fallthrough would incorrectly show
     * unrestricted valet data to a user who explicitly filtered to PSA.
     */
    private function allowedValetPlaces(?string $cpnyId): ?array
    {
        $map = self::VALET_PLACES_MAP;
        $allowed = $this->allowedCompanies();

        if ($cpnyId) {
            if (!isset($map[$cpnyId])) {
                return [];
            }
            if (!empty($allowed) && !in_array($cpnyId, $allowed, true)) {
                return [];
            }

            return [$map[$cpnyId]];
        }

        if (empty($allowed)) {
            return null; // no restriction — show all sites
        }

        return array_values(array_filter(
            array_map(fn ($code) => $map[$code] ?? null, $allowed)
        ));
    }

    private function applyCompanyFilter($q, array $allowed, ?string $cpnyId, string $column = 'cpny_id')
    {
        if ($cpnyId) {
            $cpnyId = strtoupper(trim($cpnyId));
            if (!empty($allowed) && !in_array($cpnyId, $allowed, true)) {
                return $q->whereRaw('1=0');
            }
            $q->where($column, $cpnyId);
        } elseif (!empty($allowed)) {
            $q->whereIn($column, $allowed);
        }

        return $q;
    }

    // ms_event.event_start_date/event_end_date describe when the event runs,
    // not when the row changed — filtering on overlap with the selected
    // period (rather than a single date column) matches how the event
    // calendar itself treats an event as "in" a given range.
    private function applyEventPeriodFilter($q, string $dateFrom, string $dateTo)
    {
        return $q->where('event_start_date', '<=', $dateTo)
            ->where('event_end_date', '>=', $dateFrom);
    }

    private function buildExprs(string $dateFrom, string $dateTo): array
    {
        $from = \Carbon\Carbon::parse($dateFrom);
        $to = \Carbon\Carbon::parse($dateTo);
        $monthFrom = (int) $from->format('n');
        $monthTo = (int) $to->format('n');
        $sameYear = $from->year === $to->year;

        // Full year or cross-year → annual total columns
        if (!$sameYear || ($monthFrom === 1 && $monthTo === 12)) {
            return [
                'budget' => 'COALESCE(SUM(totalbudget), 0)',
                'add' => 'COALESCE(SUM(totalbudget_add), 0)',
                'used' => 'COALESCE(SUM(total_used), 0)',
                'reserve' => 'COALESCE(SUM(total_reserve), 0)',
            ];
        }

        // Single or multi-month range → sum period columns
        $months = range($monthFrom, $monthTo);
        $buildSum = function (string $col) use ($months): string {
            $terms = array_map(
                fn ($m) => 'COALESCE(period'.str_pad($m, 2, '0', STR_PAD_LEFT)."_{$col}, 0)",
                $months
            );

            return 'COALESCE(SUM('.implode(' + ', $terms).'), 0)';
        };

        return [
            'budget' => $buildSum('budget'),
            'add' => $buildSum('budget_add'),
            'used' => $buildSum('used'),
            'reserve' => $buildSum('reserve'),
        ];
    }

    private function parseFilters(Request $request): array
    {
        $y = date('Y');
        $dateFrom = $request->input('date_from') ?: "{$y}-01-01";
        $dateTo = $request->input('date_to') ?: "{$y}-12-31";

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $request->input('cpny_id') ?: null,
            'depts' => array_filter((array) $request->input('departments', [])),
        ];
    }

    private function applyDateFilter($q, string $dateFrom, string $dateTo)
    {
        $fromYear = substr($dateFrom, 0, 4);
        $toYear = substr($dateTo, 0, 4);

        if ($fromYear === $toYear) {
            return $q->whereRaw('LEFT(perpost::text, 4) = ?', [$fromYear]);
        }

        return $q->whereRaw('LEFT(perpost::text, 4) BETWEEN ? AND ?', [$fromYear, $toYear]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.gm-report.dashboard', [
            'user' => $user,
        ]);
    }

    public function companies()
    {
        $allowed = $this->allowedCompanies();

        $q = BudgetDetail::query()->select('cpny_id')
            ->where('status', 'C')->whereNotNull('cpny_id');

        if (!empty($allowed)) {
            $q->whereIn('cpny_id', $allowed);
        }

        $list = $q->distinct()->orderBy('cpny_id')->pluck('cpny_id');

        return response()->json([
            'data' => $list,
            'locked' => count($allowed) === 1,
            'single' => count($allowed) === 1 ? $allowed[0] : null,
        ]);
    }

    // ── API: Available years (kept for reference; not used by filter bar) ─────

    public function budgetYears()
    {
        $allowed = $this->allowedCompanies();

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('perpost');
        if (!empty($allowed)) {
            $q->whereIn('cpny_id', $allowed);
        }

        $years = $q->selectRaw('DISTINCT LEFT(perpost::text, 4) AS year')
            ->orderByRaw('LEFT(perpost::text, 4) DESC')
            ->pluck('year')->filter()->values();

        return response()->json(['data' => $years]);
    }

    // ── API: Departments ──────────────────────────────────────────────────────

    public function departments(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();

        $q = BudgetDetail::query()
            ->select('department_fin_id')
            ->where('status', 'C')
            ->whereNotNull('department_fin_id');

        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyDateFilter($q, $dateFrom, $dateTo);

        $ids = $q->distinct()->orderBy('department_fin_id')->pluck('department_fin_id');

        if ($ids->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $names = DepartmentFin::query()
            ->whereIn('department_fin_id', $ids)
            ->get()
            ->keyBy('department_fin_id');

        $list = $ids->map(fn ($id) => [
            'id' => $id,
            'name' => optional($names->get($id))->department_name ?: $id,
        ]);

        return response()->json(['data' => $list]);
    }

    // ── API: Budget summary totals ────────────────────────────────────────────

    public function budgetSummary(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();
        $exprs = $this->buildExprs($dateFrom, $dateTo);

        $q = BudgetDetail::query()->where('status', 'C');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyDateFilter($q, $dateFrom, $dateTo);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $row = $q->selectRaw("
            {$exprs['budget']}  AS total_budget,
            {$exprs['add']}     AS total_budget_add,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve
        ")->first();

        $totalFinal = (float) ($row->total_budget ?? 0) + (float) ($row->total_budget_add ?? 0);
        $totalReserve = (float) ($row->total_reserve ?? 0);
        $totalUsed = (float) ($row->total_used ?? 0);
        $totalRemaining = $totalFinal - $totalReserve - $totalUsed;
        $utilizationPct = $totalFinal > 0 ? round(($totalUsed / $totalFinal) * 100, 1) : 0;

        return response()->json([
            'data' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_budget' => $totalFinal,
                'total_used' => $totalUsed,
                'total_reserve' => $totalReserve,
                'total_remaining' => $totalRemaining,
                'utilization_pct' => $utilizationPct,
            ],
        ]);
    }

    // ── API: Budget by department ─────────────────────────────────────────────

    public function budgetByDepartment(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $exprs = $this->buildExprs($dateFrom, $dateTo);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
                           THEN ROUND(({$exprs['used']} / ({$exprs['budget']}+{$exprs['add']})) * 100, 1)
                           ELSE 0 END";

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('department_fin_id');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyDateFilter($q, $dateFrom, $dateTo);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $rows = $q->selectRaw("
            department_fin_id,
            {$finalExpr}        AS total_final,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve,
            {$remExpr}          AS total_remaining,
            {$usedPct}          AS used_pct
        ")
        ->groupBy('department_fin_id')
        ->orderByRaw("{$exprs['used']} DESC")
        ->get();

        return response()->json(['data' => $rows]);
    }

    // ── API: Budget by company ────────────────────────────────────────────────
    // Route was already registered (gm.budget-by-company) but had no matching
    // method — the GM Insight panel needs a per-company breakdown alongside
    // the existing per-department one when "All Companies" is selected.

    public function budgetByCompany(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $exprs = $this->buildExprs($dateFrom, $dateTo);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
                           THEN ROUND(({$exprs['used']} / ({$exprs['budget']}+{$exprs['add']})) * 100, 1)
                           ELSE 0 END";

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('cpny_id');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyDateFilter($q, $dateFrom, $dateTo);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $rows = $q->selectRaw("
            cpny_id,
            {$finalExpr}        AS total_final,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve,
            {$remExpr}          AS total_remaining,
            {$usedPct}          AS used_pct
        ")
        ->groupBy('cpny_id')
        ->orderByRaw("{$exprs['used']} DESC")
        ->get();

        return response()->json(['data' => $rows]);
    }

    // ── API: Budget by activity ───────────────────────────────────────────────

    public function budgetByActivity(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $exprs = $this->buildExprs($dateFrom, $dateTo);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
                           THEN ROUND(({$exprs['used']} / ({$exprs['budget']}+{$exprs['add']})) * 100, 1)
                           ELSE 0 END";

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('activity_id');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyDateFilter($q, $dateFrom, $dateTo);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $rows = $q->selectRaw("
            activity_id,
            MAX(activity_descr) AS activity_descr,
            {$finalExpr}        AS total_final,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve,
            {$remExpr}          AS total_remaining,
            {$usedPct}          AS used_pct
        ")
        ->groupBy('activity_id')
        ->orderByRaw("{$exprs['used']} DESC")
        ->get();

        return response()->json(['data' => $rows]);
    }

    // ── Export helpers ────────────────────────────────────────────────────────

    private function formatIdr(float $val): string
    {
        $abs = abs($val);
        $pfx = $val < 0 ? '-' : '';
        if ($abs >= 1e12) {
            return $pfx.'Rp '.number_format($abs / 1e12, 1, ',', '.').'T';
        }
        if ($abs >= 1e9) {
            return $pfx.'Rp '.number_format($abs / 1e9, 1, ',', '.').'M';
        }
        if ($abs >= 1e6) {
            return $pfx.'Rp '.number_format($abs / 1e6, 1, ',', '.').'Jt';
        }

        return $pfx.'Rp '.number_format(round($abs), 0, ',', '.');
    }

    private function gatherExportData(Request $request): array
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();
        $exprs = $this->buildExprs($dateFrom, $dateTo);

        $baseQ = function () use ($allowed, $cpnyId, $dateFrom, $dateTo, $depts) {
            $q = BudgetDetail::query()->where('status', 'C');
            $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
            $q = $this->applyDateFilter($q, $dateFrom, $dateTo);
            if (!empty($depts)) {
                $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
            }

            return $q;
        };

        // Summary
        $row = $baseQ()->selectRaw("
            {$exprs['budget']}  AS total_budget,
            {$exprs['add']}     AS total_budget_add,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve
        ")->first();

        $totalFinal = (float) ($row->total_budget ?? 0) + (float) ($row->total_budget_add ?? 0);
        $totalReserve = (float) ($row->total_reserve ?? 0);
        $totalUsed = (float) ($row->total_used ?? 0);
        $totalRemaining = $totalFinal - $totalReserve - $totalUsed;
        $utilizationPct = $totalFinal > 0 ? round(($totalUsed / $totalFinal) * 100, 1) : 0;

        // By Department
        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
                           THEN ROUND(({$exprs['used']} / ({$exprs['budget']}+{$exprs['add']})) * 100, 1)
                           ELSE 0 END";

        $deptRows = $baseQ()->whereNotNull('department_fin_id')
            ->selectRaw("
                department_fin_id,
                {$finalExpr}        AS total_final,
                {$exprs['used']}    AS total_used,
                {$exprs['reserve']} AS total_reserve,
                {$remExpr}          AS total_remaining,
                {$usedPct}          AS used_pct
            ")
            ->groupBy('department_fin_id')
            ->orderByRaw("{$exprs['used']} DESC")
            ->get();

        // By Activity
        $actRows = $baseQ()->whereNotNull('activity_id')
            ->selectRaw("
                activity_id,
                MAX(activity_descr) AS activity_descr,
                {$finalExpr}        AS total_final,
                {$exprs['used']}    AS total_used,
                {$exprs['reserve']} AS total_reserve,
                {$remExpr}          AS total_remaining,
                {$usedPct}          AS used_pct
            ")
            ->groupBy('activity_id')
            ->orderByRaw("{$exprs['used']} DESC")
            ->get();

        // Monthly Trend
        $year = substr($dateFrom, 0, 4);
        $selects = ['COALESCE(SUM(totalbudget), 0) + COALESCE(SUM(totalbudget_add), 0) AS total_budget'];
        for ($m = 1; $m <= 12; ++$m) {
            $mm = str_pad($m, 2, '0', STR_PAD_LEFT);
            $selects[] = "COALESCE(SUM(period{$mm}_used), 0) AS m{$mm}";
        }

        $monthQ = BudgetDetail::query()->where('status', 'C');
        $monthQ = $this->applyCompanyFilter($monthQ, $allowed, $cpnyId);
        $monthQ->whereRaw('LEFT(perpost::text, 4) = ?', [$year]);
        if (!empty($depts)) {
            $monthQ->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $monthRaw = $monthQ->selectRaw(implode(', ', $selects))->first();
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthRows = [];
        $cumulative = 0;

        for ($m = 1; $m <= 12; ++$m) {
            $mm = str_pad($m, 2, '0', STR_PAD_LEFT);
            $used = (float) ($monthRaw->{"m{$mm}"} ?? 0);
            $cumulative += $used;
            $monthRows[] = ['month' => $monthNames[$m - 1], 'used' => round($used), 'cumulative' => round($cumulative)];
        }

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cpnyId' => $cpnyId,
            'year' => $year,
            'totalBudget' => round($totalFinal),
            'summary' => [
                'total_budget' => $totalFinal,
                'total_used' => $totalUsed,
                'total_reserve' => $totalReserve,
                'total_remaining' => $totalRemaining,
                'utilization_pct' => $utilizationPct,
            ],
            'deptRows' => $deptRows,
            'actRows' => $actRows,
            'monthRows' => $monthRows,
        ];
    }

    // ── Exports ───────────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $data = $this->gatherExportData($request);
        $data['fmt'] = fn (float $v) => $this->formatIdr($v);
        $pdf = Pdf::loadView('pages.gm-report.export-pdf', $data)
                       ->setPaper('a4', 'landscape');
        $filename = 'gm-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.pdf';

        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $data = $this->gatherExportData($request);
        $filename = 'gm-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, ['GM Report Dashboard']);
            fputcsv($out, ['Period', $data['dateFrom'].' to '.$data['dateTo']]);
            fputcsv($out, ['Company', $data['cpnyId'] ?: 'All Companies']);
            fputcsv($out, ['Generated', now()->format('d/m/Y H:i')]);
            fputcsv($out, []);

            fputcsv($out, ['=== SUMMARY ===']);
            fputcsv($out, ['Metric', 'Amount (IDR)', 'Formatted']);
            $s = $data['summary'];
            fputcsv($out, ['Total Budget',    round($s['total_budget']),    $this->formatIdr($s['total_budget'])]);
            fputcsv($out, ['Total Used',      round($s['total_used']),      $this->formatIdr($s['total_used'])]);
            fputcsv($out, ['Total Reserved',  round($s['total_reserve']),   $this->formatIdr($s['total_reserve'])]);
            fputcsv($out, ['Total Remaining', round($s['total_remaining']), $this->formatIdr($s['total_remaining'])]);
            fputcsv($out, ['Utilization %',   $s['utilization_pct'],        $s['utilization_pct'].'%']);
            fputcsv($out, []);

            fputcsv($out, ['=== BY DEPARTMENT ===']);
            fputcsv($out, ['Department', 'Budget (IDR)', 'Used (IDR)', 'Reserved (IDR)', 'Remaining (IDR)', 'Usage %']);
            foreach ($data['deptRows'] as $r) {
                fputcsv($out, [
                    $r->department_fin_id ?? '',
                    round((float) ($r->total_final ?? 0)),
                    round((float) ($r->total_used ?? 0)),
                    round((float) ($r->total_reserve ?? 0)),
                    round((float) ($r->total_remaining ?? 0)),
                    (float) ($r->used_pct ?? 0),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['=== BY ACTIVITY ===']);
            fputcsv($out, ['Activity', 'Budget (IDR)', 'Used (IDR)', 'Reserved (IDR)', 'Remaining (IDR)', 'Usage %']);
            foreach ($data['actRows'] as $r) {
                fputcsv($out, [
                    $r->activity_descr ?? $r->activity_id ?? '',
                    round((float) ($r->total_final ?? 0)),
                    round((float) ($r->total_used ?? 0)),
                    round((float) ($r->total_reserve ?? 0)),
                    round((float) ($r->total_remaining ?? 0)),
                    (float) ($r->used_pct ?? 0),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['=== MONTHLY TREND ('.$data['year'].') ===']);
            fputcsv($out, ['Month', 'Used (IDR)', 'Cumulative (IDR)']);
            foreach ($data['monthRows'] as $r) {
                fputcsv($out, [$r['month'], $r['used'], $r['cumulative']]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(Request $request)
    {
        $data = $this->gatherExportData($request);
        $filename = 'gm-report-'.$data['dateFrom'].'-to-'.$data['dateTo'].'.xlsx';

        return Excel::download(new GmReportExport($data), $filename);
    }

    // ── API: Isort — Available kaizen departments ────────────────────────────────

    public function isortAvailableDepts(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            $sql = <<<SQL
                SELECT DISTINCT kaizen_department
                FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  AND kaizen_department IS NOT NULL
                ORDER BY kaizen_department
            SQL;

            $rows = $bq->query($sql);
            $data = array_values(array_filter(array_map(
                fn ($r) => (string) ($r['kaizen_department'] ?? ''),
                $rows
            )));

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    private function buildIsortDeptFilter(Request $request): string
    {
        $depts = array_filter(array_map('trim', (array) $request->input('departments', [])));
        if (empty($depts)) {
            return '';
        }

        return "AND kaizen_department IN ('" . implode("','", array_map('addslashes', $depts)) . "')";
    }

    // ── API: Isort — Summary (from tb_kaizen_dashboard_summary_daily) ───────────

    public function isortSummary(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => ['total_case' => 0, 'total_open' => 0, 'total_closed' => 0, 'total_overdue' => 0, 'solved_hours' => 0, 'solved_case_count' => 0]]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            $sql = <<<SQL
                SELECT
                    COALESCE(SUM(total_case),               0) AS total_case,
                    COALESCE(SUM(total_open),               0) AS total_open,
                    COALESCE(SUM(total_closed),             0) AS total_closed,
                    COALESCE(SUM(total_overdue),            0) AS total_overdue,
                    COALESCE(SUM(solved_duration_hour_sum), 0) AS solved_hours,
                    COALESCE(SUM(solved_case_count),        0) AS solved_case_count
                FROM `{$p}.{$d}.tb_kaizen_dashboard_summary_daily`
                WHERE issue_dt BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  {$this->buildIsortDeptFilter($request)}
            SQL;

            $rows = $bq->query($sql);
            $row  = $rows[0] ?? [];

            // Per-company breakdown — only meaningful when "All Companies" is
            // selected, so the KPI strip can show a per-site table alongside
            // the aggregate totals instead of just one blended number.
            $bySite = [];
            if ($cpnyId === null) {
                $sqlSite = <<<SQL
                    SELECT
                        COALESCE(site, 'Unknown')               AS site,
                        COALESCE(SUM(total_case),               0) AS total_case,
                        COALESCE(SUM(total_open),               0) AS total_open,
                        COALESCE(SUM(total_closed),             0) AS total_closed,
                        COALESCE(SUM(total_overdue),            0) AS total_overdue,
                        COALESCE(SUM(solved_duration_hour_sum), 0) AS solved_hours,
                        COALESCE(SUM(solved_case_count),        0) AS solved_case_count
                    FROM `{$p}.{$d}.tb_kaizen_dashboard_summary_daily`
                    WHERE issue_dt BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      {$siteFilter}
                      {$this->buildIsortDeptFilter($request)}
                    GROUP BY site
                    ORDER BY total_case DESC
                SQL;

                foreach ($bq->query($sqlSite) as $sr) {
                    $siteTotal = (int) ($sr['total_case'] ?? 0);
                    $siteClosed = (int) ($sr['total_closed'] ?? 0);
                    $siteSolvedHours = (float) ($sr['solved_hours'] ?? 0);
                    $siteSolvedCount = (int) ($sr['solved_case_count'] ?? 0);

                    $bySite[] = [
                        'site' => (string) ($sr['site'] ?? 'Unknown'),
                        'total_case' => $siteTotal,
                        'total_open' => (int) ($sr['total_open'] ?? 0),
                        'total_closed' => $siteClosed,
                        'total_overdue' => (int) ($sr['total_overdue'] ?? 0),
                        'avg_resolution_hours' => $siteSolvedCount > 0 ? round($siteSolvedHours / $siteSolvedCount, 1) : 0,
                        'closure_rate' => $siteTotal > 0 ? round(($siteClosed / $siteTotal) * 100) : 0,
                    ];
                }
            }

            return response()->json([
                'data' => [
                    'total_case'        => (int)   ($row['total_case']        ?? 0),
                    'total_open'        => (int)   ($row['total_open']        ?? 0),
                    'total_closed'      => (int)   ($row['total_closed']      ?? 0),
                    'total_overdue'     => (int)   ($row['total_overdue']     ?? 0),
                    'solved_hours'      => (float) ($row['solved_hours']      ?? 0),
                    'solved_case_count' => (int)   ($row['solved_case_count'] ?? 0),
                    'by_site'           => $bySite,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Kaizen by Type (name) ───────────────────────────────────────

    public function isortKaizenByType(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId     = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites      = $this->allowedIsortSites($cpnyId);
            $deptFilter = $this->buildIsortDeptFilter($request);
            $stacked    = $cpnyId === null; // stacked when user hasn't filtered by specific company

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => [], 'stacked' => false, 'all_sites' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            if ($stacked) {
                // Return per-site breakdown for stacked chart
                $sql = <<<SQL
                    SELECT
                        COALESCE(kaizen_type, 'Unknown') AS kaizen_type,
                        COALESCE(site, 'Unknown')        AS site,
                        COUNT(*) AS cnt
                    FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                    WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      {$deptFilter}
                    GROUP BY kaizen_type, site
                SQL;

                $allTypes = [];
                $allSites = [];
                foreach ($bq->query($sql) as $row) {
                    $type = (string) ($row['kaizen_type'] ?? 'Unknown');
                    $site = (string) ($row['site']        ?? 'Unknown');
                    $cnt  = (int)    ($row['cnt']          ?? 0);
                    if (!isset($allTypes[$type])) {
                        $allTypes[$type] = ['kaizen_type' => $type, 'total' => 0, 'by_site' => []];
                    }
                    $allTypes[$type]['total']          += $cnt;
                    $allTypes[$type]['by_site'][$site]  = ($allTypes[$type]['by_site'][$site] ?? 0) + $cnt;
                    $allSites[$site] = true;
                }
                usort($allTypes, fn ($a, $b) => $b['total'] - $a['total']);
                $sites_list = array_keys($allSites);
                sort($sites_list);

                return response()->json([
                    'data'      => array_values(array_slice($allTypes, 0, 5)),
                    'stacked'   => true,
                    'all_sites' => $sites_list,
                ]);
            }

            // Single-company: simple aggregation
            $sql = <<<SQL
                SELECT
                    COALESCE(kaizen_type, 'Unknown') AS kaizen_type,
                    COUNT(*) AS total
                FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  {$deptFilter}
                GROUP BY kaizen_type
                ORDER BY total DESC
                LIMIT 5
            SQL;

            $data = [];
            foreach ($bq->query($sql) as $row) {
                $data[] = [
                    'kaizen_type' => (string) ($row['kaizen_type'] ?? 'Unknown'),
                    'total'       => (int)    ($row['total']       ?? 0),
                ];
            }

            return response()->json(['data' => $data, 'stacked' => false, 'all_sites' => []]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'stacked' => false, 'all_sites' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Incidents by Name ────────────────────────────────────────────

    public function isortIncidentsByName(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => [], 'stacked' => false, 'all_sites' => []]);
            }

            $stacked    = $cpnyId === null; // stacked when no specific company selected
            $bq         = new BigQueryService();
            $p          = self::ISORT_PROJECT;
            $d          = self::ISORT_DATASET;
            $deptFilter = $this->buildIsortDeptFilter($request);

            if ($stacked) {
                // Return per-site breakdown — join via kaizen_id to get site from tb_detail_kaizen_dashboard
                $sql = <<<SQL
                    SELECT
                        COALESCE(i.incident_name, 'Unknown') AS incident_name,
                        COALESCE(dd.site, 'Unknown')         AS site,
                        COUNT(*) AS cnt
                    FROM `{$p}.{$d}.isort_kaizen_src` k
                    LEFT JOIN (
                        SELECT id, MAX(incident_name) AS incident_name
                        FROM `{$p}.{$d}.isort_incidents_src`
                        GROUP BY id
                    ) i ON k.kejadian_id = i.id
                    INNER JOIN (
                        SELECT DISTINCT kaizen_id, site
                        FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                        WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                          {$deptFilter}
                    ) dd ON k.kaizen_id = dd.kaizen_id
                    WHERE DATE(k.issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      AND i.incident_name IS NOT NULL
                    GROUP BY i.incident_name, dd.site
                SQL;

                $allIncidents = [];
                $allSites     = [];
                foreach ($bq->query($sql) as $row) {
                    $name = (string) ($row['incident_name'] ?? 'Unknown');
                    $site = (string) ($row['site']          ?? 'Unknown');
                    $cnt  = (int)    ($row['cnt']            ?? 0);
                    if (!isset($allIncidents[$name])) {
                        $allIncidents[$name] = ['incident_name' => $name, 'total' => 0, 'by_site' => []];
                    }
                    $allIncidents[$name]['total']          += $cnt;
                    $allIncidents[$name]['by_site'][$site]  = ($allIncidents[$name]['by_site'][$site] ?? 0) + $cnt;
                    $allSites[$site] = true;
                }
                usort($allIncidents, fn ($a, $b) => $b['total'] - $a['total']);
                $sites_list = array_keys($allSites);
                sort($sites_list);

                return response()->json([
                    'data'      => array_values(array_slice($allIncidents, 0, 10)),
                    'stacked'   => true,
                    'all_sites' => $sites_list,
                ]);
            }

            // Single-company: existing logic
            $siteJoin = ($sites !== null || $deptFilter !== '')
                ? "INNER JOIN (SELECT DISTINCT kaizen_id FROM `{$p}.{$d}.tb_detail_kaizen_dashboard` WHERE 1=1"
                  . ($sites !== null ? " AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')" : '')
                  . " {$deptFilter}) _sf ON k.kaizen_id = _sf.kaizen_id"
                : '';

            $sql = <<<SQL
                SELECT
                    COALESCE(i.incident_name, 'Unknown') AS incident_name,
                    COUNT(*) AS total
                FROM `{$p}.{$d}.isort_kaizen_src` k
                LEFT JOIN (
                    SELECT id, MAX(incident_name) AS incident_name
                    FROM `{$p}.{$d}.isort_incidents_src`
                    GROUP BY id
                ) i ON k.kejadian_id = i.id
                {$siteJoin}
                WHERE DATE(k.issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  AND i.incident_name IS NOT NULL
                GROUP BY i.incident_name
                ORDER BY total DESC
                LIMIT 10
            SQL;

            $data = [];
            foreach ($bq->query($sql) as $row) {
                $data[] = [
                    'incident_name' => (string) ($row['incident_name'] ?? 'Unknown'),
                    'total'         => (int)    ($row['total']         ?? 0),
                ];
            }

            return response()->json(['data' => $data, 'stacked' => false, 'all_sites' => []]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'stacked' => false, 'all_sites' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Top 10 Dept with type + incident breakdown ─────────────────

    public function isortDeptSummary(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';
            $deptFilter = $this->buildIsortDeptFilter($request);

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            // Query 1: kaizen_type breakdown per department
            $sqlType = <<<SQL
                SELECT
                    kaizen_department,
                    COALESCE(kaizen_type, 'Unknown') AS kaizen_type,
                    COUNT(*) AS cnt
                FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  {$deptFilter}
                GROUP BY kaizen_department, kaizen_type
                ORDER BY kaizen_department, cnt DESC
            SQL;

            // Query 2: incident_name breakdown per department (via kaizen_id join)
            $sqlIncident = <<<SQL
                SELECT
                    dd.kaizen_department,
                    COALESCE(i.incident_name, 'Unknown') AS incident_name,
                    COUNT(*) AS cnt
                FROM `{$p}.{$d}.isort_kaizen_src` k
                INNER JOIN (
                    SELECT DISTINCT kaizen_id, kaizen_department
                    FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                    WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      {$siteFilter}
                      {$deptFilter}
                ) dd ON k.kaizen_id = dd.kaizen_id
                LEFT JOIN (
                    SELECT id, MAX(incident_name) AS incident_name
                    FROM `{$p}.{$d}.isort_incidents_src`
                    GROUP BY id
                ) i ON k.kejadian_id = i.id
                WHERE i.incident_name IS NOT NULL
                GROUP BY dd.kaizen_department, i.incident_name
                ORDER BY dd.kaizen_department, cnt DESC
            SQL;

            $typeRows     = $bq->query($sqlType);
            $incidentRows = $bq->query($sqlIncident);

            $depts    = [];
            $stacked  = $cpnyId === null;
            $allSites = [];

            foreach ($typeRows as $row) {
                $dept = (string) ($row['kaizen_department'] ?? '');
                if (!isset($depts[$dept])) {
                    $depts[$dept] = ['department' => $dept, 'total' => 0, 'by_type' => [], 'by_incident' => [], 'by_site' => []];
                }
                $cnt = (int) ($row['cnt'] ?? 0);
                $depts[$dept]['total'] += $cnt;
                $depts[$dept]['by_type'][] = [
                    'kaizen_type' => (string) ($row['kaizen_type'] ?? 'Unknown'),
                    'count'       => $cnt,
                ];
            }

            foreach ($incidentRows as $row) {
                $dept = (string) ($row['kaizen_department'] ?? '');
                if (!isset($depts[$dept])) {
                    $depts[$dept] = ['department' => $dept, 'total' => 0, 'by_type' => [], 'by_incident' => [], 'by_site' => []];
                }
                $depts[$dept]['by_incident'][] = [
                    'incident_name' => (string) ($row['incident_name'] ?? 'Unknown'),
                    'count'         => (int)    ($row['cnt']           ?? 0),
                ];
            }

            // When all companies: add per-site totals per department for stacked bars
            if ($stacked) {
                $sqlSite = <<<SQL
                    SELECT
                        kaizen_department,
                        COALESCE(site, 'Unknown') AS site,
                        COUNT(*) AS cnt
                    FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                    WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      {$deptFilter}
                    GROUP BY kaizen_department, site
                SQL;

                foreach ($bq->query($sqlSite) as $row) {
                    $dept = (string) ($row['kaizen_department'] ?? '');
                    $site = (string) ($row['site']              ?? 'Unknown');
                    $cnt  = (int)    ($row['cnt']               ?? 0);
                    if (isset($depts[$dept])) {
                        $depts[$dept]['by_site'][$site] = ($depts[$dept]['by_site'][$site] ?? 0) + $cnt;
                    }
                    $allSites[$site] = true;
                }
            }

            usort($depts, fn ($a, $b) => $b['total'] - $a['total']);
            $sites_list = array_keys($allSites);
            sort($sites_list);

            return response()->json([
                'data'      => array_values(array_slice($depts, 0, 10)),
                'stacked'   => $stacked,
                'all_sites' => $sites_list,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Top 10 Problem Areas (from tb_detail_kaizen_dashboard) ─────────

    public function isortTopAreas(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId     = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites      = $this->allowedIsortSites($cpnyId);
            $deptFilter = $this->buildIsortDeptFilter($request);
            $stacked    = $cpnyId === null;

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => [], 'stacked' => false, 'all_sites' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            if ($stacked) {
                $sql = <<<SQL
                    SELECT
                        COALESCE(area_name, 'Unknown') AS area_name,
                        COALESCE(site, 'Unknown')      AS site,
                        COUNT(*)                       AS cnt
                    FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                    WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                      AND area_name IS NOT NULL
                      {$deptFilter}
                    GROUP BY area_name, site
                SQL;

                $allAreas = [];
                $allSites = [];
                foreach ($bq->query($sql) as $row) {
                    $area = (string) ($row['area_name'] ?? 'Unknown');
                    $site = (string) ($row['site']      ?? 'Unknown');
                    $cnt  = (int)    ($row['cnt']        ?? 0);
                    if (!isset($allAreas[$area])) {
                        $allAreas[$area] = ['area_name' => $area, 'total' => 0, 'by_site' => []];
                    }
                    $allAreas[$area]['total']          += $cnt;
                    $allAreas[$area]['by_site'][$site]  = ($allAreas[$area]['by_site'][$site] ?? 0) + $cnt;
                    $allSites[$site] = true;
                }
                usort($allAreas, fn ($a, $b) => $b['total'] - $a['total']);
                $sites_list = array_keys($allSites);
                sort($sites_list);

                return response()->json([
                    'data'      => array_values(array_slice($allAreas, 0, 10)),
                    'stacked'   => true,
                    'all_sites' => $sites_list,
                ]);
            }

            $sql = <<<SQL
                SELECT
                    COALESCE(area_name, 'Unknown') AS area_name,
                    COUNT(*) AS total
                FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  AND area_name IS NOT NULL
                  {$siteFilter}
                  {$deptFilter}
                GROUP BY area_name
                ORDER BY total DESC
                LIMIT 10
            SQL;

            $data = [];
            foreach ($bq->query($sql) as $row) {
                $data[] = [
                    'area_name' => (string) ($row['area_name'] ?? 'Unknown'),
                    'total'     => (int)    ($row['total']     ?? 0),
                ];
            }

            return response()->json(['data' => $data, 'stacked' => false, 'all_sites' => []]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'stacked' => false, 'all_sites' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Monthly trend (from tb_kaizen_dashboard_summary_daily) ─────────

    public function isortMonthlyTrend(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';
            $deptFilter = $this->buildIsortDeptFilter($request);

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            $sql = <<<SQL
                SELECT
                    FORMAT_DATE('%Y-%m', issue_dt)   AS month,
                    COALESCE(SUM(total_case),     0) AS total_case,
                    COALESCE(SUM(total_closed),   0) AS total_closed,
                    COALESCE(SUM(total_open),     0) AS total_open,
                    COALESCE(SUM(total_overdue),  0) AS total_overdue
                FROM `{$p}.{$d}.tb_kaizen_dashboard_summary_daily`
                WHERE issue_dt BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  {$deptFilter}
                GROUP BY month
                ORDER BY month
            SQL;

            $rows = $bq->query($sql);
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'month'         => (string) ($row['month']         ?? ''),
                    'total_case'    => (int)    ($row['total_case']    ?? 0),
                    'total_closed'  => (int)    ($row['total_closed']  ?? 0),
                    'total_open'    => (int)    ($row['total_open']    ?? 0),
                    'total_overdue' => (int)    ($row['total_overdue'] ?? 0),
                ];
            }

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Isort — Detail Records (from tb_detail_kaizen_dashboard) ────────────

    public function isortDetail(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $sites  = $this->allowedIsortSites($cpnyId);

            if ($sites !== null && empty($sites)) {
                return response()->json(['data' => []]);
            }

            $siteFilter = $sites !== null
                ? "AND site IN ('" . implode("','", array_map('addslashes', $sites)) . "')"
                : '';

            $bq = new BigQueryService();
            $p  = self::ISORT_PROJECT;
            $d  = self::ISORT_DATASET;

            $sql = <<<SQL
                SELECT
                    site,
                    kaizen_id,
                    DATE(issue_date)  AS issue_date,
                    DATE(solved_date) AS solved_date,
                    kaizen_department,
                    kaizen_type,
                    area_name,
                    location_name,
                    item,
                    subitem,
                    keterangan,
                    submitter,
                    dept_submitter,
                    status,
                    kaizen_close_by,
                    dept_kaizen_close_by
                FROM `{$p}.{$d}.tb_detail_kaizen_dashboard`
                WHERE DATE(issue_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$siteFilter}
                  {$this->buildIsortDeptFilter($request)}
                ORDER BY issue_date DESC
                LIMIT 1000
            SQL;

            $rows = $bq->query($sql);
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'site'                 => (string) ($row['site']                 ?? ''),
                    'kaizen_id'            => (int)    ($row['kaizen_id']            ?? 0),
                    'issue_date'           => (string) ($row['issue_date']           ?? ''),
                    'solved_date'          => (string) ($row['solved_date']          ?? ''),
                    'kaizen_department'    => (string) ($row['kaizen_department']    ?? ''),
                    'kaizen_type'          => (string) ($row['kaizen_type']          ?? ''),
                    'area_name'            => (string) ($row['area_name']            ?? ''),
                    'location_name'        => (string) ($row['location_name']        ?? ''),
                    'item'                 => (string) ($row['item']                 ?? ''),
                    'subitem'              => (string) ($row['subitem']              ?? ''),
                    'keterangan'           => (string) ($row['keterangan']           ?? ''),
                    'submitter'            => (string) ($row['submitter']            ?? ''),
                    'dept_submitter'       => (string) ($row['dept_submitter']       ?? ''),
                    'status'               => (string) ($row['status']               ?? ''),
                    'kaizen_close_by'      => (string) ($row['kaizen_close_by']      ?? ''),
                    'dept_kaizen_close_by' => (string) ($row['dept_kaizen_close_by'] ?? ''),
                ];
            }

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: Cumulative budget used per month ─────────────────────────────────

    // ── API: Parking - Valet ─────────────────────────────────────────────────

    public function valetKpiSummary(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $places = $this->allowedValetPlaces($cpnyId);

            $empty = [
                'total_valet' => 0, 'total_income_service' => 0, 'total_income_parking' => 0,
                'total_member' => 0, 'avg_duration_minutes' => 0, 'daily_avg_turnover' => 0, 'by_place' => [],
            ];

            if ($places !== null && empty($places)) {
                return response()->json(['data' => $empty]);
            }

            $placeFilter = $places !== null
                ? 'AND places_id IN (' . implode(',', array_map('intval', $places)) . ')'
                : '';

            $bq = new BigQueryService();
            $p = self::VALET_PROJECT;
            $d = self::VALET_DATASET;

            $sqlTotals = <<<SQL
                SELECT
                    COUNT(*)                                          AS total_valet,
                    COALESCE(SUM(service_amount), 0)                  AS total_income_service,
                    COALESCE(SUM(parking_amount), 0)                  AS total_income_parking,
                    COUNTIF(voucher_status = 'MEMBER')                AS total_member,
                    COALESCE(AVG(duration_hour * 60 + duration_minute), 0) AS avg_duration_minutes
                FROM `{$p}.{$d}.valet_parking_valets_src`
                WHERE checkin_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$placeFilter}
            SQL;

            $sqlByPlace = <<<SQL
                SELECT
                    places_id,
                    COUNT(*)                         AS total_valet,
                    COALESCE(SUM(service_amount), 0) AS total_income_service,
                    COALESCE(SUM(parking_amount), 0) AS total_income_parking
                FROM `{$p}.{$d}.valet_parking_valets_src`
                WHERE checkin_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$placeFilter}
                GROUP BY places_id
                ORDER BY total_valet DESC
            SQL;

            $row = ($bq->query($sqlTotals))[0] ?? [];

            $days = max(1, \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1);
            $totalValet = (int) ($row['total_valet'] ?? 0);

            $byPlace = [];
            foreach ($bq->query($sqlByPlace) as $pr) {
                $placeId = (int) ($pr['places_id'] ?? 0);
                $byPlace[] = [
                    'place_id' => $placeId,
                    'place_name' => self::VALET_PLACE_NAMES[$placeId] ?? "Place #{$placeId}",
                    'total_valet' => (int) ($pr['total_valet'] ?? 0),
                    'total_income_service' => $this->bqFloat($pr['total_income_service'] ?? null),
                    'total_income_parking' => $this->bqFloat($pr['total_income_parking'] ?? null),
                ];
            }

            return response()->json([
                'data' => [
                    'total_valet' => $totalValet,
                    'total_income_service' => $this->bqFloat($row['total_income_service'] ?? null),
                    'total_income_parking' => $this->bqFloat($row['total_income_parking'] ?? null),
                    'total_member' => (int) ($row['total_member'] ?? 0),
                    'avg_duration_minutes' => (float) ($row['avg_duration_minutes'] ?? 0),
                    'daily_avg_turnover' => round($totalValet / $days, 1),
                    'by_place' => $byPlace,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['data' => $empty ?? [], 'error' => $e->getMessage()]);
        }
    }

    public function valetVoucherRedemption(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $places = $this->allowedValetPlaces($cpnyId);

            $empty = ['total_valet' => 0, 'redeemed' => 0, 'redemption_rate' => 0, 'by_status' => []];

            if ($places !== null && empty($places)) {
                return response()->json(['data' => $empty]);
            }

            $placeFilter = $places !== null
                ? 'AND places_id IN (' . implode(',', array_map('intval', $places)) . ')'
                : '';

            $bq = new BigQueryService();
            $p = self::VALET_PROJECT;
            $d = self::VALET_DATASET;

            // voucher_code is currently unpopulated across the whole table, so
            // "redeemed" is derived from voucher_status instead (verified against
            // live data — every non-blank voucher_status row is a MEMBER free-
            // parking redemption today, but this stays generic for future statuses).
            $sql = <<<SQL
                SELECT
                    COALESCE(NULLIF(voucher_status, ''), 'None') AS status,
                    COUNT(*)                                     AS status_count
                FROM `{$p}.{$d}.valet_parking_valets_src`
                WHERE checkin_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$placeFilter}
                GROUP BY status
            SQL;

            $rows = $bq->query($sql);

            $totalValet = 0;
            $redeemed = 0;
            $byStatus = [];
            foreach ($rows as $r) {
                $count = (int) ($r['status_count'] ?? 0);
                $status = (string) ($r['status'] ?? 'None');
                $totalValet += $count;
                if ($status !== 'None') {
                    $redeemed += $count;
                }
                $byStatus[] = ['status' => $status, 'count' => $count];
            }

            return response()->json([
                'data' => [
                    'total_valet' => $totalValet,
                    'redeemed' => $redeemed,
                    'redemption_rate' => $totalValet > 0 ? round(($redeemed / $totalValet) * 100, 1) : 0,
                    'by_status' => $byStatus,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['data' => $empty ?? [], 'error' => $e->getMessage()]);
        }
    }

    public function valetPeakHours(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $places = $this->allowedValetPlaces($cpnyId);

            $hours = array_fill(0, 24, 0);

            if ($places !== null && empty($places)) {
                return response()->json(['data' => array_values($hours)]);
            }

            $placeFilter = $places !== null
                ? 'AND places_id IN (' . implode(',', array_map('intval', $places)) . ')'
                : '';

            $bq = new BigQueryService();
            $p = self::VALET_PROJECT;
            $d = self::VALET_DATASET;

            $sql = <<<SQL
                SELECT
                    EXTRACT(HOUR FROM checkin_time) AS hr,
                    COUNT(*)                        AS total_valet
                FROM `{$p}.{$d}.valet_parking_valets_src`
                WHERE checkin_date BETWEEN '{$dateFrom}' AND '{$dateTo}'
                  {$placeFilter}
                GROUP BY hr
            SQL;

            foreach ($bq->query($sql) as $r) {
                $hr = (int) ($r['hr'] ?? -1);
                if ($hr >= 0 && $hr <= 23) {
                    $hours[$hr] = (int) ($r['total_valet'] ?? 0);
                }
            }

            return response()->json(['data' => array_values($hours)]);
        } catch (\Throwable $e) {
            return response()->json(['data' => array_values($hours ?? array_fill(0, 24, 0)), 'error' => $e->getMessage()]);
        }
    }

    // ── API: Event ─────────────────────────────────────────────────────────────

    private function baseEventQuery(string $dateFrom, string $dateTo, ?string $cpnyId)
    {
        $allowed = $this->allowedCompanies();

        $q = MsEvent::query()->where('status', 'A');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId, 'cpnyid');
        $q = $this->applyEventPeriodFilter($q, $dateFrom, $dateTo);

        return $q;
    }

    public function eventSummary(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId] = $this->parseFilters($request);

        $rows = $this->baseEventQuery($dateFrom, $dateTo, $cpnyId)
            ->selectRaw('event_status, COUNT(*) AS cnt, COALESCE(SUM(event_total_contract), 0) AS total_contract')
            ->groupBy('event_status')
            ->get()
            ->keyBy('event_status');

        $totalContract = 0;
        $totalCount = 0;
        $paidAmt = 0;
        foreach (\App\Http\Controllers\EventCalendarController::EVENT_STATUSES as $status => $_) {
            $row = $rows->get($status);
            $cnt = (int) ($row->cnt ?? 0);
            $amt = (float) ($row->total_contract ?? 0);
            if ($status === 'Paid') {
                $paidAmt = $amt;
            }
            $totalContract += $amt;
            $totalCount += $cnt;
        }

        return response()->json([
            'data' => [
                'total_contract' => $totalContract,
                'total_count' => $totalCount,
                'total_paid' => $paidAmt,
                'avg_contract' => $totalCount > 0 ? round($totalContract / $totalCount) : 0,
            ],
        ]);
    }

    /**
     * Event count by status — when no company filter is applied, broken
     * down per company (mirrors the Isort "stacked" pattern) so the chart
     * can render one bar per company instead of a single aggregate bar.
     */
    public function eventStatusByCompany(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $stacked = $cpnyId === null;

        // Row-level (not aggregated) so the tooltip can list individual event
        // names — ordered by contract value so the capped "top N" list below
        // surfaces the biggest deals first.
        $rows = $this->baseEventQuery($dateFrom, $dateTo, $cpnyId)
            ->select('event_status', 'cpnyid', 'event_name', 'event_total_contract')
            ->orderByDesc('event_total_contract')
            ->get();

        $byStatus = [];
        foreach (\App\Http\Controllers\EventCalendarController::EVENT_STATUSES as $status => $_) {
            $byStatus[$status] = ['status' => $status, 'total' => 0, 'total_contract' => 0, 'by_site' => [], 'events' => []];
        }

        $companies = [];
        $eventsSeen = [];
        foreach ($rows as $row) {
            $status = (string) $row->event_status;
            $cpny = (string) $row->cpnyid;
            $contract = (float) $row->event_total_contract;

            if (!isset($byStatus[$status])) {
                $byStatus[$status] = ['status' => $status, 'total' => 0, 'total_contract' => 0, 'by_site' => [], 'events' => []];
            }
            $byStatus[$status]['total'] += 1;
            $byStatus[$status]['total_contract'] += $contract;
            $byStatus[$status]['by_site'][$cpny] = ($byStatus[$status]['by_site'][$cpny] ?? 0) + 1;
            $eventsSeen[$status] = ($eventsSeen[$status] ?? 0) + 1;
            $companies[$cpny] = true;

            // Cap the per-status event list the tooltip renders — it's already
            // sorted by contract value, so the top 8 are the most relevant.
            if (count($byStatus[$status]['events']) < 8) {
                $byStatus[$status]['events'][] = [
                    'name' => (string) $row->event_name,
                    'cpny' => $cpny,
                    'contract' => $contract,
                ];
            }
        }

        foreach ($byStatus as $status => &$s) {
            $s['events_more'] = max(0, ($eventsSeen[$status] ?? 0) - count($s['events']));
        }
        unset($s);

        $companies = array_keys($companies);
        sort($companies);

        return response()->json([
            'data' => array_values($byStatus),
            'stacked' => $stacked,
            'all_sites' => $stacked ? $companies : [],
        ]);
    }

    public function eventByType(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId] = $this->parseFilters($request);

        $rows = $this->baseEventQuery($dateFrom, $dateTo, $cpnyId)
            ->selectRaw('
                event_type,
                COUNT(*) AS cnt,
                COALESCE(SUM(event_total_contract), 0) AS total_contract,
                COALESCE(AVG(event_total_contract), 0) AS avg_contract
            ')
            ->groupBy('event_type')
            ->get()
            ->keyBy('event_type');

        $data = [];
        foreach (\App\Http\Controllers\EventCalendarController::EVENT_TYPES as $type => $_) {
            $row = $rows->get($type);
            $data[] = [
                'event_type' => $type,
                'count' => (int) ($row->cnt ?? 0),
                'total_contract' => (float) ($row->total_contract ?? 0),
                'avg_contract' => (float) ($row->avg_contract ?? 0),
            ];
        }
        usort($data, fn ($a, $b) => $b['total_contract'] <=> $a['total_contract']);

        return response()->json(['data' => $data]);
    }

    /**
     * Paid events for the Gantt-style timeline — every event carries signed
     * start_days/end_days (today = 0) so the frontend can position it on a
     * shared axis that auto-scales to the full ongoing/upcoming/past range,
     * instead of a fixed window that would hide anything outside it.
     */
    public function eventStatusStrip(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $today = \Carbon\Carbon::today();

        // Only Paid events — Booked/Confirmed events aren't a confirmed
        // commitment yet, so they'd skew the ongoing/upcoming/past pulse.
        $rows = $this->baseEventQuery($dateFrom, $dateTo, $cpnyId)
            ->where('event_status', 'Paid')
            ->select('cpnyid', 'event_name', 'event_total_contract', 'event_start_date', 'event_end_date')
            ->get();

        $events = [];
        $groupCounts = [];
        foreach ($rows as $row) {
            $cpny = (string) $row->cpnyid;
            $startDays = (int) $today->diffInDays($row->event_start_date, false);
            $endDays = (int) $today->diffInDays($row->event_end_date, false);

            if ($row->event_start_date->lte($today) && $row->event_end_date->gte($today)) {
                $bucket = 'ongoing';
                $days = abs($endDays);
            } elseif ($row->event_start_date->gt($today)) {
                $bucket = 'upcoming';
                $days = abs($startDays);
            } else {
                $bucket = 'past';
                $days = abs($endDays);
            }

            if (!isset($groupCounts[$cpny])) {
                $groupCounts[$cpny] = ['ongoing' => 0, 'upcoming' => 0, 'past' => 0];
            }
            $groupCounts[$cpny][$bucket] += 1;

            $events[] = [
                'name' => (string) $row->event_name,
                'cpny' => $cpny,
                'code' => self::PGCARD_COMPANY_MAP[$cpny] ?? $cpny,
                'bucket' => $bucket,
                'days' => $days,
                'start_days' => $startDays,
                'end_days' => $endDays,
                'contract' => (float) $row->event_total_contract,
            ];
        }

        $bucketOrder = ['ongoing' => 0, 'upcoming' => 1, 'past' => 2];
        usort($events, function ($a, $b) use ($bucketOrder) {
            return $bucketOrder[$a['bucket']] <=> $bucketOrder[$b['bucket']]
                ?: $a['start_days'] <=> $b['start_days'];
        });

        return response()->json([
            'data' => $events,
            'groups' => $groupCounts,
        ]);
    }

    // ── API: PG Card — Top 10 customers per mall ──────────────────────────────

    public function pgcardTopCustomers(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls = $this->allowedPgcardMalls($cpnyId);

            if ($malls !== null && empty($malls)) {
                return response()->json(['data' => []]);
            }

            $mallCond = $malls !== null
                ? "AND d.directory_code IN ('".implode("','", array_map('addslashes', $malls))."')"
                : '';

            $bq = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            $sql = <<<SQL
                WITH
                top_merch_txn AS (
                    SELECT directory_id, member_id, merchant_id FROM (
                        SELECT directory_id, member_id, merchant_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, member_id ORDER BY COUNT(*) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        WHERE DATE(transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                        GROUP BY directory_id, member_id, merchant_id
                    ) WHERE rn = 1
                ),
                top_merch_amt AS (
                    SELECT directory_id, member_id, merchant_id FROM (
                        SELECT directory_id, member_id, merchant_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, member_id ORDER BY SUM(amount) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        WHERE DATE(transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                        GROUP BY directory_id, member_id, merchant_id
                    ) WHERE rn = 1
                )
                SELECT mall_code, mall_name, label, value, total_amount, txn_rank, amt_rank,
                       top_merchant_txn, top_merchant_amt
                FROM (
                    SELECT
                        d.directory_code                                        AS mall_code,
                        d.directory_name                                        AS mall_name,
                        COALESCE(MAX(m.fullname), CAST(t.member_id AS STRING))  AS label,
                        COUNT(*)                                                AS value,
                        SUM(t.amount)                                           AS total_amount,
                        ROW_NUMBER() OVER (
                            PARTITION BY t.directory_id ORDER BY COUNT(*) DESC
                        )                                                       AS txn_rank,
                        ROW_NUMBER() OVER (
                            PARTITION BY t.directory_id ORDER BY SUM(t.amount) DESC
                        )                                                       AS amt_rank,
                        MAX(mt.merchant_name)                                   AS top_merchant_txn,
                        MAX(ma.merchant_name)                                   AS top_merchant_amt
                    FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                    INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                        ON d.id = t.directory_id {$mallCond}
                    LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` m
                        ON m.id = t.member_id
                    LEFT JOIN top_merch_txn tt
                        ON tt.directory_id = t.directory_id AND tt.member_id = t.member_id
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mt ON mt.id = tt.merchant_id
                    LEFT JOIN top_merch_amt ta
                        ON ta.directory_id = t.directory_id AND ta.member_id = t.member_id
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` ma ON ma.id = ta.merchant_id
                    WHERE DATE(t.transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                    GROUP BY t.directory_id, t.member_id, d.directory_code, d.directory_name
                )
                WHERE txn_rank <= 10 OR amt_rank <= 10
                ORDER BY mall_code, total_amount DESC
            SQL;

            return response()->json(['data' => $this->pgcardGroupByMall($bq->query($sql))]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: PG Card — Top 10 tenants per mall ────────────────────────────────

    public function pgcardTopTenants(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls = $this->allowedPgcardMalls($cpnyId);

            if ($malls !== null && empty($malls)) {
                return response()->json(['data' => []]);
            }

            $mallCond = $malls !== null
                ? "AND d.directory_code IN ('".implode("','", array_map('addslashes', $malls))."')"
                : '';

            $bq = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            $sql = <<<SQL
                WITH
                top_cust_txn AS (
                    SELECT directory_id, merchant_id, member_id FROM (
                        SELECT directory_id, merchant_id, member_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, merchant_id ORDER BY COUNT(*) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        WHERE DATE(transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                        GROUP BY directory_id, merchant_id, member_id
                    ) WHERE rn = 1
                ),
                top_cust_amt AS (
                    SELECT directory_id, merchant_id, member_id FROM (
                        SELECT directory_id, merchant_id, member_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, merchant_id ORDER BY SUM(amount) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        WHERE DATE(transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                        GROUP BY directory_id, merchant_id, member_id
                    ) WHERE rn = 1
                )
                SELECT mall_code, mall_name, label, value, total_amount, txn_rank, amt_rank,
                       top_customer_txn, top_customer_amt
                FROM (
                    SELECT
                        d.directory_code                                                AS mall_code,
                        d.directory_name                                                AS mall_name,
                        COALESCE(MAX(m.merchant_name), CAST(t.merchant_id AS STRING))   AS label,
                        COUNT(*)                                                        AS value,
                        SUM(t.amount)                                                   AS total_amount,
                        ROW_NUMBER() OVER (
                            PARTITION BY t.directory_id ORDER BY COUNT(*) DESC
                        )                                                               AS txn_rank,
                        ROW_NUMBER() OVER (
                            PARTITION BY t.directory_id ORDER BY SUM(t.amount) DESC
                        )                                                               AS amt_rank,
                        MAX(ct.fullname)                                                AS top_customer_txn,
                        MAX(ca.fullname)                                                AS top_customer_amt
                    FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                    INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                        ON d.id = t.directory_id {$mallCond}
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` m
                        ON m.id = t.merchant_id
                    LEFT JOIN top_cust_txn tc_txn
                        ON tc_txn.directory_id = t.directory_id AND tc_txn.merchant_id = t.merchant_id
                    LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` ct ON ct.id = tc_txn.member_id
                    LEFT JOIN top_cust_amt tc_amt
                        ON tc_amt.directory_id = t.directory_id AND tc_amt.merchant_id = t.merchant_id
                    LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` ca ON ca.id = tc_amt.member_id
                    WHERE DATE(t.transaction_date) BETWEEN '{$dateFrom}' AND '{$dateTo}'
                    GROUP BY t.directory_id, t.merchant_id, d.directory_code, d.directory_name
                )
                WHERE txn_rank <= 10 OR amt_rank <= 10
                ORDER BY mall_code, total_amount DESC
            SQL;

            return response()->json(['data' => $this->pgcardGroupByMall($bq->query($sql))]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: PG Card — Overall KPI summary (transactions, spending, members) ──

    public function pgcardKpiSummary(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls  = $this->allowedPgcardMalls($cpnyId);

            if ($malls !== null && empty($malls)) {
                return response()->json(['data' => ['total_txn' => 0, 'total_spending' => 0, 'active_members' => 0, 'avg_txn_value' => 0]]);
            }

            $mallCond = $malls !== null
                ? "AND d.directory_code IN ('" . implode("','", array_map('addslashes', $malls)) . "')"
                : '';

            $bq      = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            // Totals query
            $sqlTotals = <<<SQL
                SELECT
                    COUNT(*)                    AS total_txn,
                    COUNT(DISTINCT t.member_id) AS active_members,
                    COALESCE(SUM(t.amount), 0)  AS total_spending,
                    COALESCE(AVG(t.amount), 0)  AS avg_txn_value
                FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                    ON d.id = t.directory_id {$mallCond}
                WHERE DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                    BETWEEN '{$dateFrom}' AND '{$dateTo}'
            SQL;

            // Per-mall breakdown query
            $sqlMall = <<<SQL
                SELECT
                    COALESCE(d.directory_code, 'Unknown')  AS mall_code,
                    COUNT(*)                               AS txn_count,
                    COALESCE(SUM(t.amount), 0)             AS total_spending,
                    COUNT(DISTINCT t.member_id)            AS active_members
                FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                    ON d.id = t.directory_id {$mallCond}
                WHERE DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                    BETWEEN '{$dateFrom}' AND '{$dateTo}'
                GROUP BY d.directory_code
                ORDER BY txn_count DESC
            SQL;

            $row    = ($bq->query($sqlTotals))[0] ?? [];
            $byMall = [];
            foreach ($bq->query($sqlMall) as $mr) {
                $byMall[] = [
                    'mall_code'      => (string) ($mr['mall_code']      ?? ''),
                    'txn_count'      => (int)    ($mr['txn_count']      ?? 0),
                    'total_spending' => (float)  ($mr['total_spending'] ?? 0),
                    'active_members' => (int)    ($mr['active_members'] ?? 0),
                ];
            }

            // ── Insights query — only when a specific company is filtered ──────────
            // Adds top customer/tenant names so the GM sidebar shows meaningful context.
            $insights = null;
            if ($malls !== null && !empty($malls)) {
                $sqlInsights = <<<SQL
                    WITH txn_base AS (
                        SELECT t.member_id, t.merchant_id, t.amount
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                        INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                            ON d.id = t.directory_id {$mallCond}
                        WHERE DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                            BETWEEN '{$dateFrom}' AND '{$dateTo}'
                    ),
                    member_stats AS (
                        SELECT member_id,
                               COUNT(*)    AS txn_count,
                               SUM(amount) AS total_spending,
                               ROW_NUMBER() OVER (ORDER BY COUNT(*)    DESC) AS rank_txn,
                               ROW_NUMBER() OVER (ORDER BY SUM(amount) DESC) AS rank_spending
                        FROM txn_base GROUP BY member_id
                    ),
                    merchant_stats AS (
                        SELECT merchant_id,
                               COUNT(*)    AS txn_count,
                               SUM(amount) AS total_spending,
                               AVG(amount) AS avg_txn,
                               ROW_NUMBER() OVER (ORDER BY COUNT(*)    DESC) AS rank_txn,
                               ROW_NUMBER() OVER (ORDER BY SUM(amount) DESC) AS rank_spending,
                               ROW_NUMBER() OVER (ORDER BY AVG(amount) DESC) AS rank_avg
                        FROM txn_base GROUP BY merchant_id
                    ),
                    new_members AS (
                        SELECT COUNT(DISTINCT member_id) AS cnt FROM (
                            SELECT member_id,
                                   MIN(DATE(DATETIME(TIMESTAMP(transaction_date), 'Asia/Bangkok'))) AS first_txn
                            FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                            GROUP BY member_id
                        ) WHERE first_txn BETWEEN '{$dateFrom}' AND '{$dateTo}'
                    )
                    SELECT 'top_customer_txn' AS metric,
                           COALESCE(m.fullname, CAST(ms.member_id AS STRING)) AS name,
                           CAST(ms.txn_count AS FLOAT64) AS value1, ms.total_spending AS value2
                    FROM member_stats ms
                    LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` m ON m.id = ms.member_id
                    WHERE ms.rank_txn = 1
                    UNION ALL
                    SELECT 'top_customer_spending',
                           COALESCE(m.fullname, CAST(ms.member_id AS STRING)),
                           ms.total_spending, CAST(ms.txn_count AS FLOAT64)
                    FROM member_stats ms
                    LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` m ON m.id = ms.member_id
                    WHERE ms.rank_spending = 1
                    UNION ALL
                    SELECT 'top_tenant_txn',
                           COALESCE(mr.merchant_name, CAST(mts.merchant_id AS STRING)),
                           CAST(mts.txn_count AS FLOAT64), mts.total_spending
                    FROM merchant_stats mts
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mr ON mr.id = mts.merchant_id
                    WHERE mts.rank_txn = 1
                    UNION ALL
                    SELECT 'top_tenant_spending',
                           COALESCE(mr.merchant_name, CAST(mts.merchant_id AS STRING)),
                           mts.total_spending, CAST(mts.txn_count AS FLOAT64)
                    FROM merchant_stats mts
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mr ON mr.id = mts.merchant_id
                    WHERE mts.rank_spending = 1
                    UNION ALL
                    SELECT 'top_tenant_avg',
                           COALESCE(mr.merchant_name, CAST(mts.merchant_id AS STRING)),
                           mts.avg_txn, CAST(mts.txn_count AS FLOAT64)
                    FROM merchant_stats mts
                    LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mr ON mr.id = mts.merchant_id
                    WHERE mts.rank_avg = 1
                    UNION ALL
                    SELECT 'new_members', NULL, CAST(nm.cnt AS FLOAT64), NULL FROM new_members nm
                SQL;

                $raw = [];
                foreach ($bq->query($sqlInsights) as $iRow) {
                    $raw[(string) ($iRow['metric'] ?? '')] = [
                        'name'   => (string) ($iRow['name']   ?? ''),
                        'value1' => (float)  ($iRow['value1'] ?? 0),
                        'value2' => (float)  ($iRow['value2'] ?? 0),
                    ];
                }

                $insights = [
                    'top_customer_txn'      => $raw['top_customer_txn']      ?? null,
                    'top_customer_spending' => $raw['top_customer_spending'] ?? null,
                    'top_tenant_txn'        => $raw['top_tenant_txn']        ?? null,
                    'top_tenant_spending'   => $raw['top_tenant_spending']   ?? null,
                    'top_tenant_avg'        => $raw['top_tenant_avg']        ?? null,
                    'new_members_count'     => (int) ($raw['new_members']['value1'] ?? 0),
                ];
            }

            return response()->json([
                'data' => [
                    'total_txn'      => (int)   ($row['total_txn']      ?? 0),
                    'active_members' => (int)   ($row['active_members'] ?? 0),
                    'total_spending' => (float) ($row['total_spending'] ?? 0),
                    'avg_txn_value'  => (float) ($row['avg_txn_value']  ?? 0),
                    'by_mall'        => $byMall,
                    'insights'       => $insights,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['data' => ['total_txn' => 0, 'total_spending' => 0, 'active_members' => 0, 'avg_txn_value' => 0], 'error' => $e->getMessage()]);
        }
    }

    // ── API: PG Card — Monthly transaction trend ───────────────────────────────

    public function pgcardMonthlyTrend(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls  = $this->allowedPgcardMalls($cpnyId);

            if ($malls !== null && empty($malls)) {
                return response()->json(['data' => []]);
            }

            $mallCond = $malls !== null
                ? "AND d.directory_code IN ('" . implode("','", array_map('addslashes', $malls)) . "')"
                : '';

            $bq      = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            $sql = <<<SQL
                SELECT
                    FORMAT_DATE('%Y-%m', DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))) AS month,
                    COUNT(*)                    AS txn_count,
                    COUNT(DISTINCT t.member_id) AS unique_members,
                    COALESCE(SUM(t.amount), 0)  AS total_spending
                FROM `{$project}.{$dataset}.pgcard_member_transactions_src` t
                INNER JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                    ON d.id = t.directory_id {$mallCond}
                WHERE DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                    BETWEEN '{$dateFrom}' AND '{$dateTo}'
                GROUP BY month
                ORDER BY month
            SQL;

            $data = [];
            foreach ($bq->query($sql) as $row) {
                $data[] = [
                    'month'          => (string) ($row['month']          ?? ''),
                    'txn_count'      => (int)    ($row['txn_count']      ?? 0),
                    'unique_members' => (int)    ($row['unique_members'] ?? 0),
                    'total_spending' => (float)  ($row['total_spending'] ?? 0),
                ];
            }

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: PG Card — Total coupon STYW 2026 ────────────────────────────────

    public function pgcardCouponStyw(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls  = $this->allowedPgcardMalls($cpnyId);

            $bq      = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            // ── Query 1: coupon count by print-mall + status (donut & status pills) ──
            $sqlDonut = <<<SQL
                WITH base AS (
                    SELECT DISTINCT
                        mc.id                 AS coupon_id,
                        mc.status,
                        mc.print_directory_id
                    FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
                    INNER JOIN `{$project}.{$dataset}.pgcard_member_transactions_src` t
                        ON t.id = mc.transaction_id
                    WHERE mc.campaign_id IN (110, 111, 112, 113, 114, 115, 121, 122, 123, 124)
                      AND DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                          BETWEEN '{$dateFrom}' AND '{$dateTo}'
                )
                SELECT
                    COALESCE(d.directory_code, 'Unknown')   AS mall_code,
                    COALESCE(d.directory_name, 'Unknown')   AS mall_name,
                    COALESCE(CAST(b.status AS STRING), '-') AS status,
                    COUNT(*)                                AS cnt
                FROM base b
                LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                    ON d.id = b.print_directory_id
                GROUP BY d.directory_code, d.directory_name, b.status
                ORDER BY mall_code, status
            SQL;

            $byStatusFiltered = [];
            $byMallStatus     = [];
            $totalFiltered    = 0;

            foreach ($bq->query($sqlDonut) as $row) {
                $code   = (string) ($row['mall_code'] ?? 'Unknown');
                $name   = (string) ($row['mall_name'] ?? $code);
                $status = (string) ($row['status']    ?? '-');
                $cnt    = (int)    ($row['cnt']        ?? 0);

                $byMallStatus[] = ['mall_code' => $code, 'mall_name' => $name, 'status' => $status, 'count' => $cnt];

                $isAllowed = $malls === null || in_array($code, $malls, true);
                if ($isAllowed) {
                    $totalFiltered += $cnt;
                    $byStatusFiltered[$status] = ($byStatusFiltered[$status] ?? 0) + $cnt;
                }
            }

            // ── Query 2: campaign chart — VALID + Printed coupons, by txn directory ──
            // txn_count  = qualifying transactions per (campaign, txn directory)
            // cust_count = distinct members per (campaign, txn directory)
            // *_total    = across ALL directories (for the no-filter case)
            $sqlCampaign = <<<SQL
                WITH valid_printed AS (
                    SELECT DISTINCT
                        mc.id          AS coupon_id,
                        mc.campaign_id,
                        t.member_id,
                        t.merchant_id,
                        t.directory_id AS txn_directory_id
                    FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
                    INNER JOIN `{$project}.{$dataset}.pgcard_member_transactions_src` t
                        ON t.id = mc.transaction_id
                    WHERE mc.campaign_id IN (110, 111, 112, 113, 114, 115, 121, 122, 123, 124)
                      AND mc.status = 'VALID'
                      AND EXISTS (
                          SELECT 1
                          FROM `{$project}.{$dataset}.pgcard_coupon_printed_histories_src` ph
                          WHERE ph.member_coupon_id = mc.id
                      )
                      AND DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                          BETWEEN '{$dateFrom}' AND '{$dateTo}'
                ),
                by_total AS (
                    SELECT campaign_id,
                           COUNT(*)                  AS txn_total,
                           COUNT(DISTINCT member_id) AS cust_total
                    FROM valid_printed
                    GROUP BY campaign_id
                ),
                by_dir AS (
                    SELECT
                        vp.campaign_id,
                        COALESCE(d.directory_code, 'Unknown') AS dir,
                        COUNT(*)                              AS txn_count,
                        COUNT(DISTINCT vp.member_id)          AS cust_count
                    FROM valid_printed vp
                    LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                        ON d.id = vp.txn_directory_id
                    GROUP BY vp.campaign_id, dir
                ),
                top_merchant AS (
                    SELECT campaign_id, merchant_name FROM (
                        SELECT
                            vp.campaign_id,
                            COALESCE(mr.merchant_name, CAST(vp.merchant_id AS STRING)) AS merchant_name,
                            ROW_NUMBER() OVER (
                                PARTITION BY vp.campaign_id ORDER BY COUNT(*) DESC
                            ) AS rn
                        FROM valid_printed vp
                        LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mr
                            ON mr.id = vp.merchant_id
                        GROUP BY vp.campaign_id, vp.merchant_id, mr.merchant_name
                    ) WHERE rn = 1
                ),
                top_customer AS (
                    SELECT campaign_id, customer_name, txn_count FROM (
                        SELECT
                            vp.campaign_id,
                            COALESCE(m.fullname, CAST(vp.member_id AS STRING)) AS customer_name,
                            COUNT(*)                                            AS txn_count,
                            ROW_NUMBER() OVER (
                                PARTITION BY vp.campaign_id ORDER BY COUNT(*) DESC
                            ) AS rn
                        FROM valid_printed vp
                        LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` m
                            ON m.id = vp.member_id
                        GROUP BY vp.campaign_id, vp.member_id, m.fullname
                    ) WHERE rn = 1
                )
                SELECT
                    CAST(bd.campaign_id AS STRING)                     AS campaign_id,
                    COALESCE(c.name, CAST(bd.campaign_id AS STRING))   AS campaign_name,
                    bd.dir                                             AS txn_directory_code,
                    bd.txn_count,
                    bd.cust_count                                      AS customer_count,
                    bt.txn_total,
                    bt.cust_total                                      AS customer_total,
                    COALESCE(tm.merchant_name, '-')                    AS top_merchant,
                    COALESCE(tc.customer_name, '-')                    AS top_customer,
                    COALESCE(tc.txn_count, 0)                          AS top_customer_txn
                FROM by_dir bd
                JOIN by_total bt ON bt.campaign_id = bd.campaign_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_campaigns_src` c ON c.id = bd.campaign_id
                LEFT JOIN top_merchant tm ON tm.campaign_id = bd.campaign_id
                LEFT JOIN top_customer tc ON tc.campaign_id = bd.campaign_id
                ORDER BY bd.campaign_id, bd.txn_count DESC
            SQL;

            $byCampaign = [];
            foreach ($bq->query($sqlCampaign) as $row) {
                $cid     = (string) ($row['campaign_id']        ?? '');
                $cname   = (string) ($row['campaign_name']      ?? $cid);
                $dir     = (string) ($row['txn_directory_code'] ?? 'Unknown');
                $txnDir  = (int)    ($row['txn_count']          ?? 0);
                $custDir = (int)    ($row['customer_count']     ?? 0);
                $txnTot  = (int)    ($row['txn_total']          ?? 0);
                $custTot = (int)    ($row['customer_total']     ?? 0);

                if (!isset($byCampaign[$cid])) {
                    $byCampaign[$cid] = [
                        'campaign_id'       => $cid,
                        'campaign_name'     => $cname,
                        'top_merchant'      => (string) ($row['top_merchant']    ?? '-'),
                        'top_customer'      => (string) ($row['top_customer']    ?? '-'),
                        'top_customer_txn'  => (int)    ($row['top_customer_txn'] ?? 0),
                        '_txn_total'        => $txnTot,
                        '_cust_total'       => $custTot,
                        '_by_dir'           => [],
                    ];
                }
                $byCampaign[$cid]['_by_dir'][$dir] = ['txn' => $txnDir, 'cust' => $custDir];
            }

            // Apply mall filter to produce final txn_count and customer_count per campaign
            foreach ($byCampaign as &$camp) {
                $dirs = $camp['_by_dir'];
                if ($malls === null) {
                    $camp['txn_count']      = $camp['_txn_total'];
                    $camp['customer_count'] = $camp['_cust_total'];
                } else {
                    $txn = $cust = 0;
                    foreach ($malls as $code) {
                        $txn  += $dirs[$code]['txn']  ?? 0;
                        $cust += $dirs[$code]['cust'] ?? 0;
                    }
                    $camp['txn_count']      = $txn;
                    $camp['customer_count'] = $cust;
                }
                unset($camp['_txn_total'], $camp['_cust_total'], $camp['_by_dir']);
            }
            unset($camp);

            $byCampaignOut = array_values($byCampaign);
            usort($byCampaignOut, fn ($a, $b) => (int) $a['campaign_id'] - (int) $b['campaign_id']);

            $byStatusOut = [];
            foreach ($byStatusFiltered as $status => $count) {
                $byStatusOut[] = ['status' => $status, 'count' => $count];
            }

            return response()->json([
                'data' => [
                    'total_filtered'     => $totalFiltered,
                    'by_status_filtered' => $byStatusOut,
                    'by_mall_status'     => $byMallStatus,
                    'by_campaign'        => $byCampaignOut,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [
                    'total_filtered'     => 0,
                    'by_status_filtered' => [],
                    'by_mall_status'     => [],
                    'by_campaign'        => [],
                ],
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── API: PG Card — 10 sample transactions per campaign (debug) ───────────────

    public function pgcardCampaignSamples(Request $request)
    {
        try {
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);

            $bq      = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            $sql = <<<SQL
                SELECT
                    CAST(mc.campaign_id AS STRING)                                      AS campaign_id,
                    COALESCE(c.name, CAST(mc.campaign_id AS STRING))                    AS campaign_name,
                    COALESCE(d_txn.directory_code, 'Unknown')                           AS txn_directory_code,
                    COALESCE(d_print.directory_code, 'Unknown')                         AS print_directory_code,
                    FORMAT_DATETIME('%Y-%m-%d',
                        DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))        AS transaction_date,
                    COALESCE(m.fullname, CAST(t.member_id AS STRING))                   AS member_name,
                    COALESCE(mr.merchant_name, CAST(t.merchant_id AS STRING))           AS merchant_name,
                    t.amount,
                    CAST(mc.status AS STRING)                                           AS coupon_status
                FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
                INNER JOIN `{$project}.{$dataset}.pgcard_member_transactions_src` t
                    ON t.id = mc.transaction_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_campaigns_src` c
                    ON c.id = mc.campaign_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d_txn
                    ON d_txn.id = t.directory_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d_print
                    ON d_print.id = mc.print_directory_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_members_src` m
                    ON m.id = t.member_id
                LEFT JOIN `{$project}.{$dataset}.pgcard_merchants_src` mr
                    ON mr.id = t.merchant_id
                WHERE mc.campaign_id IN (110, 111, 112, 113, 114, 115, 121, 122, 123, 124)
                  AND DATE(DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok'))
                      BETWEEN '{$dateFrom}' AND '{$dateTo}'
                QUALIFY ROW_NUMBER() OVER (
                    PARTITION BY mc.campaign_id ORDER BY t.transaction_date DESC
                ) <= 10
                ORDER BY campaign_id, transaction_date DESC
            SQL;

            $rows      = $bq->query($sql);
            $byCampaign = [];
            foreach ($rows as $row) {
                $cid = (string) ($row['campaign_id'] ?? '');
                if (!isset($byCampaign[$cid])) {
                    $byCampaign[$cid] = [
                        'campaign_id'   => $cid,
                        'campaign_name' => (string) ($row['campaign_name'] ?? $cid),
                        'samples'       => [],
                    ];
                }
                $byCampaign[$cid]['samples'][] = [
                    'txn_directory'    => (string) ($row['txn_directory_code']   ?? ''),
                    'print_directory'  => (string) ($row['print_directory_code'] ?? ''),
                    'transaction_date' => (string) ($row['transaction_date']     ?? ''),
                    'member_name'      => (string) ($row['member_name']          ?? ''),
                    'merchant_name'    => (string) ($row['merchant_name']        ?? ''),
                    'amount'           => (float)  ($row['amount']               ?? 0),
                    'coupon_status'    => (string) ($row['coupon_status']        ?? ''),
                ];
            }

            return response()->json(['data' => array_values($byCampaign)]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    // ── API: PG Card — Query comparison (Option A view vs Option B src) ─────────

    // public function pgcardCouponStywCompare(Request $request)
    // {
    //     try {
    //         ['dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->parseFilters($request);
    //         $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
    //         $malls = $this->allowedPgcardMalls($cpnyId);

    //         $bq = new BigQueryService();
    //         $project = self::PGCARD_PROJECT;
    //         $dataset = self::PGCARD_DATASET;

    //         $sqlA = <<<SQL
    //             SELECT
    //                 COALESCE(d.directory_code, 'Unknown')      AS mall_code,
    //                 COALESCE(d.directory_name, 'Unknown')      AS mall_name,
    //                 COALESCE(CAST(c.status AS STRING), '-')    AS status,
    //                 COUNT(*)                                   AS cnt
    //             FROM `{$project}.{$dataset}.pgcard_detail_member_coupon_styw_2026` c
    //             LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d
    //                 ON d.id = c.print_directory_id
    //             WHERE DATE(c.transaction_date_gmt7) BETWEEN '{$dateFrom}' AND '{$dateTo}'
    //             GROUP BY d.directory_code, d.directory_name, c.status
    //             ORDER BY mall_code, status
    //         SQL;

    //         $sqlB = <<<SQL
    //             WITH base AS (
    //                 SELECT DISTINCT
    //                     mc.id                 AS member_coupon_id,
    //                     mc.status,
    //                     mc.print_directory_id,
    //                     ph.user_id,
    //                     DATETIME(TIMESTAMP(t.transaction_date), 'Asia/Bangkok') AS transaction_date_gmt7
    //                 FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
    //                 LEFT JOIN `{$project}.{$dataset}.pgcard_prizes_src` prizes
    //                     ON prizes.id = mc.prize_id
    //                 LEFT JOIN `{$project}.{$dataset}.pgcard_coupon_printed_histories_src` ph
    //                     ON ph.member_coupon_id = mc.id
    //                 LEFT JOIN `{$project}.{$dataset}.xv_pgcard_member` member
    //                     ON member.id = mc.member_id
    //                 LEFT JOIN `{$project}.{$dataset}.pgcard_campaigns_src` campaign
    //                     ON campaign.id = mc.campaign_id
    //                 LEFT JOIN `{$project}.{$dataset}.pgcard_member_transactions_src` t
    //                     ON t.id = mc.transaction_id
    //                 LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` directories
    //                     ON directories.id = t.directory_id
    //                 WHERE campaign.id IN (110, 111, 112, 113, 114, 115, 121, 122, 123, 124)
    //             )
    //             SELECT
    //                 COALESCE(d.directory_code, 'Unknown')      AS mall_code,
    //                 COALESCE(d.directory_name, 'Unknown')      AS mall_name,
    //                 COALESCE(CAST(b.status AS STRING), '-')    AS status,
    //                 COUNT(*)                                   AS cnt
    //             FROM base b
    //             LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d
    //                 ON d.id = b.print_directory_id
    //             WHERE DATE(b.transaction_date_gmt7) BETWEEN '{$dateFrom}' AND '{$dateTo}'
    //             GROUP BY d.directory_code, d.directory_name, b.status
    //             ORDER BY mall_code, status
    //         SQL;

    //         $startA = microtime(true);
    //         $rowsA = $bq->query($sqlA);
    //         $timeA = round((microtime(true) - $startA) * 1000);

    //         $startB = microtime(true);
    //         $rowsB = $bq->query($sqlB);
    //         $timeB = round((microtime(true) - $startB) * 1000);

    //         return response()->json([
    //             'optionA' => array_merge(['time_ms' => $timeA], $this->summarizeCouponRows($rowsA, $malls)),
    //             'optionB' => array_merge(['time_ms' => $timeB], $this->summarizeCouponRows($rowsB, $malls)),
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    private function summarizeCouponRows(array $rows, ?array $malls): array
    {
        $byStatus = [];
        $byMall = [];
        $totalValid = 0;

        foreach ($rows as $row) {
            $code = (string) ($row['mall_code'] ?? 'Unknown');
            $name = (string) ($row['mall_name'] ?? $code);
            $status = (string) ($row['status'] ?? '-');
            $cnt = (int) ($row['cnt'] ?? 0);
            $allowed = $malls === null || in_array($code, $malls, true);

            if ($allowed) {
                $byStatus[$status] = ($byStatus[$status] ?? 0) + $cnt;
                if ($status === 'VALID') {
                    $totalValid += $cnt;
                    if (!isset($byMall[$code])) {
                        $byMall[$code] = ['mall_code' => $code, 'mall_name' => $name, 'count' => 0];
                    }
                    $byMall[$code]['count'] += $cnt;
                }
            }
        }

        $byStatusOut = [];
        foreach ($byStatus as $s => $c) {
            $byStatusOut[] = ['status' => $s, 'count' => $c];
        }

        return [
            'total_valid' => $totalValid,
            'by_status' => $byStatusOut,
            'by_mall' => array_values($byMall),
        ];
    }

    private function pgcardGroupByMall(array $rows): array
    {
        $result = [];

        foreach ($rows as $row) {
            $code = (string) ($row['mall_code'] ?? '');
            if (!$code || strtolower($code) === 'default') {
                continue;
            }

            if (!isset($result[$code])) {
                $result[$code] = [
                    'mall_name' => (string) ($row['mall_name'] ?? $code),
                    'data' => [],
                ];
            }

            $result[$code]['data'][] = [
                'label' => (string) ($row['label'] ?? ''),
                'value' => (int) ($row['value'] ?? 0),
                'total_amount' => (int) ($row['total_amount'] ?? 0),
                'txn_rank' => (int) ($row['txn_rank'] ?? 99),
                'amt_rank' => (int) ($row['amt_rank'] ?? 99),
                'top_merchant_txn' => (string) ($row['top_merchant_txn'] ?? ''),
                'top_merchant_amt' => (string) ($row['top_merchant_amt'] ?? ''),
                'top_customer_txn' => (string) ($row['top_customer_txn'] ?? ''),
                'top_customer_amt' => (string) ($row['top_customer_amt'] ?? ''),
            ];
        }

        return $result;
    }

    // ── API: Cumulative budget used per month ─────────────────────────────────

    public function budgetByMonth(Request $request)
    {
        ['dateFrom' => $dateFrom, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $year = substr($dateFrom, 0, 4);

        $selects = [
            'COALESCE(SUM(totalbudget), 0) + COALESCE(SUM(totalbudget_add), 0) AS total_budget',
        ];
        for ($m = 1; $m <= 12; ++$m) {
            $mm = str_pad($m, 2, '0', STR_PAD_LEFT);
            $selects[] = "COALESCE(SUM(period{$mm}_used), 0) AS m{$mm}";
        }

        $q = BudgetDetail::query()->where('status', 'C');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q->whereRaw('LEFT(perpost::text, 4) = ?', [$year]);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $row = $q->selectRaw(implode(', ', $selects))->first();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [];
        $cumulative = 0;

        for ($m = 1; $m <= 12; ++$m) {
            $mm = str_pad($m, 2, '0', STR_PAD_LEFT);
            $used = (float) ($row->{"m{$mm}"} ?? 0);
            $cumulative += $used;
            $data[] = [
                'month' => $months[$m - 1],
                'used' => round($used),
                'cumulative' => round($cumulative),
            ];
        }

        return response()->json([
            'data' => $data,
            'year' => $year,
            'total_budget' => round((float) ($row->total_budget ?? 0)),
        ]);
    }

    // ── API: Per-section "Last Updated" timestamps ────────────────────────────

    /**
     * Returns when each section's underlying data source was last refreshed:
     * - budget: newest updated_at on the local budget_details table.
     * - pgcard / isort / valet: newest BigQuery table sync time in that dataset
     *   (these datasets are refreshed by an external nightly ETL job, not by
     *   this app, so this is the only way to know their freshness).
     * BigQuery metadata lookups are cached briefly — the source data itself
     * only changes once a night, so there is no need to hit BigQuery on every
     * dashboard load / tab switch.
     */
    public function sectionLastUpdated()
    {
        $raw = Cache::remember('gm_section_last_updated', 300, function () {
            $bq = new BigQueryService();

            return [
                'budget' => BudgetDetail::query()->where('status', 'C')->max('updated_at'),
                'pgcard' => $this->bqDatasetLastModified($bq, self::PGCARD_PROJECT, self::PGCARD_DATASET),
                'isort' => $this->bqDatasetLastModified($bq, self::ISORT_PROJECT, self::ISORT_DATASET),
                'valet' => $this->bqDatasetLastModified($bq, self::VALET_PROJECT, self::VALET_DATASET),
                'event' => MsEvent::query()->max('updated_at'),
            ];
        });

        return response()->json([
            'data' => array_map(fn ($v) => $this->formatLastUpdated($v), $raw),
        ]);
    }

    private function bqDatasetLastModified(BigQueryService $bq, string $project, string $dataset): ?string
    {
        try {
            $sql = "SELECT TIMESTAMP_MILLIS(MAX(last_modified_time)) AS last_modified
                    FROM `{$project}.{$dataset}.__TABLES__`";
            $rows = $bq->query($sql);

            return isset($rows[0]['last_modified']) ? (string) $rows[0]['last_modified'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatLastUpdated($val): ?string
    {
        if (!$val) {
            return null;
        }

        return \Carbon\Carbon::parse((string) $val)
            ->timezone(config('app.timezone'))
            ->format('d M Y, H:i');
    }
}
