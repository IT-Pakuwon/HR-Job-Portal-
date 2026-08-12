<?php

namespace App\Http\Controllers;

use App\Exports\VplStockSummaryExport;
use App\Exports\VplStockVoucherExport;
use App\Models\MsVplAging;
use App\Models\MsVplProduct;
use App\Models\MsVplProductBal;
use App\Models\TrxVplReceiveDetail;
use App\Models\TrxVplTransferDetail;
use App\Models\TrxVplUsageDetail;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VplReportController extends Controller
{
    private const WHS_COLLECTION = 'WHCOLLECTION';
    private const WHS_LOYALTY    = 'WHLOYALTY';
    private const WHS_PROMOTION  = 'WHPROMOTION';

    /** tr_vpl_receive.receive_type -> display grouping for the Stock Summary report's "Voucher Sources" block. */
    private const SOURCE_MAP = [
        'Media Promo'   => ['group' => 'Promotion', 'column' => 'CL Media promo', 'row_label' => 'MP'],
        'Event'         => ['group' => 'Promotion', 'column' => 'Event/TR', 'row_label' => 'EVT'],
        'Promo Levy'    => ['group' => 'Leasing', 'column' => 'Promo Levy', 'row_label' => 'PL'],
        'Rent/Discount' => ['group' => 'Leasing', 'column' => 'Rent/Discount', 'row_label' => 'RENT'],
    ];

    /** tr_vpl_usage_detail.purpose_id -> "Voucher Used in Current Period" bucket. Unmapped purposes fold into Internal Use. */
    private const PURPOSE_MAP = [
        'Redeem PG Card' => 'Loyalty',
        'Promotion'      => 'Promotion',
        'Entertaiment'   => 'Entertainment',
        'Management'     => 'Internal Use',
        'Dijual'         => 'Internal Use',
        'Write Off'      => 'Internal Use',
    ];

    private const USED_COLUMNS = ['Loyalty', 'Promotion', 'Entertainment', 'Internal Use'];

    /*
    |--------------------------------------------------------------------------
    | INDEX (Main Page)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $hasVPCOLLACCESS    = $user->hasRole('VPCOLLACCESS');
        $hasVPPRMTNACCESS   = $user->hasRole('VPPRMTNACCESS');
        $hasVPLOYALTYACCESS = $user->hasRole('VPLOYALTYACCESS');

        $tabCount = 2;

        $defaultReport = 'stock-voucher';

        return view('pages.report-vpl.index', [

            'hasVPCOLLACCESS'    => $hasVPCOLLACCESS,

            'hasVPPRMTNACCESS'   => $hasVPPRMTNACCESS,

            'hasVPLOYALTYACCESS' => $hasVPLOYALTYACCESS,

            'tabCount'      => $tabCount,

            'defaultReport' => $defaultReport,

            'companies'     => $this->cpnyIds(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | JSON (server-rendered HTML fragments per report type)
    |--------------------------------------------------------------------------
    */
    public function json(Request $request, $type)
    {
        switch ($type) {
            case 'stock-voucher':
                return $this->stockVoucherJson($request);

            case 'stock-summary':
                return $this->stockSummaryJson($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    public function export(Request $request, $type)
    {
        switch ($type) {
            case 'stock-voucher':
                return $this->stockVoucherExport($request);

            case 'stock-summary':
                return $this->stockSummaryExport($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK VOUCHER REPORT
    |--------------------------------------------------------------------------
    */
    private function stockVoucherJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $groups = $this->buildStockVoucherReport($cpnyid, $year, $month);

        return view('pages.report-vpl.partials.stock-voucher-table', [
            'groups' => $groups,
            'cpnyid' => $cpnyid,
            'year'   => $year,
            'month'  => $month,
        ]);
    }

    private function stockVoucherExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $groups = $this->buildStockVoucherReport($cpnyid, $year, $month);

        $filename = "stock-voucher-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplStockVoucherExport($groups, $cpnyid, $year, $month), $filename);
    }

    /** @return array{0: string, 1: int, 2: int} */
    private function resolveStockVoucherParams(Request $request): array
    {
        $allowedCpny = $this->cpnyIds();

        $cpnyid = $request->input('cpnyid', $allowedCpny[0] ?? null);

        abort_unless($cpnyid && in_array($cpnyid, $allowedCpny, true), 403);

        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        return [$cpnyid, $year, $month];
    }

    private function buildStockVoucherReport(string $cpnyid, int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $batchRows = $this->batchStockRows($cpnyid, $year, $month);

        if (empty($batchRows)) {
            return [];
        }

        $inRows  = $this->inMovementRows($cpnyid, $monthStart, $monthEnd);
        $outRows = $this->outMovementRows($cpnyid, $monthStart, $monthEnd);

        $groups = [];

        foreach ($batchRows as $key => $row) {
            $product = $row['product'];
            $bal     = $row['bal'];

            $rows = array_merge(
                $inRows[$key] ?? [],
                $outRows[$key] ?? []
            );

            usort($rows, fn ($a, $b) => $a['date'] <=> $b['date']);

            $groups[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $row['category_label'],
                'expired_date'   => $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date,
                'nominal'        => (float) $product->product_value,
                'beginning'      => $row['beginning'],
                'in_total'       => $row['month_in'],
                'out_total'      => $row['month_out'],
                'ending'         => $row['ending'],
                'total_nominal'  => $row['ending'] * (float) $product->product_value,
                'rows'           => $rows,
            ];
        }

        usort($groups, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['expired_date']]
                <=> [$b['category_label'], $b['tenant'], $b['expired_date']];
        });

        return $this->attachRowspans($groups);
    }

    /**
     * Per-product+expiry-batch beginning/in/out/ending for the given month, keyed by
     * "product_id|expiredKey". Shared by the detail (Stock Voucher) and summary
     * (Stock Summary) reports so the balance/cumulative math only lives in one place.
     *
     * @return array<string, array{product: MsVplProduct, bal: MsVplProductBal, category_label: string, beginning: float, month_in: float, month_out: float, ending: float}>
     */
    private function batchStockRows(string $cpnyid, int $year, int $month): array
    {
        // Universe of tenant+expiry batches tracked at WHCOLLECTION this year.
        $balances = MsVplProductBal::where('cpnyid', $cpnyid)
            ->where('year', $year)
            ->where('whs_id', self::WHS_COLLECTION)
            ->get();

        if ($balances->isEmpty()) {
            return [];
        }

        $products = MsVplProduct::where('cpnyid', $cpnyid)
            ->get()
            ->keyBy('product_id');

        // Monthly In/Out per product+expiry, derived from the ledger so Promotion's
        // "out at usage" rule can override what sp_process_vpl actually posted to
        // WHCOLLECTION's own balance at transfer time.
        [$monthlyIn, $monthlyOutAmt] = $this->ledgerMonthlyInOut($cpnyid, $year);

        $rows = [];

        foreach ($balances as $bal) {
            $key = $bal->product_id.'|'.$this->expiredKey($bal->expired_date);

            $product = $products->get($bal->product_id);

            if (!$product) {
                continue;
            }

            $in  = $monthlyIn[$key] ?? [];
            $out = $monthlyOutAmt[$key] ?? [];

            $beginning = (float) $bal->begqty;

            for ($m = 1; $m < $month; $m++) {
                $beginning += ($in[$m] ?? 0) - ($out[$m] ?? 0);
            }

            $monthIn  = $in[$month] ?? 0;
            $monthOut = $out[$month] ?? 0;

            $rows[$key] = [
                'product'        => $product,
                'bal'            => $bal,
                'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                'beginning'      => $beginning,
                'month_in'       => $monthIn,
                'month_out'      => $monthOut,
                'ending'         => $beginning + $monthIn - $monthOut,
            ];
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK SUMMARY REPORT (aging + voucher sources + voucher used)
    |--------------------------------------------------------------------------
    */
    private function stockSummaryJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->buildStockSummaryReport($cpnyid, $year, $month);

        return view('pages.report-vpl.partials.stock-summary-table', [
            'rows'   => $rows,
            'meta'   => $this->summaryColumnMeta(),
            'cpnyid' => $cpnyid,
            'year'   => $year,
            'month'  => $month,
        ]);
    }

    private function stockSummaryExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->buildStockSummaryReport($cpnyid, $year, $month);

        $filename = "stock-summary-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(
            new VplStockSummaryExport($rows, $this->summaryColumnMeta(), $cpnyid, $year, $month),
            $filename
        );
    }

    /** Column metadata (aging bucket labels, voucher-source columns, usage-purpose columns) for the summary table's header + cell iteration. */
    private function summaryColumnMeta(): array
    {
        $agingLabels = MsVplAging::where('status', 'A')->orderBy('order_age')->pluck('age_descr')->all();

        $sourceColumns = [];
        foreach (self::SOURCE_MAP as $meta) {
            $sourceColumns[$meta['column']] = $meta['group'];
        }

        return [
            'aging'   => $agingLabels,
            'sources' => $sourceColumns,
            'used'    => self::USED_COLUMNS,
        ];
    }

    private function buildStockSummaryReport(string $cpnyid, int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        // For a period still in progress (or in the future), age against today rather than
        // a month-end that hasn't happened yet; only a fully-elapsed past period ages as of
        // its own month-end.
        $agingAsOf = Carbon::now()->lt($monthEnd) ? Carbon::now() : $monthEnd;

        $batchRows = $this->batchStockRows($cpnyid, $year, $month);

        if (empty($batchRows)) {
            return [];
        }

        $sources        = $this->batchSourceTypes($cpnyid);
        $purposeOut     = $this->batchPurposeOut($cpnyid, $monthStart, $monthEnd);
        $loyaltyTransfer = $this->batchLoyaltyTransferOut($cpnyid, $monthStart, $monthEnd);
        $agingBuckets   = MsVplAging::where('status', 'A')->orderBy('order_age')->get();

        $rows = [];

        foreach ($batchRows as $key => $row) {
            $product     = $row['product'];
            $bal         = $row['bal'];
            $receiveType = $sources[$key] ?? null;
            $sourceMeta  = self::SOURCE_MAP[$receiveType] ?? null;
            $nominal     = (float) $product->product_value;
            $expiredDate = $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date;

            // Usage/redemption-based split — feeds "Voucher Used in Current Period" (value).
            $used = array_fill_keys(self::USED_COLUMNS, 0.0);
            foreach ($purposeOut[$key] ?? [] as $bucket => $qty) {
                $used[$bucket] += $qty;
            }

            // Stock roll-forward's Out Loy (qty) is the WHCOLLECTION->WHLOYALTY transfer —
            // distinct from $used['Loyalty'] above, which is actual usage recorded at
            // WHLOYALTY and may lag behind the transfer by one or more periods.
            $loyaltyTransferQty = $loyaltyTransfer[$key] ?? 0;

            $rows[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $row['category_label'],
                'source_group'   => $sourceMeta['group'] ?? 'Other',
                'source_column'  => $sourceMeta['column'] ?? ($receiveType ?? 'Unknown'),
                'source_label'   => $sourceMeta['row_label'] ?? ($receiveType ?? '-'),
                'expired_date'   => $expiredDate,
                'nominal'        => $nominal,
                'beginning'      => $row['beginning'],
                'in_total'       => $row['month_in'],
                'out_loyalty'    => $loyaltyTransferQty,
                'out_promotion'  => $used['Promotion'],
                'out_entertain'  => $used['Entertainment'],
                'out_internal'   => $used['Internal Use'],
                'out_total'      => $row['month_out'],
                'ending'         => $row['ending'],
                'value'          => $row['ending'] * $nominal,
                'aging_bucket'   => $this->resolveAgingBucket(
                    $expiredDate ? Carbon::parse($expiredDate) : null,
                    $agingAsOf,
                    $agingBuckets
                ),
                'source_value'   => $row['month_in'] * $nominal,
                'used_value'     => [
                    'Loyalty'       => $used['Loyalty'] * $nominal,
                    'Promotion'     => $used['Promotion'] * $nominal,
                    'Entertainment' => $used['Entertainment'] * $nominal,
                    'Internal Use'  => $used['Internal Use'] * $nominal,
                ],
            ];
        }

        usort($rows, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['source_label'], $a['expired_date']]
                <=> [$b['category_label'], $b['tenant'], $b['source_label'], $b['expired_date']];
        });

        return $this->attachSummaryGroupingAndTotals($rows, $agingBuckets);
    }

    /**
     * Latest receive_type per product+expiry batch, from completed receives landing at
     * WHCOLLECTION. If a batch was topped up under more than one source over time, the
     * most recent receive's type wins (edge case — batches are expected to share one source).
     *
     * @return array<string, string>
     */
    private function batchSourceTypes(string $cpnyid): array
    {
        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->where('tr_vpl_receive_detail.whs_id', self::WHS_COLLECTION)
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive.receive_type',
                'tr_vpl_receive.receive_date',
            ])
            ->orderBy('tr_vpl_receive.receive_date')
            ->get();

        $sources = [];

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);
            $sources[$key] = $r->receive_type;
        }

        return $sources;
    }

    /**
     * Net usage qty in the given month, per product+expiry batch and "Voucher Used"
     * bucket — this is a redemption/consumption metric, independent of the stock
     * roll-forward's Out Loy (which tracks the WHCOLLECTION->WHLOYALTY transfer, not
     * whether that stock has actually been redeemed yet). Usage at WHLOYALTY (recorded
     * by the CUSTOMERSERVICE department) always buckets as Loyalty; usage at WHPROMOTION
     * buckets via PURPOSE_MAP.
     *
     * @return array<string, array<string, float>>
     */
    private function batchPurposeOut(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->whereIn('tr_vpl_usage_detail.whs_id', [self::WHS_PROMOTION, self::WHS_LOYALTY])
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.whs_id',
                'tr_vpl_usage_detail.purpose_id',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
            ])
            ->get();

        $out = [];

        foreach ($usages as $u) {
            $key    = $u->product_id.'|'.$this->expiredKey($u->expired_date);
            $bucket = $u->whs_id === self::WHS_LOYALTY ? 'Loyalty' : (self::PURPOSE_MAP[$u->purpose_id] ?? 'Internal Use');
            $qty    = (float) $u->qty_usage - (float) $u->qty_return_usage;

            $out[$key][$bucket] = ($out[$key][$bucket] ?? 0) + $qty;
        }

        return $out;
    }

    /**
     * Qty transferred WHCOLLECTION -> WHLOYALTY in the given month, per product+expiry
     * batch. This is the actual mechanism Loyalty vouchers leave WHCOLLECTION by (there's
     * no 'Redeem PG Card' purpose_id usage in practice) — folded into the Loyalty bucket
     * alongside batchPurposeOut() so Out Loy+Prm+Ent+Internal reconciles to out_total.
     *
     * @return array<string, float>
     */
    private function batchLoyaltyTransferOut(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->where('tr_vpl_transfer_detail.from_whs_id', self::WHS_COLLECTION)
            ->where('tr_vpl_transfer_detail.to_whs_id', self::WHS_LOYALTY)
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer',
            ])
            ->get();

        $out = [];

        foreach ($transfers as $t) {
            $key = $t->product_id.'|'.$this->expiredKey($t->expired_date);
            $out[$key] = ($out[$key] ?? 0) + abs((float) $t->qty_transfer);
        }

        return $out;
    }

    /** Bucket a batch's expiry against ms_vpl_aging ranges, measured in days from the period's month-end. */
    private function resolveAgingBucket(?Carbon $expiredDate, Carbon $monthEnd, Collection $agingBuckets): string
    {
        if ($agingBuckets->isEmpty()) {
            return '-';
        }

        // No expiry date tracked for this batch: treat as the longest-lived bucket.
        if (!$expiredDate) {
            return $agingBuckets->last()->age_descr;
        }

        $ageDays = $monthEnd->diffInDays($expiredDate, false);

        foreach ($agingBuckets as $bucket) {
            if ($ageDays >= $bucket->start_age && $ageDays <= $bucket->end_age) {
                return $bucket->age_descr;
            }
        }

        return $agingBuckets->last()->age_descr;
    }

    /** Turn the flat, sorted batch rows into category-header / tenant-subtotal / detail render rows. */
    private function attachSummaryGroupingAndTotals(array $rows, Collection $agingBuckets): array
    {
        if (empty($rows)) {
            return [];
        }

        $agingLabels = $agingBuckets->pluck('age_descr')->all();
        $sourceCols  = collect(self::SOURCE_MAP)->pluck('column')->unique()->all();

        $output = [];

        foreach (collect($rows)->groupBy('category_label') as $categoryLabel => $categoryRows) {
            $output[] = ['type' => 'category_header', 'category_label' => $categoryLabel];

            foreach ($categoryRows->groupBy('tenant') as $tenant => $tenantRows) {
                $subtotal = [
                    'type'          => 'tenant_subtotal',
                    'tenant'        => $tenant,
                    'nominal'       => $tenantRows->first()['nominal'],
                    'beginning'     => $tenantRows->sum('beginning'),
                    'in_total'      => $tenantRows->sum('in_total'),
                    'out_loyalty'   => $tenantRows->sum('out_loyalty'),
                    'out_promotion' => $tenantRows->sum('out_promotion'),
                    'out_entertain' => $tenantRows->sum('out_entertain'),
                    'out_internal'  => $tenantRows->sum('out_internal'),
                    'out_total'     => $tenantRows->sum('out_total'),
                    'ending'        => $tenantRows->sum('ending'),
                    'value'         => $tenantRows->sum('value'),
                    'aging'         => array_fill_keys($agingLabels, 0.0),
                    'sources'       => array_fill_keys($sourceCols, 0.0),
                    'used'          => array_fill_keys(self::USED_COLUMNS, 0.0),
                ];

                foreach ($tenantRows as $r) {
                    $subtotal['aging'][$r['aging_bucket']] += $r['value'];

                    if (isset($subtotal['sources'][$r['source_column']])) {
                        $subtotal['sources'][$r['source_column']] += $r['source_value'];
                    }

                    foreach ($r['used_value'] as $bucket => $val) {
                        $subtotal['used'][$bucket] += $val;
                    }
                }

                $output[] = $subtotal;

                foreach ($tenantRows as $r) {
                    $r['type'] = 'detail';
                    $output[] = $r;
                }
            }
        }

        return $output;
    }

    /**
     * @return array{0: array<string, array<int, float>>, 1: array<string, array<int, float>>}
     */
    private function ledgerMonthlyInOut(string $cpnyid, int $year): array
    {
        $rows = DB::connection('pgsql5')->table('tr_vpl_ledger')
            ->where('cpnyid', $cpnyid)
            ->where('perpost', 'like', $year.'%')
            ->where('status', 'A')
            ->whereIn('whs_id', [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION])
            ->whereIn('transaction_source', ['Receive', 'Transfer In', 'Usage', 'Return'])
            ->select('product_id', 'expired_date', 'whs_id', 'transaction_source', 'perpost', 'qty')
            ->get();

        $monthlyIn  = [];
        $monthlyOut = [];

        foreach ($rows as $row) {
            $key   = $row->product_id.'|'.$this->expiredKey($row->expired_date);
            $month = (int) substr((string) $row->perpost, 4, 2);
            $qty   = (float) $row->qty;

            if ($row->whs_id === self::WHS_COLLECTION && in_array($row->transaction_source, ['Receive', 'Transfer In'], true)) {
                $monthlyIn[$key][$month] = ($monthlyIn[$key][$month] ?? 0) + $qty;
            } elseif ($row->whs_id === self::WHS_LOYALTY && $row->transaction_source === 'Transfer In') {
                $monthlyOut[$key][$month] = ($monthlyOut[$key][$month] ?? 0) + $qty;
            } elseif ($row->whs_id === self::WHS_PROMOTION && in_array($row->transaction_source, ['Usage', 'Return'], true)) {
                $monthlyOut[$key][$month] = ($monthlyOut[$key][$month] ?? 0) - $qty;
            }
        }

        return [$monthlyIn, $monthlyOut];
    }

    /** Receive + Return-Transfer-In lines landing at WHCOLLECTION in the given month. */
    private function inMovementRows(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rows = [];

        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->where('tr_vpl_receive_detail.whs_id', self::WHS_COLLECTION)
            ->whereBetween('tr_vpl_receive.receive_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive_detail.qty_receive as qty',
                'tr_vpl_receive.receive_date as doc_date',
                'tr_vpl_receive.receive_id as doc_no',
                'tr_vpl_receive.receive_type as untuk_pembayaran',
                'tr_vpl_receive.source_receive_dept as diterima_dari',
            ])
            ->get();

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);
            $rows[$key][] = [
                'direction'         => 'in',
                'doc_label'         => 'Receive',
                'doc_no'            => $r->doc_no,
                'date'              => Carbon::parse($r->doc_date),
                'qty'               => (float) $r->qty,
                'diterima_dari'     => $r->diterima_dari,
                'untuk_pembayaran'  => $r->untuk_pembayaran,
                'diambil_oleh'      => null,
                'keperluan'         => null,
            ];
        }

        $returnTransfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->where('tr_vpl_transfer_detail.to_whs_id', self::WHS_COLLECTION)
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer as qty',
                'tr_vpl_transfer.transfer_date as doc_date',
                'tr_vpl_transfer.transfer_id as doc_no',
                'tr_vpl_transfer.department as diterima_dari',
            ])
            ->get();

        foreach ($returnTransfers as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);
            $rows[$key][] = [
                'direction'         => 'in',
                'doc_label'         => 'Return Transfer',
                'doc_no'            => $r->doc_no,
                'date'              => Carbon::parse($r->doc_date),
                'qty'               => abs((float) $r->qty),
                'diterima_dari'     => $r->diterima_dari,
                'untuk_pembayaran'  => null,
                'diambil_oleh'      => null,
                'keperluan'         => null,
            ];
        }

        return $rows;
    }

    /** Transfer-to-Loyalty + Usage/Return-at-Promotion lines in the given month. */
    private function outMovementRows(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rows = [];

        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->where('tr_vpl_transfer_detail.from_whs_id', self::WHS_COLLECTION)
            ->where('tr_vpl_transfer_detail.to_whs_id', self::WHS_LOYALTY)
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer as qty',
                'tr_vpl_transfer.transfer_date as doc_date',
                'tr_vpl_transfer.transfer_id as doc_no',
                'tr_vpl_transfer.created_user as diambil_oleh',
                'tr_vpl_transfer.department as keperluan',
            ])
            ->get();

        foreach ($transfers as $t) {
            $key = $t->product_id.'|'.$this->expiredKey($t->expired_date);
            $rows[$key][] = [
                'direction'         => 'out',
                'doc_label'         => 'Transfer',
                'doc_no'            => $t->doc_no,
                'date'              => Carbon::parse($t->doc_date),
                'qty'               => abs((float) $t->qty),
                'diterima_dari'     => null,
                'untuk_pembayaran'  => null,
                'diambil_oleh'      => $t->diambil_oleh,
                'keperluan'         => $t->keperluan,
            ];
        }

        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->where('tr_vpl_usage_detail.whs_id', self::WHS_PROMOTION)
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
                'tr_vpl_usage.usagetype',
                'tr_vpl_usage.usage_date as doc_date',
                'tr_vpl_usage.usage_id as doc_no',
                'tr_vpl_usage.created_user as diambil_oleh',
                'tr_vpl_usage.department as keperluan',
            ])
            ->get();

        foreach ($usages as $u) {
            $key = $u->product_id.'|'.$this->expiredKey($u->expired_date);
            $isReturn = $u->usagetype === 'Return';
            $rows[$key][] = [
                'direction'         => 'out',
                'doc_label'         => $isReturn ? 'Return' : 'Usage',
                'doc_no'            => $u->doc_no,
                'date'              => Carbon::parse($u->doc_date),
                'qty'               => $isReturn ? -abs((float) $u->qty_return_usage) : abs((float) $u->qty_usage),
                'diterima_dari'     => null,
                'untuk_pembayaran'  => null,
                'diambil_oleh'      => $u->diambil_oleh,
                'keperluan'         => $u->keperluan,
            ];
        }

        return $rows;
    }

    /** Attach group_rowspan (per expiry batch) and tenant_rowspan/is_first_* flags (per tenant/category). */
    private function attachRowspans(array $groups): array
    {
        $tenantNo = 0;

        foreach ($groups as $i => &$group) {
            $group['group_rowspan']     = max(1, count($group['rows']));
            $group['is_first_of_tenant'] = $i === 0 || $groups[$i - 1]['product_id'] !== $group['product_id'];
            $group['is_first_of_category'] = $i === 0 || $groups[$i - 1]['category_label'] !== $group['category_label'];

            if ($group['is_first_of_tenant']) {
                $tenantNo++;
            }
            $group['tenant_no'] = $tenantNo;
        }
        unset($group);

        foreach ($groups as $i => &$group) {
            if (!$group['is_first_of_tenant']) {
                continue;
            }

            $span = 0;
            for ($j = $i; $j < count($groups) && $groups[$j]['product_id'] === $group['product_id']; $j++) {
                $span += $groups[$j]['group_rowspan'];
            }
            $group['tenant_rowspan'] = $span;
        }
        unset($group);

        foreach ($groups as $i => &$group) {
            if (!$group['is_first_of_category']) {
                continue;
            }

            $span = 0;
            for ($j = $i; $j < count($groups) && $groups[$j]['category_label'] === $group['category_label']; $j++) {
                $span += $groups[$j]['group_rowspan'];
            }
            $group['category_rowspan'] = $span;
        }
        unset($group);

        return $groups;
    }

    private function expiredKey($date): string
    {
        if ($date === null) {
            return 'NULL';
        }

        return $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
    }

    private function cpnyIds(): array
    {
        $user = Auth::user();

        return Usercpny::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->toArray();
    }
}
