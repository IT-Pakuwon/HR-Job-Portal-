<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsCompanyController extends Controller
{
    public function json()
    {
        $companies = TsCompany::select(['id', 'companyname', 'companycity', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $companies]);
    }

    public function options()
    {
        $companies = TsCompany::where('status', 'A')
            ->orderBy('companyname')
            ->get(['id', 'companyname']);

        return response()->json(['data' => $companies]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companyname' => 'required|string|max:255',
            'companycity' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $company = TsCompany::create([
                'companyname'         => $request->companyname,
                'companycity'         => $request->companycity,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'company' => $company]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan company',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $company = TsCompany::findOrFail($id);

        return response()->json([
            'id'          => $company->id,
            'companyname' => $company->companyname,
            'companycity' => $company->companycity,
            'status'      => $company->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = TsCompany::findOrFail($id);

        $request->validate([
            'companyname' => 'required|string|max:255',
            'companycity' => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $company->update([
                'companyname'         => $request->companyname,
                'companycity'         => $request->companycity,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update company',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $company = TsCompany::findOrFail($id);
        $newStatus = request('status'); // A / X

        $company->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
