<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsSiteController extends Controller
{
    public function options()
    {
        $sites = TsSite::where('status', 'A')
            ->orderBy('sitename')
            ->get(['id', 'siteid', 'sitename']);

        return response()->json(['data' => $sites]);
    }

    public function siteTypes()
    {
        $types = TsSite::where('status', 'A')
            ->whereNotNull('sitetype')
            ->distinct()
            ->orderBy('sitetype')
            ->pluck('sitetype');

        return response()->json(['data' => $types]);
    }

    public function json()
    {
        $sites = TsSite::with('company:id,companyname')
            ->select(['id', 'siteid', 'sitename', 'sitetype', 'companyid', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $sites]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siteid'          => 'required|string|max:5|unique:mysql5.mssite,siteid',
            'sitename'        => 'required|string|max:255',
            'sitetype'        => 'nullable|string|max:50',
            'companyid'       => 'required|exists:mysql5.mscompany,id',
            'sitecompanyname' => 'nullable|string|max:100',
            'siteaddress'     => 'nullable|string',
            'sitephone'       => 'nullable|string|max:100',
            'sitefax'         => 'nullable|string|max:100',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $site = TsSite::create([
                'siteid'              => $request->siteid,
                'sitename'            => $request->sitename,
                'sitetype'            => $request->sitetype,
                'companyid'           => $request->companyid,
                'sitecompanyname'     => $request->sitecompanyname,
                'siteaddress'         => $request->siteaddress,
                'sitephone'           => $request->sitephone,
                'sitefax'             => $request->sitefax,
                'status'              => 'A',
                'created_user'        => $loginUser->username ?? 'system',
                'created_datetime'    => now(),
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'site' => $site]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan site',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $site = TsSite::findOrFail($id);

        return response()->json([
            'id'              => $site->id,
            'siteid'          => $site->siteid,
            'sitename'        => $site->sitename,
            'sitetype'        => $site->sitetype,
            'companyid'       => $site->companyid,
            'sitecompanyname' => $site->sitecompanyname,
            'siteaddress'     => $site->siteaddress,
            'sitephone'       => $site->sitephone,
            'sitefax'         => $site->sitefax,
            'status'          => $site->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $site = TsSite::findOrFail($id);

        $request->validate([
            'siteid'          => 'required|string|max:5|unique:mysql5.mssite,siteid,' . $site->id,
            'sitename'        => 'required|string|max:255',
            'sitetype'        => 'nullable|string|max:50',
            'companyid'       => 'required|exists:mysql5.mscompany,id',
            'sitecompanyname' => 'nullable|string|max:100',
            'siteaddress'     => 'nullable|string',
            'sitephone'       => 'nullable|string|max:100',
            'sitefax'         => 'nullable|string|max:100',
        ]);

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $site->update([
                'siteid'              => $request->siteid,
                'sitename'            => $request->sitename,
                'sitetype'            => $request->sitetype,
                'companyid'           => $request->companyid,
                'sitecompanyname'     => $request->sitecompanyname,
                'siteaddress'         => $request->siteaddress,
                'sitephone'           => $request->sitephone,
                'sitefax'             => $request->sitefax,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update site',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $site = TsSite::findOrFail($id);
        $newStatus = request('status'); // A / X

        $site->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
