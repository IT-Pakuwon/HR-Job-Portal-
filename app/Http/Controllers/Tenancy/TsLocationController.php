<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TsLocationController extends Controller
{
    public function json()
    {
        $locations = TsLocation::select(['id', 'location_code', 'location_name', 'address', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $locations]);
    }

    public function options()
    {
        $locations = TsLocation::where('status', 'A')
            ->orderBy('location_name')
            ->get(['id', 'location_code', 'location_name']);

        return response()->json(['data' => $locations]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_code' => 'required|string|max:50|unique:mysql5.ms_location,location_code',
            'location_name' => 'required|string|max:200',
            'address'       => 'nullable|string|max:255',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $location = TsLocation::create([
                'location_code' => strtoupper($request->location_code),
                'location_name' => $request->location_name,
                'address'       => $request->address,
                'status'        => 'A',
                'created_by'    => $loginUser->username ?? 'system',
                'created_at'    => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'location' => $location]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $location = TsLocation::findOrFail($id);

        return response()->json([
            'id'            => $location->id,
            'location_code' => $location->location_code,
            'location_name' => $location->location_name,
            'address'       => $location->address,
            'status'        => $location->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = TsLocation::findOrFail($id);

        $request->validate([
            'location_code' => ['required', 'string', 'max:50', Rule::unique('mysql5.ms_location', 'location_code')->ignore($location->id)],
            'location_name' => 'required|string|max:200',
            'address'       => 'nullable|string|max:255',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $location->update([
                'location_code' => strtoupper($request->location_code),
                'location_name' => $request->location_name,
                'address'       => $request->address,
                'updated_by'    => $loginUser->username ?? 'system',
                'updated_at'    => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $location = TsLocation::findOrFail($id);
        $newStatus = request('status'); // A / X

        $location->update([
            'status'     => $newStatus,
            'updated_by' => Auth::user()->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
