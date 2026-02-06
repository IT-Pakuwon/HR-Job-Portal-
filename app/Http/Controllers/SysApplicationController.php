<?php

namespace App\Http\Controllers;

use App\Models\SysApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysApplicationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return view('pages.applications.applications');
    }

    public function json()
    {
        $apps = SysApplication::select([
                'id',
                'application_id',
                'application_name',
                'status',
            ])
            ->orderBy('application_id')
            ->get();

        return response()->json(['data' => $apps]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'application_id'   => 'required|string|max:50|unique:pgsql2.sys_application,application_id',
            'application_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $app = SysApplication::create([
                'application_id'   => strtoupper($request->application_id),
                'application_name' => $request->application_name,
                'status'           => 'A',
                'created_by'       => $user->username ?? 'system',
                'created_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'data' => $app]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan application',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $app = SysApplication::findOrFail($id);

        return response()->json([
            'id'               => $app->id,
            'application_id'   => $app->application_id,
            'application_name' => $app->application_name,
            'status'           => $app->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $app = SysApplication::findOrFail($id);

        $request->validate([
            'application_id'   => 'required|string|max:50|unique:pgsql2.sys_application,application_id,' . $app->id,
            'application_name' => 'required|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $app->update([
                'application_id'   => strtoupper($request->application_id),
                'application_name' => $request->application_name,
                'updated_by'       => $user->username ?? 'system',
                'updated_at'       => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update application',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $app = SysApplication::findOrFail($id);
        $status = request('status'); // 'A' atau 'X'

        $app->update([
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
