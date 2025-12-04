<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsCompany;

class CompanyController extends Controller
{
    public function index()
    {
        return view('pages.company.company');
    }

    public function json()
    {
        $companies = MsCompany::select([
                'id',
                'cpny_id',
                'cpny_name',
                'city',
                'province',
                'phone',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $companies]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpny_id'   => 'required|string|max:50|unique:pgsql2.ms_company,cpny_id',
            'cpny_name' => 'required|string|max:200',
            'city'      => 'nullable|string|max:100',
            'province'  => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $company = MsCompany::create([
                'cpny_id'          => strtoupper($request->cpny_id),
                'cpny_name'        => strtoupper($request->cpny_name),
                'address_line1'    => $request->address_line1,
                'address_line2'    => $request->address_line2,
                'city'             => $request->city,
                'province'         => $request->province,
                'postalcode'       => $request->postalcode,
                'phone'            => $request->phone,
                'fax'              => $request->fax,
                'tax_registration' => $request->tax_registration,
                'tax_address_line' => $request->tax_address_line,
                'warehouse_note'   => $request->warehouse_note,
                'status'           => 'A',
                'created_by'       => $loginUser->username ?? 'system',
                'created_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'company' => $company]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan company',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $company = MsCompany::findOrFail($id);

        return response()->json([
            'id'               => $company->id,
            'cpny_id'          => $company->cpny_id,
            'cpny_name'        => $company->cpny_name,
            'address_line1'    => $company->address_line1,
            'address_line2'    => $company->address_line2,
            'city'             => $company->city,
            'province'         => $company->province,
            'postalcode'       => $company->postalcode,
            'phone'            => $company->phone,
            'fax'              => $company->fax,
            'tax_registration' => $company->tax_registration,
            'tax_address_line' => $company->tax_address_line,
            'warehouse_note'   => $company->warehouse_note,
            'status'           => $company->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = MsCompany::findOrFail($id);

        $request->validate([
            'cpny_id'   => 'required|string|max:50|unique:pgsql2.ms_company,cpny_id,' . $company->id,
            'cpny_name' => 'required|string|max:200',
            'city'      => 'nullable|string|max:100',
            'province'  => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $company->update([
                'cpny_id'          => strtoupper($request->cpny_id),
                'cpny_name'        => strtoupper($request->cpny_name),
                'address_line1'    => $request->address_line1,
                'address_line2'    => $request->address_line2,
                'city'             => $request->city,
                'province'         => $request->province,
                'postalcode'       => $request->postalcode,
                'phone'            => $request->phone,
                'fax'              => $request->fax,
                'tax_registration' => $request->tax_registration,
                'tax_address_line' => $request->tax_address_line,
                'warehouse_note'   => $request->warehouse_note,
                'updated_by'       => $loginUser->username ?? 'system',
                'updated_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal update company',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $company = MsCompany::findOrFail($id);
        $newStatus = request('status'); // 'A' atau 'X'

        $company->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
