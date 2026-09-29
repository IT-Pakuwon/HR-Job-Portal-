<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsFloor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsFloorController extends Controller
{
    public function json()
    {
        $floors = TsFloor::select(['id', 'sitetype', 'floor', 'order', 'status'])
            ->orderBy('sitetype')
            ->orderBy('order')
            ->get();

        return response()->json(['data' => $floors]);
    }

    public function options(Request $request)
    {
        $q = TsFloor::where('status', 'A')
            ->orderBy('sitetype')
            ->orderBy('order');

        if ($request->filled('sitetype')) {
            $q->where('sitetype', $request->sitetype);
        }

        return response()->json(['data' => $q->get(['id', 'sitetype', 'floor', 'order'])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sitetype' => 'required|string|max:50|exists:mysql5.mssite,sitetype',
            'floor'    => 'required|string|max:50',
            'order'    => 'nullable|integer',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $floor = TsFloor::create([
                'sitetype'            => $request->sitetype,
                'floor'               => $request->floor,
                'order'               => $request->order,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
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
            'id'       => $floor->id,
            'sitetype' => $floor->sitetype,
            'floor'    => $floor->floor,
            'order'    => $floor->order,
            'status'   => $floor->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $floor = TsFloor::findOrFail($id);

        $request->validate([
            'sitetype' => 'required|string|max:50|exists:mysql5.mssite,sitetype',
            'floor'    => 'required|string|max:50',
            'order'    => 'nullable|integer',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $floor->update([
                'sitetype'            => $request->sitetype,
                'floor'               => $request->floor,
                'order'               => $request->order,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
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
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
