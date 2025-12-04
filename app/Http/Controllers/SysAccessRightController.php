<?php

namespace App\Http\Controllers;

use App\Models\SysAccessRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SysApplication;

class SysAccessRightController extends Controller
{
    public function index()
    {
        // dropdown role
        $roles = DB::connection('pgsql2')
            ->table('sys_role')
            ->where('status', 'A')
            ->orderBy('role_id')
            ->get(['role_id', 'role_name']);

        // dropdown screen (pakai sys_menu)
        $screens = DB::connection('pgsql2')
            ->table('sys_menu')
            ->where('status', 'A')
            ->orderBy('menu_id')
            ->get(['menu_id', 'menu_name']);

        $applications = SysApplication::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('application_id')
            ->get(['application_id', 'application_name']);


        return view('pages.access_rights.access_rights', compact('roles', 'screens','applications'));
    }

    public function json()
    {
        $data = SysAccessRight::select([
                'id',
                'role_id',
                'screen_id',
                'application_id',
                'access_name',
                'access_right',
                'access_type',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id'        => 'required|string|max:50',
            'screen_id'      => 'required|string|max:100',
            'application_id' => 'required|string|max:100',
            'access_type'    => 'nullable|string|max:50',
            // access_names[] & extra_access_names kita proses manual
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Ambil checkbox default (VIEW/CREATE/EDIT/DELETE)
            $accessNames = $request->input('access_names', []);  // array

            // Ambil tambahan dari input text "Other Access Names"
            $extra = $request->input('extra_access_names');
            if (!empty($extra)) {
                $extraPieces = array_filter(array_map('trim', explode(',', $extra)));
                foreach ($extraPieces as $ex) {
                    $accessNames[] = $ex;
                }
            }

            // Normalisasi & uniq
            $accessNames = array_unique(array_map(function ($v) {
                return strtoupper(trim($v));
            }, $accessNames));

            if (empty($accessNames)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal pilih satu access (VIEW/CREATE/EDIT/DELETE atau tambahkan nama lain).',
                ], 422);
            }

            // OPTIONAL: hapus dulu semua rights utk kombinasi ini supaya tidak duplikat
            SysAccessRight::where('role_id', $request->role_id)
                ->where('screen_id', $request->screen_id)
                ->where('application_id', $request->application_id)
                ->delete();

            $rows = [];
            foreach ($accessNames as $name) {
                $rows[] = SysAccessRight::create([
                    'role_id'        => $request->role_id,
                    'screen_id'      => $request->screen_id,
                    'application_id' => $request->application_id,
                    'access_name'    => $name,
                    'access_right'   => true,
                    'access_type'    => $request->access_type,
                    'status'         => 'A',
                    'created_by'     => $user->username ?? 'system',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $rows,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan access right',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = SysAccessRight::findOrFail($id);

        // Ambil semua access_name utk kombinasi role+screen+app yang sama
        $all = SysAccessRight::where('role_id', $row->role_id)
            ->where('screen_id', $row->screen_id)
            ->where('application_id', $row->application_id)
            ->where('status', 'A')
            ->pluck('access_name')
            ->toArray();

        return response()->json([
            'id'             => $row->id,
            'role_id'        => $row->role_id,
            'screen_id'      => $row->screen_id,
            'application_id' => $row->application_id,
            'access_names'   => $all,               // 📌 array semua nama access
            'access_type'    => $row->access_type,  // ambil salah satu saja
            'status'         => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = SysAccessRight::findOrFail($id);

        $request->validate([
            'role_id'        => 'required|string|max:50',
            'screen_id'      => 'required|string|max:100',
            'application_id' => 'required|string|max:100',
            'access_type'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // SAMA seperti store()
            $accessNames = $request->input('access_names', []);
            $extra = $request->input('extra_access_names');

            if (!empty($extra)) {
                $extraPieces = array_filter(array_map('trim', explode(',', $extra)));
                foreach ($extraPieces as $ex) {
                    $accessNames[] = $ex;
                }
            }

            $accessNames = array_unique(array_map(function ($v) {
                return strtoupper(trim($v));
            }, $accessNames));

            if (empty($accessNames)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal pilih satu access (VIEW/CREATE/EDIT/DELETE atau tambahkan nama lain).',
                ], 422);
            }

            // Hapus semua lama utk kombinasi role+screen+app ini
            SysAccessRight::where('role_id', $row->role_id)
                ->where('screen_id', $row->screen_id)
                ->where('application_id', $row->application_id)
                ->delete();

            // Buat baru dengan kombinasi (role_id, screen_id, application_id) hasil form
            $rows = [];
            foreach ($accessNames as $name) {
                $rows[] = SysAccessRight::create([
                    'role_id'        => $request->role_id,
                    'screen_id'      => $request->screen_id,
                    'application_id' => $request->application_id,
                    'access_name'    => $name,
                    'access_right'   => true,
                    'access_type'    => $request->access_type,
                    'status'         => 'A',
                    'created_by'     => $user->username ?? 'system',
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update access right',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $row = SysAccessRight::findOrFail($id);
        $status = request('status'); // 'A' atau 'X'
        $user   = Auth::check() ? Auth::user()->username : 'system';

        // Update semua baris utk kombinasi role+screen+app yang sama
        SysAccessRight::where('role_id', $row->role_id)
            ->where('screen_id', $row->screen_id)
            ->where('application_id', $row->application_id)
            ->update([
                'status'     => $status,
                'updated_by' => $user,
            ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
