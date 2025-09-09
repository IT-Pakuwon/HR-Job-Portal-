<?php
// app/Http/Controllers/VendorController.php
namespace App\Http\Controllers;

use App\Models\ItemPG;
use Illuminate\Http\Request;

class CanvassxController extends Controller
{
    /**  tampilkan form canvass  */
    public function createCS()
    {
        // ambil semua item aktif (atau berdasarkan dokumen tertentu)
        $items = ItemPG::orderBy('description')->get();

        return view('pages.canvass.createcs', compact('items'));   // kirim ke blade
    }

    /**  (opsional) JSON utk Ajax  */
    public function itemsJson()
    {
        return response()->json(
            ItemPG::select('id','description','qty','uom')
                ->orderBy('description')
                ->get()
        );
    }
}