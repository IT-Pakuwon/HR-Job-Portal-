<?php

namespace App\Http\Controllers;

use App\Models\MsApprovalGroupBiaya;
use App\Models\MsGroupbiayaNonPurch;
use App\Models\MsCompany;
use App\Models\User;
use App\Models\Autonbr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MsApprovalGroupBiayaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Sesuaikan dengan model doctype yang Anda pakai di controller lama
        $doctypes = Autonbr::select('doctype','doctype_descr')
            ->distinct()
            ->orderBy('doctype')
            ->get();

        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->orderBy('cpny_id')
            ->get();

        // Sesuaikan table/connection department jika berbeda
        $departments = DB::connection('pgsql2')
            ->table('ms_department')
            ->select('department_id')
            ->distinct()
            ->orderBy('department_id')
            ->get();

        $users = User::query()
            ->select('username', 'name')
            ->where('status', 'A')
            ->orderBy('name')
            ->get();

        $groupbiaya = MsGroupbiayaNonPurch::query()
            ->select('groupbiaya_id', 'groupbiayadescr')
            ->where('status', 'A')
            ->orderBy('groupbiayadescr')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Type Condition
        |--------------------------------------------------------------------------
        | Kalau Anda punya master category sendiri, ganti query ini.
        | Untuk sementara saya buat static.
        */
        $typecondition = collect([
            (object) ['category_name' => 'AND'],
            (object) ['category_name' => 'OR'],
        ]);

        return view('pages.approvalgroupbiaya.approvalgroupbiaya', compact(
            'doctypes',
            'companies',
            'departments',
            'users',
            'groupbiaya',
            'typecondition'
        ));
    }

    public function json()
    {
        $rows = MsApprovalGroupBiaya::query()
            ->orderBy('aprv_doctype')
            ->orderBy('aprv_cpnyid')
            ->orderBy('aprv_departementid')
            ->orderBy('aprv_groupbiaya')
            ->orderBy('aprv_leveling')
            ->orderBy('id')
            ->get();

        $groupMap = MsGroupbiayaNonPurch::query()
            ->pluck('groupbiayadescr', 'groupbiaya_id')
            ->toArray();

        $rows->transform(function ($row) use ($groupMap) {
            $row->groupbiayadescr = $groupMap[$row->aprv_groupbiaya] ?? $row->aprv_groupbiaya;
            return $row;
        });

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $request->validate([
            'aprv_doctype' => ['required', 'string'],
            'aprv_cpnyid' => ['required', 'string'],
            'aprv_departementid' => ['required', 'string'],
            'aprv_groupbiaya' => ['required', 'string'],

            'aprv_leveling' => ['required', 'array', 'min:1'],
            'aprv_leveling.*' => ['required', 'numeric'],

            'aprv_username' => ['required', 'array', 'min:1'],

            'aprv_typecondition' => ['required', 'array', 'min:1'],
            'aprv_typecondition.*' => ['required', 'in:ADD,DEL'],
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {
            $doctype = $request->aprv_doctype;
            $cpnyid = $request->aprv_cpnyid;
            $deptid = $request->aprv_departementid;

            // karena Group Biaya sekarang field header, bukan array
            $groupbiaya = $request->aprv_groupbiaya;

            $levels = $request->aprv_leveling ?? [];
            $typeconditions = $request->aprv_typecondition ?? [];
            $usernamesRows = $request->aprv_username ?? [];

            foreach ($levels as $i => $level) {
                $selectedUsernames = $usernamesRows[$i] ?? [];

                if (!is_array($selectedUsernames)) {
                    $selectedUsernames = [$selectedUsernames];
                }

                $selectedUsernames = array_values(array_filter(array_map('trim', $selectedUsernames)));

                if (empty($selectedUsernames)) {
                    continue;
                }

                $names = User::query()
                    ->whereIn('username', $selectedUsernames)
                    ->pluck('name', 'username')
                    ->toArray();

                $aprvUsername = implode(',', $selectedUsernames);

                $aprvName = collect($selectedUsernames)
                    ->map(fn ($u) => $names[$u] ?? $u)
                    ->implode(', ');

                MsApprovalGroupBiaya::create([
                    'aprv_leveling' => $level,
                    'aprv_doctype' => $doctype,
                    'aprv_cpnyid' => $cpnyid,
                    'aprv_departementid' => $deptid,
                    'aprv_username' => $aprvUsername,
                    'aprv_name' => $aprvName,

                    // FIX: jangan pakai $groupbiayas[$i]
                    'aprv_groupbiaya' => $groupbiaya,

                    'aprv_typecondition' => $typeconditions[$i] ?? null,
                    'status' => 'A',
                    'created_by' => $username,
                    'created_at' => now(),
                ]);
            }

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval Group Biaya berhasil disimpan.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan approval group biaya.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsApprovalGroupBiaya::findOrFail($id);

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $request->validate([
            'aprv_doctype' => ['required', 'string'],
            'aprv_cpnyid' => ['required', 'string'],
            'aprv_departementid' => ['required', 'string'],
            'aprv_groupbiaya' => ['required', 'string'],

            'aprv_leveling' => ['required', 'array', 'min:1'],
            'aprv_leveling.0' => ['required', 'numeric'],

            'aprv_username' => ['required', 'array', 'min:1'],

            'aprv_typecondition' => ['required', 'array', 'min:1'],
            'aprv_typecondition.0' => ['required', 'in:ADD,DEL'],
        ]);

        $row = MsApprovalGroupBiaya::findOrFail($id);

        $selectedUsernames = $request->aprv_username[0] ?? [];

        if (!is_array($selectedUsernames)) {
            $selectedUsernames = [$selectedUsernames];
        }

        $selectedUsernames = array_values(array_filter(array_map('trim', $selectedUsernames)));

        $names = User::query()
            ->whereIn('username', $selectedUsernames)
            ->pluck('name', 'username')
            ->toArray();

        $aprvUsername = implode(',', $selectedUsernames);

        $aprvName = collect($selectedUsernames)
            ->map(fn ($u) => $names[$u] ?? $u)
            ->implode(', ');

        $row->update([
            'aprv_leveling' => $request->aprv_leveling[0],
            'aprv_doctype' => $request->aprv_doctype,
            'aprv_cpnyid' => $request->aprv_cpnyid,
            'aprv_departementid' => $request->aprv_departementid,
            'aprv_username' => $aprvUsername,
            'aprv_name' => $aprvName,

            // FIX: jangan pakai [0]
            'aprv_groupbiaya' => $request->aprv_groupbiaya,

            'aprv_typecondition' => $request->aprv_typecondition[0] ?? null,
            'updated_by' => $username,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Approval Group Biaya berhasil diupdate.',
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:A,X'],
        ]);

        $row = MsApprovalGroupBiaya::findOrFail($id);

        $row->update([
            'status' => $request->status,
            'updated_by' => optional($request->user())->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate.',
        ]);
    }

    public function departments(Request $request)
    {
        $doctype = $request->query('doctype');

        /*
        |--------------------------------------------------------------------------
        | Sesuaikan logic department dengan controller lama.
        |--------------------------------------------------------------------------
        */
        $query = DB::connection('pgsql2')
            ->table('ms_department')
            ->select('department_id')
            ->distinct()
            ->orderBy('department_id');

        if ($doctype) {
            // kalau memang department terikat doctype, tambahkan where di sini
            // $query->where('doctype', $doctype);
        }

        return $query->get()->map(function ($row) {
            return [
                'value' => $row->department_id,
                'text' => $row->department_id,
            ];
        });
    }
}