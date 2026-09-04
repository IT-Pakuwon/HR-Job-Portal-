<?php

namespace App\Http\Controllers;

use App\Exports\VplLedgerExport;
use App\Exports\VplLoyaltyUsageExport;
use App\Exports\VplProductReportExport;
use App\Exports\VplProductStockExport;
use App\Exports\VplStockOutVoucherExport;
use App\Exports\VplStockSummaryExport;
use App\Exports\VplStockVoucherExport;
use App\Exports\VplTrialBalanceSummaryGroupExport;
use App\Models\MsVplAging;
use App\Models\MsVplProduct;
use App\Models\MsVplProductBal;
use App\Models\TrxVplReceiveDetail;
use App\Models\TrxVplSettlementDetail;
use App\Models\TrxVplTransferDetail;
use App\Models\TrxVplUsageDetail;
use App\Models\Usercpny;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

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

        $tabCount = 8;

        $defaultReport = 'stock-voucher';

        return view('pages.report-vpl.index', [

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

            case 'loyalty-usage':
                return $this->loyaltyUsageJson($request);

            case 'product-report':
                return $this->productReportJson($request);

            case 'summary-group':
                return $this->summaryGroupJson($request);

            case 'stock-out-voucher':
                return $this->stockOutVoucherJson($request);

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

            case 'loyalty-usage':
                return $this->loyaltyUsageExport($request);

            case 'product-report':
                return $this->productReportExport($request);

            case 'summary-group':
                return $this->summaryGroupExport($request);

            case 'stock-out-voucher':
                return $this->stockOutVoucherExport($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IN & OUT VOUCHER PRODUCT (raw tr_vpl_ledger browser — every movement,
    | every warehouse, server-side DataTable)
    |--------------------------------------------------------------------------
    */

    /** DataTables per-column Type filter (exact match, see inOutData()). */
    private const LEDGER_TYPE_OPTIONS = ['Receive', 'Transfer In', 'Transfer Out', 'Usage', 'Return'];

    /** Shared select list/joins for the ledger browser, scoped to one company+period — used by both the DataTable and the Excel export. */
    private function ledgerBaseQuery(string $cpnyid, int $year, int $month)
    {
        $perpost = $year.str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return DB::connection('pgsql5')->table('tr_vpl_ledger as l')
            ->leftJoin('ms_vpl_product as p', 'p.product_id', '=', 'l.product_id')
            ->leftJoin('tr_vpl_usage as u', function ($join) {
                $join->on('u.usage_id', '=', 'l.refnbr')
                    ->where('l.transaction_source', '=', 'Usage');
            })
            ->where('l.cpnyid', $cpnyid)
            ->where('l.perpost', $perpost)
            ->where('l.status', 'A')
            ->select([
                'l.refnbr',
                DB::raw("to_char(l.created_at, 'YYYY-MM-DD HH24:MI') as create_date"),
                'l.cpnyid',
                'l.transaction_source',
                'u.usagetype',
                DB::raw("to_char(l.postdate, 'YYYY-MM-DD') as post_date"),
                'l.product_id',
                DB::raw("COALESCE(to_char(l.expired_date, 'YYYY-MM-DD'), 'No Expired') as expired_date_fmt"),
                'p.product_name',
                'l.qty',
                DB::raw("COALESCE(l.reference_refnbr, '-') as reference_refnbr"),
                DB::raw("COALESCE(l.purpose_id, '-') as purpose_id"),
                'l.whs_id',
            ]);
    }

    private function ledgerTypeLabel(object $r): string
    {
        if ($r->transaction_source !== 'Usage') {
            return $r->transaction_source;
        }

        return $r->usagetype === 'Return' ? 'Return' : 'Usage';
    }

    /** Optional Select2 filters (Ref No / Product Name / Type / Reference Refnbr) shared by the DataTable and the Excel export. */
    private function applyLedgerFilters($query, Request $request)
    {
        if ($request->filled('refnbr')) {
            $query->where('l.refnbr', $request->input('refnbr'));
        }

        if ($request->filled('product_name')) {
            $query->where('p.product_name', $request->input('product_name'));
        }

        if ($request->filled('reference_refnbr')) {
            $query->where('l.reference_refnbr', $request->input('reference_refnbr'));
        }

        if ($request->filled('type')) {
            $type = $request->input('type');

            if ($type === 'Return') {
                $query->where('l.transaction_source', 'Usage')->where('u.usagetype', 'Return');
            } elseif ($type === 'Usage') {
                $query->where('l.transaction_source', 'Usage')
                    ->where(function ($q2) {
                        $q2->whereNull('u.usagetype')->orWhere('u.usagetype', '<>', 'Return');
                    });
            } elseif (in_array($type, self::LEDGER_TYPE_OPTIONS, true)) {
                $query->where('l.transaction_source', $type);
            }
        }

        return $query;
    }

    /** Select2 remote-search options for the ledger browser's Ref No / Product Name / Reference Refnbr filters. */
    public function inOutOptions(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);
        $perpost = $year.str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $columnMap = [
            'refnbr'           => 'l.refnbr',
            'product_name'     => 'p.product_name',
            'reference_refnbr' => 'l.reference_refnbr',
        ];

        $field = $request->input('field');
        abort_unless(isset($columnMap[$field]), 404);
        $column = $columnMap[$field];

        $term = trim((string) $request->input('term', ''));

        $query = DB::connection('pgsql5')->table('tr_vpl_ledger as l')
            ->leftJoin('ms_vpl_product as p', 'p.product_id', '=', 'l.product_id')
            ->where('l.cpnyid', $cpnyid)
            ->where('l.perpost', $perpost)
            ->where('l.status', 'A')
            ->whereNotNull($column);

        if ($term !== '') {
            $query->where($column, 'like', '%'.$term.'%');
        }

        $values = $query->distinct()->orderBy($column)->limit(50)->pluck($column);

        return response()->json(['results' => $values->map(fn ($v) => ['id' => $v, 'text' => $v])]);
    }

    public function inOutData(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $query = $this->applyLedgerFilters($this->ledgerBaseQuery($cpnyid, $year, $month), $request);

        return \DataTables::of($query)
            ->addColumn('type_label', fn ($r) => $this->ledgerTypeLabel($r))
            ->filterColumn('type_label', function ($q, $keyword) {
                if ($keyword === 'Return') {
                    $q->where('l.transaction_source', 'Usage')->where('u.usagetype', 'Return');
                } elseif ($keyword === 'Usage') {
                    $q->where('l.transaction_source', 'Usage')
                        ->where(function ($q2) {
                            $q2->whereNull('u.usagetype')->orWhere('u.usagetype', '<>', 'Return');
                        });
                } elseif (in_array($keyword, self::LEDGER_TYPE_OPTIONS, true)) {
                    $q->where('l.transaction_source', $keyword);
                }
            })
            // Postgres can't reference a SELECT-list alias inside WHERE, so every column
            // above that's a DB::raw()/COALESCE() alias needs an explicit filterColumn()
            // pointing back at the real expression — Yajra's default per-column search
            // would otherwise emit "WHERE create_date LIKE ?" and 500.
            ->filterColumn('create_date', function ($q, $keyword) {
                $q->whereRaw("to_char(l.created_at, 'YYYY-MM-DD HH24:MI') LIKE ?", ['%'.$keyword.'%']);
            })
            ->filterColumn('post_date', function ($q, $keyword) {
                $q->whereRaw("to_char(l.postdate, 'YYYY-MM-DD') LIKE ?", ['%'.$keyword.'%']);
            })
            ->filterColumn('expired_date_fmt', function ($q, $keyword) {
                $q->whereRaw("COALESCE(to_char(l.expired_date, 'YYYY-MM-DD'), 'No Expired') LIKE ?", ['%'.$keyword.'%']);
            })
            ->filterColumn('reference_refnbr', function ($q, $keyword) {
                $q->where('l.reference_refnbr', 'like', '%'.$keyword.'%');
            })
            ->filterColumn('purpose_id', function ($q, $keyword) {
                $q->where('l.purpose_id', 'like', '%'.$keyword.'%');
            })
            ->editColumn('qty', fn ($r) => number_format((float) $r->qty, 0, ',', '.'))
            ->orderColumn('type_label', 'l.transaction_source $1')
            ->make(true);
    }

    public function inOutExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->applyLedgerFilters($this->ledgerBaseQuery($cpnyid, $year, $month), $request)
            ->orderByDesc('l.created_at')
            ->get()
            ->map(fn ($r) => [
                $r->refnbr,
                $r->create_date,
                $r->cpnyid,
                $this->ledgerTypeLabel($r),
                $r->post_date,
                $r->product_id,
                $r->expired_date_fmt,
                $r->product_name,
                (float) $r->qty,
                $r->reference_refnbr,
                $r->purpose_id,
                $r->whs_id,
            ]);

        $filename = "in-out-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplLedgerExport($rows), $filename);
    }

    /*
    |--------------------------------------------------------------------------
    | VOUCHER & PRODUCT STOCK (stock as of a period, per product/expiry/
    | warehouse, every warehouse, server-side DataTable)
    |--------------------------------------------------------------------------
    */

    /** Shared select list/joins for the stock browser — stock rolled forward through the selected month only (periods after it are excluded), scoped to one company+year. */
    private function productStockBaseQuery(string $cpnyid, int $year, int $month)
    {
        $periods = collect(range(1, $month))->map(fn ($m) => str_pad((string) $m, 2, '0', STR_PAD_LEFT));
        $inSum   = $periods->map(fn ($mm) => "COALESCE(b.period{$mm}in, 0)")->implode(' + ');
        $outSum  = $periods->map(fn ($mm) => "COALESCE(b.period{$mm}out, 0)")->implode(' + ');
        $stockExpr = "COALESCE(b.begqty, 0) + ({$inSum}) - ({$outSum}) as stock";

        return DB::connection('pgsql5')->table('ms_vpl_product_bal as b')
            ->join('ms_vpl_product as p', 'p.product_id', '=', 'b.product_id')
            ->where('b.cpnyid', $cpnyid)
            ->where('b.year', $year)
            ->select([
                'b.cpnyid',
                'b.product_id',
                DB::raw("COALESCE(to_char(b.expired_date, 'YYYY-MM-DD'), 'No Expired') as expired_date_fmt"),
                'p.product_name',
                'p.product_value',
                'p.product_uom',
                'b.whs_id',
                DB::raw($stockExpr),
            ]);
    }

    /** Optional Select2 filters (Product ID / Name / Warehouse) shared by the DataTable and the Excel export. */
    private function applyProductStockFilters($query, Request $request)
    {
        if ($request->filled('product_id')) {
            $query->where('b.product_id', $request->input('product_id'));
        }

        if ($request->filled('product_name')) {
            $query->where('p.product_name', $request->input('product_name'));
        }

        if ($request->filled('whs_id')) {
            $query->where('b.whs_id', $request->input('whs_id'));
        }

        return $query;
    }

    /** Select2 remote-search options for the stock browser's Product ID / Name / Warehouse filters. */
    public function productStockOptions(Request $request)
    {
        [$cpnyid, $year] = $this->resolveStockVoucherParams($request);

        $columnMap = [
            'product_id'   => 'b.product_id',
            'product_name' => 'p.product_name',
            'whs_id'       => 'b.whs_id',
        ];

        $field = $request->input('field');
        abort_unless(isset($columnMap[$field]), 404);
        $column = $columnMap[$field];

        $term = trim((string) $request->input('term', ''));

        $query = DB::connection('pgsql5')->table('ms_vpl_product_bal as b')
            ->join('ms_vpl_product as p', 'p.product_id', '=', 'b.product_id')
            ->where('b.cpnyid', $cpnyid)
            ->where('b.year', $year)
            ->whereNotNull($column);

        if ($term !== '') {
            $query->where($column, 'like', '%'.$term.'%');
        }

        $values = $query->distinct()->orderBy($column)->limit(50)->pluck($column);

        return response()->json(['results' => $values->map(fn ($v) => ['id' => $v, 'text' => $v])]);
    }

    public function productStockData(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $query = $this->applyProductStockFilters($this->productStockBaseQuery($cpnyid, $year, $month), $request);

        return \DataTables::of($query)
            // Same Postgres alias-in-WHERE restriction as inOutData() — filterColumn()
            // re-targets the real expression instead of the SELECT alias.
            ->filterColumn('expired_date_fmt', function ($q, $keyword) {
                $q->whereRaw("COALESCE(to_char(b.expired_date, 'YYYY-MM-DD'), 'No Expired') LIKE ?", ['%'.$keyword.'%']);
            })
            ->editColumn('product_value', fn ($r) => number_format((float) $r->product_value, 2, '.', ''))
            ->editColumn('stock', fn ($r) => number_format((float) $r->stock, 0, ',', '.'))
            ->make(true);
    }

    public function productStockExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->applyProductStockFilters($this->productStockBaseQuery($cpnyid, $year, $month), $request)
            ->orderBy('b.product_id')
            ->get()
            ->map(fn ($r) => [
                $r->cpnyid,
                $r->product_id,
                $r->expired_date_fmt,
                $r->product_name,
                (float) $r->product_value,
                $r->product_uom,
                $r->whs_id,
                (float) $r->stock,
            ]);

        $filename = "product-stock-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplProductStockExport($rows), $filename);
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
        // Universe of tenant+expiry batches: normally everything is received at
        // WHCOLLECTION first and moves out from there, but a migration/opening-balance
        // receive can post directly into WHLOYALTY/WHPROMOTION (see VPR26090002) — pull
        // balances from all 3 tracked warehouses so a batch that only ever existed
        // outside WHCOLLECTION still shows up, instead of being silently dropped.
        $balances = MsVplProductBal::where('cpnyid', $cpnyid)
            ->where('year', $year)
            ->whereIn('whs_id', [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION])
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

            // A batch can carry a begqty row at more than one of the 3 warehouses (e.g.
            // WHCOLLECTION *and* a direct-to-WHLOYALTY receive for the same expiry) —
            // sum begqty across every warehouse row seen for this key rather than
            // overwriting, so opening balance isn't lost to whichever row is read last.
            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'product'        => $product,
                    'bal'            => $bal,
                    'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                    'beginning'      => 0.0,
                ];
            }

            $rows[$key]['beginning'] += (float) $bal->begqty;
        }

        foreach ($rows as $key => &$row) {
            $in  = $monthlyIn[$key] ?? [];
            $out = $monthlyOutAmt[$key] ?? [];

            for ($m = 1; $m < $month; $m++) {
                $row['beginning'] += ($in[$m] ?? 0) - ($out[$m] ?? 0);
            }

            $monthIn  = $in[$month] ?? 0;
            $monthOut = $out[$month] ?? 0;

            $row['month_in']  = $monthIn;
            $row['month_out'] = $monthOut;
            $row['ending']    = $row['beginning'] + $monthIn - $monthOut;
        }
        unset($row);

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT REPORT (one row per product+expiry batch, Begin/In/Out/Ending
    | + who requested the Out — "Laporan Stok Product")
    |--------------------------------------------------------------------------
    */
    private function productReportJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $groups = $this->buildProductReport($cpnyid, $year, $month);

        return view('pages.report-vpl.partials.product-report-table', [
            'groups' => $groups,
            'cpnyid' => $cpnyid,
            'year'   => $year,
            'month'  => $month,
        ]);
    }

    private function productReportExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $groups = $this->buildProductReport($cpnyid, $year, $month);

        $filename = "product-report-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplProductReportExport($groups, $cpnyid, $year, $month), $filename);
    }

    private function buildProductReport(string $cpnyid, int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        // Beginning/In/Out/Ending per product+expiry batch, summed across whichever
        // warehouse(s) it's held in.
        $batchRows = $this->productBatchRows($cpnyid, $year, $month);

        if (empty($batchRows)) {
            return [];
        }

        $firstReceive = $this->batchFirstReceiveDate($cpnyid);
        $inRows       = $this->productInMovementRows($cpnyid, $monthStart, $monthEnd);
        $outRows      = $this->productOutMovementRows($cpnyid, $monthStart, $monthEnd);

        $groups = [];

        foreach ($batchRows as $key => $row) {
            $product = $row['product'];
            $bal     = $row['bal'];
            $price   = (float) $product->product_value;

            // Same detail-row shape as the Stock Voucher report (buildStockVoucherReport()):
            // one row per In/Out document, so who-requested-it stays traceable per document
            // instead of getting collapsed into one "; "-joined batch summary.
            $rows = array_merge($inRows[$key] ?? [], $outRows[$key] ?? []);

            usort($rows, fn ($a, $b) => $a['date'] <=> $b['date']);

            $groups[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'perusahaan'     => $product->product_source_company ?: '-',
                'photo_url'      => $this->photoSignedUrl($product->product_photo),
                'category_label' => $row['category_label'],
                'expired_date'   => $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date,
                'nominal'        => $price,
                'beginning'      => $row['beginning'],
                'ending'         => $row['ending'],
                'total_nominal'  => $row['beginning'] * $price,
                'tgl_terima'     => $firstReceive[$key] ?? null,
                'price_out'      => $row['month_out'] * $price,
                'rows'           => $rows,
            ];
        }

        usort($groups, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['expired_date']]
                <=> [$b['category_label'], $b['tenant'], $b['expired_date']];
        });

        // attachRowspans() is generic over product_id/category_label/rows — the same
        // helper the Stock Voucher report uses.
        return $this->attachRowspans($groups);
    }

    /**
     * Beginning/In/Out/Ending per Product-type (product_type='P') batch, summed
     * across every warehouse it's held in — unlike Vouchers, physical Products
     * don't all funnel through WHCOLLECTION first; a department can receive
     * straight into its own warehouse (e.g. WHPROMOTION). Reads straight off
     * ms_vpl_product_bal's own period01..12 in/out columns (same source
     * productStockBaseQuery() sums) rather than batchStockRows()'s ledger-derived
     * override, which only matters for the Collection->Loyalty voucher transfer flow.
     *
     * @return array<string, array{product: MsVplProduct, bal: MsVplProductBal, category_label: string, beginning: float, month_in: float, month_out: float, ending: float}>
     */
    private function productBatchRows(string $cpnyid, int $year, int $month): array
    {
        $products = MsVplProduct::where('cpnyid', $cpnyid)
            ->where('product_type', 'P')
            ->get()
            ->keyBy('product_id');

        if ($products->isEmpty()) {
            return [];
        }

        $balances = MsVplProductBal::where('cpnyid', $cpnyid)
            ->where('year', $year)
            ->whereIn('product_id', $products->keys())
            ->get();

        $mm = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $rows = [];

        foreach ($balances as $bal) {
            $product = $products->get($bal->product_id);

            if (!$product) {
                continue;
            }

            $key = $bal->product_id.'|'.$this->expiredKey($bal->expired_date);

            $beginning = (float) $bal->begqty;

            for ($m = 1; $m < $month; $m++) {
                $pm = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $beginning += (float) ($bal->{"period{$pm}in"} ?? 0) - (float) ($bal->{"period{$pm}out"} ?? 0);
            }

            $monthIn  = (float) ($bal->{"period{$mm}in"} ?? 0);
            $monthOut = (float) ($bal->{"period{$mm}out"} ?? 0);
            $ending   = $beginning + $monthIn - $monthOut;

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'product'        => $product,
                    'bal'            => $bal,
                    'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                    'beginning'      => 0.0,
                    'month_in'       => 0.0,
                    'month_out'      => 0.0,
                    'ending'         => 0.0,
                ];
            }

            $rows[$key]['beginning'] += $beginning;
            $rows[$key]['month_in']  += $monthIn;
            $rows[$key]['month_out'] += $monthOut;
            $rows[$key]['ending']    += $ending;
        }

        return $rows;
    }

    /** Earliest completed Receive date per product+expiry batch, any warehouse — the "Tgl Terima" this batch first entered stock. */
    private function batchFirstReceiveDate(string $cpnyid): array
    {
        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive.receive_date',
            ])
            ->orderBy('tr_vpl_receive.receive_date')
            ->get();

        $firstReceive = [];

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);

            if (!isset($firstReceive[$key])) {
                $firstReceive[$key] = Carbon::parse($r->receive_date);
            }
        }

        return $firstReceive;
    }

    /** Receive lines (any warehouse) for Product-type items in the given month — the "In" side, one row per document. */
    private function productInMovementRows(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rows = [];

        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->join('ms_vpl_product', 'ms_vpl_product.product_id', '=', 'tr_vpl_receive_detail.product_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->where('ms_vpl_product.product_type', 'P')
            ->whereBetween('tr_vpl_receive.receive_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive_detail.qty_receive as qty',
                'tr_vpl_receive.receive_date as doc_date',
                'tr_vpl_receive.receive_id as doc_no',
                'tr_vpl_receive.source_receive_dept as source_dept',
            ])
            ->get();

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);
            $rows[$key][] = [
                'direction'  => 'in',
                'doc_label'  => 'Receive',
                'doc_no'     => $r->doc_no,
                'date'       => Carbon::parse($r->doc_date),
                'qty'        => (float) $r->qty,
                'name'       => null,
                'department' => $r->source_dept,
                'remark'     => null,
            ];
        }

        return $rows;
    }

    /** Usage lines (any warehouse) for Product-type items in the given month — the "Out" side; Name/Department/Remark come straight off the Usage document that took the stock. */
    private function productOutMovementRows(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rows = [];

        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->join('ms_vpl_product', 'ms_vpl_product.product_id', '=', 'tr_vpl_usage_detail.product_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->where('ms_vpl_product.product_type', 'P')
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
                'tr_vpl_usage_detail.purpose_remark',
                'tr_vpl_usage.usagetype',
                'tr_vpl_usage.usage_date as doc_date',
                'tr_vpl_usage.usage_id as doc_no',
                'tr_vpl_usage.created_user as requester_name',
                'tr_vpl_usage.department',
            ])
            ->get();

        foreach ($usages as $u) {
            $key      = $u->product_id.'|'.$this->expiredKey($u->expired_date);
            $isReturn = $u->usagetype === 'Return';

            $rows[$key][] = [
                'direction'  => $isReturn ? 'in' : 'out',
                'doc_label'  => $isReturn ? 'Return' : 'Usage',
                'doc_no'     => $u->doc_no,
                'date'       => Carbon::parse($u->doc_date),
                'qty'        => $isReturn ? abs((float) $u->qty_return_usage) : abs((float) $u->qty_usage),
                'name'       => $u->requester_name,
                'department' => $u->department,
                'remark'     => $u->purpose_remark,
            ];
        }

        return $rows;
    }

    /** Same signed-URL pattern as VplMsProductController::photoSignedUrl() — short-lived read URL for a GCS product photo. */
    private function photoSignedUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            $config  = config('filesystems.disks.gcs');
            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);

            return $storage->bucket($config['bucket'])->object($path)->signedUrl(
                new \DateTimeImmutable('+10 minutes'),
                ['version' => 'v4']
            );
        } catch (\Throwable $e) {
            \Log::warning('VPL Product photo signed URL failed', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TRIAL BALANCE SUMMARY GROUP (Begin/In/Transfer/Out-by-purpose/End per
    | product+expiry batch, split by the department that made each movement —
    | ports the legacy voucher_das "Trial Balance Summary Group" report
    | (resources/views/voucher_das/Views/vplledger/trialbalancesummarygroup.blade.php)
    | onto the new ms_vpl_/tr_vpl_ schema. That legacy report read from a DB
    | view (view_vpl_trial_balance_summary_group) which does not actually exist
    | on the live database (confirmed via SHOW FULL TABLES) — there is no working
    | reference implementation, only the blade's column headers, so the
    | Begin/In/Transfer/Out semantics below are inferred, not copied.
    |--------------------------------------------------------------------------
    */
    private function summaryGroupJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);
        $whsId = $this->resolveSummaryGroupWhsId($request);

        $groups = $this->buildSummaryGroupReport($cpnyid, $year, $month, $whsId);

        return view('pages.report-vpl.partials.summary-group-table', [
            'groups'      => $groups,
            'purposeCols' => $this->summaryGroupPurposeColumns(),
            'cpnyid'      => $cpnyid,
            'year'        => $year,
            'month'       => $month,
        ]);
    }

    private function summaryGroupExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);
        $whsId = $this->resolveSummaryGroupWhsId($request);

        $groups = $this->buildSummaryGroupReport($cpnyid, $year, $month, $whsId);

        $filename = "summary-group-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(
            new VplTrialBalanceSummaryGroupExport($groups, $this->summaryGroupPurposeColumns(), $cpnyid, $year, $month),
            $filename
        );
    }

    /** Optional WhsOwner filter — only the three warehouses this module actually uses. */
    private function resolveSummaryGroupWhsId(Request $request): ?string
    {
        $whsId = $request->input('whs_id');

        return in_array($whsId, [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION], true) ? $whsId : null;
    }

    /** Out columns: one per raw purpose_id already tracked by PURPOSE_MAP, plus a catch-all for anything unmapped — granular, not collapsed into the 4-bucket Loyalty/Promotion/Entertainment/Internal Use used elsewhere. */
    private function summaryGroupPurposeColumns(): array
    {
        return array_merge(array_keys(self::PURPOSE_MAP), ['Other']);
    }

    private function buildSummaryGroupReport(string $cpnyid, int $year, int $month, ?string $whsId = null): array
    {
        [$batchMeta, $inByMonth, $transferByMonth, $outByMonth] = $this->summaryGroupMovementRows($cpnyid, $year);

        if ($whsId !== null) {
            $batchMeta = array_filter($batchMeta, fn ($meta) => $meta['whs_id'] === $whsId);
        }

        if (empty($batchMeta)) {
            return [];
        }

        $products    = MsVplProduct::where('cpnyid', $cpnyid)->get()->keyBy('product_id');
        $purposeCols = $this->summaryGroupPurposeColumns();
        $zeroOut     = array_fill_keys($purposeCols, 0.0);

        $rows = [];

        foreach ($batchMeta as $key => $meta) {
            $product = $products->get($meta['product_id']);

            if (!$product) {
                continue;
            }

            // $transferByMonth is already signed (negative = this warehouse sent stock
            // out, positive = this warehouse received stock in), so it's added, not
            // subtracted, when rolling the balance forward.
            $beginning = 0.0;
            for ($m = 1; $m < $month; $m++) {
                $beginning += ($inByMonth[$key][$m] ?? 0) + ($transferByMonth[$key][$m] ?? 0) - array_sum($outByMonth[$key][$m] ?? []);
            }

            $monthIn       = $inByMonth[$key][$month] ?? 0.0;
            $monthTransfer = $transferByMonth[$key][$month] ?? 0.0;
            $monthOut      = array_merge($zeroOut, $outByMonth[$key][$month] ?? []);
            $monthOutTotal = array_sum($monthOut);
            $ending        = $beginning + $monthIn + $monthTransfer - $monthOutTotal;

            $rows[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                'whs_id'         => $meta['whs_id'],
                'expired_date'   => $this->expiredKey($meta['expired_date']) === 'NULL' ? null : $meta['expired_date'],
                'beginning'      => $beginning,
                'in_total'       => $monthIn,
                'transfer'       => $monthTransfer,
                'out'            => $monthOut,
                'out_total'      => $monthOutTotal,
                'ending'         => $ending,
            ];
        }

        usort($rows, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['expired_date'], $a['whs_id']]
                <=> [$b['category_label'], $b['tenant'], $b['expired_date'], $b['whs_id']];
        });

        return $this->attachSummaryGroupCategoryHeaders($rows);
    }

    /**
     * Raw Begin/In/Transfer/Out movement figures for every (product+expiry+warehouse)
     * batch touched anywhere in the given year, bucketed by calendar month so
     * buildSummaryGroupReport() can sum months 1..selected-month-1 for Beginning and
     * read the selected month directly for In/Transfer/Out. "WhsOwner" is the warehouse
     * itself (whs_id — WHCOLLECTION/WHLOYALTY/WHPROMOTION), each movement attributed to
     * whichever warehouse it actually happened at — not a department.
     *
     * In    = Receive (at whichever warehouse the receive detail targets — normally
     *         WHCOLLECTION) + Return Usage (qty_return_usage), attributed to the
     *         warehouse the usage was returned at. Return Usage reverses a prior
     *         Usage-Out, so it's added back rather than netted against the Out-purpose
     *         bucket it came from (same convention batchOutBreakdown()/inMovementRows()
     *         already use elsewhere in this controller).
     * Transfer = both legs of a WHCOLLECTION<->WHLOYALTY Transfer/ReturnTf are posted:
     *         the source warehouse's row gets a negative entry, the destination
     *         warehouse's row gets a positive entry, so each warehouse's own balance
     *         stays self-consistent.
     * Out   = Usage (usagetype != 'Return') at WHPROMOTION/WHLOYALTY, attributed to the
     *         warehouse it was used at and bucketed by its own purpose_id (falling back
     *         to 'Other' for anything not in PURPOSE_MAP).
     *
     * @return array{0: array<string, array{product_id: string, expired_date: mixed, whs_id: string}>, 1: array<string, array<int, float>>, 2: array<string, array<int, float>>, 3: array<string, array<int, array<string, float>>>}
     */
    private function summaryGroupMovementRows(string $cpnyid, int $year): array
    {
        $batchMeta       = [];
        $inByMonth       = [];
        $transferByMonth = [];
        $outByMonth      = [];

        $touch = function (string $key, string $productId, $expiredDate, string $whsId) use (&$batchMeta) {
            if (!isset($batchMeta[$key])) {
                $batchMeta[$key] = ['product_id' => $productId, 'expired_date' => $expiredDate, 'whs_id' => $whsId];
            }
        };

        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->whereYear('tr_vpl_receive.receive_date', $year)
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive_detail.qty_receive',
                'tr_vpl_receive_detail.whs_id',
                'tr_vpl_receive.receive_date',
            ])
            ->get();

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date).'|'.$r->whs_id;
            $touch($key, $r->product_id, $r->expired_date, $r->whs_id);
            $m = Carbon::parse($r->receive_date)->month;
            $inByMonth[$key][$m] = ($inByMonth[$key][$m] ?? 0) + (float) $r->qty_receive;
        }

        // A transfer has two legs (from_whs_id/to_whs_id), each attributed to its own
        // warehouse's row: the source warehouse gets a negative entry (stock leaving),
        // the destination warehouse gets a positive entry (stock arriving). Both legs
        // are posted for every completed Transfer/ReturnTf regardless of which two
        // warehouses are involved (WHCOLLECTION<->WHLOYALTY and WHCOLLECTION<->WHPROMOTION
        // both occur live) — unlike the other reports in this controller, which only
        // track the WHCOLLECTION<->WHLOYALTY leg because they roll everything up to a
        // single WHCOLLECTION-scoped balance, this report needs every warehouse's own
        // balance to be self-consistent, so a Collection->Promotion transfer can't be
        // left out of scope the way it is elsewhere.
        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->whereIn('tr_vpl_transfer.transfertype', ['Transfer', 'ReturnTf'])
            ->whereYear('tr_vpl_transfer.transfer_date', $year)
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer',
                'tr_vpl_transfer_detail.from_whs_id',
                'tr_vpl_transfer_detail.to_whs_id',
                'tr_vpl_transfer.transfer_date',
            ])
            ->get();

        foreach ($transfers as $t) {
            $expiredKey = $this->expiredKey($t->expired_date);
            $m          = Carbon::parse($t->transfer_date)->month;
            $qty        = abs((float) $t->qty_transfer);

            $fromKey = $t->product_id.'|'.$expiredKey.'|'.$t->from_whs_id;
            $toKey   = $t->product_id.'|'.$expiredKey.'|'.$t->to_whs_id;
            $touch($fromKey, $t->product_id, $t->expired_date, $t->from_whs_id);
            $touch($toKey, $t->product_id, $t->expired_date, $t->to_whs_id);

            $transferByMonth[$fromKey][$m] = ($transferByMonth[$fromKey][$m] ?? 0) - $qty;
            $transferByMonth[$toKey][$m]   = ($transferByMonth[$toKey][$m] ?? 0) + $qty;
        }

        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->whereIn('tr_vpl_usage_detail.whs_id', [self::WHS_PROMOTION, self::WHS_LOYALTY])
            ->whereYear('tr_vpl_usage.usage_date', $year)
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.whs_id',
                'tr_vpl_usage_detail.purpose_id',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
                'tr_vpl_usage.usagetype',
                'tr_vpl_usage.usage_date',
            ])
            ->get();

        foreach ($usages as $u) {
            $key = $u->product_id.'|'.$this->expiredKey($u->expired_date).'|'.$u->whs_id;
            $touch($key, $u->product_id, $u->expired_date, $u->whs_id);
            $m = Carbon::parse($u->usage_date)->month;

            if ($u->usagetype === 'Return') {
                $inByMonth[$key][$m] = ($inByMonth[$key][$m] ?? 0) + abs((float) $u->qty_return_usage);
                continue;
            }

            $bucket = array_key_exists($u->purpose_id, self::PURPOSE_MAP) ? $u->purpose_id : 'Other';
            $outByMonth[$key][$m][$bucket] = ($outByMonth[$key][$m][$bucket] ?? 0) + (float) $u->qty_usage;
        }

        return [$batchMeta, $inByMonth, $transferByMonth, $outByMonth];
    }

    /** Turn the flat, sorted rows into category-header / detail render rows — flat, no tenant subtotal, matching the legacy report's plain-DataTable shape. */
    private function attachSummaryGroupCategoryHeaders(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $output = [];

        foreach (collect($rows)->groupBy('category_label') as $categoryLabel => $categoryRows) {
            $output[] = ['type' => 'category_header', 'category_label' => $categoryLabel];

            foreach ($categoryRows as $r) {
                $r['type'] = 'detail';
                $output[]  = $r;
            }
        }

        return $output;
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

        $sources      = $this->batchSourceTypes($cpnyid);
        $outBreakdown = $this->batchOutBreakdown($cpnyid, $monthStart, $monthEnd);
        $purposeOut   = $this->batchPurposeOut($cpnyid, $monthStart, $monthEnd);
        $agingBuckets = MsVplAging::where('status', 'A')->orderBy('order_age')->get();

        $rows = [];

        foreach ($batchRows as $key => $row) {
            $product     = $row['product'];
            $bal         = $row['bal'];
            $receiveType = $sources[$key] ?? null;
            $sourceMeta  = self::SOURCE_MAP[$receiveType] ?? null;
            $nominal     = (float) $product->product_value;
            $expiredDate = $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date;

            // Stock roll-forward Out columns — same WHCOLLECTION-balance convention as the
            // Stock Voucher report: Transfer to Loyalty and Usage at Promotion. Separate from
            // "Voucher Used in Current Period" below, which is an actual-redemption metric.
            $out = array_fill_keys(self::USED_COLUMNS, 0.0);
            foreach ($outBreakdown[$key] ?? [] as $bucket => $qty) {
                $out[$bucket] += $qty;
            }

            // Actual redemption (net of returns... now gross, see batchPurposeOut()) at
            // WHPROMOTION or WHLOYALTY — drives "Voucher Used in Current Period" (value) and
            // is shared with the Loyalty Usage Rate report.
            $used = array_fill_keys(self::USED_COLUMNS, 0.0);
            foreach ($purposeOut[$key] ?? [] as $bucket => $qty) {
                $used[$bucket] += $qty;
            }

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
                'out_loyalty'    => $out['Loyalty'],
                'out_promotion'  => $out['Promotion'],
                'out_entertain'  => $out['Entertainment'],
                'out_internal'   => $out['Internal Use'],
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
     * any of the 3 tracked warehouses (see batchStockRows()). If a batch was topped up
     * under more than one source over time, the most recent receive's type wins (edge
     * case — batches are expected to share one source).
     *
     * @return array<string, string>
     */
    private function batchSourceTypes(string $cpnyid): array
    {
        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->whereIn('tr_vpl_receive_detail.whs_id', [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION])
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
     * Gross usage qty in the given month, per product+expiry batch and "Voucher Used"
     * bucket — an actual-redemption metric, independent of the stock roll-forward's Out
     * Loy (which tracks the WHCOLLECTION->WHLOYALTY transfer itself, not whether that
     * stock has actually been redeemed yet — see batchOutBreakdown()). Usage at
     * WHLOYALTY (recorded by the CUSTOMERSERVICE department) always buckets as Loyalty;
     * usage at WHPROMOTION buckets via PURPOSE_MAP. Return Usage is NOT netted out here
     * — a return reverses stock that already left (it isn't new stock).
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
            $qty    = (float) $u->qty_usage;

            $out[$key][$bucket] = ($out[$key][$bucket] ?? 0) + $qty;
        }

        return $out;
    }

    /**
     * Stock roll-forward Out breakdown for the Stock & Aging Summary report, using the
     * exact same WHCOLLECTION-balance convention as the Stock Voucher report (report 1):
     * a Transfer to WHLOYALTY counts as Out the moment it's transferred (bucketed as
     * "Loyalty"), regardless of whether it's actually been redeemed there yet; Usage at
     * WHPROMOTION counts as Out, bucketed via PURPOSE_MAP. A WHCOLLECTION<->WHPROMOTION
     * transfer is out of scope here too (mirrors outMovementRows()/ledgerMonthlyInOut()),
     * so Promotion's Out is usage only. Gross qty_usage — Return Usage is not netted out;
     * it's surfaced as In instead (see ledgerMonthlyInOut()).
     *
     * @return array<string, array<string, float>>
     */
    private function batchOutBreakdown(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $out = [];

        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->where('tr_vpl_transfer.transfertype', 'Transfer')
            ->where('tr_vpl_transfer_detail.from_whs_id', self::WHS_COLLECTION)
            ->where('tr_vpl_transfer_detail.to_whs_id', self::WHS_LOYALTY)
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer',
            ])
            ->get();

        foreach ($transfers as $t) {
            $key = $t->product_id.'|'.$this->expiredKey($t->expired_date);
            $out[$key]['Loyalty'] = ($out[$key]['Loyalty'] ?? 0) + abs((float) $t->qty_transfer);
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
                'tr_vpl_usage_detail.purpose_id',
                'tr_vpl_usage_detail.qty_usage',
            ])
            ->get();

        foreach ($usages as $u) {
            $key    = $u->product_id.'|'.$this->expiredKey($u->expired_date);
            $bucket = self::PURPOSE_MAP[$u->purpose_id] ?? 'Internal Use';
            $out[$key][$bucket] = ($out[$key][$bucket] ?? 0) + (float) $u->qty_usage;
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

    /*
    |--------------------------------------------------------------------------
    | LOYALTY USAGE REPORT (usage at WHLOYALTY / ending stock at WHLOYALTY)
    |--------------------------------------------------------------------------
    */
    private function loyaltyUsageJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->buildLoyaltyUsageReport($cpnyid, $year, $month);

        return view('pages.report-vpl.partials.loyalty-usage-table', [
            'rows'      => $rows,
            'cpnyid'    => $cpnyid,
            'year'      => $year,
            'month'     => $month,
            'forExport' => false,
        ]);
    }

    private function loyaltyUsageExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);

        $rows = $this->buildLoyaltyUsageReport($cpnyid, $year, $month);

        $filename = "loyalty-usage-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplLoyaltyUsageExport($rows, $cpnyid, $year, $month), $filename);
    }

    /**
     * Per-tenant Usage Rate at WHLOYALTY = usage qty this month / ending stock this
     * month, both scoped to the WHLOYALTY warehouse. Ending stock is rolled forward
     * straight off ms_vpl_product_bal's own period columns (the same table
     * postStockBalanceIn()/sp_process_vpl write to for WHLOYALTY), no ledger lookup
     * needed. Usage qty reuses batchPurposeOut()'s 'Loyalty' bucket (qty_usage minus
     * qty_return_usage recorded at WHLOYALTY), the same number already shown as
     * "Loyalty" under Stock & Aging Summary's Voucher Used columns.
     */
    private function buildLoyaltyUsageReport(string $cpnyid, int $year, int $month): array
    {
        $balances = MsVplProductBal::where('cpnyid', $cpnyid)
            ->where('year', $year)
            ->where('whs_id', self::WHS_LOYALTY)
            ->get();

        if ($balances->isEmpty()) {
            return [];
        }

        $products = MsVplProduct::where('cpnyid', $cpnyid)->get()->keyBy('product_id');

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $usageOut  = $this->batchPurposeOut($cpnyid, $monthStart, $monthEnd);
        $usageDocs = $this->batchLoyaltyUsageDocs($cpnyid, $monthStart, $monthEnd);

        $mm = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $batchRows = [];

        foreach ($balances as $bal) {
            $product = $products->get($bal->product_id);

            if (!$product) {
                continue;
            }

            $beginning = (float) $bal->begqty;

            for ($m = 1; $m < $month; $m++) {
                $pm = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $beginning += (float) ($bal->{"period{$pm}in"} ?? 0) - (float) ($bal->{"period{$pm}out"} ?? 0);
            }

            $monthIn  = (float) ($bal->{"period{$mm}in"} ?? 0);
            $monthOut = (float) ($bal->{"period{$mm}out"} ?? 0);
            $ending   = $beginning + $monthIn - $monthOut;

            $key      = $bal->product_id.'|'.$this->expiredKey($bal->expired_date);
            $usageQty = $usageOut[$key]['Loyalty'] ?? 0.0;

            $batchRows[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                'expired_date'   => $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date,
                'ending_stock'   => $ending,
                'usage_qty'      => $usageQty,
                'usage_rate'     => $ending > 0 ? $usageQty / $ending : null,
            ];
        }

        if (empty($batchRows)) {
            return [];
        }

        usort($batchRows, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['expired_date']]
                <=> [$b['category_label'], $b['tenant'], $b['expired_date']];
        });

        return $this->attachLoyaltyUsageGrouping($batchRows, $usageDocs);
    }

    /**
     * Individual usage documents behind a product's usage_qty at WHLOYALTY this
     * month — one entry per usage doc (lines for the same product on the same doc
     * are summed), so the report's Action button can list "which DOCID(s) made up
     * this number" and link straight to each one (showusagevp, same route the
     * usagevp list's own view-record links use).
     *
     * @return array<string, array<int, array{id: int, usage_id: string, usage_date: ?string, qty: float, link: string}>>
     */
    private function batchLoyaltyUsageDocs(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->where('tr_vpl_usage_detail.whs_id', self::WHS_LOYALTY)
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage.id',
                'tr_vpl_usage.usage_id',
                'tr_vpl_usage.usage_date',
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage_detail.qty_return_usage',
            ])
            ->get();

        $byProductDoc = [];

        foreach ($usages as $u) {
            $qty = (float) $u->qty_usage - (float) $u->qty_return_usage;

            if (!isset($byProductDoc[$u->product_id][$u->usage_id])) {
                $byProductDoc[$u->product_id][$u->usage_id] = [
                    'id'         => $u->id,
                    'usage_id'   => $u->usage_id,
                    'usage_date' => $u->usage_date ? Carbon::parse($u->usage_date)->format('Y-m-d') : null,
                    'qty'        => 0.0,
                    'link'       => route('showusagevp', Hashids::encode($u->id)),
                ];
            }

            $byProductDoc[$u->product_id][$u->usage_id]['qty'] += $qty;
        }

        return array_map(fn ($docs) => array_values($docs), $byProductDoc);
    }

    /**
     * Turn the flat, sorted per-batch rows into category-header / per-expiry-batch
     * detail render rows — no tenant subtotal row; each batch stands on its own,
     * with the grand total left to the table footer. The Action (related DOCIDs)
     * button lives on every detail row since batchLoyaltyUsageDocs() doesn't split
     * by expiry batch, only by product — batches of the same product share the
     * same doc list.
     */
    private function attachLoyaltyUsageGrouping(array $rows, array $usageDocs): array
    {
        if (empty($rows)) {
            return [];
        }

        $output = [];

        foreach (collect($rows)->groupBy('category_label') as $categoryLabel => $categoryRows) {
            $output[] = ['type' => 'category_header', 'category_label' => $categoryLabel];

            foreach ($categoryRows as $r) {
                $r['type'] = 'detail';
                $r['docs'] = $usageDocs[$r['product_id']] ?? [];
                $output[]  = $r;
            }
        }

        return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK OUT VOUCHER REPORT ("Laporan Stok Out Voucher") — Begin/In/Out/Retur/
    | Ending + Nominal Out/Purpose/Remarks per tenant, scoped to one warehouse
    | picked via a Promotion/Loyalty selector (one tab, matching the business
    | team's existing manual Excel template).
    |--------------------------------------------------------------------------
    */
    private function stockOutVoucherJson(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);
        $whsId = $this->resolveStockOutWhsId($request);

        $rows = $this->buildStockOutVoucherReport($cpnyid, $year, $month, $whsId);

        return view('pages.report-vpl.partials.stock-out-voucher-table', [
            'rows'      => $rows,
            'cpnyid'    => $cpnyid,
            'year'      => $year,
            'month'     => $month,
            'whsLabel'  => $this->stockOutWhsLabel($whsId),
            'forExport' => false,
        ]);
    }

    private function stockOutVoucherExport(Request $request)
    {
        [$cpnyid, $year, $month] = $this->resolveStockVoucherParams($request);
        $whsId    = $this->resolveStockOutWhsId($request);
        $whsLabel = $this->stockOutWhsLabel($whsId);

        $rows = $this->buildStockOutVoucherReport($cpnyid, $year, $month, $whsId);

        $slug     = strtolower($whsLabel);
        $filename = "stock-out-{$slug}-{$cpnyid}-{$year}-".str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.xlsx';

        return Excel::download(new VplStockOutVoucherExport($rows, $cpnyid, $year, $month, $whsLabel), $filename);
    }

    /** Warehouse selector — only WHPROMOTION/WHLOYALTY are valid; defaults to whichever the user actually has access to (Promotion preferred if both). */
    private function resolveStockOutWhsId(Request $request): string
    {
        $whsId = $request->input('whs_id');

        if (in_array($whsId, [self::WHS_PROMOTION, self::WHS_LOYALTY], true)) {
            return $whsId;
        }

        $user = Auth::user();

        return ($user && $user->hasRole('VPPRMTNACCESS')) ? self::WHS_PROMOTION : self::WHS_LOYALTY;
    }

    private function stockOutWhsLabel(string $whsId): string
    {
        return $whsId === self::WHS_PROMOTION ? 'Promotion' : 'Loyalty';
    }

    /**
     * Per-tenant Begin/In/Out/Retur/Ending for the given warehouse (WHPROMOTION or
     * WHLOYALTY), rolled forward straight off ms_vpl_product_bal — same authoritative
     * source buildLoyaltyUsageReport() already uses, so Begin/Ending/gross-Out here can
     * never drift from what the SP actually posted. The one wrinkle: sp_process_vpl
     * posts Return Usage as a POSITIVE entry into periodNNin at the SAME warehouse
     * (mirroring a forward Transfer-in), so ms_vpl_product_bal's own period{mm}in column
     * is Transfer-in/Receive-in and Return-in combined. The screenshot template wants
     * Retur shown as its own column, so it's isolated by subtracting a separately
     * queried Return-qty from that combined column — In(displayed) = period_in - Retur.
     * period_out is untouched by Return (Return never posts to period_out), so gross
     * Out is read straight off it with no adjustment needed.
     */
    private function buildStockOutVoucherReport(string $cpnyid, int $year, int $month, string $whsId): array
    {
        $balances = MsVplProductBal::where('cpnyid', $cpnyid)
            ->where('year', $year)
            ->where('whs_id', $whsId)
            ->get();

        if ($balances->isEmpty()) {
            return [];
        }

        // Voucher-type only — "Laporan Stok Out Voucher" is scoped to vouchers, and
        // Product-type items (product_type='P') can transfer WHPROMOTION<->WHLOYALTY
        // directly, a topology this report's Begin/In/Out/Retur columns don't model
        // (only vouchers' WHCOLLECTION-centric flow, plus a ReturnTf back to
        // WHCOLLECTION, does).
        $products = MsVplProduct::where('cpnyid', $cpnyid)
            ->where('product_type', '<>', 'P')
            ->get()
            ->keyBy('product_id');

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        [$returnByBatch, $purposeByBatch, $remarksByBatch] = $this->stockOutVoucherUsageDetail($cpnyid, $monthStart, $monthEnd, $whsId);
        [$inDocsByBatch, $outDocsByBatch] = $this->stockOutVoucherDocs($cpnyid, $monthStart, $monthEnd, $whsId);

        $mm = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $rows = [];

        foreach ($balances as $bal) {
            $product = $products->get($bal->product_id);

            if (!$product) {
                continue;
            }

            $beginning = (float) $bal->begqty;

            for ($m = 1; $m < $month; $m++) {
                $pm = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $beginning += (float) ($bal->{"period{$pm}in"} ?? 0) - (float) ($bal->{"period{$pm}out"} ?? 0);
            }

            $key = $bal->product_id.'|'.$this->expiredKey($bal->expired_date);

            $retur      = $returnByBatch[$key] ?? 0.0;
            $monthInRaw = (float) ($bal->{"period{$mm}in"} ?? 0);
            $monthOut   = (float) ($bal->{"period{$mm}out"} ?? 0);
            $monthIn    = $monthInRaw - $retur;
            $ending     = $beginning + $monthInRaw - $monthOut;
            $nominal    = (float) $product->product_value;

            $rows[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $product->product_category === 'F&B' ? 'F&B' : 'NON F&B',
                'nominal'        => $nominal,
                'expired_date'   => $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date,
                'beginning'      => $beginning,
                'in_total'       => $monthIn,
                'out_total'      => $monthOut,
                'retur'          => $retur,
                'ending'         => $ending,
                'nominal_out'    => $monthOut * $nominal,
                'purpose'        => $purposeByBatch[$key] ?? [],
                'remarks'        => $remarksByBatch[$key] ?? [],
                'in_docs'        => $inDocsByBatch[$key] ?? [],
                'out_docs'       => $outDocsByBatch[$key] ?? [],
            ];
        }

        if (empty($rows)) {
            return [];
        }

        usort($rows, function ($a, $b) {
            return [$a['category_label'], $a['tenant'], $a['expired_date']]
                <=> [$b['category_label'], $b['tenant'], $b['expired_date']];
        });

        return $this->attachStockOutVoucherGrouping($rows);
    }

    /**
     * Return-usage qty (own column), and distinct purpose_id/purpose_remark text
     * (comma-joined in the view) actually recorded against gross Usage documents this
     * month, per product+expiry batch — all three scoped to one warehouse.
     *
     * @return array{0: array<string, float>, 1: array<string, array<int, string>>, 2: array<string, array<int, string>>}
     */
    private function stockOutVoucherUsageDetail(string $cpnyid, Carbon $monthStart, Carbon $monthEnd, string $whsId): array
    {
        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->where('tr_vpl_usage_detail.whs_id', $whsId)
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.purpose_id',
                'tr_vpl_usage_detail.purpose_remark',
                'tr_vpl_usage_detail.qty_return_usage',
                'tr_vpl_usage.usagetype',
            ])
            ->get();

        $returnByBatch  = [];
        $purposeByBatch = [];
        $remarksByBatch = [];

        foreach ($usages as $u) {
            $key = $u->product_id.'|'.$this->expiredKey($u->expired_date);

            if ($u->usagetype === 'Return') {
                $returnByBatch[$key] = ($returnByBatch[$key] ?? 0) + abs((float) $u->qty_return_usage);
                continue;
            }

            if (!empty($u->purpose_id)) {
                $purposeByBatch[$key][$u->purpose_id] = true;
            }

            if (!empty($u->purpose_remark)) {
                $remarksByBatch[$key][$u->purpose_remark] = true;
            }
        }

        return [
            $returnByBatch,
            array_map(fn ($set) => array_keys($set), $purposeByBatch),
            array_map(fn ($set) => array_keys($set), $remarksByBatch),
        ];
    }

    /**
     * Individual document IDs behind a batch's In and Out figures, for the report's
     * Action buttons — keyed by the full product+expiry batch (not just product_id,
     * unlike batchLoyaltyUsageDocs()/batchPromotionUsageDocs(), so two expiry batches
     * of the same product never share a doc list). Multiple lines on the same doc for
     * the same batch are summed into one entry.
     *
     * In  = Receive landing directly at this warehouse + Transfer arriving here +
     *       Settlement qty_remain (the unsettled leftover of a Usage doc, posted back
     *       into period_in by the SP) — sums to exactly 'in_total' (bal.period_in minus
     *       the separately-tracked Retur figure).
     * Out = gross Usage documents (usagetype != 'Return') at this warehouse + any
     *       Transfer LEAVING this warehouse (a ReturnTf sending stock back to
     *       WHCOLLECTION) — the SP posts that outbound leg into the same period_out
     *       column gross Usage-out lands in, so it has to be grouped under Out here
     *       too for the doc list to sum to exactly 'out_total'. Return-type Usage
     *       docs are excluded since they back the separate 'retur' figure, not Out.
     *
     * @return array{0: array<string, array<int, array>>, 1: array<string, array<int, array>>}
     */
    private function stockOutVoucherDocs(string $cpnyid, Carbon $monthStart, Carbon $monthEnd, string $whsId): array
    {
        $inDocs  = [];
        $outDocs = [];

        $push = function (array &$bucket, string $key, string $docKey, array $entry) {
            if (!isset($bucket[$key][$docKey])) {
                $bucket[$key][$docKey] = $entry;

                return;
            }

            $bucket[$key][$docKey]['qty'] += $entry['qty'];
        };

        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->where('tr_vpl_receive_detail.whs_id', $whsId)
            ->whereBetween('tr_vpl_receive.receive_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_receive_detail.product_id',
                'tr_vpl_receive_detail.expired_date',
                'tr_vpl_receive_detail.qty_receive',
                'tr_vpl_receive.id',
                'tr_vpl_receive.receive_id as doc_no',
                'tr_vpl_receive.receive_date as doc_date',
            ])
            ->get();

        foreach ($receives as $r) {
            $key = $r->product_id.'|'.$this->expiredKey($r->expired_date);
            $push($inDocs, $key, 'RCV-'.$r->doc_no, [
                'doc_label' => 'Receive',
                'doc_no'    => $r->doc_no,
                'date'      => Carbon::parse($r->doc_date)->format('Y-m-d'),
                'qty'       => (float) $r->qty_receive,
                'link'      => route('receivevp.show', Hashids::encode($r->id)),
            ]);
        }

        $transfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->whereIn('tr_vpl_transfer.transfertype', ['Transfer', 'ReturnTf'])
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->where(function ($q) use ($whsId) {
                $q->where('tr_vpl_transfer_detail.to_whs_id', $whsId)
                    ->orWhere('tr_vpl_transfer_detail.from_whs_id', $whsId);
            })
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer',
                'tr_vpl_transfer_detail.from_whs_id',
                'tr_vpl_transfer_detail.to_whs_id',
                'tr_vpl_transfer.transfertype',
                'tr_vpl_transfer.id',
                'tr_vpl_transfer.transfer_id as doc_no',
                'tr_vpl_transfer.transfer_date as doc_date',
            ])
            ->get();

        foreach ($transfers as $t) {
            $key   = $t->product_id.'|'.$this->expiredKey($t->expired_date);
            $qty   = abs((float) $t->qty_transfer);
            $entry = [
                'doc_label' => $t->transfertype === 'ReturnTf' ? 'Return Transfer' : 'Transfer',
                'doc_no'    => $t->doc_no,
                'date'      => Carbon::parse($t->doc_date)->format('Y-m-d'),
                'qty'       => $qty,
                'link'      => route('showtransfervp', Hashids::encode($t->id)),
            ];

            // Arriving here feeds In; leaving here (a ReturnTf sending stock back to
            // WHCOLLECTION) is posted by the SP into period_out — same column gross
            // Usage-out lands in — so it belongs in the Out doc list, not a negative
            // entry in In, to keep each list's qty sum match its displayed column total.
            if ($t->to_whs_id === $whsId) {
                $push($inDocs, $key, 'TRF-'.$t->doc_no, $entry);
            }

            if ($t->from_whs_id === $whsId) {
                $push($outDocs, $key, 'TRF-'.$t->doc_no, $entry);
            }
        }

        $usages = TrxVplUsageDetail::query()
            ->join('tr_vpl_usage', 'tr_vpl_usage.usage_id', '=', 'tr_vpl_usage_detail.usage_id')
            ->where('tr_vpl_usage.cpnyid', $cpnyid)
            ->where('tr_vpl_usage.status', 'C')
            ->where('tr_vpl_usage_detail.whs_id', $whsId)
            ->where('tr_vpl_usage.usagetype', '<>', 'Return')
            ->whereBetween('tr_vpl_usage.usage_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_usage_detail.product_id',
                'tr_vpl_usage_detail.expired_date',
                'tr_vpl_usage_detail.qty_usage',
                'tr_vpl_usage.id',
                'tr_vpl_usage.usage_id as doc_no',
                'tr_vpl_usage.usage_date as doc_date',
            ])
            ->get();

        foreach ($usages as $u) {
            $key = $u->product_id.'|'.$this->expiredKey($u->expired_date);
            $push($outDocs, $key, 'USG-'.$u->doc_no, [
                'doc_label' => 'Usage',
                'doc_no'    => $u->doc_no,
                'date'      => Carbon::parse($u->doc_date)->format('Y-m-d'),
                'qty'       => (float) $u->qty_usage,
                'link'      => route('showusagevp', Hashids::encode($u->id)),
            ]);
        }

        // A Settlement reconciles how much of a Usage doc's declared qty was actually
        // redeemed; whatever's left (qty_remain) gets posted back into period_in at the
        // usage warehouse — a third In component beyond Receive/Transfer that's easy to
        // miss (found by empirically checking bal.period_in against Receive+Transfer
        // sums and finding it consistently short by exactly each batch's qty_remain).
        $settlements = TrxVplSettlementDetail::query()
            ->join('tr_vpl_settlement', 'tr_vpl_settlement.settlement_id', '=', 'tr_vpl_settlement_detail.settlement_id')
            ->where('tr_vpl_settlement.cpnyid', $cpnyid)
            ->where('tr_vpl_settlement.status', 'C')
            ->where('tr_vpl_settlement_detail.whs_id', $whsId)
            ->where('tr_vpl_settlement_detail.qty_remain', '>', 0)
            ->whereBetween('tr_vpl_settlement.settlement_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_settlement_detail.product_id',
                'tr_vpl_settlement_detail.expired_date',
                'tr_vpl_settlement_detail.qty_remain',
                'tr_vpl_settlement.id',
                'tr_vpl_settlement.settlement_id as doc_no',
                'tr_vpl_settlement.settlement_date as doc_date',
            ])
            ->get();

        foreach ($settlements as $s) {
            $key = $s->product_id.'|'.$this->expiredKey($s->expired_date);
            $push($inDocs, $key, 'STL-'.$s->doc_no, [
                'doc_label' => 'Settlement',
                'doc_no'    => $s->doc_no,
                'date'      => Carbon::parse($s->doc_date)->format('Y-m-d'),
                'qty'       => (float) $s->qty_remain,
                'link'      => route('showsettlementvp', Hashids::encode($s->id)),
            ]);
        }

        return [
            array_map(fn ($docs) => array_values($docs), $inDocs),
            array_map(fn ($docs) => array_values($docs), $outDocs),
        ];
    }

    /** Category-header / detail render rows, no subtotal — matches the template's single grand-total row at the bottom. */
    private function attachStockOutVoucherGrouping(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $output = [];

        foreach (collect($rows)->groupBy('category_label') as $categoryLabel => $categoryRows) {
            $output[] = ['type' => 'category_header', 'category_label' => $categoryLabel];

            foreach ($categoryRows as $r) {
                $r['type'] = 'detail';
                $output[]  = $r;
            }
        }

        return $output;
    }

    /**
     * @return array{0: array<string, array<int, float>>, 1: array<string, array<int, float>>}
     */
    /**
     * A "Transfer In" ledger row at WHCOLLECTION only counts as Voucher stock coming back
     * (Return Transfer) when it was actually sourced from WHLOYALTY — the ledger row itself
     * doesn't carry the source warehouse, so this joins back to the transfer line that
     * produced it (matched by refnbr=transfer_id + linenbr) to read from_whs_id. A
     * Collection<->Promotion transfer is a different, out-of-scope movement type for this
     * report and is intentionally left uncounted on both the in and out side. Return Usage
     * at WHPROMOTION is added to In rather than subtracted from Out — it isn't new stock,
     * it's a prior Usage reversing — so it's surfaced as its own line instead of quietly
     * shrinking the Out total. This split doesn't change Beginning/Ending math (still
     * In-Out either way); it only changes how the total decomposes for display.
     */
    private function ledgerMonthlyInOut(string $cpnyid, int $year): array
    {
        $rows = DB::connection('pgsql5')->table('tr_vpl_ledger as l')
            ->leftJoin('tr_vpl_transfer_detail as td', function ($join) {
                $join->on('td.transfer_id', '=', 'l.refnbr')
                    ->on('td.linenbr', '=', 'l.linenbr')
                    ->where('l.transaction_source', '=', 'Transfer In');
            })
            ->where('l.cpnyid', $cpnyid)
            ->where('l.perpost', 'like', $year.'%')
            ->where('l.status', 'A')
            ->whereIn('l.whs_id', [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION])
            ->whereIn('l.transaction_source', ['Receive', 'Transfer In', 'Usage', 'Return'])
            ->select('l.product_id', 'l.expired_date', 'l.whs_id', 'l.transaction_source', 'l.perpost', 'l.qty', 'td.from_whs_id')
            ->get();

        $monthlyIn  = [];
        $monthlyOut = [];

        foreach ($rows as $row) {
            $key   = $row->product_id.'|'.$this->expiredKey($row->expired_date);
            $month = (int) substr((string) $row->perpost, 4, 2);
            $qty   = (float) $row->qty;

            if ($row->transaction_source === 'Receive') {
                // A Receive can post directly to WHLOYALTY/WHPROMOTION (migration/opening
                // balance docs, e.g. VPR26090002) as well as the normal WHCOLLECTION path
                // — count it as In wherever it landed so its value isn't silently dropped.
                $monthlyIn[$key][$month] = ($monthlyIn[$key][$month] ?? 0) + $qty;
            } elseif ($row->whs_id === self::WHS_COLLECTION && $row->transaction_source === 'Transfer In' && $row->from_whs_id === self::WHS_LOYALTY) {
                $monthlyIn[$key][$month] = ($monthlyIn[$key][$month] ?? 0) + $qty;
            } elseif ($row->whs_id === self::WHS_LOYALTY && $row->transaction_source === 'Transfer In') {
                $monthlyOut[$key][$month] = ($monthlyOut[$key][$month] ?? 0) + $qty;
            } elseif ($row->whs_id === self::WHS_PROMOTION && $row->transaction_source === 'Usage') {
                $monthlyOut[$key][$month] = ($monthlyOut[$key][$month] ?? 0) - $qty;
            } elseif ($row->whs_id === self::WHS_PROMOTION && $row->transaction_source === 'Return') {
                $monthlyIn[$key][$month] = ($monthlyIn[$key][$month] ?? 0) + $qty;
            }
        }

        return [$monthlyIn, $monthlyOut];
    }

    /** Receive (any of the 3 tracked warehouses — see batchStockRows()) + Return-Transfer-In lines landing at WHCOLLECTION, in the given month. */
    private function inMovementRows(string $cpnyid, Carbon $monthStart, Carbon $monthEnd): array
    {
        $rows = [];

        $receives = TrxVplReceiveDetail::query()
            ->join('tr_vpl_receive', 'tr_vpl_receive.receive_id', '=', 'tr_vpl_receive_detail.receive_id')
            ->where('tr_vpl_receive.cpnyid', $cpnyid)
            ->where('tr_vpl_receive.status', 'C')
            ->whereIn('tr_vpl_receive_detail.whs_id', [self::WHS_COLLECTION, self::WHS_LOYALTY, self::WHS_PROMOTION])
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
                'keterangan'        => null,
            ];
        }

        $returnTransfers = TrxVplTransferDetail::query()
            ->join('tr_vpl_transfer', 'tr_vpl_transfer.transfer_id', '=', 'tr_vpl_transfer_detail.transfer_id')
            ->where('tr_vpl_transfer.cpnyid', $cpnyid)
            ->where('tr_vpl_transfer.status', 'C')
            ->where('tr_vpl_transfer.transfertype', 'ReturnTf')
            ->where('tr_vpl_transfer_detail.from_whs_id', self::WHS_LOYALTY)
            ->where('tr_vpl_transfer_detail.to_whs_id', self::WHS_COLLECTION)
            ->whereBetween('tr_vpl_transfer.transfer_date', [$monthStart, $monthEnd])
            ->select([
                'tr_vpl_transfer_detail.product_id',
                'tr_vpl_transfer_detail.expired_date',
                'tr_vpl_transfer_detail.qty_transfer as qty',
                'tr_vpl_transfer.transfer_date as doc_date',
                'tr_vpl_transfer.transfer_id as doc_no',
                'tr_vpl_transfer.department as diterima_dari',
                'tr_vpl_transfer.created_user as diambil_oleh',
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
                'diambil_oleh'      => $r->diambil_oleh,
                'keperluan'         => $r->diterima_dari,
                'keterangan'        => 'Retur ke Collection',
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
            ->where('tr_vpl_transfer.transfertype', 'Transfer')
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
                'keterangan'        => null,
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
                'direction'         => $isReturn ? 'in' : 'out',
                'doc_label'         => $isReturn ? 'Return' : 'Usage',
                'doc_no'            => $u->doc_no,
                'date'              => Carbon::parse($u->doc_date),
                'qty'               => $isReturn ? abs((float) $u->qty_return_usage) : abs((float) $u->qty_usage),
                'diterima_dari'     => null,
                'untuk_pembayaran'  => null,
                'diambil_oleh'      => $u->diambil_oleh,
                'keperluan'         => $u->keperluan,
                'keterangan'        => $isReturn ? 'Retur ke Collection' : null,
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
