<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrReceipt;
use App\Models\vPoPending;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Models\TrPO;


class ReceiptListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $receiptjobs = vPoPending::when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))->count();
        $onProgress  = TrReceipt::where('created_by', $u)->where('status','P')->count();
        $completed   = TrReceipt::where('created_by', $u)->where('status','C')->count();
        $all         = TrReceipt::when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))->count();
        $rejected    = TrReceipt::where('created_by', $u)->where('status','R')->count();
        $revise      = TrReceipt::where('created_by', $u)->where('status','D')->count();

        // Return Jobs (status C + type receipt)
        $returnjobs  = TrReceipt::when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                        ->where('status','C')
                        ->where('receipttype','PR')
                        ->count();
        

        return view('pages.receipt.receiptlist', compact(
            'receiptjobs','onProgress','completed','all','returnjobs','rejected','revise'
        ));
    }


    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'receiptjobs'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        if ($scope === 'receiptjobs') {
            $base = vPoPending::with('creator')
                ->when($cpny_id, fn($q)=>$q->where('cpny_id', $cpny_id))
                ->select([
                    'id','ponbr','podate','cpny_id','vendorname',
                    'podeliverydate','created_by','status'
                ]);

            $orderColumns = [
            0=>'ponbr', 1=>'ponbr', 2=>'podate', 3=>'cpny_id',
            4=>'vendorname', 5=>'podeliverydate', 6=>'created_by', 7=>'status'
            ];


            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('ponbr','ilike',"%{$search}%")
                    ->orWhere('cpny_id','ilike',"%{$search}%")
                    ->orWhere('vendorname','ilike',"%{$search}%")
                    ->orWhere('created_by','ilike',"%{$search}%")
                    ->orWhereRaw("TO_CHAR(podate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(podeliverydate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        } else {
            // Semua scope selain 'receiptjobs' → dari TrReceipt
            $base = TrReceipt::query()
                ->when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                ->when($scope==='onprogress', fn($q)=>$q->where('created_by',$u)->where('status','P'))
                ->when($scope==='completed',  fn($q)=>$q->where('created_by',$u)->where('status','C'))
                ->when($scope==='returnjobs', fn($q)=>$q->where('status','C')->where('receipttype','PR'))
                ->when($scope==='rejected',   fn($q)=>$q->where('created_by',$u)->where('status','R'))
                ->when($scope==='revise',     fn($q)=>$q->where('created_by',$u)->where('status','D'))
                ->select(['id','receiptnbr','receiptdate','receipttype','ponbr','sppbjktid','cpny_id','created_by','status']);

            $orderColumns = [
                0=>'receiptnbr',
                1=>'receiptdate',
                2=>'receipttype', 
                3=>'ponbr',
                4=>'sppbjktid',
                5=>'cpny_id',
                6=>'created_by'
            ];

            if ($search !== '') {
                $needle = strtolower($search);
                $base->where(function($q) use ($search, $needle){
                    $q->where('receiptnbr','ilike',"%{$search}%")
                    ->orWhere('ponbr','ilike',"%{$search}%")
                    ->orWhere('sppbjktid','ilike',"%{$search}%")
                    ->orWhere('cpny_id','ilike',"%{$search}%")
                    ->orWhere('created_by','ilike',"%{$search}%")
                    ->orWhereRaw("TO_CHAR(receiptdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);

                    // biar user bisa cari pakai teks
                    if (strpos($needle, 'purchase') !== false || strpos($needle, 'pr') !== false) {
                        $q->orWhere('receipttype', 'PR');
                    }
                    if (strpos($needle, 'return') !== false || strpos($needle, 'rr') !== false) {
                        $q->orWhere('receipttype', 'RR');
                    }
                });
            }
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', ($scope==='receiptjobs'? 2 : 1));
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope==='receiptjobs' ? 'podate' : 'receiptdate');

        $rows = $base->orderBy($orderCol, $orderDir)
                    ->orderBy($scope==='receiptjobs' ? 'ponbr' : 'receiptnbr','desc')
                    ->skip($start)->take($length)
                    ->get();

        // ========= ENRICH / FORMAT =========
        $poIdMap = [];
        if ($scope !== 'receiptjobs' && $rows->count()) {
            $ponbrs = $rows->pluck('ponbr')->filter()->unique()->values()->all();

            // a) coba dari vPoPending (PO status P/O)
            $poIdMap = vPoPending::whereIn('ponbr', $ponbrs)->pluck('id','ponbr')->toArray();

            // b) fallback ke tabel tr_po (semua status)
            $missing = array_values(array_diff($ponbrs, array_keys($poIdMap)));
            if (!empty($missing)) {
                $fallback = TrPo::whereIn('ponbr', $missing)->pluck('id','ponbr')->toArray();
                $poIdMap = $poIdMap + $fallback;
            }
        }

        $rows->transform(function($r) use ($scope, $poIdMap) {
            if ($scope === 'receiptjobs') {
                $r->podate_fmt     = $r->podate        ? Carbon::parse($r->podate)->format('Y-m-d')        : null;
                $r->podelivery_fmt = $r->podeliverydate? Carbon::parse($r->podeliverydate)->format('Y-m-d'): null;
                $r->ponbr_eid      = Hashids::encode((string)$r->id);
            } else {
                $r->receiptdate_fmt = $r->receiptdate ? Carbon::parse($r->receiptdate)->format('Y-m-d') : null;
                $r->receiptnbr_eid  = Hashids::encode((string)$r->id);

                // map type ke label ramah
                $t = strtoupper((string)$r->receipttype);
                $r->receipttype = $t === 'RR' ? 'Return Receipt' : ($t === 'PR' ? 'Purchase Receipt' : $r->receipttype);

                // 🔗 PO link (via PONBR)
                $poId = $poIdMap[$r->ponbr] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string)$poId) : null;

                // 🔗 SPPB/J/K/T link
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

            // ===== status badge (untuk UI) =====
            $st = strtoupper((string)($r->status ?? ''));

            $statusText = 'Unknown';
            $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';

            switch ($st) {
                case 'P':
                    $statusText  = 'Pending';
                    $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                    break;

                case 'A':
                    $statusText  = 'Approved';
                    $statusClass = 'bg-green-100 text-green-700 border-green-200';
                    break;

                case 'R':
                    $statusText  = 'Rejected';
                    $statusClass = 'bg-red-100 text-red-700 border-red-200';
                    break;

                case 'C':
                    $statusText  = 'Completed';
                    $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    break;

                case 'D':
                    $statusText  = 'Revise';
                    $statusClass = 'bg-blue-100 text-blue-700 border-blue-200';
                    break;

                case 'X':
                    $statusText  = 'Canceled';
                    $statusClass = 'bg-gray-200 text-gray-700 border-gray-300';
                    break;

                default:
                    // kalau status ada tapi bukan P/A/R/C/D/X → tampilkan raw nya
                    $statusText = $st !== '' ? $st : 'Unknown';
                    $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';
                    break;
            }

            $r->status_label = $statusText;
            $r->status_class = $statusClass;

            // ================================


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
