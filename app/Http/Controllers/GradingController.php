<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StoGrading;
use App\Models\StoSubGrading;

class GradingController extends Controller
{
    public function index()
    {
        $grades = StoGrading::where('status', 'A')
            ->orderBy('grade_name')
            ->get(['grade_id', 'grade_name']);

        return view('pages.grading.grading', compact('grades'));
    }

    // =========================================================
    // Grading (hr_ms_sto_grading)
    // =========================================================

    public function json()
    {
        $grades = StoGrading::select([
                'id',
                'grade_id',
                'grade_name',
                'grade_color_code',
                'group_cpny_id',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade_id'         => 'required|string|max:50|unique:pgsql3.hr_ms_sto_grading,grade_id',
            'grade_name'       => 'required|string|max:200',
            'grade_color_code' => 'nullable|string|max:20',
            'group_cpny_id'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $grade = StoGrading::create([
                'grade_id'         => strtoupper($request->grade_id),
                'grade_name'       => $request->grade_name,
                'grade_color_code' => $request->grade_color_code,
                'group_cpny_id'    => strtoupper((string) $request->group_cpny_id),
                'status'           => 'A',
                'created_user'     => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $grade,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan grading',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $grade = StoGrading::findOrFail($id);

        return response()->json([
            'id'                => $grade->id,
            'grade_id'          => $grade->grade_id,
            'grade_name'        => $grade->grade_name,
            'grade_color_code'  => $grade->grade_color_code,
            'group_cpny_id'     => $grade->group_cpny_id,
            'status'            => $grade->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $grade = StoGrading::findOrFail($id);

        $request->validate([
            'grade_id'         => 'required|string|max:50|unique:pgsql3.hr_ms_sto_grading,grade_id,' . $grade->id,
            'grade_name'       => 'required|string|max:200',
            'grade_color_code' => 'nullable|string|max:20',
            'group_cpny_id'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $grade->update([
                'grade_id'         => strtoupper($request->grade_id),
                'grade_name'       => $request->grade_name,
                'grade_color_code' => $request->grade_color_code,
                'group_cpny_id'    => strtoupper((string) $request->group_cpny_id),
                'updated_user'     => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update grading',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $grade = StoGrading::findOrFail($id);

        $status = request('status');

        $grade->update([
            'status'       => $status,
            'updated_user' => Auth::check() ? Auth::user()->username : 'system',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }

    // =========================================================
    // Sub Grading (hr_ms_sto_subgrading)
    // =========================================================

    public function jsonSub()
    {
        $subgrades = StoSubGrading::query()
            ->leftJoin('hr_ms_sto_grading', DB::raw('hr_ms_sto_subgrading.grade_id::varchar'), '=', 'hr_ms_sto_grading.grade_id')
            ->select([
                'hr_ms_sto_subgrading.id',
                'hr_ms_sto_subgrading.subgrade_id',
                'hr_ms_sto_subgrading.subgrade_name',
                'hr_ms_sto_subgrading.subgrade_color_code',
                'hr_ms_sto_subgrading.grade_id',
                'hr_ms_sto_grading.grade_name',
                'hr_ms_sto_subgrading.group_grade',
                'hr_ms_sto_subgrading.group_cpny_id',
                'hr_ms_sto_subgrading.status',
            ])
            ->orderByDesc('hr_ms_sto_subgrading.id')
            ->get();

        return response()->json(['data' => $subgrades]);
    }

    public function storeSub(Request $request)
    {
        $request->validate([
            'subgrade_id'          => 'required|string|max:50|unique:pgsql3.hr_ms_sto_subgrading,subgrade_id',
            'subgrade_name'        => 'required|string|max:200',
            'subgrade_color_code'  => 'nullable|string|max:20',
            'grade_id'             => 'required|string|max:50',
            'group_grade'          => 'nullable|string|max:50',
            'group_cpny_id'        => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $subgrade = StoSubGrading::create([
                'subgrade_id'         => strtoupper($request->subgrade_id),
                'subgrade_name'       => $request->subgrade_name,
                'subgrade_color_code' => $request->subgrade_color_code,
                'grade_id'            => $request->grade_id,
                'group_grade'         => $request->group_grade,
                'group_cpny_id'       => strtoupper((string) $request->group_cpny_id),
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $subgrade,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan sub grading',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editSub($id)
    {
        $subgrade = StoSubGrading::findOrFail($id);

        return response()->json([
            'id'                   => $subgrade->id,
            'subgrade_id'          => $subgrade->subgrade_id,
            'subgrade_name'        => $subgrade->subgrade_name,
            'subgrade_color_code'  => $subgrade->subgrade_color_code,
            'grade_id'             => $subgrade->grade_id,
            'group_grade'          => $subgrade->group_grade,
            'group_cpny_id'        => $subgrade->group_cpny_id,
            'status'               => $subgrade->status,
        ]);
    }

    public function updateSub(Request $request, $id)
    {
        $subgrade = StoSubGrading::findOrFail($id);

        $request->validate([
            'subgrade_id'          => 'required|string|max:50|unique:pgsql3.hr_ms_sto_subgrading,subgrade_id,' . $subgrade->id,
            'subgrade_name'        => 'required|string|max:200',
            'subgrade_color_code'  => 'nullable|string|max:20',
            'grade_id'             => 'required|string|max:50',
            'group_grade'          => 'nullable|string|max:50',
            'group_cpny_id'        => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $subgrade->update([
                'subgrade_id'         => strtoupper($request->subgrade_id),
                'subgrade_name'       => $request->subgrade_name,
                'subgrade_color_code' => $request->subgrade_color_code,
                'grade_id'            => $request->grade_id,
                'group_grade'         => $request->group_grade,
                'group_cpny_id'       => strtoupper((string) $request->group_cpny_id),
                'updated_user'        => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update sub grading',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatusSub($id)
    {
        $subgrade = StoSubGrading::findOrFail($id);

        $status = request('status');

        $subgrade->update([
            'status'       => $status,
            'updated_user' => Auth::check() ? Auth::user()->username : 'system',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
