<?php

namespace App\Services\Dashboard\SummaryPanels;

use App\Http\Controllers\GmReportController;
use App\Models\DepartmentFin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

/**
 * Data behind the cards of config/summary_panels.php ('catalog').
 *
 * One method per `source` key (dots/underscores -> camelCase: 'budget.by_company' -> budgetByCompany).
 * Every card of the All Reports page is fed by GmReportController's JSON endpoints, so each method simply CALLS
 * the same endpoint with the panel's period and reshapes the answer. That keeps the numbers identical to All
 * Reports and the company scoping (the user's cpny_id) in one place — the controller.
 *
 * Shapes (see summary-panel.js):
 *   kpi    ['items' => [['label', 'value', 'hint', 'color' => hex, 'pct' => 0-100|null]]]   (a row of headline tiles)
 *   stat   ['value', 'hint', 'trend' => up|down|flat, 'delta']
 *   split  ['left' => [label,value,hint], 'right' => [label,value], 'pct', 'barLabel']
 *   table  ['columns' => [[label, align]], 'rows' => [[cell, ...]]]   cell = scalar | ['text', 'badge' => good|warn|bad]
 *   donut  ['series' => [], 'labels' => []]
 *   mix    ['segments' => [[label, value, color, text]], 'total', 'totalLabel']   (one stacked bar; shares are worked out from value)
 *   column ['series' => [['name', 'data' => []]], 'categories' => []]
 *   list   ['items' => [['title', 'meta', 'tone' => red|amber|indigo]]]
 *   insight ['items' => [['type' => info|positive|warning|critical, 'text']]]
 *
 * Methods run one by one, so a slow card only delays itself (SummaryPanelController also caches each result for a
 * few minutes — the PG Card / Isort / Valet sections read BigQuery). Not wired: the PG Card STYW coupon and
 * campaign cards, which are campaign-specific.
 */
class SummaryData
{
    private const TOP = 8; // rows shown in a table card

    /** $companies = company codes to narrow to (the report controller only ever narrows within the user's own); [] = all of the user's companies. */
    public function __construct(private User $user, private string $period = 'year', private array $companies = [])
    {
    }

    /**
     * How long the controller may reuse a card's answer. PG Card / Isort / Valet read BigQuery tables that an external
     * ETL refreshes once a night (see GmReportController::sectionLastUpdated), so an hour costs nothing in freshness;
     * the rest read the app's own database and are kept for a few minutes.
     */
    public static function ttlMinutes(string $source): int
    {
        // $source is a card source key ('pgcard.total') or a report endpoint name ('pgcardKpiSummary')
        return preg_match('/^(pgcard|isort|valet)/', $source) ? 60 : 10;
    }

    public function has(string $source): bool
    {
        return method_exists($this, $this->method($source));
    }

    /** True when the last resolve() hit an endpoint that answered with an error (so the result must not be cached). */
    public bool $failed = false;

    /** Unix time of the oldest report answer the last resolve() used (cached answers keep their original time). */
    public ?int $dataAt = null;

    /** Skip reading the cache (the Refresh button); fresh answers are still written back. */
    public bool $fresh = false;

    /** endpoint => [decoded json, failed] — answers already fetched during this request */
    private array $memo = [];

    public function resolve(string $source): array
    {
        $this->failed = false;
        $this->dataAt = null;

        return $this->{$this->method($source)}();
    }

    private function method(string $source): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['.', '_', '-'], ' ', $source))));
    }

    // ───────────────────── plumbing ─────────────────────

    /** date_from / date_to of the panel's period (All Reports default = this year). */
    private function range(): array
    {
        $now = Carbon::now();

        [$from, $to] = match ($this->period) {
            'today' => [$now->copy(), $now->copy()],
            'week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };

        return ['date_from' => $from->toDateString(), 'date_to' => $to->toDateString()];
    }

    /** Call a GmReportController endpoint as the current user and return its decoded JSON. */
    private function report(string $endpoint): array
    {
        // cards often share an endpoint (e.g. two PG Card cards on pgcardKpiSummary): ask once per request...
        if (isset($this->memo[$endpoint])) {
            [$json, $failed, $at] = $this->memo[$endpoint];
            $this->failed = $this->failed || $failed;
            $this->dataAt = min($this->dataAt ?? $at, $at);

            return $json;
        }

        // ...and across the separate per-card requests of one panel load (and across users of the same company scope)
        $key = 'summary-report:' . md5((string) $this->user->cpny_id . '|' . implode(',', $this->companies)) . ":{$this->period}:$endpoint";
        if (!$this->fresh && ($hit = Cache::get($key)) !== null) {
            $hit[2] = $hit[2] ?? now()->timestamp; // [json, failed=false, fetched-at] — only good answers are stored
            $this->memo[$endpoint] = $hit;
            $this->dataAt = min($this->dataAt ?? $hit[2], $hit[2]);

            return $hit[0];
        }

        $controller = app(GmReportController::class);
        if ($this->companies) {
            $controller->restrictTo($this->companies);
        }
        $res = $controller->{$endpoint}(Request::create('/', 'GET', $this->range()));

        $json = $res->getData(true) ?: [];
        $failed = isset($json['error']); // the All Reports endpoints swallow their own exceptions into {error}
        $this->failed = $this->failed || $failed;
        $this->memo[$endpoint] = [$json, $failed, now()->timestamp];
        $this->dataAt = min($this->dataAt ?? now()->timestamp, now()->timestamp);
        if (!$failed) {
            Cache::put($key, $this->memo[$endpoint], now()->addMinutes(self::ttlMinutes($endpoint)));
        }

        return $json;
    }

    private function idr(float $v): string
    {
        $abs = abs($v);
        $pfx = $v < 0 ? '-' : '';

        return match (true) {
            $abs >= 1e12 => $pfx . 'Rp ' . number_format($abs / 1e12, 1, ',', '.') . 'T',
            $abs >= 1e9  => $pfx . 'Rp ' . number_format($abs / 1e9, 1, ',', '.') . 'M',
            $abs >= 1e6  => $pfx . 'Rp ' . number_format($abs / 1e6, 1, ',', '.') . 'Jt',
            default      => $pfx . 'Rp ' . number_format(round($abs), 0, ',', '.'),
        };
    }

    /** Free-typed master data often carries leading/trailing or doubled spaces (incl. non-breaking ones) that make rows look misaligned. */
    private function clean(?string $s): string
    {
        return trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', (string) $s));
    }

    /** First $n rows (default TOP) plus a "Showing top 8 of 14" note when rows were cut, so a table never looks complete when it is not. */
    private function cut(array $rows, ?int $n = null): array
    {
        $n ??= self::TOP;

        return [array_slice($rows, 0, $n), count($rows) > $n ? 'Showing top ' . $n . ' of ' . count($rows) : null];
    }

    private function num(float $v): string
    {
        return number_format(round($v), 0, ',', '.');
    }

    /** green < 70%, amber < 90%, red above — how much of the budget is used. */
    private function usageBadge(float $pct): array
    {
        return ['text' => $pct . '%', 'badge' => $pct >= 90 ? 'bad' : ($pct >= 70 ? 'warn' : 'good')];
    }

    // ───────────────────── Budget ─────────────────────

    protected function budgetTotal(): array
    {
        $d = $this->report('budgetSummary')['data'] ?? [];
        $top = $this->report('budgetTopSpend');
        $single = (bool) ($top['single'] ?? true);

        return [
            'left'     => ['label' => 'Total Budget', 'value' => $this->idr((float) ($d['total_budget'] ?? 0)), 'hint' => 'Original + Additional'],
            'right'    => ['label' => 'Remaining', 'value' => $this->idr((float) ($d['total_remaining'] ?? 0))],
            'pct'      => (float) ($d['utilization_pct'] ?? 0),
            'barLabel' => 'Utilization (Used)', // Reserved is not counted: it can still be released back
            'topTitle' => 'Biggest spend',
            'top'      => array_map(fn ($r) => [
                'name'  => $this->clean($r['activity_descr']) ?: $r['activity_id'],
                'tag'   => $single ? null : $r['cpny_id'],
                'value' => $this->idr((float) $r['total_used']),
            ], $top['data'] ?? []),
        ];
    }

    /** Headline tiles for the top of a Budget panel: total, used, reserved and remaining, each with its share of the total. */
    protected function budgetKpi(): array
    {
        $d = $this->report('budgetSummary')['data'] ?? [];
        $total = max(0, (float) ($d['total_budget'] ?? 0));
        $used = max(0, (float) ($d['total_used'] ?? 0));
        $reserve = max(0, (float) ($d['total_reserve'] ?? 0));
        $remaining = max(0, (float) ($d['total_remaining'] ?? 0));
        $share = fn (float $v) => $total > 0 ? round($v / $total * 100, 1) : 0;

        return ['items' => [
            ['label' => 'Total Budget', 'value' => $this->idr($total), 'hint' => 'Original + Additional', 'color' => '#6366F1', 'pct' => null],
            ['label' => 'Used', 'value' => $this->idr($used), 'hint' => $share($used) . '% of total', 'color' => '#EF4444', 'pct' => $share($used)],
            ['label' => 'Reserved', 'value' => $this->idr($reserve), 'hint' => $share($reserve) . '% of total', 'color' => '#F59E0B', 'pct' => $share($reserve)],
            ['label' => 'Remaining', 'value' => $this->idr($remaining), 'hint' => $share($remaining) . '% of total', 'color' => '#10B981', 'pct' => $share($remaining)],
        ]];
    }

    protected function budgetMix(): array
    {
        $d = $this->report('budgetSummary')['data'] ?? [];
        $used = max(0, (float) ($d['total_used'] ?? 0));
        $reserve = max(0, (float) ($d['total_reserve'] ?? 0));
        $remaining = max(0, (float) ($d['total_remaining'] ?? 0));

        return [
            'totalLabel' => 'Total',
            'total'      => $this->idr($used + $reserve + $remaining),
            'segments'   => array_values(array_filter([
                ['label' => 'Used', 'value' => $used, 'color' => '#EF4444', 'text' => $this->idr($used)],
                $reserve > 0 ? ['label' => 'Reserved', 'value' => $reserve, 'color' => '#F59E0B', 'text' => $this->idr($reserve)] : null,
                ['label' => 'Remaining', 'value' => $remaining, 'color' => '#10B981', 'text' => $this->idr($remaining)],
            ])),
        ];
    }

    protected function budgetMonthly(): array
    {
        $r = $this->report('budgetByMonth');
        $rows = $r['data'] ?? [];

        return [
            'series'     => [['name' => 'Cumulative used', 'data' => array_map(fn ($m) => (float) $m['cumulative'], $rows)]],
            'categories' => array_column($rows, 'month'),
        ];
    }

    /** Shared table for the three budget breakdowns. */
    private function budgetTable(string $endpoint, string $firstLabel, callable $name): array
    {
        [$rows, $note] = $this->cut($this->report($endpoint)['data'] ?? []);

        return [
            'note'    => $note,
            'columns' => [['label' => $firstLabel], ['label' => 'Budget', 'align' => 'right'], ['label' => 'Used', 'align' => 'right'], ['label' => 'Remaining', 'align' => 'right'], ['label' => 'Usage', 'align' => 'center']],
            'rows'    => array_map(fn ($r) => [
                $name($r),
                $this->idr((float) $r['total_final']),
                $this->idr((float) $r['total_used']),
                $this->idr((float) $r['total_remaining']),
                $this->usageBadge((float) $r['used_pct']),
            ], $rows),
        ];
    }

    protected function budgetByDepartment(): array
    {
        $rows = $this->report('budgetByDepartment')['data'] ?? [];
        $names = DepartmentFin::query()->whereIn('department_fin_id', array_column($rows, 'department_fin_id'))->pluck('department_name', 'department_fin_id');

        return $this->budgetTable('budgetByDepartment', 'Department', fn ($r) => $names[$r['department_fin_id']] ?? $r['department_fin_id']);
    }

    protected function budgetByCompany(): array
    {
        return $this->budgetTable('budgetByCompany', 'Company', fn ($r) => $r['cpny_id']);
    }

    protected function budgetByActivity(): array
    {
        return $this->budgetTable('budgetByActivity', 'Activity', fn ($r) => $this->clean($r['activity_descr']) ?: $r['activity_id']);
    }

    // ───────────────────── PG Card (BigQuery) ─────────────────────

    protected function pgcardTotal(): array
    {
        $d = $this->report('pgcardKpiSummary')['data'] ?? [];

        return [
            'left'  => ['label' => 'Transactions', 'value' => $this->num((float) ($d['total_txn'] ?? 0)), 'hint' => 'In the period'],
            'right' => ['label' => 'Spending', 'value' => $this->idr((float) ($d['total_spending'] ?? 0))],
        ];
    }

    protected function pgcardMembers(): array
    {
        $d = $this->report('pgcardKpiSummary')['data'] ?? [];

        return [
            'left'  => ['label' => 'Active members', 'value' => $this->num((float) ($d['active_members'] ?? 0)), 'hint' => 'Transacting in the period'],
            'right' => ['label' => 'Avg transaction', 'value' => $this->idr((float) ($d['avg_txn_value'] ?? 0))],
        ];
    }

    protected function pgcardTrend(): array
    {
        $rows = $this->report('pgcardMonthlyTrend')['data'] ?? [];

        return [
            'series'     => [['name' => 'Transactions', 'data' => array_map(fn ($r) => (int) $r['txn_count'], $rows)]],
            'categories' => array_column($rows, 'month'),
        ];
    }

    /** Top rows of a per-mall list ({mall: {mall_name, data: [{label, value, total_amount}]}}), biggest spenders first. */
    private function pgcardTop(string $endpoint, string $firstLabel): array
    {
        $flat = [];
        foreach ($this->report($endpoint)['data'] ?? [] as $code => $mall) {
            foreach ($mall['data'] ?? [] as $r) {
                $flat[] = [$r['label'], $code, $r['value'], $r['total_amount']];
            }
        }
        usort($flat, fn ($a, $b) => $b[3] <=> $a[3]);

        [$flat, $note] = $this->cut($flat);

        return [
            'note'    => $note,
            'columns' => [['label' => $firstLabel], ['label' => 'Mall', 'align' => 'center'], ['label' => 'Txns', 'align' => 'right'], ['label' => 'Spending', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r[0], $r[1], $this->num($r[2]), $this->idr($r[3])], $flat),
        ];
    }

    protected function pgcardCustomers(): array
    {
        return $this->pgcardTop('pgcardTopCustomers', 'Customer');
    }

    protected function pgcardTenants(): array
    {
        return $this->pgcardTop('pgcardTopTenants', 'Tenant');
    }

    // ───────────────────── Operation · Isort (BigQuery) ─────────────────────

    protected function isortSummary(): array
    {
        $d = $this->report('isortSummary')['data'] ?? [];
        $total = (int) ($d['total_case'] ?? 0);
        $closed = (int) ($d['total_closed'] ?? 0);

        return [
            'left'     => ['label' => 'Total cases', 'value' => $this->num($total), 'hint' => $this->num((int) ($d['total_overdue'] ?? 0)) . ' overdue'],
            'right'    => ['label' => 'Open', 'value' => $this->num((int) ($d['total_open'] ?? 0))],
            'pct'      => $total > 0 ? round($closed / $total * 100, 1) : 0,
            'barLabel' => 'Closed',
        ];
    }

    protected function isortByType(): array
    {
        $rows = $this->report('isortKaizenByType')['data'] ?? [];

        return ['series' => array_map(fn ($r) => (int) $r['total'], $rows), 'labels' => array_column($rows, 'kaizen_type')];
    }

    protected function isortTrend(): array
    {
        $rows = $this->report('isortMonthlyTrend')['data'] ?? [];

        return [
            'series'     => [['name' => 'Cases', 'data' => array_map(fn ($r) => (int) $r['total_case'], $rows)]],
            'categories' => array_column($rows, 'month'),
        ];
    }

    protected function isortByDepartment(): array
    {
        [$rows, $note] = $this->cut($this->report('isortDeptSummary')['data'] ?? []);

        return [
            'note'    => $note,
            'columns' => [['label' => 'Department'], ['label' => 'Cases', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r['department'], $this->num($r['total'])], $rows),
        ];
    }

    protected function isortByIncident(): array
    {
        [$rows, $note] = $this->cut($this->report('isortIncidentsByName')['data'] ?? []);

        return [
            'note'    => $note,
            'columns' => [['label' => 'Incident'], ['label' => 'Cases', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r['incident_name'], $this->num($r['total'])], $rows),
        ];
    }

    // ───────────────────── Parking · Valet (BigQuery) ─────────────────────

    protected function valetKpi(): array
    {
        $d = $this->report('valetKpiSummary')['data'] ?? [];

        return [
            'left'  => ['label' => 'Valet', 'value' => $this->num((float) ($d['total_valet'] ?? 0)), 'hint' => round((float) ($d['daily_avg_turnover'] ?? 0), 1) . ' per day'],
            'right' => ['label' => 'Service income', 'value' => $this->idr((float) ($d['total_income_service'] ?? 0))],
        ];
    }

    protected function valetPeakHours(): array
    {
        $hours = $this->report('valetPeakHours')['data'] ?? [];

        return [
            'series'     => [['name' => 'Check-ins', 'data' => array_map('intval', $hours)]],
            'categories' => array_map(fn ($h) => sprintf('%02d', $h), array_keys($hours)),
        ];
    }

    protected function valetRedemption(): array
    {
        $rows = $this->report('valetVoucherRedemption')['data']['by_status'] ?? [];

        return ['series' => array_map(fn ($r) => (int) $r['count'], $rows), 'labels' => array_column($rows, 'status')];
    }

    // ───────────────────── Event ─────────────────────

    protected function eventSummary(): array
    {
        $d = $this->report('eventSummary')['data'] ?? [];
        $total = (float) ($d['total_contract'] ?? 0);

        return [
            'left'     => ['label' => 'Total contract', 'value' => $this->idr($total), 'hint' => $this->num((float) ($d['total_count'] ?? 0)) . ' events'],
            'right'    => ['label' => 'Paid', 'value' => $this->idr((float) ($d['total_paid'] ?? 0))],
            'pct'      => $total > 0 ? round(((float) ($d['total_paid'] ?? 0)) / $total * 100, 1) : 0,
            'barLabel' => 'Paid share',
        ];
    }

    protected function eventByStatus(): array
    {
        $rows = array_values(array_filter($this->report('eventStatusByCompany')['data'] ?? [], fn ($r) => $r['total'] > 0));

        return ['series' => array_map(fn ($r) => (int) $r['total'], $rows), 'labels' => array_column($rows, 'status')];
    }

    protected function eventByType(): array
    {
        $rows = array_values(array_filter($this->report('eventByType')['data'] ?? [], fn ($r) => $r['count'] > 0));

        [$rows, $note] = $this->cut($rows);

        return [
            'note'    => $note,
            'columns' => [['label' => 'Type'], ['label' => 'Events', 'align' => 'right'], ['label' => 'Contract', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r['event_type'], $this->num($r['count']), $this->idr($r['total_contract'])], $rows),
        ];
    }

    // ───────────────────── Voucher & Product ─────────────────────

    protected function vplCompanyOverview(): array
    {
        $d = $this->report('vplCompanyOverview')['data'] ?? [];

        return [
            'columns' => [['label' => 'Company'], ['label' => 'Voucher stock', 'align' => 'right'], ['label' => 'Product stock', 'align' => 'right'], ['label' => 'Voucher value', 'align' => 'right'], ['label' => 'Product value', 'align' => 'right']],
            'rows'    => array_map(fn ($c) => [
                $c['cpnyid'], $this->num($c['voucher_stock']), $this->num($c['product_stock']), $this->idr($c['voucher_value']), $this->idr($c['product_value']),
            ], $d['by_company'] ?? []),
        ];
    }

    protected function vplByCategory(): array
    {
        $rows = array_slice($this->report('vplByCategory')['data'] ?? [], 0, 6);

        return [
            'series' => array_map(fn ($r) => max(0, round($r['voucher'] + $r['product'])), $rows),
            'labels' => array_column($rows, 'category'),
        ];
    }

    protected function vplUsageByReason(): array
    {
        $rows = array_slice($this->report('vplUsageByReason')['data'] ?? [], 0, 6);

        return [
            'series' => array_map(fn ($r) => max(0, round($r['voucher'] + $r['product'])), $rows),
            'labels' => array_column($rows, 'reason'),
        ];
    }

    protected function vplTopOut(): array
    {
        [$rows, $note] = $this->cut($this->report('vplTopOut')['data'] ?? [], 10); // the card is titled "Top 10 Most Out"

        return [
            'note'    => $note,
            'columns' => [['label' => 'Product'], ['label' => 'Type', 'align' => 'center'], ['label' => 'Qty out', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r['label'], $r['type'] === 'V' ? 'Voucher' : 'Product', $this->num($r['total'])], $rows),
        ];
    }

    protected function vplVoucherList(): array
    {
        [$rows, $note] = $this->cut($this->report('vplVoucherList')['data'] ?? []);

        return [
            'note'    => $note,
            'columns' => [['label' => 'Voucher'], ['label' => 'Category'], ['label' => 'Stock', 'align' => 'right'], ['label' => 'Total value', 'align' => 'right']],
            'rows'    => array_map(fn ($r) => [$r['product_name'], $r['category'], $this->num($r['stock']), $this->idr($r['total_value'])], $rows),
        ];
    }
}
