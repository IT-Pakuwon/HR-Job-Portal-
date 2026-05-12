<?php

namespace App\Http\Controllers;

use App\Models\MsDepartment;
use App\Models\MsSPPBJKTCounting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;   // ← ADD THIS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportCanvassSheetController extends Controller
{
    public function index()
    {
        $departments = MsDepartment::pluck('department_name', 'department_id');

        return view('pages.report-cs.index', compact('departments'));
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
                'h.status',

                'h.budget_perpost',
                'd.budget_business_unit_id',

                'd.ponbr',

                'd.inventoryid',
                'd.inventory_descr',
                'd.qty',
                'd.uom',
                'd.budget_department_fin_id',
                'd.budget_account_id',
                'd.budget_activity_descr',

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

        if ($request->status) {
            $query->where('h.status', $request->status);
        }

        return $query;
    }

    private function applyUserScope($query)
    {
        $user = auth()->user();

        $isCostCtrl = $user->hasRole('COSTCTRLACCESS');
        $isPurch = $user->hasRole('PURCHACCESS');

        $isGlobalAccess = $isCostCtrl || $isPurch;

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

        if (!$isGlobalAccess) {
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

            ->addColumn('status_label', function ($row) {
                return match ($row->status) {
                    'D' => 'Revised',
                    'A' => 'Assigned',
                    'S' => 'Submitted',
                    'X' => 'Cancelled',
                    'P' => 'On Process',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    default => $row->status ?? '-',
                };
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

            ->addColumn('budget_department_name', function ($row) {
                return $row->budget_department_fin_id ?? '';
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

    public function trackingJson(Request $request)
    {
        $query = DB::connection('pgsql')
            ->table('tr_cs as h')

            // ✅ JOIN ALL DOCUMENT TABLES
            ->leftJoin('tr_sppb as b', 'b.sppbid', '=', 'h.sppbjktid')
            ->leftJoin('tr_sppj as j', 'j.sppjid', '=', 'h.sppbjktid')
            ->leftJoin('tr_sppk as k', 'k.sppkid', '=', 'h.sppbjktid')
            ->leftJoin('tr_sppt as t', 't.spptid', '=', 'h.sppbjktid')

            ->select([
                'h.csid',
                DB::raw('h.id as cs_pk'),

                'h.sppbjktid',

                DB::raw("
                    CASE
                        WHEN h.sppbjktid ILIKE 'PB%' THEN 'SPPB'
                        WHEN h.sppbjktid ILIKE 'PJ%' THEN 'SPPJ'
                        WHEN h.sppbjktid ILIKE 'PK%' THEN 'SPPK'
                        WHEN h.sppbjktid ILIKE 'PT%' THEN 'SPPT'
                        ELSE NULL
                    END as doc_type
                "),

                DB::raw('h.sppbjktid as doc_eid'),

                // ✅ REAL DOCUMENT ID (IMPORTANT)
                DB::raw('
                    COALESCE(b.id, j.id, k.id, t.id) as doc_id
                '),

                'h.csdate',
                'h.cpny_id',
                'h.department_id',
                'h.created_by',
                'h.user_peminta',
                'h.csnote',
                'h.assigndate',
                'h.submitdate',
                'h.status',

                // DB::raw("
                //     CONCAT(
                //         DATE_PART('day', NOW() - h.assigndate),
                //         ' / ',
                //         DATE_PART('day', COALESCE(h.submitdate, NOW()) - h.assigndate)
                //     ) as days
                // ")
            ]);

        // $baseQuery = DB::connection('pgsql')
        // ->table('tr_cs as h')

        // ->leftJoin('tr_sppb as b', 'b.sppbid', '=', 'h.sppbjktid')
        // ->leftJoin('tr_sppj as j', 'j.sppjid', '=', 'h.sppbjktid')
        // ->leftJoin('tr_sppk as k', 'k.sppkid', '=', 'h.sppbjktid')
        // ->leftJoin('tr_sppt as t', 't.spptid', '=', 'h.sppbjktid');

        /*
        |------------------------------------------
        | FILTER
        |------------------------------------------
        */

        if ($request->date_from) {
            $query->whereDate('h.csdate', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('h.csdate', '<=', $request->date_to);
        }

        if ($request->csid) {
            $query->where('h.csid', 'ilike', "%{$request->csid}%");
        }

        if ($request->sppbjkt) {
            $query->where('h.sppbjktid', 'ilike', "%{$request->sppbjkt}%");
        }

        // if ($request->status) {
        //     $query->where('h.status', $request->status);
        // }

        if ($request->department) {
            $query->where('h.department_id', $request->department);
        }

        if ($request->requester) {
            $query->where('h.user_peminta', 'ilike', "%{$request->requester}%");
        }

        if ($request->doc_type) {
            $query->whereRaw("
                CASE
                    WHEN h.sppbjktid ILIKE 'PB%' THEN 'SPPB'
                    WHEN h.sppbjktid ILIKE 'PJ%' THEN 'SPPJ'
                    WHEN h.sppbjktid ILIKE 'PK%' THEN 'SPPK'
                    WHEN h.sppbjktid ILIKE 'PT%' THEN 'SPPT'
                END = ?
            ", [$request->doc_type]);
        }

        // if ($request->overdue !== null && $request->overdue !== '') {
        //     if ($request->overdue == 1) {
        //         $query->whereRaw("DATE_PART('day', COALESCE(h.submitdate, NOW()) - h.csdate) > 7");
        //     } else {
        //         $query->whereRaw("DATE_PART('day', COALESCE(h.submitdate, NOW()) - h.csdate) <= 7");
        //     }
        // }
        // ✅ USER SCOPE (KEEP YOUR EXISTING)
        $query = $this->applyUserScope($query);

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        $summary = [
            'total' => (clone $query)->count(),
            'process' => (clone $query)->where('h.status', 'P')->count(),
            'completed' => (clone $query)->where('h.status', 'C')->count(),
            'rejected' => (clone $query)->where('h.status', 'R')->count(),
            'revised' => (clone $query)->where('h.status', 'D')->count(),
        ];

        $status = $request->status;

        if (!empty($status)) {
            $query->where('h.status', $status);
        }

        // return DataTables::of($query)
        $rows = $query->get();

        $countingMap = MsSPPBJKTCounting::pluck('doctype_counting', 'doctype')->toArray();

        $countBusinessDays = function ($assignDate, $submitDate) {
            if (!$assignDate || !$submitDate) {
                return 0;
            }

            $start = Carbon::parse($assignDate)->addDay();
            $end = Carbon::parse($submitDate);

            if ($end->lt($start)) {
                return 0;
            }

            $days = 0;

            foreach (CarbonPeriod::create($start, $end) as $d) {
                if ($d->isSaturday() || $d->isSunday()) {
                    continue;
                }
                ++$days;
            }

            return $days;
        };

        $rows->transform(function ($r) use ($countBusinessDays, $countingMap) {
            $assign = $r->assigndate;
            $submit = $r->submitdate ?? now();

            $days = $countBusinessDays($assign, $submit);

            $doctype = strtoupper(substr($r->sppbjktid, 0, 2));
            $limit = $countingMap[$doctype] ?? 0;

            $r->days = $days.' / '.$limit;
            $r->is_overdue = $limit > 0 && $days > $limit;

            return $r;
        });

        return DataTables::of($rows)

        ->editColumn('csdate', fn ($row) => $row->csdate ? Carbon::parse($row->csdate)->format('d-M-Y') : ''
        )

        ->editColumn('assigndate', fn ($row) => $row->assigndate ? Carbon::parse($row->assigndate)->format('d-M-Y') : ''
        )

        ->editColumn('submitdate', fn ($row) => $row->submitdate ? Carbon::parse($row->submitdate)->format('d-M-Y') : ''
        )

        ->addColumn('created_by_name', fn ($row) => $users[$row->created_by] ?? $row->created_by
        )

        ->addColumn('department_name', fn ($row) => $departments[$row->department_id] ?? ''
        )

        ->addColumn('cs_hash', fn ($row) => \Hashids::encode($row->cs_pk)
        )

        ->addColumn('doc_hash', fn ($row) => $row->doc_id ? \Hashids::encode($row->doc_id) : null
        )

        ->addColumn('status_label', function ($row) {
            return match ($row->status) {
                'D' => 'Revised',
                'A' => 'Assigned',
                'S' => 'Submitted',
                'X' => 'Cancelled',
                'P' => 'On Process',
                'C' => 'Completed',
                'R' => 'Rejected',
                default => $row->status ?? '-',
            };
        })

        ->addColumn('status_class', function ($row) {
            return match ($row->status) {
                'D' => 'bg-gray-100 text-gray-700',
                'A' => 'bg-blue-100 text-blue-700',
                'S' => 'bg-indigo-100 text-indigo-700',
                'P' => 'bg-yellow-100 text-yellow-700',
                'C' => 'bg-green-100 text-green-700',
                'R' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-600',
            };
        })

        // ✅ FIXED
        ->addColumn('is_overdue', fn ($row) => $row->is_overdue)
        ->with('summary', $summary)
        ->make(true);
    }

    public function tracking($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $cs = \App\Models\TrCS::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) {
                return null;
            }
            $u = User::where('username', $username)->first();

            return $u->name ?? $username;
        };

        $steps = [];

        /*
        |------------------------------------------
        | 1. CREATED
        |------------------------------------------
        */
        $steps[] = [
            'key' => 'created',
            'title' => 'CS Created',
            'status' => 'C',
            'status_label' => 'Created',
            'by' => $getName($cs->created_by),
            'at' => optional($cs->created_at)->format('Y-m-d H:i'),
        ];

        /*
        |------------------------------------------
        | 2. ASSIGNED
        |------------------------------------------
        */
        if ($cs->assigndate) {
            $steps[] = [
                'key' => 'assigned',
                'title' => 'Assigned',
                'status' => 'A',
                'status_label' => 'Assigned',
                'by' => $getName($cs->created_by),
                'at' => Carbon::parse($cs->assigndate)->format('Y-m-d H:i'),
            ];
        }

        /*
        |------------------------------------------
        | 3. SUBMITTED
        |------------------------------------------
        */
        if ($cs->submitdate) {
            $steps[] = [
                'key' => 'submitted',
                'title' => 'Submitted',
                'status' => 'S',
                'status_label' => 'Submitted',
                'by' => $getName($cs->created_by),
                'at' => Carbon::parse($cs->submitdate)->format('Y-m-d H:i'),
            ];
        }

        /*
        |------------------------------------------
        | 4. APPROVAL (FROM SPPBJKT)
        |------------------------------------------
        */
        if ($cs->sppbjktid) {
            // $approvals = \App\Models\TrApproval::where('refnbr', $cs->sppbjktid)
            //     ->where('status', '<>', 'X')
            //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            //     ->orderBy('created_at')
            //     ->get();

            $approvals = \App\Models\TrApproval::where('refnbr', $cs->csid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at')
            ->get();

            foreach ($approvals as $a) {
                // 🔥 IMPORTANT: DO NOT CHANGE STATUS (as you requested)
                $steps[] = [
                    'key' => 'approval_'.$a->id,
                    'title' => 'Approval Lv '.$a->aprv_leveling,
                    'status' => $a->status, // 🔥 ORIGINAL
                    'status_label' => match ($a->status) {
                        'A' => 'Approved',
                        'P' => 'Waiting Approval',
                        'R' => 'Rejected',
                        'D' => 'Revised',
                        default => $a->status,
                    },
                    'by' => $getName($a->aprv_username),
                    'at' => $a->aprv_dateafter
                        ? Carbon::parse($a->aprv_dateafter)->format('Y-m-d H:i')
                        : null,
                ];

                // stop if rejected
                if ($a->status === 'R') {
                    break;
                }
            }
        }

        /*
        |------------------------------------------
        | 5. COMPLETED
        |------------------------------------------
        */
        if ($cs->status === 'C') {
            $steps[] = [
                'key' => 'completed',
                'title' => 'Completed',
                'status' => 'C',
                'status_label' => 'Completed',
                'by' => $getName($cs->completed_by),
                'at' => optional($cs->completed_at)->format('Y-m-d H:i'),
            ];
        }

        return response()->json([
            'doc' => $cs->csid,
            'steps' => $steps,
        ]);
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

    // private function exportDetail(Request $request)
    // {
    //     $query = $this->applyFilters(
    //         $this->csDetailQuery(),
    //         $request
    //     );

    //     $query = $this->applyUserScope($query);

    //     $rows = $query->get();

    //     $users = User::pluck('name', 'username');
    //     $departments = MsDepartment::pluck('department_name', 'department_id');

    //     $rows = $rows->map(function ($row) use ($users, $departments) {
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

    //         // return [
    //         //     'Date' => $row->csdate
    //         //             ? Carbon::parse($row->csdate)->format('Y-m-d')
    //         //             : '',

    //         //     'CS No' => $row->csid,

    //         //     'SPPB/J/K/T' => $row->sppbjktid,

    //         //     'PO / SPK' => $row->ponbr ?? '',

    //         //     'Department' => $departments[$row->department_id] ?? '',

    //         //     'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

    //         //     'Purchasing' => $users[$row->cs_created_by] ?? $row->cs_created_by,

    //         //     'Purpose' => $row->keperluan,

    //         //     'Inventory ID' => $row->inventoryid ?? '',

    //         //     'Inventory Description' => $row->inventory_descr ?? '',

    //         //     'Qty' => number_format($row->qty ?? 0, 2, '.', ''),

    //         //     'UOM' => $row->uom ?? '',

    //         //     'Budget Department' => $row->budget_department_fin_id ?? '',

    //         //     'Vendor' => $vendor_name,

    //         //     'Unit Price' => number_format($unit_price, 2, '.', ''),

    //         //     'Total Price' => number_format($total_price, 2, '.', ''),
    //         // ];

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

    //             'Inventory ID' => $row->inventoryid ?? '',

    //             'Inventory Description' => $row->inventory_descr ?? '',

    //             'Qty' => number_format($row->qty ?? 0, 2, '.', ''),

    //             'UOM' => $row->uom ?? '',

    //             'Budget Department' => $departments[$row->budget_department_fin_id] ?? '',

    //             'Account ID' => $row->budget_account_id ?? '',   // NEW COLUMN

    //             'Vendor' => $vendor_name,

    //             'Unit Price' => number_format($unit_price, 2, '.', ''),

    //             'Total Price' => number_format($total_price, 2, '.', ''),
    //         ];
    //     });

    //     return Excel::download(
    //         new \App\Exports\ArrayExport($rows),
    //         'report_canvass_sheet_detail.xlsx'
    //     );
    // }

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
            /*
            |------------------------------------------
            | Detect Selected Vendor
            |------------------------------------------
            */

            $vendor = '';
            $unitPrice = 0;
            $dpp = 0;
            $tax = 0;
            $total = 0;

            if ($row->vendor1selected) {
                $vendor = $row->vendorname1;
                $unitPrice = $row->vendorprice1;
                $dpp = $row->vendortotalprice1;
            } elseif ($row->vendor2selected) {
                $vendor = $row->vendorname2;
                $unitPrice = $row->vendorprice2;
                $dpp = $row->vendortotalprice2;
            } elseif ($row->vendor3selected) {
                $vendor = $row->vendorname3;
                $unitPrice = $row->vendorprice3;
                $dpp = $row->vendortotalprice3;
            } elseif ($row->vendor4selected) {
                $vendor = $row->vendorname4;
                $unitPrice = $row->vendorprice4;
                $dpp = $row->vendortotalprice4;
            } elseif ($row->vendor5selected) {
                $vendor = $row->vendorname5;
                $unitPrice = $row->vendorprice5;
                $dpp = $row->vendortotalprice5;
            } elseif ($row->vendor6selected) {
                $vendor = $row->vendorname6;
                $unitPrice = $row->vendorprice6;
                $dpp = $row->vendortotalprice6;
            }

            /*
            |------------------------------------------
            | Calculate Tax + Total
            |------------------------------------------
            */

            $tax = $dpp * 0.11;   // adjust if tax code dynamic
            $total = $dpp + $tax;

            return [
                'CS_ID' => $row->csid,

                'Date' => $row->csdate
                    ? Carbon::parse($row->csdate)->format('Y-m-d')
                    : '',

                'Cpny' => $row->cpny_id,

                'PO' => $row->ponbr ?? '',

                'SPPBJKT' => $row->sppbjktid,

                'Purchasing' => $users[$row->cs_created_by] ?? $row->cs_created_by,

                'Department' => $departments[$row->department_id] ?? '',

                'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

                'Purpose' => $row->keperluan,

                'InventoryID' => $row->inventoryid,

                'Description' => $row->inventory_descr,

                'Qty' => number_format($row->qty ?? 0, 2, '.', ''),

                'UOM' => $row->uom,

                'Vendor' => $vendor,

                'Unit Price' => number_format($unitPrice ?? 0, 2, '.', ''),

                'DPP' => number_format($dpp ?? 0, 2, '.', ''),

                'Tax' => number_format($tax ?? 0, 2, '.', ''),

                'Grand Total' => number_format($total ?? 0, 2, '.', ''),

                'Budget Year' => $row->budget_perpost ?? '',

                'Budget Business Unit' => $row->budget_business_unit_id ?? '',

                'Budget Department' => $departments[$row->budget_department_fin_id] ?? '',

                'Activity Account' => $row->budget_account_id ?? '',

                'Acitivty Description' => $row->budget_activity_descr ?? '',
            ];
        });

        return Excel::download(
            new \App\Exports\ArrayExport($rows),
            'report_canvass_sheet_detail.xlsx'
        );
    }
}
