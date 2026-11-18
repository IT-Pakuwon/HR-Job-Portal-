<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

use App\Models\TrPoLastPrice;
use App\Models\TrPO;
use App\Models\TrCS;

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

        // Index 0 is the DataTables responsive control column.
        $columns = [
            1 => 'bq.bqid',
            2 => 'bq.csid',
            3 => 'cs.cpny_id',
            4 => 'selected_po.ponbr',
            5 => 'selected_po.podate',
            6 => 'bq.vendor_name',
            7 => 'bq.bq_descr',
            8 => 'bq.product_price',
            9 => 'bq.jasa_price',
            10 => 'bq.total_price',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'bq.bqid';

        $base = $this->lastOrderBqQuery();

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->whereRaw("CAST(bq.bqid AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(bq.csid AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('cs.cpny_id', 'ilike', "%{$search}%")
                  ->orWhere('selected_po.ponbr', 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST(selected_po.podate AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR(selected_po.podate, 'DD/MM/YYYY') ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("TO_CHAR(selected_po.podate, 'FMDD/FMMM/YYYY') ILIKE ?", ["%{$search}%"])
                  ->orWhere('bq.vendor_name', 'ilike', "%{$search}%")
                  ->orWhere('bq.bq_descr', 'ilike', "%{$search}%")
                  ->orWhereRaw("CAST(bq.product_price AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(bq.jasa_price AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CAST(bq.total_price AS TEXT) ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = $search === '' ? $recordsTotal : (clone $base)->count();

        $rows = $base->select([
                'bq.bqid', 'bq.csid', 'cs.cpny_id',
                'selected_po.ponbr', 'selected_po.podate', 'selected_po.id as po_id',
                'bqh.id as bq_id', 'cs.id as cs_id',
                'bq.vendor_id', 'bq.vendor_name', 'bq.bq_descr',
                'bq.product_price', 'bq.jasa_price', 'bq.total_price',
            ])
            ->orderBy($orderCol, $orderDir)
            ->when($length !== -1, fn ($query) => $query->skip($start)->take($length))
            ->get();

        $rows->transform(function ($r) {
            $r->cs_eid = Hashids::encode((int) $r->cs_id);
            $r->bq_eid = Hashids::encode((int) $r->bq_id);
            $r->po_eid = Hashids::encode((int) $r->po_id);
            unset($r->cs_id, $r->bq_id, $r->po_id);
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    private function lastOrderBqQuery()
    {
        $connection = DB::connection('pgsql');
        $selectedPo = $connection->table('tr_po as po')
            ->selectRaw('DISTINCT ON (po.csid, po.cpny_id, po.vendorid) po.id, po.ponbr, po.podate, po.csid, po.cpny_id, po.vendorid')
            ->whereNull('po.deleted_at')
            ->orderBy('po.csid')
            ->orderBy('po.cpny_id')
            ->orderBy('po.vendorid')
            ->orderByRaw('po.podate DESC NULLS LAST')
            ->orderByDesc('po.id');

        return $connection->table('v_lastorder_bq as bq')
            ->join('tr_bq_cs as bqh', function ($join) {
                $join->on('bqh.bqid', '=', 'bq.bqid')->on('bqh.csid', '=', 'bq.csid');
            })
            ->join('tr_cs as cs', function ($join) {
                $join->on('cs.csid', '=', 'bqh.csid')->on('cs.cpny_id', '=', 'bqh.cpny_id');
            })
            ->joinSub($selectedPo, 'selected_po', function ($join) {
                $join->on('selected_po.csid', '=', 'bq.csid')
                    ->on('selected_po.cpny_id', '=', 'cs.cpny_id')
                    ->on('selected_po.vendorid', '=', 'bq.vendor_id');
            })
            ->whereNull('bqh.deleted_at')
            ->whereNull('cs.deleted_at')
            ->where(function ($query) {
                $query->whereNull('cs.prev_csid')->orWhere('cs.prev_csid', '');
            });
    }
}
