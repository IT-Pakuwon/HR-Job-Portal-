<?php
// app/Http/Controllers/VendorController.php
namespace App\Http\Controllers;

use App\Models\VendorPG;

class VendorController extends Controller
{
    /**  GET /api/vendors  */
    public function index()
    {
        // Ambil semua vendor (atau tambahkan where status = 'A' dll.)
        return response()->json(
            VendorPG::select('id', 'name', 'contact', 'phone', 'address')
                  ->orderBy('name')
                  ->get()
        );
    }

    /**  GET /api/vendors/{id}  – opsional */
    public function show($id)
    {
        $vendor = VendorPG::findOrFail($id);
        return response()->json($vendor);
    }
}
