<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrPoLastPrice;
use Vinkla\Hashids\Facades\Hashids;

class LastOrderController extends Controller
{
    public function index()
    {
        return view('pages.canvass.lastorder');
    }

    public function inventoryJson(Request $request)
    {
        return $this->datatableJson($request, 'inventory');
    }

    public function bqJson(Request $request)
    {
        return $this->datatableJson($request, 'bq');
    }

    private function datatableJson(Request $request, string $tab)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        // order mapping sesuai kolom yang ditampilkan
        $columns = [
            0 => 'ponbr',
            1 => 'podate',
            2 => 'csid',
            3 => 'vendorname',
            4 => 'inventoryid',
            5 => 'inventory_descr',
            6 => 'unitcost',
            7 => 'purchaser',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'podate';

        $base = TrPoLastPrice::query();

        // ✅ filter tab
        // asumsi: inventory_type = 'BQ' untuk tab BQ
        // kalau data Anda pakai nilai lain (misal 'BILLQTY', dll), tinggal sesuaikan di sini.
        if ($tab === 'bq') {
            $base->where('inventory_type', 'BQ');
        } else {
            $base->where(function($q){
                $q->whereNull('inventory_type')
                  ->orWhere('inventory_type', '<>', 'BQ');
            });
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('ponbr', 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST(podate AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(csid AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('vendorname', 'ilike', "%{$search}%")
                  ->orWhere('inventoryid', 'ilike', "%{$search}%")
                  ->orWhere('inventory_descr', 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST(unitcost AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('purchaser', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base->select([
                'id', // kalau ada
                'ponbr',
                'podate',
                'csid',
                'vendorname',
                'inventoryid',
                'inventory_descr',
                'unitcost',
                'purchaser',
                'sppbjktid', // jaga-jaga kalau dipakai sebagai id PO
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('podate', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // encode link id
        $rows->transform(function ($r) {
            // csid -> encode jika numeric
            $r->cs_eid = is_numeric($r->csid) ? Hashids::encode((int)$r->csid) : null;

            // po eid: prioritas id -> sppbjktid (kalau numeric) -> null
            $poKey = null;
            if (isset($r->id) && is_numeric($r->id)) $poKey = (int)$r->id;
            elseif (isset($r->sppbjktid) && is_numeric($r->sppbjktid)) $poKey = (int)$r->sppbjktid;

            $r->po_eid = $poKey ? Hashids::encode($poKey) : null;

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
