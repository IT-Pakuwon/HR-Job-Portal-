<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TrxVplUsage;
use App\Models\TrxVplUsageDetail;
use App\Models\TrxVplUsageDetailTemp;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplWarehouseDept;
use App\Models\MsVplWarehouseUsage;
use App\Models\M_approval;
use App\Models\T_approval;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\Autonbr;
use App\Models\Site;

use DataTables;
use Mail;
use PDF;

class VplUsageController extends Controller
{

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------

    public function index()
    {
        return view('vpl.usagevp.index');
    }

    // -------------------------------------------------------
    // LIST VIEWS
    // -------------------------------------------------------

    public function waiting(Request $request)
    {
        $tittle      = 'On Progress Usage / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplUsage::leftjoin('trx_approval', 'trx_vpl_usage.usage_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_usage.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplUsage::leftjoin('trx_approval', 'trx_vpl_usage.usage_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_usage.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->whereIn('trx_vpl_usage.cpnyid', $multicpnyid)
                    ->get();
            } else {
                $data = TrxVplUsage::leftjoin('trx_approval', 'trx_vpl_usage.usage_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_usage.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->whereIn('trx_vpl_usage.cpnyid', $multicpnyid)
                    ->whereIn('trx_vpl_usage.department', $multidept)
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('usagetype', function ($row) {
                    return match ($row->usagetype) {
                        'Usage'  => 'Usage',
                        'Return' => 'Return',
                        default  => '',
                    };
                })
                ->addColumn('usage_id', function ($row) {
                    $url = route('vpl.usagevp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->usage_id . '</a>';
                })
                ->rawColumns(['status', 'usage_id', 'usagetype'])
                ->make(true);
        }

        return view('vpl.usagevp.waiting', compact('tittle', 'user'));
    }

    public function completed(Request $request)
    {
        $tittle      = 'Completed Usage / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplUsage::where('status', 'C')->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)->where('status', 'C')->get();
            } else {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->where('status', 'C')
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('usagetype', function ($row) {
                    return match ($row->usagetype) {
                        'Usage'  => 'Usage',
                        'Return' => 'Return',
                        default  => '',
                    };
                })
                ->addColumn('usage_id', function ($row) {
                    $url = route('vpl.usagevp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->usage_id . '</a>';
                })
                ->rawColumns(['status', 'usage_id', 'usagetype'])
                ->make(true);
        }

        return view('vpl.usagevp.completed', compact('tittle', 'user'));
    }

    public function rejected(Request $request)
    {
        $tittle      = 'Rejected Usage / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplUsage::where('status', 'R')->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)->where('status', 'R')->get();
            } else {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->where('status', 'R')
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('usagetype', function ($row) {
                    return match ($row->usagetype) {
                        'Usage'  => 'Usage',
                        'Return' => 'Return',
                        default  => '',
                    };
                })
                ->addColumn('usage_id', function ($row) {
                    $url = route('vpl.usagevp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->usage_id . '</a>';
                })
                ->rawColumns(['status', 'usage_id', 'usagetype'])
                ->make(true);
        }

        return view('vpl.usagevp.rejected', compact('tittle', 'user'));
    }

    public function all(Request $request)
    {
        $tittle      = 'All Usage / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplUsage::get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)->get();
            } else {
                $data = TrxVplUsage::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('usagetype', function ($row) {
                    return match ($row->usagetype) {
                        'Usage'  => 'Usage',
                        'Return' => 'Return',
                        default  => '',
                    };
                })
                ->addColumn('usage_id', function ($row) {
                    $url = route('vpl.usagevp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->usage_id . '</a>';
                })
                ->rawColumns(['status', 'usage_id', 'usagetype'])
                ->make(true);
        }

        return view('vpl.usagevp.all', compact('tittle', 'user'));
    }

    // -------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------

    private function statusBadge(string $status): string
    {
        return match ($status) {
            'P'     => '<a href="javascript:void(0)" class="label" style="background-color:#FFCD05">On Progress</a>',
            'C'     => '<a href="javascript:void(0)" class="label" style="background-color:#05A801">Completed</a>',
            'R'     => '<a href="javascript:void(0)" class="label" style="background-color:#EA002F">Rejected</a>',
            'X'     => '<a href="javascript:void(0)" class="label" style="background-color:#EA002F">Cancel</a>',
            default => '<a href="javascript:void(0)" class="label label-info">Revise</a>',
        };
    }

    // -------------------------------------------------------
    // PLACEHOLDER — to be implemented
    // -------------------------------------------------------

    public function add()            { /* TODO */ }
    public function show(int $id)   { /* TODO */ }
    public function edit(int $id)   { /* TODO */ }
}
