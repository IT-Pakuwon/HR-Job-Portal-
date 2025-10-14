<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrReceipt;
use App\Models\vPoPending;
use Vinkla\Hashids\Facades\Hashids;

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

        return view('pages.receipt.receiptlist', compact('receiptjobs','onProgress','completed','all'));
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
            // DARI VIEW v_po_pending (tetap seperti sebelumnya)
            $base = vPoPending::with('creator')
                ->when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                ->select([
                    'ponbr',
                    'podate',
                    'cpny_id',
                    'vendorname',
                    'podeliverydate',
                    'created_by',
                ]);

            // mapping kolom utk ordering di front-end "receiptjobs"
            $orderColumns = [
                0 => 'ponbr',          // tombol +
                1 => 'ponbr',
                2 => 'podate',
                3 => 'cpny_id',
                4 => 'vendorname',
                5 => 'podeliverydate',
                6 => 'created_by',     // created_by_name bukan kolom DB
            ];

            // searching receiptjobs
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
            // === DARI TrReceipt: kirim 6 kolom sesuai request ===
            $base = TrReceipt::query()
                ->when($cpny_id, fn($q)=>$q->where('cpny_id',$cpny_id))
                ->when($scope==='onprogress', fn($q)=>$q->where('created_by',$u)->where('status','P'))
                ->when($scope==='completed',  fn($q)=>$q->where('created_by',$u)->where('status','C'))
                ->select([
                    'receiptnbr',
                    'receiptdate',
                    'ponbr',
                    'sppbjktid',
                    'cpny_id',
                    'created_by',
                ]);

            // mapping kolom utk ordering di front-end "TrReceipt*"
            // (0 adalah tombol "+")
            $orderColumns = [
                0 => 'receiptnbr',
                1 => 'receiptnbr',
                2 => 'receiptdate',
                3 => 'ponbr',
                4 => 'sppbjktid',
                5 => 'cpny_id',
                6 => 'created_by',
            ];

            // searching TrReceipt
            if ($search !== '') {
                $base->where(function($q) use ($search){
                    $q->where('receiptnbr','ilike',"%{$search}%")
                    ->orWhere('ponbr','ilike',"%{$search}%")
                    ->orWhere('sppbjktid','ilike',"%{$search}%")
                    ->orWhere('cpny_id','ilike',"%{$search}%")
                    ->orWhere('created_by','ilike',"%{$search}%")
                    ->orWhereRaw("TO_CHAR(receiptdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', 2);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope==='receiptjobs' ? 'podate' : 'receiptdate');

        $rows = $base->orderBy($orderCol, $orderDir)
                    ->orderBy($scope==='receiptjobs' ? 'ponbr' : 'receiptnbr','desc')
                    ->skip($start)->take($length)
                    ->get();

        // format tanggal untuk frontend
        $rows->transform(function($r) use ($scope){
            if ($scope === 'receiptjobs') {
                $r->podate_fmt     = !empty($r->podate) ? \Carbon\Carbon::parse($r->podate)->format('Y-m-d') : null;
                $r->podelivery_fmt = !empty($r->podeliverydate) ? \Carbon\Carbon::parse($r->podeliverydate)->format('Y-m-d') : null;
                // created_by_name dari accessor (vPoPending::$appends)

                $r->ponbr_eid       = Hashids::encode((string)$r->ponbr);
            } else {
                $r->receiptdate_fmt = !empty($r->receiptdate) ? \Carbon\Carbon::parse($r->receiptdate)->format('Y-m-d') : null;

                $r->ponbr_eid       = Hashids::encode((string)$r->ponbr);
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
