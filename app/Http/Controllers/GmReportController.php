<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetDetail;
use App\Models\DepartmentFin;
use App\Models\User;

class GmReportController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function csvToArray($val): array
    {
        if ($val === null) return [];
        $s = trim((string) $val);
        if ($s === '') return [];
        return collect(explode(',', $s))
            ->map(fn($x) => strtoupper(trim($x)))
            ->filter()->unique()->values()->all();
    }

    private function allowedCompanies(): array
    {
        $user = Auth::user();
        if (!$user) return [];
        $u = User::query()->where('username', $user->username)->first();
        return $this->csvToArray(optional($u)->cpny_id);
    }

    private function applyCompanyFilter($q, array $allowed, ?string $cpnyId)
    {
        if ($cpnyId) {
            $cpnyId = strtoupper(trim($cpnyId));
            if (!empty($allowed) && !in_array($cpnyId, $allowed, true)) {
                return $q->whereRaw('1=0');
            }
            $q->where('cpny_id', $cpnyId);
        } elseif (!empty($allowed)) {
            $q->whereIn('cpny_id', $allowed);
        }
        return $q;
    }

    private function buildExprs(string $dateFrom, string $dateTo): array
    {
        $from      = \Carbon\Carbon::parse($dateFrom);
        $to        = \Carbon\Carbon::parse($dateTo);
        $monthFrom = (int) $from->format('n');
        $monthTo   = (int) $to->format('n');
        $sameYear  = $from->year === $to->year;

        // Full year or cross-year → annual total columns
        if (!$sameYear || ($monthFrom === 1 && $monthTo === 12)) {
            return [
                'budget'  => 'COALESCE(SUM(totalbudget), 0)',
                'add'     => 'COALESCE(SUM(totalbudget_add), 0)',
                'used'    => 'COALESCE(SUM(total_used), 0)',
                'reserve' => 'COALESCE(SUM(total_reserve), 0)',
            ];
        }

        // Single or multi-month range → sum period columns
        $months   = range($monthFrom, $monthTo);
        $buildSum = function (string $col) use ($months): string {
            $terms = array_map(
                fn($m) => 'COALESCE(period' . str_pad($m, 2, '0', STR_PAD_LEFT) . "_{$col}, 0)",
                $months
            );
            return 'COALESCE(SUM(' . implode(' + ', $terms) . '), 0)';
        };

        return [
            'budget'  => $buildSum('budget'),
            'add'     => $buildSum('budget_add'),
            'used'    => $buildSum('used'),
            'reserve' => $buildSum('reserve'),
        ];
    }

    private function parseFilters(Request $request): array
    {
        $y        = date('Y');
        $dateFrom = $request->input('date_from') ?: "{$y}-01-01";
        $dateTo   = $request->input('date_to')   ?: "{$y}-12-31";

        return [
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'cpnyId'   => $request->input('cpny_id') ?: null,
            'depts'    => array_filter((array) $request->input('departments', [])),
        ];
    }

    private function applyDateFilter($q, string $dateFrom, string $dateTo)
    {
        $fromYear = substr($dateFrom, 0, 4);
        $toYear   = substr($dateTo,   0, 4);

        if ($fromYear === $toYear) {
            return $q->whereRaw("LEFT(perpost::text, 4) = ?", [$fromYear]);
        }
        return $q->whereRaw("LEFT(perpost::text, 4) BETWEEN ? AND ?", [$fromYear, $toYear]);
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

        if (!empty($allowed)) $q->whereIn('cpny_id', $allowed);

        $list = $q->distinct()->orderBy('cpny_id')->pluck('cpny_id');

        return response()->json([
            'data'   => $list,
            'locked' => count($allowed) === 1,
            'single' => count($allowed) === 1 ? $allowed[0] : null,
        ]);
    }

    // ── API: Available years (kept for reference; not used by filter bar) ─────

    public function budgetYears()
    {
        $allowed = $this->allowedCompanies();

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('perpost');
        if (!empty($allowed)) $q->whereIn('cpny_id', $allowed);

        $years = $q->selectRaw("DISTINCT LEFT(perpost::text, 4) AS year")
            ->orderByRaw("LEFT(perpost::text, 4) DESC")
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

        $list = $ids->map(fn($id) => [
            'id'   => $id,
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
        $exprs   = $this->buildExprs($dateFrom, $dateTo);

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

        $totalFinal     = (float)($row->total_budget ?? 0) + (float)($row->total_budget_add ?? 0);
        $totalReserve   = (float)($row->total_reserve ?? 0);
        $totalUsed      = (float)($row->total_used    ?? 0);
        $totalRemaining = $totalFinal - $totalReserve - $totalUsed;
        $utilizationPct = $totalFinal > 0 ? round(($totalUsed / $totalFinal) * 100, 1) : 0;

        return response()->json([
            'data' => [
                'date_from'       => $dateFrom,
                'date_to'         => $dateTo,
                'total_budget'    => $totalFinal,
                'total_used'      => $totalUsed,
                'total_reserve'   => $totalReserve,
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
        $exprs   = $this->buildExprs($dateFrom, $dateTo);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr   = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct   = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
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

    // ── API: Budget by activity ───────────────────────────────────────────────

    public function budgetByActivity(Request $request)
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $exprs   = $this->buildExprs($dateFrom, $dateTo);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr   = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct   = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
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

    // ── API: Cumulative budget used per month ─────────────────────────────────

    public function budgetByMonth(Request $request)
    {
        ['dateFrom' => $dateFrom, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $year    = substr($dateFrom, 0, 4);

        $selects = [
            'COALESCE(SUM(totalbudget), 0) + COALESCE(SUM(totalbudget_add), 0) AS total_budget',
        ];
        for ($m = 1; $m <= 12; $m++) {
            $mm        = str_pad($m, 2, '0', STR_PAD_LEFT);
            $selects[] = "COALESCE(SUM(period{$mm}_used), 0) AS m{$mm}";
        }

        $q = BudgetDetail::query()->where('status', 'C');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q->whereRaw("LEFT(perpost::text, 4) = ?", [$year]);

        if (!empty($depts)) {
            $q->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $row = $q->selectRaw(implode(', ', $selects))->first();

        $months     = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data       = [];
        $cumulative = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mm          = str_pad($m, 2, '0', STR_PAD_LEFT);
            $used        = (float) ($row->{"m{$mm}"} ?? 0);
            $cumulative += $used;
            $data[]      = [
                'month'      => $months[$m - 1],
                'used'       => round($used),
                'cumulative' => round($cumulative),
            ];
        }

        return response()->json([
            'data'         => $data,
            'year'         => $year,
            'total_budget' => round((float) ($row->total_budget ?? 0)),
        ]);
    }
}
