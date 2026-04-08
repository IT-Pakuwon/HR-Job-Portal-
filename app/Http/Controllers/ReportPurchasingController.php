<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\MsDepartment;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportPurchasingController extends Controller
{
    public function index()
    {
        return view('pages.report-purchasing.index');
    }

    /*
    |--------------------------------------------------------------------------
    | SPPB QUERY
    |--------------------------------------------------------------------------
    */

    private function sppbQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_sppb as h')
            ->leftJoin('tr_sppb_detail as d', 'd.sppbid', '=', 'h.sppbid')

            ->select([
                'h.sppbdate',
                'h.sppbid',
                'h.spbid',
                'h.woid',
                'h.department_id',
                'h.requesttypeid',
                'h.keperluan',
                'h.assignpurchasing',
                'h.created_by',
                'h.cpny_id',
                'h.status',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.siteid',

                'd.budget_business_unit_id',
                'd.budget_department_fin_id',
                'd.budget_account_id',
                'd.budget_activity_id',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SPPJ QUERY
    |--------------------------------------------------------------------------
    */

    private function sppjQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_sppj as h')
            ->leftJoin('tr_sppj_detail as d', 'd.sppjid', '=', 'h.sppjid')

            ->select([
                'h.sppjdate',
                'h.sppjid',
                'h.woid',
                'h.bqid',
                'h.bqtype',
                'h.department_id',
                'h.requesttypeid',
                'h.keperluan',
                'h.assignpurchasing',
                'h.created_by',
                'h.cpny_id',
                'h.status',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.siteid',

                'd.budget_business_unit_id',
                'd.budget_department_fin_id',
                'd.budget_account_id',
                'd.budget_activity_id',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SPPK QUERY
    |--------------------------------------------------------------------------
    */
    private function sppkQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_sppk as h')
            ->leftJoin('tr_sppk_detail as d', 'd.sppkid', '=', 'h.sppkid')

            ->select([
                'h.sppkdate',
                'h.sppkid',
                'h.department_id',
                'h.no_polisi',
                'h.namakendaraan',
                'h.pemilikkendaraan',
                'h.keperluan',
                'h.assignpurchasing',
                'h.created_by',
                'h.cpny_id',
                'h.status',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.siteid',

                'd.budget_business_unit_id',
                'd.budget_department_fin_id',
                'd.budget_account_id',
                'd.budget_activity_id',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SPPT QUERY
    |--------------------------------------------------------------------------
    */

    private function spptQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_sppt as h')
            ->leftJoin('tr_sppt_detail as d', 'd.spptid', '=', 'h.spptid')

            ->select([
                'h.spptdate',
                'h.spptid',
                'h.woid',
                'h.bqid',
                'h.department_id',

                'h.nama_tenant',
                'h.no_unit_tenant',
                'h.pic_pengawas',

                'h.condition_unit',
                'h.beban',
                'h.keperluan',

                'h.assignpurchasing',
                'h.created_by',
                'h.cpny_id',
                'h.status',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.siteid',

                'd.budget_business_unit_id',
                'd.budget_department_fin_id',
                'd.budget_account_id',
                'd.budget_activity_id',
            ]);
    }
    /*
    |--------------------------------------------------------------------------
    | APPLY FILTERS
    |--------------------------------------------------------------------------
    */

    private function applyFilters($query, Request $request, $type)
    {
        if ($request->filled('date_from')) {
            $query->whereDate("h.{$type}date", '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate("h.{$type}date", '<=', $request->date_to);
        }

        $idField = $type.'id';

        if ($request->filled($idField)) {
            $query->where("h.$idField", 'ilike', "%{$request->$idField}%");
        }

        if ($request->filled('inventoryid')) {
            $query->where('d.inventoryid', 'ilike', "%{$request->inventoryid}%");
        }

        if ($request->filled('status')) {
            $query->where('h.status', $request->status);
        }

        return $query;
    }

    private function applyUserScope($query, $report)
    {
        $user = auth()->user();

        $isCostCtrl = $user->hasRole('COSTCTRLACCESS');
        $isWarehouse = $user->hasRole('WHSACCESS');

        // Company scope (always applied)
        $companyIds = Usercpny::where('username', $user->username)
            ->pluck('cpny_id');

        $query->whereIn('h.cpny_id', $companyIds);

        // Department list
        $deptIds = Userdept::where('username', $user->username)
            ->pluck('department_id');

        /*
        ROLE RULES
        */

        // Cost Control → see everything
        if ($isCostCtrl) {
            return $query;
        }

        // Warehouse → only SPPB see all departments
        if ($isWarehouse && $report === 'sppb') {
            return $query;
        }

        // All other cases → restrict by department
        $query->whereIn('h.department_id', $deptIds);

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | JSON DATATABLE
    |--------------------------------------------------------------------------
    */
    public function json(Request $request)
    {
        $report = $request->report ?? 'sppb';

        if ($report === 'sppj') {
            $query = $this->sppjQuery();
        } elseif ($report === 'sppk') {
            $query = $this->sppkQuery();
        } elseif ($report === 'sppt') {
            $query = $this->spptQuery();
        } else {
            $query = $this->sppbQuery();
        }

        $query = $this->applyFilters($query, $request, $report);

        // IMPORTANT
        $query = $this->applyUserScope($query, $report);

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name', 'business_unit_id');

        $table = DataTables::of($query)

            ->editColumn($report.'date', function ($row) use ($report) {
                $date = $row->{$report.'date'} ?? null;

                return $date
                    ? Carbon::parse($date)->format('d-M-Y')
                    : '';
            })

            ->editColumn('qty', fn ($row) => number_format($row->qty ?? 0, 3))

            ->addColumn('department_name', function ($row) use ($departments) {
                return $departments[$row->department_id] ?? '';
            })

            ->addColumn('requester', function ($row) use ($users) {
                return $users[$row->created_by] ?? $row->created_by;
            })

            ->addColumn('purchasing', function ($row) use ($users) {
                return $users[$row->assignpurchasing] ?? $row->assignpurchasing;
            })

            ->addColumn('business_unit_name', function ($row) use ($businessUnits) {
                return $businessUnits[$row->budget_business_unit_id] ?? '';
            })

            ->editColumn('status', function ($row) {
                return [
                    'N' => '<span class="px-2 py-1 text-xs text-white bg-blue-500 rounded">New</span>',
                    'P' => '<span class="px-2 py-1 text-xs text-white bg-yellow-500 rounded">On Progress</span>',
                    'C' => '<span class="px-2 py-1 text-xs text-white bg-green-500 rounded">Completed</span>',
                    'D' => '<span class="px-2 py-1 text-xs text-white bg-gray-500 rounded">Revised</span>',
                    'X' => '<span class="px-2 py-1 text-xs text-white bg-red-500 rounded">Cancelled</span>',
                    'R' => '<span class="px-2 py-1 text-xs text-white bg-red-500 rounded">Cancelled</span>',
                ][$row->status] ?? $row->status;
            });

        $statusList = [
            // 'N' => 'New',
            'P' => 'On Progress',
            'C' => 'Completed',
            'D' => 'Revised',
            'R' => 'Rejected',
            'X' => 'Cancelled',
        ];

        return $table
            ->rawColumns(['status'])
            ->with([
                'status_list' => $statusList,
            ])
            ->make(true);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function export(Request $request)
    {
        $report = $request->report ?? 'sppb';

        if ($report === 'sppj') {
            return $this->exportSppj($request);
        }

        if ($report === 'sppk') {
            return $this->exportSppk($request);
        }

        if ($report === 'sppt') {
            return $this->exportSppt($request);
        }

        return $this->exportSppb($request);
    }

    private function exportSppb(Request $request)
    {
        $query = $this->applyFilters(
            $this->sppbQuery(),
            $request,
            'sppb'
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name', 'business_unit_id');

        $rows = $rows->map(function ($row) use ($users, $departments, $businessUnits) {
            return [
                'SPPB Date' => $row->sppbdate
                    ? Carbon::parse($row->sppbdate)->format('Y-m-d')
                    : '',

                'SPPB No' => $row->sppbid,

                'SPB No' => $row->spbid ?? '',

                'WO No' => $row->woid ?? '',

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->created_by] ?? $row->created_by,

                'Purchasing' => $users[$row->assignpurchasing] ?? $row->assignpurchasing,

                'Purpose' => $row->keperluan,

                'Inventory ID' => $row->inventoryid,

                'Inventory Description' => $row->inventory_descr,

                'Qty' => number_format($row->qty ?? 0, 3, '.', ''),

                'UOM' => $row->uom,

                'Warehouse' => $row->siteid,

                'Business Unit' => $businessUnits[$row->budget_business_unit_id] ?? '',

                'Budget Department' => $row->budget_department_fin_id,

                'Budget Account (COA)' => $row->budget_account_id,

                'Budget Activity' => $row->budget_activity_id,

                'Status' => $this->plainStatus($row->status),
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'purchasing_sppb_report.xlsx'
        );
    }

    private function exportSppj(Request $request)
    {
        $query = $this->applyFilters(
            $this->sppjQuery(),
            $request,
            'sppj'
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name', 'business_unit_id');

        $rows = $rows->map(function ($row) use ($users, $departments, $businessUnits) {
            return [
                'SPPJ Date' => $row->sppjdate
                    ? Carbon::parse($row->sppjdate)->format('Y-m-d')
                    : '',

                'SPPJ No' => $row->sppjid,

                'WO No' => $row->woid ?? '',

                'BQ ID' => $row->bqid ?? '',

                'BQ Type' => $row->bqtype ?? '',

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->created_by] ?? $row->created_by,

                'Purchasing' => $users[$row->assignpurchasing] ?? $row->assignpurchasing,

                'Purpose' => $row->keperluan,

                'Inventory ID' => $row->inventoryid,

                'Inventory Description' => $row->inventory_descr,

                'Qty' => number_format($row->qty ?? 0, 3, '.', ''),

                'UOM' => $row->uom,

                'Warehouse' => $row->siteid,

                'Business Unit' => $businessUnits[$row->budget_business_unit_id] ?? '',

                'Budget Department' => $row->budget_department_fin_id,

                'Budget Account (COA)' => $row->budget_account_id,

                'Budget Activity' => $row->budget_activity_id,

                'Status' => $this->plainStatus($row->status),
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'purchasing_sppj_report.xlsx'
        );
    }

    private function exportSppk(Request $request)
    {
        $query = $this->applyFilters(
            $this->sppkQuery(),
            $request,
            'sppk'
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name', 'business_unit_id');

        $rows = $rows->map(function ($row) use ($users, $departments, $businessUnits) {
            return [
                'SPPK Date' => $row->sppkdate
                    ? Carbon::parse($row->sppkdate)->format('Y-m-d')
                    : '',

                'SPPK No' => $row->sppkid,

                'Vehicle Plate' => $row->no_polisi,

                'Vehicle Name' => $row->namakendaraan,

                'Vehicle Owner' => $row->pemilikkendaraan,

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->created_by] ?? $row->created_by,

                'Purchasing' => $users[$row->assignpurchasing] ?? $row->assignpurchasing,

                'Purpose' => $row->keperluan,

                'Inventory ID' => $row->inventoryid,

                'Inventory Description' => $row->inventory_descr,

                'Qty' => number_format($row->qty ?? 0, 3, '.', ''),

                'UOM' => $row->uom,

                'Warehouse' => $row->siteid,

                'Business Unit' => $businessUnits[$row->budget_business_unit_id] ?? '',

                'Budget Department' => $row->budget_department_fin_id,

                'Budget Account (COA)' => $row->budget_account_id,

                'Budget Activity' => $row->budget_activity_id,

                'Status' => $this->plainStatus($row->status),
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'purchasing_sppk_report.xlsx'
        );
    }

    private function exportSppt(Request $request)
    {
        $query = $this->applyFilters(
            $this->spptQuery(),
            $request,
            'sppt'
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');
        $businessUnits = BusinessUnit::pluck('business_unit_name', 'business_unit_id');

        $rows = $rows->map(function ($row) use ($users, $departments, $businessUnits) {
            return [
                'SPPT Date' => $row->spptdate
                    ? Carbon::parse($row->spptdate)->format('Y-m-d')
                    : '',

                'SPPT No' => $row->spptid,

                'WO No' => $row->woid ?? '',

                'BQ ID' => $row->bqid ?? '',

                'Tenant' => $row->nama_tenant,

                'Unit' => $row->no_unit_tenant,

                'PIC Pengawas' => $users[$row->pic_pengawas] ?? $row->pic_pengawas,

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->created_by] ?? $row->created_by,

                'Purchasing' => $users[$row->assignpurchasing] ?? $row->assignpurchasing,

                'Purpose' => $row->keperluan,

                'Condition Unit' => $row->condition_unit,

                'Load (Beban)' => $row->beban,

                'Inventory ID' => $row->inventoryid,

                'Description' => $row->inventory_descr,

                'Qty' => number_format($row->qty ?? 0, 3, '.', ''),

                'UOM' => $row->uom,

                'Warehouse' => $row->siteid,

                'Business Unit' => $businessUnits[$row->budget_business_unit_id] ?? '',

                'Budget Department' => $row->budget_department_fin_id,

                'COA' => $row->budget_account_id,

                'Activity' => $row->budget_activity_id,

                'Status' => $this->plainStatus($row->status),
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'purchasing_sppt_report.xlsx'
        );
    }

    private function plainStatus($status)
    {
        return [
            'N' => 'New',
            'D' => 'Revised',
            'P' => 'On Progress',
            'C' => 'Completed',
            'X' => 'Cancelled',
            'R' => 'Reject',
        ][$status] ?? $status;
    }
}
