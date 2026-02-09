<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

use App\Models\TrPoLastPrice;
use App\Models\TrPO;
use App\Models\TrCS;
use App\Models\ViewLastorderBq;
use App\Models\TrBQCS;

class LastOrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('pages.canvass.lastorder');
    }

    public function inventoryJson(Request $request)
    {
        return $this->datatableJsonInventory($request);
    }

    public function bqJson(Request $request)
    {
        return $this->datatableJsonBQ($request);
    }

    /**
     * =========================
     * INVENTORY (NON BQ)
     * =========================
     */
    private function datatableJsonInventory(Request $request)
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

        $base = TrPoLastPrice::query()
            ->where(function ($q) {
                $q->whereNull('inventory_type')
                  ->orWhere('inventory_type', '<>', 'BQ');
            });

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

        // mapping PO & CS -> hashid
        $ponbrs = $rows->pluck('ponbr')->filter()->unique();
        $csids  = $rows->pluck('csid')->filter()->unique();

        $poMap = TrPO::on('pgsql')->whereIn('ponbr', $ponbrs)->pluck('id', 'ponbr');
        $csMap = TrCS::on('pgsql')->whereIn('csid', $csids)->pluck('id', 'csid');

        $rows->transform(function ($r) use ($poMap, $csMap) {
            $r->po_eid = isset($poMap[$r->ponbr]) ? Hashids::encode((int)$poMap[$r->ponbr]) : null;
            $r->cs_eid = isset($csMap[$r->csid])  ? Hashids::encode((int)$csMap[$r->csid])  : null;
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    /**
     * =========================
     * BQ (PAKAI VIEW v_lastorder_bq di PGSQL)
     * =========================
     */
    private function datatableJsonBQ(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        // Sesuaikan index dengan kolom DataTables TAB BQ kamu
        $columns = [
            0 => 'bqid',
            1 => 'csid',
            2 => 'vendor_name',
            3 => 'bq_descr',
            4 => 'product_price',
            5 => 'jasa_price',
            6 => 'total_price',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'bqid';

        $base = ViewLastorderBq::query(); // ✅ otomatis pakai connection pgsql

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->whereRaw("CAST(bqid AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(csid AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('vendor_name', 'ilike', "%{$search}%")
                  ->orWhere('bq_descr', 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST(product_price AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(jasa_price AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(total_price AS TEXT) ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base->select([
                'bqid',
                'csid',
                'vendor_id',
                'vendor_name',
                'bq_descr',
                'product_price',
                'jasa_price',
                'total_price',
            ])
            ->orderBy($orderCol, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        // encode CS link kalau mau klik ke CS
        $csids = $rows->pluck('csid')->filter()->unique();
        $csMap = TrCS::on('pgsql')->whereIn('csid', $csids)->pluck('id', 'csid');

        $rows->transform(function ($r) use ($csMap) {
            $r->cs_eid = isset($csMap[$r->csid])
                ? Hashids::encode((int)$csMap[$r->csid])
                : null;
            return $r;
        });

        // =========================
        // ✅ Mapping BQ ID (bqid -> tr_bq_cs.id)
        // =========================
        $bqids = $rows->pluck('bqid')->filter()->unique()->values();

        $bqMap = TrBQCS::on('pgsql')
            ->whereIn('bqid', $bqids)
            ->pluck('id', 'bqid'); // [bqid => id]

        // encode bq_eid
        $rows->transform(function ($r) use ($bqMap) {
            $bqId = $bqMap[$r->bqid] ?? null;
            $r->bq_eid = $bqId ? Hashids::encode((int) $bqId) : null;
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
