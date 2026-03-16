<?php

namespace App\Http\Controllers;

use App\Models\MsDepartment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;   // ← ADD THIS
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportCanvassSheetController extends Controller
{
    public function index()
    {
        return view('pages.report-cs.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */

    private function csDetailQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_cs_detail as d')

            ->join('tr_cs as h', 'h.csid', '=', 'd.csid')

            // ->leftJoin('tr_po as po', 'po.csid', '=', 'h.csid')

            ->select([
                'h.csid',
                'h.csdate',
                'h.sppbjktid',
                'h.keperluan',
                'h.department_id',
                'h.created_by',
                'h.cpny_id',
                'h.user_peminta',
                'h.created_by as cs_created_by',

                'd.ponbr',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.budget_department_fin_id',
                'd.budget_account_id',

                'd.vendorprice1',
                'd.vendorprice2',
                'd.vendorprice3',
                'd.vendorprice4',
                'd.vendorprice5',
                'd.vendorprice6',

                'd.vendortotalprice1',
                'd.vendortotalprice2',
                'd.vendortotalprice3',
                'd.vendortotalprice4',
                'd.vendortotalprice5',
                'd.vendortotalprice6',

                'd.vendor1selected',
                'd.vendor2selected',
                'd.vendor3selected',
                'd.vendor4selected',
                'd.vendor5selected',
                'd.vendor6selected',

                'h.vendorname1',
                'h.vendorname2',
                'h.vendorname3',
                'h.vendorname4',
                'h.vendorname5',
                'h.vendorname6',
            ])

            ->where(function ($q) {
                $q->where('vendor1selected', true)
                    ->orWhere('vendor2selected', true)
                    ->orWhere('vendor3selected', true)
                    ->orWhere('vendor4selected', true)
                    ->orWhere('vendor5selected', true)
                    ->orWhere('vendor6selected', true);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters($query, Request $request)
    {
        $user = auth()->user();
        $cpnyIds = array_map('trim', explode(',', $user->cpny_id));

        $query->whereIn('h.cpny_id', $cpnyIds);

        if ($request->date_from) {
            $query->whereDate('h.csdate', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('h.csdate', '<=', $request->date_to);
        }

        if ($request->csid) {
            $query->where('h.csid', 'ilike', "%{$request->csid}%");
        }

        if ($request->ponbr) {
            $query->where('po.ponbr', 'ilike', "%{$request->ponbr}%");
        }

        if ($request->sppbjktid) {
            $query->where('h.sppbjktid', 'ilike', "%{$request->sppbjktid}%");
        }

        if ($request->inventoryid) {
            $query->where('d.inventoryid', 'ilike', "%{$request->inventoryid}%");
        }

        return $query;
    }

    private function applyUserScope($query)
    {
        $user = auth()->user();

        $isCostCtrl = $user->hasRole('COSTCTRLACCESS');

        /*
        |------------------------------------------------
        | Company scope
        |------------------------------------------------
        */

        $companyIds = \App\Models\Usercpny::where('username', $user->username)
            ->pluck('cpny_id');

        $query->whereIn('h.cpny_id', $companyIds);

        /*
        |------------------------------------------------
        | Department scope
        |------------------------------------------------
        */

        if (!$isCostCtrl) {
            $deptIds = \App\Models\Userdept::where('username', $user->username)
                ->pluck('department_id');

            $query->where(function ($q) use ($deptIds, $user) {
                $q->whereIn('h.department_id', $deptIds)
                  ->orWhere('h.created_by', $user->username)
                  ->orWhere('h.user_peminta', $user->username);
            });
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Datatable JSON
    |--------------------------------------------------------------------------
    */

    public function json(Request $request)
    {
        $query = $this->applyFilters(
            $this->csDetailQuery(),
            $request
        );

        $query = $this->applyUserScope($query);

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        return DataTables::of($query)

            ->editColumn('csdate', fn ($row) => $row->csdate
                    ? Carbon::parse($row->csdate)->format('d-M-Y')
                    : ''
            )

            ->editColumn('qty', fn ($row) => number_format($row->qty ?? 0, 2)
            )

            ->addColumn('unit_price', function ($row) {
                if ($row->vendor1selected) {
                    return $row->vendorprice1;
                }
                if ($row->vendor2selected) {
                    return $row->vendorprice2;
                }
                if ($row->vendor3selected) {
                    return $row->vendorprice3;
                }
                if ($row->vendor4selected) {
                    return $row->vendorprice4;
                }
                if ($row->vendor5selected) {
                    return $row->vendorprice5;
                }
                if ($row->vendor6selected) {
                    return $row->vendorprice6;
                }

                return 0;
            })

            ->addColumn('request_user', function ($row) use ($users) {
                return $users[$row->user_peminta] ?? $row->user_peminta;
            })

            ->addColumn('purchase_user', function ($row) use ($users) {
                return $users[$row->cs_created_by] ?? $row->cs_created_by;
            })

            ->addColumn('total_price', function ($row) {
                if ($row->vendor1selected) {
                    return $row->vendortotalprice1;
                }
                if ($row->vendor2selected) {
                    return $row->vendortotalprice2;
                }
                if ($row->vendor3selected) {
                    return $row->vendortotalprice3;
                }
                if ($row->vendor4selected) {
                    return $row->vendortotalprice4;
                }
                if ($row->vendor5selected) {
                    return $row->vendortotalprice5;
                }
                if ($row->vendor6selected) {
                    return $row->vendortotalprice6;
                }

                return 0;
            })

            ->addColumn('budget_department_name', function ($row) use ($departments) {
                return $departments[$row->budget_department_fin_id] ?? '';
            })

            ->addColumn('vendor_name', function ($row) {
                if ($row->vendor1selected) {
                    return $row->vendorname1;
                }
                if ($row->vendor2selected) {
                    return $row->vendorname2;
                }
                if ($row->vendor3selected) {
                    return $row->vendorname3;
                }
                if ($row->vendor4selected) {
                    return $row->vendorname4;
                }
                if ($row->vendor5selected) {
                    return $row->vendorname5;
                }
                if ($row->vendor6selected) {
                    return $row->vendorname6;
                }

                return '';
            })

            ->addColumn('creator', fn ($row) => $users[$row->created_by] ?? $row->created_by
            )

            ->addColumn('department_name', fn ($row) => $departments[$row->department_id] ?? ''
            )

            ->make(true);
    }

    // public function export(Request $request)
    // {
    //     $rows = $this->applyFilters(
    //         $this->csDetailQuery(),
    //         $request
    //     )->get();

    //     $users = User::pluck('name', 'username');
    //     $departments = MsDepartment::pluck('department_name', 'department_id');

    //     $rows = $rows->map(function ($row) use ($users, $departments) {
    //         // determine selected vendor
    //         $unit_price = 0;
    //         $total_price = 0;
    //         $vendor_name = '';

    //         if ($row->vendor1selected) {
    //             $unit_price = $row->vendorprice1;
    //             $total_price = $row->vendortotalprice1;
    //             $vendor_name = $row->vendorname1;
    //         } elseif ($row->vendor2selected) {
    //             $unit_price = $row->vendorprice2;
    //             $total_price = $row->vendortotalprice2;
    //             $vendor_name = $row->vendorname2;
    //         } elseif ($row->vendor3selected) {
    //             $unit_price = $row->vendorprice3;
    //             $total_price = $row->vendortotalprice3;
    //             $vendor_name = $row->vendorname3;
    //         } elseif ($row->vendor4selected) {
    //             $unit_price = $row->vendorprice4;
    //             $total_price = $row->vendortotalprice4;
    //             $vendor_name = $row->vendorname4;
    //         } elseif ($row->vendor5selected) {
    //             $unit_price = $row->vendorprice5;
    //             $total_price = $row->vendortotalprice5;
    //             $vendor_name = $row->vendorname5;
    //         } elseif ($row->vendor6selected) {
    //             $unit_price = $row->vendorprice6;
    //             $total_price = $row->vendortotalprice6;
    //             $vendor_name = $row->vendorname6;
    //         }

    //         return [
    //             'Date' => $row->csdate
    //                     ? Carbon::parse($row->csdate)->format('Y-m-d')
    //                     : '',

    //             'CS No' => $row->csid,

    //             'SPPB/J/K/T' => $row->sppbjktid,

    //             'PO / SPK' => $row->ponbr ?? '',

    //             'Department' => $departments[$row->department_id] ?? '',

    //             'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

    //             'Purchasing' => $users[$row->cs_created_by] ?? $row->cs_created_by,

    //             'Purpose' => $row->keperluan,

    //             'Budget Department' => $row->budget_department_fin_id,

    //             'Vendor' => $vendor_name,

    //             'Unit Price' => number_format($unit_price, 2, '.', ''),

    //             'Total Price' => number_format($total_price, 2, '.', ''),
    //         ];
    //     });

    //     return Excel::download(
    //         new \App\Exports\ArrayExport($rows),
    //         'report_canvass_sheet.xlsx'
    //     );
    // }

    public function export(Request $request)
    {
        $report = $request->report ?? 'detail';

        if ($report === 'detail') {
            return $this->exportDetail($request);
        }

        // // future reports
        // if ($report === 'summary') {
        //     return $this->exportSummary($request);
        // }

        // if ($report === 'vendor') {
        //     return $this->exportVendor($request);
        // }
    }

    private function exportDetail(Request $request)
    {
        $query = $this->applyFilters(
            $this->csDetailQuery(),
            $request
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        $rows = $rows->map(function ($row) use ($users, $departments) {
            $unit_price = 0;
            $total_price = 0;
            $vendor_name = '';

            if ($row->vendor1selected) {
                $unit_price = $row->vendorprice1;
                $total_price = $row->vendortotalprice1;
                $vendor_name = $row->vendorname1;
            } elseif ($row->vendor2selected) {
                $unit_price = $row->vendorprice2;
                $total_price = $row->vendortotalprice2;
                $vendor_name = $row->vendorname2;
            } elseif ($row->vendor3selected) {
                $unit_price = $row->vendorprice3;
                $total_price = $row->vendortotalprice3;
                $vendor_name = $row->vendorname3;
            } elseif ($row->vendor4selected) {
                $unit_price = $row->vendorprice4;
                $total_price = $row->vendortotalprice4;
                $vendor_name = $row->vendorname4;
            } elseif ($row->vendor5selected) {
                $unit_price = $row->vendorprice5;
                $total_price = $row->vendortotalprice5;
                $vendor_name = $row->vendorname5;
            } elseif ($row->vendor6selected) {
                $unit_price = $row->vendorprice6;
                $total_price = $row->vendortotalprice6;
                $vendor_name = $row->vendorname6;
            }

            // return [
            //     'Date' => $row->csdate
            //             ? Carbon::parse($row->csdate)->format('Y-m-d')
            //             : '',

            //     'CS No' => $row->csid,

            //     'SPPB/J/K/T' => $row->sppbjktid,

            //     'PO / SPK' => $row->ponbr ?? '',

            //     'Department' => $departments[$row->department_id] ?? '',

            //     'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

            //     'Purchasing' => $users[$row->cs_created_by] ?? $row->cs_created_by,

            //     'Purpose' => $row->keperluan,

            //     'Inventory ID' => $row->inventoryid ?? '',

            //     'Inventory Description' => $row->inventory_descr ?? '',

            //     'Qty' => number_format($row->qty ?? 0, 2, '.', ''),

            //     'UOM' => $row->uom ?? '',

            //     'Budget Department' => $row->budget_department_fin_id ?? '',

            //     'Vendor' => $vendor_name,

            //     'Unit Price' => number_format($unit_price, 2, '.', ''),

            //     'Total Price' => number_format($total_price, 2, '.', ''),
            // ];

            return [
                'Date' => $row->csdate
                        ? Carbon::parse($row->csdate)->format('Y-m-d')
                        : '',

                'CS No' => $row->csid,

                'SPPB/J/K/T' => $row->sppbjktid,

                'PO / SPK' => $row->ponbr ?? '',

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

                'Purchasing' => $users[$row->cs_created_by] ?? $row->cs_created_by,

                'Purpose' => $row->keperluan,

                'Inventory ID' => $row->inventoryid ?? '',

                'Inventory Description' => $row->inventory_descr ?? '',

                'Qty' => number_format($row->qty ?? 0, 2, '.', ''),

                'UOM' => $row->uom ?? '',

                'Budget Department' => $departments[$row->budget_department_fin_id] ?? '',

                'Account ID' => $row->budget_account_id ?? '',   // NEW COLUMN

                'Vendor' => $vendor_name,

                'Unit Price' => number_format($unit_price, 2, '.', ''),

                'Total Price' => number_format($total_price, 2, '.', ''),
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'report_canvass_sheet_detail.xlsx'
        );
    }
}
