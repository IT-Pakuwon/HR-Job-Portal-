<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

use App\Models\User;
use App\Models\MsDepartment;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\BusinessUnit;

use App\Exports\InventoryMovementExport;

class ReportWarehouseController extends Controller
{


    public function index()
    {
        return view('pages.report-warehouse.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */

    private function spbQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_spb_detail as d')
            ->join('tr_spb as h', 'h.spbid', '=', 'd.spbid')

            ->select([
                'h.spbdate',
                'h.spbid',
                'h.created_by',
                'h.department_id',
                'h.status as spb_status',
                'h.status_issue as issue_status',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.issue_qty',
                'd.sppbid',

                'd.location_id',
                'd.sub_location_id',
                'd.siteid',

                'd.reason_code',
                'd.budget_account_id',
                'd.budget_activity_id',

                'h.keperluan',
                'h.cpny_id',

                'd.budget_business_unit_id',

                'h.worktypeid',
                'h.subworktypeid',
                'h.woid',

                'd.deleted_at',
                'd.completed_at',
                'd.deleted_by',
                'd.completed_by',

                DB::raw('(d.qty - COALESCE(d.issue_qty,0)) as outstanding_qty')
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Issue Query
    |--------------------------------------------------------------------------
    */
    private function issueQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_issue as h')

            ->leftJoin('tr_issue_detail as d','d.issueid','=','h.issueid')
            ->leftJoin('tr_spb as spb','spb.spbid','=','d.spbid')
            ->select([

                'h.issuedate',
                'h.issueid',
                'h.issuetype',

                'h.created_by as issue_created_by',
                'h.department_id as issue_department',
                'h.cpny_id',

                'd.inventoryid',
                'd.inventory_descr',
                'd.issue_qty',
                'd.siteid',

                'd.budget_business_unit_id',

                'spb.spbid',
                'spb.created_by as spb_created_by',
                'spb.department_id as spb_department',
                'spb.keperluan'

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Receipt Query
    |--------------------------------------------------------------------------
    */
    private function receiptQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_receipt as h')

            ->leftJoin('tr_receipt_detail as d','d.receiptnbr','=','h.receiptnbr')

            ->select([

                'h.receiptdate',
                'h.receiptnbr',
                'h.receipttype',
                'h.created_by',
                'h.cpny_id',
                'h.vendorname',

                'd.inventoryid',
                'd.inventory_descr',

                'd.qtyordered',
                'd.qty_received',
                'd.qty_return',

                'd.inventory_type',
                'd.inventory_sub_type',
                'd.inventory_category',

                'd.ponbr',

                'd.siteid',

                'd.budget_business_unit_id',
                'd.budget_account_id',
                'd.budget_activity_id'
            ]);
    }

    private function movementQuery()
    {
        return DB::connection('pgsql')
            ->table('v_inventory_movement_detail as m')
            ->select([

                'm.inventoryid',
                'm.inventory_descr',

                'm.docdate',
                DB::raw("TO_CHAR(m.docdate, 'MM-YYYY') as posting_month"),

                'm.docid',
                'm.doctype',
                'm.refnbr',
                'm.cpny_id',

                'm.siteid',

                DB::raw('CAST(m.qty AS NUMERIC) as qty'),
                // IN
                DB::raw("
                    CASE
                        WHEN UPPER(m.doctype) IN ('STTB','ISSUE_RETURN')
                        THEN m.qty::NUMERIC
                        ELSE 0
                    END as qty_in
                "),

                // OUT
                DB::raw("
                    CASE
                        WHEN UPPER(m.doctype) IN ('ISSUE','STTB_RETURN')
                        THEN m.qty::NUMERIC
                        ELSE 0
                    END as qty_out
                ")
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */
    // private function applyFilters($query, Request $request, $report = 'spb')
    // {

    //     $user = auth()->user();
    //     $cpnyIds = array_map('trim', explode(',', $user->cpny_id));

    //     // Apply company filter correctly
    //     if ($report === 'movement') {
    //         $query->whereIn('x.cpny_id', $cpnyIds); // ✅ USE x
    //     } else {
    //         $query->whereIn('h.cpny_id', $cpnyIds);
    //     }

    //     if ($report === 'issue') {

    //         if ($request->date_from)
    //             $query->whereDate('h.issuedate','>=',$request->date_from);

    //         if ($request->date_to)
    //             $query->whereDate('h.issuedate','<=',$request->date_to);

    //         if ($request->inventoryid)
    //             $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");



    //     }

    //     elseif ($report === 'receipt') {

    //         if ($request->date_from)
    //             $query->whereDate('h.receiptdate','>=',$request->date_from);

    //         if ($request->date_to)
    //             $query->whereDate('h.receiptdate','<=',$request->date_to);

    //         if ($request->receiptnbr)
    //             $query->where('h.receiptnbr','ilike',"%{$request->receiptnbr}%");

    //         if ($request->inventoryid)
    //             $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");

    //     }

    //     else { // SPB

    //         if ($request->date_from)
    //             $query->whereDate('h.spbdate','>=',$request->date_from);

    //         if ($request->date_to)
    //             $query->whereDate('h.spbdate','<=',$request->date_to);

    //         if ($request->spbid)
    //             $query->where('h.spbid','ilike',"%{$request->spbid}%");

    //         if ($request->inventoryid)
    //             $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");

    //         if ($request->spb_status)
    //             $query->where('h.status',$request->spb_status);

    //         if ($request->issue_status)
    //             $query->where('h.status_issue',$request->issue_status);
    //     }

    //     return $query;
    // }


    private function applyFilters($query, Request $request, $report = 'spb')
    {
        $user = auth()->user();
        $cpnyIds = array_map('trim', explode(',', $user->cpny_id));

        // ✅ COMPANY FILTER
        if ($report === 'movement') {
            return $query->whereIn('x.cpny_id', $cpnyIds); // STOP HERE
        }

        // =========================
        // BELOW ONLY FOR NON-MOVEMENT
        // =========================

        $query->whereIn('h.cpny_id', $cpnyIds);

        if ($report === 'issue') {

            if ($request->date_from)
                $query->whereDate('h.issuedate','>=',$request->date_from);

            if ($request->date_to)
                $query->whereDate('h.issuedate','<=',$request->date_to);

            if ($request->inventoryid)
                $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");
        }

        elseif ($report === 'receipt') {

            if ($request->date_from)
                $query->whereDate('h.receiptdate','>=',$request->date_from);

            if ($request->date_to)
                $query->whereDate('h.receiptdate','<=',$request->date_to);

            if ($request->inventoryid)
                $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");
        }

        else { // SPB

            if ($request->date_from)
                $query->whereDate('h.spbdate','>=',$request->date_from);

            if ($request->date_to)
                $query->whereDate('h.spbdate','<=',$request->date_to);

            if ($request->inventoryid)
                $query->where('d.inventoryid','ilike',"%{$request->inventoryid}%");
        }

        return $query;
    }
    /*
    |--------------------------------------------------------------------------
    | Datatable
    |--------------------------------------------------------------------------
    */
    public function json(Request $request)
    {
        $report = $request->report ?? 'spb';



       if ($report === 'movement') {

            $opening = DB::connection('pgsql')
                ->table('v_inventory_movement_detail')
                ->selectRaw("
                    inventoryid,
                    SUM(
                        CASE
                            WHEN doctype IN ('STTB','STTB_RETURN','ISSUE_RETURN') THEN qty
                            WHEN doctype = 'ISSUE' THEN -qty
                            ELSE 0
                        END
                    ) as opening_qty
                ")
                ->when($request->date_from, function ($q) use ($request) {
                    $q->whereDate('docdate', '<', $request->date_from);
                })
                ->groupBy('inventoryid');

            $base = $this->movementQuery();

            // FILTER
            if ($request->date_from)
                $base->whereDate('m.docdate','>=',$request->date_from);

            if ($request->date_to)
                $base->whereDate('m.docdate','<=',$request->date_to);

            if ($request->inventoryid)
                $base->where('m.inventoryid','ilike',"%{$request->inventoryid}%");

            if ($request->refnbr)
                $base->where('m.refnbr','ilike',"%{$request->refnbr}%");

            if ($request->doctype)
                $base->where('m.doctype', $request->doctype);

            // $ending = DB::connection('pgsql')
            //     ->table('v_inventory_movement_detail as m')
            //     ->selectRaw("
            //         m.inventoryid,
            //         m.siteid,
            //         SUM(
            //             CASE
            //                 WHEN UPPER(m.doctype) IN ('STTB','ISSUE_RETURN') THEN m.qty
            //                 WHEN UPPER(m.doctype) IN ('ISSUE','STTB_RETURN') THEN -m.qty
            //                 ELSE 0
            //             END
            //         ) as ending_qty
            //     ")
            //     ->groupBy('m.inventoryid','m.siteid');


            $query = DB::connection('pgsql')
                ->query()
                ->fromSub($base, 'x')
                // ->leftJoinSub($ending, 'e', function ($join) {
                //     $join->on('x.inventoryid','=','e.inventoryid')
                //         ->on('x.siteid','=','e.siteid');
                // }
                // )

->selectRaw("
    x.*,

    -- 🔥 running backward
    SUM(qty_out - qty_in) OVER (
        PARTITION BY x.inventoryid, x.siteid
        ORDER BY x.docdate DESC, x.docid DESC
    ) as running_back,

    -- 🔥 BEGINNING (before transaction)
    SUM(qty_out - qty_in) OVER (
        PARTITION BY x.inventoryid, x.siteid
        ORDER BY x.docdate DESC, x.docid DESC
    ) as begin_qty,

    -- 🔥 ENDING (after transaction)
    SUM(qty_out - qty_in) OVER (
        PARTITION BY x.inventoryid, x.siteid
        ORDER BY x.docdate DESC, x.docid DESC
    ) - (qty_out - qty_in) as end_qty
");
        }
        elseif ($report === 'issue') {
            $query = $this->issueQuery();
        }
        elseif ($report === 'receipt') {
            $query = $this->receiptQuery();
        }
        else {
            $query = $this->spbQuery();
        }

        $query = $this->applyFilters($query, $request, $report);

        $users = User::pluck('name','username');
        $departments = MsDepartment::pluck('department_name','department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name','business_unit_id');
        $table = DataTables::of($query)

        ->addColumn('creator', function($row) use ($users, $report){

            if ($report === 'issue') {
                $username = $row->issue_created_by ?? null;
            } elseif ($report === 'movement') {
                return ''; // ✅ no creator for movement
            } else {
                $username = $row->created_by ?? null;
            }

            return $users[$username] ?? $username;
        })

        ->addColumn('department_name', function($row) use ($departments, $report){

            if ($report === 'issue') {
                $dept = $row->issue_department ?? null;
            }
            elseif ($report === 'spb') {
                $dept = $row->department_id ?? null;
            }
            else {
                return ''; // receipt has no department
            }

            return $departments[$dept] ?? '';
        })

        ->addColumn('business_unit_name', function ($row) use ($businessUnits, $report) {

            if ($report === 'movement') {
                return ''; // ✅ movement doesn't have BU
            }

            return $businessUnits[$row->budget_business_unit_id] ?? '';
        });

        /*
        |--------------------------------------------------------------------------
        | SPB FORMAT (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        if ($report === 'spb') {

            $table

            ->editColumn('spbdate', fn($row) =>
                $row->spbdate
                    ? Carbon::parse($row->spbdate)->format('d-M-Y')
                    : '')

            ->editColumn('qty', fn($row) =>
                number_format($row->qty ?? 0,3))

            ->editColumn('issue_qty', fn($row) =>
                number_format($row->issue_qty ?? 0,3))

            ->editColumn('outstanding_qty', fn($row) =>
                number_format($row->outstanding_qty ?? 0,3))

            ->editColumn('spb_status', fn($row) =>
                $this->mapSpbStatus($row->spb_status))

            ->editColumn('issue_status', fn($row) =>
                $this->mapIssueStatus($row->issue_status));

        }

        /*
        |--------------------------------------------------------------------------
        | ISSUE FORMAT
        |--------------------------------------------------------------------------
        */

        if ($report === 'issue') {

            $table

                ->editColumn('issuetype', function ($row) {

                    return match ($row->issuetype) {
                        'IS' => 'Issue',
                        'RT' => 'Return Issue',
                        // 'TR' => 'Transfer',
                        default => $row->issuetype
                    };

                })

            ->addColumn('created_issue_by', function ($row) use ($users) {
                return $users[$row->issue_created_by] ?? $row->issue_created_by;
            })

            ->addColumn('department_created_issue', function ($row) use ($departments) {
                return $departments[$row->issue_department] ?? '';
            })

            // ->addColumn('business_unit_name', function ($row) use ($businessUnits) {
            //     return $businessUnits[$row->budget_business_unit_id] ?? '';
            // })

            ->addColumn('business_unit_name', function ($row) use ($businessUnits) {
                return $businessUnits[$row->budget_business_unit_id ?? ''] ?? '';
            })

            ->addColumn('spb_created_by', function ($row) use ($users) {
                return $users[$row->spb_created_by] ?? '';
            })

            ->addColumn('spb_department_created', function ($row) use ($departments) {
                return $departments[$row->spb_department] ?? '';
            });

        }

       if ($report === 'receipt') {

            $table

            ->editColumn('receiptdate', function ($row) {
                return $row->receiptdate
                    ? Carbon::parse($row->receiptdate)->format('d-M-Y')
                    : '';
            })

            ->editColumn('qtyordered', function ($row) {
                return number_format($row->qtyordered ?? 0,3);
            })

            ->editColumn('qty_received', function ($row) {
                return number_format($row->qty_received ?? 0,3);
            })

            ->editColumn('receipttype', function ($row) {

                return match ($row->receipttype) {
                    'RR' => 'Return',
                    'PR' => 'Purchase Receive',
                    default => $row->receipttype
                };

            })

            ->editColumn('qty_return', function ($row) {
                return number_format($row->qty_return ?? 0,3);
            });

        }

        if ($report === 'movement') {

            $table

            ->editColumn('docdate', function ($row) {
                return $row->docdate
                    ? Carbon::parse($row->docdate)->format('d-M-Y')
                    : '';
            })

            ->editColumn('qty_in', fn($row) =>
                number_format($row->qty_in ?? 0,3))

            ->editColumn('qty_out', fn($row) =>
                number_format($row->qty_out ?? 0,3))

            ->editColumn('begin_qty', fn($row) =>
                number_format($row->begin_qty ?? 0,3))

            ->editColumn('end_qty', fn($row) =>
                number_format($row->end_qty ?? 0,3))

            ->editColumn('doctype', function ($row) {

                return match ($row->doctype) {
                    'STTB' => 'Receipt',
                    'STTB_RETURN' => 'Return Receipt',
                    'ISSUE' => 'Issue',
                    'ISSUE_RETURN' => 'Return Issue',
                    default => $row->doctype
                };

            });

        }
        return $table
            ->rawColumns($report === 'spb' ? ['spb_status','issue_status'] : [])
            ->make(true);
    }

    public function searchInventory(Request $request)
    {
        $q = $request->q;

        return DB::connection('pgsql')
            ->table('ms_inventory') // ⚠️ adjust if your table name different
            ->select('inventoryid', 'inventory_descr')
            ->when($q, function ($query) use ($q) {
                $query->where('inventoryid', 'ilike', "%{$q}%")
                    ->orWhere('inventory_descr', 'ilike', "%{$q}%");
            })
            ->limit(20)
            ->get();
    }
    /*
    /*
    |--------------------------------------------------------------------------
    | Export Excel
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        $report = $request->get('report', 'spb');

        switch ($report) {

            case 'issue':
                return $this->exportIssue($request);

            case 'receipt':
                return $this->exportReceipt($request);

            case 'movement': // ✅ NEW (Inventory Movement)
            case 'inventory': // optional alias
                return $this->exportMovement($request);

            case 'spb':
            default:
                return $this->exportSpb($request);
        }
    }

    private function exportSpb(Request $request)
    {

        $rows = $this->applyFilters(
            $this->spbQuery(),
            $request,
            'spb'
        )->get();

        $users = User::pluck('name','username');
        $departments = MsDepartment::pluck('department_name','department_id');
        $locations = MsLocation::pluck('location_name','location_id');
        $sublocations = MsSubLocation::pluck('sub_location_name','sub_location_id');

        $businessUnits = BusinessUnit::pluck('business_unit_name','business_unit_id');


        /*
        |--------------------------------------------------------------------------
        | Transform rows
        |--------------------------------------------------------------------------
        */
        $rows = $rows->map(function ($row) use ($users,$departments,$locations,$sublocations,$businessUnits){

            return [

                'Date SPB' =>
                    $row->spbdate
                        ? Carbon::parse($row->spbdate)->format('Y-m-d')
                        : '',

                'SPB No' => $row->spbid,

                'SPPB No' => $row->sppbid ?? '',

                'Created By' =>
                    $users[$row->created_by] ?? $row->created_by,

                'Department' =>
                    $departments[$row->department_id] ?? '',

                'Inventory ID' => $row->inventoryid,

                'Description' => $row->inventory_descr,

                'Status SPB' =>
                    $this->plainSpbStatus($row->spb_status),

                'Status Issue' =>
                    $this->plainIssueStatus($row->issue_status),

                'SPB Qty' => number_format($row->qty, 3, '.', ''),

                'BPG Qty' => number_format($row->issue_qty ?? 0, 3, '.', ''),

                'Outstanding Qty' => number_format($row->outstanding_qty ?? 0, 3, '.', ''),

                'Purpose' => $row->keperluan,

                'Company' => $row->cpny_id,

                'Business Unit' => $businessUnits[$row->budget_business_unit_id] ?? '',

                'Work Type' => $row->worktypeid ?? '',

                'Sub Work Type' => $row->subworktypeid ?? '',

                'No WO' => $row->woid ?? '',

                'Warehouse' => $row->siteid ?? '',

                'Warehouse Location' =>
                    $locations[$row->location_id] ?? '',

                'Location' =>
                    $locations[$row->location_id] ?? '',

                'Sub Location' =>
                    $sublocations[$row->sub_location_id] ?? '',

                'COA' => $row->budget_account_id ?? '',

                'Activity' => $row->budget_activity_id ?? '',
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'warehouse_spb_report.xlsx'
        );
    }

    private function exportIssue(Request $request)
    {
        $rows = $this->applyFilters(
            $this->issueQuery(),
            $request,
            'issue'
        )->get();

        $users = User::pluck('name','username');
        $departments = MsDepartment::pluck('department_name','department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name','business_unit_id');

        $rows = $rows->map(function ($row) use ($users,$departments,$businessUnits){

            return [

                'Issued Date' =>
                    $row->issuedate
                        ? Carbon::parse($row->issuedate)->format('Y-m-d')
                        : '',

                'Issue No' => $row->issueid,

                'Inventory ID' => $row->inventoryid,

                'Description' => $row->inventory_descr,

                'Qty Issued' => number_format($row->issue_qty ?? 0,3,'.',''),

                'Issued By' => $users[$row->issue_created_by] ?? $row->issue_created_by,

                'Issued Department' => $departments[$row->issue_department] ?? '',

                'Company' => $row->cpny_id,

                'Business Unit' =>
                    $businessUnits[$row->budget_business_unit_id] ?? '',

                'Warehouse' => $row->siteid,

                'SPB No' => $row->spbid ?? '',

                'Request By' =>
                    $users[$row->spb_created_by] ?? $row->spb_created_by,

                'Request Department' =>
                    $departments[$row->spb_department] ?? '',

                'Purpose' => $row->keperluan ?? '',

            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'warehouse_issue_report.xlsx'
        );
    }

    private function exportReceipt(Request $request)
    {
        $rows = $this->applyFilters(
            $this->receiptQuery(),
            $request,
            'receipt'
        )->get();

        $users = User::pluck('name','username');
        $businessUnits = BusinessUnit::pluck('business_unit_name','business_unit_id');

        $rows = $rows->map(function ($row) use ($users,$businessUnits){

            return [

                'Receipt Date' =>
                    $row->receiptdate
                        ? Carbon::parse($row->receiptdate)->format('Y-m-d')
                        : '',

                'Receipt No' => $row->receiptnbr,

                'Type' => match ($row->receipttype) {

                    'RR' => 'Return',
                    'PR' => 'Purchase Receive',

                    default => $row->receipttype
                },

                'Created By' =>
                    $users[$row->created_by] ?? $row->created_by,

                'Company' => $row->cpny_id,

                'Vendor Name' => $row->vendorname,

                'Inventory ID' => $row->inventoryid,

                'Description' => $row->inventory_descr,

                'Qty Ordered' =>
                    number_format($row->qtyordered ?? 0,3,'.',''),

                'Qty Received' =>
                    number_format($row->qty_received ?? 0,3,'.',''),

                'Qty Returned' =>
                    number_format($row->qty_return ?? 0,3,'.',''),

                'Warehouse' => $row->siteid,

                'Business Unit' =>
                    $businessUnits[$row->budget_business_unit_id] ?? '',

                /* EXTRA EXPORT FIELDS */

                'Inventory Type' => $row->inventory_type,

                'Inventory Sub Type' => $row->inventory_sub_type,

                'Inventory Category' => $row->inventory_category,

                'PO No' => $row->ponbr,

                'COA' => $row->budget_account_id,

                'Activity' => $row->budget_activity_id,

            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'warehouse_receipt_report.xlsx'
        );
    }

    private function exportMovement(Request $request)
    {
        $query = DB::connection('pgsql')
            ->table('v_inventory_movement_detail');

        $user = auth()->user();
        $cpnyIds = array_map('trim', explode(',', $user->cpny_id));

        $query->whereIn('cpny_id', $cpnyIds);


        // 🔍 FILTERS (same as UI)
        if ($request->date_from) {
            $query->whereDate('trx_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('trx_date', '<=', $request->date_to);
        }

        if ($request->inventoryid) {
            $query->where('inventoryid', $request->inventoryid);
        }

        if ($request->refnbr) {
            $query->where('refnbr', 'ILIKE', '%' . $request->refnbr . '%');
        }

        if ($request->doctype) {
            $query->where('trx_source', $request->doctype);
        }

        $data = $query
            ->orderBy('inventoryid')
            ->orderBy('trx_date')
            ->get();

        return Excel::download(
            new InventoryMovementExport($data),
            'inventory_movement_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Status Mappers
    |--------------------------------------------------------------------------
    */

    private function mapSpbStatus($status)
    {
        return [
            'C' => '<span class="px-2 py-1 text-xs text-white bg-green-500 rounded">Completed</span>',
            'P' => '<span class="px-2 py-1 text-xs text-white bg-yellow-500 rounded">On Progress</span>',
            'D' => '<span class="px-2 py-1 text-xs text-white bg-gray-500 rounded">Draft</span>',
            'N' => '<span class="px-2 py-1 text-xs text-white bg-blue-500 rounded">New</span>',
            'X' => '<span class="px-2 py-1 text-xs text-white bg-red-500 rounded">Cancelled</span>',
        ][$status] ?? $status;
    }

    private function mapIssueStatus($status)
    {
        return [
            'Open' => '<span class="px-2 py-1 text-xs text-white bg-blue-500 rounded">Open</span>',
            'Partial' => '<span class="px-2 py-1 text-xs text-white bg-yellow-500 rounded">Partial</span>',
            'Closed' => '<span class="px-2 py-1 text-xs text-white bg-green-600 rounded">Completed</span>',
            'Completed' => '<span class="px-2 py-1 text-xs text-white bg-green-600 rounded">Completed</span>',
        ][$status] ?? $status;
    }

    private function plainSpbStatus($status)
    {
        return [
            'N'=>'New',
            'D'=>'Draft',
            'P'=>'On Progress',
            'C'=>'Completed',
            'X'=>'Cancelled'
        ][$status] ?? $status;
    }

    private function plainIssueStatus($status)
    {
        return [
            'Open'=>'Open',
            'Partial'=>'Partial',
            'Closed'=>'Completed',
            'Completed'=>'Completed'
        ][$status] ?? $status;
    }

}
