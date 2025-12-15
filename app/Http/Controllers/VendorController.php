<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsVendor;

class VendorController extends Controller
{
    public function index()
    {
        return view('pages.vendor.vendor');
    }

    public function json()
    {
        $vendors = MsVendor::select([
                'id',
                'vendor_id',
                'vendor_name',
                'vendor_addr1',
                'vendor_addr2',
                'email',
                'contact_person',
                'phone_number',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'      => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id',
            'vendor_name'    => 'required|string|max:200',
            'vendor_addr1'   => 'nullable|string|max:255',
            'vendor_addr2'   => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone_number'   => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor = MsVendor::create([
                'vendor_id'      => strtoupper($request->vendor_id),
                'vendor_name'    => strtoupper($request->vendor_name),
                'vendor_addr1'   => $request->vendor_addr1,
                'vendor_addr2'   => $request->vendor_addr2,
                'email'          => $request->email,
                'contact_person' => $request->contact_person,
                'phone_number'   => $request->phone_number,
                'status'         => 'A',
                'created_by'     => $loginUser->username ?? 'system',
                'created_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'vendor' => $vendor]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan vendor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $vendor = MsVendor::findOrFail($id);

        return response()->json([
            'id'             => $vendor->id,
            'vendor_id'      => $vendor->vendor_id,
            'vendor_name'    => $vendor->vendor_name,
            'vendor_addr1'   => $vendor->vendor_addr1,
            'vendor_addr2'   => $vendor->vendor_addr2,
            'email'          => $vendor->email,
            'contact_person' => $vendor->contact_person,
            'phone_number'   => $vendor->phone_number,
            'status'         => $vendor->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = MsVendor::findOrFail($id);

        $request->validate([
            'vendor_id'      => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id,' . $vendor->id,
            'vendor_name'    => 'required|string|max:200',
            'vendor_addr1'   => 'nullable|string|max:255',
            'vendor_addr2'   => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone_number'   => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor->update([
                'vendor_id'      => strtoupper($request->vendor_id),
                'vendor_name'    => strtoupper($request->vendor_name),
                'vendor_addr1'   => $request->vendor_addr1,
                'vendor_addr2'   => $request->vendor_addr2,
                'email'          => $request->email,
                'contact_person' => $request->contact_person,
                'phone_number'   => $request->phone_number,
                'updated_by'     => $loginUser->username ?? 'system',
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal update vendor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $vendor = MsVendor::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'

        $vendor->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
