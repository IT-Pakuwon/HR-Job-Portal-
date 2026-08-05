<?php

namespace App\Http\Controllers;

use App\Models\CompanyAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyAddressController extends Controller
{
    public function json()
    {
        $rows = CompanyAddress::select([
                'id',
                'cpnyid',
                'cpnyname',
                'site',
                'sitelocation',
                'location',
                'address',
                'address2',
                'area_id',
                'group_cpny_id',
                'status',
            ])
            ->orderBy('cpnyid')
            ->orderBy('site')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpnyid' => 'required|string|max:50',
            'cpnyname' => 'required|string|max:200',
            'site' => 'nullable|string|max:50',
            'sitelocation' => 'nullable|string|max:200',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'address2' => 'nullable|string',
            'area_id' => 'nullable|string|max:100',
            'group_cpny_id' => 'nullable|string|max:100',
        ]);

        DB::connection('pgsql3')->beginTransaction();

        try {
            $user = Auth::user();

            $row = CompanyAddress::create([
                'cpnyid' => trim($request->cpnyid),
                'cpnyname' => trim($request->cpnyname),
                'site' => $request->filled('site') ? trim($request->site) : null,
                'sitelocation' => $request->filled('sitelocation') ? trim($request->sitelocation) : null,
                'location' => $request->filled('location') ? trim($request->location) : null,
                'address' => $request->filled('address') ? trim($request->address) : null,
                'address2' => $request->filled('address2') ? trim($request->address2) : null,
                'area_id' => $request->filled('area_id') ? trim($request->area_id) : null,
                'group_cpny_id' => $request->filled('group_cpny_id') ? trim($request->group_cpny_id) : null,
                'status' => 'A',
                'created_user' => $user->username ?? 'system',
            ]);

            DB::connection('pgsql3')->commit();

            return response()->json([
                'success' => true,
                'data' => $row,
                'message' => 'Placement Location berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql3')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Placement Location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = CompanyAddress::findOrFail($id);

        return response()->json([
            'id' => $row->id,
            'cpnyid' => $row->cpnyid,
            'cpnyname' => $row->cpnyname,
            'site' => $row->site,
            'sitelocation' => $row->sitelocation,
            'location' => $row->location,
            'address' => $row->address,
            'address2' => $row->address2,
            'area_id' => $row->area_id,
            'group_cpny_id' => $row->group_cpny_id,
            'status' => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = CompanyAddress::findOrFail($id);

        $request->validate([
            'cpnyid' => 'required|string|max:50',
            'cpnyname' => 'required|string|max:200',
            'site' => 'nullable|string|max:50',
            'sitelocation' => 'nullable|string|max:200',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'address2' => 'nullable|string',
            'area_id' => 'nullable|string|max:100',
            'group_cpny_id' => 'nullable|string|max:100',
        ]);

        DB::connection('pgsql3')->beginTransaction();

        try {
            $user = Auth::user();

            $row->update([
                'cpnyid' => trim($request->cpnyid),
                'cpnyname' => trim($request->cpnyname),
                'site' => $request->filled('site') ? trim($request->site) : null,
                'sitelocation' => $request->filled('sitelocation') ? trim($request->sitelocation) : null,
                'location' => $request->filled('location') ? trim($request->location) : null,
                'address' => $request->filled('address') ? trim($request->address) : null,
                'address2' => $request->filled('address2') ? trim($request->address2) : null,
                'area_id' => $request->filled('area_id') ? trim($request->area_id) : null,
                'group_cpny_id' => $request->filled('group_cpny_id') ? trim($request->group_cpny_id) : null,
                'updated_by' => $user->username ?? 'system',
            ]);

            DB::connection('pgsql3')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Placement Location berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql3')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update Placement Location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $row = CompanyAddress::findOrFail($id);
        $user = Auth::user();

        $row->update([
            'status' => $request->status,
            'updated_by' => $user->username ?? 'system',
        ]);

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Status berhasil diupdate',
        ]);
    }
}
