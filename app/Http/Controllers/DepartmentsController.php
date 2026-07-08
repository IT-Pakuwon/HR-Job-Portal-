<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\MsDepartment;
use App\Models\DepartmentFin;
use App\Models\DepartmentHR;
use App\Models\MsDivision;

class DepartmentsController extends Controller
{
    public function index()
    {
        $divisions = MsDivision::where('status', 'A')
            ->orderBy('division_name')
            ->get(['division_id', 'division_name']);

        $departmentFins = DepartmentFin::select('department_fin_id', DB::raw('MAX(department_name) as department_name'))
            ->groupBy('department_fin_id')
            ->orderBy('department_fin_id')
            ->get();

        $departments = MsDepartment::where('status', 'A')
            ->orderBy('department_name')
            ->get(['id', 'department_id', 'department_name']);

        // view boleh kamu taruh di resources/views/pages/department/department.blade.php
        return view('pages.department.department', compact('divisions', 'departmentFins', 'departments'));
    }

    public function json()
    {
        $department = MsDepartment::select([
                'id',
                'department_id',
                'department_name',
                'department_fin_id',               
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $department]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id'     => 'required|string|max:50|unique:pgsql2.ms_department,department_id',
            'department_name'   => 'required|string|max:200',
            'department_fin_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept = MsDepartment::create([
                'department_id'     => strtoupper($request->department_id),
                'department_name'   => strtoupper($request->department_name),
                'department_fin_id' => strtoupper($request->department_fin_id ?? ''),
                'status'            => 'A',
                'created_by'        => $loginUser->username ?? 'system',
                'created_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $dept,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan department',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $dept = MsDepartment::findOrFail($id);

        return response()->json([
            'id'                => $dept->id,
            'department_id'     => $dept->department_id,
            'department_name'   => $dept->department_name,
            'department_fin_id' => $dept->department_fin_id,
            'status'            => $dept->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dept = MsDepartment::findOrFail($id);

        $request->validate([
            'department_id'     => 'required|string|max:50|unique:pgsql2.ms_department,department_id,' . $dept->id,
            'department_name'   => 'required|string|max:200',
            'department_fin_id' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept->update([
                'department_id'     => strtoupper($request->department_id),
                'department_name'   => strtoupper($request->department_name),
                'department_fin_id' => strtoupper($request->department_fin_id ?? ''),
                'updated_by'        => $loginUser->username ?? 'system',
                'updated_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update department',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $dept = MsDepartment::findOrFail($id);

        $status = request('status'); // 'A' atau 'X'

        $dept->update([
            'status'     => $status,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }

    // =========================================================
    // Assign Department Fin to Department (ms_department_fin)
    // =========================================================

    public function jsonFin()
    {
        $department = DepartmentFin::select([
                'id',
                'department_fin_id',
                'department_name',
                'cpny_id',
                'ifca_dept_cd',
                'solomon_subaccount_dept',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        $assignedByFinId = MsDepartment::whereNotNull('department_fin_id')
            ->where('department_fin_id', '!=', '')
            ->get(['id', 'department_id', 'department_name', 'department_fin_id'])
            ->groupBy('department_fin_id');

        $department->each(function ($row) use ($assignedByFinId) {
            $matches = $assignedByFinId->get($row->department_fin_id);

            $row->assigned_department_id   = $matches ? $matches->first()->id : null;
            $row->assigned_department_name = $matches
                ? $matches->pluck('department_name')->implode(', ')
                : null;
        });

        return response()->json(['data' => $department]);
    }

    public function storeFin(Request $request)
    {
        $request->validate([
            'department_fin_id'       => 'required|string|max:50',
            'cpny_id'                 => 'nullable|string|max:50',
            'ifca_dept_cd'            => 'nullable|string|max:50',
            'solomon_subaccount_dept' => 'nullable|string|max:50',
            'assign_department_id'    => 'nullable|integer',
        ]);

        $departmentFinId = strtoupper($request->department_fin_id);
        $master = DepartmentFin::where('department_fin_id', $departmentFinId)->first();

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Department Fin tidak ditemukan di Master Department Fin',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept = DepartmentFin::create([
                'department_fin_id'       => $departmentFinId,
                'department_name'         => $master->department_name,
                'cpny_id'                 => strtoupper($request->cpny_id ?? ''),
                'ifca_dept_cd'            => strtoupper($request->ifca_dept_cd ?? ''),
                'solomon_subaccount_dept' => strtoupper($request->solomon_subaccount_dept ?? ''),
                'status'                  => 'A',
                'created_by'              => $loginUser->username ?? 'system',
                'created_at'              => now(),
            ]);

            $this->assignDepartmentFin($request->assign_department_id, $departmentFinId, $loginUser);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $dept,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan department finance',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editFin($id)
    {
        $dept = DepartmentFin::findOrFail($id);

        $assigned = MsDepartment::where('department_fin_id', $dept->department_fin_id)->first();

        return response()->json([
            'id'                      => $dept->id,
            'department_fin_id'       => $dept->department_fin_id,
            'department_name'         => $dept->department_name,
            'cpny_id'                 => $dept->cpny_id,
            'ifca_dept_cd'            => $dept->ifca_dept_cd,
            'solomon_subaccount_dept' => $dept->solomon_subaccount_dept,
            'status'                  => $dept->status,
            'assign_department_id'    => $assigned->id ?? null,
        ]);
    }

    public function updateFin(Request $request, $id)
    {
        $dept = DepartmentFin::findOrFail($id);

        $request->validate([
            'department_fin_id'       => 'required|string|max:50',
            'cpny_id'                 => 'nullable|string|max:50',
            'ifca_dept_cd'            => 'nullable|string|max:50',
            'solomon_subaccount_dept' => 'nullable|string|max:50',
            'assign_department_id'    => 'nullable|integer',
        ]);

        $departmentFinId = strtoupper($request->department_fin_id);
        $master = DepartmentFin::where('department_fin_id', $departmentFinId)->first();

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Department Fin tidak ditemukan di Master Department Fin',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept->update([
                'department_fin_id'       => $departmentFinId,
                'department_name'         => $master->department_name,
                'cpny_id'                 => strtoupper($request->cpny_id ?? ''),
                'ifca_dept_cd'            => strtoupper($request->ifca_dept_cd ?? ''),
                'solomon_subaccount_dept' => strtoupper($request->solomon_subaccount_dept ?? ''),
                'updated_by'              => $loginUser->username ?? 'system',
                'updated_at'              => now(),
            ]);

            $this->assignDepartmentFin($request->assign_department_id, $departmentFinId, $loginUser);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update department finance',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatusFin($id)
    {
        $dept = DepartmentFin::findOrFail($id);

        $status = request('status');

        $dept->update([
            'status'     => $status,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }

    private function assignDepartmentFin($departmentId, $departmentFinId, $loginUser)
    {
        if (!$departmentId) {
            return;
        }

        MsDepartment::where('id', $departmentId)->update([
            'department_fin_id' => $departmentFinId,
            'updated_by'        => $loginUser->username ?? 'system',
            'updated_at'        => now(),
        ]);
    }

    // =========================================================
    // Maintain Department Fin (ms_department_fin, grouped by department_fin_id)
    // =========================================================

    public function jsonFinMaster()
    {
        $rows = DepartmentFin::select([
                DB::raw('MAX(id) as id'),
                'department_fin_id',
                DB::raw('MAX(department_name) as department_name'),
                DB::raw('MAX(status) as status'),
            ])
            ->groupBy('department_fin_id')
            ->orderByDesc(DB::raw('MAX(id)'))
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function storeFinMaster(Request $request)
    {
        $request->validate([
            'department_fin_id' => 'required|string|max:50',
            'department_name'   => 'required|string|max:200',
        ]);

        $departmentFinId = strtoupper($request->department_fin_id);

        if (DepartmentFin::where('department_fin_id', $departmentFinId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Department Fin ID sudah ada',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept = DepartmentFin::create([
                'department_fin_id' => $departmentFinId,
                'department_name'   => strtoupper($request->department_name),
                'status'            => 'A',
                'created_by'        => $loginUser->username ?? 'system',
                'created_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $dept,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan master department finance',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editFinMaster($id)
    {
        $dept = DepartmentFin::findOrFail($id);

        return response()->json([
            'id'                => $dept->id,
            'department_fin_id' => $dept->department_fin_id,
            'department_name'   => $dept->department_name,
            'status'            => $dept->status,
        ]);
    }

    public function updateFinMaster(Request $request, $id)
    {
        $dept = DepartmentFin::findOrFail($id);
        $oldDepartmentFinId = $dept->department_fin_id;

        $request->validate([
            'department_fin_id' => [
                'required', 'string', 'max:50',
                Rule::unique('pgsql.ms_department_fin', 'department_fin_id')
                    ->ignore($oldDepartmentFinId, 'department_fin_id'),
            ],
            'department_name'   => 'required|string|max:200',
        ]);

        $newDepartmentFinId = strtoupper($request->department_fin_id);
        $newDepartmentName  = strtoupper($request->department_name);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            DepartmentFin::where('department_fin_id', $oldDepartmentFinId)->update([
                'department_fin_id' => $newDepartmentFinId,
                'department_name'   => $newDepartmentName,
                'updated_by'        => $loginUser->username ?? 'system',
                'updated_at'        => now(),
            ]);

            if ($newDepartmentFinId !== $oldDepartmentFinId) {
                MsDepartment::where('department_fin_id', $oldDepartmentFinId)->update([
                    'department_fin_id' => $newDepartmentFinId,
                    'updated_by'        => $loginUser->username ?? 'system',
                    'updated_at'        => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update master department finance',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatusFinMaster($id)
    {
        $dept = DepartmentFin::findOrFail($id);

        $status = request('status');

        DepartmentFin::where('department_fin_id', $dept->department_fin_id)->update([
            'status'     => $status,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }

    // =========================================================
    // Department & Division HR (hr_ms_department)
    // =========================================================

    public function jsonHr()
    {
        $department = DepartmentHR::query()
            ->leftJoin('hr_ms_division', 'hr_ms_department.division_id', '=', 'hr_ms_division.division_id')
            ->select([
                'hr_ms_department.id',
                'hr_ms_department.department_id',
                'hr_ms_department.department_name',
                'hr_ms_department.division_id',
                'hr_ms_division.division_name',
                'hr_ms_department.status',
            ])
            ->orderByDesc('hr_ms_department.id')
            ->get();

        return response()->json(['data' => $department]);
    }

    public function storeHr(Request $request)
    {
        $request->validate([
            'department_id'   => 'required|string|max:50|unique:mysql3.hr_ms_department,department_id',
            'department_name' => 'required|string|max:200',
            'division_id'     => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept = DepartmentHR::create([
                'department_id'   => strtoupper($request->department_id),
                'department_name' => strtoupper($request->department_name),
                'division_id'     => $request->division_id,
                'status'          => 'A',
                'created_user'    => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $dept,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan department HR',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editHr($id)
    {
        $dept = DepartmentHR::findOrFail($id);

        return response()->json([
            'id'              => $dept->id,
            'department_id'   => $dept->department_id,
            'department_name' => $dept->department_name,
            'division_id'     => $dept->division_id,
            'status'          => $dept->status,
        ]);
    }

    public function updateHr(Request $request, $id)
    {
        $dept = DepartmentHR::findOrFail($id);

        $request->validate([
            'department_id'   => 'required|string|max:50|unique:mysql3.hr_ms_department,department_id,' . $dept->id,
            'department_name' => 'required|string|max:200',
            'division_id'     => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $dept->update([
                'department_id'   => strtoupper($request->department_id),
                'department_name' => strtoupper($request->department_name),
                'division_id'     => $request->division_id,
                'updated_user'    => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update department HR',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatusHr($id)
    {
        $dept = DepartmentHR::findOrFail($id);

        $status = request('status');

        $dept->update([
            'status'       => $status,
            'updated_user' => Auth::check() ? Auth::user()->username : 'system',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }

    // =========================================================
    // Maintain Division (hr_ms_division)
    // =========================================================

    public function jsonDivision()
    {
        $division = MsDivision::select([
                'id',
                'division_id',
                'division_name',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $division]);
    }

    public function storeDivision(Request $request)
    {
        $request->validate([
            'division_id'   => 'required|string|max:50|unique:mysql3.hr_ms_division,division_id',
            'division_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $division = MsDivision::create([
                'division_id'   => strtoupper($request->division_id),
                'division_name' => strtoupper($request->division_name),
                'status'        => 'A',
                'created_user'  => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $division,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan division',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editDivision($id)
    {
        $division = MsDivision::findOrFail($id);

        return response()->json([
            'id'            => $division->id,
            'division_id'   => $division->division_id,
            'division_name' => $division->division_name,
            'status'        => $division->status,
        ]);
    }

    public function updateDivision(Request $request, $id)
    {
        $division = MsDivision::findOrFail($id);

        $request->validate([
            'division_id'   => 'required|string|max:50|unique:mysql3.hr_ms_division,division_id,' . $division->id,
            'division_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $loginUser = Auth::user();

            $division->update([
                'division_id'   => strtoupper($request->division_id),
                'division_name' => strtoupper($request->division_name),
                'updated_user'  => $loginUser->username ?? 'system',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update division',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatusDivision($id)
    {
        $division = MsDivision::findOrFail($id);

        $status = request('status');

        $division->update([
            'status'       => $status,
            'updated_user' => Auth::check() ? Auth::user()->username : 'system',
        ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
