<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrPO;
use Vinkla\Hashids\Facades\Hashids;

class PoListController extends Controller
{
    /** Halaman index + kartu ringkas */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $u = $user->username ?? '';

        // Hitung per status (created_by untuk 6 status pertama)
        $hold      = TrPO::where('created_by', $u)->where('status','H')->count();
        $purchase  = TrPO::where('created_by', $u)->where('status','P')->count();
        $partial   = TrPO::where('created_by', $u)->where('status','O')->count();
        $completed = TrPO::where('created_by', $u)->where('status','C')->count();
        $cancel    = TrPO::where('created_by', $u)->where('status','X')->count();
        $reuse     = TrPO::where('created_by', $u)->where('status','R')->count();

        // "All" tanpa filter status & tanpa filter created_by
        $all = TrPO::count();

        return view('pages.purchase.polist', compact(
            'hold','purchase','partial','completed','cancel','reuse','all'
        ));
    }

    /** DataTables server-side */
    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'purchase')); // default: Purchase Order (P)
        $u = Auth::user()->username ?? '';

        $base = TrPO::query();

        // Scope → filter (6 status pertama ikut created_by)
        switch ($scope) {
            case 'hold':       $base->where('created_by', $u)->where('status','H'); break;
            case 'purchase':   $base->where('created_by', $u)->where('status','P'); break;
            case 'partial':    $base->where('created_by', $u)->where('status','O'); break;
            case 'completed':  $base->where('created_by', $u)->where('status','C'); break;
            case 'cancel':     $base->where('created_by', $u)->where('status','X'); break;
            case 'reuse':      $base->where('created_by', $u)->where('status','R'); break;
            case 'all':        /* no filter */ break;
            default:           $base->where('created_by', $u)->where('status','P'); break;
        }

        return $this->buildJsonTrPO($req, $base);
    }

    /** Builder hasil JSON */
    private function buildJsonTrPO(Request $req, $base)
    {
        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $poTable = (new TrPO)->getTable(); // ex: "tr_po"

        // Urutan kolom sesuai permintaan
        $columns = [
            0 => "$poTable.ponbr",
            1 => "$poTable.podate",
            2 => "$poTable.vendorname",
            3 => "$poTable.podeliverydate",
            4 => "$poTable.totalamt",
            5 => "$poTable.taxamt",
            6 => "$poTable.grandtotalamt",
            7 => "$poTable.created_by",
        ];

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? "$poTable.podate";

        if ($search !== '') {
            $base->where(function($q) use ($search, $poTable){
                $q->where("$poTable.ponbr", 'ilike', "%{$search}%")
                  ->orWhere("$poTable.vendorname", 'ilike', "%{$search}%")
                  ->orWhere("$poTable.created_by", 'ilike', "%{$search}%")
                  ->orWhereRaw("TO_CHAR($poTable.podate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR($poTable.podeliverydate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST($poTable.totalamt AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST($poTable.taxamt AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST($poTable.grandtotalamt AS TEXT) ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
                    "$poTable.id",
                    "$poTable.ponbr",
                    "$poTable.podate",
                    "$poTable.vendorname",
                    "$poTable.podeliverydate",
                    "$poTable.totalamt",
                    "$poTable.taxamt",
                    "$poTable.grandtotalamt",
                    "$poTable.created_by",
                    "$poTable.status"
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy("$poTable.ponbr", 'desc')
                ->skip($start)->take($length)
                ->get();

        $rows->transform(function($r){
            $r->eid = Hashids::encode($r->id);
            unset($r->id);
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
