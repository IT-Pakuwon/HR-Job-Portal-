<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsInventory;

class InventoryController extends Controller
{
    public function index()
    {
        return view('pages.inventory.inventory');
    }

    public function json(Request $request)
    {
        $typeFilter = strtoupper(trim((string) $request->get('type_filter', ''))); // STOCK / NONSTOCK / ''

        $q = MsInventory::query()->select([
            'id',
            'inventoryid',
            'inventory_descr',
            'item_type',
            'item_sub_type',
            'item_class',
            'item_sub_class',
            'item_category',
            'stock_unit',
            'purchase_unit',
            'status',
        ]);

        if ($typeFilter === 'STOCK') {
            $q->where('item_type', 'GI');
        } elseif ($typeFilter === 'NONSTOCK') {
            $q->whereIn('item_type', ['NS', 'SE']);
        }

        $items = $q->orderByDesc('id')->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventoryid'      => 'required|string|max:50|unique:pgsql.ms_inventory,inventoryid',
            'inventory_descr'  => 'required|string|max:255',
            'item_type'        => 'nullable|string|max:100',
            'item_sub_type'    => 'nullable|string|max:100',
            'item_class'       => 'nullable|string|max:100',
            'item_sub_class'   => 'nullable|string|max:100',
            'item_category'    => 'nullable|string|max:100',
            'stock_unit'       => 'nullable|string|max:50',
            'purchase_unit'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $inv = MsInventory::create([
                'inventoryid'     => strtoupper($request->inventoryid),
                'inventory_descr' => strtoupper($request->inventory_descr),

                'item_type'       => $request->item_type,
                'item_sub_type'   => $request->item_sub_type,
                'item_class'      => $request->item_class,
                'item_sub_class'  => $request->item_sub_class,
                'item_category'   => $request->item_category,
                'stock_unit'      => $request->stock_unit,
                'purchase_unit'   => $request->purchase_unit,

                'status'          => 'A',
                'created_by'      => $loginUser->username ?? 'system',
                'created_at'      => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'inventory' => $inv]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan inventory',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $inv = MsInventory::findOrFail($id);

        return response()->json([
            'id'             => $inv->id,
            'inventoryid'    => $inv->inventoryid,
            'inventory_descr'=> $inv->inventory_descr,
            'item_type'      => $inv->item_type,
            'item_sub_type'  => $inv->item_sub_type,
            'item_class'     => $inv->item_class,
            'item_sub_class' => $inv->item_sub_class,
            'item_category'  => $inv->item_category,
            'stock_unit'     => $inv->stock_unit,
            'purchase_unit'  => $inv->purchase_unit,
            'status'         => $inv->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $inv = MsInventory::findOrFail($id);

        $request->validate([
            'inventoryid'      => 'required|string|max:50|unique:pgsql.ms_inventory,inventoryid,' . $inv->id,
            'inventory_descr'  => 'required|string|max:255',
            'item_type'        => 'nullable|string|max:100',
            'item_sub_type'    => 'nullable|string|max:100',
            'item_class'       => 'nullable|string|max:100',
            'item_sub_class'   => 'nullable|string|max:100',
            'item_category'    => 'nullable|string|max:100',
            'stock_unit'       => 'nullable|string|max:50',
            'purchase_unit'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $inv->update([
                'inventoryid'     => strtoupper($request->inventoryid),
                'inventory_descr' => strtoupper($request->inventory_descr),

                'item_type'       => $request->item_type,
                'item_sub_type'   => $request->item_sub_type,
                'item_class'      => $request->item_class,
                'item_sub_class'  => $request->item_sub_class,
                'item_category'   => $request->item_category,
                'stock_unit'      => $request->stock_unit,
                'purchase_unit'   => $request->purchase_unit,

                'updated_by'      => $loginUser->username ?? 'system',
                'updated_at'      => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal update inventory',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $inv = MsInventory::findOrFail($id);
        $newStatus = request('status'); // 'A' / 'X'

        $inv->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
