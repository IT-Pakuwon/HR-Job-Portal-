<?php

namespace App\Http\Controllers;

use App\Models\SysUserRole;
use App\Models\TrPO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- tambahkan
use Vinkla\Hashids\Facades\Hashids;

class PoListController extends Controller
{
    /** Halaman index + kartu ringkas */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $u = $user->username ?? '';

        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw)))) : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        // kirim list company untuk dropdown + login user (untuk hidden creator kalau non-fin)
        return view('pages.purchase.polist', [
            'companies' => $cpnyList,
            'isFinanceAccess' => $isFinanceAccess,
            'loginUser' => $u,
        ]);
    }

    /** DataTables server-side */
    public function json(Request $req)
    {
        $user = Auth::user();
        $u = $user->username ?? '';

        // ✅ GET PARAMS (ONLY DEFINE ONCE)
        $tab = strtolower((string) $req->query('tab', 'my')); // my | all
        $status = strtoupper(trim((string) $req->query('status', '')));
        $company = strtoupper(trim((string) $req->query('company', '')));
        $creator = trim((string) $req->query('creator', ''));

        // ===== company list user =====
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw))))
            : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $base = TrPO::query();

        // ===== filter company user =====
        if (!empty($cpnyList)) {
            $base->whereIn('cpny_id', $cpnyList);
        }

        // ===== filter company dropdown =====
        if ($company !== '' && in_array($company, $cpnyList, true)) {
            $base->where('cpny_id', $company);
        }

        // ===== TAB filter =====
        if ($tab === 'my') {
            // ⚠️ IMPORTANT: use username (NOT id)
            $base->where('created_by', $user->username);
        }

        // ===== STATUS filter =====
        if ($status !== '') {
            if ($status === 'P_T') {
                $base->where('status', 'P')
                     ->where('send_email', true);
            } elseif ($status === 'P_F') {
                $base->where('status', 'P')
                     ->where('send_email', false);
            } else {
                $base->where('status', $status);
            }
        }

        return $this->buildJsonTrPO($req, $base);
    }

    /** Builder hasil JSON */
    private function buildJsonTrPO(Request $req, $base)
    {
        $draw = (int) $req->input('draw', 1);
        $start = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $poTable = (new TrPO())->getTable(); // ex: "tr_po"

        // Urutan kolom sesuai permintaan
        $columns = [
            0 => "$poTable.ponbr",
            1 => "$poTable.csid",
            2 => "$poTable.sppbjktid",
            3 => "$poTable.podate",
            4 => "$poTable.cpny_id",
            5 => "$poTable.potype",
            6 => "$poTable.vendorname",
            7 => "$poTable.podeliverydate",
            8 => "$poTable.keperluan",
            9 => "$poTable.totalamt",
            10 => "$poTable.taxamt",
            11 => "$poTable.grandtotalamt",
            12 => "$poTable.created_by",
            13 => "$poTable.status",
        ];

        // $orderIdx = (int) $req->input('order.0.column', 1);
        // $orderDir = $req->input('ordFORCE order by podate DESC
        $orderCol = "$poTable.podate";
        $orderDir = 'desc';
        // $orderCol = $columns[$orderIdx] ?? "$poTable.podate";

        if ($search !== '') {
            $base->where(function ($q) use ($search, $poTable) {
                $q->where("$poTable.ponbr", 'ilike', "%{$search}%")
                    ->orWhere("$poTable.csid", 'ilike', "%{$search}%")
                    ->orWhere("$poTable.potype", 'ilike', "%{$search}%")
                    ->orWhere("$poTable.vendorname", 'ilike', "%{$search}%")
                    ->orWhere("$poTable.created_by", 'ilike', "%{$search}%")
                    ->orWhere("$poTable.keperluan", 'ilike', "%{$search}%")
                    ->orWhereRaw("CAST($poTable.cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])

                    // format database lama
                    ->orWhereRaw("TO_CHAR($poTable.podate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR($poTable.podeliverydate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])

                    // format tampilan view: contoh 16/4/2026
                    ->orWhereRaw("TO_CHAR($poTable.podate,'FMDD/FMMM/YYYY') ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR($poTable.podeliverydate,'FMDD/FMMM/YYYY') ILIKE ?", ["%{$search}%"])

                    ->orWhereRaw("CAST($poTable.totalamt AS TEXT) ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CAST($poTable.taxamt AS TEXT) ILIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CAST($poTable.grandtotalamt AS TEXT) ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
            "$poTable.id",
            "$poTable.ponbr",
            "$poTable.csid",
            "$poTable.sppbjktid",
            "$poTable.podate",
            "$poTable.cpny_id",          // ✅ NEW
            "$poTable.potype",
            "$poTable.vendorname",
            "$poTable.podeliverydate",
            "$poTable.keperluan",
            "$poTable.totalamt",
            "$poTable.taxamt",
            "$poTable.grandtotalamt",
            "$poTable.created_by",
            "$poTable.status",
            "$poTable.send_email"
        )
        ->orderBy($orderCol, $orderDir)
        ->orderBy("$poTable.ponbr", 'desc')
        ->skip($start)->take($length)
        ->get();

        $rows->transform(function ($r) {
            $r->eid = Hashids::encode($r->id);

            $st = strtoupper((string) ($r->status ?? ''));

            // default
            $statusText = $st !== '' ? $st : 'Unknown';
            $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';

            // mapping status PO: H/P/O/C/X/R
            switch ($st) {
                case 'H':
                    $statusText = 'Unsend';
                    $statusClass = 'bg-blue-100 text-blue-700 border-blue-200';
                    break;
                case 'P':
                    if ($r->send_email === false) {
                        $statusText = 'Purchase - Unsend Email';
                        $statusClass = 'bg-orange-100 text-orange-700 border-orange-200';
                    } else {
                        $statusText = 'Purchase';
                        $statusClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300 border-yellow-200';
                    }
                    break;
                case 'O':
                    $statusText = 'Partial';
                    $statusClass = 'bg-amber-200/60 text-amber-800 border border-amber-600/40';
                    // no break
                case 'C':
                    $statusText = 'Completed';
                    $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    break;
                case 'X':
                    $statusText = 'Canceled';
                    $statusClass = 'bg-red-200/60 text-red-800 border border-red-600/40';
                    break;
                case 'D':
                    $statusText = 'Reuse';
                    $statusClass = 'bg-gray-200 text-gray-700 border-gray-300';
                    break;
            }

            $r->status_label = $statusText;
            $r->status_class = $statusClass;

            unset($r->id);

            return $r;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
