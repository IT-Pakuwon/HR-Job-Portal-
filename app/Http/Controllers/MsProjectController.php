<?php

namespace App\Http\Controllers;

use App\Models\MsBudgetProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MsProjectController extends Controller
{
    public function json()
    {
        $rows = MsBudgetProject::whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'    => 'required|string|max:25|unique:pgsql2.ms_project,project_id',
            'project_name'  => 'required|string|max:255',
            'cpny_name'     => 'nullable|string|max:255',
            'area_id'       => 'nullable|string|max:10',
            'group_cpny_id' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row = MsBudgetProject::create([
                'project_id'    => strtoupper(trim($request->project_id)),
                'project_name'  => strtoupper($request->project_name),
                'cpny_name'     => strtoupper((string) $request->cpny_name),
                'area_id'       => $request->area_id,
                'group_cpny_id' => strtoupper((string) $request->group_cpny_id),
                'status'        => 'A',
                'created_by'    => $loginUser->username ?? 'system',
                'created_at'    => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'data' => $row]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal menyimpan project',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsBudgetProject::whereNull('deleted_at')->findOrFail($id);

        return response()->json([
            'id'            => $row->id,
            'project_id'    => $row->project_id,
            'project_name'  => $row->project_name,
            'cpny_name'     => $row->cpny_name,
            'area_id'       => $row->area_id,
            'group_cpny_id' => $row->group_cpny_id,
            'status'        => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = MsBudgetProject::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'project_id'    => 'required|string|max:25|unique:pgsql2.ms_project,project_id,'.$row->id,
            'project_name'  => 'required|string|max:255',
            'cpny_name'     => 'nullable|string|max:255',
            'area_id'       => 'nullable|string|max:10',
            'group_cpny_id' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row->update([
                'project_id'    => strtoupper(trim($request->project_id)),
                'project_name'  => strtoupper($request->project_name),
                'cpny_name'     => strtoupper((string) $request->cpny_name),
                'area_id'       => $request->area_id,
                'group_cpny_id' => strtoupper((string) $request->group_cpny_id),
                'updated_by'    => $loginUser->username ?? 'system',
                'updated_at'    => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal update project',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = MsBudgetProject::whereNull('deleted_at')->findOrFail($id);
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
        $row = MsBudgetProject::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'deleted_by' => $loginUser->username ?? 'system',
            'deleted_at' => now(),
        ]);

        return response()->json(['message' => 'Project deleted']);
    }
}
