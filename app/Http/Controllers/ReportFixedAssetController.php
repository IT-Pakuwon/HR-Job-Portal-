<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class ReportFixedAssetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.report-fixedassets.index', [
            'user' => $user,
        ]);
    }

    private function query()
    {
        return DB::connection('pgsql')
            ->table('tr_receipt_detail as sttbdt')
            ->leftJoin('tr_receipt as sttb', function ($join) {
                $join->on('sttbdt.receiptnbr', '=', 'sttb.receiptnbr')
                     ->on('sttbdt.budget_cpny_id', '=', 'sttb.cpny_id');
            })
            ->select([
                'sttbdt.receiptnbr',
                'sttb.receiptdate',
                'sttb.receipttype',
                'sttb.ponbr',
                'sttb.ref_receiptnbr',
                'sttb.cpny_id',
                'sttb.sppbjktid',
                'sttb.department_id',
                'sttb.user_peminta',
                'sttb.vendorid',
                'sttb.vendorname',
                'sttbdt.inventory_sub_type',
                'sttbdt.inventory_category',
                'sttbdt.inventoryid',
                'sttbdt.inventory_descr',
                'sttbdt.qtyordered',
                'sttbdt.qty_received',
                'sttbdt.uom',
                DB::raw("
                    case
                        when sttbdt.qtyordered = sttbdt.qty_received then 'Full Received'
                        when sttbdt.qtyordered > sttbdt.qty_received then 'Partial Received'
                        else 'ERR'
                    end as status
                "),
            ])
            ->where('sttbdt.inventory_sub_type', 'FIXED ASSET')
            ->where('sttb.status', 'C')
            ->orderBy('sttb.cpny_id')
            ->orderBy('sttb.receiptnbr');
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->date_from) {
            $query->whereDate('sttb.receiptdate', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('sttb.receiptdate', '<=', $request->date_to);
        }

        if ($request->receiptnbr) {
            $query->where('sttbdt.receiptnbr', 'ilike', "%{$request->receiptnbr}%");
        }

        if ($request->type) {
            $query->where('sttb.receipttype', 'ilike', "%{$request->type}%");
        }

        if ($request->ponbr) {
            $query->where('sttb.ponbr', 'ilike', "%{$request->ponbr}%");
        }

        if ($request->ref_receiptnbr) {
            $query->where('sttb.ref_receiptnbr', 'ilike', "%{$request->ref_receiptnbr}%");
        }

        if ($request->cpny) {
            $query->where('sttb.cpny_id', 'ilike', "%{$request->cpny}%");
        }

        if ($request->sppb) {
            $query->where('sttb.sppbjktid', 'ilike', "%{$request->sppb}%");
        }

        if ($request->department) {
            $query->where('sttb.department_id', 'ilike', "%{$request->department}%");
        }

        if ($request->user_peminta) {
            $query->where('sttb.user_peminta', 'ilike', "%{$request->user_peminta}%");
        }

        if ($request->vendor) {
            $query->where(function ($q) use ($request) {
                $q->where('sttb.vendorid', 'ilike', "%{$request->vendor}%")
                  ->orWhere('sttb.vendorname', 'ilike', "%{$request->vendor}%");
            });
        }

        if ($request->inventory) {
            $query->where(function ($q) use ($request) {
                $q->where('sttbdt.inventoryid', 'ilike', "%{$request->inventory}%")
                  ->orWhere('sttbdt.inventory_descr', 'ilike', "%{$request->inventory}%");
            });
        }

        if ($request->status) {
            $query->whereRaw("
                case
                    when sttbdt.qtyordered = sttbdt.qty_received then 'Full Received'
                    when sttbdt.qtyordered > sttbdt.qty_received then 'Partial Received'
                    else 'ERR'
                end = ?", [$request->status]);
        }

        return $query;
    }

    private function applyUserScope($query)
    {
        $user = Auth::user();

        $companyIds = \App\Models\Usercpny::where('username', $user->username)
            ->pluck('cpny_id');

        $query->whereIn('sttb.cpny_id', $companyIds);

        return $query;
    }

    public function json(Request $request)
    {
        $query = $this->applyUserScope($this->query());
        $query = $this->applyFilters($query, $request);

        return DataTables::of($query)
            ->addColumn('receipt_date_fmt', fn ($row) =>
                $row->receiptdate ? Carbon::parse($row->receiptdate)->format('d-M-Y') : ''
            )
            ->addColumn('qty_po', fn ($row) => $row->qtyordered)
            ->addColumn('qty_sttb', fn ($row) => $row->qty_received)
            ->make(true);
    }

    public function export(Request $request)
    {
        $query = $this->applyUserScope($this->query());
        $query = $this->applyFilters($query, $request);

        $rows = $query->get();

        $data = $rows->map(fn ($row) => [
            'STTB'           => $row->receiptnbr,
            'Date'           => $row->receiptdate ? Carbon::parse($row->receiptdate)->format('d-M-Y') : '',
            'Type'           => $row->receipttype,
            'PO'             => $row->ponbr,
            'Ref Receipt'    => $row->ref_receiptnbr,
            'Cpny'           => $row->cpny_id,
            'SPPB'           => $row->sppbjktid,
            'Department'     => $row->department_id,
            'User Peminta'   => $row->user_peminta,
            'Vendor'         => $row->vendorid,
            'Vendor Name'    => $row->vendorname,
            'Item Sub Type'  => $row->inventory_sub_type,
            'Item Category'  => $row->inventory_category,
            'Inventory Code' => $row->inventoryid,
            'Inventory Name' => $row->inventory_descr,
            'Qty PO'         => $row->qtyordered,
            'Qty STTB'       => $row->qty_received,
            'UOM'            => $row->uom,
            'Status'         => $row->status,
        ]);

        return Excel::download(
            new ArrayExport($data),
            'FixedAsset_Report_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
