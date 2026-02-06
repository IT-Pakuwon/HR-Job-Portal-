<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\MsTenant;

class TenantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return view('pages.tenant.tenant');
    }

    public function json()
    {
        $tenants = MsTenant::select([
                'id',
                'unit_id',
                'cpny_id',
                'store_name',
                'floor_id',
                'store_no',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tenants]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'    => 'required|string|max:50|unique:pgsql.ms_tenant,unit_id',
            'cpny_id'    => 'required|string|max:10',
            'store_name' => 'required|string|max:200',
            'floor_id'   => 'nullable|string|max:50',
            'store_no'   => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $tenant = MsTenant::create([
                'unit_id'    => strtoupper($request->unit_id),
                'cpny_id'    => strtoupper($request->cpny_id),
                'store_name' => strtoupper($request->store_name),
                'floor_id'   => $request->floor_id ? strtoupper($request->floor_id) : null,
                'store_no'   => $request->store_no ? strtoupper($request->store_no) : null,
                'status'     => 'A',
                'created_by' => $loginUser->username ?? 'system',
                'created_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'tenant' => $tenant]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $t = MsTenant::findOrFail($id);

        return response()->json([
            'id'         => $t->id,
            'unit_id'    => $t->unit_id,
            'cpny_id'    => $t->cpny_id,
            'store_name' => $t->store_name,
            'floor_id'   => $t->floor_id,
            'store_no'   => $t->store_no,
            'status'     => $t->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $t = MsTenant::findOrFail($id);

        $request->validate([
            'unit_id'    => 'required|string|max:50|unique:pgsql.ms_tenant,unit_id,' . $t->id,
            'cpny_id'    => 'required|string|max:10',
            'store_name' => 'required|string|max:200',
            'floor_id'   => 'nullable|string|max:50',
            'store_no'   => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $t->update([
                'unit_id'    => strtoupper($request->unit_id),
                'cpny_id'    => strtoupper($request->cpny_id),
                'store_name' => strtoupper($request->store_name),
                'floor_id'   => $request->floor_id ? strtoupper($request->floor_id) : null,
                'store_no'   => $request->store_no ? strtoupper($request->store_no) : null,
                'updated_by' => $loginUser->username ?? 'system',
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal update tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $t = MsTenant::findOrFail($id);
        $newStatus = request('status'); // A / X

        $t->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
