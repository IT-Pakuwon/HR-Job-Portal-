<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsInventory;

class InventoryUserController extends Controller
{
    public function index()
    {
        return view('pages.inventory.inventoryuser');
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

}
