<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BudgetDetail;
use App\Models\DepartmentFin;
use App\Models\User;
use App\Exports\GmReportExport;
use App\Services\BigQueryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class GmReportController extends Controller
{
    // ── PG Card constants ─────────────────────────────────────────────────────
    private const PGCARD_PROJECT  = 'ifca-pkwjakarta';
    private const PGCARD_DATASET  = 'pgcard';

    // Maps HR company code → pgcard directory_code
    private const PGCARD_COMPANY_MAP = [
        'AW'  => 'GC',
        'EP'  => 'KK',
        'PSA' => 'PBM',
        'GPS' => 'PMB',
    ];

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

    /**
     * Returns the pgcard directory_codes the current user may see.
     * null  = no restriction (show all malls)
     * []    = no access
     * ['GC', 'KK'] = filter to these codes only
     */
    private function allowedPgcardMalls(?string $cpnyId): ?array
    {
        $map     = self::PGCARD_COMPANY_MAP;
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
            array_map(fn($code) => $map[$code] ?? null, $allowed)
        ));
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

    // ── Export helpers ────────────────────────────────────────────────────────

    private function formatIdr(float $val): string
    {
        $abs = abs($val);
        $pfx = $val < 0 ? '-' : '';
        if ($abs >= 1e12) return $pfx . 'Rp ' . number_format($abs / 1e12, 1, ',', '.') . 'T';
        if ($abs >= 1e9)  return $pfx . 'Rp ' . number_format($abs / 1e9,  1, ',', '.') . 'M';
        if ($abs >= 1e6)  return $pfx . 'Rp ' . number_format($abs / 1e6,  1, ',', '.') . 'Jt';
        return $pfx . 'Rp ' . number_format(round($abs), 0, ',', '.');
    }

    private function gatherExportData(Request $request): array
    {
        ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'cpnyId' => $cpnyId, 'depts' => $depts]
            = $this->parseFilters($request);
        $allowed = $this->allowedCompanies();
        $exprs   = $this->buildExprs($dateFrom, $dateTo);

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

        $totalFinal     = (float) ($row->total_budget     ?? 0) + (float) ($row->total_budget_add ?? 0);
        $totalReserve   = (float) ($row->total_reserve    ?? 0);
        $totalUsed      = (float) ($row->total_used       ?? 0);
        $totalRemaining = $totalFinal - $totalReserve - $totalUsed;
        $utilizationPct = $totalFinal > 0 ? round(($totalUsed / $totalFinal) * 100, 1) : 0;

        // By Department
        $finalExpr = "({$exprs['budget']}+{$exprs['add']})";
        $remExpr   = "({$exprs['budget']}+{$exprs['add']}-{$exprs['used']}-{$exprs['reserve']})";
        $usedPct   = "CASE WHEN ({$exprs['budget']}+{$exprs['add']}) > 0
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
        $year    = substr($dateFrom, 0, 4);
        $selects = ['COALESCE(SUM(totalbudget), 0) + COALESCE(SUM(totalbudget_add), 0) AS total_budget'];
        for ($m = 1; $m <= 12; $m++) {
            $mm        = str_pad($m, 2, '0', STR_PAD_LEFT);
            $selects[] = "COALESCE(SUM(period{$mm}_used), 0) AS m{$mm}";
        }

        $monthQ = BudgetDetail::query()->where('status', 'C');
        $monthQ = $this->applyCompanyFilter($monthQ, $allowed, $cpnyId);
        $monthQ->whereRaw("LEFT(perpost::text, 4) = ?", [$year]);
        if (!empty($depts)) {
            $monthQ->whereIn('department_fin_id', array_map('strtoupper', $depts));
        }

        $monthRaw   = $monthQ->selectRaw(implode(', ', $selects))->first();
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthRows  = [];
        $cumulative = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mm          = str_pad($m, 2, '0', STR_PAD_LEFT);
            $used        = (float) ($monthRaw->{"m{$mm}"} ?? 0);
            $cumulative += $used;
            $monthRows[] = ['month' => $monthNames[$m - 1], 'used' => round($used), 'cumulative' => round($cumulative)];
        }

        return [
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'cpnyId'      => $cpnyId,
            'year'        => $year,
            'totalBudget' => round($totalFinal),
            'summary'     => [
                'total_budget'    => $totalFinal,
                'total_used'      => $totalUsed,
                'total_reserve'   => $totalReserve,
                'total_remaining' => $totalRemaining,
                'utilization_pct' => $utilizationPct,
            ],
            'deptRows'    => $deptRows,
            'actRows'     => $actRows,
            'monthRows'   => $monthRows,
        ];
    }

    // ── Exports ───────────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $data     = $this->gatherExportData($request);
        $data['fmt'] = fn (float $v) => $this->formatIdr($v);
        $pdf      = Pdf::loadView('pages.gm-report.export-pdf', $data)
                       ->setPaper('a4', 'landscape');
        $filename = 'gm-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.pdf';
        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $data     = $this->gatherExportData($request);
        $filename = 'gm-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, ['GM Report Dashboard']);
            fputcsv($out, ['Period', $data['dateFrom'] . ' to ' . $data['dateTo']]);
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
            fputcsv($out, ['Utilization %',   $s['utilization_pct'],        $s['utilization_pct'] . '%']);
            fputcsv($out, []);

            fputcsv($out, ['=== BY DEPARTMENT ===']);
            fputcsv($out, ['Department', 'Budget (IDR)', 'Used (IDR)', 'Reserved (IDR)', 'Remaining (IDR)', 'Usage %']);
            foreach ($data['deptRows'] as $r) {
                fputcsv($out, [
                    $r->department_fin_id ?? '',
                    round((float) ($r->total_final     ?? 0)),
                    round((float) ($r->total_used      ?? 0)),
                    round((float) ($r->total_reserve   ?? 0)),
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
                    round((float) ($r->total_final     ?? 0)),
                    round((float) ($r->total_used      ?? 0)),
                    round((float) ($r->total_reserve   ?? 0)),
                    round((float) ($r->total_remaining ?? 0)),
                    (float) ($r->used_pct ?? 0),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['=== MONTHLY TREND (' . $data['year'] . ') ===']);
            fputcsv($out, ['Month', 'Used (IDR)', 'Cumulative (IDR)']);
            foreach ($data['monthRows'] as $r) {
                fputcsv($out, [$r['month'], $r['used'], $r['cumulative']]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(Request $request)
    {
        $data     = $this->gatherExportData($request);
        $filename = 'gm-report-' . $data['dateFrom'] . '-to-' . $data['dateTo'] . '.xlsx';
        return Excel::download(new GmReportExport($data), $filename);
    }

    // ── API: Cumulative budget used per month ─────────────────────────────────

    // ── API: PG Card — Top 10 customers per mall ──────────────────────────────

    public function pgcardTopCustomers(Request $request)
    {
        try {
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
                WITH
                top_merch_txn AS (
                    SELECT directory_id, member_id, merchant_id FROM (
                        SELECT directory_id, member_id, merchant_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, member_id ORDER BY COUNT(*) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        GROUP BY directory_id, member_id, merchant_id
                    ) WHERE rn = 1
                ),
                top_merch_amt AS (
                    SELECT directory_id, member_id, merchant_id FROM (
                        SELECT directory_id, member_id, merchant_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, member_id ORDER BY SUM(amount) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
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
                WITH
                top_cust_txn AS (
                    SELECT directory_id, merchant_id, member_id FROM (
                        SELECT directory_id, merchant_id, member_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, merchant_id ORDER BY COUNT(*) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
                        GROUP BY directory_id, merchant_id, member_id
                    ) WHERE rn = 1
                ),
                top_cust_amt AS (
                    SELECT directory_id, merchant_id, member_id FROM (
                        SELECT directory_id, merchant_id, member_id,
                            ROW_NUMBER() OVER (PARTITION BY directory_id, merchant_id ORDER BY SUM(amount) DESC) AS rn
                        FROM `{$project}.{$dataset}.pgcard_member_transactions_src`
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

    // ── API: PG Card — Total coupon STYW 2026 ────────────────────────────────

    public function pgcardCouponStyw(Request $request)
    {
        try {
            $cpnyId = strtoupper(trim($request->input('cpny_id', ''))) ?: null;
            $malls  = $this->allowedPgcardMalls($cpnyId); // null = all, [] = none, [...] = list

            $bq      = new BigQueryService();
            $project = self::PGCARD_PROJECT;
            $dataset = self::PGCARD_DATASET;

            // One query: count by mall + status — serves both filtered total and unfiltered donut
            $sql = <<<SQL
                SELECT
                    COALESCE(d.directory_code, 'Unknown')   AS mall_code,
                    COALESCE(d.directory_name, 'Unknown')   AS mall_name,
                    COALESCE(mc.status, '-')                AS status,
                    COUNT(*)                                AS cnt
                FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
                LEFT JOIN `{$project}.{$dataset}.pgcard_directories_src` d
                    ON d.id = mc.print_directory_id
                WHERE mc.deleted_at IS NULL
                GROUP BY d.directory_code, d.directory_name, mc.status
                ORDER BY mall_code, status
            SQL;

            $sqlCampaigns = <<<SQL
                SELECT DISTINCT cam.name
                FROM `{$project}.{$dataset}.pgcard_member_coupons_src` mc
                INNER JOIN `{$project}.{$dataset}.pgcard_campaigns_src` cam
                    ON cam.id = mc.campaign_id
                WHERE mc.deleted_at IS NULL
                  AND cam.deleted_at IS NULL
                  AND cam.name IS NOT NULL
                ORDER BY cam.name
            SQL;

            $rows          = $bq->query($sql);
            $campaignNames = array_column(iterator_to_array($bq->query($sqlCampaigns)), 'name');

            $byMall          = [];   // all malls, for donut (unfiltered)
            $byStatusFiltered = [];  // status breakdown for allowed malls
            $totalFiltered   = 0;

            foreach ($rows as $row) {
                $code    = (string) ($row['mall_code'] ?? 'Unknown');
                $name    = (string) ($row['mall_name'] ?? $code);
                $status  = (string) ($row['status']    ?? '-');
                $cnt     = (int)    ($row['cnt']        ?? 0);

                // by_mall (always, for donut)
                if (!isset($byMall[$code])) {
                    $byMall[$code] = ['mall_code' => $code, 'mall_name' => $name, 'count' => 0];
                }
                $byMall[$code]['count'] += $cnt;

                // filtered total + status — only include allowed malls
                $allowed = $malls === null || in_array($code, $malls, true);
                if ($allowed) {
                    $totalFiltered += $cnt;
                    $byStatusFiltered[$status] = ($byStatusFiltered[$status] ?? 0) + $cnt;
                }
            }

            $byStatusOut = [];
            foreach ($byStatusFiltered as $status => $count) {
                $byStatusOut[] = ['status' => $status, 'count' => $count];
            }

            return response()->json([
                'data' => [
                    'total_filtered'     => $totalFiltered,
                    'by_status_filtered' => $byStatusOut,
                    'by_mall'            => array_values($byMall),
                    'campaign_names'     => $campaignNames,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'data'  => ['total_filtered' => 0, 'by_status_filtered' => [], 'by_mall' => []],
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function pgcardGroupByMall(array $rows): array
    {
        $result = [];

        foreach ($rows as $row) {
            $code = (string) ($row['mall_code'] ?? '');
            if (!$code || strtolower($code) === 'default') continue;

            if (!isset($result[$code])) {
                $result[$code] = [
                    'mall_name' => (string) ($row['mall_name'] ?? $code),
                    'data'      => [],
                ];
            }

            $result[$code]['data'][] = [
                'label'        => (string) ($row['label']        ?? ''),
                'value'        => (int)    ($row['value']        ?? 0),
                'total_amount' => (int)    ($row['total_amount'] ?? 0),
                'txn_rank'     => (int)    ($row['txn_rank']     ?? 99),
                'amt_rank'     => (int)    ($row['amt_rank']     ?? 99),
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
