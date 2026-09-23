<?php

namespace App\Http\Controllers;

use App\Models\HrCompanyBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrCompanyBudgetController extends Controller
{
    public function json()
    {
        $rows = HrCompanyBudget::whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_cpny_id' => 'nullable|string|max:50',
            'cpnyid'        => 'required|string|max:50',
            'budget_entity_id' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row = HrCompanyBudget::create([
                'group_cpny_id' => strtoupper((string) $request->group_cpny_id),
                'cpnyid'        => strtoupper(trim($request->cpnyid)),
                'budget_entity_id' => strtoupper(trim($request->budget_entity_id)),
                'status'        => 'A',
                'created_user'  => $loginUser->username ?? 'system',
                'created_at'    => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'data' => $row]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal menyimpan company budget',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = HrCompanyBudget::whereNull('deleted_at')->findOrFail($id);

        return response()->json([
            'id'            => $row->id,
            'group_cpny_id' => $row->group_cpny_id,
            'cpnyid'        => $row->cpnyid,
            'budget_entity_id' => $row->budget_entity_id,
            'status'        => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = HrCompanyBudget::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'group_cpny_id' => 'nullable|string|max:50',
            'cpnyid'        => 'required|string|max:50',
            'budget_entity_id' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row->update([
                'group_cpny_id' => strtoupper((string) $request->group_cpny_id),
                'cpnyid'        => strtoupper(trim($request->cpnyid)),
                'budget_entity_id' => strtoupper(trim($request->budget_entity_id)),
                'updated_by'    => $loginUser->username ?? 'system',
                'updated_at'    => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal update company budget',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = HrCompanyBudget::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }

    public function destroy($id)
    {
        $row = HrCompanyBudget::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'deleted_by' => $loginUser->username ?? 'system',
            'deleted_at' => now(),
        ]);

        return response()->json(['message' => 'Company budget deleted']);
    }
}
