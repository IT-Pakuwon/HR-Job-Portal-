<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MsVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MsVendorController extends Controller
{
    /**
     * GET /api/vendors
     */
    public function index(Request $request)
    {
        $q = MsVendor::query();

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $q->where(function ($x) use ($search) {
                $x->where('vendor_id', 'ilike', "%{$search}%")
                  ->orWhere('vendor_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%");
            });
        }

        return response()->json(
            $q->orderBy('vendor_name')
              ->paginate($request->get('per_page', 10))
        );
    }

    /**
     * POST /api/vendors
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id'       => ['required', 'string', 'max:50', 'unique:pgsql.ms_vendor,vendor_id'],
            'vendor_name'     => ['required', 'string', 'max:255'],
            'vendor_addr1'    => ['nullable', 'string'],
            'vendor_addr2'    => ['nullable', 'string'],
            'email'           => ['nullable', 'email'],
            'contact_person'  => ['nullable', 'string'],
            'phone_number'    => ['nullable', 'string'],

            // tambahan dari model
            'npwp'            => ['nullable', 'string', 'max:50'],
            'contact_email'   => ['nullable', 'email'],
            'contact_number1' => ['nullable', 'string', 'max:30'],
            'contact_number2' => ['nullable', 'string', 'max:30'],
            'fax_no'          => ['nullable', 'string', 'max:30'],
            'post_cd'         => ['nullable', 'string', 'max:20'],

            'status'          => ['required', Rule::in(['A','I'])],
        ]);

        $data['created_by'] = Auth::user()->username ?? 'SYSTEM';

        $vendor = MsVendor::create($data);

        return response()->json([
            'success' => true,
            'data'    => $vendor
        ], 201);
    }

    /**
     * GET /api/vendors/{vendor_id}
     */
    public function show(string $vendor_id)
    {
        return response()->json(
            MsVendor::findOrFail($vendor_id)
        );
    }

    /**
     * PUT /api/vendors/{vendor_id}
     */
    public function update(Request $request, string $vendor_id)
    {
        $vendor = MsVendor::findOrFail($vendor_id);

        $data = $request->validate([
            'vendor_name'     => ['required', 'string', 'max:255'],
            'vendor_addr1'    => ['nullable', 'string'],
            'vendor_addr2'    => ['nullable', 'string'],
            'email'           => ['nullable', 'email'],
            'contact_person'  => ['nullable', 'string'],
            'phone_number'    => ['nullable', 'string'],

            // tambahan dari model
            'npwp'            => ['nullable', 'string', 'max:50'],
            'contact_email'   => ['nullable', 'email'],
            'contact_number1' => ['nullable', 'string', 'max:30'],
            'contact_number2' => ['nullable', 'string', 'max:30'],
            'fax_no'          => ['nullable', 'string', 'max:30'],
            'post_cd'         => ['nullable', 'string', 'max:20'],

            'status'          => ['required', Rule::in(['A','I'])],
        ]);

        $data['updated_by'] = Auth::user()->username ?? 'SYSTEM';

        $vendor->update($data);

        return response()->json([
            'success' => true,
            'data'    => $vendor
        ]);
    }

    /**
     * DELETE /api/vendors/{vendor_id}
     */
    public function destroy(string $vendor_id)
    {
        $vendor = MsVendor::findOrFail($vendor_id);

        $vendor->deleted_by = Auth::user()->username ?? 'SYSTEM';
        $vendor->save();
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted'
        ]);
    }
}
