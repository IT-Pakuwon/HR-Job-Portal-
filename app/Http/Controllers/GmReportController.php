<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetDetail;
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

    /**
     * Build aggregate SQL expressions.
     * month = 0 → full year (use totalbudget / total_used / total_reserve columns)
     * month > 0 → single month (use period01_used etc.)
     */
    private function buildExprs(int $year, int $month): array
    {
        if ($month > 0) {
            $p = str_pad($month, 2, '0', STR_PAD_LEFT);
            return [
                'budget'  => "COALESCE(SUM(period{$p}_budget), 0)",
                'add'     => "COALESCE(SUM(period{$p}_budget_add), 0)",
                'used'    => "COALESCE(SUM(period{$p}_used), 0)",
                'reserve' => "COALESCE(SUM(period{$p}_reserve), 0)",
            ];
        }
        return [
            'budget'  => 'COALESCE(SUM(totalbudget), 0)',
            'add'     => 'COALESCE(SUM(totalbudget_add), 0)',
            'used'    => 'COALESCE(SUM(total_used), 0)',
            'reserve' => 'COALESCE(SUM(total_reserve), 0)',
        ];
    }

    private function parseFilters(Request $request): array
    {
        return [
            'year'    => max(2000, (int) $request->input('year',  date('Y'))),
            'month'   => max(0,    min(12, (int) $request->input('month', 0))),
            'cpnyId'  => $request->input('cpny_id') ?: null,
            'depts'   => array_filter((array) $request->input('departments', [])),
        ];
    }

    private function applyYearMonth($q, int $year, int $month)
    {
        $q->whereRaw("LEFT(perpost::text, 4) = ?", [(string) $year]);
        return $q;
    }

    // ── View ──────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        return view('pages.gm-report.dashboard');
    }

    // ── API: Companies accessible to this user ────────────────────────────────

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

    // ── API: Available years ──────────────────────────────────────────────────

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
        ['year' => $year, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();

        $q = BudgetDetail::query()->select('department_fin_id')
            ->where('status', 'C')->whereNotNull('department_fin_id');

        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyYearMonth($q, $year, 0);

        $list = $q->distinct()->orderBy('department_fin_id')->pluck('department_fin_id');

        return response()->json(['data' => $list]);
    }

    // ── API: Budget summary totals ────────────────────────────────────────────

    public function budgetSummary(Request $request)
    {
        ['year' => $year, 'month' => $month, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();
        $exprs   = $this->buildExprs($year, $month);

        $q = BudgetDetail::query()->where('status', 'C');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyYearMonth($q, $year, $month);

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
                'year'            => $year,
                'month'           => $month,
                'total_budget'    => $totalFinal,
                'total_used'      => $totalUsed,
                'total_reserve'   => $totalReserve,
                'total_remaining' => $totalRemaining,
                'utilization_pct' => $utilizationPct,
            ],
        ]);
    }

    // ── API: Budget by company ────────────────────────────────────────────────

    public function budgetByCompany(Request $request)
    {
        ['year' => $year, 'month' => $month, 'cpnyId' => $cpnyId] = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();
        $exprs   = $this->buildExprs($year, $month);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr   = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('cpny_id');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyYearMonth($q, $year, $month);

        $rows = $q->selectRaw("
            cpny_id,
            {$finalExpr}        AS total_final,
            {$exprs['used']}    AS total_used,
            {$exprs['reserve']} AS total_reserve,
            {$remExpr}          AS total_remaining
        ")->groupBy('cpny_id')->orderBy('cpny_id')->get();

        return response()->json(['data' => $rows]);
    }

    // ── API: Budget by department (table) ─────────────────────────────────────

    public function budgetByDepartment(Request $request)
    {
        ['year' => $year, 'month' => $month, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);

        $allowed = $this->allowedCompanies();
        $exprs   = $this->buildExprs($year, $month);

        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr   = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct   = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
                           THEN ROUND(({$exprs['used']} / ({$exprs['budget']}+{$exprs['add']})) * 100, 1)
                           ELSE 0 END";

        $q = BudgetDetail::query()->where('status', 'C')->whereNotNull('department_fin_id');
        $q = $this->applyCompanyFilter($q, $allowed, $cpnyId);
        $q = $this->applyYearMonth($q, $year, $month);

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
}
