<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrPoLastPrice;
use App\Models\TrPO;
use App\Models\TrCS;
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

        // filter tab
        if ($tab === 'bq') {
            $base->where('inventory_type', 'BQ');
        } else {
            $base->where(function ($q) {
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
                'ponbr',
                'podate',
                'csid',
                'vendorname',
                'inventoryid',
                'inventory_descr',
                'unitcost',
                'purchaser',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('podate', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // =========================
        // ✅ Ambil ID PO dari TrPO (ponbr -> id)
        // ✅ Ambil ID CS dari TrCS (csid -> id)
        // =========================
        $ponbrs = $rows->pluck('ponbr')->filter()->unique()->values();
        $csids  = $rows->pluck('csid')->filter()->unique()->values();

        // mapping: ponbr => id
        $poMap = TrPO::query()
            ->whereIn('ponbr', $ponbrs)
            ->pluck('id', 'ponbr'); // [ponbr => id]

        // mapping: csid => id
        $csMap = TrCS::query()
            ->whereIn('csid', $csids)
            ->pluck('id', 'csid'); // [csid => id]

        // encode link id berdasarkan master table
        $rows->transform(function ($r) use ($poMap, $csMap) {
            $poId = $poMap[$r->ponbr] ?? null;
            $csId = $csMap[$r->csid] ?? null;

            $r->po_eid = $poId ? Hashids::encode((int) $poId) : null;
            $r->cs_eid = $csId ? Hashids::encode((int) $csId) : null;

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
