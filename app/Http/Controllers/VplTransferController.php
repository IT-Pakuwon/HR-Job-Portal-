<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TrxVplTransfer;
use App\Models\TrxVplTransferDetail;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\MsVplWarehouseDept;
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

class VplTransferController extends Controller
{

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------

    public function index()
    {
        return view('vpl.transfervp.index');
    }

    // -------------------------------------------------------
    // LIST VIEWS
    // -------------------------------------------------------

    public function waiting(Request $request)
    {
        $tittle      = 'On Progress Transfer / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplTransfer::leftjoin('trx_approval', 'trx_vpl_transfer.transfer_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplTransfer::leftjoin('trx_approval', 'trx_vpl_transfer.transfer_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->whereIn('trx_vpl_transfer.cpnyid', $multicpnyid)
                    ->get();
            } else {
                $data = TrxVplTransfer::leftjoin('trx_approval', 'trx_vpl_transfer.transfer_id', '=', 'trx_approval.docid')
                    ->select('trx_vpl_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->whereNotNull('trx_approval.aprvdatebefore')
                    ->whereIn('trx_vpl_transfer.cpnyid', $multicpnyid)
                    ->whereIn('trx_vpl_transfer.department', $multidept)
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('transfertype', function ($row) {
                    return match ($row->transfertype) {
                        'Transfer' => 'Transfer',
                        'ReturnTf' => 'Return Transfer',
                        default    => '',
                    };
                })
                ->addColumn('transfer_id', function ($row) {
                    $url = route('vpl.transfervp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->transfer_id . '</a>';
                })
                ->rawColumns(['status', 'transfer_id', 'transfertype'])
                ->make(true);
        }

        return view('vpl.transfervp.waiting', compact('tittle', 'user'));
    }

    public function completed(Request $request)
    {
        $tittle      = 'Completed Transfer / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplTransfer::where('status', 'C')->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)->where('status', 'C')->get();
            } else {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->where('status', 'C')
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('transfertype', function ($row) {
                    return match ($row->transfertype) {
                        'Transfer' => 'Transfer',
                        'ReturnTf' => 'Return Transfer',
                        default    => '',
                    };
                })
                ->addColumn('transfer_id', function ($row) {
                    $url = route('vpl.transfervp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->transfer_id . '</a>';
                })
                ->rawColumns(['status', 'transfer_id', 'transfertype'])
                ->make(true);
        }

        return view('vpl.transfervp.completed', compact('tittle', 'user'));
    }

    public function rejected(Request $request)
    {
        $tittle      = 'Rejected Transfer / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplTransfer::where('status', 'R')->get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)->where('status', 'R')->get();
            } else {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->where('status', 'R')
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('transfertype', function ($row) {
                    return match ($row->transfertype) {
                        'Transfer' => 'Transfer',
                        'ReturnTf' => 'Return Transfer',
                        default    => '',
                    };
                })
                ->addColumn('transfer_id', function ($row) {
                    $url = route('vpl.transfervp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->transfer_id . '</a>';
                })
                ->rawColumns(['status', 'transfer_id', 'transfertype'])
                ->make(true);
        }

        return view('vpl.transfervp.rejected', compact('tittle', 'user'));
    }

    public function all(Request $request)
    {
        $tittle      = 'All Transfer / Return';
        $user        = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept   = explode(',', $user->departmentid);

        if ($request->ajax()) {
            if ($user->role == 'admin') {
                $data = TrxVplTransfer::get();
            } elseif (in_array($user->groups, [18, 6, 19])) {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)->get();
            } else {
                $data = TrxVplTransfer::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)
                    ->get();
            }

            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    return $this->statusBadge($row->status);
                })
                ->addColumn('transfertype', function ($row) {
                    return match ($row->transfertype) {
                        'Transfer' => 'Transfer',
                        'ReturnTf' => 'Return Transfer',
                        default    => '',
                    };
                })
                ->addColumn('transfer_id', function ($row) {
                    $url = route('vpl.transfervp.show', $row->id);
                    return '<a href="' . $url . '" class="btn btn-block" style="background-color:#3c87e2;color:white">' . $row->transfer_id . '</a>';
                })
                ->rawColumns(['status', 'transfer_id', 'transfertype'])
                ->make(true);
        }

        return view('vpl.transfervp.all', compact('tittle', 'user'));
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
