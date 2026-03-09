<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessUnit;
use App\Models\MsCompany;

class BusinessUnitController extends Controller
{
    public function index()
    {
        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('cpny_name')
            ->get();

        return view('pages.businessunit.businessunit', compact('companies'));
    }

    public function json(Request $request)
    {
        $cpnyId = $request->get('cpny_id');

        $query = BusinessUnit::query()
            ->from('ms_business_unit as b')
            ->leftJoin('ms_company as c', 'c.cpny_id', '=', 'b.cpny_id')
            ->select([
                'b.id',
                'b.business_unit_id',
                'b.cpny_id',
                'c.cpny_name',
                'b.business_unit_name',
                'b.ifca_entity_cd',
                'b.solomon_cpny_id',
                'b.solomon_allocation_cd',
                'b.integration_type',
                'b.status',
            ])
            ->whereNull('b.deleted_at');

        if (!empty($cpnyId)) {
            $query->where('b.cpny_id', $cpnyId);
        }

        $rows = $query
            ->orderBy('b.business_unit_id', 'asc')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_unit_id'       => 'required|string|max:50|unique:pgsql2.ms_business_unit,business_unit_id',
            'cpny_id'                => 'required|string|max:50',
            'business_unit_name'     => 'required|string|max:255',
            'ifca_entity_cd'         => 'nullable|string|max:50',
            'solomon_cpny_id'        => 'nullable|string|max:50',
            'solomon_allocation_cd'  => 'nullable|string|max:50',
            'integration_type'       => 'nullable|in:IFCA,SOLOMON',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row = BusinessUnit::create([
                'business_unit_id'      => strtoupper(trim($request->business_unit_id)),
                'cpny_id'               => strtoupper(trim($request->cpny_id)),
                'business_unit_name'    => strtoupper(trim($request->business_unit_name)),
                'ifca_entity_cd'        => strtoupper((string) $request->ifca_entity_cd),
                'solomon_cpny_id'       => strtoupper((string) $request->solomon_cpny_id),
                'solomon_allocation_cd' => strtoupper((string) $request->solomon_allocation_cd),
                'integration_type'      => strtoupper((string) $request->integration_type),
                'status'                => 'A',
                'created_by'            => $loginUser->username ?? 'system',
                'created_at'            => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal menyimpan business unit',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = BusinessUnit::whereNull('deleted_at')->findOrFail($id);

        return response()->json([
            'id'                    => $row->id,
            'business_unit_id'      => $row->business_unit_id,
            'cpny_id'               => $row->cpny_id,
            'business_unit_name'    => $row->business_unit_name,
            'ifca_entity_cd'        => $row->ifca_entity_cd,
            'solomon_cpny_id'       => $row->solomon_cpny_id,
            'solomon_allocation_cd' => $row->solomon_allocation_cd,
            'integration_type'      => $row->integration_type,
            'status'                => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = BusinessUnit::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'business_unit_id'       => 'required|string|max:50|unique:pgsql2.ms_business_unit,business_unit_id,' . $row->id,
            'cpny_id'                => 'required|string|max:50',
            'business_unit_name'     => 'required|string|max:255',
            'ifca_entity_cd'         => 'nullable|string|max:50',
            'solomon_cpny_id'        => 'nullable|string|max:50',
            'solomon_allocation_cd'  => 'nullable|string|max:50',
            'integration_type'       => 'nullable|in:IFCA,SOLOMON',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $row->update([
                'business_unit_id'      => strtoupper(trim($request->business_unit_id)),
                'cpny_id'               => strtoupper(trim($request->cpny_id)),
                'business_unit_name'    => strtoupper(trim($request->business_unit_name)),
                'ifca_entity_cd'        => strtoupper((string) $request->ifca_entity_cd),
                'solomon_cpny_id'       => strtoupper((string) $request->solomon_cpny_id),
                'solomon_allocation_cd' => strtoupper((string) $request->solomon_allocation_cd),
                'integration_type'      => strtoupper((string) $request->integration_type),
                'updated_by'            => $loginUser->username ?? 'system',
                'updated_at'            => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => 'Gagal update business unit',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = BusinessUnit::whereNull('deleted_at')->findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}