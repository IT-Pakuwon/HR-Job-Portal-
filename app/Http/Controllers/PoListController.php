<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrPO;
use App\Models\SysUserRole; // <-- tambahkan
use Vinkla\Hashids\Facades\Hashids;

class PoListController extends Controller
{
    /** Halaman index + kartu ringkas */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        // company list bisa "AW" atau "AW,GPS"
        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // cek role FINACCESS
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        // helper filter company
        $filterCompany = function ($q) use ($cpnyList) {
            if (!empty($cpnyList)) {
                $q->whereIn('cpny_id', $cpnyList);
            }
        };

        // helper filter created_by hanya untuk non-FINACCESS
        $filterCreator = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        // Hitung per status (sesuai company user, created_by hanya kalau bukan FINACCESS)
        $hold = TrPO::where('status', 'H')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $purchase = TrPO::where('status', 'P')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $partial = TrPO::where('status', 'O')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $completed = TrPO::where('status', 'C')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $cancel = TrPO::where('status', 'X')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        $reuse = TrPO::where('status', 'R')
            ->where($filterCompany)
            ->where($filterCreator)
            ->count();

        // "All" → semua PO di company user, tanpa filter created_by
        $all = TrPO::when(!empty($cpnyList), fn($q) => $q->whereIn('cpny_id', $cpnyList))
            ->count();

        return view('pages.purchase.polist', compact(
            'hold', 'purchase', 'partial', 'completed', 'cancel', 'reuse', 'all'
        ));
    }

    /** DataTables server-side */
    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'purchase')); // default: Purchase Order (P)

        $user = Auth::user();
        $u    = $user->username ?? '';

        // company list
        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // FINACCESS?
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $base = TrPO::query();

        // selalu filter by company user
        if (!empty($cpnyList)) {
            $base->whereIn('cpny_id', $cpnyList);
        }

        // helper creator filter
        $applyCreatorFilter = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        // Scope → filter
        switch ($scope) {
            case 'hold':
                $base->where('status', 'H')->where($applyCreatorFilter);
                break;

            case 'purchase':
                $base->where('status', 'P')->where($applyCreatorFilter);
                break;

            case 'partial':
                $base->where('status', 'O')->where($applyCreatorFilter);
                break;

            case 'completed':
                $base->where('status', 'C')->where($applyCreatorFilter);
                break;

            case 'cancel':
                $base->where('status', 'X')->where($applyCreatorFilter);
                break;

            case 'reuse':
                $base->where('status', 'R')->where($applyCreatorFilter);
                break;

            case 'all':
                // hanya company filter, tidak filter created_by
                break;

            default:
                // default balik ke purchase
                $base->where('status', 'P')->where($applyCreatorFilter);
                break;
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
            2 => "$poTable.potype",
            3 => "$poTable.vendorname",
            4 => "$poTable.podeliverydate",
            5 => "$poTable.totalamt",
            6 => "$poTable.taxamt",
            7 => "$poTable.grandtotalamt",
            8 => "$poTable.created_by",
        ];

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? "$poTable.podate";

        if ($search !== '') {
            $base->where(function ($q) use ($search, $poTable) {
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
                "$poTable.potype",
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

        $rows->transform(function ($r) {
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
