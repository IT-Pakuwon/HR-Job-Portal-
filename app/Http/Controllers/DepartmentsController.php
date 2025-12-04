<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsDepartment;

class DepartmentsController extends Controller
{
    public function index()
    {
        // view boleh kamu taruh di resources/views/pages/department/department.blade.php
        return view('pages.department.department');
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
}
