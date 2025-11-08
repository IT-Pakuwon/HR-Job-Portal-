<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrBast;
use App\Models\TrPOterm;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Models\TrPo;

class BastListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        // Jobs berasal dari TrPOterm
        $bastjobs   = TrPOterm::when($cpny_id, fn($q) => $q->where('cpny_id',$cpny_id))
            ->whereNull('bastid')
            ->count();

        // BAST stats (tanpa basttype, tanpa returnjobs)
        $onProgress = TrBast::where('created_by', $u)->where('status','P')->count();
        $completed  = TrBast::where('created_by', $u)->where('status','C')->count();
        $rejected   = TrBast::where('created_by', $u)->where('status','R')->count();
        $revise     = TrBast::where('created_by', $u)->where('status','D')->count();
        $all        = TrBast::when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))->count();

        return view('pages.bast.bastlist', compact(
            'bastjobs','onProgress','completed','all','rejected','revise'
        ));
    }

    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'bastjobs'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        if ($scope === 'bastjobs') {
            // Sumber list “jobs” dari TrPOterm
            $base = TrPOterm::query()
                ->when($cpny_id, fn($q)=>$q->where('cpny_id', $cpny_id))
                ->whereNull('bastid')
                ->select([
                    'id','ponbr','cpny_id','vendorname','created_by',
                    'terms_name','progress_pct','payment_pct'
                ]);

            $orderColumns = [
                0=>'ponbr',      // Action (dummy, abaikan)
                1=>'ponbr',
                2=>'cpny_id',
                3=>'vendorname',
                4=>'terms_name',
                5=>'progress_pct',
                6=>'payment_pct',
                7=>'created_by',
            ];

            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('ponbr','ilike',"%{$search}%")
                      ->orWhere('cpny_id','ilike',"%{$search}%")
                      ->orWhere('vendorname','ilike',"%{$search}%")
                      ->orWhere('created_by','ilike',"%{$search}%")
                      ->orWhere('terms_name','ilike',"%{$search}%");
                });
            }
        } else {
            // Semua scope selain 'bastjobs' → dari TrBast
            $base = TrBast::query()
                ->when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                ->when($scope==='onprogress', fn($q)=>$q->where('created_by',$u)->where('status','P'))
                ->when($scope==='completed',  fn($q)=>$q->where('created_by',$u)->where('status','C'))
                ->when($scope==='rejected',   fn($q)=>$q->where('created_by',$u)->where('status','R'))
                ->when($scope==='revise',     fn($q)=>$q->where('created_by',$u)->where('status','D'))
                ->select(['id','bastid','bastdate','ponbr','sppbjktid','cpny_id','created_by','status']);

            $orderColumns = [
                0=>'bastid',
                1=>'bastdate',
                2=>'ponbr',
                3=>'sppbjktid',
                4=>'cpny_id',
                5=>'created_by'
            ];

            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('bastid','ilike',"%{$search}%")
                      ->orWhere('ponbr','ilike',"%{$search}%")
                      ->orWhere('sppbjktid','ilike',"%{$search}%")
                      ->orWhere('cpny_id','ilike',"%{$search}%")
                      ->orWhere('created_by','ilike',"%{$search}%")
                      ->orWhereRaw("TO_CHAR(bastdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', ($scope==='bastjobs'? 1 : 1));
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope==='bastjobs' ? 'ponbr' : 'bastdate');

        $rows = $base->orderBy($orderCol, $orderDir)
                     ->orderBy($scope==='bastjobs' ? 'ponbr' : 'bastid','desc')
                     ->skip($start)->take($length)
                     ->get();

        // ========= ENRICH / FORMAT =========
        // Map PONBR -> id (TrPo) supaya link PO bisa dipakai
        $poIdMap = [];
        $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();
        if (!empty($ponbrsForMap)) {
            $poIdMap = TrPo::whereIn('ponbr', $ponbrsForMap)->pluck('id','ponbr')->toArray();
        }

        $rows->transform(function($r) use ($scope, $poIdMap) {
            if ($scope === 'bastjobs') {
                // Hash untuk ID dari table TrPOterm (id term)
                $r->term_eid = Hashids::encode((string)$r->id);

                // tetap mapping PO untuk link show PO
                $poId = $poIdMap[$r->ponbr] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string)$poId) : null;
            } else {
                $r->bastdate_fmt = $r->bastdate ? Carbon::parse($r->bastdate)->format('Y-m-d') : null;
                $r->bastid_eid   = Hashids::encode((string)$r->id);

                // 🔗 PO link via PONBR
                $poId = $poIdMap[$r->ponbr] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string)$poId) : null;

                // 🔗 SPPB/J/K/T link (berdasar prefix)
                $r->sppb_route = null;
                $r->sppb_eid   = null;
                if (!empty($r->sppbjktid)) {
                    $prefix   = Str::upper(Str::substr($r->sppbjktid, 0, 2));
                    $routeMap = ['PB'=>'showsppbs','PJ'=>'showsppjs','PK'=>'showsppks','PT'=>'showsppts'];
                    if (isset($routeMap[$prefix])) {
                        if ($prefix === 'PB')      { $id = TrSPPB::where('sppbid', $r->sppbjktid)->value('id'); }
                        elseif ($prefix === 'PJ') { $id = TrSPPJ::where('sppjid', $r->sppbjktid)->value('id'); }
                        elseif ($prefix === 'PK') { $id = TrSPPK::where('sppkid', $r->sppbjktid)->value('id'); }
                        else /* PT */             { $id = TrSPPT::where('spptid', $r->sppbjktid)->value('id'); }

                        if ($id) {
                            $r->sppb_route = $routeMap[$prefix];
                            $r->sppb_eid   = Hashids::encode((string)$id);
                        }
                    }
                }
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
