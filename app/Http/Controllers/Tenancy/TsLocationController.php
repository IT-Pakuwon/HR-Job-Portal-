<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsLocationController extends Controller
{
    public function json()
    {
        $locations = TsLocation::with('site:siteid,sitename')
            ->select(['id', 'locationname', 'siteid', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $locations]);
    }

    public function options()
    {
        $locations = TsLocation::where('status', 'A')
            ->orderBy('locationname')
            ->get(['id', 'locationname', 'siteid']);

        return response()->json(['data' => $locations]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'locationname' => 'required|string|max:255',
            'siteid'       => 'required|string|max:5|exists:mysql5.mssite,siteid',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $location = TsLocation::create([
                'locationname'        => $request->locationname,
                'siteid'              => $request->siteid,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
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
            'id'           => $location->id,
            'locationname' => $location->locationname,
            'siteid'       => $location->siteid,
            'status'       => $location->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = TsLocation::findOrFail($id);

        $request->validate([
            'locationname' => 'required|string|max:255',
            'siteid'       => 'required|string|max:5|exists:mysql5.mssite,siteid',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $location->update([
                'locationname'        => $request->locationname,
                'siteid'              => $request->siteid,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
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
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
