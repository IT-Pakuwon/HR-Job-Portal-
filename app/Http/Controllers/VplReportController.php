<?php

namespace App\Http\Controllers;

use App\Exports\VplStockVoucherExport;
use App\Models\MsVplProduct;
use App\Models\MsVplProductBal;
use App\Models\TrxVplReceiveDetail;
use App\Models\TrxVplTransferDetail;
use App\Models\TrxVplUsageDetail;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VplReportController extends Controller
{
    private const WHS_COLLECTION = 'WHCOLLECTION';
    private const WHS_LOYALTY    = 'WHLOYALTY';
    private const WHS_PROMOTION  = 'WHPROMOTION';

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

        $tabCount = 1;

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

        return Excel::download(new VplStockVoucherExport($groups, $year, $month), $filename);
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

        $inRows  = $this->inMovementRows($cpnyid, $monthStart, $monthEnd);
        $outRows = $this->outMovementRows($cpnyid, $monthStart, $monthEnd);

        $groups = [];

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
            $ending   = $beginning + $monthIn - $monthOut;

            $rows = array_merge(
                $inRows[$key] ?? [],
                $outRows[$key] ?? []
            );

            usort($rows, fn ($a, $b) => $a['date'] <=> $b['date']);

            $categoryLabel = $product->product_category === 'F&B' ? 'F&B' : 'NON F&B';

            $groups[] = [
                'product_id'     => $product->product_id,
                'tenant'         => $product->product_name,
                'category_label' => $categoryLabel,
                'expired_date'   => $this->expiredKey($bal->expired_date) === 'NULL' ? null : $bal->expired_date->format('Y-m-d'),
                'nominal'        => (float) $product->product_value,
                'beginning'      => $beginning,
                'in_total'       => $monthIn,
                'out_total'      => $monthOut,
                'ending'         => $ending,
                'total_nominal'  => $ending * (float) $product->product_value,
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
