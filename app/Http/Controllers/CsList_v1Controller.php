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
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\vAssignList;
use App\Models\vSppbjktOnProgress;
use App\Models\TrCS;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;



class CsListController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

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

        $columns = [
            0 => 'csid',
            1 => 'sppbjktid',
            2 => 'csdate',
            3 => 'user_peminta',
            4 => 'cpny_id',
            5 => 'department_id',
            6 => 'created_by',
            7 => 'csnote',
            8 => 'assigndate',
            9 => 'submitdate',
            10 => 'days',
        ];
        $orderIdx = (int) $req->input('order.0.column', 2);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'csdate';

        $csTable = (new TrCS)->getTable();
        $prefixExpr = "SUBSTRING({$csTable}.sppbjktid FROM 1 FOR 2)";

        if ($search !== '') {
            $base->where(function($q) use ($search, $csTable){
                $q->where($csTable.'.csid', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.sppbjktid', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.cpny_id', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.department_id', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.user_peminta', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.created_by', 'ilike', "%{$search}%")
                ->orWhere($csTable.'.csnote', 'ilike', "%{$search}%")
                ->orWhereRaw("TO_CHAR({$csTable}.csdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR({$csTable}.assigndate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR({$csTable}.submitdate,'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();
            
        $rows = $base->select(
                    $csTable.'.id',
                    $csTable.'.csid',
                    $csTable.'.sppbjktid',
                    $csTable.'.csdate',
                    $csTable.'.user_peminta',
                    $csTable.'.cpny_id',
                    $csTable.'.department_id',
                    $csTable.'.created_by',
                    $csTable.'.csnote',
                    $csTable.'.assigndate',
                    $csTable.'.submitdate',
                    DB::raw("$prefixExpr AS sppbjkt_prefix"),
                    DB::raw("(CASE
                        WHEN $prefixExpr = 'PB' THEN (SELECT id FROM tr_sppb WHERE tr_sppb.sppbid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PJ' THEN (SELECT id FROM tr_sppj WHERE tr_sppj.sppjid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PK' THEN (SELECT id FROM tr_sppk WHERE tr_sppk.sppkid = {$csTable}.sppbjktid LIMIT 1)
                        WHEN $prefixExpr = 'PT' THEN (SELECT id FROM tr_sppt WHERE tr_sppt.spptid = {$csTable}.sppbjktid LIMIT 1)
                        ELSE NULL
                    END) AS sppbjkt_src_id")
                )
                ->orderBy($orderCol === 'days' ? $csTable.'.csdate' : $orderCol, $orderDir)
                ->orderBy($csTable.'.csid', 'desc')
                ->skip($start)->take($length)
                ->get();

        // Hitung selisih hari (days) antara assigndate dan submitdate
        $rows->transform(function($r){
            $assign = $r->assigndate ? \Carbon\Carbon::parse($r->assigndate)->startOfDay() : null;
            $submit = $r->submitdate ? \Carbon\Carbon::parse($r->submitdate)->startOfDay() : null;

            $r->days = ($assign && $submit) ? $assign->diffInDays($submit) : null;

            $r->eid = \Hashids::encode($r->id);
            $r->sppbjkid_eid = \Hashids::encode($r->sppbjkt_src_id);
            return $r;
        });


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
