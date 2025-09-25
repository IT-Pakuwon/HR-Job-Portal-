<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\vReceivedList;
use App\Models\vSppbjktOnProgress;
use App\Models\TrCS;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class CsListController extends Controller
{

    public function index()
    {
        // kartu ringkas (opsional): total per status created_by user login
        $u = Auth::user()->username ?? '';

        $myAll      = TrCS::where('created_by', $u)->count();
        $myProgress = TrCS::where('created_by', $u)->where('status','P')->count();
        $myRejected = TrCS::where('created_by', $u)->where('status','R')->count();
        $myCompleted= TrCS::where('created_by', $u)->where('status','C')->count();
        $all        = TrCS::count();

        return view('pages.canvass.cslist', compact('myAll','myProgress','myRejected','myCompleted','all'));
    }

    /** Builder JSON DataTables untuk TrCS */
    private function buildJsonTrCS(Request $req, $base)
    {
        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        // kolom yang bisa di-sort
        $columns = [
            0 => 'csid',
            1 => 'csdate',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'user_peminta',
            5 => 'status',
        ];
        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'csdate';

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function($q) use ($search){
                $q->where('csid', 'ilike', "%{$search}%")
                  ->orWhere('cpny_id', 'ilike', "%{$search}%")
                  ->orWhere('department_id', 'ilike', "%{$search}%")
                  ->orWhere('user_peminta', 'ilike', "%{$search}%")
                  ->orWhere('created_by', 'ilike', "%{$search}%")
                  ->orWhere('status', 'ilike', "%{$search}%")
                  ->orWhere('sppbjktid', 'ilike', "%{$search}%")
                  ->orWhereRaw("TO_CHAR(csdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
                    'id','csid','csdate','cpny_id','department_id',
                    'user_peminta','status','created_by','sppbjktid','csnote'
                )
                ->orderBy($orderCol, $orderDir)->orderBy('csid','desc')
                ->skip($start)->take($length)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    /** TAB 1: My CS (semua status) created_by = user login */
    public function jsonMy(Request $req)
    {
        $u = Auth::user()->username ?? '';
        $base = TrCS::query()->where('created_by', $u);
        return $this->buildJsonTrCS($req, $base);
    }

    /** TAB 2: Onprogress CS (status=P) created_by = user */
    public function jsonOnprogress(Request $req)
    {
        $u = Auth::user()->username ?? '';
        $base = TrCS::query()->where('created_by', $u)->where('status','P');
        return $this->buildJsonTrCS($req, $base);
    }

    /** TAB 3: Rejected CS (status=R) created_by = user */
    public function jsonRejected(Request $req)
    {
        $u = Auth::user()->username ?? '';
        $base = TrCS::query()->where('created_by', $u)->where('status','R');
        return $this->buildJsonTrCS($req, $base);
    }

    /** TAB 4: Completed CS (status=C) created_by = user */
    public function jsonCompleted(Request $req)
    {
        $u = Auth::user()->username ?? '';
        $base = TrCS::query()->where('created_by', $u)->where('status','C');
        return $this->buildJsonTrCS($req, $base);
    }

    /** TAB 5: All CS (tanpa filter) */
    public function jsonAll(Request $req)
    {
        $base = TrCS::query();
        return $this->buildJsonTrCS($req, $base);
    }

    /** Ringkasan count untuk header kartu (opsional) */
    public function counts(Request $req)
    {
        $u = Auth::user()->username ?? '';
        return response()->json([
            'myAll'       => TrCS::where('created_by', $u)->count(),
            'myProgress'  => TrCS::where('created_by', $u)->where('status','P')->count(),
            'myRejected'  => TrCS::where('created_by', $u)->where('status','R')->count(),
            'myCompleted' => TrCS::where('created_by', $u)->where('status','C')->count(),
            'all'         => TrCS::count(),
        ]);
    }






}
