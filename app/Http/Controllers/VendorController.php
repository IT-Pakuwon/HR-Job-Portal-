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
                'npwp',
                'contact_email',
                'contact_number1',
                'contact_number2',
                'fax_no',
                'post_cd',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'        => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id',
            'vendor_name'      => 'required|string|max:200',
            'vendor_addr1'     => 'nullable|string|max:255',
            'vendor_addr2'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:150',
            'contact_person'   => 'nullable|string|max:150',
            'phone_number'     => 'nullable|string|max:50',
            'npwp'             => 'nullable|string|max:100',
            'contact_email'    => 'nullable|email|max:150',
            'contact_number1'  => 'nullable|string|max:50',
            'contact_number2'  => 'nullable|string|max:50',
            'fax_no'           => 'nullable|string|max:50',
            'post_cd'          => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor = MsVendor::create([
                'vendor_id'        => strtoupper(trim($request->vendor_id)),
                'vendor_name'      => strtoupper(trim($request->vendor_name)),
                'vendor_addr1'     => $request->vendor_addr1,
                'vendor_addr2'     => $request->vendor_addr2,
                'email'            => $request->email,
                'contact_person'   => $request->contact_person,
                'phone_number'     => $request->phone_number,
                'npwp'             => $request->npwp,
                'contact_email'    => $request->contact_email,
                'contact_number1'  => $request->contact_number1,
                'contact_number2'  => $request->contact_number2,
                'fax_no'           => $request->fax_no,
                'post_cd'          => $request->post_cd,
                'status'           => 'A',
                'created_by'       => $loginUser->username ?? 'system',
                'created_at'       => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'vendor'  => $vendor
            ]);
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
            'id'               => $vendor->id,
            'vendor_id'        => $vendor->vendor_id,
            'vendor_name'      => $vendor->vendor_name,
            'vendor_addr1'     => $vendor->vendor_addr1,
            'vendor_addr2'     => $vendor->vendor_addr2,
            'email'            => $vendor->email,
            'contact_person'   => $vendor->contact_person,
            'phone_number'     => $vendor->phone_number,
            'npwp'             => $vendor->npwp,
            'contact_email'    => $vendor->contact_email,
            'contact_number1'  => $vendor->contact_number1,
            'contact_number2'  => $vendor->contact_number2,
            'fax_no'           => $vendor->fax_no,
            'post_cd'          => $vendor->post_cd,
            'status'           => $vendor->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = MsVendor::findOrFail($id);

        $request->validate([
            'vendor_id'        => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id,' . $vendor->id,
            'vendor_name'      => 'required|string|max:200',
            'vendor_addr1'     => 'nullable|string|max:255',
            'vendor_addr2'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:150',
            'contact_person'   => 'nullable|string|max:150',
            'phone_number'     => 'nullable|string|max:50',
            'npwp'             => 'nullable|string|max:100',
            'contact_email'    => 'nullable|email|max:150',
            'contact_number1'  => 'nullable|string|max:50',
            'contact_number2'  => 'nullable|string|max:50',
            'fax_no'           => 'nullable|string|max:50',
            'post_cd'          => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor->update([
                'vendor_id'        => strtoupper(trim($request->vendor_id)),
                'vendor_name'      => strtoupper(trim($request->vendor_name)),
                'vendor_addr1'     => $request->vendor_addr1,
                'vendor_addr2'     => $request->vendor_addr2,
                'email'            => $request->email,
                'contact_person'   => $request->contact_person,
                'phone_number'     => $request->phone_number,
                'npwp'             => $request->npwp,
                'contact_email'    => $request->contact_email,
                'contact_number1'  => $request->contact_number1,
                'contact_number2'  => $request->contact_number2,
                'fax_no'           => $request->fax_no,
                'post_cd'          => $request->post_cd,
                'updated_by'       => $loginUser->username ?? 'system',
                'updated_at'       => now(),
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

    public function toggleStatus(Request $request, $id)
    {
        $vendor = MsVendor::findOrFail($id);
        $newStatus = $request->status;

        $vendor->update([
            'status'     => $newStatus,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated'
        ]);
    }
}