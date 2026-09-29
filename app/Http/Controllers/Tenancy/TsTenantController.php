<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsLocation;
use App\Models\Tenancy\TsTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsTenantController extends Controller
{
    public function json()
    {
        $tenants = TsTenant::with([
                'location:id,locationname,siteid',
                'floor:id,floor',
                'tenantCompany:id,tenantcompanyname',
            ])
            ->select(['id', 'storename', 'tenantcompanyid', 'siteid', 'locationid', 'floorid', 'unit', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tenants]);
    }

    public function options(Request $request)
    {
        $q = TsTenant::where('status', 'A')->orderBy('storename');

        if ($request->filled('locationid')) {
            $q->where('locationid', $request->locationid);
        }

        return response()->json(['data' => $q->get(['id', 'storename', 'locationid', 'floorid'])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenantcompanyid' => 'required|exists:mysql5.mstenantcompany,id',
            'locationid'      => 'required|exists:mysql5.mslocation,id',
            'floorid'         => 'required|exists:mysql5.msfloor,id',
            'storename'       => 'required|string|max:255',
            'unit'            => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $location = TsLocation::findOrFail($request->locationid);

            $tenant = TsTenant::create([
                'storename'           => $request->storename,
                'tenantcompanyid'     => $request->tenantcompanyid,
                'siteid'              => $location->siteid,
                'locationid'          => $request->locationid,
                'floorid'             => $request->floorid,
                'unit'                => $request->unit,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'tenant' => $tenant]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $tenant = TsTenant::findOrFail($id);

        return response()->json([
            'id'              => $tenant->id,
            'tenantcompanyid' => $tenant->tenantcompanyid,
            'locationid'      => $tenant->locationid,
            'floorid'         => $tenant->floorid,
            'storename'       => $tenant->storename,
            'unit'            => $tenant->unit,
            'status'          => $tenant->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = TsTenant::findOrFail($id);

        $request->validate([
            'tenantcompanyid' => 'required|exists:mysql5.mstenantcompany,id',
            'locationid'      => 'required|exists:mysql5.mslocation,id',
            'floorid'         => 'required|exists:mysql5.msfloor,id',
            'storename'       => 'required|string|max:255',
            'unit'            => 'nullable|string|max:50',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $location = TsLocation::findOrFail($request->locationid);

            $tenant->update([
                'storename'           => $request->storename,
                'tenantcompanyid'     => $request->tenantcompanyid,
                'siteid'              => $location->siteid,
                'locationid'          => $request->locationid,
                'floorid'             => $request->floorid,
                'unit'                => $request->unit,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update tenant',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $tenant = TsTenant::findOrFail($id);
        $newStatus = request('status'); // A / X

        $tenant->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
