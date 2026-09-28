<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsFloor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TsFloorController extends Controller
{
    public function json()
    {
        $floors = TsFloor::with('location:id,location_name')
            ->select(['id', 'location_id', 'floor_code', 'floor_name', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $floors]);
    }

    public function options(Request $request)
    {
        $q = TsFloor::with('location:id,location_name')
            ->where('status', 'A')
            ->orderBy('floor_name');

        if ($request->filled('location_id')) {
            $q->where('location_id', $request->location_id);
        }

        return response()->json(['data' => $q->get(['id', 'location_id', 'floor_code', 'floor_name'])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:mysql5.ms_location,id',
            'floor_code'  => 'required|string|max:50',
            'floor_name'  => 'required|string|max:150',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $floor = TsFloor::create([
                'location_id' => $request->location_id,
                'floor_code'  => strtoupper($request->floor_code),
                'floor_name'  => $request->floor_name,
                'status'      => 'A',
                'created_by'  => $loginUser->username ?? 'system',
                'created_at'  => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'floor' => $floor]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan floor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $floor = TsFloor::findOrFail($id);

        return response()->json([
            'id'          => $floor->id,
            'location_id' => $floor->location_id,
            'floor_code'  => $floor->floor_code,
            'floor_name'  => $floor->floor_name,
            'status'      => $floor->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $floor = TsFloor::findOrFail($id);

        $request->validate([
            'location_id' => 'required|exists:mysql5.ms_location,id',
            'floor_code'  => 'required|string|max:50',
            'floor_name'  => 'required|string|max:150',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $floor->update([
                'location_id' => $request->location_id,
                'floor_code'  => strtoupper($request->floor_code),
                'floor_name'  => $request->floor_name,
                'updated_by'  => $loginUser->username ?? 'system',
                'updated_at'  => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update floor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $floor = TsFloor::findOrFail($id);
        $newStatus = request('status'); // A / X

        $floor->update([
            'status'     => $newStatus,
            'updated_by' => Auth::user()->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
