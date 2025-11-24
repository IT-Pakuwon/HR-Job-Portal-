<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrCalr;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Models\TrPO;
use App\Models\TrRfca;
use App\Models\TrRfcaStep;

class CalrListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        // 🔁 Calr Jobs sekarang dari TrRfca + TrRfcaStep (calr_gen = 't') dan BELUM punya CALR
        $calrjobs = TrRfca::query()
            ->join('tr_rfca_step as s', function ($q) {
                $q->on('s.rfcaid', 'tr_rfca.rfcaid')
                ->on('s.ponbr',  'tr_rfca.ponbr');
            })
            ->leftJoin('tr_calr as c', function ($q) {
                $q->on('c.rfcaid', 'tr_rfca.rfcaid')
                ->on('c.ponbr',  'tr_rfca.ponbr');
            })
            ->when($cpny_id, fn($q) => $q->where('tr_rfca.cpny_id', $cpny_id))
            ->where('s.calr_gen', true)      // hanya step yang generate CALR
            ->where('s.status_rfca', 'C')
            ->whereNull('c.calrid')         // belum ada CALR
            ->count();

        // Stat CALR existing tetap dari TrCalr
        $onProgress = TrCalr::where('created_by', $u)->where('status','P')->count();
        $completed  = TrCalr::where('created_by', $u)->where('status','C')->count();
        $rejected   = TrCalr::where('created_by', $u)->where('status','R')->count();
        $revise     = TrCalr::where('created_by', $u)->where('status','D')->count();
        $all        = TrCalr::when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))->count();

        return view('pages.calr.calrlist', compact(
            'calrjobs','onProgress','completed','all','rejected','revise'
        ));
    }


    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'calrjobs'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        if ($scope === 'calrjobs') {
            // 🔁 JOBS CALR: dari TrRfca + TrRfcaStep (calr_gen = 't'), belum punya CALR
            $base = TrRfca::query()
                ->join('tr_rfca_step as s', function ($q) {
                    $q->on('s.rfcaid', 'tr_rfca.rfcaid')
                    ->on('s.ponbr',  'tr_rfca.ponbr');
                })
                ->leftJoin('tr_calr as c', function ($q) {
                    $q->on('c.rfcaid', 'tr_rfca.rfcaid')
                    ->on('c.ponbr',  'tr_rfca.ponbr');
                })
                ->when($cpny_id, fn($q)=>$q->where('tr_rfca.cpny_id', $cpny_id))
                ->where('s.calr_gen', 't')
                ->where('s.status_rfca', 'C')
                ->whereNull('c.calrid')
                ->select([
                    'tr_rfca.id',
                    'tr_rfca.rfcaid',
                    'tr_rfca.ponbr',
                    'tr_rfca.cpny_id',
                    'tr_rfca.vendorname',
                    'tr_rfca.created_by',
                    's.rfca_step_descr',
                    's.rfca_type',
                ]);

            $orderColumns = [
                0 => 'rfcaid',          // Action (dummy)
                1 => 'rfcaid',
                2 => 'ponbr',
                3 => 'cpny_id',
                4 => 'vendorname',
                5 => 'rfca_step_descr',
                6 => 'rfca_type',
                7 => 'created_by',
            ];

            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('tr_rfca.rfcaid','ilike',"%{$search}%")
                    ->orWhere('tr_rfca.ponbr','ilike',"%{$search}%")
                    ->orWhere('tr_rfca.cpny_id','ilike',"%{$search}%")
                    ->orWhere('tr_rfca.vendorname','ilike',"%{$search}%")
                    ->orWhere('tr_rfca.created_by','ilike',"%{$search}%")
                    ->orWhere('s.rfca_step_descr','ilike',"%{$search}%")
                    ->orWhere('s.rfca_type','ilike',"%{$search}%");
                });
            }
        } else {
            // Semua scope selain 'calrjobs' → dari TrCalr
            $base = TrCalr::query()
                ->when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                ->when($scope==='onprogress', fn($q)=>$q->where('created_by',$u)->where('status','P'))
                ->when($scope==='completed',  fn($q)=>$q->where('created_by',$u)->where('status','C'))
                ->when($scope==='rejected',   fn($q)=>$q->where('created_by',$u)->where('status','R'))
                ->when($scope==='revise',     fn($q)=>$q->where('created_by',$u)->where('status','D'))
                ->select([
                    'id',
                    'calrid',
                    'calrdate',
                    'rfcaid',
                    'csid',
                    'cpny_id',
                    'vendorname',
                    'created_by',
                    'status',
                ]);

            $orderColumns = [
                0 => 'calrid',
                1 => 'calrdate',
                2 => 'rfcaid',
                3 => 'csid',
                4 => 'cpny_id',
                5 => 'vendorname',
                6 => 'created_by',
                7 => 'status',
            ];

            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('calrid','ilike',"%{$search}%")
                    ->orWhere('rfcaid','ilike',"%{$search}%")
                    ->orWhere('csid','ilike',"%{$search}%")
                    ->orWhere('cpny_id','ilike',"%{$search}%")
                    ->orWhere('vendorname','ilike',"%{$search}%")
                    ->orWhere('created_by','ilike',"%{$search}%")
                    ->orWhereRaw("TO_CHAR(calrdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        }


        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', ($scope==='calrjobs'? 1 : 1));
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope==='calrjobs' ? 'rfcaid' : 'calrdate');

        $rows = $base->orderBy($orderCol, $orderDir)
                    ->orderBy($scope==='calrjobs' ? 'rfcaid' : 'calrid','desc')
                    ->skip($start)->take($length)
                    ->get();

        // ========= ENRICH / FORMAT =========
        $poIdMap = [];
        $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();
        if (!empty($ponbrsForMap)) {
            $poIdMap = TrPo::whereIn('ponbr', $ponbrsForMap)->pluck('id','ponbr')->toArray();
        }

        // Map RFCAID -> id (TrRfca) supaya bisa buat link showrfca
        $rfcaMap = [];
        $rfcaIdsForMap = $rows->pluck('rfcaid')->filter()->unique()->values()->all();
        if (!empty($rfcaIdsForMap)) {
            $rfcaMap = TrRfca::whereIn('rfcaid', $rfcaIdsForMap)
                ->pluck('id', 'rfcaid')
                ->toArray();
        }


        
        $rows->transform(function($r) use ($scope, $poIdMap) {
            if ($scope === 'calrjobs') {
                // Hash untuk ID RFCA (supaya bisa dipakai ke create CALR)
                $r->rfca_eid = Hashids::encode((string)$r->id);

                // map PONBR → id PO
                $poId = $poIdMap[$r->ponbr] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string)$poId) : null;
            } else {
                $r->calrdate_fmt = $r->calrdate
                    ? Carbon::parse($r->calrdate)->format('Y-m-d')
                    : null;
                $r->calrid_eid = Hashids::encode((string)$r->id);

                // link RFCA
                $rfcaId = $rfcaMap[$r->rfcaid] ?? null;
                $r->rfca_eid = $rfcaId ? Hashids::encode((string)$rfcaId) : null;

                return $r;
            }
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

}
