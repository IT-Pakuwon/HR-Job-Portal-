<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\MsLocation;
use App\Models\MsSubLocation;

class LocationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        return view('pages.location.index');
    }

    /* =========================
     *  LOCATION
     * ========================= */
    public function locationJson(Request $request)
    {
        $q = MsLocation::select([
                'id',
                'cpny_id',
                'location_id',
                'location_name',
                'status',
            ])
            ->orderByDesc('id');

        // optional filter by cpny_id
        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }

        return response()->json(['data' => $q->get()]);
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'cpny_id'       => 'required|string|max:20',
            'location_id'   => 'required|string|max:50|unique:pgsql.ms_location,location_id',
            'location_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $loc = MsLocation::create([
                'cpny_id'       => $request->cpny_id,
                'location_id'   => strtoupper($request->location_id),
                'location_name' => strtoupper($request->location_name),
                'status'        => 'A',
                'created_by'    => $loginUser->username ?? 'system',
                'created_at'    => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'location' => $loc]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan location',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editLocation($id)
    {
        $loc = MsLocation::findOrFail($id);

        return response()->json([
            'id'            => $loc->id,
            'cpny_id'       => $loc->cpny_id,
            'location_id'   => $loc->location_id,
            'location_name' => $loc->location_name,
            'status'        => $loc->status,
        ]);
    }

    public function updateLocation(Request $request, $id)
    {
        $loc = MsLocation::findOrFail($id);

        $request->validate([
            'cpny_id'       => 'required|string|max:20',
            'location_id'   => 'required|string|max:50|unique:pgsql.ms_location,location_id,' . $loc->id,
            'location_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $loc->update([
                'cpny_id'       => $request->cpny_id,
                'location_id'   => strtoupper($request->location_id),
                'location_name' => strtoupper($request->location_name),
                'updated_by'    => $loginUser->username ?? 'system',
                'updated_at'    => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal update location',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleLocationStatus($id)
    {
        $loc = MsLocation::findOrFail($id);
        $newStatus = request('status'); // A / X

        $loc->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }

    /* =========================
     *  SUB LOCATION
     * ========================= */
    public function subLocationJson(Request $request)
    {
        $q = MsSubLocation::select([
                'id',
                'cpny_id',
                'sub_location_id',
                'location_id',
                'sub_location_name',
                'status',
            ])
            ->orderByDesc('id');

        // filter by location_id (saat klik location di kiri)
        if ($request->filled('location_id')) {
            $q->where('location_id', $request->location_id);
        }

        // optional filter cpny_id
        if ($request->filled('cpny_id')) {
            $q->where('cpny_id', $request->cpny_id);
        }

        return response()->json(['data' => $q->get()]);
    }

    public function storeSubLocation(Request $request)
    {
        $request->validate([
            'cpny_id'            => 'required|string|max:20',
            'location_id'        => 'required|string|max:50',
            'sub_location_id'    => 'required|string|max:50|unique:pgsql.ms_sub_location,sub_location_id',
            'sub_location_name'  => 'required|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $sub = MsSubLocation::create([
                'cpny_id'           => $request->cpny_id,
                'location_id'       => $request->location_id,
                'sub_location_id'   => strtoupper($request->sub_location_id),
                'sub_location_name' => strtoupper($request->sub_location_name),
                'status'            => 'A',
                'created_by'        => $loginUser->username ?? 'system',
                'created_at'        => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'sub_location' => $sub]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan sub location',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editSubLocation($id)
    {
        $sub = MsSubLocation::findOrFail($id);

        return response()->json([
            'id'               => $sub->id,
            'cpny_id'          => $sub->cpny_id,
            'location_id'      => $sub->location_id,
            'sub_location_id'  => $sub->sub_location_id,
            'sub_location_name'=> $sub->sub_location_name,
            'status'           => $sub->status,
        ]);
    }

    public function updateSubLocation(Request $request, $id)
    {
        $sub = MsSubLocation::findOrFail($id);

        $request->validate([
            'cpny_id'            => 'required|string|max:20',
            'location_id'        => 'required|string|max:50',
            'sub_location_id'    => 'required|string|max:50|unique:pgsql.ms_sub_location,sub_location_id,' . $sub->id,
            'sub_location_name'  => 'required|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $sub->update([
                'cpny_id'           => $request->cpny_id,
                'location_id'       => $request->location_id,
                'sub_location_id'   => strtoupper($request->sub_location_id),
                'sub_location_name' => strtoupper($request->sub_location_name),
                'updated_by'        => $loginUser->username ?? 'system',
                'updated_at'        => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal update sub location',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleSubLocationStatus($id)
    {
        $sub = MsSubLocation::findOrFail($id);
        $newStatus = request('status'); // A / X

        $sub->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
