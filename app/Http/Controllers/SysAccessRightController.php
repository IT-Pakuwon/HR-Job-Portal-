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
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
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
            ->get(['menu_id', 'menu_name', 'application_id']);

        $applications = SysApplication::on('pgsql2')
            ->where('status', 'A')
            ->orderBy('application_id')
            ->get(['application_id', 'application_name']);

        $accessNames = collect(['VIEW', 'CREATE', 'EDIT', 'DELETE'])
            ->merge(
                SysAccessRight::whereNotNull('access_name')
                    ->distinct()
                    ->pluck('access_name')
            )
            ->map(fn ($n) => strtoupper(trim($n)))
            ->unique()
            ->values();

        $matrixScreens = $screens->whereNotNull('application_id')->map(fn ($s) => [
            'menu_id'        => $s->menu_id,
            'menu_name'      => $s->menu_name,
            'application_id' => $s->application_id,
        ])->values();

        return view('pages.access_rights.access_rights', compact('roles', 'screens', 'applications', 'accessNames', 'matrixScreens'));
    }

    public function byRole($roleId)
    {
        $rows = SysAccessRight::where('role_id', $roleId)
            ->where('status', 'A')
            ->get(['screen_id', 'application_id', 'access_name']);

        $rights = $rows->groupBy(fn ($r) => $r->screen_id . '|' . $r->application_id)
            ->map(fn ($group) => $group->pluck('access_name'));

        return response()->json(['rights' => $rights]);
    }

    public function saveByRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|string|max:50',
            'rights'  => 'array',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {
            $user = Auth::user();

            SysAccessRight::where('role_id', $request->role_id)->delete();

            foreach ($request->input('rights', []) as $entry) {
                $screenId = $entry['screen_id'] ?? null;
                $appId    = $entry['application_id'] ?? null;
                $names    = $entry['access_names'] ?? [];

                if (!$screenId || !$appId || empty($names)) {
                    continue;
                }

                foreach (array_unique($names) as $name) {
                    SysAccessRight::create([
                        'role_id'        => $request->role_id,
                        'screen_id'      => $screenId,
                        'application_id' => $appId,
                        'access_name'    => strtoupper(trim($name)),
                        'access_right'   => true,
                        'access_type'    => 'NORMAL',
                        'status'         => 'A',
                        'created_by'     => $user->username ?? 'system',
                        'updated_by'     => $user->username ?? 'system',
                    ]);
                }
            }

            DB::connection('pgsql2')->commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('pgsql2')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan access rights',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
