<?php

namespace App\Http\Controllers;

use App\Models\SysScreen;
use App\Models\SysApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysScreenController extends Controller
{
    public function index()
    {
        // untuk dropdown Application
        $applications = SysApplication::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('application_id')
            ->get(['application_id', 'application_name']);

        return view('pages.screens.screens', compact('applications'));
    }

    public function json()
    {
        $screens = SysScreen::select([
                'id',
                'screen_id',
                'screen_name',
                'application_id',
                'status',
            ])
            ->orderBy('screen_id')
            ->get();

        return response()->json(['data' => $screens]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'screen_id'      => 'required|string|max:50|unique:pgsql2.sys_screen,screen_id',
            'screen_name'    => 'required|string|max:200',
            'application_id' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $screen = SysScreen::create([
                'screen_id'      => strtoupper($request->screen_id),
                'screen_name'    => $request->screen_name,
                'application_id' => $request->application_id,
                'status'         => 'A',
                'created_by'     => $user->username ?? 'system',
                'created_at'     => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'data' => $screen]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan screen',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $screen = SysScreen::findOrFail($id);

        return response()->json([
            'id'             => $screen->id,
            'screen_id'      => $screen->screen_id,
            'screen_name'    => $screen->screen_name,
            'application_id' => $screen->application_id,
            'status'         => $screen->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $screen = SysScreen::findOrFail($id);

        $request->validate([
            'screen_id'      => 'required|string|max:50|unique:pgsql2.sys_screen,screen_id,' . $screen->id,
            'screen_name'    => 'required|string|max:200',
            'application_id' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $screen->update([
                'screen_id'      => strtoupper($request->screen_id),
                'screen_name'    => $request->screen_name,
                'application_id' => $request->application_id,
                'status'         => $screen->status,
                'updated_by'     => $user->username ?? 'system',
                'updated_at'     => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update screen',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $screen = SysScreen::findOrFail($id);
        $status = request('status'); // 'A' atau 'X'

        $screen->update([
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
