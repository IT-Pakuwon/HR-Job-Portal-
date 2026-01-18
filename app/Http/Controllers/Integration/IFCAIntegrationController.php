<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\MsInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IFCAIntegrationController extends Controller
{
    /**
     * Halaman utama IFCA Integration
     */
    public function index()
    {
        // view berada di: resources/views/pages/integration/ifcaintegration.blade.php
        return view('pages.integration.ifcaintegration');
    }

    /**
     * AJAX list Non Stock
     * Filter:
     * - item_type IN ('SE','NS')
     * - status = 'A'
     * - created_at BETWEEN from..to
     */
    public function nonStockList(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        if (!$from || !$to) {
            return response()->json([
                'ok'      => false,
                'message' => 'Start date dan end date wajib diisi',
                'data'    => [],
            ], 422);
        }

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        $rows = MsInventory::query()
            ->select([
                'id',
                'inventoryid',
                'inventory_descr',
                'purchase_unit',
            ])
            ->whereIn('item_type', ['SE', 'NS'])
            ->where('status', 'A')
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->orderBy('inventoryid')
            ->limit(100) // safety limit
            ->get();

        return response()->json([
            'ok'   => true,
            'data' => $rows,
        ]);
    }

    /**
     * Tombol Process Non Stock
     * (nanti: insert ke staging + call API ERP)
     */
    public function processNonStock(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = $request->ids;

        // TODO NEXT STEP:
        // 1. Ambil MsInventory by $ids
        // 2. Map ke staging_ifca_po_item
        // 3. Set process_flag = 'N'
        // 4. Call API ERP

        return response()->json([
            'ok'     => true,
            'count'  => count($ids),
            'message'=> 'Non Stock items queued for processing',
        ]);
    }
}
